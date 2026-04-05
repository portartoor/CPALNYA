<?php
$hostAuditView = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($hostAuditView, ':') !== false) {
    $hostAuditView = explode(':', $hostAuditView, 2)[0];
}
$isRuAuditView = (bool)preg_match('/\.ru$/', $hostAuditView);

$auditInput = trim((string)($ModelPage['audit_input'] ?? ''));
$auditRequested = !empty($ModelPage['audit_requested']);
$auditError = trim((string)($ModelPage['audit_error'] ?? ''));
$auditReport = is_array($ModelPage['audit_report'] ?? null) ? $ModelPage['audit_report'] : null;
$auditDebug = !empty($ModelPage['audit_debug']);
$auditStoreStatus = (string)($ModelPage['audit_store_status'] ?? '');
$contactToken = (string)($ModelPage['audit_contact_token'] ?? '');
$contactType = (string)($ModelPage['audit_contact_type'] ?? '');
$contactMsg = (string)($ModelPage['audit_contact_message'] ?? '');
$returnPath = (string)($ModelPage['audit_return_path'] ?? '/audit/');

$tt = static function (string $ru, string $en) use ($isRuAuditView): string {
    return $isRuAuditView ? $ru : $en;
};
$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
$auditLocalize = static function (string $text) use ($isRuAuditView): string {
    if (!$isRuAuditView) {
        return $text;
    }
    $text = trim($text);
    if ($text === '') {
        return $text;
    }
    $map = [
        'HTTPS page is not reachable with 2xx/3xx' => 'HTTPS-страница недоступна со статусом 2xx/3xx',
        'Fix TLS/cert/edge routing and ensure canonical URL returns 200.' => 'Проверьте TLS/сертификат/маршрутизацию на edge и добейтесь ответа 200 для canonical URL.',
        'HTTP does not redirect to HTTPS' => 'Нет редиректа с HTTP на HTTPS',
        'HTTP final URL is not HTTPS.' => 'Итоговый URL по HTTP не переходит на HTTPS.',
        'Configure permanent 301 redirect from HTTP to HTTPS.' => 'Настройте постоянный 301-редирект с HTTP на HTTPS.',
        'Page is blocked by meta robots noindex' => 'Страница закрыта meta robots noindex',
        'meta robots contains noindex.' => 'В meta robots обнаружен noindex.',
        'Remove noindex on indexable landing/content pages.' => 'Уберите noindex на страницах, которые должны индексироваться.',
        'robots.txt is missing or inaccessible' => 'robots.txt отсутствует или недоступен',
        'robots.txt is not reachable with 2xx status.' => 'robots.txt недоступен со статусом 2xx.',
        'Expose robots.txt at site root with valid directives.' => 'Разместите robots.txt в корне сайта с корректными директивами.',
        'sitemap.xml is missing or inaccessible' => 'sitemap.xml отсутствует или недоступен',
        'sitemap.xml is not reachable with 2xx status.' => 'sitemap.xml недоступен со статусом 2xx.',
        'Publish sitemap.xml and include canonical indexable URLs.' => 'Опубликуйте sitemap.xml и оставьте в нем только канонические индексируемые URL.',
        'Sitemap XML is invalid' => 'Некорректный Sitemap XML',
        'XML parsing failed for sitemap content.' => 'Не удалось разобрать XML содержимое sitemap.',
        'Fix XML syntax and keep sitemap compliant.' => 'Исправьте синтаксис XML и приведите sitemap к валидному формату.',
        'Canonical is missing' => 'Canonical отсутствует',
        'No rel=canonical found.' => 'Тег rel=canonical не найден.',
        'Add absolute canonical URL for this page.' => 'Добавьте абсолютный canonical URL для этой страницы.',
        'Canonical is not absolute URL' => 'Canonical задан не абсолютным URL',
        'Canonical should be absolute HTTPS URL.' => 'Canonical должен быть абсолютным HTTPS URL.',
        'Use absolute canonical links.' => 'Используйте абсолютные canonical-ссылки.',
        'hreflang has quality issues' => 'Проблемы в hreflang',
        'Invalid or duplicate hreflang values detected.' => 'Обнаружены некорректные или дублирующиеся значения hreflang.',
        'Normalize hreflang codes and keep one URL per language.' => 'Нормализуйте коды hreflang и оставьте по одному URL на язык.',
        'H1 structure is suboptimal' => 'Структура H1 не оптимальна',
        'Use one descriptive H1 matching page intent.' => 'Используйте один содержательный H1, соответствующий интенту страницы.',
        'Title length is outside recommended range' => 'Длина Title вне рекомендуемого диапазона',
        'Target 25-65 characters with clear intent.' => 'Рекомендуемая длина: 25-65 символов с четким смыслом.',
        'Meta description length is outside recommended range' => 'Длина meta description вне рекомендуемого диапазона',
        'Target 80-170 characters with CTA/value proposition.' => 'Рекомендуемая длина: 80-170 символов с ценностным посылом и CTA.',
        'High TTFB' => 'Высокий TTFB',
        'Review backend latency, cache strategy and DB indexes.' => 'Проверьте задержки backend, стратегию кеширования и индексы БД.',
        'robots.txt blocks all crawlers' => 'robots.txt блокирует всех роботов',
        'Detected "Disallow: /" in robots.txt.' => 'В robots.txt обнаружено "Disallow: /".',
        'Allow indexing for public sections and block only private paths.' => 'Разрешите индексацию публичных разделов, блокируйте только приватные пути.',
        'Images without ALT' => 'Изображения без ALT',
        'Add meaningful alt attributes for accessibility and image SEO.' => 'Добавьте осмысленные alt-атрибуты для доступности и image SEO.',
        'Images missing width/height' => 'У изображений отсутствуют width/height',
        'Set width/height to reduce CLS and improve rendering stability.' => 'Укажите width/height для снижения CLS и стабильной отрисовки.',
        'Mixed content links detected' => 'Обнаружен mixed content в ссылках',
        'Replace internal HTTP links with HTTPS.' => 'Замените внутренние HTTP-ссылки на HTTPS.',
        'Insecure form actions detected' => 'Обнаружены небезопасные form action',
        'Change form actions to HTTPS endpoints only.' => 'Переведите form action только на HTTPS endpoints.',
        'Header is not present in HTTPS response.' => 'Заголовок отсутствует в HTTPS-ответе.',
        'Adjust <title> length to 25-65 characters for stable SERP rendering.' => 'Приведите длину <title> к 25-65 символам для стабильного отображения в выдаче.',
        'Keep meta description in 80-170 characters and include core value proposition.' => 'Держите meta description в диапазоне 80-170 символов и добавляйте ценностный посыл.',
        'Use exactly one H1 per page and align it with the primary intent.' => 'Используйте ровно один H1 на странице и синхронизируйте его с основным интентом.',
        'Add rel=canonical to avoid duplicate indexing signals.' => 'Добавьте rel=canonical, чтобы избежать дублирующих сигналов индексации.',
        'Reduce TTFB (target < 800ms): review cache strategy, DB indexes, and edge/CDN configuration.' => 'Снизьте TTFB (цель < 800 мс): проверьте кеширование, индексы БД и edge/CDN конфигурацию.',
        'Expose robots.txt at site root and validate crawl directives.' => 'Разместите robots.txt в корне сайта и проверьте директивы для обхода.',
        'Publish sitemap.xml and ensure 200 status for indexability.' => 'Опубликуйте sitemap.xml и обеспечьте ответ 200 для индексации.',
        'Declare sitemap location inside robots.txt.' => 'Укажите путь к sitemap внутри robots.txt.',
        'Enable HSTS header for HTTPS hardening.' => 'Включите HSTS для усиления HTTPS-защиты.',
        'Add Content-Security-Policy to reduce XSS/injection surface.' => 'Добавьте Content-Security-Policy для снижения рисков XSS/инъекций.',
        'Set X-Content-Type-Options: nosniff.' => 'Установите X-Content-Type-Options: nosniff.',
        'Provide ALT attributes for all images (accessibility + image search context).' => 'Добавьте ALT для всех изображений (доступность + контекст для image search).',
        'Add structured data (JSON-LD) for eligible entities/pages.' => 'Добавьте структурированные данные (JSON-LD) для релевантных страниц.',
        'Replace HTTP links on HTTPS pages to remove mixed content signals.' => 'Замените HTTP-ссылки на HTTPS, чтобы убрать сигналы mixed content.',
        'Move all form actions to HTTPS endpoints.' => 'Перенесите все form action на HTTPS endpoints.',
        'Enforce strict 301 redirect from HTTP to HTTPS.' => 'Настройте строгий 301-редирект с HTTP на HTTPS.',
    ];
    if (isset($map[$text])) {
        return $map[$text];
    }
    if (preg_match('/^Current status:\s*(\d+)\.?$/i', $text, $m)) {
        return 'Текущий статус: ' . $m[1] . '.';
    }
    if (preg_match('/^Expected exactly one H1, got\s*(\d+)\.?$/i', $text, $m)) {
        return 'Ожидается ровно один H1, найдено: ' . $m[1] . '.';
    }
    if (preg_match('/^Current title length:\s*(\d+)\.?$/i', $text, $m)) {
        return 'Текущая длина title: ' . $m[1] . '.';
    }
    if (preg_match('/^Current length:\s*(\d+)\.?$/i', $text, $m)) {
        return 'Текущая длина: ' . $m[1] . '.';
    }
    if (preg_match('/^Current TTFB:\s*(\d+)\s*ms\.?$/i', $text, $m)) {
        return 'Текущий TTFB: ' . $m[1] . ' мс.';
    }
    if (preg_match('/^Images without alt:\s*(\d+)\.?$/i', $text, $m)) {
        return 'Изображений без alt: ' . $m[1] . '.';
    }
    if (preg_match('/^Count:\s*(\d+)\.?$/i', $text, $m)) {
        return 'Количество: ' . $m[1] . '.';
    }
    if (preg_match('/^HTTP links on HTTPS page:\s*(\d+)\.?$/i', $text, $m)) {
        return 'HTTP-ссылок на HTTPS-странице: ' . $m[1] . '.';
    }
    if (preg_match('/^Forms posting to HTTP endpoints:\s*(\d+)\.?$/i', $text, $m)) {
        return 'Форм, отправляющих данные на HTTP endpoints: ' . $m[1] . '.';
    }
    if (preg_match('/^Missing security header:\s*(.+)$/i', $text, $m)) {
        return 'Отсутствует security header: ' . $m[1];
    }
    if (preg_match('/^Add\s+(.+)\s+at edge\/server level\.?$/i', $text, $m)) {
        return 'Добавьте ' . $m[1] . ' на уровне edge/server.';
    }
    return $text;
};
$truncate = static function (string $text, int $len): string {
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $len) {
            return $text;
        }
        return rtrim((string)mb_substr($text, 0, max(1, $len - 1), 'UTF-8')) . '…';
    }
    if (strlen($text) <= $len) {
        return $text;
    }
    return rtrim((string)substr($text, 0, max(1, $len - 1))) . '…';
};
?>
<style>
.audit-wrap{max-width:1180px;margin:20px auto 56px;padding:0 16px}
.audit-hero{border:1px solid #d7e1ef;border-radius:14px;padding:20px;background:#f8fbff}
.audit-hero h1{margin:0 0 10px;font-size:32px;line-height:1.12}
.audit-hero p{margin:0;color:#4f6684;line-height:1.6}
.audit-form{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap}
.audit-form input{flex:1 1 520px;min-width:260px;border:1px solid #c8d7ea;border-radius:10px;padding:12px 14px;font:inherit}
.audit-form button{border:0;border-radius:10px;padding:12px 18px;font-weight:700;background:#1b66d6;color:#fff;cursor:pointer}
.audit-error{margin-top:12px;padding:10px 12px;border:1px solid #efc4ca;border-radius:10px;background:#fff1f2;color:#6f2632}
.audit-grid{margin-top:18px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.audit-card{border:1px solid #d4deec;border-radius:12px;background:#fff;padding:12px}
.audit-card h3{margin:0 0 6px;font-size:14px;color:#617a99;text-transform:uppercase;letter-spacing:.04em}
.audit-card .v{font-size:26px;font-weight:800;line-height:1.1}
.audit-score-card{display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center;padding-top:18px;padding-bottom:18px}
.audit-score{--value:0;--score-color:#1b66d6;position:relative;width:116px;height:116px;border-radius:50%;background:conic-gradient(var(--score-color) calc(var(--value) * 3.6deg), #e7edf6 0);display:grid;place-items:center}
.audit-score::before{content:"";position:absolute;inset:10px;border-radius:50%;background:#fff;border:1px solid #d9e3f1}
.audit-score-value{position:relative;z-index:1;font-size:22px;font-weight:800;line-height:1;color:#162235}
.audit-score-label{font-size:13px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#5d7390}
.audit-sections{margin-top:12px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.audit-table{width:100%;border-collapse:collapse}
.audit-table th,.audit-table td{padding:8px 10px;border-bottom:1px solid #e2e9f3;text-align:left;font-size:14px;vertical-align:top}
.audit-table th{width:46%;color:#5a7392;font-weight:600}
.audit-list{margin:0;padding-left:18px}
.audit-list li{margin:0 0 8px;line-height:1.45}
.issue-head{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px}
.issue-tag{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.04em;line-height:1.4;text-transform:uppercase;border:1px solid transparent}
.issue-tag-critical{background:#fff1f2;border-color:#f3bcc3;color:#b42318}
.issue-tag-high{background:#fff7ed;border-color:#ffd6a8;color:#c2410c}
.issue-tag-medium{background:#fffbeb;border-color:#fde68a;color:#a16207}
.issue-tag-low{background:transparent;border-color:#d1d5db;color:#111827}
.issue-groups{display:flex;flex-direction:column;gap:10px}
.issue-group{border-top:1px solid #dfe7f2;padding-top:10px}
.issue-group:first-child{border-top:0;padding-top:0}
.issue-group-title{margin:0 0 8px;font-size:12px;letter-spacing:.05em;text-transform:uppercase;color:#5d7390}
.audit-debug{margin-top:14px;border:1px dashed #87a2c7;background:#f5f9ff;border-radius:12px;padding:12px}
.audit-debug pre{margin:0;white-space:pre-wrap;word-break:break-word;font-size:12px;line-height:1.45}
.snippet-wrap{margin-top:12px;display:grid;grid-template-columns:2fr 1fr;gap:12px;align-items:stretch}
.snippet-card{border:1px solid #d4deec;background:#fff;padding:14px 16px;border-radius:12px;display:flex;flex-direction:column;min-height:200px;overflow:hidden}
.snippet-card h3{margin:0 0 10px;font-size:14px;color:#5a7392;text-transform:uppercase;letter-spacing:.04em}
.g-snippet{font-family:Arial,sans-serif;line-height:1.35;display:flex;flex-direction:column;gap:0;min-height:0}
.g-top{display:flex;align-items:flex-start;gap:8px;margin-bottom:6px}
.g-favicon{width:20px;height:20px;object-fit:cover;flex:0 0 20px;border-radius:50%;border:1px solid #e6ebf3;background:#fff}
.g-source{min-width:0;flex:1 1 auto}
.g-source-name{font-size:16px;line-height:1.2;color:#202124;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.g-url-line{font-size:12px;color:#5f6368;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.g-url-line .translate{color:#1a73e8}
.g-url-line .sep{color:#9aa0a6;padding:0 4px}
.g-kebab{font-size:18px;color:#5f6368;line-height:1;padding-top:2px}
.g-title{font-size:18px;line-height:1.24;color:#8ab4f8;text-decoration:none;display:-webkit-box;margin-bottom:6px;font-weight:400;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.g-desc{font-size:13px;color:#4d5156;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.g-rich-line{font-size:12px;color:#5f6368;margin:4px 0 6px;display:flex;flex-wrap:wrap;gap:6px;align-items:center}
.g-stars{color:#fbbc04;font-size:14px;letter-spacing:.5px}
.g-chip{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;background:#f1f3f4;color:#3c4043;font-size:12px}
.g-sitelinks{display:block;margin:6px 0 0 0;max-height:20px;overflow:hidden;padding:0}
.g-sitelinks a{display:inline-block;vertical-align:top;margin:0 10px 0 0;padding:0;font-size:13px;color:#1a0dab;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
.g-sitelinks a:first-child{margin-left:0;padding-left:0}
.g-comments{margin-top:6px;padding-top:6px;border-top:1px solid #eceff3;font-size:12px;color:#4d5156}
.g-note{margin-top:6px;font-size:11px;color:#6d7580}
.snippet-desktop-shell{width:100%;max-width:none}
.snippet-mobile-shell{width:100%;max-width:420px;margin:0 auto}
.snippet-mobile .g-title{font-size:17px}
.snippet-mobile .g-source-name{font-size:14px}
.snippet-mobile .g-desc{font-size:12px;-webkit-line-clamp:2}
.audit-order{margin-top:12px;border:1px solid #d4deec;background:#fff;padding:12px}
.audit-order h3{margin:0 0 8px;font-size:20px}
.audit-order p{margin:0 0 12px;color:#5b6f88}
.audit-order-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.audit-order-grid input,.audit-order-grid textarea{width:100%;box-sizing:border-box;border:1px solid #c8d7ea;padding:10px 12px;font:inherit}
.audit-order-grid textarea{grid-column:1/-1;min-height:120px;resize:vertical}
.audit-order-grid button{grid-column:1/-1;border:0;background:#1b66d6;color:#fff;padding:11px 14px;font-weight:700;cursor:pointer}
.audit-order .contact-alert{margin:0 0 10px;padding:10px;border:1px solid;border-radius:8px;font-size:14px}
.audit-order .contact-alert.ok{background:#eaf7ef;border-color:#b6dcc3;color:#234833}
.audit-order .contact-alert.error{background:#fff1f2;border-color:#efc4ca;color:#6f2632}
.audit-tech{margin-top:14px;border:1px solid #d4deec;background:#fff;padding:14px;border-radius:12px}
.audit-tech h3{margin:0 0 10px;font-size:18px}
.audit-tech ul{margin:0;padding-left:18px}
.audit-tech li{margin:0 0 8px;line-height:1.45}
body.ui-tone-dark .audit-hero{background:#0f223a;border-color:#294868}
body.ui-tone-dark .audit-hero p{color:#9db5d2}
body.ui-tone-dark .audit-form input{background:#0d1c30;border-color:#315173;color:#dbeaff}
body.ui-tone-dark .audit-card{background:#0f2138;border-color:#2d4b69}
body.ui-tone-dark .audit-card h3{color:#9ab4d7}
body.ui-tone-dark .audit-score{background:conic-gradient(var(--score-color) calc(var(--value) * 3.6deg), #22364d 0)}
body.ui-tone-dark .audit-score::before{background:#0f2138;border-color:#2d4b69}
body.ui-tone-dark .audit-score-value{color:#e6effb}
body.ui-tone-dark .audit-score-label{color:#aac2dd}
body.ui-tone-dark .audit-table th,body.ui-tone-dark .audit-table td{border-color:#2a4765}
body.ui-tone-dark .audit-debug{background:#102236;border-color:#3b5d83}
body.ui-tone-dark .issue-tag-critical{background:#3a1a22;border-color:#8f2f42;color:#ffb4c1}
body.ui-tone-dark .issue-tag-high{background:#3b2715;border-color:#8d5127;color:#ffd2ad}
body.ui-tone-dark .issue-tag-medium{background:#3a3215;border-color:#8a7222;color:#ffe5a1}
body.ui-tone-dark .issue-tag-low{background:transparent;border-color:#496586;color:#dbeaff}
body.ui-tone-dark .issue-group{border-top-color:#2a4765}
body.ui-tone-dark .issue-group-title{color:#aac2dd}
body.ui-tone-dark .snippet-card{background:#0f2138;border-color:#2d4b69}
body.ui-tone-dark .g-source-name{color:#e4ecf7}
body.ui-tone-dark .g-url-line{color:#9fb3ca}
body.ui-tone-dark .g-kebab{color:#9fb3ca}
body.ui-tone-dark .g-title{color:#8ab4f8}
body.ui-tone-dark .g-desc{color:#c6d2e2}
body.ui-tone-dark .g-rich-line{color:#9fb3ca}
body.ui-tone-dark .g-chip{background:#1d334d;color:#cfe1f7}
body.ui-tone-dark .g-comments{border-color:#2a4765;color:#c6d2e2}
body.ui-tone-dark .g-note{color:#9fb3ca}
body.ui-tone-dark .audit-order{background:#0f2138;border-color:#2d4b69}
body.ui-tone-dark .audit-order p{color:#9db5d2}
body.ui-tone-dark .audit-order-grid input,body.ui-tone-dark .audit-order-grid textarea{background:#0d1c30;border-color:#315173;color:#dbeaff}
body.ui-tone-dark .audit-tech{background:#0f2138;border-color:#2d4b69}
@media (max-width: 900px){
  .audit-grid,.audit-sections,.snippet-wrap,.audit-order-grid{grid-template-columns:1fr}
  .snippet-card{height:auto;min-height:0}
  .g-title{-webkit-line-clamp:3}
  .g-desc{-webkit-line-clamp:4}
}
</style>

<section class="audit-wrap">
    <div class="audit-hero">
        <h1><?= $h($tt('Структурированный аудит сайта', 'Structured Website Audit')) ?></h1>
        <p><?= $h($tt(
            'Проверка без AI: индексация, SEO-разметка, заголовки, ссылки, скорость ответа, robots.txt, sitemap.xml и security headers.',
            'Non-AI report: indexability, SEO markup, headings, links, response speed, robots.txt, sitemap.xml and security headers.'
        )) ?></p>
        <form class="audit-form" method="get" action="/audit/">
            <input type="text" name="site" value="<?= $h($auditInput) ?>" placeholder="<?= $h($tt('Проверь свой сайт (example.com)', 'Check your site (example.com)')) ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" required>
            <button type="submit"><?= $h($tt('Проверить', 'Run Audit')) ?></button>
        </form>
        <?php if ($auditError !== ''): ?>
            <div class="audit-error"><?= $h($auditError) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($auditRequested && is_array($auditReport)): ?>
        <?php
        $scores = (array)($auditReport['scores'] ?? []);
        $scoreOverall = max(0, min(100, (int)($scores['overall'] ?? 0)));
        $scoreSeo = max(0, min(100, (int)($scores['seo'] ?? 0)));
        $scoreTech = max(0, min(100, (int)($scores['tech'] ?? 0)));
        $scoreSecurity = max(0, min(100, (int)($scores['security'] ?? 0)));
        $html = (array)($auditReport['html'] ?? []);
        $snHost = (string)($auditReport['host'] ?? '');
        $snTitle = trim((string)($html['title'] ?? ''));
        $snDesc = trim((string)($html['description'] ?? ''));
        $snCanonical = trim((string)($html['canonical'] ?? ''));
        if ($snCanonical === '') {
            $snCanonical = (string)($auditReport['normalized_url'] ?? '');
        }
        $snPath = (string)(parse_url($snCanonical, PHP_URL_PATH) ?: '/');
        $snBreadcrumb = $snHost . ($snPath !== '' ? (' > ' . trim(str_replace('/', ' > ', trim($snPath, '/')), ' >')) : '');
        if ($snBreadcrumb === $snHost . ' > ') {
            $snBreadcrumb = $snHost;
        }
        $desktopTitle = $truncate($snTitle, 60);
        $desktopDesc = $truncate($snDesc, 158);
        $mobileTitle = $truncate($snTitle, 50);
        $mobileDesc = $truncate($snDesc, 120);
        $snScheme = (string)(parse_url($snCanonical, PHP_URL_SCHEME) ?: 'https');
        $snFavicon = $snScheme . '://' . $snHost . '/favicon.ico';
        $snPathShort = (string)(parse_url($snCanonical, PHP_URL_PATH) ?: '/');
        if ($snPathShort === '') {
            $snPathShort = '/';
        }
        if (strlen($snPathShort) > 1) {
            $snPathShort = rtrim($snPathShort, '/');
        }

        $ratingValue = isset($html['rating_value']) ? (float)$html['rating_value'] : 0.0;
        $reviewCount = isset($html['review_count']) ? (int)$html['review_count'] : 0;
        $hasReviews = ($ratingValue > 0 && $reviewCount > 0);

        $rawLinks = (array)($html['internal_link_samples'] ?? []);
        $sitelinks = [];
        foreach ($rawLinks as $row) {
            $label = trim((string)($row['label'] ?? ''));
            $href = trim((string)($row['href'] ?? ''));
            if ($href === '') {
                continue;
            }
            $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?: $href);
            if ($hrefPath === '' || $hrefPath === '/') {
                continue;
            }
            $hrefPath = '/' . ltrim($hrefPath, '/');
            if (strlen($hrefPath) > 1) {
                $hrefPath = rtrim($hrefPath, '/');
            }
            if ($label === '') {
                $label = $hrefPath;
            }
            $label = preg_replace('/\s+/', ' ', (string)$label);
            $label = trim((string)$label, " \t\n\r\0\x0B/");
            if ($label === '') {
                $label = ltrim($hrefPath, '/');
            }
            $sitelinks[] = ['label' => $label, 'href' => $hrefPath];
            if (count($sitelinks) >= 6) {
                break;
            }
        }
        ?>

        <div class="audit-grid">
            <article class="audit-card audit-score-card">
                <div class="audit-score" style="--value:<?= $scoreOverall ?>;--score-color:#2563eb">
                    <div class="audit-score-value"><?= $scoreOverall ?>/100</div>
                </div>
                <div class="audit-score-label"><?= $h($tt('Итоговый балл', 'Overall Score')) ?></div>
            </article>
            <article class="audit-card audit-score-card">
                <div class="audit-score" style="--value:<?= $scoreSeo ?>;--score-color:#f59e0b">
                    <div class="audit-score-value"><?= $scoreSeo ?>/100</div>
                </div>
                <div class="audit-score-label">SEO</div>
            </article>
            <article class="audit-card audit-score-card">
                <div class="audit-score" style="--value:<?= $scoreTech ?>;--score-color:#10b981">
                    <div class="audit-score-value"><?= $scoreTech ?>/100</div>
                </div>
                <div class="audit-score-label"><?= $h($tt('Технологии', 'Technology')) ?></div>
            </article>
            <article class="audit-card audit-score-card">
                <div class="audit-score" style="--value:<?= $scoreSecurity ?>;--score-color:#ef4444">
                    <div class="audit-score-value"><?= $scoreSecurity ?>/100</div>
                </div>
                <div class="audit-score-label"><?= $h($tt('Безопасность', 'Security')) ?></div>
            </article>
        </div>

        <div class="snippet-wrap">
            <article class="snippet-card">
                <h3>Google Snippet Preview (Desktop)</h3>
                <div class="g-snippet snippet-desktop snippet-desktop-shell">
                    <div class="g-top">
                        <img class="g-favicon" src="<?= $h($snFavicon) ?>" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                        <div class="g-source">
                            <div class="g-source-name"><?= $h($snHost) ?></div>
                            <div class="g-url-line"><?= $h($snScheme . '://' . $snHost . $snPathShort) ?><span class="sep">·</span><span class="translate"><?= $h($tt('Перевести эту страницу', 'Translate this page')) ?></span></div>
                        </div>
                        <div class="g-kebab">⋮</div>
                    </div>
                    <a class="g-title" href="javascript:void(0)"><?= $h($desktopTitle !== '' ? $desktopTitle : $tt('Заголовок отсутствует', 'Missing title')) ?></a>
                    <div class="g-desc"><?= $h($desktopDesc !== '' ? $desktopDesc : $tt('Описание отсутствует', 'Missing description')) ?></div>
                    <?php if ($hasReviews): ?>
                        <div class="g-rich-line">
                            <span><?= $h(number_format($ratingValue, 1)) ?></span>
                            <span class="g-stars">★★★★★</span>
                            <span><?= $h((string)$reviewCount . ' ' . $tt('отзывов', 'reviews')) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($sitelinks)): ?>
                        <div class="g-sitelinks">
                            <?php foreach ($sitelinks as $link): ?>
                                <a href="javascript:void(0)"><?= $h($link['label']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="g-note"><?= $h($tt('Sitelinks не показаны: не найдено надежных внутренних ссылок для превью.', 'Sitelinks hidden: no reliable internal links were detected for preview.')) ?></div>
                    <?php endif; ?>
                    <?php if ($hasReviews): ?>
                        <div class="g-comments"><?= $h($tt('Комментарии/рейтинги показаны на основе JSON-LD aggregateRating сайта.', 'Comments/ratings shown based on website JSON-LD aggregateRating.')) ?></div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="snippet-card">
                <h3>Google Snippet Preview (Mobile)</h3>
                <div class="g-snippet snippet-mobile snippet-mobile-shell">
                    <div class="g-top">
                        <img class="g-favicon" src="<?= $h($snFavicon) ?>" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                        <div class="g-source">
                            <div class="g-source-name"><?= $h($snHost) ?></div>
                            <div class="g-url-line"><?= $h($snScheme . '://' . $snHost . $snPathShort) ?></div>
                        </div>
                        <div class="g-kebab">⋮</div>
                    </div>
                    <a class="g-title" href="javascript:void(0)"><?= $h($mobileTitle !== '' ? $mobileTitle : $tt('Заголовок отсутствует', 'Missing title')) ?></a>
                    <div class="g-desc"><?= $h($mobileDesc !== '' ? $mobileDesc : $tt('Описание отсутствует', 'Missing description')) ?></div>
                    <?php if ($hasReviews): ?>
                        <div class="g-rich-line">
                            <span><?= $h(number_format($ratingValue, 1)) ?></span>
                            <span class="g-stars">★★★★★</span>
                            <span><?= $h((string)$reviewCount) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($sitelinks)): ?>
                        <div class="g-sitelinks">
                            <?php foreach ($sitelinks as $link): ?>
                                <a href="javascript:void(0)"><?= $h($link['label']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        </div>

        <div class="audit-sections">
            <article class="audit-card">
                <h3><?= $h($tt('Базовые метрики', 'Core Metrics')) ?></h3>
                <table class="audit-table">
                    <tr><th>URL</th><td><?= $h((string)($auditReport['normalized_url'] ?? '')) ?></td></tr>
                    <tr><th><?= $h($tt('HTTPS статус', 'HTTPS status')) ?></th><td><?= (int)(($auditReport['https']['status'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('TTFB (мс)', 'TTFB (ms)')) ?></th><td><?= (int)(($auditReport['https']['ttfb_ms'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('Редиректы', 'Redirects')) ?></th><td><?= (int)(($auditReport['https']['redirects'] ?? 0)) ?></td></tr>
                    <tr><th>Title</th><td><?= (int)(($html['title_length'] ?? 0)) ?></td></tr>
                    <tr><th>Description</th><td><?= (int)(($html['description_length'] ?? 0)) ?></td></tr>
                    <tr><th>H1</th><td><?= (int)(($html['h1_count'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('Слова на странице', 'Words on page')) ?></th><td><?= (int)(($html['word_count'] ?? 0)) ?></td></tr>
                </table>
            </article>
            <article class="audit-card">
                <h3><?= $h($tt('Индексация и SEO', 'Indexability & SEO')) ?></h3>
                <table class="audit-table">
                    <tr><th>Canonical</th><td><?= $h((string)($html['canonical'] ?? '')) ?></td></tr>
                    <tr><th>robots.txt</th><td><?= !empty($auditReport['robots']['robots_ok']) ? 'OK' : 'NO' ?></td></tr>
                    <tr><th>sitemap.xml</th><td><?= !empty($auditReport['robots']['sitemap_ok']) ? 'OK' : 'NO' ?></td></tr>
                    <tr><th><?= $h($tt('Sitemap в robots', 'Sitemap in robots')) ?></th><td><?= !empty($auditReport['robots']['sitemap_declared']) ? 'YES' : 'NO' ?></td></tr>
                    <tr><th><?= $h($tt('Sitemap XML valid', 'Sitemap XML valid')) ?></th><td><?= !empty($auditReport['robots']['sitemap_xml_ok']) ? 'YES' : 'NO' ?></td></tr>
                    <tr><th><?= $h($tt('Sitemap URLs', 'Sitemap URLs')) ?></th><td><?= (int)($auditReport['robots']['sitemap_url_count'] ?? 0) ?></td></tr>
                    <tr><th><?= $h($tt('HTTP→HTTPS redirect', 'HTTP→HTTPS redirect')) ?></th><td><?= !empty($auditReport['robots']['http_to_https_redirect']) ? 'YES' : 'NO' ?></td></tr>
                    <tr><th>Viewport</th><td><?= $h((string)($html['viewport'] ?? '')) ?></td></tr>
                    <tr><th>Lang</th><td><?= $h((string)($html['lang'] ?? '')) ?></td></tr>
                    <tr><th>JSON-LD</th><td><?= (int)(($html['json_ld_count'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('Hreflang count', 'Hreflang count')) ?></th><td><?= (int)(($html['hreflang_count'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('Hreflang issues', 'Hreflang issues')) ?></th><td><?= (int)(($html['hreflang_invalid'] ?? 0)) + (int)(($html['hreflang_duplicates'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('Ссылки внутр/внеш', 'Links int/ext')) ?></th><td><?= (int)(($html['internal_links'] ?? 0)) ?> / <?= (int)(($html['external_links'] ?? 0)) ?></td></tr>
                </table>
            </article>
            <article class="audit-card">
                <h3><?= $h($tt('Безопасность', 'Security Headers')) ?></h3>
                <?php $headers = array_change_key_case((array)($auditReport['https']['headers'] ?? []), CASE_LOWER); ?>
                <table class="audit-table">
                    <tr><th>HSTS</th><td><?= $h((string)($headers['strict-transport-security'] ?? '')) ?></td></tr>
                    <tr><th>CSP</th><td><?= $h((string)($headers['content-security-policy'] ?? '')) ?></td></tr>
                    <tr><th>X-Frame-Options</th><td><?= $h((string)($headers['x-frame-options'] ?? '')) ?></td></tr>
                    <tr><th>X-Content-Type-Options</th><td><?= $h((string)($headers['x-content-type-options'] ?? '')) ?></td></tr>
                    <tr><th>Referrer-Policy</th><td><?= $h((string)($headers['referrer-policy'] ?? '')) ?></td></tr>
                    <tr><th>Permissions-Policy</th><td><?= $h((string)($headers['permissions-policy'] ?? '')) ?></td></tr>
                    <tr><th><?= $h($tt('Mixed content links', 'Mixed content links')) ?></th><td><?= (int)(($html['mixed_content_links'] ?? 0)) ?></td></tr>
                    <tr><th><?= $h($tt('HTTP form actions', 'HTTP form actions')) ?></th><td><?= (int)(($html['http_form_actions'] ?? 0)) ?></td></tr>
                </table>
            </article>
            <article class="audit-card">
                <h3><?= $h($tt('Рекомендации', 'Recommendations')) ?></h3>
                <ul class="audit-list">
                    <?php foreach ((array)($auditReport['recommendations'] ?? []) as $tip): ?>
                        <li><?= $h($auditLocalize((string)$tip)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
            <article class="audit-card">
                <h3><?= $h($tt('Практические замечания', 'Practical findings')) ?></h3>
                <?php $issues = (array)($auditReport['issues'] ?? []); ?>
                <?php if (!empty($issues)): ?>
                    <?php
                        $issueOrder = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];
                        $issueGroups = ['CRITICAL' => [], 'HIGH' => [], 'MEDIUM' => [], 'LOW' => []];
                        foreach ($issues as $issue) {
                            $sev = strtoupper((string)($issue['severity'] ?? 'LOW'));
                            if (!isset($issueGroups[$sev])) {
                                $sev = 'LOW';
                            }
                            $issueGroups[$sev][] = $issue;
                        }
                        $groupTitles = [
                            'CRITICAL' => $tt('Критично', 'Critical'),
                            'HIGH' => $tt('Высокий приоритет', 'High priority'),
                            'MEDIUM' => $tt('Средний приоритет', 'Medium priority'),
                            'LOW' => $tt('Низкий приоритет', 'Low priority'),
                        ];
                    ?>
                    <div class="issue-groups">
                        <?php foreach ($issueOrder as $sevGroup): ?>
                            <?php if (empty($issueGroups[$sevGroup])) { continue; } ?>
                            <section class="issue-group">
                                <h4 class="issue-group-title"><?= $h((string)$groupTitles[$sevGroup]) ?></h4>
                                <ul class="audit-list">
                                    <?php foreach ($issueGroups[$sevGroup] as $issue): ?>
                                        <?php
                                            $sev = $sevGroup;
                                            $sevClass = 'issue-tag-low';
                                            if ($sev === 'CRITICAL') {
                                                $sevClass = 'issue-tag-critical';
                                            } elseif ($sev === 'HIGH') {
                                                $sevClass = 'issue-tag-high';
                                            } elseif ($sev === 'MEDIUM') {
                                                $sevClass = 'issue-tag-medium';
                                            }
                                            $ttl = $auditLocalize((string)($issue['title'] ?? ''));
                                            $det = $auditLocalize((string)($issue['details'] ?? ''));
                                            $fix = $auditLocalize((string)($issue['fix'] ?? ''));
                                        ?>
                                        <li>
                                            <div class="issue-head">
                                                <span class="issue-tag <?= $h($sevClass) ?>"><?= $h($sev) ?></span>
                                                <strong><?= $h($ttl) ?></strong>
                                            </div>
                                            <?= $h($det) ?><br>
                                            <em><?= $h($tt('Действие', 'Action')) ?>:</em> <?= $h($fix) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div><?= $h($tt('No critical issues detected.', 'No critical issues detected.')) ?></div>
                <?php endif; ?>
            </article>
            <article class="audit-card">
                <h3><?= $h($tt('Технический профиль', 'Technical profile')) ?></h3>
                <?php $tp = (array)($auditReport['tech_profile'] ?? []); ?>
                <table class="audit-table">
                    <tr><th>Content-Encoding</th><td><?= $h((string)($tp['content_encoding'] ?? '')) ?></td></tr>
                    <tr><th>Cache-Control</th><td><?= $h((string)($tp['cache_control'] ?? '')) ?></td></tr>
                    <tr><th>ETag</th><td><?= $h((string)($tp['etag'] ?? '')) ?></td></tr>
                    <tr><th>Last-Modified</th><td><?= $h((string)($tp['last_modified'] ?? '')) ?></td></tr>
                    <tr><th>Server</th><td><?= $h((string)($tp['server'] ?? '')) ?></td></tr>
                    <tr><th><?= $h($tt('Images w/o size', 'Images w/o size')) ?></th><td><?= (int)($html['images_missing_dimensions'] ?? 0) ?></td></tr>
                </table>
            </article>
        </div>

        <?php if ($auditDebug): ?>
            <div class="audit-debug">
                <pre><?= $h('store_status=' . ($auditStoreStatus !== '' ? $auditStoreStatus : 'n/a')) ?></pre>
                <br>
                <pre><?= $h((string)json_encode($auditReport, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) ?></pre>
            </div>
        <?php endif; ?>

        <article class="audit-order" id="audit-order-form">
            <h3><?= $h($tt('Заказать продвинутый анализ сайта', 'Order Advanced Site Analysis')) ?></h3>
            <p><?= $h($tt(
                'Отправьте данные, и я подготовлю детальный аудит с приоритетами исправлений и планом внедрения.',
                'Submit your details to receive a deeper audit with prioritized fixes and implementation plan.'
            )) ?></p>
            <?php if ($contactMsg !== ''): ?>
                <div class="contact-alert <?= $contactType === 'ok' ? 'ok' : 'error' ?>"><?= $h($contactMsg) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= $h($returnPath) ?>" class="audit-order-grid">
                <input type="hidden" name="action" value="public_contact_submit">
                <input type="hidden" name="return_path" value="<?= $h($returnPath . '#audit-order-form') ?>">
                <input type="hidden" name="contact_form_anchor" value="#audit-order-form">
                <input type="hidden" name="contact_interest" value="enterprise">
                <input type="hidden" name="contact_csrf" value="<?= $h($contactToken) ?>">
                <input type="hidden" name="contact_started_at" value="<?= time() ?>">
                <input type="hidden" name="contact_campaign" value="audit:advanced">
                <input type="hidden" name="contact_audit_url" value="<?= $h((string)($auditReport['normalized_url'] ?? '')) ?>">
                <input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="contact-hp" aria-hidden="true">
                <input type="text" name="contact_name" placeholder="<?= $h($tt('Имя', 'Name')) ?>" required>
                <input type="email" name="contact_email" placeholder="Email" required>
                <textarea name="contact_message" placeholder="<?= $h($tt('Комментарий: цели, проблемы, ограничения, приоритеты…', 'Comment: goals, issues, constraints, priorities…')) ?>" required></textarea>
                <button type="submit"><?= $h($tt('Отправить запрос', 'Send Request')) ?></button>
            </form>
        </article>

        <article class="audit-tech">
            <h3><?= $h($tt('Какие технологии использованы в этом отчете', 'Technologies Used In This Report')) ?></h3>
            <ul>
                <li><?= $h($tt('HTTP/HTTPS crawl через cURL: статус, редиректы, TTFB, headers, content-type.', 'HTTP/HTTPS crawl via cURL: status, redirects, TTFB, headers, content-type.')) ?></li>
                <li><?= $h($tt('DOM-анализ HTML: title, meta description, canonical, h1/h2, lang, viewport, JSON-LD, ссылки, изображения и ALT.', 'DOM HTML analysis: title, meta description, canonical, h1/h2, lang, viewport, JSON-LD, links, images and ALT.')) ?></li>
                <li><?= $h($tt('Проверка robots.txt и sitemap.xml: доступность и декларация sitemap в robots.', 'robots.txt and sitemap.xml checks: availability and sitemap declaration in robots.')) ?></li>
                <li><?= $h($tt('DNS-профиль домена: A/AAAA/MX/NS/TXT как базовый технический контекст.', 'DNS profile: A/AAAA/MX/NS/TXT as baseline technical context.')) ?></li>
                <li><?= $h($tt('Скоринг и рекомендации формируются детерминированными правилами без AI-генерации.', 'Scoring and recommendations are deterministic rule-based, without AI generation.')) ?></li>
            </ul>
        </article>
    <?php endif; ?>
</section>
