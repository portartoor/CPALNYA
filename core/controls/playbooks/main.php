<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$playbooksData = [
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

if (strpos((string)$playbooksData['host'], ':') !== false) {
    $playbooksData['host'] = explode(':', (string)$playbooksData['host'], 2)[0];
}

$examplesCommon = DIR . 'core/controls/examples/_common.php';
if (!is_file($examplesCommon)) {
    $playbooksData['error'] = 'playbooks dependencies not found';
    $ModelPage['playbooks'] = $playbooksData;
    return;
}

require_once $examplesCommon;

$db = $FRMWRK->DB();
if (!$db || !function_exists('examples_table_exists') || !examples_table_exists($db)) {
    $playbooksData['error'] = 'examples_articles table is missing';
    $ModelPage['playbooks'] = $playbooksData;
    return;
}

$playbooksData['lang'] = function_exists('examples_resolve_lang')
    ? examples_resolve_lang((string)$playbooksData['host'])
    : ((preg_match('/\.ru$/', (string)$playbooksData['host']) ? 'ru' : 'en'));

$isRu = ((string)$playbooksData['lang'] === 'ru');
$playbooksData['issue'] = [
    'issue_kicker' => $isRu ? 'HowTo / Playbooks' : 'HowTo / Playbooks',
    'hero_title' => $isRu ? 'Практические playbooks для арбитражной операционки' : 'Practical playbooks for affiliate operations',
    'hero_description' => $isRu
        ? 'Пошаговые разборы, troubleshooting, setup-логика и переиспользуемые рабочие схемы для трафика, фарма, трекинга и креативов.'
        : 'Step-by-step breakdowns, troubleshooting, setup logic and reusable operating patterns for traffic, farm, tracking and creatives.',
    'issue_title' => $isRu ? 'База HowTo-материалов' : 'HowTo knowledge base',
    'issue_subtitle' => $isRu
        ? 'Здесь живут прикладные статьи секции playbooks: от day-one setup до восстановления после сбоев.'
        : 'This section contains applied playbook articles: from day-one setup to recovery after failures.',
    'hero_note' => $isRu ? 'SOP, шаблоны, настройки, rollback-планы' : 'SOPs, templates, setups, rollback plans',
    'hero_image_url' => '',
    'hero_image_data' => '',
];

$playbooksData['page'] = max(1, (int)($_GET['page'] ?? 1));
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) ? trim($requestPath) : '';
$segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
    return $value !== '';
}));
$pathCluster = '';
$pathSlug = '';
if (isset($segments[0]) && strtolower((string)$segments[0]) === 'playbooks') {
    $pathCluster = trim((string)($segments[1] ?? ''));
    $pathSlug = trim((string)($segments[2] ?? ''));
}

$clusterParam = trim((string)($_GET['cluster'] ?? ''));
if ($clusterParam === '') {
    $clusterParam = $pathCluster;
}

$topicAlias = strtolower(trim((string)($_GET['topic'] ?? '')));
$topicNeedles = [
    'farm' => ['farm', 'farma', 'facebook-farm', 'nastroika-i-progrev-facebook-farm'],
    'tracking' => ['tracking', 'tracker', 'postback', 'treker', 'tracker-postback'],
    'creatives' => ['creative', 'creatives', 'kreativ', 'matritsa-testirovaniya-kreativov'],
];

$playbooksData['clusters'] = function_exists('examples_fetch_clusters')
    ? (array)examples_fetch_clusters($FRMWRK, (string)$playbooksData['host'], (string)$playbooksData['lang'], 40, 'playbooks')
    : [];

if ($clusterParam === '' && $topicAlias !== '' && isset($topicNeedles[$topicAlias])) {
    foreach ($playbooksData['clusters'] as $clusterRow) {
        $code = strtolower(trim((string)($clusterRow['code'] ?? '')));
        $label = strtolower(trim((string)($clusterRow['label'] ?? '')));
        foreach ($topicNeedles[$topicAlias] as $needle) {
            if (($code !== '' && strpos($code, $needle) !== false) || ($label !== '' && strpos($label, $needle) !== false)) {
                $clusterParam = (string)($clusterRow['code'] ?? '');
                break 2;
            }
        }
    }
}

$clusterParam = $clusterParam !== '' && function_exists('examples_normalize_cluster')
    ? examples_normalize_cluster($clusterParam, (string)$playbooksData['lang'])
    : '';
$playbooksData['current_cluster'] = $clusterParam;

$slugParam = trim((string)($_GET['slug'] ?? ''));
if ($slugParam === '') {
    $slugParam = $pathSlug;
}

if ($slugParam === '' && $clusterParam !== '' && function_exists('examples_fetch_published_by_slug')) {
    $legacySlug = function_exists('examples_slugify') ? examples_slugify($clusterParam) : $clusterParam;
    if ($legacySlug !== '') {
        $legacySelected = examples_fetch_published_by_slug(
            $FRMWRK,
            (string)$playbooksData['host'],
            $legacySlug,
            (string)$playbooksData['lang'],
            '',
            'playbooks'
        );
        if (is_array($legacySelected)) {
            $playbooksData['selected'] = $legacySelected;
            $resolvedCluster = trim((string)($legacySelected['cluster_code'] ?? ''));
            if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                $playbooksData['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$playbooksData['lang']);
            }
        }
    }
}

if ($slugParam !== '' && function_exists('examples_fetch_published_by_slug')) {
    $slugSafe = function_exists('examples_slugify') ? examples_slugify($slugParam) : $slugParam;
    if ($slugSafe !== '') {
        $selected = examples_fetch_published_by_slug(
            $FRMWRK,
            (string)$playbooksData['host'],
            $slugSafe,
            (string)$playbooksData['lang'],
            (string)$playbooksData['current_cluster'],
            'playbooks'
        );
        if (!is_array($selected) && (string)$playbooksData['current_cluster'] !== '') {
            $selected = examples_fetch_published_by_slug($FRMWRK, (string)$playbooksData['host'], $slugSafe, (string)$playbooksData['lang'], '', 'playbooks');
        }
        if (is_array($selected)) {
            $playbooksData['selected'] = $selected;
            $resolvedCluster = trim((string)($selected['cluster_code'] ?? ''));
            if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                $playbooksData['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$playbooksData['lang']);
            }
        }
    }
}

if (function_exists('examples_fetch_published_count') && function_exists('examples_fetch_published_page')) {
    $playbooksData['total'] = (int)examples_fetch_published_count(
        $FRMWRK,
        (string)$playbooksData['host'],
        (string)$playbooksData['lang'],
        '',
        false,
        (string)$playbooksData['current_cluster'],
        'playbooks'
    );
    $playbooksData['total_pages'] = max(1, (int)ceil($playbooksData['total'] / max(1, $playbooksData['per_page'])));
    if ($playbooksData['page'] > $playbooksData['total_pages']) {
        $playbooksData['page'] = $playbooksData['total_pages'];
    }
    $items = examples_fetch_published_page(
        $FRMWRK,
        (string)$playbooksData['host'],
        (string)$playbooksData['lang'],
        (int)$playbooksData['page'],
        (int)$playbooksData['per_page'],
        '',
        false,
        (string)$playbooksData['current_cluster'],
        'playbooks'
    );
    foreach ((array)$items as $row) {
        $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
        $full = trim((string)($row['preview_image_url'] ?? ''));
        $base = trim((string)($row['preview_image_data'] ?? ''));
        $row['image_src'] = $thumb !== '' ? $thumb : ($full !== '' ? $full : $base);
        $playbooksData['items'][] = $row;
    }
    if (function_exists('examples_popularity_attach_views')) {
        $playbooksData['items'] = examples_popularity_attach_views(
            $FRMWRK,
            (string)$playbooksData['host'],
            (string)$playbooksData['lang'],
            'playbooks',
            (array)$playbooksData['items']
        );
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = preg_replace('/^www\./', '', (string)$playbooksData['host']);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$selectedArticle = is_array($playbooksData['selected'] ?? null) ? $playbooksData['selected'] : null;

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
            (string)$playbooksData['host'],
            (string)$playbooksData['lang'],
            'playbooks',
            $selectedArticle
        );
    }
    $playbooksData['selected'] = $selectedArticle;
    $articleTitle = trim((string)($selectedArticle['title'] ?? ''));
    if ($articleTitle === '') {
        $articleTitle = $isRu ? 'HowTo' : 'HowTo';
    }
    $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selectedArticle['excerpt_html'] ?? $selectedArticle['content_html'] ?? ''))));
    if ($desc === '') {
        $desc = $isRu ? 'Статья в разделе Playbooks.' : 'Article in the Playbooks section.';
    }
    if (mb_strlen($desc, 'UTF-8') > 290) {
        $desc = trim((string)mb_substr($desc, 0, 287, 'UTF-8')) . '...';
    }

    $articleSlug = trim((string)($selectedArticle['slug'] ?? ''));
    $articleCluster = trim((string)($selectedArticle['cluster_code'] ?? ''));
    if ($articleCluster !== '' && function_exists('examples_normalize_cluster')) {
        $articleCluster = examples_normalize_cluster($articleCluster, (string)$playbooksData['lang']);
    }

    $ModelPage['title'] = $ModelPage['title'] ?? $articleTitle;
    $ModelPage['description'] = $ModelPage['description'] ?? $desc;
    $ModelPage['canonical'] = $ModelPage['canonical'] ?? ($baseUrl . examples_article_url_path($articleSlug, $articleCluster, (string)$playbooksData['host'], 'playbooks'));
    $ModelPage['og_type'] = $ModelPage['og_type'] ?? 'article';
    $ModelPage['og_title'] = $ModelPage['og_title'] ?? $articleTitle;
    $ModelPage['og_description'] = $ModelPage['og_description'] ?? $desc;
    $articleImage = $pickArticleImage($selectedArticle);
    if ($articleImage !== '') {
        $ModelPage['og_image'] = $ModelPage['og_image'] ?? $articleImage;
    }
    $ModelPage['article_author'] = $ModelPage['article_author'] ?? trim((string)($selectedArticle['author_name'] ?? ''));
    $ModelPage['article_published_time'] = $ModelPage['article_published_time'] ?? $normalizeIso($selectedArticle['published_at'] ?? $selectedArticle['created_at'] ?? '');
    $ModelPage['article_modified_time'] = $ModelPage['article_modified_time'] ?? $normalizeIso($selectedArticle['updated_at'] ?? '');
    $ModelPage['article_section'] = $ModelPage['article_section'] ?? 'Playbooks';
    if ($articleCluster !== '') {
        $ModelPage['article_tags'] = $ModelPage['article_tags'] ?? [$articleCluster];
    }
} else {
    $title = $isRu ? 'HowTo / Playbooks для CPA-команд' : 'HowTo / Playbooks for affiliate teams';
    $description = $isRu
        ? 'Практические playbooks, troubleshooting и setup-материалы для фарма, трекинга, креативов и арбитражной операционки.'
        : 'Practical playbooks, troubleshooting and setup materials for farms, tracking, creatives and affiliate operations.';
    $canonicalPath = examples_cluster_list_path((string)$playbooksData['current_cluster'], (string)$playbooksData['host'], 'playbooks');
    $ModelPage['title'] = $ModelPage['title'] ?? $title;
    $ModelPage['description'] = $ModelPage['description'] ?? $description;
    $ModelPage['canonical'] = $ModelPage['canonical'] ?? ($baseUrl . $canonicalPath);
    $ModelPage['og_title'] = $ModelPage['og_title'] ?? $title;
    $ModelPage['og_description'] = $ModelPage['og_description'] ?? $description;
    $ModelPage['article_section'] = $ModelPage['article_section'] ?? 'Playbooks';
}

$ModelPage['portal_user'] = null;
$ModelPage['portal_flash'] = [];
$ModelPage['portal_captcha'] = [];
$ModelPage['portal_comments'] = [];
$ModelPage['portal_comment_total'] = 0;
$ModelPage['portal_content_type'] = 'examples';
$ModelPage['portal_content_id'] = (int)($selectedArticle['id'] ?? 0);

$ModelPage['playbooks'] = $playbooksData;
