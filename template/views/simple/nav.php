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
    'discussion' => $isRu ? 'Обсуждение' : 'Discussion',
];
$sectionBasePaths = [
    'journal' => '/journal/',
    'playbooks' => '/playbooks/',
    'signals' => '/signals/',
    'fun' => '/fun/',
    'discussion' => '/discussion/',
    'services' => '/services/',
];
$sectionIcons = [
    'home' => '⌂',
    'journal' => '✦',
    'playbooks' => '⚙',
    'signals' => '⌁',
    'fun' => '✺',
    'discussion' => '☰',
    'contact' => '✉',
];

if (!function_exists('examples_fetch_clusters') && defined('DIR')) {
    $examplesCommon = rtrim((string)DIR, '/\\') . '/core/controls/examples/_common.php';
    if (is_file($examplesCommon)) {
        require_once $examplesCommon;
    }
}

$importantTopicItems = [];
if (function_exists('examples_fetch_published_list')) {
    $topicSections = ['journal', 'playbooks', 'signals', 'fun'];
    foreach ($topicSections as $topicSection) {
        $items = array_values((array)examples_fetch_published_list($FRMWRK, (string)$host, 1, $isRu ? 'ru' : 'en', '', $topicSection));
        $item = isset($items[0]) && is_array($items[0]) ? $items[0] : null;
        if (!$item) {
            continue;
        }
        $slug = trim((string)($item['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $cluster = trim((string)($item['cluster_code'] ?? ''));
        $cluster = function_exists('examples_normalize_cluster')
            ? examples_normalize_cluster($cluster, $isRu ? 'ru' : 'en')
            : $cluster;
        $topicTitle = $cluster !== '' && function_exists('examples_cluster_label')
            ? trim((string)examples_cluster_label($cluster, $isRu ? 'ru' : 'en'))
            : '';
        if ($topicTitle === '') {
            $topicTitle = $sectionTitles[$topicSection] ?? $topicSection;
        }
        $importantTopicItems[] = [
            'title' => $topicTitle,
            'path' => ($cluster !== '' && function_exists('examples_cluster_list_path'))
                ? examples_cluster_list_path($cluster, null, $topicSection)
                : ($sectionBasePaths[$topicSection] ?? ('/' . trim($topicSection, '/') . '/')),
            'icon' => $sectionIcons[$topicSection] ?? '•',
        ];
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
            ['title' => $sectionTitles['discussion'], 'path' => $sectionBasePaths['discussion'], 'icon' => $sectionIcons['discussion']],
            ['title' => 'Services', 'path' => '/services/', 'icon' => 'S'],
        ],
    ],
];

$mobileSearchPlaceholder = $isRu
    ? 'Поиск по выпуску'
    : 'Search issues';
$mobileSearchButton = $isRu ? 'Найти' : 'Search';
$mobileAccountLabel = $isRu ? 'Аккаунт / вход' : 'Account / sign in';
?>
    <div class="nav-mobile-tools">
        <form class="nav-mobile-search" method="get" action="/search/">
            <input type="text" name="q" placeholder="<?= htmlspecialchars($mobileSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
            <button type="submit"><?= htmlspecialchars($mobileSearchButton, ENT_QUOTES, 'UTF-8') ?></button>
        </form>
        <a class="nav-mobile-account" href="/account/"><?= htmlspecialchars($mobileAccountLabel, ENT_QUOTES, 'UTF-8') ?></a>
    </div>
<?php

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
