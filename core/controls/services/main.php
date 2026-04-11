<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$servicesData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'page' => 1,
    'per_page' => 12,
    'total' => 0,
    'total_pages' => 1,
    'groups' => [],
    'group' => '',
    'items' => [],
    'items_by_group' => [],
    'selected' => null,
    'related' => [],
    'error' => '',
];

if (strpos((string)$servicesData['host'], ':') !== false) {
    $servicesData['host'] = explode(':', (string)$servicesData['host'], 2)[0];
}

if (!function_exists('public_services_ensure_schema')) {
    $servicesData['error'] = 'public_services module not found';
    $ModelPage['services_catalog'] = $servicesData;
    return;
}

$db = $FRMWRK->DB();
if (!$db || !public_services_ensure_schema($db)) {
    $servicesData['error'] = 'public_services table is missing';
    $ModelPage['services_catalog'] = $servicesData;
    return;
}

$servicesData['lang'] = public_services_resolve_lang((string)$servicesData['host']);
$servicesData['page'] = 1;
$servicesData['per_page'] = 500;
$servicesData['group'] = trim((string)($_GET['group'] ?? ''));
if ($servicesData['group'] !== '') {
    $servicesData['group'] = public_services_normalize_group((string)$servicesData['group']);
}

if (
    function_exists('public_services_fetch_published_count')
    && function_exists('public_services_seed_default_catalog')
    && public_services_fetch_published_count(
        $FRMWRK,
        (string)$servicesData['host'],
        (string)$servicesData['lang']
    ) === 0
) {
    public_services_seed_default_catalog(
        $FRMWRK,
        (string)$servicesData['host'],
        (string)$servicesData['lang']
    );
}

$slugParam = trim((string)($_GET['slug'] ?? ''));
if ($slugParam === '') {
    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? trim($requestPath) : '';
    $segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
        return $value !== '';
    }));
    if (isset($segments[0]) && strtolower((string)$segments[0]) === 'services' && isset($segments[1])) {
        $slugParam = trim((string)$segments[1]);
    }
}

if ($slugParam !== '' && function_exists('public_services_fetch_published_by_slug')) {
    $selected = public_services_fetch_published_by_slug(
        $FRMWRK,
        (string)$servicesData['host'],
        $slugParam,
        (string)$servicesData['lang']
    );
    if (is_array($selected)) {
        $servicesData['selected'] = $selected;
        $selectedGroup = trim((string)($selected['service_group'] ?? ''));
        $selectedSlug = trim((string)($selected['slug'] ?? ''));
        if (
            $selectedGroup !== ''
            && function_exists('public_services_fetch_related_by_group')
        ) {
            $servicesData['related'] = public_services_fetch_related_by_group(
                $FRMWRK,
                (string)$servicesData['host'],
                (string)$servicesData['lang'],
                $selectedGroup,
                $selectedSlug,
                3
            );
        }
    }
}

$servicesData['groups'] = public_services_fetch_groups(
    $FRMWRK,
    (string)$servicesData['host'],
    (string)$servicesData['lang']
);

$servicesData['total'] = public_services_fetch_published_count(
    $FRMWRK,
    (string)$servicesData['host'],
    (string)$servicesData['lang'],
    (string)$servicesData['group']
);
$servicesData['total_pages'] = 1;
$servicesData['items'] = public_services_fetch_published_page(
    $FRMWRK,
    (string)$servicesData['host'],
    (string)$servicesData['lang'],
    (int)$servicesData['page'],
    (int)$servicesData['per_page'],
    (string)$servicesData['group']
);

$groupedItems = [];
foreach ((array)$servicesData['groups'] as $groupRow) {
    $groupCode = trim((string)($groupRow['code'] ?? ''));
    if ($groupCode === '') {
        continue;
    }
    $groupedItems[$groupCode] = [
        'group' => [
            'code' => $groupCode,
            'label' => (string)($groupRow['label'] ?? $groupCode),
            'count' => (int)($groupRow['count'] ?? 0),
        ],
        'items' => [],
    ];
}

foreach ((array)$servicesData['items'] as $itemRow) {
    $groupCode = trim((string)($itemRow['service_group'] ?? ''));
    if ($groupCode === '') {
        $groupCode = 'general';
    }
    if (!isset($groupedItems[$groupCode])) {
        $groupedItems[$groupCode] = [
            'group' => [
                'code' => $groupCode,
                'label' => function_exists('public_services_group_label')
                    ? public_services_group_label($groupCode, (string)$servicesData['lang'])
                    : $groupCode,
                'count' => 0,
            ],
            'items' => [],
        ];
    }
    $groupedItems[$groupCode]['items'][] = $itemRow;
}

foreach ($groupedItems as $code => $block) {
    $groupedItems[$code]['group']['count'] = count((array)($block['items'] ?? []));
}
$servicesData['items_by_group'] = $groupedItems;

$flatItems = [];
foreach ($groupedItems as $groupBlock) {
    $groupMeta = (array)($groupBlock['group'] ?? []);
    $groupList = (array)($groupBlock['items'] ?? []);
    if (empty($groupList)) {
        continue;
    }
    $flatItems[] = [
        '__group_header' => 1,
        'service_group' => (string)($groupMeta['code'] ?? 'general'),
        'group_label' => (string)($groupMeta['label'] ?? ($groupMeta['code'] ?? 'General')),
        'group_count' => (int)($groupMeta['count'] ?? count($groupList)),
    ];
    foreach ($groupList as $itemRow) {
        $flatItems[] = $itemRow;
    }
}
if (!empty($flatItems)) {
    $servicesData['items'] = $flatItems;
}

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = (string)$servicesData['host'];
$host = preg_replace('/^www\./', '', $host);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$isRu = ((string)$servicesData['lang'] === 'ru');

if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$selected = is_array($servicesData['selected'] ?? null) ? $servicesData['selected'] : null;
if ($selected) {
    $serviceTitle = trim((string)($selected['title'] ?? ''));
    if ($serviceTitle === '') {
        $serviceTitle = $isRu ? 'Услуга' : 'Service';
    }
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $serviceTitle;
    }
    if (empty($ModelPage['description'])) {
        $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selected['excerpt_html'] ?? $selected['content_html'] ?? ''))));
        if ($desc === '') {
            $desc = $isRu ? 'Описание услуги и формат реализации.' : 'Service details and delivery format.';
        }
        $ModelPage['description'] = $desc;
    }
    if (empty($ModelPage['canonical'])) {
        $slug = trim((string)($selected['slug'] ?? ''));
        $ModelPage['canonical'] = $baseUrl . '/services/' . rawurlencode($slug) . '/';
    }
} else {
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $isRu ? 'Услуги' : 'Services';
    }
    if (empty($ModelPage['description'])) {
        $ModelPage['description'] = $isRu
            ? 'Каталог услуг с группами и подробным описанием формата работы.'
            : 'Service catalog with groups and detailed delivery descriptions.';
    }
    if (empty($ModelPage['canonical'])) {
        $canonicalPath = '/services/';
        $query = [];
        if ((string)$servicesData['group'] !== '') {
            $query['group'] = (string)$servicesData['group'];
        }
        if (!empty($query)) {
            $canonicalPath .= '?' . http_build_query($query);
        }
        $ModelPage['canonical'] = $baseUrl . $canonicalPath;
    }
}

$ModelPage['services_catalog'] = $servicesData;
