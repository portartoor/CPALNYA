<?php
$host = function_exists('public_portal_host') ? public_portal_host() : strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$lang = function_exists('public_portal_lang') ? public_portal_lang($host) : 'en';
$isRu = ($lang === 'ru');

$journalItems = [];
$playbookItems = [];
$issue = [];

if (is_file(DIR . 'core/controls/examples/_common.php')) {
    require_once DIR . 'core/controls/examples/_common.php';
}
if (is_file(DIR . 'core/libs/journal_issue.php')) {
    require_once DIR . 'core/libs/journal_issue.php';
}

if (function_exists('examples_fetch_published_list')) {
    $journalItems = examples_fetch_published_list($FRMWRK, $host, 8, $lang, '', 'journal');
    $playbookItems = examples_fetch_published_list($FRMWRK, $host, 8, $lang, '', 'playbooks');
}

if (function_exists('examples_popularity_attach_views')) {
    $journalItems = examples_popularity_attach_views($FRMWRK, $host, $lang, 'journal', (array)$journalItems);
    $playbookItems = examples_popularity_attach_views($FRMWRK, $host, $lang, 'playbooks', (array)$playbookItems);
}

foreach ($journalItems as &$row) {
    $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
    $full = trim((string)($row['preview_image_url'] ?? ''));
    $base = trim((string)($row['preview_image_data'] ?? ''));
    $row['image_src'] = $thumb !== '' ? $thumb : ($full !== '' ? $full : $base);
}
unset($row);

foreach ($playbookItems as &$row) {
    $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
    $full = trim((string)($row['preview_image_url'] ?? ''));
    $base = trim((string)($row['preview_image_data'] ?? ''));
    $row['image_src'] = $thumb !== '' ? $thumb : ($full !== '' ? $full : $base);
}
unset($row);

if (function_exists('journal_issue_get')) {
    $db = $FRMWRK->DB();
    if ($db) {
        $issue = (array)journal_issue_get($db, $lang);
    }
}

$issueImage = trim((string)($issue['hero_image_url'] ?? ''));
if ($issueImage === '') {
    $issueImage = trim((string)($issue['hero_image_data'] ?? ''));
}
if ($issueImage === '') {
    $issueImage = '/april2026.png';
}

$ModelPage['home_portal'] = [
    'lang' => $lang,
    'issue' => $issue,
    'issue_image' => $issueImage,
    'journal_items' => array_values((array)$journalItems),
    'playbook_items' => array_values((array)$playbookItems),
];

$ModelPage['title'] = $isRu
    ? 'CPALNYA — журнал про арбитраж трафика, affiliate-операции и практику команд'
    : 'CPALNYA — journal of affiliate traffic, team operations and practical playbooks';
$ModelPage['description'] = $isRu
    ? 'Журнал и практическая библиотека про арбитраж трафика, медиабаинг, фарм, креативы, трекинг и backstage affiliate-команд.'
    : 'A journal and practical library about affiliate traffic, media buying, farm, creatives, tracking and team backstage.';
$ModelPage['keywords'] = $isRu
    ? 'арбитраж трафика, affiliate, cpa, медиабаинг, фарм, трекеры, креативы, playbooks'
    : 'affiliate, cpa, media buying, traffic arbitrage, trackers, creatives, playbooks';
