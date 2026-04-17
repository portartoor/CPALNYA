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

$seoQueueTimezone = trim((string)($GLOBALS['AppTimezone'] ?? 'Europe/Moscow'));
if ($seoQueueTimezone === '' || @date_default_timezone_set($seoQueueTimezone) === false) {
    date_default_timezone_set('Europe/Moscow');
}

function queue_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function queue_runtime_options(): array
{
    $allowedCampaigns = function_exists('seo_gen_allowed_campaign_keys')
        ? seo_gen_allowed_campaign_keys()
        : ['journal', 'playbooks', 'signals', 'reviews', 'fun'];
    $opts = [
        'mode' => 'help',
        'job_date' => date('Y-m-d'),
        'langs' => ['ru'],
        'force' => true,
        'dry_run' => false,
        'max_per_run' => 1,
        'planned_at' => '',
        'limit' => 2,
        'campaign' => '',
    ];

    $argv = isset($GLOBALS['argv']) && is_array($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
    foreach ($argv as $arg) {
        if (!is_string($arg) || $arg === '' || strpos($arg, '--') !== 0) {
            continue;
        }
        if ($arg === '--ensure') {
            $opts['mode'] = 'ensure';
            continue;
        }
        if ($arg === '--enqueue-test') {
            $opts['mode'] = 'enqueue_test';
            continue;
        }
        if ($arg === '--enqueue-daily') {
            $opts['mode'] = 'enqueue_daily';
            continue;
        }
        if ($arg === '--enqueue') {
            $opts['mode'] = 'enqueue';
            continue;
        }
        if ($arg === '--work') {
            $opts['mode'] = 'work';
            continue;
        }
        if ($arg === '--force') {
            $opts['force'] = true;
            continue;
        }
        if ($arg === '--no-force') {
            $opts['force'] = false;
            continue;
        }
        if ($arg === '--dry-run') {
            $opts['dry_run'] = true;
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
                $langs = [];
                foreach (explode(',', $raw) as $part) {
                    $lang = examples_normalize_lang(trim($part));
                    if (!in_array($lang, $langs, true)) {
                        $langs[] = $lang;
                    }
                }
                if (!empty($langs)) {
                    $opts['langs'] = $langs;
                }
            }
            continue;
        }
        if (strpos($arg, '--max-per-run=') === 0) {
            $val = (int)trim(substr($arg, 14));
            if ($val > 0) {
                $opts['max_per_run'] = max(1, min(30, $val));
            }
            continue;
        }
        if (strpos($arg, '--planned-at=') === 0) {
            $value = trim(substr($arg, 13));
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                $opts['planned_at'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--limit=') === 0) {
            $val = (int)trim(substr($arg, 8));
            if ($val > 0) {
                $opts['limit'] = max(1, min(50, $val));
            }
            continue;
        }
        if (strpos($arg, '--campaign=') === 0) {
            $value = strtolower(trim(substr($arg, 11)));
            if (in_array($value, $allowedCampaigns, true)) {
                $opts['campaign'] = $value;
            }
            continue;
        }
    }

    return $opts;
}

function queue_table_ensure(mysqli $db): bool
{
    $sql = "CREATE TABLE IF NOT EXISTS seo_article_generation_queue (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        job_date DATE NOT NULL,
        lang_code VARCHAR(8) NOT NULL,
        campaign_key VARCHAR(32) NOT NULL DEFAULT '',
        force_mode TINYINT(1) NOT NULL DEFAULT 0,
        dry_run TINYINT(1) NOT NULL DEFAULT 0,
        max_per_run INT NOT NULL DEFAULT 1,
        slot_index INT NOT NULL DEFAULT 0,
        status ENUM('queued','processing','success','failed') NOT NULL DEFAULT 'queued',
        attempts INT NOT NULL DEFAULT 0,
        planned_at DATETIME DEFAULT NULL,
        started_at DATETIME DEFAULT NULL,
        finished_at DATETIME DEFAULT NULL,
        last_exit_code INT NOT NULL DEFAULT 0,
        last_output MEDIUMTEXT NULL,
        last_error TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_status_planned (status, planned_at),
        KEY idx_job_lang (job_date, lang_code, campaign_key, slot_index)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $ok = mysqli_query($db, $sql) !== false;
    if ($ok) {
        $check = mysqli_query($db, "SHOW COLUMNS FROM seo_article_generation_queue LIKE 'campaign_key'");
        $hasCampaignKey = $check && mysqli_num_rows($check) > 0;
        if ($check) {
            mysqli_free_result($check);
        }
        if (!$hasCampaignKey) {
            $ok = mysqli_query($db, "ALTER TABLE seo_article_generation_queue ADD COLUMN campaign_key VARCHAR(32) NOT NULL DEFAULT '' AFTER lang_code") !== false;
        }
        $check = mysqli_query($db, "SHOW COLUMNS FROM seo_article_generation_queue LIKE 'slot_index'");
        $hasSlotIndex = $check && mysqli_num_rows($check) > 0;
        if ($check) {
            mysqli_free_result($check);
        }
        if (!$hasSlotIndex) {
            $ok = mysqli_query($db, "ALTER TABLE seo_article_generation_queue ADD COLUMN slot_index INT NOT NULL DEFAULT 0 AFTER max_per_run") !== false;
        }
    }
    return $ok;
}

function queue_round_up_15_minute(int $minute): int
{
    $minute = max(0, $minute);
    $mod = $minute % 15;
    return $mod === 0 ? $minute : ($minute + (15 - $mod));
}

function queue_seed_unit(string $seed, string $key): float
{
    $hash = hash('sha256', $seed . '|' . $key);
    $chunk = substr($hash, 0, 13);
    $int = hexdec($chunk);
    return max(0.0, min(0.999999, $int / 0x1FFFFFFFFFFFFF));
}

function queue_generate_irregular_slot_minutes(
    string $jobDate,
    string $lang,
    string $salt,
    int $startMinute,
    int $endMinute,
    int $target,
    string $scope = 'full-day'
): array {
    $target = max(1, min(96, $target));
    $startQuarter = max(0, (int)floor($startMinute / 15));
    $endQuarter = min(95, (int)floor($endMinute / 15));
    if ($endQuarter <= $startQuarter) {
        return [queue_round_up_15_minute($startMinute)];
    }

    $available = $endQuarter - $startQuarter + 1;
    if ($target >= $available) {
        $minutes = [];
        for ($quarter = $startQuarter; $quarter <= $endQuarter; $quarter++) {
            $minutes[] = $quarter * 15;
        }
        return $minutes;
    }

    $seed = $jobDate . '|' . $lang . '|' . $salt . '|' . $scope;
    $slots = [];
    $first = $startQuarter + (int)floor(queue_seed_unit($seed, 'first') * min(4, max(1, $available - $target + 1)));
    $slots[] = $first;

    while (count($slots) < $target) {
        $prev = $slots[count($slots) - 1];
        $remainingSlots = $target - count($slots) - 1;
        $minQuarter = min($endQuarter, $prev + 1);
        $maxQuarter = $endQuarter - $remainingSlots;
        if ($maxQuarter < $minQuarter) {
            $maxQuarter = $minQuarter;
        }
        $window = $maxQuarter - $minQuarter + 1;
        $pick = $minQuarter;
        if ($window > 1) {
            $pick += (int)floor(queue_seed_unit($seed, 'slot-' . count($slots)) * $window);
        }
        if ($pick <= $prev) {
            $pick = $prev + 1;
        }
        if ($pick > $endQuarter) {
            $pick = $endQuarter;
        }
        $slots[] = $pick;
    }

    $minutes = array_map(static function (int $slot): int {
        return $slot * 15;
    }, $slots);
    sort($minutes, SORT_NUMERIC);
    return array_values(array_unique($minutes));
}

function queue_clamp_minute_of_day(int $minuteOfDay): int
{
    return max(0, min(23 * 60 + 59, $minuteOfDay));
}

function queue_schedule_window_minutes(string $jobDate, string $lang, string $salt): array
{
    return [9 * 60, 20 * 60 + 59];
}

function queue_daily_slots_ranged(
    string $jobDate,
    string $lang,
    int $minCount,
    int $maxCount,
    string $salt,
    int $startMinute
): array {
    $minCount = max(1, min(96, $minCount));
    $maxCount = max($minCount, min(96, $maxCount));
    $seed = $jobDate . '|' . $lang . '|' . $salt . '|count-ranged';
    $spread = $maxCount - $minCount;
    $target = $minCount + ($spread > 0 ? (int)floor(queue_seed_unit($seed, 'count') * ($spread + 1)) : 0);
    [$windowStartMinute, $windowEndMinute] = queue_schedule_window_minutes($jobDate, $lang, $salt);
    $startMinute = max($windowStartMinute, min($windowEndMinute, queue_clamp_minute_of_day($startMinute)));
    $minutes = queue_generate_irregular_slot_minutes($jobDate, $lang, $salt, $startMinute, $windowEndMinute, $target, 'ranged|' . $startMinute);
    $slots = [];
    $slotIndex = 1;
    foreach ($minutes as $minuteTotal) {
        $hour = (int)floor($minuteTotal / 60);
        $minute = $minuteTotal % 60;
        $slots[$slotIndex] = sprintf('%s %02d:%02d:00', $jobDate, $hour, $minute);
        $slotIndex++;
    }
    return $slots;
}

function queue_seed_salt_for_campaign(array $settings, string $campaignKey): string
{
    $base = trim((string)($settings['seed_salt'] ?? 'seo-article'));
    $campaigns = (array)($settings['campaigns'] ?? []);
    $campaign = (array)($campaigns[$campaignKey] ?? []);
    $suffix = trim((string)($campaign['seed_salt_suffix'] ?? $campaignKey));
    return $suffix !== '' ? ($base . '|' . $suffix) : $base;
}

function queue_upsert_cron_slot(mysqli $db, string $jobDate, string $lang, string $campaignKey, int $slotIndex, string $plannedAt): bool
{
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $campaignSafe = mysqli_real_escape_string($db, $campaignKey);
    $plannedSafe = mysqli_real_escape_string($db, $plannedAt);
    $slotIndex = max(1, $slotIndex);
    $sql = "INSERT INTO seo_article_cron_runs (job_date, lang_code, campaign_key, slot_index, planned_at, status, attempts, created_at, updated_at)
            VALUES ('{$jobDateSafe}', '{$langSafe}', '{$campaignSafe}', {$slotIndex}, '{$plannedSafe}', 'pending', 0, NOW(), NOW())
            ON DUPLICATE KEY UPDATE planned_at = VALUES(planned_at), updated_at = NOW()";
    return mysqli_query($db, $sql) !== false;
}

function queue_fetch_cron_slots(mysqli $db, string $jobDate, string $lang, string $campaignKey): array
{
    $rows = [];
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, $lang);
    $campaignSafe = mysqli_real_escape_string($db, $campaignKey);
    $sql = "SELECT slot_index, planned_at
            FROM seo_article_cron_runs
            WHERE job_date = '{$jobDateSafe}'
              AND lang_code = '{$langSafe}'
              AND campaign_key = '{$campaignSafe}'
            ORDER BY slot_index ASC";
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[(int)($row['slot_index'] ?? 0)] = (string)($row['planned_at'] ?? '');
        }
        mysqli_free_result($res);
    }
    return $rows;
}

function queue_compute_slots(array $settings, string $jobDate, string $lang, string $campaignKey): array
{
    $campaigns = (array)($settings['campaigns'] ?? []);
    $campaign = (array)($campaigns[$campaignKey] ?? []);
    if (empty($campaign) || empty($campaign['enabled'])) {
        return [];
    }

    $dailyMin = max(1, (int)($campaign['daily_min'] ?? 1));
    $dailyMax = max($dailyMin, (int)($campaign['daily_max'] ?? $dailyMin));
    $salt = queue_seed_salt_for_campaign($settings, $campaignKey);
    $isTodayJob = ($jobDate === date('Y-m-d'));

    if ($isTodayJob) {
        $delayMin = max(1, min(360, (int)($settings['today_first_delay_min'] ?? 15)));
        $minuteNow = ((int)date('G') * 60) + (int)date('i');
        $startMinute = queue_round_up_15_minute($minuteNow + $delayMin);
        return queue_daily_slots_ranged($jobDate, $lang, $dailyMin, $dailyMax, $salt, $startMinute);
    }

    [$windowStartMinute] = queue_schedule_window_minutes($jobDate, $lang, $salt);
    return queue_daily_slots_ranged($jobDate, $lang, $dailyMin, $dailyMax, $salt, $windowStartMinute);
}

function queue_add_task(
    mysqli $db,
    string $jobDate,
    string $lang,
    string $campaignKey,
    bool $force,
    bool $dryRun,
    int $maxPerRun,
    ?string $plannedAt = null,
    int $slotIndex = 0
): bool {
    $allowedCampaigns = function_exists('seo_gen_allowed_campaign_keys')
        ? seo_gen_allowed_campaign_keys()
        : ['journal', 'playbooks', 'signals', 'reviews', 'fun'];
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $langSafe = mysqli_real_escape_string($db, examples_normalize_lang($lang));
    $campaignSafe = mysqli_real_escape_string($db, in_array($campaignKey, $allowedCampaigns, true) ? $campaignKey : '');
    $slotIndex = max(0, $slotIndex);
    $plannedSql = $plannedAt !== null && $plannedAt !== ''
        ? "'" . mysqli_real_escape_string($db, $plannedAt) . "'"
        : 'NOW()';
    $sql = "INSERT INTO seo_article_generation_queue
            (job_date, lang_code, campaign_key, force_mode, dry_run, max_per_run, slot_index, status, attempts, planned_at, created_at, updated_at)
            VALUES
            ('{$jobDateSafe}', '{$langSafe}', '{$campaignSafe}', " . ($force ? 1 : 0) . ", " . ($dryRun ? 1 : 0) . ", " . (int)$maxPerRun . ", {$slotIndex}, 'queued', 0, {$plannedSql}, NOW(), NOW())";
    return mysqli_query($db, $sql) !== false;
}

function queue_add_daily_if_missing(mysqli $db, array $langs, string $jobDate): int
{
    $added = 0;
    $jobDateSafe = mysqli_real_escape_string($db, $jobDate);
    $settings = function_exists('seo_gen_settings_get') ? seo_gen_settings_get($db) : [];
    $campaigns = (array)($settings['campaigns'] ?? (function_exists('seo_gen_default_campaigns') ? seo_gen_default_campaigns() : []));
    if (function_exists('seo_gen_cron_runs_table_ensure')) {
        seo_gen_cron_runs_table_ensure($db);
    }
    foreach ($langs as $langRaw) {
        $lang = examples_normalize_lang((string)$langRaw);
        $langSafe = mysqli_real_escape_string($db, $lang);
        foreach ($campaigns as $campaignKey => $campaign) {
            if (empty($campaign['enabled'])) {
                continue;
            }
            $campaignSafe = mysqli_real_escape_string($db, $campaignKey);
            $slots = queue_fetch_cron_slots($db, $jobDate, $lang, $campaignKey);
            if (empty($slots)) {
                $slots = queue_compute_slots($settings, $jobDate, $lang, $campaignKey);
                foreach ($slots as $slotIndex => $plannedAt) {
                    queue_upsert_cron_slot($db, $jobDate, $lang, $campaignKey, (int)$slotIndex, $plannedAt);
                }
                $slots = queue_fetch_cron_slots($db, $jobDate, $lang, $campaignKey);
            }
            foreach ($slots as $slotIndex => $plannedAt) {
                $slotIndex = (int)$slotIndex;
                if ($slotIndex <= 0 || trim((string)$plannedAt) === '') {
                    continue;
                }
                $sql = "SELECT id
                        FROM seo_article_generation_queue
                        WHERE job_date = '{$jobDateSafe}'
                          AND lang_code = '{$langSafe}'
                          AND campaign_key = '{$campaignSafe}'
                          AND slot_index = {$slotIndex}
                          AND status IN ('queued','processing','success')
                        ORDER BY id DESC
                        LIMIT 1";
                $res = mysqli_query($db, $sql);
                $exists = $res && mysqli_num_rows($res) > 0;
                if ($res) {
                    mysqli_free_result($res);
                }
                if ($exists) {
                    continue;
                }
                if (queue_add_task($db, $jobDate, $lang, $campaignKey, false, false, 1, (string)$plannedAt, $slotIndex)) {
                    $added++;
                }
            }
        }
    }
    return $added;
}

function queue_run_generation(array $task): array
{
    $phpBinary = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : 'php';
    $script = DIR . 'cron/generate_seo_articles.php';
    $cmd = escapeshellarg($phpBinary)
        . ' ' . escapeshellarg($script)
        . ' --date=' . escapeshellarg((string)$task['job_date'])
        . ' --lang=' . escapeshellarg((string)$task['lang_code'])
        . ' --max-per-run=' . (int)$task['max_per_run'];
    if (!empty($task['campaign_key'])) {
        $cmd .= ' --campaign=' . escapeshellarg((string)$task['campaign_key']);
    }
    if ((int)($task['slot_index'] ?? 0) > 0) {
        $cmd .= ' --slot-index=' . (int)$task['slot_index'];
    }
    if ((int)$task['force_mode'] === 1) {
        $cmd .= ' --force';
    }
    if ((int)$task['dry_run'] === 1) {
        $cmd .= ' --dry-run';
    }

    $desc = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $desc, $pipes);
    if (!is_resource($process)) {
        return ['exit_code' => 255, 'stdout' => '', 'stderr' => 'Cannot start subprocess'];
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'exit_code' => (int)$exitCode,
        'stdout' => (string)$stdout,
        'stderr' => (string)$stderr,
        'command' => $cmd,
    ];
}

function queue_mark_task_processing(mysqli $db, int $id): bool
{
    $sql = "UPDATE seo_article_generation_queue
            SET status = 'processing', attempts = attempts + 1, started_at = NOW(), updated_at = NOW()
            WHERE id = " . (int)$id . " AND status = 'queued'
            LIMIT 1";
    mysqli_query($db, $sql);
    return mysqli_affected_rows($db) > 0;
}

function queue_mark_task_done(mysqli $db, int $id, array $result): void
{
    $status = ((int)($result['exit_code'] ?? 1) === 0) ? 'success' : 'failed';
    $output = trim((string)($result['stdout'] ?? ''));
    $stderr = trim((string)($result['stderr'] ?? ''));
    $error = $stderr !== '' ? $stderr : ($status === 'failed' ? ('Exit code: ' . (int)($result['exit_code'] ?? 1)) : '');

    $outputSafe = mysqli_real_escape_string($db, mb_substr($output, 0, 60000));
    $errorSafe = mysqli_real_escape_string($db, mb_substr($error, 0, 4000));
    $statusSafe = mysqli_real_escape_string($db, $status);

    $sql = "UPDATE seo_article_generation_queue
            SET status = '{$statusSafe}',
                finished_at = NOW(),
                last_exit_code = " . (int)($result['exit_code'] ?? 1) . ",
                last_output = '{$outputSafe}',
                last_error = '{$errorSafe}',
                updated_at = NOW()
            WHERE id = " . (int)$id . "
            LIMIT 1";
    mysqli_query($db, $sql);
}

function queue_fetch_tasks(mysqli $db, int $limit): array
{
    $limit = max(1, min(50, $limit));
    $rows = [];
    $sql = "SELECT id, job_date, lang_code, campaign_key, force_mode, dry_run, max_per_run, slot_index
            FROM seo_article_generation_queue
            WHERE status = 'queued'
              AND slot_index > 0
              AND (planned_at IS NULL OR planned_at <= NOW())
            ORDER BY planned_at ASC, id ASC
            LIMIT {$limit}";
    $res = mysqli_query($db, $sql);
    if (!$res) {
        return [];
    }
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_free_result($res);
    return $rows;
}

function queue_detect_languages_from_settings(mysqli $db): array
{
    $default = ['ru'];
    $sql = "SELECT settings_json
            FROM seo_generator_settings
            ORDER BY id DESC
            LIMIT 1";
    $res = mysqli_query($db, $sql);
    if (!$res || mysqli_num_rows($res) === 0) {
        if ($res) {
            mysqli_free_result($res);
        }
        return $default;
    }
    $row = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    $raw = (string)($row['settings_json'] ?? '');
    if ($raw === '') {
        return $default;
    }
    $json = json_decode($raw, true);
    if (!is_array($json) || !is_array($json['langs'] ?? null)) {
        return $default;
    }

    $langs = [];
    foreach ($json['langs'] as $l) {
        $lang = examples_normalize_lang((string)$l);
        if (!in_array($lang, $langs, true)) {
            $langs[] = $lang;
        }
    }
    return ['ru'];
}

$opts = queue_runtime_options();
$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!$DB) {
    queue_echo('DB connection failed.');
    exit(1);
}
if (!queue_table_ensure($DB)) {
    queue_echo('Cannot ensure seo_article_generation_queue table: ' . mysqli_error($DB));
    exit(1);
}

if ($opts['mode'] === 'ensure') {
    queue_echo('Queue table is ready.');
    exit(0);
}

if ($opts['mode'] === 'enqueue_test') {
    $campaign = $opts['campaign'] !== '' ? $opts['campaign'] : 'journal';
    $okRu = queue_add_task($DB, $opts['job_date'], 'ru', $campaign, true, false, 1, $opts['planned_at'] !== '' ? $opts['planned_at'] : null, 1);
    $okEn = queue_add_task($DB, $opts['job_date'], 'en', $campaign, true, false, 1, $opts['planned_at'] !== '' ? $opts['planned_at'] : null, 1);
    queue_echo('Enqueued test jobs: RU=' . ($okRu ? 'ok' : 'fail') . ', EN=' . ($okEn ? 'ok' : 'fail'));
    exit(($okRu && $okEn) ? 0 : 1);
}

if ($opts['mode'] === 'enqueue_daily') {
    $langs = queue_detect_languages_from_settings($DB);
    $added = queue_add_daily_if_missing($DB, $langs, $opts['job_date']);
    queue_echo('Daily queue sync done. Added: ' . $added . ', date: ' . $opts['job_date']);
    exit(0);
}

if ($opts['mode'] === 'enqueue') {
    $added = 0;
    foreach ($opts['langs'] as $lang) {
        if (queue_add_task(
            $DB,
            $opts['job_date'],
            $lang,
            $opts['campaign'],
            $opts['force'],
            $opts['dry_run'],
            $opts['max_per_run'],
            $opts['planned_at'] !== '' ? $opts['planned_at'] : null,
            1
        )) {
            $added++;
        }
    }
    queue_echo('Manual enqueue done. Added: ' . $added . ', date: ' . $opts['job_date']);
    exit(0);
}

if ($opts['mode'] === 'work') {
    $tasks = queue_fetch_tasks($DB, (int)$opts['limit']);
    if (empty($tasks)) {
        queue_echo('No queued tasks.');
        exit(0);
    }

    $done = 0;
    foreach ($tasks as $task) {
        $id = (int)($task['id'] ?? 0);
        if ($id <= 0) {
            continue;
        }
        if (!queue_mark_task_processing($DB, $id)) {
            continue;
        }
        queue_echo('Processing task #' . $id . ' [' . $task['lang_code'] . '|' . ($task['campaign_key'] ?? '') . '|' . $task['job_date'] . ']');
        $result = queue_run_generation($task);
        queue_mark_task_done($DB, $id, $result);
        queue_echo('Task #' . $id . ' finished with exit=' . (int)$result['exit_code']);
        $done++;
    }

    queue_echo('Worker done. Processed: ' . $done);
    exit(0);
}

queue_echo('Usage:');
queue_echo('  --ensure');
queue_echo('  --enqueue-test [--campaign=journal|playbooks|signals|reviews|fun] [--date=YYYY-MM-DD] [--planned-at="YYYY-MM-DD HH:MM:SS"]');
queue_echo('  --enqueue-daily [--date=YYYY-MM-DD]');
queue_echo('  --enqueue --date=YYYY-MM-DD --lang=ru [--campaign=journal|playbooks|signals|reviews|fun] [--force|--no-force] [--dry-run] [--max-per-run=1]');
queue_echo('  --work [--limit=2]');
exit(0);
