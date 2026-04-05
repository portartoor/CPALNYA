<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/libs/site_audit.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

if (function_exists('site_audit_checks_table_ensure') && $DB instanceof mysqli) {
    site_audit_checks_table_ensure($DB);
}

$message = '';
$messageType = '';

$perPage = (int)($_GET['per_page'] ?? 20);
$allowedPerPage = [20, 50, 100];
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 20;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$search = trim((string)($_GET['q'] ?? ''));

$totalRows = 0;
$totalPages = 1;
$rows = [];
$dbName = '';
$tableExists = false;

if (!($DB instanceof mysqli)) {
    $message = 'Database connection failed.';
    $messageType = 'danger';
} else {
    $dbNameRes = mysqli_query($DB, "SELECT DATABASE() AS db_name");
    if ($dbNameRes) {
        $dbNameRow = mysqli_fetch_assoc($dbNameRes);
        $dbName = (string)($dbNameRow['db_name'] ?? '');
    }
    $tblRes = mysqli_query($DB, "SHOW TABLES LIKE 'site_audit_checks'");
    $tableExists = ($tblRes && mysqli_num_rows($tblRes) > 0);
    if (!$tableExists) {
        $message = 'Table site_audit_checks not found in current DB' . ($dbName !== '' ? (' [' . $dbName . ']') : '') . '.';
        $messageType = 'warning';
    }

    $columns = ['checked_url' => true, 'host' => true, 'user_ip' => true];
    $colsRes = mysqli_query($DB, "SHOW COLUMNS FROM site_audit_checks");
    if ($colsRes) {
        while ($col = mysqli_fetch_assoc($colsRes)) {
            $field = (string)($col['Field'] ?? '');
            if ($field !== '') {
                $columns[$field] = true;
            }
        }
    }

    $where = '1=1';
    if ($search !== '') {
        $searchEsc = mysqli_real_escape_string($DB, $search);
        $parts = [];
        foreach (['checked_url', 'host', 'user_ip'] as $f) {
            if (isset($columns[$f])) {
                $parts[] = "{$f} LIKE '%{$searchEsc}%'";
            }
        }
        if (!empty($parts)) {
            $where .= ' AND (' . implode(' OR ', $parts) . ')';
        }
    }

    $countRes = mysqli_query($DB, "SELECT COUNT(*) AS c FROM site_audit_checks WHERE {$where}");
    if ($countRes) {
        $countRow = mysqli_fetch_assoc($countRes);
        $totalRows = (int)($countRow['c'] ?? 0);
    } else {
        $message = 'Count query failed: ' . mysqli_error($DB);
        $messageType = 'danger';
    }

    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    $rowsRes = mysqli_query(
        $DB,
        "SELECT
            id,
            checked_url,
            host,
            score_overall,
            score_seo,
            score_tech,
            score_security,
            user_ip,
            country_iso2,
            country_name,
            city_name,
            timezone,
            created_at
         FROM site_audit_checks
         WHERE {$where}
         ORDER BY id DESC
         LIMIT {$offset}, {$perPage}"
    );
    if ($rowsRes) {
        while ($r = mysqli_fetch_assoc($rowsRes)) {
            $rows[] = [
                'id' => (int)($r['id'] ?? $r[0] ?? 0),
                'checked_url' => (string)($r['checked_url'] ?? $r[1] ?? ''),
                'host' => (string)($r['host'] ?? $r[2] ?? ''),
                'score_overall' => (int)($r['score_overall'] ?? $r[3] ?? 0),
                'score_seo' => (int)($r['score_seo'] ?? $r[4] ?? 0),
                'score_tech' => (int)($r['score_tech'] ?? $r[5] ?? 0),
                'score_security' => (int)($r['score_security'] ?? $r[6] ?? 0),
                'user_ip' => (string)($r['user_ip'] ?? $r[7] ?? ''),
                'country_iso2' => (string)($r['country_iso2'] ?? $r[8] ?? ''),
                'country_name' => (string)($r['country_name'] ?? $r[9] ?? ''),
                'city_name' => (string)($r['city_name'] ?? $r[10] ?? ''),
                'timezone' => (string)($r['timezone'] ?? $r[11] ?? ''),
                'created_at' => (string)($r['created_at'] ?? $r[12] ?? ''),
            ];
        }
    } else {
        $message = 'Rows query failed: ' . mysqli_error($DB);
        $messageType = 'danger';
    }
}

$siteChecksRows = $rows;
$siteChecksTotalRows = $totalRows;
$siteChecksTotalPages = $totalPages;
$siteChecksPage = $page;
$siteChecksPerPage = $perPage;
$siteChecksSearch = $search;
$siteChecksDbName = $dbName;
$siteChecksTableExists = $tableExists;
