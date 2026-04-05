<?php
$hostForLang = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($hostForLang, ':') !== false) {
    $hostForLang = explode(':', $hostForLang, 2)[0];
}
$htmlLang = (bool)preg_match('/\.ru$/', $hostForLang) ? 'ru' : 'en';
$isRu = ($htmlLang === 'ru');
$logoRu = (string)json_decode('"\u041f\u041e\u0420\u0422"');
$logoMain = $isRu ? $logoRu : 'PORT';
$logoAria = ($isRu ? $logoRu : 'PORT') . ' core';
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
$firstSegment = strtolower((string)(explode('/', trim($requestPath, '/'))[0] ?? ''));
$section = in_array($firstSegment, ['blog', 'services', 'projects', 'cases', 'offers', 'contact', 'audit'], true) ? $firstSegment : 'home';
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

$brandHost = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'enterprise.local'));
if (strpos($brandHost, ':') !== false) {
    $brandHost = explode(':', $brandHost, 2)[0];
}
$brandHost = preg_replace('/^www\./', '', $brandHost);
$siteNameUpper = strtoupper($brandHost !== '' ? $brandHost : 'enterprise.local');

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
$canonicalHost = ($brandHost !== '' ? $brandHost : 'enterprise.local');
$canonical = $scheme . '://' . $canonicalHost . $canonicalPath;
$ruHost = 'portcore.ru';
$enHost = 'portcore.online';
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
    $ogImage = $scheme . '://' . ($brandHost !== '' ? $brandHost : 'enterprise.local') . $faviconPrimary;
}
$ogSiteName = trim((string)($ModelPage['og_site_name'] ?? '')) ?: ($brandHost !== '' ? $brandHost : 'enterprise.local');
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

if ($brandHost !== '' && stripos($title, $brandHost) === false) {
    $title .= ' | ' . $siteNameUpper;
}
$googleTagCode = trim((string)($_SERVER['MIRROR_GOOGLE_TAG_CODE'] ?? ''));
$yandexCounterCode = trim((string)($_SERVER['MIRROR_YANDEX_COUNTER_CODE'] ?? ''));
?>
<!doctype html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
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
    :root {
        --bg: #080f1b;
        --line: #223550;
        --line-2: #2e4a70;
        --text: #ecf4ff;
        --muted: #9cb2cf;
        --accent: #2ec5a7;
        --accent-2: #7ba5ff;
    }
    body {
        margin: 0;
        padding-left: 0;
        padding-top: var(--ent-header-height, 82px);
        background:
            radial-gradient(1200px 560px at 110% -20%, rgba(123,165,255,.28), transparent 65%),
            radial-gradient(1000px 500px at -20% 110%, rgba(46,197,167,.16), transparent 64%),
            var(--bg);
        color: var(--text);
        font-family: "Space Grotesk", "Segoe UI", Arial, sans-serif;
        position: relative;
        overflow-x: hidden;
    }
    #terrainFieldGlobal {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: .9;
        mix-blend-mode: screen;
    }
    body.ui-tone-light #terrainFieldGlobal {
        opacity: .52;
        mix-blend-mode: normal;
        filter: saturate(1.06) brightness(1.03);
    }
    body.ui-tone-dark #terrainFieldGlobal {
        opacity: .9;
        mix-blend-mode: screen;
        filter: none;
    }
    body > *:not(#terrainFieldGlobal) {
        position: relative;
        z-index: 1;
    }
    .ent-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        padding: 16px 24px;
        border-bottom: 1px solid #1a2a41;
        backdrop-filter: blur(10px);
        position: fixed !important;
        left: 0;
        right: 0;
        top: 0;
        z-index: 9999 !important;
        background: rgba(8, 15, 27, .86);
    }
    .ent-header-left {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1 1 auto;
    }
    .ent-brand-wrap {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .ent-site-check-slot {
        display: flex;
        align-items: center;
        min-width: 0;
        flex: 1 1 auto;
        margin-left: var(--nav-site-check-shift, 0px);
    }
    .ent-site-check-slot .nav-site-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        position: relative;
        max-width: min(100%, 560px);
    }
    .ent-site-check-slot .nav-site-check input {
        width: 340px;
        max-width: 40vw;
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .88);
        color: #d7e7ff;
        padding: 8px 10px;
        font: inherit;
        font-size: 13px;
    }
    .ent-site-check-slot .nav-site-check button {
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .88);
        color: #d7e7ff;
        padding: 8px 11px;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }
    .ent-site-check-slot .nav-site-check-hint {
        position: absolute;
        left: 0;
        top: calc(100% + 8px);
        z-index: 30;
        padding: 8px 10px;
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .98);
        color: #d7e7ff;
        font-size: 12px;
        line-height: 1.35;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity .22s ease, transform .22s ease;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .34);
    }
    .ent-site-check-slot .nav-site-check.show-hint .nav-site-check-hint {
        opacity: 1;
        transform: translateY(0);
    }
    .ent-brand {
        line-height: 1;
    }
    .pc-logo{
        position: relative;
        display: inline-block;
        line-height: 1;
        color: #e8f3ff;
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
        color: #8ba9d0;
    }
    .ent-nav {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .ent-nav a {
        color: var(--muted);
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        border: 1px solid #294264;
        border-radius: 999px;
        padding: 8px 13px;
        transition: .15s ease;
        background: rgba(16, 30, 49, .65);
    }
    .ent-nav a:hover {
        color: #dcfaff;
        border-color: #3d5f8d;
        background: rgba(30, 58, 96, .7);
    }
    .ent-nav a.is-active { color: #031d28; background: linear-gradient(130deg, var(--accent), var(--accent-2)); border-color: transparent; }
    .ent-nav .nav-lang-sep {
        width: 1px;
        height: 24px;
        background: #2a4467;
        margin: 0 4px 0 8px;
    }
    .ent-nav .nav-lang-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #c9daf2;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        border: 1px solid #325074;
        padding: 8px 11px;
        background: rgba(18, 34, 56, .88);
    }
    .ent-nav .nav-lang-link.is-active {
        border-color: #5e90d0;
        color: #f3fbff;
        background: linear-gradient(130deg, rgba(46,197,167,.24), rgba(123,165,255,.34));
    }
    .ent-nav .nav-lang-flag {
        width: 16px;
        height: 12px;
        display: block;
        object-fit: cover;
    }
    .ent-nav .nav-lang-code {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .1em;
    }
    .ent-nav .nav-theme-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 42px;
        height: 35px;
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .88);
        color: #d7e7ff;
        cursor: pointer;
        padding: 0 8px;
    }
    .ent-nav .nav-site-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 2px;
        position: relative;
    }
    .ent-nav .nav-site-check input {
        width: 240px;
        max-width: 28vw;
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .88);
        color: #d7e7ff;
        padding: 8px 10px;
        font: inherit;
        font-size: 13px;
    }
    .ent-nav .nav-site-check button {
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .88);
        color: #d7e7ff;
        padding: 8px 11px;
        font: inherit;
        font-size: 13px;
        cursor: pointer;
    }
    .ent-nav .nav-site-check-hint {
        position: absolute;
        left: 0;
        top: calc(100% + 8px);
        z-index: 30;
        padding: 8px 10px;
        border: 1px solid #325074;
        background: rgba(18, 34, 56, .98);
        color: #d7e7ff;
        font-size: 12px;
        line-height: 1.35;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity .22s ease, transform .22s ease;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .34);
    }
    .ent-nav .nav-site-check.show-hint .nav-site-check-hint {
        opacity: 1;
        transform: translateY(0);
    }
    .ent-nav .nav-site-check--drawer {
        display: none;
    }
    .ent-nav .nav-theme-toggle .theme-icon {
        font-size: 14px;
        line-height: 1;
    }
    body.ui-tone-dark .theme-icon-sun { opacity: .45; }
    body.ui-tone-light .theme-icon-moon { opacity: .45; }
    .ent-nav-toggle {
        display: none;
        width: 44px;
        height: 44px;
        border: 1px solid #2f496c;
        background: rgba(12, 24, 40, .88);
        color: #dcecff;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .28);
    }
    .ent-nav-toggle span {
        display: block;
        width: 22px;
        height: 2px;
        background: currentColor;
        margin: 3px 0;
        transition: transform .22s ease, opacity .2s ease;
    }
    .ent-nav-toggle[aria-expanded="true"] span:nth-child(1) {
        transform: translateY(5px) rotate(45deg);
    }
    .ent-nav-toggle[aria-expanded="true"] span:nth-child(2) {
        opacity: 0;
    }
    .ent-nav-toggle[aria-expanded="true"] span:nth-child(3) {
        transform: translateY(-5px) rotate(-45deg);
    }
    .ent-nav-backdrop {
        display: none;
    }
    body.ent-nav-open {
        overflow: hidden;
    }
    .ent-main {
        min-height: 62vh;
        padding-top: 14px;
        padding-bottom: 20px;
    }
    body.ui-tone-light {
        background: #ffffff;
        color: #141d29;
    }
    body.ui-tone-light .ent-header {
        background: rgba(255, 255, 255, .94);
        border-bottom-color: #dde5f0;
    }
    body.ui-tone-light .pc-logo { color: #0d223d; }
    body.ui-tone-light .pc-logo-core { color: #667f9f; }
    body.ui-tone-light .ent-nav a {
        color: #324a6b;
        border-color: #d7e3f2;
        background: #f7fbff;
    }
    body.ui-tone-light .ent-nav a:hover {
        background: #edf3fa;
        color: #14213d;
        border-color: #d0deef;
    }
    body.ui-tone-light .ent-nav a.is-active {
        background: #dbeafe;
        color: #0f3b75;
        border-color: #8fb7e8;
    }
    body.ui-tone-light .ent-nav .nav-theme-toggle {
        border-color: #d7e3f2;
        background: #f7fbff;
        color: #2a4364;
    }
    body.ui-tone-light .ent-nav .nav-site-check input,
    body.ui-tone-light .ent-nav .nav-site-check button {
        border-color: #d7e3f2;
        background: #f7fbff;
        color: #2f4d71;
    }
    body.ui-tone-light .ent-site-check-slot .nav-site-check input,
    body.ui-tone-light .ent-site-check-slot .nav-site-check button {
        border-color: #d7e3f2;
        background: #f7fbff;
        color: #2f4d71;
    }
    body.ui-tone-light .ent-nav .nav-site-check-hint {
        border-color: #d7e3f2;
        background: #ffffff;
        color: #2f4d71;
        box-shadow: 0 8px 22px rgba(32, 61, 98, .18);
    }
    body.ui-tone-light .ent-site-check-slot .nav-site-check-hint {
        border-color: #d7e3f2;
        background: #ffffff;
        color: #2f4d71;
        box-shadow: 0 8px 22px rgba(32, 61, 98, .18);
    }
    body.ui-tone-light .ent-nav .nav-lang-sep {
        height: 35px;
        background: #d7e3f2;
    }
    body.ui-tone-light section[class*="-enterprise"],
    body.ui-tone-light article[class*="-enterprise"],
    body.ui-tone-light div[class*="-enterprise"] {
        color: #244062;
        border-color: #d7e4f3 !important;
    }
    body.ui-tone-light [class*="-enterprise-hero"],
    body.ui-tone-light [class*="-enterprise-card"],
    body.ui-tone-light [class*="-enterprise-selected"],
    body.ui-tone-light [class*="-enterprise-empty"] {
        background: #ffffff !important;
    }
    body.ui-tone-light [class*="-enterprise-title"] a { color: #153a66 !important; }
    body.ui-tone-light [class*="-enterprise"] h2,
    body.ui-tone-light [class*="-enterprise"] h3 { color: #153a66 !important; }
    body.ui-tone-light [class*="-enterprise-date"],
    body.ui-tone-light [class*="-enterprise-excerpt"],
    body.ui-tone-light [class*="-enterprise-hero"] p,
    body.ui-tone-light [class*="-enterprise-hero"] .meta { color: #5b738f !important; }
    body.ui-tone-light [class*="-enterprise-link"],
    body.ui-tone-light [class*="-enterprise-back"],
    body.ui-tone-light [class*="-enterprise-pagination"] a,
    body.ui-tone-light [class*="-enterprise-pagination"] span {
        background: #f5f9ff !important;
        color: #0d5db7 !important;
        border-color: #bfd5ee !important;
    }
    body.ui-tone-light .services-enterprise-groups a {
        border-color: #bfd5ee !important;
        color: #0d5db7 !important;
        background: #f5f9ff !important;
    }
    body.ui-tone-light .services-enterprise-groups a.active {
        background: linear-gradient(135deg, #2ec5a7, #7ba5ff) !important;
        color: #08131f !important;
        border-color: transparent !important;
    }
    body.ui-tone-light .services-enterprise-groups small {
        color: #355477 !important;
    }
    body.ui-tone-light .projects-enterprise-summary div {
        background: linear-gradient(160deg, #f4f9ff, #ecf3ff) !important;
    }
    body.ui-tone-light .projects-enterprise-summary span,
    body.ui-tone-light .projects-enterprise-excerpt,
    body.ui-tone-light .projects-enterprise-sections p,
    body.ui-tone-light .projects-enterprise-detail-copy div,
    body.ui-tone-light .projects-enterprise-detail-grid p {
        color: #28466d !important;
    }
    body.ui-tone-light .projects-enterprise-sections article,
    body.ui-tone-light .projects-enterprise-detail-grid article,
    body.ui-tone-light .projects-enterprise-detail-copy article {
        background: #fbfdff !important;
        border-color: #e1ecf9 !important;
    }
    body.ui-tone-light .projects-enterprise-sections h4,
    body.ui-tone-light .projects-enterprise-detail-grid h4,
    body.ui-tone-light .projects-enterprise-detail-copy h3 {
        color: #1f3f69 !important;
    }
    body.ui-tone-light .contact-enterprise-copy {
        background: linear-gradient(180deg, #f9fcff, #eef5ff) !important;
        border-color: #d8e4f2 !important;
        color: #355477 !important;
    }
    body.ui-tone-light .contact-enterprise-copy p {
        color: #355477 !important;
    }
    @media (max-width: 900px) {
        .ent-header-left {
            flex: 1 1 auto;
            min-width: 0;
        }
        .ent-site-check-slot {
            display: none;
        }
        .ent-nav-toggle {
            display: inline-flex;
            z-index: 2210;
            margin-left: auto;
        }
        .ent-nav-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(1, 8, 17, .62);
            opacity: 0;
            pointer-events: none;
            transition: opacity .22s ease;
            z-index: 2190;
        }
        body.ent-nav-open .ent-nav-backdrop {
            opacity: 1;
            pointer-events: auto;
        }
        .ent-nav {
            position: fixed;
            top: 0;
            right: 0;
            width: min(330px, 88vw);
            height: 100dvh;
            background: linear-gradient(180deg, #0e1b2d 0%, #0a1423 100%);
            border-left: 1px solid #2a4364;
            box-shadow: -18px 0 34px rgba(0, 0, 0, .45);
            padding: 84px 18px 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: 10px;
            transform: translateX(104%);
            transition: transform .25s cubic-bezier(.2,.7,.2,1);
            z-index: 2200;
            overflow-y: auto;
        }
        body.ui-tone-light .ent-nav-backdrop {
            background: rgba(120, 142, 168, .26);
        }
        body.ui-tone-light .ent-nav {
            background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);
            border-left-color: #d7e3f2;
            box-shadow: -18px 0 34px rgba(32, 61, 98, .16);
        }
        .ent-nav a {
            display: block;
            text-align: left;
            font-size: 12px;
            padding: 12px 13px;
            border-color: #34547d;
            background: rgba(19, 36, 58, .75);
        }
        body.ui-tone-light .ent-nav a {
            border-color: #d7e3f2;
            background: #f7fbff;
            color: #2f4d71;
        }
        body.ui-tone-light .ent-nav a:hover {
            background: #edf3fa;
            border-color: #d0deef;
            color: #14213d;
        }
        body.ui-tone-light .ent-nav a.is-active {
            background: #dbeafe;
            color: #0f3b75;
            border-color: #8fb7e8;
        }
        .ent-nav .nav-lang-sep {
            width: 100%;
            height: 1px;
            margin: 4px 0;
            background: #2f4a70;
        }
        body.ui-tone-light .ent-nav .nav-lang-sep {
            background: #d7e3f2;
        }
        .ent-nav .nav-lang-link {
            width: 100%;
            justify-content: flex-start;
        }
        body.ui-tone-light .ent-nav .nav-lang-link {
            border-color: #d7e3f2;
            background: #f7fbff;
            color: #2f4d71;
        }
        .ent-nav .nav-theme-toggle {
            width: 100%;
            justify-content: flex-start;
            min-height: 35px;
            height: 35px;
        }
        .ent-nav .nav-site-check {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }
        .ent-nav .nav-site-check--drawer {
            display: flex;
        }
        .ent-nav .nav-site-check input {
            width: 100%;
            max-width: 100%;
            min-height: 40px;
        }
        .ent-nav .nav-site-check button {
            width: 100%;
            min-height: 40px;
        }
        .ent-nav .nav-site-check-hint {
            position: static;
            white-space: normal;
            margin-top: 2px;
            transform: none;
            opacity: 1;
            display: none;
        }
        .ent-nav .nav-site-check.show-hint .nav-site-check-hint {
            display: block;
        }
        body.ui-tone-light .ent-nav .nav-theme-toggle {
            border-color: #d7e3f2;
            background: #f7fbff;
            color: #2a4364;
        }
        body.ent-nav-open .ent-nav {
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
<body data-terrain-theme="enterprise">
    <?php if ($yandexCounterCode !== ''): ?>
    <?= $yandexCounterCode . PHP_EOL ?>
    <?php endif; ?>
    <canvas id="terrainFieldGlobal" aria-hidden="true"></canvas>
    <header class="ent-header">
        <div class="ent-header-left">
            <div class="ent-brand-wrap">
                <div class="ent-brand" aria-label="<?= htmlspecialchars($logoAria, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="pc-logo">
                        <span class="pc-logo-main"><?= htmlspecialchars($logoMain, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="pc-logo-core">core</span>
                    </span>
                </div>
            </div>
            <div class="ent-site-check-slot" aria-label="<?= htmlspecialchars($isRu ? 'Проверка сайта' : 'Site check', ENT_QUOTES, 'UTF-8') ?>">
                <form class="nav-site-check nav-site-check--header" method="get" action="/audit/">
                    <input type="text" name="site" placeholder="<?= htmlspecialchars($isRu ? 'Проверь свой сайт' : 'Check your website', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" required>
                    <button type="submit"><?= htmlspecialchars($isRu ? 'Проверить сайт' : 'Check site', ENT_QUOTES, 'UTF-8') ?></button>
                    <div class="nav-site-check-hint" aria-hidden="true">
                        <?= htmlspecialchars($isRu ? 'Формат: https://tvoydomen.ru' : 'Format: https://yourdomain.com', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </form>
            </div>
        </div>
        <button class="ent-nav-toggle" type="button" aria-expanded="false" aria-controls="ent-nav-drawer" aria-label="<?= htmlspecialchars($isRu ? 'Открыть меню' : 'Open menu', ENT_QUOTES, 'UTF-8') ?>">
            <span></span><span></span><span></span>
        </button>
        <div class="ent-nav-backdrop" data-ent-nav-close></div>
        <nav class="ent-nav" id="ent-nav-drawer">
            <?php include DIR . '/template/views/enterprise/nav.php'; ?>
        </nav>
    </header>
    <script>
    (function () {
        var btn = document.querySelector('.ent-nav-toggle');
        var nav = document.getElementById('ent-nav-drawer');
        var backdrop = document.querySelector('.ent-nav-backdrop');
        if (!btn || !nav) { return; }
        var touchStartX = null;
        var touchStartY = null;

        function closeMenu() {
            document.body.classList.remove('ent-nav-open');
            btn.setAttribute('aria-expanded', 'false');
        }
        function openMenu() {
            document.body.classList.add('ent-nav-open');
            btn.setAttribute('aria-expanded', 'true');
        }
        function toggleMenu() {
            if (document.body.classList.contains('ent-nav-open')) {
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
            if (!document.body.classList.contains('ent-nav-open') || window.innerWidth > 900) { return; }
            if (!e.touches || !e.touches.length) { return; }
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        document.addEventListener('touchend', function (e) {
            if (!document.body.classList.contains('ent-nav-open') || window.innerWidth > 900) { return; }
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
        var slot = document.querySelector('.ent-site-check-slot');
        if (!slot) { return; }
        var storageKey = 'pc_site_check_shift_enterprise_' + (window.location.host || 'default');

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
                '.services-enterprise',
                '.services-simple',
                '.products-page',
                '.projects-enterprise',
                '.projects-simple',
                '.cases-simple',
                '.ofr-page',
                '.blog-enterprise',
                '.blog-simple',
                '.contact-enterprise',
                '.contact-simple',
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
            var logo = document.querySelector('.ent-brand-wrap');
            if (!anchor || !logo) {
                applyShift(0);
                return;
            }
            var targetLeft = anchor.getBoundingClientRect().left;
            var logoRight = logo.getBoundingClientRect().right;
            var shift = Math.max(0, Math.round(targetLeft - logoRight));
            applyShift(shift);
            writeCachedShift(shift);
        }

        var cachedShift = readCachedShift();
        if (!isNaN(cachedShift)) {
            applyShift(cachedShift);
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
            tone = (tone === 'light') ? 'light' : 'dark';
            document.body.classList.toggle('ui-tone-light', tone === 'light');
            document.body.classList.toggle('ui-tone-dark', tone !== 'light');
            syncMainThemeClasses(tone);
            // Keep enterprise terrain on this shell for both tones to avoid disappearing canvas on light mode.
            var terrainTheme = 'enterprise';
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
            return document.body.classList.contains('ui-tone-light') ? 'light' : 'dark';
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
        document.addEventListener('DOMContentLoaded', function () {
            applyTone(currentTone(), false, false);
        });
        window.addEventListener('load', function () {
            applyTone(currentTone(), false, false);
        });

        for (var t = 0; t < toggles.length; t++) {
            toggles[t].addEventListener('click', function () {
                var nextTone = document.body.classList.contains('ui-tone-light') ? 'dark' : 'light';
                applyTone(nextTone, true, true);
            });
        }
    })();
    </script>
    <main class="ent-main">
