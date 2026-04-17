<?php
ini_set('display_errors', '1');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/libs/seo_generator_settings.php';

function repair_reviews_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function repair_reviews_maybe_fix_mojibake(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return $value;
    }

    if (!preg_match('/(?:Р.|С.|вЂ|Ѓ|‚|€|™|њ|ќ)/u', $value)) {
        return $value;
    }

    $fixed = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
    if (!is_string($fixed) || $fixed === '') {
        return $value;
    }

    $origScore = preg_match_all('/[А-Яа-яЁё]/u', $value, $m1);
    $fixedScore = preg_match_all('/[А-Яа-яЁё]/u', $fixed, $m2);
    if ((int)$fixedScore < (int)$origScore) {
        return $value;
    }

    return $fixed;
}

function repair_reviews_walk($value)
{
    if (is_array($value)) {
        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = repair_reviews_walk($item);
        }
        return $out;
    }
    if (is_string($value)) {
        return repair_reviews_maybe_fix_mojibake($value);
    }
    return $value;
}

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!($DB instanceof mysqli)) {
    repair_reviews_echo('DB connection is not available.');
    exit(1);
}

if (!function_exists('seo_gen_settings_get') || !function_exists('seo_gen_settings_save') || !function_exists('seo_gen_default_campaigns')) {
    repair_reviews_echo('SEO generator settings library is not available.');
    exit(2);
}

$settings = seo_gen_settings_get($DB);
$beforeCampaigns = is_array($settings['campaigns'] ?? null) ? $settings['campaigns'] : [];
$defaults = seo_gen_default_campaigns();
$reviewsDefault = is_array($defaults['reviews'] ?? null) ? $defaults['reviews'] : [];

if (empty($reviewsDefault)) {
    repair_reviews_echo('Reviews campaign defaults are missing.');
    exit(3);
}

$settings = repair_reviews_walk($settings);
$settings['campaigns'] = is_array($settings['campaigns'] ?? null) ? $settings['campaigns'] : [];

$currentReviews = is_array($settings['campaigns']['reviews'] ?? null) ? $settings['campaigns']['reviews'] : [];
$settings['campaigns']['reviews'] = array_merge($reviewsDefault, $currentReviews, [
    'key' => 'reviews',
    'material_section' => 'reviews',
    'styles_en' => (array)($reviewsDefault['styles_en'] ?? []),
    'styles_ru' => (array)($reviewsDefault['styles_ru'] ?? []),
    'clusters_en' => (array)($reviewsDefault['clusters_en'] ?? []),
    'clusters_ru' => (array)($reviewsDefault['clusters_ru'] ?? []),
    'article_structures_en' => (array)($reviewsDefault['article_structures_en'] ?? []),
    'article_structures_ru' => (array)($reviewsDefault['article_structures_ru'] ?? []),
    'article_user_prompt_append_en' => (string)($reviewsDefault['article_user_prompt_append_en'] ?? ''),
    'article_user_prompt_append_ru' => (string)($reviewsDefault['article_user_prompt_append_ru'] ?? ''),
]);

if (!seo_gen_settings_save($DB, $settings, 0)) {
    repair_reviews_echo('Failed to save settings.');
    exit(4);
}

$reloaded = seo_gen_settings_get($DB);
$reviews = is_array($reloaded['campaigns']['reviews'] ?? null) ? $reloaded['campaigns']['reviews'] : [];

repair_reviews_echo('Reviews campaign repaired and expanded.');
repair_reviews_echo('Styles EN: ' . count((array)($reviews['styles_en'] ?? [])));
repair_reviews_echo('Styles RU: ' . count((array)($reviews['styles_ru'] ?? [])));
repair_reviews_echo('Clusters EN: ' . count((array)($reviews['clusters_en'] ?? [])));
repair_reviews_echo('Clusters RU: ' . count((array)($reviews['clusters_ru'] ?? [])));
repair_reviews_echo('Structures EN: ' . count((array)($reviews['article_structures_en'] ?? [])));
repair_reviews_echo('Structures RU: ' . count((array)($reviews['article_structures_ru'] ?? [])));
repair_reviews_echo('Material section: ' . (string)($reviews['material_section'] ?? ''));
repair_reviews_echo('Previously had reviews: ' . (isset($beforeCampaigns['reviews']) ? 'yes' : 'no'));

exit(0);
