<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();

require_once __DIR__ . '/_common.php';
$issueLib = DIR . '/core/libs/journal_issue.php';
if (is_file($issueLib)) {
    require_once $issueLib;
}
adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = 'success';
$journalIssueLang = strtolower(trim((string)($_GET['lang'] ?? 'ru')));
if (!in_array($journalIssueLang, ['ru', 'en'], true)) {
    $journalIssueLang = 'ru';
}

if ($DB && function_exists('journal_issue_ensure_table')) {
    journal_issue_ensure_table($DB);
}

if ($DB && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string)($_POST['action'] ?? '') === 'save_journal_issue') {
    $payload = [
        'issue_kicker' => trim((string)($_POST['issue_kicker'] ?? '')),
        'issue_title' => trim((string)($_POST['issue_title'] ?? '')),
        'issue_subtitle' => trim((string)($_POST['issue_subtitle'] ?? '')),
        'hero_title' => trim((string)($_POST['hero_title'] ?? '')),
        'hero_description' => trim((string)($_POST['hero_description'] ?? '')),
        'hero_note' => trim((string)($_POST['hero_note'] ?? '')),
        'hero_image_url' => trim((string)($_POST['hero_image_url'] ?? '')),
        'hero_image_data' => trim((string)($_POST['hero_image_data'] ?? '')),
    ];
    if (function_exists('journal_issue_save') && journal_issue_save($DB, $journalIssueLang, $payload)) {
        $message = 'Journal issue settings updated.';
    } else {
        $message = 'Failed to update journal issue settings.';
        $messageType = 'danger';
    }
}

$journalIssue = ($DB && function_exists('journal_issue_get'))
    ? journal_issue_get($DB, $journalIssueLang)
    : [];
