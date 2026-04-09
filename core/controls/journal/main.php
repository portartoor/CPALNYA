<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$journalData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'page' => 1,
    'per_page' => 16,
    'current_cluster' => '',
    'clusters' => [],
    'total' => 0,
    'total_pages' => 1,
    'items' => [],
    'selected' => null,
    'issue' => [],
    'error' => '',
];

if (strpos((string)$journalData['host'], ':') !== false) {
    $journalData['host'] = explode(':', (string)$journalData['host'], 2)[0];
}

$examplesCommon = DIR . 'core/controls/examples/_common.php';
$issueLib = DIR . 'core/libs/journal_issue.php';
if (!is_file($examplesCommon) || !is_file($issueLib)) {
    $journalData['error'] = 'journal dependencies not found';
    $ModelPage['journal'] = $journalData;
    return;
}

require_once $examplesCommon;
require_once $issueLib;

$db = $FRMWRK->DB();
if (!$db || !function_exists('examples_table_exists') || !examples_table_exists($db)) {
    $journalData['error'] = 'examples_articles table is missing';
    $ModelPage['journal'] = $journalData;
    return;
}

$journalData['lang'] = function_exists('examples_resolve_lang')
    ? examples_resolve_lang((string)$journalData['host'])
    : ((preg_match('/\.ru$/', (string)$journalData['host']) ? 'ru' : 'en'));

$journalData['page'] = max(1, (int)($_GET['page'] ?? 1));
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) ? trim($requestPath) : '';
$segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
    return $value !== '';
}));
$pathCluster = '';
$pathSlug = '';
if (isset($segments[0]) && in_array(strtolower((string)$segments[0]), ['journal', 'blog'], true)) {
    $pathCluster = trim((string)($segments[1] ?? ''));
    $pathSlug = trim((string)($segments[2] ?? ''));
}

$clusterParam = trim((string)($_GET['cluster'] ?? ''));
if ($clusterParam === '') {
    $clusterParam = $pathCluster;
}
$clusterParam = $clusterParam !== '' && function_exists('examples_normalize_cluster')
    ? examples_normalize_cluster($clusterParam, (string)$journalData['lang'])
    : '';
$journalData['current_cluster'] = $clusterParam;

$slugParam = trim((string)($_GET['slug'] ?? ''));
if ($slugParam === '') {
    $slugParam = $pathSlug;
}

$journalData['issue'] = journal_issue_get($db, (string)$journalData['lang']);

if (function_exists('examples_fetch_clusters')) {
    $journalData['clusters'] = (array)examples_fetch_clusters(
        $FRMWRK,
        (string)$journalData['host'],
        (string)$journalData['lang'],
        40,
        'journal'
    );
}

if ($slugParam === '' && $clusterParam !== '' && function_exists('examples_fetch_published_by_slug')) {
    $legacySlug = function_exists('examples_slugify') ? examples_slugify($clusterParam) : $clusterParam;
    if ($legacySlug !== '') {
        $legacySelected = examples_fetch_published_by_slug(
            $FRMWRK,
            (string)$journalData['host'],
            $legacySlug,
            (string)$journalData['lang'],
            '',
            'journal'
        );
        if (is_array($legacySelected)) {
            $journalData['selected'] = $legacySelected;
            $resolvedCluster = trim((string)($legacySelected['cluster_code'] ?? ''));
            if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                $journalData['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$journalData['lang']);
            }
        }
    }
}

if ($slugParam !== '' && function_exists('examples_fetch_published_by_slug')) {
    $slugSafe = function_exists('examples_slugify') ? examples_slugify($slugParam) : $slugParam;
    if ($slugSafe !== '') {
        $selected = examples_fetch_published_by_slug(
            $FRMWRK,
            (string)$journalData['host'],
            $slugSafe,
            (string)$journalData['lang'],
            (string)$journalData['current_cluster'],
            'journal'
        );
        if (!is_array($selected) && (string)$journalData['current_cluster'] !== '') {
            $selected = examples_fetch_published_by_slug($FRMWRK, (string)$journalData['host'], $slugSafe, (string)$journalData['lang'], '', 'journal');
        }
        if (is_array($selected)) {
            $journalData['selected'] = $selected;
            $resolvedCluster = trim((string)($selected['cluster_code'] ?? ''));
            if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                $journalData['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$journalData['lang']);
            }
        }
    }
}

if (function_exists('examples_fetch_published_count') && function_exists('examples_fetch_published_page')) {
    $journalData['total'] = (int)examples_fetch_published_count(
        $FRMWRK,
        (string)$journalData['host'],
        (string)$journalData['lang'],
        '',
        false,
        (string)$journalData['current_cluster'],
        'journal'
    );
    $journalData['total_pages'] = max(1, (int)ceil($journalData['total'] / max(1, $journalData['per_page'])));
    if ($journalData['page'] > $journalData['total_pages']) {
        $journalData['page'] = $journalData['total_pages'];
    }
    $items = examples_fetch_published_page(
        $FRMWRK,
        (string)$journalData['host'],
        (string)$journalData['lang'],
        (int)$journalData['page'],
        (int)$journalData['per_page'],
        '',
        false,
        (string)$journalData['current_cluster'],
        'journal'
    );
    foreach ((array)$items as $row) {
        $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
        $full = trim((string)($row['preview_image_url'] ?? ''));
        $base = trim((string)($row['preview_image_data'] ?? ''));
        $row['image_src'] = $thumb !== '' ? $thumb : ($full !== '' ? $full : $base);
        $journalData['items'][] = $row;
    }
    if (function_exists('examples_popularity_attach_views')) {
        $journalData['items'] = examples_popularity_attach_views(
            $FRMWRK,
            (string)$journalData['host'],
            (string)$journalData['lang'],
            'journal',
            (array)$journalData['items']
        );
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = preg_replace('/^www\./', '', (string)$journalData['host']);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$isRu = ((string)$journalData['lang'] === 'ru');
$selectedArticle = is_array($journalData['selected'] ?? null) ? $journalData['selected'] : null;

$pickArticleImage = static function (array $row): string {
    $full = trim((string)($row['preview_image_url'] ?? ''));
    if ($full !== '') {
        return $full;
    }
    $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
    if ($thumb !== '') {
        return $thumb;
    }
    return trim((string)($row['preview_image_data'] ?? ''));
};

$normalizeIso = static function ($value): string {
    $raw = trim((string)$value);
    if ($raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    return $ts === false ? '' : gmdate('c', $ts);
};

if ($selectedArticle) {
    $selectedArticle['hero_image_src'] = $pickArticleImage($selectedArticle);
    if (function_exists('examples_popularity_attach_single_view')) {
        $selectedArticle = examples_popularity_attach_single_view(
            $FRMWRK,
            (string)$journalData['host'],
            (string)$journalData['lang'],
            'journal',
            $selectedArticle
        );
    }
    $journalData['selected'] = $selectedArticle;
    $articleTitle = trim((string)($selectedArticle['title'] ?? ''));
    if ($articleTitle === '') {
        $articleTitle = $isRu ? 'Статья' : 'Article';
    }
    $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selectedArticle['excerpt_html'] ?? $selectedArticle['content_html'] ?? ''))));
    if ($desc === '') {
        $desc = $isRu ? 'Статья в разделе Journal.' : 'Article in the Journal section.';
    }
    if (mb_strlen($desc, 'UTF-8') > 290) {
        $desc = trim((string)mb_substr($desc, 0, 287, 'UTF-8')) . '...';
    }

    $articleSlug = trim((string)($selectedArticle['slug'] ?? ''));
    $articleCluster = trim((string)($selectedArticle['cluster_code'] ?? ''));
    if ($articleCluster !== '' && function_exists('examples_normalize_cluster')) {
        $articleCluster = examples_normalize_cluster($articleCluster, (string)$journalData['lang']);
    }

    $ModelPage['title'] = $ModelPage['title'] ?? $articleTitle;
    $ModelPage['description'] = $ModelPage['description'] ?? $desc;
    $ModelPage['canonical'] = $ModelPage['canonical'] ?? ($baseUrl . examples_article_url_path($articleSlug, $articleCluster, (string)$journalData['host']));
    $ModelPage['og_type'] = $ModelPage['og_type'] ?? 'article';
    $ModelPage['og_title'] = $ModelPage['og_title'] ?? $articleTitle;
    $ModelPage['og_description'] = $ModelPage['og_description'] ?? $desc;
    $articleImage = $pickArticleImage($selectedArticle);
    if ($articleImage !== '') {
        $ModelPage['og_image'] = $ModelPage['og_image'] ?? $articleImage;
    }
    $ModelPage['article_section'] = $isRu ? 'Журнал' : 'Journal';
    $publishedIso = $normalizeIso($selectedArticle['published_at'] ?? '');
    $modifiedIso = $normalizeIso($selectedArticle['updated_at'] ?? '');
    if ($publishedIso !== '') {
        $ModelPage['article_published_time'] = $publishedIso;
    }
    if ($modifiedIso !== '') {
        $ModelPage['article_modified_time'] = $modifiedIso;
    }
    $ModelPage['article_author'] = trim((string)($selectedArticle['author_name'] ?? 'Редакция ЦПАЛЬНЯ'));
} else {
    $title = trim((string)($journalData['issue']['issue_title'] ?? ''));
    $subtitle = trim((string)($journalData['issue']['issue_subtitle'] ?? ''));
    $ModelPage['title'] = $ModelPage['title'] ?? ($title !== '' ? $title : ($isRu ? 'Журнал' : 'Journal'));
    $ModelPage['description'] = $ModelPage['description'] ?? ($subtitle !== '' ? $subtitle : ($isRu ? 'Журнал ЦПАЛЬНЯ про affiliate-операции.' : 'ЦПАЛЬНЯ journal about affiliate operations.'));
    $canonicalPath = examples_cluster_list_path((string)$journalData['current_cluster'], (string)$journalData['host']);
    if ((int)$journalData['page'] > 1) {
        $canonicalPath .= '?' . http_build_query(['page' => (int)$journalData['page']]);
    }
    $ModelPage['canonical'] = $ModelPage['canonical'] ?? ($baseUrl . $canonicalPath);
    $ModelPage['og_type'] = $ModelPage['og_type'] ?? 'website';
}

$ModelPage['portal_user'] = null;
$ModelPage['portal_flash'] = [];
$ModelPage['portal_captcha'] = [];
$ModelPage['portal_comments'] = [];
$ModelPage['portal_comment_total'] = 0;
$ModelPage['portal_content_type'] = 'examples';
$ModelPage['portal_content_id'] = (int)($selectedArticle['id'] ?? 0);

$ModelPage['journal'] = $journalData;
