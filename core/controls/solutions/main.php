<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$db = isset($FRMWRK) && is_object($FRMWRK) && method_exists($FRMWRK, 'DB') ? $FRMWRK->DB() : null;
$host = public_portal_host();
$lang = public_portal_lang($host);
$path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/solutions/'), PHP_URL_PATH);
$path = is_string($path) ? trim($path) : '/solutions/';
$segments = array_values(array_filter(explode('/', (string)$path), static function ($value): bool {
    return $value !== '';
}));

$tab = trim((string)($_GET['tab'] ?? ''));
$selectedCode = '';
if (isset($segments[0]) && strtolower((string)$segments[0]) === 'solutions') {
    $tab = trim((string)($segments[1] ?? $tab));
    $selectedCode = trim((string)($segments[2] ?? ''));
}
if ($tab === '') {
    $tab = 'downloads';
}
$tab = in_array($tab, ['downloads', 'articles'], true) ? $tab : 'downloads';
$type = $tab === 'articles' ? 'article' : 'download';

$solutionsData = [
    'host' => $host,
    'lang' => $lang,
    'tab' => $tab,
    'type' => $type,
    'items' => function_exists('public_portal_fetch_solutions') ? public_portal_fetch_solutions($FRMWRK, $host, $lang, $type) : [],
    'selected' => null,
];

if ($selectedCode !== '' && function_exists('public_portal_fetch_solution_by_code')) {
    $solutionsData['selected'] = public_portal_fetch_solution_by_code($FRMWRK, $host, $lang, $selectedCode);
}

$isRu = ($lang === 'ru');
$baseUrl = ((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http') . '://' . ($host !== '' ? $host : 'localhost');
$canonical = $baseUrl . '/solutions/' . ($tab === 'articles' ? 'articles/' : 'downloads/');
if (is_array($solutionsData['selected'])) {
    $canonical .= rawurlencode((string)($solutionsData['selected']['slug'] ?? '')) . '/';
}

$ModelPage['title'] = $isRu
    ? ($tab === 'articles' ? 'Готовые разборы и мануалы по CPA' : 'Готовые решения для арбитража трафика')
    : ($tab === 'articles' ? 'Ready-made CPA breakdowns and playbooks' : 'Ready-made solutions for affiliate traffic');
$ModelPage['description'] = $isRu
    ? 'База готовых решений ЦПАЛЬНЯ: загрузки, шаблоны, аналитические каркасы и практические статьи для арбитражных команд.'
    : 'ЦПАЛЬНЯ ready-made base: downloads, templates, analytics frameworks and practical articles for affiliate teams.';
$ModelPage['canonical'] = $canonical;
$ModelPage['solutions'] = $solutionsData;
$ModelPage['portal_user'] = null;
$ModelPage['portal_flash'] = function_exists('public_portal_flash_get') ? public_portal_flash_get('portal') : [];
$ModelPage['portal_comments'] = (array)($solutionsData['comments'] ?? []);
