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
    ['title' => $isRu ? 'Главная' : 'Home', 'path' => '/', 'icon' => '◫'],
    ['title' => $isRu ? 'Журнал' : 'Journal', 'path' => '/blog/', 'icon' => '✦'],
    ['title' => $isRu ? 'Темы' : 'Topics', 'path' => '/blog/', 'icon' => '◎'],
    ['title' => $isRu ? 'Assets' : 'Assets', 'path' => '/solutions/downloads/', 'icon' => '↓'],
    ['title' => $isRu ? 'Playbooks' : 'Playbooks', 'path' => '/solutions/articles/', 'icon' => '▣'],
    ['title' => $isRu ? 'Кейсы' : 'Cases', 'path' => '/cases/', 'icon' => '◈'],
    ['title' => $isRu ? 'Продукты' : 'Products', 'path' => '/projects/', 'icon' => '⬢'],
    ['title' => $isRu ? 'Контакты' : 'Contact', 'path' => '/contact/', 'icon' => '↗'],
];
foreach ($items as $item):
    $path = (string)$item['path'];
    $isActive = ($path === '/')
        ? ($currentPath === '/')
        : (strpos($currentPath, $path) === 0);
?>
    <a class="<?= $isActive ? 'is-active' : '' ?>" href="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>">
        <span class="nav-item-icon" aria-hidden="true"><?= htmlspecialchars((string)$item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
        <span><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8') ?></span>
    </a>
<?php endforeach; ?>
<a class="nav-cta" href="/solutions/downloads/">
    <span class="nav-item-icon" aria-hidden="true">↓</span>
    <span><?= htmlspecialchars($isRu ? 'Скачать' : 'Downloads', ENT_QUOTES, 'UTF-8') ?></span>
</a>
<button class="nav-theme-toggle" type="button" data-theme-toggle aria-pressed="false" title="<?= htmlspecialchars($isRu ? 'Переключить тему' : 'Switch theme', ENT_QUOTES, 'UTF-8') ?>">
    <span class="theme-icon theme-icon-sun" aria-hidden="true">Sun</span>
    <span class="theme-icon theme-icon-moon" aria-hidden="true">Moon</span>
</button>
