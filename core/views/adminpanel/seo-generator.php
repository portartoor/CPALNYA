<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>
    <?php
    $s = (array)($seoGeneratorSettings ?? []);
    $moodLines = [];
    foreach ((array)($s['moods'] ?? []) as $m) {
        $moodLines[] = (string)($m['key'] ?? '') . ' | ' . (string)($m['weight'] ?? '1') . ' | ' . (string)($m['label_en'] ?? '') . ' | ' . (string)($m['label_ru'] ?? '');
    }
    $colorSchemeLines = [];
    foreach ((array)($s['image_color_schemes'] ?? []) as $row) {
        $colorSchemeLines[] = (string)($row['key'] ?? '') . ' | ' . (string)($row['weight'] ?? '1') . ' | ' . (string)($row['instruction'] ?? '');
    }
    $compositionLines = [];
    foreach ((array)($s['image_compositions'] ?? []) as $row) {
        $compositionLines[] = (string)($row['key'] ?? '')
            . ' | ' . (string)($row['weight'] ?? '1')
            . ' | ' . (string)($row['label_en'] ?? '')
            . ' | ' . (string)($row['label_ru'] ?? '')
            . ' | ' . (string)($row['instruction'] ?? '');
    }
    $sceneFamilyLines = [];
    foreach ((array)($s['image_scene_families'] ?? []) as $row) {
        $sceneFamilyLines[] = (string)($row['key'] ?? '')
            . ' | ' . (string)($row['weight'] ?? '1')
            . ' | ' . (string)($row['label_en'] ?? '')
            . ' | ' . (string)($row['label_ru'] ?? '')
            . ' | ' . (string)($row['instruction'] ?? '');
    }
    $scenarioLines = [];
    foreach ((array)($s['image_scenarios'] ?? []) as $row) {
        $scenarioLines[] = (string)($row['key'] ?? '')
            . ' | ' . (string)($row['weight'] ?? '1')
            . ' | ' . (string)($row['label_en'] ?? '')
            . ' | ' . (string)($row['label_ru'] ?? '')
            . ' | ' . (string)($row['instruction'] ?? '');
    }
    $provider = strtolower((string)($s['llm_provider'] ?? 'openai'));
    $pt = strtolower((string)($s['openai_proxy_type'] ?? 'http'));
    $rawCampaigns = is_array($s['campaigns'] ?? null) ? (array)($s['campaigns'] ?? []) : [];
    $campaigns = function_exists('seo_gen_normalize_campaigns')
        ? seo_gen_normalize_campaigns((array)($s['campaigns'] ?? []))
        : [];
    $campaignDefaults = function_exists('seo_gen_default_campaigns')
        ? seo_gen_default_campaigns()
        : [];
    if (!isset($campaignDefaults['reviews']) || !is_array($campaignDefaults['reviews'])) {
        $campaignDefaults['reviews'] = [
            'key' => 'reviews',
            'title' => 'Reviews',
            'title_ru' => 'Обзоры',
            'description' => 'Real reviews, comparisons and shortlists for affiliate tools, vendors and working stacks.',
            'description_ru' => 'Реальные обзоры, сравнения и подборки по инструментам, поставщикам и рабочим стекам для арбитража.',
            'material_section' => 'reviews',
            'enabled' => true,
            'daily_min' => 4,
            'daily_max' => 8,
            'max_per_run' => 3,
            'word_min' => 1800,
            'word_max' => 4200,
            'seed_salt_suffix' => 'reviews',
            'styles_en' => ['comparison review', 'tool benchmark', 'provider shortlist', 'buyer guide', 'stack review', 'field comparison'],
            'styles_ru' => ['сравнительный обзор', 'бенчмарк инструментов', 'шортлист поставщиков', 'buyer guide', 'разбор стека', 'полевое сравнение'],
            'clusters_en' => ['affiliate networks and partner programs', 'cloaking tools and routing stacks', 'tracker platforms and attribution quality'],
            'clusters_ru' => ['партнерки и affiliate-сети', 'клоаки и routing-стеки', 'трекеры и качество атрибуции'],
            'article_structures_en' => ['Category -> evaluation criteria -> shortlist -> comparison table -> recommendation'],
            'article_structures_ru' => ['Категория -> критерии оценки -> шортлист -> таблица сравнения -> рекомендация'],
            'article_system_prompt_en' => '',
            'article_system_prompt_ru' => '',
            'article_user_prompt_append_en' => 'Write real reviews, comparisons and curated shortlists for affiliate operators.',
            'article_user_prompt_append_ru' => 'Пиши реальные обзоры, сравнения и подборки для affiliate-операторов.',
        ];
    }
    $campaignFallbackFlags = [];
    foreach ($campaignDefaults as $campaignKey => $campaignDefault) {
        $campaignFallbackFlags[$campaignKey] = !isset($rawCampaigns[$campaignKey]) || !is_array($rawCampaigns[$campaignKey]);
        if (!isset($campaigns[$campaignKey]) || !is_array($campaigns[$campaignKey])) {
            $campaigns[$campaignKey] = $campaignDefault;
        }
        if ($campaignFallbackFlags[$campaignKey]) {
            $campaigns[$campaignKey] = array_merge($campaignDefault, [
                'description' => '',
                'description_ru' => '',
                'styles_en' => [],
                'styles_ru' => [],
                'clusters_en' => [],
                'clusters_ru' => [],
                'article_structures_en' => [],
                'article_structures_ru' => [],
                'article_system_prompt_en' => '',
                'article_system_prompt_ru' => '',
                'article_user_prompt_append_en' => '',
                'article_user_prompt_append_ru' => '',
            ]);
        }
    }
    $hasCampaignFallback = in_array(true, $campaignFallbackFlags, true);
    $campaignOrder = function_exists('seo_gen_allowed_campaign_keys')
        ? seo_gen_allowed_campaign_keys()
        : ['journal', 'playbooks', 'signals', 'reviews', 'fun'];
    $orderedCampaigns = [];
    foreach ($campaignOrder as $campaignKey) {
        if (isset($campaigns[$campaignKey]) && is_array($campaigns[$campaignKey])) {
            $orderedCampaigns[$campaignKey] = $campaigns[$campaignKey];
        }
    }
    foreach ($campaigns as $campaignKey => $campaign) {
        if (!isset($orderedCampaigns[$campaignKey]) && is_array($campaign)) {
            $orderedCampaigns[$campaignKey] = $campaign;
        }
    }
    $campaigns = $orderedCampaigns;
    ?>
    <style>
        .wiz-wrap .nav-link { border: 1px solid #e9ecef; margin-bottom: 8px; text-align: left; border-radius: 10px; }
        .wiz-wrap .nav-link.active { background: #eef6ff; border-color: #9ec5fe; color: #0a58ca; }
        .wiz-pane { border: 1px solid #eef1f5; border-radius: 12px; padding: 16px; background: #fff; }
        .wiz-title { font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .wiz-help { font-size: 12px; color: #6c757d; }
        .wiz-invalid { border-color: #dc3545 !important; }
        .wiz-progress { height: 8px; background: #e9ecef; border-radius: 999px; overflow: hidden; }
        .wiz-progress > span { display: block; height: 100%; width: 14.3%; background: linear-gradient(90deg,#0d6efd,#00a3ff); transition: width .2s ease; }
    </style>

    <div class="card wiz-wrap">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">SEO Generator Wizard</h5>
                    <div class="wiz-help">Grouped settings, side tabs, validation and step navigation.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light border" id="wizStepBadge">Step 1/7</span>
                    <button class="btn btn-sm btn-outline-secondary" type="button" id="wizValidate">Validate</button>
                </div>
            </div>
            <div class="wiz-progress mt-3"><span id="wizBar"></span></div>
        </div>
        <div class="card-body">
            <form method="POST" id="seoWizardForm" novalidate>
                <input type="hidden" name="action" value="save_seo_generator_settings">
                <div class="row g-3">
                    <div class="col-lg-3">
                        <div class="nav flex-column nav-pills" id="wizNav" role="tablist">
                            <button class="nav-link active" data-step="0" data-bs-toggle="pill" data-bs-target="#wiz-core" type="button"><i class="ti ti-adjustments me-1"></i> 1. Core</button>
                            <button class="nav-link" data-step="1" data-bs-toggle="pill" data-bs-target="#wiz-campaigns" type="button"><i class="ti ti-layers-intersect me-1"></i> 2. Campaigns</button>
                            <button class="nav-link" data-step="2" data-bs-toggle="pill" data-bs-target="#wiz-provider" type="button"><i class="ti ti-key me-1"></i> 3. Provider/API</button>
                            <button class="nav-link" data-step="3" data-bs-toggle="pill" data-bs-target="#wiz-topics" type="button"><i class="ti ti-bulb me-1"></i> 4. Topics</button>
                            <button class="nav-link" data-step="4" data-bs-toggle="pill" data-bs-target="#wiz-prompts" type="button"><i class="ti ti-message-2-code me-1"></i> 5. Prompts</button>
                            <button class="nav-link" data-step="5" data-bs-toggle="pill" data-bs-target="#wiz-preview" type="button"><i class="ti ti-photo-cog me-1"></i> 6. Preview/Graphics</button>
                            <button class="nav-link" data-step="6" data-bs-toggle="pill" data-bs-target="#wiz-review" type="button"><i class="ti ti-device-floppy me-1"></i> 7. Save</button>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="wiz-core">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-adjustments"></i> Core & Scheduling</div>
                                    <?php if ($hasCampaignFallback): ?>
                                        <div class="alert alert-warning py-2">
                                            Some campaign cards are currently rendered from fallback/default config because their objects are missing or malformed in <code>settings_json.campaigns</code>.
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($hasCampaignFallback): ?>
                                        <div class="alert alert-warning py-2">
                                            Some campaign cards are currently rendered from fallback/default config because their objects are missing or malformed in <code>settings_json.campaigns</code>.
                                        </div>
                                    <?php endif; ?>
                                    <div class="row g-3">
                                        <div class="col-md-3"><label class="form-label">Enabled</label><select class="form-select" name="enabled"><option value="1" <?= !empty($s['enabled']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['enabled']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Daily Min</label><input class="form-control req" type="number" name="daily_min" value="<?= (int)($s['daily_min'] ?? 1) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Daily Max</label><input class="form-control req" type="number" name="daily_max" value="<?= (int)($s['daily_max'] ?? 3) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Max Per Run</label><input class="form-control req" type="number" name="max_per_run" value="<?= (int)($s['max_per_run'] ?? 2) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Word Min</label><input class="form-control req" type="number" name="word_min" value="<?= (int)($s['word_min'] ?? 2000) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Word Max</label><input class="form-control req" type="number" name="word_max" value="<?= (int)($s['word_max'] ?? 5000) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Today Delay (min)</label><input class="form-control" type="number" name="today_first_delay_min" value="<?= (int)($s['today_first_delay_min'] ?? 15) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Auto Expand Retries</label><input class="form-control" type="number" name="auto_expand_retries" value="<?= (int)($s['auto_expand_retries'] ?? 1) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Languages</label><textarea class="form-control" rows="2" name="langs"><?= htmlspecialchars(implode("\n", (array)($s['langs'] ?? []))) ?></textarea><div class="wiz-help">Generator is locked to Russian output. Keep only <code>ru</code>.</div></div>
                                        <div class="col-md-6"><label class="form-label">Expand Context Chars</label><input class="form-control" type="number" name="expand_context_chars" value="<?= (int)($s['expand_context_chars'] ?? 7000) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Author Name</label><input class="form-control" name="author_name" value="<?= htmlspecialchars((string)($s['author_name'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Domain Host (legacy)</label><input class="form-control" name="domain_host" value="<?= htmlspecialchars((string)($s['domain_host'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Domain Host EN</label><input class="form-control" name="domain_host_en" value="<?= htmlspecialchars((string)($s['domain_host_en'] ?? 'cpalnya.ru')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Domain Host RU</label><input class="form-control" name="domain_host_ru" value="<?= htmlspecialchars((string)($s['domain_host_ru'] ?? 'cpalnya.ru')) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Prompt Version</label><input class="form-control" name="prompt_version" value="<?= htmlspecialchars((string)($s['prompt_version'] ?? '')) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Seed Salt</label><input class="form-control" name="seed_salt" value="<?= htmlspecialchars((string)($s['seed_salt'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Tone Variability (0-100)</label><input class="form-control" type="number" min="0" max="100" name="tone_variability" value="<?= (int)($s['tone_variability'] ?? 60) ?>"><div class="wiz-help">Higher value => more diverse tone and pacing; lower => stricter enterprise tone.</div></div>
                                        <div class="col-12"><div class="wiz-help"><b>Content Portfolio Weights</b>: controls funnel mix in generation (BOFU/MOFU/Authority/Case/Product).</div></div>
                                        <div class="col-md-2"><label class="form-label">BOFU Weight</label><input class="form-control" type="number" min="0" max="1000" name="portfolio_bofu_weight" value="<?= (int)($s['portfolio_bofu_weight'] ?? 30) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">MOFU Weight</label><input class="form-control" type="number" min="0" max="1000" name="portfolio_mofu_weight" value="<?= (int)($s['portfolio_mofu_weight'] ?? 30) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Authority Weight</label><input class="form-control" type="number" min="0" max="1000" name="portfolio_authority_weight" value="<?= (int)($s['portfolio_authority_weight'] ?? 20) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Case Weight</label><input class="form-control" type="number" min="0" max="1000" name="portfolio_case_weight" value="<?= (int)($s['portfolio_case_weight'] ?? 10) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Product Weight</label><input class="form-control" type="number" min="0" max="1000" name="portfolio_product_weight" value="<?= (int)($s['portfolio_product_weight'] ?? 10) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Notify Schedule</label><select class="form-select" name="notify_schedule"><option value="1" <?= !empty($s['notify_schedule']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['notify_schedule']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-6"><label class="form-label">Notify Daily Summary</label><select class="form-select" name="notify_daily_schedule"><option value="1" <?= !empty($s['notify_daily_schedule']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['notify_daily_schedule']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-12"><hr class="my-1"></div>
                                        <div class="col-md-3"><label class="form-label">IndexNow Enabled</label><select class="form-select" name="indexnow_enabled"><option value="1" <?= !empty($s['indexnow_enabled']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['indexnow_enabled']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-3"><label class="form-label">IndexNow Ping on Publish</label><select class="form-select" name="indexnow_ping_on_publish"><option value="1" <?= !empty($s['indexnow_ping_on_publish']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['indexnow_ping_on_publish']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Submit Limit / run</label><input class="form-control" type="number" min="1" max="500" name="indexnow_submit_limit" value="<?= (int)($s['indexnow_submit_limit'] ?? 100) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Retry Delay (minutes)</label><input class="form-control" type="number" min="1" max="1440" name="indexnow_retry_delay_minutes" value="<?= (int)($s['indexnow_retry_delay_minutes'] ?? 15) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">IndexNow Key</label><input class="form-control" name="indexnow_key" value="<?= htmlspecialchars((string)($s['indexnow_key'] ?? '')) ?>"><div class="wiz-help">Key used in payload as <code>key</code>.</div></div>
                                        <div class="col-md-6"><label class="form-label">IndexNow Key Location</label><input class="form-control" name="indexnow_key_location" value="<?= htmlspecialchars((string)($s['indexnow_key_location'] ?? '')) ?>"><div class="wiz-help">Absolute URL, path, or template. You can use <code>{host}</code> and <code>{key}</code> (e.g. <code>https://{host}/{key}.txt</code> or <code>/{key}.txt</code>).</div></div>
                                        <div class="col-md-12"><label class="form-label">IndexNow Endpoint Override</label><input class="form-control" name="indexnow_endpoint" value="<?= htmlspecialchars((string)($s['indexnow_endpoint'] ?? '')) ?>"><div class="wiz-help">Optional. Empty = default <code>https://api.indexnow.org/indexnow</code>.</div></div>
                                        <div class="col-md-12"><label class="form-label">IndexNow Hosts (one per line)</label><textarea class="form-control" rows="3" name="indexnow_hosts"><?= htmlspecialchars(implode("\n", (array)($s['indexnow_hosts'] ?? []))) ?></textarea></div>
                                        <div class="col-12"><hr class="my-1"></div>
                                        <div class="col-12">
                                            <div class="alert alert-light border mb-0">
                                                Campaign-specific settings are now in the separate <b>Campaigns</b> tab, so each rubric is easier to find and edit.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="wiz-campaigns">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-layers-intersect"></i> Campaigns / Rubrics</div>
                                    <div class="wiz-help mb-3">Five independent generation campaigns for ЦПАЛЬНЯ. Each rubric controls its own section, limits, topics and writing behavior.</div>
                                    <div class="row g-3">
                                        <?php foreach ($campaigns as $campaignKey => $campaign): ?>
                                            <?php $prefix = 'campaign_' . $campaignKey . '_'; ?>
                                            <div class="col-12">
                                                <div class="border rounded p-3">
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <h6 class="mb-0">
                                                                <?= htmlspecialchars((string)($campaign['title'] ?? $campaignKey)) ?> / <?= htmlspecialchars((string)($campaign['title_ru'] ?? $campaignKey)) ?>
                                                                <?php if (!empty($campaignFallbackFlags[$campaignKey])): ?>
                                                                    <span class="badge text-bg-warning ms-2">fallback</span>
                                                                <?php endif; ?>
                                                            </h6>
                                                        </div>
                                                        <?php if (!empty($campaignFallbackFlags[$campaignKey])): ?>
                                                            <div class="col-12">
                                                                <div class="alert alert-warning py-2 mb-0">
                                                                    Stored DB object <code>settings_json.campaigns.<?= htmlspecialchars((string)$campaignKey) ?></code> is missing. This card is not loaded from DB yet.
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="col-md-2"><label class="form-label">Enabled</label><select class="form-select" name="<?= htmlspecialchars($prefix) ?>enabled"><option value="1" <?= !empty($campaign['enabled']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($campaign['enabled']) ? 'selected' : '' ?>>No</option></select></div>
                                                        <div class="col-md-2"><label class="form-label">Section</label><select class="form-select" name="<?= htmlspecialchars($prefix) ?>material_section"><option value="journal" <?= (($campaign['material_section'] ?? '') === 'journal') ? 'selected' : '' ?>>Journal</option><option value="playbooks" <?= (($campaign['material_section'] ?? '') === 'playbooks') ? 'selected' : '' ?>>Playbooks</option><option value="signals" <?= (($campaign['material_section'] ?? '') === 'signals') ? 'selected' : '' ?>>Signals</option><option value="reviews" <?= (($campaign['material_section'] ?? '') === 'reviews') ? 'selected' : '' ?>>Reviews</option><option value="fun" <?= (($campaign['material_section'] ?? '') === 'fun') ? 'selected' : '' ?>>Fun</option></select></div>
                                                        <div class="col-md-2"><label class="form-label">Daily Min</label><input class="form-control" type="number" name="<?= htmlspecialchars($prefix) ?>daily_min" value="<?= (int)($campaign['daily_min'] ?? 4) ?>"></div>
                                                        <div class="col-md-2"><label class="form-label">Daily Max</label><input class="form-control" type="number" name="<?= htmlspecialchars($prefix) ?>daily_max" value="<?= (int)($campaign['daily_max'] ?? 6) ?>"></div>
                                                        <div class="col-md-2"><label class="form-label">Max Per Run</label><input class="form-control" type="number" name="<?= htmlspecialchars($prefix) ?>max_per_run" value="<?= (int)($campaign['max_per_run'] ?? 2) ?>"></div>
                                                        <div class="col-md-2"><label class="form-label">Salt Suffix</label><input class="form-control" name="<?= htmlspecialchars($prefix) ?>seed_salt_suffix" value="<?= htmlspecialchars((string)($campaign['seed_salt_suffix'] ?? $campaignKey)) ?>"></div>
                                                        <div class="col-md-3"><label class="form-label">Word Min</label><input class="form-control" type="number" name="<?= htmlspecialchars($prefix) ?>word_min" value="<?= (int)($campaign['word_min'] ?? 1800) ?>"></div>
                                                        <div class="col-md-3"><label class="form-label">Word Max</label><input class="form-control" type="number" name="<?= htmlspecialchars($prefix) ?>word_max" value="<?= (int)($campaign['word_max'] ?? 3200) ?>"></div>
                                                        <div class="col-md-3"><label class="form-label">Title EN</label><input class="form-control" name="<?= htmlspecialchars($prefix) ?>title" value="<?= htmlspecialchars((string)($campaign['title'] ?? '')) ?>"></div>
                                                        <div class="col-md-3"><label class="form-label">Title RU</label><input class="form-control" name="<?= htmlspecialchars($prefix) ?>title_ru" value="<?= htmlspecialchars((string)($campaign['title_ru'] ?? '')) ?>"></div>
                                                        <div class="col-md-6"><label class="form-label">Description EN</label><textarea class="form-control" rows="2" name="<?= htmlspecialchars($prefix) ?>description"><?= htmlspecialchars((string)($campaign['description'] ?? '')) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Description RU</label><textarea class="form-control" rows="2" name="<?= htmlspecialchars($prefix) ?>description_ru"><?= htmlspecialchars((string)($campaign['description_ru'] ?? '')) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Styles EN</label><textarea class="form-control" rows="4" name="<?= htmlspecialchars($prefix) ?>styles_en"><?= htmlspecialchars(implode("\n", (array)($campaign['styles_en'] ?? []))) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Styles RU</label><textarea class="form-control" rows="4" name="<?= htmlspecialchars($prefix) ?>styles_ru"><?= htmlspecialchars(implode("\n", (array)($campaign['styles_ru'] ?? []))) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Clusters EN</label><textarea class="form-control" rows="5" name="<?= htmlspecialchars($prefix) ?>clusters_en"><?= htmlspecialchars(implode("\n", (array)($campaign['clusters_en'] ?? []))) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Clusters RU</label><textarea class="form-control" rows="5" name="<?= htmlspecialchars($prefix) ?>clusters_ru"><?= htmlspecialchars(implode("\n", (array)($campaign['clusters_ru'] ?? []))) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Structures EN</label><textarea class="form-control" rows="4" name="<?= htmlspecialchars($prefix) ?>article_structures_en"><?= htmlspecialchars(implode("\n", (array)($campaign['article_structures_en'] ?? []))) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Structures RU</label><textarea class="form-control" rows="4" name="<?= htmlspecialchars($prefix) ?>article_structures_ru"><?= htmlspecialchars(implode("\n", (array)($campaign['article_structures_ru'] ?? []))) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Prompt Append EN</label><textarea class="form-control" rows="3" name="<?= htmlspecialchars($prefix) ?>article_user_prompt_append_en"><?= htmlspecialchars((string)($campaign['article_user_prompt_append_en'] ?? '')) ?></textarea></div>
                                                        <div class="col-md-6"><label class="form-label">Prompt Append RU</label><textarea class="form-control" rows="3" name="<?= htmlspecialchars($prefix) ?>article_user_prompt_append_ru"><?= htmlspecialchars((string)($campaign['article_user_prompt_append_ru'] ?? '')) ?></textarea></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="wiz-provider">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-key"></i> Provider, Routes, Keys, Proxy</div>
                                    <div class="row g-3">
                                        <div class="col-md-3"><label class="form-label">LLM Provider</label><select class="form-select req" name="llm_provider" id="llm_provider"><option value="openai" <?= $provider === 'openai' ? 'selected' : '' ?>>openai</option><option value="openrouter" <?= $provider === 'openrouter' ? 'selected' : '' ?>>openrouter</option></select></div>
                                        <div class="col-md-5"><label class="form-label">OpenAI Base URL</label><input class="form-control req" name="openai_base_url" value="<?= htmlspecialchars((string)($s['openai_base_url'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">OpenAI Model</label><input class="form-control req" name="openai_model" value="<?= htmlspecialchars((string)($s['openai_model'] ?? '')) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">OpenAI Timeout</label><input class="form-control" type="number" name="openai_timeout" value="<?= (int)($s['openai_timeout'] ?? 120) ?>"></div>
                                        <div class="col-md-9"><label class="form-label">OpenAI API Key</label><input class="form-control" name="openai_api_key" id="openai_api_key" value="<?= htmlspecialchars((string)($s['openai_api_key'] ?? '')) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">OpenAI Headers</label><textarea class="form-control" rows="2" name="openai_headers"><?= htmlspecialchars(implode("\n", (array)($s['openai_headers'] ?? []))) ?></textarea></div>
                                        <div class="col-md-3"><label class="form-label">OpenRouter Base URL</label><input class="form-control req" name="openrouter_base_url" value="<?= htmlspecialchars((string)($s['openrouter_base_url'] ?? '')) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">OpenRouter Model</label><input class="form-control req" name="openrouter_model" value="<?= htmlspecialchars((string)($s['openrouter_model'] ?? '')) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">OpenRouter Fallback Model</label><input class="form-control" name="openrouter_fallback_model" value="<?= htmlspecialchars((string)($s['openrouter_fallback_model'] ?? 'openai/gpt-4o-2024-11-20')) ?>"><div class="wiz-help">Used only if the main OpenRouter route returns a region/provider block. Do not leave an old Google model here unless you explicitly want that fallback.</div></div>
                                        <div class="col-md-12"><label class="form-label">OpenRouter API Key</label><input class="form-control" name="openrouter_api_key" id="openrouter_api_key" value="<?= htmlspecialchars((string)($s['openrouter_api_key'] ?? '')) ?>"></div>
                                        <div class="col-12"><hr class="my-1"></div>
                                        <div class="col-md-2"><label class="form-label">Proxy Enabled</label><select class="form-select" name="openai_proxy_enabled"><option value="1" <?= !empty($s['openai_proxy_enabled']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['openai_proxy_enabled']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Proxy Host</label><input class="form-control" name="openai_proxy_host" value="<?= htmlspecialchars((string)($s['openai_proxy_host'] ?? '')) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Proxy Port</label><input class="form-control" type="number" name="openai_proxy_port" value="<?= (int)($s['openai_proxy_port'] ?? 0) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Proxy Type</label><select class="form-select" name="openai_proxy_type"><option value="http" <?= $pt === 'http' ? 'selected' : '' ?>>http</option><option value="socks5" <?= $pt === 'socks5' ? 'selected' : '' ?>>socks5</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Pool Enabled</label><select class="form-select" name="openai_proxy_pool_enabled"><option value="1" <?= !empty($s['openai_proxy_pool_enabled']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['openai_proxy_pool_enabled']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-6"><label class="form-label">Proxy Username</label><input class="form-control" name="openai_proxy_username" value="<?= htmlspecialchars((string)($s['openai_proxy_username'] ?? '')) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Proxy Password</label><input class="form-control" name="openai_proxy_password" value="<?= htmlspecialchars((string)($s['openai_proxy_password'] ?? '')) ?>"></div>
                                        <div class="col-12"><label class="form-label">Proxy Pool</label><textarea class="form-control" rows="3" name="openai_proxy_pool"><?= htmlspecialchars(implode("\n", (array)($s['openai_proxy_pool'] ?? []))) ?></textarea></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="wiz-topics">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-bulb"></i> Topics, Structures, Moods</div>
                                    <div class="row g-3">
                                        <div class="col-md-3"><label class="form-label">Topic Analysis</label><select class="form-select" name="topic_analysis_enabled"><option value="1" <?= !empty($s['topic_analysis_enabled']) ? 'selected' : '' ?>>Enabled</option><option value="0" <?= empty($s['topic_analysis_enabled']) ? 'selected' : '' ?>>Disabled</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Topic Limit</label><input class="form-control" type="number" name="topic_analysis_limit" value="<?= (int)($s['topic_analysis_limit'] ?? 120) ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Topic Analysis System Prompt</label><textarea class="form-control" rows="2" name="topic_analysis_system_prompt"><?= htmlspecialchars((string)($s['topic_analysis_system_prompt'] ?? '')) ?></textarea></div>
                                        <div class="col-12"><label class="form-label">Topic Analysis Prompt Append</label><textarea class="form-control" rows="2" name="topic_analysis_user_prompt_append"><?= htmlspecialchars((string)($s['topic_analysis_user_prompt_append'] ?? '')) ?></textarea></div>
                                        <div class="col-12"><hr class="my-1"></div>
                                        <div class="col-12"><div class="wiz-help"><b>Signals live news</b>: feeds are fetched from the internet during `signals` generation and injected into the article prompt as fresh context.</div></div>
                                        <div class="col-md-3"><label class="form-label">Live News</label><select class="form-select" name="signals_news_enabled"><option value="1" <?= !empty($s['signals_news_enabled']) ? 'selected' : '' ?>>Enabled</option><option value="0" <?= empty($s['signals_news_enabled']) ? 'selected' : '' ?>>Disabled</option></select></div>
                                        <div class="col-md-3"><label class="form-label">News Max Items</label><input class="form-control" type="number" name="signals_news_max_items" value="<?= (int)($s['signals_news_max_items'] ?? 12) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Lookback Hours</label><input class="form-control" type="number" name="signals_news_lookback_hours" value="<?= (int)($s['signals_news_lookback_hours'] ?? 96) ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Feed Timeout</label><input class="form-control" type="number" name="signals_news_timeout" value="<?= (int)($s['signals_news_timeout'] ?? 12) ?>"></div>
                                        <div class="col-12"><label class="form-label">Signals News Feeds</label><textarea class="form-control" rows="5" name="signals_news_feeds"><?= htmlspecialchars(implode("\n", (array)($s['signals_news_feeds'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Styles EN</label><textarea class="form-control" rows="5" name="styles_en"><?= htmlspecialchars(implode("\n", (array)($s['styles_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Styles RU</label><textarea class="form-control" rows="5" name="styles_ru"><?= htmlspecialchars(implode("\n", (array)($s['styles_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Clusters EN</label><textarea class="form-control" rows="6" name="clusters_en"><?= htmlspecialchars(implode("\n", (array)($s['clusters_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Clusters RU</label><textarea class="form-control" rows="6" name="clusters_ru"><?= htmlspecialchars(implode("\n", (array)($s['clusters_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-12"><hr class="my-1"></div>
                                        <div class="col-12"><div class="wiz-help"><b>Intent Diversification</b>: one item per line. Generator will combine these axes to avoid cannibalized generic topics.</div></div>
                                        <div class="col-md-6"><label class="form-label">Intent Verticals EN</label><textarea class="form-control" rows="4" name="intent_verticals_en"><?= htmlspecialchars(implode("\n", (array)($s['intent_verticals_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Verticals RU</label><textarea class="form-control" rows="4" name="intent_verticals_ru"><?= htmlspecialchars(implode("\n", (array)($s['intent_verticals_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Scenarios EN</label><textarea class="form-control" rows="4" name="intent_scenarios_en"><?= htmlspecialchars(implode("\n", (array)($s['intent_scenarios_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Scenarios RU</label><textarea class="form-control" rows="4" name="intent_scenarios_ru"><?= htmlspecialchars(implode("\n", (array)($s['intent_scenarios_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Objectives EN</label><textarea class="form-control" rows="4" name="intent_objectives_en"><?= htmlspecialchars(implode("\n", (array)($s['intent_objectives_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Objectives RU</label><textarea class="form-control" rows="4" name="intent_objectives_ru"><?= htmlspecialchars(implode("\n", (array)($s['intent_objectives_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Constraints EN</label><textarea class="form-control" rows="4" name="intent_constraints_en"><?= htmlspecialchars(implode("\n", (array)($s['intent_constraints_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Constraints RU</label><textarea class="form-control" rows="4" name="intent_constraints_ru"><?= htmlspecialchars(implode("\n", (array)($s['intent_constraints_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Artifacts EN</label><textarea class="form-control" rows="4" name="intent_artifacts_en"><?= htmlspecialchars(implode("\n", (array)($s['intent_artifacts_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Artifacts RU</label><textarea class="form-control" rows="4" name="intent_artifacts_ru"><?= htmlspecialchars(implode("\n", (array)($s['intent_artifacts_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Outcomes EN</label><textarea class="form-control" rows="4" name="intent_outcomes_en"><?= htmlspecialchars(implode("\n", (array)($s['intent_outcomes_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Intent Outcomes RU</label><textarea class="form-control" rows="4" name="intent_outcomes_ru"><?= htmlspecialchars(implode("\n", (array)($s['intent_outcomes_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Service Focus EN</label><textarea class="form-control" rows="4" name="service_focus_en"><?= htmlspecialchars(implode("\n", (array)($s['service_focus_en'] ?? []))) ?></textarea><div class="wiz-help">Primary commercial service tracks for article intent anchoring.</div></div>
                                        <div class="col-md-6"><label class="form-label">Service Focus RU</label><textarea class="form-control" rows="4" name="service_focus_ru"><?= htmlspecialchars(implode("\n", (array)($s['service_focus_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Forbidden Topics EN</label><textarea class="form-control" rows="3" name="forbidden_topics_en"><?= htmlspecialchars(implode("\n", (array)($s['forbidden_topics_en'] ?? []))) ?></textarea><div class="wiz-help">Hard exclusions for generated topics.</div></div>
                                        <div class="col-md-6"><label class="form-label">Forbidden Topics RU</label><textarea class="form-control" rows="3" name="forbidden_topics_ru"><?= htmlspecialchars(implode("\n", (array)($s['forbidden_topics_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Article Structures EN</label><textarea class="form-control" rows="6" name="article_structures_en"><?= htmlspecialchars(implode("\n", (array)($s['article_structures_en'] ?? []))) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Article Structures RU</label><textarea class="form-control" rows="6" name="article_structures_ru"><?= htmlspecialchars(implode("\n", (array)($s['article_structures_ru'] ?? []))) ?></textarea></div>
                                        <div class="col-12"><label class="form-label">Moods (key | weight | label_en | label_ru)</label><textarea class="form-control font-monospace" rows="7" name="moods"><?= htmlspecialchars(implode("\n", $moodLines)) ?></textarea></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="wiz-prompts">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-message-2-code"></i> Prompt Overrides / Appends</div>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Article System Prompt EN</label><textarea class="form-control" rows="3" name="article_system_prompt_en"><?= htmlspecialchars((string)($s['article_system_prompt_en'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Article System Prompt RU</label><textarea class="form-control" rows="3" name="article_system_prompt_ru"><?= htmlspecialchars((string)($s['article_system_prompt_ru'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Article Prompt Append EN</label><textarea class="form-control" rows="3" name="article_user_prompt_append_en"><?= htmlspecialchars((string)($s['article_user_prompt_append_en'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Article Prompt Append RU</label><textarea class="form-control" rows="3" name="article_user_prompt_append_ru"><?= htmlspecialchars((string)($s['article_user_prompt_append_ru'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Expand System Prompt EN</label><textarea class="form-control" rows="3" name="expand_system_prompt_en"><?= htmlspecialchars((string)($s['expand_system_prompt_en'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Expand System Prompt RU</label><textarea class="form-control" rows="3" name="expand_system_prompt_ru"><?= htmlspecialchars((string)($s['expand_system_prompt_ru'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Expand Prompt Append EN</label><textarea class="form-control" rows="3" name="expand_user_prompt_append_en"><?= htmlspecialchars((string)($s['expand_user_prompt_append_en'] ?? '')) ?></textarea></div>
                                        <div class="col-md-6"><label class="form-label">Expand Prompt Append RU</label><textarea class="form-control" rows="3" name="expand_user_prompt_append_ru"><?= htmlspecialchars((string)($s['expand_user_prompt_append_ru'] ?? '')) ?></textarea></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="wiz-preview">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-photo-cog"></i> Preview, TG, Image Generation</div>
                                    <div class="row g-3">
                                        <div class="col-md-3"><label class="form-label">Preview Channel</label><select class="form-select" name="preview_channel_enabled" id="preview_channel_enabled"><option value="1" <?= !empty($s['preview_channel_enabled']) ? 'selected' : '' ?>>Enabled</option><option value="0" <?= empty($s['preview_channel_enabled']) ? 'selected' : '' ?>>Disabled</option></select></div>
                                        <div class="col-md-5"><label class="form-label">Preview Chat ID</label><input class="form-control" name="preview_channel_chat_id" id="preview_channel_chat_id" value="<?= htmlspecialchars((string)($s['preview_channel_chat_id'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">CPALNYA Channel</label><select class="form-select" name="preview_public_channel_enabled" id="preview_public_channel_enabled"><option value="1" <?= !empty($s['preview_public_channel_enabled']) ? 'selected' : '' ?>>Enabled</option><option value="0" <?= empty($s['preview_public_channel_enabled']) ? 'selected' : '' ?>>Disabled</option></select></div>
                                        <div class="col-md-4"><label class="form-label">CPALNYA Chat ID</label><input class="form-control" name="preview_public_channel_chat_id" id="preview_public_channel_chat_id" value="<?= htmlspecialchars((string)($s['preview_public_channel_chat_id'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">CPALNYA Bot Token</label><input class="form-control" name="preview_public_channel_bot_token" id="preview_public_channel_bot_token" value="<?= htmlspecialchars((string)($s['preview_public_channel_bot_token'] ?? '')) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">CPALNYA Telegram API</label><input class="form-control" name="preview_public_channel_api_base" id="preview_public_channel_api_base" value="<?= htmlspecialchars((string)($s['preview_public_channel_api_base'] ?? 'https://api.telegram.org')) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Preview Use LLM</label><select class="form-select" name="preview_use_llm"><option value="1" <?= !empty($s['preview_use_llm']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($s['preview_use_llm']) ? 'selected' : '' ?>>No</option></select></div>
                                        <div class="col-md-2"><label class="form-label">Preview LLM Model</label><input class="form-control" name="preview_llm_model" value="<?= htmlspecialchars((string)($s['preview_llm_model'] ?? '')) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Post Min</label><input class="form-control" type="number" name="preview_post_min_words" value="<?= (int)($s['preview_post_min_words'] ?? 70) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Post Max</label><input class="form-control" type="number" name="preview_post_max_words" value="<?= (int)($s['preview_post_max_words'] ?? 220) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Caption Min</label><input class="form-control" type="number" name="preview_caption_min_words" value="<?= (int)($s['preview_caption_min_words'] ?? 26) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Caption Max</label><input class="form-control" type="number" name="preview_caption_max_words" value="<?= (int)($s['preview_caption_max_words'] ?? 80) ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Preview Context Chars</label><input class="form-control" type="number" name="preview_context_chars" value="<?= (int)($s['preview_context_chars'] ?? 14000) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Image Generation</label><select class="form-select" name="preview_image_enabled" id="preview_image_enabled"><option value="1" <?= !empty($s['preview_image_enabled']) ? 'selected' : '' ?>>Enabled</option><option value="0" <?= empty($s['preview_image_enabled']) ? 'selected' : '' ?>>Disabled</option></select></div>
                                        <div class="col-md-4"><label class="form-label">Image Model</label><input class="form-control" name="preview_image_model" id="preview_image_model" value="<?= htmlspecialchars((string)($s['preview_image_model'] ?? '')) ?>"></div>
                                        <div class="col-md-2"><label class="form-label">Image Size</label><input class="form-control" name="preview_image_size" id="preview_image_size" value="<?= htmlspecialchars((string)($s['preview_image_size'] ?? '768x512')) ?>"></div>
                                        <div class="col-12"><div class="alert alert-warning py-2 mb-0">If image previews use a Google model through OpenRouter, geo restrictions can still fail the run even when the main text model is OpenAI. The safe options are: disable image previews or switch the image model to a provider available for your server region.</div></div>
                                        <div class="col-md-4"><label class="form-label">Anchor Enforcement</label><select class="form-select" name="preview_image_anchor_enforced"><option value="1" <?= !empty($s['preview_image_anchor_enforced']) ? 'selected' : '' ?>>Enabled</option><option value="0" <?= empty($s['preview_image_anchor_enforced']) ? 'selected' : '' ?>>Disabled</option></select><div class="wiz-help">Injects mandatory anchors from article: primary concept + operational context.</div></div>
                                        <div class="col-md-4"><label class="form-label">Image Styles</label><textarea class="form-control" rows="2" name="preview_image_style_options"><?= htmlspecialchars(implode("\n", (array)($s['preview_image_style_options'] ?? []))) ?></textarea></div>
                                        <div class="col-md-12"><label class="form-label">Image Color Schemes (key | weight | instruction)</label><textarea class="form-control font-monospace" rows="6" name="image_color_schemes"><?= htmlspecialchars(implode("\n", $colorSchemeLines)) ?></textarea></div>
                                        <div class="col-md-12"><label class="form-label">Image Scene Families (key | weight | label_en | label_ru | instruction)</label><textarea class="form-control font-monospace" rows="7" name="image_scene_families"><?= htmlspecialchars(implode("\n", $sceneFamilyLines)) ?></textarea><div class="wiz-help">Primary semantic family before scenario/composition: characters, infrastructure, nature metaphor, abstract signal, hybrid mix.</div></div>
                                        <div class="col-md-12"><label class="form-label">Image Scenarios (key | weight | label_en | label_ru | instruction)</label><textarea class="form-control font-monospace" rows="9" name="image_scenarios"><?= htmlspecialchars(implode("\n", $scenarioLines)) ?></textarea><div class="wiz-help">Concrete CPA scenes: farm desks, postback debugging, moderation checkpoints, Telegram distribution hubs, source maps and operator workrooms.</div></div>
                                        <div class="col-md-12"><label class="form-label">Image Compositions (key | weight | label_en | label_ru | instruction)</label><textarea class="form-control font-monospace" rows="8" name="image_compositions"><?= htmlspecialchars(implode("\n", $compositionLines)) ?></textarea><div class="wiz-help">Controls scene layout logic: centered, dynamic diagonal, golden ratio, mosaic, etc.</div></div>
                                        <div class="col-12"><label class="form-label">Image Prompt Template</label><textarea class="form-control" rows="4" name="preview_image_prompt_template"><?= htmlspecialchars((string)($s['preview_image_prompt_template'] ?? '')) ?></textarea></div>
                                        <div class="col-12"><label class="form-label">Image Anchor Prompt Append</label><textarea class="form-control" rows="2" name="preview_image_anchor_append"><?= htmlspecialchars((string)($s['preview_image_anchor_append'] ?? '')) ?></textarea><div class="wiz-help">Optional extra guardrail for anchor-driven image prompts.</div></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="wiz-review">
                                <div class="wiz-pane">
                                    <div class="wiz-title"><i class="ti ti-device-floppy"></i> Final Review</div>
                                    <div class="alert alert-info">Use <b>Validate</b>, then press <b>Save Settings</b>.</div>
                                    <div class="row g-2">
                                        <div class="col-md-4"><div class="p-2 border rounded"><b>Provider:</b> <span id="revProvider"><?= htmlspecialchars($provider) ?></span></div></div>
                                        <div class="col-md-4"><div class="p-2 border rounded"><b>Langs:</b> <span id="revLangs"><?= htmlspecialchars(implode(', ', (array)($s['langs'] ?? []))) ?></span></div></div>
                                        <div class="col-md-4"><div class="p-2 border rounded"><b>Word Range:</b> <span id="revWords"><?= (int)($s['word_min'] ?? 2000) ?>-<?= (int)($s['word_max'] ?? 5000) ?></span></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-outline-secondary" id="wizPrev">Prev</button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="wizNext">Next</button>
                                <button class="btn btn-success" type="submit"><i class="ti ti-device-floppy me-1"></i> Save Settings</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-1">Generation Queue</h6>
                <div class="text-muted small">Queue rows are now created per article slot, with random planned time for each publication.</div>
            </div>
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Date</label>
                <input type="date" class="form-control form-control-sm" name="schedule_date" value="<?= htmlspecialchars((string)($scheduleDate ?? '')) ?>">
                <button class="btn btn-sm btn-outline-primary" type="submit">Load</button>
            </form>
        </div>
        <div class="card-body">
            <?php if (empty($hasQueueTable)): ?>
                <div class="alert alert-warning mb-0">Table <code>seo_article_generation_queue</code> not found.</div>
            <?php elseif (empty($queueRows)): ?>
                <div class="text-muted">No schedule rows for <?= htmlspecialchars((string)($scheduleDate ?? '')) ?>.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Campaign / Lang / Slot</th>
                            <th>Planned At</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Run Params</th>
                            <th>Last Result</th>
                            <th>Edit Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ((array)$queueRows as $row): ?>
                            <?php
                            $plannedAtRaw = (string)($row['planned_at'] ?? '');
                            $plannedTime = '';
                            if ($plannedAtRaw !== '') {
                                $plannedTime = substr($plannedAtRaw, 11, 5);
                            }
                            $status = strtolower((string)($row['status'] ?? 'pending'));
                            $badge = 'secondary';
                            if ($status === 'success') { $badge = 'success'; }
                            elseif ($status === 'failed') { $badge = 'danger'; }
                            elseif ($status === 'processing') { $badge = 'warning'; }
                            elseif ($status === 'queued') { $badge = 'info'; }
                            $campaignKey = trim((string)($row['campaign_key'] ?? ''));
                            $lastError = trim((string)($row['last_error'] ?? ''));
                            $lastOutput = trim((string)($row['last_output'] ?? ''));
                            $lastResult = $lastError !== '' ? $lastError : $lastOutput;
                            if (mb_strlen($lastResult) > 220) {
                                $lastResult = mb_substr($lastResult, 0, 220) . '...';
                            }
                            ?>
                            <tr>
                                <td><?= (int)($row['id'] ?? 0) ?></td>
                                <td>
                                    <div><b><?= htmlspecialchars($campaignKey !== '' ? $campaignKey : 'default') ?></b></div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars((string)($row['lang_code'] ?? '')) ?>
                                        <?php if ((int)($row['slot_index'] ?? 0) > 0): ?>
                                            / slot <?= (int)($row['slot_index'] ?? 0) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><code><?= htmlspecialchars($plannedAtRaw) ?></code></td>
                                <td><span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span></td>
                                <td><?= (int)($row['attempts'] ?? 0) ?></td>
                                <td>
                                    <div class="small">max_per_run: <b><?= (int)($row['max_per_run'] ?? 0) ?></b></div>
                                    <div class="small">force: <b><?= !empty($row['force_mode']) ? 'yes' : 'no' ?></b></div>
                                    <div class="small">dry_run: <b><?= !empty($row['dry_run']) ? 'yes' : 'no' ?></b></div>
                                    <div class="small text-muted">exit: <?= (int)($row['last_exit_code'] ?? 0) ?></div>
                                </td>
                                <td style="max-width:420px"><span class="text-muted small"><?= htmlspecialchars($lastResult !== '' ? $lastResult : '-') ?></span></td>
                                <td>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="action" value="update_queue_time">
                                        <input type="hidden" name="queue_id" value="<?= (int)($row['id'] ?? 0) ?>">
                                        <input type="hidden" name="job_date" value="<?= htmlspecialchars((string)($row['job_date'] ?? '')) ?>">
                                        <input type="hidden" name="campaign_key" value="<?= htmlspecialchars((string)($row['campaign_key'] ?? '')) ?>">
                                        <input type="hidden" name="lang_code" value="<?= htmlspecialchars((string)($row['lang_code'] ?? '')) ?>">
                                        <input type="hidden" name="slot_index" value="<?= (int)($row['slot_index'] ?? 0) ?>">
                                        <input type="time" name="planned_time" class="form-control form-control-sm" value="<?= htmlspecialchars($plannedTime) ?>" required>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($hasCronRunsTable)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-1">Slot Schedule Mirror</h6>
                <div class="text-muted small">`seo_article_cron_runs` keeps the actual per-slot publication schedule used by the generator.</div>
            </div>
            <div class="card-body">
                <?php if (empty($scheduleRows)): ?>
                    <div class="text-muted">No legacy slot rows for <?= htmlspecialchars((string)($scheduleDate ?? '')) ?>.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Campaign / Lang / Slot</th>
                                <th>Planned At</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Article</th>
                                <th>Message</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ((array)$scheduleRows as $row): ?>
                                <?php
                                $status = strtolower((string)($row['status'] ?? 'pending'));
                                $badge = 'secondary';
                                if ($status === 'success') { $badge = 'success'; }
                                elseif ($status === 'failed') { $badge = 'danger'; }
                                elseif ($status === 'running') { $badge = 'warning'; }
                                elseif ($status === 'pending') { $badge = 'info'; }
                                $articleSlug = trim((string)($row['article_slug'] ?? ''));
                                $articleTitle = trim((string)($row['article_title'] ?? ''));
                                $articleUrl = $articleSlug !== '' ? ('/examples/article/' . rawurlencode($articleSlug) . '/') : '';
                                ?>
                                <tr>
                                    <td><?= (int)($row['id'] ?? 0) ?></td>
                                    <td>
                                        <b><?= htmlspecialchars((string)($row['campaign_key'] ?? '')) ?></b>
                                        <div class="text-muted small"><?= htmlspecialchars((string)($row['lang_code'] ?? '')) ?> / slot <?= (int)($row['slot_index'] ?? 0) ?></div>
                                    </td>
                                    <td><code><?= htmlspecialchars((string)($row['planned_at'] ?? '')) ?></code></td>
                                    <td><span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span></td>
                                    <td><?= (int)($row['attempts'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($articleUrl !== ''): ?>
                                            <a href="<?= htmlspecialchars($articleUrl) ?>" target="_blank" rel="noopener noreferrer">
                                                #<?= (int)($row['article_id'] ?? 0) ?> <?= htmlspecialchars($articleTitle !== '' ? $articleTitle : $articleSlug) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width:360px"><span class="text-muted small"><?= htmlspecialchars((string)($row['message'] ?? '')) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
(function(){
    const form = document.getElementById('seoWizardForm'); if(!form) return;
    const nav = Array.from(document.querySelectorAll('#wizNav .nav-link'));
    const prev = document.getElementById('wizPrev');
    const next = document.getElementById('wizNext');
    const badge = document.getElementById('wizStepBadge');
    const bar = document.getElementById('wizBar');
    const validateBtn = document.getElementById('wizValidate');
    let step = 0;
    function setStep(i){
        step = Math.max(0, Math.min(nav.length - 1, i));
        nav[step].click();
        if (badge) badge.textContent = 'Step ' + (step + 1) + '/' + nav.length;
        if (bar) bar.style.width = (((step + 1) / nav.length) * 100) + '%';
        if (prev) prev.disabled = step === 0;
        if (next) next.disabled = step === nav.length - 1;
        updateReview();
    }
    function mark(el, ok){ if(!el) return; el.classList.remove('wiz-invalid'); if(!ok) el.classList.add('wiz-invalid'); }
    function vNotEmpty(name){ const el=form.querySelector('[name="'+name+'"]'); const ok=!!el && String(el.value||'').trim()!==''; mark(el,ok); return ok; }
    function validateAll(){
        let ok = true;
        ['daily_min','daily_max','max_per_run','word_min','word_max','openai_base_url','openai_model','openrouter_base_url','openrouter_model'].forEach((n)=>{ if(!vNotEmpty(n)) ok=false; });
        const dailyMin = parseInt(form.querySelector('[name="daily_min"]').value || '0',10);
        const dailyMax = parseInt(form.querySelector('[name="daily_max"]').value || '0',10);
        const wordMin = parseInt(form.querySelector('[name="word_min"]').value || '0',10);
        const wordMax = parseInt(form.querySelector('[name="word_max"]').value || '0',10);
        if (dailyMax < dailyMin) { mark(form.querySelector('[name="daily_min"]'), false); mark(form.querySelector('[name="daily_max"]'), false); ok = false; }
        if (wordMax < wordMin) { mark(form.querySelector('[name="word_min"]'), false); mark(form.querySelector('[name="word_max"]'), false); ok = false; }
        const provider = (form.querySelector('[name="llm_provider"]').value || 'openai');
        if (provider === 'openai' && !vNotEmpty('openai_api_key')) ok = false;
        if (provider === 'openrouter' && !vNotEmpty('openrouter_api_key')) ok = false;
        const imageEnabled = form.querySelector('[name="preview_image_enabled"]').value === '1';
        if (imageEnabled) {
            if (!vNotEmpty('preview_image_model')) ok = false;
            const size = String(form.querySelector('[name="preview_image_size"]').value || '').trim();
            const sizeOk = /^(\d{2,4})x(\d{2,4})$/.test(size);
            mark(form.querySelector('[name="preview_image_size"]'), sizeOk); if (!sizeOk) ok = false;
        }
        const channelEnabled = form.querySelector('[name="preview_channel_enabled"]').value === '1';
        if (channelEnabled && !vNotEmpty('preview_channel_chat_id')) ok = false;
        const publicChannelEnabled = form.querySelector('[name="preview_public_channel_enabled"]').value === '1';
        if (publicChannelEnabled) {
            if (!vNotEmpty('preview_public_channel_chat_id')) ok = false;
            if (!vNotEmpty('preview_public_channel_bot_token')) ok = false;
        }
        const colorLines = String(form.querySelector('[name="image_color_schemes"]').value || '').split(/\r\n|\r|\n/).map(v=>v.trim()).filter(Boolean).length;
        if (colorLines < 1) { mark(form.querySelector('[name="image_color_schemes"]'), false); ok = false; }
        const compositionLines = String(form.querySelector('[name="image_compositions"]').value || '').split(/\r\n|\r|\n/).map(v=>v.trim()).filter(Boolean).length;
        if (compositionLines < 1) { mark(form.querySelector('[name="image_compositions"]'), false); ok = false; }
        return ok;
    }
    function updateReview(){
        const set = (id,v)=>{ const el=document.getElementById(id); if(el) el.textContent=v; };
        const provider = form.querySelector('[name="llm_provider"]').value || 'n/a';
        const langs = String(form.querySelector('[name="langs"]').value||'').split(/\r\n|\r|\n/).map(v=>v.trim()).filter(Boolean).join(', ') || 'n/a';
        const words = (form.querySelector('[name="word_min"]').value||'0') + '-' + (form.querySelector('[name="word_max"]').value||'0');
        set('revProvider', provider); set('revLangs', langs); set('revWords', words);
    }
    if (prev) prev.addEventListener('click', ()=>setStep(step - 1));
    if (next) next.addEventListener('click', ()=>setStep(step + 1));
    if (validateBtn) validateBtn.addEventListener('click', ()=>alert(validateAll() ? 'Validation passed' : 'Validation failed'));
    form.addEventListener('submit', function(e){ if(!validateAll()){ e.preventDefault(); alert('Fix invalid fields before save.'); } });
    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el=>{ el.addEventListener('input', updateReview); el.addEventListener('change', updateReview); });
    setStep(0);
})();
</script>
