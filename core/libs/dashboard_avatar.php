<?php

function dashboard_avatar_table_ready(mysqli $db): bool
{
    static $ready = null;
    if ($ready === true) {
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS user_avatars (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        avatar_data_url LONGTEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_user_avatars_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $ok = (bool)mysqli_query($db, $sql);
    $ready = $ok;
    return $ok;
}

function dashboard_avatar_color(string $hash, int $offset): string
{
    $r = hexdec(substr($hash, $offset % 30, 2));
    $g = hexdec(substr($hash, ($offset + 8) % 30, 2));
    $b = hexdec(substr($hash, ($offset + 16) % 30, 2));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function dashboard_avatar_generate_data_url(string $seed): string
{
    $hash = hash('sha256', $seed);
    $bgA = dashboard_avatar_color($hash, 2);
    $bgB = dashboard_avatar_color($hash, 10);
    $blobA = dashboard_avatar_color($hash, 18);
    $blobB = dashboard_avatar_color($hash, 26);

    $x1 = 20 + (hexdec(substr($hash, 0, 2)) % 44);
    $y1 = 20 + (hexdec(substr($hash, 2, 2)) % 44);
    $x2 = 36 + (hexdec(substr($hash, 4, 2)) % 44);
    $y2 = 36 + (hexdec(substr($hash, 6, 2)) % 44);
    $r1 = 16 + (hexdec(substr($hash, 8, 2)) % 20);
    $r2 = 16 + (hexdec(substr($hash, 10, 2)) % 20);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" width="96" height="96">'
        . '<defs>'
        . '<linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0%" stop-color="' . $bgA . '"/>'
        . '<stop offset="100%" stop-color="' . $bgB . '"/>'
        . '</linearGradient>'
        . '</defs>'
        . '<rect width="96" height="96" rx="24" fill="url(#g)"/>'
        . '<circle cx="' . $x1 . '" cy="' . $y1 . '" r="' . $r1 . '" fill="' . $blobA . '" fill-opacity="0.55"/>'
        . '<circle cx="' . $x2 . '" cy="' . $y2 . '" r="' . $r2 . '" fill="' . $blobB . '" fill-opacity="0.45"/>'
        . '<path d="M10 72 Q48 ' . (60 + (hexdec(substr($hash, 12, 2)) % 16)) . ' 86 26" stroke="rgba(255,255,255,0.55)" stroke-width="4" fill="none"/>'
        . '<path d="M8 20 Q40 ' . (24 + (hexdec(substr($hash, 14, 2)) % 20)) . ' 88 76" stroke="rgba(255,255,255,0.28)" stroke-width="3" fill="none"/>'
        . '</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function dashboard_avatar_ensure_for_user(FRMWRK $FRMWRK, int $userId, string $email = ''): string
{
    if ($userId <= 0) {
        return '/template/views/dashboard/assets/images/users/user-1.jpg';
    }

    $db = $FRMWRK->DB();
    if (!$db || !dashboard_avatar_table_ready($db)) {
        return '/template/views/dashboard/assets/images/users/user-1.jpg';
    }

    $uidSafe = mysqli_real_escape_string($db, (string)$userId);
    $res = mysqli_query(
        $db,
        "SELECT avatar_data_url
         FROM user_avatars
         WHERE user_id='{$uidSafe}'
         LIMIT 1"
    );

    if ($res) {
        $row = mysqli_fetch_assoc($res);
        if (is_array($row) && !empty($row['avatar_data_url'])) {
            return (string)$row['avatar_data_url'];
        }
    }

    $seed = $email !== '' ? strtolower(trim($email)) : ('user:' . $userId);
    $dataUrl = dashboard_avatar_generate_data_url($seed);
    $dataSafe = mysqli_real_escape_string($db, $dataUrl);
    mysqli_query(
        $db,
        "INSERT INTO user_avatars (user_id, avatar_data_url, created_at, updated_at)
         VALUES ('{$uidSafe}', '{$dataSafe}', NOW(), NOW())
         ON DUPLICATE KEY UPDATE
            avatar_data_url = VALUES(avatar_data_url),
            updated_at = NOW()"
    );

    return $dataUrl;
}

