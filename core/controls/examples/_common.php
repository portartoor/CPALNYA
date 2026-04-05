<?php

if (!function_exists('examples_table_exists')) {
    function examples_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'examples_articles'
             LIMIT 1"
        );
        if (!$res) {
            return false;
        }
        return mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('examples_current_host')) {
    function examples_current_host(): string
    {
        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        return trim($host);
    }
}

if (!function_exists('examples_normalize_lang')) {
    function examples_normalize_lang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        return in_array($lang, ['en', 'ru'], true) ? $lang : 'en';
    }
}

if (!function_exists('examples_default_lang_for_host')) {
    function examples_default_lang_for_host(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host !== '' && preg_match('/\.ru$/', $host)) {
            return 'ru';
        }
        return 'en';
    }
}

if (!function_exists('examples_route_base')) {
    function examples_route_base(?string $host = null): string
    {
        return '/blog';
    }
}

if (!function_exists('examples_article_detail_base')) {
    function examples_article_detail_base(?string $host = null): string
    {
        return '/blog/';
    }
}

if (!function_exists('examples_is_apigeo_host')) {
    function examples_is_apigeo_host(?string $host = null): bool
    {
        return false;
    }
}

if (!function_exists('examples_cluster_default')) {
    function examples_cluster_default(string $lang = 'en'): string
    {
        return $lang === 'ru' ? 'obshchiy' : 'general';
    }
}

if (!function_exists('examples_cluster_label')) {
    function examples_cluster_label(string $clusterCode, string $lang = 'en'): string
    {
        $code = trim(strtolower($clusterCode));
        if ($code === '') {
            return $lang === 'ru' ? 'Общий' : 'General';
        }
        if ($lang === 'ru') {
            $ruMap = [
                'general' => 'Общий',
                'obshchiy' => 'Общий',
                'b2b' => 'B2B',
                'research' => 'Исследования',
                'dev' => 'Разработка',
                'theory' => 'Теория',
                'security' => 'Безопасность',
                'fraud' => 'Антифрод',
                'compliance' => 'Комплаенс',
                'analytics' => 'Аналитика',
                'geo-intel' => 'Геоаналитика',
                'geo_intel' => 'Геоаналитика',
                'operations' => 'Операции',
                'pricing' => 'Тарификация',
                'case-study' => 'Кейсы',
                'case_study' => 'Кейсы',
                'product' => 'Продукт',
                'fintech' => 'Финтех',
                'marketplace' => 'Маркетплейс',
                'gaming' => 'Гейминг',
                'saas' => 'SaaS',
                'performance' => 'Производительность',
                'integration' => 'Интеграция',
                'strategy' => 'Стратегия',
            ];
            if (isset($ruMap[$code])) {
                return $ruMap[$code];
            }
        }
        $parts = array_filter(explode('-', $code), static function ($item): bool {
            return trim((string)$item) !== '';
        });
        if (empty($parts)) {
            return $lang === 'ru' ? 'Общий' : 'General';
        }
        return ucwords(implode(' ', $parts));
    }
}
if (!function_exists('examples_normalize_cluster')) {
    function examples_normalize_cluster(string $raw, string $lang = 'en'): string
    {
        if (trim($raw) === '') {
            return examples_cluster_default($lang);
        }
        $cluster = examples_slugify($raw);
        if ($cluster === '') {
            return examples_cluster_default($lang);
        }
        return substr($cluster, 0, 64);
    }
}

if (!function_exists('examples_article_url_path')) {
    function examples_article_url_path(string $slug, ?string $clusterCode = null, ?string $host = null): string
    {
        $slugSafe = examples_slugify($slug);
        if ($slugSafe === '') {
            $slugSafe = 'article';
        }
        $clusterSafe = examples_slugify((string)$clusterCode);
        if ($clusterSafe === '') {
            return '/blog/' . rawurlencode($slugSafe) . '/';
        }
        return '/blog/' . rawurlencode($clusterSafe) . '/' . rawurlencode($slugSafe) . '/';
    }
}
if (!function_exists('examples_cluster_list_path')) {
    function examples_cluster_list_path(?string $clusterCode = null, ?string $host = null): string
    {
        $clusterSafe = examples_slugify((string)$clusterCode);
        if ($clusterSafe === '') {
            return '/blog/';
        }
        return '/blog/' . rawurlencode($clusterSafe) . '/';
    }
}
if (!function_exists('examples_resolve_lang')) {
    function examples_resolve_lang(string $host): string
    {
        $defaultLang = examples_default_lang_for_host($host);
        if ($defaultLang === 'ru') {
            return 'ru';
        }
        $fromQuery = (string)($_GET['lang'] ?? '');
        if ($fromQuery !== '') {
            return examples_normalize_lang($fromQuery);
        }
        return $defaultLang;
    }
}

if (!function_exists('examples_table_has_lang_column')) {
    function examples_table_has_lang_column(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'examples_articles'
               AND COLUMN_NAME = 'lang_code'
             LIMIT 1"
        );
        if (!$res) {
            return false;
        }
        return mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('examples_table_has_column')) {
    function examples_table_has_column(mysqli $db, string $column): bool
    {
        $columnSafe = mysqli_real_escape_string($db, $column);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'examples_articles'
               AND COLUMN_NAME = '{$columnSafe}'
             LIMIT 1"
        );
        if (!$res) {
            return false;
        }
        return mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('examples_preview_select_sql')) {
    function examples_preview_select_sql(mysqli $db): string
    {
        $hasPreviewThumbUrl = examples_table_has_column($db, 'preview_image_thumb_url');
        $hasPreviewUrl = examples_table_has_column($db, 'preview_image_url');
        $hasPreviewData = examples_table_has_column($db, 'preview_image_data');
        $hasPreviewStyle = examples_table_has_column($db, 'preview_image_style');

        $parts = [];
        $parts[] = $hasPreviewThumbUrl ? "preview_image_thumb_url" : "'' AS preview_image_thumb_url";
        $parts[] = $hasPreviewUrl ? "preview_image_url" : "'' AS preview_image_url";
        $parts[] = $hasPreviewStyle ? "preview_image_style" : "'' AS preview_image_style";
        $parts[] = $hasPreviewData ? "preview_image_data" : "'' AS preview_image_data";
        return ', ' . implode(', ', $parts);
    }
}

if (!function_exists('examples_slugify')) {
    function examples_slugify(string $raw): string
    {
        $raw = trim($raw);
        $raw = mb_strtolower($raw, 'UTF-8');
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
        ];
        $raw = strtr($raw, $map);
        $raw = preg_replace('/[^a-z0-9]+/', '-', (string)$raw);
        $raw = trim((string)$raw, '-');
        if ($raw === '') {
            $raw = 'article-' . date('YmdHis');
        }
        return substr($raw, 0, 180);
    }
}
if (!function_exists('examples_build_excerpt')) {
    function examples_build_excerpt(string $contentHtml, int $maxLen = 220): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($contentHtml)));
        if (mb_strlen($text) <= $maxLen) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $maxLen - 1)) . '...';
    }
}

if (!function_exists('examples_fetch_published_list')) {
    function examples_fetch_published_list($FRMWRK, string $host, int $limit = 100, string $lang = 'en', string $cluster = ''): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db)) {
            return [];
        }

        $lang = examples_normalize_lang($lang);
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $limit = max(1, min(500, $limit));
        $hasLang = examples_table_has_lang_column($db);
        $hasCluster = examples_table_has_column($db, 'cluster_code');
        $previewSelect = examples_preview_select_sql($db);
        $clusterSelect = $hasCluster ? 'cluster_code' : ("'" . examples_cluster_default($lang) . "' AS cluster_code");
        $cluster = trim($cluster);
        $clusterWhere = '';
        if ($cluster !== '') {
            $clusterSafe = mysqli_real_escape_string($db, examples_normalize_cluster($cluster, $lang));
            $clusterWhere = $hasCluster
                ? " AND cluster_code = '{$clusterSafe}'"
                : '';
        }

        if (!$hasLang) {
            return $FRMWRK->DBRecords(
                "SELECT id, domain_host, title, slug, {$clusterSelect}, excerpt_html, content_html{$previewSelect}, is_published, published_at, created_at, updated_at, 'en' AS lang_code
                 FROM examples_articles
                 WHERE is_published = 1
                   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
                   {$clusterWhere}
                 ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
                 LIMIT {$limit}"
            );
        }

        $langSafe = mysqli_real_escape_string($db, $lang);
        $langCond = $lang === 'ru'
            ? "lang_code = 'ru'"
            : "lang_code = 'en'";

        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, title, slug, {$clusterSelect}, excerpt_html, content_html{$previewSelect}, is_published, published_at, created_at, updated_at, sort_order
             FROM examples_articles
             WHERE is_published = 1
               AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
               AND {$langCond}
               {$clusterWhere}
             ORDER BY
               (lang_code = '{$langSafe}') DESC,
               COALESCE(published_at, updated_at, created_at) DESC,
               id DESC
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

if (!function_exists('examples_build_where')) {
    function examples_build_where(mysqli $db, string $host, string $lang, string $query = '', bool $exact = false, string $cluster = ''): string
    {
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $lang = examples_normalize_lang($lang);
        $where = [
            "is_published = 1",
            "(domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')",
        ];

        if (examples_table_has_lang_column($db)) {
            $where[] = $lang === 'ru' ? "lang_code = 'ru'" : "lang_code = 'en'";
        }
        if (examples_table_has_column($db, 'cluster_code')) {
            $cluster = trim($cluster);
            if ($cluster !== '') {
                $clusterSafe = mysqli_real_escape_string($db, examples_normalize_cluster($cluster, $lang));
                $where[] = "cluster_code = '{$clusterSafe}'";
            }
        }

        $query = trim($query);
        if ($query !== '') {
            $querySafe = mysqli_real_escape_string($db, $query);
            if ($exact) {
                $where[] = "(title = '{$querySafe}' OR excerpt_html = '{$querySafe}' OR content_html = '{$querySafe}')";
            } else {
                $like = "'%" . $querySafe . "%'";
                $where[] = "(title LIKE {$like} OR excerpt_html LIKE {$like} OR content_html LIKE {$like})";
            }
        }

        return implode(' AND ', $where);
    }
}

if (!function_exists('examples_fetch_published_count')) {
    function examples_fetch_published_count($FRMWRK, string $host, string $lang = 'en', string $query = '', bool $exact = false, string $cluster = ''): int
    {
        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db)) {
            return 0;
        }

        $where = examples_build_where($db, $host, $lang, $query, $exact, $cluster);
        $rows = $FRMWRK->DBRecords(
            "SELECT COUNT(*) AS cnt
             FROM examples_articles
             WHERE {$where}"
        );
        if (empty($rows)) {
            return 0;
        }
        return (int)($rows[0]['cnt'] ?? 0);
    }
}

if (!function_exists('examples_fetch_published_page')) {
    function examples_fetch_published_page(
        $FRMWRK,
        string $host,
        string $lang = 'en',
        int $page = 1,
        int $perPage = 10,
        string $query = '',
        bool $exact = false,
        string $cluster = ''
    ): array {
        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db)) {
            return [];
        }

        $perPage = max(1, min(50, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = examples_build_where($db, $host, $lang, $query, $exact, $cluster);
        $previewSelect = examples_preview_select_sql($db);
        $hasLang = examples_table_has_lang_column($db);
        $hasCluster = examples_table_has_column($db, 'cluster_code');
        $langSelect = $hasLang ? "lang_code" : "'en' AS lang_code";
        $clusterSelect = $hasCluster ? "cluster_code" : ("'" . examples_cluster_default($lang) . "' AS cluster_code");

        return $FRMWRK->DBRecords(
            "SELECT id, domain_host, {$langSelect}, title, slug, {$clusterSelect}, excerpt_html, content_html{$previewSelect}, is_published, published_at, created_at, updated_at, sort_order
             FROM examples_articles
             WHERE {$where}
             ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }
}

if (!function_exists('examples_fetch_suggestions')) {
    function examples_fetch_suggestions($FRMWRK, string $host, string $lang = 'en', string $query = '', int $limit = 10, string $cluster = ''): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 3) {
            return [];
        }

        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db)) {
            return [];
        }

        $limit = max(1, min(10, $limit));
        $where = examples_build_where($db, $host, $lang, $query, false, $cluster);
        $hasCluster = examples_table_has_column($db, 'cluster_code');
        $clusterSelect = $hasCluster ? 'cluster_code' : ("'" . examples_cluster_default($lang) . "' AS cluster_code");
        $rows = $FRMWRK->DBRecords(
            "SELECT id, title, slug, {$clusterSelect}
             FROM examples_articles
             WHERE {$where}
             ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
             LIMIT {$limit}"
        );

        $out = [];
        foreach ($rows as $row) {
            $title = trim((string)($row['title'] ?? ''));
            $slug = trim((string)($row['slug'] ?? ''));
            if ($title === '' || $slug === '') {
                continue;
            }
            $out[] = ['title' => $title, 'slug' => $slug, 'cluster_code' => (string)($row['cluster_code'] ?? '')];
        }
        return $out;
    }
}

if (!function_exists('examples_fetch_published_by_slug')) {
    function examples_fetch_published_by_slug($FRMWRK, string $host, string $slug, string $lang = 'en', string $cluster = ''): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db)) {
            return null;
        }

        $lang = examples_normalize_lang($lang);
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $slugRaw = trim(rawurldecode($slug));
        $slugExactSafe = mysqli_real_escape_string($db, $slugRaw);
        $slugSafe = mysqli_real_escape_string($db, examples_slugify($slugRaw));
        $slugWhere = $slugExactSafe === $slugSafe
            ? "slug = '{$slugSafe}'"
            : "(slug = '{$slugExactSafe}' OR slug = '{$slugSafe}')";
        $hasLang = examples_table_has_lang_column($db);
        $hasCluster = examples_table_has_column($db, 'cluster_code');
        $previewSelect = examples_preview_select_sql($db);
        $clusterSelect = $hasCluster ? "cluster_code" : ("'" . examples_cluster_default($lang) . "' AS cluster_code");
        $clusterWhere = '';
        if ($hasCluster && trim($cluster) !== '') {
            $clusterSafe = mysqli_real_escape_string($db, examples_normalize_cluster($cluster, $lang));
            $clusterWhere = " AND cluster_code = '{$clusterSafe}'";
        }
        if (!$hasLang) {
            $rows = $FRMWRK->DBRecords(
                "SELECT id, domain_host, title, slug, {$clusterSelect}, excerpt_html, content_html{$previewSelect}, is_published, published_at, created_at, updated_at, 'en' AS lang_code
                 FROM examples_articles
                 WHERE is_published = 1
                   AND {$slugWhere}
                   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
                   {$clusterWhere}
                 ORDER BY (domain_host = '{$hostSafe}') DESC, id DESC
                 LIMIT 1"
            );
            return !empty($rows) ? $rows[0] : null;
        }

        $langSafe = mysqli_real_escape_string($db, $lang);
        $langCond = $lang === 'ru'
            ? "lang_code = 'ru'"
            : "lang_code = 'en'";
        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, title, slug, {$clusterSelect}, excerpt_html, content_html{$previewSelect}, is_published, published_at, created_at, updated_at
             FROM examples_articles
             WHERE is_published = 1
               AND {$slugWhere}
               AND {$langCond}
               {$clusterWhere}
               AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
             ORDER BY (lang_code = '{$langSafe}') DESC, (domain_host = '{$hostSafe}') DESC, id DESC
             LIMIT 1"
        );

        return !empty($rows) ? $rows[0] : null;
    }
}

if (!function_exists('examples_fetch_clusters')) {
    function examples_fetch_clusters($FRMWRK, string $host, string $lang = 'en', int $limit = 20): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db) || !examples_table_has_column($db, 'cluster_code')) {
            return [];
        }
        $limit = max(1, min(50, $limit));
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langCond = examples_table_has_lang_column($db)
            ? ($lang === 'ru' ? "AND lang_code = 'ru'" : "AND lang_code = 'en'")
            : '';

        $rows = $FRMWRK->DBRecords(
            "SELECT cluster_code, COUNT(*) AS cnt
             FROM examples_articles
             WHERE is_published = 1
               AND COALESCE(cluster_code, '') <> ''
               AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
               {$langCond}
             GROUP BY cluster_code
             ORDER BY cnt DESC, cluster_code ASC
             LIMIT {$limit}"
        );
        $out = [];
        foreach ($rows as $row) {
            $code = trim((string)($row['cluster_code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $out[] = [
                'code' => $code,
                'label' => examples_cluster_label($code, $lang),
                'count' => (int)($row['cnt'] ?? 0),
            ];
        }
        return $out;
    }
}

