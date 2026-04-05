<?php
session_start();

$FRMWRK = new FRMWRK();
$adminpanelUser = null;
$message = '';
$messageType = '';

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);
$dashboardNotifyLib = DIR . '/core/libs/dashboard_notifications.php';
if (file_exists($dashboardNotifyLib)) {
    require_once $dashboardNotifyLib;
}

$DB = $FRMWRK->DB();
$hasRegistrationDomain = false;
$check = mysqli_query(
    $DB,
    "SELECT 1
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'admins'
       AND COLUMN_NAME = 'registration_domain'
     LIMIT 1"
);
if ($check && mysqli_num_rows($check) > 0) {
    $hasRegistrationDomain = true;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (($_POST['action'] ?? '') === 'resend_system_notifications')) {
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    if ($targetUserId <= 0 || !function_exists('dashboard_notification_create')) {
        $message = 'Invalid resend request.';
        $messageType = 'danger';
    } else {
        $ts = date('YmdHis');
        dashboard_notification_create(
            $FRMWRK,
            $targetUserId,
            'welcome',
            'Welcome to your account',
            'Manual resend: welcome and onboarding reminder.',
            ['source' => 'admin_resend'],
            'manual_welcome_' . $targetUserId . '_' . $ts,
            '/dashboard/docs/'
        );
        dashboard_notification_create(
            $FRMWRK,
            $targetUserId,
            'security',
            'Security reminder',
            'Manual resend: keep your password and API key secure.',
            ['source' => 'admin_resend'],
            'manual_security_' . $targetUserId . '_' . $ts,
            '/dashboard/settings/'
        );
        $message = 'System notifications were resent.';
        $messageType = 'success';
    }
}

$select = "SELECT id, email, is_confirmed, api_key, created_at";
if ($hasRegistrationDomain) {
    $select .= ", registration_domain";
}
$select .= " FROM admins ORDER BY id DESC";

$users = $FRMWRK->DBRecords($select);
?>
