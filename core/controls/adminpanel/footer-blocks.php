<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/libs/footer_seo_blocks.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';
$editBlock = null;

if ($DB) {
    footer_seo_blocks_ensure_schema($DB);
    footer_seo_blocks_seed_defaults($DB);
}

function admin_footer_blocks_clean_host(string $raw): string
{
    $host = strtolower(trim($raw));
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    if ($host !== '' && !preg_match('/^[a-z0-9.-]+$/', $host)) {
        return '';
    }
    return trim($host, '.');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $DB) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_footer_block') {
        $blockId = (int)($_POST['block_id'] ?? 0);
        $domainHost = admin_footer_blocks_clean_host((string)($_POST['domain_host'] ?? ''));
        $langCode = strtolower(trim((string)($_POST['lang_code'] ?? 'ru')));
        $sectionScope = trim((string)($_POST['section_scope'] ?? 'all'));
        $styleVariant = trim((string)($_POST['style_variant'] ?? 'editorial-note'));
        $blockKicker = trim((string)($_POST['block_kicker'] ?? ''));
        $blockTitle = trim((string)($_POST['block_title'] ?? ''));
        $bodyHtml = trim((string)($_POST['body_html'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = ((int)($_POST['is_active'] ?? 0) === 1) ? 1 : 0;

        $allowedLangs = ['ru', 'en'];
        if (!in_array($langCode, $allowedLangs, true)) {
            $langCode = 'ru';
        }
        $allowedScopes = ['all', 'journal', 'playbooks', 'signals', 'fun', 'contact'];
        if (!in_array($sectionScope, $allowedScopes, true)) {
            $sectionScope = 'all';
        }
        $allowedStyles = ['editorial-note', 'mini-story', 'memo', 'allegory', 'field-note'];
        if (!in_array($styleVariant, $allowedStyles, true)) {
            $styleVariant = 'editorial-note';
        }

        if ($blockTitle === '' || $bodyHtml === '') {
            $message = 'Title and body are required.';
            $messageType = 'danger';
        } else {
            $table = footer_seo_blocks_table_name();
            $domainSafe = mysqli_real_escape_string($DB, $domainHost);
            $langSafe = mysqli_real_escape_string($DB, $langCode);
            $scopeSafe = mysqli_real_escape_string($DB, $sectionScope);
            $styleSafe = mysqli_real_escape_string($DB, $styleVariant);
            $kickerSafe = mysqli_real_escape_string($DB, $blockKicker);
            $titleSafe = mysqli_real_escape_string($DB, $blockTitle);
            $bodySafe = mysqli_real_escape_string($DB, $bodyHtml);

            if ($blockId > 0) {
                mysqli_query(
                    $DB,
                    "UPDATE `{$table}`
                     SET `domain_host` = '{$domainSafe}',
                         `lang_code` = '{$langSafe}',
                         `section_scope` = '{$scopeSafe}',
                         `style_variant` = '{$styleSafe}',
                         `block_kicker` = '{$kickerSafe}',
                         `block_title` = '{$titleSafe}',
                         `body_html` = '{$bodySafe}',
                         `is_active` = {$isActive},
                         `sort_order` = {$sortOrder},
                         `updated_at` = NOW()
                     WHERE `id` = {$blockId}
                     LIMIT 1"
                );
                $message = 'Footer block updated.';
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO `{$table}`
                        (`domain_host`, `lang_code`, `section_scope`, `style_variant`, `block_kicker`, `block_title`, `body_html`, `is_active`, `sort_order`, `created_at`, `updated_at`)
                     VALUES
                        ('{$domainSafe}', '{$langSafe}', '{$scopeSafe}', '{$styleSafe}', '{$kickerSafe}', '{$titleSafe}', '{$bodySafe}', {$isActive}, {$sortOrder}, NOW(), NOW())"
                );
                $message = 'Footer block created.';
            }
            $messageType = 'success';
        }
    } elseif ($action === 'delete_footer_block') {
        $blockId = (int)($_POST['block_id'] ?? 0);
        if ($blockId > 0) {
            $table = footer_seo_blocks_table_name();
            mysqli_query($DB, "DELETE FROM `{$table}` WHERE `id` = {$blockId} LIMIT 1");
            $message = 'Footer block deleted.';
            $messageType = 'success';
        }
    } elseif ($action === 'refresh_default_footer_blocks') {
        if (function_exists('footer_seo_blocks_refresh_defaults')) {
            $inserted = footer_seo_blocks_refresh_defaults($DB);
            $message = 'Default footer block library refreshed. Inserted: ' . (int)$inserted;
            $messageType = 'success';
        }
    }
}

if ($DB) {
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $table = footer_seo_blocks_table_name();
        $rows = $FRMWRK->DBRecords("SELECT * FROM `{$table}` WHERE id = {$editId} LIMIT 1");
        if (!empty($rows)) {
            $editBlock = $rows[0];
        }
    }
}

$footerBlocksRows = [];
if ($DB) {
    $table = footer_seo_blocks_table_name();
    $footerBlocksRows = $FRMWRK->DBRecords(
        "SELECT id, domain_host, lang_code, section_scope, style_variant, block_kicker, block_title, is_active, sort_order, updated_at
         FROM `{$table}`
         ORDER BY sort_order DESC, updated_at DESC, id DESC"
    );
}
