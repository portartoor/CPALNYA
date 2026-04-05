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

$allowedLangs = ['ru', 'en'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (($messageType !== 'danger') && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $action = trim((string)($_POST['action'] ?? ''));
    if ($action === 'save') {
        $domainHost = strtolower(trim((string)($_POST['domain_host'] ?? '')));
        $langCode = public_services_normalize_lang((string)($_POST['lang_code'] ?? 'en'));
        if (!in_array($langCode, $allowedLangs, true)) {
            $langCode = 'en';
        }
        $groupCode = public_services_normalize_group((string)($_POST['service_group'] ?? 'general'));
        $title = trim((string)($_POST['title'] ?? ''));
        $slugInput = trim((string)($_POST['slug'] ?? ''));
        $slug = $slugInput !== '' ? public_services_slugify($slugInput) : public_services_slugify($title);
        $excerptHtml = trim((string)($_POST['excerpt_html'] ?? ''));
        $contentHtml = trim((string)($_POST['content_html'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 100);
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') {
            $message = 'Title is required.';
            $messageType = 'warning';
        } else {
            $domainSafe = mysqli_real_escape_string($DB, $domainHost);
            $langSafe = mysqli_real_escape_string($DB, $langCode);
            $groupSafe = mysqli_real_escape_string($DB, $groupCode);
            $titleSafe = mysqli_real_escape_string($DB, $title);
            $slugSafe = mysqli_real_escape_string($DB, $slug);
            $excerptSafe = mysqli_real_escape_string($DB, $excerptHtml);
            $contentSafe = mysqli_real_escape_string($DB, $contentHtml);
            $sortOrder = max(-100000, min(100000, $sortOrder));
            $isPublished = $isPublished ? 1 : 0;

            if ($id > 0) {
                mysqli_query(
                    $DB,
                    "UPDATE public_services
                     SET domain_host='{$domainSafe}',
                         lang_code='{$langSafe}',
                         service_group='{$groupSafe}',
                         title='{$titleSafe}',
                         slug='{$slugSafe}',
                         excerpt_html='{$excerptSafe}',
                         content_html='{$contentSafe}',
                         sort_order={$sortOrder},
                         is_published={$isPublished},
                         updated_at=NOW()
                     WHERE id={$id}
                     LIMIT 1"
                );
                if (!mysqli_error($DB)) {
                    $message = 'Service updated.';
                    $messageType = 'success';
                } else {
                    $message = 'Update failed: ' . mysqli_error($DB);
                    $messageType = 'danger';
                }
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO public_services
                        (domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at)
                     VALUES
                        ('{$domainSafe}', '{$langSafe}', '{$groupSafe}', '{$titleSafe}', '{$slugSafe}', '{$excerptSafe}', '{$contentSafe}', {$sortOrder}, {$isPublished}, NOW(), NOW())"
                );
                if (!mysqli_error($DB)) {
                    $id = (int)mysqli_insert_id($DB);
                    $message = 'Service created.';
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
    $rows = $FRMWRK->DBRecords("SELECT * FROM public_services WHERE id={$id} LIMIT 1");
    if (!empty($rows)) {
        $editRow = $rows[0];
    }
}

$form = [
    'id' => (int)($editRow['id'] ?? 0),
    'domain_host' => (string)($editRow['domain_host'] ?? ''),
    'lang_code' => (string)($editRow['lang_code'] ?? 'ru'),
    'service_group' => (string)($editRow['service_group'] ?? 'general'),
    'title' => (string)($editRow['title'] ?? ''),
    'slug' => (string)($editRow['slug'] ?? ''),
    'excerpt_html' => (string)($editRow['excerpt_html'] ?? ''),
    'content_html' => (string)($editRow['content_html'] ?? ''),
    'sort_order' => (int)($editRow['sort_order'] ?? 100),
    'is_published' => ((int)($editRow['is_published'] ?? 1) === 1 ? 1 : 0),
];
