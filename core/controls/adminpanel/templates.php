<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$mirrorDomainsLib = DIR . '/core/libs/mirror_domains.php';
if (file_exists($mirrorDomainsLib)) {
    require_once $mirrorDomainsLib;
}
if (function_exists('mirror_domains_ensure_schema')) {
    mirror_domains_ensure_schema($FRMWRK);
}

$message = '';
$messageType = '';

function templates_clean_key(string $raw): string
{
    $raw = strtolower(trim($raw));
    $raw = preg_replace('/[^a-z0-9_-]/', '', $raw);
    return substr($raw, 0, 64);
}

function templates_clean_file(string $raw, string $fallback = 'main.php'): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return $fallback;
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]+\.php$/', $raw)) {
        return $fallback;
    }
    return $raw;
}

$protectedKeys = ['simple', 'dashboard'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_template') {
        $templateId = (int)($_POST['template_id'] ?? 0);
        $templateKey = templates_clean_key((string)($_POST['template_key'] ?? ''));
        $displayName = trim((string)($_POST['display_name'] ?? ''));
        $shellView = strtolower(trim((string)($_POST['shell_view'] ?? 'simple')));
        $mainViewFile = templates_clean_file((string)($_POST['main_view_file'] ?? 'main.php'));
        $modelFile = templates_clean_file((string)($_POST['model_file'] ?? 'main.php'));
        $controlFile = templates_clean_file((string)($_POST['control_file'] ?? 'main.php'));
        $isActive = ((int)($_POST['is_active'] ?? 1) === 1) ? 1 : 0;

        if ($templateKey === '' || $displayName === '') {
            $message = 'Template key and display name are required.';
            $messageType = 'danger';
        } elseif (!in_array($shellView, ['simple', 'dashboard', 'enterprise'], true)) {
            $message = 'Invalid shell view.';
            $messageType = 'danger';
        } else {
            $keySafe = mysqli_real_escape_string($DB, $templateKey);
            $nameSafe = mysqli_real_escape_string($DB, $displayName);
            $shellSafe = mysqli_real_escape_string($DB, $shellView);
            $viewSafe = mysqli_real_escape_string($DB, $mainViewFile);
            $modelSafe = mysqli_real_escape_string($DB, $modelFile);
            $controlSafe = mysqli_real_escape_string($DB, $controlFile);

            if ($templateId > 0) {
                if (in_array($templateKey, $protectedKeys, true) && $isActive === 0) {
                    $message = 'Built-in templates cannot be disabled.';
                    $messageType = 'warning';
                } else {
                    mysqli_query(
                        $DB,
                        "UPDATE mirror_templates
                         SET template_key='{$keySafe}',
                             display_name='{$nameSafe}',
                             shell_view='{$shellSafe}',
                             main_view_file='{$viewSafe}',
                             model_file='{$modelSafe}',
                             control_file='{$controlSafe}',
                             is_active={$isActive},
                             updated_at=NOW()
                         WHERE id={$templateId}
                         LIMIT 1"
                    );
                    $message = 'Template updated.';
                    $messageType = 'success';
                }
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO mirror_templates
                        (template_key, display_name, shell_view, main_view_file, model_file, control_file, is_active, created_at, updated_at)
                     VALUES
                        ('{$keySafe}', '{$nameSafe}', '{$shellSafe}', '{$viewSafe}', '{$modelSafe}', '{$controlSafe}', {$isActive}, NOW(), NOW())"
                );
                if (mysqli_errno($DB) === 1062) {
                    $message = 'Template key already exists.';
                    $messageType = 'warning';
                } else {
                    $message = 'Template created.';
                    $messageType = 'success';
                }
            }
        }
    } elseif ($action === 'delete_template') {
        $templateId = (int)($_POST['template_id'] ?? 0);
        if ($templateId > 0) {
            $row = $FRMWRK->DBRecords("SELECT template_key FROM mirror_templates WHERE id={$templateId} LIMIT 1");
            $templateKey = strtolower((string)($row[0]['template_key'] ?? ''));
            if (in_array($templateKey, $protectedKeys, true)) {
                $message = 'Built-in templates cannot be deleted.';
                $messageType = 'warning';
            } else {
                $inUse = $FRMWRK->DBRecordsCount('mirror_domains', "template_view='".mysqli_real_escape_string($DB, $templateKey)."'");
                if ((int)$inUse > 0) {
                    $message = 'Template is used by one or more domains.';
                    $messageType = 'warning';
                } else {
                    mysqli_query($DB, "DELETE FROM mirror_templates WHERE id={$templateId} LIMIT 1");
                    $message = 'Template deleted.';
                    $messageType = 'success';
                }
            }
        }
    }
}

$templates = $FRMWRK->DBRecords(
    "SELECT id, template_key, display_name, shell_view, main_view_file, model_file, control_file, is_active, created_at, updated_at
     FROM mirror_templates
     WHERE template_key NOT IN ('simple_apigeo', 'simple_apigeo_ru')
     ORDER BY id ASC"
);
?>
