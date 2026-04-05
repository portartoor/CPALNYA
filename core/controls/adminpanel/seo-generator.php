<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/libs/seo_generator_settings.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = 'info';

function admin_seo_gen_post_bool(string $name, bool $default = false): bool
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    $v = (string)$_POST[$name];
    return in_array($v, ['1', 'true', 'on', 'yes'], true);
}

function admin_seo_gen_post_int(string $name, int $default): int
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    return (int)$_POST[$name];
}

function admin_seo_gen_post_string(string $name, string $default = ''): string
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    return trim((string)$_POST[$name]);
}

function admin_seo_gen_table_exists(mysqli $db, string $table): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $sql = "SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableSafe}'
            LIMIT 1";
    $res = mysqli_query($db, $sql);
    return $res && mysqli_num_rows($res) > 0;
}

function admin_seo_gen_parse_moods(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 4) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $labelEn = (string)$parts[2];
        $labelRu = (string)$parts[3];
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'label_en' => $labelEn !== '' ? $labelEn : $key,
            'label_ru' => $labelRu !== '' ? $labelRu : $key,
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_parse_color_schemes(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 2) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $instruction = (string)($parts[2] ?? $key);
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'instruction' => $instruction !== '' ? $instruction : $key,
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_parse_image_compositions(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 4) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $labelEn = (string)$parts[2];
        $labelRu = (string)$parts[3];
        $instruction = (string)($parts[4] ?? '');
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'label_en' => $labelEn !== '' ? $labelEn : $key,
            'label_ru' => $labelRu !== '' ? $labelRu : $key,
            'instruction' => $instruction !== '' ? $instruction : ($labelEn !== '' ? $labelEn : $key),
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_parse_image_scene_families(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 4) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $labelEn = (string)$parts[2];
        $labelRu = (string)$parts[3];
        $instruction = (string)($parts[4] ?? '');
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'label_en' => $labelEn !== '' ? $labelEn : $key,
            'label_ru' => $labelRu !== '' ? $labelRu : $key,
            'instruction' => $instruction !== '' ? $instruction : ($labelEn !== '' ? $labelEn : $key),
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

if (!$DB) {
    $message = 'Database connection failed.';
    $messageType = 'danger';
    $seoGeneratorSettings = seo_gen_settings_default();
    return;
}

seo_gen_settings_table_ensure($DB);
if (function_exists('seo_gen_cron_runs_table_ensure')) {
    seo_gen_cron_runs_table_ensure($DB);
}
$seoGeneratorSettings = seo_gen_settings_get($DB);
$scheduleDate = trim((string)($_GET['schedule_date'] ?? gmdate('Y-m-d')));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $scheduleDate)) {
    $scheduleDate = gmdate('Y-m-d');
}
$scheduleRows = [];
$hasCronRunsTable = admin_seo_gen_table_exists($DB, 'seo_article_cron_runs');
$hasExamplesTable = admin_seo_gen_table_exists($DB, 'examples_articles');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'update_schedule_time') {
        if (!$hasCronRunsTable) {
            $message = 'Table seo_article_cron_runs is missing.';
            $messageType = 'danger';
        } else {
            $runId = (int)($_POST['run_id'] ?? 0);
            $rowDate = trim((string)($_POST['job_date'] ?? ''));
            $time = trim((string)($_POST['planned_time'] ?? ''));
            if ($runId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $rowDate) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
                $message = 'Invalid schedule update payload.';
                $messageType = 'danger';
            } else {
                $plannedAt = $rowDate . ' ' . $time . ':00';
                $plannedAtSafe = mysqli_real_escape_string($DB, $plannedAt);
                $jobDateSafe = mysqli_real_escape_string($DB, $rowDate);
                $sql = "UPDATE seo_article_cron_runs
                        SET planned_at = '{$plannedAtSafe}', updated_at = NOW()
                        WHERE id = {$runId} AND job_date = '{$jobDateSafe}'
                        LIMIT 1";
                if (mysqli_query($DB, $sql)) {
                    $message = 'Schedule time updated.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update schedule time: ' . mysqli_error($DB);
                    $messageType = 'danger';
                }
            }
        }
    }
    if ($action === 'save_seo_generator_settings') {
        $incoming = $seoGeneratorSettings;
        $incoming['enabled'] = admin_seo_gen_post_bool('enabled', false);
        $incoming['langs'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('langs', 'en' . PHP_EOL . 'ru'), 10);
        $incoming['domain_host'] = admin_seo_gen_post_string('domain_host');
        $incoming['domain_host_en'] = admin_seo_gen_post_string('domain_host_en');
        $incoming['domain_host_ru'] = admin_seo_gen_post_string('domain_host_ru');
        $incoming['author_name'] = admin_seo_gen_post_string('author_name');
        $incoming['daily_min'] = admin_seo_gen_post_int('daily_min', 1);
        $incoming['daily_max'] = admin_seo_gen_post_int('daily_max', 3);
        $incoming['max_per_run'] = admin_seo_gen_post_int('max_per_run', 2);
        $incoming['word_min'] = admin_seo_gen_post_int('word_min', 2000);
        $incoming['word_max'] = admin_seo_gen_post_int('word_max', 5000);
        $incoming['today_first_delay_min'] = admin_seo_gen_post_int('today_first_delay_min', 15);
        $incoming['auto_expand_retries'] = admin_seo_gen_post_int('auto_expand_retries', 1);
        $incoming['expand_context_chars'] = admin_seo_gen_post_int('expand_context_chars', 7000);
        $incoming['prompt_version'] = admin_seo_gen_post_string('prompt_version', 'seo-cron-v1');
        $incoming['seed_salt'] = admin_seo_gen_post_string('seed_salt', 'geoip-seo-articles');
        $incoming['tone_variability'] = admin_seo_gen_post_int('tone_variability', 60);
        $incoming['portfolio_bofu_weight'] = admin_seo_gen_post_int('portfolio_bofu_weight', 30);
        $incoming['portfolio_mofu_weight'] = admin_seo_gen_post_int('portfolio_mofu_weight', 30);
        $incoming['portfolio_authority_weight'] = admin_seo_gen_post_int('portfolio_authority_weight', 20);
        $incoming['portfolio_case_weight'] = admin_seo_gen_post_int('portfolio_case_weight', 10);
        $incoming['portfolio_product_weight'] = admin_seo_gen_post_int('portfolio_product_weight', 10);
        $incoming['notify_schedule'] = admin_seo_gen_post_bool('notify_schedule', false);
        $incoming['notify_daily_schedule'] = admin_seo_gen_post_bool('notify_daily_schedule', false);
        $incoming['indexnow_enabled'] = admin_seo_gen_post_bool('indexnow_enabled', false);
        $incoming['indexnow_key'] = admin_seo_gen_post_string('indexnow_key');
        $incoming['indexnow_key_location'] = admin_seo_gen_post_string('indexnow_key_location');
        $incoming['indexnow_endpoint'] = admin_seo_gen_post_string('indexnow_endpoint');
        $incoming['indexnow_hosts'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('indexnow_hosts'), 40);
        $incoming['indexnow_ping_on_publish'] = admin_seo_gen_post_bool('indexnow_ping_on_publish', true);
        $incoming['indexnow_submit_limit'] = admin_seo_gen_post_int('indexnow_submit_limit', 100);
        $incoming['indexnow_retry_delay_minutes'] = admin_seo_gen_post_int('indexnow_retry_delay_minutes', 15);

        $incoming['llm_provider'] = admin_seo_gen_post_string('llm_provider', 'openai');
        $incoming['openai_api_key'] = admin_seo_gen_post_string('openai_api_key');
        $incoming['openai_base_url'] = admin_seo_gen_post_string('openai_base_url');
        $incoming['openai_model'] = admin_seo_gen_post_string('openai_model');
        $incoming['openai_timeout'] = admin_seo_gen_post_int('openai_timeout', 120);
        $incoming['openai_headers'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('openai_headers'), 100);
        $incoming['openrouter_api_key'] = admin_seo_gen_post_string('openrouter_api_key');
        $incoming['openrouter_base_url'] = admin_seo_gen_post_string('openrouter_base_url');
        $incoming['openrouter_model'] = admin_seo_gen_post_string('openrouter_model');

        $incoming['openai_proxy_enabled'] = admin_seo_gen_post_bool('openai_proxy_enabled', false);
        $incoming['openai_proxy_host'] = admin_seo_gen_post_string('openai_proxy_host');
        $incoming['openai_proxy_port'] = admin_seo_gen_post_int('openai_proxy_port', 0);
        $incoming['openai_proxy_type'] = admin_seo_gen_post_string('openai_proxy_type', 'http');
        $incoming['openai_proxy_username'] = admin_seo_gen_post_string('openai_proxy_username');
        $incoming['openai_proxy_password'] = admin_seo_gen_post_string('openai_proxy_password');
        $incoming['openai_proxy_pool_enabled'] = admin_seo_gen_post_bool('openai_proxy_pool_enabled', false);
        $incoming['openai_proxy_pool'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('openai_proxy_pool'), 300);

        $incoming['topic_analysis_enabled'] = admin_seo_gen_post_bool('topic_analysis_enabled', true);
        $incoming['topic_analysis_limit'] = admin_seo_gen_post_int('topic_analysis_limit', 120);
        $incoming['topic_analysis_system_prompt'] = admin_seo_gen_post_string('topic_analysis_system_prompt');
        $incoming['topic_analysis_user_prompt_append'] = admin_seo_gen_post_string('topic_analysis_user_prompt_append');

        $incoming['styles_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('styles_en'), 120);
        $incoming['styles_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('styles_ru'), 120);
        $incoming['clusters_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('clusters_en'), 120);
        $incoming['clusters_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('clusters_ru'), 120);
        $incoming['intent_verticals_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_verticals_en'), 150);
        $incoming['intent_verticals_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_verticals_ru'), 150);
        $incoming['intent_scenarios_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_scenarios_en'), 150);
        $incoming['intent_scenarios_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_scenarios_ru'), 150);
        $incoming['intent_objectives_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_objectives_en'), 150);
        $incoming['intent_objectives_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_objectives_ru'), 150);
        $incoming['intent_constraints_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_constraints_en'), 150);
        $incoming['intent_constraints_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_constraints_ru'), 150);
        $incoming['intent_artifacts_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_artifacts_en'), 150);
        $incoming['intent_artifacts_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_artifacts_ru'), 150);
        $incoming['intent_outcomes_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_outcomes_en'), 150);
        $incoming['intent_outcomes_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_outcomes_ru'), 150);
        $incoming['service_focus_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('service_focus_en'), 150);
        $incoming['service_focus_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('service_focus_ru'), 150);
        $incoming['forbidden_topics_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('forbidden_topics_en'), 150);
        $incoming['forbidden_topics_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('forbidden_topics_ru'), 150);
        $incoming['article_structures_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('article_structures_en'), 120);
        $incoming['article_structures_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('article_structures_ru'), 120);
        $incoming['moods'] = admin_seo_gen_parse_moods(admin_seo_gen_post_string('moods'));

        $incoming['article_system_prompt_en'] = admin_seo_gen_post_string('article_system_prompt_en');
        $incoming['article_system_prompt_ru'] = admin_seo_gen_post_string('article_system_prompt_ru');
        $incoming['article_user_prompt_append_en'] = admin_seo_gen_post_string('article_user_prompt_append_en');
        $incoming['article_user_prompt_append_ru'] = admin_seo_gen_post_string('article_user_prompt_append_ru');

        $incoming['expand_system_prompt_en'] = admin_seo_gen_post_string('expand_system_prompt_en');
        $incoming['expand_system_prompt_ru'] = admin_seo_gen_post_string('expand_system_prompt_ru');
        $incoming['expand_user_prompt_append_en'] = admin_seo_gen_post_string('expand_user_prompt_append_en');
        $incoming['expand_user_prompt_append_ru'] = admin_seo_gen_post_string('expand_user_prompt_append_ru');

        $incoming['preview_channel_enabled'] = admin_seo_gen_post_bool('preview_channel_enabled', false);
        $incoming['preview_channel_chat_id'] = admin_seo_gen_post_string('preview_channel_chat_id');
        $incoming['preview_post_max_words'] = admin_seo_gen_post_int('preview_post_max_words', 220);
        $incoming['preview_caption_max_words'] = admin_seo_gen_post_int('preview_caption_max_words', 80);
        $incoming['preview_post_min_words'] = admin_seo_gen_post_int('preview_post_min_words', 70);
        $incoming['preview_caption_min_words'] = admin_seo_gen_post_int('preview_caption_min_words', 26);
        $incoming['preview_use_llm'] = admin_seo_gen_post_bool('preview_use_llm', true);
        $incoming['preview_llm_model'] = admin_seo_gen_post_string('preview_llm_model');
        $incoming['preview_context_chars'] = admin_seo_gen_post_int('preview_context_chars', 14000);

        $incoming['preview_image_enabled'] = admin_seo_gen_post_bool('preview_image_enabled', false);
        $incoming['preview_image_model'] = admin_seo_gen_post_string('preview_image_model');
        $incoming['preview_image_size'] = admin_seo_gen_post_string('preview_image_size', '768x512');
        $incoming['preview_image_anchor_enforced'] = admin_seo_gen_post_bool('preview_image_anchor_enforced', true);
        $incoming['preview_image_anchor_append'] = admin_seo_gen_post_string('preview_image_anchor_append');
        $incoming['preview_image_style_options'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('preview_image_style_options'), 60);
        $incoming['image_color_schemes'] = admin_seo_gen_parse_color_schemes(admin_seo_gen_post_string('image_color_schemes'));
        $incoming['image_compositions'] = admin_seo_gen_parse_image_compositions(admin_seo_gen_post_string('image_compositions'));
        $incoming['image_scene_families'] = admin_seo_gen_parse_image_scene_families(admin_seo_gen_post_string('image_scene_families'));
        $incoming['preview_image_prompt_template'] = admin_seo_gen_post_string('preview_image_prompt_template');

        if (seo_gen_settings_save($DB, $incoming, (int)($adminpanelUser['id'] ?? 0))) {
            $seoGeneratorSettings = seo_gen_settings_get($DB);
            $message = 'SEO generator settings saved.';
            $messageType = 'success';
        } else {
            $message = 'Failed to save settings.';
            $messageType = 'danger';
        }
    }
}

if ($hasCronRunsTable) {
    $dateSafe = mysqli_real_escape_string($DB, $scheduleDate);
    $selectArticle = $hasExamplesTable
        ? "ea.slug AS article_slug, ea.lang_code AS article_lang, ea.title AS article_title"
        : "NULL AS article_slug, NULL AS article_lang, NULL AS article_title";
    $joinArticle = $hasExamplesTable
        ? "LEFT JOIN examples_articles ea ON ea.id = r.article_id"
        : "";
    $sql = "SELECT r.id, r.job_date, r.lang_code, r.slot_index, r.planned_at, r.status, r.attempts, r.article_id, r.message,
                   {$selectArticle}
            FROM seo_article_cron_runs r
            {$joinArticle}
            WHERE r.job_date = '{$dateSafe}'
            ORDER BY r.lang_code ASC, r.slot_index ASC, r.id ASC";
    $res = mysqli_query($DB, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $scheduleRows[] = $row;
        }
        mysqli_free_result($res);
    }
}
