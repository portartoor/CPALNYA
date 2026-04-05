<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

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
    if (is_array($solutionsData['selected'])) {
        $selectedId = (int)($solutionsData['selected']['id'] ?? 0);
        if ($selectedId > 0 && function_exists('public_portal_record_view')) {
            $solutionsData['selected']['view_count'] = public_portal_record_view($FRMWRK, 'solutions', $selectedId);
        }
        if ($selectedId > 0 && function_exists('public_portal_fetch_comments')) {
            $solutionsData['comments'] = public_portal_fetch_comments($FRMWRK, 'solutions', $selectedId);
            $solutionsData['comment_count'] = function_exists('public_portal_comment_count')
                ? public_portal_comment_count((array)$solutionsData['comments'])
                : count((array)$solutionsData['comments']);
        }
    }
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
    ? 'База готовых решений CPALNYA: загрузки, шаблоны, аналитические каркасы и практические статьи для арбитражных команд.'
    : 'CPALNYA ready-made base: downloads, templates, analytics frameworks and practical articles for affiliate teams.';
$ModelPage['canonical'] = $canonical;
$ModelPage['solutions'] = $solutionsData;
$ModelPage['portal_user'] = function_exists('public_portal_current_user') ? public_portal_current_user($FRMWRK) : null;
$ModelPage['portal_flash'] = function_exists('public_portal_flash_get') ? public_portal_flash_get('portal') : [];
$ModelPage['portal_comments'] = (array)($solutionsData['comments'] ?? []);
