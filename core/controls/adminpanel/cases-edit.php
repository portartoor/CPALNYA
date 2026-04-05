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

$allowedLangs = ['ru', 'en'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

function admin_cases_clean_host(string $raw): string
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

function admin_cases_public_url(string $domainHost, string $langCode, string $symbolicCode, string $slug = ''): string
{
    $code = trim($symbolicCode) !== '' ? trim($symbolicCode) : trim($slug);
    if ($code === '') {
        return '';
    }
    $host = admin_cases_clean_host($domainHost);
    if ($host === '') {
        $host = public_cases_normalize_lang($langCode) === 'ru' ? 'portcore.ru' : 'portcore.online';
    }
    return 'https://' . $host . '/cases/' . rawurlencode($code) . '/';
}

function admin_cases_indexnow_enabled(mysqli $DB): bool
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

function admin_cases_indexnow_enqueue(mysqli $DB, string $url, string $langCode, string $eventType): void
{
    if ($url === '' || !function_exists('indexnow_queue_enqueue') || !admin_cases_indexnow_enabled($DB)) {
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
    if ($action === 'save') {
        $domainHost = strtolower(trim((string)($_POST['domain_host'] ?? '')));
        $langCode = public_cases_normalize_lang((string)($_POST['lang_code'] ?? 'en'));
        if (!in_array($langCode, $allowedLangs, true)) {
            $langCode = 'en';
        }
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = public_cases_slugify((string)($_POST['slug'] ?? ($title !== '' ? $title : '')));
        $symbolicCode = public_cases_slugify((string)($_POST['symbolic_code'] ?? ($slug !== '' ? $slug : $title)));
        $clientName = trim((string)($_POST['client_name'] ?? ''));
        $industrySummary = trim((string)($_POST['industry_summary'] ?? ''));
        $periodSummary = trim((string)($_POST['period_summary'] ?? ''));
        $roleSummary = trim((string)($_POST['role_summary'] ?? ''));
        $stackSummary = trim((string)($_POST['stack_summary'] ?? ''));
        $problemSummary = trim((string)($_POST['problem_summary'] ?? ''));
        $resultSummary = trim((string)($_POST['result_summary'] ?? ''));
        $seoTitle = trim((string)($_POST['seo_title'] ?? ''));
        $seoDescription = trim((string)($_POST['seo_description'] ?? ''));
        $excerptHtml = trim((string)($_POST['excerpt_html'] ?? ''));
        $challengeHtml = trim((string)($_POST['challenge_html'] ?? ''));
        $solutionHtml = trim((string)($_POST['solution_html'] ?? ''));
        $architectureHtml = trim((string)($_POST['architecture_html'] ?? ''));
        $resultsHtml = trim((string)($_POST['results_html'] ?? ''));
        $metricsHtml = trim((string)($_POST['metrics_html'] ?? ''));
        $deliverablesHtml = trim((string)($_POST['deliverables_html'] ?? ''));
        $sortOrder = max(-100000, min(100000, (int)($_POST['sort_order'] ?? 100)));
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') {
            $message = 'Title is required.';
            $messageType = 'warning';
        } else {
            $pairs = [
                'domain_host' => $domainHost,
                'lang_code' => $langCode,
                'title' => $title,
                'slug' => $slug,
                'symbolic_code' => $symbolicCode,
                'client_name' => $clientName,
                'industry_summary' => $industrySummary,
                'period_summary' => $periodSummary,
                'role_summary' => $roleSummary,
                'stack_summary' => $stackSummary,
                'problem_summary' => $problemSummary,
                'result_summary' => $resultSummary,
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
                'excerpt_html' => $excerptHtml,
                'challenge_html' => $challengeHtml,
                'solution_html' => $solutionHtml,
                'architecture_html' => $architectureHtml,
                'results_html' => $resultsHtml,
                'metrics_html' => $metricsHtml,
                'deliverables_html' => $deliverablesHtml,
            ];
            $safe = [];
            foreach ($pairs as $key => $value) {
                $safe[$key] = mysqli_real_escape_string($DB, (string)$value);
            }

            if ($id > 0) {
                $existingRows = $FRMWRK->DBRecords("SELECT domain_host, lang_code, slug, symbolic_code, is_published FROM public_cases WHERE id={$id} LIMIT 1");
                $oldUrl = '';
                $oldLang = $langCode;
                $oldWasPublished = false;
                if (!empty($existingRows)) {
                    $oldRow = $existingRows[0];
                    $oldLang = (string)($oldRow['lang_code'] ?? $langCode);
                    $oldWasPublished = ((int)($oldRow['is_published'] ?? 0) === 1);
                    $oldUrl = admin_cases_public_url(
                        (string)($oldRow['domain_host'] ?? ''),
                        $oldLang,
                        (string)($oldRow['symbolic_code'] ?? ''),
                        (string)($oldRow['slug'] ?? '')
                    );
                }
                mysqli_query(
                    $DB,
                    "UPDATE public_cases
                     SET domain_host='{$safe['domain_host']}',
                         lang_code='{$safe['lang_code']}',
                         title='{$safe['title']}',
                         slug='{$safe['slug']}',
                         symbolic_code='{$safe['symbolic_code']}',
                         client_name='{$safe['client_name']}',
                         industry_summary='{$safe['industry_summary']}',
                         period_summary='{$safe['period_summary']}',
                         role_summary='{$safe['role_summary']}',
                         stack_summary='{$safe['stack_summary']}',
                         problem_summary='{$safe['problem_summary']}',
                         result_summary='{$safe['result_summary']}',
                         seo_title='{$safe['seo_title']}',
                         seo_description='{$safe['seo_description']}',
                         excerpt_html='{$safe['excerpt_html']}',
                         challenge_html='{$safe['challenge_html']}',
                         solution_html='{$safe['solution_html']}',
                         architecture_html='{$safe['architecture_html']}',
                         results_html='{$safe['results_html']}',
                         metrics_html='{$safe['metrics_html']}',
                         deliverables_html='{$safe['deliverables_html']}',
                         sort_order={$sortOrder},
                         is_published={$isPublished},
                         updated_at=NOW()
                     WHERE id={$id}
                     LIMIT 1"
                );
                if (!mysqli_error($DB)) {
                    $newUrl = admin_cases_public_url($domainHost, $langCode, $symbolicCode, $slug);
                    if ($oldWasPublished && $oldUrl !== '' && ($isPublished !== 1 || $oldUrl !== $newUrl)) {
                        admin_cases_indexnow_enqueue($DB, $oldUrl, $oldLang, $isPublished === 1 ? 'update' : 'delete');
                    }
                    if ($isPublished === 1 && $newUrl !== '') {
                        admin_cases_indexnow_enqueue($DB, $newUrl, $langCode, $oldWasPublished ? 'update' : 'publish');
                    }
                }
                $message = mysqli_error($DB) ? ('Update failed: ' . mysqli_error($DB)) : 'Case updated.';
                $messageType = mysqli_error($DB) ? 'danger' : 'success';
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO public_cases
                        (domain_host, lang_code, title, slug, symbolic_code, client_name, industry_summary, period_summary, role_summary,
                         stack_summary, problem_summary, result_summary, seo_title, seo_description, excerpt_html, challenge_html,
                         solution_html, architecture_html, results_html, metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at)
                     VALUES
                        ('{$safe['domain_host']}', '{$safe['lang_code']}', '{$safe['title']}', '{$safe['slug']}', '{$safe['symbolic_code']}',
                         '{$safe['client_name']}', '{$safe['industry_summary']}', '{$safe['period_summary']}', '{$safe['role_summary']}',
                         '{$safe['stack_summary']}', '{$safe['problem_summary']}', '{$safe['result_summary']}', '{$safe['seo_title']}',
                         '{$safe['seo_description']}', '{$safe['excerpt_html']}', '{$safe['challenge_html']}', '{$safe['solution_html']}',
                         '{$safe['architecture_html']}', '{$safe['results_html']}', '{$safe['metrics_html']}', '{$safe['deliverables_html']}',
                         {$sortOrder}, {$isPublished}, NOW(), NOW())"
                );
                if (!mysqli_error($DB)) {
                    $id = (int)mysqli_insert_id($DB);
                    if ($isPublished === 1) {
                        admin_cases_indexnow_enqueue($DB, admin_cases_public_url($domainHost, $langCode, $symbolicCode, $slug), $langCode, 'publish');
                    }
                }
                $message = mysqli_error($DB) ? ('Create failed: ' . mysqli_error($DB)) : 'Case created.';
                $messageType = mysqli_error($DB) ? 'danger' : 'success';
            }
        }
    }
}

$editRow = null;
if ($id > 0 && $messageType !== 'danger') {
    $rows = $FRMWRK->DBRecords("SELECT * FROM public_cases WHERE id={$id} LIMIT 1");
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
    'client_name' => (string)($editRow['client_name'] ?? ''),
    'industry_summary' => (string)($editRow['industry_summary'] ?? ''),
    'period_summary' => (string)($editRow['period_summary'] ?? ''),
    'role_summary' => (string)($editRow['role_summary'] ?? ''),
    'stack_summary' => (string)($editRow['stack_summary'] ?? ''),
    'problem_summary' => (string)($editRow['problem_summary'] ?? ''),
    'result_summary' => (string)($editRow['result_summary'] ?? ''),
    'seo_title' => (string)($editRow['seo_title'] ?? ''),
    'seo_description' => (string)($editRow['seo_description'] ?? ''),
    'excerpt_html' => (string)($editRow['excerpt_html'] ?? ''),
    'challenge_html' => (string)($editRow['challenge_html'] ?? ''),
    'solution_html' => (string)($editRow['solution_html'] ?? ''),
    'architecture_html' => (string)($editRow['architecture_html'] ?? ''),
    'results_html' => (string)($editRow['results_html'] ?? ''),
    'metrics_html' => (string)($editRow['metrics_html'] ?? ''),
    'deliverables_html' => (string)($editRow['deliverables_html'] ?? ''),
    'sort_order' => (int)($editRow['sort_order'] ?? 100),
    'is_published' => ((int)($editRow['is_published'] ?? 1) === 1 ? 1 : 0),
];
