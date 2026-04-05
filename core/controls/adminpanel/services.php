<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';

if (!$DB || !function_exists('public_services_ensure_schema') || !public_services_ensure_schema($DB)) {
    $message = 'Services storage is unavailable.';
    $messageType = 'danger';
}

if (($messageType !== 'danger') && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $action = trim((string)($_POST['action'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        mysqli_query($DB, "DELETE FROM public_services WHERE id={$id} LIMIT 1");
        $message = mysqli_error($DB) ? ('Delete failed: ' . mysqli_error($DB)) : 'Service deleted.';
        $messageType = mysqli_error($DB) ? 'danger' : 'success';
    } elseif ($action === 'toggle_publish' && $id > 0) {
        mysqli_query($DB, "UPDATE public_services SET is_published = IF(is_published=1,0,1), updated_at=NOW() WHERE id={$id} LIMIT 1");
        $message = mysqli_error($DB) ? ('Publish toggle failed: ' . mysqli_error($DB)) : 'Publish status updated.';
        $messageType = mysqli_error($DB) ? 'danger' : 'success';
    } elseif ($action === 'duplicate' && $id > 0) {
        $rows = $FRMWRK->DBRecords("SELECT * FROM public_services WHERE id={$id} LIMIT 1");
        if (!empty($rows)) {
            $row = $rows[0];
            $title = trim((string)($row['title'] ?? '')) . ' (Copy)';
            $slug = public_services_slugify((string)($row['slug'] ?? $title) . '-copy-' . date('His'));
            $domainSafe = mysqli_real_escape_string($DB, (string)($row['domain_host'] ?? ''));
            $langSafe = mysqli_real_escape_string($DB, (string)($row['lang_code'] ?? 'en'));
            $groupSafe = mysqli_real_escape_string($DB, (string)($row['service_group'] ?? 'general'));
            $titleSafe = mysqli_real_escape_string($DB, $title);
            $slugSafe = mysqli_real_escape_string($DB, $slug);
            $excerptSafe = mysqli_real_escape_string($DB, (string)($row['excerpt_html'] ?? ''));
            $contentSafe = mysqli_real_escape_string($DB, (string)($row['content_html'] ?? ''));
            $sortOrder = (int)($row['sort_order'] ?? 100);
            $isPublished = (int)($row['is_published'] ?? 0) === 1 ? 1 : 0;

            mysqli_query(
                $DB,
                "INSERT INTO public_services
                    (domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at)
                 VALUES
                    ('{$domainSafe}', '{$langSafe}', '{$groupSafe}', '{$titleSafe}', '{$slugSafe}', '{$excerptSafe}', '{$contentSafe}', {$sortOrder}, {$isPublished}, NOW(), NOW())"
            );
            $message = mysqli_error($DB) ? ('Duplicate failed: ' . mysqli_error($DB)) : 'Service duplicated.';
            $messageType = mysqli_error($DB) ? 'danger' : 'success';
        }
    }
}

$filterLang = public_services_normalize_lang((string)($_GET['lang'] ?? 'all'));
if ((string)($_GET['lang'] ?? 'all') === 'all') {
    $filterLang = 'all';
}
$filterGroup = trim((string)($_GET['group'] ?? ''));
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
if ($filterGroup !== '') {
    $where[] = "service_group='" . mysqli_real_escape_string($DB, public_services_normalize_group($filterGroup)) . "'";
}
if ($q !== '') {
    $qSafe = mysqli_real_escape_string($DB, $q);
    $where[] = "(title LIKE '%{$qSafe}%' OR slug LIKE '%{$qSafe}%' OR excerpt_html LIKE '%{$qSafe}%')";
}
$whereSql = implode(' AND ', $where);

$totalRows = ($messageType !== 'danger') ? (int)$FRMWRK->DBRecordsCount('public_services', $whereSql) : 0;
$totalPages = max(1, (int)ceil($totalRows / max(1, $perPage)));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$servicesRows = [];
if ($messageType !== 'danger') {
    $servicesRows = $FRMWRK->DBRecords(
        "SELECT id, domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at
         FROM public_services
         WHERE {$whereSql}
         ORDER BY sort_order ASC, id DESC
         LIMIT {$offset}, {$perPage}"
    );
}

$groupRows = [];
if ($messageType !== 'danger') {
    $groupRows = $FRMWRK->DBRecords(
        "SELECT service_group, COUNT(*) AS cnt
         FROM public_services
         GROUP BY service_group
         ORDER BY service_group ASC"
    );
}

$groupOptions = [];
foreach ($groupRows as $gr) {
    $code = trim((string)($gr['service_group'] ?? ''));
    if ($code === '') {
        $code = 'general';
    }
    $groupOptions[] = [
        'code' => $code,
        'label' => public_services_group_label($code, 'en'),
        'count' => (int)($gr['cnt'] ?? 0),
    ];
}
