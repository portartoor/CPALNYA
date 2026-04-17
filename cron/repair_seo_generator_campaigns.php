<?php
ini_set('display_errors', '1');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/controls/examples/_common.php';
$seoGeneratorSettingsLib = DIR . 'core/libs/seo_generator_settings.php';
if (is_file($seoGeneratorSettingsLib)) {
    require_once $seoGeneratorSettingsLib;
}

function repair_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!($DB instanceof mysqli)) {
    repair_echo('DB connection is not available.');
    exit(1);
}
if (!function_exists('seo_gen_settings_get') || !function_exists('seo_gen_settings_save') || !function_exists('seo_gen_default_campaigns')) {
    repair_echo('SEO generator settings library is not available.');
    exit(1);
}

$settings = seo_gen_settings_get($DB);
$defaults = seo_gen_default_campaigns();
$currentCampaigns = is_array($settings['campaigns'] ?? null) ? $settings['campaigns'] : [];
$added = [];

foreach ($defaults as $campaignKey => $campaignDefault) {
    if (!isset($currentCampaigns[$campaignKey]) || !is_array($currentCampaigns[$campaignKey])) {
        $currentCampaigns[$campaignKey] = $campaignDefault;
        $added[] = $campaignKey;
    }
}

$settings['campaigns'] = $currentCampaigns;
if (!seo_gen_settings_save($DB, $settings, 0)) {
    repair_echo('Failed to save normalized campaigns into seo_generator_settings.');
    exit(2);
}

$reloaded = seo_gen_settings_get($DB);
$reloadedCampaigns = is_array($reloaded['campaigns'] ?? null) ? $reloaded['campaigns'] : [];
$reviewsOk = isset($reloadedCampaigns['reviews']) && is_array($reloadedCampaigns['reviews']);

repair_echo('Campaign repair complete. Added: ' . (!empty($added) ? implode(', ', $added) : 'none'));
repair_echo('Campaigns now: ' . implode(', ', array_keys($reloadedCampaigns)));
repair_echo('Reviews campaign present: ' . ($reviewsOk ? 'yes' : 'no'));

exit($reviewsOk ? 0 : 3);
