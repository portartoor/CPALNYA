<?php
$currentPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$currentPath = is_string($currentPath) && $currentPath !== '' ? $currentPath : '/';
if ($currentPath !== '/' && substr($currentPath, -1) !== '/') {
    $currentPath .= '/';
}

$items = [['title' => 'Home', 'path' => '/']];
$mirrorRoutesLib = DIR . '/core/libs/mirror_routes.php';
$frmwrkLib = DIR . '/core/libs/frmwrk/frmwrk.php';
if (is_file($mirrorRoutesLib) && is_file($frmwrkLib)) {
    require_once $mirrorRoutesLib;
    require_once $frmwrkLib;
    if (class_exists('FRMWRK') && function_exists('mirror_routes_nav_items')) {
        $FRMWRK = new FRMWRK();
        $items = mirror_routes_nav_items($FRMWRK, true);
    }
}

$items = array_values(array_filter($items, static function (array $navItem): bool {
    $navPath = (string)($navItem['path'] ?? '');
    return !in_array($navPath, ['/cases/', '/cases', '/offers/', '/offers'], true);
}));

$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$isRu = (bool)preg_match('/\.ru$/', $host);
$titleByPathRu = [
    '/' => 'Главная',
    '/blog/' => 'Блог',
    '/services/' => 'Услуги',
    '/projects/' => 'Продукты',
    '/contact/' => 'Контакты',
    '/audit/' => 'Аудит',
];
$titleByPathEn = [
    '/' => 'Home',
    '/blog/' => 'Blog',
    '/services/' => 'Services',
    '/projects/' => 'Products',
    '/contact/' => 'Contact',
    '/audit/' => 'Audit',
];

foreach ($items as $item):
    $path = (string)($item['path'] ?? '/');
    if ($path === '') {
        $path = '/';
    }
    if ($path !== '/' && substr($path, -1) !== '/') {
        $path .= '/';
    }
    $isActive = ($path === '/')
        ? ($currentPath === '/')
        : (strpos($currentPath, $path) === 0);
    $title = (string)($item['title'] ?? $path);
    if ($isRu) {
        $title = $titleByPathRu[$path] ?? $title;
    } else {
        $title = $titleByPathEn[$path] ?? $title;
    }
?>
    <a class="<?= $isActive ? 'is-active' : '' ?>" href="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
    </a>
<?php endforeach; ?>
<form class="nav-site-check nav-site-check--drawer" method="get" action="/audit/">
    <input type="text" name="site" placeholder="<?= htmlspecialchars($isRu ? 'Проверь свой сайт' : 'Check your website', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" required>
    <button type="submit"><?= htmlspecialchars($isRu ? 'Проверить' : 'Check', ENT_QUOTES, 'UTF-8') ?></button>
    <div class="nav-site-check-hint" aria-hidden="true">
        <?= htmlspecialchars($isRu ? 'Формат: https://tvoydomen.ru' : 'Format: https://yourdomain.com', ENT_QUOTES, 'UTF-8') ?>
    </div>
</form>
<?php
$requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
if ($requestUri === '') {
    $requestUri = '/';
}
if ($requestUri[0] !== '/') {
    $requestUri = '/' . ltrim($requestUri, '/');
}
$requestPathOnly = parse_url($requestUri, PHP_URL_PATH);
$requestPathOnly = is_string($requestPathOnly) ? $requestPathOnly : '/';
if ($requestPathOnly === '' || strpos($requestPathOnly, '/adminpanel') === 0) {
    $requestUri = '/';
}

$ruHost = 'portcore.ru';
$enHost = 'portcore.online';
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$ruHref = $scheme . '://' . $ruHost . $requestUri;
$enHref = $scheme . '://' . $enHost . $requestUri;
?>
<span class="nav-lang-sep" aria-hidden="true"></span>
<a class="nav-lang-link <?= $isRu ? 'is-active' : '' ?>" href="<?= htmlspecialchars($ruHref, ENT_QUOTES, 'UTF-8') ?>" hreflang="ru" title="Русская версия">
    <img class="nav-lang-flag" src="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.3.2/flags/4x3/ru.svg" alt="RU" loading="lazy" decoding="async">
    <span class="nav-lang-code">RU</span>
</a>
<a class="nav-lang-link <?= !$isRu ? 'is-active' : '' ?>" href="<?= htmlspecialchars($enHref, ENT_QUOTES, 'UTF-8') ?>" hreflang="en" title="English version">
    <img class="nav-lang-flag" src="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.3.2/flags/4x3/gb.svg" alt="EN" loading="lazy" decoding="async">
    <span class="nav-lang-code">EN</span>
</a>
<button class="nav-theme-toggle" type="button" data-theme-toggle aria-pressed="false" title="<?= htmlspecialchars($isRu ? 'Переключить тему' : 'Switch theme', ENT_QUOTES, 'UTF-8') ?>">
    <span class="theme-icon theme-icon-sun" aria-hidden="true">☀</span>
    <span class="theme-icon theme-icon-moon" aria-hidden="true">☾</span>
</button>
