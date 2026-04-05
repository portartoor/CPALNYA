<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$blogData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'page' => 1,
    'per_page' => 8,
    'current_cluster' => '',
    'clusters' => [],
    'total' => 0,
    'total_pages' => 1,
    'items' => [],
    'selected' => null,
    'error' => '',
];

if (strpos((string)$blogData['host'], ':') !== false) {
    $blogData['host'] = explode(':', (string)$blogData['host'], 2)[0];
}

$examplesCommon = DIR . 'core/controls/examples/_common.php';
if (!is_file($examplesCommon)) {
    $blogData['error'] = 'examples common module not found';
    $ModelPage['blog'] = $blogData;
    return;
}
require_once $examplesCommon;

$db = $FRMWRK->DB();
if (!$db || !function_exists('examples_table_exists') || !examples_table_exists($db)) {
    $blogData['error'] = 'examples_articles table is missing';
    $ModelPage['blog'] = $blogData;
    return;
}
if (function_exists('public_portal_seed_blog_articles')) {
    public_portal_seed_blog_articles($FRMWRK, (string)$blogData['host'], function_exists('examples_resolve_lang') ? examples_resolve_lang((string)$blogData['host']) : 'en');
}

if (function_exists('examples_resolve_lang')) {
    $blogData['lang'] = examples_resolve_lang((string)$blogData['host']);
} else {
    $blogData['lang'] = (preg_match('/\.ru$/', (string)$blogData['host']) ? 'ru' : 'en');
}

$blogData['page'] = max(1, (int)($_GET['page'] ?? 1));
$blogData['per_page'] = ((string)$blogData['lang'] === 'ru') ? 9 : 8;
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) ? trim($requestPath) : '';
$segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
    return $value !== '';
}));
$pathCluster = '';
$pathSlug = '';
if (isset($segments[0]) && strtolower((string)$segments[0]) === 'blog') {
    $pathCluster = trim((string)($segments[1] ?? ''));
    $pathSlug = trim((string)($segments[2] ?? ''));
}

$clusterParam = trim((string)($_GET['cluster'] ?? ''));
if ($clusterParam === '') {
    $clusterParam = $pathCluster;
}
$clusterParam = $clusterParam !== '' && function_exists('examples_normalize_cluster')
    ? examples_normalize_cluster($clusterParam, (string)$blogData['lang'])
    : '';
$blogData['current_cluster'] = $clusterParam;

$slugParam = trim((string)($_GET['slug'] ?? ''));
if ($slugParam === '') {
    $slugParam = $pathSlug;
}

if (function_exists('examples_fetch_clusters')) {
    $clusters = (array)examples_fetch_clusters($FRMWRK, (string)$blogData['host'], (string)$blogData['lang'], 40);
    $blogData['clusters'] = $clusters;
}

if ($slugParam === '' && $clusterParam !== '' && function_exists('examples_fetch_published_by_slug')) {
    // Backward compatibility for legacy route /blog/{slug}/.
    $legacySlug = function_exists('examples_slugify') ? examples_slugify($clusterParam) : $clusterParam;
    if ($legacySlug !== '') {
        $legacySelected = examples_fetch_published_by_slug($FRMWRK, (string)$blogData['host'], $legacySlug, (string)$blogData['lang'], '');
        if (is_array($legacySelected)) {
            $blogData['selected'] = $legacySelected;
            $resolvedCluster = trim((string)($legacySelected['cluster_code'] ?? ''));
            if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                $blogData['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$blogData['lang']);
            }
        }
    }
}

if ($slugParam !== '' && function_exists('examples_fetch_published_by_slug')) {
    $slugSafe = function_exists('examples_slugify') ? examples_slugify($slugParam) : $slugParam;
    if ($slugSafe !== '') {
        $selected = examples_fetch_published_by_slug(
            $FRMWRK,
            (string)$blogData['host'],
            $slugSafe,
            (string)$blogData['lang'],
            (string)$blogData['current_cluster']
        );
        if (!is_array($selected) && (string)$blogData['current_cluster'] !== '') {
            $selected = examples_fetch_published_by_slug($FRMWRK, (string)$blogData['host'], $slugSafe, (string)$blogData['lang'], '');
        }
        if (is_array($selected)) {
            $blogData['selected'] = $selected;
            $resolvedCluster = trim((string)($selected['cluster_code'] ?? ''));
            if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                $blogData['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$blogData['lang']);
            }
        }
    }
}

if (function_exists('examples_fetch_published_count') && function_exists('examples_fetch_published_page')) {
    $blogData['total'] = (int)examples_fetch_published_count(
        $FRMWRK,
        (string)$blogData['host'],
        (string)$blogData['lang'],
        '',
        false,
        (string)$blogData['current_cluster']
    );
    $blogData['total_pages'] = max(1, (int)ceil($blogData['total'] / max(1, $blogData['per_page'])));
    if ($blogData['page'] > $blogData['total_pages']) {
        $blogData['page'] = $blogData['total_pages'];
    }
    $items = examples_fetch_published_page(
        $FRMWRK,
        (string)$blogData['host'],
        (string)$blogData['lang'],
        (int)$blogData['page'],
        (int)$blogData['per_page'],
        '',
        false,
        (string)$blogData['current_cluster']
    );
    foreach ((array)$items as $row) {
        $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
        $full = trim((string)($row['preview_image_url'] ?? ''));
        $base = trim((string)($row['preview_image_data'] ?? ''));
        $imageSrc = '';
        $imageKind = 'none';
        if ($thumb !== '') {
            $imageSrc = $thumb;
            $imageKind = 'thumb';
        } elseif ($full !== '') {
            $imageSrc = $full;
            $imageKind = 'full';
        } elseif ($base !== '') {
            $imageSrc = $base;
            $imageKind = 'base64';
        }
        $row['image_src'] = $imageSrc;
        $row['image_kind'] = $imageKind;
        $blogData['items'][] = $row;
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = (string)$blogData['host'];
$host = preg_replace('/^www\./', '', $host);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$isRu = ((string)$blogData['lang'] === 'ru');

$normalizeIso = static function ($value): string {
    $raw = trim((string)$value);
    if ($raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    if ($ts === false) {
        return '';
    }
    return gmdate('c', $ts);
};
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
$collectKeywordCandidates = static function (string $title, string $slug): array {
    $pool = [];
    $titleParts = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($title, 'UTF-8')) ?: [];
    $slugParts = preg_split('/[^a-z0-9]+/i', strtolower($slug)) ?: [];
    foreach (array_merge($titleParts, $slugParts) as $part) {
        $part = trim((string)$part);
        if ($part === '' || mb_strlen($part, 'UTF-8') < 3) {
            continue;
        }
        $pool[] = $part;
    }
    $pool = array_values(array_unique($pool));
    if (count($pool) > 12) {
        $pool = array_slice($pool, 0, 12);
    }
    return $pool;
};

$selectedArticle = is_array($blogData['selected'] ?? null) ? $blogData['selected'] : null;
$portalUser = function_exists('public_portal_current_user') ? public_portal_current_user($FRMWRK) : null;
$portalFlash = function_exists('public_portal_flash_get') ? public_portal_flash_get('portal') : [];
$portalComments = [];
$portalCommentCount = 0;
$portalViewCount = 0;
if ($selectedArticle) {
    $selectedId = (int)($selectedArticle['id'] ?? 0);
    if ($selectedId > 0 && function_exists('public_portal_record_view')) {
        $portalViewCount = public_portal_record_view($FRMWRK, 'blog', $selectedId);
    }
    if ($selectedId > 0 && function_exists('public_portal_fetch_comments')) {
        $portalComments = public_portal_fetch_comments($FRMWRK, 'blog', $selectedId);
        $portalCommentCount = function_exists('public_portal_comment_count')
            ? public_portal_comment_count($portalComments)
            : count($portalComments);
    }
    $selectedArticle['view_count'] = $portalViewCount;
    $selectedArticle['comment_count'] = $portalCommentCount;
    $articleTitle = trim((string)($selectedArticle['title'] ?? ''));
    if ($articleTitle === '') {
        $articleTitle = $isRu ? 'Статья' : 'Article';
    }
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $articleTitle;
    }

    $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selectedArticle['excerpt_html'] ?? $selectedArticle['content_html'] ?? ''))));
    if ($desc === '') {
        $desc = $isRu ? 'Экспертная статья в блоге.' : 'Expert article in the blog.';
    }
    if (mb_strlen($desc, 'UTF-8') > 290) {
        $desc = trim((string)mb_substr($desc, 0, 287, 'UTF-8')) . '...';
    }
    if (empty($ModelPage['description'])) {
        $ModelPage['description'] = $desc;
    }

    $articleSlug = trim((string)($selectedArticle['slug'] ?? ''));
    $articleCluster = trim((string)($selectedArticle['cluster_code'] ?? ''));
    if ($articleCluster !== '' && function_exists('examples_normalize_cluster')) {
        $articleCluster = examples_normalize_cluster($articleCluster, (string)$blogData['lang']);
    }
    if (empty($ModelPage['canonical'])) {
        $ModelPage['canonical'] = $baseUrl
            . (function_exists('examples_article_url_path')
                ? examples_article_url_path($articleSlug, $articleCluster, (string)$blogData['host'])
                : '/blog/' . rawurlencode($articleSlug) . '/');
    }

    $tagList = $collectKeywordCandidates($articleTitle, $articleSlug);
    if (empty($ModelPage['keywords']) && !empty($tagList)) {
        $ModelPage['keywords'] = implode(', ', $tagList);
    }

    $articleImage = $pickArticleImage($selectedArticle);
    if (empty($ModelPage['og_type'])) {
        $ModelPage['og_type'] = 'article';
    }
    if (empty($ModelPage['og_title'])) {
        $ModelPage['og_title'] = $articleTitle;
    }
    if (empty($ModelPage['og_description'])) {
        $ModelPage['og_description'] = (string)($ModelPage['description'] ?? $desc);
    }
    if (empty($ModelPage['og_image']) && $articleImage !== '') {
        $ModelPage['og_image'] = $articleImage;
    }

    $authorName = trim((string)($selectedArticle['author_name'] ?? ''));
    if ($authorName === '') {
        $authorName = 'Portcore Team';
    }
    $publishedIso = $normalizeIso($selectedArticle['published_at'] ?? '');
    if ($publishedIso === '') {
        $publishedIso = $normalizeIso($selectedArticle['created_at'] ?? '');
    }
    $modifiedIso = $normalizeIso($selectedArticle['updated_at'] ?? '');
    if ($modifiedIso === '') {
        $modifiedIso = $publishedIso;
    }

    $ModelPage['article_author'] = $authorName;
    if ($publishedIso !== '') {
        $ModelPage['article_published_time'] = $publishedIso;
    }
    if ($modifiedIso !== '') {
        $ModelPage['article_modified_time'] = $modifiedIso;
    }
    $ModelPage['article_section'] = $isRu ? 'Блог' : 'Blog';
    if (!empty($tagList)) {
        $ModelPage['article_tags'] = $tagList;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => (string)($ModelPage['canonical'] ?? ($baseUrl . '/blog/')),
        ],
        'headline' => $articleTitle,
        'description' => (string)($ModelPage['description'] ?? $desc),
        'inLanguage' => $isRu ? 'ru-RU' : 'en-US',
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $host !== '' ? $host : 'localhost',
            'url' => $baseUrl . '/',
        ],
        'url' => (string)($ModelPage['canonical'] ?? ($baseUrl . '/blog/')),
    ];
    if ($publishedIso !== '') {
        $schema['datePublished'] = $publishedIso;
    }
    if ($modifiedIso !== '') {
        $schema['dateModified'] = $modifiedIso;
    }
    if ($articleImage !== '') {
        $schema['image'] = [$articleImage];
    }
    if (!empty($tagList)) {
        $schema['keywords'] = implode(', ', $tagList);
    }
    $ModelPage['structured_data'] = [$schema];
} else {
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $isRu ? 'Блог' : 'Blog';
    }
    if (empty($ModelPage['description'])) {
        $ModelPage['description'] = $isRu
            ? 'Блог со статьями по сайтам услуг, SEO и B2B-лидогенерации.'
            : 'Blog about service websites, SEO and B2B lead generation.';
    }
    if (empty($ModelPage['canonical'])) {
        $canonicalPath = function_exists('examples_cluster_list_path')
            ? examples_cluster_list_path((string)$blogData['current_cluster'], (string)$blogData['host'])
            : '/blog/';
        if ((int)$blogData['page'] > 1) {
            $canonicalPath .= '?' . http_build_query(['page' => (int)$blogData['page']]);
        }
        $ModelPage['canonical'] = $baseUrl . $canonicalPath;
    }
    if (empty($ModelPage['og_type'])) {
        $ModelPage['og_type'] = 'website';
    }
    if (empty($ModelPage['structured_data'])) {
        $ModelPage['structured_data'] = [[
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => (string)($ModelPage['title'] ?? 'Blog'),
            'description' => (string)($ModelPage['description'] ?? ''),
            'url' => (string)($ModelPage['canonical'] ?? ($baseUrl . '/blog/')),
            'inLanguage' => $isRu ? 'ru-RU' : 'en-US',
        ]];
    }
}

$ModelPage['blog'] = $blogData;
$ModelPage['portal_user'] = $portalUser;
$ModelPage['portal_flash'] = $portalFlash;
$ModelPage['portal_comments'] = $portalComments;
