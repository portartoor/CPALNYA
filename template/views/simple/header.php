<?php
$hostForLang = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($hostForLang, ':') !== false) {
    $hostForLang = explode(':', $hostForLang, 2)[0];
}
$htmlLang = (bool)preg_match('/\.ru$/', $hostForLang) ? 'ru' : 'en';
$isRu = ($htmlLang === 'ru');
$logoRu = 'ЦПАЛНЯ';
$logoMain = 'ЦПАЛЬНЯ';
$logoAria = $logoMain . ' portal';
$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
$firstSegment = strtolower((string)(explode('/', trim($requestPath, '/'))[0] ?? ''));
$section = in_array($firstSegment, ['blog', 'journal', 'playbooks', 'services', 'projects', 'solutions', 'contact', 'audit'], true) ? $firstSegment : 'home';
if ($section === 'blog') {
    $section = 'journal';
}
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$faviconPrimary = '/favicon4.png';
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
$isEquivalentPath = in_array($canonicalPath, ['/', '/journal/', '/playbooks/', '/services/', '/projects/', '/contact/', '/audit/', '/privacy/', '/terms/'], true);
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
$publicPortalUser = null;
$publicPortalAvatar = '';

if (!function_exists('header_search_preview_results')) {
    function header_search_preview_tokens(string $query): array
    {
        $query = trim((string)preg_replace('/\s+/u', ' ', $query));
        if ($query === '') {
            return [];
        }
        $parts = preg_split('/[\s,.;:!?()\[\]{}"\'«»\/\\\\|+-]+/u', $query) ?: [];
        $tokens = [];
        foreach ($parts as $part) {
            $part = trim((string)$part);
            if ($part === '') {
                continue;
            }
            if (function_exists('mb_strlen') && mb_strlen($part, 'UTF-8') < 2) {
                continue;
            }
            $key = function_exists('mb_strtolower') ? mb_strtolower($part, 'UTF-8') : strtolower($part);
            $tokens[$key] = $part;
        }
        return array_values($tokens);
    }

    function header_search_preview_mark(string $text, array $tokens): string
    {
        $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if ($safe === '' || empty($tokens)) {
            return $safe;
        }
        $pattern = '/' . implode('|', array_map(static function (string $token): string {
            return preg_quote($token, '/');
        }, $tokens)) . '/iu';
        return (string)preg_replace($pattern, '<mark>$0</mark>', $safe);
    }

    function header_search_preview_snippet(string $text, array $tokens, int $radius = 64, int $maxLen = 140): string
    {
        $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($text)));
        if ($text === '') {
            return '';
        }
        $offset = null;
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            if (function_exists('mb_stripos')) {
                $found = mb_stripos($text, $token, 0, 'UTF-8');
                if ($found !== false) {
                    $offset = (int)$found;
                    break;
                }
            } else {
                $found = stripos($text, $token);
                if ($found !== false) {
                    $offset = (int)$found;
                    break;
                }
            }
        }
        if ($offset === null) {
            if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $maxLen) {
                return rtrim((string)mb_substr($text, 0, $maxLen - 3, 'UTF-8')) . '...';
            }
            return $text;
        }

        if (function_exists('mb_substr') && function_exists('mb_strlen')) {
            $start = max(0, $offset - $radius);
            $snippet = mb_substr($text, $start, $maxLen, 'UTF-8');
            if ($start > 0) {
                $snippet = '...' . ltrim($snippet);
            }
            if (($start + mb_strlen($snippet, 'UTF-8')) < mb_strlen($text, 'UTF-8')) {
                $snippet = rtrim($snippet, ". \t\n\r\0\x0B") . '...';
            }
            return $snippet;
        }

        $start = max(0, $offset - $radius);
        $snippet = substr($text, $start, $maxLen);
        if ($start > 0) {
            $snippet = '...' . ltrim($snippet);
        }
        if (($start + strlen($snippet)) < strlen($text)) {
            $snippet = rtrim($snippet) . '...';
        }
        return $snippet;
    }

    function header_search_preview_results($FRMWRK, string $host, string $lang, string $query, int $limit = 12): array
    {
        $query = trim($query);
        if ($query === '' || !is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return [];
        }
        $db = $FRMWRK->DB();
        if (!$db) {
            return [];
        }
        $examplesCommon = defined('DIR') ? DIR . 'core/controls/examples/_common.php' : '';
        if ($examplesCommon !== '' && is_file($examplesCommon)) {
            require_once $examplesCommon;
        }
        if (!function_exists('examples_table_exists') || !examples_table_exists($db)) {
            return [];
        }

        $host = strtolower(trim($host));
        $tokens = header_search_preview_tokens($query);
        $lang = function_exists('examples_normalize_lang') ? examples_normalize_lang($lang) : (($lang === 'ru') ? 'ru' : 'en');
        $limit = max(4, min(24, $limit));
        $hostSafe = mysqli_real_escape_string($db, $host);
        $queryLike = mysqli_real_escape_string($db, '%' . $query . '%');
        $langCond = function_exists('examples_table_has_lang_column') && examples_table_has_lang_column($db)
            ? "AND lang_code = '" . mysqli_real_escape_string($db, $lang) . "'"
            : '';
        $sectionCond = function_exists('examples_table_has_column') && examples_table_has_column($db, 'material_section')
            ? "AND material_section IN ('journal','playbooks','signals','fun')"
            : '';
        $clusterSelect = (function_exists('examples_table_has_column') && examples_table_has_column($db, 'cluster_code'))
            ? 'cluster_code'
            : "'' AS cluster_code";
        $materialSelect = (function_exists('examples_table_has_column') && examples_table_has_column($db, 'material_section'))
            ? 'material_section'
            : "'journal' AS material_section";
        $previewSelect = function_exists('examples_preview_select_sql') ? examples_preview_select_sql($db) : '';

        $sql = "SELECT id, title, slug, excerpt_html, content_html, {$clusterSelect}, {$materialSelect}{$previewSelect}
                FROM examples_articles
                WHERE is_published = 1
                  AND slug IS NOT NULL
                  AND slug <> ''
                  AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
                  {$langCond}
                  {$sectionCond}
                  AND (
                      title LIKE '{$queryLike}'
                      OR excerpt_html LIKE '{$queryLike}'
                      OR content_html LIKE '{$queryLike}'
                  )
                ORDER BY
                  CASE
                    WHEN title LIKE '" . mysqli_real_escape_string($db, $query . '%') . "' THEN 0
                    WHEN title LIKE '{$queryLike}' THEN 1
                    ELSE 2
                  END,
                  COALESCE(published_at, updated_at, created_at) DESC,
                  id DESC
                LIMIT {$limit}";

        $rows = method_exists($FRMWRK, 'DBRecords') ? (array)$FRMWRK->DBRecords($sql) : [];
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = trim((string)($row['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $section = trim((string)($row['material_section'] ?? 'journal'));
            if (!in_array($section, ['journal', 'playbooks', 'signals', 'fun'], true)) {
                $section = 'journal';
            }
            $cluster = trim((string)($row['cluster_code'] ?? ''));
            $url = function_exists('examples_article_url_path')
                ? examples_article_url_path($slug, $cluster, $host, $section)
                : '/journal/' . rawurlencode($slug) . '/';
            $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
            $full = trim((string)($row['preview_image_url'] ?? ''));
            $data = trim((string)($row['preview_image_data'] ?? ''));
            $image = $thumb !== '' ? $thumb : ($full !== '' ? $full : $data);
            $title = trim((string)($row['title'] ?? ''));
            $excerptSource = (string)($row['excerpt_html'] ?? '');
            if (trim($excerptSource) === '') {
                $excerptSource = (string)($row['content_html'] ?? '');
            }
            $excerpt = header_search_preview_snippet($excerptSource, $tokens, 72, 148);
            if ($excerpt === '' && function_exists('examples_build_excerpt')) {
                $excerpt = examples_build_excerpt($excerptSource);
            }
            $out[] = [
                'title' => $title,
                'title_html' => header_search_preview_mark($title, $tokens),
                'url' => $url,
                'image' => $image,
                'section' => $section,
                'excerpt' => $excerpt,
                'excerpt_html' => header_search_preview_mark($excerpt, $tokens),
            ];
        }

        return $out;
    }
}

if (isset($_GET['header_search_preview'])) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=UTF-8');
    $rawQuery = trim((string)($_GET['q'] ?? ''));
    $items = header_search_preview_results($FRMWRK ?? null, $titleHost, $isRu ? 'ru' : 'en', $rawQuery, 12);
    echo json_encode([
        'ok' => true,
        'query' => $rawQuery,
        'items' => $items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
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
        padding-top: 0;
        font: 500 16px/1.65 "Sora", system-ui, sans-serif;
        color: var(--shell-text);
        position: relative;
        overflow-x: hidden;
        letter-spacing: -.01em;
        transition: padding-top .24s ease;
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
    body > :not(#terrainFieldGlobal):not(.simple-header):not(.public-back-to-top) {
        position: relative;
        z-index: 1;
    }
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
        z-index: 999;
        display: grid;
        grid-template-columns: minmax(220px, 300px) minmax(320px, 1fr) auto;
        gap: 10px 16px;
        align-items: center;
        max-width: none;
        margin: 0;
        padding: 16px 20px;
        border: 1px solid var(--shell-border);
        background: linear-gradient(180deg, rgba(6,10,18,.34), rgba(7,12,23,.18));
        box-shadow: 0 14px 42px rgba(0, 4, 14, .16);
        clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 22px, 100% 100%, 0 100%);
        backdrop-filter: blur(10px) saturate(116%);
        transition: top .24s ease, padding .24s ease, background .24s ease, box-shadow .24s ease, border-color .24s ease, transform .24s ease;
    }
    .simple-header::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(61,116,255,.08), transparent 22%, transparent 78%, rgba(39,223,192,.08)), linear-gradient(180deg, rgba(255,255,255,.03), transparent 44%), radial-gradient(circle at 82% 18%, rgba(255,154,95,.06), transparent 18%);
        pointer-events: none;
    }
    .simple-header.is-scrolled {
        z-index: 999;
        top: 0;
        transform: translateY(0);
        padding: 10px 16px;
        background: linear-gradient(180deg, rgba(6,10,18,.72), rgba(7,12,23,.58));
        box-shadow: 0 18px 42px rgba(0, 4, 14, .22);
        border-color: var(--shell-border-strong);
        margin-top: 0;
    }
    .simple-header.is-scrolled.is-search-open {
        clip-path: none;
        overflow: visible;
    }
    .simple-header.has-search-preview {
        clip-path: none;
        overflow: visible;
    }
    .simple-header.has-search-preview::before {
        clip-path: none;
    }

    .simple-brand {
        grid-column: 1;
        grid-row: 1 / span 2;
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        text-decoration: none;
        transition: gap .24s ease, transform .24s ease;
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
        transition: font-size .24s ease, letter-spacing .24s ease;
    }
    .pc-logo-main .pc-logo-accent {
        font-weight: 900;
        color: #ffffff;
    }
    .pc-logo-main .pc-logo-rest {
        font-weight: 700;
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
        transition: min-width .24s ease, min-height .24s ease, padding .24s ease, font-size .24s ease, opacity .2s ease, transform .24s ease;
    }
    .pc-brand-copy {
        display: grid;
        gap: 3px;
        min-width: 0;
    }
    .simple-header-note {
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        align-self: end;
        overflow: hidden;
        transform-origin: top;
        transition: opacity .2s ease, max-height .24s ease, transform .24s ease, margin .24s ease;
        max-height: 88px;
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
    :where(body) h1:not(.pc-logo-main)::before {
        content: "//";
        display: inline-block;
        margin: 0 12px 0 0;
        color: var(--shell-highlight);
        font-size: 42px;
        font-weight: 700;
        letter-spacing: 1px;
        line-height: 1;
        text-transform: uppercase;
        position: relative;
        top: -3px;
    }
    :where(body) h2:not(.pc-logo-main)::before {
        content: "//";
        display: inline-block;
        margin: 0 10px 0 0;
        color: var(--shell-highlight);
        font-size: 32px;
        font-weight: 700;
        letter-spacing: 1px;
        line-height: 1;
        text-transform: uppercase;
        position: relative;
        top: -2px;
    }
    :where(body) h3:not(.pc-logo-main)::before {
        content: "//";
        display: inline-block;
        margin: 0 8px 0 0;
        color: var(--shell-highlight);
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1px;
        line-height: 1;
        text-transform: uppercase;
        position: relative;
        top: -1px;
    }
    .simple-header :is(h1, h2, h3)::before,
    .simple-nav :is(h1, h2, h3)::before,
    .jrnl-lightbox :is(h1, h2, h3)::before {
        content: none;
    }
    .pc-brand-copy span {
        display: block;
        max-width: 42ch;
        color: var(--shell-muted);
        font-size: 12px;
        line-height: 1.32;
    }

    .simple-header-center {
        grid-column: 2;
        grid-row: 2;
        min-width: 0;
        overflow: hidden;
        transform-origin: top;
        transition: opacity .2s ease, max-height .24s ease, transform .24s ease, margin .24s ease;
        max-height: 88px;
    }
    .simple-header.has-search-preview .simple-header-center {
        overflow: visible;
    }
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
        transition: opacity .2s ease, transform .24s ease, border-color .24s ease;
        position: relative;
    }
    .simple-header.has-search-preview .simple-header-search {
        clip-path: none;
        overflow: visible;
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
    .simple-search-preview {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 10px);
        display: grid;
        gap: 12px;
        padding: 14px;
        border: 1px solid var(--shell-border);
        background: var(--shell-panel-strong);
        box-shadow: 0 18px 44px rgba(0, 4, 14, .22);
        backdrop-filter: blur(16px);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        pointer-events: none;
        transition: opacity .18s ease, transform .22s ease, visibility .18s ease;
        z-index: 10020;
        max-height: min(60vh, 520px);
        overflow-y: auto;
    }
    .simple-search-preview.is-visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }
    .simple-search-preview-group { display: grid; gap: 8px; }
    .simple-search-preview-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(173, 190, 217, .82);
        font: 700 10px/1 "Space Grotesk", "Sora", sans-serif;
        letter-spacing: .16em;
        text-transform: uppercase;
    }
    .simple-search-preview-title::before {
        content: "";
        width: 14px;
        height: 1px;
        background: linear-gradient(90deg, rgba(115,184,255,.45), transparent);
    }
    .simple-search-preview-list { display: grid; gap: 8px; }
    .simple-search-preview-item {
        display: grid;
        grid-template-columns: 56px minmax(0,1fr);
        gap: 10px;
        align-items: start;
        padding: 8px;
        background: rgba(255,255,255,.03);
        text-decoration: none;
        color: inherit;
        transition: background .18s ease, border-color .18s ease, transform .18s ease;
    }
    .simple-search-preview-item:hover,
    .simple-search-preview-item.is-active {
        background: rgba(255,255,255,.07);
        transform: translateY(-1px);
    }
    .simple-search-preview-thumb {
        width: 56px;
        height: 56px;
        border: 1px solid rgba(255,255,255,.08);
        background: linear-gradient(135deg, rgba(115,184,255,.18), rgba(39,223,192,.12));
        overflow: hidden;
    }
    .simple-search-preview-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .simple-search-preview-copy {
        min-width: 0;
        display: grid;
        gap: 4px;
    }
    .simple-search-preview-copy strong {
        font-size: 13px;
        line-height: 1.25;
        color: var(--shell-text);
    }
    .simple-search-preview-copy span {
        color: var(--shell-muted);
        font-size: 11px;
        line-height: 1.35;
    }
    .simple-search-preview-copy mark {
        padding: 0 .16em;
        background: rgba(244, 213, 107, .22);
        color: #fff3bf;
    }
    .simple-search-preview-empty {
        color: var(--shell-muted);
        font-size: 12px;
        line-height: 1.5;
        padding: 4px 2px;
    }
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
        grid-column: 3;
        grid-row: 1 / span 2;
        justify-self: end;
        gap: 10px;
        min-width: 0;
        flex-wrap: nowrap;
        transition: transform .24s ease;
    }
    .simple-account {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .simple-account-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        padding: 0;
        border: 1px solid var(--shell-border);
        background: rgba(255,255,255,.04);
        color: var(--shell-text);
        text-decoration: none;
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }
    .simple-account-trigger:hover {
        transform: translateY(-1px);
        border-color: var(--shell-border-strong);
        background: rgba(255,255,255,.08);
    }
    .simple-account-trigger img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .simple-account-icon {
        width: 18px;
        height: 18px;
        position: relative;
        display: inline-block;
    }
    .simple-account-icon::before,
    .simple-account-icon::after {
        content: "";
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 999px;
        border: 1.5px solid currentColor;
    }
    .simple-account-icon::before {
        top: 0;
        width: 10px;
        height: 10px;
    }
    .simple-account-icon::after {
        bottom: 0;
        width: 16px;
        height: 9px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        border-bottom-left-radius: 3px;
        border-bottom-right-radius: 3px;
    }
    .simple-account-popover {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        min-width: 250px;
        padding: 14px;
        border: 1px solid var(--shell-border);
        background: var(--shell-panel-strong);
        box-shadow: 0 18px 44px rgba(0, 4, 14, .22);
        display: grid;
        gap: 10px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
        z-index: 10030;
    }
    .simple-account:hover .simple-account-popover,
    .simple-account:focus-within .simple-account-popover {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .simple-account-popover strong {
        font-size: 12px;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--shell-accent);
    }
    .simple-account-popover p {
        margin: 0;
        color: var(--shell-muted);
        font-size: 12px;
        line-height: 1.5;
    }
    .simple-account-actions {
        display: grid;
        gap: 8px;
    }
    .simple-account-actions a,
    .simple-account-actions button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid var(--shell-border);
        background: rgba(255,255,255,.04);
        color: var(--shell-text);
        text-decoration: none;
        cursor: pointer;
    }
    .simple-search-toggle {
        display: none;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
        cursor: pointer;
        color: rgba(235,243,252,.9);
        transition: color .2s ease, transform .2s ease, opacity .2s ease;
    }
    .simple-search-toggle-icon {
        position: relative;
        width: 15px;
        height: 15px;
        border: 1.5px solid currentColor;
        border-radius: 50%;
        opacity: .96;
        transform: translateY(-1px);
    }
    .simple-search-toggle-icon::after {
        content: "";
        position: absolute;
        right: -4px;
        bottom: -3px;
        width: 6px;
        height: 1.5px;
        background: currentColor;
        transform: rotate(45deg);
        transform-origin: center;
    }
    .simple-search-toggle:hover {
        color: #ffffff;
        transform: translateY(-1px);
    }
    .simple-search-toggle:focus-visible {
        outline: none;
        color: #f4d56b;
    }
    .simple-nav {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        min-width: 0;
        padding-top: 12px;
        margin-top: 2px;
        border-top: 1px solid rgba(255,255,255,.08);
        flex-wrap: nowrap;
        white-space: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
        transition: padding-top .24s ease, margin-top .24s ease, border-color .24s ease, gap .24s ease;
    }
    .simple-nav::-webkit-scrollbar { display: none; }
    .simple-nav a,
    .nav-theme-toggle {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        background: rgba(255,255,255,.03);
        color: var(--shell-muted);
        text-decoration: none;
        border-bottom: 0;
        font-size: 12px;
        line-height: 1.1;
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
    .simple-header.is-scrolled .simple-brand {
        grid-row: 1;
        gap: 10px;
    }
    .simple-header.is-scrolled .pc-logo-main {
        font-size: 28px;
        letter-spacing: -.06em;
    }
    .simple-header.is-scrolled .pc-logo-core {
        min-width: 64px;
        min-height: 20px;
        padding: 2px 8px;
        font-size: 9px;
        transform: translateY(1px);
    }
    .simple-header.is-scrolled .simple-header-note,
    .simple-header.is-scrolled .simple-header-center {
        opacity: 0;
        max-height: 0;
        transform: translateY(-8px) scaleY(.92);
        pointer-events: none;
        margin: 0;
    }
    .simple-header.is-scrolled .simple-search-toggle {
        display: inline-flex;
    }
    .simple-header.is-scrolled .simple-header-right {
        grid-row: 1;
        align-self: center;
    }
    .simple-header.is-scrolled .simple-nav {
        grid-column: 2;
        grid-row: 1;
        align-self: center;
        min-width: 0;
        padding-top: 0;
        margin-top: 0;
        border-top-color: transparent;
        justify-content: flex-start;
        gap: 6px;
    }
    .simple-header.is-scrolled .simple-nav a,
    .simple-header.is-scrolled .nav-theme-toggle {
        padding: 5px 8px;
        font-size: 11px;
    }
    .simple-header.is-scrolled.is-search-open .simple-header-center {
        position: absolute;
        left: clamp(220px, 24vw, 300px);
        right: 58px;
        top: 50%;
        grid-column: auto;
        grid-row: auto;
        z-index: 4;
        opacity: 1;
        max-height: none;
        transform: translateY(-50%);
        pointer-events: auto;
        margin: 0;
        overflow: visible;
    }
    .simple-header.is-scrolled.is-search-open .simple-header-search {
        grid-template-columns: 1fr;
        gap: 0;
        width: 100%;
        box-sizing: border-box;
        min-height: 42px;
        padding: 4px 8px;
        border-color: rgba(141,179,236,.16);
        background: rgba(255,255,255,.035);
        clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
    }
    .simple-header.is-scrolled.is-search-open .simple-header-search input {
        padding: 8px 10px;
        font-size: 13px;
        line-height: 1.2;
    }
    .simple-header.is-scrolled.is-search-open .simple-header-search button {
        display: none;
    }
    .simple-header.is-scrolled.is-search-open .simple-search-preview {
        top: calc(100% + 8px);
    }
    .simple-header.is-scrolled.is-search-open .simple-nav {
        opacity: .08;
        pointer-events: none;
    }
    .simple-header.is-scrolled.is-search-open .simple-search-toggle {
        display: inline-flex;
        color: #f4d56b;
    }
    .nav-item-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 12px;
        color: var(--shell-highlight);
        font-size: 10px;
    }
    .nav-section-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 6px 0 2px;
        color: rgba(173, 190, 217, .72);
        font: 700 9px/1 "Space Grotesk", "Sora", sans-serif;
        letter-spacing: .18em;
        text-transform: uppercase;
        flex: 0 0 auto;
    }
    .nav-section-label::before {
        content: "";
        width: 16px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(115,184,255,.34));
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
    body.simple-nav-open { overflow: hidden; }

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
    .cpb-feature,
    .cpb-stream,
    .cpb-comment,
    .sol-card,
    .sol-board,
    .sol-detail,
    .sol-comments,
    .sol-list,
    .sol-comment,
    .cpz-cover,
    .cpz-issue-rail,
    .cpz-lead-card,
    .cpz-assets,
    .cpz-cases,
    .cpz-topic-card,
    .cpz-asset-card,
    .cpz-case-card,
    .cpz-products article,
    .cpz-panel,
    .cpz-feature-note,
    .cpz-secondary-lead,
    .cpz-cluster-intro,
    .cpz-issue-row strong,
    .cpb-index,
    .cpb-feed-arrow,
    .sol-meta div,
    .cpb-meta div {
        border-radius: 0 !important;
        background: linear-gradient(180deg, rgba(7, 13, 24, .22), rgba(7, 12, 23, .08)) !important;
        border: 1px solid rgba(141, 179, 236, .14) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.03), 0 10px 28px rgba(2, 7, 18, .08) !important;
        clip-path: polygon(0 0, calc(100% - 18px) 0, 100% 18px, 100% 100%, 18px 100%, 0 calc(100% - 18px)) !important;
        backdrop-filter: blur(10px) saturate(112%);
    }

    .cpz-kicker,
    .cpz-tag,
    .cpz-meta,
    .cpz-section-tag,
    .cpb-kicker,
    .cpb-chip,
    .cpb-meta-pill,
    .cpb-comment-meta span,
    .sol-kicker,
    .sol-tab,
    .sol-type,
    .sol-note,
    .sol-comment-meta span {
        border-radius: 0 !important;
        padding: 8px 11px !important;
        border: 1px solid rgba(141, 179, 236, .16) !important;
        background: rgba(255,255,255,.03) !important;
        clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%) !important;
    }

    .cpz-icon,
    .cpb-icon,
    .sol-icon {
        width: 16px !important;
        height: 16px !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--shell-highlight) !important;
    }

    .cpz-btn,
    .cpb-link,
    .cpb-btn,
    .sol-link,
    .sol-btn,
    .cpb-toolbar button,
    .sol-toolbar button,
    .cpb-auth-grid button,
    .cpb-comment-form button,
    .solutions-comment-form button,
    .cpz-actions a,
    .cpz-actions button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        padding: 12px 16px !important;
        border-radius: 0 !important;
        border: 1px solid var(--shell-border) !important;
        background: linear-gradient(135deg, rgba(115,184,255,.22), rgba(39,223,192,.18)) !important;
        color: var(--shell-text) !important;
        text-decoration: none !important;
        font-weight: 700 !important;
        clip-path: polygon(0 0, calc(100% - 14px) 0, 100% 14px, 100% 100%, 0 100%) !important;
        transition: transform .2s ease, border-color .2s ease, filter .2s ease !important;
        box-shadow: none !important;
    }

    .cpz-btn:hover,
    .cpb-link:hover,
    .cpb-btn:hover,
    .sol-link:hover,
    .sol-btn:hover,
    .cpb-toolbar button:hover,
    .sol-toolbar button:hover,
    .cpb-auth-grid button:hover,
    .cpb-comment-form button:hover,
    .solutions-comment-form button:hover,
    .cpz-actions a:hover,
    .cpz-actions button:hover {
        transform: translateY(-1px) !important;
        filter: saturate(1.08) !important;
        border-color: var(--shell-border-strong) !important;
    }

    .cpb-auth-grid input,
    .cpb-comment-form input,
    .cpb-comment-form select,
    .cpb-comment-form textarea,
    .sol-auth-grid input,
    .solutions-comment-form input,
    .solutions-comment-form select,
    .solutions-comment-form textarea,
    .cpz-cover input,
    .cpz-cover select,
    .cpz-cover textarea {
        border-radius: 0 !important;
        border: 1px solid rgba(141, 179, 236, .16) !important;
        background: rgba(255,255,255,.03) !important;
        color: var(--shell-text) !important;
        clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%) !important;
        box-shadow: none !important;
    }

    .cpb-article img,
    .cpz-cover img,
    .cpz-products img,
    .sol-detail img {
        border-radius: 0 !important;
        clip-path: polygon(0 0, calc(100% - 18px) 0, 100% 18px, 100% 100%, 0 100%) !important;
        border: 1px solid rgba(141, 179, 236, .14) !important;
    }

    main, .pc-main { width: 100%; }

    @media (max-width: 1080px) {
        .simple-header { grid-template-columns: minmax(240px, 1fr) auto; }
        .simple-header-note, .simple-header-center { display: none; }
        .simple-nav-toggle { display: inline-flex; align-items: center; justify-content: center; }
        .simple-search-toggle { display: none !important; }
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
            max-height: calc(100vh - var(--simple-header-height) - 28px);
            padding: 18px;
            border: 1px solid var(--shell-border);
            background: var(--shell-panel-strong);
            backdrop-filter: blur(18px);
            box-shadow: 0 14px 42px rgba(0, 4, 14, .16);
            clip-path: polygon(0 0, calc(100% - 18px) 0, 100% 18px, 100% 100%, 0 100%);
            display: grid;
            justify-content: stretch;
            gap: 10px;
            transform: translateY(-10px) scale(.98);
            opacity: 0;
            pointer-events: none;
            overflow-y: auto;
            overscroll-behavior: contain;
            transition: transform .22s ease, opacity .22s ease;
            z-index: 10001;
        }
        .simple-nav a,
        .nav-cta,
        .nav-theme-toggle { width: 100%; justify-content: flex-start; min-height: 42px; padding: 11px 12px; }
        .nav-section-label { width: 100%; justify-content: flex-start; padding: 8px 2px 2px; }
        .nav-section-label::before { width: 18px; }
        body.simple-nav-open .simple-nav { transform: none; opacity: 1; pointer-events: auto; }
        body.simple-nav-open .simple-nav-backdrop { opacity: 1; pointer-events: auto; }
        body.simple-nav-open .simple-nav-toggle span:nth-child(1) { top: 25px; transform: rotate(45deg); }
        body.simple-nav-open .simple-nav-toggle span:nth-child(2) { opacity: 0; }
        body.simple-nav-open .simple-nav-toggle span:nth-child(3) { top: 25px; transform: rotate(-45deg); }
        .simple-header.is-scrolled {
            padding: 12px 14px;
        }
        .simple-header.is-scrolled .pc-logo-main {
            font-size: 28px;
        }
        .simple-header.is-scrolled .simple-nav {
            grid-column: 1 / -1;
            grid-row: auto;
        }
    }

    @media (max-width: 720px) {
        :root { --simple-header-height: 88px; }
        body { padding-top: 0; }
        .simple-header {
            left: 10px;
            right: 10px;
            top: 10px;
            padding: 12px 14px;
        }
        .simple-header.is-scrolled {
            z-index: 999;
            top: 0;
        }
        .pc-logo-main { font-size: 28px; }
        .pc-brand-copy span { display: none; }
        .simple-header-action { display: none; }
        .simple-nav {
            left: 10px;
            right: 10px;
            width: auto;
            top: calc(var(--simple-header-height) + 6px);
            max-height: calc(100vh - var(--simple-header-height) - 18px);
            padding: 16px 14px;
        }
        .simple-nav a,
        .nav-theme-toggle { font-size: 13px; }
        .simple-search-preview {
            top: calc(100% + 8px);
            padding: 12px;
        }
    }
    </style>
    <?php if ($googleTagCode !== ''): ?>
    <?= $googleTagCode . PHP_EOL ?>
    <?php endif; ?>
</head>
<?php if ($yandexCounterCode !== ''): ?>
<?= $yandexCounterCode . PHP_EOL ?>
<?php endif; ?>
<canvas id="terrainFieldGlobal" aria-hidden="true"></canvas>
<header class="simple-header" id="cp-header">
    <a class="simple-brand" href="/" aria-label="<?= htmlspecialchars($logoAria, ENT_QUOTES, 'UTF-8') ?>">
        <span class="pc-logo-wrap">
            <span class="pc-logo">
                <span class="pc-logo-main"><span class="pc-logo-accent">ЦПА</span><span class="pc-logo-rest">ЛЬНЯ</span></span>
                <span class="pc-logo-core"><?= htmlspecialchars($isRu ? 'журнал' : 'journal', ENT_QUOTES, 'UTF-8') ?></span>
            </span>
        </span>
    </a>

    <div class="simple-header-note">
        <span class="pc-brand-copy">
            <strong><?= htmlspecialchars($isRu ? 'CPA backstage / issue desk' : 'CPA backstage / issue desk', ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($isRu ? 'редакционная полоса закулисья: трафик, фарм, креативы, трекеры, кейсы и готовые наборы для affiliate-команд' : 'an editorial backstage strip: traffic, farms, creatives, trackers, cases and ready-made packs for affiliate teams', ENT_QUOTES, 'UTF-8') ?></span>
        </span>
    </div>

    <div class="simple-header-center">
        <form class="simple-header-search" id="simple-header-search" method="get" action="/journal/">
            <input type="text" name="q" data-search-input placeholder="<?= htmlspecialchars($isRu ? 'Поиск: Facebook farm, антидетект, tracker setup, nutra funnel' : 'Search: Facebook farm, anti-detect, tracker setup, nutra funnel', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
            <button type="submit"><?= htmlspecialchars($isRu ? 'Найти в выпусках' : 'Search issues', ENT_QUOTES, 'UTF-8') ?></button>
            <div class="simple-search-preview" id="simple-search-preview" aria-hidden="true"></div>
        </form>
    </div>

    <div class="simple-header-right">
        <div class="simple-account">
            <a class="simple-account-trigger" href="/account/" aria-label="<?= htmlspecialchars($isRu ? 'Личный кабинет' : 'Account', ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($publicPortalAvatar !== ''): ?>
                    <img src="<?= htmlspecialchars($publicPortalAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($publicPortalUser['display_name'] ?? $publicPortalUser['username'] ?? 'Account'), ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <span class="simple-account-icon" aria-hidden="true"></span>
                <?php endif; ?>
            </a>
            <div class="simple-account-popover">
                <?php if (is_array($publicPortalUser)): ?>
                    <strong><?= htmlspecialchars((string)($publicPortalUser['display_name'] ?? $publicPortalUser['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($isRu ? 'Профиль активен. Здесь можно открыть кабинет, обновить аватарку, контакты и продолжить разговор под статьями.' : 'Profile is active. Open your account, update avatar and continue the discussion under articles.', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="simple-account-actions">
                        <a href="/account/"><?= htmlspecialchars($isRu ? 'Открыть кабинет' : 'Open account', ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                <?php else: ?>
                    <strong><?= htmlspecialchars($isRu ? 'Личный кабинет' : 'Community account', ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($isRu ? 'Войдите или зарегистрируйтесь, чтобы комментировать статьи, получить PIN-код и управлять профилем.' : 'Sign in or register to comment on articles, receive a PIN code and manage your profile.', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="simple-account-actions">
                        <a href="/account/"><?= htmlspecialchars($isRu ? 'Вход / регистрация' : 'Sign in / register', ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <button class="simple-search-toggle" type="button" aria-expanded="false" aria-controls="simple-header-search">
            <span class="simple-search-toggle-icon" aria-hidden="true"></span>
        </button>
        <button class="simple-nav-toggle" type="button" aria-expanded="false" aria-controls="simple-nav-drawer" aria-label="<?= htmlspecialchars($isRu ? 'Открыть меню журнала' : 'Open magazine menu', ENT_QUOTES, 'UTF-8') ?>">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="simple-nav-backdrop" data-simple-nav-close></div>
    <nav class="simple-nav" id="simple-nav-drawer" aria-hidden="true">
        <?php include DIR . '/template/views/simple/nav.php'; ?>
    </nav>
</header>
<script>
(function () {
    var btn = document.querySelector('.simple-nav-toggle');
    var searchToggle = document.querySelector('.simple-search-toggle');
    var nav = document.getElementById('simple-nav-drawer');
    var backdrop = document.querySelector('.simple-nav-backdrop');
    var header = document.getElementById('cp-header');
    var searchInput = document.querySelector('[data-search-input]');
    var searchForm = searchInput ? searchInput.closest('form') : null;
    var searchPreview = document.getElementById('simple-search-preview');
    var placeholderIndex = 0;
    var previewTimer = null;
    var previewAbort = null;
    var previewItems = [];
    var previewActiveIndex = -1;
    var previewSectionOrder = ['journal', 'playbooks', 'signals', 'fun'];
    var searchPreviewUrl = <?= json_encode($canonicalPath . '?header_search_preview=1', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var sectionLabels = <?= json_encode($isRu
        ? ['journal' => 'Журнал', 'playbooks' => 'Практика', 'signals' => 'Повестка', 'fun' => 'Фан']
        : ['journal' => 'Journal', 'playbooks' => 'Playbooks', 'signals' => 'Signals', 'fun' => 'Fun'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var placeholders = <?= json_encode($isRu
        ? ["Поиск: Facebook farm, антидетект, tracker setup, nutra funnel", "Поиск: TikTok creatives, BM risk, cloaking, iGaming flow", "Поиск: прогрев аккаунтов, sweepstakes, crypto angles, team SOP"]
        : ["Search: Facebook farm, anti-detect, tracker setup, nutra funnel", "Search: TikTok creatives, BM risk, cloaking, iGaming flow", "Search: account warmup, sweepstakes, crypto angles, team SOP"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'})[ch] || ch;
        });
    }
    function closePreview() {
        previewItems = [];
        previewActiveIndex = -1;
        if (header) {
            header.classList.remove('has-search-preview');
        }
        if (searchPreview) {
            searchPreview.classList.remove('is-visible');
            searchPreview.setAttribute('aria-hidden', 'true');
            searchPreview.innerHTML = '';
        }
    }
    function setSearchState(open) {
        if (!header || !searchToggle) {
            return;
        }
        var next = !!open && header.classList.contains('is-scrolled');
        header.classList.toggle('is-search-open', next);
        searchToggle.setAttribute('aria-expanded', next ? 'true' : 'false');
        if (!next) {
            header.classList.remove('has-search-preview');
        }
        if (next) {
            if (typeof closeMenu === 'function') {
                closeMenu();
            }
            window.setTimeout(function () {
                if (searchInput && typeof searchInput.focus === 'function') {
                    searchInput.focus();
                    if (typeof searchInput.select === 'function' && String(searchInput.value || '').trim() !== '') {
                        searchInput.select();
                    }
                }
            }, 20);
        }
    }
    function renderPreview(groups) {
        if (!searchPreview) {
            return;
        }
        var html = '';
        previewItems = [];
        previewActiveIndex = -1;
        if (header) {
            header.classList.add('has-search-preview');
        }
        var keys = previewSectionOrder.filter(function (key) {
            return Array.isArray(groups[key]) && groups[key].length;
        });
        Object.keys(groups).forEach(function (key) {
            if (keys.indexOf(key) === -1 && Array.isArray(groups[key]) && groups[key].length) {
                keys.push(key);
            }
        });
        keys.forEach(function (sectionKey) {
            var items = groups[sectionKey] || [];
            if (!items.length) {
                return;
            }
            html += '<section class="simple-search-preview-group">';
            html += '<div class="simple-search-preview-title">' + escapeHtml(sectionLabels[sectionKey] || sectionKey) + '</div>';
            html += '<div class="simple-search-preview-list">';
            items.forEach(function (item) {
                var idx = previewItems.length;
                previewItems.push(item);
                html += '<a class="simple-search-preview-item" data-preview-index="' + idx + '" href="' + escapeHtml(item.url || '#') + '">';
                html += '<span class="simple-search-preview-thumb">';
                if (item.image) {
                    html += '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.title || '') + '">';
                }
                html += '</span>';
                html += '<span class="simple-search-preview-copy">';
                html += '<strong>' + (item.title_html || escapeHtml(item.title || '')) + '</strong>';
                if (item.excerpt) {
                    html += '<span>' + (item.excerpt_html || escapeHtml(item.excerpt)) + '</span>';
                }
                html += '</span>';
                html += '</a>';
            });
            html += '</div></section>';
        });
        searchPreview.innerHTML = html;
        searchPreview.classList.add('is-visible');
        searchPreview.setAttribute('aria-hidden', 'false');
    }
    function highlightPreview(index) {
        previewActiveIndex = index;
        if (!searchPreview) {
            return;
        }
        searchPreview.querySelectorAll('.simple-search-preview-item').forEach(function (node) {
            node.classList.toggle('is-active', Number(node.getAttribute('data-preview-index')) === index);
        });
        if (index >= 0) {
            var active = searchPreview.querySelector('.simple-search-preview-item.is-active');
            if (active && typeof active.scrollIntoView === 'function') {
                active.scrollIntoView({ block: 'nearest' });
            }
        }
    }
    function fetchPreview(query) {
        if (!searchPreview) {
            return;
        }
        if (previewAbort && typeof previewAbort.abort === 'function') {
            previewAbort.abort();
        }
        previewAbort = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        fetch(searchPreviewUrl + '&q=' + encodeURIComponent(query), {
            credentials: 'same-origin',
            signal: previewAbort ? previewAbort.signal : undefined
        })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) {
                if (!data || !Array.isArray(data.items)) {
                    closePreview();
                    return;
                }
                if (!data.items.length) {
                    searchPreview.innerHTML = '<div class="simple-search-preview-empty"><?= htmlspecialchars($isRu ? 'Ничего не найдено' : 'No results found', ENT_QUOTES, 'UTF-8') ?></div>';
                    searchPreview.classList.add('is-visible');
                    searchPreview.setAttribute('aria-hidden', 'false');
                    if (header) {
                        header.classList.add('has-search-preview');
                    }
                    previewItems = [];
                    previewActiveIndex = -1;
                    return;
                }
                var grouped = {};
                data.items.forEach(function (item) {
                    var key = item.section || 'journal';
                    if (!grouped[key]) {
                        grouped[key] = [];
                    }
                    grouped[key].push(item);
                });
                renderPreview(grouped);
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') {
                    return;
                }
                closePreview();
            });
    }
    if (header) {
        var measuredHeaderOffset = 0;
        var measureExpandedHeaderOffset = function () {
            var hadScrolled = header.classList.contains('is-scrolled');
            if (hadScrolled) {
                header.classList.remove('is-scrolled');
            }
            var styles = window.getComputedStyle(header);
            var marginTop = parseFloat(styles.marginTop || '0') || 0;
            var marginBottom = parseFloat(styles.marginBottom || '0') || 0;
            var topOffset = parseFloat(styles.top || '0') || 0;
            measuredHeaderOffset = Math.ceil(header.offsetHeight + marginTop + marginBottom + Math.max(0, topOffset));
            document.documentElement.style.setProperty('--simple-header-height', String(Math.ceil(header.offsetHeight)) + 'px');
            if (hadScrolled) {
                header.classList.add('is-scrolled');
            }
        };
        var syncHeader = function () {
            var isScrolled = window.scrollY > 18;
            header.classList.toggle('is-scrolled', isScrolled);
            document.body.style.paddingTop = String(measuredHeaderOffset) + 'px';
            if (!isScrolled) {
                header.classList.remove('is-search-open');
                if (searchToggle) {
                    searchToggle.setAttribute('aria-expanded', 'false');
                }
            }
        };
        measureExpandedHeaderOffset();
        window.addEventListener('scroll', syncHeader, { passive: true });
        window.addEventListener('resize', function () {
            measureExpandedHeaderOffset();
            syncHeader();
        }, { passive: true });
        syncHeader();
    }
    if (searchInput && Array.isArray(placeholders) && placeholders.length > 1) {
        window.setInterval(function () {
            placeholderIndex = (placeholderIndex + 1) % placeholders.length;
            searchInput.setAttribute('placeholder', placeholders[placeholderIndex]);
        }, 2600);
    }
    if (searchInput && searchPreview) {
        searchInput.addEventListener('input', function () {
            var value = String(searchInput.value || '').trim();
            if (previewTimer) {
                window.clearTimeout(previewTimer);
            }
            if (value.length < 2) {
                closePreview();
                return;
            }
            previewTimer = window.setTimeout(function () {
                fetchPreview(value);
            }, 180);
        });
        searchInput.addEventListener('focus', function () {
            var value = String(searchInput.value || '').trim();
            if (value.length >= 2 && !searchPreview.classList.contains('is-visible')) {
                fetchPreview(value);
            }
        });
        searchInput.addEventListener('keydown', function (event) {
            if (!searchPreview.classList.contains('is-visible')) {
                return;
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlightPreview(Math.min(previewItems.length - 1, previewActiveIndex + 1));
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlightPreview(Math.max(-1, previewActiveIndex - 1));
            } else if (event.key === 'Enter' && previewActiveIndex >= 0 && previewItems[previewActiveIndex] && previewItems[previewActiveIndex].url) {
                event.preventDefault();
                window.location.href = previewItems[previewActiveIndex].url;
            } else if (event.key === 'Escape') {
                closePreview();
            }
        });
        document.addEventListener('click', function (event) {
            if (!searchForm) {
                return;
            }
            if (searchToggle && searchToggle.contains(event.target)) {
                return;
            }
            if (!searchForm.contains(event.target)) {
                closePreview();
                if (header && header.classList.contains('is-search-open') && !header.contains(event.target)) {
                    setSearchState(false);
                }
            }
        });
        searchPreview.addEventListener('mouseenter', function (event) {
            var item = event.target.closest('.simple-search-preview-item');
            if (!item) {
                return;
            }
            highlightPreview(Number(item.getAttribute('data-preview-index')));
        });
    }
    if (!btn || !nav) {
        return;
    }
    function setMenuState(open) {
        document.body.classList.toggle('simple-nav-open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        nav.setAttribute('aria-hidden', open ? 'false' : 'true');
    }
    function closeMenu() {
        setMenuState(false);
    }
    if (searchToggle) {
        searchToggle.addEventListener('click', function () {
            var isOpen = header && header.classList.contains('is-search-open');
            setSearchState(!isOpen);
        });
    }
    btn.addEventListener('click', function () {
        var isOpen = document.body.classList.contains('simple-nav-open');
        setSearchState(false);
        setMenuState(!isOpen);
    });
    if (backdrop) {
        backdrop.addEventListener('click', closeMenu);
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setSearchState(false);
            closeMenu();
        }
    });
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
    setMenuState(false);
})();
</script>



<?php include DIR . '/template/views/simple/partials/terrain_polygon_map.php'; ?>

