<?php
$catalog = (array)($ModelPage['offers_catalog'] ?? []);
$isRu = ((string)($catalog['lang'] ?? 'en') === 'ru');
$items = (array)($catalog['items'] ?? []);
$selected = is_array($catalog['selected'] ?? null) ? (array)$catalog['selected'] : [];
$isDetail = !empty($selected) && !empty($catalog['selected_slug']);
$relatedBlog = (array)($catalog['related_blog'] ?? []);
$publicLayoutFooterMaxWidth = '1240px';
$offerContactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$offerContactFlash = function_exists('public_contact_form_flash') ? public_contact_form_flash() : [];
$offerContactType = (string)($offerContactFlash['type'] ?? '');
$offerContactMsg = (string)($offerContactFlash['message'] ?? '');
$offerReturnPath = (string)($_SERVER['REQUEST_URI'] ?? '/');
$offerTurnstileSiteKey = trim((string)($GLOBALS['ContactTurnstileSiteKey'] ?? ''));

$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
?>
<style>
.ofr-page{max-width:1240px;box-sizing:border-box;margin:0 auto;padding:18px 16px 32px;font-family:"IBM Plex Sans",system-ui,sans-serif;color:#0f203a}
.ofr-hero{border:1px solid #d7e2f0;border-radius:18px;padding:20px;background:linear-gradient(145deg,#f9fcff,#edf5ff)}
.ofr-hero h1{margin:0 0 8px;font-size:32px;line-height:1.15;font-family:"Manrope",sans-serif}
.ofr-hero p{margin:0;color:#5b6f88;line-height:1.6;max-width:90ch}
.ofr-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
.ofr-btn{display:inline-flex;align-items:center;text-decoration:none;border-radius:10px;padding:9px 12px;font-weight:700;font-size:14px;border:1px solid #c5d6ea;color:#1f436a;background:#fff;transition:transform .18s ease,box-shadow .22s ease,filter .2s ease,background .2s ease}
.ofr-btn.primary{
    position:relative;
    color:#fff;
    border-color:#1f58b8;
    background:linear-gradient(140deg,#1f6ef0 0%,#0d63d6 48%,#0ea3a0 100%);
    box-shadow:
        0 10px 20px rgba(13,99,214,.28),
        0 4px 0 #0a4eac,
        inset 0 1px 0 rgba(255,255,255,.35);
}
.ofr-btn.primary::before{
    content:"";
    position:absolute;
    inset:1px 1px auto 1px;
    height:48%;
    border-radius:9px 9px 12px 12px;
    background:linear-gradient(180deg,rgba(255,255,255,.28),rgba(255,255,255,0));
    pointer-events:none;
}
.ofr-btn.primary:hover{
    transform:translateY(-2px);
    filter:saturate(1.08);
    box-shadow:
        0 14px 24px rgba(13,99,214,.35),
        0 5px 0 #0a4eac,
        inset 0 1px 0 rgba(255,255,255,.38);
}
.ofr-btn.primary:active{
    transform:translateY(2px);
    box-shadow:
        0 6px 12px rgba(13,99,214,.26),
        0 1px 0 #0a4eac,
        inset 0 1px 0 rgba(255,255,255,.25);
}
.ofr-card .ofr-btn.primary{
    border-radius:9px;
    padding:8px 11px;
    border-color:#c2d4ea;
    color:#fff;
    background:linear-gradient(135deg,#2169d6,#1760cc);
    box-shadow:0 2px 8px rgba(23,96,204,.18);
}
.ofr-card .ofr-btn.primary::before{display:none}
.ofr-card .ofr-btn.primary:hover{
    transform:none;
    filter:none;
    background:linear-gradient(135deg,#2a72df,#1c66d2);
    box-shadow:0 3px 10px rgba(23,96,204,.22);
}
.ofr-card .ofr-btn.primary:active{
    transform:none;
    box-shadow:0 2px 7px rgba(23,96,204,.2);
}
.ofr-actions .ofr-btn{
    border-radius:14px;
    padding:10px 14px;
    letter-spacing:.02em;
    border-width:1px;
    backdrop-filter:blur(4px);
}
.ofr-actions .ofr-btn.primary{
    border-color:#0f5bc3;
    background:
        radial-gradient(120% 140% at 10% 0%,rgba(255,255,255,.32),rgba(255,255,255,0) 44%),
        linear-gradient(140deg,#1f6ef0 0%,#0d63d6 46%,#0ea3a0 100%);
    box-shadow:
        0 14px 26px rgba(13,99,214,.3),
        0 5px 0 #0a4eac,
        inset 0 1px 0 rgba(255,255,255,.35);
}
.ofr-actions .ofr-btn:not(.primary){
    border-color:#9cbce5;
    color:#1a416d;
    background:
        radial-gradient(100% 120% at 10% 0%,rgba(255,255,255,.8),rgba(255,255,255,0) 46%),
        linear-gradient(135deg,#f9fcff,#e4f0ff);
    box-shadow:0 8px 18px rgba(37,86,150,.12),0 4px 0 #c8d8ec;
}
.ofr-actions .ofr-btn:not(.primary):hover{
    transform:translateY(-2px);
    box-shadow:0 12px 20px rgba(37,86,150,.16),0 5px 0 #c8d8ec;
}
.ofr-actions .ofr-btn:not(.primary):active{
    transform:translateY(2px);
    box-shadow:0 5px 10px rgba(37,86,150,.14),0 1px 0 #c8d8ec;
}
.ofr-grid{margin-top:14px;display:grid;gap:12px;grid-template-columns:repeat(2,minmax(0,1fr))}
.ofr-card{border:1px solid #d8e3f2;border-radius:14px;background:#fff;padding:14px;display:grid;gap:8px}
.ofr-card h3{margin:0;font-size:22px;line-height:1.28;font-family:"Manrope",sans-serif;color:#0d2749}
.ofr-card p{margin:0;color:#567191;line-height:1.58}
.ofr-meta{display:flex;gap:8px;flex-wrap:wrap}
.ofr-chip{display:inline-flex;align-items:center;border:1px solid #c8d9ee;border-radius:999px;padding:4px 9px;font-size:11px;letter-spacing:.04em;text-transform:uppercase;color:#355b88;background:#f3f8ff}
.ofr-price{font-weight:700;color:#0e4a8a}
.ofr-detail{margin-top:14px;border:1px solid #d8e3f2;border-radius:16px;background:#fff;padding:16px}
.ofr-back{display:inline-flex;align-items:center;text-decoration:none;border:1px solid #c7d8ed;border-radius:999px;padding:6px 10px;color:#22517f;font-size:13px;margin-bottom:10px}
.ofr-detail h2{margin:0 0 8px;font-size:30px;line-height:1.2;font-family:"Manrope",sans-serif}
.ofr-sub{margin:0 0 10px;color:#5a7291;line-height:1.58}
.ofr-lead{display:grid;gap:10px;margin-top:12px}
.ofr-lead p{margin:0;color:#355476;line-height:1.66}
.ofr-story{margin-top:12px;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#f9fcff}
.ofr-story h3{margin:0 0 8px;font-size:18px;color:#173a62}
.ofr-story p{margin:0 0 8px;color:#3d5f85;line-height:1.66}
.ofr-story p:last-child{margin-bottom:0}
.ofr-sections{margin-top:12px;display:grid;gap:10px}
.ofr-section{border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#fbfdff}
.ofr-section h3{margin:0 0 6px;font-size:18px;color:#173a62}
.ofr-section p{margin:0;color:#3c5d83;line-height:1.62}
.ofr-phases{margin-top:12px;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#f9fcff}
.ofr-phases h3{margin:0 0 8px;font-size:18px;color:#173a62}
.ofr-phase-list{display:grid;gap:8px}
.ofr-phase{border:1px solid #d6e3f3;border-radius:10px;background:#fff;padding:10px}
.ofr-phase h4{margin:0 0 6px;font-size:15px;color:#153a63}
.ofr-phase p{margin:0;color:#3d5f85;line-height:1.58}
.ofr-int{margin-top:12px;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#f9fcff}
.ofr-int h3{margin:0 0 8px;font-size:18px;color:#173a62}
.ofr-int p{margin:0 0 8px;color:#3d5f85;line-height:1.58}
.ofr-int ul{margin:0;padding-left:18px;display:grid;gap:6px;color:#3f638b}
.ofr-check{margin-top:12px;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#f9fcff}
.ofr-check h3{margin:0 0 8px;font-size:18px;color:#173a62}
.ofr-check ul{margin:0;padding-left:18px;display:grid;gap:6px;color:#3f638b;line-height:1.58}
.ofr-related-offers{margin-top:12px;display:grid;gap:8px;grid-template-columns:repeat(3,minmax(0,1fr))}
.ofr-related-offers a{display:block;text-decoration:none;border:1px solid #d5e2f1;border-radius:12px;background:#fff;padding:10px;color:#1d446f;font-weight:600;line-height:1.4}
.ofr-related-offers a:hover{border-color:#0d63d6;color:#0d63d6}
.ofr-order{margin-top:12px;border:1px solid #cddff4;border-radius:14px;padding:14px;background:linear-gradient(145deg,#f7fbff,#edf6ff)}
.ofr-order h3{margin:0 0 8px;font-size:22px;line-height:1.2;color:#133c66;font-family:"Manrope",sans-serif}
.ofr-order p{margin:0 0 10px;color:#3a5f89;line-height:1.58}
.ofr-order-alert{margin:0 0 10px;padding:10px 12px;border:1px solid;border-radius:10px;font-size:13px}
.ofr-order-alert.ok{background:#eaf7ef;border-color:#b6dcc3;color:#234833}
.ofr-order-alert.error{background:#fff1f2;border-color:#efc4ca;color:#6f2632}
.ofr-order form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.ofr-order input,.ofr-order textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #c6d8ef;border-radius:10px;background:#fff;color:#193754;font:inherit}
.ofr-order textarea{grid-column:1 / -1;min-height:120px;resize:vertical}
.ofr-order button{grid-column:1 / -1;border:0;border-radius:10px;padding:11px 14px;font-weight:700;cursor:pointer;background:linear-gradient(135deg,#0d63d6,#0e9a91);color:#fff}
.ofr-contact-hp{position:absolute!important;left:-9999px!important;opacity:0!important;pointer-events:none!important}
.ofr-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d3deec;margin-top:8px}
.ofr-table th,.ofr-table td{border:1px solid #d3deec;padding:8px 10px;text-align:left;vertical-align:top}
.ofr-table th{background:#f3f8ff;color:#14355f;font-size:13px}
@media (max-width:980px){.ofr-grid{grid-template-columns:1fr}.ofr-related-offers{grid-template-columns:1fr}.ofr-order form{grid-template-columns:1fr}}

body.ui-tone-dark .ofr-page{color:#dbe7f6}
body.ui-tone-dark .ofr-hero{border-color:#27415d;background:linear-gradient(145deg,#0f1d2e,#12263d)}
body.ui-tone-dark .ofr-hero h1{color:#e7f0fb}
body.ui-tone-dark .ofr-hero p{color:#9cb3cf}
body.ui-tone-dark .ofr-btn{border-color:#35587b;color:#c9ddf5;background:#14283f}
body.ui-tone-dark .ofr-btn.primary{
    border-color:#3f7fe8;
    color:#f7fbff;
    background:linear-gradient(140deg,#377ef2 0%,#2b6fdb 52%,#1ea7a2 100%);
    box-shadow:
        0 11px 22px rgba(12,28,52,.52),
        0 4px 0 #1c4fa6,
        inset 0 1px 0 rgba(255,255,255,.32);
}
body.ui-tone-dark .ofr-btn.primary:hover{
    transform:translateY(-2px);
    filter:saturate(1.1);
    box-shadow:
        0 16px 28px rgba(10,24,45,.62),
        0 5px 0 #1c4fa6,
        inset 0 1px 0 rgba(255,255,255,.36);
}
body.ui-tone-dark .ofr-btn.primary:active{
    transform:translateY(2px);
    box-shadow:
        0 6px 12px rgba(10,24,45,.52),
        0 1px 0 #1c4fa6,
        inset 0 1px 0 rgba(255,255,255,.22);
}
body.ui-tone-dark .ofr-card .ofr-btn.primary{
    border-color:#4179d8;
    background:linear-gradient(135deg,#3378e7,#2a6edc);
    box-shadow:0 3px 10px rgba(21,45,82,.4);
}
body.ui-tone-dark .ofr-card .ofr-btn.primary:hover{
    background:linear-gradient(135deg,#3d83f0,#3176e4);
    box-shadow:0 4px 12px rgba(21,45,82,.48);
}
body.ui-tone-dark .ofr-card .ofr-btn.primary:active{
    box-shadow:0 2px 7px rgba(21,45,82,.44);
}
body.ui-tone-dark .ofr-actions .ofr-btn{
    border-radius:14px;
}
body.ui-tone-dark .ofr-actions .ofr-btn.primary{
    border-color:#3f7fe8;
    background:
        radial-gradient(120% 140% at 10% 0%,rgba(255,255,255,.22),rgba(255,255,255,0) 44%),
        linear-gradient(140deg,#377ef2 0%,#2b6fdb 52%,#1ea7a2 100%);
    box-shadow:
        0 16px 28px rgba(10,24,45,.62),
        0 5px 0 #1c4fa6,
        inset 0 1px 0 rgba(255,255,255,.3);
}
body.ui-tone-dark .ofr-actions .ofr-btn:not(.primary){
    border-color:#46688d;
    color:#d4e7ff;
    background:
        radial-gradient(100% 120% at 10% 0%,rgba(255,255,255,.13),rgba(255,255,255,0) 46%),
        linear-gradient(135deg,#16304b,#10253a);
    box-shadow:0 10px 20px rgba(3,14,27,.4),0 4px 0 #0a1b2d;
}
body.ui-tone-dark .ofr-actions .ofr-btn:not(.primary):hover{
    transform:translateY(-2px);
    box-shadow:0 14px 24px rgba(3,14,27,.48),0 5px 0 #0a1b2d;
}
body.ui-tone-dark .ofr-actions .ofr-btn:not(.primary):active{
    transform:translateY(2px);
    box-shadow:0 6px 12px rgba(3,14,27,.42),0 1px 0 #0a1b2d;
}
body.ui-tone-dark .ofr-btn:hover{background:#19324f}
body.ui-tone-dark .ofr-grid .ofr-card,
body.ui-tone-dark .ofr-detail{border-color:#2b4662;background:#101f31}
body.ui-tone-dark .ofr-card h3,
body.ui-tone-dark .ofr-detail h2{color:#e6effb}
body.ui-tone-dark .ofr-card p,
body.ui-tone-dark .ofr-sub,
body.ui-tone-dark .ofr-lead p{color:#9fb6d2}
body.ui-tone-dark .ofr-chip{border-color:#3a5f86;background:#17314b;color:#b7d0ee}
body.ui-tone-dark .ofr-price{color:#9ed0ff}
body.ui-tone-dark .ofr-back{border-color:#3a5f86;color:#bad4ef;background:#132841}
body.ui-tone-dark .ofr-story,
body.ui-tone-dark .ofr-section,
body.ui-tone-dark .ofr-phases,
body.ui-tone-dark .ofr-int,
body.ui-tone-dark .ofr-check,
body.ui-tone-dark .ofr-order{border-color:#2f4b68;background:#13263b}
body.ui-tone-dark .ofr-section{background:#102338}
body.ui-tone-dark .ofr-story h3,
body.ui-tone-dark .ofr-section h3,
body.ui-tone-dark .ofr-phases h3,
body.ui-tone-dark .ofr-int h3,
body.ui-tone-dark .ofr-check h3,
body.ui-tone-dark .ofr-order h3{color:#dbeafe}
body.ui-tone-dark .ofr-story p,
body.ui-tone-dark .ofr-section p,
body.ui-tone-dark .ofr-phase p,
body.ui-tone-dark .ofr-int p,
body.ui-tone-dark .ofr-int ul,
body.ui-tone-dark .ofr-check ul,
body.ui-tone-dark .ofr-order p{color:#9eb8d5}
body.ui-tone-dark .ofr-phase{border-color:#345675;background:#0f2236}
body.ui-tone-dark .ofr-phase h4{color:#d1e3f8}
body.ui-tone-dark .ofr-table{border-color:#365776;background:#10253a}
body.ui-tone-dark .ofr-table th,
body.ui-tone-dark .ofr-table td{border-color:#365776;color:#c8dbf2}
body.ui-tone-dark .ofr-table th{background:#17324b;color:#d7e8fb}
body.ui-tone-dark .ofr-related-offers a{border-color:#385a7f;background:#10253a;color:#c3d9f2}
body.ui-tone-dark .ofr-related-offers a:hover{border-color:#5a8ec8;color:#d9ebff}
body.ui-tone-dark .ofr-order input,
body.ui-tone-dark .ofr-order textarea{background:#0d1d2f;border-color:#395a7e;color:#dbe9f9}
body.ui-tone-dark .ofr-order input::placeholder,
body.ui-tone-dark .ofr-order textarea::placeholder{color:#7f9cbd}
body.ui-tone-dark .ofr-order button{background:linear-gradient(135deg,#2c6fde,#1b8f8f);color:#fff}
</style>

<section class="ofr-page">
    <article class="ofr-hero">
        <h1><?= htmlspecialchars($t('Офферы', 'Offers'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($t('Здесь собраны мои практические офферы: каждый формат не “теория”, а отработанный сценарий внедрения на реальных проектах. Выбирайте направление под задачу, открывайте деталку и сразу видите, что именно внедряется, какой результат ожидается и с какого бюджета можно стартовать.', 'This section contains my practical offers: each one is a proven implementation format, not theory. Choose the direction that matches your task and open details to see what gets implemented, what outcomes to expect, and the starting budget.'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="ofr-actions">
            <a class="ofr-btn primary" href="https://geoip.space" target="_blank" rel="noopener">geoip.space</a>
            <a class="ofr-btn" href="https://apigeoip.ru" target="_blank" rel="noopener">apigeoip.ru</a>
            <?php if (!$isDetail): ?>
                <a class="ofr-btn" href="https://postforge.ru" target="_blank" rel="noopener">postforge.ru</a>
            <?php endif; ?>
            <a class="ofr-btn" href="/contact/"><?= htmlspecialchars($t('Обсудить внедрение', 'Discuss integration'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </article>

    <?php if (!$isDetail): ?>
        <div class="ofr-grid">
            <?php foreach ($items as $item): ?>
                <article class="ofr-card">
                    <div class="ofr-meta">
                        <span class="ofr-chip"><?= htmlspecialchars($t('Оффер', 'Offer'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="ofr-chip ofr-price"><?= htmlspecialchars((string)($item['price_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string)($item['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="ofr-actions">
                        <a class="ofr-btn primary" href="/offers/<?= htmlspecialchars((string)($item['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/"><?= htmlspecialchars($t('Открыть оффер', 'Open offer'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <article class="ofr-detail">
            <a class="ofr-back" href="/offers/"><?= htmlspecialchars($t('Назад к списку офферов', 'Back to offers list'), ENT_QUOTES, 'UTF-8') ?></a>
            <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="ofr-sub"><?= htmlspecialchars((string)($selected['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="ofr-meta">
                <span class="ofr-chip"><?= htmlspecialchars($t('Детальный оффер', 'Detailed offer'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="ofr-chip ofr-price"><?= htmlspecialchars((string)($selected['price_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="ofr-lead">
                <?php foreach ((array)($selected[$isRu ? 'overview_ru' : 'overview_en'] ?? []) as $paragraph): ?>
                    <p><?= htmlspecialchars((string)$paragraph, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>

            <?php $caseStory = (array)($selected[$isRu ? 'case_story_ru' : 'case_story_en'] ?? []); ?>
            <?php if (!empty($caseStory)): ?>
                <article class="ofr-story">
                    <h3><?= htmlspecialchars($t('Кейс: как эта задача обычно выглядит на практике', 'Case: how this problem usually appears in practice'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php foreach ($caseStory as $storyParagraph): ?>
                        <p><?= htmlspecialchars((string)$storyParagraph, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endforeach; ?>
                </article>
            <?php endif; ?>

            <?php $detailSections = (array)($selected[$isRu ? 'sections_ru' : 'sections_en'] ?? []); ?>
            <?php if (!empty($detailSections)): ?>
                <div class="ofr-sections">
                    <?php foreach ($detailSections as $section): ?>
                        <article class="ofr-section">
                            <h3><?= htmlspecialchars((string)($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars((string)($section['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php $phaseRows = (array)($selected[$isRu ? 'implementation_phases_ru' : 'implementation_phases_en'] ?? []); ?>
            <?php if (!empty($phaseRows)): ?>
                <article class="ofr-phases">
                    <h3><?= htmlspecialchars($t('Фазы внедрения: от диагностики до масштабирования', 'Implementation phases: from diagnostic to scale'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="ofr-phase-list">
                        <?php foreach ($phaseRows as $phase): ?>
                            <article class="ofr-phase">
                                <h4><?= htmlspecialchars((string)($phase['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h4>
                                <p><?= htmlspecialchars((string)($phase['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endif; ?>

            <?php $tableRows = (array)($selected[$isRu ? 'compliance_table_ru' : 'compliance_table_en'] ?? []); ?>
            <?php if (!empty($tableRows)): ?>
                <article class="ofr-int">
                    <h3><?= htmlspecialchars($t('Требования, риски и выгода от внедрения', 'Requirements, risk and implementation benefit'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <table class="ofr-table">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars($t('Сценарий', 'Scenario'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($t('Без внедрения', 'Without implementation'), ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($t('После внедрения', 'After implementation'), ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableRows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row['scenario'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($row['without'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($row['with'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>
            <?php endif; ?>

            <?php $ints = (array)($selected['integrations'] ?? []); ?>
            <?php if (!empty($ints)): ?>
                <article class="ofr-int">
                    <h3><?= htmlspecialchars($t('Варианты интеграции', 'Integration options'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul>
                        <li><strong>CMS:</strong> <?= htmlspecialchars(implode(', ', (array)($ints['cms'] ?? [])), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><strong>Frameworks:</strong> <?= htmlspecialchars(implode(', ', (array)($ints['frameworks'] ?? [])), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><strong>CRM:</strong> <?= htmlspecialchars(implode(', ', (array)($ints['crm'] ?? [])), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><strong>Trackers:</strong> <?= htmlspecialchars(implode(', ', (array)($ints['trackers'] ?? [])), ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </article>
            <?php endif; ?>

            <?php $checklist = (array)($selected[$isRu ? 'checklist_ru' : 'checklist_en'] ?? []); ?>
            <?php if (!empty($checklist)): ?>
                <article class="ofr-check">
                    <h3><?= htmlspecialchars($t('Чеклист внедрения', 'Implementation checklist'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul>
                        <?php foreach ($checklist as $checkItem): ?>
                            <li><?= htmlspecialchars((string)$checkItem, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endif; ?>

            <?php $antiPatterns = (array)($selected[$isRu ? 'anti_patterns_ru' : 'anti_patterns_en'] ?? []); ?>
            <?php if (!empty($antiPatterns)): ?>
                <article class="ofr-check">
                    <h3><?= htmlspecialchars($t('Анти-паттерны, которые ломают результат', 'Anti-patterns that break delivery outcomes'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul>
                        <?php foreach ($antiPatterns as $antiItem): ?>
                            <li><?= htmlspecialchars((string)$antiItem, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endif; ?>

            <?php $extraOffers = (array)($selected['additional_offers'] ?? []); ?>
            <?php if (!empty($extraOffers)): ?>
                <article class="ofr-int">
                    <h3><?= htmlspecialchars($t('Дополнительные предложения', 'Additional offers'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($t('Если задача шире, можно собрать связку внедрений: это ускоряет результат и снижает стоимость последующих доработок.', 'If your scope is broader, we can combine related offers to accelerate outcomes and reduce future implementation costs.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="ofr-related-offers">
                        <?php foreach ($extraOffers as $extraOffer): ?>
                            <?php
                            $extraSlug = trim((string)($extraOffer['slug'] ?? ''));
                            if ($extraSlug === '') { continue; }
                            $extraTitle = $isRu ? (string)($extraOffer['title_ru'] ?? '') : (string)($extraOffer['title_en'] ?? '');
                            if ($extraTitle === '') {
                                $extraTitle = $isRu ? (string)($extraOffer['title_en'] ?? '') : (string)($extraOffer['title_ru'] ?? '');
                            }
                            ?>
                            <a href="/offers/<?= htmlspecialchars($extraSlug, ENT_QUOTES, 'UTF-8') ?>/"><?= htmlspecialchars($extraTitle, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endif; ?>

            <article class="ofr-order" id="offer-order-form">
                <h3><?= htmlspecialchars($t('Заказать внедрение оффера и получить план запуска', 'Order this offer and get a launch plan'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('Оставьте заявку: я предложу практический формат внедрения, сроки запуска и ориентир по бюджету под ваш проект.', 'Send a request and I will propose the implementation format, launch timeline and budget estimate for your project.'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($offerContactMsg !== ''): ?>
                    <div class="ofr-order-alert <?= $offerContactType === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($offerContactMsg, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars($offerReturnPath, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="public_contact_submit">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($offerReturnPath, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="contact_form_anchor" value="#offer-order-form">
                    <input type="hidden" name="contact_interest" value="offers">
                    <input type="hidden" name="contact_csrf" value="<?= htmlspecialchars($offerContactToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="contact_started_at" value="<?= time() ?>">
                    <input type="hidden" name="contact_campaign" value="<?= htmlspecialchars('offer:' . (string)($selected['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="ofr-contact-hp" aria-hidden="true">
                    <input type="text" name="contact_name" placeholder="<?= htmlspecialchars($t('Имя', 'Name'), ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="email" name="contact_email" placeholder="Email" required>
                    <textarea name="contact_message" placeholder="<?= htmlspecialchars($t('Опишите задачу: что внедряем, какие сроки и какой ожидаемый результат.', 'Describe your task: what should be implemented, timeline and expected outcome.'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                    <?php if ($offerTurnstileSiteKey !== ''): ?>
                        <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($offerTurnstileSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
                    <?php endif; ?>
                    <button type="submit"><?= htmlspecialchars($t('Получить план внедрения', 'Get implementation plan'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            </article>

            <?php if (!empty($relatedBlog)): ?>
                <article class="ofr-int">
                    <h3><?= htmlspecialchars($t('Связанные материалы блога', 'Related blog posts'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul>
                        <?php foreach ($relatedBlog as $post): ?>
                            <li><a href="<?= htmlspecialchars((string)($post['url'] ?? '/blog/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endif; ?>
        </article>
    <?php endif; ?>
</section>
