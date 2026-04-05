<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';

$indexNowLib = DIR . 'core/libs/indexnow.php';
if (is_file($indexNowLib)) {
    require_once $indexNowLib;
}
$seoSettingsLib = DIR . 'core/libs/seo_generator_settings.php';
if (is_file($seoSettingsLib)) {
    require_once $seoSettingsLib;
}
$examplesCommon = DIR . 'core/controls/examples/_common.php';
if (is_file($examplesCommon)) {
    require_once $examplesCommon;
}

function enqueue_blog_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function enqueue_blog_opts(): array
{
    $opts = [
        'host' => '',
        'lang' => '',
        'limit' => 5000,
        'dry_run' => false,
    ];
    $argv = isset($GLOBALS['argv']) && is_array($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
    foreach ($argv as $arg) {
        if (!is_string($arg) || strpos($arg, '--') !== 0) {
            continue;
        }
        if ($arg === '--dry-run') {
            $opts['dry_run'] = true;
            continue;
        }
        if (strpos($arg, '--host=') === 0) {
            $opts['host'] = trim(substr($arg, 7));
            continue;
        }
        if (strpos($arg, '--lang=') === 0) {
            $opts['lang'] = strtolower(trim(substr($arg, 7)));
            continue;
        }
        if (strpos($arg, '--limit=') === 0) {
            $v = (int)substr($arg, 8);
            if ($v > 0) {
                $opts['limit'] = max(1, min(50000, $v));
            }
            continue;
        }
    }
    $opts['host'] = function_exists('indexnow_clean_host')
        ? indexnow_clean_host((string)$opts['host'])
        : strtolower(trim((string)$opts['host']));
    if (!in_array($opts['lang'], ['ru', 'en'], true)) {
        $opts['lang'] = '';
    }
    return $opts;
}

$opts = enqueue_blog_opts();
$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!$DB || !function_exists('indexnow_queue_enqueue') || !function_exists('indexnow_clean_host')) {
    enqueue_blog_echo('Init failed: DB/indexnow lib unavailable');
    exit(1);
}

$settingsHosts = (array)($GLOBALS['IndexNowHosts'] ?? ['portcore.ru', 'portcore.online']);
if (function_exists('seo_gen_settings_get')) {
    $seo = seo_gen_settings_get($DB);
    if (is_array($seo) && !empty($seo['indexnow_hosts']) && is_array($seo['indexnow_hosts'])) {
        $settingsHosts = (array)$seo['indexnow_hosts'];
    }
}
$hosts = [];
foreach ($settingsHosts as $h) {
    $clean = indexnow_clean_host((string)$h);
    if ($clean !== '' && !in_array($clean, $hosts, true)) {
        $hosts[] = $clean;
    }
}
if ($opts['host'] !== '') {
    $hosts = [$opts['host']];
}
if (empty($hosts)) {
    enqueue_blog_echo('No hosts to process');
    exit(1);
}

$tableCheck = mysqli_query(
    $DB,
    "SELECT 1 FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'examples_articles' LIMIT 1"
);
if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
    enqueue_blog_echo('examples_articles table is unavailable');
    exit(1);
}

$hasLang = function_exists('examples_table_has_lang_column') ? examples_table_has_lang_column($DB) : false;
$hasCluster = function_exists('examples_table_has_column') ? examples_table_has_column($DB, 'cluster_code') : false;

$totalCandidates = 0;
$totalEnqueued = 0;
$seen = [];

foreach ($hosts as $host) {
    $hostSafe = mysqli_real_escape_string($DB, $host);
    $lang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
    if ($opts['lang'] !== '' && $opts['lang'] !== $lang) {
        continue;
    }
    $langSafe = mysqli_real_escape_string($DB, $lang);
    $langCond = $hasLang ? "AND lang_code = '{$langSafe}'" : '';
    $clusterSelect = $hasCluster ? ', cluster_code' : ", '' AS cluster_code";

    $rows = $FRMWRK->DBRecords(
        "SELECT slug{$clusterSelect}
         FROM examples_articles
         WHERE is_published = 1
           AND slug IS NOT NULL
           AND slug <> ''
           AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
           {$langCond}
         ORDER BY id DESC
         LIMIT " . (int)$opts['limit']
    );

    enqueue_blog_echo('Host ' . $host . ' lang=' . $lang . ' candidates=' . count($rows));
    $seenClusters = [];
    foreach ($rows as $row) {
        $slug = trim((string)($row['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $clusterCode = $hasCluster
            ? (function_exists('examples_normalize_cluster')
                ? examples_normalize_cluster((string)($row['cluster_code'] ?? ''), $lang)
                : trim((string)($row['cluster_code'] ?? '')))
            : '';

        if ($clusterCode !== '' && !isset($seenClusters[$clusterCode])) {
            $seenClusters[$clusterCode] = true;
            $clusterUrl = 'https://' . $host . '/blog/' . rawurlencode($clusterCode) . '/';
            $clusterHash = hash('sha256', $clusterUrl);
            if (!isset($seen[$clusterHash])) {
                $seen[$clusterHash] = true;
                $totalCandidates++;
                if (!$opts['dry_run']) {
                    $ok = indexnow_queue_enqueue($DB, $clusterUrl, [
                        'lang_code' => $lang,
                        'source' => 'blog_seed',
                        'event_type' => 'update',
                    ]);
                    if ($ok) {
                        $totalEnqueued++;
                    }
                }
            }
        }

        $url = 'https://' . $host
            . ($clusterCode !== ''
                ? '/blog/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/'
                : '/blog/' . rawurlencode($slug) . '/');
        $urlHash = hash('sha256', $url);
        if (isset($seen[$urlHash])) {
            continue;
        }
        $seen[$urlHash] = true;
        $totalCandidates++;
        if ($opts['dry_run']) {
            continue;
        }
        $ok = indexnow_queue_enqueue($DB, $url, [
            'lang_code' => $lang,
            'source' => 'blog_seed',
            'event_type' => 'publish',
        ]);
        if ($ok) {
            $totalEnqueued++;
        }
    }
}

enqueue_blog_echo(
    'Done. hosts=' . count($hosts)
    . ', candidates=' . $totalCandidates
    . ', enqueued=' . $totalEnqueued
    . ', dry_run=' . (int)$opts['dry_run']
);
exit(0);

