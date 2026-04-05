<?php
$catalog = (array)($ModelPage['projects_catalog'] ?? []);
$items = (array)($catalog['items'] ?? []);
$selected = is_array($catalog['selected'] ?? null) ? $catalog['selected'] : null;
$lang = strtolower((string)($catalog['lang'] ?? 'en'));
$isRu = $lang === 'ru';

$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};

$contactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$contactFlash = function_exists('public_contact_form_flash') ? public_contact_form_flash() : [];
$contactType = (string)($contactFlash['type'] ?? '');
$contactMsg = (string)($contactFlash['message'] ?? '');
$returnPath = (string)($_SERVER['REQUEST_URI'] ?? '/projects/');
$turnstileSiteKey = trim((string)($GLOBALS['ContactTurnstileSiteKey'] ?? ''));
$publicLayoutFooterMaxWidth = '1180px';

$toList = static function (string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
        return [];
    }
    $chunks = preg_split('/[\r\n;]+/', $raw) ?: [];
    $out = [];
    foreach ($chunks as $chunk) {
        $v = trim((string)$chunk);
        if ($v !== '') {
            $out[] = $v;
        }
    }
    return $out;
};

$fallbackProducts = [
    [
        'title' => 'geoip.space',
        'slug' => 'geoip-space',
        'symbolic_code' => 'geoip-space',
        'project_url' => 'https://geoip.space/',
        'role_summary' => 'Geo API + antifraud scoring',
        'industry_summary' => $t('Fintech, e-commerce, SaaS, security', 'Fintech, e-commerce, SaaS, security'),
        'period_summary' => "Starter: from $29/mo; Growth: from $99/mo; Enterprise: custom",
        'stack_summary' => "REST API (JSON); signed webhooks; PHP/Node.js/Python/Go/Java",
        'result_summary' => $t('Объяснимый risk-scoring с триггерами для compliance и support.', 'Explainable risk scoring with trigger-level transparency for compliance and support.'),
        'excerpt_html' => $t('API для геоаналитики, ASN/network-контекста и antifraud-сигналов для login, checkout и risk-routing.', 'API for geolocation, ASN/network context and antifraud signals for login, checkout and risk routing.'),
    ],
    [
        'title' => 'postforge.ru',
        'slug' => 'postforge-ru',
        'symbolic_code' => 'postforge-ru',
        'project_url' => 'https://postforge.ru/',
        'role_summary' => 'AI-SEO publishing pipeline',
        'industry_summary' => $t('Media, B2B marketing, content teams', 'Media, B2B marketing, content teams'),
        'period_summary' => "Starter: from $49/mo; Pro: from $199/mo; Enterprise: custom",
        'stack_summary' => "WordPress/headless CMS/custom CMS; webhook orchestration; scheduled publishing",
        'result_summary' => $t('Управляемый SEO-production без контентного шума и с контролем качества.', 'Governed SEO production with quality controls and predictable growth cadence.'),
        'excerpt_html' => $t('Платформа для массового и управляемого выпуска SEO-контента: кластеры спроса, качество, аналитика.', 'Platform for scalable SEO content production with demand clustering, quality gates and analytics.'),
    ],
    [
        'title' => 'ANTIFRAUD TRACKER',
        'slug' => 'antifraud-tracker',
        'symbolic_code' => 'antifraud-tracker',
        'project_url' => '',
        'role_summary' => 'Risk events + incident workflow',
        'industry_summary' => $t('Risk-ops, fraud, support teams', 'Risk-ops, fraud, support teams'),
        'period_summary' => "Team: from $79/mo; Scale: from $299/mo; Enterprise: custom",
        'stack_summary' => "Webhook/REST/event bus; SSO; audit export; CRM/SIEM/helpdesk integration",
        'result_summary' => $t('Сокращение времени от детекта до решения за счет единого контура расследования.', 'Reduced time from detection to resolution with unified investigation workflow.'),
        'excerpt_html' => $t('Система трекинга риск-событий с очередями расследования, эскалациями и audit trail.', 'Risk-event tracking with investigation queues, escalations and full audit trail.'),
    ],
];

if (empty($items)) {
    $items = $fallbackProducts;
}

$productRows = [];
foreach ($items as $row) {
    if (!is_array($row)) {
        continue;
    }
    $productRows[] = [
        'title' => (string)($row['title'] ?? ''),
        'slug' => (string)($row['slug'] ?? ''),
        'symbolic_code' => (string)($row['symbolic_code'] ?? ''),
        'project_url' => trim((string)($row['project_url'] ?? '')),
        'tag' => trim((string)($row['role_summary'] ?? '')),
        'audience' => trim((string)($row['industry_summary'] ?? '')),
        'pricing_summary' => trim((string)($row['period_summary'] ?? '')),
        'integrations_summary' => trim((string)($row['stack_summary'] ?? '')),
        'nuance' => trim((string)($row['result_summary'] ?? '')),
        'summary_html' => (string)($row['excerpt_html'] ?? ''),
        'pricing_html' => (string)($row['challenge_html'] ?? ''),
        'integrations_html' => (string)($row['solution_html'] ?? ''),
        'value_html' => (string)($row['impact_html'] ?? ''),
    ];
}

if ($selected !== null) {
    $productRows = [[
        'title' => (string)($selected['title'] ?? ''),
        'slug' => (string)($selected['slug'] ?? ''),
        'symbolic_code' => (string)($selected['symbolic_code'] ?? ''),
        'project_url' => trim((string)($selected['project_url'] ?? '')),
        'tag' => trim((string)($selected['role_summary'] ?? '')),
        'audience' => trim((string)($selected['industry_summary'] ?? '')),
        'pricing_summary' => trim((string)($selected['period_summary'] ?? '')),
        'integrations_summary' => trim((string)($selected['stack_summary'] ?? '')),
        'nuance' => trim((string)($selected['result_summary'] ?? '')),
        'summary_html' => (string)($selected['excerpt_html'] ?? ''),
        'pricing_html' => (string)($selected['challenge_html'] ?? ''),
        'integrations_html' => (string)($selected['solution_html'] ?? ''),
        'value_html' => (string)($selected['impact_html'] ?? ''),
    ]];
}

$productDeep = [
    'geoip-space' => [
        'use_cases' => $t(
            'Антифрод-проверки в auth/login, checkout и payout; фильтрация ботов в performance-маркетинге; гео-правила для compliance и контентных ограничений.',
            'Antifraud checks for auth/login, checkout and payout; bot filtering in performance marketing; geo rules for compliance and content restrictions.'
        ),
        'capabilities' => $t(
            'IP intelligence, ASN/network сигналы, proxy/VPN/tor detection, risk scoring, правила и explainable decision log для support/compliance.',
            'IP intelligence, ASN/network signals, proxy/VPN/tor detection, risk scoring, rules and explainable decision logs for support/compliance.'
        ),
        'integration' => $t(
            'Встраивается в любой backend через REST API и webhooks: синхронные проверки на критичных шагах и асинхронный event-поток в CRM/SIEM/BI. Поддерживаются phased rollout и fallback-режимы.',
            'Integrates into any backend via REST API and webhooks: synchronous checks on critical steps and async event flow to CRM/SIEM/BI. Supports phased rollout and fallback modes.'
        ),
    ],
    'postforge-ru' => [
        'use_cases' => $t(
            'Масштабируемый выпуск SEO-контента для продуктовых и сервисных страниц; контент-хабы под спрос; multilingual контуры для RU/EN проектов.',
            'Scalable SEO production for product and service pages; demand-driven content hubs; multilingual RU/EN workflows.'
        ),
        'capabilities' => $t(
            'Планирование кластеров тем, генерация по шаблонам, quality gates, редакторские этапы, расписание публикаций и контроль индексации/эффективности.',
            'Topic-cluster planning, template generation, quality gates, editorial stages, scheduled publishing and index/performance tracking.'
        ),
        'integration' => $t(
            'Интеграция с WordPress, headless CMS и custom backend через API/webhooks/очереди: draft -> moderation -> publish -> analytics feedback loop.',
            'Integrates with WordPress, headless CMS and custom backends via API/webhooks/queues: draft -> moderation -> publish -> analytics feedback loop.'
        ),
    ],
    'antifraud-tracker' => [
        'use_cases' => $t(
            'Incident-менеджмент для anti-fraud команд, triage очередей по приоритетам, эскалации по SLA, совместная работа risk/support/compliance.',
            'Incident management for antifraud teams, priority triage queues, SLA escalations and cross-team risk/support/compliance workflows.'
        ),
        'capabilities' => $t(
            'Единый реестр risk-событий, маршрутизация инцидентов, аудит действий, фильтры и поиск по сигналам, playbook-процессы реагирования.',
            'Unified risk event registry, incident routing, action audit trail, filters/search by signals and response playbooks.'
        ),
        'integration' => $t(
            'Подключение через webhook/REST/message bus, двусторонние интеграции с CRM/helpdesk/SIEM, role-based access и экспорт отчетов для внутренних и регуляторных контуров.',
            'Connects via webhook/REST/message bus, bi-directional CRM/helpdesk/SIEM integrations, role-based access and report exports for internal/regulatory workflows.'
        ),
    ],
];
?>
<style>
.products-page{max-width:1180px;box-sizing:border-box;margin:0 auto;padding:20px 16px 36px;font-family:"IBM Plex Sans",system-ui,sans-serif;color:#122842}
.products-hero{border:1px solid #d8e4f2;border-radius:18px;padding:22px;background:linear-gradient(145deg,#f8fbff,#edf4ff)}
.products-hero h1{margin:0 0 8px;font-size:34px;font-family:"Manrope",sans-serif}
.products-hero p{margin:0;color:#59718f;max-width:90ch;line-height:1.62}
.products-grid{margin-top:14px;display:grid;gap:14px}
.product-card{border:1px solid #d7e4f3;border-radius:16px;padding:18px;background:#fff}
.product-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap}
.product-head h2{margin:0;font-size:24px;font-family:"Manrope",sans-serif;color:#0d2340}
.product-tag{display:inline-flex;padding:5px 10px;border-radius:999px;border:1px solid #bfd5ee;color:#0d5db7;font-size:12px;font-weight:700;letter-spacing:.02em}
.product-summary{margin:10px 0;color:#2a476c;line-height:1.58}
.product-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.product-block{border:1px solid #e1ecf9;border-radius:12px;padding:12px;background:#fbfdff}
.product-block h3{margin:0 0 8px;font-size:15px;color:#14335b}
.product-block ul{margin:0;padding-left:18px}
.product-block li{margin:0 0 6px;color:#365274;line-height:1.5}
.product-block p{margin:0;color:#365274;line-height:1.55}
.product-nuance{margin-top:10px;border-radius:12px;padding:12px;background:linear-gradient(145deg,#f3f9ff,#eef5ff);border:1px solid #d8e4f2;color:#24486f;line-height:1.6}
.product-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:10px}
.product-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px;line-height:1}
.product-btn-open{border:1px solid #0d63d6;background:linear-gradient(135deg,#0d63d6,#0e9a91);color:#fff}
.product-btn-open:hover{filter:brightness(1.03)}
.product-btn-detail{border:1px solid #bfd5ee;background:#fff;color:#0d5db7}
.product-btn-detail:hover{background:#f1f7ff}
.product-deep{margin-top:12px;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#fff}
.product-deep h3{margin:0 0 8px;font-size:16px;font-family:"Manrope",sans-serif;color:#14355f}
.product-deep dl{margin:0;display:grid;gap:8px}
.product-deep dt{font-weight:700;color:#1a3e67}
.product-deep dd{margin:0;color:#365274;line-height:1.58}
.product-back{display:inline-flex;align-items:center;gap:6px;margin-top:12px;padding:6px 10px;border:1px solid #bfd5ee;border-radius:999px;color:#0d5db7;text-decoration:none;font-size:13px;font-weight:600}
.products-contact{margin-top:16px;border:1px solid #d8e4f2;border-radius:16px;padding:16px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.products-contact h2{margin:0 0 8px;font-size:24px;font-family:"Manrope",sans-serif;color:#14355f}
.products-contact p{margin:0 0 10px;color:#355477}
.products-contact .contact-alert{margin:0 0 10px;padding:10px;border:1px solid;border-radius:8px;font-size:14px}
.products-contact .contact-alert.ok{background:#eaf7ef;border-color:#b6dcc3;color:#234833}
.products-contact .contact-alert.error{background:#fff1f2;border-color:#efc4ca;color:#6f2632}
.products-contact form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.products-contact input,.products-contact select,.products-contact textarea{width:100%;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid #c8d7ea;background:#fbfdff;color:#163250;font:inherit}
.products-contact textarea{grid-column:1 / -1;min-height:120px;resize:vertical}
.products-contact button{grid-column:1 / -1;border:0;border-radius:10px;padding:11px 14px;font-weight:700;cursor:pointer;background:linear-gradient(135deg,#0d63d6,#0e9a91);color:#fff}
.products-seo-block{margin-top:16px;border:1px solid #d8e4f2;border-radius:16px;padding:16px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.products-seo-block h2{margin:0 0 10px;font-size:24px;font-family:"Manrope",sans-serif;color:#14355f}
.products-seo-block p{margin:0 0 10px;color:#355477;line-height:1.62}
.products-seo-list{margin:0 0 10px;padding-left:18px;display:grid;gap:6px}
.products-seo-list li{color:#365274;line-height:1.55}
.contact-hp{position:absolute!important;left:-9999px!important;opacity:0!important;pointer-events:none!important}
@media (max-width:980px){
  .product-columns{grid-template-columns:1fr}
  .products-contact form{grid-template-columns:1fr}
}

body.ui-tone-dark .products-page{color:#dbe7f6}
body.ui-tone-dark .products-hero{border-color:#27415d;background:linear-gradient(145deg,#0f1d2e,#12263d)}
body.ui-tone-dark .products-hero h1{color:#e7f0fb}
body.ui-tone-dark .products-hero p{color:#9cb3cf}
body.ui-tone-dark .product-card,
body.ui-tone-dark .products-contact,
body.ui-tone-dark .products-seo-block{border-color:#2b4662;background:#101f31}
body.ui-tone-dark .product-head h2,
body.ui-tone-dark .products-contact h2,
body.ui-tone-dark .products-seo-block h2{color:#e6effb}
body.ui-tone-dark .product-tag{border-color:#3a5f86;background:#17314b;color:#b7d0ee}
body.ui-tone-dark .product-summary,
body.ui-tone-dark .product-nuance,
body.ui-tone-dark .products-contact p,
body.ui-tone-dark .products-seo-block p,
body.ui-tone-dark .products-seo-list li{color:#9eb8d5}
body.ui-tone-dark .product-block,
body.ui-tone-dark .product-deep{border-color:#2f4b68;background:#13263b}
body.ui-tone-dark .product-block h3,
body.ui-tone-dark .product-deep h3,
body.ui-tone-dark .product-deep dt{color:#dbeafe}
body.ui-tone-dark .product-block li,
body.ui-tone-dark .product-block p,
body.ui-tone-dark .product-deep dd{color:#9eb8d5}
body.ui-tone-dark .product-nuance{border-color:#325270;background:linear-gradient(145deg,#132a40,#16314a)}
body.ui-tone-dark .product-btn-open{border-color:#2c6fde;background:linear-gradient(135deg,#2c6fde,#1b8f8f);color:#fff}
body.ui-tone-dark .product-btn-detail{border-color:#3a5f86;background:#132841;color:#bad4ef}
body.ui-tone-dark .product-btn-detail:hover{background:#1a3552}
body.ui-tone-dark .product-back{border-color:#3a5f86;background:#132841;color:#bad4ef}
body.ui-tone-dark .products-contact input,
body.ui-tone-dark .products-contact select,
body.ui-tone-dark .products-contact textarea{background:#0d1d2f;border-color:#395a7e;color:#dbe9f9}
body.ui-tone-dark .products-contact input::placeholder,
body.ui-tone-dark .products-contact textarea::placeholder{color:#7f9cbd}
body.ui-tone-dark .products-contact button{background:linear-gradient(135deg,#2c6fde,#1b8f8f)}
body.ui-tone-dark .products-contact .contact-alert.ok{background:#173828;border-color:#2a6045;color:#b8e8cb}
body.ui-tone-dark .products-contact .contact-alert.error{background:#3b1f25;border-color:#71414a;color:#f0c7cf}
</style>
<style id="cp-front-override">
.products-page{max-width:1240px;padding:24px 16px 46px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.products-hero,.product-card,.product-block,.product-nuance,.product-deep,.products-contact,.products-seo-block{border-color:var(--shell-border)!important;background:var(--shell-panel)!important;backdrop-filter:blur(14px);box-shadow:var(--shell-shadow)}
.products-hero{position:relative;overflow:hidden;border-radius:30px;padding:34px}
.products-hero:before{content:"";position:absolute;left:-60px;top:-60px;width:220px;height:220px;border-radius:999px;background:radial-gradient(circle,rgba(44,224,199,.24),rgba(44,224,199,0));pointer-events:none}
.products-hero h1,.product-head h2,.products-contact h2,.products-seo-block h2,.product-deep h3{font-family:"Space Grotesk","Sora",sans-serif;color:var(--shell-text)}
.products-hero h1{font-size:clamp(2.5rem,4vw,4.3rem);line-height:.94;max-width:12ch}
.products-hero p,.product-summary,.product-block li,.product-block p,.product-nuance,.product-deep dd,.products-contact p,.products-seo-block p,.products-seo-list li{color:var(--shell-muted)!important}
.product-card,.products-contact,.products-seo-block{border-radius:26px}
.product-tag,.product-back,.product-btn-detail{border-color:var(--shell-border)!important;background:rgba(255,255,255,.05)!important;color:var(--shell-accent)!important}
.product-btn-open,.products-contact button{background:linear-gradient(135deg,#7ab4ff,#2ce0c7)!important;color:#07111f!important;border-color:rgba(122,180,255,.34)!important}
.products-contact input,.products-contact select,.products-contact textarea{border-color:var(--shell-border)!important;background:rgba(4,8,18,.56)!important;color:var(--shell-text)}
</style>

<section class="products-page">
    <div class="products-hero">
        <h1><?= htmlspecialchars($t('Продукты', 'Products'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($t('Готовые B2B-продукты для роста выручки и контроля рисков: геоаналитика и antifraud, AI-SEO production и трекинг антифрод-инцидентов. Каждый продукт можно встроить в любой backend через API, webhooks или событийную шину.', 'Ready B2B products for revenue growth and risk control: geo + antifraud intelligence, AI-SEO production, and antifraud incident tracking. Each product can be integrated into any backend via API, webhooks, or event bus.'), ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($selected !== null): ?>
            <a class="product-back" href="/projects/"><?= htmlspecialchars($t('Назад к списку продуктов', 'Back to product list'), ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
    </div>

    <div class="products-grid">
        <?php foreach ($productRows as $product): ?>
            <?php
            $pricingList = $toList((string)$product['pricing_summary']);
            $integrationsList = $toList((string)$product['integrations_summary']);
            $code = trim((string)($product['symbolic_code'] !== '' ? $product['symbolic_code'] : $product['slug']));
            $deep = is_array($productDeep[$code] ?? null) ? $productDeep[$code] : null;
            $detailHref = $code !== '' ? ('/projects/' . rawurlencode($code) . '/') : '/projects/';
            ?>
            <article class="product-card" id="<?= htmlspecialchars($code !== '' ? $code : 'product', ENT_QUOTES, 'UTF-8') ?>">
                <div class="product-head">
                    <h2><?= htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <?php if (trim((string)$product['tag']) !== ''): ?><span class="product-tag"><?= htmlspecialchars((string)$product['tag'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                </div>

                <?php if (trim((string)$product['audience']) !== ''): ?>
                    <p class="product-summary"><strong><?= htmlspecialchars($t('Для кого:', 'Best fit:'), ENT_QUOTES, 'UTF-8') ?></strong> <?= htmlspecialchars((string)$product['audience'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if (trim((string)strip_tags((string)$product['summary_html'])) !== ''): ?>
                    <p class="product-summary"><?= (string)$product['summary_html'] ?></p>
                <?php endif; ?>

                <div class="product-columns">
                    <div class="product-block">
                        <h3><?= htmlspecialchars($t('Расценки и модель подключения', 'Pricing and engagement model'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($pricingList)): ?>
                            <ul><?php foreach ($pricingList as $row): ?><li><?= htmlspecialchars((string)$row, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                        <?php elseif (trim((string)strip_tags((string)$product['pricing_html'])) !== ''): ?>
                            <p><?= (string)$product['pricing_html'] ?></p>
                        <?php else: ?>
                            <p><?= htmlspecialchars($t('Тарифы обсуждаются под задачу и нагрузку.', 'Pricing is scoped per workload and requirements.'), ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="product-block">
                        <h3><?= htmlspecialchars($t('Интеграции в любой backend', 'Integrations with any backend'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($integrationsList)): ?>
                            <ul><?php foreach ($integrationsList as $row): ?><li><?= htmlspecialchars((string)$row, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                        <?php elseif (trim((string)strip_tags((string)$product['integrations_html'])) !== ''): ?>
                            <p><?= (string)$product['integrations_html'] ?></p>
                        <?php else: ?>
                            <p><?= htmlspecialchars($t('REST API, webhooks и событийная интеграция с вашим стеком.', 'REST API, webhooks and event-driven integration with your stack.'), ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (trim((string)$product['nuance']) !== ''): ?>
                    <div class="product-nuance"><?= htmlspecialchars((string)$product['nuance'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php elseif (trim((string)strip_tags((string)$product['value_html'])) !== ''): ?>
                    <div class="product-nuance"><?= (string)$product['value_html'] ?></div>
                <?php endif; ?>

                <div class="product-actions">
                <?php if (trim((string)$product['project_url']) !== ''): ?>
                    <a class="product-btn product-btn-open" href="<?= htmlspecialchars((string)$product['project_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener nofollow"><span aria-hidden="true">&#8599;</span><?= htmlspecialchars($t('Открыть продукт', 'Open product'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>

                <?php if ($selected === null && $code !== ''): ?>
                    <a class="product-btn product-btn-detail" href="<?= htmlspecialchars($detailHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Подробнее', 'Details'), ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
            </div>

            <?php if ($deep !== null): ?>
                <div class="product-deep">
                    <h3><?= htmlspecialchars($t('Кейсы применения, возможности и интеграция', 'Use cases, capabilities and integration'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <dl>
                        <dt><?= htmlspecialchars($t('Где применяется', 'Where it is used'), ENT_QUOTES, 'UTF-8') ?></dt>
                        <dd><?= htmlspecialchars((string)($deep['use_cases'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt><?= htmlspecialchars($t('Ключевые возможности', 'Core capabilities'), ENT_QUOTES, 'UTF-8') ?></dt>
                        <dd><?= htmlspecialchars((string)($deep['capabilities'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt><?= htmlspecialchars($t('Интеграция в клиентский проект', 'Integration into client project'), ENT_QUOTES, 'UTF-8') ?></dt>
                        <dd><?= htmlspecialchars((string)($deep['integration'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd>
                    </dl>
                </div>
            <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="products-contact" id="product-contact-form">
        <h2><?= htmlspecialchars($t('Заказать продукт или интеграцию', 'Order a product or integration'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t('Оставьте контакты и опишите задачу: подберу продукт, тариф, схему интеграции и план запуска под ваш стек.', 'Share your contacts and task: I will recommend the right product, pricing tier, integration scheme and launch plan for your stack.'), ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($contactMsg !== ''): ?>
            <div class="contact-alert <?= $contactType === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($contactMsg, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="public_contact_submit">
            <input type="hidden" name="return_path" value="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="contact_form_anchor" value="#product-contact-form">
            <input type="hidden" name="contact_interest" value="products">
            <input type="hidden" name="contact_csrf" value="<?= htmlspecialchars($contactToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="contact_started_at" value="<?= time() ?>">
            <input type="hidden" name="contact_campaign" value="products:catalog">
            <input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="contact-hp" aria-hidden="true">

            <input type="text" name="contact_name" placeholder="<?= htmlspecialchars($t('Имя', 'Name'), ENT_QUOTES, 'UTF-8') ?>" required>
            <input type="email" name="contact_email" placeholder="Email" required>
            <select name="contact_subject">
                <option value=""><?= htmlspecialchars($t('Интересующий продукт', 'Product of interest'), ENT_QUOTES, 'UTF-8') ?></option>
                <?php foreach ($productRows as $product): ?>
                    <option value="<?= htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="contact_campaign_hint" placeholder="<?= htmlspecialchars($t('Компания (опционально)', 'Company (optional)'), ENT_QUOTES, 'UTF-8') ?>">

            <textarea name="contact_message" placeholder="<?= htmlspecialchars($t('Опишите задачу: текущий backend, объём трафика, ограничения по срокам и бюджету, требования к SLA и безопасности.', 'Describe your task: current backend, traffic volume, timeline and budget constraints, SLA and security requirements.'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>

            <?php if ($turnstileSiteKey !== ''): ?>
                <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
            <?php endif; ?>

            <button type="submit"><?= htmlspecialchars($t('Отправить запрос', 'Send request'), ENT_QUOTES, 'UTF-8') ?></button>
        </form>
    </div>
    <article class="products-seo-block">
        <h2><?= htmlspecialchars($t('B2B-продукты под интеграцию в ваш backend и рост выручки', 'B2B products built for backend integration and revenue growth'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t('Мы внедряем geoip.space, postforge.ru и ANTIFRAUD TRACKER поэтапно: аудит текущей архитектуры, схема интеграции, безопасный rollout и контроль бизнес-метрик.', 'We deliver geoip.space, postforge.ru and ANTIFRAUD TRACKER in phases: architecture audit, integration design, safe rollout and business KPI control.'), ENT_QUOTES, 'UTF-8') ?></p>
        <ul class="products-seo-list">
            <li><?= htmlspecialchars($t('Интеграции в любой стек: PHP, Node.js, Python, Go, Java и микросервисные backend-контуры.', 'Works with any stack: PHP, Node.js, Python, Go, Java and microservice backends.'), ENT_QUOTES, 'UTF-8') ?></li>
            <li><?= htmlspecialchars($t('Прозрачные тарифы и масштабирование от MVP до enterprise-нагрузки без остановки текущих процессов.', 'Transparent pricing and scaling from MVP to enterprise workloads without disrupting current operations.'), ENT_QUOTES, 'UTF-8') ?></li>
            <li><?= htmlspecialchars($t('Фокус на результате: снижение fraud-losses, ускорение time-to-publish и сокращение MTTR по инцидентам.', 'Outcome-focused delivery: lower fraud losses, faster time-to-publish and shorter incident MTTR.'), ENT_QUOTES, 'UTF-8') ?></li>
        </ul>
        <p><?= htmlspecialchars($t('Оставьте запрос, и получите рабочий план внедрения: этапы, сроки, требования к интеграции и прогнозируемый экономический эффект.', 'Send a request to get a practical rollout plan: stages, timeline, integration requirements and projected business impact.'), ENT_QUOTES, 'UTF-8') ?></p>
    </article>
</section>

