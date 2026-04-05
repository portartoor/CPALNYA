<?php

function dashboard_db_has_table(mysqli $db, string $table): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

function dashboard_db_has_column(mysqli $db, string $table, string $column): bool
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
    return mysqli_num_rows($res) > 0;
}

function dashboard_current_host(): string
{
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ($_SERVER['MIRROR_DOMAIN_HOST'] ?? '')));
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    return trim($host);
}

function dashboard_support_link(): string
{
    $name = (string)($GLOBALS['SupportTelegramName'] ?? '');
    $url = (string)($GLOBALS['SupportTelegramUrl'] ?? '');
    $name = trim($name);
    $url = trim($url);

    // In this project config.php is often included inside methods, so
    // SupportTelegram* variables may not always land in $GLOBALS.
    if ($name === '' && $url === '') {
        $configFile = dirname(__DIR__) . '/config.php';
        if (is_file($configFile)) {
            $SupportTelegramName = '';
            $SupportTelegramUrl = '';
            include $configFile;
            $name = trim((string)$SupportTelegramName);
            $url = trim((string)$SupportTelegramUrl);
        }
    }

    if ($url !== '') {
        return $url;
    }
    if ($name === '') {
        return 'https://t.me/apigeoip';
    }
    $name = ltrim($name, '@');
    return 'https://t.me/' . $name;
}

function dashboard_branding(): array
{
    $templateKey = strtolower((string)($_SERVER['MIRROR_TEMPLATE_KEY'] ?? 'simple'));
    $host = dashboard_current_host();
    $isApiGeoRu = ($templateKey === 'simple_apigeo_ru' || $host === 'apigeoip.ru' || $host === 'www.apigeoip.ru');
    $isApiGeo = ($templateKey === 'simple_apigeo' || $isApiGeoRu || $host === 'apigeoip.online' || $host === 'www.apigeoip.online');

    if ($isApiGeo) {
        return [
            'title' => $isApiGeoRu ? 'ApiGeoIP.ru' : 'ApiGeoIP.online',
            'sub' => $isApiGeoRu ? 'Платформа IP-аналитики и антифрода' : 'API Intelligence Platform',
            'is_apigeo' => true,
            'is_apigeo_ru' => $isApiGeoRu,
        ];
    }

    return [
        'title' => 'CODERS',
        'sub' => 'Blog, Services and Portfolio',
        'is_apigeo' => false,
        'is_apigeo_ru' => false,
    ];
}

function dashboard_notification_create(
    FRMWRK $FRMWRK,
    int $userId,
    string $type,
    string $title,
    string $message,
    array $payload = [],
    string $eventKey = '',
    string $linkUrl = ''
): bool {
    if ($userId <= 0) {
        return false;
    }

    $db = $FRMWRK->DB();
    if (!dashboard_db_has_table($db, 'user_notifications')) {
        return false;
    }

    $eventKey = trim($eventKey);
    if ($eventKey !== '') {
        $eventKeySafe = mysqli_real_escape_string($db, $eventKey);
        $exists = $FRMWRK->DBRecords("SELECT id FROM user_notifications WHERE event_key='{$eventKeySafe}' LIMIT 1");
        if (!empty($exists)) {
            return true;
        }
    }

    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($payloadJson)) {
        $payloadJson = '{}';
    }

    $fields = ['user_id', 'type', 'title', 'message', 'payload_json', 'is_read', 'created_at'];
    $values = [
        (string)$userId,
        mysqli_real_escape_string($db, $type),
        mysqli_real_escape_string($db, $title),
        mysqli_real_escape_string($db, $message),
        mysqli_real_escape_string($db, $payloadJson),
        '0',
        date('Y-m-d H:i:s')
    ];

    if (dashboard_db_has_column($db, 'user_notifications', 'event_key')) {
        $fields[] = 'event_key';
        $values[] = mysqli_real_escape_string($db, $eventKey);
    }
    if (dashboard_db_has_column($db, 'user_notifications', 'link_url')) {
        $fields[] = 'link_url';
        $values[] = mysqli_real_escape_string($db, $linkUrl);
    }

    $res = $FRMWRK->DBRecordsCreate('user_notifications', $fields, $values);
    return (($res['status'] ?? '') === 'success');
}

function dashboard_notification_fetch(FRMWRK $FRMWRK, int $userId, int $limit = 10): array
{
    if ($userId <= 0) {
        return ['rows' => [], 'unread' => 0];
    }

    $db = $FRMWRK->DB();
    if (!dashboard_db_has_table($db, 'user_notifications')) {
        return ['rows' => [], 'unread' => 0];
    }

    $limit = max(1, min(100, $limit));
    $uidSafe = mysqli_real_escape_string($db, (string)$userId);
    $rows = $FRMWRK->DBRecords(
        "SELECT id, type, title, message, link_url, is_read, created_at
         FROM user_notifications
         WHERE user_id='{$uidSafe}'
         ORDER BY id DESC
         LIMIT {$limit}"
    );

    $cntRows = $FRMWRK->DBRecords(
        "SELECT COUNT(*) AS c
         FROM user_notifications
         WHERE user_id='{$uidSafe}' AND is_read=0"
    );

    return [
        'rows' => $rows,
        'unread' => (int)($cntRows[0]['c'] ?? 0),
    ];
}

function dashboard_notification_mark_read(FRMWRK $FRMWRK, int $userId, int $notificationId): bool
{
    if ($userId <= 0 || $notificationId <= 0) {
        return false;
    }
    $db = $FRMWRK->DB();
    if (!dashboard_db_has_table($db, 'user_notifications')) {
        return false;
    }
    $uidSafe = mysqli_real_escape_string($db, (string)$userId);
    $nidSafe = mysqli_real_escape_string($db, (string)$notificationId);
    $res = $FRMWRK->DBRecordsUpdate(
        'user_notifications',
        ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
        "id='{$nidSafe}' AND user_id='{$uidSafe}'"
    );
    return (($res['status'] ?? '') === 'success');
}

function dashboard_notification_mark_all_read(FRMWRK $FRMWRK, int $userId): bool
{
    if ($userId <= 0) {
        return false;
    }
    $db = $FRMWRK->DB();
    if (!dashboard_db_has_table($db, 'user_notifications')) {
        return false;
    }
    $uidSafe = mysqli_real_escape_string($db, (string)$userId);
    $sql = "UPDATE user_notifications
            SET is_read=1, read_at='" . mysqli_real_escape_string($db, date('Y-m-d H:i:s')) . "'
            WHERE user_id='{$uidSafe}' AND is_read=0";
    return (bool)mysqli_query($db, $sql);
}

function dashboard_active_subscription(FRMWRK $FRMWRK, int $userId): ?array
{
    if ($userId <= 0) {
        return null;
    }
    $db = $FRMWRK->DB();
    $uidSafe = mysqli_real_escape_string($db, (string)$userId);
    $hasExpires = dashboard_db_has_column($db, 'subscriptions', 'expires_at');

    $rows = $FRMWRK->DBRecords(
        "SELECT s.*, p.name AS plan_name
         FROM subscriptions s
         LEFT JOIN plans p ON p.id = s.plan_id
         WHERE s.user_id='{$uidSafe}' AND s.status=1
         ORDER BY s.created_at DESC
         LIMIT 30"
    );
    if (empty($rows)) {
        return null;
    }

    $best = null;
    $bestTs = 0;
    $nowTs = time();
    foreach ($rows as $row) {
        $eff = dashboard_subscription_effective_expiry($row);
        if ($eff === null) {
            // Legacy active row without expiry: treat as active and highest priority.
            if ($best === null) {
                $row['effective_expires_at'] = null;
                $best = $row;
            }
            continue;
        }
        $ts = strtotime($eff);
        if ($ts !== false && $ts > $nowTs && $ts > $bestTs) {
            $row['effective_expires_at'] = $eff;
            $best = $row;
            $bestTs = $ts;
        }
    }

    return $best;
}

function dashboard_subscription_effective_expiry(array $sub): ?string
{
    $expiresAt = trim((string)($sub['expires_at'] ?? ''));
    if ($expiresAt !== '') {
        return $expiresAt;
    }

    $duration = max(1, (int)($sub['duration'] ?? 1));
    $base = trim((string)($sub['activated_at'] ?? ''));
    if ($base === '') {
        $base = trim((string)($sub['created_at'] ?? ''));
    }
    if ($base === '') {
        return null;
    }

    try {
        $d = new DateTime($base);
        $d->modify('+' . $duration . ' month');
        return $d->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        return null;
    }
}

function dashboard_subscription_status(FRMWRK $FRMWRK, int $userId): array
{
    $sub = dashboard_active_subscription($FRMWRK, $userId);
    if (!is_array($sub)) {
        return [
            'active' => false,
            'label' => 'Inactive',
            'plan_name' => null,
            'expires_at' => null,
            'remaining_text' => null,
        ];
    }

    $expiresAt = (string)($sub['effective_expires_at'] ?? ($sub['expires_at'] ?? ''));
    $remainingText = null;
    if ($expiresAt !== '') {
        $remainingText = dashboard_time_left_human($expiresAt);
    }

    $label = 'Active';
    if ($expiresAt !== '') {
        $label .= ' until ' . date('Y-m-d', strtotime($expiresAt));
    } elseif ($remainingText !== null && $remainingText !== '') {
        $label .= ' - ' . $remainingText;
    }

    return [
        'active' => true,
        'label' => $label,
        'plan_name' => (string)($sub['plan_name'] ?? ''),
        'expires_at' => $expiresAt !== '' ? $expiresAt : null,
        'remaining_text' => $remainingText,
    ];
}

function dashboard_time_left_human(string $expiresAt): string
{
    try {
        $now = new DateTime('now');
        $exp = new DateTime($expiresAt);
        if ($exp <= $now) {
            return 'expired';
        }
        $diff = $now->diff($exp);
        if ($diff->days > 0) {
            return $diff->days . ' day(s) left';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour(s) left';
        }
        return max(1, (int)$diff->i) . ' min left';
    } catch (Throwable $e) {
        return '';
    }
}

function dashboard_subscription_activate(FRMWRK $FRMWRK, int $subscriptionId, string $actor = 'system'): array
{
    $db = $FRMWRK->DB();
    $subIdSafe = mysqli_real_escape_string($db, (string)$subscriptionId);
    $rows = $FRMWRK->DBRecords("SELECT * FROM subscriptions WHERE id='{$subIdSafe}' LIMIT 1");
    if (empty($rows)) {
        return ['ok' => false, 'error' => 'Subscription not found'];
    }
    $sub = $rows[0];
    $userId = (int)($sub['user_id'] ?? 0);
    $duration = max(1, (int)($sub['duration'] ?? 1));

    $hasExpires = dashboard_db_has_column($db, 'subscriptions', 'expires_at');
    $hasActivatedAt = dashboard_db_has_column($db, 'subscriptions', 'activated_at');
    $hasDeactivatedAt = dashboard_db_has_column($db, 'subscriptions', 'deactivated_at');
    $hasExtendedFrom = dashboard_db_has_column($db, 'subscriptions', 'extended_from_subscription_id');

    $extendedFromId = 0;
    $startAt = new DateTime('now');
    $nowStr = $startAt->format('Y-m-d H:i:s');

    if ($hasExpires) {
        $uidSafe = mysqli_real_escape_string($db, (string)$userId);
        $activeRows = $FRMWRK->DBRecords(
            "SELECT id, expires_at
             FROM subscriptions
             WHERE user_id='{$uidSafe}'
               AND status=1
               AND id<>'{$subIdSafe}'
               AND expires_at IS NOT NULL
               AND expires_at > NOW()
             ORDER BY expires_at DESC
             LIMIT 1"
        );
        if (!empty($activeRows)) {
            $extendedFromId = (int)$activeRows[0]['id'];
            try {
                $fromExp = new DateTime((string)$activeRows[0]['expires_at']);
                if ($fromExp > $startAt) {
                    $startAt = $fromExp;
                }
            } catch (Throwable $e) {
            }
        }
    }

    $expireAt = clone $startAt;
    $expireAt->modify('+' . $duration . ' month');
    $expireAtStr = $expireAt->format('Y-m-d H:i:s');

    $update = ['status' => 1];
    if ($hasActivatedAt) {
        $update['activated_at'] = $nowStr;
    }
    if ($hasExpires) {
        $update['expires_at'] = $expireAtStr;
    }
    if ($hasExtendedFrom && $extendedFromId > 0) {
        $update['extended_from_subscription_id'] = $extendedFromId;
    }
    $FRMWRK->DBRecordsUpdate('subscriptions', $update, "id='{$subIdSafe}'");

    // Keep one active subscription per user.
    if ($userId > 0) {
        $uidSafe = mysqli_real_escape_string($db, (string)$userId);
        $deactivateUpdate = ['status' => 2];
        if ($hasDeactivatedAt) {
            $deactivateUpdate['deactivated_at'] = $nowStr;
        }
        $FRMWRK->DBRecordsUpdate(
            'subscriptions',
            $deactivateUpdate,
            "user_id='{$uidSafe}' AND id<>'{$subIdSafe}' AND status=1"
        );
    }

    $planRows = $FRMWRK->DBRecords("SELECT name FROM plans WHERE id='" . (int)($sub['plan_id'] ?? 0) . "' LIMIT 1");
    $planName = (string)($planRows[0]['name'] ?? 'Subscription');
    $message = 'Plan "' . $planName . '" is active until ' . $expireAtStr . '.';
    dashboard_notification_create(
        $FRMWRK,
        $userId,
        'subscription_activated',
        'Subscription activated',
        $message,
        [
            'subscription_id' => (int)$sub['id'],
            'expires_at' => $expireAtStr,
            'actor' => $actor
        ],
        'sub_activated_' . (int)$sub['id'],
        '/dashboard/subscribe/'
    );

    return [
        'ok' => true,
        'subscription_id' => (int)$sub['id'],
        'expires_at' => $expireAtStr,
        'extended_from_subscription_id' => $extendedFromId > 0 ? $extendedFromId : null,
    ];
}
