<?php

if (!function_exists('indexnow_log')) {
    function indexnow_log(string $message): void
    {
        $baseDir = defined('DIR') ? rtrim((string)DIR, '/\\') : dirname(__DIR__, 2);
        $logDir = $baseDir . '/cache';
        $logFile = $logDir . '/indexnow.log';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}

if (!function_exists('indexnow_clean_host')) {
    function indexnow_clean_host(string $raw): string
    {
        $host = strtolower(trim($raw));
        if ($host === '') {
            return '';
        }
        $host = preg_replace('/^https?:\/\//i', '', $host);
        if (strpos($host, '/') !== false) {
            $host = (string)parse_url('https://' . $host, PHP_URL_HOST);
        }
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        if ($host !== '' && !preg_match('/^[a-z0-9.-]+$/', $host)) {
            return '';
        }
        return trim($host, '.');
    }
}

if (!function_exists('indexnow_is_valid_absolute_url')) {
    function indexnow_is_valid_absolute_url(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || stripos($url, 'https://') !== 0) {
            return false;
        }
        $parts = @parse_url($url);
        if (!is_array($parts)) {
            return false;
        }
        $host = indexnow_clean_host((string)($parts['host'] ?? ''));
        $path = (string)($parts['path'] ?? '/');
        if ($host === '' || $path === '') {
            return false;
        }
        return true;
    }
}

if (!function_exists('indexnow_settings_normalize')) {
    function indexnow_settings_normalize(array $raw): array
    {
        $hosts = [];
        foreach ((array)($raw['hosts'] ?? []) as $h) {
            $clean = indexnow_clean_host((string)$h);
            if ($clean !== '' && !in_array($clean, $hosts, true)) {
                $hosts[] = $clean;
            }
        }
        return [
            'enabled' => !empty($raw['enabled']),
            'key' => trim((string)($raw['key'] ?? '')),
            'key_location' => trim((string)($raw['key_location'] ?? '')),
            'endpoint' => trim((string)($raw['endpoint'] ?? '')),
            'hosts' => $hosts,
            'submit_limit' => max(1, min(500, (int)($raw['submit_limit'] ?? 50))),
            'retry_delay_minutes' => max(1, min(1440, (int)($raw['retry_delay_minutes'] ?? 15))),
            'ping_on_publish' => array_key_exists('ping_on_publish', $raw) ? !empty($raw['ping_on_publish']) : true,
        ];
    }
}

if (!function_exists('indexnow_queue_table_ensure')) {
    function indexnow_queue_table_ensure(mysqli $db): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS indexnow_queue (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            url VARCHAR(1200) NOT NULL,
            url_hash CHAR(64) NOT NULL,
            host VARCHAR(190) NOT NULL,
            lang_code VARCHAR(5) NOT NULL DEFAULT '',
            source VARCHAR(64) NOT NULL DEFAULT 'unknown',
            event_type VARCHAR(32) NOT NULL DEFAULT 'publish',
            status ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
            attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            last_http_status SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            last_error VARCHAR(1000) NOT NULL DEFAULT '',
            next_retry_at DATETIME NULL,
            submitted_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_indexnow_url_hash (url_hash),
            KEY idx_indexnow_status_retry (status, next_retry_at, id),
            KEY idx_indexnow_host_status (host, status),
            KEY idx_indexnow_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('indexnow_queue_enqueue')) {
    function indexnow_queue_enqueue(mysqli $db, string $url, array $meta = []): bool
    {
        $url = trim($url);
        if (!indexnow_is_valid_absolute_url($url)) {
            indexnow_log('enqueue skipped: invalid url "' . $url . '"');
            return false;
        }
        if (!indexnow_queue_table_ensure($db)) {
            indexnow_log('enqueue failed: table ensure error: ' . mysqli_error($db));
            return false;
        }

        $parts = (array)parse_url($url);
        $host = indexnow_clean_host((string)($parts['host'] ?? ''));
        if ($host === '') {
            return false;
        }
        $lang = strtolower(trim((string)($meta['lang_code'] ?? '')));
        if (!in_array($lang, ['en', 'ru'], true)) {
            $lang = '';
        }
        $source = strtolower(trim((string)($meta['source'] ?? 'unknown')));
        if ($source === '') {
            $source = 'unknown';
        }
        $eventType = strtolower(trim((string)($meta['event_type'] ?? 'publish')));
        if ($eventType === '') {
            $eventType = 'publish';
        }

        $urlHash = hash('sha256', $url);
        $urlSafe = mysqli_real_escape_string($db, $url);
        $hashSafe = mysqli_real_escape_string($db, $urlHash);
        $hostSafe = mysqli_real_escape_string($db, $host);
        $langSafe = mysqli_real_escape_string($db, $lang);
        $sourceSafe = mysqli_real_escape_string($db, substr($source, 0, 64));
        $eventSafe = mysqli_real_escape_string($db, substr($eventType, 0, 32));

        $sql = "INSERT INTO indexnow_queue (url, url_hash, host, lang_code, source, event_type, status, attempts, last_http_status, last_error, next_retry_at, submitted_at, created_at, updated_at)
                VALUES ('{$urlSafe}', '{$hashSafe}', '{$hostSafe}', '{$langSafe}', '{$sourceSafe}', '{$eventSafe}', 'pending', 0, 0, '', NULL, NULL, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    url = VALUES(url),
                    host = VALUES(host),
                    lang_code = VALUES(lang_code),
                    source = VALUES(source),
                    event_type = VALUES(event_type),
                    status = 'pending',
                    attempts = 0,
                    last_http_status = 0,
                    last_error = '',
                    next_retry_at = NULL,
                    submitted_at = NULL,
                    updated_at = NOW()";
        $ok = mysqli_query($db, $sql) !== false;
        if (!$ok) {
            indexnow_log('enqueue failed: ' . mysqli_error($db));
            return false;
        }
        return true;
    }
}

if (!function_exists('indexnow_queue_pick_pending')) {
    function indexnow_queue_pick_pending(mysqli $db, int $limit = 50): array
    {
        $limit = max(1, min(500, $limit));
        if (!indexnow_queue_table_ensure($db)) {
            return [];
        }
        $rows = [];
        $sql = "SELECT id, url, host, lang_code, source, event_type, status, attempts
                FROM indexnow_queue
                WHERE status = 'pending'
                   OR (status = 'failed' AND (next_retry_at IS NULL OR next_retry_at <= NOW()))
                ORDER BY id ASC
                LIMIT {$limit}";
        $res = mysqli_query($db, $sql);
        if (!$res) {
            indexnow_log('pick pending failed: ' . mysqli_error($db));
            return [];
        }
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('indexnow_queue_mark_processing')) {
    function indexnow_queue_mark_processing(mysqli $db, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $sql = "UPDATE indexnow_queue
                SET status = 'processing', updated_at = NOW()
                WHERE id = {$id}
                LIMIT 1";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('indexnow_queue_mark_done')) {
    function indexnow_queue_mark_done(mysqli $db, int $id, int $httpStatus = 200): bool
    {
        if ($id <= 0) {
            return false;
        }
        $httpStatus = max(0, min(999, $httpStatus));
        $sql = "UPDATE indexnow_queue
                SET status = 'done',
                    attempts = attempts + 1,
                    last_http_status = {$httpStatus},
                    last_error = '',
                    next_retry_at = NULL,
                    submitted_at = NOW(),
                    updated_at = NOW()
                WHERE id = {$id}
                LIMIT 1";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('indexnow_queue_mark_failed')) {
    function indexnow_queue_mark_failed(mysqli $db, int $id, string $error, int $httpStatus = 0, int $retryDelayMinutes = 15): bool
    {
        if ($id <= 0) {
            return false;
        }
        $httpStatus = max(0, min(999, $httpStatus));
        $retryDelayMinutes = max(1, min(1440, $retryDelayMinutes));
        $errSafe = mysqli_real_escape_string($db, mb_substr(trim($error), 0, 1000));
        $sql = "UPDATE indexnow_queue
                SET status = 'failed',
                    attempts = attempts + 1,
                    last_http_status = {$httpStatus},
                    last_error = '{$errSafe}',
                    next_retry_at = DATE_ADD(NOW(), INTERVAL {$retryDelayMinutes} MINUTE),
                    updated_at = NOW()
                WHERE id = {$id}
                LIMIT 1";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('indexnow_derive_key_location')) {
    function indexnow_derive_key_location(string $host, array $settings): string
    {
        $key = trim((string)($settings['key'] ?? ''));
        $encodedKey = rawurlencode($key);
        $explicit = trim((string)($settings['key_location'] ?? ''));
        if ($explicit !== '') {
            $explicit = str_replace(
                ['{host}', '{key}'],
                [$host, $encodedKey],
                $explicit
            );
            if (stripos($explicit, 'http://') === 0 || stripos($explicit, 'https://') === 0) {
                return $explicit;
            }
            $path = '/' . ltrim($explicit, '/');
            return 'https://' . $host . $path;
        }
        if ($key !== '') {
            return 'https://' . $host . '/' . $encodedKey . '.txt';
        }
        return '';
    }
}

if (!function_exists('indexnow_build_endpoint')) {
    function indexnow_build_endpoint(string $host, array $settings): string
    {
        $endpoint = trim((string)($settings['endpoint'] ?? ''));
        if ($endpoint !== '') {
            if (stripos($endpoint, 'http://') === 0 || stripos($endpoint, 'https://') === 0) {
                return $endpoint;
            }
            return 'https://' . $host . '/' . ltrim($endpoint, '/');
        }
        return 'https://api.indexnow.org/indexnow';
    }
}

if (!function_exists('indexnow_submit_url')) {
    function indexnow_submit_url(string $url, array $settings): array
    {
        $url = trim($url);
        $settings = indexnow_settings_normalize($settings);
        if (!$settings['enabled']) {
            return ['ok' => false, 'http_status' => 0, 'error' => 'disabled'];
        }
        if (!indexnow_is_valid_absolute_url($url)) {
            return ['ok' => false, 'http_status' => 0, 'error' => 'invalid_url'];
        }
        if ($settings['key'] === '') {
            return ['ok' => false, 'http_status' => 0, 'error' => 'missing_key'];
        }

        $host = indexnow_clean_host((string)parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return ['ok' => false, 'http_status' => 0, 'error' => 'invalid_host'];
        }
        if (!empty($settings['hosts']) && !in_array($host, $settings['hosts'], true)) {
            return ['ok' => false, 'http_status' => 0, 'error' => 'host_not_allowed:' . $host];
        }

        $endpoint = indexnow_build_endpoint($host, $settings);
        $payload = [
            'host' => $host,
            'key' => $settings['key'],
            'keyLocation' => indexnow_derive_key_location($host, $settings),
            'urlList' => [$url],
        ];
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            return ['ok' => false, 'http_status' => 0, 'error' => 'json_encode_failed'];
        }

        if (!function_exists('curl_init')) {
            return ['ok' => false, 'http_status' => 0, 'error' => 'curl_missing'];
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $resp = curl_exec($ch);
        $err = (string)curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false || $status < 200 || $status >= 300) {
            return [
                'ok' => false,
                'http_status' => $status,
                'error' => ($err !== '' ? $err : ('http_' . $status)),
                'response' => is_string($resp) ? mb_substr($resp, 0, 2000) : '',
            ];
        }
        return ['ok' => true, 'http_status' => $status, 'response' => is_string($resp) ? mb_substr($resp, 0, 2000) : ''];
    }
}

if (!function_exists('indexnow_worker_run')) {
    function indexnow_worker_run(mysqli $db, array $settings, int $limit = 50, bool $dryRun = false): array
    {
        $settings = indexnow_settings_normalize($settings);
        $limit = max(1, min(500, $limit));
        $summary = [
            'picked' => 0,
            'done' => 0,
            'failed' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun ? 1 : 0,
        ];
        if (!indexnow_queue_table_ensure($db)) {
            $summary['error'] = 'queue_table_ensure_failed';
            return $summary;
        }
        $rows = indexnow_queue_pick_pending($db, $limit);
        $summary['picked'] = count($rows);
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $url = trim((string)($row['url'] ?? ''));
            if ($id <= 0 || $url === '') {
                $summary['skipped']++;
                continue;
            }
            indexnow_queue_mark_processing($db, $id);
            if ($dryRun) {
                indexnow_queue_mark_failed($db, $id, 'dry_run', 0, 1);
                $summary['skipped']++;
                continue;
            }
            $result = indexnow_submit_url($url, $settings);
            if (!empty($result['ok'])) {
                indexnow_queue_mark_done($db, $id, (int)($result['http_status'] ?? 200));
                $summary['done']++;
                indexnow_log('DONE #' . $id . ' ' . $url . ' http=' . (int)($result['http_status'] ?? 0));
                continue;
            }
            $error = (string)($result['error'] ?? 'submit_failed');
            $httpStatus = (int)($result['http_status'] ?? 0);
            indexnow_queue_mark_failed($db, $id, $error, $httpStatus, (int)$settings['retry_delay_minutes']);
            $summary['failed']++;
            indexnow_log('FAIL #' . $id . ' ' . $url . ' http=' . $httpStatus . ' err=' . $error);
        }
        return $summary;
    }
}
