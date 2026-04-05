<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$projectsData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'total' => 0,
    'items' => [],
    'selected' => null,
    'selected_code' => '',
    'error' => '',
];

if (strpos((string)$projectsData['host'], ':') !== false) {
    $projectsData['host'] = explode(':', (string)$projectsData['host'], 2)[0];
}

if (!function_exists('public_projects_ensure_schema')) {
    $projectsData['error'] = 'public_projects module not found';
    $ModelPage['projects_catalog'] = $projectsData;
    return;
}

$db = $FRMWRK->DB();
if (!$db || !public_projects_ensure_schema($db)) {
    $projectsData['error'] = 'public_projects table is missing';
    $ModelPage['projects_catalog'] = $projectsData;
    return;
}

$projectsData['lang'] = public_projects_resolve_lang((string)$projectsData['host']);
$selectedCode = trim((string)($_GET['code'] ?? ''));
if ($selectedCode === '') {
    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? trim($requestPath) : '';
    $segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
        return $value !== '';
    }));
    if (isset($segments[0]) && strtolower((string)$segments[0]) === 'projects' && isset($segments[1])) {
        $selectedCode = trim((string)$segments[1]);
    }
}
if ($selectedCode !== '') {
    $selectedCode = public_projects_slugify($selectedCode);
    if ($selectedCode !== '') {
        $projectsData['selected'] = public_projects_fetch_published_by_code(
            $FRMWRK,
            (string)$projectsData['host'],
            $selectedCode,
            (string)$projectsData['lang']
        );
        $projectsData['selected_code'] = $selectedCode;
    }
}

$projectsData['items'] = public_projects_fetch_published(
    $FRMWRK,
    (string)$projectsData['host'],
    (string)$projectsData['lang']
);
if (empty($projectsData['items']) && function_exists('public_projects_seed_default_products')) {
    public_projects_seed_default_products(
        $FRMWRK,
        (string)$projectsData['host'],
        (string)$projectsData['lang']
    );
    $projectsData['items'] = public_projects_fetch_published(
        $FRMWRK,
        (string)$projectsData['host'],
        (string)$projectsData['lang']
    );
}
$projectsData['total'] = count((array)$projectsData['items']);

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = preg_replace('/^www\./', '', (string)$projectsData['host']);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$isRu = ((string)$projectsData['lang'] === 'ru');
$selectedProduct = is_array($projectsData['selected'] ?? null) ? $projectsData['selected'] : null;

if ($selectedProduct) {
    $productTitle = trim((string)($selectedProduct['title'] ?? ''));
    if ($productTitle === '') {
        $productTitle = $isRu ? 'Продукт' : 'Product';
    }
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $productTitle;
    }
    if (empty($ModelPage['description'])) {
        $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selectedProduct['excerpt_html'] ?? $selectedProduct['result_summary'] ?? ''))));
        if ($desc === '') {
            $desc = $isRu
                ? 'Продукт с описанием возможностей, интеграций, тарифов и сценариев внедрения в backend.'
                : 'Product overview with integrations, pricing and backend implementation scenarios.';
        }
        $ModelPage['description'] = $desc;
    }
    if (empty($ModelPage['keywords'])) {
        $ModelPage['keywords'] = $isRu
            ? 'продукт, интеграция backend, тарифы, b2b продукт, внедрение'
            : 'product, backend integration, pricing, b2b product, implementation';
    }
    if (empty($ModelPage['canonical'])) {
        $code = trim((string)($selectedProduct['symbolic_code'] ?? $selectedProduct['slug'] ?? ''));
        $ModelPage['canonical'] = $baseUrl . '/projects/' . rawurlencode($code) . '/';
    }
} else {
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $isRu
            ? 'Продукты для геоаналитики, antifraud и AI-автоматизации'
            : 'Products for geo intelligence, antifraud and AI automation';
    }
    if (empty($ModelPage['description'])) {
        $ModelPage['description'] = $isRu
            ? 'Каталог продуктов: geoip.space, postforge.ru и ANTIFRAUD TRACKER. Тарифы, интеграции в любой backend и форма заказа.'
            : 'Product catalog: geoip.space, postforge.ru and ANTIFRAUD TRACKER. Pricing, backend integrations and order form.';
    }
    if (empty($ModelPage['keywords'])) {
        $ModelPage['keywords'] = $isRu
            ? 'продукты antifraud, geoip api, ai seo платформа, трекинг антифрод инцидентов, интеграция в backend'
            : 'antifraud products, geoip api, ai seo platform, antifraud incident tracker, backend integration';
    }
    if (empty($ModelPage['canonical'])) {
        $ModelPage['canonical'] = $baseUrl . '/projects/';
    }
}

$ModelPage['projects_catalog'] = $projectsData;
