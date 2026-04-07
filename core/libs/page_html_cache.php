<?php

if (!function_exists('page_html_cache_table_name')) {
    function page_html_cache_table_name(): string
    {
        return 'page_html_cache_settings';
    }
}

if (!function_exists('page_html_cache_table_ensure')) {
    function page_html_cache_table_ensure(mysqli $db): bool
    {
        $table = page_html_cache_table_name();
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            `settings_json` LONGTEXT NOT NULL,
            `updated_by_admin_id` INT UNSIGNED DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('page_html_cache_defaults')) {
    function page_html_cache_defaults(): array
    {
        return [
            'enabled' => false,
            'default_ttl' => 120,
            'excluded_prefixes' => [
                '/adminpanel/',
                '/adminpanel',
                '/api/',
                '/api',
                '/audit/',
                '/audit',
            ],
            'ttl_by_prefix' => [
                '/' => 120,
                '/journal/' => 600,
                '/services/' => 900,
                '/projects/' => 900,
                '/cases/' => 900,
                '/contact/' => 300,
            ],
            'max_file_size' => 2097152,
        ];
    }
}

if (!function_exists('page_html_cache_normalize_prefix')) {
    function page_html_cache_normalize_prefix(string $prefix): string
    {
        $prefix = trim($prefix);
        if ($prefix === '') {
            return '';
        }
        if ($prefix[0] !== '/') {
            $prefix = '/' . $prefix;
        }
        return $prefix;
    }
}

if (!function_exists('page_html_cache_normalize')) {
    function page_html_cache_normalize(array $raw): array
    {
        $defaults = page_html_cache_defaults();
        $settings = array_merge($defaults, $raw);

        $settings['enabled'] = (bool)($settings['enabled'] ?? false);
        $settings['default_ttl'] = max(30, min(86400, (int)($settings['default_ttl'] ?? 120)));
        $settings['max_file_size'] = max(16384, min(15728640, (int)($settings['max_file_size'] ?? 2097152)));

        $excluded = [];
        foreach ((array)($settings['excluded_prefixes'] ?? []) as $prefix) {
            $normalized = page_html_cache_normalize_prefix((string)$prefix);
            if ($normalized === '') {
                continue;
            }
            $excluded[$normalized] = true;
        }
        $settings['excluded_prefixes'] = array_keys($excluded);

        $ttlByPrefix = [];
        foreach ((array)($settings['ttl_by_prefix'] ?? []) as $prefix => $ttl) {
            $normalized = page_html_cache_normalize_prefix((string)$prefix);
            if ($normalized === '') {
                continue;
            }
            $ttlByPrefix[$normalized] = max(30, min(86400, (int)$ttl));
        }
        if (empty($ttlByPrefix)) {
            $ttlByPrefix = $defaults['ttl_by_prefix'];
        }
        $settings['ttl_by_prefix'] = $ttlByPrefix;

        return $settings;
    }
}

if (!function_exists('page_html_cache_get')) {
    function page_html_cache_get(mysqli $db): array
    {
        page_html_cache_table_ensure($db);
        $table = page_html_cache_table_name();
        $res = mysqli_query($db, "SELECT settings_json FROM `{$table}` WHERE id = 1 LIMIT 1");
        if (!$res || mysqli_num_rows($res) === 0) {
            return page_html_cache_defaults();
        }
        $row = mysqli_fetch_assoc($res);
        $json = (string)($row['settings_json'] ?? '');
        if ($json === '') {
            return page_html_cache_defaults();
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return page_html_cache_defaults();
        }
        return page_html_cache_normalize($decoded);
    }
}

if (!function_exists('page_html_cache_save')) {
    function page_html_cache_save(mysqli $db, array $settings, int $adminId = 0): bool
    {
        if (!page_html_cache_table_ensure($db)) {
            return false;
        }
        $table = page_html_cache_table_name();
        $settings = page_html_cache_normalize($settings);
        $json = json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            return false;
        }
        $jsonSafe = mysqli_real_escape_string($db, $json);
        $adminSafe = $adminId > 0 ? (string)$adminId : 'NULL';
        $sql = "INSERT INTO `{$table}` (`id`, `settings_json`, `updated_by_admin_id`, `created_at`, `updated_at`)
                VALUES (1, '{$jsonSafe}', {$adminSafe}, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    `settings_json` = VALUES(`settings_json`),
                    `updated_by_admin_id` = VALUES(`updated_by_admin_id`),
                    `updated_at` = NOW()";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('page_html_cache_dir')) {
    function page_html_cache_dir(): string
    {
        $candidates = [];

        $dirConst = defined('DIR') ? rtrim((string)DIR, '/\\') : '';
        if ($dirConst !== '') {
            $candidates[] = $dirConst . '/cache/page_html';
            $parent = dirname($dirConst);
            if ($parent !== '' && $parent !== '.' && $parent !== $dirConst) {
                $candidates[] = rtrim($parent, '/\\') . '/cache/page_html';
            }
        }

        $docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
        if ($docRoot !== '') {
            $candidates[] = $docRoot . '/cache/page_html';
        }

        foreach ($candidates as $candidate) {
            if (is_dir($candidate) && is_writable($candidate)) {
                return $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        if (!empty($candidates)) {
            return $candidates[0];
        }

        return __DIR__ . '/../../cache/page_html';
    }
}

if (!function_exists('page_html_cache_ensure_dir')) {
    function page_html_cache_ensure_dir(): bool
    {
        $dir = page_html_cache_dir();
        if (is_dir($dir)) {
            return true;
        }
        return @mkdir($dir, 0775, true);
    }
}

if (!function_exists('page_html_cache_request_context')) {
    function page_html_cache_request_context(string $path = ''): array
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host);
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $query = (string)parse_url($requestUri, PHP_URL_QUERY);
        $pathResolved = $path !== '' ? $path : (string)parse_url($requestUri, PHP_URL_PATH);
        if ($pathResolved === '') {
            $pathResolved = '/';
        }
        return [
            'method' => $method,
            'host' => $host,
            'path' => $pathResolved,
            'query' => $query,
            'uri' => $pathResolved . ($query !== '' ? ('?' . $query) : ''),
        ];
    }
}

if (!function_exists('page_html_cache_has_auth_cookie')) {
    function page_html_cache_has_auth_cookie(): bool
    {
        if (empty($_COOKIE) || !is_array($_COOKIE)) {
            return false;
        }
        foreach ($_COOKIE as $name => $_v) {
            $name = strtolower((string)$name);
            if ($name === '') {
                continue;
            }
            if (
                $name === 'admin_token'
                || $name === 'adminpanel_token'
                || strpos($name, 'auth') !== false
                || strpos($name, 'token') !== false
                || strpos($name, 'csrf') !== false
            ) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('page_html_cache_path_matches_prefixes')) {
    function page_html_cache_path_matches_prefixes(string $path, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            $prefix = page_html_cache_normalize_prefix((string)$prefix);
            if ($prefix === '') {
                continue;
            }
            if ($prefix === '/') {
                return true;
            }
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('page_html_cache_is_cacheable')) {
    function page_html_cache_is_cacheable(array $settings, array $ctx): bool
    {
        if (empty($settings['enabled'])) {
            return false;
        }
        if (strtoupper((string)($ctx['method'] ?? 'GET')) !== 'GET') {
            return false;
        }
        $path = (string)($ctx['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }
        $excluded = (array)($settings['excluded_prefixes'] ?? []);
        if (page_html_cache_path_matches_prefixes($path, $excluded)) {
            return false;
        }
        $query = (string)($ctx['query'] ?? '');
        if ($query !== '') {
            parse_str($query, $queryParams);
            if (!empty($queryParams['contact_result'])) {
                return false;
            }
        }
        if (page_html_cache_has_auth_cookie()) {
            return false;
        }
        return true;
    }
}

if (!function_exists('page_html_cache_ttl_for_path')) {
    function page_html_cache_ttl_for_path(array $settings, string $path): int
    {
        $ttl = (int)($settings['default_ttl'] ?? 120);
        $bestLen = -1;
        foreach ((array)($settings['ttl_by_prefix'] ?? []) as $prefix => $candidateTtl) {
            $prefix = page_html_cache_normalize_prefix((string)$prefix);
            if ($prefix === '') {
                continue;
            }
            if (($prefix === '/' || strpos($path, $prefix) === 0) && strlen($prefix) > $bestLen) {
                $ttl = (int)$candidateTtl;
                $bestLen = strlen($prefix);
            }
        }
        return max(30, min(86400, $ttl));
    }
}

if (!function_exists('page_html_cache_key')) {
    function page_html_cache_key(array $ctx): string
    {
        $host = strtolower((string)($ctx['host'] ?? ''));
        $uri = (string)($ctx['uri'] ?? '/');
        return sha1($host . '|' . $uri);
    }
}

if (!function_exists('page_html_cache_file_paths')) {
    function page_html_cache_file_paths(string $key): array
    {
        $dir = page_html_cache_dir();
        return [
            'html' => $dir . '/' . $key . '.html',
            'meta' => $dir . '/' . $key . '.json',
        ];
    }
}

if (!function_exists('page_html_cache_send_headers')) {
    function page_html_cache_send_headers(int $ttl, string $status): void
    {
        if (headers_sent()) {
            return;
        }
        $ttl = max(0, $ttl);
        header('Cache-Control: public, max-age=' . $ttl);
        header('Vary: Accept-Encoding');
        header('X-Page-Cache: ' . $status);
    }
}

if (!function_exists('page_html_cache_try_serve')) {
    function page_html_cache_try_serve(array $settings, array $ctx): bool
    {
        if (!page_html_cache_is_cacheable($settings, $ctx)) {
            return false;
        }
        if (!page_html_cache_ensure_dir()) {
            return false;
        }
        $key = page_html_cache_key($ctx);
        $paths = page_html_cache_file_paths($key);
        if (!is_file($paths['html']) || !is_file($paths['meta'])) {
            return false;
        }

        $metaJson = @file_get_contents($paths['meta']);
        if (!is_string($metaJson) || $metaJson === '') {
            return false;
        }
        $meta = json_decode($metaJson, true);
        if (!is_array($meta)) {
            return false;
        }
        $expiresAt = (int)($meta['expires_at'] ?? 0);
        $now = time();
        if ($expiresAt <= $now) {
            @unlink($paths['html']);
            @unlink($paths['meta']);
            return false;
        }

        $html = @file_get_contents($paths['html']);
        if (!is_string($html) || $html === '') {
            return false;
        }

        $ttlLeft = max(1, $expiresAt - $now);
        page_html_cache_send_headers($ttlLeft, 'HIT');
        echo $html;
        return true;
    }
}

if (!function_exists('page_html_cache_store')) {
    function page_html_cache_store(array $settings, array $ctx, string $html): bool
    {
        if (!page_html_cache_is_cacheable($settings, $ctx)) {
            return false;
        }
        if (!page_html_cache_ensure_dir()) {
            return false;
        }
        if ($html === '') {
            return false;
        }
        $statusCodeRaw = http_response_code();
        $statusCode = (int)$statusCodeRaw;
        // Some SAPIs may return 0/false when status wasn't explicitly set.
        if ($statusCode <= 0) {
            $statusCode = 200;
        }
        if ($statusCode !== 200) {
            return false;
        }

        $maxSize = (int)($settings['max_file_size'] ?? 2097152);
        if (strlen($html) > $maxSize) {
            return false;
        }

        $ttl = page_html_cache_ttl_for_path($settings, (string)($ctx['path'] ?? '/'));
        $now = time();
        $expiresAt = $now + $ttl;
        $key = page_html_cache_key($ctx);
        $paths = page_html_cache_file_paths($key);
        $meta = [
            'key' => $key,
            'host' => (string)($ctx['host'] ?? ''),
            'uri' => (string)($ctx['uri'] ?? ''),
            'path' => (string)($ctx['path'] ?? ''),
            'query' => (string)($ctx['query'] ?? ''),
            'created_at' => $now,
            'expires_at' => $expiresAt,
            'ttl' => $ttl,
            'size' => strlen($html),
        ];
        $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($metaJson) || $metaJson === '') {
            return false;
        }

        $okHtml = @file_put_contents($paths['html'], $html, LOCK_EX);
        $okMeta = @file_put_contents($paths['meta'], $metaJson, LOCK_EX);
        if ($okHtml === false || $okMeta === false) {
            @unlink($paths['html']);
            @unlink($paths['meta']);
            return false;
        }
        return true;
    }
}

if (!function_exists('page_html_cache_purge_all')) {
    function page_html_cache_purge_all(): int
    {
        $dir = page_html_cache_dir();
        if (!is_dir($dir)) {
            return 0;
        }
        $deleted = 0;
        $files = glob($dir . '/*.{html,json}', GLOB_BRACE);
        if (!is_array($files)) {
            return 0;
        }
        foreach ($files as $file) {
            if (is_file($file) && @unlink($file)) {
                $deleted++;
            }
        }
        return $deleted;
    }
}

if (!function_exists('page_html_cache_purge_prefix')) {
    function page_html_cache_purge_prefix(string $prefix): int
    {
        $prefix = page_html_cache_normalize_prefix($prefix);
        if ($prefix === '') {
            return 0;
        }
        $dir = page_html_cache_dir();
        if (!is_dir($dir)) {
            return 0;
        }
        $deleted = 0;
        $metaFiles = glob($dir . '/*.json');
        if (!is_array($metaFiles)) {
            return 0;
        }
        foreach ($metaFiles as $metaFile) {
            $metaJson = @file_get_contents($metaFile);
            if (!is_string($metaJson) || $metaJson === '') {
                continue;
            }
            $meta = json_decode($metaJson, true);
            if (!is_array($meta)) {
                continue;
            }
            $path = (string)($meta['path'] ?? '');
            if ($path === '' || strpos($path, $prefix) !== 0) {
                continue;
            }
            $key = (string)($meta['key'] ?? basename($metaFile, '.json'));
            $paths = page_html_cache_file_paths($key);
            if (is_file($paths['meta']) && @unlink($paths['meta'])) {
                $deleted++;
            }
            if (is_file($paths['html']) && @unlink($paths['html'])) {
                $deleted++;
            }
        }
        return $deleted;
    }
}

if (!function_exists('page_html_cache_purge_url')) {
    function page_html_cache_purge_url(string $urlOrPath, ?string $host = null): int
    {
        $urlOrPath = trim($urlOrPath);
        if ($urlOrPath === '') {
            return 0;
        }
        $path = '';
        $query = '';
        $effectiveHost = strtolower((string)($host ?? ($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')));
        if (preg_match('~^https?://~i', $urlOrPath)) {
            $parsed = parse_url($urlOrPath);
            $effectiveHost = strtolower((string)($parsed['host'] ?? $effectiveHost));
            $path = (string)($parsed['path'] ?? '/');
            $query = (string)($parsed['query'] ?? '');
        } else {
            $path = (string)parse_url($urlOrPath, PHP_URL_PATH);
            $query = (string)parse_url($urlOrPath, PHP_URL_QUERY);
        }
        if ($path === '') {
            $path = '/';
        }
        $ctx = [
            'host' => $effectiveHost,
            'path' => $path,
            'query' => $query,
            'uri' => $path . ($query !== '' ? ('?' . $query) : ''),
        ];
        $key = page_html_cache_key($ctx);
        $paths = page_html_cache_file_paths($key);
        $deleted = 0;
        if (is_file($paths['meta']) && @unlink($paths['meta'])) {
            $deleted++;
        }
        if (is_file($paths['html']) && @unlink($paths['html'])) {
            $deleted++;
        }
        return $deleted;
    }
}

if (!function_exists('page_html_cache_stats')) {
    function page_html_cache_stats(): array
    {
        $dir = page_html_cache_dir();
        if (!is_dir($dir)) {
            return ['files' => 0, 'html_files' => 0, 'meta_files' => 0, 'size_bytes' => 0, 'size_human' => '0 B'];
        }
        $files = glob($dir . '/*');
        if (!is_array($files)) {
            return ['files' => 0, 'html_files' => 0, 'meta_files' => 0, 'size_bytes' => 0, 'size_human' => '0 B'];
        }
        $count = 0;
        $htmlCount = 0;
        $metaCount = 0;
        $size = 0;
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $count++;
            $ext = strtolower((string)pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'html') {
                $htmlCount++;
            } elseif ($ext === 'json') {
                $metaCount++;
            }
            $size += (int)@filesize($file);
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $u = 0;
        $sizeF = (float)$size;
        while ($sizeF >= 1024 && $u < count($units) - 1) {
            $sizeF /= 1024;
            $u++;
        }
        return [
            'files' => $count,
            'html_files' => $htmlCount,
            'meta_files' => $metaCount,
            'size_bytes' => $size,
            'size_human' => number_format($sizeF, 2) . ' ' . $units[$u],
        ];
    }
}
