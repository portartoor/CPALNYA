<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $planId = intval($_POST['plan_id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $price = trim((string)($_POST['price'] ?? '0'));
    $type = trim((string)($_POST['type'] ?? 'geo'));

    if ($planId > 0 && $name !== '' && is_numeric($price) && in_array($type, ['geo', 'antifraud'], true)) {
        $nameSafe = mysqli_real_escape_string($DB, $name);
        $descriptionSafe = mysqli_real_escape_string($DB, $description);
        $priceFloat = (float)$price;

        $FRMWRK->DBRecordsUpdate('plans', [
            'name' => $nameSafe,
            'description' => $descriptionSafe,
            'price' => $priceFloat,
            'type' => $type
        ], "id='{$planId}'");

        $message = 'Plan updated.';
        $messageType = 'success';
    } else {
        $message = 'Invalid plan data.';
        $messageType = 'danger';
    }
}

$plans = $FRMWRK->DBRecords("SELECT * FROM plans ORDER BY id ASC");
?>
