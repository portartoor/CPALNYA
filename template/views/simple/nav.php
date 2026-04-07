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

$pickIcon = static function (string $code, string $fallback = '#'): string {
    $code = trim($code);
    if ($code === '') {
        return $fallback;
    }
    if (function_exists('mb_substr')) {
        return mb_strtoupper((string)mb_substr($code, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return strtoupper(substr($code, 0, 1));
};

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
    'journal' => '+',
    'playbooks' => '#',
    'signals' => '!',
    'fun' => '~',
];

$buildClusterPath = static function (string $section, string $code) use ($host, $sectionBasePaths): string {
    if (function_exists('examples_cluster_list_path')) {
        return examples_cluster_list_path($code, $host, $section);
    }
    return $sectionBasePaths[$section] ?? '/';
};

$normalizeTopicTitle = static function (string $value): string {
    $value = trim((string)preg_replace('/\s+/u', ' ', $value));
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }
    return strtolower($value);
};

$fetchSectionTopics = static function (string $section, int $limit = 2) use ($FRMWRK, $host, $lang, $pickIcon, $buildClusterPath): array {
    $items = [];
    if (isset($FRMWRK) && function_exists('examples_popularity_fetch_top_clusters')) {
        foreach ((array)examples_popularity_fetch_top_clusters($FRMWRK, $host, $lang, $section, $limit) as $row) {
            $code = trim((string)($row['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $items[] = [
                'title' => (string)($row['label'] ?? $code),
                'path' => $buildClusterPath($section, $code),
                'icon' => $pickIcon($code),
            ];
        }
    }
    return array_slice($items, 0, $limit);
};

$importantTopics = [];
if (isset($FRMWRK) && function_exists('examples_popularity_fetch_top_clusters_global')) {
    $importantTopicCounts = [];
    $importantTopicRaw = [];
    foreach ((array)examples_popularity_fetch_top_clusters_global($FRMWRK, $host, $lang, 3) as $row) {
        $section = trim((string)($row['section'] ?? 'journal'));
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '' || !isset($sectionBasePaths[$section])) {
            continue;
        }
        $title = trim((string)($row['label'] ?? $code));
        $normalizedTitle = $normalizeTopicTitle($title);
        $normalizedParent = $normalizeTopicTitle((string)($sectionTitles[$section] ?? ''));
        if ($normalizedTitle === '' || $normalizedTitle === $normalizedParent) {
            continue;
        }
        $importantTopicCounts[$normalizedTitle] = (int)($importantTopicCounts[$normalizedTitle] ?? 0) + 1;
        $importantTopicRaw[] = [
            'section' => $section,
            'title' => $title,
            'normalized_title' => $normalizedTitle,
            'path' => $buildClusterPath($section, $code),
            'icon' => $pickIcon($code),
        ];
    }
    foreach ($importantTopicRaw as $item) {
        $title = $item['title'];
        if ((int)($importantTopicCounts[$item['normalized_title']] ?? 0) > 1) {
            $title .= ' (// ' . (string)($sectionTitles[$item['section']] ?? $item['section']) . ')';
        }
        $importantTopics[] = [
            'title' => $title,
            'path' => $item['path'],
            'icon' => $item['icon'],
        ];
    }
}
if (count($importantTopics) < 3) {
    $importantTopics = [
        ['title' => $isRu ? 'Источники' : 'Sources', 'path' => '/journal/', 'icon' => '>'],
        ['title' => $isRu ? 'Фарм' : 'Farm', 'path' => '/playbooks/', 'icon' => 'F'],
        ['title' => 'AI Creatives', 'path' => '/playbooks/', 'icon' => 'A'],
    ];
}

$navSections = [
    [
        'label' => $isRu ? 'Выпуск' : 'Issue',
        'items' => [
            ['title' => $isRu ? 'Главная' : 'Home', 'path' => '/', 'icon' => '*'],
            ['title' => $sectionTitles['journal'], 'path' => $sectionBasePaths['journal'], 'icon' => $sectionIcons['journal']],
            ['title' => $sectionTitles['playbooks'], 'path' => $sectionBasePaths['playbooks'], 'icon' => $sectionIcons['playbooks']],
            ['title' => $sectionTitles['signals'], 'path' => $sectionBasePaths['signals'], 'icon' => $sectionIcons['signals']],
            ['title' => $sectionTitles['fun'], 'path' => $sectionBasePaths['fun'], 'icon' => $sectionIcons['fun']],
        ],
    ],
    [
        'label' => $isRu ? 'Важные темы' : 'Important Topics',
        'items' => $importantTopics,
    ],
];

foreach (['journal', 'playbooks', 'signals', 'fun'] as $sectionKey) {
    $sectionItems = [[
        'title' => $sectionTitles[$sectionKey],
        'path' => $sectionBasePaths[$sectionKey],
        'icon' => $sectionIcons[$sectionKey],
    ]];
    foreach ($fetchSectionTopics($sectionKey, 2) as $topicItem) {
        $sectionItems[] = $topicItem;
    }
    $navSections[] = [
        'label' => '',
        'items' => $sectionItems,
    ];
}

$navSections[] = [
    'label' => $isRu ? 'Связь' : 'Reach',
    'items' => [
        ['title' => $isRu ? 'Контакты' : 'Contact', 'path' => '/contact/', 'icon' => '@'],
    ],
];

foreach ($navSections as $sectionBlock):
?>
    <?php if (trim((string)$sectionBlock['label']) !== ''): ?>
        <span class="nav-section-label"><?= htmlspecialchars((string)$sectionBlock['label'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
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
