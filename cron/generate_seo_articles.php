<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/controls/examples/_common.php';
$seoGeneratorSettingsLib = DIR . 'core/libs/seo_generator_settings.php';
if (is_file($seoGeneratorSettingsLib)) {
    require_once $seoGeneratorSettingsLib;
}
$pageHtmlCacheLib = DIR . 'core/libs/page_html_cache.php';
if (is_file($pageHtmlCacheLib)) {
    require_once $pageHtmlCacheLib;
}
$indexNowLib = DIR . 'core/libs/indexnow.php';
if (is_file($indexNowLib)) {
    require_once $indexNowLib;
}
$telegramNotifyLib = DIR . 'core/libs/telegram_notify.php';
if (is_file($telegramNotifyLib)) {
    require_once $telegramNotifyLib;
}

$seoCronTimezone = trim((string)($GLOBALS['AppTimezone'] ?? 'Europe/Moscow'));
if ($seoCronTimezone === '' || @date_default_timezone_set($seoCronTimezone) === false) {
    date_default_timezone_set('Europe/Moscow');
}

function seo_runtime_options(): array
{
    $opts = [
        'force' => false,
        'dry_run' => false,
        'proxy_check' => false,
        'backfill_images' => false,
        'backfill_force' => false,
        'job_date' => '',
        'langs' => [],
        'max_per_run' => null,
        'image_limit' => null,
        'campaign' => '',
    ];

    $argv = isset($GLOBALS['argv']) && is_array($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
    foreach ($argv as $arg) {
        if (!is_string($arg) || $arg === '' || strpos($arg, '--') !== 0) {
            continue;
        }
        if ($arg === '--force') {
            $opts['force'] = true;
            continue;
        }
        if ($arg === '--dry-run') {
            $opts['dry_run'] = true;
            continue;
        }
        if ($arg === '--proxy-check') {
            $opts['proxy_check'] = true;
            continue;
        }
        if ($arg === '--backfill-images') {
            $opts['backfill_images'] = true;
            continue;
        }
        if ($arg === '--backfill-force') {
            $opts['backfill_force'] = true;
            continue;
        }
        if (strpos($arg, '--date=') === 0) {
            $date = trim(substr($arg, 7));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $opts['job_date'] = $date;
            }
            continue;
        }
        if (strpos($arg, '--lang=') === 0) {
            $raw = trim(substr($arg, 7));
            if ($raw !== '') {
                $parts = explode(',', $raw);
                foreach ($parts as $part) {
                    $lang = examples_normalize_lang(trim($part));
                    if (!in_array($lang, $opts['langs'], true)) {
                        $opts['langs'][] = $lang;
                    }
                }
            }
            continue;
        }
        if (strpos($arg, '--max-per-run=') === 0) {
            $value = (int)trim(substr($arg, 14));
            if ($value > 0) {
                $opts['max_per_run'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--image-limit=') === 0) {
            $value = (int)trim(substr($arg, 14));
            if ($value > 0) {
                $opts['image_limit'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--campaign=') === 0) {
            $value = strtolower(trim(substr($arg, 11)));
            if (in_array($value, ['journal', 'playbooks'], true)) {
                $opts['campaign'] = $value;
            }
            continue;
        }
    }

    return $opts;
}

function seo_cfg(string $key, $default = null)
{
    return array_key_exists($key, $GLOBALS) ? $GLOBALS[$key] : $default;
}

function seo_apply_db_settings_to_cfg(array $cfg, array $dbSettings): array
{
    $directKeys = [
        'enabled', 'langs', 'daily_min', 'daily_max', 'word_min', 'word_max', 'auto_expand_retries',
        'expand_context_chars', 'author_name', 'domain_host', 'max_per_run', 'seed_salt',
        'tone_variability', 'portfolio_bofu_weight', 'portfolio_mofu_weight', 'portfolio_authority_weight',
        'portfolio_case_weight', 'portfolio_product_weight',
        'domain_host_en', 'domain_host_ru',
        'notify_schedule', 'notify_daily_schedule', 'today_first_delay_min', 'preview_channel_enabled',
        'preview_channel_chat_id', 'preview_image_enabled', 'preview_image_model', 'preview_image_size',
        'preview_image_prompt_template', 'preview_image_anchor_enforced', 'preview_image_anchor_append',
        'preview_post_max_words', 'preview_caption_max_words',
        'preview_post_min_words', 'preview_caption_min_words', 'preview_use_llm', 'preview_llm_model',
        'preview_context_chars', 'prompt_version', 'llm_provider', 'openai_api_key', 'openai_base_url',
        'indexnow_enabled', 'indexnow_key', 'indexnow_key_location', 'indexnow_endpoint',
        'indexnow_hosts', 'indexnow_ping_on_publish', 'indexnow_submit_limit', 'indexnow_retry_delay_minutes',
        'openai_model', 'openai_timeout', 'openai_headers', 'openai_proxy_pool_enabled', 'openai_proxy_pool',
        'topic_analysis_enabled', 'topic_analysis_limit', 'topic_analysis_system_prompt',
        'topic_analysis_user_prompt_append', 'styles_en', 'styles_ru', 'clusters_en', 'clusters_ru', 'moods',
        'intent_verticals_en', 'intent_verticals_ru', 'intent_scenarios_en', 'intent_scenarios_ru',
        'intent_objectives_en', 'intent_objectives_ru', 'intent_constraints_en', 'intent_constraints_ru',
        'intent_artifacts_en', 'intent_artifacts_ru', 'intent_outcomes_en', 'intent_outcomes_ru',
        'service_focus_en', 'service_focus_ru', 'forbidden_topics_en', 'forbidden_topics_ru',
        'article_cluster_taxonomy_en', 'article_cluster_taxonomy_ru',
        'article_structures_en', 'article_structures_ru',
        'article_system_prompt_en', 'article_system_prompt_ru', 'article_user_prompt_append_en',
        'article_user_prompt_append_ru', 'expand_system_prompt_en', 'expand_system_prompt_ru',
        'expand_user_prompt_append_en', 'expand_user_prompt_append_ru', 'preview_image_style_options',
        'image_color_schemes', 'image_scene_families', 'image_compositions', 'image_scenarios',
        'openrouter_api_key', 'openrouter_base_url', 'openrouter_model',
        'openrouter_fallback_model',
    ];
    foreach ($directKeys as $key) {
        if (array_key_exists($key, $dbSettings)) {
            $cfg[$key] = $dbSettings[$key];
        }
    }

    if (array_key_exists('openai_proxy_enabled', $dbSettings)) {
        $cfg['openai_proxy']['enabled'] = (bool)$dbSettings['openai_proxy_enabled'];
    }
    if (array_key_exists('openai_proxy_host', $dbSettings)) {
        $cfg['openai_proxy']['host'] = (string)$dbSettings['openai_proxy_host'];
    }
    if (array_key_exists('openai_proxy_port', $dbSettings)) {
        $cfg['openai_proxy']['port'] = (int)$dbSettings['openai_proxy_port'];
    }
    if (array_key_exists('openai_proxy_type', $dbSettings)) {
        $cfg['openai_proxy']['type'] = (string)$dbSettings['openai_proxy_type'];
    }
    if (array_key_exists('openai_proxy_username', $dbSettings)) {
        $cfg['openai_proxy']['username'] = (string)$dbSettings['openai_proxy_username'];
    }
    if (array_key_exists('openai_proxy_password', $dbSettings)) {
        $cfg['openai_proxy']['password'] = (string)$dbSettings['openai_proxy_password'];
    }

    return $cfg;
}

function seo_proxy_entry_label(array $proxy): string
{
    $host = (string)($proxy['host'] ?? '');
    $port = (int)($proxy['port'] ?? 0);
    if ($host === '' || $port <= 0) {
        return 'direct';
    }
    return $host . ':' . $port;
}

function seo_proxy_from_compact_string(string $raw, string $defaultType = 'http'): ?array
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    $parts = explode(':', $raw);
    if (count($parts) < 2) {
        return null;
    }

    $host = trim((string)($parts[0] ?? ''));
    $port = (int)trim((string)($parts[1] ?? '0'));
    if ($host === '' || $port <= 0) {
        return null;
    }

    $username = count($parts) >= 3 ? trim((string)$parts[2]) : '';
    $password = count($parts) >= 4 ? trim((string)$parts[3]) : '';

    return [
        'enabled' => true,
        'host' => $host,
        'port' => $port,
        'type' => strtolower($defaultType) === 'socks5' ? 'socks5' : 'http',
        'username' => $username,
        'password' => $password,
    ];
}

function seo_normalize_proxy(array $rawProxy, string $fallbackType = 'http'): ?array
{
    $host = trim((string)($rawProxy['host'] ?? ''));
    $port = (int)($rawProxy['port'] ?? 0);
    if ($host === '' || $port <= 0) {
        return null;
    }
    $type = strtolower(trim((string)($rawProxy['type'] ?? $fallbackType)));
    if ($type !== 'socks5') {
        $type = 'http';
    }
    return [
        'enabled' => true,
        'host' => $host,
        'port' => $port,
        'type' => $type,
        'username' => (string)($rawProxy['username'] ?? ''),
        'password' => (string)($rawProxy['password'] ?? ''),
    ];
}

function seo_proxy_candidates(array $cfg): array
{
    $fallbackType = (string)($cfg['openai_proxy']['type'] ?? 'http');
    $poolEnabled = (bool)($cfg['openai_proxy_pool_enabled'] ?? false);
    $poolRaw = is_array($cfg['openai_proxy_pool'] ?? null) ? $cfg['openai_proxy_pool'] : [];

    $proxies = [];
    if ($poolEnabled && !empty($poolRaw)) {
        foreach ($poolRaw as $row) {
            if (is_string($row)) {
                $p = seo_proxy_from_compact_string($row, $fallbackType);
                if ($p !== null) {
                    $proxies[] = $p;
                }
                continue;
            }
            if (is_array($row)) {
                $p = seo_normalize_proxy($row, $fallbackType);
                if ($p !== null) {
                    $proxies[] = $p;
                }
            }
        }
    }

    if (!empty($proxies)) {
        return $proxies;
    }

    $single = seo_normalize_proxy((array)($cfg['openai_proxy'] ?? []), $fallbackType);
    if ($single !== null && (bool)($cfg['openai_proxy']['enabled'] ?? false)) {
        return [$single];
    }

    return [[
        'enabled' => false,
        'host' => '',
        'port' => 0,
        'type' => 'http',
        'username' => '',
        'password' => '',
    ]];
}

function seo_proxy_mode_label(array $cfg, array $proxyCandidates): string
{
    if (!empty($proxyCandidates) && (bool)($proxyCandidates[0]['enabled'] ?? false) === false && count($proxyCandidates) === 1) {
        return 'direct';
    }
    if ((bool)($cfg['openai_proxy_pool_enabled'] ?? false)) {
        return 'pool(' . count($proxyCandidates) . ')';
    }
    return 'single(' . count($proxyCandidates) . ')';
}

function seo_proxy_candidates_with_direct_fallback(array $cfg): array
{
    $candidates = seo_proxy_candidates($cfg);
    $hasDirect = false;
    foreach ($candidates as $candidate) {
        if ((bool)($candidate['enabled'] ?? false) === false) {
            $hasDirect = true;
            break;
        }
    }
    if (!$hasDirect) {
        $candidates[] = [
            'enabled' => false,
            'host' => '',
            'port' => 0,
            'type' => 'http',
            'username' => '',
            'password' => '',
        ];
    }
    return $candidates;
}

function seo_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function seo_table_exists(mysqli $db, string $table): bool
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
    return $res && mysqli_num_rows($res) > 0;
}

function seo_table_has_column(mysqli $db, string $table, string $column): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $columnSafe = mysqli_real_escape_string($db, $column);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
           AND COLUMN_NAME = '{$columnSafe}'
         LIMIT 1"
    );
    return $res && mysqli_num_rows($res) > 0;
}

function seo_ensure_examples_image_columns(mysqli $db): void
{
    if (!seo_table_exists($db, 'examples_articles')) {
        return;
    }
    if (!seo_table_has_column($db, 'examples_articles', 'preview_image_url')) {
        @mysqli_query($db, "ALTER TABLE examples_articles ADD COLUMN preview_image_url VARCHAR(1000) NULL");
    }
    if (!seo_table_has_column($db, 'examples_articles', 'preview_image_data')) {
        @mysqli_query($db, "ALTER TABLE examples_articles ADD COLUMN preview_image_data LONGTEXT NULL");
    }
    if (!seo_table_has_column($db, 'examples_articles', 'preview_image_thumb_url')) {
        @mysqli_query($db, "ALTER TABLE examples_articles ADD COLUMN preview_image_thumb_url VARCHAR(1000) NULL AFTER preview_image_url");
    }
    if (!seo_table_has_column($db, 'examples_articles', 'preview_image_style')) {
        @mysqli_query($db, "ALTER TABLE examples_articles ADD COLUMN preview_image_style VARCHAR(120) NULL AFTER preview_image_thumb_url");
    }
    if (!seo_table_has_column($db, 'examples_articles', 'cluster_code')) {
        @mysqli_query($db, "ALTER TABLE examples_articles ADD COLUMN cluster_code VARCHAR(64) NOT NULL DEFAULT 'general' AFTER lang_code");
        @mysqli_query($db, "ALTER TABLE examples_articles ADD KEY idx_examples_cluster_code (cluster_code)");
    }
    if (!seo_table_has_column($db, 'examples_articles', 'material_section')) {
        @mysqli_query($db, "ALTER TABLE examples_articles ADD COLUMN material_section VARCHAR(32) NOT NULL DEFAULT 'journal' AFTER cluster_code");
        @mysqli_query($db, "ALTER TABLE examples_articles ADD KEY idx_examples_material_section (material_section)");
    }
}

function seo_ensure_generator_logs_table(mysqli $db): void
{
    $ok = mysqli_query(
        $db,
        "CREATE TABLE IF NOT EXISTS seo_generator_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            job_date DATE NULL,
            lang_code VARCHAR(5) NOT NULL DEFAULT 'en',
            slot_index INT UNSIGNED NOT NULL DEFAULT 0,
            status VARCHAR(24) NOT NULL DEFAULT 'success',
            is_dry_run TINYINT(1) NOT NULL DEFAULT 0,
            article_id INT UNSIGNED NULL,
            title VARCHAR(255) NOT NULL DEFAULT '',
            slug VARCHAR(255) NOT NULL DEFAULT '',
            article_url VARCHAR(1024) NOT NULL DEFAULT '',
            words_final INT UNSIGNED NOT NULL DEFAULT 0,
            words_initial INT UNSIGNED NOT NULL DEFAULT 0,
            structure_used VARCHAR(1024) NOT NULL DEFAULT '',
            topic_analysis_source VARCHAR(64) NOT NULL DEFAULT '',
            topic_analysis_summary TEXT NULL,
            topic_bans_count INT UNSIGNED NOT NULL DEFAULT 0,
            image_request_json LONGTEXT NULL,
            image_result_json LONGTEXT NULL,
            article_request_json LONGTEXT NULL,
            article_result_json LONGTEXT NULL,
            llm_usage_json LONGTEXT NULL,
            llm_requests_count INT UNSIGNED NOT NULL DEFAULT 0,
            llm_prompt_tokens INT UNSIGNED NOT NULL DEFAULT 0,
            llm_completion_tokens INT UNSIGNED NOT NULL DEFAULT 0,
            llm_total_tokens INT UNSIGNED NOT NULL DEFAULT 0,
            settings_snapshot_json LONGTEXT NULL,
            tg_preview_result_json LONGTEXT NULL,
            error_message TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_seo_gen_logs_created (created_at),
            KEY idx_seo_gen_logs_lang_date (lang_code, job_date),
            KEY idx_seo_gen_logs_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    if (!$ok) {
        seo_echo('Generator logs table ensure failed: ' . mysqli_error($db));
        return;
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'tg_preview_result_json')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN tg_preview_result_json LONGTEXT NULL AFTER settings_snapshot_json")) {
            seo_echo('Generator logs table alter failed (tg_preview_result_json): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'article_request_json')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN article_request_json LONGTEXT NULL AFTER image_result_json")) {
            seo_echo('Generator logs table alter failed (article_request_json): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'article_result_json')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN article_result_json LONGTEXT NULL AFTER article_request_json")) {
            seo_echo('Generator logs table alter failed (article_result_json): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'llm_usage_json')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN llm_usage_json LONGTEXT NULL AFTER article_result_json")) {
            seo_echo('Generator logs table alter failed (llm_usage_json): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'llm_requests_count')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN llm_requests_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_usage_json")) {
            seo_echo('Generator logs table alter failed (llm_requests_count): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'llm_prompt_tokens')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN llm_prompt_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_requests_count")) {
            seo_echo('Generator logs table alter failed (llm_prompt_tokens): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'llm_completion_tokens')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN llm_completion_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_prompt_tokens")) {
            seo_echo('Generator logs table alter failed (llm_completion_tokens): ' . mysqli_error($db));
        }
    }
    if (!seo_table_has_column($db, 'seo_generator_logs', 'llm_total_tokens')) {
        if (!mysqli_query($db, "ALTER TABLE seo_generator_logs ADD COLUMN llm_total_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_completion_tokens")) {
            seo_echo('Generator logs table alter failed (llm_total_tokens): ' . mysqli_error($db));
        }
    }
}

function seo_ensure_topic_analysis_cache_table(mysqli $db): void
{
    $ok = mysqli_query(
        $db,
        "CREATE TABLE IF NOT EXISTS seo_topic_analysis_cache (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            job_date DATE NOT NULL,
            lang_code VARCHAR(5) NOT NULL,
            domain_host VARCHAR(190) NOT NULL,
            settings_hash VARCHAR(64) NOT NULL,
            analysis_json LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_topic_analysis_cache (job_date, lang_code, domain_host, settings_hash),
            KEY idx_topic_analysis_lookup (job_date, lang_code, domain_host)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    if (!$ok) {
        seo_echo('Topic analysis cache table ensure failed: ' . mysqli_error($db));
    }
}

function seo_topic_analysis_settings_hash(array $cfg, string $lang, string $domainHost): string
{
    $parts = [
        'v1',
        (string)($cfg['llm_provider'] ?? ''),
        (string)($cfg['openai_model'] ?? ''),
        (string)($cfg['openai_base_url'] ?? ''),
        (int)($cfg['topic_analysis_limit'] ?? 120),
        trim((string)($cfg['topic_analysis_system_prompt'] ?? '')),
        trim((string)($cfg['topic_analysis_user_prompt_append'] ?? '')),
        $lang,
        $domainHost,
    ];
    return hash('sha256', implode('|', $parts));
}

function seo_topic_analysis_cache_load(mysqli $db, string $jobDate, string $lang, string $domainHost, string $settingsHash): ?array
{
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $domainSafe = mysqli_real_escape_string($db, $domainHost);
    $hashSafe = mysqli_real_escape_string($db, $settingsHash);
    $res = mysqli_query(
        $db,
        "SELECT analysis_json
         FROM seo_topic_analysis_cache
         WHERE job_date = '{$jobDateSafe}'
           AND lang_code = '{$langSafe}'
           AND domain_host = '{$domainSafe}'
           AND settings_hash = '{$hashSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return null;
    }
    $row = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    if (!is_array($row)) {
        return null;
    }
    $decoded = json_decode((string)($row['analysis_json'] ?? ''), true);
    if (!is_array($decoded)) {
        return null;
    }
    return $decoded;
}

function seo_topic_analysis_cache_save(mysqli $db, string $jobDate, string $lang, string $domainHost, string $settingsHash, array $analysis): void
{
    $payload = json_encode($analysis, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($payload) || $payload === '') {
        return;
    }
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $domainSafe = mysqli_real_escape_string($db, $domainHost);
    $hashSafe = mysqli_real_escape_string($db, $settingsHash);
    $payloadSafe = mysqli_real_escape_string($db, $payload);
    @mysqli_query(
        $db,
        "INSERT INTO seo_topic_analysis_cache (job_date, lang_code, domain_host, settings_hash, analysis_json, created_at, updated_at)
         VALUES ('{$jobDateSafe}', '{$langSafe}', '{$domainSafe}', '{$hashSafe}', '{$payloadSafe}', NOW(), NOW())
         ON DUPLICATE KEY UPDATE analysis_json = VALUES(analysis_json), updated_at = NOW()"
    );
}

function seo_log_generation(mysqli $db, array $row): void
{
    $jobDate = trim((string)($row['job_date'] ?? ''));
    $lang = examples_normalize_lang((string)($row['lang_code'] ?? 'en'));
    $slotIndex = max(0, (int)($row['slot_index'] ?? 0));
    $status = trim((string)($row['status'] ?? 'success'));
    $dry = !empty($row['is_dry_run']) ? 1 : 0;
    $articleId = isset($row['article_id']) ? (int)$row['article_id'] : 0;
    $title = trim((string)($row['title'] ?? ''));
    $slug = trim((string)($row['slug'] ?? ''));
    $articleUrl = trim((string)($row['article_url'] ?? ''));
    $wordsFinal = max(0, (int)($row['words_final'] ?? 0));
    $wordsInitial = max(0, (int)($row['words_initial'] ?? 0));
    $structureUsed = trim((string)($row['structure_used'] ?? ''));
    $topicSource = trim((string)($row['topic_analysis_source'] ?? ''));
    $topicSummary = trim((string)($row['topic_analysis_summary'] ?? ''));
    $topicBansCount = max(0, (int)($row['topic_bans_count'] ?? 0));
    $imageReqJson = json_encode((array)($row['image_request'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $imageResJson = json_encode((array)($row['image_result'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $articleReqJson = json_encode((array)($row['article_request'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $articleResJson = json_encode((array)($row['article_result'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $llmUsageJson = json_encode((array)($row['llm_usage'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $llmRequestsCount = max(0, (int)($row['llm_requests_count'] ?? 0));
    $llmPromptTokens = max(0, (int)($row['llm_prompt_tokens'] ?? 0));
    $llmCompletionTokens = max(0, (int)($row['llm_completion_tokens'] ?? 0));
    $llmTotalTokens = max(0, (int)($row['llm_total_tokens'] ?? 0));
    $settingsSnapshotJson = json_encode((array)($row['settings_snapshot'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tgPreviewJson = json_encode((array)($row['tg_preview_result'] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $errorMessage = trim((string)($row['error_message'] ?? ''));

    $jobDateSql = preg_match('/^\d{4}-\d{2}-\d{2}$/', $jobDate) ? "'" . mysqli_real_escape_string($db, $jobDate) . "'" : 'NULL';
    $articleIdSql = $articleId > 0 ? (string)$articleId : 'NULL';
    $titleSafe = mysqli_real_escape_string($db, mb_substr($title, 0, 255));
    $slugSafe = mysqli_real_escape_string($db, mb_substr($slug, 0, 255));
    $urlSafe = mysqli_real_escape_string($db, mb_substr($articleUrl, 0, 1024));
    $structureSafe = mysqli_real_escape_string($db, mb_substr($structureUsed, 0, 1024));
    $statusSafe = mysqli_real_escape_string($db, mb_substr($status, 0, 24));
    $langSafe = mysqli_real_escape_string($db, $lang);
    $topicSourceSafe = mysqli_real_escape_string($db, mb_substr($topicSource, 0, 64));
    $topicSummarySafe = mysqli_real_escape_string($db, $topicSummary);
    $imageReqSafe = mysqli_real_escape_string($db, (string)$imageReqJson);
    $imageResSafe = mysqli_real_escape_string($db, (string)$imageResJson);
    $articleReqSafe = mysqli_real_escape_string($db, (string)$articleReqJson);
    $articleResSafe = mysqli_real_escape_string($db, (string)$articleResJson);
    $llmUsageSafe = mysqli_real_escape_string($db, (string)$llmUsageJson);
    $snapshotSafe = mysqli_real_escape_string($db, (string)$settingsSnapshotJson);
    $tgPreviewSafe = mysqli_real_escape_string($db, (string)$tgPreviewJson);
    $errorSafe = mysqli_real_escape_string($db, $errorMessage);
    $hasTgPreviewColumn = seo_table_has_column($db, 'seo_generator_logs', 'tg_preview_result_json');
    $hasArticleRequestColumn = seo_table_has_column($db, 'seo_generator_logs', 'article_request_json');
    $hasArticleResultColumn = seo_table_has_column($db, 'seo_generator_logs', 'article_result_json');
    $hasLlmUsageColumn = seo_table_has_column($db, 'seo_generator_logs', 'llm_usage_json');
    $hasLlmRequestsCountColumn = seo_table_has_column($db, 'seo_generator_logs', 'llm_requests_count');
    $hasLlmPromptTokensColumn = seo_table_has_column($db, 'seo_generator_logs', 'llm_prompt_tokens');
    $hasLlmCompletionTokensColumn = seo_table_has_column($db, 'seo_generator_logs', 'llm_completion_tokens');
    $hasLlmTotalTokensColumn = seo_table_has_column($db, 'seo_generator_logs', 'llm_total_tokens');
    $sql = "INSERT INTO seo_generator_logs (
            job_date, lang_code, slot_index, status, is_dry_run, article_id, title, slug, article_url,
            words_final, words_initial, structure_used, topic_analysis_source, topic_analysis_summary, topic_bans_count,
            image_request_json, image_result_json"
            . ($hasArticleRequestColumn ? ", article_request_json" : "")
            . ($hasArticleResultColumn ? ", article_result_json" : "")
            . ($hasLlmUsageColumn ? ", llm_usage_json" : "")
            . ($hasLlmRequestsCountColumn ? ", llm_requests_count" : "")
            . ($hasLlmPromptTokensColumn ? ", llm_prompt_tokens" : "")
            . ($hasLlmCompletionTokensColumn ? ", llm_completion_tokens" : "")
            . ($hasLlmTotalTokensColumn ? ", llm_total_tokens" : "")
            . ", settings_snapshot_json"
            . ($hasTgPreviewColumn ? ", tg_preview_result_json" : "") .
            ", error_message, created_at
        ) VALUES (
            {$jobDateSql}, '{$langSafe}', {$slotIndex}, '{$statusSafe}', {$dry}, {$articleIdSql}, '{$titleSafe}', '{$slugSafe}', '{$urlSafe}',
            {$wordsFinal}, {$wordsInitial}, '{$structureSafe}', '{$topicSourceSafe}', '{$topicSummarySafe}', {$topicBansCount},
            '{$imageReqSafe}', '{$imageResSafe}'"
            . ($hasArticleRequestColumn ? ", '{$articleReqSafe}'" : "")
            . ($hasArticleResultColumn ? ", '{$articleResSafe}'" : "")
            . ($hasLlmUsageColumn ? ", '{$llmUsageSafe}'" : "")
            . ($hasLlmRequestsCountColumn ? ", {$llmRequestsCount}" : "")
            . ($hasLlmPromptTokensColumn ? ", {$llmPromptTokens}" : "")
            . ($hasLlmCompletionTokensColumn ? ", {$llmCompletionTokens}" : "")
            . ($hasLlmTotalTokensColumn ? ", {$llmTotalTokens}" : "")
            . ", '{$snapshotSafe}'"
            . ($hasTgPreviewColumn ? ", '{$tgPreviewSafe}'" : "") .
            ", '{$errorSafe}', NOW()
        )";
    if (!mysqli_query($db, $sql)) {
        seo_echo('Generator log insert failed: ' . mysqli_error($db));
    }
}

function seo_article_public_url(string $lang, string $slug, string $clusterCode = ''): string
{
    $host = seo_host_for_lang($lang);
    $lang = examples_normalize_lang($lang);
    $slug = trim((string)$slug);
    if ($slug === '') {
        return '';
    }
    $clusterCode = examples_normalize_cluster((string)$clusterCode, $lang);
    if ($clusterCode === '') {
        return 'https://' . $host . '/blog/' . rawurlencode($slug);
    }
    return 'https://' . $host . '/blog/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug);
}

function seo_public_image_url_for_lang(string $lang, string $rawUrl): string
{
    $rawUrl = trim($rawUrl);
    if ($rawUrl === '') {
        return '';
    }
    if (stripos($rawUrl, 'data:image/') === 0) {
        return $rawUrl;
    }

    if (preg_match('/^https?:\/\//i', $rawUrl)) {
        return $rawUrl;
    }

    if ($rawUrl[0] === '/') {
        return 'https://' . seo_host_for_lang($lang) . $rawUrl;
    }

    return $rawUrl;
}
function seo_indexnow_enqueue_article_url(mysqli $db, array $cfg, string $url, string $lang, string $eventType = 'publish'): void
{
    if (!(bool)($cfg['indexnow_enabled'] ?? false)) {
        return;
    }
    if (!(bool)($cfg['indexnow_ping_on_publish'] ?? true)) {
        return;
    }
    if (!function_exists('indexnow_queue_enqueue') || !function_exists('indexnow_clean_host')) {
        return;
    }
    if (!function_exists('indexnow_is_valid_absolute_url') || !indexnow_is_valid_absolute_url($url)) {
        return;
    }
    $allowedHosts = array_values(array_filter(array_map('indexnow_clean_host', (array)($cfg['indexnow_hosts'] ?? []))));
    $urlHost = indexnow_clean_host((string)parse_url($url, PHP_URL_HOST));
    if (!empty($allowedHosts) && !in_array($urlHost, $allowedHosts, true)) {
        return;
    }
    $ok = indexnow_queue_enqueue($db, $url, [
        'lang_code' => $lang,
        'source' => 'seo_generator',
        'event_type' => $eventType,
    ]);
    if ($ok) {
        seo_echo('IndexNow queued: ' . $url);
    }
}

function seo_host_for_lang(string $lang): string
{
    $lang = examples_normalize_lang($lang);
    $hosts = $GLOBALS['SeoArticleHosts'] ?? [];
    $host = '';
    if (is_array($hosts)) {
        $host = trim((string)($hosts[$lang] ?? ''));
    }
    if ($host === '') {
        $host = trim((string)($GLOBALS['SeoArticlePrimaryHost'] ?? ''));
    }
    if ($host === '') {
        $host = 'cpalnya.ru';
    }
    $host = strtolower(preg_replace('/^www\./i', '', $host));
    return $host !== '' ? $host : 'cpalnya.ru';
}

function seo_fetch_articles_missing_images(mysqli $db, int $limit, array $langs = [], bool $forceRebuild = false): array
{
    $limit = max(1, min(200, $limit));
    $hasLang = examples_table_has_lang_column($db);
    $hasPreviewUrl = seo_table_has_column($db, 'examples_articles', 'preview_image_url');
    $hasPreviewData = seo_table_has_column($db, 'examples_articles', 'preview_image_data');
    $hasPreviewThumb = seo_table_has_column($db, 'examples_articles', 'preview_image_thumb_url');
    $hasPreviewStyle = seo_table_has_column($db, 'examples_articles', 'preview_image_style');
    if (!$hasPreviewUrl && !$hasPreviewData) {
        return [];
    }
    $styleSelect = $hasPreviewStyle ? 'preview_image_style' : "'' AS preview_image_style";
    $thumbSelect = $hasPreviewThumb ? 'preview_image_thumb_url' : "'' AS preview_image_thumb_url";
    $urlSelect = $hasPreviewUrl ? 'preview_image_url' : "'' AS preview_image_url";
    $dataSelect = $hasPreviewData ? 'preview_image_data' : "'' AS preview_image_data";
    $langSelect = $hasLang ? 'lang_code' : "'en' AS lang_code";

    $where = ["is_published = 1"];
    if ($forceRebuild) {
        if ($hasPreviewUrl && $hasPreviewData) {
            $where[] = "(COALESCE(preview_image_data, '') <> '' OR COALESCE(preview_image_url, '') <> '')";
        } elseif ($hasPreviewData) {
            $where[] = "COALESCE(preview_image_data, '') <> ''";
        } else {
            $where[] = "COALESCE(preview_image_url, '') <> ''";
        }
    } else {
        $conditions = [];
        if ($hasPreviewUrl && $hasPreviewData) {
            $conditions[] = "(COALESCE(preview_image_data, '') = '' AND COALESCE(preview_image_url, '') = '')";
            // Existing base64/url images may still need local file+thumbnail migration.
            $conditions[] = "COALESCE(preview_image_data, '') <> ''";
        } elseif ($hasPreviewData) {
            $conditions[] = "COALESCE(preview_image_data, '') = ''";
            $conditions[] = "COALESCE(preview_image_data, '') <> ''";
        } else {
            $conditions[] = "COALESCE(preview_image_url, '') = ''";
        }
        if ($hasPreviewThumb && ($hasPreviewUrl || $hasPreviewData)) {
            $conditions[] = "((COALESCE(preview_image_data, '') <> '' OR COALESCE(preview_image_url, '') <> '') AND COALESCE(preview_image_thumb_url, '') = '')";
        }
        $where[] = '(' . implode(' OR ', array_unique($conditions)) . ')';
    }
    if ($hasLang && !empty($langs)) {
        $langParts = [];
        foreach ($langs as $langRaw) {
            $lang = examples_normalize_lang((string)$langRaw);
            $langParts[] = "'" . mysqli_real_escape_string($db, $lang) . "'";
        }
        $langParts = array_values(array_unique($langParts));
        if (!empty($langParts)) {
            $where[] = 'lang_code IN (' . implode(',', $langParts) . ')';
        }
    }

    $sql = "SELECT id, title, slug, excerpt_html, content_html, {$langSelect}, {$styleSelect}, {$urlSelect}, {$dataSelect}, {$thumbSelect}
            FROM examples_articles
            WHERE " . implode(' AND ', $where) . "
            ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
            LIMIT {$limit}";
    $rows = [];
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        mysqli_free_result($res);
    }
    return $rows;
}

function seo_update_article_preview_image(
    mysqli $db,
    int $articleId,
    string $imageUrl,
    string $imageData,
    string $imageStyle,
    string $thumbUrl = ''
): bool
{
    $articleId = max(1, $articleId);
    [$imageUrl, $imageData] = seo_normalize_preview_image_fields($imageUrl, $imageData, 1000);
    $urlSafe = mysqli_real_escape_string($db, $imageUrl);
    $dataSafe = mysqli_real_escape_string($db, $imageData);
    $styleSafe = mysqli_real_escape_string($db, $imageStyle);
    $thumbSafe = mysqli_real_escape_string($db, trim($thumbUrl));

    $sets = [];
    if (seo_table_has_column($db, 'examples_articles', 'preview_image_url')) {
        $sets[] = "preview_image_url = '{$urlSafe}'";
    }
    if (seo_table_has_column($db, 'examples_articles', 'preview_image_thumb_url')) {
        $sets[] = "preview_image_thumb_url = '{$thumbSafe}'";
    }
    if (seo_table_has_column($db, 'examples_articles', 'preview_image_data')) {
        $sets[] = "preview_image_data = '{$dataSafe}'";
    }
    if (seo_table_has_column($db, 'examples_articles', 'preview_image_style')) {
        $sets[] = "preview_image_style = '{$styleSafe}'";
    }
    if (seo_table_has_column($db, 'examples_articles', 'updated_at')) {
        $sets[] = "updated_at = NOW()";
    }
    if (empty($sets)) {
        return false;
    }
    $sql = "UPDATE examples_articles SET " . implode(', ', $sets) . " WHERE id = {$articleId} LIMIT 1";
    return mysqli_query($db, $sql) !== false;
}

function seo_normalize_preview_image_fields(string $imageUrl, string $imageData, int $maxUrlLen = 1000): array
{
    $imageUrl = trim($imageUrl);
    $imageData = trim($imageData);

    if ($imageUrl !== '' && stripos($imageUrl, 'data:image/') === 0) {
        if ($imageData === '') {
            $imageData = $imageUrl;
        }
        $imageUrl = '';
    }

    if ($imageUrl !== '') {
        $urlLen = function_exists('mb_strlen') ? mb_strlen($imageUrl, '8bit') : strlen($imageUrl);
        if ($urlLen > max(64, $maxUrlLen)) {
            if ($imageData === '') {
                $imageData = $imageUrl;
            }
            $imageUrl = '';
        }
    }

    return [$imageUrl, $imageData];
}

function seo_guess_extension_from_mime(string $mime): string
{
    $mime = strtolower(trim($mime));
    if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
        return 'jpg';
    }
    if ($mime === 'image/png') {
        return 'png';
    }
    if ($mime === 'image/webp') {
        return 'webp';
    }
    if ($mime === 'image/gif') {
        return 'gif';
    }
    return 'png';
}

function seo_preview_assets_host(): string
{
    $host = trim((string)($GLOBALS['SeoArticlePrimaryHost'] ?? ''));
    if ($host === '') {
        $host = trim((string)($_SERVER['HTTP_HOST'] ?? 'cpalnya.ru'));
    }
    $host = strtolower(preg_replace('/^www\./i', '', $host));
    return $host !== '' ? $host : 'cpalnya.ru';
}

function seo_preview_assets_url_from_relative(string $relative): string
{
    $relative = '/' . ltrim($relative, '/');
    $host = seo_preview_assets_host();
    return 'https://' . $host . $relative;
}

function seo_decode_data_url_image(string $dataUrl, ?string &$mimeOut = null): string
{
    $mimeOut = null;
    $dataUrl = trim($dataUrl);
    if (!preg_match('#^data:(image/[a-zA-Z0-9.+-]+);base64,(.+)$#s', $dataUrl, $m)) {
        return '';
    }
    $mimeOut = strtolower(trim((string)$m[1]));
    $bin = base64_decode((string)$m[2], true);
    if (!is_string($bin) || $bin === '') {
        return '';
    }
    return $bin;
}

function seo_thumb_make_from_binary(string $binary, string $targetPath, int $targetW = 480, int $targetH = 270): bool
{
    if (!function_exists('imagecreatefromstring')) {
        return false;
    }
    $src = @imagecreatefromstring($binary);
    if (!$src) {
        return false;
    }
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    if ($srcW <= 0 || $srcH <= 0) {
        imagedestroy($src);
        return false;
    }

    $scale = max($targetW / $srcW, $targetH / $srcH);
    $cropW = (int)round($targetW / $scale);
    $cropH = (int)round($targetH / $scale);
    $cropX = max(0, (int)floor(($srcW - $cropW) / 2));
    $cropY = max(0, (int)floor(($srcH - $cropH) / 2));

    $dst = imagecreatetruecolor($targetW, $targetH);
    if (!$dst) {
        imagedestroy($src);
        return false;
    }

    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $transparent);

    $ok = imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
    if ($ok) {
        $ok = imagejpeg($dst, $targetPath, 86);
    }
    imagedestroy($src);
    imagedestroy($dst);
    return (bool)$ok;
}

function seo_store_preview_assets_on_disk(array $cfg, int $articleId, string $slug, string $imageUrl, string $imageData): array
{
    $out = [
        'stored' => false,
        'image_url' => trim($imageUrl),
        'thumb_url' => '',
        'image_data' => trim($imageData),
    ];

    $binary = '';
    $mime = '';
    if ($out['image_data'] !== '') {
        $binary = seo_decode_data_url_image($out['image_data'], $mime);
    }
    if ($binary === '' && $out['image_url'] !== '') {
        $dataUrl = seo_fetch_image_data_url_with_fallback($out['image_url'], $cfg);
        if ($dataUrl !== '') {
            $out['image_data'] = $dataUrl;
            $binary = seo_decode_data_url_image($dataUrl, $mime);
        }
    }
    if ($binary === '') {
        return $out;
    }

    $ext = seo_guess_extension_from_mime((string)$mime);
    $baseSlug = examples_slugify($slug !== '' ? $slug : ('article-' . $articleId));
    $stamp = gmdate('YmdHis');
    $name = $baseSlug . '-' . $articleId . '-' . $stamp;
    $fullDirAbs = DIR . 'cache/examples_previews/full';
    $thumbDirAbs = DIR . 'cache/examples_previews/thumb';
    if (!is_dir($fullDirAbs)) {
        @mkdir($fullDirAbs, 0775, true);
    }
    if (!is_dir($thumbDirAbs)) {
        @mkdir($thumbDirAbs, 0775, true);
    }
    $fullAbs = $fullDirAbs . '/' . $name . '.' . $ext;
    $thumbAbs = $thumbDirAbs . '/' . $name . '.jpg';

    if (@file_put_contents($fullAbs, $binary) === false) {
        return $out;
    }
    $thumbOk = seo_thumb_make_from_binary($binary, $thumbAbs, 480, 270);

    $out['stored'] = true;
    $out['image_url'] = seo_preview_assets_url_from_relative('/cache/examples_previews/full/' . $name . '.' . $ext);
    $out['image_data'] = '';
    if ($thumbOk) {
        $out['thumb_url'] = seo_preview_assets_url_from_relative('/cache/examples_previews/thumb/' . $name . '.jpg');
    }
    return $out;
}

function seo_backfill_missing_images(mysqli $db, array $cfg, array $runtime): int
{
    if (!(bool)($cfg['preview_image_enabled'] ?? false)) {
        seo_echo('Backfill images: preview_image_enabled is false in settings.');
        return 0;
    }
    if (trim((string)($cfg['preview_image_model'] ?? '')) === '') {
        seo_echo('Backfill images: preview_image_model is empty in settings.');
        return 0;
    }

    $limit = (int)($runtime['image_limit'] ?? 0);
    if ($limit <= 0) {
        $limit = max(1, min(50, (int)($cfg['max_per_run'] ?? 5)));
    } else {
        $limit = max(1, min(200, $limit));
    }

    $langs = !empty($runtime['langs']) ? (array)$runtime['langs'] : (array)($cfg['langs'] ?? []);
    $forceRebuild = !empty($runtime['backfill_force']);
    $rows = seo_fetch_articles_missing_images($db, $limit, $langs, $forceRebuild);
    seo_echo(
        'Backfill images: found ' . count($rows)
        . ' articles to process'
        . ($forceRebuild ? ' (force mode)' : '')
        . '.'
    );
    if (empty($rows)) {
        return 0;
    }

    $processed = 0;
    $updated = 0;
    foreach ($rows as $row) {
        $processed++;
        $articleId = (int)($row['id'] ?? 0);
        $title = trim((string)($row['title'] ?? ''));
        $slug = trim((string)($row['slug'] ?? ''));
        $lang = examples_normalize_lang((string)($row['lang_code'] ?? 'en'));
        $currentImageUrl = trim((string)($row['preview_image_url'] ?? ''));
        $currentImageData = trim((string)($row['preview_image_data'] ?? ''));
        $currentThumbUrl = trim((string)($row['preview_image_thumb_url'] ?? ''));
        if ($articleId <= 0 || $title === '') {
            continue;
        }
        $article = [
            'title' => $title,
            'excerpt_html' => (string)($row['excerpt_html'] ?? ''),
            'content_html' => (string)($row['content_html'] ?? ''),
            'preview_image_style' => (string)($row['preview_image_style'] ?? ''),
        ];

        try {
            $imageAsset = ['request' => ['mode' => 'reuse_existing'], 'result' => ['status' => 'reuse_existing']];
            $previewImageUrl = $currentImageUrl;
            $previewImageData = $currentImageData;
            $previewImageStyle = trim((string)($row['preview_image_style'] ?? ''));
            $previewThumbUrl = $currentThumbUrl;

            if ($previewImageUrl === '' && $previewImageData === '') {
                $imageAsset = seo_generate_image_asset($cfg, $lang, $article, (string)($row['preview_image_style'] ?? ''));
                $previewImageUrl = trim((string)($imageAsset['url'] ?? ''));
                $previewImageData = trim((string)($imageAsset['data_url'] ?? ''));
                if ($previewImageData === '' && $previewImageUrl !== '') {
                    $previewImageData = seo_fetch_image_data_url_with_fallback($previewImageUrl, $cfg);
                }
                $previewImageStyle = trim((string)($imageAsset['style'] ?? (string)($row['preview_image_style'] ?? '')));
            }
            $ok = ($previewImageUrl !== '' || $previewImageData !== '');
            if ($ok) {
                $stored = seo_store_preview_assets_on_disk($cfg, $articleId, $slug, $previewImageUrl, $previewImageData);
                if (!empty($stored['stored'])) {
                    $previewImageUrl = (string)($stored['image_url'] ?? $previewImageUrl);
                    $previewImageData = (string)($stored['image_data'] ?? '');
                    $previewThumbUrl = (string)($stored['thumb_url'] ?? $previewThumbUrl);
                }
            }
            if ($ok && !$runtime['dry_run']) {
                $ok = seo_update_article_preview_image($db, $articleId, $previewImageUrl, $previewImageData, $previewImageStyle, $previewThumbUrl);
            }
            if ($ok) {
                $updated++;
            }

            seo_log_generation($db, [
                'job_date' => date('Y-m-d'),
                'lang_code' => $lang,
                'slot_index' => 0,
                'status' => $ok ? 'success' : 'failed',
                'is_dry_run' => !empty($runtime['dry_run']) ? 1 : 0,
                'article_id' => $articleId,
                'title' => $title,
                'slug' => $slug,
                'article_url' => seo_article_public_url($lang, $slug, (string)($row['cluster_code'] ?? '')),
                'words_final' => 0,
                'words_initial' => 0,
                'structure_used' => '',
                'topic_analysis_source' => 'image_backfill',
                'topic_analysis_summary' => '',
                'topic_bans_count' => 0,
                'image_request' => (array)($imageAsset['request'] ?? []),
                'image_result' => (array)($imageAsset['result'] ?? []),
                'settings_snapshot' => [
                    'mode' => 'image_backfill',
                    'preview_image_model' => (string)($cfg['preview_image_model'] ?? ''),
                    'preview_image_size' => (string)($cfg['preview_image_size'] ?? ''),
                    'llm_provider' => (string)($cfg['llm_provider'] ?? ''),
                ],
                'tg_preview_result' => ['status' => 'not_applicable'],
                'error_message' => $ok ? '' : 'Image generation returned empty asset',
            ]);
            seo_admin_notification_create(
                $db,
                $ok ? 'seo_image_backfill_success' : 'seo_image_backfill_failed',
                $ok ? 'Image restored for article' : 'Image restore failed for article',
                '#' . $articleId . ' ' . $title . ($runtime['dry_run'] ? ' (dry-run)' : ''),
                '/adminpanel/examples/',
                [
                    'mode' => 'image_backfill',
                    'article_id' => $articleId,
                    'title' => $title,
                    'slug' => $slug,
                    'lang' => $lang,
                    'dry_run' => !empty($runtime['dry_run']) ? 1 : 0,
                    'status' => $ok ? 'success' : 'failed',
                    'image_result' => (array)($imageAsset['result'] ?? []),
                ],
                'image_backfill_' . ($ok ? 'success' : 'failed') . '_' . $articleId . '_' . date('YmdHis')
            );
            seo_echo(
                'Backfill images: article #' . $articleId . ' "' . $title . '" -> '
                . ($ok ? ($runtime['dry_run'] ? 'dry-run OK' : 'updated') : 'failed')
            );
        } catch (Throwable $e) {
            seo_log_generation($db, [
                'job_date' => date('Y-m-d'),
                'lang_code' => $lang,
                'slot_index' => 0,
                'status' => 'failed',
                'is_dry_run' => !empty($runtime['dry_run']) ? 1 : 0,
                'article_id' => $articleId,
                'title' => $title,
                'slug' => $slug,
                'article_url' => seo_article_public_url($lang, $slug, (string)($row['cluster_code'] ?? '')),
                'words_final' => 0,
                'words_initial' => 0,
                'structure_used' => '',
                'topic_analysis_source' => 'image_backfill',
                'topic_analysis_summary' => '',
                'topic_bans_count' => 0,
                'image_request' => [],
                'image_result' => [],
                'settings_snapshot' => [
                    'mode' => 'image_backfill',
                    'preview_image_model' => (string)($cfg['preview_image_model'] ?? ''),
                    'preview_image_size' => (string)($cfg['preview_image_size'] ?? ''),
                    'llm_provider' => (string)($cfg['llm_provider'] ?? ''),
                ],
                'tg_preview_result' => ['status' => 'not_applicable'],
                'error_message' => $e->getMessage(),
            ]);
            seo_admin_notification_create(
                $db,
                'seo_image_backfill_failed',
                'Image restore failed for article',
                '#' . $articleId . ' ' . $title . ': ' . mb_substr($e->getMessage(), 0, 400),
                '/adminpanel/examples/',
                [
                    'mode' => 'image_backfill',
                    'article_id' => $articleId,
                    'title' => $title,
                    'slug' => $slug,
                    'lang' => $lang,
                    'dry_run' => !empty($runtime['dry_run']) ? 1 : 0,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ],
                'image_backfill_exception_' . $articleId . '_' . date('YmdHis')
            );
            seo_echo('Backfill images: article #' . $articleId . ' failed - ' . $e->getMessage());
        }
    }

    seo_admin_notification_create(
        $db,
        'seo_image_backfill_summary',
        'Image backfill run finished',
        'processed=' . $processed . ', updated=' . $updated . ($runtime['dry_run'] ? ' (dry-run)' : ''),
        '/adminpanel/seo-generator-logs/',
        [
            'mode' => 'image_backfill',
            'processed' => $processed,
            'updated' => $updated,
            'dry_run' => !empty($runtime['dry_run']) ? 1 : 0,
        ],
        'image_backfill_summary_' . date('YmdHis')
    );
    seo_echo('Backfill images: processed=' . $processed . ', updated=' . $updated . ($runtime['dry_run'] ? ' (dry-run)' : ''));
    return $updated;
}

function seo_admin_notification_create(
    mysqli $db,
    string $type,
    string $title,
    string $message,
    string $linkUrl = '',
    array $payload = [],
    string $eventKey = ''
): bool {
    if (!seo_admin_notifications_ensure_schema($db)) {
        return false;
    }

    $hasLink = seo_table_has_column($db, 'admin_notifications', 'link_url');
    $hasPayload = seo_table_has_column($db, 'admin_notifications', 'payload_json');
    $hasEventKey = seo_table_has_column($db, 'admin_notifications', 'event_key');

    if ($hasEventKey && $eventKey !== '') {
        $eventKeySafe = mysqli_real_escape_string($db, $eventKey);
        $existsRes = mysqli_query(
            $db,
            "SELECT id
             FROM admin_notifications
             WHERE event_key = '{$eventKeySafe}'
             LIMIT 1"
        );
        if ($existsRes && mysqli_num_rows($existsRes) > 0) {
            return true;
        }
    }

    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($payloadJson)) {
        $payloadJson = '{}';
    }

    $columns = ['type', 'title', 'message', 'is_read', 'created_at'];
    $values = [
        "'" . mysqli_real_escape_string($db, $type) . "'",
        "'" . mysqli_real_escape_string($db, mb_substr($title, 0, 255)) . "'",
        "'" . mysqli_real_escape_string($db, mb_substr($message, 0, 1000)) . "'",
        '0',
        'NOW()',
    ];

    if ($hasLink) {
        $columns[] = 'link_url';
        $values[] = "'" . mysqli_real_escape_string($db, mb_substr($linkUrl, 0, 500)) . "'";
    }
    if ($hasPayload) {
        $columns[] = 'payload_json';
        $values[] = "'" . mysqli_real_escape_string($db, $payloadJson) . "'";
    }
    if ($hasEventKey) {
        $columns[] = 'event_key';
        $values[] = "'" . mysqli_real_escape_string($db, mb_substr($eventKey, 0, 190)) . "'";
    }

    $sql = "INSERT INTO admin_notifications (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $values) . ")";
    return (bool)mysqli_query($db, $sql);
}

function seo_admin_notifications_ensure_schema(mysqli $db): bool
{
    $sql = "
        CREATE TABLE IF NOT EXISTS admin_notifications (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(64) NOT NULL DEFAULT '',
            title VARCHAR(255) NOT NULL DEFAULT '',
            message VARCHAR(1000) NOT NULL DEFAULT '',
            link_url VARCHAR(500) NOT NULL DEFAULT '',
            payload_json LONGTEXT NULL,
            event_key VARCHAR(190) NOT NULL DEFAULT '',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_admin_notifications_event_key (event_key),
            KEY idx_admin_notifications_unread (is_read, id),
            KEY idx_admin_notifications_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    if (!mysqli_query($db, $sql)) {
        return false;
    }
    return seo_table_exists($db, 'admin_notifications');
}

function seo_notify_telegram_success(string $lang, array $article, string $jobDate, int $slotIndex): void
{
    if ((bool)($GLOBALS['SeoArticleTelegramHardDisable'] ?? false)) {
        return;
    }
    if (!function_exists('tg_notify_send')) {
        return;
    }
    $slug = (string)($article['slug'] ?? '');
    $url = $slug !== '' ? seo_article_public_url($lang, $slug, (string)($article['cluster_code'] ?? '')) : '';
    $title = htmlspecialchars((string)($article['title'] ?? ''), ENT_QUOTES, 'UTF-8');
    $excerpt = seo_strip_html_to_text((string)($article['excerpt_html'] ?? ''));
    if ($excerpt === '') {
        $excerpt = seo_strip_html_to_text((string)($article['content_html'] ?? ''));
    }
    $excerpt = htmlspecialchars(seo_trim_words($excerpt, 70), ENT_QUOTES, 'UTF-8');
    $lines = ['<b>' . $title . '</b>'];
    if ($excerpt !== '') {
        $lines[] = '';
        $lines[] = $excerpt;
    }
    if ($url !== '') {
        $cta = ($lang === 'ru') ? 'Читать полностью' : 'Read full article';
        $lines[] = '';
        $lines[] = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $cta . '</a>';
    }
    $message = implode("\n", $lines);

    $imagePayload = trim((string)($article['preview_image_data'] ?? ''));
    if ($imagePayload === '') {
        $imagePayload = seo_public_image_url_for_lang($lang, (string)($article['preview_image_url'] ?? ''));
    }
    if ($imagePayload !== '') {
        $caption = seo_trim_text_chars($message, 950);
        $sent = false;
        if (stripos($imagePayload, 'data:image/') === 0) {
            $sent = seo_tg_send_photo_data_url_to_chat($imagePayload, $caption, '');
        } else {
            $sent = seo_tg_send_photo_to_chat($imagePayload, $caption, '');
        }
        if ($sent) {
            return;
        }
        seo_echo('Admin TG: photo send failed for success alert, fallback to text.');
    }

    tg_notify_send($message);
}

function seo_notify_telegram_failure(string $lang, string $jobDate, int $slotIndex, string $error): void
{
    if ((bool)($GLOBALS['SeoArticleTelegramHardDisable'] ?? false)) {
        return;
    }
    if (!function_exists('tg_notify_send')) {
        return;
    }
    $siteHost = seo_host_for_lang($lang);
    $lines = [
        '<b>SEO article generation failed</b>',
        'Site: <b>' . htmlspecialchars($siteHost !== '' ? $siteHost : 'n/a', ENT_QUOTES, 'UTF-8') . '</b>',
        'Lang: <b>' . strtoupper(htmlspecialchars($lang, ENT_QUOTES, 'UTF-8')) . '</b>',
        'Date/slot: ' . htmlspecialchars($jobDate, ENT_QUOTES, 'UTF-8') . ' / ' . (int)$slotIndex,
        'Error: ' . htmlspecialchars(mb_substr($error, 0, 700), ENT_QUOTES, 'UTF-8'),
    ];
    tg_notify_send(implode("\n", $lines));
}

function seo_notify_telegram_scheduled(string $lang, string $jobDate, int $slotIndex, string $plannedAt): void
{
    if ((bool)($GLOBALS['SeoArticleTelegramHardDisable'] ?? false)) {
        return;
    }
    if (!function_exists('tg_notify_send')) {
        return;
    }
    $siteHost = seo_host_for_lang($lang);
    $lines = [
        '<b>SEO article scheduled</b>',
        'Site: <b>' . htmlspecialchars($siteHost !== '' ? $siteHost : 'n/a', ENT_QUOTES, 'UTF-8') . '</b>',
        'Lang: <b>' . strtoupper(htmlspecialchars($lang, ENT_QUOTES, 'UTF-8')) . '</b>',
        'Date/slot: ' . htmlspecialchars($jobDate, ENT_QUOTES, 'UTF-8') . ' / ' . (int)$slotIndex,
        'Planned at (local): ' . htmlspecialchars($plannedAt, ENT_QUOTES, 'UTF-8'),
    ];
    tg_notify_send(implode("\n", $lines));
}

function seo_notify_telegram_schedule_summary(string $jobDate, array $scheduleByLang): void
{
    if ((bool)($GLOBALS['SeoArticleTelegramHardDisable'] ?? false)) {
        return;
    }
    if (!function_exists('tg_notify_send')) {
        return;
    }
    $siteHost = trim((string)($GLOBALS['SeoArticlePrimaryHost'] ?? ''));
    $lines = [
        '<b>Daily SEO schedule</b>',
        'Site: <b>' . htmlspecialchars($siteHost !== '' ? $siteHost : 'n/a', ENT_QUOTES, 'UTF-8') . '</b>',
        'Date (local): <b>' . htmlspecialchars($jobDate, ENT_QUOTES, 'UTF-8') . '</b>',
    ];
    foreach ($scheduleByLang as $lang => $slots) {
        $times = [];
        foreach ((array)$slots as $plannedAt) {
            $time = '';
            if (preg_match('/\b(\d{2}:\d{2}):\d{2}\b/', (string)$plannedAt, $m)) {
                $time = $m[1];
            }
            if ($time !== '') {
                $times[] = $time;
            }
        }
        $line = strtoupper((string)$lang) . ': ' . (!empty($times) ? implode(', ', $times) : '-');
        $lines[] = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
    }
    tg_notify_send(implode("\n", $lines));
}

function seo_tg_settings_with_chat(?string $chatId = null): ?array
{
    if (!function_exists('tg_notify_settings')) {
        return null;
    }
    $settings = tg_notify_settings();
    if (!is_array($settings) || !(bool)($settings['enabled'] ?? false)) {
        return null;
    }
    $chatId = trim((string)$chatId);
    if ($chatId !== '') {
        $settings['chat_id'] = $chatId;
    }
    if ((string)($settings['chat_id'] ?? '') === '') {
        return null;
    }
    return $settings;
}

function seo_tg_send_message_to_chat(string $text, string $chatId): bool
{
    $settings = seo_tg_settings_with_chat($chatId);
    if ($settings === null) {
        return false;
    }
    if (function_exists('tg_notify_send')) {
        return tg_notify_send($text, $settings);
    }
    return false;
}

function seo_tg_send_photo_to_chat(string $photoUrl, string $caption, string $chatId): bool
{
    $settings = seo_tg_settings_with_chat($chatId);
    if ($settings === null) {
        return false;
    }
    if (function_exists('tg_notify_api_call')) {
        $result = tg_notify_api_call(
            'sendPhoto',
            [
                'chat_id' => (string)$settings['chat_id'],
                'photo' => $photoUrl,
                'caption' => $caption,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => 'true',
            ],
            $settings
        );
        return (bool)($result['ok'] ?? false);
    }
    return false;
}

function seo_tg_send_photo_data_url_to_chat(string $dataUrl, string $caption, string $chatId): bool
{
    $settings = seo_tg_settings_with_chat($chatId);
    if ($settings === null) {
        return false;
    }
    if (!preg_match('#^data:(image/[a-zA-Z0-9.+-]+);base64,(.+)$#s', $dataUrl, $m)) {
        return false;
    }
    $mime = (string)$m[1];
    $b64 = (string)$m[2];
    $bin = base64_decode($b64, true);
    if ($bin === false || $bin === '') {
        return false;
    }

    $tmpDir = rtrim((string)sys_get_temp_dir(), '/\\');
    $ext = 'png';
    if (stripos($mime, 'webp') !== false) {
        $ext = 'webp';
    } elseif (stripos($mime, 'jpeg') !== false || stripos($mime, 'jpg') !== false) {
        $ext = 'jpg';
    }
    $tmpFile = $tmpDir . '/seo_preview_' . uniqid('', true) . '.' . $ext;
    if (@file_put_contents($tmpFile, $bin) === false) {
        return false;
    }

    $apiBase = rtrim((string)($settings['api_base'] ?? 'https://api.telegram.org'), '/');
    $botToken = (string)($settings['bot_token'] ?? '');
    $chat = (string)($settings['chat_id'] ?? '');
    $timeout = (int)($settings['timeout'] ?? 12);
    if ($botToken === '' || $chat === '') {
        @unlink($tmpFile);
        return false;
    }
    $url = $apiBase . '/bot' . $botToken . '/sendPhoto';

    $ok = false;
    $ch = curl_init($url);
    if ($ch !== false) {
        $photoFile = class_exists('CURLFile')
            ? new CURLFile($tmpFile, $mime, 'preview.' . $ext)
            : '@' . $tmpFile;
        $payload = [
            'chat_id' => $chat,
            'caption' => $caption,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => 'true',
            'photo' => $photoFile,
        ];
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($response !== false && $status >= 200 && $status < 300) {
            $json = json_decode((string)$response, true);
            $ok = is_array($json) ? (bool)($json['ok'] ?? false) : false;
        } else {
            seo_echo('Preview TG: sendPhoto upload failed, status=' . $status . ', err=' . $err);
        }
    }

    @unlink($tmpFile);
    return $ok;
}

function seo_http_post_json_with_proxy(string $url, array $payload, array $headers, array $proxy, int $timeoutSec, ?array &$meta = null): array
{
    $httpHeaders = ['Content-Type: application/json'];
    foreach ($headers as $h) {
        $h = trim((string)$h);
        if ($h !== '') {
            $httpHeaders[] = $h;
        }
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $httpHeaders,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => max(20, $timeoutSec),
    ]);

    $proxyEnabled = (bool)($proxy['enabled'] ?? false);
    $proxyHost = trim((string)($proxy['host'] ?? ''));
    $proxyPort = (int)($proxy['port'] ?? 0);
    if ($proxyEnabled && $proxyHost !== '' && $proxyPort > 0) {
        curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
        $proxyType = strtolower(trim((string)($proxy['type'] ?? 'http')));
        curl_setopt($ch, CURLOPT_PROXYTYPE, $proxyType === 'socks5' ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP);
        $proxyUsername = (string)($proxy['username'] ?? '');
        $proxyPassword = (string)($proxy['password'] ?? '');
        if ($proxyUsername !== '' || $proxyPassword !== '') {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
        }
    }

    $body = curl_exec($ch);
    $err = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    $bodyShort = mb_substr((string)$body, 0, 900);
    $meta = [
        'url' => $url,
        'http' => $http,
        'content_type' => $contentType,
        'body_short' => $bodyShort,
    ];

    if ($body === false || $err !== '') {
        throw new RuntimeException('Image transport error: ' . $err);
    }
    if ($http < 200 || $http >= 300) {
        throw new RuntimeException(
            'Image HTTP ' . $http
            . ', content-type=' . ($contentType !== '' ? $contentType : 'n/a')
            . ', url=' . $url
            . ', body=' . $bodyShort
        );
    }

    $json = json_decode((string)$body, true);
    if (!is_array($json)) {
        throw new RuntimeException(
            'Image JSON decode failed, content-type=' . ($contentType !== '' ? $contentType : 'n/a')
            . ', url=' . $url
            . ', body=' . $bodyShort
        );
    }
    return $json;
}

function seo_fetch_image_data_url_with_proxy(string $url, array $proxy, int $timeoutSec): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (stripos($url, 'data:image/') === 0) {
        return $url;
    }
    if (!preg_match('#^https?://#i', $url)) {
        return '';
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => max(20, $timeoutSec),
        CURLOPT_USERAGENT => 'SEO-Article-Cron/1.0',
    ]);

    $proxyEnabled = (bool)($proxy['enabled'] ?? false);
    $proxyHost = trim((string)($proxy['host'] ?? ''));
    $proxyPort = (int)($proxy['port'] ?? 0);
    if ($proxyEnabled && $proxyHost !== '' && $proxyPort > 0) {
        curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
        $proxyType = strtolower(trim((string)($proxy['type'] ?? 'http')));
        curl_setopt($ch, CURLOPT_PROXYTYPE, $proxyType === 'socks5' ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP);
        $proxyUsername = (string)($proxy['username'] ?? '');
        $proxyPassword = (string)($proxy['password'] ?? '');
        if ($proxyUsername !== '' || $proxyPassword !== '') {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
        }
    }

    $body = curl_exec($ch);
    $err = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($body === false || $err !== '') {
        throw new RuntimeException('Image download transport error: ' . $err);
    }
    if ($http < 200 || $http >= 300) {
        throw new RuntimeException('Image download HTTP ' . $http);
    }
    $mime = strtolower(trim(explode(';', $contentType)[0] ?? ''));
    if ($mime === '' || strpos($mime, 'image/') !== 0) {
        throw new RuntimeException('Image download unexpected content-type: ' . ($contentType !== '' ? $contentType : 'n/a'));
    }
    if (strlen((string)$body) > 2500000) {
        throw new RuntimeException('Image download too large: ' . strlen((string)$body) . ' bytes');
    }
    return 'data:' . $mime . ';base64,' . base64_encode((string)$body);
}

function seo_fetch_image_data_url_with_fallback(string $url, array $cfg): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (stripos($url, 'data:image/') === 0) {
        return $url;
    }
    $proxyCandidates = seo_proxy_candidates_with_direct_fallback($cfg);
    if (count($proxyCandidates) > 1) {
        shuffle($proxyCandidates);
    }
    foreach ($proxyCandidates as $proxy) {
        $proxyLabel = seo_proxy_entry_label($proxy);
        try {
            $dataUrl = seo_fetch_image_data_url_with_proxy($url, $proxy, (int)$cfg['openai_timeout']);
            if ($dataUrl !== '') {
                seo_echo('Preview image: cached in DB as data-url via ' . $proxyLabel);
                return $dataUrl;
            }
        } catch (Throwable $e) {
            seo_echo('Preview image: data-url fetch failed via ' . $proxyLabel . ' - ' . $e->getMessage());
        }
    }
    return '';
}

function seo_generate_image_asset(
    array $cfg,
    string $lang,
    array $article,
    ?string $preferredStyle = null
): array
{
    global $DB;
    $style = seo_pick_image_style($preferredStyle, (array)($cfg['preview_image_style_options'] ?? []));
    if (!(bool)($cfg['preview_image_enabled'] ?? false)) {
        seo_echo('Preview image: disabled by config.');
        return ['url' => '', 'style' => $style, 'data_url' => '', 'request' => ['enabled' => false], 'result' => ['status' => 'disabled']];
    }
    $model = trim((string)($cfg['preview_image_model'] ?? ''));
    if ($model === '') {
        seo_echo('Preview image: model is empty, skip.');
        return ['url' => '', 'style' => $style, 'data_url' => '', 'request' => ['enabled' => true], 'result' => ['status' => 'skip', 'reason' => 'model_empty']];
    }
    $title = trim((string)($article['title'] ?? ''));
    if ($title === '') {
        seo_echo('Preview image: article title is empty, skip.');
        return ['url' => '', 'style' => $style, 'data_url' => '', 'request' => ['enabled' => true], 'result' => ['status' => 'skip', 'reason' => 'title_empty']];
    }
    $isOpenRouter = ((string)($cfg['llm_provider'] ?? '') === 'openrouter');
    $url = rtrim((string)$cfg['openai_base_url'], '/') . ($isOpenRouter ? '/chat/completions' : '/images/generations');
    $promptTpl = trim((string)($cfg['preview_image_prompt_template'] ?? ''));
    if ($promptTpl === '') {
        $promptTpl = 'Create a {{image_style}} editorial hero illustration for article "{{title}}" in {{lang}} language. Theme: B2B systems architecture, product engineering, security, reliability, analytics. No text, no logos, 16:9.';
    }
    $excerptText = seo_strip_html_to_text((string)($article['excerpt_html'] ?? ''));
    $contextText = seo_strip_html_to_text((string)($article['content_html'] ?? ''));
    if ($contextText !== '') {
        $contextText = mb_substr($contextText, 0, 1800);
    }
    $storyAnchors = seo_image_extract_story_anchors($title, $excerptText, $contextText, $lang);
    $anchorPrimary = trim((string)($storyAnchors['primary_concept'] ?? ''));
    $anchorContext = trim((string)($storyAnchors['operational_context'] ?? ''));
    $anchorEnforced = (bool)($cfg['preview_image_anchor_enforced'] ?? true);
    $anchorAppend = trim((string)($cfg['preview_image_anchor_append'] ?? ''));
    $prompt = str_replace(
        ['{{title}}', '{{lang}}', '{{image_style}}', '{{excerpt}}', '{{context}}'],
        [$title, strtoupper($lang), $style, $excerptText, $contextText],
        $promptTpl
    );
    $colorScheme = seo_pick_image_color_scheme((array)($cfg['image_color_schemes'] ?? []), ($DB instanceof mysqli ? $DB : null));
    $colorSchemeKey = strtolower(trim((string)($colorScheme['key'] ?? 'dark')));
    $colorSchemeInstruction = trim((string)($colorScheme['instruction'] ?? $colorSchemeKey));
    $sceneFamily = seo_pick_image_scene_family((array)($cfg['image_scene_families'] ?? []));
    $sceneFamilyKey = strtolower(trim((string)($sceneFamily['key'] ?? 'characters')));
    $sceneFamilyInstruction = trim((string)($sceneFamily['instruction'] ?? $sceneFamilyKey));
    $sceneFamilyLabel = strtoupper($lang) === 'RU'
        ? trim((string)($sceneFamily['label_ru'] ?? $sceneFamilyKey))
        : trim((string)($sceneFamily['label_en'] ?? $sceneFamilyKey));
    $scenario = seo_pick_image_scenario((array)($cfg['image_scenarios'] ?? []));
    $scenarioKey = strtolower(trim((string)($scenario['key'] ?? 'business_team_strategy')));
    $scenarioInstruction = trim((string)($scenario['instruction'] ?? $scenarioKey));
    $scenarioLabel = strtoupper($lang) === 'RU'
        ? trim((string)($scenario['label_ru'] ?? $scenarioKey))
        : trim((string)($scenario['label_en'] ?? $scenarioKey));
    $composition = seo_pick_image_composition((array)($cfg['image_compositions'] ?? []));
    $compositionKey = strtolower(trim((string)($composition['key'] ?? 'centered')));
    $compositionInstruction = trim((string)($composition['instruction'] ?? $compositionKey));
    $compositionLabel = strtoupper($lang) === 'RU'
        ? trim((string)($composition['label_ru'] ?? $compositionKey))
        : trim((string)($composition['label_en'] ?? $compositionKey));
    $allowedDirections = array_values(array_filter(array_map('trim', (array)($cfg['preview_image_style_options'] ?? []))));
    if (empty($allowedDirections)) {
        $allowedDirections = ['schematic visualization', 'real-life scene visualization', 'abstract composition', 'mood-driven atmosphere'];
    }
    $prompt .= "\n\nStyle mode: {$style}.";
    $prompt .= "\nPrimary scene family (MANDATORY): {$sceneFamilyKey} ({$sceneFamilyLabel}).";
    $prompt .= "\nPrimary scene family instruction (MANDATORY): {$sceneFamilyInstruction}";
    $prompt .= "\nPrimary visual scenario (MANDATORY): {$scenarioKey} ({$scenarioLabel}).";
    $prompt .= "\nPrimary visual scenario instruction (MANDATORY): {$scenarioInstruction}";
    $prompt .= "\nSemantic hierarchy: scene family -> scenario -> composition -> color.";
    $prompt .= "\nThe selected scene family defines the core semantic subject matter.";
    $prompt .= "\nThe selected scenario refines and operationalizes the family.";
    $prompt .= "\nComposition and color are supporting layers and must not override subject semantics.";
    $prompt .= "\nNever replace the selected scenario with generic abstract chart-only art unless scenario explicitly demands it.";
    $prompt .= "\nIf scenario includes people, depict humans as main actors in a professional B2B context.";
    $prompt .= "\nIf scenario includes map/routes, depict clear route logic and geospatial causality.";
    $prompt .= "\nIf scenario includes illustrative style, keep it editorial and commercially relevant.";
    $prompt .= "\nColor scheme mode (MANDATORY): {$colorSchemeKey}.";
    $prompt .= "\nColor scheme instruction (MANDATORY): {$colorSchemeInstruction}";
    $prompt .= "\nComposition mode (MANDATORY): {$compositionKey} ({$compositionLabel}).";
    $prompt .= "\nComposition instruction (MANDATORY): {$compositionInstruction}";
    $prompt .= "\nAllowed visual directions: " . implode(', ', $allowedDirections) . '.';
    $prompt .= "\nFocus on B2B architecture decisions, engineering quality, reliability and measurable business outcomes. No text overlays, no logos, no competitors.";
    $prompt .= "\nDo not ignore color scheme mode. The final image must clearly follow the selected color scheme.";
    if ($anchorEnforced) {
        $prompt .= "\nArticle anchors (MANDATORY):";
        $prompt .= "\n- Primary concept: {$anchorPrimary}";
        $prompt .= "\n- Operational context: {$anchorContext}";
        $prompt .= "\nThese anchors must be visible in the subject matter and not only implied by abstract UI patterns.";
        $prompt .= "\nDo not render a generic dashboard-only image without a concrete narrative subject tied to both anchors.";
    }
    if ($anchorAppend !== '') {
        $prompt .= "\n{$anchorAppend}";
    }
    $configuredSize = (string)($cfg['preview_image_size'] ?? '768x512');
    $aspectRatio = '16:9';
    $imageSizeTier = '1K';
    if (preg_match('/^(\d{2,4})x(\d{2,4})$/', (string)($cfg['preview_image_size'] ?? ''), $ms)) {
        $w = (int)$ms[1];
        $h = (int)$ms[2];
        if ($w > 0 && $h > 0) {
            $a = $w;
            $b = $h;
            while ($b !== 0) {
                $t = $a % $b;
                $a = $b;
                $b = $t;
            }
            $g = $a > 0 ? $a : 1;
            $rw = (int)($w / $g);
            $rh = (int)($h / $g);
            $ratio = $rw . ':' . $rh;
            $allowed = ['1:1','2:3','3:2','3:4','4:3','4:5','5:4','9:16','16:9','21:9'];
            if (in_array($ratio, $allowed, true)) {
                $aspectRatio = $ratio;
            }
            $maxEdge = max($w, $h);
            if ($maxEdge >= 3000) {
                $imageSizeTier = '4K';
            } elseif ($maxEdge >= 1500) {
                $imageSizeTier = '2K';
            }
        }
    }

    $requestMeta = [
        'provider' => $isOpenRouter ? 'openrouter' : 'openai',
        'model' => $model,
        'url' => $url,
        'style' => $style,
        'scene_family' => $sceneFamilyKey,
        'scene_family_label' => $sceneFamilyLabel,
        'scene_family_instruction' => $sceneFamilyInstruction,
        'scenario' => $scenarioKey,
        'scenario_label' => $scenarioLabel,
        'scenario_instruction' => $scenarioInstruction,
        'color_scheme' => $colorSchemeKey,
        'color_scheme_instruction' => $colorSchemeInstruction,
        'composition' => $compositionKey,
        'composition_label' => $compositionLabel,
        'composition_instruction' => $compositionInstruction,
        'size' => $configuredSize,
        'aspect_ratio' => $aspectRatio,
        'image_size_tier' => $imageSizeTier,
        'prompt' => mb_substr($prompt, 0, 6000),
        'prompt_template' => mb_substr((string)$promptTpl, 0, 3000),
        'anchor_enforced' => $anchorEnforced,
        'anchor_primary' => $anchorPrimary,
        'anchor_operational_context' => $anchorContext,
    ];
    $sizeCandidates = [];
    $sizeCandidates[] = $configuredSize;
    foreach (['1536x1024', '1024x683', '1024x1024', '1024x768', '768x512'] as $fallbackSize) {
        if (!in_array($fallbackSize, $sizeCandidates, true)) {
            $sizeCandidates[] = $fallbackSize;
        }
    }
    $requestMeta['size_candidates'] = $sizeCandidates;
    $headers = array_merge(
        ['Authorization: Bearer ' . (string)$cfg['openai_api_key']],
        (array)($cfg['openai_headers'] ?? [])
    );
    $proxyCandidates = seo_proxy_candidates_with_direct_fallback($cfg);
    if (count($proxyCandidates) > 1) {
        shuffle($proxyCandidates);
    }
    foreach ($sizeCandidates as $attemptSize) {
        $attemptAspect = $aspectRatio;
        $attemptTier = $imageSizeTier;
        if (preg_match('/^(\d{2,4})x(\d{2,4})$/', $attemptSize, $msAttempt)) {
            $aw = (int)$msAttempt[1];
            $ah = (int)$msAttempt[2];
            if ($aw > 0 && $ah > 0) {
                $a = $aw;
                $b = $ah;
                while ($b !== 0) {
                    $t = $a % $b;
                    $a = $b;
                    $b = $t;
                }
                $g = $a > 0 ? $a : 1;
                $rw = (int)($aw / $g);
                $rh = (int)($ah / $g);
                $candidateRatio = $rw . ':' . $rh;
                $allowed = ['1:1','2:3','3:2','3:4','4:3','4:5','5:4','9:16','16:9','21:9'];
                if (in_array($candidateRatio, $allowed, true)) {
                    $attemptAspect = $candidateRatio;
                }
                $attemptTier = (max($aw, $ah) >= 1400) ? '2K' : '1K';
            }
        }
        foreach ($proxyCandidates as $proxy) {
            $proxyLabel = seo_proxy_entry_label($proxy);
            $payload = $isOpenRouter
                ? [
                    'model' => $model,
                    'modalities' => ['image', 'text'],
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'image_config' => [
                        'aspect_ratio' => $attemptAspect,
                        'image_size' => $attemptTier,
                    ],
                ]
                : [
                    'model' => $model,
                    'prompt' => $prompt,
                    'size' => $attemptSize,
                ];
            seo_echo(
                'Preview image: requesting model=' . $model
                . ', size=' . $attemptSize
                . ($isOpenRouter ? ', aspect_ratio=' . $attemptAspect . ', image_size=' . $attemptTier : '')
                . ', style=' . $style
                . ', proxy=' . $proxyLabel
                . ', lang=' . $lang
                . ', url=' . $url
            );
            try {
                $meta = null;
                $json = seo_http_post_json_with_proxy($url, $payload, $headers, $proxy, (int)$cfg['openai_timeout'], $meta);
            $imageUrl = '';
            $imageDataUrl = '';
            if ($isOpenRouter) {
                $imageUrl = (string)(
                    $json['choices'][0]['message']['images'][0]['image_url']['url']
                    ?? $json['choices'][0]['message']['images'][0]['image_url']
                    ?? $json['choices'][0]['message']['images'][0]['url']
                    ?? ''
                );
                if ($imageUrl === '') {
                    $b64 = (string)(
                        $json['choices'][0]['message']['images'][0]['b64_json']
                        ?? $json['choices'][0]['message']['images'][0]['base64']
                        ?? ''
                    );
                    if ($b64 !== '') {
                        $imageDataUrl = 'data:image/png;base64,' . $b64;
                    }
                }
                if ($imageUrl === '') {
                    $content = $json['choices'][0]['message']['content'] ?? null;
                    if (is_array($content)) {
                        foreach ($content as $part) {
                            if (!is_array($part)) {
                                continue;
                            }
                            $candidate = (string)($part['image_url']['url'] ?? $part['image_url'] ?? '');
                            if ($candidate !== '') {
                                $imageUrl = $candidate;
                                break;
                            }
                            $candidateB64 = (string)($part['b64_json'] ?? $part['base64'] ?? '');
                            if ($candidateB64 !== '') {
                                $imageDataUrl = 'data:image/png;base64,' . $candidateB64;
                                break;
                            }
                        }
                    }
                }
            } else {
                $imageUrl = (string)($json['data'][0]['url'] ?? '');
                if ($imageUrl === '') {
                    $b64 = (string)($json['data'][0]['b64_json'] ?? '');
                    if ($b64 !== '') {
                        $imageDataUrl = 'data:image/png;base64,' . $b64;
                    }
                }
            }
            if ($imageUrl !== '' || $imageDataUrl !== '') {
                seo_echo(
                    'Preview image: generated successfully via ' . $proxyLabel
                    . ', http=' . (string)($meta['http'] ?? 200)
                    . ', content-type=' . (string)($meta['content_type'] ?? 'application/json')
                );
                return [
                    'url' => $imageUrl,
                    'style' => $style,
                    'data_url' => $imageDataUrl,
                    'request' => array_merge($requestMeta, [
                        'size' => $attemptSize,
                        'aspect_ratio' => $attemptAspect,
                        'image_size_tier' => $attemptTier,
                    ]),
                    'result' => [
                        'status' => 'ok',
                        'proxy_used' => $proxyLabel,
                        'image_url' => $imageUrl,
                        'is_data_url' => ($imageDataUrl !== '' || stripos($imageUrl, 'data:image/') === 0),
                        'color_scheme' => $colorSchemeKey,
                        'scene_family' => $sceneFamilyKey,
                        'scenario' => $scenarioKey,
                        'composition' => $compositionKey,
                        'size_used' => $attemptSize,
                        'aspect_ratio' => $attemptAspect,
                        'image_size_tier' => $attemptTier,
                    ],
                ];
            }
                seo_echo(
                    'Preview image: response has no image data via ' . $proxyLabel
                    . ', keys=' . implode(',', array_keys($json))
                    . ', body=' . (string)($meta['body_short'] ?? '')
                );
            } catch (Throwable $e) {
                seo_echo('Preview image: failed via ' . $proxyLabel . ' at size=' . $attemptSize . ' - ' . $e->getMessage());
            }
        }
    }
    seo_echo('Preview image: all attempts failed, fallback to text-only preview.');
    return [
        'url' => '',
        'style' => $style,
        'data_url' => '',
        'request' => $requestMeta,
        'result' => ['status' => 'failed', 'scene_family' => $sceneFamilyKey, 'scenario' => $scenarioKey, 'color_scheme' => $colorSchemeKey, 'composition' => $compositionKey, 'size_candidates' => $sizeCandidates],
    ];
}

function seo_send_preview_post(array $cfg, string $lang, array $article): array
{
    if (!(bool)($cfg['preview_channel_enabled'] ?? false)) {
        seo_echo('Preview TG: channel preview disabled by config.');
        return ['status' => 'skipped_disabled'];
    }
    $chatIds = seo_parse_tg_chat_ids((string)($cfg['preview_channel_chat_id'] ?? ''));
    if (empty($chatIds)) {
        seo_echo('Preview TG: chat_id is empty, skip.');
        return ['status' => 'skipped_chat_empty'];
    }
    $slug = (string)($article['slug'] ?? '');
    $url = $slug !== '' ? seo_article_public_url($lang, $slug, (string)($article['cluster_code'] ?? '')) : '';
    $postText = seo_build_tg_preview_text(
        $lang,
        $article,
        $url,
        (int)($cfg['preview_post_max_words'] ?? 220),
        (int)($cfg['preview_post_min_words'] ?? 70)
    );
    $captionText = seo_build_tg_preview_text(
        $lang,
        $article,
        $url,
        (int)($cfg['preview_caption_max_words'] ?? 80),
        (int)($cfg['preview_caption_min_words'] ?? 26)
    );
    $semanticTags = seo_tags_line(seo_tags_from_article($article, $lang, 4));
    try {
        $llmPreview = seo_tg_preview_generate_with_llm(
            $cfg,
            $lang,
            $article,
            $url,
            (int)($cfg['preview_post_max_words'] ?? 220),
            (int)($cfg['preview_post_min_words'] ?? 70),
            (int)($cfg['preview_caption_max_words'] ?? 80),
            (int)($cfg['preview_caption_min_words'] ?? 26)
        );
        if (!empty($llmPreview)) {
            $postCandidate = seo_normalize_tg_preview_text(
                (string)($llmPreview['post_text'] ?? ''),
                (int)($cfg['preview_post_min_words'] ?? 70),
                (int)($cfg['preview_post_max_words'] ?? 220)
            );
            $captionCandidate = seo_normalize_tg_preview_text(
                (string)($llmPreview['caption_text'] ?? ''),
                (int)($cfg['preview_caption_min_words'] ?? 26),
                (int)($cfg['preview_caption_max_words'] ?? 80)
            );
            $llmTagsLine = seo_tags_line(is_array($llmPreview['tags'] ?? null) ? $llmPreview['tags'] : []);
            if ($llmTagsLine !== '') {
                $semanticTags = $llmTagsLine;
            }
            if ($postCandidate !== '' && $captionCandidate !== '') {
                $postText = $postCandidate;
                $captionText = $captionCandidate;
                seo_echo('Preview TG: using LLM-generated post/caption text.');
            } else {
                seo_echo('Preview TG: LLM text rejected by length guard, fallback template text.');
            }
        }
    } catch (Throwable $e) {
        seo_echo('Preview TG LLM: failed - ' . $e->getMessage() . '; fallback template text.');
    }
    if ($semanticTags !== '') {
        $postText = seo_ensure_single_tags_at_end($postText, $semanticTags);
        $captionText = seo_ensure_single_tags_at_end($captionText, $semanticTags);
    }
    if (function_exists('tg_notify_utf8_clean')) {
        $postText = tg_notify_utf8_clean($postText);
        $captionText = tg_notify_utf8_clean($captionText);
    }
    $captionText = seo_trim_text_chars($captionText, 950);
    $postChars = function_exists('mb_strlen') ? mb_strlen($postText, 'UTF-8') : strlen($postText);
    $captionChars = function_exists('mb_strlen') ? mb_strlen($captionText, 'UTF-8') : strlen($captionText);
    seo_echo('Preview TG: prepared text lengths, post_chars=' . $postChars . ', caption_chars=' . $captionChars);

    $imageUrl = trim((string)($article['preview_image_data'] ?? ''));
    if ($imageUrl === '') {
        $imageUrl = seo_public_image_url_for_lang($lang, (string)($article['preview_image_url'] ?? ''));
    }
    if ($imageUrl === '') {
        $imgAsset = seo_generate_image_asset($cfg, $lang, $article, (string)($article['preview_image_style'] ?? ''));
        $imageUrl = trim((string)($imgAsset['data_url'] ?? ''));
        if ($imageUrl === '') {
            $imageUrl = trim((string)($imgAsset['url'] ?? ''));
            if ($imageUrl !== '') {
                $imageData = seo_fetch_image_data_url_with_fallback($imageUrl, $cfg);
                if ($imageData !== '') {
                    $imageUrl = $imageData;
                }
            }
        }
    }
    $results = [];
    $okCount = 0;
    foreach ($chatIds as $chatId) {
        $sent = false;
        $status = 'failed';
        if ($imageUrl !== '') {
            $isDataUrl = (stripos($imageUrl, 'data:image/') === 0);
            seo_echo('Preview TG: sending photo post to chat ' . $chatId . ($isDataUrl ? ' (data-url upload)' : ' (url)'));
            if ($isDataUrl) {
                if (seo_tg_send_photo_data_url_to_chat($imageUrl, $captionText, $chatId)) {
                    seo_echo('Preview TG: photo post sent successfully (upload).');
                    $sent = true;
                    $status = 'sent_photo_upload';
                }
            } else {
                if (seo_tg_send_photo_to_chat($imageUrl, $captionText, $chatId)) {
                    seo_echo('Preview TG: photo post sent successfully.');
                    $sent = true;
                    $status = 'sent_photo';
                }
            }
            if (!$sent) {
                seo_echo('Preview TG: photo send failed, fallback to text post.');
            }
        }
        if (!$sent) {
            seo_echo('Preview TG: sending text post to chat ' . $chatId);
            if (seo_tg_send_message_to_chat($postText, $chatId)) {
                seo_echo('Preview TG: text post sent.');
                $sent = true;
                $status = 'sent_text';
            } else {
                seo_echo('Preview TG: text post failed.');
            }
        }
        if ($sent) {
            $okCount++;
        }
        $results[] = ['chat_id' => $chatId, 'status' => $status];
    }
    if ($okCount === count($chatIds)) {
        return ['status' => 'sent_all', 'results' => $results];
    }
    if ($okCount > 0) {
        return ['status' => 'partial', 'results' => $results];
    }
    return ['status' => 'failed', 'results' => $results];
}

function seo_parse_tg_chat_ids(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '') {
        return [];
    }
    $parts = preg_split('/[\s,;]+/', $raw);
    if (!is_array($parts)) {
        return [];
    }
    $out = [];
    foreach ($parts as $part) {
        $id = trim((string)$part);
        if ($id === '') {
            continue;
        }
        if (!preg_match('/^-?\d+$/', $id)) {
            continue;
        }
        if (!in_array($id, $out, true)) {
            $out[] = $id;
        }
    }
    return $out;
}

function seo_strip_html_to_text(string $html): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    return $text;
}

function seo_trim_words(string $text, int $maxWords): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    if ($text === '' || $maxWords <= 0) {
        return '';
    }
    $parts = preg_split('/\s+/u', $text);
    if (!is_array($parts) || count($parts) <= $maxWords) {
        return $text;
    }
    $parts = array_slice($parts, 0, $maxWords);
    return rtrim(implode(' ', $parts)) . '...';
}

function seo_trim_text_chars(string $text, int $maxChars): string
{
    $text = trim($text);
    if ($maxChars <= 0) {
        return '';
    }
    if (mb_strlen($text) <= $maxChars) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, max(1, $maxChars - 3))) . '...';
}

function seo_ensure_single_tags_at_end(string $text, string $tagsLine): string
{
    $text = trim($text);
    $tagsLine = trim($tagsLine);
    if ($tagsLine === '') {
        return $text;
    }
    $pattern = '/' . preg_quote($tagsLine, '/') . '/u';
    $text = (string)preg_replace($pattern, '', $text);
    $text = (string)preg_replace("/\n{3,}/u", "\n\n", trim($text));
    return rtrim($text) . "\n\n" . $tagsLine;
}

function seo_tags_from_article(array $article, string $lang, int $limit = 4): array
{
    $text = mb_strtolower(
        seo_strip_html_to_text(
            (string)($article['title'] ?? '') . ' '
            . (string)($article['excerpt_html'] ?? '') . ' '
            . (string)($article['content_html'] ?? '')
        )
    );

    $candidates = [
        '#SystemArchitecture' => ['architecture', 'Р°СЂС…Рё', 'system design', 'microservice', 'distributed'],
        '#Engineering' => ['engineering', 'СЂР°Р·СЂР°Р±РѕС‚', 'implementation', 'production', 'backend'],
        '#B2B' => ['b2b', 'enterprise', 'business', 'roi', 'РѕРїРµСЂР°С†'],
        '#Security' => ['security', 'РєРёР±РµСЂ', 'threat', 'risk', 'hardening'],
        '#DevOps' => ['devops', 'ci/cd', 'deployment', 'monitoring', 'observability'],
        '#Analytics' => ['analytics', 'РјРµС‚СЂРёРє', 'dashboard', 'data', 'reporting'],
        '#Product' => ['product', 'roadmap', 'feature', 'adoption', 'retention'],
        '#API' => ['api', 'endpoint', 'integration', 'sdk', 'token'],
    ];

    $scored = [];
    foreach ($candidates as $tag => $needles) {
        $score = 0;
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($text, $needle) !== false) {
                $score++;
            }
        }
        if ($score > 0) {
            $scored[$tag] = $score;
        }
    }
    arsort($scored);
    $tags = array_slice(array_keys($scored), 0, max(1, $limit));
    if (empty($tags)) {
        $tags = ['#SystemArchitecture', '#Engineering', '#B2B'];
    }
    return $tags;
}

function seo_tags_line(array $tags): string
{
    $clean = [];
    foreach ($tags as $tag) {
        $tag = trim((string)$tag);
        if ($tag === '') {
            continue;
        }
        if ($tag[0] !== '#') {
            $tag = '#' . preg_replace('/\s+/u', '', $tag);
        }
        $tag = preg_replace('/[^#\p{L}\p{N}_]/u', '', $tag);
        if ($tag !== '' && !in_array($tag, $clean, true)) {
            $clean[] = $tag;
        }
    }
    return implode(' ', array_slice($clean, 0, 5));
}

function seo_tg_preview_generate_with_llm(
    array $cfg,
    string $lang,
    array $article,
    string $url,
    int $postMaxWords,
    int $postMinWords,
    int $captionMaxWords,
    int $captionMinWords
): array {
    if (!(bool)($cfg['preview_use_llm'] ?? false)) {
        return [];
    }

    $title = trim((string)($article['title'] ?? ''));
    $excerpt = seo_strip_html_to_text((string)($article['excerpt_html'] ?? ''));
    $content = seo_strip_html_to_text((string)($article['content_html'] ?? ''));
    if ($title === '' || $content === '') {
        return [];
    }

    $contextChars = (int)($cfg['preview_context_chars'] ?? 14000);
    $contentTail = mb_substr($content, 0, $contextChars);
    $isRu = ($lang === 'ru');
    $styleVariants = $isRu
        ? ['РґРµР»РѕРІРѕР№ Рё РїСЂР°РєС‚РёС‡РЅС‹Р№', 'Р°РЅР°Р»РёС‚РёС‡РЅС‹Р№ Рё СЃРїРѕРєРѕР№РЅС‹Р№', 'РґСЂР°Р№РІРѕРІС‹Р№ Рё РїСЂРѕРґР°СЋС‰РёР№']
        : ['practical and business-like', 'analytical and concise', 'dynamic and conversion-focused'];
    $styleHint = $styleVariants[random_int(0, count($styleVariants) - 1)];

    $systemPrompt = $isRu
        ? 'РўС‹ СЂРµРґР°РєС‚РѕСЂ Telegram-РєР°РЅР°Р»Р° Р»РёС‡РЅРѕРіРѕ СЃР°Р№С‚Р° Р°СЂС…РёС‚РµРєС‚РѕСЂР° СЃРёСЃС‚РµРј. РџРёС€Рё С‡РёС‚Р°Р±РµР»СЊРЅРѕ, СЂР°Р·РЅРѕРѕР±СЂР°Р·РЅРѕ, Р±РµР· С€Р°Р±Р»РѕРЅРЅРѕСЃС‚Рё.'
        : 'You are a Telegram editor for a personal systems architect website. Write varied, clear, non-generic copy.';

    $userPrompt = ($isRu
        ? "РЎРіРµРЅРµСЂРёСЂСѓР№ РўРћР›Р¬РљРћ JSON Р±РµР· markdown РґР»СЏ Р°РЅРѕРЅСЃР° СЃС‚Р°С‚СЊРё РІ Telegram.\n"
          . "РЎС‚РёР»СЊ: {$styleHint}.\n"
          . "РћРіСЂР°РЅРёС‡РµРЅРёСЏ: post_text {$postMinWords}-{$postMaxWords} СЃР»РѕРІ, caption_text {$captionMinWords}-{$captionMaxWords} СЃР»РѕРІ.\n"
          . "РСЃРїРѕР»СЊР·СѓР№ Telegram HTML (<b>, <i>, <a href=\"...\">), 2-4 С‚РµРјР°С‚РёС‡РµСЃРєРёРµ РёРєРѕРЅРєРё, РЅРµ РґРµР»Р°Р№ РѕРґРЅРѕС‚РёРїРЅРѕ.\n"
          . "Р’ РѕР±РѕРёС… С‚РµРєСЃС‚Р°С… РѕР±СЏР·Р°С‚РµР»СЊРЅРѕ СЃСЃС‹Р»РєР° РЅР° СЃС‚Р°С‚СЊСЋ.\n"
          . "Р¤РѕСЂРјР°С‚ РѕС‚РІРµС‚Р°:\n"
          . "{\n  \"post_text\": \"...\",\n  \"caption_text\": \"...\",\n  \"tags\": [\"#Tag1\", \"#Tag2\"]\n}\n\n"
          . "РўРµРіРё РїРѕРґР±РёСЂР°Р№ РїРѕ СЃРјС‹СЃР»Сѓ СЃС‚Р°С‚СЊРё, РЅРµ С€Р°Р±Р»РѕРЅРЅРѕ.\n"
          . "TITLE: {$title}\n"
          . "URL: {$url}\n"
          . "EXCERPT: {$excerpt}\n"
          . "ARTICLE_CONTEXT:\n{$contentTail}"
        : "Return JSON only (no markdown) for Telegram promotion of an article.\n"
          . "Style: {$styleHint}.\n"
          . "Limits: post_text {$postMinWords}-{$postMaxWords} words, caption_text {$captionMinWords}-{$captionMaxWords} words.\n"
          . "Use Telegram HTML (<b>, <i>, <a href=\"...\">), 2-4 relevant emojis, avoid repetitive template style.\n"
          . "Both texts must include the article link.\n"
          . "Response format:\n"
          . "{\n  \"post_text\": \"...\",\n  \"caption_text\": \"...\",\n  \"tags\": [\"#Tag1\", \"#Tag2\"]\n}\n\n"
          . "Select tags by article meaning, not generic placeholders.\n"
          . "TITLE: {$title}\n"
          . "URL: {$url}\n"
          . "EXCERPT: {$excerpt}\n"
          . "ARTICLE_CONTEXT:\n{$contentTail}");

    $proxyCandidates = seo_proxy_candidates($cfg);
    if (count($proxyCandidates) > 1) {
        shuffle($proxyCandidates);
    }
    $usedProxy = null;
    $raw = seo_call_openai_with_fallback(
        (string)$cfg['openai_api_key'],
        (string)$cfg['openai_base_url'],
        (string)$cfg['preview_llm_model'],
        (int)$cfg['openai_timeout'],
        $systemPrompt,
        $userPrompt,
        $proxyCandidates,
        (array)($cfg['openai_headers'] ?? []),
        $usedProxy
    );
    seo_echo('Preview TG LLM: generated via model=' . (string)$cfg['preview_llm_model'] . ', proxy=' . (string)($usedProxy ?? 'direct'));
    $json = seo_extract_json($raw);
    $post = trim((string)($json['post_text'] ?? ''));
    $caption = trim((string)($json['caption_text'] ?? ''));
    $tags = is_array($json['tags'] ?? null) ? $json['tags'] : [];
    if ($post === '' || $caption === '') {
        throw new RuntimeException('Preview TG LLM returned empty post/caption');
    }
    return ['post_text' => $post, 'caption_text' => $caption, 'tags' => $tags];
}

function seo_build_tg_preview_text(string $lang, array $article, string $url, int $maxWords = 220, int $minWords = 70): string
{
    $isRu = ($lang === 'ru');
    $titleRaw = trim((string)($article['title'] ?? ''));
    $title = htmlspecialchars($titleRaw, ENT_QUOTES, 'UTF-8');

    $excerpt = seo_strip_html_to_text((string)($article['excerpt_html'] ?? ''));
    if ($excerpt === '') {
        $excerpt = seo_strip_html_to_text((string)($article['content_html'] ?? ''));
    }
    $maxWords = max(20, min(700, $maxWords));
    $minWords = max(12, min($maxWords, $minWords));
    $excerpt = seo_trim_words($excerpt, $maxWords);
    $wordsCount = 0;
    if ($excerpt !== '') {
        $parts = preg_split('/\s+/u', $excerpt);
        $wordsCount = is_array($parts) ? count($parts) : 0;
    }
    if ($wordsCount < $minWords) {
        $fillerRu = 'Р Р°Р·Р±РѕСЂ РѕС…РІР°С‚С‹РІР°РµС‚ РїСЂР°РєС‚РёС‡РµСЃРєРёРµ С€Р°РіРё РІРЅРµРґСЂРµРЅРёСЏ, С‚РёРїРѕРІС‹Рµ РѕС€РёР±РєРё Рё СЂРµРєРѕРјРµРЅРґР°С†РёРё РїРѕ РЅР°РґРµР¶РЅРѕР№ Р°СЂС…РёС‚РµРєС‚СѓСЂРµ Рё СЌРєСЃРїР»СѓР°С‚Р°С†РёРё РІ РїСЂРѕРґР°РєС€РµРЅРµ.';
        $fillerEn = 'This overview adds practical implementation steps, common pitfalls, and recommendations for reliable production architecture and operations.';
        $base = trim($excerpt);
        $filler = $isRu ? $fillerRu : $fillerEn;
        $excerpt = trim($base !== '' ? ($base . ' ' . $filler) : $filler);
        $excerpt = seo_trim_words($excerpt, $maxWords);
    }
    $excerptEscaped = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
    $urlEscaped = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    if ($isRu) {
        $lines = [
            '<b>' . $title . '</b>',
            '',
            $excerptEscaped,
        ];
        if ($url !== '') {
            $lines[] = '';
            $lines[] = '<a href="' . $urlEscaped . '">Читать полностью</a>';
        }
    } else {
        $lines = [
            '<b>' . $title . '</b>',
            '',
            $excerptEscaped,
        ];
        if ($url !== '') {
            $lines[] = '';
            $lines[] = '<a href="' . $urlEscaped . '">Read full article</a>';
        }
    }
    return implode("\n", $lines);
}

function seo_normalize_tg_preview_text(string $text, int $minWords, int $maxWords): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', ' ', $text);
    $allowed = '<b><strong><i><em><a><code><pre><br>';
    $text = strip_tags($text, $allowed);
    $plain = seo_strip_html_to_text($text);
    $plain = seo_trim_words($plain, $maxWords);
    $words = 0;
    if ($plain !== '') {
        $parts = preg_split('/\s+/u', $plain);
        $words = is_array($parts) ? count($parts) : 0;
    }
    if ($words < $minWords) {
        return '';
    }
    return $text;
}

function seo_word_count(string $html): int
{
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return 0;
    }
    preg_match_all('/[\p{L}\p{N}][\p{L}\p{N}\p{Mn}\p{Pd}\']*/u', $text, $m);
    return count($m[0] ?? []);
}

function seo_normalize_generated_title_casing(string $title, string $lang = 'en'): string
{
    $title = trim(preg_replace('/\s+/u', ' ', $title));
    if ($title === '') {
        return '';
    }

    $tokens = preg_split('/\s+/u', $title);
    if (!is_array($tokens) || count($tokens) < 2) {
        return $title;
    }

    $alphaWords = 0;
    $titleCaseWords = 0;
    foreach ($tokens as $tok) {
        $core = trim((string)preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', (string)$tok));
        if ($core === '' || !preg_match('/\p{L}/u', $core)) {
            continue;
        }
        $alphaWords++;
        if (preg_match('/^\p{Lu}\p{Ll}+$/u', $core)) {
            $titleCaseWords++;
        }
    }
    if ($alphaWords < 2) {
        return $title;
    }

    // Normalize only when most words are in forced Title Case.
    if (($titleCaseWords / max(1, $alphaWords)) < 0.7) {
        return $title;
    }

    $out = [];
    foreach ($tokens as $idx => $tok) {
        $prefix = '';
        $core = $tok;
        $suffix = '';
        if (preg_match('/^([^\p{L}\p{N}]*)((?:[\p{L}\p{N}\-\/\+\#])+)([^\p{L}\p{N}]*)$/u', (string)$tok, $m)) {
            $prefix = (string)$m[1];
            $core = (string)$m[2];
            $suffix = (string)$m[3];
        }

        $preserve = false;
        if (preg_match('/\d/u', $core)) {
            $preserve = true; // B2B, 2FA, etc.
        } elseif (preg_match('/^[A-Z]{2,8}$/', $core)) {
            $preserve = true; // API, IP, SDK, JWT...
        } elseif (preg_match('/[\/\-\+]/u', $core) && preg_match('/[A-Z]/', $core)) {
            $preserve = true; // CI/CD-like tokens.
        }

        if ($idx === 0) {
            if ($preserve) {
                $out[] = $prefix . $core . $suffix;
            } else {
                $lower = mb_strtolower($core, 'UTF-8');
                $first = mb_substr($lower, 0, 1, 'UTF-8');
                $rest = mb_substr($lower, 1, null, 'UTF-8');
                $out[] = $prefix . mb_strtoupper($first, 'UTF-8') . $rest . $suffix;
            }
        } else {
            $out[] = $prefix . ($preserve ? $core : mb_strtolower($core, 'UTF-8')) . $suffix;
        }
    }

    return trim(implode(' ', $out));
}

function seo_clean_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $html = preg_replace('~<\s*script\b[^>]*>.*?<\s*/\s*script\s*>~is', '', $html);
    $html = preg_replace('~\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)~is', '', $html);
    $html = preg_replace('~javascript\s*:~i', '', $html);
    return trim($html);
}

function seo_det_int(string $key, int $min, int $max): int
{
    if ($max <= $min) {
        return $min;
    }
    $hash = hash('sha256', $key);
    $value = hexdec(substr($hash, 0, 8));
    return $min + ($value % ($max - $min + 1));
}

function seo_weighted_pick(array $items): string
{
    $pool = [];
    foreach ($items as $key => $weightRaw) {
        $weight = (float)$weightRaw;
        if ($weight <= 0) {
            continue;
        }
        $pool[] = ['key' => (string)$key, 'weight' => $weight];
    }
    if (empty($pool)) {
        return '';
    }

    $sum = 0.0;
    foreach ($pool as $item) {
        $sum += (float)$item['weight'];
    }
    if ($sum <= 0) {
        return (string)$pool[0]['key'];
    }

    $r = (random_int(1, 1000000) / 1000000) * $sum;
    $acc = 0.0;
    foreach ($pool as $item) {
        $acc += (float)$item['weight'];
        if ($r <= $acc) {
            return (string)$item['key'];
        }
    }

    return (string)$pool[count($pool) - 1]['key'];
}

function seo_pick_image_style(?string $preferred = null, array $styleOptions = []): string
{
    $preferred = strtolower(trim((string)$preferred));
    $weights = [
        'schematic visualization' => 1.0,
        'real-life scene visualization' => 0.8,
        'abstract composition' => 0.45,
        'mood-driven atmosphere' => 0.3,
    ];
    if (!empty($styleOptions)) {
        $weights = [];
        foreach ($styleOptions as $opt) {
            $v = trim((string)$opt);
            if ($v !== '') {
                $weights[$v] = 1.0;
            }
        }
        if (empty($weights)) {
            $weights = [
                'schematic visualization' => 1.0,
                'real-life scene visualization' => 0.8,
                'abstract composition' => 0.45,
                'mood-driven atmosphere' => 0.3,
            ];
        }
    }
    if ($preferred !== '' && array_key_exists($preferred, $weights)) {
        return $preferred;
    }
    $picked = seo_weighted_pick($weights);
    return $picked !== '' ? $picked : 'schematic';
}

function seo_pick_image_scene_family(array $families = []): array
{
    $defaults = [
        ['key' => 'characters', 'weight' => 1.0, 'label_en' => 'Characters', 'label_ru' => 'Characters', 'instruction' => 'Human-centered scene with business actors and clear decision context.'],
        ['key' => 'infrastructure_tech', 'weight' => 0.9, 'label_en' => 'Infrastructure & Tech', 'label_ru' => 'Infrastructure & Tech', 'instruction' => 'Systems, APIs, networks and service components as primary subject.'],
        ['key' => 'nature_metaphor', 'weight' => 0.45, 'label_en' => 'Nature metaphor', 'label_ru' => 'Nature metaphor', 'instruction' => 'Natural metaphor adapted to risk flow and system behavior.'],
        ['key' => 'abstract_signal', 'weight' => 0.55, 'label_en' => 'Abstract signal', 'label_ru' => 'Abstract signal', 'instruction' => 'Signal-driven abstract composition with strong semantic anchors.'],
        ['key' => 'hybrid_mix', 'weight' => 0.7, 'label_en' => 'Hybrid mix', 'label_ru' => 'Hybrid mix', 'instruction' => 'Balanced mix of people, systems and signal motifs.'],
    ];

    $rows = [];
    foreach ($families as $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = strtolower(trim((string)($row['key'] ?? '')));
        if ($key === '') {
            continue;
        }
        $rows[] = [
            'key' => $key,
            'weight' => max(0.01, (float)($row['weight'] ?? 1.0)),
            'label_en' => trim((string)($row['label_en'] ?? $key)),
            'label_ru' => trim((string)($row['label_ru'] ?? $key)),
            'instruction' => trim((string)($row['instruction'] ?? $key)),
        ];
    }
    if (empty($rows)) {
        $rows = $defaults;
    }

    $weights = [];
    foreach ($rows as $row) {
        $weights[(string)$row['key']] = (float)$row['weight'];
    }
    $pick = seo_weighted_pick($weights);
    if ($pick === '') {
        $pick = (string)$rows[0]['key'];
    }
    foreach ($rows as $row) {
        if ((string)$row['key'] === $pick) {
            return $row;
        }
    }
    return $rows[0];
}

function seo_pick_image_scenario(array $scenarios = []): array
{
    $defaults = [
        ['key' => 'business_team_strategy', 'weight' => 1.0, 'label_en' => 'Business team solving strategy', 'label_ru' => 'Business team solving strategy', 'instruction' => 'B2B meeting scene: executives and analysts discussing risk dashboards and decisions.'],
        ['key' => 'scenario_timeline_chart', 'weight' => 0.9, 'label_en' => 'Scenario timeline graph', 'label_ru' => 'Scenario timeline graph', 'instruction' => 'Editorial chart-driven visual with scenario branches, KPI shifts and timeline emphasis.'],
        ['key' => 'relationship_network_graph', 'weight' => 0.85, 'label_en' => 'Relationship network graph', 'label_ru' => 'Relationship network graph', 'instruction' => 'Node-link network map showing IP, users, ASN, routes and risk relationships.'],
        ['key' => 'real_life_case_photoreal', 'weight' => 0.82, 'label_en' => 'Real-life photoreal case', 'label_ru' => 'Real-life photoreal case', 'instruction' => 'Photorealistic business-context scene with real-world environment and believable people.'],
        ['key' => 'cartoon_editorial', 'weight' => 0.55, 'label_en' => 'Cartoon editorial illustration', 'label_ru' => 'Cartoon editorial illustration', 'instruction' => 'Stylized cartoon editorial artwork, simplified forms, expressive but professional mood.'],
        ['key' => 'fairytale_illustration', 'weight' => 0.28, 'label_en' => 'Fairytale-inspired illustration', 'label_ru' => 'Fairytale-inspired illustration', 'instruction' => 'Illustrative fairy-tale visual language adapted to technology and cyber-risk storytelling.'],
        ['key' => 'people_and_robots', 'weight' => 0.62, 'label_en' => 'People and robots collaboration', 'label_ru' => 'People and robots collaboration', 'instruction' => 'Human and AI/robot collaboration visual for decision support and risk operations.'],
        ['key' => 'conspiracy_motif', 'weight' => 0.2, 'label_en' => 'Conspiracy motif', 'label_ru' => 'Conspiracy motif', 'instruction' => 'Suspense investigative scene with hidden patterns, secret links and threat-intel atmosphere.'],
        ['key' => 'soc_control_room', 'weight' => 0.7, 'label_en' => 'SOC control room', 'label_ru' => 'SOC control room', 'instruction' => 'Security operations control room with large screens, alerts and analyst workflow context.'],
        ['key' => 'incident_response_warroom', 'weight' => 0.68, 'label_en' => 'Incident response war-room', 'label_ru' => 'Incident response war-room', 'instruction' => 'High-focus response room visual with rapid decisions, escalation board and coordination.'],
        ['key' => 'fraud_ring_investigation', 'weight' => 0.58, 'label_en' => 'Fraud ring investigation', 'label_ru' => 'Fraud ring investigation', 'instruction' => 'Investigative composition around fraud ring signals, linked accounts and proxy routes.'],
        ['key' => 'payment_checkout_risk', 'weight' => 0.78, 'label_en' => 'Payment checkout risk scene', 'label_ru' => 'Payment checkout risk scene', 'instruction' => 'Checkout/payment context with risk gating and conversion-preserving antifraud decisions.'],
        ['key' => 'login_abuse_prevention', 'weight' => 0.74, 'label_en' => 'Login abuse prevention', 'label_ru' => 'Login abuse prevention', 'instruction' => 'Authentication risk scene focused on login/signup abuse prevention and trust scoring.'],
        ['key' => 'geo_route_map', 'weight' => 0.76, 'label_en' => 'Geo route map', 'label_ru' => 'Geo route map', 'instruction' => 'World/region route map with geolocation paths, anomalies and confidence overlays.'],
        ['key' => 'executive_dashboard_story', 'weight' => 0.66, 'label_en' => 'Executive dashboard story', 'label_ru' => 'Executive dashboard story', 'instruction' => 'Board-level dashboard storytelling visual: KPI impact, fraud pressure, conversion metrics.'],
        ['key' => 'api_integration_architecture', 'weight' => 0.63, 'label_en' => 'API integration architecture', 'label_ru' => 'API integration architecture', 'instruction' => 'Technical architecture scene with API gateway, services, events and decision engine.'],
        ['key' => 'marketplace_trust_safety', 'weight' => 0.57, 'label_en' => 'Marketplace trust and safety', 'label_ru' => 'Marketplace trust and safety', 'instruction' => 'Marketplace ecosystem visual with buyer/seller risk controls and moderation signals.'],
        ['key' => 'fintech_risk_committee', 'weight' => 0.6, 'label_en' => 'Fintech risk committee', 'label_ru' => 'Fintech risk committee', 'instruction' => 'Financial risk committee scene with compliance, antifraud and product discussion context.'],
        ['key' => 'zero_trust_access', 'weight' => 0.52, 'label_en' => 'Zero-trust access decision', 'label_ru' => 'Zero-trust access decision', 'instruction' => 'Access decision visual under zero-trust model with identity, context and policy checks.'],
        ['key' => 'threat_intel_evidence_board', 'weight' => 0.5, 'label_en' => 'Threat-intel evidence board', 'label_ru' => 'Threat-intel evidence board', 'instruction' => 'Evidence-board style scene with threat artifacts, links, hypotheses and validation flow.'],
    ];

    $rows = [];
    foreach ($scenarios as $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = strtolower(trim((string)($row['key'] ?? '')));
        if ($key === '') {
            continue;
        }
        $rows[] = [
            'key' => $key,
            'weight' => max(0.01, (float)($row['weight'] ?? 1.0)),
            'label_en' => trim((string)($row['label_en'] ?? $key)),
            'label_ru' => trim((string)($row['label_ru'] ?? $key)),
            'instruction' => trim((string)($row['instruction'] ?? $key)),
        ];
    }
    if (empty($rows)) {
        $rows = $defaults;
    }

    $weights = [];
    foreach ($rows as $row) {
        $weights[(string)$row['key']] = (float)$row['weight'];
    }
    $pick = seo_weighted_pick($weights);
    if ($pick === '') {
        $pick = (string)$rows[0]['key'];
    }
    foreach ($rows as $row) {
        if ((string)$row['key'] === $pick) {
            return $row;
        }
    }
    return $rows[0];
}

function seo_pick_image_composition(array $compositions = []): array
{
    $defaults = [
        ['key' => 'centered', 'weight' => 1.0, 'label_en' => 'Centered', 'label_ru' => 'Centered', 'instruction' => 'Centered focal composition with clear subject priority.'],
        ['key' => 'dynamic_diagonal', 'weight' => 0.9, 'label_en' => 'Dynamic diagonal', 'label_ru' => 'Dynamic diagonal', 'instruction' => 'Strong diagonal flow with dynamic motion and tension.'],
        ['key' => 'broken_reflection', 'weight' => 0.45, 'label_en' => 'Broken reflection', 'label_ru' => 'Broken reflection', 'instruction' => 'Fragmented mirrored composition, asymmetry with reflective motifs.'],
        ['key' => 'golden_ratio', 'weight' => 0.75, 'label_en' => 'Golden ratio', 'label_ru' => 'Golden ratio', 'instruction' => 'Golden ratio based layout for balanced visual hierarchy.'],
        ['key' => 'mosaic', 'weight' => 0.55, 'label_en' => 'Mosaic', 'label_ru' => 'Mosaic', 'instruction' => 'Mosaic modular blocks composition with structured segmentation.'],
        ['key' => 'expressionist', 'weight' => 0.38, 'label_en' => 'Expressionist', 'label_ru' => 'Expressionist', 'instruction' => 'Expressive composition with dramatic rhythm and emotional contrast.'],
        ['key' => 'rule_of_thirds', 'weight' => 0.82, 'label_en' => 'Rule of thirds', 'label_ru' => 'Rule of thirds', 'instruction' => 'Rule-of-thirds placement with off-center focal points.'],
        ['key' => 'symmetrical', 'weight' => 0.62, 'label_en' => 'Symmetrical', 'label_ru' => 'Symmetrical', 'instruction' => 'Symmetrical composition with strong vertical axis and balance.'],
        ['key' => 'asymmetrical_balance', 'weight' => 0.66, 'label_en' => 'Asymmetrical balance', 'label_ru' => 'Asymmetrical balance', 'instruction' => 'Asymmetrical but balanced layout with weighted visual masses.'],
        ['key' => 'radial_focus', 'weight' => 0.41, 'label_en' => 'Radial focus', 'label_ru' => 'Radial focus', 'instruction' => 'Radial composition radiating from central core or hotspot.'],
        ['key' => 'spiral_flow', 'weight' => 0.34, 'label_en' => 'Spiral flow', 'label_ru' => 'Spiral flow', 'instruction' => 'Spiral flow guiding eye through layered content depth.'],
        ['key' => 'depth_layers', 'weight' => 0.64, 'label_en' => 'Depth layers', 'label_ru' => 'Depth layers', 'instruction' => 'Foreground-midground-background layering for spatial depth.'],
        ['key' => 'split_screen', 'weight' => 0.4, 'label_en' => 'Split screen', 'label_ru' => 'Split screen', 'instruction' => 'Split-screen composition contrasting two states or scenarios.'],
        ['key' => 'timeline_sequence', 'weight' => 0.37, 'label_en' => 'Timeline sequence', 'label_ru' => 'Timeline sequence', 'instruction' => 'Sequential timeline composition with progressive narrative.'],
        ['key' => 'grid_modular', 'weight' => 0.58, 'label_en' => 'Modular grid', 'label_ru' => 'Modular grid', 'instruction' => 'Strict modular grid composition with aligned structural blocks.'],
        ['key' => 'minimal_negative_space', 'weight' => 0.5, 'label_en' => 'Minimal negative space', 'label_ru' => 'Minimal negative space', 'instruction' => 'Minimal composition with deliberate negative space dominance.'],
        ['key' => 'cinematic_wide', 'weight' => 0.48, 'label_en' => 'Cinematic wide', 'label_ru' => 'Cinematic wide', 'instruction' => 'Cinematic wide framing with panoramic storytelling emphasis.'],
        ['key' => 'isometric', 'weight' => 0.35, 'label_en' => 'Isometric', 'label_ru' => 'Isometric', 'instruction' => 'Isometric composition with technical 3D-like perspective.'],
        ['key' => 'collage_data', 'weight' => 0.33, 'label_en' => 'Data collage', 'label_ru' => 'Data collage', 'instruction' => 'Data-collage composition blending charts, signals and abstract forms.'],
        ['key' => 'vortex_tension', 'weight' => 0.29, 'label_en' => 'Vortex tension', 'label_ru' => 'Vortex tension', 'instruction' => 'Vortex-like composition creating directional pull and tension.'],
    ];

    $rows = [];
    foreach ($compositions as $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = strtolower(trim((string)($row['key'] ?? '')));
        if ($key === '') {
            continue;
        }
        $rows[] = [
            'key' => $key,
            'weight' => max(0.01, (float)($row['weight'] ?? 1.0)),
            'label_en' => trim((string)($row['label_en'] ?? $key)),
            'label_ru' => trim((string)($row['label_ru'] ?? $key)),
            'instruction' => trim((string)($row['instruction'] ?? $key)),
        ];
    }
    if (empty($rows)) {
        $rows = $defaults;
    }

    $weights = [];
    foreach ($rows as $row) {
        $weights[(string)$row['key']] = (float)$row['weight'];
    }
    $pick = seo_weighted_pick($weights);
    if ($pick === '') {
        $pick = (string)$rows[0]['key'];
    }
    foreach ($rows as $row) {
        if ((string)$row['key'] === $pick) {
            return $row;
        }
    }
    return $rows[0];
}

function seo_recent_image_color_schemes(?mysqli $db, int $limit = 8): array
{
    if (!($db instanceof mysqli) || $limit <= 0) {
        return [];
    }
    if (!seo_table_exists($db, 'seo_generator_logs')) {
        return [];
    }
    $rows = [];
    $res = mysqli_query(
        $db,
        "SELECT image_result_json, image_request_json
         FROM seo_generator_logs
         WHERE status = 'success'
         ORDER BY id DESC
         LIMIT " . (int)$limit
    );
    if (!$res) {
        return [];
    }
    while ($row = mysqli_fetch_assoc($res)) {
        if (!is_array($row)) {
            continue;
        }
        $imageResult = json_decode((string)($row['image_result_json'] ?? '{}'), true);
        if (!is_array($imageResult)) {
            $imageResult = [];
        }
        $imageRequest = json_decode((string)($row['image_request_json'] ?? '{}'), true);
        if (!is_array($imageRequest)) {
            $imageRequest = [];
        }
        $key = strtolower(trim((string)($imageResult['color_scheme'] ?? ($imageRequest['color_scheme'] ?? ''))));
        if ($key !== '') {
            $rows[] = $key;
        }
    }
    return $rows;
}

function seo_pick_image_color_scheme(array $schemes = [], ?mysqli $db = null): array
{
    $defaults = [
        ['key' => 'dark', 'weight' => 1.0, 'instruction' => 'Dark cinematic palette, deep shadows, high contrast accents.'],
        ['key' => 'light', 'weight' => 0.9, 'instruction' => 'Light clean palette, high readability, soft contrast.'],
        ['key' => 'colordull', 'weight' => 0.6, 'instruction' => 'Muted desaturated palette, restrained color intensity.'],
        ['key' => 'noir', 'weight' => 0.45, 'instruction' => 'Noir monochrome leaning palette, dramatic lighting.'],
        ['key' => 'neon', 'weight' => 0.35, 'instruction' => 'Neon cyber palette with glowing accents on dark base.'],
        ['key' => 'pastel', 'weight' => 0.35, 'instruction' => 'Pastel palette, soft gradients, low harshness.'],
        ['key' => 'teal_orange', 'weight' => 0.55, 'instruction' => 'Teal and orange blockbuster palette with balanced warm/cool contrast.'],
        ['key' => 'earthy', 'weight' => 0.5, 'instruction' => 'Earthy natural palette: clay, olive, sand, and low saturation browns.'],
        ['key' => 'ice_blue', 'weight' => 0.48, 'instruction' => 'Cold ice-blue palette with crisp highlights and restrained warmth.'],
        ['key' => 'sunset', 'weight' => 0.5, 'instruction' => 'Sunset palette with amber, coral, and magenta gradients.'],
        ['key' => 'duotone', 'weight' => 0.42, 'instruction' => 'Strong duotone palette limited to two dominant colors plus neutrals.'],
        ['key' => 'monochrome', 'weight' => 0.44, 'instruction' => 'Monochrome palette with tonal depth and controlled contrast.'],
        ['key' => 'vaporwave', 'weight' => 0.34, 'instruction' => 'Vaporwave-inspired palette: pink, cyan, and retro glow atmosphere.'],
        ['key' => 'industrial', 'weight' => 0.46, 'instruction' => 'Industrial palette with graphite, steel blue, and warning accent tones.'],
        ['key' => 'forest', 'weight' => 0.43, 'instruction' => 'Forest palette with deep greens, moss, and subtle amber highlights.'],
        ['key' => 'high_key', 'weight' => 0.4, 'instruction' => 'High-key bright palette with airy whites and gentle contrast edges.'],
    ];
    $defaultByKey = [];
    foreach ($defaults as $d) {
        $k = strtolower(trim((string)($d['key'] ?? '')));
        if ($k !== '') {
            $defaultByKey[$k] = $d;
        }
    }
    $rows = [];
    foreach ($schemes as $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = strtolower(trim((string)($row['key'] ?? '')));
        if ($key === '') {
            continue;
        }
        $instructionRaw = trim((string)($row['instruction'] ?? ''));
        if ($instructionRaw === '' && isset($defaultByKey[$key])) {
            $instructionRaw = (string)($defaultByKey[$key]['instruction'] ?? '');
        }
        if ($instructionRaw === '') {
            $instructionRaw = $key;
        }
        $rows[] = [
            'key' => $key,
            'weight' => max(0.01, (float)($row['weight'] ?? 1.0)),
            'instruction' => $instructionRaw,
        ];
    }
    if (empty($rows)) {
        $rows = $defaults;
    }
    $weights = [];
    foreach ($rows as $row) {
        $weights[(string)$row['key']] = (float)$row['weight'];
    }
    $recent = seo_recent_image_color_schemes($db, 8);
    if (count($weights) > 1 && !empty($recent)) {
        $penalties = [0.14, 0.25, 0.4, 0.55];
        foreach ($recent as $i => $recentKey) {
            if (!isset($weights[$recentKey])) {
                continue;
            }
            $factor = $penalties[$i] ?? 0.75;
            $weights[$recentKey] = max(0.01, (float)$weights[$recentKey] * $factor);
        }
    }
    $pick = seo_weighted_pick($weights);
    if ($pick === '') {
        $pick = (string)$rows[0]['key'];
    }
    foreach ($rows as $row) {
        if ((string)$row['key'] === $pick) {
            return $row;
        }
    }
    return $rows[0];
}

function seo_daily_slots(string $jobDate, string $lang, int $minCount, int $maxCount, string $salt): array
{
    [$startMinute, $endMinute] = seo_lang_schedule_window_minutes($jobDate, $lang, $salt);
    $target = seo_det_int("{$jobDate}|{$lang}|{$salt}|target", $minCount, $maxCount);
    $minutes = seo_generate_irregular_slot_minutes($jobDate, $lang, $salt, $startMinute, $endMinute, $target, 'full-day');

    $slots = [];
    $slotIndex = 1;
    foreach ($minutes as $minuteOfDay) {
        $hour = (int)floor($minuteOfDay / 60);
        $minute = (int)($minuteOfDay % 60);
        $plannedAt = sprintf('%s %02d:%02d:00', $jobDate, $hour, $minute);
        $slots[$slotIndex] = $plannedAt;
        $slotIndex++;
    }
    return $slots;
}

function seo_round_up_15_minute(int $minuteOfDay): int
{
    if ($minuteOfDay <= 0) {
        return 0;
    }
    return (int)(ceil($minuteOfDay / 15) * 15);
}

function seo_generate_irregular_slot_minutes(
    string $jobDate,
    string $lang,
    string $salt,
    int $startMinute,
    int $endMinute,
    int $target,
    string $seedScope = ''
): array {
    $minGapSlots = 3; // 45 minutes minimum gap
    $startSlot = max(0, (int)ceil($startMinute / 15));
    $endSlot = min(95, (int)floor($endMinute / 15));
    if ($endSlot < $startSlot || $target <= 0) {
        return [];
    }

    $maxFeasible = (int)floor(($endSlot - $startSlot) / $minGapSlots) + 1;
    $target = max(1, min($target, max(1, $maxFeasible)));
    $seedBase = "{$jobDate}|{$lang}|{$salt}|irregular|{$seedScope}";

    $slots = [];
    $latestFirst = $endSlot - (($target - 1) * $minGapSlots);
    if ($latestFirst < $startSlot) {
        $latestFirst = $startSlot;
    }
    $first = seo_det_int("{$seedBase}|first", $startSlot, $latestFirst);
    $slots[] = $first;

    for ($i = 1; $i < $target; $i++) {
        $prev = $slots[count($slots) - 1];
        $remaining = $target - $i - 1;
        $minSlot = $prev + $minGapSlots;
        $maxSlot = $endSlot - ($remaining * $minGapSlots);
        if ($maxSlot < $minSlot) {
            $maxSlot = $minSlot;
        }

        // Randomized "preferred" upper bound to keep spacing uneven.
        $dynamicMaxGapSlots = seo_det_int("{$seedBase}|dyn-gap|{$i}", 4, 16); // 60..240 minutes
        $preferredMax = min($maxSlot, $prev + $dynamicMaxGapSlots);
        if ($preferredMax < $minSlot) {
            $preferredMax = $maxSlot;
        }

        $pick = seo_det_int("{$seedBase}|pick|{$i}", $minSlot, $preferredMax);
        if ($preferredMax < $maxSlot) {
            $jumpChance = seo_det_int("{$seedBase}|jump|{$i}", 0, 99);
            if ($jumpChance < 30) {
                $pick = seo_det_int("{$seedBase}|jump-pick|{$i}", $preferredMax, $maxSlot);
            }
        }
        $slots[] = $pick;
    }

    $minutes = array_map(static function (int $slot): int {
        return $slot * 15;
    }, $slots);
    sort($minutes, SORT_NUMERIC);
    return $minutes;
}

function seo_lang_schedule_window_minutes(string $jobDate, string $lang, string $salt): array
{
    $langKey = strtolower(trim($lang));
    $windowStart = 15;
    $windowEnd = 23 * 60 + 45;

    if ($langKey === 'ru') {
        // Moscow daytime.
        $windowStart = 9 * 60;
        $windowEnd = 19 * 60 + 45;
    } elseif ($langKey === 'en') {
        // Western hemisphere daytime projected to server clock.
        $windowStart = 15 * 60;
        $windowEnd = 23 * 60 + 45;
    }

    $jitterStart = seo_det_int("{$jobDate}|{$langKey}|{$salt}|window-start-jitter", -45, 45);
    $jitterEnd = seo_det_int("{$jobDate}|{$langKey}|{$salt}|window-end-jitter", -45, 45);

    $startMinute = max(0, min(23 * 60 + 45, seo_round_up_15_minute($windowStart + $jitterStart)));
    $endMinute = max(15, min(23 * 60 + 45, (int)(floor(($windowEnd + $jitterEnd) / 15) * 15)));

    if ($endMinute < $startMinute) {
        $endMinute = min(23 * 60 + 45, $startMinute + 60);
        $endMinute = (int)(floor($endMinute / 15) * 15);
    }
    if ($endMinute < $startMinute) {
        $startMinute = max(0, min(23 * 60, $startMinute - 15));
        $endMinute = min(23 * 60 + 45, $startMinute + 15);
    }

    return [$startMinute, $endMinute];
}

function seo_daily_slots_ranged(
    string $jobDate,
    string $lang,
    int $minCount,
    int $maxCount,
    string $salt,
    int $startMinute
): array {
    [$langStartMinute, $langEndMinute] = seo_lang_schedule_window_minutes($jobDate, $lang, $salt);
    $endMinute = $langEndMinute;
    $startMinute = max($langStartMinute, min($endMinute, seo_round_up_15_minute($startMinute)));

    $available = (int)floor(($endMinute - $startMinute) / 15) + 1;
    if ($available <= 0) {
        $available = 1;
        $startMinute = $endMinute;
    }

    $target = seo_det_int("{$jobDate}|{$lang}|{$salt}|target", $minCount, $maxCount);
    $target = max(1, min($target, $available));
    $minutes = seo_generate_irregular_slot_minutes(
        $jobDate,
        $lang,
        $salt,
        $startMinute,
        $endMinute,
        $target,
        'ranged|' . $startMinute
    );
    $slots = [];
    $slotIndex = 1;
    foreach ($minutes as $minuteOfDay) {
        $hour = (int)floor($minuteOfDay / 60);
        $minute = (int)($minuteOfDay % 60);
        $plannedAt = sprintf('%s %02d:%02d:00', $jobDate, $hour, $minute);
        $slots[$slotIndex] = $plannedAt;
        $slotIndex++;
    }
    return $slots;
}

function seo_fetch_slots(mysqli $db, string $jobDate, string $lang): array
{
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $rows = [];
    $res = mysqli_query(
        $db,
        "SELECT slot_index, planned_at
         FROM seo_article_cron_runs
         WHERE job_date = '{$jobDateSafe}'
           AND lang_code = '{$langSafe}'
         ORDER BY slot_index ASC"
    );
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[(int)$row['slot_index']] = (string)$row['planned_at'];
        }
        mysqli_free_result($res);
    }
    return $rows;
}

function seo_schedule_exists(mysqli $db, string $jobDate): bool
{
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $res = mysqli_query(
        $db,
        "SELECT id
         FROM seo_article_cron_runs
         WHERE job_date = '{$jobDateSafe}'
         LIMIT 1"
    );
    return $res && mysqli_num_rows($res) > 0;
}

function seo_fetch_recent_articles(mysqli $db, string $lang, string $domainHost, int $limit = 120): array
{
    $langSafe = mysqli_real_escape_string($db, $lang);
    $domainSafe = mysqli_real_escape_string($db, $domainHost);
    $limit = max(1, min(500, $limit));

    $rows = [];
    $res = mysqli_query(
        $db,
        "SELECT id, title, slug, published_at
         FROM examples_articles
         WHERE COALESCE(lang_code, 'en') = '{$langSafe}'
           AND COALESCE(domain_host, '') = '{$domainSafe}'
         ORDER BY published_at DESC, id DESC
         LIMIT {$limit}"
    );
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        mysqli_free_result($res);
    }
    return $rows;
}

function seo_fetch_services_links(mysqli $db, string $lang, string $domainHost, int $limit = 20): array
{
    if (!seo_table_exists($db, 'public_services')) {
        return [];
    }
    $langSafe = mysqli_real_escape_string($db, $lang);
    $domainSafe = mysqli_real_escape_string($db, $domainHost);
    $limit = max(1, min(200, $limit));
    $rows = [];
    $orderExpr = seo_table_has_column($db, 'public_services', 'sort_order')
        ? 'sort_order ASC, id DESC'
        : 'id DESC';
    $res = mysqli_query(
        $db,
        "SELECT title, slug
         FROM public_services
         WHERE is_published = 1
           AND COALESCE(lang_code, 'en') = '{$langSafe}'
           AND COALESCE(domain_host, '') = '{$domainSafe}'
         ORDER BY {$orderExpr}
         LIMIT {$limit}"
    );
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $title = trim((string)($row['title'] ?? ''));
            $slug = trim((string)($row['slug'] ?? ''));
            if ($title === '' || $slug === '') {
                continue;
            }
            $rows[] = [
                'type' => 'service',
                'title' => $title,
                'slug' => $slug,
                'url' => '/services/' . rawurlencode($slug) . '/',
            ];
        }
        mysqli_free_result($res);
    }
    return $rows;
}

function seo_fetch_projects_links(mysqli $db, string $lang, string $domainHost, int $limit = 20): array
{
    if (!seo_table_exists($db, 'public_projects')) {
        return [];
    }
    $hasSymbolic = seo_table_has_column($db, 'public_projects', 'symbolic_code');
    $codeSelect = $hasSymbolic ? "COALESCE(NULLIF(symbolic_code,''), slug)" : 'slug';
    $langSafe = mysqli_real_escape_string($db, $lang);
    $domainSafe = mysqli_real_escape_string($db, $domainHost);
    $limit = max(1, min(200, $limit));
    $rows = [];
    $orderExpr = seo_table_has_column($db, 'public_projects', 'sort_order')
        ? 'sort_order ASC, id DESC'
        : 'id DESC';
    $res = mysqli_query(
        $db,
        "SELECT title, slug, {$codeSelect} AS route_code
         FROM public_projects
         WHERE is_published = 1
           AND COALESCE(lang_code, 'en') = '{$langSafe}'
           AND COALESCE(domain_host, '') = '{$domainSafe}'
         ORDER BY {$orderExpr}
         LIMIT {$limit}"
    );
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $title = trim((string)($row['title'] ?? ''));
            $code = trim((string)($row['route_code'] ?? ''));
            if ($title === '' || $code === '') {
                continue;
            }
            $rows[] = [
                'type' => 'project',
                'title' => $title,
                'slug' => trim((string)($row['slug'] ?? '')),
                'url' => '/projects/' . rawurlencode($code) . '/',
            ];
        }
        mysqli_free_result($res);
    }
    return $rows;
}

function seo_unique_slug(mysqli $db, string $slugBase, string $domainHost, string $lang): string
{
    $slugBase = examples_slugify($slugBase);
    if ($slugBase === '') {
        $slugBase = 'article-' . gmdate('YmdHis');
    }
    $slug = $slugBase;
    $suffix = 1;
    $domainSafe = mysqli_real_escape_string($db, $domainHost);
    $langSafe = mysqli_real_escape_string($db, $lang);

    while (true) {
        $slugSafe = mysqli_real_escape_string($db, $slug);
        $res = mysqli_query(
            $db,
            "SELECT id
             FROM examples_articles
             WHERE slug = '{$slugSafe}'
               AND COALESCE(domain_host, '') = '{$domainSafe}'
               AND COALESCE(lang_code, 'en') = '{$langSafe}'
             LIMIT 1"
        );
        if ($res && mysqli_num_rows($res) === 0) {
            return $slug;
        }
        $suffix++;
        $slug = substr($slugBase, 0, 170) . '-' . $suffix;
    }
}

function seo_detect_cluster_code(string $seed, string $lang = 'en'): string
{
    $seed = mb_strtolower(trim($seed), 'UTF-8');
    if ($seed === '') {
        return 'b2b';
    }
    $researchKeywords = [
        'research', 'study', 'benchmark', 'insight report', 'whitepaper', 'trend report',
        'РёСЃСЃР»РµРґРѕРІР°РЅ', 'Р±РµРЅС‡РјР°СЂРє', 'РѕС‚С‡РµС‚', 'white paper', 'СЃС‚Р°С‚РёСЃС‚', 'РґРёРЅР°РјРёРє'
    ];
    $b2bKeywords = ['b2b', 'business', 'enterprise', 'revenue', 'roi', 'РєРѕРјРјРµСЂ', 'Р±РёР·РЅРµСЃ', 'РєРѕСЂРїРѕСЂР°С‚'];
    $researchHits = 0;
    $b2bHits = 0;
    foreach ($researchKeywords as $kw) {
        if (mb_strpos($seed, $kw) !== false) {
            $researchHits++;
        }
    }
    foreach ($b2bKeywords as $kw) {
        if (mb_strpos($seed, $kw) !== false) {
            $b2bHits++;
        }
    }
    if ($researchHits >= $b2bHits + 1) {
        return 'research';
    }
    return 'b2b';
}

function seo_detect_cluster_code_llm(
    array $cfg,
    string $lang,
    string $title,
    string $excerptHtml,
    string $contentHtml,
    array $proxyCandidates,
    ?string &$usedProxyLabel = null,
    ?array &$llmTrace = null
): array {
    $llmTrace = [];
    $heuristic = seo_detect_cluster_code($title . ' ' . seo_strip_html_to_text($excerptHtml), $lang);

    $systemPrompt = 'You are a strict classifier for SEO article clusters. Output JSON only.';
    $context = trim(
        $title . "\n\n"
        . mb_substr(seo_strip_html_to_text($excerptHtml), 0, 600) . "\n\n"
        . mb_substr(seo_strip_html_to_text($contentHtml), 0, 1800)
    );
    $userPrompt = "Classify the article into one cluster code only.\n"
        . "Allowed cluster_code values: b2b, research.\n"
        . "Rules:\n"
        . "1) b2b: implementation, business value, playbooks, operations, product/risk teams.\n"
        . "2) research: primarily analytical study/report/benchmark findings and methodology focus.\n"
        . "3) If mixed, choose the dominant narrative intent by headings and evidence blocks.\n"
        . "4) If uncertain, choose b2b.\n"
        . "Return strict JSON:\n"
        . "{\n  \"cluster_code\": \"b2b|research\",\n  \"confidence\": 0.0,\n  \"reason\": \"short\"\n}\n\n"
        . "Article language: " . strtoupper($lang) . "\n"
        . "Article content:\n" . $context;

    try {
        $callMeta = null;
        $raw = seo_call_openai_with_fallback(
            (string)$cfg['openai_api_key'],
            (string)$cfg['openai_base_url'],
            (string)$cfg['openai_model'],
            (int)$cfg['openai_timeout'],
            $systemPrompt,
            $userPrompt,
            $proxyCandidates,
            (array)($cfg['openai_headers'] ?? []),
            $usedProxyLabel,
            $callMeta
        );
        $json = seo_extract_json($raw);
        $code = strtolower(trim((string)($json['cluster_code'] ?? '')));
        if (!in_array($code, ['b2b', 'research'], true)) {
            $code = $heuristic;
        }
        $llmTrace = [
            'request' => [
                'phase' => 'cluster_classify',
                'provider' => (string)($cfg['llm_provider'] ?? ''),
                'base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'model' => (string)($cfg['openai_model'] ?? ''),
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'response' => [
                'raw_content' => $raw,
                'parsed_json' => $json,
                'used_proxy' => (string)($usedProxyLabel ?? 'direct'),
            ],
            'usage' => (array)($callMeta['usage'] ?? []),
        ];
        return [
            'cluster_code' => $code,
            'cluster_source' => 'llm',
        ];
    } catch (Throwable $e) {
        $llmTrace = [
            'request' => [
                'phase' => 'cluster_classify',
                'provider' => (string)($cfg['llm_provider'] ?? ''),
                'base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'model' => (string)($cfg['openai_model'] ?? ''),
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'response' => [
                'error' => (string)$e->getMessage(),
            ],
            'usage' => [],
        ];
        return [
            'cluster_code' => $heuristic,
            'cluster_source' => 'heuristic-fallback',
        ];
    }
}

function seo_cluster_taxonomy_v2(string $lang, array $cfg = []): array
{
    $taxonomyKey = $lang === 'ru' ? 'article_cluster_taxonomy_ru' : 'article_cluster_taxonomy_en';
    $raw = (array)($cfg[$taxonomyKey] ?? []);
    $out = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = examples_normalize_cluster((string)($row['key'] ?? ''), $lang);
        if ($key === '') {
            continue;
        }
        $weight = max(0.01, min(5.0, (float)($row['weight'] ?? 1.0)));
        $keywordsRaw = trim((string)($row['keywords'] ?? ''));
        $keywords = [];
        if ($keywordsRaw !== '') {
            $parts = preg_split('/[,;]+/', $keywordsRaw);
            if (is_array($parts)) {
                foreach ($parts as $part) {
                    $kw = mb_strtolower(trim((string)$part), 'UTF-8');
                    if ($kw !== '') {
                        $keywords[] = $kw;
                    }
                }
            }
        }
        $out[] = [
            'key' => $key,
            'weight' => $weight,
            'keywords' => $keywords,
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    if (!empty($out)) {
        return $out;
    }
    return [
        ['key' => 'b2b', 'weight' => 1.0, 'keywords' => ['business', 'enterprise', 'roi', 'revenue', 'implementation', 'Р±РёР·РЅРµСЃ', 'РІРЅРµРґСЂРµРЅРёРµ']],
        ['key' => 'research', 'weight' => 0.9, 'keywords' => ['research', 'study', 'benchmark', 'report', 'analysis', 'РёСЃСЃР»РµРґРѕРІР°РЅРёРµ', 'РѕС‚С‡РµС‚', 'Р°РЅР°Р»РёС‚РёРєР°']],
        ['key' => 'dev', 'weight' => 0.8, 'keywords' => ['api', 'sdk', 'integration', 'code', 'php', 'node', 'python', 'РёРЅС‚РµРіСЂР°С†РёСЏ', 'РєРѕРґ']],
        ['key' => 'theory', 'weight' => 0.55, 'keywords' => ['concept', 'principles', 'framework', 'model', 'РєРѕРЅС†РµРїС†РёСЏ', 'РїСЂРёРЅС†РёРїС‹', 'РјРѕРґРµР»СЊ']],
    ];
}

function seo_detect_cluster_code_v2(string $seed, string $lang = 'en', array $cfg = []): string
{
    $seed = mb_strtolower(trim($seed), 'UTF-8');
    $taxonomy = seo_cluster_taxonomy_v2($lang, $cfg);
    if (empty($taxonomy)) {
        return 'b2b';
    }
    if ($seed === '') {
        return (string)($taxonomy[0]['key'] ?? 'b2b');
    }
    $bestCode = (string)($taxonomy[0]['key'] ?? 'b2b');
    $bestScore = -1.0;
    foreach ($taxonomy as $row) {
        $code = (string)($row['key'] ?? '');
        if ($code === '') {
            continue;
        }
        $score = (float)($row['weight'] ?? 1.0) * 0.2;
        foreach ((array)($row['keywords'] ?? []) as $kw) {
            $kw = mb_strtolower(trim((string)$kw), 'UTF-8');
            if ($kw !== '' && mb_strpos($seed, $kw) !== false) {
                $score += 1.0;
            }
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestCode = $code;
        }
    }
    return examples_normalize_cluster($bestCode, $lang);
}

function seo_detect_cluster_code_llm_v2(
    array $cfg,
    string $lang,
    string $title,
    string $excerptHtml,
    string $contentHtml,
    array $proxyCandidates,
    ?string &$usedProxyLabel = null,
    ?array &$llmTrace = null
): array {
    $llmTrace = [];
    $taxonomy = seo_cluster_taxonomy_v2($lang, $cfg);
    $allowed = [];
    foreach ($taxonomy as $row) {
        $code = trim((string)($row['key'] ?? ''));
        if ($code !== '' && !in_array($code, $allowed, true)) {
            $allowed[] = $code;
        }
    }
    if (empty($allowed)) {
        $allowed = ['b2b', 'research', 'dev', 'theory'];
    }
    $heuristic = seo_detect_cluster_code_v2($title . ' ' . seo_strip_html_to_text($excerptHtml), $lang, $cfg);

    $systemPrompt = 'You are a strict classifier for SEO article clusters. Output JSON only.';
    $context = trim(
        $title . "\n\n"
        . mb_substr(seo_strip_html_to_text($excerptHtml), 0, 600) . "\n\n"
        . mb_substr(seo_strip_html_to_text($contentHtml), 0, 1800)
    );
    $userPrompt = "Classify the article into one cluster code only.\n"
        . "Allowed cluster_code values: " . implode(', ', $allowed) . ".\n"
        . "Rules:\n"
        . "1) Choose dominant intent by headings and concrete content focus.\n"
        . "2) If mixed, pick the strongest narrative center.\n"
        . "3) Return strict JSON only.\n"
        . "{\n  \"cluster_code\": \"" . implode('|', $allowed) . "\",\n  \"confidence\": 0.0,\n  \"reason\": \"short\"\n}\n\n"
        . "Article language: " . strtoupper($lang) . "\n"
        . "Article content:\n" . $context;

    try {
        $callMeta = null;
        $raw = seo_call_openai_with_fallback(
            (string)$cfg['openai_api_key'],
            (string)$cfg['openai_base_url'],
            (string)$cfg['openai_model'],
            (int)$cfg['openai_timeout'],
            $systemPrompt,
            $userPrompt,
            $proxyCandidates,
            (array)($cfg['openai_headers'] ?? []),
            $usedProxyLabel,
            $callMeta
        );
        $json = seo_extract_json($raw);
        $code = strtolower(trim((string)($json['cluster_code'] ?? '')));
        if (!in_array($code, $allowed, true)) {
            $code = $heuristic;
        }
        $llmTrace = [
            'request' => [
                'phase' => 'cluster_classify',
                'provider' => (string)($cfg['llm_provider'] ?? ''),
                'base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'model' => (string)($cfg['openai_model'] ?? ''),
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'response' => [
                'raw_content' => $raw,
                'parsed_json' => $json,
                'used_proxy' => (string)($usedProxyLabel ?? 'direct'),
            ],
            'usage' => (array)($callMeta['usage'] ?? []),
        ];
        return [
            'cluster_code' => $code,
            'cluster_source' => 'llm',
        ];
    } catch (Throwable $e) {
        $llmTrace = [
            'request' => [
                'phase' => 'cluster_classify',
                'provider' => (string)($cfg['llm_provider'] ?? ''),
                'base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'model' => (string)($cfg['openai_model'] ?? ''),
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'response' => [
                'error' => (string)$e->getMessage(),
            ],
            'usage' => [],
        ];
        return [
            'cluster_code' => $heuristic,
            'cluster_source' => 'heuristic-fallback',
        ];
    }
}

function seo_call_openai(
    string $apiKey,
    string $baseUrl,
    string $model,
    int $timeoutSec,
    string $systemPrompt,
    string $userPrompt,
    array $proxy = [],
    array $extraHeaders = [],
    ?array &$responseMeta = null
): string
{
    $url = rtrim($baseUrl, '/') . '/chat/completions';
    $modelLc = strtolower(trim($model));
    $isOpenAiModel = (
        strpos($modelLc, 'openai/') === 0
        || strpos($modelLc, 'gpt-') === 0
        || strpos($modelLc, '/gpt-') !== false
    );
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ],
        'temperature' => $isOpenAiModel ? 0.2 : 0.7,
        'max_tokens' => 12000,
    ];
    if ($isOpenAiModel) {
        // Force strict JSON object mode for OpenAI-family models to reduce
        // "LLM JSON decode failed" on long structured outputs.
        $payload['response_format'] = ['type' => 'json_object'];
    }

    $httpHeaders = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ];
    foreach ($extraHeaders as $h) {
        $h = trim((string)$h);
        if ($h !== '') {
            $httpHeaders[] = $h;
        }
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $httpHeaders,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => max(30, $timeoutSec),
    ]);

    $proxyEnabled = (bool)($proxy['enabled'] ?? false);
    $proxyHost = trim((string)($proxy['host'] ?? ''));
    $proxyPort = (int)($proxy['port'] ?? 0);
    if ($proxyEnabled && $proxyHost !== '' && $proxyPort > 0) {
        curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);

        $proxyType = strtolower(trim((string)($proxy['type'] ?? 'http')));
        if ($proxyType === 'socks5') {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        } else {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

        $proxyUsername = (string)($proxy['username'] ?? '');
        $proxyPassword = (string)($proxy['password'] ?? '');
        if ($proxyUsername !== '' || $proxyPassword !== '') {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
        }
    }

    $body = curl_exec($ch);
    $err = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $err !== '') {
        throw new RuntimeException('OpenAI transport error: ' . $err);
    }
    if ($http < 200 || $http >= 300) {
        throw new RuntimeException('OpenAI HTTP ' . $http . ': ' . mb_substr($body, 0, 400));
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        throw new RuntimeException('OpenAI invalid JSON');
    }

    $contentRaw = $json['choices'][0]['message']['content'] ?? null;
    $content = seo_normalize_llm_content_to_text($contentRaw);
    if ($content === '') {
        $content = trim((string)($json['choices'][0]['text'] ?? ''));
    }
    if (
        $content === ''
        && is_array($json['choices'][0]['message']['tool_calls'] ?? null)
        && !empty($json['choices'][0]['message']['tool_calls'][0]['function']['arguments'])
    ) {
        $content = trim((string)$json['choices'][0]['message']['tool_calls'][0]['function']['arguments']);
    }
    if (
        $content === ''
        && is_array($json['choices'][0]['message']['function_call'] ?? null)
        && !empty($json['choices'][0]['message']['function_call']['arguments'])
    ) {
        $content = trim((string)$json['choices'][0]['message']['function_call']['arguments']);
    }
    if ($content === '') {
        throw new RuntimeException('OpenAI empty response');
    }
    $usageRaw = is_array($json['usage'] ?? null) ? $json['usage'] : [];
    $promptTokens = (int)($usageRaw['prompt_tokens'] ?? $usageRaw['input_tokens'] ?? 0);
    $completionTokens = (int)($usageRaw['completion_tokens'] ?? $usageRaw['output_tokens'] ?? 0);
    $totalTokens = (int)($usageRaw['total_tokens'] ?? ($promptTokens + $completionTokens));
    if ($totalTokens <= 0 && ($promptTokens > 0 || $completionTokens > 0)) {
        $totalTokens = $promptTokens + $completionTokens;
    }
    $responseMeta = [
        'http_code' => $http,
        'usage' => [
            'prompt_tokens' => max(0, $promptTokens),
            'completion_tokens' => max(0, $completionTokens),
            'total_tokens' => max(0, $totalTokens),
        ],
        'response_json' => $json,
        'response_content' => $content,
    ];
    return $content;
}

function seo_apply_campaign_to_cfg(array $cfg, array $runtime): array
{
    $campaignKey = strtolower(trim((string)($runtime['campaign'] ?? '')));
    if ($campaignKey === '' || empty($cfg['campaigns'][$campaignKey]) || !is_array($cfg['campaigns'][$campaignKey])) {
        $cfg['campaign_key'] = '';
        $cfg['campaign_material_section'] = 'journal';
        return $cfg;
    }

    $campaign = $cfg['campaigns'][$campaignKey];
    $cfg['campaign_key'] = $campaignKey;
    $cfg['campaign_material_section'] = (string)($campaign['material_section'] ?? $campaignKey);
    $cfg['enabled'] = !empty($campaign['enabled']) && !empty($cfg['enabled']);
    $cfg['daily_min'] = (int)($campaign['daily_min'] ?? $cfg['daily_min']);
    $cfg['daily_max'] = (int)($campaign['daily_max'] ?? $cfg['daily_max']);
    $cfg['max_per_run'] = (int)($campaign['max_per_run'] ?? $cfg['max_per_run']);
    $cfg['word_min'] = (int)($campaign['word_min'] ?? $cfg['word_min']);
    $cfg['word_max'] = (int)($campaign['word_max'] ?? $cfg['word_max']);
    $cfg['seed_salt'] = trim((string)($cfg['seed_salt'] ?? 'seo-articles')) . '::' . trim((string)($campaign['seed_salt_suffix'] ?? $campaignKey));
    foreach ([
        'styles_en',
        'styles_ru',
        'clusters_en',
        'clusters_ru',
        'article_structures_en',
        'article_structures_ru',
        'article_system_prompt_en',
        'article_system_prompt_ru',
        'article_user_prompt_append_en',
        'article_user_prompt_append_ru',
    ] as $key) {
        if (array_key_exists($key, $campaign)) {
            $cfg[$key] = $campaign[$key];
        }
    }

    return $cfg;
}

function seo_normalize_llm_content_to_text($content): string
{
    if (is_string($content)) {
        return trim($content);
    }
    if (!is_array($content)) {
        return '';
    }
    $chunks = [];
    foreach ($content as $part) {
        if (is_string($part)) {
            $t = trim($part);
            if ($t !== '') {
                $chunks[] = $t;
            }
            continue;
        }
        if (!is_array($part)) {
            continue;
        }
        $type = strtolower(trim((string)($part['type'] ?? '')));
        $text = '';
        if (isset($part['text']) && is_string($part['text'])) {
            $text = $part['text'];
        } elseif (isset($part['output_text']) && is_string($part['output_text'])) {
            $text = $part['output_text'];
        } elseif (isset($part['content']) && is_string($part['content'])) {
            $text = $part['content'];
        } elseif (isset($part['value']) && is_string($part['value'])) {
            $text = $part['value'];
        } elseif (isset($part['json']) && (is_array($part['json']) || is_object($part['json']))) {
            $text = json_encode($part['json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        } elseif ($type === 'refusal' && isset($part['refusal']) && is_string($part['refusal'])) {
            $text = $part['refusal'];
        }
        $text = trim((string)$text);
        if ($text !== '') {
            $chunks[] = $text;
        }
    }
    return trim(implode("\n", $chunks));
}

function seo_is_region_block_error(string $message): bool
{
    $m = mb_strtolower($message, 'UTF-8');
    return (
        mb_strpos($m, 'unsupported_country_region_territory') !== false
        || mb_strpos($m, 'country, region, or territory not supported') !== false
        || mb_strpos($m, 'request_forbidden') !== false
        || mb_strpos($m, 'provider_name":"openai"') !== false
    );
}

function seo_call_openai_with_fallback(
    string $apiKey,
    string $baseUrl,
    string $model,
    int $timeoutSec,
    string $systemPrompt,
    string $userPrompt,
    array $proxyCandidates,
    array $extraHeaders = [],
    ?string &$usedProxyLabel = null,
    ?array &$responseMeta = null,
    string $fallbackModel = ''
): string
{
    if ($fallbackModel === '' && isset($GLOBALS['SeoOpenRouterFallbackModel'])) {
        $fallbackModel = trim((string)$GLOBALS['SeoOpenRouterFallbackModel']);
    }
    $attemptCandidates = [];
    foreach ($proxyCandidates as $proxy) {
        if (!is_array($proxy)) {
            continue;
        }
        $attemptCandidates[] = $proxy;
    }
    $hasDirectAttempt = false;
    foreach ($attemptCandidates as $candidate) {
        if ((bool)($candidate['enabled'] ?? false) === false) {
            $hasDirectAttempt = true;
            break;
        }
    }
    if (!$hasDirectAttempt) {
        // Resilience guard: if configured proxies are unavailable (e.g. 407 auth),
        // still try direct transport once to keep OpenRouter/OpenAI generation alive.
        $attemptCandidates[] = [
            'enabled' => false,
            'host' => '',
            'port' => 0,
            'type' => 'http',
            'username' => '',
            'password' => '',
        ];
    }
    $errors = [];
    $usedProxyLabel = null;
    $responseMeta = null;
    foreach ($attemptCandidates as $proxy) {
        $label = seo_proxy_entry_label($proxy);
        try {
            $callMeta = null;
            $content = seo_call_openai(
                $apiKey,
                $baseUrl,
                $model,
                $timeoutSec,
                $systemPrompt,
                $userPrompt,
                $proxy,
                $extraHeaders,
                $callMeta
            );
            $usedProxyLabel = $label;
            $responseMeta = is_array($callMeta) ? $callMeta : [];
            $responseMeta['used_proxy'] = $label;
            $responseMeta['attempts_total'] = count($errors) + 1;
            if (!empty($errors)) {
                $responseMeta['attempt_errors'] = $errors;
            }
            return $content;
        } catch (Throwable $e) {
            $errors[] = $label . ' -> ' . $e->getMessage();
        }
    }

    if ($fallbackModel !== '' && $fallbackModel !== $model && seo_is_region_block_error(implode(' | ', $errors))) {
        foreach ($attemptCandidates as $proxy) {
            $label = seo_proxy_entry_label($proxy);
            try {
                $callMeta = null;
                $content = seo_call_openai(
                    $apiKey,
                    $baseUrl,
                    $fallbackModel,
                    $timeoutSec,
                    $systemPrompt,
                    $userPrompt,
                    $proxy,
                    $extraHeaders,
                    $callMeta
                );
                $usedProxyLabel = $label;
                $responseMeta = is_array($callMeta) ? $callMeta : [];
                $responseMeta['used_proxy'] = $label;
                $responseMeta['used_model'] = $fallbackModel;
                $responseMeta['attempts_total'] = count($errors) + 1;
                $responseMeta['attempt_errors'] = $errors;
                return $content;
            } catch (Throwable $e) {
                $errors[] = $label . ' [fallback-model:' . $fallbackModel . '] -> ' . $e->getMessage();
            }
        }
    }

    $responseMeta = [
        'used_proxy' => 'none',
        'attempts_total' => count($errors),
        'attempt_errors' => $errors,
    ];
    throw new RuntimeException('All transport attempts failed: ' . implode(' | ', $errors));
}

function seo_proxy_check(array $cfg): int
{
    $proxyCandidates = seo_proxy_candidates($cfg);
    $ok = 0;
    $total = count($proxyCandidates);

    seo_echo('Proxy check started. Candidates: ' . $total);
    foreach ($proxyCandidates as $proxy) {
        $label = seo_proxy_entry_label($proxy);
        try {
            seo_call_openai(
                $cfg['openai_api_key'],
                $cfg['openai_base_url'],
                $cfg['openai_model'],
                max(20, min(60, (int)$cfg['openai_timeout'])),
                'Return only JSON.',
                '{"ok":true}',
                $proxy,
                (array)($cfg['openai_headers'] ?? [])
            );
            $ok++;
            seo_echo('[OK] ' . $label);
        } catch (Throwable $e) {
            seo_echo('[FAIL] ' . $label . ' -> ' . $e->getMessage());
        }
    }

    seo_echo('Proxy check finished. Working: ' . $ok . '/' . $total);
    return $ok;
}

function seo_extract_json(string $raw): array
{
    $raw = trim($raw);

    if ($raw === '') {
        throw new RuntimeException('JSON payload not found in LLM response');
    }

    $raw = preg_replace('/^\xEF\xBB\xBF/u', '', $raw);
    $candidates = [];

    if (preg_match_all('/```(?:json)?\s*([\s\S]*?)```/iu', $raw, $m)) {
        foreach ((array)($m[1] ?? []) as $block) {
            $block = trim((string)$block);
            if ($block !== '') {
                $candidates[] = $block;
            }
        }
    }

    $candidates[] = $raw;

    $len = strlen($raw);
    $start = -1;
    $depth = 0;
    $inString = false;
    $escaped = false;
    for ($i = 0; $i < $len; $i++) {
        $ch = $raw[$i];
        if ($inString) {
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($ch === '\\') {
                $escaped = true;
                continue;
            }
            if ($ch === '"') {
                $inString = false;
            }
            continue;
        }
        if ($ch === '"') {
            $inString = true;
            continue;
        }
        if ($ch === '{' || $ch === '[') {
            if ($depth === 0) {
                $start = $i;
            }
            $depth++;
            continue;
        }
        if ($ch === '}' || $ch === ']') {
            if ($depth > 0) {
                $depth--;
                if ($depth === 0 && $start >= 0) {
                    $segment = trim(substr($raw, $start, $i - $start + 1));
                    if ($segment !== '') {
                        $candidates[] = $segment;
                    }
                    $start = -1;
                }
            }
        }
    }

    $seen = [];
    foreach ($candidates as $candidate) {
        $candidate = trim((string)$candidate);
        if ($candidate === '') {
            continue;
        }
        if (isset($seen[$candidate])) {
            continue;
        }
        $seen[$candidate] = true;

        $json = json_decode($candidate, true);
        if (is_array($json)) {
            if (function_exists('array_is_list') && array_is_list($json) && isset($json[0]) && is_array($json[0])) {
                return $json[0];
            }
            return $json;
        }

        $nested = json_decode($candidate, true);
        if (is_string($nested)) {
            $nestedDecoded = json_decode($nested, true);
            if (is_array($nestedDecoded)) {
                if (function_exists('array_is_list') && array_is_list($nestedDecoded) && isset($nestedDecoded[0]) && is_array($nestedDecoded[0])) {
                    return $nestedDecoded[0];
                }
                return $nestedDecoded;
            }
        }
    }

    $rawShort = trim((string)preg_replace('/\s+/u', ' ', mb_substr($raw, 0, 280)));
    throw new RuntimeException('LLM JSON decode failed: ' . $rawShort);
}

function seo_upsert_slot_run(mysqli $db, string $jobDate, string $lang, int $slotIndex, string $plannedAt): array
{
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $plannedSafe = mysqli_real_escape_string($db, $plannedAt);
    $slotIndex = max(1, $slotIndex);

    mysqli_query(
        $db,
        "INSERT INTO seo_article_cron_runs (job_date, lang_code, slot_index, planned_at, status, attempts, created_at, updated_at)
         VALUES ('{$jobDateSafe}', '{$langSafe}', {$slotIndex}, '{$plannedSafe}', 'pending', 0, NOW(), NOW())
         ON DUPLICATE KEY UPDATE planned_at = VALUES(planned_at), updated_at = NOW()"
    );
    $isNew = ((int)mysqli_affected_rows($db) === 1);

    $res = mysqli_query(
        $db,
        "SELECT id, status, attempts, article_id, message, planned_at
         FROM seo_article_cron_runs
         WHERE job_date = '{$jobDateSafe}'
           AND lang_code = '{$langSafe}'
           AND slot_index = {$slotIndex}
         LIMIT 1"
    );
    $row = $res ? mysqli_fetch_assoc($res) : null;
    if (!$row) {
        throw new RuntimeException('Cannot load slot run row');
    }
    $row['is_new'] = $isNew ? 1 : 0;
    return $row;
}

function seo_mark_slot_result(mysqli $db, int $runId, string $status, int $attempts, ?int $articleId, string $message): void
{
    $runId = max(1, $runId);
    $statusSafe = mysqli_real_escape_string($db, $status);
    $messageSafe = mysqli_real_escape_string($db, mb_substr($message, 0, 500));
    $articleSql = $articleId !== null ? (string)(int)$articleId : 'NULL';
    mysqli_query(
        $db,
        "UPDATE seo_article_cron_runs
         SET status = '{$statusSafe}',
             attempts = " . (int)$attempts . ",
             article_id = {$articleSql},
             message = '{$messageSafe}',
             updated_at = NOW()
         WHERE id = {$runId}
         LIMIT 1"
    );
}

function seo_append_related_links(string $contentHtml, array $related, bool $isRu): string
{
    $hasRelatedInContent = (strpos($contentHtml, 'related-links') !== false);
    if ($hasRelatedInContent) {
        // Keep only the first related-links list and cut any trailing duplicated tail.
        $normalized = preg_replace(
            '~(<ul[^>]*class\s*=\s*["\'][^"\']*related-links[^"\']*["\'][^>]*>.*?</ul>).*~is',
            '$1',
            $contentHtml,
            1
        );
        if (is_string($normalized) && $normalized !== '') {
            return trim($normalized);
        }
        return trim($contentHtml);
    }
    if (empty($related)) {
        return trim($contentHtml);
    }
    $title = $isRu ? 'Связанные материалы' : 'Related reads';
    $list = "<h3>{$title}</h3><ul class=\"related-links\">";
    foreach ($related as $item) {
        $href = trim((string)($item['url'] ?? ''));
        if ($href === '') {
            $href = '/blog/' . rawurlencode((string)($item['cluster_code'] ?? 'general')) . '/' . rawurlencode((string)($item['slug'] ?? '')) . '/';
        }
        $href = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8');
        $list .= '<li><a href="' . $href . '">' . $label . '</a></li>';
    }
    $list .= '</ul>';
    return rtrim($contentHtml) . "\n\n" . $list;
}

function seo_ensure_cta(string $contentHtml, bool $isRu): string
{
    if (strpos($contentHtml, '/services/') !== false) {
        return $contentHtml;
    }
    $cta = $isRu
        ? '<p>Р•СЃР»Рё С…РѕС‚РёС‚Рµ РІРЅРµРґСЂРёС‚СЊ СЌС‚Рё РїРѕРґС…РѕРґС‹ РІ РІР°С€РµРј РїСЂРѕРґСѓРєС‚Рµ, <a href="/services/">СЃРѕР·РґР°Р№С‚Рµ Р°РєРєР°СѓРЅС‚</a> Рё РїСЂРѕС‚РµСЃС‚РёСЂСѓР№С‚Рµ API РЅР° СЂРµР°Р»СЊРЅРѕРј С‚СЂР°С„РёРєРµ.</p>'
        : '<p>To implement these patterns in production, <a href="/services/">create an account</a> and test the API on real traffic.</p>';
    return rtrim($contentHtml) . "\n\n" . $cta;
}

function seo_fallback_topic_bans(array $recent, int $limit = 30): array
{
    $limit = max(5, min(80, $limit));
    $bans = [];
    $seen = [];
    foreach ($recent as $row) {
        $title = trim((string)($row['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $topic = (string)preg_replace('/\s+/u', ' ', strip_tags($title));
        $topic = trim($topic, " \t\n\r\0\x0B.,;:!?-");
        if ($topic === '') {
            continue;
        }
        if (mb_strlen($topic) > 120) {
            $topic = trim(mb_substr($topic, 0, 120));
        }
        $key = mb_strtolower($topic);
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $bans[] = $topic;
        if (count($bans) >= $limit) {
            break;
        }
    }
    return $bans;
}

function seo_analyze_existing_topics(
    array $cfg,
    array $recent,
    string $lang,
    array $proxyCandidates,
    ?string &$usedProxyLabel = null,
    ?array &$analysisLlmTrace = null
): array {
    $analysisLlmTrace = [];
    $usedProxyLabel = null;
    $fallbackBans = seo_fallback_topic_bans($recent, 30);
    if (!(bool)($cfg['topic_analysis_enabled'] ?? true)) {
        return [
            'topic_bans' => $fallbackBans,
            'analysis_summary' => 'Topic analysis is disabled by settings.',
            'used_proxy' => 'none',
            'source' => 'disabled',
        ];
    }
    if (empty($recent)) {
        return [
            'topic_bans' => $fallbackBans,
            'analysis_summary' => 'No prior articles found in /examples/ for this language/domain.',
            'used_proxy' => 'none',
            'source' => 'fallback-empty',
        ];
    }

    $analysisDomain = seo_host_for_lang($lang);
    $analysisDomain = strtolower(preg_replace('/^www\./i', '', $analysisDomain));
    $analysisBaseUrl = 'https://' . $analysisDomain;

    $sourceLines = [];
    $topicLimit = max(20, min(300, (int)($cfg['topic_analysis_limit'] ?? 120)));
    foreach ($recent as $idx => $row) {
        if ($idx >= $topicLimit) {
            break;
        }
        $title = trim((string)($row['title'] ?? ''));
        $slug = trim((string)($row['slug'] ?? ''));
        if ($title === '') {
            continue;
        }
        $line = '- ' . $title;
        if ($slug !== '') {
            $clusterCode = examples_normalize_cluster((string)($row['cluster_code'] ?? ''), $lang);
            $line .= ' | ' . $analysisBaseUrl . '/blog/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/';
        }
        $sourceLines[] = $line;
    }
    if (empty($sourceLines)) {
        return [
            'topic_bans' => $fallbackBans,
            'analysis_summary' => 'No title rows available for topic analysis.',
            'used_proxy' => 'none',
            'source' => 'fallback-no-rows',
        ];
    }

    $systemPrompt = trim((string)($cfg['topic_analysis_system_prompt'] ?? ''));
    if ($systemPrompt === '') {
        $systemPrompt = 'You analyze existing blog articles on ' . $analysisDomain . ' and produce strict anti-duplication topic bans for /blog/. Return JSON only.';
    }
    $userPrompt = "Analyze existing /blog/ article topics and output stop-topics for NEW generation.\n"
        . "Language: {$lang}.\n"
        . "Primary site domain for this language: {$analysisDomain}.\n"
        . "Use absolute links based on: {$analysisBaseUrl}.\n"
        . "Return JSON only with fields:\n"
        . "{\n  \"analysis_summary\": \"short summary\",\n  \"topic_bans\": [\"topic 1\", \"topic 2\", \"...\"]\n}\n"
        . "Rules:\n"
        . "1) topic_bans must be unique, concrete, and represent already-covered themes.\n"
        . "2) Include 20-40 items if possible.\n"
        . "3) Avoid vague bans like 'security' or 'api'.\n"
        . "4) No markdown. No extra keys.\n\n"
        . "Existing article titles and absolute URLs:\n"
        . implode("\n", $sourceLines);
    $analysisAppend = trim((string)($cfg['topic_analysis_user_prompt_append'] ?? ''));
    if ($analysisAppend !== '') {
        $userPrompt .= "\n\nAdditional constraints:\n" . $analysisAppend;
    }

    try {
        $callMeta = null;
        $raw = seo_call_openai_with_fallback(
            (string)$cfg['openai_api_key'],
            (string)$cfg['openai_base_url'],
            (string)$cfg['openai_model'],
            (int)$cfg['openai_timeout'],
            $systemPrompt,
            $userPrompt,
            $proxyCandidates,
            (array)($cfg['openai_headers'] ?? []),
            $usedProxyLabel,
            $callMeta
        );
        $json = seo_extract_json($raw);
        $analysisLlmTrace = [
            'request' => [
                'phase' => 'topic_analysis',
                'provider' => (string)($cfg['llm_provider'] ?? ''),
                'base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'model' => (string)($cfg['openai_model'] ?? ''),
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'response' => [
                'raw_content' => $raw,
                'parsed_json' => $json,
                'used_proxy' => (string)($usedProxyLabel ?? 'direct'),
            ],
            'usage' => (array)($callMeta['usage'] ?? []),
        ];
        $summary = trim((string)($json['analysis_summary'] ?? ''));
        $topicBansRaw = is_array($json['topic_bans'] ?? null) ? $json['topic_bans'] : [];
        $topicBans = [];
        $seen = [];
        foreach ($topicBansRaw as $item) {
            $topic = trim((string)$item);
            if ($topic === '') {
                continue;
            }
            if (mb_strlen($topic) > 140) {
                $topic = trim(mb_substr($topic, 0, 140));
            }
            $key = mb_strtolower($topic);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $topicBans[] = $topic;
            if (count($topicBans) >= 60) {
                break;
            }
        }
        if (empty($topicBans)) {
            $topicBans = $fallbackBans;
        } elseif (count($topicBans) < 20) {
            foreach ($fallbackBans as $fb) {
                $key = mb_strtolower($fb);
                if (isset($seen[$key])) {
                    continue;
                }
                $topicBans[] = $fb;
                $seen[$key] = true;
                if (count($topicBans) >= 30) {
                    break;
                }
            }
        }

        if ($summary === '') {
            $summary = 'Existing topics analyzed from /examples/.';
        }

        return [
            'topic_bans' => $topicBans,
            'analysis_summary' => $summary,
            'used_proxy' => (string)($usedProxyLabel ?? 'direct'),
            'source' => 'llm',
        ];
    } catch (Throwable $e) {
        $analysisLlmTrace = [
            'request' => [
                'phase' => 'topic_analysis',
                'provider' => (string)($cfg['llm_provider'] ?? ''),
                'base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'model' => (string)($cfg['openai_model'] ?? ''),
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'response' => [
                'error' => (string)$e->getMessage(),
            ],
            'usage' => [],
        ];
        return [
            'topic_bans' => $fallbackBans,
            'analysis_summary' => 'Topic analysis fallback used: ' . mb_substr($e->getMessage(), 0, 220),
            'used_proxy' => (string)($usedProxyLabel ?? 'none'),
            'source' => 'fallback-error',
        ];
    }
}

function seo_words_window(string $text, int $maxWords): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    if ($text === '' || $maxWords <= 0) {
        return '';
    }
    $parts = preg_split('/\s+/u', $text);
    if (!is_array($parts) || empty($parts)) {
        return '';
    }
    return trim(implode(' ', array_slice($parts, 0, $maxWords)));
}

function seo_image_extract_story_anchors(string $title, string $excerpt, string $context, string $lang): array
{
    $title = trim((string)preg_replace('/\s+/u', ' ', $title));
    $excerpt = trim((string)preg_replace('/\s+/u', ' ', $excerpt));
    $context = trim((string)preg_replace('/\s+/u', ' ', $context));
    $lang = strtolower(trim($lang));

    $titleParts = preg_split('/\s*[:\-|]\s*/u', $title);
    $titleForConcept = isset($titleParts[0]) ? (string)$titleParts[0] : $title;
    $titleForConcept = trim((string)preg_replace('/[^\p{L}\p{N}\s\/&+]+/u', ' ', $titleForConcept));
    $primaryConcept = seo_words_window($titleForConcept, 8);
    if ($primaryConcept === '') {
        $primaryConcept = seo_words_window($title, 8);
    }
    if ($primaryConcept === '') {
        $primaryConcept = ($lang === 'ru') ? 'РєР»СЋС‡РµРІР°СЏ РёРЅР¶РµРЅРµСЂРЅР°СЏ РёРґРµСЏ СЃС‚Р°С‚СЊРё' : 'the core engineering concept of the article';
    }

    $source = trim($excerpt . ' ' . $context);
    $sentences = preg_split('/(?<=[\.\!\?;])\s+/u', $source);
    if (!is_array($sentences) || empty($sentences)) {
        $sentences = [$source];
    }
    $keywords = ($lang === 'ru')
        ? ['РїСЂРѕРґР°РєС€РµРЅ', 'РІРЅРµРґСЂРµРЅ', 'РёРЅС‚РµРіСЂР°С†', 'РјРѕРЅРёС‚РѕСЂРёРЅРі', 'РЅР°РґРµР¶РЅ', 'РёРЅС†РёРґРµРЅС‚', 'СЂРёСЃ', 'Р±РµР·РѕРїР°СЃ', 'Р±РёР·РЅРµСЃ', 'РєР»РёРµРЅС‚', 'sla', 'ci/cd', 'api']
        : ['production', 'integrat', 'deployment', 'monitoring', 'reliability', 'incident', 'risk', 'security', 'business', 'client', 'sla', 'ci/cd', 'api'];

    $bestSentence = '';
    $bestScore = -1;
    foreach ($sentences as $sentenceRaw) {
        $sentence = trim((string)$sentenceRaw);
        if ($sentence === '') {
            continue;
        }
        $score = 0;
        $lower = mb_strtolower($sentence);
        foreach ($keywords as $kw) {
            if (mb_strpos($lower, $kw) !== false) {
                $score++;
            }
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestSentence = $sentence;
        }
    }
    if ($bestSentence === '') {
        $bestSentence = $excerpt !== '' ? $excerpt : $context;
    }
    $operationalContext = seo_words_window((string)$bestSentence, 14);
    if ($operationalContext === '') {
        $operationalContext = ($lang === 'ru')
            ? 'РѕРїРµСЂР°С†РёРѕРЅРЅС‹Р№ РєРѕРЅС‚РµРєСЃС‚ Рё РїСЂР°РєС‚РёС‡РµСЃРєРёРµ СЂРµС€РµРЅРёСЏ РґР»СЏ РїСЂРѕРґР°РєС€РµРЅР°'
            : 'operational context and practical production constraints';
    }

    return [
        'primary_concept' => $primaryConcept,
        'operational_context' => $operationalContext,
    ];
}

function seo_get_or_build_topic_analysis_for_day(
    mysqli $db,
    array $cfg,
    array $recent,
    string $lang,
    string $jobDate,
    array $proxyCandidates,
    ?string &$usedProxyLabel = null,
    ?array &$analysisLlmTrace = null
): array {
    $analysisLlmTrace = [];
    $usedProxyLabel = null;
    $domain = strtolower(trim((string)seo_host_for_lang($lang)));
    $settingsHash = seo_topic_analysis_settings_hash($cfg, $lang, $domain);
    $cached = seo_topic_analysis_cache_load($db, $jobDate, $lang, $domain, $settingsHash);
    if (is_array($cached)) {
        $cached['source'] = (string)($cached['source'] ?? 'llm-cache');
        $cached['used_proxy'] = 'cache';
        $cached['cache_hit'] = true;
        $usedProxyLabel = 'cache';
        return $cached;
    }

    $analysis = seo_analyze_existing_topics($cfg, $recent, $lang, $proxyCandidates, $usedProxyLabel, $analysisLlmTrace);
    $toStore = [
        'topic_bans' => (array)($analysis['topic_bans'] ?? []),
        'analysis_summary' => (string)($analysis['analysis_summary'] ?? ''),
        'source' => (string)($analysis['source'] ?? 'unknown'),
        'used_proxy' => (string)($analysis['used_proxy'] ?? ''),
    ];
    if ((string)($toStore['source'] ?? '') !== 'fallback-error') {
        seo_topic_analysis_cache_save($db, $jobDate, $lang, $domain, $settingsHash, $toStore);
    }
    $analysis['cache_hit'] = false;
    return $analysis;
}

function seo_select_recent_titles_for_prompt(array $recent, string $clusterSeed, string $lang, int $limit = 30): array
{
    $limit = max(10, min(60, $limit));
    $seed = mb_strtolower(trim($clusterSeed));
    $seed = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $seed);
    $seedTokens = preg_split('/\s+/u', (string)$seed, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($seedTokens)) {
        $seedTokens = [];
    }
    $stop = [
        'and' => true, 'the' => true, 'for' => true, 'with' => true, 'from' => true, 'into' => true, 'your' => true,
        'РєР°Рє' => true, 'РґР»СЏ' => true, 'РїСЂРё' => true, 'С‡С‚Рѕ' => true, 'СЌС‚Рѕ' => true, 'РёР»Рё' => true, 'С‡РµСЂРµР·' => true,
    ];
    $seedTokenMap = [];
    foreach ($seedTokens as $t) {
        $t = mb_strtolower(trim((string)$t));
        if ($t === '' || mb_strlen($t) < 3 || isset($stop[$t])) {
            continue;
        }
        $seedTokenMap[$t] = true;
    }

    $ranked = [];
    foreach ($recent as $idx => $row) {
        $title = trim((string)($row['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $t = mb_strtolower($title);
        $norm = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $t);
        $titleTokens = preg_split('/\s+/u', (string)$norm, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($titleTokens)) {
            $titleTokens = [];
        }
        $matchCount = 0;
        foreach ($titleTokens as $tt) {
            $tt = mb_strtolower(trim((string)$tt));
            if ($tt !== '' && isset($seedTokenMap[$tt])) {
                $matchCount++;
            }
        }
        $recencyBonus = max(0, 40 - (int)$idx) * 0.1;
        $score = ($matchCount * 8.0) + $recencyBonus;
        $ranked[] = ['title' => $title, 'score' => $score, 'idx' => (int)$idx];
    }
    usort($ranked, static function (array $a, array $b): int {
        if ($a['score'] === $b['score']) {
            return $a['idx'] <=> $b['idx'];
        }
        return ($a['score'] > $b['score']) ? -1 : 1;
    });

    $out = [];
    $seen = [];
    foreach ($ranked as $row) {
        $title = trim((string)($row['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $k = mb_strtolower($title);
        if (isset($seen[$k])) {
            continue;
        }
        $seen[$k] = true;
        $out[] = $title;
        if (count($out) >= $limit) {
            break;
        }
    }

    if (empty($out)) {
        foreach ($recent as $row) {
            $title = trim((string)($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $k = mb_strtolower($title);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = $title;
            if (count($out) >= $limit) {
                break;
            }
        }
    }

    return $out;
}

function seo_cfg_text_list(array $cfg, string $key, array $fallback, int $max = 120): array
{
    $raw = $cfg[$key] ?? null;
    $source = is_array($raw) ? $raw : $fallback;
    $out = [];
    $seen = [];
    foreach ((array)$source as $item) {
        $line = trim((string)$item);
        if ($line === '') {
            continue;
        }
        $dedupe = mb_strtolower($line);
        if (isset($seen[$dedupe])) {
            continue;
        }
        $seen[$dedupe] = true;
        $out[] = $line;
        if (count($out) >= $max) {
            break;
        }
    }
    if (empty($out)) {
        return $fallback;
    }
    return $out;
}

function seo_pick_intent_profile(bool $isRu, array $cfg): array
{
    $verticalsEn = [
        'fintech compliance platforms',
        'high-load streaming products',
        'enterprise ERP and CRM integration estates',
        'marketplace trust and safety stacks',
        'multi-tenant B2B SaaS platforms',
        'real-time analytics products',
        'global API ecosystems',
    ];
    $verticalsRu = [
        'fintech compliance platforms',
        'high-load streaming products',
        'enterprise ERP and CRM integration estates',
        'marketplace trust and safety stacks',
        'multi-tenant B2B SaaS platforms',
        'real-time analytics products',
        'global API ecosystems',
    ];
    $scenariosEn = [
        'latency troubleshooting under production load',
        'migration from monolith to modular services',
        'Kubernetes multi-cluster rollout',
        'cost optimization at scale',
        'incident recovery and resilience hardening',
        'backward-compatible API version transition',
        'zero-downtime rollout for critical endpoints',
    ];
    $scenariosRu = $scenariosEn;
    $objectivesEn = [
        'reduce p95 latency without breaking reliability',
        'strengthen policy enforcement and auditability',
        'decrease error rates during peak traffic',
        'improve change-failure rate in releases',
        'cut infra spend while preserving SLO',
        'stabilize integration quality across teams',
        'shorten time-to-debug in incident response',
    ];
    $objectivesRu = $objectivesEn;
    $constraintsEn = [
        'strict compliance and audit requirements',
        'multi-region traffic variability',
        'legacy integration dependencies',
        'limited engineering bandwidth',
        'high-cardinality observability data',
        'SLA and contractual uptime targets',
        'complex stakeholder governance',
    ];
    $constraintsRu = $constraintsEn;
    $artifactsEn = [
        'implementation playbook',
        'troubleshooting guide',
        'migration blueprint',
        'operations runbook',
        'decision memo',
        'architecture checklist',
        'hardening guide',
    ];
    $artifactsRu = $artifactsEn;
    $outcomesEn = [
        'faster incident MTTR and fewer regressions',
        'better conversion and lower operational risk',
        'higher delivery throughput with stable quality',
        'improved reliability without runaway cloud costs',
        'clearer governance with measurable technical KPIs',
        'safer scale-up for enterprise onboarding',
        'stronger retention through platform stability',
    ];
    $outcomesRu = $outcomesEn;

    $verticals = seo_cfg_text_list($cfg, $isRu ? 'intent_verticals_ru' : 'intent_verticals_en', $isRu ? $verticalsRu : $verticalsEn, 150);
    $scenarios = seo_cfg_text_list($cfg, $isRu ? 'intent_scenarios_ru' : 'intent_scenarios_en', $isRu ? $scenariosRu : $scenariosEn, 150);
    $objectives = seo_cfg_text_list($cfg, $isRu ? 'intent_objectives_ru' : 'intent_objectives_en', $isRu ? $objectivesRu : $objectivesEn, 150);
    $constraints = seo_cfg_text_list($cfg, $isRu ? 'intent_constraints_ru' : 'intent_constraints_en', $isRu ? $constraintsRu : $constraintsEn, 150);
    $artifacts = seo_cfg_text_list($cfg, $isRu ? 'intent_artifacts_ru' : 'intent_artifacts_en', $isRu ? $artifactsRu : $artifactsEn, 150);
    $outcomes = seo_cfg_text_list($cfg, $isRu ? 'intent_outcomes_ru' : 'intent_outcomes_en', $isRu ? $outcomesRu : $outcomesEn, 150);

    $pick = static function (array $pool): string {
        if (empty($pool)) {
            return '';
        }
        return (string)$pool[random_int(0, count($pool) - 1)];
    };

    $vertical = $pick($verticals);
    $scenario = $pick($scenarios);
    $objective = $pick($objectives);
    $constraint = $pick($constraints);
    $artifact = $pick($artifacts);
    $outcome = $pick($outcomes);

    $intentHeadline = $isRu
        ? "Intent focus: {$artifact} for {$vertical} - {$scenario}"
        : "Intent focus: {$artifact} for {$vertical} - {$scenario}";
    $intentAngle = $isRu
        ? "Goal: {$objective}; Constraint: {$constraint}; Business outcome: {$outcome}"
        : "Goal: {$objective}; Constraint: {$constraint}; Business outcome: {$outcome}";

    $slugHintParts = array_filter([$vertical, $scenario, $artifact], static fn($v) => trim((string)$v) !== '');
    $slugHint = implode(' ', $slugHintParts);
    $slugHint = mb_strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $slugHint), '-'));
    if ($slugHint === '') {
        $slugHint = 'advanced-intent';
    }

    return [
        'vertical' => $vertical,
        'scenario' => $scenario,
        'objective' => $objective,
        'constraint' => $constraint,
        'artifact' => $artifact,
        'outcome' => $outcome,
        'headline' => $intentHeadline,
        'angle' => $intentAngle,
        'slug_hint' => $slugHint,
    ];
}

function seo_pick_portfolio_stage(array $cfg, bool $isRu): array
{
    $weights = [
        'bofu' => max(0.01, (float)($cfg['portfolio_bofu_weight'] ?? 30)),
        'mofu' => max(0.01, (float)($cfg['portfolio_mofu_weight'] ?? 30)),
        'authority' => max(0.01, (float)($cfg['portfolio_authority_weight'] ?? 20)),
        'case' => max(0.01, (float)($cfg['portfolio_case_weight'] ?? 10)),
        'product' => max(0.01, (float)($cfg['portfolio_product_weight'] ?? 10)),
    ];
    $stage = (string)seo_weighted_pick($weights);
    if ($stage === '') {
        $stage = 'bofu';
    }

    $ruMap = [
        'bofu' => [
            'label' => 'BOFU (коммерческий)',
            'instruction' => 'Фокус на коммерческом интенте: стоимость, сроки, критерии выбора, план внедрения, риски, четкий CTA в услуги/аудит.',
        ],
        'mofu' => [
            'label' => 'MOFU (сравнение и диагностика)',
            'instruction' => 'Фокус на сравнении подходов, диагностике симптомов, чеклистах оценки и критериях принятия решения.',
        ],
        'authority' => [
            'label' => 'Authority (экспертность)',
            'instruction' => 'Глубокий инженерный разбор с практической применимостью, без абстрактного теоретизирования.',
        ],
        'case' => [
            'label' => 'Case (кейсовый)',
            'instruction' => 'Формат кейса: контекст, ограничения, решение, шаги внедрения, метрики до/после, выводы.',
        ],
        'product' => [
            'label' => 'Product (продуктовый)',
            'instruction' => 'Продуктовый угол: сценарий применения, интеграционный путь, ROI-логика, ограничения и критерии готовности.',
        ],
    ];
    $enMap = [
        'bofu' => [
            'label' => 'BOFU (commercial)',
            'instruction' => 'Prioritize commercial intent: pricing factors, timeline, selection criteria, implementation roadmap, risks, and strong services/audit CTA.',
        ],
        'mofu' => [
            'label' => 'MOFU (evaluation)',
            'instruction' => 'Prioritize decision support: approach comparison, symptom diagnostics, evaluation checklists, and acceptance criteria.',
        ],
        'authority' => [
            'label' => 'Authority (expert)',
            'instruction' => 'Provide deep engineering insight with practical utility and avoid abstract generic commentary.',
        ],
        'case' => [
            'label' => 'Case (evidence)',
            'instruction' => 'Use a case-driven flow: context, constraints, solution, rollout steps, before/after metrics, conclusions.',
        ],
        'product' => [
            'label' => 'Product (solution-led)',
            'instruction' => 'Use product-led framing: use-case fit, integration path, ROI logic, constraints, and readiness criteria.',
        ],
    ];
    $map = $isRu ? $ruMap : $enMap;
    $row = $map[$stage] ?? $map['bofu'];

    return [
        'key' => $stage,
        'label' => (string)($row['label'] ?? $stage),
        'instruction' => (string)($row['instruction'] ?? ''),
        'weights' => $weights,
    ];
}

function seo_pick_service_focus(bool $isRu, array $cfg): string
{
    $pool = seo_cfg_text_list(
        $cfg,
        $isRu ? 'service_focus_ru' : 'service_focus_en',
        $isRu
            ? ['разработка сайтов под коммерческие задачи']
            : ['business websites and conversion architecture'],
        180
    );
    if (empty($pool)) {
        return '';
    }
    return (string)$pool[random_int(0, count($pool) - 1)];
}

function seo_generate_article_payload(
    array $recent,
    string $lang,
    int $minWords,
    int $maxWords,
    array $cfg = [],
    array $topicBans = [],
    string $topicAnalysisSummary = '',
    array $internalLinks = []
): array
{
    $isRu = $lang === 'ru';
    $toneVariability = max(0, min(100, (int)($cfg['tone_variability'] ?? 60)));
    $voiceProfiles = [
        [
            'key' => 'executive_brief',
            'w' => 1.0,
            'en' => 'Executive brief: concise, structured, ROI-oriented, low emotional coloring.',
            'ru' => 'Executive brief: concise, structured, ROI-oriented, low emotional coloring.',
        ],
        [
            'key' => 'practical_story',
            'w' => 0.9,
            'en' => 'Practical story mode: short real-world scenes, less formal transitions, still technical.',
            'ru' => 'Practical story mode: short real-world scenes, less formal transitions, still technical.',
        ],
        [
            'key' => 'mentor_voice',
            'w' => 0.75,
            'en' => 'Mentor voice: explain complex points in plain language, with direct recommendations.',
            'ru' => 'Mentor voice: explain complex points in plain language, with direct recommendations.',
        ],
        [
            'key' => 'debate_mode',
            'w' => 0.55,
            'en' => 'Debate mode: compare alternatives and tradeoffs before recommending a path.',
            'ru' => 'Debate mode: compare alternatives and tradeoffs before recommending a path.',
        ],
        [
            'key' => 'field_notes',
            'w' => 0.45,
            'en' => 'Field notes: practical pitfalls, incident-style observations, and what to do first.',
            'ru' => 'Field notes: practical pitfalls, incident-style observations, and what to do first.',
        ],
    ];
    $voiceWeights = [];
    foreach ($voiceProfiles as $vp) {
        $k = (string)$vp['key'];
        $base = (float)$vp['w'];
        $factor = 1.0;
        if ($toneVariability <= 35) {
            $factor = in_array($k, ['executive_brief', 'practical_story'], true) ? 1.2 : 0.45;
        } elseif ($toneVariability <= 70) {
            $factor = in_array($k, ['executive_brief', 'practical_story', 'mentor_voice'], true) ? 1.0 : 0.75;
        } else {
            $factor = in_array($k, ['debate_mode', 'field_notes'], true) ? 1.2 : 0.95;
        }
        $voiceWeights[$k] = max(0.01, $base * $factor);
    }
    $voiceKey = seo_weighted_pick($voiceWeights);
    if ($voiceKey === '') {
        $voiceKey = 'executive_brief';
    }
    $voiceInstructionEn = 'Executive brief: concise, structured, ROI-oriented, low emotional coloring.';
    $voiceInstructionRu = 'Executive brief: concise, structured, ROI-oriented, low emotional coloring.';
    foreach ($voiceProfiles as $vp) {
        if ((string)$vp['key'] === $voiceKey) {
            $voiceInstructionEn = (string)$vp['en'];
            $voiceInstructionRu = (string)$vp['ru'];
            break;
        }
    }
    $stylesRu = ['СЌРєСЃРїРµСЂС‚РЅС‹Р№', 'РїРѕС€Р°РіРѕРІС‹Р№', 'РїСЂР°РєС‚РёС‡РµСЃРєРёР№ playbook', 'Р°РЅР°Р»РёС‚РёС‡РµСЃРєРёР№'];
    $stylesEn = ['technical guide', 'playbook', 'architecture note', 'implementation tutorial'];
    $clustersRu = [
        'scalable B2B SaaS architecture patterns',
        'СЃРЅРёР¶РµРЅРёРµ chargeback С‡РµСЂРµР· РіРµРѕ-СЃРёРіРЅР°Р»С‹',
        'risk-based authentication Рё step-up KYC',
        'РґРµС‚РµРєС‚ multi-account Рё abuse patterns',
        'СЃРЅРёР¶РµРЅРёРµ false positive РІ Р°РЅС‚РёС„СЂРѕРґРµ',
        'business process automation and analytics platforms',
    ];
    $clustersEn = [
        'scalable B2B SaaS architecture patterns',
        'high-availability microservices and reliability engineering',
        'secure API integration for enterprise systems',
        'observability, metrics and operational excellence',
        'DevOps and CI/CD strategies for high-load products',
        'business process automation and analytics platforms',
    ];
    $moodWeights = [
        'technical' => 1.0,
        'b2b_oriented' => 0.9,
        'philosophical' => 0.35,
        'scientific' => 0.7,
        'case_with_examples' => 0.85,
        'historical_entertaining' => 0.2,
    ];
    $moodKey = seo_weighted_pick($moodWeights);
    if ($moodKey === '') {
        $moodKey = 'technical';
    }
    $moodMapRu = [
        'technical' => 'С‚РµС…РЅРёС‡РµСЃРєР°СЏ СЃС‚Р°С‚СЊСЏ',
        'b2b_oriented' => 'b2b РѕСЂРёРµРЅС‚РёСЂРѕРІР°РЅРЅР°СЏ',
        'philosophical' => 'С„РёР»РѕСЃРѕС„СЃРєР°СЏ',
        'scientific' => 'РЅР°СѓС‡РЅР°СЏ',
        'case_with_examples' => 'РєРµР№СЃ СЃ РїСЂРёРјРµСЂР°РјРё',
        'historical_entertaining' => 'РёСЃС‚РѕСЂРёС‡РµСЃРєРѕ-СЂР°Р·РІР»РµРєР°С‚РµР»СЊРЅР°СЏ',
    ];
    $moodMapEn = [
        'technical' => 'technical article',
        'b2b_oriented' => 'B2B-oriented',
        'philosophical' => 'philosophical',
        'scientific' => 'scientific',
        'case_with_examples' => 'case study with examples',
        'historical_entertaining' => 'historical-entertaining',
    ];
    if (!empty($cfg['styles_ru']) && is_array($cfg['styles_ru'])) {
        $stylesRu = array_values(array_filter(array_map('trim', (array)$cfg['styles_ru'])));
    }
    if (!empty($cfg['styles_en']) && is_array($cfg['styles_en'])) {
        $stylesEn = array_values(array_filter(array_map('trim', (array)$cfg['styles_en'])));
    }
    if (!empty($cfg['clusters_ru']) && is_array($cfg['clusters_ru'])) {
        $clustersRu = array_values(array_filter(array_map('trim', (array)$cfg['clusters_ru'])));
    }
    if (!empty($cfg['clusters_en']) && is_array($cfg['clusters_en'])) {
        $clustersEn = array_values(array_filter(array_map('trim', (array)$cfg['clusters_en'])));
    }
    if (empty($stylesRu)) {
        $stylesRu = ['СЌРєСЃРїРµСЂС‚РЅС‹Р№', 'РїРѕС€Р°РіРѕРІС‹Р№', 'РїСЂР°РєС‚РёС‡РµСЃРєРёР№ playbook', 'Р°РЅР°Р»РёС‚РёС‡РµСЃРєРёР№'];
    }
    if (empty($stylesEn)) {
        $stylesEn = ['technical guide', 'playbook', 'architecture note', 'implementation tutorial'];
    }
    if (empty($clustersRu)) {
        $clustersRu = ['scalable B2B SaaS architecture patterns'];
    }
    if (empty($clustersEn)) {
        $clustersEn = ['scalable B2B SaaS architecture patterns'];
    }
    if (!empty($cfg['moods']) && is_array($cfg['moods'])) {
        $moodWeights = [];
        $moodMapRu = [];
        $moodMapEn = [];
        foreach ((array)$cfg['moods'] as $m) {
            if (!is_array($m)) {
                continue;
            }
            $key = trim((string)($m['key'] ?? ''));
            if ($key === '') {
                continue;
            }
            $moodWeights[$key] = max(0.01, (float)($m['weight'] ?? 1.0));
            $moodMapEn[$key] = trim((string)($m['label_en'] ?? $key));
            $moodMapRu[$key] = trim((string)($m['label_ru'] ?? $key));
        }
        if (!empty($moodWeights)) {
            $moodKey = seo_weighted_pick($moodWeights);
            if ($moodKey === '') {
                $moodKey = (string)array_key_first($moodWeights);
            }
        }
    }
    $moodLabel = $isRu ? ($moodMapRu[$moodKey] ?? 'С‚РµС…РЅРёС‡РµСЃРєР°СЏ СЃС‚Р°С‚СЊСЏ') : ($moodMapEn[$moodKey] ?? 'technical article');

    $style = $isRu
        ? $stylesRu[random_int(0, count($stylesRu) - 1)]
        : $stylesEn[random_int(0, count($stylesEn) - 1)];
    $cluster = $isRu
        ? $clustersRu[random_int(0, count($clustersRu) - 1)]
        : $clustersEn[random_int(0, count($clustersEn) - 1)];
    $intentProfile = seo_pick_intent_profile($isRu, $cfg);
    $portfolioStage = seo_pick_portfolio_stage($cfg, $isRu);
    $serviceFocus = seo_pick_service_focus($isRu, $cfg);
    $forbiddenTopics = seo_cfg_text_list(
        $cfg,
        $isRu ? 'forbidden_topics_ru' : 'forbidden_topics_en',
        $isRu ? ['сравнение брендов и конкурентов'] : ['competitor brand comparisons'],
        150
    );
    $clusterCode = seo_detect_cluster_code_v2($cluster, $lang, $cfg);
    $taxonomy = seo_cluster_taxonomy_v2($lang, $cfg);
    $taxonomyWeights = [];
    foreach ($taxonomy as $taxRow) {
        $taxKey = trim((string)($taxRow['key'] ?? ''));
        if ($taxKey === '') {
            continue;
        }
        $taxonomyWeights[$taxKey] = max(0.01, (float)($taxRow['weight'] ?? 1.0));
    }
    if (!empty($taxonomyWeights)) {
        $pickedCluster = (string)seo_weighted_pick($taxonomyWeights);
        if ($pickedCluster !== '') {
            $clusterCode = examples_normalize_cluster($pickedCluster, $lang);
        }
    }
    $structureDefaultsEn = [
        'Introduction -> FAQ grid -> Cases -> Code examples -> Conclusion',
        'Problem framing -> Constraints -> Solution architecture -> Implementation steps -> Checklist',
        'Business context -> Risk scenarios -> Technical deep dive -> Anti-patterns -> Summary',
        'Quick start -> Minimal code path -> Production hardening -> Monitoring -> Next steps',
        'Myth vs reality -> Evidence -> Practical approach -> Reference snippets -> Wrap-up',
        'Before/after comparison -> Migration steps -> Validation -> Rollback plan -> Conclusion',
        'Use case map -> Decision matrix -> Integration blueprint -> QA checklist -> CTA',
        'Incident postmortem style -> Root causes -> Fix design -> Code samples -> Prevention',
        'Playbook format -> Step 1..N -> Metrics -> Common failures -> Final recommendations',
        'Executive summary -> Technical appendix -> Security notes -> Testing strategy -> Conclusion',
    ];
    $structureDefaultsRu = [
        'Р’СЃС‚СѓРїР»РµРЅРёРµ -> РЎРµС‚РєР° РІРѕРїСЂРѕСЃ-РѕС‚РІРµС‚ -> РљРµР№СЃС‹ -> РџСЂРёРјРµСЂС‹ РєРѕРґР° -> Р—Р°РєР»СЋС‡РµРЅРёРµ',
        'РћРїРёСЃР°РЅРёРµ РїСЂРѕР±Р»РµРјС‹ -> РћРіСЂР°РЅРёС‡РµРЅРёСЏ -> РђСЂС…РёС‚РµРєС‚СѓСЂР° СЂРµС€РµРЅРёСЏ -> РЁР°РіРё РІРЅРµРґСЂРµРЅРёСЏ -> Р§РµРєР»РёСЃС‚',
        'Р‘РёР·РЅРµСЃ-РєРѕРЅС‚РµРєСЃС‚ -> РЎС†РµРЅР°СЂРёРё СЂРёСЃРєРѕРІ -> РўРµС…РЅРёС‡РµСЃРєРёР№ СЂР°Р·Р±РѕСЂ -> РђРЅС‚РёРїР°С‚С‚РµСЂРЅС‹ -> РС‚РѕРіРё',
        'Р‘С‹СЃС‚СЂС‹Р№ СЃС‚Р°СЂС‚ -> РњРёРЅРёРјР°Р»СЊРЅС‹Р№ РєРѕРґ -> РЈСЃРёР»РµРЅРёРµ РґР»СЏ production -> РњРѕРЅРёС‚РѕСЂРёРЅРі -> РЎР»РµРґСѓСЋС‰РёРµ С€Р°РіРё',
        'РњРёС„С‹ Рё СЂРµР°Р»СЊРЅРѕСЃС‚СЊ -> Р¤Р°РєС‚С‹ -> РџСЂР°РєС‚РёС‡РµСЃРєРёР№ РїРѕРґС…РѕРґ -> РЎРЅРёРїРїРµС‚С‹ -> Р’С‹РІРѕРґ',
        'Р”Рѕ/РїРѕСЃР»Рµ -> РџР»Р°РЅ РјРёРіСЂР°С†РёРё -> Р’Р°Р»РёРґР°С†РёСЏ -> РџР»Р°РЅ РѕС‚РєР°С‚Р° -> Р—Р°РєР»СЋС‡РµРЅРёРµ',
        'РљР°СЂС‚Р° use-case -> РњР°С‚СЂРёС†Р° СЂРµС€РµРЅРёР№ -> Blueprint РёРЅС‚РµРіСЂР°С†РёРё -> QA-С‡РµРєР»РёСЃС‚ -> CTA',
        'Р¤РѕСЂРјР°С‚ РїРѕСЃС‚РјРѕСЂС‚РµРјР° -> РљРѕСЂРЅРµРІС‹Рµ РїСЂРёС‡РёРЅС‹ -> Р”РёР·Р°Р№РЅ С„РёРєСЃР° -> РџСЂРёРјРµСЂС‹ РєРѕРґР° -> РџСЂРѕС„РёР»Р°РєС‚РёРєР°',
        'Playbook -> РЁР°Рі 1..N -> РњРµС‚СЂРёРєРё -> Р§Р°СЃС‚С‹Рµ РѕС€РёР±РєРё -> Р РµРєРѕРјРµРЅРґР°С†РёРё',
        'Р РµР·СЋРјРµ РґР»СЏ Р±РёР·РЅРµСЃР° -> РўРµС…РЅРёС‡РµСЃРєРѕРµ РїСЂРёР»РѕР¶РµРЅРёРµ -> Security-РЅСЋР°РЅСЃС‹ -> РўРµСЃС‚-СЃС‚СЂР°С‚РµРіРёСЏ -> Р—Р°РєР»СЋС‡РµРЅРёРµ',
    ];
    $structurePool = $isRu
        ? array_values(array_filter(array_map('trim', (array)($cfg['article_structures_ru'] ?? []))))
        : array_values(array_filter(array_map('trim', (array)($cfg['article_structures_en'] ?? []))));
    if (empty($structurePool)) {
        $structurePool = $isRu ? $structureDefaultsRu : $structureDefaultsEn;
    }
    $structure = $structurePool[random_int(0, count($structurePool) - 1)];
    // Floating per-article word limits: each generation gets its own target window.
    $floatingMin = 600;
    $floatingMax = 2000;
    if ($floatingMax < $floatingMin) {
        $floatingMax = $floatingMin;
    }
    $targetWords = random_int($floatingMin, $floatingMax);
    $articleMinWords = max($floatingMin, $targetWords - random_int(90, 220));
    $articleMaxWords = min($floatingMax, $targetWords + random_int(120, 340));
    if ($articleMaxWords <= $articleMinWords) {
        $articleMaxWords = min($floatingMax, $articleMinWords + 140);
    }

    $recentTitles = [];
    $related = [];
    foreach ($recent as $row) {
        $title = trim((string)($row['title'] ?? ''));
        $slug = trim((string)($row['slug'] ?? ''));
        if ($title !== '' && $slug !== '' && count($related) < 10) {
            $clusterCode = examples_normalize_cluster((string)($row['cluster_code'] ?? ''), $lang);
            $related[] = [
                'type' => 'blog',
                'title' => $title,
                'slug' => $slug,
                'cluster_code' => $clusterCode,
                'url' => '/blog/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/'
            ];
        }
    }
    $recentTitles = seo_select_recent_titles_for_prompt($recent, $cluster, $lang, 30);
    foreach ($internalLinks as $item) {
        if (count($related) >= 24) {
            break;
        }
        $title = trim((string)($item['title'] ?? ''));
        $url = trim((string)($item['url'] ?? ''));
        if ($title === '' || $url === '') {
            continue;
        }
        $related[] = [
            'type' => (string)($item['type'] ?? 'internal'),
            'title' => $title,
            'slug' => trim((string)($item['slug'] ?? '')),
            'url' => $url,
        ];
    }
    if (!empty($related)) {
        $seenUrls = [];
        $uniq = [];
        foreach ($related as $r) {
            $u = trim((string)($r['url'] ?? ''));
            if ($u === '' || isset($seenUrls[$u])) {
                continue;
            }
            $seenUrls[$u] = true;
            $uniq[] = $r;
        }
        $related = $uniq;
        shuffle($related);
        $relatedMax = min(10, count($related));
        if ($relatedMax > 0) {
            $relatedMin = min(4, $relatedMax);
            $relatedTake = random_int($relatedMin, $relatedMax);
            $related = array_slice($related, 0, $relatedTake);
        } else {
            $related = [];
        }
    }

    $relatedLines = [];
    foreach ($related as $r) {
        $relatedLines[] = '- ' . $r['title'] . ' -> ' . (string)($r['url'] ?? ('/blog/' . (string)($r['slug'] ?? '')));
    }
    $topicBanLines = [];
    foreach ($topicBans as $topic) {
        $topic = trim((string)$topic);
        if ($topic !== '') {
            $topicBanLines[] = '- ' . $topic;
        }
        if (count($topicBanLines) >= 50) {
            break;
        }
    }

    $systemPrompt = $isRu
        ? 'РўС‹ senior SEO Рё technical writer РґР»СЏ Р»РёС‡РЅРѕРіРѕ СЃР°Р№С‚Р° Р°СЂС…РёС‚РµРєС‚РѕСЂР° СЃРёСЃС‚РµРј. РџРёС€Рё РїСЂР°РєС‚РёС‡РЅС‹Р№, С„Р°РєС‚РѕР»РѕРіРёС‡РЅС‹Р№ Рё РїСЂРёРєР»Р°РґРЅРѕР№ РєРѕРЅС‚РµРЅС‚ РґР»СЏ B2B-Р°СѓРґРёС‚РѕСЂРёРё Р±РµР· РІРѕРґС‹.'
        : 'You are a senior SEO and technical writer for a personal systems architect website. Write practical, factual, high-value B2B content.';
    $articleSystemOverride = trim((string)($isRu
        ? ($cfg['article_system_prompt_ru'] ?? '')
        : ($cfg['article_system_prompt_en'] ?? '')
    ));
    if ($articleSystemOverride !== '') {
        $systemPrompt = $articleSystemOverride;
    }

    $userPrompt = ($isRu
        ? "РЎРіРµРЅРµСЂРёСЂСѓР№ РќРћР’РЈР® SEO-СЃС‚Р°С‚СЊСЋ РЅР° С‚РµРјСѓ: {$cluster}.\n"
          . "РЎС‚РёР»СЊ: {$style}.\n"
          . "Р¦РµР»РµРІР°СЏ РґР»РёРЅР°: РѕРєРѕР»Рѕ {$targetWords} СЃР»РѕРІ (РґРѕРїСѓСЃС‚РёРјРѕ РІ РґРёР°РїР°Р·РѕРЅРµ {$articleMinWords}-{$articleMaxWords}).\n"
          . "РЇР·С‹Рє: СЂСѓСЃСЃРєРёР№.\n\n"
          . "Р–РµСЃС‚РєРёРµ С‚СЂРµР±РѕРІР°РЅРёСЏ:\n"
          . "1) Р’РµСЂРЅРё РўРћР›Р¬РљРћ JSON Р±РµР· markdown.\n"
          . "2) Р¤РѕСЂРјР°С‚ JSON:\n"
          . "{\n  \"title\": \"...\",\n  \"slug\": \"...\",\n  \"excerpt_html\": \"<p>...</p>\",\n  \"content_html\": \"...\"\n}\n"
          . "3) slug: С‚РѕР»СЊРєРѕ Р»Р°С‚РёРЅРёС†Р°, С†РёС„СЂС‹ Рё РґРµС„РёСЃС‹, 8-180 СЃРёРјРІРѕР»РѕРІ.\n"
          . "4) content_html: РІР°Р»РёРґРЅС‹Р№ HTML СЃ Р·Р°РіРѕР»РѕРІРєР°РјРё <h2>/<h3>, СЃРїРёСЃРєР°РјРё, С‚Р°Р±Р»РёС†РµР№ РёР»Рё РєРѕРґ-Р±Р»РѕРєРѕРј РїСЂРё СѓРјРµСЃС‚РЅРѕСЃС‚Рё.\n"
          . "5) Р”РѕР±Р°РІСЊ РјРёРЅРёРјСѓРј 2 РІРЅСѓС‚СЂРµРЅРЅРёС… СЃСЃС‹Р»РєРё РЅР° /blog/... РёР· СЂР°Р·СЂРµС€РµРЅРЅРѕРіРѕ СЃРїРёСЃРєР°.\n"
          . "6) Р”РѕР±Р°РІСЊ РµСЃС‚РµСЃС‚РІРµРЅРЅС‹Р№ CTA Рє /services/.\n"
          . "7) РЎС‚Р°С‚СЊСЏ РґРѕР»Р¶РЅР° РѕС‚РІРµС‡Р°С‚СЊ РїРѕРёСЃРєРѕРІРѕРјСѓ РёРЅС‚РµРЅС‚Сѓ, РїРѕРєСЂС‹РІР°С‚СЊ РєРѕРЅРєСЂРµС‚РЅС‹Рµ РІРѕРїСЂРѕСЃС‹, РґР°РІР°С‚СЊ С‡РµРєР»РёСЃС‚С‹/С€Р°РіРё/Р°РЅС‚РёРїР°С‚С‚РµСЂРЅС‹.\n"
          . "8) РќРµ РїРѕРІС‚РѕСЂСЏР№ Р±Р»РёР·РєРѕ С‚РµРјС‹ Рё Р·Р°РіРѕР»РѕРІРєРё РёР· СЃРїРёСЃРєР° СѓР¶Рµ СЃСѓС‰РµСЃС‚РІСѓСЋС‰РёС… СЃС‚Р°С‚РµР№.\n\n"
          . "9) Р—Р°РїСЂРµС‰РµРЅРѕ СѓРїРѕРјРёРЅР°С‚СЊ Р°Р»СЊС‚РµСЂРЅР°С‚РёРІРЅС‹Рµ СЃРµСЂРІРёСЃС‹, РєРѕРЅРєСѓСЂРµРЅС‚РѕРІ, СЃС‚РѕСЂРѕРЅРЅРёРµ РїР»Р°С‚С„РѕСЂРјС‹ Рё РёРЅСЃС‚СЂСѓРјРµРЅС‚С‹ (РІРєР»СЋС‡Р°СЏ mindmap/mind map, external AI tools, competitor APIs).\n"
          . "10) Р—Р°РїСЂРµС‰РµРЅС‹ РІРЅРµС€РЅРёРµ СЃСЃС‹Р»РєРё РЅР° СЃС‚РѕСЂРѕРЅРЅРёРµ СЂРµСЃСѓСЂСЃС‹. Р”РѕРїСѓСЃС‚РёРјС‹ С‚РѕР»СЊРєРѕ РІРЅСѓС‚СЂРµРЅРЅРёРµ СЃСЃС‹Р»РєРё РІРёРґР° /blog/... Рё /services/.\n"
          . "11) РћСЃРЅРѕРІРЅРѕР№ С„РѕРєСѓСЃ СЃС‚Р°С‚СЊРё: РїСЂР°РєС‚РёС‡РµСЃРєР°СЏ РїРѕР»СЊР·Р° Р°СЂС…РёС‚РµРєС‚СѓСЂРЅС‹С… Рё РїСЂРѕРґСѓРєС‚РѕРІС‹С… СЂРµС€РµРЅРёР№ РґР»СЏ B2B, СЃ vendor-neutral РїРѕРґР°С‡РµР№.\n\n"
          . "РЎСѓС‰РµСЃС‚РІСѓСЋС‰РёРµ Р·Р°РіРѕР»РѕРІРєРё (РёР·Р±РµРіР°Р№ РґСѓР±Р»РµР№):\n"
          . implode("\n", array_map(static function ($t) { return '- ' . $t; }, $recentTitles)) . "\n\n"
          . "Р Р°Р·СЂРµС€РµРЅРЅС‹Рµ РІРЅСѓС‚СЂРµРЅРЅРёРµ СЃСЃС‹Р»РєРё:\n"
          . implode("\n", $relatedLines) . "\n"
        : "Generate a NEW SEO article on: {$cluster}.\n"
          . "Style: {$style}.\n"
          . "Narrative mood (must influence delivery): {$moodLabel}.\n"
          . "Target length: around {$targetWords} words (acceptable range {$articleMinWords}-{$articleMaxWords}).\n"
          . "Language: English.\n\n"
          . "Hard requirements:\n"
          . "1) Return JSON only, no markdown.\n"
          . "2) JSON format:\n"
          . "{\n  \"title\": \"...\",\n  \"slug\": \"...\",\n  \"excerpt_html\": \"<p>...</p>\",\n  \"content_html\": \"...\"\n}\n"
          . "3) slug: latin letters, numbers, dashes only, 8-180 chars.\n"
          . "4) content_html must be valid HTML with <h2>/<h3>, lists, and practical implementation details.\n"
          . "5) Include at least 3 internal links from the allowed list (blog/services/projects), including at least one /blog/ link.\n"
          . "6) Include a natural CTA to /services/.\n"
          . "7) Cover concrete questions users search for and provide checklists, steps, anti-patterns.\n"
          . "8) Avoid near-duplicate topics and titles from existing list.\n\n"
          . "9) Do not mention alternatives, competitors, third-party services, or external tools (including mindmap/mind map and competitor APIs).\n"
          . "10) Do not add external links. Only internal links are allowed: /blog/... /services/... /projects/... and /services/.\n"
          . "11) Keep the article focused on practical B2B architecture decisions, implementation quality and measurable outcomes in a vendor-neutral style.\n\n"
          . "12) Section cluster code for URL taxonomy: {$clusterCode}. Keep the topic strictly aligned with this cluster.\n\n"
          . "13) Apply mood '{$moodLabel}' in examples, vocabulary and pacing while keeping technical usefulness and conversion intent.\n\n"
          . "14) Enforce deep intent diversification: do NOT produce a generic topic like 'How to implement X'. Build the article around the exact intent blueprint below.\n"
          . "15) Title must include at least one concrete qualifier (domain/problem/constraint/migration/optimization/troubleshooting), and must not look generic.\n\n"
          . "Existing titles (avoid duplicates):\n"
          . implode("\n", array_map(static function ($t) { return '- ' . $t; }, $recentTitles)) . "\n\n"
          . "Allowed internal links:\n"
          . implode("\n", $relatedLines) . "\n");

    $userPrompt .= "\nIntent blueprint (mandatory):\n"
        . "- " . (string)($intentProfile['headline'] ?? '') . "\n"
        . "- " . (string)($intentProfile['angle'] ?? '') . "\n"
        . "- Slug hint fragment: " . (string)($intentProfile['slug_hint'] ?? '') . "\n";
    $userPrompt .= "\nPortfolio stage (mandatory): "
        . (string)($portfolioStage['label'] ?? '')
        . " / key=" . (string)($portfolioStage['key'] ?? 'bofu')
        . "\nStage instruction: " . (string)($portfolioStage['instruction'] ?? '') . "\n";
    if (trim($serviceFocus) !== '') {
        $userPrompt .= "Primary service focus (mandatory): " . trim($serviceFocus)
            . "\nEvery section must stay relevant to this service focus and conversion path.\n";
    }
    if (!empty($forbiddenTopics)) {
        $userPrompt .= "\nHard forbidden topic vectors (never use as main angle):\n"
            . implode("\n", array_map(static function ($line): string {
                return '- ' . trim((string)$line);
            }, $forbiddenTopics))
            . "\n";
    }

    if (!empty($topicBanLines)) {
        $userPrompt .= "\nStrict stop-topics (already covered in /blog/, DO NOT REPEAT):\n"
            . implode("\n", $topicBanLines) . "\n";
    }
    if (trim($topicAnalysisSummary) !== '') {
        $userPrompt .= "\nTopic coverage analysis snapshot:\n"
            . trim($topicAnalysisSummary) . "\n";
    }

    if ($isRu) {
        $userPrompt .= "\n\nР”РѕРїРѕР»РЅРёС‚РµР»СЊРЅРѕРµ С‚СЂРµР±РѕРІР°РЅРёРµ РїРѕ С‚РѕРЅСѓ: РёСЃРїРѕР»СЊР·СѓР№ РЅР°СЃС‚СЂРѕРµРЅРёРµ '{$moodLabel}' Рё РїСЂРѕСЏРІР»СЏР№ РµРіРѕ С‡РµСЂРµР· Р»РµРєСЃРёРєСѓ, РїСЂРёРјРµСЂС‹ Рё РїРѕРґР°С‡Сѓ, СЃРѕС…СЂР°РЅСЏСЏ РїСЂР°РєС‚РёС‡РµСЃРєСѓСЋ РїРѕР»СЊР·Сѓ Рё РєРѕРјРјРµСЂС‡РµСЃРєРёР№ С„РѕРєСѓСЃ.";
        $userPrompt .= "\nTone profile: {$voiceInstructionRu}. Variability level: {$toneVariability}/100.";
        if ($toneVariability >= 70) {
            $userPrompt .= "\nAllow moderate narrative freedom: mix short and long paragraphs, add one practical mini-case, keep technical accuracy.";
        }
    } else {
        $userPrompt .= "\n\nAdditional tone requirement: use mood '{$moodLabel}' consistently through vocabulary, examples, and pacing while preserving practical value and sales focus.";
        $userPrompt .= "\nTone profile: {$voiceInstructionEn}. Variability level: {$toneVariability}/100.";
        if ($toneVariability >= 70) {
            $userPrompt .= "\nAllow moderate narrative freedom: mix short and long paragraphs, add one practical mini-case, keep technical accuracy.";
        }
    }
    if ($isRu) {
        $userPrompt .= "\n\nРћР±СЏР·Р°С‚РµР»СЊРЅР°СЏ СЃС…РµРјР° СЃС‚СЂСѓРєС‚СѓСЂС‹ СЃС‚Р°С‚СЊРё (РІР°СЂРёР°РЅС‚ РёР· РіРµРЅРµСЂР°С‚РѕСЂР°):\n"
            . $structure
            . "\nРЎР»РµРґСѓР№ СЌС‚РѕР№ СЃС‚СЂСѓРєС‚СѓСЂРµ Рё РѕС‚СЂР°Р·Рё Р±Р»РѕРєРё С‡РµСЂРµР· Р»РѕРіРёС‡РЅС‹Рµ H2/H3 СЂР°Р·РґРµР»С‹.";
    } else {
        $userPrompt .= "\n\nRequired article structure schema (generator-selected):\n"
            . $structure
            . "\nFollow this structure and map it to coherent H2/H3 sections.";
    }
    $articleAppend = trim((string)($isRu
        ? ($cfg['article_user_prompt_append_ru'] ?? '')
        : ($cfg['article_user_prompt_append_en'] ?? '')
    ));
    if ($articleAppend !== '') {
        $userPrompt .= "\n\n" . $articleAppend;
    }

    return [
        'system_prompt' => $systemPrompt,
        'user_prompt' => $userPrompt,
        'related' => $related,
        'structure' => $structure,
        'cluster_seed' => $cluster,
        'cluster_code' => $clusterCode,
        'voice_profile' => $voiceKey,
        'tone_variability' => $toneVariability,
        'intent_profile' => $intentProfile,
        'portfolio_stage_key' => (string)($portfolioStage['key'] ?? 'bofu'),
        'portfolio_stage_label' => (string)($portfolioStage['label'] ?? ''),
        'service_focus' => (string)$serviceFocus,
        'target_words' => $targetWords,
        'min_words' => $articleMinWords,
        'max_words' => $articleMaxWords,
    ];
}

function seo_expand_short_article(
    array $cfg,
    string $lang,
    string $title,
    string $excerptHtml,
    string $contentHtml,
    int $currentWords,
    int $hardMin,
    array $proxyCandidates,
    ?string &$usedProxyLabel = null,
    ?array &$llmTrace = null
): array {
    $llmTrace = [];
    $isRu = $lang === 'ru';
    $targetWords = max($hardMin + 250, (int)$cfg['word_min']);

    $systemPrompt = $isRu
        ? 'РўС‹ senior technical writer. РўРІРѕСЏ Р·Р°РґР°С‡Р°: РґРѕРїРёСЃР°С‚СЊ СЃСѓС‰РµСЃС‚РІСѓСЋС‰СѓСЋ СЃС‚Р°С‚СЊСЋ РєР°С‡РµСЃС‚РІРµРЅРЅС‹Рј РїСЂРѕРґРѕР»Р¶РµРЅРёРµРј, РЅРµ РїРµСЂРµРїРёСЃС‹РІР°СЏ СЂР°РЅРµРµ РЅР°РїРёСЃР°РЅРЅС‹Р№ С‚РµРєСЃС‚.'
        : 'You are a senior technical writer. Your task is to append a high-quality continuation to an existing article without rewriting prior sections.';
    $expandSystemOverride = trim((string)($isRu
        ? ($cfg['expand_system_prompt_ru'] ?? '')
        : ($cfg['expand_system_prompt_en'] ?? '')
    ));
    if ($expandSystemOverride !== '') {
        $systemPrompt = $expandSystemOverride;
    }

    $contextChars = max(1200, (int)($cfg['expand_context_chars'] ?? 7000));
    $tailHtml = trim(mb_substr($contentHtml, -$contextChars));
    $tailText = trim((string)preg_replace('/\s+/u', ' ', strip_tags($tailHtml)));

    $headings = [];
    if (preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>/is', $contentHtml, $m)) {
        foreach (($m[1] ?? []) as $h) {
            $clean = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)$h)));
            if ($clean !== '' && count($headings) < 18) {
                $headings[] = $clean;
            }
        }
    }
    $headingsText = !empty($headings) ? implode("\n", array_map(static function ($h) { return '- ' . $h; }, $headings)) : '- (no headings)';

    $userPrompt = $isRu
        ? "РќРёР¶Рµ РµСЃС‚СЊ HTML СЃС‚Р°С‚СЊРё. РћРЅР° СЃР»РёС€РєРѕРј РєРѕСЂРѕС‚РєР°СЏ: {$currentWords} СЃР»РѕРІ. РќСѓР¶РЅРѕ СѓРІРµР»РёС‡РёС‚СЊ РјРёРЅРёРјСѓРј РґРѕ {$hardMin} СЃР»РѕРІ (Р»СѓС‡С€Рµ РѕРєРѕР»Рѕ {$targetWords}).\n"
          . "Р’РµСЂРЅРё С‚РѕР»СЊРєРѕ JSON:\n"
          . "{\n  \"append_html\": \"...\",\n  \"excerpt_html\": \"<p>...</p>\"\n}\n"
          . "РўСЂРµР±РѕРІР°РЅРёСЏ:\n"
          . "1) РќРµ РїРµСЂРµРїРёСЃС‹РІР°Р№ Рё РЅРµ РїРѕРІС‚РѕСЂСЏР№ СѓР¶Рµ СЃСѓС‰РµСЃС‚РІСѓСЋС‰РёР№ С‚РµРєСЃС‚. РќСѓР¶РµРЅ С‚РѕР»СЊРєРѕ РќРћР’Р«Р™ С…РІРѕСЃС‚ СЃС‚Р°С‚СЊРё.\n"
          . "2) РЎРѕС…СЂР°РЅРё С‚РµРјСѓ, H2/H3 СЃС‚СЂСѓРєС‚СѓСЂСѓ Рё РїСЂР°РєС‚РёС‡РµСЃРєСѓСЋ РЅР°РїСЂР°РІР»РµРЅРЅРѕСЃС‚СЊ.\n"
          . "3) Р”РѕР±Р°РІСЊ РєРѕРЅРєСЂРµС‚РЅС‹Рµ С€Р°РіРё, С‡РµРєР»РёСЃС‚С‹, Р°РЅС‚РёРїР°С‚С‚РµСЂРЅС‹, РїСЂРёРјРµСЂС‹ РІРЅРµРґСЂРµРЅРёСЏ.\n"
          . "4) РќРµ РґРѕР±Р°РІР»СЏР№ markdown Рё СЃР»СѓР¶РµР±РЅС‹Р№ С‚РµРєСЃС‚.\n"
          . "5) Р’РµСЂРЅРё РІР°Р»РёРґРЅС‹Р№ HTML РІ РїРѕР»Рµ append_html.\n\n"
          . "6) Р—Р°РїСЂРµС‰РµРЅРѕ СѓРїРѕРјРёРЅР°С‚СЊ Р°Р»СЊС‚РµСЂРЅР°С‚РёРІС‹, РєРѕРЅРєСѓСЂРµРЅС‚РѕРІ, СЃС‚РѕСЂРѕРЅРЅРёРµ СЃРµСЂРІРёСЃС‹/РёРЅСЃС‚СЂСѓРјРµРЅС‚С‹ (РІРєР»СЋС‡Р°СЏ mindmap/mind map).\n"
          . "7) Р—Р°РїСЂРµС‰РµРЅС‹ РІРЅРµС€РЅРёРµ СЃСЃС‹Р»РєРё. Р”РѕРїСѓСЃС‚РёРјС‹ С‚РѕР»СЊРєРѕ /blog/... Рё /services/.\n"
          . "8) Р¤РѕРєСѓСЃ РЅР° С†РµРЅРЅРѕСЃС‚Рё, Р°СЂС…РёС‚РµРєС‚СѓСЂРµ Рё РІРЅРµРґСЂРµРЅРёРё РїСЂРѕРґСѓРєС‚РѕРІС‹С… СЂРµС€РµРЅРёР№ РІ vendor-neutral РїРѕРґР°С‡Рµ.\n\n"
          . "TITLE:\n{$title}\n\nEXCERPT_HTML:\n{$excerptHtml}\n\nHEADINGS:\n{$headingsText}\n\nLAST_HTML_CHUNK:\n{$tailHtml}\n\nLAST_TEXT_CHUNK:\n{$tailText}"
        : "Below is an HTML article that is too short: {$currentWords} words. Expand it to at least {$hardMin} words (preferably around {$targetWords}).\n"
          . "Return JSON only:\n"
          . "{\n  \"append_html\": \"...\",\n  \"excerpt_html\": \"<p>...</p>\"\n}\n"
          . "Requirements:\n"
          . "1) Do not rewrite or repeat existing text. Return only NEW continuation content.\n"
          . "2) Preserve topic, H2/H3 structure, and practical value.\n"
          . "3) Add concrete steps, checklists, anti-patterns, and implementation examples.\n"
          . "4) No markdown, no extra wrapper text.\n"
          . "5) Return valid HTML in append_html.\n\n"
          . "6) Do not mention alternatives, competitors, third-party services/tools (including mindmap/mind map).\n"
          . "7) No external links. Allowed links: /blog/... /services/... /projects/... and /services/ only.\n"
          . "8) Keep focus on practical adoption, architecture quality and business value in a vendor-neutral style.\n\n"
          . "TITLE:\n{$title}\n\nEXCERPT_HTML:\n{$excerptHtml}\n\nHEADINGS:\n{$headingsText}\n\nLAST_HTML_CHUNK:\n{$tailHtml}\n\nLAST_TEXT_CHUNK:\n{$tailText}";
    $expandAppend = trim((string)($isRu
        ? ($cfg['expand_user_prompt_append_ru'] ?? '')
        : ($cfg['expand_user_prompt_append_en'] ?? '')
    ));
    if ($expandAppend !== '') {
        $userPrompt .= "\n\n" . $expandAppend;
    }

    $requestPayload = [
        'phase' => 'expand',
        'provider' => (string)($cfg['llm_provider'] ?? ''),
        'base_url' => (string)($cfg['openai_base_url'] ?? ''),
        'model' => (string)($cfg['openai_model'] ?? ''),
        'system_prompt' => $systemPrompt,
        'user_prompt' => $userPrompt,
    ];

    $callMeta = null;
    $raw = seo_call_openai_with_fallback(
        $cfg['openai_api_key'],
        $cfg['openai_base_url'],
        $cfg['openai_model'],
        $cfg['openai_timeout'],
        $systemPrompt,
        $userPrompt,
        $proxyCandidates,
        (array)($cfg['openai_headers'] ?? []),
        $usedProxyLabel,
        $callMeta
    );
    $json = seo_extract_json($raw);
    $llmTrace = [
        'request' => $requestPayload,
        'response' => [
            'raw_content' => $raw,
            'parsed_json' => $json,
            'used_proxy' => (string)($usedProxyLabel ?? 'direct'),
        ],
        'usage' => (array)($callMeta['usage'] ?? []),
    ];
    $appendHtml = seo_clean_html((string)($json['append_html'] ?? ''));
    $nextContent = seo_clean_html((string)($json['content_html'] ?? '')); // fallback for models that ignore append_html
    $nextExcerpt = seo_clean_html((string)($json['excerpt_html'] ?? ''));
    if ($appendHtml === '' && $nextContent === '') {
        throw new RuntimeException('Expand response has empty append_html/content_html');
    }
    if ($nextExcerpt === '') {
        $nextExcerpt = $excerptHtml;
    }
    return [
        'append_html' => $appendHtml,
        'content_html' => $nextContent,
        'excerpt_html' => $nextExcerpt,
    ];
}

function seo_local_expand_fallback_html(string $lang, string $title, int $needWords): string
{
    $needWords = max(80, min(1200, $needWords));
    $isRu = ($lang === 'ru');
    $titleSafe = htmlspecialchars(trim($title), ENT_QUOTES, 'UTF-8');

    $intro = $isRu
        ? '<h2>Практический план внедрения</h2>'
        : '<h2>Practical implementation plan</h2>';

    $chunks = $isRu
        ? [
            '<p>Чтобы перейти от теории к результату, зафиксируйте целевую метрику и горизонт проверки: конверсия, стоимость лида, скорость обработки, доля ошибок. Для темы «' . $titleSafe . '» важно заранее определить, какой сигнал считается успехом и какой порог запускает корректировку процесса.</p>',
            '<p>Разбейте внедрение на короткие итерации: подготовка данных, ограниченный запуск, валидация гипотез, расширение охвата. На каждом шаге ведите журнал решений и причин отклонений, чтобы команда могла быстро воспроизводить результат и не терять контекст при передаче задач между ролями.</p>',
            '<p>Добавьте операционный чеклист: входные условия, критерии качества, допустимые риски, план отката, ответственные по SLA. Такой формат снижает вероятность «тихих» регрессий и помогает масштабировать процесс без роста ручной нагрузки.</p>',
            '<p>Параллельно настройте мониторинг: отдельные события для бизнес-метрик и технической стабильности. Если метрика улучшается, но растёт латентность или увеличивается доля ошибок, фиксируйте компромисс и корректируйте конфигурацию до балансного режима.</p>',
            '<h3>Контроль качества перед масштабированием</h3><ul><li>Проверка полноты входных данных и корректности обогащения.</li><li>Сравнение результата с базовой линией до внедрения.</li><li>Аудит edge-case сценариев и правил эскалации.</li><li>Документирование итоговых порогов и регламентов поддержки.</li></ul>',
            '<p>После стабилизации переведите решение в регулярный цикл улучшений: еженедельный разбор аномалий, обновление порогов и пересмотр приоритетов под текущие бизнес-цели. Это позволяет поддерживать предсказуемый рост качества без резких колебаний в продакшене.</p>',
        ]
        : [
            '<p>To move from theory to measurable outcomes, lock a primary KPI and a validation window first: conversion, lead cost, processing latency, or error rate. For “' . $titleSafe . '”, define upfront what success looks like and which threshold triggers a controlled adjustment.</p>',
            '<p>Run implementation in short iterations: data readiness, limited rollout, hypothesis validation, and coverage expansion. Keep a decision log for every change so the team can reproduce wins and quickly isolate regressions when ownership shifts between roles.</p>',
            '<p>Add an operational checklist with entry criteria, quality gates, acceptable risk envelope, rollback conditions, and SLA ownership. This structure reduces silent regressions and makes scaling predictable without increasing manual overhead.</p>',
            '<p>Set dual-track monitoring: business outcome signals and technical stability signals. If business metrics improve while latency or failures rise, record the tradeoff and tune configuration until both tracks stay within target bounds.</p>',
            '<h3>Pre-scale validation checklist</h3><ul><li>Input completeness and enrichment integrity checks.</li><li>Before/after benchmark against baseline performance.</li><li>Edge-case audit and escalation path verification.</li><li>Documented thresholds and ownership for ongoing support.</li></ul>',
            '<p>After stabilization, switch to a continuous optimization loop: periodic anomaly review, threshold recalibration, and priority updates aligned with current business goals. This keeps delivery quality high while preserving predictable production behavior.</p>',
        ];

    $out = $intro;
    $i = 0;
    while (seo_word_count($out) < $needWords && $i < 20) {
        $out .= "\n\n" . $chunks[$i % count($chunks)];
        $i++;
    }
    return trim($out);
}

function seo_publish_article(
    mysqli $db,
    array $cfg,
    string $lang,
    array $recent,
    bool $dryRun = false,
    ?array $topicAnalysisPrecomputed = null
): array
{
    $isRu = $lang === 'ru';
    $proxyCandidates = seo_proxy_candidates($cfg);
    $topicAnalysisProxyLabel = null;
    $topicAnalysisLlmTrace = [];
    if (is_array($topicAnalysisPrecomputed) && !empty($topicAnalysisPrecomputed)) {
        $topicAnalysis = $topicAnalysisPrecomputed;
        $topicAnalysisProxyLabel = (string)($topicAnalysis['used_proxy'] ?? 'cache');
    } else {
        $topicAnalysis = seo_analyze_existing_topics($cfg, $recent, $lang, $proxyCandidates, $topicAnalysisProxyLabel, $topicAnalysisLlmTrace);
    }
    $articleLlmCalls = [];
    $llmUsageSummary = [
        'requests_total' => 0,
        'prompt_tokens' => 0,
        'completion_tokens' => 0,
        'total_tokens' => 0,
    ];
    if (!empty($topicAnalysisLlmTrace)) {
        $articleLlmCalls[] = $topicAnalysisLlmTrace;
        $llmUsageSummary['requests_total']++;
        $llmUsageSummary['prompt_tokens'] += (int)($topicAnalysisLlmTrace['usage']['prompt_tokens'] ?? 0);
        $llmUsageSummary['completion_tokens'] += (int)($topicAnalysisLlmTrace['usage']['completion_tokens'] ?? 0);
        $llmUsageSummary['total_tokens'] += (int)($topicAnalysisLlmTrace['usage']['total_tokens'] ?? 0);
    }
    $domainForLang = seo_host_for_lang($lang);
    $internalLinks = array_merge(
        seo_fetch_services_links($db, $lang, $domainForLang, 20),
        seo_fetch_projects_links($db, $lang, $domainForLang, 20)
    );
    $payload = seo_generate_article_payload(
        $recent,
        $lang,
        $cfg['word_min'],
        $cfg['word_max'],
        $cfg,
        (array)($topicAnalysis['topic_bans'] ?? []),
        (string)($topicAnalysis['analysis_summary'] ?? ''),
        $internalLinks
    );
    $proxyMode = seo_proxy_mode_label($cfg, $proxyCandidates);
    if (count($proxyCandidates) > 1) {
        shuffle($proxyCandidates);
    }
    $usedProxyLabel = null;
    $mainCallMeta = null;
    $raw = seo_call_openai_with_fallback(
        $cfg['openai_api_key'],
        $cfg['openai_base_url'],
        $cfg['openai_model'],
        $cfg['openai_timeout'],
        $payload['system_prompt'],
        $payload['user_prompt'],
        $proxyCandidates,
        (array)($cfg['openai_headers'] ?? []),
        $usedProxyLabel,
        $mainCallMeta
    );
    $json = seo_extract_json($raw);
    $mainCallTrace = [
        'request' => [
            'phase' => 'article_generate',
            'provider' => (string)($cfg['llm_provider'] ?? ''),
            'base_url' => (string)($cfg['openai_base_url'] ?? ''),
            'model' => (string)($cfg['openai_model'] ?? ''),
            'system_prompt' => (string)($payload['system_prompt'] ?? ''),
            'user_prompt' => (string)($payload['user_prompt'] ?? ''),
        ],
        'response' => [
            'raw_content' => $raw,
            'parsed_json' => $json,
            'used_proxy' => (string)($usedProxyLabel ?? 'direct'),
        ],
        'usage' => (array)($mainCallMeta['usage'] ?? []),
    ];
    $articleLlmCalls[] = $mainCallTrace;
    $llmUsageSummary['requests_total']++;
    $llmUsageSummary['prompt_tokens'] += (int)($mainCallTrace['usage']['prompt_tokens'] ?? 0);
    $llmUsageSummary['completion_tokens'] += (int)($mainCallTrace['usage']['completion_tokens'] ?? 0);
    $llmUsageSummary['total_tokens'] += (int)($mainCallTrace['usage']['total_tokens'] ?? 0);

    $title = trim((string)($json['title'] ?? ''));
    $title = seo_normalize_generated_title_casing($title, $lang);
    $slugRaw = trim((string)($json['slug'] ?? ''));
    $excerptHtml = seo_clean_html((string)($json['excerpt_html'] ?? ''));
    $contentHtml = seo_clean_html((string)($json['content_html'] ?? ''));

    if ($title === '' || $contentHtml === '') {
        throw new RuntimeException('LLM returned empty title/content');
    }

    $clusterProxyLabel = null;
    $clusterLlmTrace = [];
    $clusterResolved = seo_detect_cluster_code_llm_v2(
        $cfg,
        $lang,
        $title,
        $excerptHtml,
        $contentHtml,
        $proxyCandidates,
        $clusterProxyLabel,
        $clusterLlmTrace
    );
    if (!empty($clusterLlmTrace)) {
        $articleLlmCalls[] = $clusterLlmTrace;
        $llmUsageSummary['requests_total']++;
        $llmUsageSummary['prompt_tokens'] += (int)($clusterLlmTrace['usage']['prompt_tokens'] ?? 0);
        $llmUsageSummary['completion_tokens'] += (int)($clusterLlmTrace['usage']['completion_tokens'] ?? 0);
        $llmUsageSummary['total_tokens'] += (int)($clusterLlmTrace['usage']['total_tokens'] ?? 0);
    }
    if (is_string($clusterProxyLabel) && $clusterProxyLabel !== '') {
        $usedProxyLabel = $clusterProxyLabel;
    }

    $slug = examples_slugify($slugRaw);
    if ($slug === '' || !preg_match('/^[a-z0-9-]{8,180}$/', $slug)) {
        $slug = examples_slugify($title);
    }
    if ($slug === '') {
        $slug = 'article-' . gmdate('YmdHis') . '-' . random_int(100, 999);
    }
    $slug = seo_unique_slug($db, $slug, $domainForLang, $lang);
    $clusterCode = examples_normalize_cluster((string)($clusterResolved['cluster_code'] ?? ($payload['cluster_code'] ?? 'b2b')), $lang);
    $clusterSource = (string)($clusterResolved['cluster_source'] ?? 'heuristic');

    if ($excerptHtml === '') {
        $excerptHtml = '<p>' . htmlspecialchars(examples_build_excerpt($contentHtml, 260), ENT_QUOTES, 'UTF-8') . '</p>';
    }

    $contentHtml = seo_ensure_cta($contentHtml, $isRu);
    $words = seo_word_count($contentHtml);
    $initialWords = $words;
    $payloadMinWords = max(600, (int)($payload['min_words'] ?? 0));
    // Require at least ~85% of the per-article lower bound to reduce false fails.
    $hardMin = max(500, (int)floor($payloadMinWords * 0.85));
    $expandRetries = max(0, (int)($cfg['auto_expand_retries'] ?? 1));
    $expandSoftThreshold = max(500, $hardMin - 120);
    $maxExpandAttempts = ($words >= $expandSoftThreshold && $words < $hardMin)
        ? max(1, $expandRetries)
        : $expandRetries;
    $expandAttempt = 0;
    $shortPublishFallbackUsed = false;
    while ($words < $hardMin && $expandAttempt < $maxExpandAttempts) {
        $expandAttempt++;
        $expandProxyLabel = null;
        $expandLlmTrace = [];
        try {
            $expanded = seo_expand_short_article(
                $cfg,
                $lang,
                $title,
                $excerptHtml,
                $contentHtml,
                $words,
                $hardMin,
                $proxyCandidates,
                $expandProxyLabel,
                $expandLlmTrace
            );
            if (!empty($expandLlmTrace)) {
                $articleLlmCalls[] = $expandLlmTrace;
                $llmUsageSummary['requests_total']++;
                $llmUsageSummary['prompt_tokens'] += (int)($expandLlmTrace['usage']['prompt_tokens'] ?? 0);
                $llmUsageSummary['completion_tokens'] += (int)($expandLlmTrace['usage']['completion_tokens'] ?? 0);
                $llmUsageSummary['total_tokens'] += (int)($expandLlmTrace['usage']['total_tokens'] ?? 0);
            }
            if (is_string($expandProxyLabel) && $expandProxyLabel !== '') {
                $usedProxyLabel = $expandProxyLabel;
            }
            $appendHtml = (string)($expanded['append_html'] ?? '');
            if ($appendHtml !== '') {
                $existingText = mb_strtolower(trim((string)preg_replace('/\s+/u', ' ', strip_tags($contentHtml))));
                $appendText = mb_strtolower(trim((string)preg_replace('/\s+/u', ' ', strip_tags($appendHtml))));
                $probe = mb_substr($appendText, 0, 220);
                if ($probe !== '' && mb_strlen($probe) > 80 && mb_strpos($existingText, $probe) !== false) {
                    throw new RuntimeException('Expand append overlaps existing content');
                }
                $contentHtml = rtrim($contentHtml) . "\n\n" . $appendHtml;
            } else {
                $contentHtml = (string)$expanded['content_html']; // fallback mode
            }
            $contentHtml = seo_ensure_cta($contentHtml, $isRu);
            $excerptHtml = (string)$expanded['excerpt_html'];
            $words = seo_word_count($contentHtml);
        } catch (Throwable $e) {
            if ($expandAttempt >= $expandRetries) {
                throw $e;
            }
        }
    }

    if ($words < $hardMin) {
        $fallbackNeed = $hardMin - $words + 40;
        $fallbackAppend = seo_local_expand_fallback_html($lang, $title, $fallbackNeed);
        if ($fallbackAppend !== '') {
            $contentHtml = rtrim($contentHtml) . "\n\n" . $fallbackAppend;
            $contentHtml = seo_ensure_cta($contentHtml, $isRu);
            $words = seo_word_count($contentHtml);
        }
    }

    if ($words < $hardMin) {
        // Fallback mode requested by business: keep and publish the latest
        // generated result even if it did not reach hard minimum after retries.
        $shortPublishFallbackUsed = true;
    }

    // Add related links only after all expand passes are done, otherwise
    // anti-duplicate cleanup may cut newly appended expand content.
    $contentHtml = seo_append_related_links($contentHtml, $payload['related'], $isRu);
    $words = seo_word_count($contentHtml);

    $imageAsset = seo_generate_image_asset($cfg, $lang, [
        'title' => $title,
        'excerpt_html' => $excerptHtml,
        'content_html' => $contentHtml,
    ]);
    $previewImageRequest = (array)($imageAsset['request'] ?? []);
    $previewImageResult = (array)($imageAsset['result'] ?? []);
    $previewImageUrl = trim((string)($imageAsset['url'] ?? ''));
    $previewImageStyle = trim((string)($imageAsset['style'] ?? ''));
    $previewImageData = trim((string)($imageAsset['data_url'] ?? ''));
    $previewImageThumbUrl = '';
    if ($previewImageData === '' && $previewImageUrl !== '') {
        $previewImageData = seo_fetch_image_data_url_with_fallback($previewImageUrl, $cfg);
    }
    [$previewImageUrl, $previewImageData] = seo_normalize_preview_image_fields($previewImageUrl, $previewImageData, 1000);

    if ($dryRun) {
        return [
            'article_id' => 0,
            'title' => $title,
            'slug' => $slug,
            'cluster_code' => $clusterCode,
            'cluster_source' => $clusterSource,
            'excerpt_html' => $excerptHtml,
            'content_html' => $contentHtml,
            'preview_image_url' => $previewImageUrl,
            'preview_image_thumb_url' => $previewImageThumbUrl,
            'preview_image_style' => $previewImageStyle,
            'preview_image_data' => $previewImageData,
            'words' => $words,
            'words_initial' => $initialWords,
            'target_words' => (int)($payload['target_words'] ?? 0),
            'min_words' => (int)($payload['min_words'] ?? 0),
            'max_words' => (int)($payload['max_words'] ?? 0),
            'expand_attempts' => $expandAttempt,
            'short_publish_fallback_used' => $shortPublishFallbackUsed,
            'proxy_mode' => $proxyMode,
            'proxy_used' => (string)($usedProxyLabel ?? 'direct'),
            'topic_analysis_source' => (string)($topicAnalysis['source'] ?? 'unknown'),
            'topic_analysis_proxy' => (string)($topicAnalysisProxyLabel ?? 'none'),
            'topic_bans_count' => count((array)($topicAnalysis['topic_bans'] ?? [])),
            'structure_used' => (string)($payload['structure'] ?? ''),
            'portfolio_stage' => (string)($payload['portfolio_stage_key'] ?? 'bofu'),
            'service_focus' => (string)($payload['service_focus'] ?? ''),
            'topic_analysis_summary' => (string)($topicAnalysis['analysis_summary'] ?? ''),
            'preview_image_request' => $previewImageRequest,
            'preview_image_result' => $previewImageResult,
            'article_request' => ['calls' => $articleLlmCalls],
            'article_result' => [
                'title' => $title,
                'slug' => $slug,
                'cluster_code' => $clusterCode,
                'cluster_source' => $clusterSource,
                'words_initial' => $initialWords,
                'words_final' => $words,
                'portfolio_stage' => (string)($payload['portfolio_stage_key'] ?? 'bofu'),
                'service_focus' => (string)($payload['service_focus'] ?? ''),
                'target_words' => (int)($payload['target_words'] ?? 0),
                'min_words' => (int)($payload['min_words'] ?? 0),
                'max_words' => (int)($payload['max_words'] ?? 0),
                'expand_attempts' => $expandAttempt,
                'short_publish_fallback_used' => $shortPublishFallbackUsed,
            ],
            'llm_usage' => $llmUsageSummary,
            'llm_requests_count' => (int)$llmUsageSummary['requests_total'],
            'llm_prompt_tokens' => (int)$llmUsageSummary['prompt_tokens'],
            'llm_completion_tokens' => (int)$llmUsageSummary['completion_tokens'],
            'llm_total_tokens' => (int)$llmUsageSummary['total_tokens'],
        ];
    }

    $titleSafe = mysqli_real_escape_string($db, $title);
    $slugSafe = mysqli_real_escape_string($db, $slug);
    $clusterSafe = mysqli_real_escape_string($db, $clusterCode);
    $materialSection = in_array((string)($cfg['campaign_material_section'] ?? 'journal'), ['journal', 'playbooks'], true)
        ? (string)$cfg['campaign_material_section']
        : 'journal';
    $materialSectionSafe = mysqli_real_escape_string($db, $materialSection);
    $excerptSafe = mysqli_real_escape_string($db, $excerptHtml);
    $contentSafe = mysqli_real_escape_string($db, $contentHtml);
    $authorSafe = mysqli_real_escape_string($db, $cfg['author_name']);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $domainSafe = mysqli_real_escape_string($db, $domainForLang);

    $insertColumns = [
        'domain_host', 'lang_code', 'title', 'slug', 'excerpt_html', 'content_html',
        'author_name', 'sort_order', 'is_published', 'published_at', 'created_at', 'updated_at',
    ];
    $insertValues = [
        "'{$domainSafe}'", "'{$langSafe}'", "'{$titleSafe}'", "'{$slugSafe}'", "'{$excerptSafe}'", "'{$contentSafe}'",
        "'{$authorSafe}'", '0', '1', 'NOW()', 'NOW()', 'NOW()',
    ];
    if ((bool)($cfg['examples_has_cluster_code'] ?? false)) {
        $insertColumns[] = 'cluster_code';
        $insertValues[] = "'{$clusterSafe}'";
    }
    if ((bool)($cfg['examples_has_material_section'] ?? false)) {
        $insertColumns[] = 'material_section';
        $insertValues[] = "'{$materialSectionSafe}'";
    }

    if ((bool)($cfg['examples_has_is_ai_generated'] ?? false)) {
        $insertColumns[] = 'is_ai_generated';
        $insertValues[] = '1';
    }
    if ((bool)($cfg['examples_has_ai_provider'] ?? false)) {
        $insertColumns[] = 'ai_provider';
        $insertValues[] = "'" . mysqli_real_escape_string($db, (string)($cfg['llm_provider'] ?? 'openai')) . "'";
    }
    if ((bool)($cfg['examples_has_ai_model'] ?? false)) {
        $insertColumns[] = 'ai_model';
        $insertValues[] = "'" . mysqli_real_escape_string($db, (string)($cfg['openai_model'] ?? '')) . "'";
    }
    if ((bool)($cfg['examples_has_ai_prompt_version'] ?? false)) {
        $insertColumns[] = 'ai_prompt_version';
        $insertValues[] = "'" . mysqli_real_escape_string($db, (string)($cfg['prompt_version'] ?? 'cpalnya-generator-v1')) . "'";
    }
    if ((bool)($cfg['examples_has_ai_generated_at'] ?? false)) {
        $insertColumns[] = 'ai_generated_at';
        $insertValues[] = 'NOW()';
    }
    if ((bool)($cfg['examples_has_preview_image_url'] ?? false)) {
        $insertColumns[] = 'preview_image_url';
        $insertValues[] = "'" . mysqli_real_escape_string($db, $previewImageUrl) . "'";
    }
    if ((bool)($cfg['examples_has_preview_image_thumb_url'] ?? false)) {
        $insertColumns[] = 'preview_image_thumb_url';
        $insertValues[] = "'" . mysqli_real_escape_string($db, $previewImageThumbUrl) . "'";
    }
    if ((bool)($cfg['examples_has_preview_image_style'] ?? false)) {
        $insertColumns[] = 'preview_image_style';
        $insertValues[] = "'" . mysqli_real_escape_string($db, $previewImageStyle) . "'";
    }
    if ((bool)($cfg['examples_has_preview_image_data'] ?? false)) {
        $insertColumns[] = 'preview_image_data';
        $insertValues[] = "'" . mysqli_real_escape_string($db, $previewImageData) . "'";
    }

    mysqli_query(
        $db,
        "INSERT INTO examples_articles (" . implode(', ', $insertColumns) . ")
         VALUES (" . implode(', ', $insertValues) . ")"
    );

    $articleId = (int)mysqli_insert_id($db);
    if ($articleId <= 0) {
        throw new RuntimeException('Insert article failed: ' . mysqli_error($db));
    }

    // Save image and thumbnail as files immediately after insert.
    $storedPreview = seo_store_preview_assets_on_disk($cfg, $articleId, $slug, $previewImageUrl, $previewImageData);
    if (!empty($storedPreview['stored'])) {
        $previewImageUrl = (string)($storedPreview['image_url'] ?? $previewImageUrl);
        $previewImageThumbUrl = (string)($storedPreview['thumb_url'] ?? $previewImageThumbUrl);
        $previewImageData = (string)($storedPreview['image_data'] ?? '');
        seo_update_article_preview_image($db, $articleId, $previewImageUrl, $previewImageData, $previewImageStyle, $previewImageThumbUrl);
    }

    return [
        'article_id' => $articleId,
        'title' => $title,
        'slug' => $slug,
        'cluster_code' => $clusterCode,
        'material_section' => $materialSection,
        'cluster_source' => $clusterSource,
        'excerpt_html' => $excerptHtml,
        'content_html' => $contentHtml,
        'preview_image_url' => $previewImageUrl,
        'preview_image_thumb_url' => $previewImageThumbUrl,
        'preview_image_style' => $previewImageStyle,
        'preview_image_data' => $previewImageData,
        'words' => $words,
        'words_initial' => $initialWords,
        'target_words' => (int)($payload['target_words'] ?? 0),
        'min_words' => (int)($payload['min_words'] ?? 0),
        'max_words' => (int)($payload['max_words'] ?? 0),
        'expand_attempts' => $expandAttempt,
        'short_publish_fallback_used' => $shortPublishFallbackUsed,
        'proxy_mode' => $proxyMode,
        'proxy_used' => (string)($usedProxyLabel ?? 'direct'),
        'topic_analysis_source' => (string)($topicAnalysis['source'] ?? 'unknown'),
        'topic_analysis_proxy' => (string)($topicAnalysisProxyLabel ?? 'none'),
        'topic_bans_count' => count((array)($topicAnalysis['topic_bans'] ?? [])),
        'structure_used' => (string)($payload['structure'] ?? ''),
        'portfolio_stage' => (string)($payload['portfolio_stage_key'] ?? 'bofu'),
        'service_focus' => (string)($payload['service_focus'] ?? ''),
        'topic_analysis_summary' => (string)($topicAnalysis['analysis_summary'] ?? ''),
        'preview_image_request' => $previewImageRequest,
        'preview_image_result' => $previewImageResult,
        'article_request' => ['calls' => $articleLlmCalls],
        'article_result' => [
            'title' => $title,
            'slug' => $slug,
            'cluster_code' => $clusterCode,
            'cluster_source' => $clusterSource,
            'words_initial' => $initialWords,
            'words_final' => $words,
            'portfolio_stage' => (string)($payload['portfolio_stage_key'] ?? 'bofu'),
            'service_focus' => (string)($payload['service_focus'] ?? ''),
            'target_words' => (int)($payload['target_words'] ?? 0),
            'min_words' => (int)($payload['min_words'] ?? 0),
            'max_words' => (int)($payload['max_words'] ?? 0),
            'expand_attempts' => $expandAttempt,
            'short_publish_fallback_used' => $shortPublishFallbackUsed,
        ],
        'llm_usage' => $llmUsageSummary,
        'llm_requests_count' => (int)$llmUsageSummary['requests_total'],
        'llm_prompt_tokens' => (int)$llmUsageSummary['prompt_tokens'],
        'llm_completion_tokens' => (int)$llmUsageSummary['completion_tokens'],
        'llm_total_tokens' => (int)$llmUsageSummary['total_tokens'],
    ];
}

$cfg = [
    'llm_provider' => strtolower(trim((string)seo_cfg('LLMProvider', 'openai'))),
    'enabled' => (bool)seo_cfg('SeoArticleCronEnabled', false),
    'langs' => (array)seo_cfg('SeoArticleCronLanguages', ['ru']),
    'daily_min' => (int)seo_cfg('SeoArticleCronDailyMin', 1),
    'daily_max' => (int)seo_cfg('SeoArticleCronDailyMax', 3),
    'word_min' => (int)seo_cfg('SeoArticleCronWordMin', 2000),
    'word_max' => (int)seo_cfg('SeoArticleCronWordMax', 5000),
    'auto_expand_retries' => (int)seo_cfg('SeoArticleCronAutoExpandRetries', 1),
    'expand_context_chars' => (int)seo_cfg('SeoArticleCronExpandContextChars', 7000),
    'author_name' => trim((string)seo_cfg('SeoArticleCronAuthorName', 'CPALNYA Editorial Desk')),
    'domain_host' => strtolower(trim((string)seo_cfg('SeoArticleCronDomainHost', ''))),
    'domain_host_en' => strtolower(trim((string)seo_cfg('SeoArticleCronDomainHostEn', ''))),
    'domain_host_ru' => strtolower(trim((string)seo_cfg('SeoArticleCronDomainHostRu', ''))),
    'max_per_run' => (int)seo_cfg('SeoArticleCronMaxPerRun', 2),
    'seed_salt' => (string)seo_cfg('SeoArticleCronSeedSalt', 'cpalnya-affiliate-content'),
    'notify_schedule' => (bool)seo_cfg('SeoArticleCronNotifySchedule', false),
    'notify_daily_schedule' => (bool)seo_cfg('SeoArticleCronNotifyDailySchedule', true),
    'today_first_delay_min' => (int)seo_cfg('SeoArticleCronTodayFirstDelayMinutes', 15),
    'preview_channel_enabled' => (bool)seo_cfg('SeoArticleTelegramPreviewEnabled', false),
    'preview_channel_chat_id' => trim((string)seo_cfg('SeoArticleTelegramPreviewChatId', '-1003704622762')),
    'preview_image_enabled' => (bool)seo_cfg('SeoArticlePreviewImageEnabled', false),
    'preview_image_model' => trim((string)seo_cfg('SeoArticlePreviewImageModel', '')),
    'preview_image_size' => trim((string)seo_cfg('SeoArticlePreviewImageSize', '768x512')),
    'preview_image_prompt_template' => trim((string)seo_cfg('SeoArticlePreviewImagePromptTemplate', '')),
    'preview_image_anchor_enforced' => (bool)seo_cfg('SeoArticlePreviewImageAnchorEnforced', true),
    'preview_image_anchor_append' => trim((string)seo_cfg('SeoArticlePreviewImageAnchorAppend', '')),
    'preview_post_max_words' => (int)seo_cfg('SeoArticleTelegramPreviewPostMaxWords', 220),
    'preview_caption_max_words' => (int)seo_cfg('SeoArticleTelegramPreviewCaptionMaxWords', 80),
    'preview_post_min_words' => (int)seo_cfg('SeoArticleTelegramPreviewPostMinWords', 70),
    'preview_caption_min_words' => (int)seo_cfg('SeoArticleTelegramPreviewCaptionMinWords', 26),
    'preview_use_llm' => (bool)seo_cfg('SeoArticleTelegramPreviewUseLLM', true),
    'preview_llm_model' => trim((string)seo_cfg('SeoArticleTelegramPreviewModel', '')),
    'preview_context_chars' => (int)seo_cfg('SeoArticleTelegramPreviewContextChars', 14000),
    'indexnow_enabled' => (bool)seo_cfg('IndexNowEnabled', false),
    'indexnow_key' => trim((string)seo_cfg('IndexNowKey', '')),
    'indexnow_key_location' => trim((string)seo_cfg('IndexNowKeyLocation', '')),
    'indexnow_endpoint' => trim((string)seo_cfg('IndexNowEndpoint', '')),
    'indexnow_hosts' => (array)seo_cfg('IndexNowHosts', ['cpalnya.ru']),
    'indexnow_ping_on_publish' => (bool)seo_cfg('IndexNowPingOnPublish', true),
    'indexnow_submit_limit' => (int)seo_cfg('IndexNowSubmitLimit', 100),
    'indexnow_retry_delay_minutes' => (int)seo_cfg('IndexNowRetryDelayMinutes', 15),
    'telegram_autopost_enabled' => false,
    'prompt_version' => trim((string)seo_cfg('SeoArticleCronPromptVersion', 'cpalnya-generator-v1')),
    'tone_variability' => (int)seo_cfg('SeoArticleToneVariability', 60),
    'portfolio_bofu_weight' => 30,
    'portfolio_mofu_weight' => 30,
    'portfolio_authority_weight' => 20,
    'portfolio_case_weight' => 10,
    'portfolio_product_weight' => 10,
    'topic_analysis_enabled' => true,
    'topic_analysis_limit' => 120,
    'topic_analysis_system_prompt' => '',
    'topic_analysis_user_prompt_append' => '',
    'styles_en' => [],
    'styles_ru' => [],
    'clusters_en' => [],
    'clusters_ru' => [],
    'intent_verticals_en' => [],
    'intent_verticals_ru' => [],
    'intent_scenarios_en' => [],
    'intent_scenarios_ru' => [],
    'intent_objectives_en' => [],
    'intent_objectives_ru' => [],
    'intent_constraints_en' => [],
    'intent_constraints_ru' => [],
    'intent_artifacts_en' => [],
    'intent_artifacts_ru' => [],
    'intent_outcomes_en' => [],
    'intent_outcomes_ru' => [],
    'service_focus_en' => [],
    'service_focus_ru' => [],
    'forbidden_topics_en' => [],
    'forbidden_topics_ru' => [],
    'article_cluster_taxonomy_en' => [],
    'article_cluster_taxonomy_ru' => [],
    'article_structures_en' => [],
    'article_structures_ru' => [],
    'moods' => [],
    'article_system_prompt_en' => '',
    'article_system_prompt_ru' => '',
    'article_user_prompt_append_en' => '',
    'article_user_prompt_append_ru' => '',
    'expand_system_prompt_en' => '',
    'expand_system_prompt_ru' => '',
    'expand_user_prompt_append_en' => '',
    'expand_user_prompt_append_ru' => '',
    'preview_image_style_options' => [],
    'image_color_schemes' => [],
    'image_scene_families' => [],
    'image_compositions' => [],
    'image_scenarios' => [],
    'openrouter_api_key' => trim((string)seo_cfg('OpenRouterApiKey', '')),
    'openrouter_base_url' => trim((string)seo_cfg('OpenRouterBaseUrl', 'https://openrouter.ai/api/v1')),
    'openrouter_model' => trim((string)seo_cfg('OpenRouterModel', 'openai/gpt-4o-2024-11-20')),
    'openrouter_fallback_model' => trim((string)seo_cfg('OpenRouterFallbackModel', 'google/gemini-2.0-flash-001')),
    'openai_api_key' => trim((string)seo_cfg('OpenAIApiKey', '')),
    'openai_base_url' => trim((string)seo_cfg('OpenAIBaseUrl', 'https://api.openai.com/v1')),
    'openai_model' => trim((string)seo_cfg('OpenAIModel', 'gpt-4.1-mini')),
    'openai_timeout' => (int)seo_cfg('OpenAIHttpTimeout', 120),
    'openai_headers' => [],
    'openai_proxy' => [
        'enabled' => (bool)seo_cfg('OpenAIProxyEnabled', false),
        'host' => trim((string)seo_cfg('OpenAIProxyHost', '')),
        'port' => (int)seo_cfg('OpenAIProxyPort', 0),
        'type' => strtolower(trim((string)seo_cfg('OpenAIProxyType', 'http'))),
        'username' => (string)seo_cfg('OpenAIProxyUsername', ''),
        'password' => (string)seo_cfg('OpenAIProxyPassword', ''),
    ],
    'openai_proxy_pool_enabled' => (bool)seo_cfg('OpenAIProxyPoolEnabled', false),
    'openai_proxy_pool' => (array)seo_cfg('OpenAIProxyPool', []),
];

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!($DB instanceof mysqli)) {
    seo_echo('DB connection is not available.');
    exit(1);
}
if (function_exists('seo_gen_settings_get')) {
    $dbSettings = seo_gen_settings_get($DB);
    if (is_array($dbSettings) && !empty($dbSettings)) {
        $cfg = seo_apply_db_settings_to_cfg($cfg, $dbSettings);
    }
}

if ($cfg['llm_provider'] === 'openrouter') {
    $cfg['openai_api_key'] = trim((string)($cfg['openrouter_api_key'] ?? ''));
    $cfg['openai_base_url'] = trim((string)($cfg['openrouter_base_url'] ?? 'https://openrouter.ai/api/v1'));
    $cfg['openai_model'] = trim((string)($cfg['openrouter_model'] ?? 'openai/gpt-4o-2024-11-20'));
    if (empty($cfg['openai_headers'])) {
        $ref = trim((string)seo_cfg('OpenRouterHttpReferer', ''));
        $title = trim((string)seo_cfg('OpenRouterAppTitle', 'CPALNYA Content Generator'));
        $headers = [];
        if ($ref !== '') {
            $headers[] = 'HTTP-Referer: ' . $ref;
        }
        if ($title !== '') {
            $headers[] = 'X-Title: ' . $title;
        }
        $cfg['openai_headers'] = $headers;
    }
    $cfg['openrouter_fallback_model'] = trim((string)($cfg['openrouter_fallback_model'] ?? 'google/gemini-2.0-flash-001'));
    if ($cfg['openrouter_fallback_model'] === '') {
        $cfg['openrouter_fallback_model'] = 'google/gemini-2.0-flash-001';
    }
}

$cfg['domain_host'] = strtolower(trim((string)($cfg['domain_host'] ?? '')));
$cfg['domain_host'] = preg_replace('/^www\./i', '', $cfg['domain_host']);
$cfg['domain_host_en'] = strtolower(trim((string)($cfg['domain_host_en'] ?? '')));
$cfg['domain_host_en'] = preg_replace('/^www\./i', '', $cfg['domain_host_en']);
$cfg['domain_host_ru'] = strtolower(trim((string)($cfg['domain_host_ru'] ?? '')));
$cfg['domain_host_ru'] = preg_replace('/^www\./i', '', $cfg['domain_host_ru']);
if ($cfg['domain_host'] === '') {
    $cfg['domain_host'] = 'cpalnya.ru';
}
$cfg['domain_host_en'] = $cfg['domain_host_en'] !== '' ? $cfg['domain_host_en'] : 'cpalnya.ru';
$cfg['domain_host_ru'] = $cfg['domain_host_ru'] !== '' ? $cfg['domain_host_ru'] : $cfg['domain_host'];
$GLOBALS['SeoArticlePrimaryHost'] = $cfg['domain_host'];
$GLOBALS['SeoArticleHosts'] = [
    'en' => $cfg['domain_host_en'],
    'ru' => $cfg['domain_host_ru'],
];
$GLOBALS['SeoArticleTelegramHardDisable'] = false;

$GLOBALS['SeoOpenRouterFallbackModel'] = '';
if ($cfg['llm_provider'] === 'openrouter') {
    $GLOBALS['SeoOpenRouterFallbackModel'] = trim((string)($cfg['openrouter_fallback_model'] ?? ''));
}

$cfg['daily_min'] = max(1, $cfg['daily_min']);
$cfg['daily_max'] = max($cfg['daily_min'], $cfg['daily_max']);
$cfg['word_min'] = max(400, $cfg['word_min']);
$cfg['word_max'] = max($cfg['word_min'], $cfg['word_max']);
$cfg['max_per_run'] = max(1, min(10, $cfg['max_per_run']));
$cfg['preview_post_max_words'] = max(40, min(700, (int)$cfg['preview_post_max_words']));
$cfg['preview_caption_max_words'] = max(20, min(180, (int)$cfg['preview_caption_max_words']));
$cfg['preview_post_min_words'] = max(20, min($cfg['preview_post_max_words'], (int)$cfg['preview_post_min_words']));
$cfg['preview_caption_min_words'] = max(12, min($cfg['preview_caption_max_words'], (int)$cfg['preview_caption_min_words']));
$cfg['preview_context_chars'] = max(2000, min(30000, (int)$cfg['preview_context_chars']));
$cfg['tone_variability'] = max(0, min(100, (int)($cfg['tone_variability'] ?? 60)));
$cfg['portfolio_bofu_weight'] = max(0, min(1000, (int)($cfg['portfolio_bofu_weight'] ?? 30)));
$cfg['portfolio_mofu_weight'] = max(0, min(1000, (int)($cfg['portfolio_mofu_weight'] ?? 30)));
$cfg['portfolio_authority_weight'] = max(0, min(1000, (int)($cfg['portfolio_authority_weight'] ?? 20)));
$cfg['portfolio_case_weight'] = max(0, min(1000, (int)($cfg['portfolio_case_weight'] ?? 10)));
$cfg['portfolio_product_weight'] = max(0, min(1000, (int)($cfg['portfolio_product_weight'] ?? 10)));
$portfolioWeightSum = (int)$cfg['portfolio_bofu_weight']
    + (int)$cfg['portfolio_mofu_weight']
    + (int)$cfg['portfolio_authority_weight']
    + (int)$cfg['portfolio_case_weight']
    + (int)$cfg['portfolio_product_weight'];
if ($portfolioWeightSum <= 0) {
    $cfg['portfolio_bofu_weight'] = 30;
    $cfg['portfolio_mofu_weight'] = 30;
    $cfg['portfolio_authority_weight'] = 20;
    $cfg['portfolio_case_weight'] = 10;
    $cfg['portfolio_product_weight'] = 10;
}
if (!preg_match('/^\d{2,4}x\d{2,4}$/', (string)$cfg['preview_image_size'])) {
    $cfg['preview_image_size'] = '768x512';
} elseif (preg_match('/^(\d{2,4})x(\d{2,4})$/', (string)$cfg['preview_image_size'], $m)) {
    $w = (int)$m[1];
    $h = (int)$m[2];
    if ($w < 512 || $h < 512) {
        $cfg['preview_image_size'] = '768x512';
    } elseif (max($w, $h) > 2048) {
        $cfg['preview_image_size'] = '1536x1024';
    }
}
if ($cfg['preview_llm_model'] === '') {
    $cfg['preview_llm_model'] = (string)$cfg['openai_model'];
}
if ($cfg['author_name'] === '') {
    $cfg['author_name'] = 'CPALNYA Editorial Desk';
}
$cfg['indexnow_enabled'] = (bool)($cfg['indexnow_enabled'] ?? false);
$cfg['indexnow_key'] = trim((string)($cfg['indexnow_key'] ?? ''));
$cfg['indexnow_key_location'] = trim((string)($cfg['indexnow_key_location'] ?? ''));
$cfg['indexnow_endpoint'] = trim((string)($cfg['indexnow_endpoint'] ?? ''));
$cfg['indexnow_ping_on_publish'] = array_key_exists('indexnow_ping_on_publish', $cfg) ? (bool)$cfg['indexnow_ping_on_publish'] : true;
$cfg['indexnow_submit_limit'] = max(1, min(500, (int)($cfg['indexnow_submit_limit'] ?? 100)));
$cfg['indexnow_retry_delay_minutes'] = max(1, min(1440, (int)($cfg['indexnow_retry_delay_minutes'] ?? 15)));
$cfg['indexnow_hosts'] = function_exists('indexnow_clean_host')
    ? array_values(array_unique(array_filter(array_map('indexnow_clean_host', (array)($cfg['indexnow_hosts'] ?? [])))))
    : array_values(array_unique(array_filter(array_map(static function ($v): string {
        $h = strtolower(trim((string)$v));
        $h = preg_replace('/^https?:\/\//i', '', $h);
        $h = trim($h, '/');
        if (strpos($h, ':') !== false) {
            $h = explode(':', $h, 2)[0];
        }
        return preg_match('/^[a-z0-9.-]+$/', $h) ? trim($h, '.') : '';
    }, (array)($cfg['indexnow_hosts'] ?? [])))));
if (empty($cfg['indexnow_hosts'])) {
    $cfg['indexnow_hosts'] = ['cpalnya.ru'];
}

$runtime = seo_runtime_options();
if ($runtime['max_per_run'] !== null) {
    $cfg['max_per_run'] = max(1, min(30, (int)$runtime['max_per_run']));
}
if (!empty($runtime['campaign'])) {
    $cfg = seo_apply_campaign_to_cfg($cfg, $runtime);
}

$langList = [];
foreach ($cfg['langs'] as $langRaw) {
    $lang = examples_normalize_lang((string)$langRaw);
    if (!in_array($lang, $langList, true)) {
        $langList[] = $lang;
    }
}
$cfg['langs'] = $langList ?: ['ru'];
if (!empty($runtime['langs'])) {
    $cfg['langs'] = $runtime['langs'];
}

if (!in_array($cfg['llm_provider'], ['openai', 'openrouter'], true)) {
    $cfg['llm_provider'] = 'openai';
}
if (!$cfg['enabled'] && !$runtime['proxy_check'] && !$runtime['backfill_images']) {
    seo_echo('SEO article cron is disabled in settings.');
    exit(0);
}
if ($cfg['openai_api_key'] === '') {
    seo_echo('LLM API key is empty for provider ' . $cfg['llm_provider'] . '. Check generator settings in DB or fallback config.');
    exit(1);
}
if ($runtime['proxy_check']) {
    $ok = seo_proxy_check($cfg);
    exit($ok > 0 ? 0 : 2);
}

if (!examples_table_exists($DB)) {
    seo_echo('Table examples_articles does not exist. Run examples_articles_setup.sql');
    exit(1);
}
if (!examples_table_has_lang_column($DB)) {
    seo_echo('Column examples_articles.lang_code is missing. Run lang migration first.');
    exit(1);
}
if (!$runtime['backfill_images'] && !seo_table_exists($DB, 'seo_article_cron_runs')) {
    seo_echo('Table seo_article_cron_runs does not exist. Run seo_articles_cron_setup.sql');
    exit(1);
}
seo_ensure_examples_image_columns($DB);
seo_ensure_generator_logs_table($DB);
seo_ensure_topic_analysis_cache_table($DB);
if (function_exists('indexnow_queue_table_ensure')) {
    indexnow_queue_table_ensure($DB);
}
$cfg['examples_has_is_ai_generated'] = seo_table_has_column($DB, 'examples_articles', 'is_ai_generated');
$cfg['examples_has_ai_provider'] = seo_table_has_column($DB, 'examples_articles', 'ai_provider');
$cfg['examples_has_ai_model'] = seo_table_has_column($DB, 'examples_articles', 'ai_model');
$cfg['examples_has_ai_prompt_version'] = seo_table_has_column($DB, 'examples_articles', 'ai_prompt_version');
$cfg['examples_has_ai_generated_at'] = seo_table_has_column($DB, 'examples_articles', 'ai_generated_at');
$cfg['examples_has_cluster_code'] = seo_table_has_column($DB, 'examples_articles', 'cluster_code');
$cfg['examples_has_material_section'] = seo_table_has_column($DB, 'examples_articles', 'material_section');
$cfg['examples_has_preview_image_url'] = seo_table_has_column($DB, 'examples_articles', 'preview_image_url');
$cfg['examples_has_preview_image_thumb_url'] = seo_table_has_column($DB, 'examples_articles', 'preview_image_thumb_url');
$cfg['examples_has_preview_image_style'] = seo_table_has_column($DB, 'examples_articles', 'preview_image_style');
$cfg['examples_has_preview_image_data'] = seo_table_has_column($DB, 'examples_articles', 'preview_image_data');
if ($runtime['backfill_images']) {
    $updated = seo_backfill_missing_images($DB, $cfg, $runtime);
    exit($updated >= 0 ? 0 : 1);
}

$jobDate = $runtime['job_date'] !== '' ? $runtime['job_date'] : date('Y-m-d');
$nowTs = time();
$generated = 0;
$processed = 0;
if ($runtime['force']) {
    seo_echo('Force mode enabled: due-time check is skipped.');
}
if ($runtime['dry_run']) {
    seo_echo('Dry-run mode enabled: articles are generated but not inserted into DB.');
}
seo_echo('Job date: ' . $jobDate);
if (!empty($cfg['campaign_key'])) {
    seo_echo('Campaign: ' . $cfg['campaign_key'] . ' -> section=' . (string)($cfg['campaign_material_section'] ?? 'journal'));
}
seo_echo('LLM provider: ' . $cfg['llm_provider'] . ', model: ' . $cfg['openai_model']);
if ($cfg['llm_provider'] === 'openrouter' && !empty($cfg['openrouter_fallback_model'])) {
    seo_echo('OpenRouter fallback model: ' . (string)$cfg['openrouter_fallback_model']);
}
$startupProxyCandidates = seo_proxy_candidates($cfg);
seo_echo('Proxy mode: ' . seo_proxy_mode_label($cfg, $startupProxyCandidates));
seo_echo('Proxy candidates: ' . implode(', ', array_map('seo_proxy_entry_label', $startupProxyCandidates)));
$isTodayJob = ($jobDate === date('Y-m-d'));
$scheduleExistedAtStart = $runtime['dry_run'] ? true : seo_schedule_exists($DB, $jobDate);
$planningRun = (!$runtime['dry_run'] && !$scheduleExistedAtStart);
$scheduleForSummary = [];
$newScheduledSlots = 0;

foreach ($cfg['langs'] as $lang) {
    $slots = !$runtime['dry_run'] ? seo_fetch_slots($DB, $jobDate, $lang) : [];
    if (empty($slots)) {
        if ($isTodayJob) {
            $delayMin = max(1, min(360, (int)$cfg['today_first_delay_min']));
            $minuteNow = ((int)date('G') * 60) + (int)date('i');
            $startMinute = seo_round_up_15_minute($minuteNow + $delayMin);
            $slots = seo_daily_slots_ranged($jobDate, $lang, $cfg['daily_min'], $cfg['daily_max'], $cfg['seed_salt'], $startMinute);
        } else {
            $slots = seo_daily_slots($jobDate, $lang, $cfg['daily_min'], $cfg['daily_max'], $cfg['seed_salt']);
        }

        if (!$runtime['dry_run']) {
            foreach ($slots as $slotIndex => $plannedAt) {
                $runRow = seo_upsert_slot_run($DB, $jobDate, $lang, (int)$slotIndex, $plannedAt);
                if ((int)($runRow['is_new'] ?? 0) === 1) {
                    $newScheduledSlots++;
                    if (!empty($cfg['notify_schedule'])) {
                        seo_notify_telegram_scheduled($lang, $jobDate, (int)$slotIndex, $plannedAt);
                    }
                }
            }
            $slots = seo_fetch_slots($DB, $jobDate, $lang);
        }
    }

    $scheduleForSummary[$lang] = array_values($slots);
    $recent = seo_fetch_recent_articles($DB, $lang, seo_host_for_lang($lang), 150);
    seo_echo("Lang {$lang}: planned " . count($slots) . ' article slots for ' . $jobDate . '.');
    $topicAnalysisForLang = null;
    if (!empty($slots)) {
        $topicAnalysisTraceCached = [];
        $topicAnalysisProxyCached = null;
        $topicAnalysisForLang = seo_get_or_build_topic_analysis_for_day(
            $DB,
            $cfg,
            $recent,
            $lang,
            $jobDate,
            $startupProxyCandidates,
            $topicAnalysisProxyCached,
            $topicAnalysisTraceCached
        );
        $cacheHit = !empty($topicAnalysisForLang['cache_hit']);
        seo_echo('Lang ' . $lang . ': topic_analysis ' . ($cacheHit ? 'cache-hit' : 'fresh-build')
            . ', source=' . (string)($topicAnalysisForLang['source'] ?? 'unknown')
            . ', bans=' . count((array)($topicAnalysisForLang['topic_bans'] ?? [])));
    }

    foreach ($slots as $slotIndex => $plannedAt) {
        if ($processed >= $cfg['max_per_run']) {
            break;
        }

        $runId = 0;
        $attempts = 1;
        if (!$runtime['dry_run']) {
            $runRow = seo_upsert_slot_run($DB, $jobDate, $lang, (int)$slotIndex, $plannedAt);
            $runId = (int)($runRow['id'] ?? 0);
            $status = (string)($runRow['status'] ?? 'pending');
            $attempts = (int)($runRow['attempts'] ?? 0);

            if ($runId <= 0) {
                continue;
            }
            if ($status === 'success') {
                continue;
            }
            if ($attempts >= 3 && !$runtime['force']) {
                seo_echo("Lang {$lang}, slot {$slotIndex}: skipped (attempt limit reached).");
                continue;
            }
            if ($attempts >= 3 && $runtime['force']) {
                seo_echo("Lang {$lang}, slot {$slotIndex}: force retry despite attempt limit ({$attempts}).");
            }
        }
        $plannedTs = strtotime($plannedAt);
        if (!$runtime['force'] && ($plannedTs === false || $nowTs < $plannedTs)) {
            continue;
        }
        if (!$runtime['dry_run']) {
            $attempts++;
        }
        $processed++;
        try {
            $created = seo_publish_article($DB, $cfg, $lang, $recent, $runtime['dry_run'], $topicAnalysisForLang);
            $imageResult = (array)($created['preview_image_result'] ?? []);
            $imageColor = (string)($imageResult['color_scheme'] ?? '');
            $imageFamily = (string)($imageResult['scene_family'] ?? '');
            $imageComposition = (string)($imageResult['composition'] ?? '');
            $imageScenario = (string)($imageResult['scenario'] ?? '');
            $message = 'Created #' . (int)$created['article_id']
                . ' "' . (string)$created['title'] . '"'
                . ' (words_initial=' . (int)($created['words_initial'] ?? 0)
                . ', words_final=' . (int)$created['words']
                . ', target=' . (int)($created['target_words'] ?? 0)
                . ', range=' . (int)($created['min_words'] ?? 0) . '-' . (int)($created['max_words'] ?? 0)
                . ', expand_attempts=' . (int)($created['expand_attempts'] ?? 0) . ')'
                . ', topic_analysis=' . (string)($created['topic_analysis_source'] ?? 'unknown')
                . ', topic_bans=' . (int)($created['topic_bans_count'] ?? 0)
                . ', cluster=' . (string)($created['cluster_code'] ?? '')
                . ', cluster_source=' . (string)($created['cluster_source'] ?? 'unknown')
                . ', structure=' . (string)($created['structure_used'] ?? '')
                . ', stage=' . (string)($created['portfolio_stage'] ?? 'bofu')
                . ', service_focus=' . (string)($created['service_focus'] ?? '')
                . ', voice=' . (string)($created['voice_profile'] ?? '')
                . ', tone_var=' . (int)($created['tone_variability'] ?? 0)
                . ', image_color=' . $imageColor
                . ', image_family=' . $imageFamily
                . ', image_scenario=' . $imageScenario
                . ', image_composition=' . $imageComposition
                . ', proxy_mode=' . (string)($created['proxy_mode'] ?? 'direct')
                . ', proxy_used=' . (string)($created['proxy_used'] ?? 'direct');
            $articleUrl = seo_article_public_url($lang, (string)($created['slug'] ?? ''), (string)($created['cluster_code'] ?? ''));
            $settingsSnapshot = [
                'llm_provider' => (string)($cfg['llm_provider'] ?? ''),
                'openai_model' => (string)($cfg['openai_model'] ?? ''),
                'openai_base_url' => (string)($cfg['openai_base_url'] ?? ''),
                'preview_image_model' => (string)($cfg['preview_image_model'] ?? ''),
                'preview_image_size' => (string)($cfg['preview_image_size'] ?? ''),
                'image_color_schemes_count' => count((array)($cfg['image_color_schemes'] ?? [])),
                'image_scene_families_count' => count((array)($cfg['image_scene_families'] ?? [])),
                'image_compositions_count' => count((array)($cfg['image_compositions'] ?? [])),
                'image_scenarios_count' => count((array)($cfg['image_scenarios'] ?? [])),
                'prompt_version' => (string)($cfg['prompt_version'] ?? ''),
                'tone_variability' => (int)($cfg['tone_variability'] ?? 60),
                'portfolio_bofu_weight' => (int)($cfg['portfolio_bofu_weight'] ?? 30),
                'portfolio_mofu_weight' => (int)($cfg['portfolio_mofu_weight'] ?? 30),
                'portfolio_authority_weight' => (int)($cfg['portfolio_authority_weight'] ?? 20),
                'portfolio_case_weight' => (int)($cfg['portfolio_case_weight'] ?? 10),
                'portfolio_product_weight' => (int)($cfg['portfolio_product_weight'] ?? 10),
                'service_focus_count' => count((array)($cfg[$lang === 'ru' ? 'service_focus_ru' : 'service_focus_en'] ?? [])),
                'word_min' => (int)($cfg['word_min'] ?? 0),
                'word_max' => (int)($cfg['word_max'] ?? 0),
                'auto_expand_retries' => (int)($cfg['auto_expand_retries'] ?? 0),
            ];
            $tgPreviewResult = ['status' => ($runtime['dry_run'] ? 'skipped_dry_run' : 'not_attempted')];
            if (!$runtime['dry_run']) {
                seo_mark_slot_result($DB, $runId, 'success', $attempts, (int)$created['article_id'], $message);
                $publicUrl = $articleUrl;
                seo_admin_notification_create(
                    $DB,
                    'seo_article_success',
                    'SEO article generated [' . strtoupper($lang) . ']',
                    (string)($created['title'] ?? '') . ' (' . (int)($created['words'] ?? 0) . ' words)',
                    $publicUrl,
                    [
                        'article_id' => (int)$created['article_id'],
                        'title' => (string)($created['title'] ?? ''),
                        'slug' => (string)($created['slug'] ?? ''),
                        'cluster_code' => (string)($created['cluster_code'] ?? ''),
                        'lang' => $lang,
                        'url' => $publicUrl,
                        'admin_edit_url' => '/adminpanel/examples/?edit=' . (int)$created['article_id'],
                        'job_date' => $jobDate,
                        'slot' => (int)$slotIndex,
                    ],
                    'seo_article_success_' . $jobDate . '_' . $lang . '_' . (int)$slotIndex
                );
                // Always notify admin Telegram channel about generation result.
                seo_notify_telegram_success($lang, $created, $jobDate, (int)$slotIndex);
                seo_indexnow_enqueue_article_url($DB, $cfg, $publicUrl, $lang, 'publish');
                $tgPreviewResult = seo_send_preview_post($cfg, $lang, $created);
                seo_echo("Lang {$lang}, slot {$slotIndex}: {$message}");
            } else {
                seo_echo("Lang {$lang}, slot {$slotIndex}: dry-run OK -> {$message}");
            }
            seo_log_generation($DB, [
                'job_date' => $jobDate,
                'lang_code' => $lang,
                'slot_index' => (int)$slotIndex,
                'status' => 'success',
                'is_dry_run' => $runtime['dry_run'],
                'article_id' => (int)($created['article_id'] ?? 0),
                'title' => (string)($created['title'] ?? ''),
                'slug' => (string)($created['slug'] ?? ''),
                'article_url' => $articleUrl,
                'words_final' => (int)($created['words'] ?? 0),
                'words_initial' => (int)($created['words_initial'] ?? 0),
                'structure_used' => (string)($created['structure_used'] ?? ''),
                'topic_analysis_source' => (string)($created['topic_analysis_source'] ?? ''),
                'topic_analysis_summary' => (string)($created['topic_analysis_summary'] ?? ''),
                'topic_bans_count' => (int)($created['topic_bans_count'] ?? 0),
                'image_request' => (array)($created['preview_image_request'] ?? []),
                'image_result' => (array)($created['preview_image_result'] ?? []),
                'article_request' => (array)($created['article_request'] ?? []),
                'article_result' => (array)($created['article_result'] ?? []),
                'llm_usage' => (array)($created['llm_usage'] ?? []),
                'llm_requests_count' => (int)($created['llm_requests_count'] ?? 0),
                'llm_prompt_tokens' => (int)($created['llm_prompt_tokens'] ?? 0),
                'llm_completion_tokens' => (int)($created['llm_completion_tokens'] ?? 0),
                'llm_total_tokens' => (int)($created['llm_total_tokens'] ?? 0),
                'settings_snapshot' => $settingsSnapshot,
                'tg_preview_result' => (array)$tgPreviewResult,
                'error_message' => '',
            ]);
            $generated++;
        } catch (Throwable $e) {
            $err = $e->getMessage();
            seo_log_generation($DB, [
                'job_date' => $jobDate,
                'lang_code' => $lang,
                'slot_index' => (int)$slotIndex,
                'status' => 'failed',
                'is_dry_run' => $runtime['dry_run'],
                'article_id' => 0,
                'title' => '',
                'slug' => '',
                'article_url' => '',
                'words_final' => 0,
                'words_initial' => 0,
                'structure_used' => '',
                'topic_analysis_source' => '',
                'topic_analysis_summary' => '',
                'topic_bans_count' => 0,
                'image_request' => [],
                'image_result' => [],
                'article_request' => [],
                'article_result' => [],
                'llm_usage' => [],
                'llm_requests_count' => 0,
                'llm_prompt_tokens' => 0,
                'llm_completion_tokens' => 0,
                'llm_total_tokens' => 0,
                'settings_snapshot' => [
                    'llm_provider' => (string)($cfg['llm_provider'] ?? ''),
                    'openai_model' => (string)($cfg['openai_model'] ?? ''),
                    'openai_base_url' => (string)($cfg['openai_base_url'] ?? ''),
                    'preview_image_model' => (string)($cfg['preview_image_model'] ?? ''),
                    'preview_image_size' => (string)($cfg['preview_image_size'] ?? ''),
                    'image_color_schemes_count' => count((array)($cfg['image_color_schemes'] ?? [])),
                    'image_scene_families_count' => count((array)($cfg['image_scene_families'] ?? [])),
                    'image_compositions_count' => count((array)($cfg['image_compositions'] ?? [])),
                    'image_scenarios_count' => count((array)($cfg['image_scenarios'] ?? [])),
                    'prompt_version' => (string)($cfg['prompt_version'] ?? ''),
                    'tone_variability' => (int)($cfg['tone_variability'] ?? 60),
                    'portfolio_bofu_weight' => (int)($cfg['portfolio_bofu_weight'] ?? 30),
                    'portfolio_mofu_weight' => (int)($cfg['portfolio_mofu_weight'] ?? 30),
                    'portfolio_authority_weight' => (int)($cfg['portfolio_authority_weight'] ?? 20),
                    'portfolio_case_weight' => (int)($cfg['portfolio_case_weight'] ?? 10),
                    'portfolio_product_weight' => (int)($cfg['portfolio_product_weight'] ?? 10),
                ],
                'tg_preview_result' => ['status' => 'not_attempted_due_generation_error'],
                'error_message' => $err,
            ]);
            if (!$runtime['dry_run']) {
                seo_mark_slot_result($DB, $runId, 'failed', $attempts, null, $err);
                seo_admin_notification_create(
                    $DB,
                    'seo_article_failed',
                    'SEO article generation failed [' . strtoupper($lang) . ']',
                    mb_substr($err, 0, 800),
                    '/adminpanel/examples/',
                    [
                        'lang' => $lang,
                        'job_date' => $jobDate,
                        'slot' => (int)$slotIndex,
                        'error' => $err,
                    ],
                    'seo_article_failed_' . $jobDate . '_' . $lang . '_' . (int)$slotIndex . '_' . (int)$attempts
                );
                // Always notify admin Telegram channel about generation result.
                seo_notify_telegram_failure($lang, $jobDate, (int)$slotIndex, $err);
            }
            seo_echo("Lang {$lang}, slot {$slotIndex}: failed - {$err}");
        }
    }
}

if (!empty($cfg['notify_daily_schedule']) && $newScheduledSlots > 0 && !empty($scheduleForSummary)) {
    seo_notify_telegram_schedule_summary($jobDate, $scheduleForSummary);
}

if (!$runtime['dry_run'] && $generated > 0 && function_exists('page_html_cache_purge_prefix')) {
    $purged = 0;
    $purged += page_html_cache_purge_prefix('/');
    $purged += page_html_cache_purge_prefix('/blog/');
    $purged += page_html_cache_purge_prefix('/services/');
    $purged += page_html_cache_purge_prefix('/projects/');
    seo_echo('HTML cache purge after generation: deleted=' . (int)$purged);
}

seo_echo("Done. Generated this run: {$generated}, attempted: {$processed}");
exit(0);


