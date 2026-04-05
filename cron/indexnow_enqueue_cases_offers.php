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

function enqueue_cases_offers_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function enqueue_cases_offers_opts(): array
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
    $opts['host'] = function_exists('indexnow_clean_host') ? indexnow_clean_host((string)$opts['host']) : strtolower(trim((string)$opts['host']));
    if (!in_array($opts['lang'], ['ru', 'en'], true)) {
        $opts['lang'] = '';
    }
    return $opts;
}

function enqueue_cases_offers_offer_slugs(): array
{
    $controlsPath = DIR . 'core/controls/offers.php';
    if (!is_file($controlsPath)) {
        return [];
    }
    $raw = (string)@file_get_contents($controlsPath);
    if ($raw === '') {
        return [];
    }
    $matches = [];
    if (!preg_match_all("/'slug'\\s*=>\\s*'([a-z0-9_-]+)'/i", $raw, $matches)) {
        return [];
    }
    $seen = [];
    $out = [];
    foreach ((array)($matches[1] ?? []) as $slugRaw) {
        $slug = strtolower(trim((string)$slugRaw));
        if ($slug === '' || isset($seen[$slug])) {
            continue;
        }
        $seen[$slug] = true;
        $out[] = $slug;
    }
    return $out;
}

$opts = enqueue_cases_offers_opts();
$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!$DB || !function_exists('indexnow_queue_enqueue') || !function_exists('indexnow_clean_host')) {
    enqueue_cases_offers_echo('Init failed: DB/indexnow lib unavailable');
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
    enqueue_cases_offers_echo('No hosts to process');
    exit(1);
}

$offerSlugs = enqueue_cases_offers_offer_slugs();
$totalCandidates = 0;
$totalEnqueued = 0;
$seenHashes = [];

foreach ($hosts as $host) {
    $lang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
    if ($opts['lang'] !== '' && $opts['lang'] !== $lang) {
        continue;
    }
    $hostSafe = mysqli_real_escape_string($DB, $host);
    $langSafe = mysqli_real_escape_string($DB, $lang);
    enqueue_cases_offers_echo('Host ' . $host . ' lang=' . $lang);

    $seedUrls = [
        'https://' . $host . '/cases/',
        'https://' . $host . '/offers/',
    ];
    foreach ($seedUrls as $url) {
        $hash = hash('sha256', $url);
        if (isset($seenHashes[$hash])) {
            continue;
        }
        $seenHashes[$hash] = true;
        $totalCandidates++;
        if ($opts['dry_run']) {
            continue;
        }
        if (indexnow_queue_enqueue($DB, $url, ['lang_code' => $lang, 'source' => 'cases_offers_seed', 'event_type' => 'publish'])) {
            $totalEnqueued++;
        }
    }

    $caseRows = $FRMWRK->DBRecords(
        "SELECT slug, symbolic_code
         FROM public_cases
         WHERE is_published = 1
           AND (COALESCE(symbolic_code, '') <> '' OR COALESCE(slug, '') <> '')
           AND lang_code = '{$langSafe}'
           AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
         ORDER BY id DESC
         LIMIT " . (int)$opts['limit']
    );
    foreach ($caseRows as $row) {
        $code = trim((string)($row['symbolic_code'] ?? ''));
        if ($code === '') {
            $code = trim((string)($row['slug'] ?? ''));
        }
        if ($code === '') {
            continue;
        }
        $url = 'https://' . $host . '/cases/' . rawurlencode($code) . '/';
        $hash = hash('sha256', $url);
        if (isset($seenHashes[$hash])) {
            continue;
        }
        $seenHashes[$hash] = true;
        $totalCandidates++;
        if ($opts['dry_run']) {
            continue;
        }
        if (indexnow_queue_enqueue($DB, $url, ['lang_code' => $lang, 'source' => 'cases_offers_seed', 'event_type' => 'publish'])) {
            $totalEnqueued++;
        }
    }

    foreach ($offerSlugs as $slug) {
        $url = 'https://' . $host . '/offers/' . rawurlencode($slug) . '/';
        $hash = hash('sha256', $url);
        if (isset($seenHashes[$hash])) {
            continue;
        }
        $seenHashes[$hash] = true;
        $totalCandidates++;
        if ($opts['dry_run']) {
            continue;
        }
        if (indexnow_queue_enqueue($DB, $url, ['lang_code' => $lang, 'source' => 'cases_offers_seed', 'event_type' => 'publish'])) {
            $totalEnqueued++;
        }
    }
}

enqueue_cases_offers_echo(
    'Done. hosts=' . count($hosts)
    . ', offer_slugs=' . count($offerSlugs)
    . ', candidates=' . $totalCandidates
    . ', enqueued=' . $totalEnqueued
    . ', dry_run=' . (int)$opts['dry_run']
);
exit(0);

