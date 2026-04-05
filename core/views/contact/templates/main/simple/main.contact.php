<?php
$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$isRu = (bool)preg_match('/\.ru$/', $host);
$forceDark = !empty($forceDarkContactWizard);
$title = $isRu ? 'Связь' : 'Contact';
$lead = $isRu
    ? 'Если у вас есть идея, задача или запрос на развитие проекта, давайте обсудим это в деталях.'
    : 'If you have an idea, challenge or growth goal, let us discuss it in a practical and detailed way.';
$wizardCatalog = (array)($ModelPage['contact_wizard'] ?? []);
$wizardGroups = (array)($wizardCatalog['items_by_group'] ?? []);
$contactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$returnPath = '/contact/';
$wizardNeeds = $isRu ? [
    ['code' => 'develop', 'label' => 'Разработка под ключ', 'desc' => 'От идеи до продакшена и поддержки.'],
    ['code' => 'mvp', 'label' => 'Запуск MVP', 'desc' => 'Быстрый вывод первой рабочей версии.'],
    ['code' => 'refactor', 'label' => 'Рефакторинг готового проекта', 'desc' => 'Стабильность, скорость, уменьшение техдолга.'],
    ['code' => 'audit', 'label' => 'Аудит системы', 'desc' => 'Код, архитектура, безопасность, performance.'],
    ['code' => 'infra', 'label' => 'DevOps / инфраструктура', 'desc' => 'CI/CD, контейнеры, наблюдаемость, отказоустойчивость.'],
    ['code' => 'integrations', 'label' => 'Интеграции API/CRM/ERP', 'desc' => 'Склейка систем через API/webhooks/queues.'],
    ['code' => 'docs', 'label' => 'Техническая документация', 'desc' => 'Архитектура, API-контракты, runbook.'],
    ['code' => 'seo', 'label' => 'SEO продвижение', 'desc' => 'Трафик, лиды, контент и индексация.'],
    ['code' => 'analytics', 'label' => 'Сквозная аналитика', 'desc' => 'События, BI, воронки и атрибуция.'],
    ['code' => 'antifraud', 'label' => 'Antifraud / риск-контур', 'desc' => 'Сигналы, правила, трекинг инцидентов.'],
    ['code' => 'support', 'label' => 'Техподдержка и развитие', 'desc' => 'Пострелизное сопровождение и roadmap.'],
    ['code' => 'consulting', 'label' => 'Консалтинг', 'desc' => 'Стратегия, приоритизация и формат реализации.'],
] : [
    ['code' => 'develop', 'label' => 'Turnkey development', 'desc' => 'From idea to production and support.'],
    ['code' => 'mvp', 'label' => 'MVP launch', 'desc' => 'Fast delivery of first workable version.'],
    ['code' => 'refactor', 'label' => 'Refactor existing project', 'desc' => 'Stability, speed, tech debt reduction.'],
    ['code' => 'audit', 'label' => 'System audit', 'desc' => 'Code, architecture, security, performance.'],
    ['code' => 'infra', 'label' => 'DevOps / infrastructure', 'desc' => 'CI/CD, containers, observability, resilience.'],
    ['code' => 'integrations', 'label' => 'API/CRM/ERP integrations', 'desc' => 'Connect systems via API/webhooks/queues.'],
    ['code' => 'docs', 'label' => 'Technical documentation', 'desc' => 'Architecture docs, API contracts, runbooks.'],
    ['code' => 'seo', 'label' => 'SEO growth', 'desc' => 'Traffic, leads, content and indexing.'],
    ['code' => 'analytics', 'label' => 'End-to-end analytics', 'desc' => 'Events, BI, funnels, attribution.'],
    ['code' => 'antifraud', 'label' => 'Antifraud / risk layer', 'desc' => 'Signals, rules, incident tracking.'],
    ['code' => 'support', 'label' => 'Support and growth', 'desc' => 'Post-release support and roadmap.'],
    ['code' => 'consulting', 'label' => 'Consulting', 'desc' => 'Strategy, prioritization, delivery format.'],
];
$groupPayload = [];
foreach ($wizardGroups as $code => $groupBlock) {
    $meta = (array)($groupBlock['group'] ?? []);
    $items = (array)($groupBlock['items'] ?? []);
    $label = trim((string)($meta['label'] ?? $code));
    if ($label === '') {
        continue;
    }
    $titles = [];
    foreach ($items as $it) {
        $name = trim((string)($it['title'] ?? ''));
        if ($name !== '') {
            $titles[] = $name;
        }
    }
    $groupPayload[] = ['code' => (string)$code, 'label' => $label, 'items' => $titles];
}
$stepTitles = $isRu
    ? ['Направление', 'Детали', 'Услуги и файлы', 'Контакты']
    : ['Direction', 'Details', 'Services & files', 'Contacts'];
?>
<style>
.contact-simple{max-width:1180px;box-sizing:border-box;margin:0 auto;padding:18px 16px 30px;font-family:"IBM Plex Sans",system-ui,sans-serif;color:#0f203a}
.contact-simple-hero{border:1px solid #d6e0ed;border-radius:18px;padding:20px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.contact-simple-hero h1{margin:0 0 8px;font-size:34px;font-family:"Manrope",sans-serif}
.contact-simple-copy{margin-top:14px;border:1px solid #d6e0ed;border-radius:12px;padding:14px;background:#fff}
.contact-simple-copy p{margin:0 0 10px;color:#3f5f84;line-height:1.6}.contact-simple-copy p:last-child{margin-bottom:0}
.cw{margin-top:16px;border:1px solid #d6e0ed;border-radius:16px;padding:16px;background:linear-gradient(145deg,#f8fbff,#eef5ff)}
.cw h2{margin:0 0 8px;font-size:28px;font-family:"Manrope",sans-serif;color:#14355f}.cw p{margin:0 0 10px;color:#355477;line-height:1.62}
.cw-steps{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.cw-steps span{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid #bfd5ee;background:#fff;color:#2f517b;font-weight:700;font-size:12px;line-height:1;padding:9px 12px;white-space:nowrap}
.cw-steps span.a{background:linear-gradient(135deg,#0d63d6,#0e9a91);border-color:#0d63d6;color:#fff}
.cw-panel{display:none;border:1px solid #dbe6f4;border-radius:12px;padding:12px;background:#fff}.cw-panel.a{display:block}
.cw-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.cw-field label{display:block;margin:0 0 6px;font-weight:700;color:#1a3e67}
.cw-field input,.cw-field select,.cw-field textarea{width:100%;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid #c8d7ea;background:#fbfdff;color:#163250;font:inherit}
.cw-field textarea{min-height:100px;resize:vertical}
.cw-needs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.cw-needs label{display:block;border:1px solid #cddded;border-radius:12px;padding:10px;background:#fff}
.cw-needs strong{display:block;color:#17385f}.cw-needs span{display:block;color:#4a6789;font-size:13px;margin-top:4px}
.cw-nav{display:flex;justify-content:space-between;gap:10px;margin-top:12px}.cw-nav button{border:1px solid #bfd5ee;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer}.cw-nav .p{background:linear-gradient(135deg,#0d63d6,#0e9a91);border-color:#0d63d6;color:#fff}
.cw-note{margin-top:12px;border:1px dashed #c3d4ea;border-radius:12px;padding:12px;background:#fff;color:#466182}
.cw-by-need{display:none}.cw-by-need.a{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.cw-groups-list{display:grid;gap:8px}
.cw-group-row{display:grid!important;grid-template-columns:16px minmax(0,1fr);align-items:start;column-gap:10px;margin:0!important;font-weight:600;color:#17385f}
.cw-group-row input[type="checkbox"]{position:static!important;left:auto!important;top:auto!important;margin:4px 0 0 0!important;width:14px;height:14px;justify-self:start;align-self:start}
.cw-group-title{display:block;line-height:1.35;word-break:break-word}
.cw-budget{display:grid;gap:8px}.cw-budget-values{font-size:13px;color:#355477}
.cw-budget input[type=range]{padding:0}
.contact-simple.dark,body.ui-tone-dark .contact-simple{color:#d8e7fb}
.contact-simple.dark .contact-simple-hero,body.ui-tone-dark .contact-simple-hero{border-color:#304966;background:linear-gradient(145deg,#102338,#0d1c2e)}
.contact-simple.dark .contact-simple-copy,body.ui-tone-dark .contact-simple-copy{border-color:#304966;background:linear-gradient(180deg,#112339,#0f1e32)}
.contact-simple.dark .contact-simple-copy p,body.ui-tone-dark .contact-simple-copy p{color:#b7cae3}
.contact-simple.dark .cw,body.ui-tone-dark .cw{border-color:#304966;background:linear-gradient(145deg,#102338,#0d1c2e)}
.contact-simple.dark .cw h2,.contact-simple.dark .cw p,body.ui-tone-dark .cw h2,body.ui-tone-dark .cw p{color:#c6dcf8}
.contact-simple.dark .cw-panel,body.ui-tone-dark .cw-panel{border-color:#2f4c6f;background:#112339}
.contact-simple.dark .cw-field label,body.ui-tone-dark .cw-field label{color:#c5dcf7}
.contact-simple.dark .cw-field input,.contact-simple.dark .cw-field select,.contact-simple.dark .cw-field textarea,body.ui-tone-dark .cw-field input,body.ui-tone-dark .cw-field select,body.ui-tone-dark .cw-field textarea{background:#0b1626;border-color:#3a5881;color:#d4e6fb}
.contact-simple.dark .cw-needs label,.contact-simple.dark .cw-note,body.ui-tone-dark .cw-needs label,body.ui-tone-dark .cw-note{background:#0f1f33;border-color:#2f4d70;color:#c5dcf7}
.contact-simple.dark .cw-needs strong,.contact-simple.dark .cw-needs span,body.ui-tone-dark .cw-needs strong,body.ui-tone-dark .cw-needs span{color:#c5dcf7}
.contact-simple.dark .cw-group-row,body.ui-tone-dark .cw-group-row{color:#c5dcf7}
@media (max-width:980px){.cw-needs,.cw-grid,.cw-by-need.a{grid-template-columns:1fr}}
</style>

<section class="contact-simple<?= $forceDark ? ' dark' : '' ?>">
  <div class="contact-simple-hero"><h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1><p><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p></div>
  <div class="contact-simple-copy"><p><?= htmlspecialchars($isRu ? 'Используйте мастер обращения, чтобы быстро собрать бриф. Он показывает только релевантные поля по вашей задаче.' : 'Use the request wizard to build a brief quickly. It shows only relevant fields for your task.', ENT_QUOTES, 'UTF-8') ?></p></div>

  <section class="cw" id="contact-wizard">
    <h2><?= htmlspecialchars($isRu ? 'Подбор решения под вашу задачу' : 'Find the right solution for your task', ENT_QUOTES, 'UTF-8') ?></h2>
    <p><?= htmlspecialchars($isRu ? 'Ответьте на несколько коротких вопросов, и я подготовлю понятный план: что делать в первую очередь, как лучше реализовать, какие сроки и формат сотрудничества будут оптимальны.' : 'Answer a few short questions and I will prepare a clear plan: what to do first, how to implement it best, and which timeline and collaboration format fit your case.', ENT_QUOTES, 'UTF-8') ?></p>
    <form id="cwForm" method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="action" value="public_contact_submit"><input type="hidden" name="return_path" value="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="contact_form_anchor" value="#contact-wizard"><input type="hidden" name="contact_interest" value="wizard"><input type="hidden" name="contact_csrf" value="<?= htmlspecialchars($contactToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="contact_started_at" value="<?= time() ?>"><input type="hidden" name="contact_campaign" value="contact:wizard"><input type="hidden" name="contact_wizard_payload" value=""><input type="hidden" name="contact_subject" value=""><input type="hidden" name="contact_message" value=""><input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="contact-hp" aria-hidden="true">
      <div class="cw-steps">
        <span class="a" data-i="1">1. <?= htmlspecialchars((string)$stepTitles[0], ENT_QUOTES, 'UTF-8') ?></span>
        <span data-i="2">2. <?= htmlspecialchars((string)$stepTitles[1], ENT_QUOTES, 'UTF-8') ?></span>
        <span data-i="3">3. <?= htmlspecialchars((string)$stepTitles[2], ENT_QUOTES, 'UTF-8') ?></span>
        <span data-i="4">4. <?= htmlspecialchars((string)$stepTitles[3], ENT_QUOTES, 'UTF-8') ?></span>
      </div>

      <div class="cw-panel a" data-step="1">
        <p><?= htmlspecialchars($isRu ? 'Выберите направление, по которому вас интересует разработка или развитие проекта.' : 'Choose the direction you want to discuss for development or product growth.', ENT_QUOTES, 'UTF-8') ?></p>
        <div class="cw-needs"><?php foreach ($wizardNeeds as $i => $need): ?><label><input type="radio" name="wizard_need" value="<?= htmlspecialchars((string)$need['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $i === 0 ? 'checked' : '' ?>><strong><?= htmlspecialchars((string)$need['label'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string)$need['desc'], ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div>
      </div>

      <div class="cw-panel" data-step="2">
        <div class="cw-by-need" data-need="develop">
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Тип продукта' : 'Product type', ENT_QUOTES, 'UTF-8') ?></label><select name="wz_product_type"><option value=""><?= htmlspecialchars($isRu ? 'Выберите' : 'Select', ENT_QUOTES, 'UTF-8') ?></option><option>SaaS</option><option><?= htmlspecialchars($isRu ? 'Маркетплейс' : 'Marketplace', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Корпоративный портал' : 'Corporate portal', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Интернет-магазин' : 'E-commerce', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Мобильное приложение' : 'Mobile app', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Личный кабинет' : 'Customer cabinet', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'CRM/ERP модуль' : 'CRM/ERP module', ENT_QUOTES, 'UTF-8') ?></option></select></div>
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Стадия' : 'Stage', ENT_QUOTES, 'UTF-8') ?></label><select name="wz_stage"><option><?= htmlspecialchars($isRu ? 'Идея / Discovery' : 'Idea / Discovery', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Прототип' : 'Prototype', ENT_QUOTES, 'UTF-8') ?></option><option>MVP</option><option><?= htmlspecialchars($isRu ? 'Развитие production' : 'Production growth', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Миграция / масштабирование' : 'Migration / scale-up', ENT_QUOTES, 'UTF-8') ?></option></select></div>
        </div>
        <div class="cw-by-need" data-need="refactor"><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Текущий стек' : 'Current stack', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_stack"></div><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Проблема' : 'Issue', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_refactor_problem"></div></div>
        <div class="cw-by-need" data-need="audit"><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Тип аудита' : 'Audit type', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_audit_type"></div><div class="cw-field"><label>URL</label><input type="text" name="wz_audit_target" placeholder="https://"></div></div>
        <div class="cw-by-need" data-need="docs"><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Вид документации' : 'Doc type', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_docs_type"></div><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Аудитория' : 'Audience', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_docs_audience"></div></div>
        <div class="cw-by-need" data-need="seo"><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Сайт' : 'Site', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_seo_site" placeholder="https://"></div><div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Приоритет' : 'Priority', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_seo_priority"></div></div>

        <div class="cw-grid">
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Сроки' : 'Timeline', ENT_QUOTES, 'UTF-8') ?></label><select name="wz_timeline"><option>1-3 <?= htmlspecialchars($isRu ? 'дня' : 'days', ENT_QUOTES, 'UTF-8') ?></option><option>1 <?= htmlspecialchars($isRu ? 'неделя' : 'week', ENT_QUOTES, 'UTF-8') ?></option><option>2-3 <?= htmlspecialchars($isRu ? 'недели' : 'weeks', ENT_QUOTES, 'UTF-8') ?></option><option>1 <?= htmlspecialchars($isRu ? 'месяц' : 'month', ENT_QUOTES, 'UTF-8') ?></option><option>2-3 <?= htmlspecialchars($isRu ? 'месяца' : 'months', ENT_QUOTES, 'UTF-8') ?></option><option>4-6 <?= htmlspecialchars($isRu ? 'месяцев' : 'months', ENT_QUOTES, 'UTF-8') ?></option><option><?= htmlspecialchars($isRu ? 'Бессрочно / по этапам' : 'Open-ended / phased', ENT_QUOTES, 'UTF-8') ?></option></select></div>
          <div class="cw-field cw-budget"><label><?= htmlspecialchars($isRu ? 'Бюджет (₽)' : 'Budget (RUB)', ENT_QUOTES, 'UTF-8') ?></label><div class="cw-budget-values" id="cwBudgetText"></div><input type="range" name="wz_budget_from" min="1000" max="3000000" step="1000" value="300000"><input type="range" name="wz_budget_to" min="1000" max="3000000" step="1000" value="1200000"></div>
        </div>
      </div>

      <div class="cw-panel" data-step="3">
        <div class="cw-grid">
          <div class="cw-field">
            <label><?= htmlspecialchars($isRu ? 'Группы услуг' : 'Service groups', ENT_QUOTES, 'UTF-8') ?></label>
            <div class="cw-groups-list">
              <?php foreach ($groupPayload as $ix => $gr): ?>
                <label class="cw-group-row">
                  <input type="checkbox" class="cw-group" value="<?= htmlspecialchars((string)$gr['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $ix < 2 ? 'checked' : '' ?>>
                  <span class="cw-group-title"><?= htmlspecialchars((string)$gr['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Услуги' : 'Services', ENT_QUOTES, 'UTF-8') ?></label><select name="wz_services[]" id="cwServices" multiple size="8"></select></div>
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Ссылки' : 'Links', ENT_QUOTES, 'UTF-8') ?></label><textarea name="wz_links"></textarea></div>
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Файлы (PDF, DOC, XLS)' : 'Files (PDF, DOC, XLS)', ENT_QUOTES, 'UTF-8') ?></label><input type="file" name="contact_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ods"></div>
          <div class="cw-field" style="grid-column:1/-1"><label><?= htmlspecialchars($isRu ? 'Комментарий' : 'Comment', ENT_QUOTES, 'UTF-8') ?></label><textarea name="wz_comment"></textarea></div>
        </div>
      </div>

      <div class="cw-panel" data-step="4">
        <div class="cw-grid">
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Имя' : 'Name', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="contact_name" required></div>
          <div class="cw-field"><label>Email</label><input type="email" name="contact_email" required></div>
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Компания (опционально)' : 'Company (optional)', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="contact_campaign_hint"></div>
          <div class="cw-field"><label><?= htmlspecialchars($isRu ? 'Telegram/Другой мессенджер (опционально)' : 'Telegram/Other messenger (optional)', ENT_QUOTES, 'UTF-8') ?></label><input type="text" name="wz_contact_channel"></div>
        </div>
        <div class="cw-note" id="cwSummary"></div>
      </div>

      <div class="cw-nav"><button type="button" id="cwPrev"><?= htmlspecialchars($isRu ? 'Назад' : 'Back', ENT_QUOTES, 'UTF-8') ?></button><button type="button" class="p" id="cwNext"><?= htmlspecialchars($isRu ? 'Далее' : 'Next', ENT_QUOTES, 'UTF-8') ?></button><button type="submit" class="p" id="cwSubmit" style="display:none;"><?= htmlspecialchars($isRu ? 'Отправить запрос' : 'Send request', ENT_QUOTES, 'UTF-8') ?></button></div>
    </form>
  </section>
  <div class="contact-simple-copy"><p><?= htmlspecialchars($isRu ? 'Мастер нужен для ускоренного брифа и точной оценки.' : 'The wizard is designed for a faster brief and more accurate estimate.', ENT_QUOTES, 'UTF-8') ?></p><p><?= htmlspecialchars($isRu ? 'Если у вас короткое отдельное обращение, используйте форму связи ниже в подвале.' : 'If you have a short standalone request, use the contact form below in the footer.', ENT_QUOTES, 'UTF-8') ?></p></div>
</section>

<script>
(function(){var form=document.getElementById('cwForm');if(!form)return;var steps=[].slice.call(form.querySelectorAll('.cw-panel')),inds=[].slice.call(form.querySelectorAll('.cw-steps span')),needInputs=[].slice.call(form.querySelectorAll('input[name="wizard_need"]')),needBlocks=[].slice.call(form.querySelectorAll('.cw-by-need')),prev=document.getElementById('cwPrev'),next=document.getElementById('cwNext'),submit=document.getElementById('cwSubmit'),services=document.getElementById('cwServices'),groups=[].slice.call(form.querySelectorAll('.cw-group')),hiddenPayload=form.querySelector('input[name="contact_wizard_payload"]'),hiddenMsg=form.querySelector('input[name="contact_message"]'),hiddenSubj=form.querySelector('input[name="contact_subject"]'),hiddenCamp=form.querySelector('input[name="contact_campaign"]'),summary=document.getElementById('cwSummary'),budgetFrom=form.querySelector('input[name="wz_budget_from"]'),budgetTo=form.querySelector('input[name="wz_budget_to"]'),budgetText=document.getElementById('cwBudgetText'),needMap=<?= json_encode($wizardNeeds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,grp=<?= json_encode($groupPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,ru=<?= $isRu ? 'true' : 'false' ?>,s=1;
function fmtRub(v){var n=Number(v||0);return n.toLocaleString('ru-RU')+' ₽';}
function syncBudget(){if(!budgetFrom||!budgetTo||!budgetText)return;var a=Number(budgetFrom.value),b=Number(budgetTo.value);if(a>b){var t=a;a=b;b=t;budgetFrom.value=a;budgetTo.value=b;}budgetText.textContent=(ru?'Диапазон: ':'Range: ')+fmtRub(a)+' - '+fmtRub(b);}
function needCode(){for(var i=0;i<needInputs.length;i++){if(needInputs[i].checked)return needInputs[i].value;}return'';}
function needLabel(){var c=needCode();for(var i=0;i<needMap.length;i++){if(needMap[i].code===c)return needMap[i].label;}return c;}
function activeGroups(){var a=[];groups.forEach(function(g){if(g.checked)a.push(g.value);});return a;}
function refreshServices(){if(!services)return;var keep={};for(var i=0;i<services.options.length;i++){if(services.options[i].selected)keep[services.options[i].value]=1;}services.innerHTML='';var ag=activeGroups();grp.forEach(function(g){if(ag.indexOf(g.code)===-1)return;(g.items||[]).forEach(function(it){var o=document.createElement('option');o.value=g.code+'::'+it;o.textContent='['+g.label+'] '+it;if(keep[o.value])o.selected=true;services.appendChild(o);});});}
function toggleNeed(){var c=needCode();needBlocks.forEach(function(b){b.classList.toggle('a',b.getAttribute('data-need')===c);});}
function setStep(v){s=Math.max(1,Math.min(4,v));steps.forEach(function(p){p.classList.toggle('a',Number(p.getAttribute('data-step'))===s);});inds.forEach(function(i){i.classList.toggle('a',Number(i.getAttribute('data-i'))===s);});prev.style.visibility=s===1?'hidden':'visible';next.style.display=s===4?'none':'inline-flex';submit.style.display=s===4?'inline-flex':'none';if(s===4){summary.innerHTML='<strong>'+(ru?'Итог запроса:':'Request summary:')+'</strong><br>'+(ru?'Тип: ':'Type: ')+needLabel()+'<br>'+(ru?'Сроки: ':'Timeline: ')+(form.wz_timeline.value||'')+'<br>'+(ru?'Бюджет: ':'Budget: ')+budgetText.textContent.replace(/^.*?:\\s*/,'');}}
function val4(){var n=form.querySelector('input[name="contact_name"]'),e=form.querySelector('input[name="contact_email"]');return !!(n&&String(n.value||'').trim().length>1&&e&&String(e.value||'').indexOf('@')>0);}
needInputs.forEach(function(n){n.addEventListener('change',toggleNeed);});groups.forEach(function(g){g.addEventListener('change',refreshServices);});if(budgetFrom)budgetFrom.addEventListener('input',syncBudget);if(budgetTo)budgetTo.addEventListener('input',syncBudget);
prev.addEventListener('click',function(){setStep(s-1);});next.addEventListener('click',function(){setStep(s+1);});
form.addEventListener('submit',function(e){if(!val4()){e.preventDefault();setStep(4);return;}syncBudget();var payload={need:needLabel(),need_code:needCode(),timeline:(form.wz_timeline.value||''),budget_from:(budgetFrom?budgetFrom.value:''),budget_to:(budgetTo?budgetTo.value:''),budget_range:(budgetText?budgetText.textContent:''),product_type:(form.wz_product_type?form.wz_product_type.value:''),stage:(form.wz_stage?form.wz_stage.value:''),stack:(form.wz_stack?form.wz_stack.value:''),refactor_problem:(form.wz_refactor_problem?form.wz_refactor_problem.value:''),audit_type:(form.wz_audit_type?form.wz_audit_type.value:''),audit_target:(form.wz_audit_target?form.wz_audit_target.value:''),docs_type:(form.wz_docs_type?form.wz_docs_type.value:''),docs_audience:(form.wz_docs_audience?form.wz_docs_audience.value:''),seo_site:(form.wz_seo_site?form.wz_seo_site.value:''),seo_priority:(form.wz_seo_priority?form.wz_seo_priority.value:''),services:[].slice.call(services.options).filter(function(o){return o.selected;}).map(function(o){return o.textContent;}),links:(form.wz_links.value||''),comment:(form.wz_comment.value||''),contact_channel:(form.wz_contact_channel.value||'')};hiddenPayload.value=JSON.stringify(payload);hiddenSubj.value=payload.need||(ru?'Заявка через мастер':'Wizard request');hiddenCamp.value='contact:wizard:'+payload.need_code;hiddenMsg.value=(ru?'Запрос через мастер обращения':'Request from contact wizard')+'\n'+(ru?'Тип: ':'Type: ')+payload.need+'\n'+(ru?'Сроки: ':'Timeline: ')+payload.timeline+'\n'+(ru?'Бюджет: ':'Budget: ')+payload.budget_range;});
toggleNeed();refreshServices();syncBudget();setStep(1);})();
</script>
