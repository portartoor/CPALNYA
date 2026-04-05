<?php
// get_ip2proxy.php
ini_set('memory_limit', '1024M');
set_time_limit(0);

$DB = new mysqli('localhost', 'geoip', 'rMPZgqCyhOVyfJFKtzR2wFgq5', 'geoip');
if ($DB->connect_errno) {
    die("DB error: " . $DB->connect_error);
}

$csvFile = '/home/geoip/geoip-db/IP2PROXY-LITE-PX9.CSV';
if (!file_exists($csvFile)) {
    die("CSV not found\n");
}

$fp = fopen($csvFile, 'r');
if (!$fp) {
    die("Cannot open CSV\n");
}

$DB->begin_transaction();

$stmt = $DB->prepare("
INSERT INTO ip2proxy_lite
(ip_from, ip_to, proxy_type, country_code, country_name, region, city, isp, domain, usage_type, asn, as_name, last_seen, threat, updated_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
ON DUPLICATE KEY UPDATE
 proxy_type = VALUES(proxy_type),
 country_code = VALUES(country_code),
 country_name = VALUES(country_name),
 region = VALUES(region),
 city = VALUES(city),
 isp = VALUES(isp),
 domain = VALUES(domain),
 usage_type = VALUES(usage_type),
 asn = VALUES(asn),
 as_name = VALUES(as_name),
 last_seen = VALUES(last_seen),
 threat = VALUES(threat),
 updated_at = NOW()
");

$batch = 0;
$total = 0;

while (($row = fgetcsv($fp)) !== false) {

    // защита от битых строк
    if (count($row) < 14) {
        continue;
    }

    [
        $ip_from,
        $ip_to,
        $proxy_type,
        $country_code,
        $country_name,
        $region,
        $city,
        $isp,
        $domain,
        $usage_type,
        $asn,
        $as_name,
        $last_seen,
        $threat
    ] = $row;

    // last_seen
    if ($last_seen === '-' || empty($last_seen)) {
        $last_seen = null;
    } else {
        $ts = strtotime($last_seen);
        $last_seen = $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    $asn = is_numeric($asn) ? (int)$asn : null;

    $stmt->bind_param(
        'iissssssssisss',
        $ip_from,
        $ip_to,
        $proxy_type,
        $country_code,
        $country_name,
        $region,
        $city,
        $isp,
        $domain,
        $usage_type,
        $asn,
        $as_name,
        $last_seen,
        $threat
    );

    $stmt->execute();

    $batch++;
    $total++;

    if ($batch >= 2000) {
        $DB->commit();
        $DB->begin_transaction();
        $batch = 0;
    }
}

$DB->commit();
fclose($fp);

echo "IP2Proxy import done. Rows: {$total}\n";
