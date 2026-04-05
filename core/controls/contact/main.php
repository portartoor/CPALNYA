<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$host = preg_replace('/^www\./', '', $host);
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$isRu = (bool)preg_match('/\.ru$/', (string)$host);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');

if (empty($ModelPage['title'])) {
    $ModelPage['title'] = $isRu ? 'Контакты' : 'Contact';
}
if (empty($ModelPage['description'])) {
    $ModelPage['description'] = $isRu
        ? 'Форма связи для запросов по услугам, проектам и сотрудничеству.'
        : 'Contact form for services, projects and collaboration requests.';
}
if (empty($ModelPage['canonical'])) {
    $ModelPage['canonical'] = $baseUrl . '/contact/';
}

$wizardData = [
    'lang' => $isRu ? 'ru' : 'en',
    'groups' => [],
    'items_by_group' => [],
];

if (isset($FRMWRK) && is_object($FRMWRK) && method_exists($FRMWRK, 'DB') && function_exists('public_services_ensure_schema')) {
    $db = $FRMWRK->DB();
    if ($db && public_services_ensure_schema($db)) {
        $lang = function_exists('public_services_resolve_lang')
            ? public_services_resolve_lang((string)$host)
            : ($isRu ? 'ru' : 'en');
        $wizardData['lang'] = $lang;
        if (function_exists('public_services_fetch_groups')) {
            $wizardData['groups'] = (array)public_services_fetch_groups($FRMWRK, (string)$host, (string)$lang);
        }
        $items = function_exists('public_services_fetch_published_page')
            ? (array)public_services_fetch_published_page($FRMWRK, (string)$host, (string)$lang, 1, 500, '')
            : [];
        $grouped = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $groupCode = trim((string)($item['service_group'] ?? ''));
            if ($groupCode === '') {
                $groupCode = 'general';
            }
            if (!isset($grouped[$groupCode])) {
                $grouped[$groupCode] = [
                    'group' => [
                        'code' => $groupCode,
                        'label' => function_exists('public_services_group_label')
                            ? public_services_group_label($groupCode, (string)$lang)
                            : $groupCode,
                    ],
                    'items' => [],
                ];
            }
            $grouped[$groupCode]['items'][] = [
                'title' => (string)($item['title'] ?? ''),
                'slug' => (string)($item['slug'] ?? ''),
            ];
        }
        $wizardData['items_by_group'] = $grouped;
    }
}

$ModelPage['contact_wizard'] = $wizardData;
