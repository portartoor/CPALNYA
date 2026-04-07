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
$lang = $isRu ? 'ru' : 'en';
$pickIcon = static function (string $code): string {
    $code = trim($code);
    if ($code === '') {
        return '#';
    }
    if (function_exists('mb_substr')) {
        return mb_strtoupper((string)mb_substr($code, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return strtoupper(substr($code, 0, 1));
};

$opsItems = [];
if (isset($FRMWRK) && function_exists('examples_popularity_fetch_top_clusters')) {
    foreach ((array)examples_popularity_fetch_top_clusters($FRMWRK, $host, $lang, 'journal', 3) as $row) {
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '') {
            continue;
        }
        $opsItems[] = [
            'title' => (string)($row['label'] ?? $code),
            'path' => function_exists('examples_cluster_list_path') ? examples_cluster_list_path($code, $host, 'journal') : '/journal/',
            'icon' => $pickIcon($code),
        ];
    }
}
if (count($opsItems) < 3) {
    $opsItems = [
        ['title' => $isRu ? 'Источники' : 'Sources', 'path' => '/journal/', 'icon' => '>'],
        ['title' => $isRu ? 'Фарм' : 'Farm', 'path' => '/journal/', 'icon' => 'F'],
        ['title' => $isRu ? 'Креативы' : 'Creatives', 'path' => '/journal/', 'icon' => 'C'],
    ];
}

$howToItems = [
    ['title' => $isRu ? 'Все HowTo' : 'All HowTo', 'path' => '/playbooks/', 'icon' => 'H'],
];
if (isset($FRMWRK) && function_exists('examples_popularity_fetch_top_clusters')) {
    foreach ((array)examples_popularity_fetch_top_clusters($FRMWRK, $host, $lang, 'playbooks', 3) as $row) {
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '') {
            continue;
        }
        $howToItems[] = [
            'title' => (string)($row['label'] ?? $code),
            'path' => function_exists('examples_cluster_list_path') ? examples_cluster_list_path($code, $host, 'playbooks') : '/playbooks/',
            'icon' => $pickIcon($code),
        ];
    }
}
if (count($howToItems) < 4) {
    $howToItems = [
        ['title' => $isRu ? 'Все HowTo' : 'All HowTo', 'path' => '/playbooks/', 'icon' => 'H'],
        ['title' => $isRu ? 'Фарм-гайды' : 'Farm Guides', 'path' => '/playbooks/?topic=farm', 'icon' => 'F'],
        ['title' => $isRu ? 'Трекинг' : 'Tracking', 'path' => '/playbooks/?topic=tracking', 'icon' => 'T'],
        ['title' => $isRu ? 'Креативы' : 'Creatives', 'path' => '/playbooks/?topic=creatives', 'icon' => 'K'],
    ];
}

$navSections = [
    [
        'label' => $isRu ? 'Выпуск' : 'Issue',
        'items' => [
            ['title' => $isRu ? 'Главная' : 'Home', 'path' => '/', 'icon' => '*'],
            ['title' => $isRu ? 'Журнал' : 'Journal', 'path' => '/journal/', 'icon' => '+'],
            ['title' => $isRu ? 'Практика' : 'Playbooks', 'path' => '/playbooks/', 'icon' => '#'],
        ],
    ],
    [
        'label' => $isRu ? 'Операционка' : 'Ops',
        'items' => $opsItems,
    ],
    [
        'label' => 'HowTo',
        'items' => $howToItems,
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
        $pathForMatch = parse_url($path, PHP_URL_PATH);
        $pathForMatch = is_string($pathForMatch) && $pathForMatch !== '' ? $pathForMatch : $path;
        if ($pathForMatch !== '/' && substr($pathForMatch, -1) !== '/') {
            $pathForMatch .= '/';
        }
        $isActive = ($pathForMatch === '/')
            ? ($currentPath === '/')
            : (strpos($currentPath, $pathForMatch) === 0);
    ?>
        <a class="<?= $isActive ? 'is-active' : '' ?>" href="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>">
            <span class="nav-item-icon" aria-hidden="true"><?= htmlspecialchars((string)$item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
            <span><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>
    <?php endforeach; ?>
<?php endforeach; ?>
