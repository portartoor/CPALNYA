<?php
// Runs every 5 minutes via cron.
// Performs only 2 external API calls per cycle:
// 1) fiat rates from open.er-api.com (USD base)
// 2) crypto prices from CoinGecko (BTC, ETH, BNB, TON)

ini_set('display_errors', '0');
date_default_timezone_set('UTC');

const JOB_NAME = 'update_fx_rates';
const FIAT_PROVIDER = 'open.er-api.com';
const CRYPTO_PROVIDER = 'api.coingecko.com';

function db(): mysqli
{
    $db = new mysqli('localhost', 'geoip', 'rMPZgqCyhOVyfJFKtzR2wFgq5', 'geoip');
    if ($db->connect_errno) {
        throw new RuntimeException('DB connection failed: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
    return $db;
}

function fetchJson(string $url, int $timeoutSec = 12): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => $timeoutSec,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: geoip.space-cron/1.0'
        ],
    ]);

    $body = curl_exec($ch);
    $err = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $err !== '') {
        throw new RuntimeException('HTTP transport error: ' . $err);
    }
    if ($http < 200 || $http >= 300) {
        throw new RuntimeException('HTTP status ' . $http . ' for ' . $url);
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid JSON from ' . $url);
    }

    return $decoded;
}

function parseFiatRates(array $payload): array
{
    if (($payload['result'] ?? '') !== 'success' || !isset($payload['rates']) || !is_array($payload['rates'])) {
        throw new RuntimeException('Invalid fiat payload structure');
    }

    $providerTs = isset($payload['time_last_update_unix']) ? (int)$payload['time_last_update_unix'] : 0;
    $providerUpdatedAt = $providerTs > 0 ? gmdate('Y-m-d H:i:s', $providerTs) : null;
    $rates = $payload['rates'];

    if (!isset($rates['USD'])) {
        $rates['USD'] = 1.0;
    }

    $result = [];
    foreach ($rates as $code => $unitsPerUsdRaw) {
        $currencyCode = strtoupper((string)$code);
        if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            continue;
        }

        $unitsPerUsd = (float)$unitsPerUsdRaw;
        if ($unitsPerUsd <= 0) {
            continue;
        }

        $usdPerUnit = 1 / $unitsPerUsd;
        $result[] = [
            'currency_code' => $currencyCode,
            'units_per_usd' => $unitsPerUsd,
            'usd_per_unit' => $usdPerUnit,
            'provider_updated_at' => $providerUpdatedAt
        ];
    }

    if (empty($result)) {
        throw new RuntimeException('No fiat rates parsed');
    }

    return $result;
}

function parseCryptoRates(array $payload): array
{
    $map = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'BNB' => 'binancecoin',
        'TON' => 'the-open-network',
    ];

    $result = [];
    foreach ($map as $symbol => $id) {
        $row = $payload[$id] ?? null;
        if (!is_array($row)) {
            throw new RuntimeException('Missing crypto row for ' . $id);
        }

        $usdPerCoin = isset($row['usd']) ? (float)$row['usd'] : 0.0;
        if ($usdPerCoin <= 0) {
            throw new RuntimeException('Invalid usd price for ' . $id);
        }

        $coinPerUsd = 1 / $usdPerCoin;
        $providerUpdatedAt = null;
        if (isset($row['last_updated_at']) && is_numeric($row['last_updated_at'])) {
            $providerUpdatedAt = gmdate('Y-m-d H:i:s', (int)$row['last_updated_at']);
        }

        $result[] = [
            'crypto_symbol' => $symbol,
            'coingecko_id' => $id,
            'usd_per_coin' => $usdPerCoin,
            'coin_per_usd' => $coinPerUsd,
            'provider_updated_at' => $providerUpdatedAt
        ];
    }

    return $result;
}

function upsertFiatRates(mysqli $db, array $rows): int
{
    $now = gmdate('Y-m-d H:i:s');
    $sql = "
        INSERT INTO fx_rates_fiat_usd
        (currency_code, units_per_usd, usd_per_unit, provider, provider_updated_at, fetched_at)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            units_per_usd = VALUES(units_per_usd),
            usd_per_unit = VALUES(usd_per_unit),
            provider = VALUES(provider),
            provider_updated_at = VALUES(provider_updated_at),
            fetched_at = VALUES(fetched_at)
    ";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare fiat upsert failed: ' . $db->error);
    }

    $count = 0;
    foreach ($rows as $row) {
        $currencyCode = (string)$row['currency_code'];
        $unitsPerUsd = (float)$row['units_per_usd'];
        $usdPerUnit = (float)$row['usd_per_unit'];
        $provider = FIAT_PROVIDER;
        $providerUpdatedAt = $row['provider_updated_at']; // nullable string
        $fetchedAt = $now;

        $stmt->bind_param(
            'sddsss',
            $currencyCode,
            $unitsPerUsd,
            $usdPerUnit,
            $provider,
            $providerUpdatedAt,
            $fetchedAt
        );
        if (!$stmt->execute()) {
            throw new RuntimeException('Fiat upsert failed for ' . $currencyCode . ': ' . $stmt->error);
        }
        $count++;
    }

    $stmt->close();
    return $count;
}

function upsertCryptoRates(mysqli $db, array $rows): int
{
    $now = gmdate('Y-m-d H:i:s');
    $sql = "
        INSERT INTO fx_rates_crypto_usd
        (crypto_symbol, coingecko_id, usd_per_coin, coin_per_usd, provider, provider_updated_at, fetched_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            coingecko_id = VALUES(coingecko_id),
            usd_per_coin = VALUES(usd_per_coin),
            coin_per_usd = VALUES(coin_per_usd),
            provider = VALUES(provider),
            provider_updated_at = VALUES(provider_updated_at),
            fetched_at = VALUES(fetched_at)
    ";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare crypto upsert failed: ' . $db->error);
    }

    $count = 0;
    foreach ($rows as $row) {
        $symbol = (string)$row['crypto_symbol'];
        $coingeckoId = (string)$row['coingecko_id'];
        $usdPerCoin = (float)$row['usd_per_coin'];
        $coinPerUsd = (float)$row['coin_per_usd'];
        $provider = CRYPTO_PROVIDER;
        $providerUpdatedAt = $row['provider_updated_at']; // nullable string
        $fetchedAt = $now;

        $stmt->bind_param(
            'ssddsss',
            $symbol,
            $coingeckoId,
            $usdPerCoin,
            $coinPerUsd,
            $provider,
            $providerUpdatedAt,
            $fetchedAt
        );
        if (!$stmt->execute()) {
            throw new RuntimeException('Crypto upsert failed for ' . $symbol . ': ' . $stmt->error);
        }
        $count++;
    }

    $stmt->close();
    return $count;
}

function writeCronLog(mysqli $db, string $status, string $message, int $fiatRows, int $cryptoRows): void
{
    $sql = "INSERT INTO fx_rates_cron_log (job_name, status, message, fiat_rows, crypto_rows) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return;
    }
    $job = JOB_NAME;
    $stmt->bind_param('sssii', $job, $status, $message, $fiatRows, $cryptoRows);
    $stmt->execute();
    $stmt->close();
}

$fiatRows = 0;
$cryptoRows = 0;
$db = null;

try {
    $db = db();

    // External call #1: all fiat rates with USD base.
    $fiatPayload = fetchJson('https://open.er-api.com/v6/latest/USD');
    $fiatRates = parseFiatRates($fiatPayload);

    // External call #2: selected crypto prices in USD.
    $cryptoPayload = fetchJson(
        'https://api.coingecko.com/api/v3/simple/price'
        . '?ids=bitcoin,ethereum,binancecoin,the-open-network'
        . '&vs_currencies=usd'
        . '&include_last_updated_at=true'
    );
    $cryptoRates = parseCryptoRates($cryptoPayload);

    $db->begin_transaction();
    $fiatRows = upsertFiatRates($db, $fiatRates);
    $cryptoRows = upsertCryptoRates($db, $cryptoRates);
    $db->commit();

    $okMessage = "FX updated: fiat={$fiatRows}, crypto={$cryptoRows}";
    writeCronLog($db, 'ok', $okMessage, $fiatRows, $cryptoRows);
    echo $okMessage . PHP_EOL;
} catch (Throwable $e) {
    if ($db instanceof mysqli) {
        try {
            $db->rollback();
        } catch (Throwable $ignored) {
        }
        writeCronLog($db, 'error', $e->getMessage(), $fiatRows, $cryptoRows);
    }
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
