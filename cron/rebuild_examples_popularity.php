<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/controls/examples/_common.php';
require_once DIR . 'core/libs/examples_popularity.php';
$pageHtmlCacheLib = DIR . 'core/libs/page_html_cache.php';
if (is_file($pageHtmlCacheLib)) {
    require_once $pageHtmlCacheLib;
}

function popularity_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!$DB) {
    popularity_echo('DB connection failed.');
    exit(1);
}

if (!function_exists('examples_table_exists') || !examples_table_exists($DB)) {
    popularity_echo('examples_articles table is missing.');
    exit(1);
}

examples_popularity_ensure_tables($DB);

$hasLang = function_exists('examples_table_has_lang_column') ? examples_table_has_lang_column($DB) : false;
$hasCluster = function_exists('examples_table_has_column') ? examples_table_has_column($DB, 'cluster_code') : false;
$hasSection = function_exists('examples_table_has_column') ? examples_table_has_column($DB, 'material_section') : false;

$langSelect = $hasLang ? 'lang_code' : "'en' AS lang_code";
$clusterSelect = $hasCluster ? 'cluster_code' : "'' AS cluster_code";
$sectionSelect = $hasSection ? 'material_section' : "'journal' AS material_section";

$articles = $FRMWRK->DBRecords(
    "SELECT id, domain_host, {$langSelect}, slug, {$clusterSelect}, {$sectionSelect}
     FROM examples_articles
     WHERE is_published = 1
       AND COALESCE(slug, '') <> ''
     ORDER BY id DESC"
);

$articleRows = [];
$clusterAgg = [];

foreach ((array)$articles as $row) {
    $articleId = (int)($row['id'] ?? 0);
    $host = strtolower(trim((string)($row['domain_host'] ?? '')));
    $lang = trim((string)($row['lang_code'] ?? 'en'));
    $slug = trim((string)($row['slug'] ?? ''));
    $cluster = trim((string)($row['cluster_code'] ?? ''));
    $section = trim((string)($row['material_section'] ?? 'journal'));
    if (!in_array($section, ['journal', 'playbooks', 'signals', 'fun'], true)) {
        $section = 'journal';
    }
    if ($articleId <= 0 || $slug === '') {
        continue;
    }

    $path = function_exists('examples_article_url_path')
        ? examples_article_url_path($slug, $cluster, $host, $section)
        : (($section === 'playbooks' ? '/playbooks/' : '/journal/') . rawurlencode($slug) . '/');

    $pathVariants = [];
    $path = trim((string)$path);
    if ($path !== '') {
        $pathVariants[] = $path;
        $trimmed = rtrim($path, '/');
        if ($trimmed === '') {
            $trimmed = '/';
        }
        $pathVariants[] = $trimmed;
        if ($trimmed !== '/') {
            $pathVariants[] = $trimmed . '/';
        }
    }
    $pathVariants = array_values(array_unique(array_filter($pathVariants, static function ($value): bool {
        return trim((string)$value) !== '';
    })));
    if (empty($pathVariants)) {
        continue;
    }

    $pathConditions = [];
    foreach ($pathVariants as $variant) {
        $pathConditions[] = "path = '" . mysqli_real_escape_string($DB, $variant) . "'";
    }
    $pathWhere = implode(' OR ', $pathConditions);

    $hostWhere = $host !== ''
        ? "AND (host = '" . mysqli_real_escape_string($DB, $host) . "' OR COALESCE(host, '') = '')"
        : '';

    $viewRows = $FRMWRK->DBRecords(
        "SELECT COUNT(*) AS cnt
         FROM analytics_visits
         WHERE COALESCE(is_bot, 0) = 0
           {$hostWhere}
           AND ({$pathWhere})"
    );
    $views = (int)($viewRows[0]['cnt'] ?? 0);

    $articleRows[] = [
        'article_id' => $articleId,
        'domain_host' => $host,
        'lang_code' => $lang,
        'material_section' => $section,
        'cluster_code' => $cluster,
        'slug' => $slug,
        'views_count' => $views,
    ];

    if ($cluster !== '') {
        $aggKey = $host . '|' . $lang . '|' . $section . '|' . $cluster;
        if (!isset($clusterAgg[$aggKey])) {
            $clusterAgg[$aggKey] = [
                'domain_host' => $host,
                'lang_code' => $lang,
                'material_section' => $section,
                'cluster_code' => $cluster,
                'cluster_label' => function_exists('examples_cluster_label') ? examples_cluster_label($cluster, $lang) : $cluster,
                'views_count' => 0,
            ];
        }
        $clusterAgg[$aggKey]['views_count'] += $views;
    }
}

@mysqli_query($DB, "TRUNCATE TABLE examples_article_popularity_cache");
@mysqli_query($DB, "TRUNCATE TABLE examples_cluster_popularity_cache");

foreach ($articleRows as $row) {
    $sql = sprintf(
        "INSERT INTO examples_article_popularity_cache
        (article_id, domain_host, lang_code, material_section, cluster_code, slug, views_count, updated_at)
        VALUES (%d, '%s', '%s', '%s', '%s', '%s', %d, NOW())",
        (int)$row['article_id'],
        mysqli_real_escape_string($DB, (string)$row['domain_host']),
        mysqli_real_escape_string($DB, (string)$row['lang_code']),
        mysqli_real_escape_string($DB, (string)$row['material_section']),
        mysqli_real_escape_string($DB, (string)$row['cluster_code']),
        mysqli_real_escape_string($DB, (string)$row['slug']),
        (int)$row['views_count']
    );
    @mysqli_query($DB, $sql);
}

foreach ($clusterAgg as $row) {
    $sql = sprintf(
        "INSERT INTO examples_cluster_popularity_cache
        (domain_host, lang_code, material_section, cluster_code, cluster_label, views_count, updated_at)
        VALUES ('%s', '%s', '%s', '%s', '%s', %d, NOW())",
        mysqli_real_escape_string($DB, (string)$row['domain_host']),
        mysqli_real_escape_string($DB, (string)$row['lang_code']),
        mysqli_real_escape_string($DB, (string)$row['material_section']),
        mysqli_real_escape_string($DB, (string)$row['cluster_code']),
        mysqli_real_escape_string($DB, (string)$row['cluster_label']),
        (int)$row['views_count']
    );
    @mysqli_query($DB, $sql);
}

popularity_echo('Rebuilt article popularity rows: ' . count($articleRows));
popularity_echo('Rebuilt cluster popularity rows: ' . count($clusterAgg));
if (function_exists('page_html_cache_purge_content_routes')) {
    $purged = page_html_cache_purge_content_routes();
    popularity_echo('HTML cache purge after popularity rebuild: deleted=' . (int)$purged);
}
exit(0);
