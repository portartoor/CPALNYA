<?php

use GeoIp2\Database\Reader;


function analytics_is_obvious_scan(string $path): ?string
{
    $patterns = [
        '#/wp-admin#i',
        '#/wordpress#i',
        '#/xmlrpc\.php#i',
        '#/phpmyadmin#i',
        '#/pma#i',
        '#/adminer#i',
        '#/\.env#i',
        '#/vendor/#i',
        '#/\.git#i',
        '#/cgi-bin#i',
        '#/\.well-known#i'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $path)) {
            return 'scan_pattern';
        }
    }

    return null;
}

function analytics_register_invalid_route(mysqli $DB, string $ip): void
{
    analytics_ensure_schema($DB);
    $ipSafe = mysqli_real_escape_string($DB, $ip);

    mysqli_query($DB, "
        INSERT INTO analytics_suspect_ip (ip, invalid_hits, first_seen, last_seen)
        VALUES ('{$ipSafe}', 1, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            invalid_hits = invalid_hits + 1,
            last_seen = NOW()
    ");
}

function analytics_mark_ip_as_threat(mysqli $DB, string $ip, string $reason = 'threat_rule'): void
{
    $ip = trim($ip);
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return;
    }
    analytics_ensure_schema($DB);

    $ipSafe = mysqli_real_escape_string($DB, $ip);
    $reasonSafe = mysqli_real_escape_string($DB, mb_substr($reason, 0, 190));
    mysqli_query(
        $DB,
        "INSERT INTO analytics_suspect_ip (ip, invalid_hits, first_seen, last_seen, is_confirmed_bot, source, reason)
         VALUES ('{$ipSafe}', 1, NOW(), NOW(), 1, 'manual', '{$reasonSafe}')
         ON DUPLICATE KEY UPDATE
            invalid_hits = invalid_hits + 1,
            last_seen = NOW(),
            is_confirmed_bot = 1,
            source = 'manual',
            reason = '{$reasonSafe}'"
    );
}

function analytics_create_ip_threat_rule(mysqli $DB, string $ip, string $title = ''): int
{
    $ip = trim($ip);
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return 0;
    }
    analytics_ensure_schema($DB);

    $ipSafe = mysqli_real_escape_string($DB, $ip);
    $title = trim($title) !== '' ? trim($title) : ('Manual IP threat ' . $ip);
    $titleSafe = mysqli_real_escape_string($DB, mb_substr($title, 0, 190));

    $existing = mysqli_query(
        $DB,
        "SELECT id
         FROM analytics_threat_rules
         WHERE match_type = 'ip_equals'
           AND pattern = '{$ipSafe}'
         LIMIT 1"
    );
    if ($existing && ($row = mysqli_fetch_assoc($existing))) {
        $id = (int)($row['id'] ?? 0);
        if ($id > 0) {
            mysqli_query(
                $DB,
                "UPDATE analytics_threat_rules
                 SET title = '{$titleSafe}',
                     is_active = 1,
                     updated_at = NOW()
                 WHERE id = {$id}
                 LIMIT 1"
            );
            return $id;
        }
    }

    mysqli_query(
        $DB,
        "INSERT INTO analytics_threat_rules
            (title, match_type, pattern, notes, is_active, match_count, created_at, updated_at)
         VALUES
            ('{$titleSafe}', 'ip_equals', '{$ipSafe}', 'Created from admin visits list', 1, 0, NOW(), NOW())"
    );
    return (int)mysqli_insert_id($DB);
}

function analytics_threat_rule_matches(array $rule, array $payload): bool
{
    $matchType = strtolower(trim((string)($rule['match_type'] ?? '')));
    $pattern = trim((string)($rule['pattern'] ?? ''));
    if ($matchType === '' || $pattern === '') {
        return false;
    }

    $path = strtolower((string)($payload['path'] ?? ''));
    $query = strtolower((string)($payload['query_string'] ?? ''));
    $ua = strtolower((string)($payload['user_agent'] ?? ''));
    $ip = trim((string)($payload['ip'] ?? ''));
    $needle = strtolower($pattern);

    switch ($matchType) {
        case 'path_contains':
            return str_contains($path, $needle);
        case 'query_contains':
            return str_contains($query, $needle);
        case 'ua_contains':
            return str_contains($ua, $needle);
        case 'ip_equals':
            return $ip !== '' && strcasecmp($ip, $pattern) === 0;
        case 'regex_path':
            return @preg_match($pattern, (string)($payload['path'] ?? '')) === 1;
        case 'regex_query':
            return @preg_match($pattern, (string)($payload['query_string'] ?? '')) === 1;
        case 'regex_path_or_query':
            return @preg_match($pattern, (string)($payload['path'] ?? '')) === 1
                || @preg_match($pattern, (string)($payload['query_string'] ?? '')) === 1;
        case 'path_or_query_contains':
        default:
            return str_contains($path, $needle) || str_contains($query, $needle);
    }
}

function analytics_match_threat_rule(mysqli $DB, array $payload): ?array
{
    analytics_ensure_schema($DB);
    $res = mysqli_query(
        $DB,
        "SELECT id, title, match_type, pattern
         FROM analytics_threat_rules
         WHERE is_active = 1
         ORDER BY id ASC"
    );
    if (!$res) {
        return null;
    }
    while ($row = mysqli_fetch_assoc($res)) {
        if (analytics_threat_rule_matches((array)$row, $payload)) {
            return $row;
        }
    }
    return null;
}

function analytics_register_threat_rule_match(mysqli $DB, int $ruleId): void
{
    if ($ruleId <= 0) {
        return;
    }
    mysqli_query(
        $DB,
        "UPDATE analytics_threat_rules
         SET match_count = match_count + 1,
             last_matched_at = NOW(),
             updated_at = NOW()
         WHERE id = {$ruleId}
         LIMIT 1"
    );
}

function analytics_is_trusted_bot(string $ip, string $ua): bool
{
    if (preg_match('/googlebot|bingbot|yandexbot|duckduckbot/i', $ua)) {
        return true;
    }

    // Google IP range пример (по желанию)
    if (preg_match('#^66\.249\.#', $ip)) {
        return true;
    }

    return false;
}


function analytics_log_file(string $message): void
{
    $baseDir = defined('DIR') ? rtrim((string)DIR, '/\\') : dirname(__DIR__, 2);
    $logDir = $baseDir . '/cache';
    $logFile = $logDir . '/analytics.log';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

function analytics_real_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        $value = trim((string)($_SERVER[$key] ?? ''));
        if ($value === '') {
            continue;
        }
        if ($key === 'HTTP_X_FORWARDED_FOR') {
            $parts = array_map('trim', explode(',', $value));
            $value = (string)($parts[0] ?? '');
        }
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }
    }
    return '0.0.0.0';
}

function analytics_geo_user_identifier(array $context, string $ip = '', string $ua = ''): string
{
    $explicit = trim((string)($context['geo_user_id'] ?? ''));
    if ($explicit !== '') {
        return mb_substr($explicit, 0, 128);
    }

    $numericUserId = isset($context['user_id']) ? (int)$context['user_id'] : 0;
    if ($numericUserId > 0) {
        return 'usr_' . $numericUserId;
    }

    foreach (['user_id', 'gid_uid', 'tools_uid'] as $cookieKey) {
        $cookieValue = trim((string)($_COOKIE[$cookieKey] ?? ''));
        if ($cookieValue !== '') {
            return mb_substr($cookieValue, 0, 128);
        }
    }

    $generated = '';
    try {
        $generated = 'anon_' . bin2hex(random_bytes(12));
    } catch (\Throwable $e) {
        $seed = $ip . '|' . $ua . '|' . microtime(true);
        $generated = 'anon_' . substr(sha1($seed), 0, 24);
    }
    $generated = mb_substr($generated, 0, 128);

    if (!headers_sent()) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('user_id', $generated, [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['user_id'] = $generated;
    }

    return $generated;
}

function analytics_device_type(string $ua): array
{
    $uaLc = strtolower($ua);
    $isBot = (bool)preg_match('/bot|spider|crawler|curl|python|wget|scrapy|httpclient|headless|phantom|monitor|uptime|preview/i', $uaLc);
    if ($isBot) {
        return ['device' => 'bot', 'is_bot' => 1];
    }
    if (preg_match('/ipad|tablet|kindle|playbook|silk|sm-t/i', $uaLc)) {
        return ['device' => 'tablet', 'is_bot' => 0];
    }
    if (preg_match('/mobi|iphone|android|phone|blackberry|opera mini|windows phone/i', $uaLc)) {
        return ['device' => 'mobile', 'is_bot' => 0];
    }
    return ['device' => 'desktop', 'is_bot' => 0];
}

function analytics_referrer_meta(string $referrer): array
{
    if ($referrer === '') {
        return ['host' => '', 'source_type' => 'direct'];
    }
    $host = strtolower((string)parse_url($referrer, PHP_URL_HOST));
    if ($host === '') {
        return ['host' => '', 'source_type' => 'unknown'];
    }
    if (preg_match('/google\.|bing\.|yahoo\.|duckduckgo\.|yandex\.|baidu\./i', $host)) {
        return ['host' => $host, 'source_type' => 'search'];
    }
    if (preg_match('/facebook\.|instagram\.|t\.co|twitter\.|x\.com|linkedin\.|youtube\.|tiktok\.|reddit\.|vk\.|telegram\./i', $host)) {
        return ['host' => $host, 'source_type' => 'social'];
    }
    return ['host' => $host, 'source_type' => 'referral'];
}

function analytics_referrer_search_query(string $referrer): string
{
    $referrer = trim($referrer);
    if ($referrer === '') {
        return '';
    }

    $host = strtolower((string)parse_url($referrer, PHP_URL_HOST));
    if ($host === '') {
        return '';
    }

    if (!preg_match('/google\.|bing\.|yahoo\.|duckduckgo\.|yandex\.|baidu\./i', $host)) {
        return '';
    }

    $queryString = (string)parse_url($referrer, PHP_URL_QUERY);
    if ($queryString === '') {
        return '';
    }

    $params = [];
    parse_str($queryString, $params);
    foreach (['q', 'p', 'query', 'text', 'wd', 'term'] as $key) {
        $value = trim((string)($params[$key] ?? ''));
        if ($value !== '') {
            return mb_substr($value, 0, 190);
        }
    }
    return '';
}

function analytics_query_source_param(array $queryParams): string
{
    foreach (['referal', 'referral', 'ref', 'ref_source'] as $key) {
        $value = trim((string)($queryParams[$key] ?? ''));
        if ($value !== '') {
            return mb_substr($value, 0, 190);
        }
    }
    return '';
}

function analytics_table_exists(mysqli $db, string $table): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

function analytics_table_has_column(mysqli $db, string $table, string $column): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $columnSafe = mysqli_real_escape_string($db, $column);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
           AND COLUMN_NAME = '{$columnSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

function analytics_table_has_index(mysqli $db, string $table, string $index): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $indexSafe = mysqli_real_escape_string($db, $index);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
           AND INDEX_NAME = '{$indexSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

function analytics_schema_exec(mysqli $db, string $sql): void
{
    try {
        mysqli_query($db, $sql);
    } catch (\Throwable $e) {
        // ignore schema race conditions (duplicate column/index) on live traffic
    }
}

function analytics_seed_default_threat_rules(mysqli $db): void
{
    $countRes = mysqli_query($db, "SELECT COUNT(*) AS total FROM analytics_threat_rules");
    $total = 0;
    if ($countRes && ($row = mysqli_fetch_assoc($countRes))) {
        $total = (int)($row['total'] ?? 0);
    }
    if ($total > 0) {
        return;
    }

    $defaults = [
        ['WordPress login/admin', 'path_or_query_contains', 'wp-login', 'Legacy hardcoded signature'],
        ['WordPress admin', 'path_or_query_contains', 'wp-admin', 'Legacy hardcoded signature'],
        ['WordPress xmlrpc', 'path_or_query_contains', 'xmlrpc', 'Legacy hardcoded signature'],
        ['PhpMyAdmin probe', 'path_or_query_contains', 'phpmyadmin', 'Legacy hardcoded signature'],
        ['PMA probe', 'path_or_query_contains', 'pma', 'Legacy hardcoded signature'],
        ['Adminer probe', 'path_or_query_contains', 'adminer', 'Legacy hardcoded signature'],
        ['ENV leakage probe', 'path_or_query_contains', '.env', 'Legacy hardcoded signature'],
        ['PHPUnit probe', 'path_or_query_contains', 'vendor/phpunit', 'Legacy hardcoded signature'],
        ['CGI bin probe', 'path_or_query_contains', 'cgi-bin', 'Legacy hardcoded signature'],
        ['Config probe', 'path_or_query_contains', 'config.php', 'Legacy hardcoded signature'],
        ['Backup dump probe', 'path_or_query_contains', 'dump.sql', 'Legacy hardcoded signature'],
    ];

    foreach ($defaults as $item) {
        $titleSafe = mysqli_real_escape_string($db, (string)$item[0]);
        $typeSafe = mysqli_real_escape_string($db, (string)$item[1]);
        $patternSafe = mysqli_real_escape_string($db, (string)$item[2]);
        $notesSafe = mysqli_real_escape_string($db, (string)$item[3]);
        mysqli_query(
            $db,
            "INSERT INTO analytics_threat_rules
                (title, match_type, pattern, notes, is_active, match_count, created_at, updated_at)
             VALUES
                ('{$titleSafe}', '{$typeSafe}', '{$patternSafe}', '{$notesSafe}', 1, 0, NOW(), NOW())"
        );
    }
}

function analytics_ensure_schema(mysqli $db): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if (analytics_table_exists($db, 'analytics_visits')) {
        if (!analytics_table_has_column($db, 'analytics_visits', 'search_query')) {
            analytics_schema_exec($db, "ALTER TABLE analytics_visits ADD COLUMN search_query VARCHAR(190) NULL AFTER utm_content");
        }
        if (!analytics_table_has_index($db, 'analytics_visits', 'idx_analytics_visits_search_query')) {
            analytics_schema_exec($db, "CREATE INDEX idx_analytics_visits_search_query ON analytics_visits(search_query)");
        }
    }

    analytics_schema_exec(
        $db,
        "CREATE TABLE IF NOT EXISTS analytics_suspect_ip (
            ip VARCHAR(45) NOT NULL,
            invalid_hits INT UNSIGNED NOT NULL DEFAULT 0,
            first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_confirmed_bot TINYINT(1) NOT NULL DEFAULT 0,
            source VARCHAR(32) NULL,
            reason VARCHAR(190) NULL,
            PRIMARY KEY (ip),
            KEY idx_analytics_suspect_is_bot (is_confirmed_bot),
            KEY idx_analytics_suspect_last_seen (last_seen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    if (!analytics_table_has_column($db, 'analytics_suspect_ip', 'source')) {
        analytics_schema_exec($db, "ALTER TABLE analytics_suspect_ip ADD COLUMN source VARCHAR(32) NULL AFTER is_confirmed_bot");
    }
    if (!analytics_table_has_column($db, 'analytics_suspect_ip', 'reason')) {
        analytics_schema_exec($db, "ALTER TABLE analytics_suspect_ip ADD COLUMN reason VARCHAR(190) NULL AFTER source");
    }

    analytics_schema_exec(
        $db,
        "CREATE TABLE IF NOT EXISTS analytics_threat_rules (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(190) NOT NULL,
            match_type VARCHAR(32) NOT NULL,
            pattern VARCHAR(512) NOT NULL,
            notes VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            match_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
            last_matched_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_analytics_threat_rules_active_type (is_active, match_type),
            KEY idx_analytics_threat_rules_pattern (pattern(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    analytics_seed_default_threat_rules($db);

    if (analytics_table_exists($db, 'analytics_lead_events')) {
        $columns = [
            'session_id' => "ALTER TABLE analytics_lead_events ADD COLUMN session_id VARCHAR(128) NULL AFTER user_agent",
            'host' => "ALTER TABLE analytics_lead_events ADD COLUMN host VARCHAR(190) NULL AFTER session_id",
            'path' => "ALTER TABLE analytics_lead_events ADD COLUMN path VARCHAR(512) NULL AFTER host",
            'referrer_host' => "ALTER TABLE analytics_lead_events ADD COLUMN referrer_host VARCHAR(190) NULL AFTER path",
            'source_type' => "ALTER TABLE analytics_lead_events ADD COLUMN source_type VARCHAR(32) NULL AFTER referrer_host",
            'utm_source' => "ALTER TABLE analytics_lead_events ADD COLUMN utm_source VARCHAR(190) NULL AFTER source_type",
            'utm_medium' => "ALTER TABLE analytics_lead_events ADD COLUMN utm_medium VARCHAR(190) NULL AFTER utm_source",
            'utm_campaign' => "ALTER TABLE analytics_lead_events ADD COLUMN utm_campaign VARCHAR(190) NULL AFTER utm_medium",
            'utm_term' => "ALTER TABLE analytics_lead_events ADD COLUMN utm_term VARCHAR(190) NULL AFTER utm_campaign",
            'utm_content' => "ALTER TABLE analytics_lead_events ADD COLUMN utm_content VARCHAR(190) NULL AFTER utm_term",
            'search_query' => "ALTER TABLE analytics_lead_events ADD COLUMN search_query VARCHAR(190) NULL AFTER utm_content",
        ];
        foreach ($columns as $column => $sql) {
            if (!analytics_table_has_column($db, 'analytics_lead_events', $column)) {
                analytics_schema_exec($db, $sql);
            }
        }
        if (!analytics_table_has_index($db, 'analytics_lead_events', 'idx_analytics_leads_source_type')) {
            analytics_schema_exec($db, "CREATE INDEX idx_analytics_leads_source_type ON analytics_lead_events(source_type)");
        }
        if (!analytics_table_has_index($db, 'analytics_lead_events', 'idx_analytics_leads_referrer_host')) {
            analytics_schema_exec($db, "CREATE INDEX idx_analytics_leads_referrer_host ON analytics_lead_events(referrer_host)");
        }
        if (!analytics_table_has_index($db, 'analytics_lead_events', 'idx_analytics_leads_utm_source')) {
            analytics_schema_exec($db, "CREATE INDEX idx_analytics_leads_utm_source ON analytics_lead_events(utm_source)");
        }
        if (!analytics_table_has_index($db, 'analytics_lead_events', 'idx_analytics_leads_utm_campaign')) {
            analytics_schema_exec($db, "CREATE INDEX idx_analytics_leads_utm_campaign ON analytics_lead_events(utm_campaign)");
        }
        if (!analytics_table_has_index($db, 'analytics_lead_events', 'idx_analytics_leads_search_query')) {
            analytics_schema_exec($db, "CREATE INDEX idx_analytics_leads_search_query ON analytics_lead_events(search_query)");
        }
    }
}

function analytics_geo_map_from_payload(array $payload): array
{
    $geo = $payload;
    if (isset($payload['geo']) && is_array($payload['geo'])) {
        $geo = $payload['geo'];
    }

    $countryIso2 = (string)(
        $payload['country_iso2']
        ?? $payload['country_code']
        ?? $payload['countryCode']
        ?? $geo['country_iso2']
        ?? $geo['country_code']
        ?? $geo['countryCode']
        ?? (($geo['country']['iso2'] ?? $geo['country']['iso_code'] ?? $geo['country']['code'] ?? ''))
    );
    $countryNameRaw = $payload['country_name']
        ?? $payload['countryName']
        ?? $geo['country_name']
        ?? $geo['countryName']
        ?? ($geo['country']['name'] ?? null)
        ?? $payload['country']
        ?? $geo['country']
        ?? '';
    $countryName = is_array($countryNameRaw) ? (string)($countryNameRaw['name'] ?? '') : (string)$countryNameRaw;

    $cityRaw = $payload['city_name']
        ?? $payload['city']
        ?? $payload['cityName']
        ?? $geo['city_name']
        ?? $geo['city']
        ?? $geo['cityName']
        ?? ($geo['city']['name'] ?? '');
    $cityName = is_array($cityRaw) ? (string)($cityRaw['name'] ?? '') : (string)$cityRaw;

    $timezone = (string)(
        $payload['timezone']
        ?? $payload['time_zone']
        ?? $payload['tz']
        ?? $geo['timezone']
        ?? $geo['time_zone']
        ?? $geo['tz']
        ?? ($geo['location']['timezone'] ?? $geo['location']['time_zone'] ?? '')
    );
    $latitude = $payload['latitude']
        ?? $payload['lat']
        ?? $geo['latitude']
        ?? $geo['lat']
        ?? ($geo['location']['latitude'] ?? $geo['location']['lat'] ?? null);
    $longitude = $payload['longitude']
        ?? $payload['lon']
        ?? $payload['lng']
        ?? $geo['longitude']
        ?? $geo['lon']
        ?? $geo['lng']
        ?? ($geo['location']['longitude'] ?? $geo['location']['lon'] ?? $geo['location']['lng'] ?? null);

    return [
        'country_iso2' => strtoupper(trim($countryIso2)),
        'country_name' => trim($countryName),
        'city_name' => trim($cityName),
        'timezone' => trim($timezone),
        'latitude' => is_numeric($latitude) ? (float)$latitude : null,
        'longitude' => is_numeric($longitude) ? (float)$longitude : null,
    ];
}

function analytics_geo_debug_probe(string $ip, string $ua = '', ?string $userId = null): array
{
    $baseUrl = trim((string)($GLOBALS['GeoIpSpaceApiBaseUrl'] ?? ''));
    $apiKey = trim((string)($GLOBALS['GeoIpSpaceApiKey'] ?? ''));
    $timeout = (int)($GLOBALS['GeoIpSpaceApiTimeout'] ?? 8);

    $result = [
        'ip' => $ip,
        'ua' => $ua,
        'user_id' => $userId,
        'base_url' => $baseUrl,
        'valid_ip' => filter_var($ip, FILTER_VALIDATE_IP) ? true : false,
        'has_api_key' => $apiKey !== '',
        'curl_available' => function_exists('curl_init'),
        'attempts' => [],
        'mapped' => [],
        'success' => false,
    ];

    if ($baseUrl === '' || $apiKey === '' || !filter_var($ip, FILTER_VALIDATE_IP) || !function_exists('curl_init')) {
        return $result;
    }

    $baseUrl = rtrim($baseUrl, '/');
    $endpoint = $baseUrl;

    $payload = [
        'ip' => $ip,
        'user_agent' => (string)$ua,
        'ua' => (string)$ua,
        'userid' => $userId !== null ? (string)$userId : '',
        'user_id' => $userId !== null ? (string)$userId : '',
    ];

    $qs = http_build_query([
        'ip' => $ip,
        'ua' => (string)$ua,
        'userid' => $userId !== null ? (string)$userId : '',
        'user_id' => $userId !== null ? (string)$userId : '',
        'key' => $apiKey,
        'api_key' => $apiKey,
    ]);

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'X-API-Key: ' . $apiKey,
    ];

    $attemptUrls = [
        $endpoint . '?' . $qs,
        $endpoint,
        $baseUrl . '/lookup?' . $qs,
        $baseUrl . '/json/' . rawurlencode($ip) . '?' . $qs,
        $baseUrl . '/' . rawurlencode($ip) . '?' . $qs,
    ];
    $attemptUrls = array_values(array_unique($attemptUrls));

    $mask = static function (string $text, string $key): string {
        if ($key === '') {
            return $text;
        }
        return str_replace($key, '***', $text);
    };

    foreach ($attemptUrls as $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, max(2, $timeout));
        curl_setopt($ch, CURLOPT_TIMEOUT, max(3, $timeout));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $body = (string)curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = (string)curl_error($ch);
        curl_close($ch);

        $json = json_decode($body, true);
        $candidate = [];
        if (is_array($json)) {
            if (isset($json['data']) && is_array($json['data'])) {
                $candidate = $json['data'];
            } elseif (isset($json['result']) && is_array($json['result'])) {
                $candidate = $json['result'];
            } else {
                $candidate = $json;
            }
        }
        $mapped = is_array($candidate) ? analytics_geo_map_from_payload($candidate) : [];

        $attempt = [
            'method' => 'GET',
            'url' => $mask($url, $apiKey),
            'http_code' => $http,
            'curl_error' => $err,
            'body_preview' => $mask(mb_substr($body, 0, 1200), $apiKey),
            'mapped' => $mapped,
        ];
        $result['attempts'][] = $attempt;

        if (($mapped['country_iso2'] ?? '') !== '' || ($mapped['country_name'] ?? '') !== '' || ($mapped['city_name'] ?? '') !== '') {
            $result['success'] = true;
            $result['mapped'] = $mapped;
            return $result;
        }
    }

    return $result;
}

function analytics_geo_lookup_remote(string $ip, string $ua = '', ?string $userId = null): array
{
    static $resolvedEndpointUrl = null;

    $baseUrl = trim((string)($GLOBALS['GeoIpSpaceApiBaseUrl'] ?? ''));
    $apiKey = trim((string)($GLOBALS['GeoIpSpaceApiKey'] ?? ''));
    $timeout = (int)($GLOBALS['GeoIpSpaceApiTimeout'] ?? 8);
    if ($baseUrl === '' || $apiKey === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return [];
    }
    if (!function_exists('curl_init')) {
        analytics_log_file('GeoIP remote lookup skipped: curl extension is not available');
        return [];
    }

    $baseUrl = rtrim($baseUrl, '/');
    $endpoint = $baseUrl;

    $qs = http_build_query([
        'ip' => $ip,
        'ua' => (string)$ua,
        'userid' => $userId !== null ? (string)$userId : '',
        'user_id' => $userId !== null ? (string)$userId : '',
        'key' => $apiKey,
        'api_key' => $apiKey,
    ]);

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'X-API-Key: ' . $apiKey,
    ];

    $attemptUrls = [];
    if (is_string($resolvedEndpointUrl) && $resolvedEndpointUrl !== '') {
        $attemptUrls[] = $resolvedEndpointUrl;
        $attemptUrls[] = $resolvedEndpointUrl . (strpos($resolvedEndpointUrl, '?') === false ? '?' : '&') . $qs;
    }
    $attemptUrls[] = $endpoint . '?' . $qs;
    $attemptUrls[] = $endpoint;
    $attemptUrls[] = $baseUrl . '/lookup?' . $qs;
    $ipSafeForPath = rawurlencode($ip);
    $attemptUrls[] = $baseUrl . '/json/' . $ipSafeForPath . '?' . $qs;
    $attemptUrls[] = $baseUrl . '/' . $ipSafeForPath . '?' . $qs;
    $attemptUrls = array_values(array_unique($attemptUrls));

    foreach ($attemptUrls as $url) {
        $body = '';
        $http = 0;
        $err = '';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, max(2, $timeout));
        curl_setopt($ch, CURLOPT_TIMEOUT, max(3, $timeout));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $body = (string)curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!is_string($body) || $body === '') {
            if ($err !== '') {
                analytics_log_file('GeoIP remote lookup curl error: ' . $err . ' url=' . $url);
            }
            continue;
        }
        $json = json_decode($body, true);
        if (!is_array($json)) {
            analytics_log_file('GeoIP remote lookup invalid JSON: ' . mb_substr($body, 0, 240));
            continue;
        }

        $candidate = [];
        if (isset($json['data']) && is_array($json['data'])) {
            $candidate = $json['data'];
        } elseif (isset($json['result']) && is_array($json['result'])) {
            $candidate = $json['result'];
        } else {
            $candidate = $json;
        }

        $mapped = analytics_geo_map_from_payload($candidate);
        if ($mapped['country_iso2'] !== '' || $mapped['country_name'] !== '' || $mapped['city_name'] !== '') {
            $resolvedEndpointUrl = $url;
            analytics_log_file(
                'GeoIP remote success: ip=' . $ip
                . ' country=' . $mapped['country_iso2']
                . ' city=' . $mapped['city_name']
                . ' url=' . $url
            );
            return $mapped;
        }

        analytics_log_file('GeoIP remote lookup empty payload, http=' . $http . ' url=' . $url . ' body=' . mb_substr($body, 0, 240));
    }

    return [];
}

function analytics_geo_remote_enabled(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $explicit = $GLOBALS['GeoIpSpaceApiFrontendEnabled'] ?? null;
    if ($explicit !== null) {
        return (bool)$explicit;
    }

    return false;
}

function analytics_geo_lookup(string $ip, string $ua = '', ?string $userId = null): array
{
    static $cityReader = null;
    if ($ip === '0.0.0.0' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return [];
    }

    try {
        if ($cityReader === null) {
            $dbCandidates = [];
            $configuredPath = trim((string)($GLOBALS['GeoLiteCityDbPath'] ?? ''));
            if ($configuredPath !== '') {
                $dbCandidates[] = $configuredPath;
            }
            $dbCandidates[] = '/home/portcore/geoip-db/GeoLite2-City.mmdb';
            $dbCandidates[] = '/home/geoip/geoip-db/GeoLite2-City.mmdb';
            if (defined('DIR')) {
                $dbCandidates[] = rtrim((string)DIR, '/\\') . '/geoip-db/GeoLite2-City.mmdb';
            }

            $dbPath = '';
            foreach ($dbCandidates as $candidatePath) {
                if (is_string($candidatePath) && $candidatePath !== '' && is_file($candidatePath)) {
                    $dbPath = $candidatePath;
                    break;
                }
            }
            if ($dbPath === '') {
                analytics_log_file('Geo lookup fallback skipped: GeoLite2-City.mmdb not found');
                $cityReader = false;
            } else {
                $cityReader = new Reader($dbPath);
            }
        }
        if ($cityReader instanceof Reader) {
            $city = $cityReader->city($ip);
            return [
                'country_iso2' => (string)($city->country->isoCode ?? ''),
                'country_name' => (string)($city->country->name ?? ''),
                'city_name' => (string)($city->city->name ?? ''),
                'timezone' => (string)($city->location->timeZone ?? ''),
                'latitude' => isset($city->location->latitude) ? (float)$city->location->latitude : null,
                'longitude' => isset($city->location->longitude) ? (float)$city->location->longitude : null,
            ];
        }
    } catch (\Throwable $e) {
        analytics_log_file('Geo lookup local failed: ' . $e->getMessage());
    }

    if (!analytics_geo_remote_enabled()) {
        return [];
    }

    $remote = analytics_geo_lookup_remote($ip, $ua, $userId);
    if (!empty($remote)) {
        return $remote;
    }

    return [];
}

function analytics_visit_payload(array $context = []): array
{
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string)(parse_url($uri, PHP_URL_PATH) ?? '/');
    $queryString = (string)($_SERVER['QUERY_STRING'] ?? '');
    $referrer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
    $refMeta = analytics_referrer_meta($referrer);
    $ua = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $device = analytics_device_type($ua);
    $ip = analytics_real_ip();
    $userId = isset($context['user_id']) ? (int)$context['user_id'] : null;
    $geoUserId = analytics_geo_user_identifier($context, $ip, $ua);
    $geo = analytics_geo_lookup($ip, $ua, $geoUserId);
    $hostRaw = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    $host = strtolower($hostRaw);
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    $host = trim($host, '.');
    if (isset($context['domain_host']) && is_string($context['domain_host']) && $context['domain_host'] !== '') {
        $host = strtolower(trim($context['domain_host']));
    }
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $sid = session_id();
    $requestId = bin2hex(random_bytes(10));
    $queryParams = $_GET;

    $utmSource = (string)($queryParams['utm_source'] ?? '');
    if ($utmSource === '') {
        $utmSource = analytics_query_source_param($queryParams);
    }
    $utmMedium = (string)($queryParams['utm_medium'] ?? '');
    $utmCampaign = (string)($queryParams['utm_campaign'] ?? '');
    $utmTerm = (string)($queryParams['utm_term'] ?? '');
    $utmContent = (string)($queryParams['utm_content'] ?? '');
    $searchQuery = analytics_referrer_search_query($referrer);

    $headers = [];
    if (function_exists('getallheaders')) {
        $h = getallheaders();
        if (is_array($h)) {
            $headers = $h;
        }
    }

    return [
        'request_id' => $requestId,
        'ip' => $ip,
        'method' => $method,
        'scheme' => $scheme,
        'host' => $host,
        'uri' => $uri,
        'path' => $path,
        'query_string' => $queryString,
        'get_params_json' => json_encode($queryParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'request_headers_json' => json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'referrer' => $referrer,
        'referrer_host' => (string)$refMeta['host'],
        'source_type' => (string)$refMeta['source_type'],
        'user_agent' => $ua,
        'device_type' => (string)$device['device'],
        'is_bot' => (int)$device['is_bot'],
        'accept_language' => (string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
        'country_iso2' => (string)($geo['country_iso2'] ?? ''),
        'country_name' => (string)($geo['country_name'] ?? ''),
        'city_name' => (string)($geo['city_name'] ?? ''),
        'timezone' => (string)($geo['timezone'] ?? ''),
        'latitude' => $geo['latitude'] ?? null,
        'longitude' => $geo['longitude'] ?? null,
        'session_id' => $sid,
        'user_id' => $userId,
        'utm_source' => $utmSource,
        'utm_medium' => $utmMedium,
        'utm_campaign' => $utmCampaign,
        'utm_term' => $utmTerm,
        'utm_content' => $utmContent,
        'search_query' => $searchQuery,
    ];
}

function analytics_should_log_visit(): bool
{
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method !== 'GET') {
        return false;
    }

    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = strtolower((string)(parse_url($requestUri, PHP_URL_PATH) ?? '/'));
    if ($path === '') {
        $path = '/';
    }

    if ($path === '/favicon.ico' || $path === '/robots.txt' || $path === '/sitemap.xml') {
        return false;
    }

    if (preg_match('#^/(template|assets|vendor)/#', $path)) {
        return false;
    }

	// 1️⃣ Отсекаем статические файлы
	if (preg_match('/\.(?:js|mjs|css|map|json|xml|txt|csv|png|jpe?g|gif|webp|svg|ico|avif|woff2?|ttf|eot|otf|pdf|zip|gz|wasm|mp4|webm)$/i', $path)) {
		return false;
	}

	// 2️⃣ Отсекаем чувствительные пути / файлы и конфиги
	$badPatterns = [
		// WP / CMS
		'wp-сontent',
		'wp-login',
		'wp-trackback',
		'wp-admin',
		'wordpress',
		'wp_filemanager.php',
		'xmlrpc',
		'feed',
		'txets.php',
		'archivarix',
		'boaform',
		'developmentserver',
		'actuator',
		'onvif-http',
		'jckeditor',

		// DB tools
		'phpmyadmin',
		'pma',
		'adminer',

		// shells
		'webshell',
		'shell',
		'cmd=',
		'exec=',

		// secrets
		'.env',
		'.aws',
		'.aws/credentials',
		'.npmrc',
		'.pypirc',

		// repos
		'.git',
		'.svn',

		// CI / configs
		'docker-compose.yml',
		'.travis.yml',
		'bitbucket-pipelines.yml',
		'configuration.php',
		'config.php',
		'web.config',
		'logon.aspx',
		'themes.php',
		'init.php',
		'robots.php',
		'Run.php',
		'run.php',
		'revealability.php',

		// misc
		'vendor/phpunit',
		'cgi-bin',
		'backup',
		'dump.sql'
	];

	// создаём регулярку из массива
	$badRegex = implode('|', array_map('preg_quote', $badPatterns, array_fill(0, count($badPatterns), '#')));

	// 2️⃣ Отсекаем по badPatterns
	if (preg_match("#(?:{$badRegex})#i", $path)) {
		return false;
	}

	// 3️⃣ Отсекаем скрытые директории и часто атакуемые папки
	if (preg_match('#/(?:storage|config)(?:/|$)#i', $path)) {
		return false;
	}

    $xrw = strtolower(trim((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')));
    if ($xrw === 'xmlhttprequest') {
        return false;
    }

    $secFetchDest = strtolower(trim((string)($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '')));
    if ($secFetchDest !== '' && $secFetchDest !== 'document' && $secFetchDest !== 'iframe') {
        return false;
    }

    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    if ($accept !== '' && strpos($accept, 'text/html') === false && strpos($accept, 'application/xhtml+xml') === false) {
        return false;
    }

    return true;
}

function detectImmediateBot($path, $queryString = '')
{
    $path = strtolower($path);
    $queryString = strtolower($queryString);

    $badPatterns = [
        'wp-login',
        'wp-admin',
        'wordpress',
        'xmlrpc',
        'phpmyadmin',
        'pma',
        'adminer',
        'webshell',
        'shell',
        'cmd=',
        'exec=',
        '.env',
        'vendor/phpunit',
        'cgi-bin',
        'config.php',
        'backup',
        'dump.sql'
    ];

    foreach ($badPatterns as $pattern) {
        if (str_contains($path, $pattern) || str_contains($queryString, $pattern)) {
            return $pattern;
        }
    }

    return false;
}

function analytics_log_visit($FRMWRK, array $context = []): bool
{
    if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
        return false;
    }

    if (!analytics_should_log_visit()) {
        return false;
    }

    $p = analytics_visit_payload($context);
    $DB = $FRMWRK->DB();
    if (!$DB) {
        return false;
    }
    analytics_ensure_schema($DB);

    // Do not log visits for authenticated adminpanel users.
    $adminpanelToken = trim((string)($_COOKIE['adminpanel_token'] ?? ''));
    if ($adminpanelToken !== '') {
        $tokenSafe = mysqli_real_escape_string($DB, $adminpanelToken);
        $adminRows = mysqli_query(
            $DB,
            "SELECT 1
             FROM adminpanel_users
             WHERE token = '{$tokenSafe}'
               AND token_expires > NOW()
             LIMIT 1"
        );
        if ($adminRows && mysqli_num_rows($adminRows) > 0) {
            return false;
        }
    }
	
    $ip = (string)($p['ip'] ?? '');
    $ua = (string)($p['user_agent'] ?? '');

    if (!analytics_is_trusted_bot($ip, $ua)) {
        $confirmedThreat = false;
        if ($ip !== '') {
            $ipSafe = mysqli_real_escape_string($DB, $ip);
            $ipThreatRows = mysqli_query(
                $DB,
                "SELECT 1
                 FROM analytics_suspect_ip
                 WHERE ip = '{$ipSafe}'
                   AND is_confirmed_bot = 1
                 LIMIT 1"
            );
            $confirmedThreat = (bool)($ipThreatRows && mysqli_num_rows($ipThreatRows) > 0);
        }

        if ($confirmedThreat) {
            $p['is_bot'] = 1;
            $p['is_suspect'] = 1;
            $p['suspect_reason'] = 'threat_ip_confirmed';
        } else {
            $matchedRule = analytics_match_threat_rule($DB, $p);
            if (is_array($matchedRule)) {
                $ruleId = (int)($matchedRule['id'] ?? 0);
                $p['is_bot'] = 1;
                $p['is_suspect'] = 1;
                $p['suspect_reason'] = 'threat_rule:' . $ruleId;
                analytics_register_threat_rule_match($DB, $ruleId);
                analytics_mark_ip_as_threat($DB, $ip, 'threat_rule:' . $ruleId);
            }
        }

        if (!empty($context['route_not_found'])) {
            analytics_register_invalid_route($DB, $ip);
        }
    }
	
    $toNull = static function ($value) use ($DB): string {
        if ($value === null || $value === '') {
            return 'NULL';
        }
        return "'" . mysqli_real_escape_string($DB, (string)$value) . "'";
    };
    $toNumOrNull = static function ($value): string {
        if ($value === null || $value === '') {
            return 'NULL';
        }
        if (!is_numeric($value)) {
            return 'NULL';
        }
        return (string)$value;
    };

    $sql = "
    INSERT INTO analytics_visits (
        request_id, ip, method, scheme, host, uri, path, query_string, get_params_json, request_headers_json,
        referrer, referrer_host, source_type, user_agent, device_type, is_bot, accept_language,
        country_iso2, country_name, city_name, timezone, latitude, longitude, session_id, user_id,
        utm_source, utm_medium, utm_campaign, utm_term, utm_content, search_query, visited_at,
        is_suspect, suspect_reason
    ) VALUES (
        {$toNull($p['request_id'])},
        {$toNull($p['ip'])},
        {$toNull($p['method'])},
        {$toNull($p['scheme'])},
        {$toNull($p['host'])},
        {$toNull($p['uri'])},
        {$toNull($p['path'])},
        {$toNull($p['query_string'])},
        {$toNull($p['get_params_json'])},
        {$toNull($p['request_headers_json'])},
        {$toNull($p['referrer'])},
        {$toNull($p['referrer_host'])},
        {$toNull($p['source_type'])},
        {$toNull($p['user_agent'])},
        {$toNull($p['device_type'])},
        {$toNumOrNull($p['is_bot'] ?? 0)},
        {$toNull($p['accept_language'])},
        {$toNull($p['country_iso2'])},
        {$toNull($p['country_name'])},
        {$toNull($p['city_name'])},
        {$toNull($p['timezone'])},
        {$toNumOrNull($p['latitude'])},
        {$toNumOrNull($p['longitude'])},
        {$toNull($p['session_id'])},
        {$toNumOrNull($p['user_id'])},
        {$toNull($p['utm_source'])},
        {$toNull($p['utm_medium'])},
        {$toNull($p['utm_campaign'])},
        {$toNull($p['utm_term'])},
        {$toNull($p['utm_content'])},
        {$toNull($p['search_query'])},
        NOW(),
        {$toNumOrNull($p['is_suspect'] ?? 0)},
        {$toNull($p['suspect_reason'] ?? null)}
    )
";

    $ok = mysqli_query($DB, $sql);
    if (!$ok) {
        analytics_log_file('Visit insert failed: ' . mysqli_error($DB));
        return false;
    }
    return true;
}

function analytics_log_lead_event($FRMWRK, string $eventType, array $data = []): bool
{
    if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
        return false;
    }

    $DB = $FRMWRK->DB();
    if (!$DB) {
        return false;
    }
    analytics_ensure_schema($DB);

    $ip = analytics_real_ip();
    $ua = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $requestId = bin2hex(random_bytes(8));
    $sessionId = session_id();
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string)(parse_url($uri, PHP_URL_PATH) ?? '/');
    $referrer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
    $refMeta = analytics_referrer_meta($referrer);
    $queryParams = $_GET;
    $hostRaw = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    $host = strtolower($hostRaw);
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    $searchQuery = analytics_referrer_search_query($referrer);

    $toNull = static function ($value) use ($DB): string {
        if ($value === null || $value === '') {
            return 'NULL';
        }
        return "'" . mysqli_real_escape_string($DB, (string)$value) . "'";
    };
    $toNumOrNull = static function ($value): string {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return 'NULL';
        }
        return (string)$value;
    };

    $attribVisit = null;
    $sessionSafe = mysqli_real_escape_string($DB, (string)$sessionId);
    $ipSafe = mysqli_real_escape_string($DB, (string)$ip);
    if ($sessionSafe !== '') {
        $rows = $FRMWRK->DBRecords(
            "SELECT host, path, referrer_host, source_type, utm_source, utm_medium, utm_campaign, utm_term, utm_content, search_query
             FROM analytics_visits
             WHERE session_id='{$sessionSafe}'
             ORDER BY id DESC
             LIMIT 1"
        );
        if (!empty($rows)) {
            $attribVisit = $rows[0];
        }
    }
    if ($attribVisit === null && $ipSafe !== '') {
        $rows = $FRMWRK->DBRecords(
            "SELECT host, path, referrer_host, source_type, utm_source, utm_medium, utm_campaign, utm_term, utm_content, search_query
             FROM analytics_visits
             WHERE ip='{$ipSafe}'
             ORDER BY id DESC
             LIMIT 1"
        );
        if (!empty($rows)) {
            $attribVisit = $rows[0];
        }
    }

    $leadHost = (string)($data['host'] ?? ($attribVisit['host'] ?? $host));
    $leadPath = (string)($data['path'] ?? ($attribVisit['path'] ?? $path));
    $leadReferrerHost = (string)($data['referrer_host'] ?? ($attribVisit['referrer_host'] ?? (string)$refMeta['host']));
    $leadSourceType = (string)($data['source_type'] ?? ($attribVisit['source_type'] ?? (string)$refMeta['source_type']));
    $queryUtmSource = (string)($queryParams['utm_source'] ?? '');
    if ($queryUtmSource === '') {
        $queryUtmSource = analytics_query_source_param($queryParams);
    }
    $leadUtmSource = (string)($data['utm_source'] ?? ($attribVisit['utm_source'] ?? $queryUtmSource));
    $leadUtmMedium = (string)($data['utm_medium'] ?? ($attribVisit['utm_medium'] ?? ($queryParams['utm_medium'] ?? '')));
    $leadUtmCampaign = (string)($data['utm_campaign'] ?? ($attribVisit['utm_campaign'] ?? ($queryParams['utm_campaign'] ?? '')));
    $leadUtmTerm = (string)($data['utm_term'] ?? ($attribVisit['utm_term'] ?? ($queryParams['utm_term'] ?? '')));
    $leadUtmContent = (string)($data['utm_content'] ?? ($attribVisit['utm_content'] ?? ($queryParams['utm_content'] ?? '')));
    $leadSearchQuery = (string)($data['search_query'] ?? ($attribVisit['search_query'] ?? $searchQuery));

    $metaJson = json_encode($data['meta'] ?? $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $sql = "
        INSERT INTO analytics_lead_events (
            request_id, event_type, user_id, email, subscription_id, plan_id,
            amount_usd, currency_code, amount_in_currency, ip, user_agent, session_id, host, path,
            referrer_host, source_type, utm_source, utm_medium, utm_campaign, utm_term, utm_content, search_query,
            meta_json, event_time
        ) VALUES (
            {$toNull($requestId)},
            {$toNull($eventType)},
            {$toNumOrNull($data['user_id'] ?? null)},
            {$toNull($data['email'] ?? null)},
            {$toNumOrNull($data['subscription_id'] ?? null)},
            {$toNumOrNull($data['plan_id'] ?? null)},
            {$toNumOrNull($data['amount_usd'] ?? null)},
            {$toNull($data['currency_code'] ?? null)},
            {$toNumOrNull($data['amount_in_currency'] ?? null)},
            {$toNull($ip)},
            {$toNull($ua)},
            {$toNull($sessionId)},
            {$toNull($leadHost)},
            {$toNull($leadPath)},
            {$toNull($leadReferrerHost)},
            {$toNull($leadSourceType)},
            {$toNull($leadUtmSource)},
            {$toNull($leadUtmMedium)},
            {$toNull($leadUtmCampaign)},
            {$toNull($leadUtmTerm)},
            {$toNull($leadUtmContent)},
            {$toNull($leadSearchQuery)},
            {$toNull($metaJson)},
            NOW()
        )
    ";

    $ok = mysqli_query($DB, $sql);
    if (!$ok) {
        analytics_log_file('Lead insert failed: ' . mysqli_error($DB) . '; event=' . $eventType);
        return false;
    }
    return true;
}
