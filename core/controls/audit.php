<?php

require_once DIR . '/core/libs/site_audit.php';

$hostAudit = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($hostAudit, ':') !== false) {
    $hostAudit = explode(':', $hostAudit, 2)[0];
}
$isRuAudit = (bool)preg_match('/\.ru$/', $hostAudit);

$auditInput = trim((string)($_GET['site'] ?? ''));
$auditRequested = ($auditInput !== '');
$auditDebug = ((string)($_GET['test'] ?? '') === '1');
$auditError = '';
$auditReport = null;
$auditStoreStatus = '';
$contactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$contactFlash = function_exists('public_contact_form_flash') ? public_contact_form_flash() : [];
$contactType = (string)($contactFlash['type'] ?? '');
$contactMsg = (string)($contactFlash['message'] ?? '');
$returnPath = (string)($_SERVER['REQUEST_URI'] ?? '/audit/');

if ($auditRequested) {
    $auditReport = site_audit_run($auditInput);
    if (!is_array($auditReport) || ($auditReport['ok'] ?? false) !== true) {
        $errorCode = (string)($auditReport['error'] ?? '');
        if ($errorCode === 'host_not_allowed') {
            $auditError = $isRuAudit
                ? 'Этот хост недоступен для публичного аудита. Используйте внешний домен.'
                : 'This host is not allowed for public audit. Use a public domain.';
        } else {
            $auditError = $isRuAudit
                ? 'Не удалось выполнить аудит. Проверьте корректность URL.'
                : 'Audit failed. Please verify the URL format.';
        }
    } else {
        $dbAudit = $FRMWRK->DB();
        if ($dbAudit instanceof mysqli && function_exists('site_audit_store_check')) {
            $clientIp = function_exists('analytics_real_ip')
                ? (string)analytics_real_ip()
                : (string)($_SERVER['REMOTE_ADDR'] ?? '');
            $clientUa = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $geo = [
                'country_iso2' => '',
                'country_name' => '',
                'city_name' => '',
                'timezone' => '',
            ];
            if ($clientIp !== '' && function_exists('analytics_geo_lookup')) {
                try {
                    $geoLookup = (array)analytics_geo_lookup($clientIp, $clientUa, 'site-audit');
                    $geo['country_iso2'] = (string)($geoLookup['country_iso2'] ?? '');
                    $geo['country_name'] = (string)($geoLookup['country_name'] ?? '');
                    $geo['city_name'] = (string)($geoLookup['city_name'] ?? '');
                    $geo['timezone'] = (string)($geoLookup['timezone'] ?? '');
                } catch (\Throwable $e) {
                    // Keep empty geo fields if enrichment failed.
                }
            }
            $scores = (array)($auditReport['scores'] ?? []);
            $stored = site_audit_store_check($dbAudit, [
                'checked_url' => (string)($auditReport['normalized_url'] ?? $auditInput),
                'host' => (string)($auditReport['host'] ?? ''),
                'score_overall' => (int)($scores['overall'] ?? 0),
                'score_seo' => (int)($scores['seo'] ?? 0),
                'score_tech' => (int)($scores['tech'] ?? 0),
                'score_security' => (int)($scores['security'] ?? 0),
                'user_ip' => $clientIp,
                'user_agent' => $clientUa,
                'country_iso2' => (string)$geo['country_iso2'],
                'country_name' => (string)$geo['country_name'],
                'city_name' => (string)$geo['city_name'],
                'timezone' => (string)$geo['timezone'],
                'result_json' => (string)json_encode($auditReport, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $auditStoreStatus = $stored ? 'ok' : 'failed';
        }
    }
}

$ModelPage = array_merge((array)($ModelPage ?? []), [
    'audit_input' => $auditInput,
    'audit_requested' => $auditRequested,
    'audit_error' => $auditError,
    'audit_report' => is_array($auditReport) ? $auditReport : null,
    'audit_debug' => $auditDebug,
    'audit_store_status' => $auditStoreStatus,
    'audit_contact_token' => $contactToken,
    'audit_contact_type' => $contactType,
    'audit_contact_message' => $contactMsg,
    'audit_return_path' => $returnPath,
]);
