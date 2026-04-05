<?php
$blog = (array)($ModelPage['blog'] ?? []);
$items = (array)($blog['items'] ?? []);
$selected = is_array($blog['selected'] ?? null) ? $blog['selected'] : null;
$total = (int)($blog['total'] ?? 0);
$page = max(1, (int)($blog['page'] ?? 1));
$totalPages = max(1, (int)($blog['total_pages'] ?? 1));
$lang = (string)($blog['lang'] ?? 'en');
$err = trim((string)($blog['error'] ?? ''));
$clusters = array_values(array_filter((array)($blog['clusters'] ?? []), static function ($row): bool {
    return trim((string)($row['code'] ?? '')) !== '';
}));
$currentCluster = trim((string)($blog['current_cluster'] ?? ''));
$isRu = ($lang === 'ru');
$title = $isRu ? 'Блог' : 'Blog';
$lead = $isRu
    ? 'Аналитика, архитектурные заметки и практические разборы по запуску и развитию digital-проектов: от структуры продукта до контентных и технических решений, которые влияют на воронку и выручку.'
    : 'Analytics, architecture notes and practical breakdowns for launching and scaling digital products: from product structure to content and technical decisions that impact pipeline and revenue.';
$emptyText = $isRu ? 'Пока нет опубликованных статей.' : 'No published articles yet.';
$prevText = $isRu ? 'Назад' : 'Prev';
$nextText = $isRu ? 'Вперед' : 'Next';
if ($selected !== null) {
    $selectedThumb = trim((string)($selected['preview_image_thumb_url'] ?? ''));
    $selectedFull = trim((string)($selected['preview_image_url'] ?? ''));
    $selectedBase = trim((string)($selected['preview_image_data'] ?? ''));
    $selected['hero_image_src'] = $selectedFull !== '' ? $selectedFull : ($selectedThumb !== '' ? $selectedThumb : $selectedBase);
}
$relatedBlogItems = [];
if ($selected !== null) {
    $selectedSlug = trim((string)($selected['slug'] ?? ''));
    foreach ($items as $item) {
        $itemSlug = trim((string)($item['slug'] ?? ''));
        if ($itemSlug === '' || $itemSlug === $selectedSlug) {
            continue;
        }
        $relatedBlogItems[] = $item;
    }
    if (count($relatedBlogItems) > 1) {
        shuffle($relatedBlogItems);
    }
    if (count($relatedBlogItems) > 3) {
        $relatedBlogItems = array_slice($relatedBlogItems, 0, 3);
    }
}

$buildClusterLink = static function (string $clusterCode = ''): string {
    $clusterCode = trim($clusterCode);
    if ($clusterCode === '') {
        return '/blog/';
    }
    return '/blog/' . rawurlencode($clusterCode) . '/';
};

$buildPageLink = static function (int $p) use ($currentCluster): string {
    $base = $currentCluster === '' ? '/blog/' : ('/blog/' . rawurlencode($currentCluster) . '/');
    return $base . '?' . http_build_query(['page' => max(1, $p)]);
};

$buildArticleLink = static function (string $slug, string $clusterCode = '') use ($currentCluster): string {
    $slug = trim($slug);
    if ($slug === '') {
        return $currentCluster === '' ? '/blog/' : '/blog/' . rawurlencode($currentCluster) . '/';
    }
    $clusterCode = trim($clusterCode);
    if ($clusterCode === '') {
        $clusterCode = $currentCluster;
    }
    if ($clusterCode === '') {
        return '/blog/' . rawurlencode($slug) . '/';
    }
    return '/blog/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/';
};
$trimPreview = static function (string $text, int $limit = 210): string {
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    if ($text === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $limit) {
            return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
        }
        return $text;
    }
    if (strlen($text) > $limit) {
        return rtrim(substr($text, 0, $limit - 1)) . '...';
    }
    return $text;
};

$splitContentByFirstH2 = static function (string $html): array {
    $html = trim($html);
    if ($html === '') {
        return ['intro' => '', 'body' => $html];
    }

    $prevUseInternal = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<div id="pc-content-root">' . $html . '</div>';
    $loaded = $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return ['intro' => '', 'body' => $html];
    }

    $root = $doc->getElementById('pc-content-root');
    if (!$root instanceof DOMElement) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return ['intro' => '', 'body' => $html];
    }

    $introDoc = new DOMDocument('1.0', 'UTF-8');
    $introWrap = $introDoc->createElement('div');
    $introDoc->appendChild($introWrap);

    $bodyDoc = new DOMDocument('1.0', 'UTF-8');
    $bodyWrap = $bodyDoc->createElement('div');
    $bodyDoc->appendChild($bodyWrap);

    $nodes = [];
    foreach ($root->childNodes as $childNode) {
        if ($childNode instanceof DOMText && trim((string)$childNode->nodeValue) === '') {
            continue;
        }
        $nodes[] = $childNode;
    }

    $firstH2Removed = false;
    $filtered = [];
    foreach ($nodes as $node) {
        $isH2 = ($node instanceof DOMElement) && (strtolower($node->tagName) === 'h2');
        if ($isH2 && !$firstH2Removed) {
            $firstH2Removed = true; // remove first H2 only
            continue;
        }
        $filtered[] = $node;
    }

    $introMode = true;
    foreach ($filtered as $node) {
        $isParagraph = ($node instanceof DOMElement) && (strtolower($node->tagName) === 'p');
        if ($introMode && $isParagraph) {
            $introWrap->appendChild($introDoc->importNode($node, true));
            continue;
        }
        $introMode = false;
        $bodyWrap->appendChild($bodyDoc->importNode($node, true));
    }

    $readInner = static function (DOMNode $node, DOMDocument $ownerDoc): string {
        $out = '';
        foreach ($node->childNodes as $child) {
            $out .= (string)$ownerDoc->saveHTML($child);
        }
        return trim($out);
    };

    $introHtml = $readInner($introWrap, $introDoc);
    $bodyHtml = $readInner($bodyWrap, $bodyDoc);

    libxml_clear_errors();
    libxml_use_internal_errors($prevUseInternal);

    return ['intro' => $introHtml, 'body' => $bodyHtml];
};
$stripChecklistBrackets = static function (string $html): string {
    $html = trim($html);
    if ($html === '' || strpos($html, '[') === false) {
        return $html;
    }

    $prevUseInternal = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<div id="pc-checklist-root">' . $html . '</div>';
    $loaded = $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return preg_replace('/\[\s*\]\s*/u', '', $html) ?? $html;
    }

    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query('//text()');
    if ($nodes instanceof DOMNodeList) {
        foreach ($nodes as $textNode) {
            $parentName = strtolower((string)($textNode->parentNode->nodeName ?? ''));
            if (in_array($parentName, ['script', 'style', 'code', 'pre'], true)) {
                continue;
            }
            $textNode->nodeValue = (string)(preg_replace('/\[\s*\]\s*/u', '', (string)$textNode->nodeValue) ?? (string)$textNode->nodeValue);
        }
    }

    $root = $doc->getElementById('pc-checklist-root');
    if (!$root instanceof DOMElement) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return preg_replace('/\[\s*\]\s*/u', '', $html) ?? $html;
    }

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= (string)$doc->saveHTML($child);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($prevUseInternal);
    return trim($out);
};
?>
<style>
.blog-enterprise {
    max-width: 1180px;
    margin: 0 auto;
    padding: 18px 16px 36px;
    box-sizing: border-box;
    color: #e7f2ff;
    font-family: "Space Grotesk", "Segoe UI", Arial, sans-serif;
    --blog-enterprise-cols: <?= $isRu ? '2' : '4' ?>;
}
.blog-enterprise-hero {
    border: 1px solid #304966;
    border-radius: 20px;
    padding: 18px;
    background: linear-gradient(145deg, #122740, #0f1f33);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.blog-enterprise-hero h1 { margin: 0; font-size: 34px; letter-spacing: .01em; }
.blog-enterprise-hero p { margin: 8px 0 0; color: #a8c0df; max-width: 78ch; line-height: 1.6; }
.blog-enterprise-hero .meta { color: #9db7d8; font-size: 14px; }
.blog-enterprise-alert { margin-top: 12px; border: 1px solid #765439; background: #34261a; color: #ffdcb9; border-radius: 10px; padding: 10px 12px; }
.blog-enterprise-selected {
    margin-top: 14px;
    border: 1px solid #324c6c;
    border-radius: 14px;
    background: linear-gradient(180deg, #13243a, #101d31);
    padding: 18px;
}
.blog-enterprise-selected-content {
    margin-top: 10px;
    color: #d4e5fa;
    line-height: 1.68;
}
.blog-enterprise-selected-intro {
    margin-top: 10px;
}
.blog-enterprise-selected-intro p {
    padding: 0 !important;
}
.blog-enterprise-selected-hero {
    margin-top: 12px;
}
.blog-enterprise-selected-hero img {
    width: 100%;
    max-height: none;
    object-fit: contain;
    height: auto;
    border-radius: 12px;
    border: 1px solid #35516f;
    display: block;
}
.blog-enterprise-selected-content h1,
.blog-enterprise-selected-content h2,
.blog-enterprise-selected-content h3,
.blog-enterprise-selected-content h4 {
    color: #e8f2ff;
    line-height: 1.25;
    margin: 18px 0 10px;
}
.blog-enterprise-selected-content p {
    margin: 0 0 13px;
    line-height: 1.74;
}
.blog-enterprise-selected-content > p,
.blog-enterprise-selected-content > ul,
.blog-enterprise-selected-content > ol,
.blog-enterprise-selected-content > blockquote,
.blog-enterprise-selected-content > table,
.blog-enterprise-selected-content > pre,
.blog-enterprise-selected-content > hr,
.blog-enterprise-selected-content > figure,
.blog-enterprise-selected-content > img,
.blog-enterprise-selected-content > div,
.blog-enterprise-selected-content > section {
    padding: 6px 29px;
    box-sizing: border-box;
}
.blog-enterprise-selected-content > p:empty,
.blog-enterprise-selected-content > div:empty,
.blog-enterprise-selected-content > section:empty {
    display: none;
}
.blog-enterprise-selected-content > ul li,
.blog-enterprise-selected-content > ol li {
    padding: 0;
}
.blog-enterprise-selected-content p strong {
    color: #ffffff;
}
.blog-enterprise-selected-content p a,
.blog-enterprise-selected-content li a,
.blog-enterprise-selected-content blockquote a,
.blog-enterprise-selected-content td a,
.blog-enterprise-selected-content th a {
    color: #d8e9ff;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #48678f;
    background: linear-gradient(180deg, rgba(28, 49, 74, .95), rgba(20, 37, 58, .95));
    padding: 1px 8px;
    font-weight: 600;
    line-height: 1.4;
    font-size: .95em;
    transition: color .18s ease, border-color .18s ease, background .18s ease, transform .18s ease;
    vertical-align: baseline;
}
.blog-enterprise-selected-content p a::after,
.blog-enterprise-selected-content li a::after,
.blog-enterprise-selected-content blockquote a::after,
.blog-enterprise-selected-content td a::after,
.blog-enterprise-selected-content th a::after {
    content: "↗";
    font-size: 11px;
    opacity: .82;
    transform: translateY(-1px);
}
.blog-enterprise-selected-content p a:hover,
.blog-enterprise-selected-content li a:hover,
.blog-enterprise-selected-content blockquote a:hover,
.blog-enterprise-selected-content td a:hover,
.blog-enterprise-selected-content th a:hover {
    color: #ffffff;
    border-color: #6f95c6;
    background: linear-gradient(180deg, rgba(33, 59, 90, .98), rgba(23, 43, 68, .98));
    transform: translateY(-1px);
}
.blog-enterprise-selected-content .related-links a {
    text-transform: lowercase;
}
.blog-enterprise-selected-content .related-links a::first-letter {
    text-transform: uppercase;
}
.blog-enterprise-selected-content ul,
.blog-enterprise-selected-content ol {
    margin: 0 0 14px;
    padding: 0;
}
.blog-enterprise-selected-content ul {
    padding-left: 29px;
}
.blog-enterprise-selected-content p + ul {
    padding-left: 50px;
}
.blog-enterprise-selected-content h2 + ul {
    padding-left: 29px;
}
.blog-enterprise-selected-content ul {
    list-style: none;
}
.blog-enterprise-selected-content ul li,
.blog-enterprise-selected-content ol li {
    margin: 0 0 8px;
    line-height: 1.62;
}
.blog-enterprise-selected-content ul li {
    position: relative;
    padding-left: 22px;
}
.blog-enterprise-selected-content ul li::before {
    content: "◆";
    position: absolute;
    left: 0;
    top: 0;
    color: #7ba5ff;
    font-size: 11px;
}
.blog-enterprise-selected-content ol {
    padding-left: 74px;
}
.blog-enterprise-selected-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    border: 1px solid #35516f;
}
.blog-enterprise-selected-content blockquote {
    margin: 0 0 14px;
    padding: 12px 14px 12px 16px;
    border-left: 3px solid #4f75a7;
    background: #122844;
    color: #b7cde8;
    border-radius: 6px;
    position: relative;
}
.blog-enterprise-selected-content blockquote::before {
    content: "“";
    position: absolute;
    left: 8px;
    top: -4px;
    font-size: 28px;
    color: #6f94c5;
    line-height: 1;
}
.blog-enterprise-selected-content table {
    width: 100%;
    max-width: 100%;
    display: block;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-collapse: collapse;
    margin: 12px 0;
    background: #0f1f33;
    border: 1px solid #35516f;
}
.blog-enterprise-selected-content thead,
.blog-enterprise-selected-content tbody,
.blog-enterprise-selected-content tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}
.blog-enterprise-selected-content th,
.blog-enterprise-selected-content td {
    border: 1px solid #35516f;
    padding: 8px 10px;
    vertical-align: top;
    min-width: 140px;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.blog-enterprise-selected-content th {
    background: #162e4a;
    color: #e8f2ff;
    font-weight: 700;
}
.blog-enterprise-selected-content code {
    background: #152b45;
    color: #cfe6ff;
    padding: 2px 6px;
    border-radius: 5px;
}
.blog-enterprise-selected-content pre {
    margin: 0 0 14px;
    padding: 12px;
    background: #081422;
    color: #dcecff;
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #2f4a69;
}
.blog-enterprise-selected-content hr {
    border: 0;
    border-top: 1px solid #2d4665;
    margin: 18px 0;
}
.blog-enterprise-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    padding: 6px 10px;
    border: 1px solid #45648a;
    border-radius: 999px;
    color: #bfe0ff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}
.blog-enterprise-back:hover { background: rgba(117,170,230,.12); }
.blog-enterprise-groups {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.blog-enterprise-groups a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #3f5f84;
    border-radius: 999px;
    padding: 7px 11px;
    text-decoration: none;
    color: #c5dbf8;
    background: #102138;
    font-size: 13px;
}
.blog-enterprise-groups a.active {
    background: linear-gradient(135deg, #2ec5a7, #7ba5ff);
    color: #08131f;
    border-color: transparent;
}
.blog-enterprise-groups small { opacity: .85; }
.blog-enterprise-grid {
    margin-top: 14px;
    display: grid;
    grid-template-columns: repeat(var(--blog-enterprise-cols), minmax(0, 1fr));
    gap: 12px;
}
.blog-enterprise-card {
    border: 1px solid #314a69;
    border-radius: 14px;
    overflow: hidden;
    background: linear-gradient(180deg, #12243a, #101d31);
    display: flex;
    flex-direction: column;
}
.blog-enterprise-image { width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block; background: #1d3352; }
.blog-enterprise-image-link { display: block; }
.blog-enterprise-body { padding: 14px; display: flex; flex-direction: column; gap: 8px; height: 100%; }
.blog-enterprise-title { margin: 0; font-size: 21px; }
.blog-enterprise-title a {
    color: inherit;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.blog-enterprise-title a:hover { text-decoration: underline; }
.blog-enterprise-date { color: #9fb8d8; font-size: 13px; }
.blog-enterprise-excerpt {
    margin: 0;
    color: #c1d3ea;
    line-height: 1.55;
    min-height: 4.65em;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.blog-enterprise-link {
    margin-top: auto;
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border: 1px solid #45648a;
    border-radius: 999px;
    color: #bfe0ff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}
.blog-enterprise-link:hover { background: rgba(117,170,230,.12); }
.blog-enterprise-empty {
    margin-top: 14px;
    border: 1px solid #324c6c;
    border-radius: 12px;
    padding: 12px;
    color: #b9cbe2;
    background: #112238;
}
.blog-enterprise-pagination {
    margin-top: 16px;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}
.blog-enterprise-pagination a,
.blog-enterprise-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    border: 1px solid #3f5f84;
    border-radius: 8px;
    min-width: 36px;
    min-height: 34px;
    text-align: center;
    padding: 7px 10px;
    text-decoration: none;
    color: #c5dbf8;
    background: #102138;
    background-image: none;
    box-shadow: none;
    overflow: hidden;
}
.blog-enterprise-pagination .active { background: linear-gradient(135deg, #2ec5a7, #7ba5ff); color: #08131f; border-color: transparent; }
.blog-enterprise-pagination .disabled { opacity: .45; pointer-events: none; }
@media (max-width: 1100px) {
    .blog-enterprise-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 760px) {
    .blog-enterprise { padding: 14px 12px 24px; }
    .blog-enterprise-hero { flex-direction: column; align-items: flex-start; }
    .blog-enterprise-hero h1 { font-size: 28px; }
    .blog-enterprise-hero p { font-size: 14px; line-height: 1.5; }
    .blog-enterprise-grid { grid-template-columns: 1fr; }
    .blog-enterprise-body { padding: 11px; }
    .blog-enterprise-title { font-size: 18px; }
    .blog-enterprise-date { font-size: 12px; }
    .blog-enterprise-excerpt { font-size: 14px; line-height: 1.48; min-height: 4.4em; }
    .blog-enterprise-link { font-size: 12px; padding: 7px 10px; }
    .blog-enterprise-selected-content > p,
    .blog-enterprise-selected-content > ul,
    .blog-enterprise-selected-content > ol,
    .blog-enterprise-selected-content > blockquote,
    .blog-enterprise-selected-content > table,
    .blog-enterprise-selected-content > pre,
    .blog-enterprise-selected-content > hr,
    .blog-enterprise-selected-content > figure,
    .blog-enterprise-selected-content > img,
    .blog-enterprise-selected-content > div,
    .blog-enterprise-selected-content > section { padding: 4px 14px; }
    .blog-enterprise-selected-content ul { padding-left: 14px; }
    .blog-enterprise-selected-content p + ul { padding-left: 24px; }
    .blog-enterprise-selected-content h2 + ul { padding-left: 14px; }
    .blog-enterprise-selected-content ol { padding-left: 34px; }
    .blog-enterprise-selected-content table { margin-left: 14px; padding: 4px; width: calc(100% - 14px); }
    .blog-enterprise-selected-content p,
    .blog-enterprise-selected-content li,
    .blog-enterprise-selected-content td,
    .blog-enterprise-selected-content th { font-size: 15px; line-height: 1.58; }
    .blog-enterprise-selected-content th,
    .blog-enterprise-selected-content td { min-width: 120px; font-size: 13px; }
}
</style>

<section class="blog-enterprise">
    <div class="blog-enterprise-hero">
        <div>
            <h1><?= htmlspecialchars($selected !== null ? ((string)($selected['title'] ?? ($isRu ? '������' : 'Article'))) : $title, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($selected === null): ?>
                <p><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
        <?php if ($selected === null): ?>
        <div class="meta"><?= (int)$total ?> <?= $isRu ? 'статей' : 'articles' ?></div>
        <?php endif; ?>
    </div>


    <?php if ($err !== ''): ?>
        <div class="blog-enterprise-alert"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($selected === null && !empty($clusters)): ?>
        <div class="blog-enterprise-groups">
            <a class="<?= $currentCluster === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildClusterLink(''), ENT_QUOTES, 'UTF-8') ?>">
                <?= $isRu ? '&#1042;&#1089;&#1077; &#1088;&#1072;&#1079;&#1076;&#1077;&#1083;&#1099;' : 'All clusters' ?>
            </a>
            <?php foreach ($clusters as $group): ?>
                <?php $code = trim((string)($group['code'] ?? '')); ?>
                <?php if ($code === '') { continue; } ?>
                <a class="<?= $currentCluster === $code ? 'active' : '' ?>" href="<?= htmlspecialchars($buildClusterLink($code), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars((string)($group['label'] ?? $code), ENT_QUOTES, 'UTF-8') ?>
                    <small><?= (int)($group['count'] ?? 0) ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <?php if ($selected !== null): ?>
        <?php
        $selectedContentHtml = (string)($selected['content_html'] ?? '');
        $selectedSplit = $splitContentByFirstH2($selectedContentHtml);
        $selectedIntroHtml = (string)($selectedSplit['intro'] ?? '');
        $selectedBodyHtml = (string)($selectedSplit['body'] ?? $selectedContentHtml);
        $selectedIntroHtml = $stripChecklistBrackets($selectedIntroHtml);
        $selectedBodyHtml = $stripChecklistBrackets($selectedBodyHtml);
        ?>
        <article class="blog-enterprise-selected">
            <a class="blog-enterprise-back" href="<?= htmlspecialchars($buildPageLink($page), ENT_QUOTES, 'UTF-8') ?>">
                <?= $isRu ? 'Назад к списку' : 'Back to list' ?>
            </a>

            <div class="blog-enterprise-date"><?= htmlspecialchars((string)($selected['published_at'] ?? $selected['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <?php if ($selectedIntroHtml !== ''): ?>
                <div class="blog-enterprise-selected-content blog-enterprise-selected-intro"><?= $selectedIntroHtml ?></div>
            <?php endif; ?>
            <?php if (trim((string)($selected['hero_image_src'] ?? '')) !== ''): ?>
                <div class="blog-enterprise-selected-hero">
                    <img src="<?= htmlspecialchars((string)$selected['hero_image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            <?php endif; ?>
            <div class="blog-enterprise-selected-content"><?= $selectedBodyHtml ?></div>
        </article>
    <?php endif; ?>

    <?php if ($selected !== null && !empty($relatedBlogItems)): ?>
        <h3><?= $isRu ? 'Другие статьи' : 'More posts' ?></h3>
        <div class="blog-enterprise-grid">
            <?php foreach ($relatedBlogItems as $item): ?>
                <?php
                $slug = (string)($item['slug'] ?? '');
                $articleLink = $buildArticleLink($slug, (string)($item['cluster_code'] ?? ''));
                $img = trim((string)($item['image_src'] ?? ''));
                $postTitle = (string)($item['title'] ?? '');
                $excerpt = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($item['excerpt_html'] ?? ''))));
                ?>
                <article class="blog-enterprise-card">
                    <?php if ($img !== ''): ?>
                        <a class="blog-enterprise-image-link" href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" class="blog-enterprise-image" alt="<?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?>">
                        </a>
                    <?php else: ?>
                        <a class="blog-enterprise-image-link" href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="blog-enterprise-image"></div>
                        </a>
                    <?php endif; ?>
                    <div class="blog-enterprise-body">
                        <h3 class="blog-enterprise-title">
                            <a href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></a>
                        </h3>
                        <div class="blog-enterprise-date"><?= htmlspecialchars((string)($item['published_at'] ?? $item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        <p class="blog-enterprise-excerpt"><?= htmlspecialchars($trimPreview($excerpt), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="blog-enterprise-link" href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>"><?= $isRu ? 'Читать дальше' : 'Continue reading' ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif ($selected === null && !empty($items)): ?>
        <div class="blog-enterprise-grid">
            <?php foreach ($items as $item): ?>
                <?php
                $slug = (string)($item['slug'] ?? '');
                $articleLink = $buildArticleLink($slug, (string)($item['cluster_code'] ?? ''));
                $img = trim((string)($item['image_src'] ?? ''));
                $postTitle = (string)($item['title'] ?? '');
                $excerpt = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($item['excerpt_html'] ?? ''))));
                ?>
                <article class="blog-enterprise-card">
                    <?php if ($img !== ''): ?>
                        <a class="blog-enterprise-image-link" href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" class="blog-enterprise-image" alt="<?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?>">
                        </a>
                    <?php else: ?>
                        <a class="blog-enterprise-image-link" href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="blog-enterprise-image"></div>
                        </a>
                    <?php endif; ?>
                    <div class="blog-enterprise-body">
                        <h3 class="blog-enterprise-title">
                            <a href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></a>
                        </h3>
                        <div class="blog-enterprise-date"><?= htmlspecialchars((string)($item['published_at'] ?? $item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        <p class="blog-enterprise-excerpt"><?= htmlspecialchars($trimPreview($excerpt), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="blog-enterprise-link" href="<?= htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8') ?>"><?= $isRu ? 'Читать дальше' : 'Continue reading' ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif ($selected === null): ?>
        <div class="blog-enterprise-empty"><?= htmlspecialchars($emptyText, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($selected === null && $totalPages > 1): ?>
        <nav class="blog-enterprise-pagination" aria-label="Blog pages">
            <?php if ($page <= 1): ?>
                <span class="disabled"><?= htmlspecialchars($prevText, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($buildPageLink(max(1, $page - 1)), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($prevText, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($buildPageLink($i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page >= $totalPages): ?>
                <span class="disabled"><?= htmlspecialchars($nextText, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($buildPageLink(min($totalPages, $page + 1)), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($nextText, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>

