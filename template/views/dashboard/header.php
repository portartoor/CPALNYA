<?
$section = $routes['routes'][1] ?? '';
$subroute = $routes['routes'][2] ?? '';
$isAuthPage = (($section === 'dashboard' || $section === 'adminpanel') && $subroute === 'auth');

if ($isAuthPage) {
	include (DIR.'/template/views/dashboard_auth/header.php');
}
else {
$FRMWRK = new FRMWRK();
$dashboardNotifyLib = DIR . '/core/libs/dashboard_notifications.php';
if (file_exists($dashboardNotifyLib)) {
    require_once $dashboardNotifyLib;
}
$dashboardAvatarLib = DIR . '/core/libs/dashboard_avatar.php';
if (file_exists($dashboardAvatarLib)) {
    require_once $dashboardAvatarLib;
}
$currentEmail = '';
$currentAvatarUrl = '/template/views/dashboard/assets/images/users/user-1.jpg';
$logoutUrl = '/dashboard/logout/';
$isAdminPanel = ($section === 'adminpanel');
$supportUrl = function_exists('dashboard_support_link') ? dashboard_support_link() : '#';
$supportMetaAuthor = $supportUrl;
$brand = function_exists('dashboard_branding') ? dashboard_branding() : ['title' => 'CODERS', 'sub' => 'Blog and Portfolio', 'is_apigeo' => false];
$brandTitleText = (string)($brand['title'] ?? 'CODERS');
$isApiGeoRuBrand = (bool)($brand['is_apigeo_ru'] ?? false);
$brandWordmarkTld = $isApiGeoRuBrand ? '.RU' : '.ONLINE';
$brandHostRaw = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'coders.local'));
$brandHostRaw = preg_replace('/:\d+$/', '', $brandHostRaw);
$brandHostRaw = preg_replace('/^www\./', '', $brandHostRaw);
$adminDomainBrand = strtoupper((string)$brandHostRaw);
$adminDomainBadge = substr($adminDomainBrand, 0, 1) !== '' ? substr($adminDomainBrand, 0, 1) : 'C';
$isRuUi = $isApiGeoRuBrand;
$notifications = ['rows' => [], 'unread' => 0];
$subStatus = ['active' => false, 'label' => 'Inactive'];
$settingsUrl = $isAdminPanel ? '/adminpanel/' : '/dashboard/settings/';
$paymentsUrl = '/dashboard/payments/';
$pageMap = $isRuUi
    ? [
        'main' => 'РћР±Р·РѕСЂ',
        'docs' => 'Р”РѕРєСѓРјРµРЅС‚Р°С†РёСЏ API',
        'subscribe' => 'РџРѕРґРїРёСЃРєР°',
        'usage' => 'РСЃРїРѕР»СЊР·РѕРІР°РЅРёРµ',
        'examples' => 'РџСЂРёРјРµСЂС‹',
        'tools' => 'РРЅСЃС‚СЂСѓРјРµРЅС‚С‹',
        'settings' => 'РќР°СЃС‚СЂРѕР№РєРё',
        'payments' => 'РџР»Р°С‚РµР¶Рё',
        'cache' => 'Cache',
        'auth' => 'РђРІС‚РѕСЂРёР·Р°С†РёСЏ'
    ]
    : [
        'main' => 'Overview',
        'docs' => 'API Documentation',
        'subscribe' => 'Subscription',
        'usage' => 'Usage',
        'examples' => 'Examples',
        'tools' => 'Tools',
        'settings' => 'Settings',
        'payments' => 'Payments',
        'cache' => 'Cache',
        'auth' => 'Authentication'
    ];
$subrouteKey = strtolower((string)$subroute);
$pageSuffix = $pageMap[$subrouteKey] ?? 'Dashboard';
$dynamicTitle = ($isAdminPanel ? ($isRuUi ? 'РђРґРјРёРЅ-РїР°РЅРµР»СЊ' : 'Admin Panel') : $pageSuffix) . ' | ' . (string)($brand['title'] ?? 'CODERS');
if ($brandHostRaw !== '' && stripos($dynamicTitle, $brandHostRaw) === false) {
    $dynamicTitle .= ' | ' . strtoupper($brandHostRaw);
}

if ($isAdminPanel) {
	$logoutUrl = '/adminpanel/logout/';
	$token = $_COOKIE['adminpanel_token'] ?? null;
	if ($token) {
		$DB = $FRMWRK->DB();
		$tokenSafe = mysqli_real_escape_string($DB, $token);
		$rows = $FRMWRK->DBRecords("SELECT email FROM adminpanel_users WHERE token = '$tokenSafe' AND token_expires > NOW() LIMIT 1");
		if (!empty($rows)) {
			$currentEmail = $rows[0]['email'];
		}
	}
} else {
	$user = $FRMWRK->GetCurrentUser();
	$currentEmail = $user['email'] ?? '';
    $userId = (int)($user['id'] ?? 0);
    if ($userId > 0 && function_exists('dashboard_avatar_ensure_for_user')) {
        $currentAvatarUrl = dashboard_avatar_ensure_for_user($FRMWRK, $userId, (string)$currentEmail);
    }
    if ($userId > 0 && function_exists('dashboard_notification_mark_read') && isset($_GET['notify_read'])) {
        dashboard_notification_mark_read($FRMWRK, $userId, (int)$_GET['notify_read']);
        $cleanUrl = strtok((string)($_SERVER['REQUEST_URI'] ?? '/dashboard/'), '?');
        header('Location: ' . $cleanUrl);
        exit;
    }
    if ($userId > 0 && function_exists('dashboard_notification_mark_all_read') && isset($_GET['notify_read_all'])) {
        dashboard_notification_mark_all_read($FRMWRK, $userId);
        $cleanUrl = strtok((string)($_SERVER['REQUEST_URI'] ?? '/dashboard/'), '?');
        header('Location: ' . $cleanUrl);
        exit;
    }
    if ($userId > 0 && function_exists('dashboard_notification_fetch')) {
        $notifications = dashboard_notification_fetch($FRMWRK, $userId, 10);
    }
    if ($userId > 0 && function_exists('dashboard_subscription_status')) {
        $subStatus = dashboard_subscription_status($FRMWRK, $userId);
    }
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= htmlspecialchars($dynamicTitle) ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="<?= htmlspecialchars((string)($brand['title'] ?? 'CODERS') . ' admin dashboard.') ?>">
        <meta name="keywords" content="dashboard, analytics, admin panel, site management">
        <meta name="author" content="<?= htmlspecialchars((string)$supportMetaAuthor) ?>">

        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">


        <!-- Vector Maps css -->
        <link href="/template/views/dashboard/assets/plugins/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

        <!-- Theme Config Js -->
        <script src="/template/views/dashboard/assets/js/config.js"></script>
        <script src="/template/views/dashboard/demo.js"></script>

        <!-- Vendor css -->
        <link href="/template/views/dashboard/assets/css/vendors.min.css" rel="stylesheet" type="text/css">

        <!-- App css -->
        <link id="app-style" href="/template/views/dashboard/assets/css/app.min.css" rel="stylesheet" type="text/css">
        <style>
            .landing-logo {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
                line-height: 1;
            }
            .landing-logo .logo-mark,
            .sidenav-menu .sidebar-brand .logo-mark {
                display: inline-block;
                flex: 0 0 auto;
                position: relative;
                width: 62px;
                height: 62px;
                border-radius: 16px;
                background: linear-gradient(160deg, rgba(5, 18, 34, .98), rgba(9, 28, 52, .9));
                border: 1px solid rgba(170, 234, 255, 0.65);
                box-shadow:
                    0 0 0 1px rgba(160, 240, 255, 0.24),
                    0 0 34px rgba(54, 210, 255, 0.35),
                    0 22px 36px rgba(5, 17, 35, 0.76),
                    inset 0 1px 0 rgba(231, 248, 255, 0.36);
                overflow: hidden;
                will-change: transform, filter;
                animation: logoFloat 6.4s ease-in-out infinite;
            }
            .landing-logo .logo-mark::before,
            .sidenav-menu .sidebar-brand .logo-mark::before {
                content: "";
                position: absolute;
                inset: -20%;
                border-radius: 50%;
                background: conic-gradient(from 18deg, rgba(54,210,255,0), rgba(54,210,255,0.62), rgba(103,255,219,0.16), rgba(54,210,255,0));
                filter: blur(10px);
                opacity: 0.85;
                animation: logoHaloSpin 8.5s linear infinite;
            }
            .landing-logo .logo-scope,
            .sidenav-menu .sidebar-brand .logo-scope {
                position: absolute;
                inset: 3px;
                border-radius: 50%;
                border: 1px solid rgba(163, 232, 255, 0.38);
                z-index: 2;
                transform-style: preserve-3d;
                animation: logoScopeSpin 9s linear infinite;
            }
            .landing-logo .logo-scope::before,
            .landing-logo .logo-scope::after,
            .sidenav-menu .sidebar-brand .logo-scope::before,
            .sidenav-menu .sidebar-brand .logo-scope::after {
                content: "";
                position: absolute;
                inset: 7px;
                border-radius: 50%;
                border: 1px dashed rgba(103, 255, 219, 0.42);
                animation: logoScopeSpin 6.4s linear infinite reverse;
            }
            .landing-logo .logo-scope::after,
            .sidenav-menu .sidebar-brand .logo-scope::after {
                inset: 13px;
                border-style: solid;
                border-color: rgba(156, 231, 255, 0.52);
                animation-duration: 4.8s;
            }
            .landing-logo .logo-crosshair,
            .sidenav-menu .sidebar-brand .logo-crosshair {
                position: absolute;
                inset: 0;
                pointer-events: none;
                z-index: 3;
            }
            .landing-logo .logo-crosshair::before,
            .landing-logo .logo-crosshair::after,
            .sidenav-menu .sidebar-brand .logo-crosshair::before,
            .sidenav-menu .sidebar-brand .logo-crosshair::after {
                content: "";
                position: absolute;
                left: 50%;
                top: 50%;
                width: 48px;
                height: 1px;
                background: linear-gradient(90deg, rgba(156,231,255,0), rgba(156,231,255,.85), rgba(156,231,255,0));
                transform: translate(-50%, -50%);
                filter: drop-shadow(0 0 4px rgba(156,231,255,.5));
            }
            .landing-logo .logo-crosshair::after,
            .sidenav-menu .sidebar-brand .logo-crosshair::after {
                width: 1px;
                height: 48px;
                background: linear-gradient(180deg, rgba(156,231,255,0), rgba(156,231,255,.85), rgba(156,231,255,0));
            }
            .landing-logo .logo-globe,
            .sidenav-menu .sidebar-brand .logo-globe {
                position: absolute;
                left: 50%;
                top: 50%;
                width: 34px;
                height: 34px;
                transform: translate(-50%, -50%);
                border-radius: 50%;
                border: 1px solid rgba(188, 240, 255, 0.75);
                background: radial-gradient(circle at 30% 25%, rgba(178, 236, 255, 0.9) 0%, rgba(46, 130, 196, 0.72) 42%, rgba(15, 58, 106, 0.98) 100%);
                box-shadow:
                    inset -9px -10px 14px rgba(3, 15, 32, 0.74),
                    inset 9px 6px 12px rgba(163, 231, 255, 0.14),
                    0 0 18px rgba(54, 210, 255, 0.58);
                z-index: 4;
                will-change: filter;
                overflow: hidden;
            }
            .landing-logo .logo-globe::before,
            .landing-logo .logo-globe::after,
            .sidenav-menu .sidebar-brand .logo-globe::before,
            .sidenav-menu .sidebar-brand .logo-globe::after {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: 50%;
            }
            .landing-logo .logo-globe::before,
            .sidenav-menu .sidebar-brand .logo-globe::before {
                background: radial-gradient(circle at 76% 24%, rgba(238, 252, 255, 0.55), rgba(238, 252, 255, 0) 45%);
            }
            .landing-logo .logo-globe::after,
            .sidenav-menu .sidebar-brand .logo-globe::after {
                background: linear-gradient(90deg, rgba(2, 11, 23, 0.54), rgba(2, 11, 23, 0) 42%, rgba(221, 247, 255, 0.28) 100%);
                mix-blend-mode: screen;
            }
            .landing-logo .logo-globe-surface,
            .sidenav-menu .sidebar-brand .logo-globe-surface {
                position: absolute;
                inset: 0;
                border-radius: 50%;
                overflow: hidden;
                background: transparent;
                z-index: 5;
            }
            .landing-logo .logo-dot-sphere,
            .sidenav-menu .sidebar-brand .logo-dot-sphere {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                display: block;
                border-radius: 50%;
                pointer-events: none;
            }
            .landing-logo .logo-geo-point,
            .sidenav-menu .sidebar-brand .logo-geo-point {
                position: absolute;
                left: 31px;
                top: 31px;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: #fff2a7;
                box-shadow: 0 0 12px rgba(255, 242, 167, 0.95), 0 0 22px rgba(103,255,219,0.48);
                transform: translate(-50%, -50%);
                z-index: 9;
                pointer-events: none;
                animation: geoPointPulse 1.45s ease-out infinite;
            }
            .landing-logo .brand-title {
                font-size: 21px;
                font-weight: 900;
                letter-spacing: .38px;
                color: #9ce7ff;
                background: linear-gradient(90deg, #dff5ff 0%, #9ce7ff 38%, #67ffdb 72%, #dff5ff 100%);
                background-size: 210% auto;
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 0 18px rgba(156, 231, 255, 0.38), 0 0 34px rgba(54, 210, 255, 0.18);
                animation: brandGlow 5.5s linear infinite;
            }
            .landing-logo .brand-text {
                display: flex;
                flex-direction: column;
                line-height: 1.05;
            }
            .landing-logo .brand-sub {
                margin-top: 2px;
                font-size: 10px;
                letter-spacing: 1.2px;
                text-transform: uppercase;
                color: #8fb7df;
            }
            .sidenav-menu .sidebar-brand {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                line-height: 1;
                flex-wrap: nowrap;
            }
            .sidenav-menu > .logo {
                margin-top: <?php echo !empty($brand['is_apigeo']) ? '0' : '20px'; ?>;
            }
            .sidenav-menu .sidebar-brand .logo-mark {
                display: inline-block;
                flex: 0 0 auto;
            }
            .sidenav-menu .sidebar-brand .brand-text {
                display: flex;
                flex-direction: column;
                min-width: 0;
            }
            .sidenav-menu .sidebar-brand .brand-title {
                font-size: 16px;
                font-weight: 900;
                letter-spacing: .2px;
                color: #9ce7ff;
                background: linear-gradient(90deg, #dff5ff 0%, #9ce7ff 38%, #67ffdb 72%, #dff5ff 100%);
                background-size: 210% auto;
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 0 18px rgba(156, 231, 255, 0.38), 0 0 34px rgba(54, 210, 255, 0.18);
                animation: brandGlow 5.5s linear infinite;
            }
            .sidenav-menu .sidebar-brand .brand-sub {
                margin-top: 2px;
                font-size: 9px;
                letter-spacing: .75px;
                text-transform: uppercase;
                color: #8fb7df;
                opacity: .95;
            }
            .sidenav-menu .sidebar-brand .wordmark {
                margin: 0;
                line-height: 1;
                font-family: "Trebuchet MS","Segoe UI",Tahoma,sans-serif;
                font-weight: 800;
                font-size: 19px;
                letter-spacing: .02em;
                text-transform: uppercase;
            }
            .sidenav-menu .sidebar-brand .wordmark .api { color: #c63838; }
            .sidenav-menu .sidebar-brand .wordmark .geo { color: #0f314b; }
            .sidenav-menu .sidebar-brand .wordmark .ip { color: #1f608f; }
            .sidenav-menu .sidebar-brand .wordmark .tld {
                color: #5d7890;
                font-size: .56em;
                font-weight: 700;
                margin-left: 3px;
                letter-spacing: .10em;
            }
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .geo,
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .ip,
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .tld {
                color: #d2e9fb;
            }
            .sidenav-menu .sidebar-brand .logo-mark {
                width: 46px;
                height: 46px;
            }
            .sidenav-menu .sidebar-brand .logo-crosshair::before { width: 34px; }
            .sidenav-menu .sidebar-brand .logo-crosshair::after { height: 34px; }
            .sidenav-menu .sidebar-brand .logo-globe {
                width: 26px;
                height: 26px;
            }
            .sidenav-menu .sidebar-brand .logo-geo-point {
                left: 23px;
                top: 23px;
            }
            @keyframes logoHaloSpin {
                from { transform: rotate(0deg) scale(1); }
                50% { transform: rotate(180deg) scale(1.05); }
                to { transform: rotate(360deg) scale(1); }
            }
            @keyframes logoScopeSpin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            @keyframes logoFloat {
                0%, 100% { transform: translateY(0px) scale(1); }
                50% { transform: translateY(-2px) scale(1.01); }
            }
            @keyframes geoPointPulse {
                0% { box-shadow: 0 0 9px rgba(255, 242, 167, 0.9), 0 0 18px rgba(103,255,219,0.42); }
                50% { box-shadow: 0 0 14px rgba(255, 242, 167, 1), 0 0 28px rgba(103,255,219,0.58); }
                100% { box-shadow: 0 0 9px rgba(255, 242, 167, 0.9), 0 0 18px rgba(103,255,219,0.42); }
            }
            @keyframes brandGlow {
                0% { background-position: 0% 50%; }
                100% { background-position: 100% 50%; }
            }
            .apigeo-brand {
                display: inline-flex;
                align-items: flex-end;
                gap: 8px;
                line-height: 1;
                text-decoration: none;
            }
            .apigeo-brand .wordmark {
                margin: 0;
                line-height: 1;
                font-family: "Trebuchet MS","Segoe UI",Tahoma,sans-serif;
                font-weight: 800;
                font-size: 24px;
                letter-spacing: .02em;
                text-transform: uppercase;
                color: #163b58;
            }
            .apigeo-brand .wordmark .api { color: #c63838; }
            .apigeo-brand .wordmark .geo { color: #0f314b; }
            .apigeo-brand .wordmark .ip { color: #1f608f; }
            .apigeo-brand .wordmark .tld {
                color: #5d7890;
                font-size: .56em;
                font-weight: 700;
                margin-left: 4px;
                letter-spacing: .12em;
            }
            .apigeo-brand .brand-sub {
                margin-top: 2px;
                font-size: 10px;
                letter-spacing: .75px;
                text-transform: uppercase;
                opacity: .72;
            }
            .logo-dark .apigeo-brand .wordmark .geo { color: #0f314b; }
            .logo-dark .apigeo-brand .wordmark .ip { color: #1f608f; }
            .logo-dark .apigeo-brand .wordmark .tld { color: #5d7890; }
            .domain-brand-silver {
                display: inline-flex;
                align-items: baseline;
                gap: 8px;
                color: #d7dce3;
                text-transform: uppercase;
                letter-spacing: .08em;
                font-weight: 700;
                text-shadow: 0 0 10px rgba(195, 203, 216, 0.25);
            }
            .domain-brand-silver .domain-main {
                font-size: 20px;
                color: #d7dce3;
            }
            .domain-brand-silver .domain-sub {
                font-size: 10px;
                color: #a9b2bf;
            }
            .domain-badge-silver {
                color: #d7dce3 !important;
                border: 1px solid rgba(185, 193, 206, 0.5);
                background: rgba(175, 185, 198, 0.12);
                border-radius: 6px;
                padding: 1px 7px;
            }
            <?php if ($isApiGeoRuBrand): ?>
            .sidenav-menu .sidebar-brand .wordmark .api { color: #f3f8ff; }
            .sidenav-menu .sidebar-brand .wordmark .geo { color: #d9ebff; }
            .sidenav-menu .sidebar-brand .wordmark .ip { color: #c6e2ff; }
            .sidenav-menu .sidebar-brand .wordmark .tld { color: #adc9e8; }
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .geo,
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .ip,
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .tld,
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .api {
                color: #d9ebff;
            }
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .api { color: #f3f8ff; }
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .ip { color: #c6e2ff; }
            .sidenav-menu .logo-dark .sidebar-brand .wordmark .tld { color: #adc9e8; }
            .apigeo-brand .wordmark { color: #eef6ff; }
            .apigeo-brand .wordmark .api { color: #f3f8ff; }
            .apigeo-brand .wordmark .geo { color: #d9ebff; }
            .apigeo-brand .wordmark .ip { color: #c6e2ff; }
            .apigeo-brand .wordmark .tld { color: #adc9e8; }
            .logo-dark .apigeo-brand .wordmark .api { color: #f3f8ff; }
            .logo-dark .apigeo-brand .wordmark .geo { color: #d9ebff; }
            .logo-dark .apigeo-brand .wordmark .ip { color: #c6e2ff; }
            .logo-dark .apigeo-brand .wordmark .tld { color: #adc9e8; }
            <?php endif; ?>
        </style>
        <script>
            (function () {
                document.documentElement.setAttribute('data-sidenav-size', 'default');
                try {
                    var cfgRaw = sessionStorage.getItem('__THEME_CONFIG__');
                    if (!cfgRaw) return;
                    var cfg = JSON.parse(cfgRaw);
                    cfg['sidenav-size'] = 'default';
                    sessionStorage.setItem('__THEME_CONFIG__', JSON.stringify(cfg));
                } catch (e) {}
            })();
        </script>

    </head>

    <body>
        <!-- Begin page -->
        <div class="wrapper">
            <header class="app-topbar">
                <div class="container-fluid topbar-menu">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Topbar Brand Logo -->
                        <div class="logo-topbar">
                            <?php if ($isAdminPanel): ?>
                                <a href="/" class="logo-light">
                                    <span class="logo-lg">
                                        <span class="domain-brand-silver">
                                            <span class="domain-main"><?= htmlspecialchars($adminDomainBrand) ?></span>
                                            <span class="domain-sub">ADMIN</span>
                                        </span>
                                    </span>
                                    <span class="logo-sm">
                                        <span class="domain-badge-silver"><?= htmlspecialchars($adminDomainBadge) ?></span>
                                    </span>
                                </a>
                                <a href="/" class="logo-dark">
                                    <span class="logo-lg">
                                        <span class="domain-brand-silver">
                                            <span class="domain-main"><?= htmlspecialchars($adminDomainBrand) ?></span>
                                            <span class="domain-sub">ADMIN</span>
                                        </span>
                                    </span>
                                    <span class="logo-sm">
                                        <span class="domain-badge-silver"><?= htmlspecialchars($adminDomainBadge) ?></span>
                                    </span>
                                </a>
                            <?php elseif (!empty($brand['is_apigeo'])): ?>
                                <a href="/" class="logo-light">
                                    <span class="logo-lg">
                                        <span class="apigeo-brand">
                                            <span>
                                                <span class="wordmark"><span class="api">API</span><span class="geo">Geo</span><span class="ip">IP</span><span class="tld"><?= htmlspecialchars($brandWordmarkTld) ?></span></span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="logo-sm">
                                        <span class="fw-bold text-dark">A</span>
                                    </span>
                                </a>
                                <a href="/" class="logo-dark">
                                    <span class="logo-lg">
                                        <span class="apigeo-brand">
                                            <span>
                                                <span class="wordmark"><span class="api">API</span><span class="geo">Geo</span><span class="ip">IP</span><span class="tld"><?= htmlspecialchars($brandWordmarkTld) ?></span></span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="logo-sm">
                                        <span class="fw-bold text-white">A</span>
                                    </span>
                                </a>
                <?php else: ?>
                                <a href="/" class="landing-logo brand-wrap logo-light" aria-label="Site animated logo">
                                    <span class="logo-mark">
                                        <span class="logo-scope"></span>
                                        <span class="logo-crosshair"></span>
                                        <span class="logo-globe" style="transform: translate(-50%, -50%);"><span class="logo-globe-surface"><canvas class="logo-dot-sphere"></canvas></span></span>
                                        <span class="logo-geo-point"></span>
                                    </span>
                                    <span class="brand-text">
                                        <span class="brand-title"><?= htmlspecialchars($brandTitleText) ?></span>
                                    </span>
                                </a>
                                <a href="/" class="landing-logo brand-wrap logo-dark" aria-label="Site animated logo">
                                    <span class="logo-mark">
                                        <span class="logo-scope"></span>
                                        <span class="logo-crosshair"></span>
                                        <span class="logo-globe" style="transform: translate(-50%, -50%);"><span class="logo-globe-surface"><canvas class="logo-dot-sphere"></canvas></span></span>
                                        <span class="logo-geo-point"></span>
                                    </span>
                                    <span class="brand-text">
                                        <span class="brand-title"><?= htmlspecialchars($brandTitleText) ?></span>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Sidebar Menu Toggle Button -->
                        <button class="sidenav-toggle-button btn btn-primary btn-icon">
                            <i class="ti ti-menu-4"></i>
                        </button>

                        <!-- Horizontal Menu Toggle Button -->
                        <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu">
                            <i class="ti ti-menu-4"></i>
                        </button>
                        <?php if (!$isAdminPanel): ?>
                        <div class="ms-2 d-none d-md-flex align-items-center">
                            <?php if (!empty($subStatus['active'])): ?>
                                <span class="badge bg-success"><?= htmlspecialchars((string)$subStatus['label']) ?></span>
                <?php else: ?>
                                <span class="badge bg-secondary"><?= $isRuUi ? 'РџРѕРґРїРёСЃРєР° РЅРµР°РєС‚РёРІРЅР°' : 'Subscription inactive' ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div id="theme-dropdown" class="topbar-item d-none d-sm-flex">
                            <div class="dropdown">
                                <button class="topbar-link" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                                    <i class="ti ti-sun topbar-link-icon d-none" id="theme-icon-light"></i>
                                    <i class="ti ti-moon topbar-link-icon d-none" id="theme-icon-dark"></i>
                                    <i class="ti ti-sun-moon topbar-link-icon d-none" id="theme-icon-system"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" data-thememode="dropdown">
                                    <label class="dropdown-item cursor-pointer">
                                        <input class="form-check-input" type="radio" name="data-bs-theme" value="light" style="display: none">
                                        <i class="ti ti-sun align-middle me-1 fs-16"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'РЎРІРµС‚Р»Р°СЏ' : 'Light' ?></span>
                                    </label>
                                    <label class="dropdown-item cursor-pointer">
                                        <input class="form-check-input" type="radio" name="data-bs-theme" value="dark" style="display: none">
                                        <i class="ti ti-moon align-middle me-1 fs-16"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'РўРµРјРЅР°СЏ' : 'Dark' ?></span>
                                    </label>
                                    <label class="dropdown-item cursor-pointer">
                                        <input class="form-check-input" type="radio" name="data-bs-theme" value="system" style="display: none">
                                        <i class="ti ti-sun-moon align-middle me-1 fs-16"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'РЎРёСЃС‚РµРјРЅР°СЏ' : 'System' ?></span>
                                    </label>
                                </div>
                                <!-- end dropdown-menu-->
                            </div>
                            <!-- end dropdown-->
                        </div>


                        <div id="fullscreen-toggler" class="topbar-item d-none d-md-flex">
                            <button class="topbar-link" type="button" data-toggle="fullscreen">
                                <i class="ti ti-maximize topbar-link-icon"></i>
                                <i class="ti ti-minimize topbar-link-icon d-none"></i>
                            </button>
                        </div>

                        <div id="monochrome-toggler" class="topbar-item d-none d-xl-flex">
                            <button id="monochrome-mode" class="topbar-link" type="button" data-toggle="monochrome">
                                <i class="ti ti-palette topbar-link-icon"></i>
                            </button>
                        </div>

                        <!--<div id="language-selector-rounded" class="topbar-item">
                            <div class="dropdown">
                                <button class="topbar-link fw-bold" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                                    <img src="/template/views/dashboard/assets/images/flags/us.svg" alt="user-image" class="rounded-circle me-2" height="18" id="selected-language-image">
                                    <span id="selected-language-code">EN</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="en" title="English">
                                        <img src="/template/views/dashboard/assets/images/flags/us.svg" alt="English" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">English</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="de" title="German">
                                        <img src="/template/views/dashboard/assets/images/flags/de.svg" alt="German" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">Deutsch</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="it" title="Italian">
                                        <img src="/template/views/dashboard/assets/images/flags/it.svg" alt="Italian" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">Italiano</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="es" title="Spanish">
                                        <img src="/template/views/dashboard/assets/images/flags/es.svg" alt="Spanish" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">EspaГ±ol</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="ru" title="Russian">
                                        <img src="/template/views/dashboard/assets/images/flags/ru.svg" alt="Russian" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">Р СѓСЃСЃРєРёР№</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="hi" title="Hindi">
                                        <img src="/template/views/dashboard/assets/images/flags/in.svg" alt="Hindi" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">а¤№а¤їа¤ЁаҐЌа¤¦аҐЂ</span>
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item" data-translator-lang="ar" title="Arabic">
                                        <img src="/template/views/dashboard/assets/images/flags/sa.svg" alt="Arabic" class="me-1 rounded-circle" height="18" data-translator-image="">
                                        <span class="align-middle">Ш№Ш±ШЁЩЉ</span>
                                    </a>
                                </div>
                                
                            </div>
                           
                        </div>-->

                        <?php if (!$isAdminPanel): ?>
                        <div id="notification-dropdown-people" class="topbar-item">
                            <div class="dropdown">
                                <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
                                    <i class="ti ti-bell topbar-link-icon animate-ring"></i>
                                    <?php if ((int)($notifications['unread'] ?? 0) > 0): ?>
                                        <span class="badge text-bg-danger badge-circle topbar-badge"><?= (int)$notifications['unread'] ?></span>
                                    <?php endif; ?>
                                </button>

                                <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 fs-md fw-semibold"><?= $isRuUi ? 'РЈРІРµРґРѕРјР»РµРЅРёСЏ' : 'Notifications' ?></h6>
                                        <small class="text-muted"><?= (int)($notifications['unread'] ?? 0) ?> unread</small>
                                    </div>
                                    <div style="max-height: 300px" data-simplebar="">
                                        <?php if (!empty($notifications['rows'])): ?>
                                            <?php foreach ($notifications['rows'] as $n): ?>
                                                <?php
                                                    $read = ((int)($n['is_read'] ?? 0) === 1);
                                                    $targetUrl = '/dashboard/?notify_read=' . (int)$n['id'];
                                                    if (!empty($n['link_url'])) {
                                                        $sep = (strpos((string)$n['link_url'], '?') !== false) ? '&' : '?';
                                                        $targetUrl = (string)$n['link_url'] . $sep . 'notify_read=' . (int)$n['id'];
                                                    }
                                                ?>
                                                <a class="dropdown-item notification-item py-2 text-wrap <?= $read ? 'opacity-75' : '' ?>" href="<?= htmlspecialchars($targetUrl) ?>">
                                                    <span class="d-flex align-items-center gap-3">
                                                        <span class="avatar-md rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                            <i class="ti ti-bell fs-5"></i>
                                                        </span>
                                                        <span class="flex-grow-1 text-muted">
                                                            <span class="fw-medium text-body"><?= htmlspecialchars((string)($n['title'] ?? 'Notification')) ?></span><br>
                                                            <?= htmlspecialchars((string)($n['message'] ?? '')) ?><br>
                                                            <span class="fs-xs"><?= htmlspecialchars((string)($n['created_at'] ?? '')) ?></span>
                                                        </span>
                                                    </span>
                                                </a>
                                            <?php endforeach; ?>
                <?php else: ?>
                                            <div class="dropdown-item text-muted py-3"><?= $isRuUi ? 'РЈРІРµРґРѕРјР»РµРЅРёР№ РїРѕРєР° РЅРµС‚' : 'No notifications' ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/dashboard/?notify_read_all=1" class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2"><?= $isRuUi ? 'РџСЂРѕС‡РёС‚Р°С‚СЊ РІСЃРµ' : 'Read all messages' ?></a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div id="user-dropdown-detailed" class="topbar-item nav-user">
                            <div class="dropdown">
                                <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" href="#!" aria-haspopup="false" aria-expanded="false">
                                    <img src="<?= htmlspecialchars((string)$currentAvatarUrl) ?>" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                                    <div class="d-lg-flex align-items-center gap-1 d-none">
                                        <span>
                                            <h5 class="my-0 lh-1 pro-username"><?=$currentEmail;?></h5>
                                        </span>
                                        <i class="ti ti-chevron-down align-middle"></i>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- Header -->
                                    <div class="dropdown-header noti-title">
                                        <h6 class="text-overflow m-0"><?= $isRuUi ? 'РЎ РІРѕР·РІСЂР°С‰РµРЅРёРµРј рџ‘‹!' : 'Welcome back рџ‘‹!' ?></h6>
                                    </div>
                                    <!-- Settings -->
                                    <a href="<?= htmlspecialchars((string)$settingsUrl) ?>" class="dropdown-item">
                                        <i class="ti ti-settings-2 me-1 fs-lg align-middle"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'РќР°СЃС‚СЂРѕР№РєРё' : 'Settings' ?></span>
                                    </a>
                                    <?php if (!$isAdminPanel): ?>
                                    <a href="<?= htmlspecialchars((string)$paymentsUrl) ?>" class="dropdown-item">
                                        <i class="ti ti-credit-card me-1 fs-lg align-middle"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'РџР»Р°С‚РµР¶Рё' : 'Payments' ?></span>
                                    </a>
                                    <?php endif; ?>

                                    <!-- Support -->
                                    <a href="<?= htmlspecialchars((string)$supportUrl) ?>" target="_blank" rel="noopener" class="dropdown-item">
                                        <i class="ti ti-headset me-1 fs-lg align-middle"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'РџРѕРґРґРµСЂР¶РєР°' : 'Support' ?></span>
                                    </a>

                                    <!-- Divider -->
                                    <div class="dropdown-divider"></div>
                                    <!-- Logout -->
                                    <a href="<?=$logoutUrl;?>" class="dropdown-item fw-semibold">
                                        <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                                        <span class="align-middle"><?= $isRuUi ? 'Р’С‹Р№С‚Рё' : 'Log Out' ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Topbar End -->
 <div class="sidenav-menu">
    <!-- Brand Logo -->
    <a href="/" class="logo">
        <?php if ($isAdminPanel): ?>
            <span class="logo logo-light">
                <span class="logo-lg">
                    <span class="sidebar-brand">
                        <span class="domain-brand-silver">
                            <span class="domain-main"><?= htmlspecialchars($adminDomainBrand) ?></span>
                            <span class="domain-sub">ADMIN</span>
                        </span>
                    </span>
                </span>
                <span class="logo-sm"><span class="domain-badge-silver"><?= htmlspecialchars($adminDomainBadge) ?></span></span>
            </span>
            <span class="logo logo-dark">
                <span class="logo-lg">
                    <span class="sidebar-brand">
                        <span class="domain-brand-silver">
                            <span class="domain-main"><?= htmlspecialchars($adminDomainBrand) ?></span>
                            <span class="domain-sub">ADMIN</span>
                        </span>
                    </span>
                </span>
                <span class="logo-sm"><span class="domain-badge-silver"><?= htmlspecialchars($adminDomainBadge) ?></span></span>
            </span>
        <?php elseif (!empty($brand['is_apigeo'])): ?>
            <span class="logo logo-light">
                <span class="logo-lg">
                    <span class="sidebar-brand">
                        <span>
                            <span class="wordmark"><span class="api">API</span><span class="geo">Geo</span><span class="ip">IP</span><span class="tld"><?= htmlspecialchars($brandWordmarkTld) ?></span></span>
                        </span>
                    </span>
                </span>
                <span class="logo-sm"><span class="fw-bold text-dark">A</span></span>
            </span>
            <span class="logo logo-dark">
                <span class="logo-lg">
                    <span class="sidebar-brand">
                        <span>
                            <span class="wordmark"><span class="api">API</span><span class="geo">Geo</span><span class="ip">IP</span><span class="tld"><?= htmlspecialchars($brandWordmarkTld) ?></span></span>
                        </span>
                    </span>
                </span>
                <span class="logo-sm"><span class="fw-bold text-white">A</span></span>
            </span>
                <?php else: ?>
            <span class="logo logo-light">
                <span class="logo-lg">
                    <span class="sidebar-brand">
                        <span class="logo-mark">
                            <span class="logo-scope"></span>
                            <span class="logo-crosshair"></span>
                            <span class="logo-globe" style="transform: translate(-50%, -50%);"><span class="logo-globe-surface"><canvas class="logo-dot-sphere"></canvas></span></span>
                            <span class="logo-geo-point"></span>
                        </span>
                        <span class="brand-text">
                            <span class="brand-title"><?= htmlspecialchars($brandTitleText) ?></span>
                        </span>
                    </span>
                </span>
                <span class="logo-sm"><span class="fw-bold text-dark">G</span></span>
            </span>
            <span class="logo logo-dark">
                <span class="logo-lg">
                    <span class="sidebar-brand">
                        <span class="logo-mark">
                            <span class="logo-scope"></span>
                            <span class="logo-crosshair"></span>
                            <span class="logo-globe" style="transform: translate(-50%, -50%);"><span class="logo-globe-surface"><canvas class="logo-dot-sphere"></canvas></span></span>
                            <span class="logo-geo-point"></span>
                        </span>
                        <span class="brand-text">
                            <span class="brand-title"><?= htmlspecialchars($brandTitleText) ?></span>
                        </span>
                    </span>
                </span>
                <span class="logo-sm"><span class="fw-bold text-white">G</span></span>
            </span>
        <?php endif; ?>
    </a>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-offcanvas">
        <i class="ti ti-menu-4 align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar="">
        <!--- Sidenav Menu -->
        <div id="sidenav-menu">
            <ul class="side-nav">
                <?php if ($isAdminPanel): ?>
					<li class="side-nav-item">
						<a href="/adminpanel/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'РђРґРјРёРЅ-РїР°РЅРµР»СЊ' : 'Admin Dashboard' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/domains/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-world"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Р”РѕРјРµРЅС‹' : 'Domains' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/templates/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-layout-grid"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'РЁР°Р±Р»РѕРЅС‹' : 'Templates' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/routes/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-route"></i></span>
							<span class="menu-text">Routes</span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/examples/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-notebook"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Все материалы' : 'All Materials' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/journal/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-news"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Журнал' : 'Journal' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/playbooks/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-book-2"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'HowTo / Playbooks' : 'HowTo / Playbooks' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/signals/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-radio"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Повестка / Signals' : 'Signals' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/fun/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-mask"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Отдых / Фан' : 'Fun' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/seo-generator/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-wand"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'SEO РіРµРЅРµСЂР°С‚РѕСЂ' : 'SEO Generator' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/seo-generator-logs/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-file-analytics"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'SEO Р»РѕРіРё' : 'SEO Logs' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/adminpanel/threats/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-shield-exclamation"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'РџСЂР°РІРёР»Р° СѓРіСЂРѕР·' : 'Threat Rules' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
	<a href="/adminpanel/contacts/" class="side-nav-link">
		<span class="menu-icon"><i class="ti ti-mail-forward"></i></span>
		<span class="menu-text"><?= $isRuUi ? '���������' : 'Contact Requests' ?></span>
	</a>
</li>
<li class="side-nav-item">
	<a href="/adminpanel/site-checks/" class="side-nav-link">
		<span class="menu-icon"><i class="ti ti-world-search"></i></span>
		<span class="menu-text"><?= $isRuUi ? '�������� ������' : 'Site Checks' ?></span>
	</a>
</li>
<li class="side-nav-item">
	<a href="/adminpanel/cache/" class="side-nav-link">
		<span class="menu-icon"><i class="ti ti-database-cog"></i></span>
		<span class="menu-text"><?= $isRuUi ? '��� HTML' : 'HTML Cache' ?></span>
	</a>
</li>
                <?php else: ?>
					<li class="side-nav-item">
						<a href="/dashboard/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Р”Р°С€Р±РѕСЂРґ' : 'Dashboard' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/dashboard/docs/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-book-2"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'Р”РѕРєСѓРјРµРЅС‚Р°С†РёСЏ' : 'Documentation' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/dashboard/usage/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-chart-bar"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'РСЃРїРѕР»СЊР·РѕРІР°РЅРёРµ' : 'Usage' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
						<a href="/dashboard/subscribe/" class="side-nav-link">
							<span class="menu-icon"><i class="ti ti-credit-card"></i></span>
							<span class="menu-text"><?= $isRuUi ? 'РџРѕРґРїРёСЃРєР°' : 'Subscription' ?></span>
						</a>
					</li>
					<li class="side-nav-item">
                        <a href="<?= htmlspecialchars((string)$supportUrl) ?>" target="_blank" rel="noopener" class="side-nav-link">
                            <span class="menu-icon"><i class="ti ti-headset"></i></span>
                            <span class="menu-text"><?= $isRuUi ? 'РџРѕРґРґРµСЂР¶РєР°' : 'Support' ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                
            </ul>
        </div>
    </div>
</div>
<!-- Sidenav Menu End -->

<?php if (empty($brand['is_apigeo'])): ?>
<script>
(function () {
    const logoMarks = Array.from(document.querySelectorAll('.landing-logo .logo-mark, .sidebar-brand .logo-mark'));
    if (!logoMarks.length) return;

    function buildPoints(count, radius) {
        const pts = [];
        for (let i = 0; i < count; i++) {
            const phi = Math.acos(1 - (2 * (i + 0.5)) / count);
            const theta = Math.PI * (1 + Math.sqrt(5)) * (i + 0.5);
            pts.push({
                x: radius * Math.sin(phi) * Math.cos(theta),
                y: radius * Math.cos(phi),
                z: radius * Math.sin(phi) * Math.sin(theta)
            });
        }
        return pts;
    }

    const states = logoMarks.map((mark) => {
        const globeSurface = mark.querySelector('.logo-globe-surface');
        const point = mark.querySelector('.logo-geo-point');
        const canvas = globeSurface ? globeSurface.querySelector('.logo-dot-sphere') : null;
        const ctx = canvas ? canvas.getContext('2d') : null;
        return {
            mark, point, globeSurface, canvas, ctx,
            pts: [],
            radius: 10,
            w: 34,
            h: 34,
            rotX: 0.22,
            rotY: 0
        };
    }).filter((s) => s.canvas && s.ctx && s.point && s.globeSurface);

    if (!states.length) return;

    function resizeState(s) {
        const rect = s.globeSurface.getBoundingClientRect();
        const w = Math.max(20, Math.round(rect.width || 34));
        const h = Math.max(20, Math.round(rect.height || 34));
        const dpr = Math.min(2, window.devicePixelRatio || 1);
        s.canvas.width = Math.round(w * dpr);
        s.canvas.height = Math.round(h * dpr);
        s.canvas.style.width = w + 'px';
        s.canvas.style.height = h + 'px';
        s.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        s.w = w;
        s.h = h;
        s.radius = Math.max(7, Math.min(w, h) * 0.36);
        s.pts = buildPoints(84, s.radius);
    }

    function renderSphere(s) {
        const fov = s.radius * 3.4;
        const cx = s.w / 2;
        const cy = s.h / 2;
        const cosX = Math.cos(s.rotX), sinX = Math.sin(s.rotX);
        const cosY = Math.cos(s.rotY), sinY = Math.sin(s.rotY);
        const projected = [];

        for (let i = 0; i < s.pts.length; i++) {
            const p = s.pts[i];
            const y1 = p.y * cosX - p.z * sinX;
            const z1 = p.y * sinX + p.z * cosX;
            const x2 = p.x * cosY + z1 * sinY;
            const z2 = -p.x * sinY + z1 * cosY;
            const perspective = fov / (fov - z2);
            const sx = x2 * perspective + cx;
            const sy = y1 * perspective + cy;
            const alpha = 0.2 + ((z2 + s.radius) / (2 * s.radius)) * 0.7;
            const r = Math.max(0.45, 0.42 + perspective * 0.56);
            projected.push({ x: sx, y: sy, z: z2, a: Math.max(0.15, Math.min(0.9, alpha)), r: r });
        }

        projected.sort((a, b) => a.z - b.z);
        s.ctx.clearRect(0, 0, s.w, s.h);
        for (let i = 0; i < projected.length; i++) {
            const p = projected[i];
            s.ctx.fillStyle = 'rgba(128, 212, 245,' + p.a.toFixed(3) + ')';
            s.ctx.beginPath();
            s.ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            s.ctx.fill();
        }
    }

    function tick(now) {
        const phase = now * 0.0022;
        for (let i = 0; i < states.length; i++) {
            const s = states[i];
            s.rotY += 0.012;
            renderSphere(s);

            const size = s.mark.clientWidth || 62;
            const center = size / 2;
            const ampX = size * 0.25;
            const ampY = size * 0.15;
            const t = Math.sin(phase + i * 0.3);
            const x = center + ampX * t;
            const y = center - (1 - t * t) * ampY + Math.sin((phase + i * 0.3) * 1.15) * (size * 0.03);
            const depth = Math.cos(phase + i * 0.3);
            const opacity = 0.3 + Math.max(0, depth) * 0.9;
            const scale = 0.78 + Math.max(0, depth) * 0.5;
            s.point.style.left = x.toFixed(2) + 'px';
            s.point.style.top = y.toFixed(2) + 'px';
            s.point.style.opacity = opacity.toFixed(3);
            s.point.style.transform = 'translate(-50%, -50%) scale(' + scale.toFixed(3) + ')';
        }
        requestAnimationFrame(tick);
    }

    function onResize() {
        for (let i = 0; i < states.length; i++) resizeState(states[i]);
    }

    onResize();
    window.addEventListener('resize', onResize);
    requestAnimationFrame(tick);
})();
</script>
<?php endif; ?>
            <!-- ============================================================== -->
            <!-- Start Main Content -->
            <!-- ============================================================== -->

            <div class="content-page">
               <?
}
?>






