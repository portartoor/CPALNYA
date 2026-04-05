<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';

$seoSettingsLib = DIR . 'core/libs/seo_generator_settings.php';
if (is_file($seoSettingsLib)) {
    require_once $seoSettingsLib;
}
$indexNowLib = DIR . 'core/libs/indexnow.php';
if (is_file($indexNowLib)) {
    require_once $indexNowLib;
}

function indexnow_cli_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function indexnow_runtime_options(): array
{
    $opts = [
        'limit' => 100,
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
        if (strpos($arg, '--limit=') === 0) {
            $v = (int)substr($arg, 8);
            if ($v > 0) {
                $opts['limit'] = max(1, min(500, $v));
            }
            continue;
        }
    }
    return $opts;
}

$runtime = indexnow_runtime_options();
$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!$DB || !function_exists('indexnow_worker_run')) {
    indexnow_cli_echo('Init failed: DB or indexnow lib not available.');
    exit(1);
}

$settings = [
    'enabled' => (bool)($GLOBALS['IndexNowEnabled'] ?? false),
    'key' => (string)($GLOBALS['IndexNowKey'] ?? ''),
    'key_location' => (string)($GLOBALS['IndexNowKeyLocation'] ?? ''),
    'endpoint' => (string)($GLOBALS['IndexNowEndpoint'] ?? ''),
    'hosts' => (array)($GLOBALS['IndexNowHosts'] ?? []),
    'submit_limit' => (int)($GLOBALS['IndexNowSubmitLimit'] ?? 100),
    'retry_delay_minutes' => (int)($GLOBALS['IndexNowRetryDelayMinutes'] ?? 15),
    'ping_on_publish' => (bool)($GLOBALS['IndexNowPingOnPublish'] ?? true),
];
if (function_exists('seo_gen_settings_get')) {
    $seo = seo_gen_settings_get($DB);
    if (is_array($seo)) {
        $settings['enabled'] = !empty($seo['indexnow_enabled']) || $settings['enabled'];
        if (trim((string)($seo['indexnow_key'] ?? '')) !== '') {
            $settings['key'] = (string)$seo['indexnow_key'];
        }
        if (trim((string)($seo['indexnow_key_location'] ?? '')) !== '') {
            $settings['key_location'] = (string)$seo['indexnow_key_location'];
        }
        if (trim((string)($seo['indexnow_endpoint'] ?? '')) !== '') {
            $settings['endpoint'] = (string)$seo['indexnow_endpoint'];
        }
        if (!empty($seo['indexnow_hosts']) && is_array($seo['indexnow_hosts'])) {
            $settings['hosts'] = $seo['indexnow_hosts'];
        }
        if (!empty($seo['indexnow_submit_limit'])) {
            $settings['submit_limit'] = (int)$seo['indexnow_submit_limit'];
        }
        if (!empty($seo['indexnow_retry_delay_minutes'])) {
            $settings['retry_delay_minutes'] = (int)$seo['indexnow_retry_delay_minutes'];
        }
        if (array_key_exists('indexnow_ping_on_publish', $seo)) {
            $settings['ping_on_publish'] = !empty($seo['indexnow_ping_on_publish']);
        }
    }
}

$limit = min((int)$runtime['limit'], max(1, (int)($settings['submit_limit'] ?? 100)));
$settings = indexnow_settings_normalize($settings);
indexnow_cli_echo(
    'Start. enabled=' . (int)$settings['enabled']
    . ', dry_run=' . (int)$runtime['dry_run']
    . ', limit=' . $limit
    . ', hosts=' . (empty($settings['hosts']) ? '(all)' : implode(',', $settings['hosts']))
);

$summary = indexnow_worker_run($DB, $settings, $limit, (bool)$runtime['dry_run']);
indexnow_cli_echo(
    'Done. picked=' . (int)($summary['picked'] ?? 0)
    . ', done=' . (int)($summary['done'] ?? 0)
    . ', failed=' . (int)($summary['failed'] ?? 0)
    . ', skipped=' . (int)($summary['skipped'] ?? 0)
    . (!empty($summary['error']) ? (', error=' . (string)$summary['error']) : '')
);
exit(0);
