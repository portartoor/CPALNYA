<?php
$currentPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$currentPath = is_string($currentPath) && $currentPath !== '' ? $currentPath : '/';
if ($currentPath !== '/' && substr($currentPath, -1) !== '/') {
    $currentPath .= '/';
}

$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$isRu = (bool)preg_match('/\.ru$/', $host);
$items = [
    ['title' => $isRu ? 'Главная' : 'Home', 'path' => '/'],
    ['title' => $isRu ? 'Блог' : 'Blog', 'path' => '/blog/'],
    ['title' => $isRu ? 'Решения' : 'Solutions', 'path' => '/solutions/'],
    ['title' => $isRu ? 'Продукты' : 'Products', 'path' => '/projects/'],
    ['title' => $isRu ? 'Кейсы' : 'Cases', 'path' => '/cases/'],
    ['title' => $isRu ? 'Контакты' : 'Contact', 'path' => '/contact/'],
];
foreach ($items as $item):
    $path = (string)$item['path'];
    $isActive = ($path === '/') ? ($currentPath === '/') : (strpos($currentPath, $path) === 0);
?>
    <a class="<?= $isActive ? 'is-active' : '' ?>" href="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8') ?></a>
<?php endforeach; ?>
<a class="nav-cta" href="/solutions/downloads/"><?= htmlspecialchars($isRu ? 'Скачать' : 'Downloads', ENT_QUOTES, 'UTF-8') ?></a>
<button class="nav-theme-toggle" type="button" data-theme-toggle aria-pressed="false" title="<?= htmlspecialchars($isRu ? 'Переключить тему' : 'Switch theme', ENT_QUOTES, 'UTF-8') ?>">
    <span class="theme-icon theme-icon-sun" aria-hidden="true">Sun</span>
    <span class="theme-icon theme-icon-moon" aria-hidden="true">Moon</span>
</button>
