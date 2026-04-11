<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$searchData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'query' => trim((string)($_GET['q'] ?? '')),
    'page' => 1,
    'per_page' => 12,
    'total' => 0,
    'total_pages' => 1,
    'items' => [],
    'featured' => null,
    'fallback_featured' => null,
    'fallback_items' => [],
    'error' => '',
];

if (strpos((string)$searchData['host'], ':') !== false) {
    $searchData['host'] = explode(':', (string)$searchData['host'], 2)[0];
}

$examplesCommon = DIR . 'core/controls/examples/_common.php';
if (!is_file($examplesCommon)) {
    $searchData['error'] = 'search dependencies not found';
    $ModelPage['search'] = $searchData;
    return;
}

require_once $examplesCommon;

$db = $FRMWRK->DB();
if (!$db || !function_exists('examples_table_exists') || !examples_table_exists($db)) {
    $searchData['error'] = 'examples_articles table is missing';
    $ModelPage['search'] = $searchData;
    return;
}

$searchData['lang'] = function_exists('examples_resolve_lang')
    ? examples_resolve_lang((string)$searchData['host'])
    : ((preg_match('/\.ru$/', (string)$searchData['host']) ? 'ru' : 'en'));
$searchData['page'] = max(1, (int)($_GET['page'] ?? 1));

$buildSearchImage = static function (array $row): string {
    $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
    if ($thumb !== '') {
        return $thumb;
    }
    $full = trim((string)($row['preview_image_url'] ?? ''));
    if ($full !== '') {
        return $full;
    }
    return trim((string)($row['preview_image_data'] ?? ''));
};

$buildSearchUrl = static function (array $row, string $host): string {
    $slug = trim((string)($row['slug'] ?? ''));
    if ($slug === '') {
        return '/journal/';
    }
    $cluster = trim((string)($row['cluster_code'] ?? ''));
    $section = trim((string)($row['material_section'] ?? 'journal'));
    if (!in_array($section, ['journal', 'playbooks', 'signals', 'fun'], true)) {
        $section = 'journal';
    }
    if (function_exists('examples_article_url_path')) {
        return (string)examples_article_url_path($slug, $cluster, $host, $section);
    }
    return '/' . $section . '/' . rawurlencode($slug) . '/';
};

$buildSearchSnippet = static function (string $html, int $limit = 320): string {
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }
        return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '…';
    }
    if (strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(substr($text, 0, $limit - 1)) . '...';
};

if ($searchData['query'] !== '' && function_exists('examples_fetch_published_count') && function_exists('examples_fetch_published_page')) {
    $searchData['total'] = (int)examples_fetch_published_count(
        $FRMWRK,
        (string)$searchData['host'],
        (string)$searchData['lang'],
        (string)$searchData['query']
    );
    $searchData['total_pages'] = max(1, (int)ceil($searchData['total'] / max(1, $searchData['per_page'])));
    if ($searchData['page'] > $searchData['total_pages']) {
        $searchData['page'] = $searchData['total_pages'];
    }

    $rows = examples_fetch_published_page(
        $FRMWRK,
        (string)$searchData['host'],
        (string)$searchData['lang'],
        (int)$searchData['page'],
        (int)$searchData['per_page'],
        (string)$searchData['query']
    );

    foreach ((array)$rows as $row) {
        $row['image_src'] = $buildSearchImage((array)$row);
        $row['article_url'] = $buildSearchUrl((array)$row, (string)$searchData['host']);
        $row['search_snippet'] = $buildSearchSnippet((string)($row['excerpt_html'] ?? $row['content_html'] ?? ''), 220);
        $searchData['items'][] = $row;
    }

    if (!empty($searchData['items'])) {
        $searchData['featured'] = $searchData['items'][0];
    }
}

if (empty($searchData['items'])) {
    $hostSafe = mysqli_real_escape_string($db, strtolower((string)$searchData['host']));
    $langCond = function_exists('examples_table_has_lang_column') && examples_table_has_lang_column($db)
        ? (((string)$searchData['lang'] === 'ru') ? "AND lang_code = 'ru'" : "AND lang_code = 'en'")
        : '';
    $sectionCond = function_exists('examples_table_has_column') && examples_table_has_column($db, 'material_section')
        ? "AND material_section IN ('journal','playbooks','signals','fun')"
        : '';
    $clusterSelect = (function_exists('examples_table_has_column') && examples_table_has_column($db, 'cluster_code'))
        ? 'cluster_code'
        : "'' AS cluster_code";
    $sectionSelect = (function_exists('examples_table_has_column') && examples_table_has_column($db, 'material_section'))
        ? 'material_section'
        : "'journal' AS material_section";
    $previewSelect = function_exists('examples_preview_select_sql') ? examples_preview_select_sql($db) : '';

    $fallbackRows = $FRMWRK->DBRecords(
        "SELECT id, title, slug, excerpt_html, content_html, {$clusterSelect}, {$sectionSelect}{$previewSelect}
         FROM examples_articles
         WHERE is_published = 1
           AND slug IS NOT NULL
           AND slug <> ''
           AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
           {$langCond}
           {$sectionCond}
         ORDER BY RAND()
         LIMIT 7"
    );

    foreach ((array)$fallbackRows as $index => $row) {
        $row['image_src'] = $buildSearchImage((array)$row);
        $row['article_url'] = $buildSearchUrl((array)$row, (string)$searchData['host']);
        $row['search_snippet'] = $buildSearchSnippet((string)($row['content_html'] ?? $row['excerpt_html'] ?? ''), $index === 0 ? 520 : 180);
        if ($index === 0) {
            $searchData['fallback_featured'] = $row;
        } else {
            $searchData['fallback_items'][] = $row;
        }
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = preg_replace('/^www\./', '', (string)$searchData['host']);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$isRu = ((string)$searchData['lang'] === 'ru');
$queryForMeta = trim((string)$searchData['query']);

if (!empty($searchData['items'])) {
    $featuredTitle = trim((string)($searchData['featured']['title'] ?? ''));
    $ModelPage['title'] = $queryForMeta !== ''
        ? ($isRu ? ('Поиск: ' . $queryForMeta) : ('Search: ' . $queryForMeta))
        : ($isRu ? 'Поиск' : 'Search');
    $ModelPage['description'] = $featuredTitle !== ''
        ? $buildSearchSnippet($featuredTitle . '. ' . (string)($searchData['featured']['excerpt_html'] ?? $searchData['featured']['content_html'] ?? ''), 180)
        : ($isRu ? 'Результаты поиска по материалам CPALNYA.' : 'Search results across CPALNYA content.');
} else {
    $ModelPage['title'] = $queryForMeta !== ''
        ? ($isRu ? ('Ничего не найдено: ' . $queryForMeta) : ('No results: ' . $queryForMeta))
        : ($isRu ? 'Поиск' : 'Search');
    $ModelPage['description'] = $isRu
        ? 'Поиск по материалам CPALNYA с подборкой случайных материалов, если точных совпадений не найдено.'
        : 'Search across CPALNYA articles with random fallback suggestions when there is no exact match.';
}
$ModelPage['canonical'] = $baseUrl . '/search/' . ($queryForMeta !== '' ? ('?q=' . rawurlencode($queryForMeta)) : '');
$ModelPage['search'] = $searchData;
