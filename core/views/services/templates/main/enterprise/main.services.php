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
    ? 'Каталог решений по направлениям: выбирайте сферу, смотрите кейсовый формат и техническую глубину.'
    : 'A domain-based services catalog with architecture-first execution: product engineering, automation, integrations and advisory delivery mapped to business outcomes.';
if ($isRu) {
    $lead = 'Каталог решений с фокусом на архитектуру и бизнес-эффект: продуктовая разработка, автоматизация, интеграции и консалтинг в формате, который масштабируется вместе с вашим проектом.';
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
.services-enterprise{max-width:1180px;margin:0 auto;padding:18px 16px 30px;color:#e7f2ff;font-family:"Space Grotesk","Segoe UI",Arial,sans-serif}
.services-enterprise-hero{border:1px solid #304966;border-radius:20px;padding:20px;background:linear-gradient(145deg,#102338,#0d1c2e)}
.services-enterprise-hero h1{margin:0 0 8px;font-size:34px;letter-spacing:.01em}
.services-enterprise-hero p{margin:0;color:#9db7d8;max-width:86ch;line-height:1.62}
.services-enterprise-alert{margin-top:12px;border:1px solid #765439;background:#34261a;color:#ffdcb9;border-radius:10px;padding:10px 12px}
.services-enterprise-groups{margin-top:12px;display:flex;flex-wrap:wrap;gap:8px}
.services-enterprise-groups a{display:inline-flex;align-items:center;gap:6px;border:1px solid #3f5f84;border-radius:999px;padding:7px 11px;text-decoration:none;color:#c5dbf8;background:#102138;font-size:13px}
.services-enterprise-groups a.active{background:linear-gradient(135deg,#2ec5a7,#7ba5ff);color:#08131f;border-color:transparent}
.services-enterprise-groups small{opacity:.8}
.services-enterprise-selected{margin-top:14px;border:1px solid #324c6c;border-radius:14px;background:linear-gradient(180deg,#13243a,#101d31);padding:18px}
.services-enterprise-selected h2{margin:0 0 6px}
.services-enterprise-selected-content{margin-top:10px;color:#d4e5fa;line-height:1.66}
.services-enterprise-selected-content p{margin:0 0 12px}
.services-enterprise-selected-content h2,.services-enterprise-selected-content h3{margin:16px 0 8px;color:#e7f3ff}
.services-enterprise-selected-content ul,.services-enterprise-selected-content ol{margin:0 0 12px 18px}
.services-enterprise-selected-content .svc-block{margin:0 0 12px;padding:12px;border:1px solid #365777;border-radius:12px;background:#0f2136}
.services-enterprise-selected-content .svc-block:last-child{margin-bottom:0}
.services-enterprise-selected-content table{width:100%;max-width:100%;display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;border-collapse:collapse;margin:12px 0;background:#0f1f33;border:1px solid #35516f}
.services-enterprise-selected-content thead,.services-enterprise-selected-content tbody,.services-enterprise-selected-content tr{display:table;width:100%;table-layout:fixed}
.services-enterprise-selected-content th,.services-enterprise-selected-content td{border:1px solid #35516f;padding:8px 10px;vertical-align:top;min-width:140px;word-break:break-word;overflow-wrap:anywhere}
.services-enterprise-selected-content th{background:#162e4a;color:#e8f2ff;font-weight:700}
.services-enterprise-back{display:inline-flex;align-items:center;gap:6px;margin-bottom:10px;padding:6px 10px;border:1px solid #45648a;border-radius:999px;color:#bfe0ff;text-decoration:none;font-size:13px;font-weight:600}
.services-enterprise-back:hover{background:rgba(117,170,230,.12)}
.services-enterprise-grid{margin-top:12px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.services-enterprise-group-header{grid-column:1/-1;display:flex;align-items:center;gap:12px;margin:4px 0 0}
.services-enterprise-group-header h2{margin:0;font-size:18px;color:#e7f2ff}
.services-enterprise-group-header small{color:#9db7d8;font-weight:600}
.services-enterprise-group-line{height:1px;flex:1;background:linear-gradient(90deg,#3f5f84,rgba(63,95,132,0));}
.services-enterprise-card{background:linear-gradient(180deg,#12243a,#0f1d31);border:1px solid #304966;border-radius:14px;padding:14px;display:flex;flex-direction:column;gap:8px}
.services-enterprise-card h3{margin:0;font-size:20px}
.services-enterprise-card p{margin:0;color:#b9cce4;line-height:1.5}
.services-enterprise-link{margin-top:auto;align-self:flex-start;display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #45648a;border-radius:999px;color:#bfe0ff;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap}
.services-enterprise-link:hover{background:rgba(117,170,230,.12)}
.services-enterprise-empty{margin-top:14px;border:1px solid #324c6c;border-radius:12px;padding:12px;color:#b9cbe2;background:#112238}
.services-enterprise-contact{margin-top:14px;border:1px solid #2f4a6b;border-radius:14px;padding:14px;background:rgba(14,29,47,.45)}
.services-enterprise-contact h3{margin:0 0 8px;font-size:18px;color:#e3f1ff}
.services-enterprise-contact p{margin:0 0 10px;color:#bcd2ec}
.services-enterprise-contact .contact-alert{margin:0 0 10px;padding:10px;border:1px solid;border-radius:8px;font-size:14px}
.services-enterprise-contact .contact-alert.ok{background:#163728;border-color:#2a6d4c;color:#c9f1dd}
.services-enterprise-contact .contact-alert.error{background:#3a1a24;border-color:#6f3445;color:#ffd9e2}
.services-enterprise-contact form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.services-enterprise-contact input,.services-enterprise-contact textarea{width:100%;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid #35506f;background:#0b1625;color:#d4e6fb;font:inherit}
.services-enterprise-contact textarea{grid-column:1 / -1;min-height:120px;resize:vertical}
.services-enterprise-contact button{grid-column:1 / -1;border:0;border-radius:10px;padding:11px 14px;font-weight:700;cursor:pointer;background:linear-gradient(135deg,#36bda3,#6a95f0);color:#08131f}
.contact-hp{position:absolute!important;left:-9999px!important;opacity:0!important;pointer-events:none!important}
.services-enterprise-pagination{margin-top:16px;display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.services-enterprise-pagination a,.services-enterprise-pagination span{display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;line-height:1.2;border:1px solid #3f5f84;border-radius:8px;min-width:36px;min-height:34px;padding:7px 10px;text-decoration:none;color:#c5dbf8;background:#102138;background-image:none;box-shadow:none;overflow:hidden}
.services-enterprise-pagination .active{background:linear-gradient(135deg,#2ec5a7,#7ba5ff);color:#08131f;border-color:transparent}
.services-enterprise-pagination .disabled{opacity:.55;background:#122136;color:#8ea8cb;border-color:#304a6d;cursor:not-allowed}
body:not(.ui-tone-dark) .services-enterprise{color:#0f203a}
body:not(.ui-tone-dark) .services-enterprise-hero{border-color:#d6e0ed;background:linear-gradient(145deg,#f9fcff,#eff6ff)}
body:not(.ui-tone-dark) .services-enterprise-hero p{color:#5b6f88}
body:not(.ui-tone-dark) .services-enterprise-groups a{border-color:#c7d7ec;color:#2f4d71;background:#fff}
body:not(.ui-tone-dark) .services-enterprise-groups a.active{background:#0d63d6;color:#fff;border-color:#0d63d6}
body:not(.ui-tone-dark) .services-enterprise-selected{border-color:#d7e2f0;background:#ffffff}
body:not(.ui-tone-dark) .services-enterprise-selected-content{color:#1b3452}
body:not(.ui-tone-dark) .services-enterprise-selected-content h2,
body:not(.ui-tone-dark) .services-enterprise-selected-content h3{color:#132f52}
body:not(.ui-tone-dark) .services-enterprise-selected-content .svc-block{border-color:#d6e2f1;background:#f9fcff}
body:not(.ui-tone-dark) .services-enterprise-selected-content table{background:#fff;border-color:#d3deec}
body:not(.ui-tone-dark) .services-enterprise-selected-content th,
body:not(.ui-tone-dark) .services-enterprise-selected-content td{border-color:#d3deec}
body:not(.ui-tone-dark) .services-enterprise-selected-content th{background:#f4f8ff;color:#14355f}
body:not(.ui-tone-dark) .services-enterprise-back{border-color:#bfd5ee;color:#0d5db7}
body:not(.ui-tone-dark) .services-enterprise-back:hover{background:#f1f7ff}
body:not(.ui-tone-dark) .services-enterprise-contact{border-color:#d8e4f2;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
body:not(.ui-tone-dark) .services-enterprise-contact h3{color:#14355f}
body:not(.ui-tone-dark) .services-enterprise-contact p{color:#355477}
body:not(.ui-tone-dark) .services-enterprise-contact input,
body:not(.ui-tone-dark) .services-enterprise-contact textarea{border-color:#c8d7ea;background:#fbfdff;color:#163250}
body:not(.ui-tone-dark) .services-enterprise-contact button{background:linear-gradient(135deg,#0d63d6,#0e9a91);color:#fff}
body:not(.ui-tone-dark) .services-enterprise-card{background:#fff;border-color:#d6e0ed}
body:not(.ui-tone-dark) .services-enterprise-card p{color:#5b6f88}
body:not(.ui-tone-dark) .services-enterprise-link{border-color:#bfd5ee;color:#0d5db7}
body:not(.ui-tone-dark) .services-enterprise-link:hover{background:#f1f7ff}
body:not(.ui-tone-dark) .services-enterprise-group-header h2{color:#0f203a}
body:not(.ui-tone-dark) .services-enterprise-group-header small{color:#5b6f88}
body:not(.ui-tone-dark) .services-enterprise-group-line{background:linear-gradient(90deg,#cfe0f3,rgba(207,224,243,0))}
@media (max-width:860px){.services-enterprise-grid{grid-template-columns:1fr}.services-enterprise-contact form{grid-template-columns:1fr}.services-enterprise-selected-content th,.services-enterprise-selected-content td{min-width:120px;font-size:13px}}
</style>
<section class="services-enterprise">
    <div class="services-enterprise-hero">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if ($err !== ''): ?>
        <div class="services-enterprise-alert"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="services-enterprise-groups">
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
        <article class="services-enterprise-selected">
            <a class="services-enterprise-back" href="<?= htmlspecialchars($buildListLink($page, $currentGroup), ENT_QUOTES, 'UTF-8') ?>">
                <?= $isRu ? 'Назад к каталогу' : 'Back to catalog' ?>
            </a>
            <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="services-enterprise-selected-content"><?= (string)($selected['content_html'] ?? $selected['excerpt_html'] ?? '') ?></div>
            <div class="services-enterprise-contact" id="service-contact-form">
                <h3><?= $isRu ? 'Заказать похожую услугу?' : 'Need this service for your project?' ?></h3>
                <p><?= $isRu ? 'Опишите задачу, и я предложу архитектуру, этапы и оценку.' : 'Describe your task and I will suggest architecture, phases and estimate.' ?></p>
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
                <div class="services-enterprise-grid">
                    <?php foreach ($relatedItems as $item): ?>
                        <?php
                        $postTitle = (string)($item['title'] ?? '');
                        $excerpt = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($item['excerpt_html'] ?? ''))));
                        $slug = (string)($item['slug'] ?? '');
                        ?>
                        <article class="services-enterprise-card">
                            <h3><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                            <a class="services-enterprise-link" href="<?= htmlspecialchars($buildDetailLink($slug), ENT_QUOTES, 'UTF-8') ?>">
                                <?= $isRu ? 'Подробнее' : 'Details' ?>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endif; ?>

    <?php if ($selected === null && !empty($items)): ?>
        <div class="services-enterprise-grid">
            <?php foreach ($items as $item): ?>
                <?php
                if ((int)($item['__group_header'] ?? 0) === 1):
                    $groupLabel = (string)($item['group_label'] ?? ($item['service_group'] ?? 'General'));
                    $groupCount = (int)($item['group_count'] ?? 0);
                ?>
                <div class="services-enterprise-group-header" data-service-group="<?= htmlspecialchars((string)($item['service_group'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <h2><?= htmlspecialchars($groupLabel, ENT_QUOTES, 'UTF-8') ?></h2>
                    <small><?= $groupCount ?></small>
                    <div class="services-enterprise-group-line" aria-hidden="true"></div>
                </div>
                <?php
                    continue;
                endif;
                $postTitle = (string)($item['title'] ?? '');
                $excerpt = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($item['excerpt_html'] ?? ''))));
                $slug = (string)($item['slug'] ?? '');
                ?>
                <article class="services-enterprise-card">
                    <h3><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="services-enterprise-link" href="<?= htmlspecialchars($buildDetailLink($slug), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $isRu ? 'Подробнее' : 'Details' ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif ($selected === null): ?>
        <div class="services-enterprise-empty"><?= htmlspecialchars($emptyText, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($selected === null && $totalPages > 1): ?>
        <nav class="services-enterprise-pagination" aria-label="Services pages">
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



