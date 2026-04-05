<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();

$token = $_COOKIE['adminpanel_token'] ?? null;
if (is_string($token) && $token !== '') {
    $tokenSafe = mysqli_real_escape_string($DB, $token);
    mysqli_query($DB, "UPDATE adminpanel_users SET token = '', token_expires = NULL WHERE token = '{$tokenSafe}'");
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

// Expire auth cookie for all common path variants.
setcookie('adminpanel_token', '', [
    'expires' => time() - 3600,
    'path' => '/adminpanel/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);
setcookie('adminpanel_token', '', [
    'expires' => time() - 3600,
    'path' => '/adminpanel',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);
setcookie('adminpanel_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

$_SESSION = [];
if (session_id() !== '' || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
    setcookie(session_name(), '', time() - 3600, '/adminpanel/');
    setcookie(session_name(), '', time() - 3600, '/adminpanel');
}
session_destroy();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: /adminpanel/auth/');

// Fallback in case redirects are blocked by server output state.
echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=/adminpanel/auth/"><script>location.replace("/adminpanel/auth/");</script></head><body></body></html>';
exit;
?>
