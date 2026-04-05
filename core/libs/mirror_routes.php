<?php

if (!function_exists('mirror_routes_slug')) {
    function mirror_routes_slug(string $raw): string
    {
        $raw = strtolower(trim($raw));
        $raw = preg_replace('/[^a-z0-9_-]/', '', $raw);
        return substr($raw, 0, 64);
    }
}

if (!function_exists('mirror_routes_path_segments')) {
    function mirror_routes_path_segments(string $path): array
    {
        $path = trim($path);
        if ($path === '') {
            return [];
        }
        $path = parse_url($path, PHP_URL_PATH);
        if (!is_string($path)) {
            return [];
        }
        $parts = explode('/', trim($path, '/'));
        $parts = array_values(array_filter($parts, static function ($v) {
            return $v !== null && $v !== '';
        }));
        return array_map('mirror_routes_slug', $parts);
    }
}

if (!function_exists('mirror_routes_ensure_schema')) {
    function mirror_routes_ensure_schema($FRMWRK): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return;
        }
        $DB = $FRMWRK->DB();
        if (!$DB) {
            return;
        }

        mysqli_query($DB, "
            CREATE TABLE IF NOT EXISTS mirror_routes (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                route_type ENUM('page','section_page') NOT NULL DEFAULT 'page',
                route_name VARCHAR(64) NOT NULL,
                page_name VARCHAR(64) NOT NULL DEFAULT '',
                display_name VARCHAR(190) NOT NULL DEFAULT '',
                view_name VARCHAR(64) NOT NULL DEFAULT 'main',
                sort_order INT NOT NULL DEFAULT 100,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                seo_title VARCHAR(255) NOT NULL DEFAULT '',
                seo_description TEXT NULL,
                seo_keywords VARCHAR(255) NOT NULL DEFAULT '',
                og_title VARCHAR(255) NOT NULL DEFAULT '',
                og_description TEXT NULL,
                og_image VARCHAR(255) NOT NULL DEFAULT '',
                seo_noindex TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_route (route_type, route_name, page_name),
                KEY idx_route_name (route_name),
                KEY idx_is_active (is_active),
                KEY idx_sort_order (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}

if (!function_exists('mirror_routes_reserved_roots')) {
    function mirror_routes_reserved_roots(): array
    {
        return ['adminpanel', 'dashboard', 'api', 'debug', 'pmyad', 'config'];
    }
}

if (!function_exists('mirror_routes_humanize')) {
    function mirror_routes_humanize(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return ucwords($value);
    }
}

if (!function_exists('mirror_routes_current_site_name')) {
    function mirror_routes_current_site_name(): string
    {
        $site = mirror_routes_slug((string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple'));
        return $site !== '' ? $site : 'simple';
    }
}

if (!function_exists('mirror_routes_build_model_page')) {
    function mirror_routes_build_model_page(array $row, string $requestPath): array
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        $host = trim($host, '.');
        if ($host === '') {
            $host = 'localhost';
        }

        $routeName = (string)($row['route_name'] ?? '');
        $pageName = (string)($row['page_name'] ?? '');
        $displayName = trim((string)($row['display_name'] ?? ''));
        if ($displayName === '') {
            $displayName = mirror_routes_humanize($pageName !== '' ? ($routeName . ' ' . $pageName) : $routeName);
        }
        if ($displayName === '') {
            $displayName = 'Page';
        }

        $isRu = (bool)preg_match('/\.ru$/', $host);

        $title = trim((string)($row['seo_title'] ?? ''));
        if ($title === '') {
            $title = $isRu
                ? ($displayName . ' — B2B-решения для роста продаж')
                : ($displayName . ' — B2B solutions for measurable growth');
        }

        $description = trim((string)($row['seo_description'] ?? ''));
        if ($description === '') {
            $description = $isRu
                ? ('Раздел "' . $displayName . '" с B2B-фокусом: структура, контент и сценарии, ориентированные на заявки и выручку.')
                : ('"' . $displayName . '" section with a B2B focus: structure, content and conversion-oriented scenarios for revenue growth.');
        }

        $canonicalPath = $requestPath !== '' ? $requestPath : '/';
        if ($canonicalPath[0] !== '/') {
            $canonicalPath = '/' . $canonicalPath;
        }
        if ($canonicalPath !== '/' && substr($canonicalPath, -1) !== '/') {
            $canonicalPath .= '/';
        }

        $robots = ((int)($row['seo_noindex'] ?? 0) === 1) ? 'noindex,nofollow' : 'index,follow';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => trim((string)($row['seo_keywords'] ?? '')),
            'canonical' => $scheme . '://' . $host . $canonicalPath,
            'robots' => $robots,
            'og_type' => 'website',
            'og_title' => trim((string)($row['og_title'] ?? '')) !== '' ? trim((string)($row['og_title'] ?? '')) : $title,
            'og_description' => trim((string)($row['og_description'] ?? '')) !== '' ? trim((string)($row['og_description'] ?? '')) : $description,
            'og_image' => trim((string)($row['og_image'] ?? '')),
            'og_site_name' => $host,
            'twitter_card' => 'summary',
            'twitter_site' => '',
            'twitter_creator' => '',
        ];
    }
}

if (!function_exists('mirror_routes_resolve')) {
    function mirror_routes_resolve($FRMWRK, string $requestPath): ?array
    {
        if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return null;
        }
        mirror_routes_ensure_schema($FRMWRK);
        $DB = $FRMWRK->DB();
        if (!$DB) {
            return null;
        }

        $segments = mirror_routes_path_segments($requestPath);
        $count = count($segments);
        if ($count < 1 || $count > 2) {
            return null;
        }
        if (in_array($segments[0], mirror_routes_reserved_roots(), true)) {
            return null;
        }

        $rows = [];
        if ($count === 1) {
            $route = mysqli_real_escape_string($DB, (string)$segments[0]);
            $rows = $FRMWRK->DBRecords(
                "SELECT *
                 FROM mirror_routes
                 WHERE is_active=1
                   AND route_name='{$route}'
                   AND (
                       (route_type='page' AND page_name='')
                       OR
                       (route_type='section_page' AND page_name='main')
                   )
                 ORDER BY CASE WHEN route_type='page' THEN 0 ELSE 1 END, sort_order ASC, id ASC
                 LIMIT 1"
            );
        } elseif ($count === 2) {
            $route = mysqli_real_escape_string($DB, (string)$segments[0]);
            $page = mysqli_real_escape_string($DB, (string)$segments[1]);
            $rows = $FRMWRK->DBRecords(
                "SELECT *
                 FROM mirror_routes
                 WHERE is_active=1
                   AND route_type='section_page'
                   AND route_name='{$route}'
                   AND page_name='{$page}'
                 ORDER BY sort_order ASC, id ASC
                 LIMIT 1"
            );
        }

        if (empty($rows)) {
            return null;
        }

        $row = $rows[0];
        $type = (string)($row['route_type'] ?? 'page');
        $routeName = mirror_routes_slug((string)($row['route_name'] ?? ''));
        $pageName = mirror_routes_slug((string)($row['page_name'] ?? ''));
        $viewName = mirror_routes_slug((string)($row['view_name'] ?? 'main'));
        if ($viewName === '') {
            $viewName = 'main';
        }
        $siteName = mirror_routes_current_site_name();

        $resolved = [
            'id' => (int)($row['id'] ?? 0),
            'type' => $type,
            'route_name' => $routeName,
            'page_name' => $pageName,
            'view_name' => $viewName,
            'site_name' => $siteName,
            'model_page' => mirror_routes_build_model_page($row, $requestPath),
        ];

        if ($type === 'section_page') {
            if ($routeName === '' || $pageName === '') {
                return null;
            }
            $resolved['paths'] = [
                'model_dir' => $routeName . '/',
                'control_dir' => $routeName . '/',
                'view_dir' => $routeName . '/',
                'file' => $pageName . '.php',
                'template_file' => DIR . '/core/views/' . $routeName . '/templates/' . $pageName . '/' . $siteName . '/' . $viewName . '.' . $routeName . '.php',
            ];
            return $resolved;
        }

        if ($routeName === '') {
            return null;
        }
        $resolved['paths'] = [
            'model_dir' => '',
            'control_dir' => '',
            'view_dir' => '',
            'file' => $routeName . '.php',
            'template_file' => DIR . '/core/views/templates/' . $routeName . '/' . $siteName . '/' . $viewName . '.' . $routeName . '.php',
        ];
        return $resolved;
    }
}

if (!function_exists('mirror_routes_nav_items')) {
    function mirror_routes_nav_items($FRMWRK, bool $includeHome = true): array
    {
        $items = [];
        if ($includeHome) {
            $items[] = ['title' => 'Home', 'path' => '/'];
        }

        if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return $items;
        }

        mirror_routes_ensure_schema($FRMWRK);
        $rows = $FRMWRK->DBRecords(
            "SELECT route_type, route_name, page_name, display_name, sort_order, id
             FROM mirror_routes
             WHERE is_active=1
             ORDER BY sort_order ASC, route_name ASC, page_name ASC, id ASC"
        );
        if (empty($rows)) {
            return $items;
        }

        $seen = [];
        foreach ($items as $baseItem) {
            $seen[(string)($baseItem['path'] ?? '')] = true;
        }

        foreach ($rows as $row) {
            $type = (string)($row['route_type'] ?? 'page');
            $routeName = mirror_routes_slug((string)($row['route_name'] ?? ''));
            $pageName = mirror_routes_slug((string)($row['page_name'] ?? ''));
            if ($routeName === '') {
                continue;
            }

            $path = '/' . $routeName . '/';
            if ($type === 'section_page' && $pageName !== '' && $pageName !== 'main') {
                $path = '/' . $routeName . '/' . $pageName . '/';
            }
            if (isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;

            $displayName = trim((string)($row['display_name'] ?? ''));
            if ($displayName === '') {
                if ($type === 'section_page' && $pageName !== '' && $pageName !== 'main') {
                    $displayName = mirror_routes_humanize($routeName . ' ' . $pageName);
                } else {
                    $displayName = mirror_routes_humanize($routeName);
                }
            }
            if ($displayName === '') {
                $displayName = $path;
            }

            $items[] = [
                'title' => $displayName,
                'path' => $path,
            ];
        }

        return $items;
    }
}

if (!function_exists('mirror_routes_scaffold')) {
    function mirror_routes_scaffold(array $row, array $siteNames = ['simple', 'enterprise']): array
    {
        $type = (string)($row['route_type'] ?? 'page');
        $routeName = mirror_routes_slug((string)($row['route_name'] ?? ''));
        $pageName = mirror_routes_slug((string)($row['page_name'] ?? ''));
        $viewName = mirror_routes_slug((string)($row['view_name'] ?? 'main'));
        if ($viewName === '') {
            $viewName = 'main';
        }

        $siteNames = array_values(array_unique(array_filter(array_map('mirror_routes_slug', $siteNames))));
        if (empty($siteNames)) {
            $siteNames = ['simple', 'enterprise'];
        }

        $created = [];
        $existing = [];
        $errors = [];

        $writeFileIfMissing = static function (string $path, string $content) use (&$created, &$existing, &$errors): void {
            if (is_file($path)) {
                $existing[] = $path;
                return;
            }
            $dir = dirname($path);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                $errors[] = 'Cannot create directory: ' . $dir;
                return;
            }
            $ok = @file_put_contents($path, $content);
            if ($ok === false) {
                $errors[] = 'Cannot write file: ' . $path;
                return;
            }
            $created[] = $path;
        };

        if ($type === 'section_page') {
            if ($routeName === '' || $pageName === '') {
                return ['created' => [], 'existing' => [], 'errors' => ['Invalid section route data.']];
            }

            $controlPath = DIR . '/core/controls/' . $routeName . '/' . $pageName . '.php';
            $modelPath = DIR . '/core/models/' . $routeName . '/' . $pageName . '.php';
            $viewPath = DIR . '/core/views/' . $routeName . '/' . $pageName . '.php';

            $writeFileIfMissing($controlPath, "<?php\n// Control logic for /{$routeName}/{$pageName}/\n");
            $writeFileIfMissing($modelPath, "<?php\nif (!isset(\$ModelPage) || !is_array(\$ModelPage)) {\n    \$ModelPage = [];\n}\n\$meta = \$_SERVER['MIRROR_ROUTE_MODEL_PAGE'] ?? null;\nif (is_array(\$meta)) {\n    \$ModelPage = array_merge(\$meta, \$ModelPage);\n}\n");

            $viewContent = "<?php\n\$__mirrorSite = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string)(\$_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple')));\n\$__templateFile = __DIR__ . '/templates/{$pageName}/' . \$__mirrorSite . '/{$viewName}.{$routeName}.php';\nif (is_file(\$__templateFile)) {\n    include \$__templateFile;\n    return;\n}\n?>\n<section class=\"container py-5\">\n    <h1>" . htmlspecialchars(mirror_routes_humanize($routeName . ' ' . $pageName), ENT_QUOTES, 'UTF-8') . "</h1>\n    <p>Template file not found: <code><?= htmlspecialchars(\$__templateFile, ENT_QUOTES, 'UTF-8') ?></code></p>\n</section>\n";
            $writeFileIfMissing($viewPath, $viewContent);

            foreach ($siteNames as $siteName) {
                $templatePath = DIR . '/core/views/' . $routeName . '/templates/' . $pageName . '/' . $siteName . '/' . $viewName . '.' . $routeName . '.php';
                $templateContent = "<section class=\"container py-5\">\n    <h1>" . htmlspecialchars(mirror_routes_humanize($routeName . ' ' . $pageName), ENT_QUOTES, 'UTF-8') . "</h1>\n    <p>Site template: <strong>" . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "</strong>.</p>\n</section>\n";
                $writeFileIfMissing($templatePath, $templateContent);
            }

            return ['created' => $created, 'existing' => $existing, 'errors' => $errors];
        }

        if ($routeName === '') {
            return ['created' => [], 'existing' => [], 'errors' => ['Invalid page route data.']];
        }

        $controlPath = DIR . '/core/controls/' . $routeName . '.php';
        $modelPath = DIR . '/core/models/' . $routeName . '.php';
        $viewPath = DIR . '/core/views/' . $routeName . '.php';

        $writeFileIfMissing($controlPath, "<?php\n// Control logic for /{$routeName}/\n");
        $writeFileIfMissing($modelPath, "<?php\nif (!isset(\$ModelPage) || !is_array(\$ModelPage)) {\n    \$ModelPage = [];\n}\n\$meta = \$_SERVER['MIRROR_ROUTE_MODEL_PAGE'] ?? null;\nif (is_array(\$meta)) {\n    \$ModelPage = array_merge(\$meta, \$ModelPage);\n}\n");

        $viewContent = "<?php\n\$__mirrorSite = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string)(\$_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple')));\n\$__templateFile = __DIR__ . '/templates/{$routeName}/' . \$__mirrorSite . '/{$viewName}.{$routeName}.php';\nif (is_file(\$__templateFile)) {\n    include \$__templateFile;\n    return;\n}\n?>\n<section class=\"container py-5\">\n    <h1>" . htmlspecialchars(mirror_routes_humanize($routeName), ENT_QUOTES, 'UTF-8') . "</h1>\n    <p>Template file not found: <code><?= htmlspecialchars(\$__templateFile, ENT_QUOTES, 'UTF-8') ?></code></p>\n</section>\n";
        $writeFileIfMissing($viewPath, $viewContent);

        foreach ($siteNames as $siteName) {
            $templatePath = DIR . '/core/views/templates/' . $routeName . '/' . $siteName . '/' . $viewName . '.' . $routeName . '.php';
            $templateContent = "<section class=\"container py-5\">\n    <h1>" . htmlspecialchars(mirror_routes_humanize($routeName), ENT_QUOTES, 'UTF-8') . "</h1>\n    <p>Site template: <strong>" . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "</strong>.</p>\n</section>\n";
            $writeFileIfMissing($templatePath, $templateContent);
        }

        return ['created' => $created, 'existing' => $existing, 'errors' => $errors];
    }
}
