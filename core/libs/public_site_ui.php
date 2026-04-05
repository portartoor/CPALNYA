<?php

if (!function_exists('public_site_href')) {
    function public_site_href(string $path = '/', array $query = []): string
    {
        $path = trim($path);
        if ($path === '') {
            $path = '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        $path = preg_replace('#/+#', '/', $path);

        $cleanQuery = [];
        foreach ($query as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $cleanQuery[(string)$key] = (string)$value;
        }
        if (empty($cleanQuery)) {
            return $path;
        }

        return $path . '?' . http_build_query($cleanQuery);
    }
}

if (!function_exists('public_site_kind')) {
    function public_site_kind(): string
    {
        return 'site';
    }
}

if (!function_exists('public_site_is_ru_host')) {
    function public_site_is_ru_host(): bool
    {
        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        return $host !== '' && preg_match('/\.ru$/', $host) === 1;
    }
}

if (!function_exists('public_site_include_template')) {
    function public_site_include_template(
        string $templatesDir,
        string $baseName,
        array $context = [],
        array $options = []
    ): bool {
        $templatesDir = rtrim($templatesDir, '/\\');
        if ($templatesDir === '') {
            return false;
        }

        $safeBase = preg_replace('/[^a-zA-Z0-9_.-]/', '', $baseName);
        if (!is_string($safeBase) || $safeBase === '') {
            return false;
        }

        $templateKey = strtolower((string)($options['template_key'] ?? ($_SERVER['MIRROR_TEMPLATE_KEY'] ?? '')));
        if ($templateKey === '' && function_exists('public_site_is_ru_host') && public_site_is_ru_host()) {
            $templateKey = 'simple';
        }
        $kind = strtolower((string)($options['kind'] ?? public_site_kind()));

        $sanitize = static function (string $value): string {
            return preg_replace('/[^a-z0-9_-]/', '', strtolower($value));
        };

        $candidates = [];
        $keyCandidate = $sanitize($templateKey);
        if ($keyCandidate !== '') {
            $candidates[] = $safeBase . '.' . $keyCandidate . '.php';
        }

        $kindCandidate = $sanitize($kind);
        if ($kindCandidate !== '') {
            $candidates[] = $safeBase . '.' . $kindCandidate . '.php';
        }

        $fallback = strtolower((string)($options['fallback'] ?? 'default'));
        $fallbackCandidate = $sanitize($fallback);
        if ($fallbackCandidate !== '') {
            $candidates[] = $safeBase . '.' . $fallbackCandidate . '.php';
        }
        $candidates[] = $safeBase . '.php';

        $candidates = array_values(array_unique($candidates));
        foreach ($candidates as $file) {
            $path = $templatesDir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($path)) {
                continue;
            }

            if (!empty($context)) {
                extract($context, EXTR_SKIP);
            }
            include $path;
            return true;
        }

        return false;
    }
}
