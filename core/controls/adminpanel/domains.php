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

function domains_normalize(string $domain): string
{
    $domain = strtolower(trim($domain));
    if ($domain === '') {
        return '';
    }
    $domain = preg_replace('#^https?://#i', '', $domain);
    if (strpos($domain, '/') !== false) {
        $domain = explode('/', $domain, 2)[0];
    }
    if (strpos($domain, ':') !== false) {
        $domain = explode(':', $domain, 2)[0];
    }
    return trim($domain, '.');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_domain') {
        $domainId = (int)($_POST['domain_id'] ?? 0);
        $domain = domains_normalize((string)($_POST['domain'] ?? ''));
        $templateView = strtolower(trim((string)($_POST['template_view'] ?? 'simple')));
        $googleTagCode = trim((string)($_POST['google_tag_code'] ?? ''));
        $yandexCounterCode = trim((string)($_POST['yandex_counter_code'] ?? ''));
        $isActive = ((int)($_POST['is_active'] ?? 1) === 1) ? 1 : 0;

        $allowedTemplateKeys = ['simple', 'dashboard', 'enterprise'];
        $templateRows = $FRMWRK->DBRecords(
            "SELECT template_key
             FROM mirror_templates
             WHERE is_active=1"
        );
        if (is_array($templateRows)) {
            foreach ($templateRows as $templateRow) {
                $key = strtolower(trim((string)($templateRow['template_key'] ?? '')));
                if ($key !== '') {
                    $allowedTemplateKeys[] = $key;
                }
            }
        }
        $allowedTemplateKeys = array_values(array_unique($allowedTemplateKeys));

        if (!in_array($templateView, $allowedTemplateKeys, true)) {
            $templateView = 'simple';
        }

        if ($domain === '' || !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
            $message = 'Invalid domain.';
            $messageType = 'danger';
        } else {
            $domainSafe = mysqli_real_escape_string($DB, $domain);
            $tplSafe = mysqli_real_escape_string($DB, $templateView);
            $googleTagCodeSafe = mysqli_real_escape_string($DB, $googleTagCode);
            $yandexCounterCodeSafe = mysqli_real_escape_string($DB, $yandexCounterCode);

            if ($domainId > 0) {
                try {
                    mysqli_query(
                        $DB,
                        "UPDATE mirror_domains
                         SET domain='{$domainSafe}',
                             template_view='{$tplSafe}',
                             google_tag_code='{$googleTagCodeSafe}',
                             yandex_counter_code='{$yandexCounterCodeSafe}',
                             is_active={$isActive},
                             updated_at=NOW()
                         WHERE id=".(int)$domainId." LIMIT 1"
                    );
                    $message = 'Domain updated.';
                    $messageType = 'success';
                } catch (mysqli_sql_exception $e) {
                    if ((int)$e->getCode() === 1062) {
                        $message = 'Domain already exists.';
                        $messageType = 'warning';
                    } else {
                        throw $e;
                    }
                }
            } else {
                try {
                    mysqli_query(
                        $DB,
                        "INSERT INTO mirror_domains (domain, template_view, google_tag_code, yandex_counter_code, is_active, created_at, updated_at)
                         VALUES ('{$domainSafe}', '{$tplSafe}', '{$googleTagCodeSafe}', '{$yandexCounterCodeSafe}', {$isActive}, NOW(), NOW())"
                    );
                    $message = 'Domain created.';
                    $messageType = 'success';
                } catch (mysqli_sql_exception $e) {
                    if ((int)$e->getCode() === 1062) {
                        $message = 'Domain already exists.';
                        $messageType = 'warning';
                    } else {
                        throw $e;
                    }
                }
            }
        }
    } elseif ($action === 'delete_domain') {
        $domainId = (int)($_POST['domain_id'] ?? 0);
        if ($domainId > 0) {
            mysqli_query($DB, "DELETE FROM mirror_domains WHERE id={$domainId} LIMIT 1");
            $message = 'Domain deleted.';
            $messageType = 'success';
        }
    }
}

$domains = $FRMWRK->DBRecords(
    "SELECT id, domain, template_view, google_tag_code, yandex_counter_code, is_active, created_at, updated_at
     FROM mirror_domains
     ORDER BY id DESC"
);

$templateOptions = $FRMWRK->DBRecords(
    "SELECT template_key, display_name, shell_view
     FROM mirror_templates
     WHERE is_active=1
       AND template_key NOT IN ('simple_apigeo', 'simple_apigeo_ru')
     ORDER BY template_key ASC"
);
?>
