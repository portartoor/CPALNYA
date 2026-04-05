<?php
if (!function_exists('adminpanel_get_current_user')) {
    function adminpanel_get_current_user($FRMWRK): ?array
    {
        $token = $_COOKIE['adminpanel_token'] ?? null;
        if (!$token) {
            return null;
        }

        $DB = $FRMWRK->DB();
        $tokenSafe = mysqli_real_escape_string($DB, $token);

        $rows = $FRMWRK->DBRecords(
            "SELECT id, email, is_active, token_expires, last_login_at, created_at
             FROM adminpanel_users
             WHERE token = '{$tokenSafe}'
               AND is_active = 1
               AND token_expires > NOW()
             LIMIT 1"
        );

        return !empty($rows) ? $rows[0] : null;
    }
}

if (!function_exists('adminpanel_require_auth')) {
    function adminpanel_require_auth($FRMWRK): array
    {
        $user = adminpanel_get_current_user($FRMWRK);
        if (!$user) {
            header('Location: /adminpanel/auth/');
            exit;
        }

        return $user;
    }
}
