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
    function seo_gen_campaign_variance_library(): array
    {
        return [
            'journal' => [
                'styles_en' => ['issue opener', 'editorial notebook', 'market map', 'backstage report', 'operator column', 'field memo', 'signal essay', 'niche dispatch'],
                'styles_ru' => ['открывающая статья номера', 'редакционный блокнот', 'карта рынка', 'закулисный отчет', 'операторская колонка', 'полевой memo', 'сигнальное эссе', 'нишевой dispatch'],
                'clusters_en' => ['creative shelf life and refresh economics', 'source volatility and buyer adaptation patterns', 'offer selection under shrinking margins', 'team handoff discipline in affiliate operations', 'operator hiring and backstage team structure', 'whitehat shells and compliance buffers', 'moderation pressure and account trust decay', 'telegram distribution as owned media infrastructure', 'farm economics and account lifespan strategy', 'market maps for geos offers and source mix', 'decision quality after attribution degradation', 'operating rhythm inside high-pressure media teams'],
                'clusters_ru' => ['срок жизни креативов и экономика постоянной пересборки', 'волатильность источников и адаптация баеров', 'выбор офферов в условиях сжатия маржи', 'дисциплина handoff внутри affiliate-операционки', 'найм операторов и backstage-структура команды', 'whitehat-оболочки и compliance-буферы', 'давление модерации и деградация account trust', 'Telegram как owned media-инфраструктура для команды', 'экономика фарма и стратегия срока жизни аккаунтов', 'карта гео офферов и source mix для команды', 'качество решений после деградации атрибуции', 'рабочий ритм внутри медиабаинг-команд под давлением'],
                'article_structures_en' => ['Issue opener -> market tension -> backstage realities -> takeaways -> close', 'Observation -> symptoms -> interpretation -> operator response -> summary', 'Market map -> segments -> hidden shifts -> tactical response -> close'],
                'article_structures_ru' => ['Открывающая статья номера -> напряжение рынка -> backstage-реальность -> выводы -> итог', 'Наблюдение -> симптомы -> интерпретация -> реакция оператора -> итог', 'Карта рынка -> сегменты -> скрытые сдвиги -> тактический ответ -> closing note'],
            ],
            'playbooks' => [
                'styles_en' => ['setup memo', 'launch checklist', 'rollback memo', 'implementation guide', 'runbook', 'SOP blueprint', 'debug diary', 'recovery guide'],
                'styles_ru' => ['setup memo', 'launch checklist', 'rollback memo', 'implementation guide', 'runbook', 'SOP blueprint', 'дневник отладки', 'гайд по восстановлению'],
                'clusters_en' => ['launch QA and preflight checks', 'bm trust recovery after bans', 'handoff templates for buyers and assistants', 'routing failover and reserve infrastructure', 'creative review loop and fatigue control', 'offer screening workflow for operators', 'day-one setup for junior buyers', 'checkout continuity after payment failures', 'tracking hygiene and macro validation', 'rollback planning after failed releases', 'bundle migration between teams', 'operator dashboard and alert routine'],
                'clusters_ru' => ['launch QA и preflight-проверки перед запуском', 'восстановление trust у BM после банов', 'handoff-шаблоны для баеров и ассистентов', 'failover-маршрутизация и резервная инфраструктура', 'creative review loop и контроль выгорания', 'workflow скрининга офферов для операторов', 'day-one setup для junior media buyer', 'непрерывность checkout после сбоев платежей', 'hygiene трекинга и валидация макросов', 'rollback-план после неудачных релизов', 'миграция связки между командами', 'операторский дашборд и рутина алертов'],
                'article_structures_en' => ['Checklist -> stack -> actions -> QA -> scale notes -> summary', 'Failure -> diagnosis -> fix path -> verification -> safeguard', 'Start point -> implementation -> edge cases -> handoff -> final SOP'],
                'article_structures_ru' => ['Чеклист -> стек -> действия -> QA -> заметки по масштабу -> итог', 'Сбой -> диагностика -> путь фикса -> верификация -> safeguard', 'Стартовая точка -> implementation -> edge cases -> handoff -> финальный SOP'],
            ],
            'signals' => [
                'styles_en' => ['market alert', 'government watch', 'exchange watch', 'crypto signal memo', 'legislation tracker'],
                'styles_ru' => ['market alert', 'government watch', 'биржевой watch', 'crypto signal memo', 'законодательный трекер'],
                'clusters_en' => ['fresh politics and government decisions affecting traffic markets', 'business analysis around public companies sectors and market stress', 'exchange analytics and market structure shifts', 'major cryptocurrency moves and asset-specific implications', 'russian business news with operational market consequences', 'new russian laws bills and enforcement practice with digital impact', 'sanctions payments and settlement risks for operators', 'central bank signals and financial regulation shifts', 'ad market budget reallocations after macro events', 'messenger platform rules and distribution economics', 'election cycles politics and platform behavior changes', 'operator exposure after geopolitical shocks'],
                'clusters_ru' => ['свежие политические новости и решения власти с влиянием на рынок трафика', 'бизнес-аналитика по компаниям секторам и рыночному стрессу', 'аналитика бирж структуры рынка и движения ликвидности', 'отдельные криптовалюты и их новостной фон как рыночный сигнал', 'свежие бизнес-новости России с последствиями для digital-рынка', 'новости законодательства России законопроекты и практика применения', 'санкции платежи и риски расчетов для операторов', 'сигналы центробанков и повороты финансового регулирования', 'перераспределение рекламных бюджетов после макро-событий', 'правила мессенджеров и экономика дистрибуции', 'политические циклы и смена поведения платформ', 'уязвимость операторов после геополитических шоков'],
                'article_structures_en' => ['Market shock -> operator exposure -> control points -> next steps'],
                'article_structures_ru' => ['Рыночный шок -> уязвимость оператора -> точки контроля -> следующие шаги'],
            ],
            'reviews' => [
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
                'duplicate_retry_attempts' => 2,
                'word_min' => 1800,
                'word_max' => 4200,
                'seed_salt_suffix' => 'reviews',
                'styles_en' => ['comparison review', 'tool benchmark', 'provider shortlist', 'buyer guide', 'stack review', 'field comparison'],
                'styles_ru' => ['сравнительный обзор', 'бенчмарк инструментов', 'шортлист поставщиков', 'buyer guide', 'разбор стека', 'полевое сравнение'],
                'clusters_en' => [
                    'affiliate networks and partner programs',
                    'cloaking tools and routing stacks',
                    'tracker platforms and attribution quality',
                    'ai generators for creatives and workflows',
                    'server providers and hosting reliability',
                    'domain registrars and domain vendors',
                    'anti-detect browsers and operator environments',
                    'spy tools and ad intelligence suites',
                    'proxy providers and mobile proxy pools',
                    'landing builders and page infrastructure',
                    'payment tools and routing helpers',
                    'telegram tools and team utilities',
                ],
                'clusters_ru' => [
                    'партнерки и affiliate-сети',
                    'клоаки и routing-стеки',
                    'трекеры и качество атрибуции',
                    'ИИ-генераторы для креативов и workflow',
                    'серверные провайдеры и надежность хостинга',
                    'регистраторы доменов и доменные поставщики',
                    'anti-detect браузеры и среда оператора',
                    'spy-сервисы и ad-intelligence наборы',
                    'proxy-поставщики и mobile proxy-пулы',
                    'конструкторы лендингов и инфраструктура страниц',
                    'платежные сервисы и routing helpers',
                    'Telegram-инструменты и утилиты команды',
                ],
                'article_structures_en' => [
                    'Category -> evaluation criteria -> shortlist -> comparison table -> recommendation',
                    'Use case -> stack candidates -> benchmarks -> tradeoffs -> verdict',
                    'Problem -> providers -> strengths and weaknesses -> risk notes -> best fit',
                ],
                'article_structures_ru' => [
                    'Категория -> критерии оценки -> шортлист -> таблица сравнения -> рекомендация',
                    'Use-case -> кандидаты в стек -> бенчмарки -> tradeoff -> вердикт',
                    'Проблема -> провайдеры -> сильные и слабые стороны -> риски -> лучший fit',
                ],
                'article_system_prompt_en' => '',
                'article_system_prompt_ru' => '',
                'article_user_prompt_append_en' => 'Write real reviews, comparisons and curated shortlists for affiliate operators. Compare tools and vendors by concrete criteria: routing reliability, moderation resilience, attribution quality, pricing logic, onboarding friction, geo-fit, support quality, scaling limits, hidden risks and who each option is actually for. External resources are allowed when useful.',
                'article_user_prompt_append_ru' => 'Пиши реальные обзоры, сравнения и подборки для affiliate-операторов. Сравнивай инструменты и поставщиков по конкретным критериям: надежность routing, устойчивость под модерацией, качество атрибуции, логика цен, сложность онбординга, fit по гео, качество саппорта, ограничения по масштабу, скрытые риски и для кого подходит каждый вариант. В этом разделе допустимы ссылки на сторонние ресурсы, если они помогают обзору.',
            ],
            'fun' => [
                'styles_en' => ['dark comedy note', 'absurd dispatch', 'team mythology sketch', 'mock memo', 'tragicomedy review', 'ritual notebook'],
                'styles_ru' => ['темная комедия', 'абсурдный dispatch', 'набросок командной мифологии', 'mock memo', 'трагикомический обзор', 'ритуальный блокнот'],
                'clusters_en' => ['late-night dashboard delirium', 'assistant and buyer miscommunication folklore', 'launch day rituals and panic management', 'creative review as office theatre', 'bm recovery as heroic quest', 'telegram chats as tribal memory', 'operator burnout turned into niche comedy', 'spreadsheet cults and metric superstition', 'anti-detect discipline as workplace mythology', 'offer screening as satirical character study', 'team voice messages and crisis folklore', 'meeting after banwave as tragic farce'],
                'clusters_ru' => ['ночной делирий у дашбордов', 'фольклор недопонимания между ассистентом и баером', 'ритуалы launch-day и управление паникой', 'creative review как офисный театр', 'восстановление BM как героический квест', 'Telegram-чаты как племенная память команды', 'выгорание операторов как нишевая комедия', 'культ таблиц и суеверия вокруг метрик', 'anti-detect-дисциплина как рабочая мифология', 'скрининг офферов как сатирический портрет команды', 'голосовые команды и фольклор кризисов', 'митинг после banwave как трагическая буффонада'],
                'article_structures_en' => ['Scene -> ritual -> collapse -> niche moral', 'Backstage anecdote -> escalation -> reveal -> takeaways'],
                'article_structures_ru' => ['Сцена -> ритуал -> обрушение -> нишевая мораль', 'Закулисный анекдот -> эскалация -> разворот -> выводы'],
            ],
        ];
    }

    function seo_gen_default_campaigns(): array
    {
        $campaigns = [
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
                'duplicate_retry_attempts' => 1,
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
                'duplicate_retry_attempts' => 1,
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
            'signals' => [
                'key' => 'signals',
                'title' => 'Signals',
                'title_ru' => 'Повестка',
                'description' => 'Policy, platform news and regulatory shifts in traffic arbitrage across CIS and global markets.',
                'description_ru' => 'Политика платформ, новости и регуляторные сдвиги в арбитраже трафика для СНГ и мировой практики.',
                'material_section' => 'signals',
                'enabled' => true,
                'daily_min' => 2,
                'daily_max' => 4,
                'max_per_run' => 2,
                'duplicate_retry_attempts' => 4,
                'word_min' => 1400,
                'word_max' => 2600,
                'seed_salt_suffix' => 'signals',
                'styles_en' => [
                    'policy brief',
                    'market signal memo',
                    'regulatory watch',
                    'platform change dispatch',
                    'policy-impact memo',
                    'operator checklist',
                    'what-to-do-now brief',
                    'enforcement watch',
                    'risk memo',
                    'compliance update',
                    'platform shift note',
                    'impact bulletin',
                ],
                'styles_ru' => [
                    'policy brief',
                    'обзор сигнала рынка',
                    'регуляторная записка',
                    'сводка изменений платформ',
                    'policy-impact memo',
                    'operator checklist',
                    'что делать сейчас',
                    'enforcement watch',
                    'risk memo',
                    'compliance update',
                    'операционный bulletin',
                    'разбор последствий для команды',
                ],
                'clusters_en' => [
                    'meta policy changes and moderation enforcement',
                    'telegram platform shifts and monetization rules',
                    'tiktok ads restrictions and commerce updates',
                    'cis regulation and affiliate market implications',
                    'global privacy moves and attribution fallout',
                    'payment, compliance and source volatility signals',
                    'platform trust shifts and account quality pressure',
                    'source-side volatility after moderation rule changes',
                    'measurement loss after privacy and data retention changes',
                    'telegram enforcement patterns for affiliate operators',
                    'what changes first for buyers after policy shifts',
                    'operator response plans to sudden platform restrictions',
                    'fresh politics and government decisions affecting traffic markets',
                    'business analysis around public companies, sectors and market stress',
                    'exchange analytics and market structure shifts',
                    'major cryptocurrency moves and asset-specific implications',
                    'russian business news with operational market consequences',
                    'new russian laws, bills and enforcement practice with digital impact',
                ],
                'clusters_ru' => [
                    'изменения policy в Meta и ужесточение модерации',
                    'сдвиги Telegram, правила монетизации и новые ограничения',
                    'ограничения TikTok Ads и апдейты commerce-экосистемы',
                    'регуляторные движения в СНГ и их эффект на affiliate-рынок',
                    'privacy-сдвиги в мире и последствия для атрибуции',
                    'сигналы по payment, compliance и волатильности источников',
                    'сдвиги trust-механик и давление на качество аккаунтов',
                    'волатильность источников после новых правил модерации',
                    'потери измерения после privacy-ограничений и data retention сдвигов',
                    'Telegram enforcement-паттерны для affiliate-команд',
                    'что первым меняется для баеров после platform shifts',
                    'операционный response plan при резких ограничениях платформ',
                    'свежие политические новости и решения власти с влиянием на рынок трафика',
                    'бизнес-аналитика по компаниям, секторам и рыночному стрессу',
                    'аналитика бирж, структуры рынка и движения ликвидности',
                    'отдельные криптовалюты и их новостной фон как рыночный сигнал',
                    'свежие бизнес-новости России с последствиями для digital-рынка',
                    'новости законодательства России, законопроекты и практика применения',
                ],
                'article_structures_en' => [
                    'Signal -> what changed -> operator impact -> affected geos -> action checklist',
                    'Policy update -> enforcement pattern -> hidden risks -> response plan',
                    'News brief -> why it matters -> who gets hit first -> mitigation steps',
                    'Policy-impact memo -> what changed -> what breaks -> what to do now',
                    'Regulatory watch -> signal summary -> risk map -> response checklist',
                    'Operator checklist -> immediate actions -> edge cases -> verification',
                    'What-to-do-now -> signal -> implications -> first 24 hours plan',
                ],
                'article_structures_ru' => [
                    'Сигнал -> что изменилось -> влияние на оператора -> затронутые гео -> чеклист действий',
                    'Policy-апдейт -> паттерн enforcement -> скрытые риски -> план реакции',
                    'Новость -> почему это важно -> кто почувствует первым -> шаги смягчения',
                    'Policy-impact memo -> что изменилось -> что ломается -> что делать сейчас',
                    'Regulatory watch -> краткий сигнал -> карта рисков -> чеклист реакции',
                    'Operator checklist -> немедленные действия -> edge cases -> верификация',
                    'Что делать сейчас -> сигнал -> последствия -> план на первые 24 часа',
                ],
                'article_system_prompt_en' => '',
                'article_system_prompt_ru' => '',
                'article_user_prompt_append_en' => 'Track policy, moderation, legal and market signals around affiliate traffic. Include fresh politics, business analysis, exchange analytics, major cryptocurrency developments, Russian business news and Russian legislation when they create real operator-relevant consequences. Force a non-generic angle: prefer policy-impact memo, regulatory watch, operator checklist, enforcement watch or what-to-do-now format over a generic overview. Every article must answer what changed, who gets hit first, what breaks operationally, and what the team should do next.',
                'article_user_prompt_append_ru' => 'Отслеживай policy, модерацию, регуляторику и рыночные сигналы вокруг арбитража трафика. В этот же слой включай свежие политические новости, бизнес-аналитику, аналитику бирж, движения по отдельным криптовалютам, бизнес-новости России и новости законодательства России, если у них есть реальное последствие для digital-рынка, affiliate-операций, платежей, комплаенса, источников трафика или поведения платформ. Принудительно смещай угол в policy-impact memo, regulatory watch, operator checklist, enforcement watch или формат «что делать сейчас», а не в очередной общий обзор. Каждый материал должен отвечать на вопросы: что изменилось, кто почувствует удар первым, что ломается в операционке и что команде делать дальше.',
            ],
            'fun' => [
                'key' => 'fun',
                'title' => 'Fun',
                'title_ru' => 'Отдых / Фан',
                'description' => 'Entertainment, satire, meme-art and playful editorial formats about affiliate teams and traffic arbitrage culture.',
                'description_ru' => 'Развлекательные, сатирические и мем-редакционные материалы про арбитраж, команды и культурный код ниши.',
                'material_section' => 'fun',
                'enabled' => true,
                'daily_min' => 1,
                'daily_max' => 3,
                'max_per_run' => 1,
                'duplicate_retry_attempts' => 3,
                'word_min' => 1000,
                'word_max' => 2200,
                'seed_salt_suffix' => 'fun',
                'styles_en' => ['satirical pamphlet', 'humorous review', 'meme editorial', 'operator parody', 'office folklore note', 'comic backstage column'],
                'styles_ru' => ['сатирический памфлет', 'юмористический обзор', 'мем-редакционка', 'операционная пародия', 'заметка про офисный фольклор', 'комическая backstage-колонка'],
                'clusters_en' => [
                    'agency life and buyer folklore',
                    'farm drama and operator superstitions',
                    'creative fatigue as dark comedy',
                    'tracker chaos and postback absurdity',
                    'team memes and backstage rituals',
                    'platform moderation as tragicomedy',
                ],
                'clusters_ru' => [
                    'агентская жизнь и фольклор медиабаеров',
                    'драма фарма и суеверия операторов',
                    'выгорание креативов как темная комедия',
                    'хаос трекеров и абсурд postback-ошибок',
                    'командные мемы и backstage-ритуалы',
                    'модерация платформ как трагикомедия',
                ],
                'article_structures_en' => [
                    'Setup -> absurd escalation -> punchline -> niche truth',
                    'Character sketch -> operational chaos -> meme payoff -> takeaway',
                    'Mock review -> exaggeration -> real pain points -> closing jab',
                ],
                'article_structures_ru' => [
                    'Сетап -> абсурдное обострение -> панчлайн -> нишевая правда',
                    'Портрет персонажа -> операционный хаос -> мемный payoff -> вывод',
                    'Пародийный обзор -> преувеличение -> реальные боли -> финальный укол',
                ],
                'article_system_prompt_en' => '',
                'article_system_prompt_ru' => '',
                'article_user_prompt_append_en' => 'Keep it playful, sharp and niche-native. Humor should come from real affiliate operations, role behavior, handoff failures, moderation absurdity and tracker chaos, not generic jokes.',
                'article_user_prompt_append_ru' => 'Держи тон живым, ироничным и нишевым. Юмор должен рождаться из реальной affiliate-операционки, ролевого поведения команды, handoff-сбоев, модерационного абсурда и хаоса трекеров, а не из общих шуток.',
            ],
        ];

        $variance = seo_gen_campaign_variance_library();
        foreach ($campaigns as $key => $row) {
            if (empty($variance[$key]) || !is_array($variance[$key])) {
                continue;
            }
            foreach (['styles_en', 'styles_ru', 'clusters_en', 'clusters_ru', 'article_structures_en', 'article_structures_ru'] as $listKey) {
                $base = array_values(array_filter(array_map('trim', (array)($row[$listKey] ?? []))));
                $extra = array_values(array_filter(array_map('trim', (array)($variance[$key][$listKey] ?? []))));
                if (!empty($extra)) {
                    $campaigns[$key][$listKey] = array_values(array_unique(array_merge($base, $extra)));
                }
            }
        }

        return $campaigns;
    }

    function seo_gen_normalize_campaigns($raw): array
    {
        $defaults = seo_gen_default_campaigns();
        $raw = is_array($raw) ? $raw : [];
        $out = [];
        foreach ($defaults as $key => $default) {
            $row = array_merge($default, is_array($raw[$key] ?? null) ? $raw[$key] : []);
            $row['key'] = $key;
            $row['material_section'] = in_array((string)($row['material_section'] ?? $key), ['journal', 'playbooks', 'signals', 'reviews', 'fun'], true)
                ? (string)$row['material_section']
                : $key;
            $row['enabled'] = !empty($row['enabled']);
            $row['daily_min'] = max(1, min(48, (int)($row['daily_min'] ?? $default['daily_min'])));
            $row['daily_max'] = max($row['daily_min'], min(96, (int)($row['daily_max'] ?? $default['daily_max'])));
            $row['max_per_run'] = max(1, min(24, (int)($row['max_per_run'] ?? $default['max_per_run'])));
            $row['duplicate_retry_attempts'] = max(1, min(8, (int)($row['duplicate_retry_attempts'] ?? ($default['duplicate_retry_attempts'] ?? 1))));
            $row['word_min'] = max(600, min(12000, (int)($row['word_min'] ?? $default['word_min'])));
            $row['word_max'] = max($row['word_min'], min(20000, (int)($row['word_max'] ?? $default['word_max'])));
            $row['seed_salt_suffix'] = trim((string)($row['seed_salt_suffix'] ?? $default['seed_salt_suffix']));
            if ($row['seed_salt_suffix'] === '') {
                $row['seed_salt_suffix'] = $default['seed_salt_suffix'];
            }
            foreach (['styles_en', 'styles_ru', 'clusters_en', 'clusters_ru', 'article_structures_en', 'article_structures_ru'] as $listKey) {
                $row[$listKey] = seo_gen_settings_parse_lines(implode("\n", (array)($row[$listKey] ?? [])), 1200);
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
            'langs' => ['ru'],
            'domain_host' => '',
            'domain_host_en' => 'cpalnya.ru',
            'domain_host_ru' => 'cpalnya.ru',
            'author_name' => 'Редакция ЦПАЛЬНЯ',
            'daily_min' => 1,
            'daily_max' => 3,
            'max_per_run' => 2,
            'duplicate_retry_attempts' => 1,
            'word_min' => 2000,
            'word_max' => 5000,
            'today_first_delay_min' => 15,
            'auto_expand_retries' => 1,
            'expand_context_chars' => 7000,
            'prompt_version' => 'cpalnya-generator-v1',
            'seed_salt' => 'cpalnya-affiliate-content',
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
            'indexnow_hosts' => ['cpalnya.ru'],
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
            'openrouter_fallback_model' => 'openai/gpt-4o-2024-11-20',

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
            'signals_news_enabled' => true,
            'signals_news_max_items' => 12,
            'signals_news_lookback_hours' => 96,
            'signals_news_timeout' => 12,
            'signals_news_feeds' => [
                'https://news.google.com/rss/search?q=affiliate+marketing+OR+digital+advertising+policy&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=traffic+arbitrage+OR+adtech+regulation&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=Russia+business+news+law+digital&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=Russia+legislation+advertising+internet&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=crypto+market+regulation+exchange&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=stock+market+business+analysis+Russia&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=politics+business+sanctions+payments+Russia&hl=ru&gl=RU&ceid=RU:ru',
                'https://news.google.com/rss/search?q=telegram+policy+monetization+news&hl=ru&gl=RU&ceid=RU:ru',
            ],

            'styles_en' => ['editorial breakdown', 'trend memo', 'operator playbook', 'step-by-step tutorial'],
            'styles_ru' => ['редакционный разбор', 'пошаговый how-to', 'операционный playbook', 'аналитический'],
            'clusters_en' => [
                'facebook farm setup and account resilience',
                'tiktok affiliate funnels and creative iteration',
                'telegram communities mini apps and retention',
                'tracker attribution postbacks and signal recovery',
                'anti-detect browsers team SOP and workflows',
                'nutra crypto igaming finance traffic shifts in 2026',
            ],
            'clusters_ru' => [
                'Facebook farm и антибан в 2026',
                'TikTok-воронки и итерации креативов под affiliate',
                'Telegram-комьюнити и Mini Apps в арбитраже',
                'трекеры, postback и потеря атрибуции',
                'фарм, BM-устойчивость и anti-ban операционка',
                'креативные циклы, UGC и AI-пайплайн команды',
            ],
            'intent_verticals_en' => [
                'affiliate media teams',
                'solo media buyers',
                'in-house arbitrage teams',
                'creative production teams',
                'tracker and analytics operators',
                'offer owners and affiliate managers',
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
                'farm bans and account trust recovery',
                'creative fatigue and testing loop rebuild',
                'tracker misattribution and postback debugging',
                'landing domain and hosting recovery after moderation hit',
                'team SOP rollout for scaling buyers and farmers',
                'source mix rebalance after platform policy changes',
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
                'stabilize traffic delivery and account lifespan',
                'improve test-to-win rate on creative batches',
                'reduce tracking blind spots and data loss',
                'speed up team onboarding and repeatability',
                'protect profitable bundles from operational failures',
                'turn tactical notes into reusable playbooks',
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
                'platform moderation and policy volatility',
                'budget pressure on tests and farm spend',
                'signal loss after privacy and attribution changes',
                'small teams with overlapping roles',
                'fast offer turnover and short shelf life',
                'fragmented infrastructure across tools and contractors',
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
                'how-to guide',
                'operator checklist',
                'launch SOP',
                'troubleshooting memo',
                'creative testing matrix',
                'reusable setup template',
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
                'more stable source scaling',
                'faster issue resolution inside the team',
                'cleaner attribution and decision making',
                'better reuse of winning setups',
                'less chaos in daily operations',
                'higher survival rate of profitable funnels',
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
                'journal articles on affiliate trends',
                'hands-on playbooks and SOPs',
                'case studies with metrics and breakdowns',
                'creative systems and testing workflows',
                'tracker templates and troubleshooting',
                'team operations and backstage infrastructure',
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
                'generic vendor rankings without practical value',
                'empty motivation content with no operator insight',
            ],
            'forbidden_topics_ru' => [
                'сравнение брендов и конкурентов',
                'рейтинги вендоров',
            ],
            'article_structures_en' => [
                'Hook -> what changed -> operator impact -> examples -> checklist',
                'Problem -> stack -> exact steps -> failure cases -> verification',
                'Case context -> setup -> metrics -> bottlenecks -> reusable template',
                'Trend brief -> source signals -> implications -> action plan -> summary',
                'How-to -> prerequisites -> implementation -> debugging -> final SOP',
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
            'article_user_prompt_append_en' => 'Write for affiliate operators. Prefer practical details, current platform behavior, tradeoffs, and reusable workflows over generic theory.',
            'article_user_prompt_append_ru' => '',

            'expand_system_prompt_en' => '',
            'expand_system_prompt_ru' => '',
            'expand_user_prompt_append_en' => '',
            'expand_user_prompt_append_ru' => '',

            'preview_channel_enabled' => true,
            'preview_channel_chat_id' => '',
            'preview_public_channel_enabled' => false,
            'preview_public_channel_chat_id' => '',
            'preview_public_channel_bot_token' => '',
            'preview_public_channel_api_base' => 'https://api.telegram.org',
            'preview_post_max_words' => 220,
            'preview_caption_max_words' => 80,
            'preview_post_min_words' => 70,
            'preview_caption_min_words' => 26,
            'preview_use_llm' => true,
            'preview_llm_model' => '',
            'preview_context_chars' => 14000,

            'preview_image_enabled' => false,
            'preview_image_model' => '',
            'preview_image_size' => '1536x1024',
            'preview_image_anchor_enforced' => true,
            'preview_image_anchor_append' => '',
            'preview_image_style_options' => ['schematic', 'realistic', 'abstract', 'moody', 'cinematic', 'editorial', 'dark_ui', 'backstage', 'surveillance', 'ops_console'],
            'image_color_schemes' => [
                ['key' => 'dark', 'weight' => 1.0, 'instruction' => 'Dark cinematic palette, deep shadows, high contrast accents.'],
                ['key' => 'midnight_cyan', 'weight' => 0.82, 'instruction' => 'Dark midnight base with restrained cyan glow accents and tracker-screen energy.'],
                ['key' => 'charcoal_teal', 'weight' => 0.74, 'instruction' => 'Charcoal foundation with muted teal emphasis and backstage operations mood.'],
                ['key' => 'obsidian_blue', 'weight' => 0.78, 'instruction' => 'Obsidian dark base with refined cool highlights and layered console depth.'],
                ['key' => 'matte_black', 'weight' => 0.72, 'instruction' => 'Matte black low-reflection palette with sharp edge lighting and premium editorial restraint.'],
                ['key' => 'neon', 'weight' => 0.68, 'instruction' => 'Neon cyber palette with glowing accents on dark base, suitable for affiliate city-console scenes.'],
                ['key' => 'slate_cyan_focus', 'weight' => 0.62, 'instruction' => 'Slate gray with controlled cyan focus points and dashboard-like precision.'],
                ['key' => 'midnight_amber', 'weight' => 0.58, 'instruction' => 'Deep midnight palette with warm amber alert accents for moderation and warning motifs.'],
                ['key' => 'carbon_orange', 'weight' => 0.55, 'instruction' => 'Carbon black palette with controlled industrial orange highlights and urgent operations tone.'],
                ['key' => 'graphite_gold', 'weight' => 0.4, 'instruction' => 'Graphite base with brushed gold accents for high-value operator desk scenes.'],
                ['key' => 'storm_blue', 'weight' => 0.5, 'instruction' => 'Stormy blue-gray palette with cool layered shadows and strategic editorial calm.'],
                ['key' => 'tech_slate', 'weight' => 0.54, 'instruction' => 'Tech slate palette with balanced cool tones and UI clarity.'],
                ['key' => 'ink_blue', 'weight' => 0.64, 'instruction' => 'Dark ink blue base with subtle high-contrast highlights and newsroom-tech crossover feel.'],
                ['key' => 'noir', 'weight' => 0.52, 'instruction' => 'Noir monochrome leaning palette, dramatic lighting, secrecy and backstage tension.'],
                ['key' => 'industrial', 'weight' => 0.48, 'instruction' => 'Industrial graphite, steel blue and warning accent palette for routing, hosting and recovery topics.'],
                ['key' => 'teal_orange', 'weight' => 0.5, 'instruction' => 'Balanced teal-orange blockbuster palette for dynamic funnels and active decision scenes.'],
                ['key' => 'light', 'weight' => 0.28, 'instruction' => 'Light clean palette for whitehat, documentation or compliance-buffer contexts.'],
                ['key' => 'colordull', 'weight' => 0.49, 'instruction' => 'Muted desaturated palette, restrained color intensity and tactical seriousness.'],
                ['key' => 'duotone', 'weight' => 0.42, 'instruction' => 'Strong duotone palette with two dominant operational colors and disciplined neutrals.'],
                ['key' => 'monochrome', 'weight' => 0.44, 'instruction' => 'Monochrome palette with tonal depth and controlled contrast, suited for analytic or postmortem scenes.'],
            ],
            'image_compositions' => [
                ['key' => 'centered', 'weight' => 0.66, 'label_en' => 'Centered', 'label_ru' => 'Центрированная', 'instruction' => 'Centered focal composition with clear subject priority.'],
                ['key' => 'deep_perspective_pull', 'weight' => 0.88, 'label_en' => 'Deep perspective pull', 'label_ru' => 'Глубокая перспектива', 'instruction' => 'Strong perspective vanishing point pulling the viewer into a console-like operating space.'],
                ['key' => 'visual_corridor', 'weight' => 0.84, 'label_en' => 'Visual corridor', 'label_ru' => 'Коридор взгляда', 'instruction' => 'Corridor-like depth guiding attention through funnels, routing or layered workstations.'],
                ['key' => 'dynamic_diagonal', 'weight' => 0.78, 'label_en' => 'Dynamic diagonal', 'label_ru' => 'Динамическая', 'instruction' => 'Strong diagonal flow with motion and tactical urgency.'],
                ['key' => 'grid_modular', 'weight' => 0.74, 'label_en' => 'Modular grid', 'label_ru' => 'Модульная сетка', 'instruction' => 'Strict modular grid composition with aligned structural blocks and dashboard logic.'],
                ['key' => 'split_screen', 'weight' => 0.58, 'label_en' => 'Split screen', 'label_ru' => 'Разделенный экран', 'instruction' => 'Split-screen contrast between before and after, approved and banned, signal and blind zone.'],
                ['key' => 'collage_data', 'weight' => 0.55, 'label_en' => 'Data collage', 'label_ru' => 'Коллаж данных', 'instruction' => 'Layered data collage mixing charts, cards, routing maps and operator notes.'],
                ['key' => 'overhead_desk', 'weight' => 0.49, 'label_en' => 'Overhead desk', 'label_ru' => 'Вид на стол сверху', 'instruction' => 'Top-down desk layout showing bundle cards, metrics sheets, devices and tactical artifacts.'],
                ['key' => 'clustered_mass', 'weight' => 0.58, 'label_en' => 'Clustered mass', 'label_ru' => 'Кластерная масса', 'instruction' => 'Dense grouping of tools and actors forming a single weighted operational focus.'],
                ['key' => 'mosaic', 'weight' => 0.5, 'label_en' => 'Mosaic', 'label_ru' => 'Мозаика', 'instruction' => 'Mosaic modular blocks composition with structured segmentation and parallel workflows.'],
                ['key' => 'rule_of_thirds', 'weight' => 0.6, 'label_en' => 'Rule of thirds', 'label_ru' => 'Правило третей', 'instruction' => 'Rule-of-thirds placement with off-center focal points and editorial balance.'],
                ['key' => 'asymmetrical_balance', 'weight' => 0.57, 'label_en' => 'Asymmetrical balance', 'label_ru' => 'Асимметричный баланс', 'instruction' => 'Asymmetrical but balanced layout with weighted consoles, screens or human actors.'],
                ['key' => 'timeline_sequence', 'weight' => 0.46, 'label_en' => 'Timeline sequence', 'label_ru' => 'Последовательность', 'instruction' => 'Sequential timeline composition for postmortems, rollout steps and troubleshooting chains.'],
                ['key' => 'radial_focus', 'weight' => 0.36, 'label_en' => 'Radial focus', 'label_ru' => 'Радиальный фокус', 'instruction' => 'Radial composition around a central alert, decision engine or key asset.'],
                ['key' => 'cinematic_wide', 'weight' => 0.52, 'label_en' => 'Cinematic wide', 'label_ru' => 'Кинематографичный wide', 'instruction' => 'Wide cinematic framing for rooms, teams and city-like source ecosystems.'],
                ['key' => 'isometric', 'weight' => 0.38, 'label_en' => 'Isometric', 'label_ru' => 'Изометрия', 'instruction' => 'Isometric systems view of trackers, routing and operations infrastructure.'],
                ['key' => 'minimal_negative_space', 'weight' => 0.4, 'label_en' => 'Minimal negative space', 'label_ru' => 'Минимализм', 'instruction' => 'Intentional negative space around one strong operator symbol or dashboard fragment.'],
                ['key' => 'broken_reflection', 'weight' => 0.34, 'label_en' => 'Broken reflection', 'label_ru' => 'Ломаное отражение', 'instruction' => 'Fragmented mirrored composition for ambiguity, moderation risk and split outcomes.'],
            ],
            'image_scene_families' => [
                ['key' => 'operators', 'weight' => 1.0, 'label_en' => 'Operators', 'label_ru' => 'Операторы', 'instruction' => 'Human-centered affiliate operators, buyers, farmers or analysts making concrete decisions.'],
                ['key' => 'control_rooms', 'weight' => 0.94, 'label_en' => 'Control rooms', 'label_ru' => 'Операционные комнаты', 'instruction' => 'Rooms of screens, dashboards, tactical maps and coordinated activity.'],
                ['key' => 'devices_farm', 'weight' => 0.88, 'label_en' => 'Devices and farm', 'label_ru' => 'Устройства и фарм', 'instruction' => 'Phones, workstations, browser profiles and trust-management setups as the main subject.'],
                ['key' => 'trackers_dashboards', 'weight' => 0.9, 'label_en' => 'Trackers and dashboards', 'label_ru' => 'Трекеры и дашборды', 'instruction' => 'Tracker interfaces, attribution signals, path maps and event logic.'],
                ['key' => 'creative_studio', 'weight' => 0.86, 'label_en' => 'Creative studio', 'label_ru' => 'Креативная студия', 'instruction' => 'Hooks, concepts, thumbnails, scripts and testing boards in production context.'],
                ['key' => 'routing_infrastructure', 'weight' => 0.84, 'label_en' => 'Routing infrastructure', 'label_ru' => 'Инфраструктура маршрутизации', 'instruction' => 'Domains, hosting, redirects, path logic and resilient delivery systems.'],
                ['key' => 'editorial_backstage', 'weight' => 0.74, 'label_en' => 'Editorial backstage', 'label_ru' => 'Редакционное закулисье', 'instruction' => 'Magazine-meets-operations environment with notes, metrics and scene-setting atmosphere.'],
                ['key' => 'abstract_signal', 'weight' => 0.46, 'label_en' => 'Abstract signal', 'label_ru' => 'Абстрактный сигнал', 'instruction' => 'Signal-driven abstract composition anchored to tracking, moderation or traffic movement.'],
                ['key' => 'hybrid_mix', 'weight' => 0.68, 'label_en' => 'Hybrid mix', 'label_ru' => 'Гибридный микс', 'instruction' => 'Balanced mix of people, systems, devices and signal motifs.'],
            ],
            'image_scenarios' => [
                ['key' => 'traffic_control_room', 'weight' => 1.0, 'label_en' => 'Traffic control room', 'label_ru' => 'Комната управления трафиком', 'instruction' => 'Affiliate operators review traffic, creatives and attribution on multiple screens.'],
                ['key' => 'creative_war_room', 'weight' => 0.92, 'label_en' => 'Creative war room', 'label_ru' => 'Креативная war room', 'instruction' => 'Walls of hooks, thumbnails, scripts and testing boards in a backstage studio.'],
                ['key' => 'tracker_signal_grid', 'weight' => 0.9, 'label_en' => 'Tracker signal grid', 'label_ru' => 'Сетка сигналов трекера', 'instruction' => 'Routing nodes, postbacks, event paths and analytics overlays in a dark console style.'],
                ['key' => 'farm_desk_cluster', 'weight' => 0.82, 'label_en' => 'Farm desk cluster', 'label_ru' => 'Фарм-зона', 'instruction' => 'Rows of controlled devices, profiles, trust dashboards and operator checklists.'],
                ['key' => 'source_map_console', 'weight' => 0.78, 'label_en' => 'Source map console', 'label_ru' => 'Консоль карты источников', 'instruction' => 'Source routes, cost zones, payout markers and performance overlays.'],
                ['key' => 'moderation_checkpoint', 'weight' => 0.74, 'label_en' => 'Moderation checkpoint', 'label_ru' => 'Точка модерации', 'instruction' => 'A stylized checkpoint filtering creatives, accounts and landing flows.'],
                ['key' => 'landing_recovery_scene', 'weight' => 0.7, 'label_en' => 'Landing recovery', 'label_ru' => 'Восстановление лендингов', 'instruction' => 'Rapid rebuild of landing assets, domains and routing in a crisis setup.'],
                ['key' => 'bundle_card_table', 'weight' => 0.66, 'label_en' => 'Bundle table', 'label_ru' => 'Стол со связками', 'instruction' => 'Cards, metrics, notes and source maps arranged like a tactical operations board.'],
                ['key' => 'night_shift_team', 'weight' => 0.62, 'label_en' => 'Night shift team', 'label_ru' => 'Ночная смена', 'instruction' => 'A small affiliate team works through the night in a focused control-room environment.'],
                ['key' => 'postback_debug_lab', 'weight' => 0.76, 'label_en' => 'Postback debug lab', 'label_ru' => 'Лаборатория postback-отладки', 'instruction' => 'Macros, click IDs, logs and broken event chains investigated at close range.'],
                ['key' => 'bm_recovery_desk', 'weight' => 0.72, 'label_en' => 'BM recovery desk', 'label_ru' => 'Стол восстановления BM', 'instruction' => 'Trust recovery work around business managers, accounts, identity signals and careful sequence planning.'],
                ['key' => 'anti_detect_workspace', 'weight' => 0.74, 'label_en' => 'Anti-detect workspace', 'label_ru' => 'Anti-detect workspace', 'instruction' => 'Browser profile operations, compartmentalized devices and operator discipline in one workspace.'],
                ['key' => 'creative_batch_factory', 'weight' => 0.78, 'label_en' => 'Creative batch factory', 'label_ru' => 'Фабрика креативных батчей', 'instruction' => 'A rapid production line of concepts, edits, ratings and batch test preparation.'],
                ['key' => 'telegram_distribution_hub', 'weight' => 0.68, 'label_en' => 'Telegram distribution hub', 'label_ru' => 'Telegram distribution hub', 'instruction' => 'Channels, mini apps, bots and retention loops visualized as an owned-media nerve center.'],
                ['key' => 'offer_screening_board', 'weight' => 0.66, 'label_en' => 'Offer screening board', 'label_ru' => 'Доска скрининга офферов', 'instruction' => 'Offers, geos, margins, approval rates and source compatibility compared on a decision board.'],
                ['key' => 'routing_tunnel_network', 'weight' => 0.7, 'label_en' => 'Routing tunnel network', 'label_ru' => 'Сеть туннелей маршрутизации', 'instruction' => 'Domains, redirects and hosting failover shown as layered delivery tunnels with decision branches.'],
                ['key' => 'editorial_backroom', 'weight' => 0.6, 'label_en' => 'Editorial backroom', 'label_ru' => 'Редакционный backroom', 'instruction' => 'A magazine desk mixed with operator dashboards, notes and affiliate market maps.'],
                ['key' => 'neon_city_funnels', 'weight' => 0.64, 'label_en' => 'Neon city funnels', 'label_ru' => 'Неоновый город и воронки', 'instruction' => 'A dark futuristic city where routes, towers and traffic streams behave like live funnels.'],
            ],
            'preview_image_prompt_template' => 'Create a high-quality {{image_style}} hero image for article "{{title}}" ({{lang}}). Use context: {{excerpt}}. Additional context: {{context}}. Visual theme: CPA affiliate backstage, media buying operations, tracker dashboards, creative batch testing, farm devices, moderation checkpoints, routing infrastructure, Telegram distribution loops, dark neon console mood, layered perspective, no text, no logos.',
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
        $settings['daily_min'] = max(1, min(48, (int)$settings['daily_min']));
        $settings['daily_max'] = max($settings['daily_min'], min(96, (int)$settings['daily_max']));
        $settings['max_per_run'] = max(1, min(40, (int)$settings['max_per_run']));
        $settings['duplicate_retry_attempts'] = max(1, min(8, (int)($settings['duplicate_retry_attempts'] ?? 1)));
        $settings['word_min'] = max(400, min(12000, (int)$settings['word_min']));
        $settings['word_max'] = max($settings['word_min'], min(20000, (int)$settings['word_max']));
        $settings['today_first_delay_min'] = max(1, min(360, (int)$settings['today_first_delay_min']));
        $settings['auto_expand_retries'] = max(0, min(16, (int)$settings['auto_expand_retries']));
        $settings['expand_context_chars'] = max(1000, min(120000, (int)$settings['expand_context_chars']));
        $settings['openai_timeout'] = max(20, min(600, (int)$settings['openai_timeout']));
        $settings['topic_analysis_limit'] = max(20, min(1000, (int)$settings['topic_analysis_limit']));
        $settings['signals_news_enabled'] = array_key_exists('signals_news_enabled', $settings) ? (bool)$settings['signals_news_enabled'] : true;
        $settings['signals_news_max_items'] = max(3, min(40, (int)($settings['signals_news_max_items'] ?? 12)));
        $settings['signals_news_lookback_hours'] = max(6, min(336, (int)($settings['signals_news_lookback_hours'] ?? 96)));
        $settings['signals_news_timeout'] = max(4, min(60, (int)($settings['signals_news_timeout'] ?? 12)));
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
        $settings['preview_channel_enabled'] = (bool)($settings['preview_channel_enabled'] ?? true);
        $settings['preview_channel_chat_id'] = trim((string)($settings['preview_channel_chat_id'] ?? ''));
        $settings['preview_public_channel_enabled'] = (bool)($settings['preview_public_channel_enabled'] ?? false);
        $settings['preview_public_channel_chat_id'] = trim((string)($settings['preview_public_channel_chat_id'] ?? ''));
        $settings['preview_public_channel_bot_token'] = trim((string)($settings['preview_public_channel_bot_token'] ?? ''));
        $settings['preview_public_channel_api_base'] = trim((string)($settings['preview_public_channel_api_base'] ?? 'https://api.telegram.org'));
        if ($settings['preview_public_channel_api_base'] === '') {
            $settings['preview_public_channel_api_base'] = 'https://api.telegram.org';
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
        $settings['openrouter_fallback_model'] = trim((string)($settings['openrouter_fallback_model'] ?? 'openai/gpt-4o-2024-11-20'));
        $settings['openai_api_key'] = trim((string)$settings['openai_api_key']);
        $settings['openrouter_api_key'] = trim((string)$settings['openrouter_api_key']);
        $settings['domain_host'] = strtolower(trim((string)($settings['domain_host'] ?? '')));
        $settings['domain_host'] = preg_replace('/^www\./i', '', $settings['domain_host']);
        $settings['domain_host_en'] = strtolower(trim((string)($settings['domain_host_en'] ?? '')));
        $settings['domain_host_en'] = preg_replace('/^www\./i', '', $settings['domain_host_en']);
        $settings['domain_host_ru'] = strtolower(trim((string)($settings['domain_host_ru'] ?? '')));
        $settings['domain_host_ru'] = preg_replace('/^www\./i', '', $settings['domain_host_ru']);
        if ($settings['domain_host_en'] === '') {
            $settings['domain_host_en'] = 'cpalnya.ru';
        }
        if ($settings['domain_host_ru'] === '') {
            $settings['domain_host_ru'] = 'cpalnya.ru';
        }

        $settings['langs'] = array_values(array_unique(array_filter(array_map(
            static function ($x): string {
                $x = strtolower(trim((string)$x));
                return in_array($x, ['en', 'ru'], true) ? $x : '';
            },
            (array)$settings['langs']
        ))));
        $settings['langs'] = ['ru'];

        $settings['styles_en'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['styles_en']), 1200);
        $settings['styles_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['styles_ru']), 1200);
        $settings['clusters_en'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['clusters_en']), 1200);
        $settings['clusters_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['clusters_ru']), 1200);
        $settings['intent_verticals_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_verticals_en'] ?? [])), 1200);
        $settings['intent_verticals_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_verticals_ru'] ?? [])), 1200);
        $settings['intent_scenarios_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_scenarios_en'] ?? [])), 1200);
        $settings['intent_scenarios_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_scenarios_ru'] ?? [])), 1200);
        $settings['intent_objectives_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_objectives_en'] ?? [])), 1200);
        $settings['intent_objectives_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_objectives_ru'] ?? [])), 1200);
        $settings['intent_constraints_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_constraints_en'] ?? [])), 1200);
        $settings['intent_constraints_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_constraints_ru'] ?? [])), 1200);
        $settings['intent_artifacts_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_artifacts_en'] ?? [])), 1200);
        $settings['intent_artifacts_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_artifacts_ru'] ?? [])), 1200);
        $settings['intent_outcomes_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_outcomes_en'] ?? [])), 1200);
        $settings['intent_outcomes_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['intent_outcomes_ru'] ?? [])), 1200);
        $settings['service_focus_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['service_focus_en'] ?? [])), 1200);
        $settings['service_focus_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['service_focus_ru'] ?? [])), 1200);
        $settings['forbidden_topics_en'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['forbidden_topics_en'] ?? [])), 1200);
        $settings['forbidden_topics_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['forbidden_topics_ru'] ?? [])), 1200);
        $settings['article_structures_en'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['article_structures_en']), 1200);
        $settings['article_structures_ru'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['article_structures_ru']), 1200);
        $settings['openai_headers'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['openai_headers']), 100);
        $settings['openai_proxy_pool'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['openai_proxy_pool']), 300);
        $settings['preview_image_style_options'] = seo_gen_settings_parse_lines(implode("\n", (array)$settings['preview_image_style_options']), 60);
        $settings['signals_news_feeds'] = seo_gen_settings_parse_lines(implode("\n", (array)($settings['signals_news_feeds'] ?? [])), 80);
        if (empty($settings['signals_news_feeds'])) {
            $settings['signals_news_feeds'] = (array)($defaults['signals_news_feeds'] ?? []);
        }
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

        $imageScenarios = [];
        foreach ((array)($settings['image_scenarios'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = strtolower(trim((string)($row['key'] ?? '')));
            if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
                continue;
            }
            $imageScenarios[] = [
                'key' => $key,
                'weight' => max(0.01, min(5.0, (float)($row['weight'] ?? 1.0))),
                'label_en' => trim((string)($row['label_en'] ?? $key)),
                'label_ru' => trim((string)($row['label_ru'] ?? $key)),
                'instruction' => trim((string)($row['instruction'] ?? $key)),
            ];
            if (count($imageScenarios) >= 120) {
                break;
            }
        }
        if (empty($imageScenarios)) {
            $imageScenarios = (array)($defaults['image_scenarios'] ?? []);
        } else {
            $existingScenarioKeys = [];
            foreach ($imageScenarios as $row) {
                $existingScenarioKeys[$row['key']] = true;
            }
            foreach ((array)($defaults['image_scenarios'] ?? []) as $defRow) {
                if (!is_array($defRow)) {
                    continue;
                }
                $defKey = strtolower(trim((string)($defRow['key'] ?? '')));
                if ($defKey === '' || isset($existingScenarioKeys[$defKey])) {
                    continue;
                }
                $imageScenarios[] = [
                    'key' => $defKey,
                    'weight' => max(0.01, min(5.0, (float)($defRow['weight'] ?? 1.0))),
                    'label_en' => trim((string)($defRow['label_en'] ?? $defKey)),
                    'label_ru' => trim((string)($defRow['label_ru'] ?? $defKey)),
                    'instruction' => trim((string)($defRow['instruction'] ?? $defKey)),
                ];
                $existingScenarioKeys[$defKey] = true;
                if (count($imageScenarios) >= 120) {
                    break;
                }
            }
        }
        $settings['image_scenarios'] = $imageScenarios;

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
            `campaign_key` VARCHAR(32) NOT NULL DEFAULT '',
            `slot_index` TINYINT UNSIGNED NOT NULL,
            `planned_at` DATETIME NOT NULL,
            `status` ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `article_id` INT UNSIGNED NULL,
            `message` VARCHAR(500) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_seo_article_slot_campaign` (`job_date`, `lang_code`, `campaign_key`, `slot_index`),
            KEY `idx_seo_article_planned_status` (`planned_at`, `status`),
            KEY `idx_seo_article_lang_date_campaign` (`lang_code`, `campaign_key`, `job_date`)
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
