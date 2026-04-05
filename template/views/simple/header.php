<?php
$hostForLang = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($hostForLang, ':') !== false) {
    $hostForLang = explode(':', $hostForLang, 2)[0];
}
$htmlLang = (bool)preg_match('/\.ru$/', $hostForLang) ? 'ru' : 'en';
$isRu = ($htmlLang === 'ru');
$logoRu = 'ЦПАЛНЯ';
$logoMain = $isRu ? $logoRu : 'CPALNYA';
$logoAria = $logoMain . ' portal';
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
$firstSegment = strtolower((string)(explode('/', trim($requestPath, '/'))[0] ?? ''));
$section = in_array($firstSegment, ['blog', 'services', 'projects', 'cases', 'offers', 'solutions', 'contact', 'audit'], true) ? $firstSegment : 'home';
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$faviconPrimary = $isRu ? '/favicon1.png' : '/favicon2.png';
$favicon32 = $faviconPrimary;
$favicon16 = $faviconPrimary;
$faviconIco = '/favicon.ico';
$faviconPrimaryFs = defined('DIR') ? (DIR . $faviconPrimary) : '';
$favicon32Fs = defined('DIR') ? (DIR . $favicon32) : '';
$faviconIcoFs = defined('DIR') ? (DIR . $faviconIco) : '';
$faviconVersion = (is_string($faviconPrimaryFs) && $faviconPrimaryFs !== '' && is_file($faviconPrimaryFs))
    ? (string)@filemtime($faviconPrimaryFs)
    : ((is_string($favicon32Fs) && $favicon32Fs !== '' && is_file($favicon32Fs)) ? (string)@filemtime($favicon32Fs) : '1');
$faviconIcoHref = ((is_string($faviconIcoFs) && $faviconIcoFs !== '' && is_file($faviconIcoFs)) ? $faviconIco : $favicon32)
    . '?v=' . $faviconVersion;

$titleHost = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost'));
if (strpos($titleHost, ':') !== false) {
    $titleHost = explode(':', $titleHost, 2)[0];
}
$titleHost = preg_replace('/^www\./', '', $titleHost);
$siteNameUpper = strtoupper($titleHost !== '' ? $titleHost : 'localhost');

$seoFallbacks = [
    'ru' => [
        'home' => [
            'title' => 'B2B-разработка сайтов, систем и digital-продуктов',
            'description' => 'Архитектура B2B-сайтов и цифровых систем: от стратегии и SEO до запуска и роста заявок.',
            'keywords' => 'B2B разработка сайтов, SEO для услуг, digital стратегия, продуктовая разработка, лидогенерация',
        ],
        'blog' => [
            'title' => 'Блог про B2B-маркетинг, SEO и архитектуру продуктов',
            'description' => 'Практические статьи про SEO, контент, архитектуру сайтов и рост B2B-продаж.',
            'keywords' => 'B2B блог, SEO статьи, маркетинг для услуг, архитектура сайта, рост заявок',
        ],
        'services' => [
            'title' => 'B2B-услуги: сайты, SaaS, Telegram-боты, API и консалтинг',
            'description' => 'Услуги для бизнеса: разработка сайтов и платформ, интеграции API, Telegram-боты и продуктовый консалтинг.',
            'keywords' => 'разработка сайтов B2B, SaaS разработка, Telegram боты, API интеграция, IT консалтинг',
        ],
        'projects' => [
            'title' => 'Проекты и кейсы с фокусом на выручку и рост',
            'description' => 'Портфолио проектов: что было сделано, какие решения приняты и какой бизнес-результат получен.',
            'keywords' => 'кейсы разработки, портфолио B2B, проекты сайтов, digital кейсы, результат внедрения',
        ],
        'contact' => [
            'title' => 'Контакты по проектам, услугам и B2B-задачам',
            'description' => 'Обсудим вашу задачу: сроки, архитектуру решения, SEO и коммерческий результат.',
            'keywords' => 'заказать сайт, B2B разработка под ключ, консультация по проекту, digital партнер',
        ],
    ],
    'en' => [
        'home' => [
            'title' => 'B2B websites, product systems and growth-focused delivery',
            'description' => 'Business-first architecture for websites and digital products: strategy, SEO, launch and measurable pipeline growth.',
            'keywords' => 'B2B website development, growth marketing, product architecture, SEO strategy, lead generation',
        ],
        'blog' => [
            'title' => 'B2B blog on SEO, product architecture and demand growth',
            'description' => 'Actionable insights on SEO, content systems, website architecture and B2B conversion growth.',
            'keywords' => 'B2B blog, SEO insights, product marketing, conversion strategy, growth content',
        ],
        'services' => [
            'title' => 'B2B services: websites, SaaS, Telegram bots, APIs and consulting',
            'description' => 'Delivery services for revenue teams: website and SaaS development, API integrations, Telegram bots and consulting.',
            'keywords' => 'B2B services, SaaS development, API integration, Telegram bot development, digital consulting',
        ],
        'projects' => [
            'title' => 'Products for geo intelligence, antifraud and AI automation',
            'description' => 'Product catalog with pricing, backend integrations and implementation details for geo intelligence, antifraud and AI-driven SEO workflows.',
            'keywords' => 'B2B products, geoip api, antifraud platform, AI SEO platform, backend integrations, product pricing',
        ],
        'contact' => [
            'title' => 'Contact for projects, services and B2B initiatives',
            'description' => 'Share your goal and constraints to get a practical scope, timeline and growth-oriented execution plan.',
            'keywords' => 'hire B2B developer, digital product partner, website project consultation, growth execution',
        ],
    ],
];

if (!isset($seoFallbacks['ru']['offers'])) {
    $seoFallbacks['ru']['offers'] = [
        'title' => 'Офферы по интеграции VPN/Proxy detection, GeoIP и anti-fraud сценариев',
        'description' => 'Прикладные офферы для внедрения проверки VPN/Proxy/TOR, маршрутизации лидов и compliance-контуров через geoip.space (apigeoip.ru).',
        'keywords' => 'оффер vpn detection, проверка proxy tor, geoip api, compliance трафика, интеграция bitrix wordpress opencart drupal joomla',
    ];
}
if (!isset($seoFallbacks['en']['offers'])) {
    $seoFallbacks['en']['offers'] = [
        'title' => 'Offers for VPN/Proxy detection, GeoIP and anti-fraud integrations',
        'description' => 'Implementation-ready offers for VPN/Proxy/TOR detection, lead routing and compliance traffic controls via geoip.space (apigeoip.ru).',
        'keywords' => 'vpn proxy detection offer, geoip api integration, anti fraud traffic filtering, compliance controls',
    ];
}

$langKey = $isRu ? 'ru' : 'en';
$fallback = $seoFallbacks[$langKey][$section] ?? $seoFallbacks[$langKey]['home'];
$dbTitle = trim((string)($ModelPage['title'] ?? ''));
$dbDescription = trim((string)($ModelPage['description'] ?? ''));
$dbKeywords = trim((string)($ModelPage['keywords'] ?? ''));
$dbCanonical = trim((string)($ModelPage['canonical'] ?? ''));
$dbRobots = trim((string)($ModelPage['robots'] ?? ''));

$hasCyrillic = static function (string $value): bool {
    return $value !== '' && (bool)preg_match('/\p{Cyrillic}/u', $value);
};
if (!$isRu) {
    if ($hasCyrillic($dbTitle)) {
        $dbTitle = '';
    }
    if ($hasCyrillic($dbDescription)) {
        $dbDescription = '';
    }
    if ($hasCyrillic($dbKeywords)) {
        $dbKeywords = '';
    }
}

$title = $dbTitle !== '' ? $dbTitle : (string)$fallback['title'];
$description = $dbDescription !== '' ? $dbDescription : (string)$fallback['description'];
$keywords = $dbKeywords !== '' ? $dbKeywords : (string)$fallback['keywords'];
$canonicalPath = $requestPath;
if ($canonicalPath !== '/' && substr($canonicalPath, -1) !== '/' && !preg_match('/\.[a-z0-9]{1,8}$/i', basename($canonicalPath))) {
    $canonicalPath .= '/';
}
$canonicalHost = ($titleHost !== '' ? $titleHost : 'localhost');
$canonical = $scheme . '://' . $canonicalHost . $canonicalPath;
$ruHost = $canonicalHost;
$enHost = $canonicalHost;
$selfLang = $isRu ? 'ru' : 'en';
$isEquivalentPath = in_array($canonicalPath, ['/', '/blog/', '/services/', '/projects/', '/cases/', '/offers/', '/contact/', '/audit/', '/privacy/', '/terms/'], true);
$hreflangLinks = [];
$hreflangLinks[] = ['lang' => $selfLang, 'href' => $canonical];
if ($isEquivalentPath) {
    $hreflangLinks[] = ['lang' => 'ru', 'href' => $scheme . '://' . $ruHost . $canonicalPath];
    $hreflangLinks[] = ['lang' => 'en', 'href' => $scheme . '://' . $enHost . $canonicalPath];
    $hreflangLinks[] = ['lang' => 'x-default', 'href' => $scheme . '://' . $ruHost . $canonicalPath];
} else {
    $hreflangLinks[] = ['lang' => 'x-default', 'href' => $canonical];
}
$hreflangSeen = [];
$hreflangFinal = [];
foreach ($hreflangLinks as $row) {
    $lang = strtolower((string)($row['lang'] ?? ''));
    $href = trim((string)($row['href'] ?? ''));
    if ($lang === '' || $href === '' || isset($hreflangSeen[$lang])) {
        continue;
    }
    $hreflangSeen[$lang] = true;
    $hreflangFinal[] = ['lang' => $lang, 'href' => $href];
}
$robots = $dbRobots !== '' ? $dbRobots : 'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1';
$ogType = trim((string)($ModelPage['og_type'] ?? '')) ?: 'website';
$ogTitle = trim((string)($ModelPage['og_title'] ?? '')) ?: $title;
$ogDescription = trim((string)($ModelPage['og_description'] ?? '')) ?: $description;
if (!$isRu) {
    if ($hasCyrillic($ogTitle)) {
        $ogTitle = $title;
    }
    if ($hasCyrillic($ogDescription)) {
        $ogDescription = $description;
    }
}
$ogImage = trim((string)($ModelPage['og_image'] ?? ''));
if ($ogImage === '') {
    $ogImage = $scheme . '://' . ($titleHost !== '' ? $titleHost : 'localhost') . $faviconPrimary;
}
$ogSiteName = trim((string)($ModelPage['og_site_name'] ?? '')) ?: ($titleHost !== '' ? $titleHost : 'localhost');
$twitterCard = trim((string)($ModelPage['twitter_card'] ?? '')) ?: 'summary_large_image';
$twitterSite = trim((string)($ModelPage['twitter_site'] ?? ''));
$twitterCreator = trim((string)($ModelPage['twitter_creator'] ?? ''));
$metaAuthor = trim((string)($ModelPage['article_author'] ?? $ModelPage['author'] ?? ''));
$articlePublishedTime = trim((string)($ModelPage['article_published_time'] ?? ''));
$articleModifiedTime = trim((string)($ModelPage['article_modified_time'] ?? ''));
$articleSection = trim((string)($ModelPage['article_section'] ?? ''));
$articleTags = $ModelPage['article_tags'] ?? [];
if (!is_array($articleTags)) {
    $articleTags = [];
}
$structuredData = $ModelPage['structured_data'] ?? [];
if (is_string($structuredData) && trim($structuredData) !== '') {
    $decodedStructured = json_decode($structuredData, true);
    $structuredData = is_array($decodedStructured) ? $decodedStructured : [];
}
if (!is_array($structuredData)) {
    $structuredData = [];
}
if (!empty($structuredData) && array_key_exists('@context', $structuredData)) {
    $structuredData = [$structuredData];
}

if ($titleHost !== '' && stripos($title, $titleHost) === false) {
    $title .= ' | ' . $siteNameUpper;
}
$googleTagCode = trim((string)($_SERVER['MIRROR_GOOGLE_TAG_CODE'] ?? ''));
$yandexCounterCode = trim((string)($_SERVER['MIRROR_YANDEX_COUNTER_CODE'] ?? ''));
?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars($robots, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($canonical !== ''): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php foreach ($hreflangFinal as $alt): ?>
    <link rel="alternate" hreflang="<?= htmlspecialchars($alt['lang'], ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($alt['href'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($faviconIcoHref, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars($faviconIcoHref, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($favicon32 . '?v=' . $faviconVersion, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars($favicon16 . '?v=' . $faviconVersion, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($favicon32 . '?v=' . $faviconVersion, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($favicon32 . '?v=' . $faviconVersion, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta property="og:locale" content="<?= $isRu ? 'ru_RU' : 'en_US' ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($ogSiteName, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($articlePublishedTime !== ''): ?><meta property="article:published_time" content="<?= htmlspecialchars($articlePublishedTime, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php if ($articleModifiedTime !== ''): ?><meta property="article:modified_time" content="<?= htmlspecialchars($articleModifiedTime, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php if ($metaAuthor !== ''): ?><meta property="article:author" content="<?= htmlspecialchars($metaAuthor, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php if ($articleSection !== ''): ?><meta property="article:section" content="<?= htmlspecialchars($articleSection, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php foreach ($articleTags as $articleTag): ?>
    <?php $articleTag = trim((string)$articleTag); if ($articleTag === '') { continue; } ?>
    <meta property="article:tag" content="<?= htmlspecialchars($articleTag, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <meta name="twitter:card" content="<?= htmlspecialchars($twitterCard, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($metaAuthor !== ''): ?><meta name="author" content="<?= htmlspecialchars($metaAuthor, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php if ($twitterSite !== ''): ?><meta name="twitter:site" content="<?= htmlspecialchars($twitterSite, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php if ($twitterCreator !== ''): ?><meta name="twitter:creator" content="<?= htmlspecialchars($twitterCreator, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
    <?php foreach ($structuredData as $schemaRow): ?>
    <?php if (!is_array($schemaRow) || empty($schemaRow)) { continue; } ?>
    <script type="application/ld+json"><?= json_encode($schemaRow, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endforeach; ?>
    <style>
    html, body {
        background-color: var(--terrain-page-bg, #f4f7fb);
    }
    body {
        margin: 0;
        padding-left: 0;
        padding-top: var(--simple-header-height, 74px);
        font-family: Segoe UI, Arial, sans-serif;
        background: var(--terrain-page-bg-grad, #f4f7fb);
        color: #141d29;
        position: relative;
        overflow-x: hidden;
    }
    body.ui-tone-light {
        color: #141d29;
        background-color: #e7e5e0 !important;
        background-image: linear-gradient(180deg, #efefef 0%, #e7e5e0 100%) !important;
    }
    body.ui-tone-dark {
        background-color: #05070a !important;
        background-image: linear-gradient(180deg, #11151b 0%, #05070a 100%) !important;
    }
    #terrainFieldGlobal {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: .92;
    }
    body > *:not(#terrainFieldGlobal) {
        position: relative;
        z-index: 1;
    }
    .simple-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        background: #ffffff;
        border-bottom: 1px solid #dde5f0;
        position: fixed !important;
        left: 0;
        right: 0;
        top: 0;
        z-index: 9999 !important;
    }
    .simple-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        flex: 1 1 auto;
    }
    .simple-brand {
        white-space: nowrap;
    }
    .simple-site-check-slot {
        display: flex;
        align-items: center;
        min-width: 0;
        flex: 1 1 auto;
        margin-left: var(--nav-site-check-shift, 0px);
        transition: margin-left .52s cubic-bezier(.22,.61,.36,1);
        will-change: margin-left;
    }
    .simple-site-check-slot .nav-site-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        position: relative;
        max-width: min(100%, 560px);
    }
    .simple-site-check-slot .nav-site-check input {
        width: 320px;
        max-width: 46vw;
        border: 1px solid #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
        padding: 7px 9px;
        font: inherit;
        font-size: 13px;
    }
    .simple-site-check-slot .nav-site-check button {
        border: 1px solid #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
        padding: 7px 10px;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }
    .simple-site-check-slot .nav-site-check-hint {
        position: absolute;
        left: 0;
        top: calc(100% + 8px);
        z-index: 30;
        padding: 8px 10px;
        border: 1px solid #c8d7ea;
        background: #ffffff;
        color: #2a4364;
        font-size: 12px;
        line-height: 1.35;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity .22s ease, transform .22s ease;
        white-space: nowrap;
        box-shadow: 0 8px 22px rgba(18, 42, 74, .14);
    }
    .simple-site-check-slot .nav-site-check.show-hint .nav-site-check-hint {
        opacity: 1;
        transform: translateY(0);
    }
    .pc-logo{
        position: relative;
        display: inline-block;
        line-height: 1;
        color: #0d223d;
    }
    .pc-logo-main{
        display: inline-block;
        font-weight: 900;
        font-size: 40px;
        letter-spacing: .02em;
    }
    .pc-logo-core{
        position: absolute;
        right: -45px;
        top: 13px;
        transform-origin: top right;
        font-weight: 700;
        font-size: 20px;
        letter-spacing: .08em;
        text-transform: lowercase;
        color: #667f9f;
    }
    .simple-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .simple-nav a {
        text-decoration: none;
        color: #324a6b;
        padding: 7px 10px;
        border-radius: 8px;
        font-size: 14px;
    }
    .simple-nav a:hover {
        background: #edf3fa;
        color: #14213d;
    }
    .simple-nav a.is-active {
        background: #dbeafe;
        color: #0f3b75;
        font-weight: 600;
    }
    .simple-nav .nav-lang-sep {
        width: 1px;
        height: 24px;
        background: #d7e3f2;
        margin: 0 2px 0 6px;
    }
    .simple-nav .nav-lang-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 9px;
        border: 1px solid #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
        text-decoration: none;
    }
    body.ui-tone-light .simple-nav .nav-lang-link {
        padding: 7px 9px;
        border: 1px solid #d7e3f2;
        background: #f7fbff;
    }
    .simple-nav .nav-lang-link.is-active {
        border-color: #8fb7e8;
        background: #dbeafe;
        color: #0f3b75;
    }
    .simple-nav .nav-lang-flag {
        width: 16px;
        height: 12px;
        display: block;
        object-fit: cover;
    }
    .simple-nav .nav-lang-code {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
    }
    .simple-nav .nav-theme-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 42px;
        height: 32px;
        border: 1px solid #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
        cursor: pointer;
        padding: 0 8px;
    }
    .simple-nav .nav-site-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 2px;
        position: relative;
    }
    .simple-nav .nav-site-check input {
        width: 230px;
        max-width: 32vw;
        border: 1px solid #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
        padding: 7px 9px;
        font: inherit;
        font-size: 13px;
    }
    .simple-nav .nav-site-check button {
        border: 1px solid #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
        padding: 7px 10px;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }
    .simple-nav .nav-site-check-hint {
        position: absolute;
        left: 0;
        top: calc(100% + 8px);
        z-index: 30;
        padding: 8px 10px;
        border: 1px solid #c8d7ea;
        background: #ffffff;
        color: #2a4364;
        font-size: 12px;
        line-height: 1.35;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity .22s ease, transform .22s ease;
        white-space: nowrap;
        box-shadow: 0 8px 22px rgba(18, 42, 74, .14);
    }
    .simple-nav .nav-site-check.show-hint .nav-site-check-hint {
        opacity: 1;
        transform: translateY(0);
    }
    .simple-nav .nav-site-check--drawer {
        display: none;
    }
    .simple-nav .nav-theme-toggle .theme-icon {
        font-size: 14px;
        line-height: 1;
    }
    body.ui-tone-dark .simple-nav .nav-theme-toggle {
        border-color: #3f5f84;
        background: #102138;
        color: #d7e7ff;
    }
    body.ui-tone-dark .theme-icon-sun { opacity: .45; }
    body.ui-tone-light .theme-icon-moon { opacity: .45; }
    .simple-nav-toggle {
        display: none;
        width: 44px;
        height: 44px;
        border: 1px solid #d3dfef;
        background: #ffffff;
        color: #1d3557;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-shadow: 0 4px 12px rgba(20, 45, 79, .12);
    }
    .simple-nav-toggle span {
        display: block;
        width: 22px;
        height: 2px;
        background: currentColor;
        margin: 3px 0;
        transition: transform .22s ease, opacity .2s ease;
    }
    .simple-nav-toggle[aria-expanded="true"] span:nth-child(1) {
        transform: translateY(5px) rotate(45deg);
    }
    .simple-nav-toggle[aria-expanded="true"] span:nth-child(2) {
        opacity: 0;
    }
    .simple-nav-toggle[aria-expanded="true"] span:nth-child(3) {
        transform: translateY(-5px) rotate(-45deg);
    }
    .simple-nav-backdrop {
        display: none;
    }
    body.ui-tone-dark {
        background: #08111d;
        color: #e7f2ff;
    }
    body.ui-tone-dark .simple-header {
        background: rgba(8, 16, 28, .94);
        border-bottom-color: #284261;
    }
    body.ui-tone-dark .simple-nav-toggle {
        border-color: #355272;
        background: #0f2138;
        color: #d7e7ff;
        box-shadow: 0 4px 14px rgba(3, 11, 22, .45);
    }
    body.ui-tone-dark .pc-logo { color: #e8f3ff; }
    body.ui-tone-dark .pc-logo-core { color: #8ba9d0; }
    body.ui-tone-dark .simple-nav a {
        color: #b8cbe6;
    }
    body.ui-tone-dark .simple-nav a:hover {
        background: #12243b;
        color: #e8f3ff;
    }
    body.ui-tone-dark .simple-nav a.is-active {
        background: #1d3a5e;
        color: #f4fbff;
    }
    body.ui-tone-dark .simple-nav .nav-lang-link {
        padding: 7px 9px;
        border: 1px solid #3f5f84;
        background: #102138;
        color: #c5dbf8;
    }
    body.ui-tone-dark .simple-nav .nav-site-check input,
    body.ui-tone-dark .simple-nav .nav-site-check button {
        border-color: #3f5f84;
        background: #102138;
        color: #c5dbf8;
    }
    body.ui-tone-dark .simple-site-check-slot .nav-site-check input,
    body.ui-tone-dark .simple-site-check-slot .nav-site-check button {
        border-color: #3f5f84;
        background: #102138;
        color: #c5dbf8;
    }
    body.ui-tone-dark .simple-nav .nav-site-check-hint {
        border-color: #3f5f84;
        background: #102138;
        color: #c5dbf8;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .28);
    }
    body.ui-tone-dark .simple-site-check-slot .nav-site-check-hint {
        border-color: #3f5f84;
        background: #102138;
        color: #c5dbf8;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .28);
    }
    body.ui-tone-dark .simple-nav .nav-lang-sep {
        background: #3f5f84;
    }
    body.ui-tone-dark section[class*="-simple"],
    body.ui-tone-dark article[class*="-simple"],
    body.ui-tone-dark div[class*="-simple"] {
        color: #d4e5fa;
        border-color: #324c6c !important;
    }
    body.ui-tone-dark [class*="-simple-hero"],
    body.ui-tone-dark [class*="-simple-card"],
    body.ui-tone-dark [class*="-simple-selected"],
    body.ui-tone-dark [class*="-simple-empty"] {
        background: linear-gradient(180deg, #13243a, #101d31) !important;
    }
    body.ui-tone-dark [class*="-simple-title"] a { color: #e8f2ff !important; }
    body.ui-tone-dark [class*="-simple"] h2,
    body.ui-tone-dark [class*="-simple"] h3 { color: #e8f2ff !important; }
    body.ui-tone-dark [class*="-simple-date"],
    body.ui-tone-dark [class*="-simple-excerpt"],
    body.ui-tone-dark [class*="-simple-hero"] p,
    body.ui-tone-dark [class*="-simple-hero"] .meta { color: #9db7d8 !important; }
    body.ui-tone-dark [class*="-simple-back"],
    body.ui-tone-dark [class*="-simple-link"],
    body.ui-tone-dark [class*="-simple-pagination"] a,
    body.ui-tone-dark [class*="-simple-pagination"] span {
        background: #102138 !important;
        color: #c5dbf8 !important;
        border-color: #3f5f84 !important;
    }
    body.ui-tone-dark .services-simple-groups a {
        border-color: #3f5f84 !important;
        color: #c5dbf8 !important;
        background: #102138 !important;
    }
    body.ui-tone-dark .services-simple-groups a.active {
        background: linear-gradient(135deg, #2ec5a7, #7ba5ff) !important;
        color: #08131f !important;
        border-color: transparent !important;
    }
    body.ui-tone-dark .projects-simple-summary div {
        background: linear-gradient(160deg, rgba(45, 76, 112, .45), rgba(26, 53, 86, .45)) !important;
    }
    body.ui-tone-dark .projects-simple-sections article,
    body.ui-tone-dark .projects-simple-detail-grid article,
    body.ui-tone-dark .projects-simple-detail-copy article {
        background: linear-gradient(180deg, #13243a, #101d31) !important;
        border-color: #324c6c !important;
    }
    body.ui-tone-dark .projects-simple-sections h4,
    body.ui-tone-dark .projects-simple-detail-grid h4,
    body.ui-tone-dark .projects-simple-detail-copy h3 {
        color: #e8f2ff !important;
    }
    body.ui-tone-dark .projects-simple-summary span,
    body.ui-tone-dark .projects-simple-excerpt,
    body.ui-tone-dark .projects-simple-sections p,
    body.ui-tone-dark .projects-simple-detail-copy div,
    body.ui-tone-dark .projects-simple-detail-grid p {
        color: #c7dcf7 !important;
    }
    body.ui-tone-dark .blog-simple-breadcrumbs {
        color: #9db7d8 !important;
    }
    body.ui-tone-dark .blog-simple-breadcrumbs a {
        color: #d2e6ff !important;
        border-color: #45648a !important;
        background: #102138 !important;
    }
    body.ui-tone-dark .blog-simple-breadcrumbs .current {
        color: #e8f2ff !important;
    }
    body.ui-tone-dark .blog-simple-selected-hero img {
        border: none !important;
    }
    body.ui-tone-dark .contact-simple-copy,
    body.ui-tone-dark .contact-simple-note {
        background: linear-gradient(180deg, #13243a, #101d31) !important;
        border-color: #324c6c !important;
        color: #d4e5fa !important;
    }
    body.ui-tone-dark .contact-simple-copy p {
        color: #c7dcf7 !important;
    }
    body.ui-tone-dark .blog-simple-selected-content p a,
    body.ui-tone-dark .blog-simple-selected-content li a,
    body.ui-tone-dark .blog-simple-selected-content blockquote a,
    body.ui-tone-dark .blog-simple-selected-content td a,
    body.ui-tone-dark .blog-simple-selected-content th a {
        color: #d8e9ff !important;
        border-color: #48678f !important;
        background: linear-gradient(180deg, rgba(28, 49, 74, .95), rgba(20, 37, 58, .95)) !important;
    }
    body.ui-tone-dark .blog-simple-selected-content p a:hover,
    body.ui-tone-dark .blog-simple-selected-content li a:hover,
    body.ui-tone-dark .blog-simple-selected-content blockquote a:hover,
    body.ui-tone-dark .blog-simple-selected-content td a:hover,
    body.ui-tone-dark .blog-simple-selected-content th a:hover {
        color: #ffffff !important;
        border-color: #6f95c6 !important;
        background: linear-gradient(180deg, rgba(33, 59, 90, .98), rgba(23, 43, 68, .98)) !important;
    }
    @media (min-width: 641px) {
        .blog-simple-selected-content > p,
        .blog-simple-selected-content > ul,
        .blog-simple-selected-content > ol,
        .blog-simple-selected-content > blockquote,
        .blog-simple-selected-content > table,
        .blog-simple-selected-content > pre,
        .blog-simple-selected-content > hr,
        .blog-simple-selected-content > figure,
        .blog-simple-selected-content > img,
        .blog-simple-selected-content > div,
        .blog-simple-selected-content > section { padding: 6px 29px !important; box-sizing: border-box; }
        .blog-simple-selected-content ul { padding-left: 29px !important; }
        .blog-simple-selected-content p + ul { padding-left: 50px !important; }
        .blog-simple-selected-content h2 + ul { padding-left: 29px !important; }
        .blog-simple-selected-content ol { padding-left: 74px !important; }
        .blog-simple-selected-content table { margin-left: 29px !important; padding: 5px !important; width: calc(100% - 29px) !important; }
    }
    body.simple-nav-open {
        overflow: hidden;
    }
    @media (max-width: 900px) {
        .simple-header-left {
            flex: 1 1 auto;
            min-width: 0;
        }
        .simple-site-check-slot {
            display: none;
        }
        .simple-nav-toggle {
            display: inline-flex;
            z-index: 2210;
        }
        .simple-nav-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(9, 19, 34, .45);
            opacity: 0;
            pointer-events: none;
            transition: opacity .22s ease;
            z-index: 2190;
        }
        body.simple-nav-open .simple-nav-backdrop {
            opacity: 1;
            pointer-events: auto;
        }
        .simple-nav {
            position: fixed;
            top: 0;
            right: 0;
            width: min(320px, 88vw);
            height: 100dvh;
            background: #ffffff;
            border-left: 1px solid #d9e4f2;
            box-shadow: -18px 0 32px rgba(22, 42, 70, .18);
            padding: 84px 18px 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            transform: translateX(104%);
            transition: transform .25s cubic-bezier(.2,.7,.2,1);
            z-index: 2200;
            overflow-y: auto;
        }
        body.ui-tone-dark .simple-nav {
            background: #0d1b2f;
            border-left-color: #355272;
            box-shadow: -18px 0 36px rgba(2, 9, 18, .7);
        }
        .simple-nav a {
            display: block;
            font-size: 16px;
            padding: 12px 10px;
            border: 1px solid #deebf8;
            background: #f8fbff;
        }
        body.ui-tone-dark .simple-nav a {
            border-color: #3f5f84;
            background: #102138;
            color: #d7e7ff;
        }
        body.ui-tone-dark .simple-nav a:hover {
            background: #163050;
            color: #f2f8ff;
        }
        body.ui-tone-dark .simple-nav a.is-active {
            background: #22466f;
            color: #ffffff;
            border-color: #6f95c6;
        }
        .simple-nav .nav-lang-sep {
            width: 100%;
            height: 1px;
            margin: 4px 0;
            background: #d7e3f2;
        }
        body.ui-tone-dark .simple-nav .nav-lang-sep {
            background: #3f5f84;
        }
        .simple-nav .nav-lang-link {
            justify-content: flex-start;
            width: 100%;
            font-size: 14px;
            padding: 10px 12px;
        }
        .simple-nav .nav-theme-toggle {
            width: 100%;
            justify-content: flex-start;
            min-height: 40px;
        }
        .simple-nav .nav-site-check {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }
        .simple-nav .nav-site-check--drawer {
            display: flex;
        }
        .simple-nav .nav-site-check input {
            width: 100%;
            max-width: 100%;
            min-height: 42px;
            font-size: 14px;
        }
        body.ui-tone-dark .simple-nav .nav-site-check input,
        body.ui-tone-dark .simple-nav .nav-site-check button,
        body.ui-tone-dark .simple-nav .nav-theme-toggle {
            border-color: #3f5f84;
            background: #102138;
            color: #d7e7ff;
        }
        body.ui-tone-dark .simple-nav .nav-site-check-hint {
            border-color: #3f5f84;
            background: #102138;
            color: #c5dbf8;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
        }
        .simple-nav .nav-site-check button {
            width: 100%;
            min-height: 42px;
            font-size: 14px;
        }
        .simple-nav .nav-site-check-hint {
            position: static;
            white-space: normal;
            margin-top: 2px;
            transform: none;
            opacity: 1;
            display: none;
        }
        .simple-nav .nav-site-check.show-hint .nav-site-check-hint {
            display: block;
        }
        body.simple-nav-open .simple-nav {
            transform: translateX(0);
        }
    }
    body *,
    body *::before,
    body *::after {
        border-radius: 0 !important;
    }
    </style>
    <?php if ($googleTagCode !== ''): ?>
    <?= $googleTagCode . PHP_EOL ?>
    <?php endif; ?>
</head>
<body data-terrain-theme="simple">
<?php if ($yandexCounterCode !== ''): ?>
<?= $yandexCounterCode . PHP_EOL ?>
<?php endif; ?>
<canvas id="terrainFieldGlobal" aria-hidden="true"></canvas>
<header class="simple-header">
    <div class="simple-header-left">
        <div class="simple-brand" aria-label="<?= htmlspecialchars($logoAria, ENT_QUOTES, 'UTF-8') ?>">
            <span class="pc-logo">
                <span class="pc-logo-main"><?= htmlspecialchars($logoMain, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="pc-logo-core">core</span>
            </span>
        </div>
        <div class="simple-site-check-slot" aria-label="<?= htmlspecialchars($isRu ? 'Проверка сайта' : 'Site check', ENT_QUOTES, 'UTF-8') ?>">
            <form class="nav-site-check nav-site-check--header" method="get" action="/audit/">
                <input type="text" name="site" placeholder="<?= htmlspecialchars($isRu ? 'Проверь свой сайт' : 'Check your website', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" required>
                <button type="submit"><?= htmlspecialchars($isRu ? 'Проверить сайт' : 'Check site', ENT_QUOTES, 'UTF-8') ?></button>
                <div class="nav-site-check-hint" aria-hidden="true">
                    <?= htmlspecialchars($isRu ? 'Формат: https://tvoydomen.ru' : 'Format: https://yourdomain.com', ENT_QUOTES, 'UTF-8') ?>
                </div>
            </form>
        </div>
    </div>
    <button class="simple-nav-toggle" type="button" aria-expanded="false" aria-controls="simple-nav-drawer" aria-label="<?= htmlspecialchars($isRu ? 'Открыть меню' : 'Open menu', ENT_QUOTES, 'UTF-8') ?>">
        <span></span><span></span><span></span>
    </button>
    <div class="simple-nav-backdrop" data-simple-nav-close></div>
    <nav class="simple-nav" id="simple-nav-drawer">
        <?php include DIR . '/template/views/simple/nav.php'; ?>
    </nav>
</header>
<script>
(function () {
    var btn = document.querySelector('.simple-nav-toggle');
    var nav = document.getElementById('simple-nav-drawer');
    var backdrop = document.querySelector('.simple-nav-backdrop');
    if (!btn || !nav) { return; }
    var touchStartX = null;
    var touchStartY = null;

    function closeMenu() {
        document.body.classList.remove('simple-nav-open');
        btn.setAttribute('aria-expanded', 'false');
    }
    function openMenu() {
        document.body.classList.add('simple-nav-open');
        btn.setAttribute('aria-expanded', 'true');
    }
    function toggleMenu() {
        if (document.body.classList.contains('simple-nav-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    btn.addEventListener('click', toggleMenu);
    if (backdrop) {
        backdrop.addEventListener('click', closeMenu);
    }
    nav.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (link) {
            closeMenu();
        }
    });
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) {
            closeMenu();
        }
    });
    document.addEventListener('touchstart', function (e) {
        if (!document.body.classList.contains('simple-nav-open') || window.innerWidth > 900) { return; }
        if (!e.touches || !e.touches.length) { return; }
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });
    document.addEventListener('touchend', function (e) {
        if (!document.body.classList.contains('simple-nav-open') || window.innerWidth > 900) { return; }
        if (touchStartX === null || touchStartY === null) { return; }
        if (!e.changedTouches || !e.changedTouches.length) {
            touchStartX = null;
            touchStartY = null;
            return;
        }
        var dx = e.changedTouches[0].clientX - touchStartX;
        var dy = e.changedTouches[0].clientY - touchStartY;
        touchStartX = null;
        touchStartY = null;
        if (dx > 70 && Math.abs(dy) < 56) {
            closeMenu();
        }
    }, { passive: true });
})();
</script>
<script>
(function () {
    var slot = document.querySelector('.simple-site-check-slot');
    if (!slot) { return; }
    var storageKey = 'pc_site_check_shift_simple_' + (window.location.host || 'default');
    var introAnimated = false;

    function applyShift(px) {
        var normalized = Math.max(0, Math.round(Number(px) || 0));
        slot.style.setProperty('--nav-site-check-shift', normalized + 'px');
    }

    function readCachedShift() {
        try {
            return parseInt(localStorage.getItem(storageKey) || '', 10);
        } catch (e) {
            return NaN;
        }
    }

    function writeCachedShift(px) {
        try {
            localStorage.setItem(storageKey, String(Math.max(0, Math.round(Number(px) || 0))));
        } catch (e) {}
    }

    function pickAnchor() {
        var selectors = [
            '.audit-wrap',
            '.services-simple',
            '.services-enterprise',
            '.products-page',
            '.projects-simple',
            '.projects-enterprise',
            '.cases-simple',
            '.ofr-page',
            '.blog-simple',
            '.blog-enterprise',
            '.contact-simple',
            '.contact-enterprise',
            '.pc-main',
            'main'
        ];
        for (var i = 0; i < selectors.length; i++) {
            var el = document.querySelector(selectors[i]);
            if (el) { return el; }
        }
        return null;
    }

    function alignSiteCheckSlot() {
        if (window.innerWidth <= 900) {
            applyShift(0);
            writeCachedShift(0);
            return;
        }
        var anchor = pickAnchor();
        var logo = document.querySelector('.simple-brand');
        if (!anchor || !logo) {
            applyShift(0);
            return;
        }
        var targetLeft = anchor.getBoundingClientRect().left;
        var logoRight = logo.getBoundingClientRect().right;
        var shift = Math.max(0, Math.round(targetLeft - logoRight));

        var introStartShift = shift;
        var nav = document.querySelector('.simple-nav');
        if (nav) {
            var navLeft = nav.getBoundingClientRect().left;
            var slotWidth = slot.getBoundingClientRect().width || 0;
            var rightGap = 16;
            var desiredLeftNearMenu = navLeft - slotWidth - rightGap;
            var startByMenu = Math.max(shift, Math.round(desiredLeftNearMenu - logoRight));
            introStartShift = Math.max(startByMenu, shift + 220);
        } else {
            introStartShift = Math.max(shift + 220, 260);
        }

        if (!introAnimated) {
            introAnimated = true;
            applyShift(introStartShift);
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    applyShift(shift);
                });
            });
        } else {
            applyShift(shift);
        }
        writeCachedShift(shift);
    }

    var cachedShift = readCachedShift();
    if (!isNaN(cachedShift)) {
        applyShift(Math.max(cachedShift + 240, 280));
    }

    alignSiteCheckSlot();
    window.addEventListener('resize', alignSiteCheckSlot);
    window.addEventListener('load', alignSiteCheckSlot);
})();
</script>
<script>
(function () {
    var forms = document.querySelectorAll('.nav-site-check');
    if (!forms.length) return;
    var siteInputs = [];
    function normalizeSiteValue(raw) {
        var v = String(raw || '').trim();
        if (v === '') { return ''; }
        if (/^[a-z][a-z0-9+.-]*:\/\//i.test(v)) {
            return v;
        }
        if (/^\/\//.test(v)) {
            return 'https:' + v;
        }
        return 'https://' + v.replace(/^\/+/, '');
    }

    var hideTimer = null;
    function show(form) {
        form.classList.add('show-hint');
        if (hideTimer) {
            clearTimeout(hideTimer);
            hideTimer = null;
        }
    }
    function scheduleHide(form) {
        if (hideTimer) {
            clearTimeout(hideTimer);
        }
        hideTimer = setTimeout(function () {
            form.classList.remove('show-hint');
        }, 1800);
    }

    for (var i = 0; i < forms.length; i++) {
        (function (form) {
            var input = form.querySelector('input[name="site"]');
            var button = form.querySelector('button[type="submit"]');
            if (!input || !button) return;
            siteInputs.push(input);
            input.addEventListener('focus', function () { show(form); });
            input.addEventListener('click', function () { show(form); });
            input.addEventListener('blur', function () {
                input.value = normalizeSiteValue(input.value);
                scheduleHide(form);
            });
            input.addEventListener('paste', function () {
                setTimeout(function () {
                    input.value = normalizeSiteValue(input.value);
                }, 0);
            });
            button.addEventListener('click', function () {
                var v = (input.value || '').trim();
                if (v === '') {
                    show(form);
                    return;
                }
                input.value = normalizeSiteValue(v);
            });
            form.addEventListener('submit', function () {
                input.value = normalizeSiteValue(input.value);
            });
            })(forms[i]);
    }

    var placeholderPhrases = <?= json_encode(
        $isRu
            ? [
                'Проверь сайт на SEO и индексируемость',
                'Проверь сайт на уязвимости и риски',
                'Проверь сайт на технические ошибки',
                'Проверь сайт перед запуском рекламы',
                'Проверь сайт перед редизайном',
                'Проверь скорость и стабильность сайта',
                'Проверь сниппет и выдачу в Google',
                'Проверь robots.txt и sitemap.xml',
                'Проверь каноникалы и hreflang',
                'Проверь сайт и получи план исправлений',
            ]
            : [
                'Check your site for SEO and indexability',
                'Check your site for security risks',
                'Check your site for technical issues',
                'Check your site before paid traffic',
                'Check your site before redesign',
                'Check speed and response stability',
                'Check your Google snippet preview',
                'Check robots.txt and sitemap.xml',
                'Check canonical and hreflang setup',
                'Check your site and get fix priorities',
            ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) ?>;
    if (!siteInputs.length || !placeholderPhrases.length) return;

    var phraseIndex = 0;
    var charIndex = 0;
    var deleting = false;
    var started = false;

    function hasFocusedInput() {
        var active = document.activeElement;
        if (!active) return false;
        for (var i = 0; i < siteInputs.length; i++) {
            if (siteInputs[i] === active) return true;
        }
        return false;
    }

    function applyPlaceholder(text) {
        for (var i = 0; i < siteInputs.length; i++) {
            var input = siteInputs[i];
            if (!input) continue;
            if (input.value && input.value.trim() !== '') continue;
            if (document.activeElement === input) continue;
            input.setAttribute('placeholder', text);
        }
    }

    function nextTick() {
        if (!started) return;
        var current = String(placeholderPhrases[phraseIndex] || '');
        if (hasFocusedInput()) {
            setTimeout(nextTick, 320);
            return;
        }

        if (!deleting) {
            charIndex = Math.min(current.length, charIndex + 1);
            applyPlaceholder(current.slice(0, charIndex));
            if (charIndex >= current.length) {
                deleting = true;
                setTimeout(nextTick, 2100);
                return;
            }
            setTimeout(nextTick, 125);
            return;
        }

        charIndex = Math.max(0, charIndex - 1);
        applyPlaceholder(current.slice(0, charIndex));
        if (charIndex <= 0) {
            deleting = false;
            phraseIndex = (phraseIndex + 1) % placeholderPhrases.length;
            setTimeout(nextTick, 900);
            return;
        }
        setTimeout(nextTick, 78);
    }

    // Start later than hero typing and run slower.
    setTimeout(function () {
        started = true;
        nextTick();
    }, 2600);
})();
</script>
<script>
(function () {
    var toggles = document.querySelectorAll('[data-theme-toggle]');
    if (!toggles.length) return;
    var isRuLocale = <?= json_encode((bool)$isRu) ?>;
    var ruTimezone = 'Europe/Moscow';
    var enTimezone = 'America/New_York';
    var ruDayStartHour = 7;
    var ruDayEndHour = 21;
    var enDayStartHour = 7;
    var enDayEndHour = 21;
    function syncMainThemeClasses(tone) {
        var mains = document.querySelectorAll('.pc-main');
        for (var i = 0; i < mains.length; i++) {
            if (tone === 'dark') {
                mains[i].classList.add('pc-enterprise');
                mains[i].classList.remove('pc-simple');
            } else {
                mains[i].classList.add('pc-simple');
                mains[i].classList.remove('pc-enterprise');
            }
        }
    }

    function applyTone(tone, persist, animate) {
        tone = (tone === 'dark') ? 'dark' : 'light';
        document.body.classList.toggle('ui-tone-dark', tone === 'dark');
        document.body.classList.toggle('ui-tone-light', tone !== 'dark');
        var root = document.documentElement;
        if (tone === 'dark') {
            if (root) { root.style.backgroundColor = '#05070a'; }
            document.body.style.backgroundColor = '#05070a';
            document.body.style.backgroundImage = 'linear-gradient(180deg,#11151b 0%,#05070a 100%)';
        } else {
            if (root) { root.style.backgroundColor = '#e7e5e0'; }
            document.body.style.backgroundColor = '#e7e5e0';
            document.body.style.backgroundImage = 'linear-gradient(180deg,#efefef 0%,#e7e5e0 100%)';
        }
        syncMainThemeClasses(tone);
        var terrainTheme = (tone === 'dark') ? 'enterprise' : 'simple';
        document.body.setAttribute('data-terrain-theme', terrainTheme);
        if (persist) {
            try { localStorage.setItem('pc_ui_tone', tone); } catch (e) {}
        }
        for (var i = 0; i < toggles.length; i++) {
            toggles[i].setAttribute('aria-pressed', tone === 'dark' ? 'true' : 'false');
        }
        if (animate) {
            window.__terrainPendingTheme = terrainTheme;
            if (typeof window.__terrainApplyTheme === 'function') {
                window.__terrainApplyTheme(terrainTheme);
            } else {
                setTimeout(function () {
                    if (typeof window.__terrainApplyTheme === 'function') {
                        window.__terrainApplyTheme(terrainTheme);
                    }
                }, 120);
            }
        }
    }

    function currentTone() {
        return document.body.classList.contains('ui-tone-dark') ? 'dark' : 'light';
    }

    function getStoredTone() {
        try {
            var tone = localStorage.getItem('pc_ui_tone');
            if (tone === 'dark' || tone === 'light') {
                return tone;
            }
        } catch (e) {}
        return '';
    }

    function systemPrefersDark() {
        try {
            return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        } catch (e) {}
        return false;
    }

    function getHourInTimezone(tz) {
        try {
            if (typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
                var hh = new Intl.DateTimeFormat('en-US', {
                    hour: '2-digit',
                    hour12: false,
                    timeZone: tz
                }).format(new Date());
                var parsed = parseInt(hh, 10);
                if (!isNaN(parsed) && parsed >= 0 && parsed <= 23) {
                    return parsed;
                }
            }
        } catch (e) {}
        return (new Date()).getHours();
    }

    function isDayHour(hour, startHour, endHour) {
        if (startHour === endHour) {
            return true;
        }
        if (startHour < endHour) {
            return hour >= startHour && hour < endHour;
        }
        return hour >= startHour || hour < endHour;
    }

    function resolveTimeTone() {
        var hour = getHourInTimezone(isRuLocale ? ruTimezone : enTimezone);
        var day = isRuLocale
            ? isDayHour(hour, ruDayStartHour, ruDayEndHour)
            : isDayHour(hour, enDayStartHour, enDayEndHour);
        return day ? 'light' : 'dark';
    }

    function resolveInitialTone() {
        var stored = getStoredTone();
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
        if (systemPrefersDark()) {
            return 'dark';
        }
        return resolveTimeTone();
    }

    var initialTone = resolveInitialTone();
    applyTone(initialTone, false, false);

    // Header script is parsed before page main content; re-apply once DOM is ready.
    document.addEventListener('DOMContentLoaded', function () {
        applyTone(currentTone(), false, false);
    });
    window.addEventListener('load', function () {
        applyTone(currentTone(), false, false);
    });

    for (var t = 0; t < toggles.length; t++) {
        toggles[t].addEventListener('click', function () {
            var nextTone = document.body.classList.contains('ui-tone-dark') ? 'light' : 'dark';
            applyTone(nextTone, true, true);
        });
    }
})();
</script>
