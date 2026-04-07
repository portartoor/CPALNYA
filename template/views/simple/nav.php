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

$sectionTitles = [
    'journal' => $isRu ? 'Журнал' : 'Journal',
    'playbooks' => $isRu ? 'Практика' : 'Playbooks',
    'signals' => $isRu ? 'Повестка' : 'Signals',
    'fun' => $isRu ? 'Фан' : 'Fun',
];
$sectionBasePaths = [
    'journal' => '/journal/',
    'playbooks' => '/playbooks/',
    'signals' => '/signals/',
    'fun' => '/fun/',
];
$sectionIcons = [
    'home' => '⌂',
    'journal' => '✦',
    'playbooks' => '⚙',
    'signals' => '⌁',
    'fun' => '✺',
    'contact' => '✉',
];

$importantTopicItems = [];
if (function_exists('examples_fetch_clusters')) {
    $topicSections = ['journal', 'playbooks', 'signals', 'fun'];
    $seenTopicLabels = [];
    foreach ($topicSections as $topicSection) {
        $clusters = array_values((array)examples_fetch_clusters($FRMWRK, (string)$host, $isRu ? 'ru' : 'en', 8, $topicSection));
        foreach ($clusters as $cluster) {
            $cluster = (array)$cluster;
            $code = trim((string)($cluster['code'] ?? ''));
            $label = trim((string)($cluster['label'] ?? $code));
            if ($code === '' || $label === '') {
                continue;
            }
            $labelKey = function_exists('mb_strtolower')
                ? mb_strtolower($label, 'UTF-8')
                : strtolower($label);
            if (isset($seenTopicLabels[$labelKey])) {
                continue;
            }
            $seenTopicLabels[$labelKey] = true;
            $importantTopicItems[] = [
                'title' => $label,
                'path' => function_exists('examples_cluster_list_path')
                    ? examples_cluster_list_path($code, null, $topicSection)
                    : ('/' . trim($topicSection, '/') . '/' . $code . '/'),
                'icon' => $sectionIcons[$topicSection] ?? '•',
            ];
        }
    }
}

$navSections = [
    [
        'label' => $isRu ? 'Выпуск' : 'Issue',
        'items' => [
            ['title' => $isRu ? 'Главная' : 'Home', 'path' => '/', 'icon' => $sectionIcons['home']],
            ['title' => $sectionTitles['journal'], 'path' => $sectionBasePaths['journal'], 'icon' => $sectionIcons['journal']],
            ['title' => $sectionTitles['playbooks'], 'path' => $sectionBasePaths['playbooks'], 'icon' => $sectionIcons['playbooks']],
            ['title' => $sectionTitles['signals'], 'path' => $sectionBasePaths['signals'], 'icon' => $sectionIcons['signals']],
            ['title' => $sectionTitles['fun'], 'path' => $sectionBasePaths['fun'], 'icon' => $sectionIcons['fun']],
        ],
    ],
];

if (!empty($importantTopicItems)) {
    $navSections[] = [
        'label' => $isRu ? 'Важное' : 'Important',
        'items' => $importantTopicItems,
    ];
}

$navSections[] = [
    'label' => $isRu ? 'Связь' : 'Reach',
    'items' => [
        ['title' => $isRu ? 'Контакты' : 'Contact', 'path' => '/contact/', 'icon' => $sectionIcons['contact']],
    ],
];

foreach ($navSections as $sectionBlock):
?>
    <span class="nav-section-label"><?= htmlspecialchars((string)$sectionBlock['label'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php foreach ((array)$sectionBlock['items'] as $item):
        $path = (string)($item['path'] ?? '/');
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
            <span class="nav-item-icon" aria-hidden="true"><?= htmlspecialchars((string)($item['icon'] ?? '•'), ENT_QUOTES, 'UTF-8') ?></span>
            <span><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
    <?php endforeach; ?>
<?php endforeach; ?>
