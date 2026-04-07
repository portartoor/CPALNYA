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

$navSections = [
    [
        'label' => $isRu ? 'Выпуск' : 'Issue',
        'items' => [
            ['title' => $isRu ? 'Главная' : 'Home', 'path' => '/', 'icon' => '*'],
            ['title' => $isRu ? 'Журнал' : 'Journal', 'path' => '/journal/', 'icon' => '+'],
            ['title' => $isRu ? 'Темы' : 'Topics', 'path' => '/journal/', 'icon' => '#'],
        ],
    ],
    [
        'label' => $isRu ? 'Операционка' : 'Ops',
        'items' => [
            ['title' => $isRu ? 'Источники' : 'Sources', 'path' => '/journal/', 'icon' => '>'],
            ['title' => $isRu ? 'Фарм' : 'Farm', 'path' => '/journal/', 'icon' => 'F'],
            ['title' => $isRu ? 'Креативы' : 'Creatives', 'path' => '/journal/', 'icon' => 'C'],
            ['title' => $isRu ? 'Трекеры' : 'Trackers', 'path' => '/journal/', 'icon' => 'T'],
        ],
    ],
    [
        'label' => $isRu ? 'Библиотека' : 'Library',
        'items' => [
            ['title' => 'Assets', 'path' => '/solutions/downloads/', 'icon' => 'D'],
            ['title' => 'Playbooks', 'path' => '/solutions/articles/', 'icon' => 'P'],
            ['title' => $isRu ? 'Кейсы' : 'Cases', 'path' => '/cases/', 'icon' => 'K'],
            ['title' => $isRu ? 'Продукты' : 'Products', 'path' => '/projects/', 'icon' => 'R'],
        ],
    ],
    [
        'label' => $isRu ? 'Связь' : 'Reach',
        'items' => [
            ['title' => $isRu ? 'Контакты' : 'Contact', 'path' => '/contact/', 'icon' => '@'],
        ],
    ],
];

foreach ($navSections as $sectionBlock):
?>
    <span class="nav-section-label"><?= htmlspecialchars((string)$sectionBlock['label'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php foreach ($sectionBlock['items'] as $item):
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
<?php endforeach; ?>
