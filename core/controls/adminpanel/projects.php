<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';

if (!$DB || !function_exists('public_projects_ensure_schema') || !public_projects_ensure_schema($DB)) {
    $message = 'Products storage is unavailable.';
    $messageType = 'danger';
}

$allowedLangs = ['ru', 'en'];
if (($messageType !== 'danger') && function_exists('public_projects_seed_default_products')) {
    $totalProjects = (int)$FRMWRK->DBRecordsCount('public_projects', '1=1');
    if ($totalProjects === 0) {
        public_projects_seed_default_products(
            $FRMWRK,
            (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''),
            'ru'
        );
        public_projects_seed_default_products(
            $FRMWRK,
            (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''),
            'en'
        );
    }
}

if (($messageType !== 'danger') && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $action = trim((string)($_POST['action'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        mysqli_query($DB, "DELETE FROM public_projects WHERE id={$id} LIMIT 1");
        $message = mysqli_error($DB) ? ('Delete failed: ' . mysqli_error($DB)) : 'Product deleted.';
        $messageType = mysqli_error($DB) ? 'danger' : 'success';
    } elseif ($action === 'toggle_publish' && $id > 0) {
        mysqli_query($DB, "UPDATE public_projects SET is_published = IF(is_published=1,0,1), updated_at=NOW() WHERE id={$id} LIMIT 1");
        $message = mysqli_error($DB) ? ('Publish toggle failed: ' . mysqli_error($DB)) : 'Publish status updated.';
        $messageType = mysqli_error($DB) ? 'danger' : 'success';
    } elseif ($action === 'duplicate' && $id > 0) {
        $rows = $FRMWRK->DBRecords("SELECT * FROM public_projects WHERE id={$id} LIMIT 1");
        if (!empty($rows)) {
            $row = $rows[0];
            $title = trim((string)($row['title'] ?? '')) . ' (Copy)';
            $slug = public_projects_slugify((string)($row['slug'] ?? $title) . '-copy-' . date('His'));
            $symbolicCode = public_projects_slugify((string)($row['symbolic_code'] ?? $slug) . '-copy-' . date('His'));
            $domainSafe = mysqli_real_escape_string($DB, (string)($row['domain_host'] ?? ''));
            $langCode = public_projects_normalize_lang((string)($row['lang_code'] ?? 'en'));
            if (!in_array($langCode, $allowedLangs, true)) {
                $langCode = 'en';
            }
            $langSafe = mysqli_real_escape_string($DB, $langCode);
            $titleSafe = mysqli_real_escape_string($DB, $title);
            $slugSafe = mysqli_real_escape_string($DB, $slug);
            $symbolicSafe = mysqli_real_escape_string($DB, $symbolicCode);
            $projectUrlSafe = mysqli_real_escape_string($DB, (string)($row['project_url'] ?? ''));
            $roleSafe = mysqli_real_escape_string($DB, (string)($row['role_summary'] ?? ''));
            $industrySafe = mysqli_real_escape_string($DB, (string)($row['industry_summary'] ?? ''));
            $periodSafe = mysqli_real_escape_string($DB, (string)($row['period_summary'] ?? ''));
            $stackSafe = mysqli_real_escape_string($DB, (string)($row['stack_summary'] ?? ''));
            $resultSafe = mysqli_real_escape_string($DB, (string)($row['result_summary'] ?? ''));
            $excerptSafe = mysqli_real_escape_string($DB, (string)($row['excerpt_html'] ?? ''));
            $challengeSafe = mysqli_real_escape_string($DB, (string)($row['challenge_html'] ?? ''));
            $solutionSafe = mysqli_real_escape_string($DB, (string)($row['solution_html'] ?? ''));
            $impactSafe = mysqli_real_escape_string($DB, (string)($row['impact_html'] ?? ''));
            $metricsSafe = mysqli_real_escape_string($DB, (string)($row['metrics_html'] ?? ''));
            $deliverablesSafe = mysqli_real_escape_string($DB, (string)($row['deliverables_html'] ?? ''));
            $sortOrder = (int)($row['sort_order'] ?? 100);
            $isPublished = (int)($row['is_published'] ?? 0) === 1 ? 1 : 0;

            mysqli_query(
                $DB,
                "INSERT INTO public_projects
                    (domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary, period_summary,
                     stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html, metrics_html, deliverables_html,
                     sort_order, is_published, created_at, updated_at)
                 VALUES
                    ('{$domainSafe}', '{$langSafe}', '{$titleSafe}', '{$slugSafe}', '{$symbolicSafe}', '{$projectUrlSafe}', '{$roleSafe}', '{$industrySafe}', '{$periodSafe}',
                     '{$stackSafe}', '{$resultSafe}', '{$excerptSafe}', '{$challengeSafe}', '{$solutionSafe}', '{$impactSafe}', '{$metricsSafe}', '{$deliverablesSafe}',
                     {$sortOrder}, {$isPublished}, NOW(), NOW())"
            );
            $message = mysqli_error($DB) ? ('Duplicate failed: ' . mysqli_error($DB)) : 'Product duplicated.';
            $messageType = mysqli_error($DB) ? 'danger' : 'success';
        }
    }
}

$filterLang = public_projects_normalize_lang((string)($_GET['lang'] ?? 'all'));
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

$totalRows = ($messageType !== 'danger') ? (int)$FRMWRK->DBRecordsCount('public_projects', $whereSql) : 0;
$totalPages = max(1, (int)ceil($totalRows / max(1, $perPage)));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$projectsRows = [];
if ($messageType !== 'danger') {
    $projectsRows = $FRMWRK->DBRecords(
        "SELECT id, domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary, period_summary,
                stack_summary, result_summary, sort_order, is_published, created_at, updated_at
         FROM public_projects
         WHERE {$whereSql}
         ORDER BY sort_order ASC, id DESC
         LIMIT {$offset}, {$perPage}"
    );
}
