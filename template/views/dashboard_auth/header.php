<?php
$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost'));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$templateKey = (string)($_SERVER['MIRROR_TEMPLATE_KEY'] ?? '');
$isApiGeoRu = ($host === 'apigeoip.ru' || $host === 'www.apigeoip.ru' || $templateKey === 'simple_apigeo_ru');
$isApiGeo = ($host === 'apigeoip.online' || $host === 'www.apigeoip.online' || $templateKey === 'simple_apigeo' || $isApiGeoRu);
$brandTitle = $isApiGeo ? ($isApiGeoRu ? 'ApiGeoIP.ru' : 'ApiGeoIP.online') : strtoupper($host);
$authTitle = (($subroute ?? '') === 'auth')
    ? ($isApiGeoRu ? 'Вход' : 'Sign In')
    : ($isApiGeoRu ? 'Авторизация дашборда' : 'Dashboard Auth');
$metaDescription = $isApiGeoRu
    ? ($brandTitle . ' — вход в дашборд, регистрация аккаунта и безопасный доступ.')
    : ($brandTitle . ' dashboard access: sign in, account registration and secure access.');
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= htmlspecialchars($authTitle . ' | ' . $brandTitle) ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
        <meta name="keywords" content="dashboard auth, account sign in, secure access">
        <meta name="author" content="<?= htmlspecialchars($brandTitle) ?>">

        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
 <!-- Theme Config Js -->
<script src="/template/views/dashboard/assets/js/config.js"></script>
        <script src="/template/views/dashboard/demo.js"></script>

<!-- Vendor css -->
<link href="/template/views/dashboard/assets/css/vendors.min.css" rel="stylesheet" type="text/css">

<!-- App css -->
<link id="app-style" href="/template/views/dashboard/assets/css/app.min.css" rel="stylesheet" type="text/css">

    </head>

    <body>
