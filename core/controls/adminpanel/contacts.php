<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

if (function_exists('public_contact_form_table_ensure') && $DB) {
    public_contact_form_table_ensure($DB);
}

$message = '';
$messageType = '';
$allowedStatuses = ['new', 'in_progress', 'closed'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $DB) {
    $action = (string)($_POST['action'] ?? '');
    $requestId = (int)($_POST['request_id'] ?? 0);

    if ($action === 'set_status' && $requestId > 0) {
        $status = strtolower(trim((string)($_POST['status'] ?? 'new')));
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'new';
        }
        $statusSafe = mysqli_real_escape_string($DB, $status);
        mysqli_query($DB, "UPDATE contact_requests SET status='{$statusSafe}', updated_at=NOW() WHERE id={$requestId} LIMIT 1");
        $message = 'Request status updated.';
        $messageType = 'success';
    } elseif ($action === 'delete' && $requestId > 0) {
        mysqli_query($DB, "DELETE FROM contact_requests WHERE id={$requestId} LIMIT 1");
        $message = 'Request deleted.';
        $messageType = 'success';
    }
}

$statusFilter = strtolower(trim((string)($_GET['status'] ?? 'all')));
if ($statusFilter !== 'all' && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

$perPage = (int)($_GET['per_page'] ?? 20);
$allowedPerPage = [20, 50, 100];
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 20;
}
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = "1=1";
if ($statusFilter !== 'all' && $DB) {
    $where .= " AND status='" . mysqli_real_escape_string($DB, $statusFilter) . "'";
}

$totalRows = 0;
$totalPages = 1;
if ($DB) {
    $totalRows = (int)$FRMWRK->DBRecordsCount('contact_requests', $where);
    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }
} else {
    $message = 'Database connection failed.';
    $messageType = 'danger';
}

$contactRequests = [];
if ($DB) {
    $contactRequests = $FRMWRK->DBRecords(
        "SELECT id, name, campaign, subject, email, message, attachments_json, source_page, host, ip, user_agent, status, created_at, updated_at
         FROM contact_requests
         WHERE {$where}
         ORDER BY created_at DESC, id DESC
         LIMIT {$offset}, {$perPage}"
    );
}

$statusCounts = [
    'all' => $DB ? (int)$FRMWRK->DBRecordsCount('contact_requests', '') : 0,
    'new' => $DB ? (int)$FRMWRK->DBRecordsCount('contact_requests', "status='new'") : 0,
    'in_progress' => $DB ? (int)$FRMWRK->DBRecordsCount('contact_requests', "status='in_progress'") : 0,
    'closed' => $DB ? (int)$FRMWRK->DBRecordsCount('contact_requests', "status='closed'") : 0,
];
