<?php

if (!function_exists('examples_popularity_table_exists')) {
    function examples_popularity_table_exists(mysqli $db, string $table): bool
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
        return $res ? mysqli_num_rows($res) > 0 : false;
    }
}

if (!function_exists('examples_popularity_ensure_tables')) {
    function examples_popularity_ensure_tables(mysqli $db): void
    {
        if (!examples_popularity_table_exists($db, 'examples_article_popularity_cache')) {
            @mysqli_query(
                $db,
                "CREATE TABLE examples_article_popularity_cache (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    article_id INT UNSIGNED NOT NULL DEFAULT 0,
                    domain_host VARCHAR(190) NOT NULL DEFAULT '',
                    lang_code VARCHAR(8) NOT NULL DEFAULT 'en',
                    material_section VARCHAR(32) NOT NULL DEFAULT 'journal',
                    cluster_code VARCHAR(64) NOT NULL DEFAULT '',
                    slug VARCHAR(190) NOT NULL DEFAULT '',
                    views_count INT UNSIGNED NOT NULL DEFAULT 0,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uniq_examples_article_popularity (domain_host, lang_code, material_section, cluster_code, slug),
                    KEY idx_examples_article_popularity_lookup (domain_host, lang_code, material_section, views_count),
                    KEY idx_examples_article_popularity_article (article_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        }

        if (!examples_popularity_table_exists($db, 'examples_cluster_popularity_cache')) {
            @mysqli_query(
                $db,
                "CREATE TABLE examples_cluster_popularity_cache (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    domain_host VARCHAR(190) NOT NULL DEFAULT '',
                    lang_code VARCHAR(8) NOT NULL DEFAULT 'en',
                    material_section VARCHAR(32) NOT NULL DEFAULT 'journal',
                    cluster_code VARCHAR(64) NOT NULL DEFAULT '',
                    cluster_label VARCHAR(190) NOT NULL DEFAULT '',
                    views_count INT UNSIGNED NOT NULL DEFAULT 0,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uniq_examples_cluster_popularity (domain_host, lang_code, material_section, cluster_code),
                    KEY idx_examples_cluster_popularity_lookup (domain_host, lang_code, material_section, views_count)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        }
    }
}

if (!function_exists('examples_popularity_fetch_top_clusters')) {
    function examples_popularity_fetch_top_clusters($FRMWRK, string $host, string $lang, string $materialSection, int $limit = 3): array
    {
        if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return [];
        }
        $db = $FRMWRK->DB();
        if (!$db) {
            return [];
        }

        examples_popularity_ensure_tables($db);

        $hostSafe = mysqli_real_escape_string($db, strtolower(trim($host)));
        $langSafe = mysqli_real_escape_string($db, strtolower(trim($lang)));
        $sectionSafe = mysqli_real_escape_string($db, trim($materialSection) === 'playbooks' ? 'playbooks' : 'journal');
        $limit = max(1, min(10, $limit));

        $rows = $FRMWRK->DBRecords(
            "SELECT cluster_code, cluster_label, MAX(views_count) AS views_count
             FROM examples_cluster_popularity_cache
             WHERE (domain_host = '{$hostSafe}' OR domain_host = '')
               AND lang_code = '{$langSafe}'
               AND material_section = '{$sectionSafe}'
               AND COALESCE(cluster_code, '') <> ''
             GROUP BY cluster_code, cluster_label
             ORDER BY views_count DESC, cluster_label ASC, cluster_code ASC
             LIMIT {$limit}"
        );

        $out = [];
        foreach ((array)$rows as $row) {
            $code = trim((string)($row['cluster_code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $label = trim((string)($row['cluster_label'] ?? ''));
            if ($label === '' && function_exists('examples_cluster_label')) {
                $label = examples_cluster_label($code, $lang);
            }
            $out[] = [
                'code' => $code,
                'label' => $label !== '' ? $label : $code,
                'views_count' => (int)($row['views_count'] ?? 0),
            ];
        }

        return $out;
    }
}

if (!function_exists('examples_popularity_view_map')) {
    function examples_popularity_view_map($FRMWRK, string $host, string $lang, string $materialSection, array $items): array
    {
        if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return [];
        }
        $db = $FRMWRK->DB();
        if (!$db || empty($items)) {
            return [];
        }

        examples_popularity_ensure_tables($db);

        $pairs = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $slug = trim((string)($item['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $cluster = trim((string)($item['cluster_code'] ?? ''));
            $pairs[] = [$cluster, $slug];
        }

        if (empty($pairs)) {
            return [];
        }

        $hostSafe = mysqli_real_escape_string($db, strtolower(trim($host)));
        $langSafe = mysqli_real_escape_string($db, strtolower(trim($lang)));
        $sectionSafe = mysqli_real_escape_string($db, trim($materialSection) === 'playbooks' ? 'playbooks' : 'journal');
        $conditions = [];
        foreach ($pairs as [$cluster, $slug]) {
            $conditions[] = "(cluster_code = '" . mysqli_real_escape_string($db, $cluster) . "' AND slug = '" . mysqli_real_escape_string($db, $slug) . "')";
        }
        $wherePairs = implode(' OR ', $conditions);
        if ($wherePairs === '') {
            return [];
        }

        $rows = $FRMWRK->DBRecords(
            "SELECT cluster_code, slug, MAX(views_count) AS views_count
             FROM examples_article_popularity_cache
             WHERE (domain_host = '{$hostSafe}' OR domain_host = '')
               AND lang_code = '{$langSafe}'
               AND material_section = '{$sectionSafe}'
               AND ({$wherePairs})
             GROUP BY cluster_code, slug"
        );

        $map = [];
        foreach ((array)$rows as $row) {
            $key = trim((string)($row['cluster_code'] ?? '')) . '|' . trim((string)($row['slug'] ?? ''));
            $map[$key] = (int)($row['views_count'] ?? 0);
        }

        return $map;
    }
}

if (!function_exists('examples_popularity_attach_views')) {
    function examples_popularity_attach_views($FRMWRK, string $host, string $lang, string $materialSection, array $items): array
    {
        if (empty($items)) {
            return $items;
        }

        $map = examples_popularity_view_map($FRMWRK, $host, $lang, $materialSection, $items);
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $key = trim((string)($item['cluster_code'] ?? '')) . '|' . trim((string)($item['slug'] ?? ''));
            $item['view_count'] = (int)($map[$key] ?? 0);
        }
        unset($item);

        return $items;
    }
}

if (!function_exists('examples_popularity_attach_single_view')) {
    function examples_popularity_attach_single_view($FRMWRK, string $host, string $lang, string $materialSection, ?array $item): ?array
    {
        if (!is_array($item)) {
            return $item;
        }
        $rows = examples_popularity_attach_views($FRMWRK, $host, $lang, $materialSection, [$item]);
        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : $item;
    }
}
