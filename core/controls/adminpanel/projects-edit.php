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
$seedHost = (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '');
if (($messageType !== 'danger') && function_exists('public_projects_seed_default_products')) {
    $totalProjects = (int)$FRMWRK->DBRecordsCount('public_projects', '1=1');
    if ($totalProjects === 0) {
        public_projects_seed_default_products($FRMWRK, $seedHost, 'ru');
        public_projects_seed_default_products($FRMWRK, $seedHost, 'en');
    }
}
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (($messageType !== 'danger') && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $action = trim((string)($_POST['action'] ?? ''));
    if ($action === 'save') {
        $domainHost = strtolower(trim((string)($_POST['domain_host'] ?? '')));
        $langCode = public_projects_normalize_lang((string)($_POST['lang_code'] ?? 'en'));
        if (!in_array($langCode, $allowedLangs, true)) {
            $langCode = 'en';
        }
        $title = trim((string)($_POST['title'] ?? ''));
        $slugInput = trim((string)($_POST['slug'] ?? ''));
        $slug = $slugInput !== '' ? public_projects_slugify($slugInput) : public_projects_slugify($title);
        $symbolicInput = trim((string)($_POST['symbolic_code'] ?? ''));
        $symbolicCode = $symbolicInput !== '' ? public_projects_slugify($symbolicInput) : $slug;
        $projectUrl = trim((string)($_POST['project_url'] ?? ''));
        $roleSummary = trim((string)($_POST['role_summary'] ?? ''));
        $industrySummary = trim((string)($_POST['industry_summary'] ?? ''));
        $periodSummary = trim((string)($_POST['period_summary'] ?? ''));
        $stackSummary = trim((string)($_POST['stack_summary'] ?? ''));
        $resultSummary = trim((string)($_POST['result_summary'] ?? ''));
        $excerptHtml = trim((string)($_POST['excerpt_html'] ?? ''));
        $challengeHtml = trim((string)($_POST['challenge_html'] ?? ''));
        $solutionHtml = trim((string)($_POST['solution_html'] ?? ''));
        $impactHtml = trim((string)($_POST['impact_html'] ?? ''));
        $metricsHtml = trim((string)($_POST['metrics_html'] ?? ''));
        $deliverablesHtml = trim((string)($_POST['deliverables_html'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 100);
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') {
            $message = 'Title is required.';
            $messageType = 'warning';
        } else {
            $domainSafe = mysqli_real_escape_string($DB, $domainHost);
            $langSafe = mysqli_real_escape_string($DB, $langCode);
            $titleSafe = mysqli_real_escape_string($DB, $title);
            $slugSafe = mysqli_real_escape_string($DB, $slug);
            $symbolicSafe = mysqli_real_escape_string($DB, $symbolicCode);
            $urlSafe = mysqli_real_escape_string($DB, $projectUrl);
            $roleSafe = mysqli_real_escape_string($DB, $roleSummary);
            $industrySafe = mysqli_real_escape_string($DB, $industrySummary);
            $periodSafe = mysqli_real_escape_string($DB, $periodSummary);
            $stackSafe = mysqli_real_escape_string($DB, $stackSummary);
            $resultSafe = mysqli_real_escape_string($DB, $resultSummary);
            $excerptSafe = mysqli_real_escape_string($DB, $excerptHtml);
            $challengeSafe = mysqli_real_escape_string($DB, $challengeHtml);
            $solutionSafe = mysqli_real_escape_string($DB, $solutionHtml);
            $impactSafe = mysqli_real_escape_string($DB, $impactHtml);
            $metricsSafe = mysqli_real_escape_string($DB, $metricsHtml);
            $deliverablesSafe = mysqli_real_escape_string($DB, $deliverablesHtml);
            $sortOrder = max(-100000, min(100000, $sortOrder));
            $isPublished = $isPublished ? 1 : 0;

            if ($id > 0) {
                mysqli_query(
                    $DB,
                    "UPDATE public_projects
                     SET domain_host='{$domainSafe}',
                         lang_code='{$langSafe}',
                         title='{$titleSafe}',
                         slug='{$slugSafe}',
                         symbolic_code='{$symbolicSafe}',
                         project_url='{$urlSafe}',
                         role_summary='{$roleSafe}',
                         industry_summary='{$industrySafe}',
                         period_summary='{$periodSafe}',
                         stack_summary='{$stackSafe}',
                         result_summary='{$resultSafe}',
                         excerpt_html='{$excerptSafe}',
                         challenge_html='{$challengeSafe}',
                         solution_html='{$solutionSafe}',
                         impact_html='{$impactSafe}',
                         metrics_html='{$metricsSafe}',
                         deliverables_html='{$deliverablesSafe}',
                         sort_order={$sortOrder},
                         is_published={$isPublished},
                         updated_at=NOW()
                     WHERE id={$id}
                     LIMIT 1"
                );
                if (!mysqli_error($DB)) {
                    $message = 'Product updated.';
                    $messageType = 'success';
                } else {
                    $message = 'Update failed: ' . mysqli_error($DB);
                    $messageType = 'danger';
                }
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO public_projects
                        (domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary, period_summary,
                         stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html, metrics_html, deliverables_html,
                         sort_order, is_published, created_at, updated_at)
                     VALUES
                        ('{$domainSafe}', '{$langSafe}', '{$titleSafe}', '{$slugSafe}', '{$symbolicSafe}', '{$urlSafe}', '{$roleSafe}', '{$industrySafe}', '{$periodSafe}',
                         '{$stackSafe}', '{$resultSafe}', '{$excerptSafe}', '{$challengeSafe}', '{$solutionSafe}', '{$impactSafe}', '{$metricsSafe}', '{$deliverablesSafe}',
                         {$sortOrder}, {$isPublished}, NOW(), NOW())"
                );
                if (!mysqli_error($DB)) {
                    $id = (int)mysqli_insert_id($DB);
                    $message = 'Product created.';
                    $messageType = 'success';
                } else {
                    $message = 'Create failed: ' . mysqli_error($DB);
                    $messageType = 'danger';
                }
            }
        }
    }
}

$editRow = null;
if ($id > 0 && $messageType !== 'danger') {
    $rows = $FRMWRK->DBRecords("SELECT * FROM public_projects WHERE id={$id} LIMIT 1");
    if (!empty($rows)) {
        $editRow = $rows[0];
    }
}

$form = [
    'id' => (int)($editRow['id'] ?? 0),
    'domain_host' => (string)($editRow['domain_host'] ?? ''),
    'lang_code' => (string)($editRow['lang_code'] ?? 'ru'),
    'title' => (string)($editRow['title'] ?? ''),
    'slug' => (string)($editRow['slug'] ?? ''),
    'symbolic_code' => (string)($editRow['symbolic_code'] ?? ''),
    'project_url' => (string)($editRow['project_url'] ?? ''),
    'role_summary' => (string)($editRow['role_summary'] ?? ''),
    'industry_summary' => (string)($editRow['industry_summary'] ?? ''),
    'period_summary' => (string)($editRow['period_summary'] ?? ''),
    'stack_summary' => (string)($editRow['stack_summary'] ?? ''),
    'result_summary' => (string)($editRow['result_summary'] ?? ''),
    'excerpt_html' => (string)($editRow['excerpt_html'] ?? ''),
    'challenge_html' => (string)($editRow['challenge_html'] ?? ''),
    'solution_html' => (string)($editRow['solution_html'] ?? ''),
    'impact_html' => (string)($editRow['impact_html'] ?? ''),
    'metrics_html' => (string)($editRow['metrics_html'] ?? ''),
    'deliverables_html' => (string)($editRow['deliverables_html'] ?? ''),
    'sort_order' => (int)($editRow['sort_order'] ?? 100),
    'is_published' => ((int)($editRow['is_published'] ?? 1) === 1 ? 1 : 0),
];
