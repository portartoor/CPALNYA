<?php
session_start();

$FRMWRK = new FRMWRK();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);
$dashboardNotifyLib = DIR . '/core/libs/dashboard_notifications.php';
if (file_exists($dashboardNotifyLib)) {
    require_once $dashboardNotifyLib;
}

$message = '';
$messageType = '';
$DB = $FRMWRK->DB();

function adminpanel_subscriptions_has_column(mysqli $db, string $table, string $column): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $columnSafe = mysqli_real_escape_string($db, $column);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
           AND COLUMN_NAME = '{$columnSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return (mysqli_num_rows($res) > 0);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $subscriptionId = intval($_POST['subscription_id'] ?? 0);
    $newStatus = intval($_POST['status'] ?? -1);

    if ($subscriptionId > 0 && in_array($newStatus, [0, 1, 2], true)) {
        if ($newStatus === 1 && function_exists('dashboard_subscription_activate')) {
            $activation = dashboard_subscription_activate($FRMWRK, $subscriptionId, 'adminpanel');
            if (!($activation['ok'] ?? false)) {
                $message = (string)($activation['error'] ?? 'Activation failed');
                $messageType = 'danger';
            } else {
                $message = 'Subscription activated and extended timeline applied.';
                $messageType = 'success';
            }
        } else {
            $FRMWRK->DBRecordsUpdate('subscriptions', [
                'status' => $newStatus
            ], "id='{$subscriptionId}'");
            if ($newStatus === 2 && function_exists('dashboard_db_has_column') && dashboard_db_has_column($DB, 'subscriptions', 'deactivated_at')) {
                $FRMWRK->DBRecordsUpdate('subscriptions', [
                    'deactivated_at' => date('Y-m-d H:i:s')
                ], "id='{$subscriptionId}'");
            }

            if ($newStatus === 2 && function_exists('dashboard_notification_create')) {
                $rows = $FRMWRK->DBRecords("SELECT id, user_id FROM subscriptions WHERE id='{$subscriptionId}' LIMIT 1");
                if (!empty($rows)) {
                    $userId = (int)($rows[0]['user_id'] ?? 0);
                    dashboard_notification_create(
                        $FRMWRK,
                        $userId,
                        'subscription_deactivated',
                        'Subscription deactivated',
                        'Your subscription was deactivated by administrator.',
                        ['subscription_id' => $subscriptionId],
                        'sub_deactivated_' . $subscriptionId,
                        '/dashboard/subscribe/'
                    );
                }
            }

            $message = 'Subscription status updated.';
            $messageType = 'success';
        }
    } else {
        $message = 'Invalid subscription action.';
        $messageType = 'danger';
    }
}

$hasWalletAddress = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'wallet_address');
$hasCurrencyCode = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'currency_code');
$hasAmountUsd = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'amount_usd');
$hasAmountCrypto = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'amount_crypto');
$hasAmountInCurrency = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'amount_in_currency');
$hasSourceDomain = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'source_domain');
$hasExpiresAt = adminpanel_subscriptions_has_column($DB, 'subscriptions', 'expires_at');

$extraFields = [];
if ($hasWalletAddress) {
    $extraFields[] = 's.wallet_address';
}
if ($hasCurrencyCode) {
    $extraFields[] = 's.currency_code';
}
if ($hasAmountUsd) {
    $extraFields[] = 's.amount_usd';
}
if ($hasAmountCrypto) {
    $extraFields[] = 's.amount_crypto';
}
if ($hasAmountInCurrency) {
    $extraFields[] = 's.amount_in_currency';
}
if ($hasSourceDomain) {
    $extraFields[] = 's.source_domain';
}
if ($hasExpiresAt) {
    $extraFields[] = 's.expires_at';
}

$selectExtra = '';
if (!empty($extraFields)) {
    $selectExtra = ",\n        " . implode(",\n        ", $extraFields);
}

$subscriptions = $FRMWRK->DBRecords(
    "SELECT
        s.id,
        s.user_id,
        s.plan_id,
        s.wallet_id,
        s.tx_hash,
        s.duration,
        s.price,
        s.discount,
        s.final_price,
        s.status,
        s.created_at
        {$selectExtra},
        a.email AS user_email,
        p.name AS plan_name,
        p.type AS plan_type,
        w.name AS wallet_name,
        w.address AS wallet_db_address
     FROM subscriptions s
     LEFT JOIN admins a ON a.id = s.user_id
     LEFT JOIN plans p ON p.id = s.plan_id
     LEFT JOIN wallets w ON w.id = s.wallet_id
     ORDER BY s.id DESC"
);
?>
