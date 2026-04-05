<?php
$host = function_exists('public_portal_host') ? public_portal_host() : strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$lang = function_exists('public_portal_lang') ? public_portal_lang($host) : 'en';
$isRu = ($lang === 'ru');

$blogItems = [];
$featuredDownloads = [];
$featuredArticles = [];
$featuredProjects = [];
$featuredCases = [];

if (is_file(DIR . 'core/controls/examples/_common.php')) {
    require_once DIR . 'core/controls/examples/_common.php';
    if (function_exists('examples_fetch_published_list')) {
        $blogItems = examples_fetch_published_list($FRMWRK, $host, 6, $lang, '');
    }
}
if (function_exists('public_portal_fetch_solutions')) {
    $featuredDownloads = array_slice(public_portal_fetch_solutions($FRMWRK, $host, $lang, 'download'), 0, 3);
    $featuredArticles = array_slice(public_portal_fetch_solutions($FRMWRK, $host, $lang, 'article'), 0, 3);
}
if (function_exists('public_projects_fetch_published')) {
    $featuredProjects = array_slice((array)public_projects_fetch_published($FRMWRK, $host, $lang), 0, 3);
}
if (function_exists('public_cases_fetch_published')) {
    $featuredCases = array_slice((array)public_cases_fetch_published($FRMWRK, $host, $lang), 0, 3);
}

$ModelPage['home_portal'] = [
    'lang' => $lang,
    'blog_items' => $blogItems,
    'downloads' => $featuredDownloads,
    'solution_articles' => $featuredArticles,
    'projects' => $featuredProjects,
    'cases' => $featuredCases,
];

$ModelPage['title'] = $isRu ? 'CPALNYA - портал по CPA, арбитражу трафика и закулисью affiliate-рынка' : 'CPALNYA - CPA, affiliate traffic and behind-the-scenes performance portal';
$ModelPage['description'] = $isRu ? 'CPALNYA объединяет статьи, готовые решения, техничку, SEO-хабы и продуктовые воронки для арбитража трафика, affiliate-команд и медиабаинга.' : 'CPALNYA combines articles, ready-made solutions, technical guides, SEO hubs and product funnels for affiliate teams and media buying.';
$ModelPage['keywords'] = $isRu ? 'арбитраж трафика, CPA, affiliate marketing, кейсы, статьи, готовые решения, трекеры, медиабаинг, SEO' : 'affiliate marketing, CPA, traffic arbitrage, playbooks, ready-made solutions, media buying, trackers, SEO hub';
