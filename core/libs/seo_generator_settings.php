<?php

if (!function_exists('seo_gen_settings_table_name')) {
    function seo_gen_settings_table_name(): string
    {
        return 'seo_generator_settings';
    }
}

if (!function_exists('seo_gen_settings_table_ensure')) {
    function seo_gen_settings_table_ensure(mysqli $db): bool
    {
        $table = seo_gen_settings_table_name();
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            `settings_json` LONGTEXT NOT NULL,
            `updated_by_admin_id` INT UNSIGNED DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('seo_gen_settings_parse_lines')) {
    function seo_gen_settings_parse_lines(string $raw, int $max = 200): array
    {
        $out = [];
        $seen = [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if (!is_array($lines)) {
            return $out;
        }
        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }
            if (strpos($line, '#') === 0) {
                continue;
            }
            $key = mb_strtolower($line);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $line;
            if (count($out) >= $max) {
                break;
            }
        }
        return $out;
    }
}

if (!function_exists('seo_gen_settings_default')) {
    function seo_gen_default_campaigns(): array
    {
        return [
            'journal' => [
                'key' => 'journal',
                'title' => 'Journal',
                'title_ru' => 'Журнал',
                'description' => 'Editorial stream around affiliate strategy, source trends, platform shifts and operating models.',
                'description_ru' => 'Редакционный поток про стратегию арбитража, изменения источников, рыночные сдвиги и операционные модели.',
                'material_section' => 'journal',
                'enabled' => true,
                'daily_min' => 4,
                'daily_max' => 6,
                'max_per_run' => 2,
                'word_min' => 1800,
                'word_max' => 3200,
                'seed_salt_suffix' => 'journal',
                'styles_en' => ['trend briefing', 'editorial breakdown', 'market memo', 'operating analysis'],
                'styles_ru' => ['редакционный разбор', 'аналитическая записка', 'trend briefing', 'стратегический обзор'],
                'clusters_en' => [
                    'meta ads resilience and account farming',
                    'tiktok shop and short-form affiliate funnels',
                    'telegram mini apps and community retention',
                    'ai creatives workflow and compliance',
                    'tracker stacks attribution and signal loss',
                    'nutra finance igaming and crypto demand shifts',
                ],
                'clusters_ru' => [
                    'Meta Ads в 2026: фарм, BM-устойчивость и антибан',
                    'TikTok и short-form воронки под affiliate-офферы',
                    'Telegram Mini Apps, комьюнити и ретеншн в арбитраже',
                    'AI-креативы, compliance и контроль промпт-пайплайна',
                    'трекеры, атрибуция и потеря сигналов после privacy-сдвигов',
                    'сдвиги спроса по nutra, finance, iGaming и crypto в 2026',
                ],
                'article_structures_en' => [
                    'Trend summary -> what changed -> who wins -> operating implications -> checklist',
                    'Source shift -> risks -> adaptation patterns -> examples -> next steps',
                    'Myth vs reality -> evidence -> playbook signals -> conclusion',
                ],
                'article_structures_ru' => [
                    'Краткий тренд -> что изменилось -> кто выигрывает -> операционные выводы -> чеклист',
                    'Сдвиг источника -> риски -> адаптация -> примеры -> следующие шаги',
                    'Миф и реальность -> факты -> рабочие сигналы -> вывод',
                ],
                'article_system_prompt_en' => '',
                'article_system_prompt_ru' => '',
                'article_user_prompt_append_en' => 'Focus on 2026 affiliate traffic trends, operator decisions, signal quality and practical strategic implications.',
                'article_user_prompt_append_ru' => 'Фокус на трендах арбитража трафика 2026, операционных решениях команды, качестве сигналов и прикладных стратегических выводах.',
            ],
            'playbooks' => [
                'key' => 'playbooks',
                'title' => 'Playbooks',
                'title_ru' => 'HowTo',
                'description' => 'Case studies, how-to guides, troubleshooting notes and reusable operational solutions.',
                'description_ru' => 'Кейсы, how-to гайды, troubleshooting-материалы и переиспользуемые рабочие решения.',
                'material_section' => 'playbooks',
                'enabled' => true,
                'daily_min' => 4,
                'daily_max' => 8,
                'max_per_run' => 3,
                'word_min' => 1600,
                'word_max' => 3600,
                'seed_salt_suffix' => 'playbooks',
                'styles_en' => ['step-by-step tutorial', 'troubleshooting guide', 'case note', 'operator playbook'],
                'styles_ru' => ['пошаговый how-to', 'гайд по устранению проблем', 'операционный playbook', 'технический кейс'],
                'clusters_en' => [
                    'facebook farm setup and warmup',
                    'cloaking moderation-safe routing',
                    'tracker postback templates and debugging',
                    'anti-detect browser operations and team SOP',
                    'creative testing matrix and iteration loops',
                    'payment domains hosting and landing recovery',
                ],
                'clusters_ru' => [
                    'настройка и прогрев Facebook farm',
                    'cloaking и безопасная маршрутизация под модерацию',
                    'tracker postback-шаблоны, макросы и отладка',
                    'anti-detect браузеры, роли команды и SOP',
                    'матрица тестирования креативов и цикл итераций',
                    'платежки, домены, хостинг и восстановление лендингов',
                ],
                'article_structures_en' => [
                    'Problem -> prerequisites -> exact steps -> failure cases -> verification',
                    'Goal -> stack -> implementation -> screenshots/code -> rollback plan',
                    'Case context -> setup -> metrics -> bottlenecks -> reusable template',
                ],
                'article_structures_ru' => [
                    'Проблема -> prerequisites -> точные шаги -> типовые фейлы -> проверка',
                    'Цель -> стек -> внедрение -> код/настройки -> rollback-план',
                    'Контекст кейса -> сетап -> метрики -> узкие места -> переиспользуемый шаблон',
                ],
                'article_system_prompt_en' => '',
                'article_system_prompt_ru' => '',
                'article_user_prompt_append_en' => 'Favor practical implementation, troubleshooting depth, reusable snippets, SOPs and exact operator steps.',
                'article_user_prompt_append_ru' => 'Смещай акцент в сторону практической реализации, troubleshooting, переиспользуемых фрагментов, SOP и точных шагов оператора.',
            ],
        ];
    }

    function seo_gen_normalize_campaigns($raw): array
    {
        $defaults = seo_gen_default_campaigns();
        $raw = is_array($raw) ? $raw : [];
        $out = [];
        foreach ($defaults as $key => $default) {
            $row = array_merge($default, is_array($raw[$key] ?? null) ? $raw[$key] : []);
            $row['key'] = $key;
            $row['material_section'] = in_array((string)($row['material_section'] ?? $key), ['journal', 'playbooks'], true)
                ? (string)$row['material_section']
                : $key;
            $row['enabled'] = !empty($row['enabled']);
            $row['daily_min'] = max(1, min(24, (int)($row['daily_min'] ?? $default['daily_min'])));
            $row['daily_max'] = max($row['daily_min'], min(48, (int)($row['daily_max'] ?? $default['daily_max'])));
            $row['max_per_run'] = max(1, min(12, (int)($row['max_per_run'] ?? $default['max_per_run'])));
            $row['word_min'] = max(600, min(12000, (int)($row['word_min'] ?? $default['word_min'])));
            $row['word_max'] = max($row['word_min'], min(20000, (int)($row['word_max'] ?? $default['word_max'])));
            $row['seed_salt_suffix'] = trim((string)($row['seed_salt_suffix'] ?? $default['seed_salt_suffix']));
            if ($row['seed_salt_suffix'] === '') {
                $row['seed_salt_suffix'] = $default['seed_salt_suffix'];
            }
            foreach (['styles_en', 'styles_ru', 'clusters_en', 'clusters_ru', 'article_structures_en', 'article_structures_ru'] as $listKey) {
                $row[$listKey] = seo_gen_settings_parse_lines(implode("\n", (array)($row[$listKey] ?? [])), 120);
                if (empty($row[$listKey])) {
                    $row[$listKey] = (array)$default[$listKey];
                }
            }
            foreach (['title', 'title_ru', 'description', 'description_ru', 'article_system_prompt_en', 'article_system_prompt_ru', 'article_user_prompt_append_en', 'article_user_prompt_append_ru'] as $textKey) {
                $row[$textKey] = trim((string)($row[$textKey] ?? $default[$textKey] ?? ''));
            }
            $out[$key] = $row;
        }
        return $out;
    }

    function seo_gen_settings_default(): array
    {
        return [
            'enabled' => true,
            'langs' => ['en', 'ru'],
            'domain_host' => '',
            'domain_host_en' => 'portcore.online',
            'domain_host_ru' => 'portcore.ru',
            'author_name' => 'Portcore Team',
            'daily_min' => 1,
            'daily_max' => 3,
            'max_per_run' => 2,
            'word_min' => 2000,
            'word_max' => 5000,
            'today_first_delay_min' => 15,
            'auto_expand_retries' => 1,
            'expand_context_chars' => 7000,
            'prompt_version' => 'seo-cron-v1',
            'seed_salt' => 'portcore-seo-articles',
            'narrative_person' => 'first_person_singular',
            'tone_variability' => 60,
            'portfolio_bofu_weight' => 30,
            'portfolio_mofu_weight' => 30,
            'portfolio_authority_weight' => 20,
            'portfolio_case_weight' => 10,
            'portfolio_product_weight' => 10,
            'notify_schedule' => false,
            'notify_daily_schedule' => true,
            'indexnow_enabled' => false,
            'indexnow_key' => '',
            'indexnow_key_location' => '',
            'indexnow_endpoint' => '',
            'indexnow_hosts' => ['portcore.ru', 'portcore.online'],
            'indexnow_ping_on_publish' => true,
            'indexnow_submit_limit' => 100,
            'indexnow_retry_delay_minutes' => 15,

            'llm_provider' => 'openai',
            'openai_api_key' => '',
            'openai_base_url' => 'https://api.openai.com/v1',
            'openai_model' => 'gpt-4.1-mini',
            'openai_timeout' => 120,
            'openai_headers' => [],

            'openrouter_api_key' => '',
            'openrouter_base_url' => 'https://openrouter.ai/api/v1',
            'openrouter_model' => 'openai/gpt-4o-2024-11-20',

            'openai_proxy_enabled' => false,
            'openai_proxy_host' => '',
            'openai_proxy_port' => 0,
            'openai_proxy_type' => 'http',
            'openai_proxy_username' => '',
            'openai_proxy_password' => '',
            'openai_proxy_pool_enabled' => false,
            'openai_proxy_pool' => [],

            'topic_analysis_enabled' => true,
            'topic_analysis_limit' => 120,
            'topic_analysis_system_prompt' => '',
            'topic_analysis_user_prompt_append' => '',

            'styles_en' => ['technical guide', 'playbook', 'architecture note', 'implementation tutorial'],
            'styles_ru' => ['экспертный', 'пошаговый', 'практический playbook', 'аналитический'],
            'clusters_en' => [
                'scalable B2B SaaS architecture patterns',
                'high-availability microservices and reliability engineering',
                'secure API integration for enterprise systems',
                'observability, metrics and operational excellence',
                'DevOps and CI/CD strategies for high-load products',
                'business process automation and analytics platforms',
            ],
            'clusters_ru' => [
                'GeoIP и antifraud в login/signup flow',
                'снижение chargeback через гео-сигналы',
                'risk-based authentication и step-up KYC',
                'детект multi-account и abuse patterns',
                'снижение false positive в антифроде',
                'backend интеграция GeoIP в PHP/Node/Python',
            ],
            'intent_verticals_en' => [
                'fintech compliance platforms',
                'high-load streaming products',
                'enterprise ERP and CRM integration estates',
                'marketplace trust and safety stacks',
                'multi-tenant B2B SaaS platforms',
                'real-time analytics products',
            ],
            'intent_verticals_ru' => [
                'финтех-платформы с требованиями комплаенса',
                'высоконагруженные стриминговые продукты',
                'ландшафты интеграций enterprise ERP и CRM',
                'контуры trust and safety для маркетплейсов',
                'мультитенантные B2B SaaS-платформы',
                'продукты real-time аналитики',
            ],
            'intent_scenarios_en' => [
                'latency troubleshooting under production load',
                'migration from monolith to modular services',
                'Kubernetes multi-cluster rollout',
                'cost optimization at scale',
                'incident recovery and resilience hardening',
                'backward-compatible API version transition',
            ],
            'intent_scenarios_ru' => [
                'диагностика задержек под production-нагрузкой',
                'миграция с монолита на модульные сервисы',
                'развертывание в Kubernetes multi-cluster',
                'оптимизация затрат на масштабе',
                'восстановление после инцидента и hardening устойчивости',
                'переход API-версий с обратной совместимостью',
            ],
            'intent_objectives_en' => [
                'reduce p95 latency without breaking reliability',
                'strengthen policy enforcement and auditability',
                'decrease error rates during peak traffic',
                'improve change-failure rate in releases',
                'cut infra spend while preserving SLO',
                'shorten time-to-debug in incident response',
            ],
            'intent_objectives_ru' => [
                'снизить p95 latency без потери надежности',
                'усилить policy enforcement и auditability',
                'снизить долю ошибок в пиковый трафик',
                'улучшить change-failure-rate в релизах',
                'сократить инфраструктурные затраты при сохранении SLO',
                'сократить время диагностики в incident response',
            ],
            'intent_constraints_en' => [
                'strict compliance and audit requirements',
                'multi-region traffic variability',
                'legacy integration dependencies',
                'limited engineering bandwidth',
                'SLA and contractual uptime targets',
                'complex stakeholder governance',
            ],
            'intent_constraints_ru' => [
                'жесткие требования комплаенса и аудита',
                'вариативность multi-region трафика',
                'зависимости от legacy-интеграций',
                'ограниченная пропускная способность инженерной команды',
                'SLA-обязательства и контрактные цели по аптайму',
                'сложный stakeholder-governance',
            ],
            'intent_artifacts_en' => [
                'implementation playbook',
                'troubleshooting guide',
                'migration blueprint',
                'operations runbook',
                'decision memo',
                'architecture checklist',
            ],
            'intent_artifacts_ru' => [
                'практический playbook внедрения',
                'гайд по troubleshooting',
                'blueprint миграции',
                'operations runbook',
                'decision memo',
                'чеклист архитектурной готовности',
            ],
            'intent_outcomes_en' => [
                'faster incident MTTR and fewer regressions',
                'better conversion and lower operational risk',
                'higher delivery throughput with stable quality',
                'improved reliability without runaway cloud costs',
                'clearer governance with measurable technical KPIs',
                'stronger retention through platform stability',
            ],
            'intent_outcomes_ru' => [
                'быстрее incident MTTR и меньше регрессий',
                'лучше конверсия и ниже операционный риск',
                'выше delivery throughput при стабильном качестве',
                'выше надежность без неконтролируемого роста cloud-затрат',
                'более прозрачный governance с измеримыми техническими KPI',
                'лучше удержание за счет стабильности платформы',
            ],
            'service_focus_en' => [
                'business websites and conversion architecture',
                'corporate portals and account areas',
                'CMS and CRM integration',
                'workflow automation and bots',
                'MVP delivery and validation',
                'web application architecture audits',
                'technical SEO implementation',
                'AI-assisted engineering workflows',
            ],
            'service_focus_ru' => [
                'разработка сайтов под коммерческие задачи',
                'корпоративные порталы и личные кабинеты',
                'интеграции CMS CRM и учетных систем',
                'автоматизация процессов и разработка ботов',
                'MVP запуск и валидация',
                'архитектурный аудит веб-приложений',
                'техническое SEO для коммерческих страниц',
                'ИИ в разработке и проектировании',
            ],
            'forbidden_topics_en' => [
                'competitor brand comparisons',
                'vendor ranking lists',
            ],
            'forbidden_topics_ru' => [
                'сравнение брендов и конкурентов',
                'рейтинги вендоров',
            ],
            'article_structures_en' => [
                'Introduction -> FAQ grid -> Cases -> Code examples -> Conclusion',
                'Problem framing -> Constraints -> Solution architecture -> Implementation steps -> Checklist',
                'Business context -> Risk scenarios -> Technical deep dive -> Anti-patterns -> Summary',
                'Quick start -> Minimal code path -> Production hardening -> Monitoring -> Next steps',
                'Myth vs reality -> Evidence -> Practical approach -> Reference snippets -> Wrap-up',
                'Before/after comparison -> Migration steps -> Validation -> Rollback plan -> Conclusion',
                'Use case map -> Decision matrix -> Integration blueprint -> QA checklist -> CTA',
                'Incident postmortem style -> Root causes -> Fix design -> Code samples -> Prevention',
                'Playbook format -> Step 1..N -> Metrics -> Common failures -> Final recommendations',
                'Executive summary -> Technical appendix -> Security notes -> Testing strategy -> Conclusion',
            ],
            'article_structures_ru' => [
                'Вступление -> Сетка вопрос-ответ -> Кейсы -> Примеры кода -> Заключение',
                'Описание проблемы -> Ограничения -> Архитектура решения -> Шаги внедрения -> Чеклист',
                'Бизнес-контекст -> Сценарии рисков -> Технический разбор -> Антипаттерны -> Итоги',
                'Быстрый старт -> Минимальный код -> Усиление для production -> Мониторинг -> Следующие шаги',
                'Мифы и реальность -> Факты -> Практический подход -> Сниппеты -> Вывод',
                'До/после -> План миграции -> Валидация -> План отката -> Заключение',
                'Карта use-case -> Матрица решений -> Blueprint интеграции -> QA-чеклист -> CTA',
                'Формат постмортема -> Корневые причины -> Дизайн фикса -> Примеры кода -> Профилактика',
                'Playbook -> Шаг 1..N -> Метрики -> Частые ошибки -> Рекомендации',
                'Резюме для бизнеса -> Техническое приложение -> Security-нюансы -> Тест-стратегия -> Заключение',
            ],
            'moods' => [
                ['key' => 'technical', 'weight' => 1.0, 'label_en' => 'technical article', 'label_ru' => 'техническая статья'],
                ['key' => 'b2b_oriented', 'weight' => 0.9, 'label_en' => 'B2B-oriented', 'label_ru' => 'b2b ориентированная'],
                ['key' => 'philosophical', 'weight' => 0.35, 'label_en' => 'philosophical', 'label_ru' => 'философская'],
                ['key' => 'scientific', 'weight' => 0.7, 'label_en' => 'scientific', 'label_ru' => 'научная'],
                ['key' => 'case_with_examples', 'weight' => 0.85, 'label_en' => 'case study with examples', 'label_ru' => 'кейс с примерами'],
                ['key' => 'historical_entertaining', 'weight' => 0.2, 'label_en' => 'historical-entertaining', 'label_ru' => 'историческо-развлекательная'],
            ],

            'article_system_prompt_en' => '',
            'article_system_prompt_ru' => '',
            'article_user_prompt_append_en' => '',
            'article_user_prompt_append_ru' => '',

            'expand_system_prompt_en' => '',
            'expand_system_prompt_ru' => '',
            'expand_user_prompt_append_en' => '',
            'expand_user_prompt_append_ru' => '',

            'preview_channel_enabled' => false,
            'preview_channel_chat_id' => '',
            'preview_post_max_words' => 220,
            'preview_caption_max_words' => 80,
            'preview_post_min_words' => 70,
            'preview_caption_min_words' => 26,
            'preview_use_llm' => true,
            'preview_llm_model' => '',
            'preview_context_chars' => 14000,

            'preview_image_enabled' => false,
            'preview_image_model' => '',
            'preview_image_size' => '768x512',
            'preview_image_anchor_enforced' => true,
            'preview_image_anchor_append' => '',
            'preview_image_style_options' => ['schematic', 'realistic', 'abstract', 'moody'],
            'image_color_schemes' => [
                ['key' => 'dark', 'weight' => 1.0, 'instruction' => 'Dark cinematic palette, deep shadows, high contrast accents.'],
                ['key' => 'light', 'weight' => 0.9, 'instruction' => 'Light clean palette, high readability, soft contrast.'],
                ['key' => 'colordull', 'weight' => 0.6, 'instruction' => 'Muted desaturated palette, restrained color intensity.'],
                ['key' => 'noir', 'weight' => 0.45, 'instruction' => 'Noir monochrome leaning palette, dramatic lighting.'],
                ['key' => 'neon', 'weight' => 0.35, 'instruction' => 'Neon cyber palette with glowing accents on dark base.'],
                ['key' => 'pastel', 'weight' => 0.35, 'instruction' => 'Pastel palette, soft gradients, low harshness.'],
                ['key' => 'teal_orange', 'weight' => 0.55, 'instruction' => 'Teal and orange blockbuster palette with balanced warm/cool contrast.'],
                ['key' => 'earthy', 'weight' => 0.5, 'instruction' => 'Earthy natural palette: clay, olive, sand, and low saturation browns.'],
                ['key' => 'ice_blue', 'weight' => 0.48, 'instruction' => 'Cold ice-blue palette with crisp highlights and restrained warmth.'],
                ['key' => 'sunset', 'weight' => 0.5, 'instruction' => 'Sunset palette with amber, coral, and magenta gradients.'],
                ['key' => 'duotone', 'weight' => 0.42, 'instruction' => 'Strong duotone palette limited to two dominant colors plus neutrals.'],
                ['key' => 'monochrome', 'weight' => 0.44, 'instruction' => 'Monochrome palette with tonal depth and controlled contrast.'],
                ['key' => 'vaporwave', 'weight' => 0.34, 'instruction' => 'Vaporwave-inspired palette: pink, cyan, and retro glow atmosphere.'],
                ['key' => 'industrial', 'weight' => 0.46, 'instruction' => 'Industrial palette with graphite, steel blue, and warning accent tones.'],
                ['key' => 'forest', 'weight' => 0.43, 'instruction' => 'Forest palette with deep greens, moss, and subtle amber highlights.'],
                ['key' => 'high_key', 'weight' => 0.4, 'instruction' => 'High-key bright palette with airy whites and gentle contrast edges.'],
            ],
            'image_compositions' => [
                ['key' => 'centered', 'weight' => 1.0, 'label_en' => 'Centered', 'label_ru' => 'Центированная', 'instruction' => 'Centered focal composition with clear subject priority.'],
                ['key' => 'dynamic_diagonal', 'weight' => 0.9, 'label_en' => 'Dynamic diagonal', 'label_ru' => 'Динамическая', 'instruction' => 'Strong diagonal flow with dynamic motion and tension.'],
                ['key' => 'broken_reflection', 'weight' => 0.45, 'label_en' => 'Broken reflection', 'label_ru' => 'Ломаное отражение', 'instruction' => 'Fragmented mirrored composition, asymmetry with reflective motifs.'],
                ['key' => 'golden_ratio', 'weight' => 0.75, 'label_en' => 'Golden ratio', 'label_ru' => 'Золотое сечение', 'instruction' => 'Golden ratio based layout for balanced visual hierarchy.'],
                ['key' => 'mosaic', 'weight' => 0.55, 'label_en' => 'Mosaic', 'label_ru' => 'Мозаика', 'instruction' => 'Mosaic modular blocks composition with structured segmentation.'],
                ['key' => 'expressionist', 'weight' => 0.38, 'label_en' => 'Expressionist', 'label_ru' => 'Экспрессия', 'instruction' => 'Expressive composition with dramatic rhythm and emotional contrast.'],
                ['key' => 'rule_of_thirds', 'weight' => 0.82, 'label_en' => 'Rule of thirds', 'label_ru' => 'Правило третей', 'instruction' => 'Rule-of-thirds placement with off-center focal points.'],
                ['key' => 'symmetrical', 'weight' => 0.62, 'label_en' => 'Symmetrical', 'label_ru' => 'Симметрия', 'instruction' => 'Symmetrical composition with strong vertical axis and balance.'],
                ['key' => 'asymmetrical_balance', 'weight' => 0.66, 'label_en' => 'Asymmetrical balance', 'label_ru' => 'Асимметричный баланс', 'instruction' => 'Asymmetrical but balanced layout with weighted visual masses.'],
                ['key' => 'radial_focus', 'weight' => 0.41, 'label_en' => 'Radial focus', 'label_ru' => 'Радиальный фокус', 'instruction' => 'Radial composition radiating from central core or hotspot.'],
                ['key' => 'spiral_flow', 'weight' => 0.34, 'label_en' => 'Spiral flow', 'label_ru' => 'Спираль', 'instruction' => 'Spiral flow guiding eye through layered content depth.'],
                ['key' => 'depth_layers', 'weight' => 0.64, 'label_en' => 'Depth layers', 'label_ru' => 'Глубинные слои', 'instruction' => 'Foreground-midground-background layering for spatial depth.'],
                ['key' => 'split_screen', 'weight' => 0.4, 'label_en' => 'Split screen', 'label_ru' => 'Разделенный экран', 'instruction' => 'Split-screen composition contrasting two states or scenarios.'],
                ['key' => 'timeline_sequence', 'weight' => 0.37, 'label_en' => 'Timeline sequence', 'label_ru' => 'Последовательность', 'instruction' => 'Sequential timeline composition with progressive narrative.'],
                ['key' => 'grid_modular', 'weight' => 0.58, 'label_en' => 'Modular grid', 'label_ru' => 'Модульная сетка', 'instruction' => 'Strict modular grid composition with aligned structural blocks.'],
                ['key' => 'minimal_negative_space', 'weight' => 0.5, 'label_en' => 'Minimal negative space', 'label_ru' => 'Минимализм', 'instruction' => 'Minimal composition with deliberate negative space dominance.'],
                ['key' => 'cinematic_wide', 'weight' => 0.48, 'label_en' => 'Cinematic wide', 'label_ru' => 'Кинематографичный wide', 'instruction' => 'Cinematic wide framing with panoramic storytelling emphasis.'],
                ['key' => 'isometric', 'weight' => 0.35, 'label_en' => 'Isometric', 'label_ru' => 'Изометрия', 'instruction' => 'Isometric composition with technical 3D-like perspective.'],
                ['key' => 'collage_data', 'weight' => 0.33, 'label_en' => 'Data collage', 'label_ru' => 'Коллаж данных', 'instruction' => 'Data-collage composition blending charts, signals and abstract forms.'],
                ['key' => 'vortex_tension', 'weight' => 0.29, 'label_en' => 'Vortex tension', 'label_ru' => 'Вихрь', 'instruction' => 'Vortex-like composition creating directional pull and tension.'],
            ],
            'image_scene_families' => [
                ['key' => 'characters', 'weight' => 1.0, 'label_en' => 'Characters', 'label_ru' => 'Персонажи', 'instruction' => 'Human-centered scene with business actors and clear decision context.'],
                ['key' => 'infrastructure_tech', 'weight' => 0.9, 'label_en' => 'Infrastructure & Tech', 'label_ru' => 'Инфраструктура и техника', 'instruction' => 'Systems, APIs, networks and service components as primary subject.'],
                ['key' => 'nature_metaphor', 'weight' => 0.45, 'label_en' => 'Nature metaphor', 'label_ru' => 'Природная метафора', 'instruction' => 'Natural metaphor adapted to risk flow and system behavior.'],
                ['key' => 'abstract_signal', 'weight' => 0.55, 'label_en' => 'Abstract signal', 'label_ru' => 'Абстрактный сигнал', 'instruction' => 'Signal-driven abstract composition with strong semantic anchors.'],
                ['key' => 'hybrid_mix', 'weight' => 0.7, 'label_en' => 'Hybrid mix', 'label_ru' => 'Гибридный микс', 'instruction' => 'Balanced mix of people, systems and signal motifs.'],
            ],
            'preview_image_prompt_template' => 'Create a {{image_style}} editorial hero illustration for article "{{title}}" in {{lang}} language. Theme: B2B systems architecture, product engineering, security, reliability, analytics. No text, no logos, 16:9.',
            'campaigns' => seo_gen_default_campaigns(),
        ];
    }
}

if (!function_exists('seo_gen_settings_normalize')) {
    function seo_gen_settings_normalize(array $raw): array
    {
        $defaults = seo_gen_settings_default();
        $settings = array_merge($defaults, $raw);

        $settings['enabled'] = (bool)$settings['enabled'];
        $settings['topic_analysis_enabled'] = (bool)$settings['topic_analysis_enabled'];
        $settings['daily_min'] = max(1, min(24, (int)$settings['daily_min']));
        $settings['daily_max'] = max($settings['daily_min'], min(48, (int)$settings['daily_max']));
        $settings['max_per_run'] = max(1, min(20, (int)$settings['max_per_run']));
        $settings['word_min'] = max(400, min(12000, (int)$settings['word_min']));
        $settings['word_max'] = max($settings['word_min'], min(20000, (int)$settings['word_max']));
        $settings['today_first_delay_min'] = max(1, min(360, (int)$settings['today_first_delay_min']));
        $settings['auto_expand_retries'] = max(0, min(8, (int)$settings['auto_expand_retries']));
        $settings['expand_context_chars'] = max(1000, min(50000, (int)$settings['expand_context_chars']));
        $settings['openai_timeout'] = max(20, min(600, (int)$settings['openai_timeout']));
        $settings['topic_analysis_limit'] = max(20, min(300, (int)$settings['topic_analysis_limit']));
        $settings['narrative_person'] = strtolower(trim((string)($settings['narrative_person'] ?? 'first_person_singular')));
        if (!in_array($settings['narrative_person'], ['first_person_singular', 'first_person_plural', 'neutral'], true)) {
            $settings['narrative_person'] = 'first_person_singular';
        }
        $settings['tone_variability'] = max(0, min(100, (int)($settings['tone_variability'] ?? 60)));
        $settings['portfolio_bofu_weight'] = max(0, min(1000, (int)($settings['portfolio_bofu_weight'] ?? 30)));
        $settings['portfolio_mofu_weight'] = max(0, min(1000, (int)($settings['portfolio_mofu_weight'] ?? 30)));
        $settings['portfolio_authority_weight'] = max(0, min(1000, (int)($settings['portfolio_authority_weight'] ?? 20)));
        $settings['portfolio_case_weight'] = max(0, min(1000, (int)($settings['portfolio_case_weight'] ?? 10)));
        $settings['portfolio_product_weight'] = max(0, min(1000, (int)($settings['portfolio_product_weight'] ?? 10)));
        $portfolioWeightSum = (int)$settings['portfolio_bofu_weight']
            + (int)$settings['portfolio_mofu_weight']
            + (int)$settings['portfolio_authority_weight']
            + (int)$settings['portfolio_case_weight']
            + (int)$settings['portfolio_product_weight'];
        if ($portfolioWeightSum <= 0) {
            $settings['portfolio_bofu_weight'] = 30;
            $settings['portfolio_mofu_weight'] = 30;
            $settings['portfolio_authority_weight'] = 20;
            $settings['portfolio_case_weight'] = 10;
            $settings['portfolio_product_weight'] = 10;
        }
        $settings['preview_image_anchor_enforced'] = (bool)($settings['preview_image_anchor_enforced'] ?? true);
        $settings['preview_image_anchor_append'] = trim((string)($settings['preview_image_anchor_append'] ?? ''));
        $settings['indexnow_enabled'] = (bool)($settings['indexnow_enabled'] ?? false);
        $settings['indexnow_key'] = trim((string)($settings['indexnow_key'] ?? ''));
        $settings['indexnow_key_location'] = trim((string)($settings['indexnow_key_location'] ?? ''));
        $settings['indexnow_endpoint'] = trim((string)($settings['indexnow_endpoint'] ?? ''));
        $settings['indexnow_ping_on_publish'] = array_key_exists('indexnow_ping_on_publish', $settings) ? (bool)$settings['indexnow_ping_on_publish'] : true;
        $settings['indexnow_submit_limit'] = max(1, min(500, (int)($settings['indexnow_submit_limit'] ?? 100)));
        $settings['indexnow_retry_delay_minutes'] = max(1, min(1440, (int)($settings['indexnow_retry_delay_minutes'] ?? 15)));

        $settings['llm_provider'] = strtolower(trim((string)$settings['llm_provider']));
        if (!in_array($settings['llm_provider'], ['openai', 'openrouter'], true)) {
            $settings['llm_provider'] = 'openai';
        }
        $settings['openai_base_url'] = trim((string)$settings['openai_base_url']);
        $settings['openrouter_base_url'] = trim((string)$settings['openrouter_base_url']);
        $settings['openai_model'] = trim((string)$settings['openai_model']);
        $settings['openrouter_model'] = trim((string)$settings['openrouter_model']);
        $settings['openai_api_key'] = trim((string)$settings['openai_api_key']);
        $settings['openrouter_api_key'] = trim((string)$settings['openrouter_api_key']);
        $settings['domain_host'] = strtolower(trim((string)($settings['domain_host'] ?? '')));
        $settings['domain_host'] = preg_replace('/^www\./i', '', $settings['domain_host']);
        $settings['domain_host_en'] = strtolower(trim((string)($settings['domain_host_en'] ?? '')));
        $settings['domain_host_en'] = preg_replace('/^www\./i', '', $settings['domain_host_en']);
        $settings['domain_host_ru'] = strtolower(trim((string)($settings['domain_host_ru'] ?? '')));
        $settings['domain_host_ru'] = preg_replace('/^www\./i', '', $settings['domain_host_ru']);
        if ($settings['domain_host_en'] === '') {
            $settings['domain_host_en'] = 'portcore.online';
        }
        if ($settings['domain_host_ru'] === '') {
            $settings['domain_host_ru'] = 'portcore.ru';
        }

        $settings['langs'] = array_values(array_unique(array_filter(array_map(
            static function ($x): string {
                $x = strtolower(trim((string)$x));
                return in_array($x, ['en', 'ru'], true) ? $x : '';
            },
            (array)$settings['langs']
        ))));
        if (empty($settings['langs'])) {
            $settings['langs'] = ['en', 'ru'];
        }

        $settings['styles_en'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['styles_en']), 120);
        $settings['styles_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['styles_ru']), 120);
        $settings['clusters_en'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['clusters_en']), 120);
        $settings['clusters_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['clusters_ru']), 120);
        $settings['intent_verticals_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_verticals_en'] ?? [])), 150);
        $settings['intent_verticals_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_verticals_ru'] ?? [])), 150);
        $settings['intent_scenarios_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_scenarios_en'] ?? [])), 150);
        $settings['intent_scenarios_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_scenarios_ru'] ?? [])), 150);
        $settings['intent_objectives_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_objectives_en'] ?? [])), 150);
        $settings['intent_objectives_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_objectives_ru'] ?? [])), 150);
        $settings['intent_constraints_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_constraints_en'] ?? [])), 150);
        $settings['intent_constraints_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_constraints_ru'] ?? [])), 150);
        $settings['intent_artifacts_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_artifacts_en'] ?? [])), 150);
        $settings['intent_artifacts_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_artifacts_ru'] ?? [])), 150);
        $settings['intent_outcomes_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_outcomes_en'] ?? [])), 150);
        $settings['intent_outcomes_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_outcomes_ru'] ?? [])), 150);
        $settings['service_focus_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['service_focus_en'] ?? [])), 150);
        $settings['service_focus_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['service_focus_ru'] ?? [])), 150);
        $settings['forbidden_topics_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['forbidden_topics_en'] ?? [])), 150);
        $settings['forbidden_topics_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['forbidden_topics_ru'] ?? [])), 150);
        $settings['article_structures_en'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['article_structures_en']), 120);
        $settings['article_structures_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['article_structures_ru']), 120);
        $settings['openai_headers'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['openai_headers']), 100);
        $settings['openai_proxy_pool'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['openai_proxy_pool']), 300);
        $settings['preview_image_style_options'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['preview_image_style_options']), 60);
        $settings['campaigns'] = seo_gen_normalize_campaigns($settings['campaigns'] ?? []);
        $indexNowHosts = seo_gen_settings_parse_lines(implode("\n", (array)($settings['indexnow_hosts'] ?? [])), 40);
        if (empty($indexNowHosts)) {
            $indexNowHosts = (array)($defaults['indexnow_hosts'] ?? []);
        }
        $settings['indexnow_hosts'] = array_values(array_unique(array_filter(array_map(
            static function ($host): string {
                $host = strtolower(trim((string)$host));
                $host = preg_replace('/^https?:\/\//i', '', $host);
                $host = trim($host, '/');
                if (strpos($host, ':') !== false) {
                    $host = explode(':', $host, 2)[0];
                }
                $host = preg_replace('/^www\./i', '', $host);
                if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
                    return '';
                }
                return trim($host, '.');
            },
            $indexNowHosts
        ))));
        $colorSchemes = [];
        foreach ((array)($settings['image_color_schemes'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = strtolower(trim((string)($row['key'] ?? '')));
            if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
                continue;
            }
            $colorSchemes[] = [
                'key' => $key,
                'weight' => max(0.01, min(5.0, (float)($row['weight'] ?? 1.0))),
                'instruction' => trim((string)($row['instruction'] ?? $key)),
            ];
            if (count($colorSchemes) >= 80) {
                break;
            }
        }
        if (empty($colorSchemes)) {
            $colorSchemes = (array)$defaults['image_color_schemes'];
        } else {
            $seenColorKeys = [];
            foreach ($colorSchemes as $row) {
                $k = strtolower(trim((string)($row['key'] ?? '')));
                if ($k !== '') {
                    $seenColorKeys[$k] = true;
                }
            }
            foreach ((array)$defaults['image_color_schemes'] as $defRow) {
                if (!is_array($defRow)) {
                    continue;
                }
                $k = strtolower(trim((string)($defRow['key'] ?? '')));
                if ($k === '' || isset($seenColorKeys[$k])) {
                    continue;
                }
                $colorSchemes[] = [
                    'key' => $k,
                    'weight' => max(0.01, min(5.0, (float)($defRow['weight'] ?? 1.0))),
                    'instruction' => trim((string)($defRow['instruction'] ?? $k)),
                ];
                $seenColorKeys[$k] = true;
                if (count($colorSchemes) >= 80) {
                    break;
                }
            }
        }
        $settings['image_color_schemes'] = $colorSchemes;

        $compositions = [];
        foreach ((array)($settings['image_compositions'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = strtolower(trim((string)($row['key'] ?? '')));
            if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
                continue;
            }
            $labelEn = trim((string)($row['label_en'] ?? $key));
            $labelRu = trim((string)($row['label_ru'] ?? $key));
            $instruction = trim((string)($row['instruction'] ?? $labelEn));
            $compositions[] = [
                'key' => $key,
                'weight' => max(0.01, min(5.0, (float)($row['weight'] ?? 1.0))),
                'label_en' => $labelEn !== '' ? $labelEn : $key,
                'label_ru' => $labelRu !== '' ? $labelRu : $key,
                'instruction' => $instruction !== '' ? $instruction : $key,
            ];
            if (count($compositions) >= 80) {
                break;
            }
        }
        if (empty($compositions)) {
            $compositions = (array)$defaults['image_compositions'];
        } else {
            $seenCompKeys = [];
            foreach ($compositions as $row) {
                $k = strtolower(trim((string)($row['key'] ?? '')));
                if ($k !== '') {
                    $seenCompKeys[$k] = true;
                }
            }
            foreach ((array)$defaults['image_compositions'] as $defRow) {
                if (!is_array($defRow)) {
                    continue;
                }
                $k = strtolower(trim((string)($defRow['key'] ?? '')));
                if ($k === '' || isset($seenCompKeys[$k])) {
                    continue;
                }
                $compositions[] = [
                    'key' => $k,
                    'weight' => max(0.01, min(5.0, (float)($defRow['weight'] ?? 1.0))),
                    'label_en' => trim((string)($defRow['label_en'] ?? $k)),
                    'label_ru' => trim((string)($defRow['label_ru'] ?? $k)),
                    'instruction' => trim((string)($defRow['instruction'] ?? $k)),
                ];
                $seenCompKeys[$k] = true;
                if (count($compositions) >= 80) {
                    break;
                }
            }
        }
        $settings['image_compositions'] = $compositions;

        $sceneFamilies = [];
        foreach ((array)($settings['image_scene_families'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = strtolower(trim((string)($row['key'] ?? '')));
            if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
                continue;
            }
            $labelEn = trim((string)($row['label_en'] ?? $key));
            $labelRu = trim((string)($row['label_ru'] ?? $key));
            $instruction = trim((string)($row['instruction'] ?? $labelEn));
            $sceneFamilies[] = [
                'key' => $key,
                'weight' => max(0.01, min(5.0, (float)($row['weight'] ?? 1.0))),
                'label_en' => $labelEn !== '' ? $labelEn : $key,
                'label_ru' => $labelRu !== '' ? $labelRu : $key,
                'instruction' => $instruction !== '' ? $instruction : $key,
            ];
            if (count($sceneFamilies) >= 80) {
                break;
            }
        }
        if (empty($sceneFamilies)) {
            $sceneFamilies = (array)$defaults['image_scene_families'];
        } else {
            $seenSceneKeys = [];
            foreach ($sceneFamilies as $row) {
                $k = strtolower(trim((string)($row['key'] ?? '')));
                if ($k !== '') {
                    $seenSceneKeys[$k] = true;
                }
            }
            foreach ((array)$defaults['image_scene_families'] as $defRow) {
                if (!is_array($defRow)) {
                    continue;
                }
                $k = strtolower(trim((string)($defRow['key'] ?? '')));
                if ($k === '' || isset($seenSceneKeys[$k])) {
                    continue;
                }
                $sceneFamilies[] = [
                    'key' => $k,
                    'weight' => max(0.01, min(5.0, (float)($defRow['weight'] ?? 1.0))),
                    'label_en' => trim((string)($defRow['label_en'] ?? $k)),
                    'label_ru' => trim((string)($defRow['label_ru'] ?? $k)),
                    'instruction' => trim((string)($defRow['instruction'] ?? $k)),
                ];
                $seenSceneKeys[$k] = true;
                if (count($sceneFamilies) >= 80) {
                    break;
                }
            }
        }
        $settings['image_scene_families'] = $sceneFamilies;

        $moods = [];
        foreach ((array)($settings['moods'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = strtolower(trim((string)($row['key'] ?? '')));
            if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
                continue;
            }
            $moods[] = [
                'key' => $key,
                'weight' => max(0.01, min(5.0, (float)($row['weight'] ?? 1.0))),
                'label_en' => trim((string)($row['label_en'] ?? $key)),
                'label_ru' => trim((string)($row['label_ru'] ?? $key)),
            ];
            if (count($moods) >= 80) {
                break;
            }
        }
        if (empty($moods)) {
            $moods = (array)$defaults['moods'];
        }
        $settings['moods'] = $moods;

        return $settings;
    }
}

if (!function_exists('seo_gen_settings_get')) {
    function seo_gen_settings_get(mysqli $db): array
    {
        seo_gen_settings_table_ensure($db);
        $table = seo_gen_settings_table_name();
        $res = mysqli_query($db, "SELECT settings_json FROM `{$table}` WHERE id = 1 LIMIT 1");
        if ($res && ($row = mysqli_fetch_assoc($res))) {
            $json = (string)($row['settings_json'] ?? '');
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return seo_gen_settings_normalize($decoded);
            }
        }
        $defaults = seo_gen_settings_default();
        seo_gen_settings_save($db, $defaults, 0);
        return $defaults;
    }
}

if (!function_exists('seo_gen_cron_runs_table_ensure')) {
    function seo_gen_cron_runs_table_ensure(mysqli $db): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS `seo_article_cron_runs` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `job_date` DATE NOT NULL,
            `lang_code` VARCHAR(5) NOT NULL,
            `slot_index` TINYINT UNSIGNED NOT NULL,
            `planned_at` DATETIME NOT NULL,
            `status` ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `article_id` INT UNSIGNED NULL,
            `message` VARCHAR(500) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_seo_article_slot` (`job_date`, `lang_code`, `slot_index`),
            KEY `idx_seo_article_planned_status` (`planned_at`, `status`),
            KEY `idx_seo_article_lang_date` (`lang_code`, `job_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return mysqli_query($db, $sql) !== false;
    }
}

if (!function_exists('seo_gen_settings_save')) {
    function seo_gen_settings_save(mysqli $db, array $settings, int $adminId = 0): bool
    {
        seo_gen_settings_table_ensure($db);
        $table = seo_gen_settings_table_name();
        $normalized = seo_gen_settings_normalize($settings);
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            return false;
        }
        $jsonSafe = mysqli_real_escape_string($db, $json);
        $adminSql = $adminId > 0 ? (string)$adminId : 'NULL';
        $sql = "INSERT INTO `{$table}` (id, settings_json, updated_by_admin_id, created_at, updated_at)
                VALUES (1, '{$jsonSafe}', {$adminSql}, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    settings_json = VALUES(settings_json),
                    updated_by_admin_id = VALUES(updated_by_admin_id),
                    updated_at = NOW()";
        return mysqli_query($db, $sql) !== false;
    }
}
