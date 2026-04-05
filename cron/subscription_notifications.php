<?php
ini_set('display_errors', '0');
date_default_timezone_set('UTC');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/libs/dashboard_notifications.php';

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();

if (!dashboard_db_has_table($DB, 'subscriptions') || !dashboard_db_has_table($DB, 'user_notifications')) {
    exit(0);
}

if (!dashboard_db_has_column($DB, 'subscriptions', 'expires_at')) {
    exit(0);
}

function cron_sub_create_notification(FRMWRK $FRMWRK, int $userId, string $type, string $title, string $message, string $eventKey, array $payload = []): void
{
    dashboard_notification_create(
        $FRMWRK,
        $userId,
        $type,
        $title,
        $message,
        $payload,
        $eventKey,
        '/dashboard/subscribe/'
    );
}

// 1) Create "subscription created" notifications for pending rows.
$pendingSubs = $FRMWRK->DBRecords(
    "SELECT id, user_id, plan_id, created_at
     FROM subscriptions
     WHERE status=0
     ORDER BY id DESC
     LIMIT 500"
);
foreach ($pendingSubs as $sub) {
    $subId = (int)($sub['id'] ?? 0);
    $userId = (int)($sub['user_id'] ?? 0);
    if ($subId <= 0 || $userId <= 0) {
        continue;
    }
    cron_sub_create_notification(
        $FRMWRK,
        $userId,
        'subscription_created',
        'Subscription request created',
        'Your payment request is waiting for activation.',
        'sub_created_' . $subId,
        ['subscription_id' => $subId]
    );
}

// 2) Create "subscription activated" notifications for active rows.
$activeSubs = $FRMWRK->DBRecords(
    "SELECT id, user_id, expires_at
     FROM subscriptions
     WHERE status=1
       AND expires_at IS NOT NULL
     ORDER BY id DESC
     LIMIT 500"
);
foreach ($activeSubs as $sub) {
    $subId = (int)($sub['id'] ?? 0);
    $userId = (int)($sub['user_id'] ?? 0);
    $expiresAt = (string)($sub['expires_at'] ?? '');
    if ($subId <= 0 || $userId <= 0) {
        continue;
    }
    cron_sub_create_notification(
        $FRMWRK,
        $userId,
        'subscription_activated',
        'Subscription activated',
        'Your subscription is active until ' . $expiresAt . '.',
        'sub_activated_' . $subId,
        ['subscription_id' => $subId, 'expires_at' => $expiresAt]
    );
}

// 3) 3/2/1 day reminders.
for ($days = 3; $days >= 1; $days--) {
    $rows = $FRMWRK->DBRecords(
        "SELECT id, user_id, expires_at
         FROM subscriptions
         WHERE status=1
           AND expires_at IS NOT NULL
           AND DATE(expires_at) = DATE(DATE_ADD(NOW(), INTERVAL {$days} DAY))
         LIMIT 1000"
    );
    foreach ($rows as $sub) {
        $subId = (int)($sub['id'] ?? 0);
        $userId = (int)($sub['user_id'] ?? 0);
        $expiresAt = (string)($sub['expires_at'] ?? '');
        if ($subId <= 0 || $userId <= 0) {
            continue;
        }
        cron_sub_create_notification(
            $FRMWRK,
            $userId,
            'subscription_expiring',
            'Subscription expires soon',
            'Your subscription expires in ' . $days . ' day(s): ' . $expiresAt . '.',
            'sub_expiring_' . $subId . '_' . $days,
            ['subscription_id' => $subId, 'days_left' => $days, 'expires_at' => $expiresAt]
        );
    }
}

// 4) Expired subscriptions -> deactivate + notify.
$expiredRows = $FRMWRK->DBRecords(
    "SELECT id, user_id, expires_at
     FROM subscriptions
     WHERE status=1
       AND expires_at IS NOT NULL
       AND expires_at <= NOW()
     LIMIT 1000"
);
foreach ($expiredRows as $sub) {
    $subId = (int)($sub['id'] ?? 0);
    $userId = (int)($sub['user_id'] ?? 0);
    $expiresAt = (string)($sub['expires_at'] ?? '');
    if ($subId <= 0 || $userId <= 0) {
        continue;
    }

    $updates = ['status' => 2];
    if (dashboard_db_has_column($DB, 'subscriptions', 'deactivated_at')) {
        $updates['deactivated_at'] = date('Y-m-d H:i:s');
    }
    $FRMWRK->DBRecordsUpdate('subscriptions', $updates, "id='" . $subId . "'");

    cron_sub_create_notification(
        $FRMWRK,
        $userId,
        'subscription_deactivated',
        'Subscription deactivated',
        'Subscription expired and was deactivated at ' . date('Y-m-d H:i:s') . '.',
        'sub_deactivated_' . $subId,
        ['subscription_id' => $subId, 'expired_at' => $expiresAt]
    );
}

exit(0);

