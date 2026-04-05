<?php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$host = trim($host, '.');
if ($host === '') {
    $host = 'localhost';
}
$baseUrl = $scheme . '://' . $host;
$isRu = (bool)preg_match('/\.ru$/', $host);

$ModelPage = [
    'title' => $isRu
        ? 'B2B-разработка сайтов, систем и digital-продуктов'
        : 'B2B websites, product systems and growth-focused delivery',
    'description' => $isRu
        ? 'Архитектура B2B-сайтов и цифровых систем: от стратегии и SEO до запуска и роста заявок.'
        : 'Business-first architecture for websites and digital products: strategy, SEO, launch and measurable pipeline growth.',
    'keywords' => $isRu
        ? 'B2B разработка сайтов, SEO для услуг, digital стратегия, продуктовая разработка, лидогенерация'
        : 'B2B website development, growth marketing, product architecture, SEO strategy, lead generation',
    'canonical' => $baseUrl . '/',
    'sitemap' => $baseUrl . '/sitemap.xml',
    'robots' => 'index,follow',
    'og_type' => 'website',
    'og_image' => '',
    'og_site_name' => $host,
    'twitter_card' => 'summary',
    'twitter_site' => '',
    'twitter_creator' => '',
    'json_ld' => '',
];
