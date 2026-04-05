<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

function admin_seo_gen_logs_has_column(mysqli $db, string $column): bool
{
    $columnSafe = mysqli_real_escape_string($db, $column);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'seo_generator_logs'
           AND COLUMN_NAME = '{$columnSafe}'
         LIMIT 1"
    );
    return ($res && mysqli_num_rows($res) > 0);
}

function admin_seo_gen_logs_ensure_schema(mysqli $db): bool
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
        return false;
    }

    $alterMap = [
        'tg_preview_result_json' => "ALTER TABLE seo_generator_logs ADD COLUMN tg_preview_result_json LONGTEXT NULL AFTER settings_snapshot_json",
        'article_request_json' => "ALTER TABLE seo_generator_logs ADD COLUMN article_request_json LONGTEXT NULL AFTER image_result_json",
        'article_result_json' => "ALTER TABLE seo_generator_logs ADD COLUMN article_result_json LONGTEXT NULL AFTER article_request_json",
        'llm_usage_json' => "ALTER TABLE seo_generator_logs ADD COLUMN llm_usage_json LONGTEXT NULL AFTER article_result_json",
        'llm_requests_count' => "ALTER TABLE seo_generator_logs ADD COLUMN llm_requests_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_usage_json",
        'llm_prompt_tokens' => "ALTER TABLE seo_generator_logs ADD COLUMN llm_prompt_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_requests_count",
        'llm_completion_tokens' => "ALTER TABLE seo_generator_logs ADD COLUMN llm_completion_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_prompt_tokens",
        'llm_total_tokens' => "ALTER TABLE seo_generator_logs ADD COLUMN llm_total_tokens INT UNSIGNED NOT NULL DEFAULT 0 AFTER llm_completion_tokens",
    ];
    foreach ($alterMap as $column => $sql) {
        if (!admin_seo_gen_logs_has_column($db, $column)) {
            @mysqli_query($db, $sql);
        }
    }
    return true;
}

$seoGeneratorLogRows = [];
$total = 0;
$perPageAllowed = [10, 20, 50, 100, 200];
$perPage = (int)($_GET['per_page'] ?? 10);
if (!in_array($perPage, $perPageAllowed, true)) {
    $perPage = 10;
}
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalPages = 1;
$tableExists = false;
$hasTgPreviewColumn = false;
$hasArticleRequestColumn = false;
$hasArticleResultColumn = false;
$hasLlmUsageColumn = false;
$hasLlmRequestsCountColumn = false;
$hasLlmPromptTokensColumn = false;
$hasLlmCompletionTokensColumn = false;
$hasLlmTotalTokensColumn = false;
$listSqlError = '';
$isAjax = ((string)($_GET['ajax'] ?? '') === '1');
$ajaxMode = trim((string)($_GET['mode'] ?? 'list'));
$ajaxRowId = (int)($_GET['id'] ?? 0);

if ($DB instanceof mysqli) {
    admin_seo_gen_logs_ensure_schema($DB);
    $res = mysqli_query(
        $DB,
        "SELECT 1
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'seo_generator_logs'
         LIMIT 1"
    );
    $tableExists = ($res && mysqli_num_rows($res) > 0);

    if ($tableExists && $isAjax) {
        $colRes = mysqli_query(
            $DB,
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'seo_generator_logs'
               AND COLUMN_NAME = 'tg_preview_result_json'
             LIMIT 1"
        );
        $hasTgPreviewColumn = ($colRes && mysqli_num_rows($colRes) > 0);
        $hasColumn = static function (mysqli $db, string $column): bool {
            return admin_seo_gen_logs_has_column($db, $column);
        };
        $hasArticleRequestColumn = $hasColumn($DB, 'article_request_json');
        $hasArticleResultColumn = $hasColumn($DB, 'article_result_json');
        $hasLlmUsageColumn = $hasColumn($DB, 'llm_usage_json');
        $hasLlmRequestsCountColumn = $hasColumn($DB, 'llm_requests_count');
        $hasLlmPromptTokensColumn = $hasColumn($DB, 'llm_prompt_tokens');
        $hasLlmCompletionTokensColumn = $hasColumn($DB, 'llm_completion_tokens');
        $hasLlmTotalTokensColumn = $hasColumn($DB, 'llm_total_tokens');

        $articleRequestSelect = $hasArticleRequestColumn ? 'article_request_json' : "'{}' AS article_request_json";
        $articleResultSelect = $hasArticleResultColumn ? 'article_result_json' : "'{}' AS article_result_json";
        $llmUsageSelect = $hasLlmUsageColumn ? 'llm_usage_json' : "'{}' AS llm_usage_json";
        $llmRequestsSelect = $hasLlmRequestsCountColumn ? 'llm_requests_count' : "0 AS llm_requests_count";
        $llmPromptTokensSelect = $hasLlmPromptTokensColumn ? 'llm_prompt_tokens' : "0 AS llm_prompt_tokens";
        $llmCompletionTokensSelect = $hasLlmCompletionTokensColumn ? 'llm_completion_tokens' : "0 AS llm_completion_tokens";
        $llmTotalTokensSelect = $hasLlmTotalTokensColumn ? 'llm_total_tokens' : "0 AS llm_total_tokens";

        $cntRes = mysqli_query($DB, "SELECT COUNT(*) AS c FROM seo_generator_logs");
        if ($cntRes) {
            $cntRow = mysqli_fetch_assoc($cntRes);
            $total = (int)($cntRow['c'] ?? 0);
        } else {
            $total = 0;
        }
        $totalPages = max(1, (int)ceil($total / max(1, $perPage)));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        if ($ajaxMode === 'ids') {
            $listSql =
                "SELECT id
                 FROM seo_generator_logs
                 ORDER BY id DESC
                 LIMIT " . (int)$offset . ", " . (int)$perPage;
            $listRes = mysqli_query($DB, $listSql);
            if ($listRes) {
                while ($row = mysqli_fetch_assoc($listRes)) {
                    $id = (int)($row['id'] ?? 0);
                    if ($id > 0) {
                        $seoGeneratorLogRows[] = ['id' => $id];
                    }
                }
            } else {
                $listSqlError = (string)mysqli_error($DB);
            }
        } elseif ($ajaxMode === 'row' && $ajaxRowId > 0) {
            $rowSql =
                "SELECT id, created_at, job_date, lang_code, slot_index, status, is_dry_run,
                        article_id, title, slug, article_url, words_final, words_initial, structure_used,
                        topic_analysis_source, topic_analysis_summary, topic_bans_count,
                        image_request_json, image_result_json, settings_snapshot_json,
                        {$articleRequestSelect}, {$articleResultSelect}, {$llmUsageSelect},
                        {$llmRequestsSelect}, {$llmPromptTokensSelect}, {$llmCompletionTokensSelect}, {$llmTotalTokensSelect},
                        " . ($hasTgPreviewColumn ? "tg_preview_result_json" : "'{}' AS tg_preview_result_json") . ",
                        error_message
                 FROM seo_generator_logs
                 WHERE id = " . (int)$ajaxRowId . "
                 LIMIT 1";
            $rowRes = mysqli_query($DB, $rowSql);
            if ($rowRes) {
                $row = mysqli_fetch_assoc($rowRes);
                if (is_array($row)) {
                    $seoGeneratorLogRows[] = $row;
                }
            } else {
                $listSqlError = (string)mysqli_error($DB);
            }
        } else {
            $listSql =
                "SELECT id, created_at, job_date, lang_code, slot_index, status, is_dry_run,
                        article_id, title, slug, article_url, words_final, words_initial, structure_used,
                        topic_analysis_source, topic_analysis_summary, topic_bans_count,
                        image_request_json, image_result_json, settings_snapshot_json,
                        {$articleRequestSelect}, {$articleResultSelect}, {$llmUsageSelect},
                        {$llmRequestsSelect}, {$llmPromptTokensSelect}, {$llmCompletionTokensSelect}, {$llmTotalTokensSelect},
                        " . ($hasTgPreviewColumn ? "tg_preview_result_json" : "'{}' AS tg_preview_result_json") . ",
                        error_message
                 FROM seo_generator_logs
                 ORDER BY id DESC
                 LIMIT " . (int)$offset . ", " . (int)$perPage;
            $listRes = mysqli_query($DB, $listSql);
            if ($listRes) {
                while ($row = mysqli_fetch_assoc($listRes)) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $id = (int)($row['id'] ?? 0);
                    $createdAt = trim((string)($row['created_at'] ?? ''));
                    $status = trim((string)($row['status'] ?? ''));
                    if ($id <= 0 && $createdAt === '' && $status === '') {
                        continue;
                    }
                    $seoGeneratorLogRows[] = $row;
                }
            } else {
                $listSqlError = (string)mysqli_error($DB);
            }
        }
    }
}

$SEO_GENERATOR_LOGS_PAGE = [
    'rows' => $seoGeneratorLogRows,
    'total' => (int)$total,
    'perPage' => (int)$perPage,
    'page' => (int)$page,
    'totalPages' => (int)$totalPages,
    'tableExists' => (bool)$tableExists,
    'listSqlError' => $listSqlError,
    'perPageAllowed' => $perPageAllowed,
];

if ($isAjax) {
    // Ensure strict JSON response even if some included file emitted whitespace/BOM.
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'mode' => $ajaxMode,
        'rows' => $seoGeneratorLogRows,
        'total' => (int)$total,
        'perPage' => (int)$perPage,
        'page' => (int)$page,
        'totalPages' => (int)$totalPages,
        'tableExists' => (bool)$tableExists,
        'listSqlError' => $listSqlError,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
