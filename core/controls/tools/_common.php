<?php

if (!function_exists('tools_table_exists')) {
    function tools_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'public_tools'
             LIMIT 1"
        );
        if (!$res) {
            return false;
        }
        return mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('tools_ensure_schema')) {
    function tools_ensure_schema(mysqli $db): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS public_tools (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            domain_host VARCHAR(255) NOT NULL DEFAULT '',
            lang_code VARCHAR(5) NOT NULL DEFAULT 'en',
            slug VARCHAR(180) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description_text TEXT NOT NULL,
            icon_emoji VARCHAR(24) NOT NULL DEFAULT 'IP',
            page_heading VARCHAR(255) NOT NULL DEFAULT '',
            page_subheading TEXT NULL,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            seo_title VARCHAR(255) NULL,
            seo_description TEXT NULL,
            seo_keywords VARCHAR(255) NULL,
            og_title VARCHAR(255) NULL,
            og_description TEXT NULL,
            og_image VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uniq_public_tools_domain_slug_lang (domain_host, slug, lang_code),
            KEY idx_public_tools_published (is_published, sort_order, id),
            KEY idx_public_tools_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!mysqli_query($db, $sql)) {
            return false;
        }

        return tools_seed_default_tools($db);
    }
}

if (!function_exists('tools_seed_default_tools')) {
    function tools_seed_default_tools(mysqli $db): bool
    {
        $defaults = [
            [
                'slug' => 'ip-lookup',
                'name' => 'IP Lookup',
                'description' => 'Find geolocation, network and risk signals for IPv4/IPv6 in one compact view.',
                'icon' => 'IP',
                'heading' => 'IP Lookup Tool',
                'subheading' => 'Inspect geolocation, ASN context, proxy hints and antifraud signals for any IP address.',
                'sort' => 1000,
                'seo_title' => 'IP Lookup Tool | GeoIP.space',
                'seo_description' => 'Lookup geolocation, ASN and risk signals for any IP address in a clean interface.',
                'seo_keywords' => 'ip lookup, geoip lookup, asn lookup, ip intelligence',
            ],
            [
                'slug' => 'asn-lookup',
                'name' => 'ASN Lookup',
                'description' => 'Resolve ASN number and organization for any IPv4/IPv6 address.',
                'icon' => 'AS',
                'heading' => 'ASN Lookup Tool',
                'subheading' => 'Check autonomous system number, network owner and related context.',
                'sort' => 990,
                'seo_title' => 'ASN Lookup Tool | GeoIP.space',
                'seo_description' => 'Lookup ASN number and ISP organization by IP address.',
                'seo_keywords' => 'asn lookup, as number lookup, isp asn checker',
            ],
            [
                'slug' => 'proxy-vpn-tor-check',
                'name' => 'Proxy VPN TOR Check',
                'description' => 'Quickly check if an IP looks like proxy/VPN/TOR traffic.',
                'icon' => 'PX',
                'heading' => 'Proxy VPN TOR Check',
                'subheading' => 'Fast verification for suspicious network routing and anonymization.',
                'sort' => 980,
                'seo_title' => 'Proxy VPN TOR Check | GeoIP.space',
                'seo_description' => 'Check proxy, VPN and TOR risk indicators for any IP.',
                'seo_keywords' => 'proxy checker, vpn detection, tor ip check',
            ],
            [
                'slug' => 'ip-risk-explainer',
                'name' => 'IP Risk Explainer',
                'description' => 'Convert risk score and signals into a clear readable decision.',
                'icon' => 'RS',
                'heading' => 'IP Risk Explainer',
                'subheading' => 'Understand why an IP is risky and what to do next.',
                'sort' => 970,
                'seo_title' => 'IP Risk Score Explainer | GeoIP.space',
                'seo_description' => 'Explain IP risk score with confidence and antifraud signals.',
                'seo_keywords' => 'ip risk score, fraud score ip, ip risk analysis',
            ],
            [
                'slug' => 'ip-compare',
                'name' => 'IP Compare',
                'description' => 'Compare two IPs by country, ASN, timezone and risk side-by-side.',
                'icon' => 'CMP',
                'heading' => 'IP Compare Tool',
                'subheading' => 'Compare two addresses to quickly spot suspicious differences.',
                'sort' => 960,
                'seo_title' => 'Compare IP Addresses | GeoIP.space',
                'seo_description' => 'Compare two IP addresses by geolocation and risk signals.',
                'seo_keywords' => 'compare ip addresses, ip difference checker',
            ],
            [
                'slug' => 'timezone-by-ip',
                'name' => 'Timezone by IP',
                'description' => 'Get timezone and local offset for a target IP address.',
                'icon' => 'TZ',
                'heading' => 'Timezone by IP',
                'subheading' => 'Resolve current timezone context to improve delivery timing and checks.',
                'sort' => 950,
                'seo_title' => 'Timezone by IP | GeoIP.space',
                'seo_description' => 'Lookup timezone and local time context from IP address.',
                'seo_keywords' => 'timezone by ip, local time by ip',
            ],
            [
                'slug' => 'country-enrichment-viewer',
                'name' => 'Country Enrichment Viewer',
                'description' => 'Show country metadata: currency, calling code, TLD and flags by IP.',
                'icon' => 'CTY',
                'heading' => 'Country Enrichment Viewer',
                'subheading' => 'Inspect country-level metadata used in checkout and localization.',
                'sort' => 940,
                'seo_title' => 'Country Enrichment by IP | GeoIP.space',
                'seo_description' => 'View country code, currency, calling code and TLD by IP.',
                'seo_keywords' => 'country code by ip, currency by ip, tld by country',
            ],
            [
                'slug' => 'crypto-local-price',
                'name' => 'Crypto Local Price',
                'description' => 'Estimate BTC/ETH/TON/BNB in local fiat context from country data.',
                'icon' => 'CR',
                'heading' => 'Crypto Local Price by IP',
                'subheading' => 'Get crypto price context in local currency from geolocation enrichment.',
                'sort' => 930,
                'seo_title' => 'Crypto Local Price by IP | GeoIP.space',
                'seo_description' => 'See BTC, ETH and TON values in local fiat by country context.',
                'seo_keywords' => 'btc to local currency, crypto local price',
            ],
            [
                'slug' => 'suspicious-login-helper',
                'name' => 'Suspicious Login Helper',
                'description' => 'Generate a simple allow/review/block recommendation for login IP.',
                'icon' => 'LGN',
                'heading' => 'Suspicious Login Helper',
                'subheading' => 'Practical recommendation for authentication risk decisions.',
                'sort' => 920,
                'seo_title' => 'Suspicious Login Check | GeoIP.space',
                'seo_description' => 'Assess if login IP is safe, requires review, or should be blocked.',
                'seo_keywords' => 'suspicious login check, login fraud detection',
            ],
            [
                'slug' => 'ip-batch-mini-checker',
                'name' => 'IP Batch Mini Checker',
                'description' => 'Check up to 20 IPs in one run and get compact risk summary.',
                'icon' => 'BCH',
                'heading' => 'IP Batch Mini Checker',
                'subheading' => 'Daily support and operations utility for quick multi-IP checks.',
                'sort' => 910,
                'seo_title' => 'Bulk IP Checker | GeoIP.space',
                'seo_description' => 'Batch check IP addresses for geolocation and antifraud indicators.',
                'seo_keywords' => 'bulk ip checker, batch ip lookup',
            ],
            [
                'slug' => 'utm-seo-landing-validator',
                'name' => 'UTM SEO Landing Validator',
                'description' => 'Validate UTM tags and basic SEO tags for any landing URL.',
                'icon' => 'SEO',
                'heading' => 'UTM SEO Landing Validator',
                'subheading' => 'Check campaign URL quality and critical meta tags quickly.',
                'sort' => 900,
                'seo_title' => 'UTM and SEO Landing Validator | GeoIP.space',
                'seo_description' => 'Validate UTM query and core SEO tags for marketing landing pages.',
                'seo_keywords' => 'utm checker, seo meta checker, landing validator',
            ],
        ];

        foreach ($defaults as $tool) {
            $slug = mysqli_real_escape_string($db, (string)$tool['slug']);
            $check = mysqli_query(
                $db,
                "SELECT id FROM public_tools
                 WHERE slug = '{$slug}'
                   AND domain_host = ''
                   AND lang_code = 'en'
                 LIMIT 1"
            );
            if (!$check || mysqli_num_rows($check) > 0) {
                continue;
            }

            $name = mysqli_real_escape_string($db, (string)$tool['name']);
            $description = mysqli_real_escape_string($db, (string)$tool['description']);
            $icon = mysqli_real_escape_string($db, (string)$tool['icon']);
            $heading = mysqli_real_escape_string($db, (string)$tool['heading']);
            $subheading = mysqli_real_escape_string($db, (string)$tool['subheading']);
            $sort = (int)($tool['sort'] ?? 0);
            $seoTitle = mysqli_real_escape_string($db, (string)$tool['seo_title']);
            $seoDescription = mysqli_real_escape_string($db, (string)$tool['seo_description']);
            $seoKeywords = mysqli_real_escape_string($db, (string)$tool['seo_keywords']);

            mysqli_query(
                $db,
                "INSERT INTO public_tools
                    (domain_host, lang_code, slug, name, description_text, icon_emoji, page_heading, page_subheading, is_published, sort_order, seo_title, seo_description, seo_keywords, og_title, og_description, created_at, updated_at)
                 VALUES
                    ('', 'en', '{$slug}', '{$name}', '{$description}', '{$icon}', '{$heading}', '{$subheading}', 1, {$sort}, '{$seoTitle}', '{$seoDescription}', '{$seoKeywords}', '{$seoTitle}', '{$seoDescription}', NOW(), NOW())"
            );
        }

        return true;
    }
}

if (!function_exists('tools_current_host')) {
    function tools_current_host(): string
    {
        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        return trim($host);
    }
}

if (!function_exists('tools_normalize_lang')) {
    function tools_normalize_lang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        return in_array($lang, ['en', 'ru'], true) ? $lang : 'en';
    }
}

if (!function_exists('tools_default_lang_for_host')) {
    function tools_default_lang_for_host(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host !== '' && preg_match('/\.ru$/', $host)) {
            return 'ru';
        }
        return 'en';
    }
}

if (!function_exists('tools_route_base')) {
    function tools_route_base(?string $host = null): string
    {
        $resolvedHost = strtolower(trim((string)($host ?? tools_current_host())));
        if (strpos($resolvedHost, ':') !== false) {
            $resolvedHost = explode(':', $resolvedHost, 2)[0];
        }
        if (in_array($resolvedHost, ['apigeoip.online', 'www.apigeoip.online'], true)) {
            return '/use';
        }
        return '/tools';
    }
}

if (!function_exists('tools_resolve_lang')) {
    function tools_resolve_lang(string $host): string
    {
        $fromQuery = (string)($_GET['lang'] ?? '');
        if ($fromQuery !== '') {
            return tools_normalize_lang($fromQuery);
        }
        return tools_default_lang_for_host($host);
    }
}

if (!function_exists('tools_slugify')) {
    function tools_slugify(string $raw): string
    {
        $raw = strtolower(trim($raw));
        $raw = preg_replace('/[^a-z0-9]+/', '-', $raw);
        $raw = trim((string)$raw, '-');
        if ($raw === '') {
            $raw = 'tool-' . date('YmdHis');
        }
        return substr($raw, 0, 180);
    }
}

if (!function_exists('tools_fetch_published_list')) {
    function tools_fetch_published_list($FRMWRK, string $host, int $limit = 100, string $lang = 'en'): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !tools_table_exists($db)) {
            return [];
        }
        tools_ensure_schema($db);

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $lang = tools_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $lang);
        $limit = max(1, min(500, $limit));

        $langCond = $lang === 'ru'
            ? "lang_code IN ('ru', 'en')"
            : "lang_code = 'en'";

        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, slug, name, description_text, icon_emoji, page_heading, page_subheading, is_published, sort_order, seo_title, seo_description, seo_keywords, og_title, og_description, og_image
             FROM public_tools
             WHERE is_published = 1
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               AND {$langCond}
             ORDER BY (lang_code = '{$langSafe}') DESC, sort_order DESC, id DESC
             LIMIT {$limit}"
        );

        if ($lang !== 'ru') {
            return $rows;
        }

        $seen = [];
        $filtered = [];
        foreach ($rows as $row) {
            $slug = (string)($row['slug'] ?? '');
            if ($slug === '' || isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;
            $filtered[] = $row;
        }
        return $filtered;
    }
}

if (!function_exists('tools_fetch_published_by_slug')) {
    function tools_fetch_published_by_slug($FRMWRK, string $host, string $slug, string $lang = 'en'): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !tools_table_exists($db)) {
            return null;
        }
        tools_ensure_schema($db);

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $lang = tools_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $lang);
        $slugSafe = mysqli_real_escape_string($db, tools_slugify($slug));

        $langCond = $lang === 'ru'
            ? "lang_code IN ('ru', 'en')"
            : "lang_code = 'en'";

        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, slug, name, description_text, icon_emoji, page_heading, page_subheading, is_published, sort_order, seo_title, seo_description, seo_keywords, og_title, og_description, og_image
             FROM public_tools
             WHERE is_published = 1
               AND slug = '{$slugSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               AND {$langCond}
             ORDER BY (lang_code = '{$langSafe}') DESC, (domain_host = '{$hostSafe}') DESC, id DESC
             LIMIT 1"
        );

        return !empty($rows) ? $rows[0] : null;
    }
}
