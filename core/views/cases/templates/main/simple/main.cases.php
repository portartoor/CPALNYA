<?php
$catalog = (array)($ModelPage['cases_catalog'] ?? []);
$items = (array)($catalog['items'] ?? []);
$selected = is_array($catalog['selected'] ?? null) ? $catalog['selected'] : null;
$lang = strtolower((string)($catalog['lang'] ?? 'en'));
$isRu = $lang === 'ru';
$title = $isRu ? 'Кейсы' : 'Case Studies';
$lead = $isRu
    ? 'Практические кейсы по B2B tech: от постановки проблемы и архитектуры до внедрения, интеграций и измеримого результата.'
    : 'Practical B2B tech case studies: from problem framing and architecture to delivery, integrations and measurable business impact.';
$emptyText = $isRu ? 'Пока нет опубликованных кейсов.' : 'No published case studies yet.';
$detailsLabel = $isRu ? 'Подробнее' : 'Details';
$backToListLabel = $isRu ? 'Назад к кейсам' : 'Back to case studies';
$contactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$contactFlash = function_exists('public_contact_form_flash') ? public_contact_form_flash() : [];
$contactType = (string)($contactFlash['type'] ?? '');
$contactMsg = (string)($contactFlash['message'] ?? '');
$returnPath = (string)($_SERVER['REQUEST_URI'] ?? '/');
$turnstileSiteKey = trim((string)($GLOBALS['ContactTurnstileSiteKey'] ?? ''));
$publicLayoutFooterMaxWidth = '1180px';
$buildDetailLink = static function (array $item): string {
    $code = trim((string)($item['symbolic_code'] ?? ''));
    if ($code === '') {
        $code = trim((string)($item['slug'] ?? ''));
    }
    return $code === '' ? '/cases/' : ('/cases/' . rawurlencode($code) . '/');
};

$stripHtml = static function (string $value): string {
    $value = trim((string)preg_replace('/\s+/u', ' ', strip_tags($value)));
    return $value;
};
$containsAny = static function (string $haystack, array $needles): bool {
    $lower = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);
    foreach ($needles as $needle) {
        $n = (string)$needle;
        if ($n === '') {
            continue;
        }
        $n = function_exists('mb_strtolower') ? mb_strtolower($n, 'UTF-8') : strtolower($n);
        if (strpos($lower, $n) !== false) {
            return true;
        }
    }
    return false;
};
$buildCaseEnhancement = static function (array $case, bool $isRu) use ($stripHtml, $containsAny): array {
    $title = (string)($case['title'] ?? '');
    $stack = (string)($case['stack_summary'] ?? '');
    $industry = (string)($case['industry_summary'] ?? '');
    $problem = $stripHtml((string)($case['problem_summary'] ?? ''));
    $result = $stripHtml((string)($case['result_summary'] ?? ''));
    $challenge = $stripHtml((string)($case['challenge_html'] ?? ''));
    $solution = $stripHtml((string)($case['solution_html'] ?? ''));
    $arch = $stripHtml((string)($case['architecture_html'] ?? ''));
    $metrics = $stripHtml((string)($case['metrics_html'] ?? ''));

    $context = trim($title . ' ' . $stack . ' ' . $industry . ' ' . $challenge . ' ' . $solution . ' ' . $arch);
    $isBitrix = $containsAny($context, ['битрикс', 'bitrix', 'bitrix24', 'б24', '1с']);
    $isSeo = $containsAny($context, ['seo', 'контент', 'organic', 'индексац']);
    $isGeo = $containsAny($context, ['geoip', 'vpn', 'proxy', 'tor', 'fraud', 'антифрод']);
    $isBot = $containsAny($context, ['telegram', 'бот', 'bot']);
    $isMvp = $containsAny($context, ['mvp', 'pilot', 'пилот']);
    $isAi = $containsAny($context, ['ai', 'ии', 'llm', 'assistant', 'ассистент']);

    $fingerprint = [];
    if ($isBitrix) { $fingerprint[] = $isRu ? 'компонентная доменная модель на 1С-Битрикс' : 'component-driven Bitrix domain model'; }
    if ($isSeo) { $fingerprint[] = $isRu ? 'SEO-структура с коммерческими интентами и quality-gates' : 'SEO structure mapped to commercial intent with quality gates'; }
    if ($isGeo) { $fingerprint[] = $isRu ? 'risk-aware фильтрация трафика и объяснимые правила принятия решений' : 'risk-aware traffic filtering with explainable decision rules'; }
    if ($isBot) { $fingerprint[] = $isRu ? 'бот-оркестрация входящих сценариев и SLA-маршрутизация' : 'bot orchestration for inbound scenarios with SLA routing'; }
    if ($isMvp) { $fingerprint[] = $isRu ? 'поэтапный MVP-rollout с контролем scope и рисков' : 'phased MVP rollout with scope and risk control'; }
    if ($isAi) { $fingerprint[] = $isRu ? 'AI-контур с безопасным внедрением и валидацией качества' : 'AI workflow with safe rollout and quality validation'; }
    if (empty($fingerprint)) { $fingerprint[] = $isRu ? 'системный подход к внедрению с фокусом на измеримый бизнес-эффект' : 'systems-first implementation focused on measurable business impact'; }

    $solutionText = $isRu
        ? 'В этом кейсе ключевым отличием стала ' . implode(', ', $fingerprint) . '. Я не ограничивался точечной доработкой: сначала зафиксировал архитектурные ограничения, затем собрал рабочий контур внедрения и довел его до состояния, где команда может масштабировать решение без потери управляемости.'
        : 'In this case, the differentiator was ' . implode(', ', $fingerprint) . '. The delivery was not a one-off patch: architecture constraints were fixed first, then a production workflow was rolled out so the team can scale without losing control.';

    $howto = $isRu
        ? [
            'Сформулировать бизнес-цель и метрику успеха до начала работ.',
            'Разложить текущий сценарий на точки потерь: данные, время, качество.',
            'Выделить минимальный контур внедрения и критерии приемки.',
            'Запустить поэтапный rollout с наблюдаемостью и логированием.',
            'Закрепить регламент сопровождения, эскалаций и улучшений.',
        ]
        : [
            'Define business objective and success metric before implementation.',
            'Map current flow and identify losses in data, time and quality.',
            'Scope minimum viable rollout with explicit acceptance criteria.',
            'Launch phased rollout with observability and trace logging.',
            'Lock support, escalation and iteration workflow.',
        ];

    $checklist = $isRu
        ? [
            'Фиксация baseline-метрик до внедрения.',
            'Проверка интеграционных точек и контрактов данных.',
            'Тестирование отказоустойчивости и fallback-сценариев.',
            'Контроль качества контента/данных после запуска.',
            'Подготовка runbook для команды эксплуатации.',
            'План последующих итераций на 30/60 дней.',
        ]
        : [
            'Baseline metrics captured before rollout.',
            'Integration points and data contracts verified.',
            'Failure modes and fallback scenarios tested.',
            'Post-launch quality controls enabled.',
            'Operational runbook prepared for the team.',
            '30/60-day optimization plan documented.',
        ];

    $comparison = $isRu
        ? [
            ['aspect' => 'Подход к внедрению', 'before' => 'Локальные правки без единой модели', 'after' => 'Системный rollout с архитектурной логикой'],
            ['aspect' => 'Управляемость решения', 'before' => 'Зависимость от ручных действий и контекста', 'after' => 'Прозрачные правила, чеклисты и контроль качества'],
            ['aspect' => 'Бизнес-эффект', 'before' => ($problem !== '' ? $problem : 'Проблема не имела устойчивого решения'), 'after' => ($result !== '' ? $result : 'Появился измеримый и масштабируемый результат')],
        ]
        : [
            ['aspect' => 'Delivery model', 'before' => 'Local fixes without unified architecture', 'after' => 'Systems-first rollout with clear architecture logic'],
            ['aspect' => 'Operational control', 'before' => 'Manual and context-dependent execution', 'after' => 'Transparent rules, checklists and quality control'],
            ['aspect' => 'Business impact', 'before' => ($problem !== '' ? $problem : 'No stable resolution in place'), 'after' => ($result !== '' ? $result : 'Measurable and scalable outcome achieved')],
        ];

    $related = [];
    $related[] = ['label' => $isRu ? 'Каталог услуг' : 'Services catalog', 'url' => '/services/', 'external' => false];
    $related[] = ['label' => $isRu ? 'Каталог офферов' : 'Offers catalog', 'url' => '/offers/', 'external' => false];
    $related[] = ['label' => $isRu ? 'Продукты и решения' : 'Products', 'url' => '/projects/', 'external' => false];
    if ($isBitrix) { $related[] = ['label' => $isRu ? 'Оффер: Разработка сайта на 1С-Битрикс' : 'Offer: Website development on 1C-Bitrix', 'url' => '/offers/website-development-bitrix/', 'external' => false]; }
    if ($isGeo) {
        $related[] = ['label' => $isRu ? 'Оффер: Определение VPN/Proxy-подключений для сайта' : 'Offer: VPN/Proxy detection for websites', 'url' => '/offers/vpn-traffic-site-detection/', 'external' => false];
        $related[] = ['label' => 'geoip.space', 'url' => 'https://geoip.space', 'external' => true];
    }
    if ($isSeo) { $related[] = ['label' => $isRu ? 'Оффер: SEO контент для сайта' : 'Offer: SEO content for website', 'url' => '/offers/postforge-seo-pipeline/', 'external' => false]; }
    if ($isBot) { $related[] = ['label' => $isRu ? 'Оффер: Telegram бот для продаж' : 'Offer: Telegram bot for sales', 'url' => '/offers/telegram-bot-for-sales/', 'external' => false]; }
    if ($isAi) { $related[] = ['label' => $isRu ? 'Оффер: AI ассистент для бизнес-процессов' : 'Offer: AI assistant for business', 'url' => '/offers/ai-assistant-for-business/', 'external' => false]; }
    if ($isMvp) { $related[] = ['label' => $isRu ? 'Оффер: Разработка MVP для стартапа' : 'Offer: MVP development for startup', 'url' => '/offers/mvp-development-for-startup/', 'external' => false]; }

    $howtoTitle = $isRu ? 'How-to: как повторить результат в вашем проекте' : 'How-to: how to replicate this result in your project';
    $solutionTitle = $isRu ? 'Уникальное решение в этом кейсе' : 'Unique solution in this case';
    $compareTitle = $isRu ? 'Сравнение: до и после системного внедрения' : 'Comparison: before vs after systems rollout';
    $checkTitle = $isRu ? 'Практический чеклист внедрения' : 'Practical implementation checklist';
    $relatedTitle = $isRu ? 'Связанные услуги, офферы и продукты' : 'Related services, offers and products';

    return [
        'solution_title' => $solutionTitle,
        'solution_text' => $solutionText,
        'howto_title' => $howtoTitle,
        'howto_steps' => $howto,
        'checklist_title' => $checkTitle,
        'checklist' => $checklist,
        'compare_title' => $compareTitle,
        'comparison_rows' => $comparison,
        'related_title' => $relatedTitle,
        'related_links' => $related,
        'metrics_hint' => $metrics,
    ];
};
?>
<style>
.cases-simple{max-width:1180px;box-sizing:border-box;margin:0 auto;padding:20px 16px 36px;font-family:"IBM Plex Sans",system-ui,sans-serif;color:#10233f}
.cases-simple-hero{border:1px solid #d8e4f2;border-radius:18px;padding:22px;background:linear-gradient(145deg,#f8fbff,#edf4ff)}
.cases-simple-hero h1{margin:0 0 8px;font-size:34px;font-family:"Manrope",sans-serif}
.cases-simple-hero p{margin:0;color:#59718f;max-width:86ch;line-height:1.62}
.cases-simple-grid{margin-top:14px;display:grid;gap:14px}
.cases-simple-card,.cases-simple-detail{border:1px solid #d7e4f3;border-radius:16px;padding:18px;background:#fff}
.cases-simple-top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:8px}
.cases-simple-top h3,.cases-simple-detail h2{margin:0;font-family:"Manrope",sans-serif;color:#0d2340}
.cases-simple-top h3{font-size:24px}
.cases-simple-detail h2{font-size:30px;margin:16px 0 10px}
.cases-simple-actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.cases-simple-link,.cases-simple-back{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #bfd5ee;border-radius:999px;color:#0d5db7;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap}
.cases-simple-link:hover,.cases-simple-back:hover{background:#f1f7ff}
.cases-simple-summary,.cases-simple-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:10px 0 12px}
.cases-simple-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.cases-simple-summary div,.cases-simple-detail-grid article,.cases-simple-detail-copy article{border-radius:12px;padding:10px 12px;background:#fbfdff;border:1px solid #e1ecf9}
.cases-simple-summary b,.cases-simple-detail-grid h4,.cases-simple-detail-copy h3{display:block;margin:0 0 6px;color:#14335b}
.cases-simple-summary b{font-size:12px;opacity:.75;margin-bottom:4px}
.cases-simple-summary span,.cases-simple-detail-grid p,.cases-simple-detail-copy div,.cases-simple-excerpt,.cases-simple-sections p{color:#365274;line-height:1.58}
.cases-simple-sections{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.cases-simple-sections article{border:1px solid #e1ecf9;border-radius:12px;padding:10px 12px;background:#fbfdff}
.cases-simple-sections h4{margin:0 0 6px;font-size:14px;color:#1f3f69}
.cases-simple-detail-copy{display:grid;gap:10px}
.cases-deep-block{margin-top:12px;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#f9fcff}
.cases-deep-block h3{margin:0 0 8px;font-size:19px;color:#173a62}
.cases-deep-block p{margin:0;color:#3d5f85;line-height:1.62}
.cases-deep-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #d3deec}
.cases-deep-table th,.cases-deep-table td{border:1px solid #d3deec;padding:8px 10px;text-align:left;vertical-align:top}
.cases-deep-table th{background:#f3f8ff;color:#14355f;font-size:13px}
.cases-howto,.cases-checklist{margin:0;padding-left:20px;display:grid;gap:6px;color:#3f638b;line-height:1.58}
.cases-related{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.cases-related a{display:block;text-decoration:none;border:1px solid #d5e2f1;border-radius:12px;background:#fff;padding:10px;color:#1d446f;font-weight:600;line-height:1.4}
.cases-related a:hover{border-color:#0d63d6;color:#0d63d6}
.cases-empty{margin-top:12px;border:1px dashed #c8d8ea;border-radius:14px;padding:18px;color:#607c9d}
.cases-inline-contact{margin-top:14px;border:1px solid #d8e4f2;border-radius:14px;padding:14px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.cases-inline-contact h3{margin:0 0 8px;font-size:18px;color:#14355f}
.cases-inline-contact p{margin:0 0 10px;color:#355477}
.cases-inline-contact .contact-alert{margin:0 0 10px;padding:10px;border:1px solid;border-radius:8px;font-size:14px}
.cases-inline-contact .contact-alert.ok{background:#eaf7ef;border-color:#b6dcc3;color:#234833}
.cases-inline-contact .contact-alert.error{background:#fff1f2;border-color:#efc4ca;color:#6f2632}
.cases-inline-contact form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.cases-inline-contact input,.cases-inline-contact textarea{width:100%;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid #c8d7ea;background:#fbfdff;color:#163250;font:inherit}
.cases-inline-contact textarea{grid-column:1 / -1;min-height:120px;resize:vertical}
.cases-inline-contact button{grid-column:1 / -1;border:0;border-radius:10px;padding:11px 14px;font-weight:700;cursor:pointer;background:linear-gradient(135deg,#0d63d6,#0e9a91);color:#fff}
.cases-seo-block{margin-top:16px;border:1px solid #d8e4f2;border-radius:16px;padding:16px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.cases-seo-block h2{margin:0 0 10px;font-size:24px;font-family:"Manrope",sans-serif;color:#14355f}
.cases-seo-block p{margin:0 0 10px;color:#355477;line-height:1.62}
.cases-seo-list{margin:0 0 10px;padding-left:18px;display:grid;gap:6px}
.cases-seo-list li{color:#365274;line-height:1.55}
.contact-hp{position:absolute!important;left:-9999px!important;opacity:0!important;pointer-events:none!important}
@media (max-width:980px){
  .cases-simple-summary,.cases-simple-sections,.cases-simple-detail-grid{grid-template-columns:1fr}
  .cases-inline-contact form{grid-template-columns:1fr}
  .cases-related{grid-template-columns:1fr}
}
</style>
<style id="cp-front-override">
.cases-simple{max-width:1240px;padding:24px 16px 46px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.cases-simple-hero,.cases-simple-card,.cases-simple-detail,.cases-deep-block,.cases-inline-contact,.cases-seo-block,.cases-related a,.cases-empty{border-color:var(--shell-border)!important;background:var(--shell-panel)!important;backdrop-filter:blur(14px);box-shadow:var(--shell-shadow)}
.cases-simple-hero{position:relative;overflow:hidden;border-radius:30px;padding:34px}
.cases-simple-hero:after{content:"";position:absolute;right:-50px;bottom:-70px;width:240px;height:240px;border-radius:999px;background:radial-gradient(circle,rgba(122,180,255,.24),rgba(122,180,255,0));pointer-events:none}
.cases-simple-hero h1,.cases-simple-top h3,.cases-simple-detail h2,.cases-deep-block h3,.cases-seo-block h2{font-family:"Space Grotesk","Sora",sans-serif;color:var(--shell-text)}
.cases-simple-hero h1{font-size:clamp(2.5rem,4vw,4.2rem);line-height:.94;max-width:10ch}
.cases-simple-hero p,.cases-simple-summary span,.cases-simple-detail-grid p,.cases-simple-detail-copy div,.cases-simple-excerpt,.cases-simple-sections p,.cases-deep-block p,.cases-howto,.cases-checklist,.cases-seo-block p,.cases-seo-list li{color:var(--shell-muted)!important}
.cases-simple-card,.cases-simple-detail,.cases-inline-contact,.cases-seo-block{border-radius:26px}
.cases-simple-link,.cases-simple-back{border-color:var(--shell-border)!important;background:rgba(255,255,255,.05)!important;color:var(--shell-accent)!important}
.cases-simple-summary div,.cases-simple-detail-grid article,.cases-simple-detail-copy article,.cases-simple-sections article,.cases-deep-table,.cases-deep-table th,.cases-deep-table td{border-color:var(--shell-border)!important;background:rgba(255,255,255,.03)!important;color:inherit}
.cases-inline-contact input,.cases-inline-contact textarea{border-color:var(--shell-border)!important;background:rgba(4,8,18,.56)!important;color:var(--shell-text)}
.cases-inline-contact button{background:linear-gradient(135deg,#7ab4ff,#2ce0c7)!important;color:#07111f}
</style>
<section class="cases-simple">
    <div class="cases-simple-hero">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if ($selected !== null): ?>
        <article class="cases-simple-detail">
            <a class="cases-simple-back" href="/cases/"><?= htmlspecialchars($backToListLabel, ENT_QUOTES, 'UTF-8') ?></a>
            <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>

            <div class="cases-simple-actions">
                <?php if (trim((string)($selected['client_name'] ?? '')) !== ''): ?>
                    <span class="cases-simple-link"><?= htmlspecialchars((string)($selected['client_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if (trim((string)($selected['symbolic_code'] ?? '')) !== ''): ?>
                    <span class="cases-simple-link">#<?= htmlspecialchars((string)($selected['symbolic_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="cases-simple-detail-grid">
                <article><h4><?= $isRu ? 'Сфера' : 'Industry' ?></h4><p><?= htmlspecialchars((string)($selected['industry_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></article>
                <article><h4><?= $isRu ? 'Период' : 'Period' ?></h4><p><?= htmlspecialchars((string)($selected['period_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></article>
                <article><h4><?= $isRu ? 'Роль' : 'Role' ?></h4><p><?= htmlspecialchars((string)($selected['role_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></article>
                <article><h4><?= $isRu ? 'Технологии' : 'Tech stack' ?></h4><p><?= htmlspecialchars((string)($selected['stack_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p></article>
            </div>

            <div class="cases-simple-detail-copy">
                <article><h3><?= $isRu ? 'Проблема' : 'Problem' ?></h3><div><?= (string)($selected['challenge_html'] ?? '') ?></div></article>
                <article><h3><?= $isRu ? 'Подход и решение' : 'Approach and solution' ?></h3><div><?= (string)($selected['solution_html'] ?? '') ?></div></article>
                <article><h3><?= $isRu ? 'Архитектура' : 'Architecture' ?></h3><div><?= (string)($selected['architecture_html'] ?? '') ?></div></article>
                <article><h3><?= $isRu ? 'Результат' : 'Outcome' ?></h3><div><?= (string)($selected['results_html'] ?? '') ?></div></article>
                <article><h3><?= $isRu ? 'Метрики' : 'Metrics' ?></h3><div><?= (string)($selected['metrics_html'] ?? '') ?></div></article>
                <article><h3><?= $isRu ? 'Что сделали' : 'Deliverables' ?></h3><div><?= (string)($selected['deliverables_html'] ?? '') ?></div></article>
            </div>

            <?php $deep = $buildCaseEnhancement((array)$selected, $isRu); ?>
            <article class="cases-deep-block">
                <h3><?= htmlspecialchars((string)($deep['solution_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars((string)($deep['solution_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            </article>

            <?php $compareRows = (array)($deep['comparison_rows'] ?? []); ?>
            <?php if (!empty($compareRows)): ?>
                <article class="cases-deep-block">
                    <h3><?= htmlspecialchars((string)($deep['compare_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <table class="cases-deep-table">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars($isRu ? '������' : 'Aspect', ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($isRu ? '��' : 'Before', ENT_QUOTES, 'UTF-8') ?></th>
                                <th><?= htmlspecialchars($isRu ? '�����' : 'After', ENT_QUOTES, 'UTF-8') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compareRows as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row['aspect'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($row['before'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($row['after'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>
            <?php endif; ?>

            <?php $howtoSteps = (array)($deep['howto_steps'] ?? []); ?>
            <?php if (!empty($howtoSteps)): ?>
                <article class="cases-deep-block">
                    <h3><?= htmlspecialchars((string)($deep['howto_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ol class="cases-howto">
                        <?php foreach ($howtoSteps as $step): ?>
                            <li><?= htmlspecialchars((string)$step, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ol>
                </article>
            <?php endif; ?>

            <?php $checkRows = (array)($deep['checklist'] ?? []); ?>
            <?php if (!empty($checkRows)): ?>
                <article class="cases-deep-block">
                    <h3><?= htmlspecialchars((string)($deep['checklist_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul class="cases-checklist">
                        <?php foreach ($checkRows as $checkItem): ?>
                            <li><?= htmlspecialchars((string)$checkItem, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endif; ?>

            <?php $relatedLinks = (array)($deep['related_links'] ?? []); ?>
            <?php if (!empty($relatedLinks)): ?>
                <article class="cases-deep-block">
                    <h3><?= htmlspecialchars((string)($deep['related_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="cases-related">
                        <?php foreach ($relatedLinks as $rel): ?>
                            <?php
                            $relUrl = trim((string)($rel['url'] ?? ''));
                            $relLabel = trim((string)($rel['label'] ?? ''));
                            $isExternal = !empty($rel['external']);
                            if ($relUrl === '' || $relLabel === '') { continue; }
                            ?>
                            <a href="<?= htmlspecialchars($relUrl, ENT_QUOTES, 'UTF-8') ?>"<?= $isExternal ? ' target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($relLabel, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endif; ?>
            <div class="cases-inline-contact" id="case-contact-form">
                <h3><?= $isRu ? 'Нужен похожий кейс?' : 'Need a similar case delivered?' ?></h3>
                <p><?= $isRu ? 'Опишите задачу, и я предложу архитектуру, этапы и формат реализации.' : 'Describe your task and I will suggest architecture, scope and delivery format.' ?></p>
                <?php if ($contactMsg !== ''): ?>
                    <div class="contact-alert <?= $contactType === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($contactMsg, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="public_contact_submit">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="contact_form_anchor" value="#case-contact-form">
                    <input type="hidden" name="contact_interest" value="cases">
                    <input type="hidden" name="contact_csrf" value="<?= htmlspecialchars($contactToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="contact_started_at" value="<?= time() ?>">
                    <input type="hidden" name="contact_campaign" value="<?= htmlspecialchars('case:' . (string)($selected['symbolic_code'] ?? $selected['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="contact-hp" aria-hidden="true">
                    <input type="text" name="contact_name" placeholder="<?= htmlspecialchars($isRu ? 'Имя' : 'Name', ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="email" name="contact_email" placeholder="Email" required>
                    <textarea name="contact_message" placeholder="<?= htmlspecialchars($isRu ? 'Нужен похожий проект. Цели, ограничения, сроки, бюджет…' : 'We need a similar project. Goals, constraints, timeline, budget…', ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                    <?php if ($turnstileSiteKey !== ''): ?>
                        <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
                    <?php endif; ?>
                    <button type="submit"><?= htmlspecialchars($isRu ? 'Обсудить проект' : 'Discuss project', ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            </div>
        </article>
    <?php endif; ?>

    <?php if ($selected === null && empty($items)): ?>
        <div class="cases-empty"><?= htmlspecialchars($emptyText, ENT_QUOTES, 'UTF-8') ?></div>
    <?php elseif ($selected === null): ?>
        <div class="cases-simple-grid">
            <?php foreach ($items as $item): ?>
                <article class="cases-simple-card">
                    <div class="cases-simple-top">
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="cases-simple-actions">
                            <a class="cases-simple-link" href="<?= htmlspecialchars($buildDetailLink($item), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($detailsLabel, ENT_QUOTES, 'UTF-8') ?></a>
                        </div>
                    </div>

                    <div class="cases-simple-summary">
                        <div><b><?= $isRu ? 'Проблема' : 'Problem' ?></b><span><?= htmlspecialchars((string)($item['problem_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= $isRu ? 'Сфера' : 'Industry' ?></b><span><?= htmlspecialchars((string)($item['industry_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= $isRu ? 'Результат' : 'Outcome' ?></b><span><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                    </div>

                    <p class="cases-simple-excerpt"><?= (string)($item['excerpt_html'] ?? '') ?></p>

                    <div class="cases-simple-sections">
                        <article><h4><?= $isRu ? 'Контекст' : 'Context' ?></h4><p><?= (string)($item['challenge_html'] ?? '') ?></p></article>
                        <article><h4><?= $isRu ? 'Решение' : 'Solution' ?></h4><p><?= (string)($item['solution_html'] ?? '') ?></p></article>
                        <article><h4><?= $isRu ? 'Бизнес-эффект' : 'Business impact' ?></h4><p><?= (string)($item['results_html'] ?? '') ?></p></article>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <article class="cases-seo-block">
        <h2><?= htmlspecialchars($isRu ? 'Кейсы с измеримым бизнес-эффектом и рабочей архитектурой внедрения' : 'Case studies with measurable business impact and practical delivery architecture', ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($isRu ? 'Каждый кейс показывает не только результат, но и путь к нему: исходная проблема, системный подход, стек, этапы внедрения и контроль метрик после запуска.' : 'Each case shows not just the outcome, but the path to it: initial problem, systems approach, stack, delivery stages and post-launch metric control.', ENT_QUOTES, 'UTF-8') ?></p>
        <ul class="cases-seo-list">
            <li><?= htmlspecialchars($isRu ? 'Подробно раскрываем архитектурные решения, чтобы их можно было применить в вашем проекте.' : 'We break down architecture decisions so they can be reused in your own delivery context.', ENT_QUOTES, 'UTF-8') ?></li>
            <li><?= htmlspecialchars($isRu ? 'Фокус на практических KPI: скорость внедрения, снижение рисков, рост качества лидов и устойчивость системы.' : 'Focus on practical KPIs: delivery speed, risk reduction, lead quality growth and system stability.', ENT_QUOTES, 'UTF-8') ?></li>
            <li><?= htmlspecialchars($isRu ? 'Показываем интеграции в реальных backend-сценариях без «маркетинговой воды».' : 'Demonstrates real backend integration scenarios without marketing fluff.', ENT_QUOTES, 'UTF-8') ?></li>
        </ul>
        <p><?= htmlspecialchars($isRu ? 'Если нужен похожий результат, оставьте заявку: подготовим план реализации под ваш стек, ограничения и целевые метрики бизнеса.' : 'If you need a similar result, send a request and we will prepare an implementation plan for your stack, constraints and business KPIs.', ENT_QUOTES, 'UTF-8') ?></p>
    </article>
</section>


