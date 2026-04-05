<?php

use GeoIp2\Database\Reader;

class GeoIP
{
    private Reader $city;
    private Reader $asn;
    private $FRMWRK;

    public function __construct($FRMWRK)
    {
        $dbPath = '/home/geoip/geoip-db';
        $this->city = new Reader($dbPath . '/GeoLite2-City.mmdb');
        $this->asn  = new Reader($dbPath . '/GeoLite2-ASN.mmdb');
        $this->FRMWRK = $FRMWRK;
    }

    /**
     * Проверка, является ли IP TOR exit node
     */
    private function isTorExitNode(string $ip): bool
    {
        if (!$this->FRMWRK) return false;
        $rows = $this->FRMWRK->DBRecords("
            SELECT 1 FROM tor_exit_nodes
            WHERE ip = '".$this->FRMWRK->DB()->real_escape_string($ip)."' 
              AND last_seen IS NOT NULL
            LIMIT 1
        ");
        return !empty($rows);
    }
	private function checkIP2Proxy(string $ip): array
	{
		if (!$this->FRMWRK) return [];

		$ipLongSigned = ip2long($ip);
		if ($ipLongSigned === false) return [];

		// UNSIGNED IPv4
		$ipLong = sprintf('%u', $ipLongSigned);

		$rows = $this->FRMWRK->DBRecords("
			SELECT proxy_type, country_code, usage_type, asn, threat
			FROM ip2proxy_lite
			WHERE ip_from <= {$ipLong}
			ORDER BY ip_from DESC
			LIMIT 1
		");

		if (empty($rows)) return [];

		// финальная проверка диапазона
		if ($rows[0]['ip_to'] >= $ipLong) {
			return $rows[0];
		}

		return [];
	}


    /**
     * Основной lookup
     */
    public function lookup(string $ip, bool $full_mode = false, ?string $ua = null, ?string $account_id = null): array
    {
       $result = [
			'ip' => $ip,
			'asn' => null,
			'geo' => [],
			'ptr' => null,
			'antifraud' => [],
		];

        $risk = 0;
        $confidence = 0.0;
        $signals = [];
        $behavior = [];

        // -------------------------
        //  CITY / COUNTRY / LOCATION
        // -------------------------
        try {
            $city = $this->city->city($ip);
            $result['geo']['city'] = [
                'name' => $city->city->name,
                'geoname_id' => $city->city->geonameId
            ];
            $result['geo']['country'] = [
                'iso_code' => $city->country->isoCode,
                'name' => $city->country->name,
                'geoname_id' => $city->country->geonameId
            ];
            $result['geo']['subdivisions'] = array_map(fn($sub) => [
                'iso_code' => $sub->isoCode,
                'name' => $sub->name,
                'geoname_id' => $sub->geonameId
            ], iterator_to_array($city->subdivisions));
            $result['geo']['location'] = [
                'latitude' => $city->location->latitude,
                'longitude' => $city->location->longitude,
                'accuracy_radius' => $city->location->accuracyRadius
            ];
            $result['geo']['timezone'] = $city->location->timeZone ?? null;
            $result['geo']['postal'] = $city->postal->code ?? null;

            if ($full_mode) {
                $result['geo']['raw'] = [
                    'city' => (array)$city->city,
                    'country' => (array)$city->country,
                    'location' => (array)$city->location,
                    'subdivisions' => array_map(fn($s) => (array)$s, iterator_to_array($city->subdivisions)),
                    'postal' => (array)$city->postal,
                    'traits' => method_exists($city, 'traits') ? (array)$city->traits : null,
                    'registered_country' => method_exists($city, 'registeredCountry') ? (array)$city->registeredCountry : null,
                ];
            }
        } catch (\Throwable $e) {
            $result['geo_error'] = $e->getMessage();
        }

        // -------------------------
        //  ASN + risk_score
        // -------------------------
        $asnNum = null;
        try {
            $asn = $this->asn->asn($ip);
            $asnNum = $asn->autonomousSystemNumber;
            $org = $asn->autonomousSystemOrganization;
            $network = is_object($asn->network) && method_exists($asn->network, 'toString')
                ? $asn->network->toString()
                : (string)$asn->network;

            $result['asn'] = [
                'asn' => $asnNum,
                'org' => $org,
                'ip_network' => $network,
                'network_type' => 'ISP'
            ];

            // ASN risk table
            if ($this->FRMWRK) {
                $rows = $this->FRMWRK->DBRecords(
                    "SELECT type, score FROM asn_risk WHERE asn = ".((int)$asnNum)." LIMIT 1"
                );
                if (!empty($rows)) {
                    $result['asn']['network_type'] = $rows[0]['type'];
                    $risk += (int)$rows[0]['score'];
                    $confidence += 0.5;
                }
            }

            // Heuristic for VPN/Cloud hosting
            if (preg_match('/vpn|hosting|cloud|server|vps|proxy/i', $org)) {
                $risk += 20;
                $confidence += 0.2;
                $signals[] = 'ASN:heuristic';
            }

            if ($full_mode) $result['asn_raw'] = (array)$asn;
        } catch (\Throwable $e) {
            $result['asn_error'] = $e->getMessage();
        }

        // -------------------------
        //  PTR / Reverse DNS
        // -------------------------
        try {
            $ptr = @gethostbyaddr($ip);
            $ptr_lc = strtolower($ptr ?? '');
            $result['ptr'] = $ptr;

            // Random-looking PTRs often TOR/VPN/CDN
            if ($ptr && preg_match('/^[a-f0-9]{16,}$/', $ptr_lc)) {
                $risk += 30;
                $confidence += 0.3;
                $signals[] = 'PTR:hash';
            }
            elseif ($ptr && preg_match('/vpn|proxy|tor|exit node|anonymizer|hide ip|expressvpn|nordvpn|surfshark|private internet access|pia|cyberghost|windscribe|protonvpn/i', $ptr_lc)) {
                $risk += 70;
                $confidence += 0.3;
                $signals[] = 'PTR:VPN';
            }
            elseif ($ptr && preg_match('/hosting|data center|colo|colocation|rackspace|hetzner|digitalocean|linode|vultr|ovh|kimsufi|scaleway|hosteurope|interserver|hostgator|bluehost|godaddy|1&1|dreamhost|siteground|hostinger|liquidweb|inmotion|a2hosting|greencloud|uk2/i', $ptr_lc)) {
                $risk += 60;
                $confidence += 0.3;
                $signals[] = 'PTR:HOSTING';
            }
            elseif ($ptr && preg_match('/tor|onion/i', $ptr_lc)) {
                $risk += 40;
                $confidence += 0.3;
                $signals[] = 'PTR:TOR';
            }
            elseif ($ptr && preg_match('/cloud|cdn|akamai|fastly|cloudflare|cloudfront|stackpath|limelight|keycdn|incapsula|maxcdn|vercel|netlify|aws|amazon|gcp|google|microsoft|azure|oracle cloud|tencent cloud|alibaba cloud|huawei cloud|digitalocean|vultr|linode|hetzner|scaleway|rackspace|upcloud|joyent|joyent cloud|dreamhost cloud|fly.io/i', $ptr_lc)) {
                $risk += 25;
                $confidence += 0.25;
                $signals[] = 'PTR:DC';
            }
            elseif ($ptr && preg_match('/telecom|isp|internet|mobile|vodafone|verizon|att|t-mobile|sprint|comcast|cox|orange|telefonica|deutsche telekom|bell canada|rogers|telus|sprint|sprint spectrum|spectrum|wind mobile|ee|dynamic|pppoe|dsl|broadband|fiber|home/i', $ptr_lc)) {
                $risk -= 5;
                $signals[] = 'PTR:ISP';
            }
            else {
                $risk -= 5;
            }
        } catch (\Throwable $e) {
            $result['ptr_error'] = $e->getMessage();
        }        
		
		// -------------------------
		// -------------- IP2Proxy
        // -------------------------
		
        $ip2proxyData = $this->checkIP2Proxy($ip);
        if (!empty($ip2proxyData)) {
            $result['ip2proxy'] = $ip2proxyData;
            $behavior['ip2proxy'] = true;

            // risk scoring
            $type = strtoupper($ip2proxyData['proxy_type'] ?? '');
            if (in_array($type,['VPN','TOR','PROXY','DCH','RDC'])) { $risk+=30; $confidence+=0.5; $signals[]='IP2Proxy:'.$type; }
        }

        // -------------------------
        //  Behavior Engine (UA, Accounts, Country/ASN Switch)
        // -------------------------
		// -------------------------
		//  Hybrid Behavior Engine
		// -------------------------
		if ($this->FRMWRK && $ua !== null) {

			$ua_hash = hash('sha256', $ua);
			$country = $result['geo']['country']['iso_code'] ?? '';
			$user_key = $account_id ?? session_id();
			$ipEsc = $this->FRMWRK->DB()->real_escape_string($ip);
			$userEsc = $this->FRMWRK->DB()->real_escape_string($user_key);

			// Логируем активность
			$activityLat = null;
			$activityLon = null;
			if (isset($result['geo']['location']['latitude']) && is_numeric($result['geo']['location']['latitude'])) {
				$activityLat = (float)$result['geo']['location']['latitude'];
			}
			if (isset($result['geo']['location']['longitude']) && is_numeric($result['geo']['location']['longitude'])) {
				$activityLon = (float)$result['geo']['location']['longitude'];
			}
			$activityLatSql = $activityLat !== null ? sprintf('%.7F', $activityLat) : 'NULL';
			$activityLonSql = $activityLon !== null ? sprintf('%.7F', $activityLon) : 'NULL';
			$activityInsertWithGeo = "
				INSERT INTO ip_activity (ip, asn, ua_hash, event, account_id, country, lat, lon, created_at)
				VALUES ('{$ipEsc}', ".($asnNum ?? 'NULL').", '{$ua_hash}', 'lookup', '{$userEsc}', '{$country}', {$activityLatSql}, {$activityLonSql}, NOW())
			";
			if (!$this->FRMWRK->DB()->query($activityInsertWithGeo)) {
				$this->FRMWRK->DB()->query("
					INSERT INTO ip_activity (ip, asn, ua_hash, event, account_id, country, created_at)
					VALUES ('{$ipEsc}', ".($asnNum ?? 'NULL').", '{$ua_hash}', 'lookup', '{$userEsc}', '{$country}', NOW())
				");
			}

			// ---------------- ACCOUNT LEVEL (24h)
			$acc = $this->FRMWRK->DBRecords("
				SELECT 
					COUNT(DISTINCT country) as countries,
					COUNT(DISTINCT asn) as asns
				FROM ip_activity
				WHERE account_id = '{$userEsc}'
				  AND created_at > NOW() - INTERVAL 24 HOUR
			");

			if (!empty($acc)) {
				if ($acc[0]['countries'] > 1) {
					$risk += 60;
					$confidence += 0.4;
					$signals[] = 'ACCOUNT:country_switch';
					$behavior['acc_country_switch'] = $acc[0]['countries'];
				}

				if ($acc[0]['asns'] > 1) {
					$risk += 25;
					$confidence += 0.3;
					$signals[] = 'ACCOUNT:asn_switch';
					$behavior['acc_asn_switch'] = $acc[0]['asns'];
				}
			}

			// ---------------- DEVICE LEVEL (24h)
			$dev = $this->FRMWRK->DBRecords("
				SELECT 
					COUNT(DISTINCT country) as countries,
					COUNT(DISTINCT asn) as asns
				FROM ip_activity
				WHERE ua_hash = '{$ua_hash}'
				  AND created_at > NOW() - INTERVAL 24 HOUR
			");

			if (!empty($dev)) {
				if ($dev[0]['countries'] > 1) {
					$risk += 35;
					$confidence += 0.2;
					$signals[] = 'DEVICE:country_switch';
					$behavior['dev_country_switch'] = $dev[0]['countries'];
				}

				if ($dev[0]['asns'] > 1) {
					$risk += 25;
					$confidence += 0.2;
					$signals[] = 'DEVICE:asn_switch';
					$behavior['dev_asn_switch'] = $dev[0]['asns'];
				}
			}

			// ---------------- IP LEVEL (6h)
			$ipStats = $this->FRMWRK->DBRecords("
				SELECT 
					COUNT(DISTINCT country) as countries,
					COUNT(DISTINCT asn) as asns
				FROM ip_activity
				WHERE ip = '{$ipEsc}'
				  AND created_at > NOW() - INTERVAL 6 HOUR
			");

			if (!empty($ipStats)) {
				if ($ipStats[0]['countries'] > 2) {
					$risk += 15;
					$confidence += 0.1;
					$signals[] = 'IP:country_switch';
					$behavior['ip_country_switch'] = $ipStats[0]['countries'];
				}

				if ($ipStats[0]['asns'] > 2) {
					$risk += 10;
					$confidence += 0.1;
					$signals[] = 'IP:asn_switch';
					$behavior['ip_asn_switch'] = $ipStats[0]['asns'];
				}
			}
		}



        // -------------------------
        //  Headers / Fingerprint rotation
        // -------------------------
        if ($this->FRMWRK && $ua !== null) {
            $headers = [
                'ua' => $ua,
                'accept' => $_SERVER['HTTP_ACCEPT'] ?? '',
                'accept_lang' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
                'accept_enc' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
                'connection' => $_SERVER['HTTP_CONNECTION'] ?? '',
                'dnt' => $_SERVER['HTTP_DNT'] ?? '',
                'sec_ch_ua' => $_SERVER['HTTP_SEC_CH_UA'] ?? '',
            ];

            $headers_hash = hash('sha256', json_encode($headers));

            $this->FRMWRK->DB()->query("
                INSERT INTO ip_headers (ip, ua_hash, headers_hash, headers_json)
                VALUES ('{$ip}', '".hash('sha256',$ua)."', '{$headers_hash}', '".$this->FRMWRK->DB()->real_escape_string(json_encode($headers))."')
            ");

            $rows = $this->FRMWRK->DBRecords("
                SELECT COUNT(DISTINCT headers_hash) as variants
                FROM ip_headers
                WHERE ip='{$ip}' AND created_at > NOW() - INTERVAL 1 HOUR
            ");

            if (!empty($rows) && $rows[0]['variants'] >= 3) {
                $risk += 30;
                $confidence += 0.3;
                $signals[] = 'HEADERS:ROTATION';
            }
        }

        // -------------------------
        //  TOR Exit Node check
        // -------------------------
        if ($this->isTorExitNode($ip)) {
            $risk += 50;
            $confidence += 0.5;
            $signals[] = 'TOR:exit_node';
            $behavior['tor_node'] = true;
        }

        // -------------------------
        //  finalize risk_score
        // -------------------------
        $risk = min(100, $risk);
        $result['antifraud']['risk_score'] = $risk;
        $result['antifraud']['proxy_suspected'] = $risk >= 60;
        $result['antifraud']['confidence'] = round(min(1, $confidence), 2);
        $result['antifraud']['signals'] = $signals;
        $result['antifraud']['behavior'] = $behavior;

        return $result;
    }
}
