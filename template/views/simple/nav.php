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

$importantTopics = [];
$importantCounts = [];
$importantRaw = [];

foreach (['journal', 'playbooks', 'signals', 'fun'] as $sectionKey) {
    if (!isset($FRMWRK) || !function_exists('examples_popularity_fetch_top_clusters')) {
        continue;
    }
    foreach ((array)examples_popularity_fetch_top_clusters($FRMWRK, $host, $lang, $sectionKey, 2) as $row) {
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '') {
            continue;
        }
        $title = trim((string)($row['label'] ?? $code));
        $normalizedTitle = $normalizeTopicTitle($title);
        $normalizedParent = $normalizeTopicTitle((string)($sectionTitles[$sectionKey] ?? ''));
        if ($normalizedTitle === '' || $normalizedTitle === $normalizedParent) {
            continue;
        }
        $importantCounts[$normalizedTitle] = (int)($importantCounts[$normalizedTitle] ?? 0) + 1;
        $importantRaw[] = [
            'section' => $sectionKey,
            'title' => $title,
            'normalized_title' => $normalizedTitle,
            'path' => $buildClusterPath($sectionKey, $code),
            'icon' => $pickIcon($code),
        ];
    }
}

foreach ($importantRaw as $item) {
    $title = $item['title'];
    if ((int)($importantCounts[$item['normalized_title']] ?? 0) > 1) {
        $title .= ' (// ' . (string)($sectionTitles[$item['section']] ?? $item['section']) . ')';
    }
    $importantTopics[] = [
        'title' => $title,
        'path' => $item['path'],
        'icon' => $item['icon'],
    ];
}

if (count($importantTopics) < 8) {
    $importantTopics = [
        ['title' => $isRu ? 'Источники' : 'Sources', 'path' => '/journal/', 'icon' => '>'],
        ['title' => $isRu ? 'Фарм' : 'Farm', 'path' => '/journal/', 'icon' => 'F'],
        ['title' => 'AI Creatives', 'path' => '/playbooks/', 'icon' => 'A'],
        ['title' => $isRu ? 'Операции' : 'Operations', 'path' => '/playbooks/', 'icon' => 'O'],
        ['title' => $isRu ? 'Policy shifts' : 'Policy shifts', 'path' => '/signals/', 'icon' => 'P'],
        ['title' => $isRu ? 'Регуляторика СНГ' : 'CIS regulation', 'path' => '/signals/', 'icon' => 'R'],
        ['title' => $isRu ? 'Мемы команды' : 'Team memes', 'path' => '/fun/', 'icon' => 'M'],
        ['title' => $isRu ? 'Драма модерации' : 'Moderation drama', 'path' => '/fun/', 'icon' => 'D'],
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
