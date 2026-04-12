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
    'signals' => '⌃',
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
    $topicLang = $isRu ? 'ru' : 'en';
    $generalTitle = $isRu ? 'Общий' : 'General';
    $generalKey = function_exists('mb_strtolower') ? mb_strtolower($generalTitle, 'UTF-8') : strtolower($generalTitle);
    $expandedQuota = ['journal' => 2, 'playbooks' => 2, 'signals' => 1, 'fun' => 1];
    $collapsedQuota = ['journal' => 1, 'playbooks' => 1, 'signals' => 1, 'fun' => 1];

    $normalizeTopicKey = static function (string $value): string {
        $value = trim((string)preg_replace('/\s+/u', ' ', $value));
        if ($value === '') {
            return '';
        }
        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
    };

    $topicCandidates = [];
    foreach ($topicSections as $topicSection) {
        $items = array_values((array)examples_fetch_published_list($FRMWRK, (string)$host, 48, $topicLang, '', $topicSection));
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $slug = trim((string)($item['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $cluster = trim((string)($item['cluster_code'] ?? ''));
            $cluster = function_exists('examples_normalize_cluster')
                ? examples_normalize_cluster($cluster, $topicLang)
                : $cluster;
            $topicTitle = function_exists('examples_cluster_label')
                ? trim((string)examples_cluster_label($cluster, $topicLang))
                : '';
            if ($topicTitle === '') {
                $topicTitle = $generalTitle;
            }
            $topicCandidates[$topicSection][] = [
                'title' => $topicTitle,
                'topic_key' => $normalizeTopicKey($topicTitle),
                'cluster' => $cluster,
                'path' => function_exists('examples_cluster_list_path')
                    ? examples_cluster_list_path($cluster, null, $topicSection)
                    : ($sectionBasePaths[$topicSection] ?? ('/' . trim($topicSection, '/') . '/')),
                'icon' => $sectionIcons[$topicSection] ?? '•',
                'section' => $topicSection,
            ];
        }
    }

    $generalTopicItem = null;
    $usedTopicKeys = [];
    foreach ($topicSections as $topicSection) {
        foreach ((array)($topicCandidates[$topicSection] ?? []) as $candidate) {
            $clusterCode = trim((string)($candidate['cluster'] ?? ''));
            $topicKey = (string)($candidate['topic_key'] ?? '');
            if (!in_array($clusterCode, ['general', 'obshchiy', ''], true) && $topicKey !== $generalKey) {
                continue;
            }
            $generalTopicItem = $candidate;
            $generalTopicItem['title'] = $generalTitle;
            $generalTopicItem['topic_key'] = $generalKey;
            $usedTopicKeys[$generalKey] = true;
            break 2;
        }
    }

    $pickTopics = static function (array $quotaMap, array $seedUsed) use ($topicSections, $topicCandidates): array {
        $result = [];
        $used = $seedUsed;
        foreach ($topicSections as $topicSection) {
            $limit = (int)($quotaMap[$topicSection] ?? 0);
            if ($limit < 1) {
                continue;
            }
            foreach ((array)($topicCandidates[$topicSection] ?? []) as $candidate) {
                $topicKey = (string)($candidate['topic_key'] ?? '');
                if ($topicKey === '' || isset($used[$topicKey])) {
                    continue;
                }
                $used[$topicKey] = true;
                $result[] = $candidate;
                $limit--;
                if ($limit <= 0) {
                    break;
                }
            }
        }
        return $result;
    };

    $expandedTopicItems = $pickTopics($expandedQuota, $usedTopicKeys);
    $collapsedTopicItems = $pickTopics($collapsedQuota, $usedTopicKeys);
    $collapsedPaths = [];
    foreach ($collapsedTopicItems as $collapsedItem) {
        $collapsedPaths[(string)($collapsedItem['path'] ?? '')] = true;
    }

    if (is_array($generalTopicItem)) {
        $generalTopicItem['class'] = 'nav-topic-item';
        $importantTopicItems[] = $generalTopicItem;
        $collapsedPaths[(string)($generalTopicItem['path'] ?? '')] = true;
    }

    foreach ($expandedTopicItems as $topicItem) {
        $topicPath = (string)($topicItem['path'] ?? '');
        $topicItem['class'] = isset($collapsedPaths[$topicPath]) ? 'nav-topic-item' : 'nav-topic-item nav-topic-item-extra';
        $importantTopicItems[] = $topicItem;
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

$mobileSearchPlaceholder = $isRu ? 'Поиск по выпуску' : 'Search issues';
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
        $itemClasses = trim(((string)($item['class'] ?? '')) . ' ' . ($isActive ? 'is-active' : ''));
    ?>
        <a class="<?= htmlspecialchars($itemClasses, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>">
            <span class="nav-item-icon" aria-hidden="true"><?= htmlspecialchars((string)($item['icon'] ?? '•'), ENT_QUOTES, 'UTF-8') ?></span>
            <span><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
        </a>
    <?php endforeach; ?>
<?php endforeach; ?>
