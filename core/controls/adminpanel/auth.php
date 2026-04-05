<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();

require_once __DIR__ . '/_common.php';

if (adminpanel_get_current_user($FRMWRK)) {
    header('Location: /adminpanel/');
    exit;
}

$message = '';
$message_type = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));

    if ($email === '' || $password === '') {
        $message = 'Email and password are required.';
        $message_type = 'error';
    } else {
        $emailSafe = mysqli_real_escape_string($DB, $email);
        $rows = $FRMWRK->DBRecords(
            "SELECT * FROM adminpanel_users WHERE email = '{$emailSafe}' AND is_active = 1 LIMIT 1"
        );

        if (empty($rows)) {
            $message = 'Invalid credentials.';
            $message_type = 'error';
        } else {
            $user = $rows[0];
            $passwordSha = hash('sha256', $password);
            $passwordSha1 = sha1($password);
            $storedSha = strtolower(trim((string)($user['password_hash_sha256'] ?? '')));
            $storedPasswordHash = (string)($user['password_hash'] ?? '');

            $isValid = false;
            if ($storedSha !== '') {
                if (strlen($storedSha) === 64) {
                    $isValid = hash_equals($storedSha, strtolower($passwordSha));
                } elseif (strlen($storedSha) === 40) {
                    // Legacy rows may contain SHA-1; allow login once and migrate to SHA-256.
                    $isValid = hash_equals($storedSha, strtolower($passwordSha1));
                }
            }
            if (!$isValid && $storedPasswordHash !== '') {
                $isValid = password_verify($password, $storedPasswordHash);
            }

            if (!$isValid) {
                $message = 'Invalid credentials.';
                $message_type = 'error';
            } else {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', time() + 7 * 24 * 3600);

                $updateData = [
                    'token' => $token,
                    'token_expires' => $expiry,
                    'last_login_at' => date('Y-m-d H:i:s')
                ];
                // Normalize on successful login: keep SHA-256 current in the primary adminpanel field.
                if ($storedSha === '' || strlen($storedSha) !== 64 || !hash_equals($storedSha, strtolower($passwordSha))) {
                    $updateData['password_hash_sha256'] = strtolower($passwordSha);
                }

                $FRMWRK->DBRecordsUpdate(
                    'adminpanel_users',
                    $updateData,
                    "id='" . intval($user['id']) . "'"
                );

                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                setcookie('adminpanel_token', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                setcookie('adminpanel_token', $token, [
                    'expires' => time() + 7 * 24 * 3600,
                    'path' => '/adminpanel/',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                header('Location: /adminpanel/');
                exit;
            }
        }
    }
}
?>

