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
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap');

    :root {
        color-scheme: dark;
        --shell-bg: #06101d;
        --shell-bg-2: #0b1730;
        --shell-panel: rgba(7, 14, 28, .62);
        --shell-panel-strong: rgba(9, 18, 36, .82);
        --shell-surface: rgba(255, 255, 255, .04);
        --shell-border: rgba(116, 171, 255, .18);
        --shell-border-strong: rgba(116, 171, 255, .3);
        --shell-text: #eef5ff;
        --shell-muted: #9cb0ce;
        --shell-accent: #73b8ff;
        --shell-accent-2: #27dfc0;
        --shell-accent-3: #ff9a5f;
        --shell-highlight: #ffdf78;
        --shell-shadow: 0 28px 80px rgba(1, 6, 18, .42);
        --simple-header-height: 104px;
        --simple-header-max: none;
        --shell-notch: polygon(0 0, calc(100% - 22px) 0, 100% 22px, 100% 100%, 0 100%);
    }

    * { box-sizing: border-box; }
    html, body {
        min-height: 100%;
        background:
            radial-gradient(circle at top, rgba(13, 31, 66, .8), transparent 42%),
            linear-gradient(180deg, #07101d 0%, #050b15 100%);
    }
    html { scroll-behavior: smooth; }
    body {
        margin: 0;
        padding-top: calc(var(--simple-header-height) + 18px);
        font: 500 16px/1.65 "Sora", system-ui, sans-serif;
        color: var(--shell-text);
        position: relative;
        overflow-x: hidden;
        letter-spacing: -.01em;
    }
    body.ui-tone-light {
        --shell-bg: #f2f7ff;
        --shell-bg-2: #ffffff;
        --shell-panel: rgba(255, 255, 255, .68);
        --shell-panel-strong: rgba(255, 255, 255, .88);
        --shell-surface: rgba(25, 73, 154, .04);
        --shell-border: rgba(28, 84, 175, .12);
        --shell-border-strong: rgba(28, 84, 175, .22);
        --shell-text: #10233f;
        --shell-muted: #607696;
        --shell-accent: #225dcd;
        --shell-accent-2: #0aa08d;
        --shell-accent-3: #ec7d47;
        --shell-highlight: #d49e17;
        --shell-shadow: 0 24px 60px rgba(46, 88, 170, .14);
        color-scheme: light;
    }
    body > *:not(#terrainFieldGlobal) { position: relative; z-index: 1; }
    a, button, input, textarea, select { font: inherit; }
    a { color: inherit; }
    img { max-width: 100%; height: auto; }
    #terrainFieldGlobal {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: .98;
    }
    ::selection { background: rgba(115, 184, 255, .32); color: #fff; }

    .neo-title {
        font-family: "Space Grotesk", "Sora", sans-serif;
        letter-spacing: -.06em;
        line-height: .92;
    }
    .neo-title strong,
    .neo-title em {
        position: relative;
        display: inline;
        font-style: normal;
        font-weight: 700;
        color: var(--shell-text);
        background: linear-gradient(120deg, rgba(115, 184, 255, .24), rgba(39, 223, 192, .08));
        box-shadow: inset 0 -0.48em 0 rgba(115, 184, 255, .12);
    }
    .neo-title em {
        color: var(--shell-highlight);
        box-shadow: inset 0 -0.5em 0 rgba(255, 154, 95, .16);
    }

    .simple-header {
        position: fixed;
        left: 18px;
        right: 18px;
        top: 14px;
        z-index: 9999;
        display: grid;
        grid-template-columns: minmax(280px, 340px) minmax(360px, 1fr) auto;
        gap: 16px;
        align-items: center;
        max-width: none;
        margin: 0;
        padding: 16px 20px;
        border: 1px solid var(--shell-border);
        background: linear-gradient(180deg, rgba(6,10,18,.96), rgba(7,12,23,.88));
        box-shadow: 0 22px 72px rgba(0, 4, 14, .34);
        clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 22px, 100% 100%, 0 100%);
        backdrop-filter: blur(20px) saturate(122%);
    }
    .simple-header::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(61,116,255,.16), transparent 22%, transparent 78%, rgba(39,223,192,.14)), linear-gradient(180deg, rgba(255,255,255,.06), transparent 44%), radial-gradient(circle at 82% 18%, rgba(255,154,95,.12), transparent 18%);
        pointer-events: none;
    }
    .simple-header.is-scrolled { transform: translateY(-2px); }

    .simple-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        text-decoration: none;
    }
    .pc-logo-wrap { display: grid; gap: 4px; }
    .pc-logo {
        position: relative;
        display: inline-flex;
        align-items: baseline;
        gap: 6px;
        line-height: 1;
    }
    .pc-logo-main {
        font: 700 34px/1 "Space Grotesk", "Sora", sans-serif;
        letter-spacing: -.08em;
        color: var(--shell-text);
    }
    .pc-logo-core {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        min-height: 24px;
        padding: 3px 10px;
        border: 1px solid var(--shell-border-strong);
        background: linear-gradient(135deg, rgba(39,223,192,.18), rgba(115,184,255,.16));
        clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px));
        color: var(--shell-highlight);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
    }
    .pc-brand-copy {
        display: grid;
        gap: 3px;
        min-width: 0;
    }
    .pc-brand-copy strong {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--shell-accent);
    }
    .pc-brand-copy strong::before {
        content: "//";
        color: var(--shell-highlight);
        font-size: 10px;
    }
    .pc-brand-copy span {
        display: block;
        max-width: 42ch;
        color: var(--shell-muted);
        font-size: 12px;
        line-height: 1.32;
    }

    .simple-header-center { min-width: 0; }
    .simple-header-search {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
        min-width: 0;
        padding: 8px;
        border: 1px solid var(--shell-border);
        background: rgba(255,255,255,.03);
        clip-path: polygon(0 0, calc(100% - 16px) 0, 100% 16px, 100% 100%, 0 100%);
    }
    .simple-header-search input {
        width: 100%;
        min-width: 0;
        padding: 11px 12px;
        border: 0;
        outline: none;
        background: transparent;
        color: var(--shell-text);
    }
    .simple-header-search input::placeholder { color: var(--shell-muted); }
    .simple-header-search button,
    .simple-header-action,
    .nav-cta,
    .nav-theme-toggle,
    .simple-nav-toggle {
        border: 1px solid var(--shell-border);
        color: var(--shell-text);
        text-decoration: none;
    }
    .simple-header-search button,
    .simple-header-action,
    .nav-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        font-weight: 700;
        background: linear-gradient(135deg, rgba(115,184,255,.22), rgba(39,223,192,.18));
        clip-path: polygon(0 0, calc(100% - 14px) 0, 100% 14px, 100% 100%, 0 100%);
        transition: transform .2s ease, border-color .2s ease, filter .2s ease;
    }
    .simple-header-search button:hover,
    .simple-header-action:hover,
    .nav-cta:hover {
        transform: translateY(-1px);
        filter: saturate(1.08);
        border-color: var(--shell-border-strong);
    }

    .simple-header-right {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        min-width: 0;
        flex-wrap: nowrap;
    }
    .simple-nav {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
    }
    .simple-nav::-webkit-scrollbar { display: none; }
    .simple-nav a,
    .nav-theme-toggle {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 10px 14px;
        background: rgba(255,255,255,.03);
        color: var(--shell-muted);
        clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%);
        transition: color .2s ease, border-color .2s ease, background .2s ease, transform .2s ease;
        flex: 0 0 auto;
    }
    .simple-nav a:hover,
    .nav-theme-toggle:hover {
        color: var(--shell-text);
        border-color: var(--shell-border-strong);
        background: rgba(255,255,255,.07);
        transform: translateY(-1px);
    }
    .simple-nav a.is-active {
        color: var(--shell-text);
        background: linear-gradient(135deg, rgba(115,184,255,.18), rgba(39,223,192,.12));
        border-color: var(--shell-border-strong);
    }
    .nav-item-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        color: var(--shell-highlight);
        font-size: 13px;
    }
    .nav-cta { color: var(--shell-text) !important; }
    .nav-theme-toggle {
        min-width: 112px;
        justify-content: center;
        cursor: pointer;
    }
    .nav-theme-toggle .theme-icon {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        opacity: .42;
    }
    body.ui-tone-dark .theme-icon-moon,
    body.ui-tone-light .theme-icon-sun {
        opacity: 1;
        color: var(--shell-highlight);
    }

    .simple-nav-toggle {
        position: relative;
        display: none;
        width: 52px;
        height: 52px;
        padding: 0;
        background: rgba(255,255,255,.05);
        clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
        cursor: pointer;
    }
    .simple-nav-toggle span {
        position: absolute;
        left: 14px;
        right: 14px;
        height: 2px;
        background: currentColor;
        border-radius: 999px;
        transition: transform .22s ease, opacity .22s ease, top .22s ease;
    }
    .simple-nav-toggle span:nth-child(1) { top: 18px; }
    .simple-nav-toggle span:nth-child(2) { top: 25px; }
    .simple-nav-toggle span:nth-child(3) { top: 32px; }
    .simple-nav-backdrop { display: none; }

    .neo-panel,
    .cp-card,
    .cp-side-panel,
    .cp-signal-board,
    .cp-feed,
    .cp-feed-item,
    .cp-case-card,
    .cpb-lead-card,
    .cpb-feed,
    .cpb-feed-card,
    .cpb-side-panel,
    .cpb-article,
    .cpb-comments,
    .sol-card,
    .sol-board,
    .sol-detail,
    .sol-comments {
        clip-path: polygon(0 0, calc(100% - 18px) 0, 100% 18px, 100% 100%, 18px 100%, 0 calc(100% - 18px));
    }

    main, .pc-main { width: 100%; }

    @media (max-width: 1080px) {
        .simple-header { grid-template-columns: minmax(240px, 1fr) auto; }
        .simple-header-center { display: none; }
        .simple-nav-toggle { display: inline-flex; align-items: center; justify-content: center; }
        .simple-nav-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(3, 8, 18, .62);
            opacity: 0;
            pointer-events: none;
            transition: opacity .22s ease;
            z-index: 10000;
        }
        .simple-nav {
            position: fixed;
            right: 18px;
            top: calc(var(--simple-header-height) + 8px);
            width: min(92vw, 390px);
            padding: 18px;
            border: 1px solid var(--shell-border);
            background: var(--shell-panel-strong);
            backdrop-filter: blur(18px);
            box-shadow: 0 22px 72px rgba(0, 4, 14, .34);
            clip-path: polygon(0 0, calc(100% - 18px) 0, 100% 18px, 100% 100%, 0 100%);
            display: grid;
            justify-content: stretch;
            gap: 10px;
            transform: translateY(-10px) scale(.98);
            opacity: 0;
            pointer-events: none;
            transition: transform .22s ease, opacity .22s ease;
            z-index: 10001;
        }
        .simple-nav a,
        .nav-cta,
        .nav-theme-toggle { width: 100%; justify-content: center; }
        body.simple-nav-open .simple-nav { transform: none; opacity: 1; pointer-events: auto; }
        body.simple-nav-open .simple-nav-backdrop { opacity: 1; pointer-events: auto; }
        body.simple-nav-open .simple-nav-toggle span:nth-child(1) { top: 25px; transform: rotate(45deg); }
        body.simple-nav-open .simple-nav-toggle span:nth-child(2) { opacity: 0; }
        body.simple-nav-open .simple-nav-toggle span:nth-child(3) { top: 25px; transform: rotate(-45deg); }
    }

    @media (max-width: 720px) {
        :root { --simple-header-height: 88px; }
        body { padding-top: calc(var(--simple-header-height) + 12px); }
        .simple-header {
            left: 10px;
            right: 10px;
            top: 10px;
            padding: 12px 14px;
        }
        .pc-logo-main { font-size: 28px; }
        .pc-brand-copy span { display: none; }
        .simple-header-action { display: none; }
    }
    </style>
</head><header class="simple-header" id="cp-header">
    <a class="simple-brand" href="/" aria-label="<?= htmlspecialchars($logoAria, ENT_QUOTES, 'UTF-8') ?>">
        <span class="pc-logo-wrap">
            <span class="pc-logo">
                <span class="pc-logo-main"><?= htmlspecialchars($logoMain, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="pc-logo-core">backroom</span>
            </span>
        </span>
        <span class="pc-brand-copy">
            <strong><?= htmlspecialchars($isRu ? 'CPA backstage / issue desk' : 'CPA backstage / issue desk', ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($isRu ? '������������ ������ ���������: ������, ����, ��������, �������, ����� � ready-made packs ��� affiliate-������' : 'an editorial backstage strip: traffic, farms, creatives, trackers, cases and ready-made packs for affiliate teams', ENT_QUOTES, 'UTF-8') ?></span>
        </span>
    </a>

    <div class="simple-header-center">
        <form class="simple-header-search" method="get" action="/blog/">
            <input type="text" name="q" data-search-input placeholder="<?= htmlspecialchars($isRu ? '�����: Facebook farm, ����������, tracker setup, nutra funnel' : 'Search: Facebook farm, anti-detect, tracker setup, nutra funnel', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
            <button type="submit"><?= htmlspecialchars($isRu ? '�����' : 'Search', ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>

    <div class="simple-header-right">
        <a class="simple-header-action" href="/solutions/downloads/"><?= htmlspecialchars($isRu ? 'Backroom pack' : 'Backroom pack', ENT_QUOTES, 'UTF-8') ?></a>
        <button class="simple-nav-toggle" type="button" aria-expanded="false" aria-controls="simple-nav-drawer" aria-label="<?= htmlspecialchars($isRu ? '������� ����' : 'Open menu', ENT_QUOTES, 'UTF-8') ?>">
            <span></span><span></span><span></span>
        </button>
    </div>

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
    var header = document.getElementById('cp-header');
    var searchInput = document.querySelector('[data-search-input]');
    var placeholderIndex = 0;
    var placeholders = <?= $isRu ? "json_encode(['�����: Facebook farm, ����������, tracker setup, nutra funnel','�����: TikTok creatives, BM risk, cloaking, iGaming flow','�����: ������� ���������, sweepstakes, crypto angles, team SOP'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)" : "json_encode(['Search: Facebook farm, anti-detect, tracker setup, nutra funnel','Search: TikTok creatives, BM risk, cloaking, iGaming flow','Search: account warmup, sweepstakes, crypto angles, team SOP'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)" ?>;
    if (header) {
        var syncHeader = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 18);
        };
        window.addEventListener('scroll', syncHeader, { passive: true });
        syncHeader();
    }
    if (searchInput && Array.isArray(placeholders) && placeholders.length > 1) {
        window.setInterval(function () {
            placeholderIndex = (placeholderIndex + 1) % placeholders.length;
            searchInput.setAttribute('placeholder', placeholders[placeholderIndex]);
        }, 2600);
    }
    if (!btn || !nav) {
        return;
    }
    function closeMenu() {
        document.body.classList.remove('simple-nav-open');
        btn.setAttribute('aria-expanded', 'false');
    }
    btn.addEventListener('click', function () {
        var isOpen = document.body.classList.contains('simple-nav-open');
        document.body.classList.toggle('simple-nav-open', !isOpen);
        btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });
    if (backdrop) {
        backdrop.addEventListener('click', closeMenu);
    }
    nav.addEventListener('click', function (event) {
        if (event.target.closest('a') || event.target.closest('button')) {
            closeMenu();
        }
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1080) {
            closeMenu();
        }
    });
})();
</script>



<?php include DIR . '/template/views/simple/partials/terrain_polygon_map.php'; ?>

