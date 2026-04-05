<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$seoSettingsLib = DIR . '/core/libs/seo_generator_settings.php';
if (is_file($seoSettingsLib)) {
    require_once $seoSettingsLib;
}
$indexNowLib = DIR . '/core/libs/indexnow.php';
if (is_file($indexNowLib)) {
    require_once $indexNowLib;
}
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';

if (!$DB || !function_exists('public_cases_ensure_schema') || !public_cases_ensure_schema($DB)) {
    $message = 'Cases storage is unavailable.';
    $messageType = 'danger';
}
if (($messageType !== 'danger') && function_exists('public_cases_sync_from_projects')) {
    public_cases_sync_from_projects(
        $FRMWRK,
        (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''),
        'ru'
    );
    public_cases_sync_from_projects(
        $FRMWRK,
        (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''),
        'en'
    );
}

function admin_cases_list_clean_host(string $raw): string
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

function admin_cases_list_public_url(string $domainHost, string $langCode, string $symbolicCode, string $slug = ''): string
{
    $code = trim($symbolicCode) !== '' ? trim($symbolicCode) : trim($slug);
    if ($code === '') {
        return '';
    }
    $host = admin_cases_list_clean_host($domainHost);
    if ($host === '') {
        $host = public_cases_normalize_lang($langCode) === 'ru' ? 'portcore.ru' : 'portcore.online';
    }
    return 'https://' . $host . '/cases/' . rawurlencode($code) . '/';
}

function admin_cases_list_indexnow_enabled(mysqli $DB): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    if (!function_exists('seo_gen_settings_get')) {
        $cached = false;
        return $cached;
    }
    $settings = seo_gen_settings_get($DB);
    $cached = is_array($settings) && !empty($settings['indexnow_enabled']) && !empty($settings['indexnow_ping_on_publish']);
    return $cached;
}

function admin_cases_list_indexnow_enqueue(mysqli $DB, string $url, string $langCode, string $eventType): void
{
    if ($url === '' || !function_exists('indexnow_queue_enqueue') || !admin_cases_list_indexnow_enabled($DB)) {
        return;
    }
    indexnow_queue_enqueue($DB, $url, [
        'lang_code' => public_cases_normalize_lang($langCode),
        'source' => 'admin_cases',
        'event_type' => $eventType,
    ]);
}

if (($messageType !== 'danger') && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $action = trim((string)($_POST['action'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $rows = $FRMWRK->DBRecords("SELECT domain_host, lang_code, slug, symbolic_code FROM public_cases WHERE id={$id} LIMIT 1");
        $deletedUrl = '';
        $deletedLang = 'en';
        if (!empty($rows)) {
            $row = $rows[0];
            $deletedLang = (string)($row['lang_code'] ?? 'en');
            $deletedUrl = admin_cases_list_public_url(
                (string)($row['domain_host'] ?? ''),
                $deletedLang,
                (string)($row['symbolic_code'] ?? ''),
                (string)($row['slug'] ?? '')
            );
        }
        mysqli_query($DB, "DELETE FROM public_cases WHERE id={$id} LIMIT 1");
        if (!mysqli_error($DB) && $deletedUrl !== '') {
            admin_cases_list_indexnow_enqueue($DB, $deletedUrl, $deletedLang, 'delete');
        }
        $message = mysqli_error($DB) ? ('Delete failed: ' . mysqli_error($DB)) : 'Case deleted.';
        $messageType = mysqli_error($DB) ? 'danger' : 'success';
    } elseif ($action === 'toggle_publish' && $id > 0) {
        $rows = $FRMWRK->DBRecords("SELECT domain_host, lang_code, slug, symbolic_code, is_published FROM public_cases WHERE id={$id} LIMIT 1");
        $toggleUrl = '';
        $toggleLang = 'en';
        $wasPublished = false;
        if (!empty($rows)) {
            $row = $rows[0];
            $toggleLang = (string)($row['lang_code'] ?? 'en');
            $wasPublished = ((int)($row['is_published'] ?? 0) === 1);
            $toggleUrl = admin_cases_list_public_url(
                (string)($row['domain_host'] ?? ''),
                $toggleLang,
                (string)($row['symbolic_code'] ?? ''),
                (string)($row['slug'] ?? '')
            );
        }
        mysqli_query($DB, "UPDATE public_cases SET is_published = IF(is_published=1,0,1), updated_at=NOW() WHERE id={$id} LIMIT 1");
        if (!mysqli_error($DB) && $toggleUrl !== '') {
            admin_cases_list_indexnow_enqueue($DB, $toggleUrl, $toggleLang, $wasPublished ? 'delete' : 'publish');
        }
        $message = mysqli_error($DB) ? ('Publish toggle failed: ' . mysqli_error($DB)) : 'Publish status updated.';
        $messageType = mysqli_error($DB) ? 'danger' : 'success';
    }
}

$filterLang = public_cases_normalize_lang((string)($_GET['lang'] ?? 'all'));
if ((string)($_GET['lang'] ?? 'all') === 'all') {
    $filterLang = 'all';
}
$q = trim((string)($_GET['q'] ?? ''));
$perPage = (int)($_GET['per_page'] ?? 25);
if (!in_array($perPage, [25, 50, 100], true)) {
    $perPage = 25;
}
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
if ($filterLang !== 'all') {
    $where[] = "lang_code='" . mysqli_real_escape_string($DB, $filterLang) . "'";
}
if ($q !== '') {
    $qSafe = mysqli_real_escape_string($DB, $q);
    $where[] = "(title LIKE '%{$qSafe}%' OR slug LIKE '%{$qSafe}%' OR symbolic_code LIKE '%{$qSafe}%')";
}
$whereSql = implode(' AND ', $where);

$totalRows = ($messageType !== 'danger') ? (int)$FRMWRK->DBRecordsCount('public_cases', $whereSql) : 0;
$totalPages = max(1, (int)ceil($totalRows / max(1, $perPage)));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$casesRows = [];
if ($messageType !== 'danger') {
    $casesRows = $FRMWRK->DBRecords(
        "SELECT id, domain_host, lang_code, title, slug, symbolic_code, client_name, industry_summary, sort_order, is_published, created_at, updated_at
         FROM public_cases
         WHERE {$whereSql}
         ORDER BY sort_order ASC, id DESC
         LIMIT {$offset}, {$perPage}"
    );
}
