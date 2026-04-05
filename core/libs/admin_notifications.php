<?php

if (!function_exists('admin_notifications_table_exists')) {
    function admin_notifications_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'admin_notifications'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('admin_notifications_ensure_schema')) {
    function admin_notifications_ensure_schema(mysqli $db): bool
    {
        $createSql = "
            CREATE TABLE IF NOT EXISTS admin_notifications (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                type VARCHAR(64) NOT NULL DEFAULT '',
                title VARCHAR(255) NOT NULL DEFAULT '',
                message VARCHAR(1000) NOT NULL DEFAULT '',
                link_url VARCHAR(500) NOT NULL DEFAULT '',
                payload_json LONGTEXT NULL,
                event_key VARCHAR(190) NOT NULL DEFAULT '',
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_admin_notifications_event_key (event_key),
                KEY idx_admin_notifications_unread (is_read, id),
                KEY idx_admin_notifications_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        if (!mysqli_query($db, $createSql)) {
            return false;
        }
        return admin_notifications_table_exists($db);
    }
}

if (!function_exists('admin_notifications_table_has_column')) {
    function admin_notifications_table_has_column(mysqli $db, string $column): bool
    {
        $columnSafe = mysqli_real_escape_string($db, $column);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'admin_notifications'
               AND COLUMN_NAME = '{$columnSafe}'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('admin_notifications_cut')) {
    function admin_notifications_cut(string $value, int $limit): string
    {
        if ($limit <= 0) {
            return '';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $limit);
        }
        return substr($value, 0, $limit);
    }
}

if (!function_exists('admin_notifications_create')) {
    function admin_notifications_create(
        mysqli $db,
        string $type,
        string $title,
        string $message,
        string $linkUrl = '',
        array $payload = [],
        string $eventKey = ''
    ): bool {
        if (!admin_notifications_ensure_schema($db)) {
            return false;
        }

        $hasLink = admin_notifications_table_has_column($db, 'link_url');
        $hasPayload = admin_notifications_table_has_column($db, 'payload_json');
        $hasEventKey = admin_notifications_table_has_column($db, 'event_key');

        if ($hasEventKey && $eventKey !== '') {
            $eventKeySafe = mysqli_real_escape_string($db, admin_notifications_cut($eventKey, 190));
            $existsRes = mysqli_query(
                $db,
                "SELECT id
                 FROM admin_notifications
                 WHERE event_key = '{$eventKeySafe}'
                 LIMIT 1"
            );
            if ($existsRes && mysqli_num_rows($existsRes) > 0) {
                return true;
            }
        }

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($payloadJson) || $payloadJson === '') {
            $payloadJson = '{}';
        }

        $columns = ['type', 'title', 'message', 'is_read', 'created_at'];
        $values = [
            "'" . mysqli_real_escape_string($db, admin_notifications_cut($type, 64)) . "'",
            "'" . mysqli_real_escape_string($db, admin_notifications_cut($title, 255)) . "'",
            "'" . mysqli_real_escape_string($db, admin_notifications_cut($message, 1000)) . "'",
            '0',
            'NOW()',
        ];

        if ($hasLink) {
            $columns[] = 'link_url';
            $values[] = "'" . mysqli_real_escape_string($db, admin_notifications_cut($linkUrl, 500)) . "'";
        }
        if ($hasPayload) {
            $columns[] = 'payload_json';
            $values[] = "'" . mysqli_real_escape_string($db, $payloadJson) . "'";
        }
        if ($hasEventKey) {
            $columns[] = 'event_key';
            $values[] = "'" . mysqli_real_escape_string($db, admin_notifications_cut($eventKey, 190)) . "'";
        }

        $sql = "INSERT INTO admin_notifications (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $values) . ")";
        return (bool)mysqli_query($db, $sql);
    }
}

if (!function_exists('admin_notifications_mark_read')) {
    function admin_notifications_mark_read(mysqli $db, int $id): bool
    {
        if ($id <= 0 || !admin_notifications_ensure_schema($db)) {
            return false;
        }
        $hasReadAt = admin_notifications_table_has_column($db, 'read_at');
        $sql = "UPDATE admin_notifications
                SET is_read = 1" . ($hasReadAt ? ", read_at = NOW()" : "") . "
                WHERE id = " . (int)$id . "
                LIMIT 1";
        return (bool)mysqli_query($db, $sql);
    }
}

if (!function_exists('admin_notifications_mark_all_read')) {
    function admin_notifications_mark_all_read(mysqli $db): bool
    {
        if (!admin_notifications_ensure_schema($db)) {
            return false;
        }
        $hasReadAt = admin_notifications_table_has_column($db, 'read_at');
        $sql = "UPDATE admin_notifications
                SET is_read = 1" . ($hasReadAt ? ", read_at = NOW()" : "") . "
                WHERE is_read = 0";
        return (bool)mysqli_query($db, $sql);
    }
}

if (!function_exists('admin_notifications_fetch')) {
    function admin_notifications_fetch(mysqli $db, int $limit = 10): array
    {
        $result = ['rows' => [], 'unread' => 0];
        if (!admin_notifications_ensure_schema($db)) {
            return $result;
        }

        $limit = max(1, min(100, $limit));
        $hasLink = admin_notifications_table_has_column($db, 'link_url');
        $hasPayload = admin_notifications_table_has_column($db, 'payload_json');

        $unreadRows = mysqli_query($db, "SELECT COUNT(*) AS c FROM admin_notifications WHERE is_read = 0");
        if ($unreadRows) {
            $row = mysqli_fetch_assoc($unreadRows);
            $result['unread'] = (int)($row['c'] ?? 0);
        }

        $rowsRes = mysqli_query(
            $db,
            "SELECT id, type, title, message, is_read, created_at, "
            . ($hasLink ? "link_url" : "'' AS link_url") . ", "
            . ($hasPayload ? "payload_json" : "'{}' AS payload_json") . "
             FROM admin_notifications
             ORDER BY id DESC
             LIMIT " . (int)$limit
        );
        if ($rowsRes) {
            while ($row = mysqli_fetch_assoc($rowsRes)) {
                if (is_array($row)) {
                    $result['rows'][] = $row;
                }
            }
        }

        return $result;
    }
}
