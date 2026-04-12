<?php
$catalog = (array)($ModelPage['services_catalog'] ?? []);
$items = (array)($catalog['items'] ?? []);
$groupedItems = (array)($catalog['items_by_group'] ?? []);
$groups = (array)($catalog['groups'] ?? []);
$selected = is_array($catalog['selected'] ?? null) ? $catalog['selected'] : null;
$relatedFromCatalog = (array)($catalog['related'] ?? []);
$total = (int)($catalog['total'] ?? 0);
$page = max(1, (int)($catalog['page'] ?? 1));
$totalPages = max(1, (int)($catalog['total_pages'] ?? 1));
$lang = (string)($catalog['lang'] ?? 'en');
$err = trim((string)($catalog['error'] ?? ''));
$currentGroup = trim((string)($catalog['group'] ?? ''));
$isRu = ($lang === 'ru');

$title = $isRu ? 'Услуги' : 'Services';
$lead = $isRu
    ? 'Каталог услуг по направлениям. Выбирайте сферу, смотрите детали и формат реализации.'
    : 'A service catalog with practical delivery formats: from websites and platforms to automation, integrations and consulting. Choose a domain and explore execution approach, business impact and implementation depth.';
if ($isRu) {
    $lead = 'Каталог услуг с практическими форматами реализации: от разработки сайтов и платформ до автоматизации, интеграций и консалтинга. Выбирайте направление и изучайте подход, ожидаемые результаты и глубину внедрения под задачи бизнеса.';
}
$emptyText = $isRu ? 'Пока нет опубликованных услуг.' : 'No published services yet.';
$prevText = $isRu ? 'Назад' : 'Prev';
$nextText = $isRu ? 'Вперед' : 'Next';
$allGroupsText = $isRu ? 'Все сферы' : 'All domains';
$contactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$contactFlash = function_exists('public_contact_form_flash') ? public_contact_form_flash() : [];
$contactType = (string)($contactFlash['type'] ?? '');
$contactMsg = (string)($contactFlash['message'] ?? '');
$returnPath = (string)($_SERVER['REQUEST_URI'] ?? '/');
$turnstileSiteKey = trim((string)($GLOBALS['ContactTurnstileSiteKey'] ?? ''));

$buildListLink = static function (int $p, string $group = ''): string {
    $params = [];
    $p = max(1, $p);
    if ($p > 1) {
        $params['page'] = $p;
    }
    if (trim($group) !== '') {
        $params['group'] = $group;
    }
    $qs = http_build_query($params);
    return '/services/' . ($qs !== '' ? ('?' . $qs) : '');
};
$buildGroupLink = static function (string $group): string {
    $params = [];
    if (trim($group) !== '') {
        $params['group'] = $group;
    }
    $qs = http_build_query($params);
    return '/services/' . ($qs !== '' ? ('?' . $qs) : '');
};
$buildDetailLink = static function (string $slug): string {
    $slug = trim($slug);
    if ($slug === '') {
        return '/services/';
    }
    return '/services/' . rawurlencode($slug) . '/';
};
$relatedItems = [];
if ($selected !== null && !empty($relatedFromCatalog)) {
    $relatedItems = array_slice($relatedFromCatalog, 0, 3);
}
if ($selected !== null && empty($relatedItems)) {
    $selectedSlug = trim((string)($selected['slug'] ?? ''));
    $selectedGroup = trim((string)($selected['service_group'] ?? ''));
    foreach ($items as $item) {
        $itemSlug = trim((string)($item['slug'] ?? ''));
        if ($itemSlug === '' || $itemSlug === $selectedSlug) {
            continue;
        }
        if ($selectedGroup !== '' && trim((string)($item['service_group'] ?? '')) !== $selectedGroup) {
            continue;
        }
        $relatedItems[] = $item;
    }
    if (count($relatedItems) > 3) {
        $relatedItems = array_slice($relatedItems, 0, 3);
    }
}
?>
<style>
.services-simple{max-width:1180px;margin:0 auto;padding:18px 16px 28px;font-family:"IBM Plex Sans",system-ui,sans-serif;color:#0f203a}
.services-simple-hero{border:1px solid #d6e0ed;padding:20px;background:linear-gradient(145deg,#f9fcff,#eff6ff)}
.services-simple-hero h1{margin:0 0 8px;font-size:34px;font-family:"Manrope",sans-serif}
.services-simple-hero p{margin:0;color:#5b6f88;max-width:86ch;line-height:1.62}
.services-simple-alert{margin-top:12px;border:1px solid #edcc8a;background:#fff5e0;color:#664a17;padding:10px 12px}
.services-simple-groups{margin-top:12px;display:flex;flex-wrap:wrap;gap:8px}
.services-simple-groups a{display:inline-flex;align-items:center;gap:6px;border:1px solid #c7d7ec;padding:7px 11px;text-decoration:none;color:#2f4d71;background:#fff;font-size:13px}
.services-simple-groups a.active{background:#0d63d6;color:#fff;border-color:#0d63d6}
.services-simple-groups small{opacity:.8}
.services-simple-selected{margin-top:14px;border:1px solid #d7e2f0;background:#ffffff;padding:18px}
.services-simple-selected h2{margin:0 0 6px;font-family:"Manrope",sans-serif}
.services-simple-selected-content{margin-top:10px;color:#1b3452;line-height:1.62}
.services-simple-selected-content p{margin:0 0 12px}
.services-simple-selected-content h2,.services-simple-selected-content h3{margin:16px 0 8px;color:#132f52;font-family:"Manrope",sans-serif}
.services-simple-selected-content ul,.services-simple-selected-content ol{margin:0 0 12px 18px}
.services-simple-selected-content .svc-block{margin:0 0 12px;padding:12px;border:1px solid #d6e2f1;background:#f9fcff}
.services-simple-selected-content .svc-block:last-child{margin-bottom:0}
.services-simple-selected-content table{width:100%;max-width:100%;display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;border-collapse:collapse;margin:12px 0;background:#fff;border:1px solid #d3deec}
.services-simple-selected-content thead,.services-simple-selected-content tbody,.services-simple-selected-content tr{display:table;width:100%;table-layout:fixed}
.services-simple-selected-content th,.services-simple-selected-content td{border:1px solid #d3deec;padding:8px 10px;vertical-align:top;min-width:140px;word-break:break-word;overflow-wrap:anywhere}
.services-simple-selected-content th{background:#f4f8ff;color:#14355f;font-weight:700}
.services-simple-back{display:inline-flex;align-items:center;gap:6px;margin-bottom:10px;padding:6px 10px;border:1px solid #bfd5ee;color:#0d5db7;text-decoration:none;font-size:13px;font-weight:600}
.services-simple-back:hover{background:#f1f7ff}
.services-simple-grid{margin-top:12px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.services-simple-sections{margin-top:14px;display:flex;flex-direction:column;gap:18px}
.services-simple-group-section{padding-top:2px}
.services-simple-group-head{display:flex;align-items:center;gap:12px;margin:0 0 10px}
.services-simple-group-head h2{margin:0;font-size:18px;font-family:"Manrope",sans-serif;color:#0f203a}
.services-simple-group-head small{color:#5b6f88;font-weight:600}
.services-simple-group-line{height:1px;flex:1;background:linear-gradient(90deg,#cfe0f3,rgba(207,224,243,0));}
.services-simple-group-header{grid-column:1/-1;display:flex;align-items:center;gap:12px;margin:4px 0 0}
.services-simple-group-header h2{margin:0;font-size:18px;font-family:"Manrope",sans-serif;color:#0f203a}
.services-simple-group-header small{color:#5b6f88;font-weight:600}
.services-simple-card{background:#fff;border:1px solid #d6e0ed;padding:14px;display:flex;flex-direction:column;gap:8px}
.services-simple-card h3{margin:0;font-size:20px;font-family:"Manrope",sans-serif}
.services-simple-card p{margin:0;color:#5b6f88;line-height:1.5}
.services-simple-link{margin-top:auto;align-self:flex-start;display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #bfd5ee;color:#0d5db7;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap}
.services-simple-link:hover{background:#f1f7ff}
.services-simple-empty{margin-top:14px;border:1px solid #d6e0ed;padding:12px;color:#4f6684;background:#fff}
.services-inline-contact{margin-top:14px;border:1px solid #d8e4f2;padding:14px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.services-inline-contact h3{margin:0 0 8px;font-size:18px;color:#14355f}
.services-inline-contact p{margin:0 0 10px;color:#355477}
.services-inline-contact .contact-alert{margin:0 0 10px;padding:10px;border:1px solid;font-size:14px}
.services-inline-contact .contact-alert.ok{background:#eaf7ef;border-color:#b6dcc3;color:#234833}
.services-inline-contact .contact-alert.error{background:#fff1f2;border-color:#efc4ca;color:#6f2632}
.services-inline-contact form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.services-inline-contact input,.services-inline-contact textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #c8d7ea;background:#fbfdff;color:#163250;font:inherit}
.services-inline-contact textarea{grid-column:1 / -1;min-height:120px;resize:vertical}
.services-inline-contact button{grid-column:1 / -1;border:0;padding:11px 14px;font-weight:700;cursor:pointer;background:linear-gradient(135deg,#0d63d6,#0e9a91);color:#fff}
.contact-hp{position:absolute!important;left:-9999px!important;opacity:0!important;pointer-events:none!important}
.services-simple-pagination{margin-top:16px;display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.services-simple-pagination a,.services-simple-pagination span{display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;line-height:1.2;border:1px solid #d0dced;min-width:36px;min-height:34px;padding:7px 10px;text-decoration:none;color:#27476b;background:#fff;background-image:none;box-shadow:none;overflow:hidden}
.services-simple-pagination .active{background:#0d63d6;color:#fff;border-color:#0d63d6}
.services-simple-pagination .disabled{opacity:.55;background:#f4f7fb;color:#7a8ea8;border-color:#d8e2ef;cursor:not-allowed}
body.ui-tone-dark .services-simple{color:#e7f2ff}
body.ui-tone-dark .services-simple-hero{border-color:#304966;background:linear-gradient(145deg,#102338,#0d1c2e)}
body.ui-tone-dark .services-simple-hero p{color:#9db7d8}
body.ui-tone-dark .services-simple-groups a{border-color:#3f5f84;color:#c5dbf8;background:#102138}
body.ui-tone-dark .services-simple-groups a.active{background:linear-gradient(135deg,#2ec5a7,#7ba5ff);color:#08131f;border-color:transparent}
body.ui-tone-dark .services-simple-selected{border-color:#324c6c;background:linear-gradient(180deg,#13243a,#101d31)}
body.ui-tone-dark .services-simple-selected-content{color:#d4e5fa}
body.ui-tone-dark .services-simple-selected-content h2,
body.ui-tone-dark .services-simple-selected-content h3{color:#e7f3ff}
body.ui-tone-dark .services-simple-selected-content .svc-block{border-color:#365777;background:#0f2136}
body.ui-tone-dark .services-simple-selected-content table{background:#0f1f33;border-color:#35516f}
body.ui-tone-dark .services-simple-selected-content th,
body.ui-tone-dark .services-simple-selected-content td{border-color:#35516f}
body.ui-tone-dark .services-simple-selected-content th{background:#162e4a;color:#e8f2ff}
body.ui-tone-dark .services-inline-contact{border-color:#2f4a6b;background:rgba(14,29,47,.45)}
body.ui-tone-dark .services-inline-contact h3{color:#e3f1ff}
body.ui-tone-dark .services-inline-contact p{color:#bcd2ec}
body.ui-tone-dark .services-inline-contact input,
body.ui-tone-dark .services-inline-contact textarea{border-color:#35506f;background:#0b1625;color:#d4e6fb}
body.ui-tone-dark .services-inline-contact button{background:linear-gradient(135deg,#36bda3,#6a95f0);color:#08131f}
body.ui-tone-dark .services-simple-card{background:linear-gradient(180deg,#12243a,#0f1d31);border-color:#304966}
body.ui-tone-dark .services-simple-card p{color:#b9cce4}
body.ui-tone-dark .services-simple-link{border-color:#45648a;color:#bfe0ff}
body.ui-tone-dark .services-simple-link:hover{background:rgba(117,170,230,.12)}
body.ui-tone-dark .services-simple-group-head h2,
body.ui-tone-dark .services-simple-group-header h2{color:#e7f2ff}
body.ui-tone-dark .services-simple-group-head small,
body.ui-tone-dark .services-simple-group-header small{color:#9db7d8}
body.ui-tone-dark .services-simple-group-line{background:linear-gradient(90deg,#3f5f84,rgba(63,95,132,0))}
@media (max-width:860px){.services-simple-grid{grid-template-columns:1fr}.services-inline-contact form{grid-template-columns:1fr}.services-simple-selected-content th,.services-simple-selected-content td{min-width:120px;font-size:13px}}
</style>
<style id="cp-front-override">
.services-simple{max-width:1240px;padding:24px 16px 44px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.services-simple-hero,.services-simple-selected,.services-inline-contact,.services-simple-card,.services-simple-empty{border:1px solid var(--shell-border)!important;background:var(--shell-panel)!important;backdrop-filter:blur(14px);box-shadow:var(--shell-shadow)}
.services-simple-hero{position:relative;overflow:hidden;padding:34px}
.services-simple-hero:before{content:"";position:absolute;inset:auto auto -80px -40px;width:240px;height:240px;background:radial-gradient(circle,rgba(44,224,199,.24),rgba(44,224,199,0));pointer-events:none}
.services-simple-hero h1,.services-simple-selected h2,.services-simple-card h3{font-family:"Space Grotesk","Sora",sans-serif;color:var(--shell-text)}
.services-simple-hero h1{font-size:clamp(2.5rem,4vw,4.4rem);line-height:.94;max-width:11ch}
.services-simple-hero p,.services-simple-card p,.services-simple-selected-content,.services-inline-contact p,.services-simple-empty{color:var(--shell-muted)!important}
.services-simple-groups{gap:10px}
.services-simple-groups a,.services-simple-back,.services-simple-link,.services-simple-pagination a,.services-simple-pagination span{border:1px solid var(--shell-border)!important;background:rgba(255,255,255,.04)!important;color:var(--shell-muted)!important}
.services-simple-groups a.active,.services-simple-pagination .active{background:linear-gradient(135deg,rgba(122,180,255,.26),rgba(44,224,199,.18))!important;color:var(--shell-text)!important;border-color:rgba(122,180,255,.34)!important}
.services-simple-group-head h2,.services-simple-group-header h2{color:var(--shell-text)!important}
.services-simple-group-head small,.services-simple-group-header small{color:#d8e8ff!important}
.services-simple-grid{gap:16px}
.services-simple-card{padding:20px}
.services-simple-link{padding:10px 14px!important}
.services-simple-selected{padding:26px}
.services-simple-selected-content .svc-block,.services-simple-selected-content table,.services-simple-selected-content th,.services-simple-selected-content td{border-color:var(--shell-border)!important;background:rgba(255,255,255,.03)!important;color:inherit}
.services-inline-contact{padding:20px}
.services-inline-contact input,.services-inline-contact textarea{border-color:var(--shell-border)!important;background:rgba(4,8,18,.56)!important;color:var(--shell-text)}
.services-inline-contact button{background:linear-gradient(135deg,#7ab4ff,#2ce0c7)!important;color:#07111f}
</style>
<section class="services-simple">
    <div class="services-simple-hero">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if ($err !== ''): ?>
        <div class="services-simple-alert"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="services-simple-groups">
        <a class="<?= $currentGroup === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildGroupLink(''), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($allGroupsText, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php foreach ($groups as $group): ?>
            <?php $code = (string)($group['code'] ?? ''); ?>
            <a class="<?= $currentGroup === $code ? 'active' : '' ?>" href="<?= htmlspecialchars($buildGroupLink($code), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars((string)($group['label'] ?? $code), ENT_QUOTES, 'UTF-8') ?>
                <small><?= (int)($group['count'] ?? 0) ?></small>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($selected !== null): ?>
        <article class="services-simple-selected">
            <a class="services-simple-back" href="<?= htmlspecialchars($buildListLink($page, $currentGroup), ENT_QUOTES, 'UTF-8') ?>">
                <?= $isRu ? 'Назад к каталогу' : 'Back to catalog' ?>
            </a>
            <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="services-simple-selected-content"><?= (string)($selected['content_html'] ?? $selected['excerpt_html'] ?? '') ?></div>
            <div class="services-inline-contact" id="service-contact-form">
                <h3><?= $isRu ? 'Заказать похожую услугу?' : 'Need this service for your project?' ?></h3>
                <p><?= $isRu ? 'Опишите задачу, и я предложу формат работы, сроки и оценку.' : 'Describe your task and I will suggest scope, timeline and estimate.' ?></p>
                <?php if ($contactMsg !== ''): ?>
                    <div class="contact-alert <?= $contactType === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($contactMsg, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="public_contact_submit">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="contact_form_anchor" value="#service-contact-form">
                    <input type="hidden" name="contact_interest" value="enterprise">
                    <input type="hidden" name="contact_csrf" value="<?= htmlspecialchars($contactToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="contact_started_at" value="<?= time() ?>">
                    <input type="hidden" name="contact_campaign" value="<?= htmlspecialchars('service:' . (string)($selected['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="contact-hp" aria-hidden="true">
                    <input type="text" name="contact_name" placeholder="<?= htmlspecialchars($isRu ? 'Имя' : 'Name', ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="email" name="contact_email" placeholder="Email" required>
                    <textarea name="contact_message" placeholder="<?= htmlspecialchars($isRu ? 'Нужна эта услуга. Цели, бюджет, сроки…' : 'I need this service. Goals, budget, timeline…', ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                    <?php if ($turnstileSiteKey !== ''): ?>
                        <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
                    <?php endif; ?>
                    <button type="submit"><?= htmlspecialchars($isRu ? 'Отправить запрос' : 'Send request', ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            </div>
            <?php if (!empty($relatedItems)): ?>
                <h3><?= $isRu ? (string)json_decode('"\u041f\u043e\u0445\u043e\u0436\u0438\u0435 \u0443\u0441\u043b\u0443\u0433\u0438"') : 'Similar services' ?></h3>
                <div class="services-simple-grid">
                    <?php foreach ($relatedItems as $item): ?>
                        <?php
                        $postTitle = (string)($item['title'] ?? '');
                        $excerpt = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($item['excerpt_html'] ?? ''))));
                        $slug = (string)($item['slug'] ?? '');
                        ?>
                        <article class="services-simple-card">
                            <h3><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                            <a class="services-simple-link" href="<?= htmlspecialchars($buildDetailLink($slug), ENT_QUOTES, 'UTF-8') ?>">
                                <?= $isRu ? 'Подробнее' : 'Details' ?>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endif; ?>

    <?php if ($selected === null && !empty($items)): ?>
        <div class="services-simple-grid">
            <?php foreach ($items as $item): ?>
                <?php
                if ((int)($item['__group_header'] ?? 0) === 1):
                    $groupLabel = (string)($item['group_label'] ?? ($item['service_group'] ?? 'General'));
                    $groupCount = (int)($item['group_count'] ?? 0);
                ?>
                <div class="services-simple-group-header" data-service-group="<?= htmlspecialchars((string)($item['service_group'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <h2><?= htmlspecialchars($groupLabel, ENT_QUOTES, 'UTF-8') ?></h2>
                    <small><?= $groupCount ?></small>
                    <div class="services-simple-group-line" aria-hidden="true"></div>
                </div>
                <?php
                    continue;
                endif;
                $postTitle = (string)($item['title'] ?? '');
                $excerpt = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($item['excerpt_html'] ?? ''))));
                $slug = (string)($item['slug'] ?? '');
                ?>
                <article class="services-simple-card">
                    <h3><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="services-simple-link" href="<?= htmlspecialchars($buildDetailLink($slug), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $isRu ? 'Подробнее' : 'Details' ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif ($selected === null): ?>
        <div class="services-simple-empty"><?= htmlspecialchars($emptyText, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($selected === null && $totalPages > 1): ?>
        <nav class="services-simple-pagination" aria-label="Services pages">
            <?php if ($page <= 1): ?>
                <span class="disabled"><?= htmlspecialchars($prevText, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($buildListLink(max(1, $page - 1), $currentGroup), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($prevText, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($buildListLink($i, $currentGroup), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page >= $totalPages): ?>
                <span class="disabled"><?= htmlspecialchars($nextText, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($buildListLink(min($totalPages, $page + 1), $currentGroup), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($nextText, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>




