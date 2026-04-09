<?php
$publicNavSite = (string)($publicLayoutSite ?? (function_exists('public_site_kind') ? public_site_kind() : 'geo'));
$publicNavPage = (string)($publicLayoutPage ?? '');
$publicGeoBtnClass = ($publicNavSite === 'geo' && $publicNavPage === 'home') ? 'btn ghost' : 'btn';
$publicNavLang = trim((string)($publicLayoutLang ?? ''));
$publicNavHost = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($publicNavHost, ':') !== false) {
    $publicNavHost = explode(':', $publicNavHost, 2)[0];
}
$publicIsRu = ($publicNavLang === 'ru' || preg_match('/\.ru$/', $publicNavHost) === 1);
$t = static function (string $ru, string $en) use ($publicIsRu): string {
    return $publicIsRu ? $ru : $en;
};
$publicExamplesQuery = ($publicNavLang !== '') ? ['lang' => $publicNavLang] : [];
$publicHomeHref = function_exists('public_site_href') ? public_site_href('/') : '/';
$publicDocsHref = function_exists('public_site_href') ? public_site_href('/docs/') : '/docs/';
$publicExamplesHref = function_exists('public_site_href') ? public_site_href('/examples/', $publicExamplesQuery) : '/examples/';
$publicToolsHref = function_exists('public_site_href') ? public_site_href('/tools/', $publicExamplesQuery) : '/tools/';
$publicDashboardHref = function_exists('public_site_href') ? public_site_href('/dashboard/') : '/dashboard/';
?>
<nav class="actions">
<?php if ($publicNavSite === 'apigeo'): ?>
    <?php if ($publicNavPage !== 'home'): ?>
        <a class="btn" href="<?= htmlspecialchars($publicHomeHref) ?>"><?= htmlspecialchars($t('Главная', 'Home')) ?></a>
    <?php endif; ?>
    <?php if ($publicNavPage !== 'examples'): ?>
        <a class="btn" href="<?= htmlspecialchars($publicExamplesHref) ?>"><?= htmlspecialchars($t('Примеры', 'Examples')) ?></a>
    <?php endif; ?>
    <?php if ($publicNavPage !== 'tools'): ?>
        <a class="btn" href="<?= htmlspecialchars($publicToolsHref) ?>"><?= htmlspecialchars($t('Инструменты', 'Tools')) ?></a>
    <?php endif; ?>
    <?php if ($publicNavPage !== 'docs'): ?>
        <a class="btn" href="<?= htmlspecialchars($publicDocsHref) ?>"><?= htmlspecialchars($t('Документация API', 'API Reference')) ?></a>
    <?php endif; ?>
    <a class="btn primary" href="<?= htmlspecialchars($publicDashboardHref) ?>"><?= htmlspecialchars($t('Открыть кабинет', 'Open Dashboard')) ?></a>
<?php else: ?>
    <?php if ($publicNavPage !== 'home'): ?>
        <a class="btn" href="<?= htmlspecialchars($publicHomeHref) ?>"><?= htmlspecialchars($t('Главная', 'Home')) ?></a>
    <?php endif; ?>
    <?php if ($publicNavPage !== 'docs'): ?>
        <a class="<?= htmlspecialchars($publicGeoBtnClass) ?>" href="<?= htmlspecialchars($publicDocsHref) ?>"><?= htmlspecialchars($t('Документация API', 'API Documentation')) ?></a>
    <?php endif; ?>
    <?php if ($publicNavPage !== 'examples'): ?>
        <a class="<?= htmlspecialchars($publicGeoBtnClass) ?>" href="<?= htmlspecialchars($publicExamplesHref) ?>"><?= htmlspecialchars($t('Примеры', 'Examples')) ?></a>
    <?php endif; ?>
    <?php if ($publicNavPage !== 'tools'): ?>
        <a class="<?= htmlspecialchars($publicGeoBtnClass) ?>" href="<?= htmlspecialchars($publicToolsHref) ?>"><?= htmlspecialchars($t('Инструменты', 'Tools')) ?></a>
    <?php endif; ?>
    <a class="btn primary" href="<?= htmlspecialchars($publicDashboardHref) ?>"><?= htmlspecialchars($t('Открыть кабинет', 'Open Dashboard')) ?></a>
<?php endif; ?>
</nav>
