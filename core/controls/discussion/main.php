<?php
$host = function_exists('public_portal_host') ? public_portal_host() : strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$lang = function_exists('public_portal_lang') ? public_portal_lang($host) : 'en';
$isRu = ($lang === 'ru');

if (is_file(DIR . 'core/controls/examples/_common.php')) {
    require_once DIR . 'core/controls/examples/_common.php';
}
if (is_file(DIR . 'core/libs/cpalnya_authors.php')) {
    require_once DIR . 'core/libs/cpalnya_authors.php';
}

$db = $FRMWRK->DB();
$allowedSections = ['journal', 'playbooks', 'signals', 'fun'];
$sectionLabels = $isRu
    ? ['journal' => 'Журнал', 'playbooks' => 'Практика', 'signals' => 'Повестка', 'fun' => 'Фан']
    : ['journal' => 'Journal', 'playbooks' => 'Playbooks', 'signals' => 'Signals', 'fun' => 'Fun'];

$discussionData = [
    'host' => $host,
    'lang' => $lang,
    'page' => max(1, (int)($_GET['page'] ?? 1)),
    'per_page' => 18,
    'total' => 0,
    'total_pages' => 1,
    'current_section' => '',
    'items' => [],
    'selected' => null,
    'grouped_items' => [],
    'section_labels' => $sectionLabels,
    'error' => '',
];

$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/discussion/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) ? trim($requestPath, '/') : '';
$segments = array_values(array_filter(explode('/', $requestPath), static function ($value): bool {
    return $value !== '';
}));
$pathSection = '';
$pathSlug = '';
if (($segments[0] ?? '') === 'discussion') {
    $segmentTwo = trim((string)($segments[1] ?? ''));
    $segmentThree = trim((string)($segments[2] ?? ''));
    if (in_array($segmentTwo, $allowedSections, true)) {
        $pathSection = $segmentTwo;
        $pathSlug = $segmentThree;
    } elseif ($segmentTwo !== '') {
        $pathSlug = $segmentTwo;
    }
}

$sectionParam = trim((string)($_GET['section'] ?? $pathSection));
if (!in_array($sectionParam, $allowedSections, true)) {
    $sectionParam = '';
}
$discussionData['current_section'] = $sectionParam;

$slugParam = trim((string)($_GET['slug'] ?? $pathSlug));
$slugParam = $slugParam !== '' && function_exists('examples_slugify')
    ? examples_slugify($slugParam)
    : trim($slugParam);

$buildImage = static function (array $row): string {
    $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
    $full = trim((string)($row['preview_image_url'] ?? ''));
    $data = trim((string)($row['preview_image_data'] ?? ''));
    return $thumb !== '' ? $thumb : ($full !== '' ? $full : $data);
};

$buildExcerpt = static function (array $row): string {
    $html = trim((string)($row['excerpt_html'] ?? ''));
    if ($html === '') {
        $html = trim((string)($row['content_html'] ?? ''));
    }
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') > 320) {
        return trim((string)mb_substr($text, 0, 317, 'UTF-8')) . '...';
    }
    return $text;
};

$buildDiscussionUrl = static function (array $row): string {
    $section = trim((string)($row['material_section'] ?? 'journal'));
    $slug = trim((string)($row['slug'] ?? ''));
    if ($slug === '') {
        return '/discussion/';
    }
    return '/discussion/' . rawurlencode($section) . '/' . rawurlencode($slug) . '/';
};

$buildSourceUrl = static function (array $row): string {
    $slug = trim((string)($row['slug'] ?? ''));
    $cluster = trim((string)($row['cluster_code'] ?? ''));
    $section = trim((string)($row['material_section'] ?? 'journal'));
    if ($slug === '') {
        return '/journal/';
    }
    return function_exists('examples_article_url_path')
        ? examples_article_url_path($slug, $cluster, null, $section)
        : '/journal/';
};

if (
    !$db
    || !function_exists('examples_table_exists')
    || !examples_table_exists($db)
    || !function_exists('public_portal_table_exists')
    || !public_portal_table_exists($db, 'public_comments')
    || !public_portal_table_exists($db, 'public_users')
) {
    $discussionData['error'] = 'discussion dependencies not found';
} else {
    $previewSelect = function_exists('examples_preview_select_sql') ? examples_preview_select_sql($db) : '';
    $hasLang = function_exists('examples_table_has_lang_column') ? examples_table_has_lang_column($db) : false;
    $hasHost = function_exists('examples_table_has_column') ? examples_table_has_column($db, 'domain_host') : false;
    $hasSectionColumn = function_exists('examples_table_has_column') ? examples_table_has_column($db, 'material_section') : false;
    $hasClusterColumn = function_exists('examples_table_has_column') ? examples_table_has_column($db, 'cluster_code') : false;
    $langWhere = $hasLang ? " AND COALESCE(a.lang_code, 'en') = '" . mysqli_real_escape_string($db, $lang) . "'" : '';
    $hostWhere = ($hasHost && $host !== '')
        ? " AND (COALESCE(a.domain_host, '') = '' OR COALESCE(a.domain_host, '') = '" . mysqli_real_escape_string($db, $host) . "')"
        : '';
    $sectionSelect = $hasSectionColumn ? "a.material_section" : "'journal'";
    $clusterSelect = $hasClusterColumn ? "a.cluster_code" : "''";
    $articleWhere = "
        COALESCE(a.is_published, 1) = 1
        {$langWhere}
        {$hostWhere}
    ";
    if ($hasSectionColumn) {
        $articleWhere .= $sectionParam !== ''
            ? " AND a.material_section = '" . mysqli_real_escape_string($db, $sectionParam) . "'"
            : " AND a.material_section IN ('journal','playbooks','signals','fun')";
    }
    $commentStatsSql = "
        SELECT
            c.content_id,
            COUNT(c.id) AS comment_count,
            MAX(c.created_at) AS latest_comment_at
        FROM public_comments c
        INNER JOIN public_users u ON u.id = c.user_id
        WHERE c.content_type = 'examples'
          AND c.is_deleted = 0
          AND c.is_hidden = 0
          AND u.is_active = 1
          AND u.is_banned = 0
        GROUP BY c.content_id
    ";

    if ($slugParam !== '') {
        $detailWhere = $articleWhere . " AND a.slug = '" . mysqli_real_escape_string($db, $slugParam) . "'";
        $rows = (array)$FRMWRK->DBRecords(
            "SELECT
                a.id,
                a.title,
                a.slug,
                a.excerpt_html,
                a.content_html,
                a.author_name,
                {$sectionSelect} AS material_section,
                {$clusterSelect} AS cluster_code{$previewSelect},
                a.published_at,
                a.created_at,
                a.updated_at,
                cs.comment_count,
                cs.latest_comment_at
             FROM examples_articles a
             INNER JOIN ({$commentStatsSql}) cs ON cs.content_id = a.id
             WHERE {$detailWhere}
             ORDER BY COALESCE(a.published_at, a.created_at) DESC, a.id DESC
             LIMIT 1"
        );
        if (!empty($rows[0]) && is_array($rows[0])) {
            $selected = $rows[0];
            $selected['image_src'] = $buildImage($selected);
            $selected['short_excerpt'] = $buildExcerpt($selected);
            $selected['discussion_url'] = $buildDiscussionUrl($selected);
            $selected['source_url'] = $buildSourceUrl($selected);
            $discussionData['selected'] = $selected;
            $discussionData['current_section'] = trim((string)($selected['material_section'] ?? $discussionData['current_section']));
            if (function_exists('cpalnya_author_resolve')) {
                $ModelPage['article_author_profile'] = cpalnya_author_resolve((string)($selected['author_name'] ?? ''), $lang);
            }
            $ModelPage['article_author'] = trim((string)($selected['author_name'] ?? ($isRu ? 'Редакция ЦПАЛЬНЯ' : 'CPALNYA Editorial')));
            $ModelPage['article_section'] = $isRu ? 'Обсуждение' : 'Discussion';
            $ModelPage['title'] = trim((string)($selected['title'] ?? ''));
            $ModelPage['description'] = trim((string)($selected['short_excerpt'] ?? ''));
            $ModelPage['canonical'] = $selected['discussion_url'];
            $ModelPage['og_type'] = 'article';
            $ModelPage['og_title'] = $ModelPage['title'];
            $ModelPage['og_description'] = $ModelPage['description'];
            if ($selected['image_src'] !== '') {
                $ModelPage['og_image'] = $selected['image_src'];
            }
        }
    }

    if (!is_array($discussionData['selected'] ?? null)) {
        $countRows = (array)$FRMWRK->DBRecords(
            "SELECT COUNT(*) AS total
             FROM examples_articles a
             INNER JOIN ({$commentStatsSql}) cs ON cs.content_id = a.id
             WHERE {$articleWhere}"
        );
        $discussionData['total'] = (int)($countRows[0]['total'] ?? 0);
        $discussionData['total_pages'] = max(1, (int)ceil($discussionData['total'] / max(1, $discussionData['per_page'])));
        if ($discussionData['page'] > $discussionData['total_pages']) {
            $discussionData['page'] = $discussionData['total_pages'];
        }
        $offset = ($discussionData['page'] - 1) * $discussionData['per_page'];

        $rows = (array)$FRMWRK->DBRecords(
            "SELECT
                a.id,
                a.title,
                a.slug,
                a.excerpt_html,
                a.content_html,
                a.author_name,
                {$sectionSelect} AS material_section,
                {$clusterSelect} AS cluster_code{$previewSelect},
                a.published_at,
                a.created_at,
                cs.comment_count,
                cs.latest_comment_at
             FROM examples_articles a
             INNER JOIN ({$commentStatsSql}) cs ON cs.content_id = a.id
             WHERE {$articleWhere}
             ORDER BY COALESCE(a.published_at, a.created_at) DESC, a.id DESC
             LIMIT {$offset}, " . (int)$discussionData['per_page']
        );

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['image_src'] = $buildImage($row);
            $row['short_excerpt'] = $buildExcerpt($row);
            $row['discussion_url'] = $buildDiscussionUrl($row);
            $row['source_url'] = $buildSourceUrl($row);
            $discussionData['items'][] = $row;
        }

        foreach ($allowedSections as $sectionCode) {
            $discussionData['grouped_items'][$sectionCode] = [];
        }
        foreach ($discussionData['items'] as $item) {
            $sectionCode = trim((string)($item['material_section'] ?? 'journal'));
            if (!isset($discussionData['grouped_items'][$sectionCode])) {
                $discussionData['grouped_items'][$sectionCode] = [];
            }
            $discussionData['grouped_items'][$sectionCode][] = $item;
        }

        $ModelPage['title'] = $sectionParam !== ''
            ? (($sectionLabels[$sectionParam] ?? 'Discussion') . ($isRu ? ' — обсуждение материалов' : ' discussion threads'))
            : ($isRu ? 'Обсуждение материалов' : 'Discussion threads');
        $ModelPage['description'] = $sectionParam !== ''
            ? ($isRu ? 'Темы с комментариями в разделе ' . ($sectionLabels[$sectionParam] ?? '') . ', от новых материалов к старым.' : 'Commented threads in ' . ($sectionLabels[$sectionParam] ?? 'section') . ', sorted from newest articles to oldest.')
            : ($isRu ? 'Все материалы, под которыми уже идет обсуждение: журнал, практика, повестка и фан.' : 'All materials that already have an active discussion: journal, playbooks, signals and fun.');
        $ModelPage['canonical'] = $sectionParam !== '' ? '/discussion/' . rawurlencode($sectionParam) . '/' : '/discussion/';
        $ModelPage['og_title'] = $ModelPage['title'];
        $ModelPage['og_description'] = $ModelPage['description'];
        $ModelPage['article_section'] = $isRu ? 'Обсуждение' : 'Discussion';
    }
}

$selectedArticle = is_array($discussionData['selected'] ?? null) ? $discussionData['selected'] : null;
$ModelPage['portal_user'] = function_exists('public_portal_current_user')
    ? public_portal_current_user($FRMWRK)
    : null;
$ModelPage['portal_flash'] = function_exists('public_portal_pull_flash')
    ? (array)public_portal_pull_flash()
    : [];
$ModelPage['portal_captcha'] = function_exists('public_portal_captcha_challenge')
    ? (array)public_portal_captcha_challenge()
    : [];
$ModelPage['portal_comments'] = [];
$ModelPage['portal_comment_total'] = 0;
$ModelPage['portal_content_type'] = 'examples';
$ModelPage['portal_content_id'] = (int)($selectedArticle['id'] ?? 0);
$ModelPage['portal_is_favorite'] = (
    !empty($ModelPage['portal_user']['id'])
    && $ModelPage['portal_content_id'] > 0
    && function_exists('public_portal_user_has_favorite')
)
    ? public_portal_user_has_favorite(
        $FRMWRK,
        (int)$ModelPage['portal_user']['id'],
        (string)$ModelPage['portal_content_type'],
        (int)$ModelPage['portal_content_id']
    )
    : false;
if ($ModelPage['portal_content_id'] > 0) {
    if (function_exists('public_portal_fetch_comments')) {
        $ModelPage['portal_comments'] = (array)public_portal_fetch_comments(
            $FRMWRK,
            (string)$ModelPage['portal_content_type'],
            (int)$ModelPage['portal_content_id']
        );
    }
    if (function_exists('public_portal_comment_total_for_content')) {
        $ModelPage['portal_comment_total'] = (int)public_portal_comment_total_for_content(
            $FRMWRK,
            (string)$ModelPage['portal_content_type'],
            (int)$ModelPage['portal_content_id']
        );
        if ($selectedArticle) {
            $discussionData['selected']['comment_count'] = (int)$ModelPage['portal_comment_total'];
        }
    }
}

$ModelPage['discussion'] = $discussionData;
