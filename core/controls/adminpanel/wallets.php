<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';
$action = $_POST['action'] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($action === 'add') {
        $name = trim((string)($_POST['name'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));

        if ($name !== '' && $address !== '') {
            $FRMWRK->DBRecordsCreate('wallets', ['name', 'address'], [
                mysqli_real_escape_string($DB, $name),
                mysqli_real_escape_string($DB, $address)
            ]);
            $message = 'Wallet added.';
            $messageType = 'success';
        } else {
            $message = 'Name and address are required.';
            $messageType = 'danger';
        }
    }

    if ($action === 'update') {
        $walletId = intval($_POST['wallet_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));

        if ($walletId > 0 && $name !== '' && $address !== '') {
            $FRMWRK->DBRecordsUpdate('wallets', [
                'name' => mysqli_real_escape_string($DB, $name),
                'address' => mysqli_real_escape_string($DB, $address)
            ], "id='{$walletId}'");
            $message = 'Wallet updated.';
            $messageType = 'success';
        } else {
            $message = 'Invalid wallet data.';
            $messageType = 'danger';
        }
    }

    if ($action === 'delete') {
        $walletId = intval($_POST['wallet_id'] ?? 0);
        if ($walletId > 0) {
            $FRMWRK->DBRecordsDelete('wallets', "id='{$walletId}'");
            $message = 'Wallet deleted.';
            $messageType = 'success';
        } else {
            $message = 'Invalid wallet id.';
            $messageType = 'danger';
        }
    }
}

$wallets = $FRMWRK->DBRecords("SELECT * FROM wallets ORDER BY id ASC");
?>
