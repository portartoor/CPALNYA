<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$offersData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'items' => [],
    'selected' => null,
    'selected_slug' => '',
    'related_blog' => [],
];

if (strpos((string)$offersData['host'], ':') !== false) {
    $offersData['host'] = explode(':', (string)$offersData['host'], 2)[0];
}

$offersData['lang'] = (bool)preg_match('/\.ru$/', (string)$offersData['host']) ? 'ru' : 'en';
$isRu = ((string)$offersData['lang'] === 'ru');

$offersData['items'] = [
    [
        'slug' => 'vpn-traffic-site-detection',
        'title_ru' => 'Определение VPN/Proxy-подключений для сайта',
        'title_en' => 'VPN/Proxy connection detection for websites',
        'subtitle_ru' => 'GeoIP API интеграция в формы, CRM и трекеры для compliance и качества лидов',
        'subtitle_en' => 'GeoIP API integration for forms, CRM and trackers focused on compliance and lead quality',
        'excerpt_ru' => 'Подключаю проверку VPN/Proxy/TOR в точках входа сайта, чтобы снизить долю мусорных заявок, удержать конверсию и обеспечить прозрачный аудит трафика.',
        'excerpt_en' => 'I integrate VPN/Proxy/TOR checks into website entry points to reduce junk leads, preserve conversion and keep source-quality controls auditable.',
        'price_label_ru' => 'Стоимость интеграции: от 5 000 ₽',
        'price_label_en' => 'Integration price: from 5,000 RUB',
        'overview_ru' => [
            'Этот оффер решает практическую задачу: отделить коммерчески полезный трафик от источников, которые искажают воронку и статистику продаж. Проверка выполняется на уровне формы, endpoint или middleware, а решение по лиду принимается по заранее заданным правилам.',
            'Я внедряю контур так, чтобы он не ломал UX и не снижал SEO-сигналы: используются кэширование, fail-safe режимы и поэтапное включение правил. Вы получаете управляемую механику фильтрации, а не случайные блокировки.',
            'Итогом становится более чистый CRM-поток, выше точность маршрутизации лидов, прозрачнее отчетность для compliance и проще работа команды продаж.'
        ],
        'overview_en' => [
            'This offer separates commercially valuable traffic from sources that distort funnel quality and sales metrics. Checks are added at form, endpoint or middleware level with explicit lead-routing rules.',
            'I deploy the control layer in a UX-safe and SEO-safe way using cache, fail-safe behavior and phased rule activation. You get deterministic traffic governance instead of random blocking.',
            'The practical outcome is cleaner CRM flow, better lead-routing accuracy, stronger compliance reporting and less wasted sales effort.'
        ],
        'sections_ru' => [
            ['title' => 'Правовой контекст и ответственность', 'text' => 'Для многих компаний уже недостаточно просто собирать заявки. Нужна доказуемая логика контроля источников, фиксация принятых решений и возможность объяснить, почему конкретный трафик был допущен, отправлен на проверку или ограничен.'],
            ['title' => 'Последствия неприменения', 'text' => 'Без такого контура растут ложные лиды, увеличиваются операционные расходы на ручной triage, искажаются маркетинговые метрики, а в критических сценариях возникает регуляторный и репутационный риск из-за отсутствия прозрачного аудита входящего трафика.'],
            ['title' => 'Плюсы внедрения', 'text' => 'После внедрения улучшается качество заявок, снижается шум в CRM, ускоряется работа отдела продаж и появляется контролируемая база для внутренних проверок и управленческих решений по источникам трафика.'],
        ],
        'sections_en' => [
            ['title' => 'Legal and operational context', 'text' => 'Many teams now need explicit and auditable source-quality controls, not just raw lead collection. The process must explain why a traffic source was allowed, challenged or routed to review.'],
            ['title' => 'Consequences of non-adoption', 'text' => 'Without this layer, junk leads increase, manual triage costs grow, marketing attribution degrades and compliance exposure rises due to weak decision traceability.'],
            ['title' => 'Benefits after rollout', 'text' => 'After rollout, lead quality improves, CRM noise drops, sales response becomes more focused and source-quality governance becomes measurable.'],
        ],
        'compliance_table_ru' => [
            ['scenario' => 'Нет проверки VPN/Proxy/TOR', 'without' => 'Рост мусорных лидов и ручной фильтрации', 'with' => 'Чистый поток заявок и авто-маршрутизация'],
            ['scenario' => 'Нет фиксируемых правил', 'without' => 'Сложно объяснить решения в аудите', 'with' => 'Прозрачный decision trail для контроля'],
            ['scenario' => 'Нет risk-tagging в CRM', 'without' => 'Потери времени у sales и нет приоритезации', 'with' => 'Приоритеты лидов по risk-сигналам'],
            ['scenario' => 'Нет связки с трекерами', 'without' => 'Искаженная эффективность каналов', 'with' => 'Управляемая оптимизация кампаний'],
        ],
        'compliance_table_en' => [
            ['scenario' => 'No VPN/Proxy/TOR checks', 'without' => 'Higher junk-lead share and manual triage', 'with' => 'Cleaner lead flow and automated routing'],
            ['scenario' => 'No auditable rules', 'without' => 'Hard to justify decisions during review', 'with' => 'Transparent decision trail'],
            ['scenario' => 'No CRM risk tagging', 'without' => 'Sales time loss and weak prioritization', 'with' => 'Risk-aware lead prioritization'],
            ['scenario' => 'No tracker integration', 'without' => 'Distorted channel performance data', 'with' => 'Controlled campaign optimization'],
        ],
        'integrations' => [
            'cms' => ['1C-Bitrix', 'WordPress', 'OpenCart', 'Drupal', 'Joomla'],
            'frameworks' => ['Laravel', 'Yii', 'CodeIgniter', 'Symfony', 'Node.js', 'FastAPI'],
            'crm' => ['Bitrix24', 'amoCRM', 'Custom CRM via REST/Webhook'],
            'trackers' => ['Keitaro', 'Binom'],
        ],
    ],
    [
        'slug' => 'bitrix-seo-safe-migration',
        'title_ru' => 'SEO-safe миграция сайта на 1С-Битрикс',
        'title_en' => 'SEO-safe migration to 1C-Bitrix',
        'subtitle_ru' => 'Редизайн и перенос структуры без критичной потери органики',
        'subtitle_en' => 'Redesign and structure migration without critical organic loss',
        'excerpt_ru' => 'Планирую и выполняю миграцию с картой редиректов, проверкой индексации и пост-релизным контролем.',
        'excerpt_en' => 'I deliver migration with redirect mapping, indexation checks and post-release monitoring.',
        'price_label_ru' => 'Стоимость интеграции: от 25 000 ₽',
        'price_label_en' => 'Integration price: from 25,000 RUB',
    ],
    [
        'slug' => 'bitrix24-sales-automation',
        'title_ru' => 'Автоматизация продаж в Битрикс24',
        'title_en' => 'Bitrix24 sales automation',
        'subtitle_ru' => 'Маршрутизация лидов, SLA и сквозные workflow',
        'subtitle_en' => 'Lead routing, SLA and end-to-end workflows',
        'excerpt_ru' => 'Собираю процессную модель продаж и внедряю управляемый контур обработки входящих лидов.',
        'excerpt_en' => 'I build process-driven sales workflows with controllable inbound lead handling.',
        'price_label_ru' => 'Стоимость интеграции: от 15 000 ₽',
        'price_label_en' => 'Integration price: from 15,000 RUB',
    ],
    [
        'slug' => 'telegram-support-bot-sla',
        'title_ru' => 'Telegram-бот поддержки с SLA',
        'title_en' => 'Telegram support bot with SLA routing',
        'subtitle_ru' => 'Приоритезация обращений и эскалации без ручного хаоса',
        'subtitle_en' => 'Ticket prioritization and escalations without manual chaos',
        'excerpt_ru' => 'Проектирую bot-flow и подключаю тикетный контур, чтобы ускорить первую реакцию и снизить просрочки.',
        'excerpt_en' => 'I design bot flows and ticket routing to improve first response time and reduce SLA breaches.',
        'price_label_ru' => 'Стоимость интеграции: от 12 000 ₽',
        'price_label_en' => 'Integration price: from 12,000 RUB',
    ],
    [
        'slug' => 'webapp-architecture-audit',
        'title_ru' => 'Архитектурный аудит веб-приложения',
        'title_en' => 'Web application architecture audit',
        'subtitle_ru' => 'Поиск узких мест, рисков и roadmap безопасного рефакторинга',
        'subtitle_en' => 'Bottleneck discovery, risk map and safe refactor roadmap',
        'excerpt_ru' => 'Провожу технический аудит с приоритизацией исправлений по влиянию на бизнес и релизную устойчивость.',
        'excerpt_en' => 'I run architecture audits with business-priority remediation and release-stability focus.',
        'price_label_ru' => 'Стоимость интеграции: от 20 000 ₽',
        'price_label_en' => 'Integration price: from 20,000 RUB',
    ],
    [
        'slug' => 'mvp-launch-8-weeks',
        'title_ru' => 'Запуск MVP за 8 недель',
        'title_en' => 'MVP launch in 8 weeks',
        'subtitle_ru' => 'Scope-control, быстрый релиз и основа для эволюции в production',
        'subtitle_en' => 'Scope control, fast release and production-ready evolution path',
        'excerpt_ru' => 'Собираю MVP-контур под бизнес-гипотезу с минимальными рисками перерасхода времени и бюджета.',
        'excerpt_en' => 'I build MVP scope around business hypotheses with tight timeline and budget control.',
        'price_label_ru' => 'Стоимость интеграции: от 35 000 ₽',
        'price_label_en' => 'Integration price: from 35,000 RUB',
    ],
    [
        'slug' => 'ai-presale-assistant',
        'title_ru' => 'AI-ассистент для пресейла',
        'title_en' => 'AI assistant for presale',
        'subtitle_ru' => 'От брифа к структуре оценки и risk memo',
        'subtitle_en' => 'From brief to structured estimate and risk memo',
        'excerpt_ru' => 'Внедряю AI-контур, который ускоряет первичную оценку и стандартизирует пресейл-документацию.',
        'excerpt_en' => 'I deploy AI workflows that accelerate first-pass estimation and standardize presale artifacts.',
        'price_label_ru' => 'Стоимость интеграции: от 18 000 ₽',
        'price_label_en' => 'Integration price: from 18,000 RUB',
    ],
    [
        'slug' => 'postforge-seo-pipeline',
        'title_ru' => 'SEO контент для сайта',
        'title_en' => 'SEO content pipeline for commercial intent',
        'subtitle_ru' => 'Управляемый выпуск публикаций на базе PostForge',
        'subtitle_en' => 'Controlled publishing flow based on PostForge',
        'excerpt_ru' => 'Настраиваю фабрику контента с quality-gates, дедупликацией и связкой с услугами/офферами.',
        'excerpt_en' => 'I configure content-factory workflows with quality gates, deduplication and service-driven CTA.',
        'price_label_ru' => 'Стоимость интеграции: от 10 000 ₽',
        'price_label_en' => 'Integration price: from 10,000 RUB',
        'overview_ru' => [
            'Этот оффер закрывает задачу стабильного SEO-выпуска под коммерческие интенты: от матрицы тем до публикации и контроля качества.',
            'Я внедряю PostForge-контур как управляемый pipeline: планировщик, генерация, quality-gates, антидублирование и связка материалов с услугами.',
            'Результат: предсказуемый темп публикаций, меньше слабых текстов и выше доля материалов, которые реально поддерживают заявки.'
        ],
        'overview_en' => [
            'This offer builds a predictable SEO publishing system mapped to commercial intent.',
            'I deploy a PostForge workflow with planning, generation, quality gates, deduplication and service-aware CTA mapping.',
            'Outcome: steady publishing cadence, lower low-quality output and stronger demand-capture relevance.'
        ],
        'sections_ru' => [
            ['title' => 'SEO-планирование и кластеры', 'text' => 'Формирую карту тем и структур, чтобы контент закрывал этапы воронки, а не публиковался хаотично.'],
            ['title' => 'Контроль качества контента', 'text' => 'Встраиваю проверки структуры, длины, релевантности, повторов и полноты ответа под пользовательский интент.'],
            ['title' => 'Связка с коммерческими страницами', 'text' => 'Перелинковка и CTA строятся так, чтобы материалы работали на услуги, офферы и продукты.'],
        ],
        'integrations' => [
            'cms' => ['1C-Bitrix', 'WordPress', 'OpenCart', 'Drupal', 'Joomla'],
            'frameworks' => ['Laravel', 'Yii', 'CodeIgniter', 'Symfony', 'Node.js'],
            'crm' => ['Bitrix24', 'amoCRM', 'Webhook routing'],
            'trackers' => ['Keitaro', 'Binom', 'UTM pipelines'],
        ],
    ],
    [
        'slug' => 'antifraud-tracker-rollout',
        'title_ru' => 'ANTIFRAUD TRACKER: внедрение risk-ops контура',
        'title_en' => 'ANTIFRAUD TRACKER risk-ops rollout',
        'subtitle_ru' => 'Инциденты, SLA и рабочий anti-fraud runbook',
        'subtitle_en' => 'Incidents, SLA and practical anti-fraud runbook',
        'excerpt_ru' => 'Внедряю anti-fraud процесс с очередями инцидентов, эскалациями и измеримым контролем качества трафика.',
        'excerpt_en' => 'I roll out anti-fraud operations with incident queues, escalation rules and measurable traffic-quality control.',
        'price_label_ru' => 'Стоимость интеграции: от 22 000 ₽',
        'price_label_en' => 'Integration price: from 22,000 RUB',
    ],
    [
        'slug' => 'geoip-api-for-website',
        'title_ru' => 'GeoIP API для сайта',
        'title_en' => 'GeoIP API for websites',
        'subtitle_ru' => 'Определение страны, ASN, VPN/Proxy/TOR и datacenter IP в real-time',
        'subtitle_en' => 'Real-time country, ASN, VPN/Proxy/TOR and datacenter IP detection',
        'excerpt_ru' => 'Подключаю GeoIP API в формы, авторизацию и backend-валидацию, чтобы улучшить качество трафика и маршрутизацию лидов.',
        'excerpt_en' => 'I integrate GeoIP API into forms, auth and backend validation to improve traffic quality and lead routing.',
        'price_label_ru' => 'Стоимость интеграции: от 9 000 ₽',
        'price_label_en' => 'Integration price: from 9,000 RUB',
    ],
    [
        'slug' => 'website-development-bitrix',
        'title_ru' => 'Разработка сайта на 1С-Битрикс',
        'title_en' => 'Website development on 1C-Bitrix',
        'subtitle_ru' => 'Коммерческий сайт услуг с SEO-структурой и управляемой админкой',
        'subtitle_en' => 'Commercial service website with SEO structure and manageable admin flow',
        'excerpt_ru' => 'Проектирую и собираю Bitrix-сайт под заявки: оффер, архитектура страниц, шаблоны и SEO-контур.',
        'excerpt_en' => 'I build Bitrix websites for conversion: offer architecture, page system, templates and SEO foundation.',
        'price_label_ru' => 'Стоимость интеграции: от 40 000 ₽',
        'price_label_en' => 'Integration price: from 40,000 RUB',
    ],
    [
        'slug' => 'technical-seo-audit-for-website',
        'title_ru' => 'Технический SEO аудит сайта',
        'title_en' => 'Technical SEO audit for website',
        'subtitle_ru' => 'Индексация, дубли, скорость, структура и точки роста органики',
        'subtitle_en' => 'Indexation, duplicates, speed, structure and organic growth points',
        'excerpt_ru' => 'Провожу аудит SEO-техники и выдаю приоритетный план исправлений с фокусом на коммерческие страницы.',
        'excerpt_en' => 'I run technical SEO audits and deliver prioritized fixes focused on demand-capture pages.',
        'price_label_ru' => 'Стоимость интеграции: от 14 000 ₽',
        'price_label_en' => 'Integration price: from 14,000 RUB',
    ],
    [
        'slug' => 'bitrix24-crm-integration',
        'title_ru' => 'Интеграция сайта с Битрикс24 CRM',
        'title_en' => 'Website integration with Bitrix24 CRM',
        'subtitle_ru' => 'Лиды, сделки, SLA и автоматизация воронки без ручного дублирования',
        'subtitle_en' => 'Leads, deals, SLA and funnel automation without manual duplication',
        'excerpt_ru' => 'Внедряю интеграционный контур сайта с Б24: формы, источники, статусы, маршрутизация и контроль качества обработки.',
        'excerpt_en' => 'I deploy website-to-Bitrix24 integration with form intake, source mapping, status routing and SLA control.',
        'price_label_ru' => 'Стоимость интеграции: от 12 000 ₽',
        'price_label_en' => 'Integration price: from 12,000 RUB',
    ],
    [
        'slug' => 'onec-bitrix-order-sync',
        'title_ru' => 'Интеграция 1С и сайта без дублей заказов',
        'title_en' => '1C and website integration without order duplicates',
        'subtitle_ru' => 'Идемпотентный обмен заказами, статусами и остатками',
        'subtitle_en' => 'Idempotent sync for orders, statuses and inventory',
        'excerpt_ru' => 'Настраиваю надежную синхронизацию между 1С, сайтом и CRM с контролем конфликтов и журналом инцидентов.',
        'excerpt_en' => 'I implement reliable 1C-website-CRM sync with conflict handling and incident visibility.',
        'price_label_ru' => 'Стоимость интеграции: от 25 000 ₽',
        'price_label_en' => 'Integration price: from 25,000 RUB',
    ],
    [
        'slug' => 'telegram-bot-for-sales',
        'title_ru' => 'Telegram бот для продаж',
        'title_en' => 'Telegram bot for sales',
        'subtitle_ru' => 'Квалификация лидов, сценарии диалога и передача в CRM',
        'subtitle_en' => 'Lead qualification, dialog scenarios and CRM handoff',
        'excerpt_ru' => 'Создаю Telegram-бота для первичного квалифицирования и маршрутизации входящих обращений в sales-процесс.',
        'excerpt_en' => 'I build Telegram bots for first-line lead qualification and controlled CRM handoff.',
        'price_label_ru' => 'Стоимость интеграции: от 11 000 ₽',
        'price_label_en' => 'Integration price: from 11,000 RUB',
    ],
    [
        'slug' => 'mvp-development-for-startup',
        'title_ru' => 'Разработка MVP для стартапа',
        'title_en' => 'MVP development for startup',
        'subtitle_ru' => 'Быстрый запуск первой версии с архитектурой под рост',
        'subtitle_en' => 'Fast first release with architecture ready for growth',
        'excerpt_ru' => 'Собираю MVP с приоритетом на проверку гипотезы, скорость релиза и контролируемую эволюцию в production.',
        'excerpt_en' => 'I deliver MVP scope focused on hypothesis validation, release speed and controlled production evolution.',
        'price_label_ru' => 'Стоимость интеграции: от 30 000 ₽',
        'price_label_en' => 'Integration price: from 30,000 RUB',
    ],
    [
        'slug' => 'website-speed-optimization',
        'title_ru' => 'Ускорение сайта и оптимизация рендера',
        'title_en' => 'Website speed optimization and render performance',
        'subtitle_ru' => 'Core Web Vitals, вес ресурсов и стабильная работа под нагрузкой',
        'subtitle_en' => 'Core Web Vitals, resource weight and high-load stability',
        'excerpt_ru' => 'Оптимизирую frontend и backend-контур, чтобы улучшить скорость, конверсию и SEO-показатели без потери функционала.',
        'excerpt_en' => 'I optimize frontend and backend performance to improve speed, conversion and SEO without feature loss.',
        'price_label_ru' => 'Стоимость интеграции: от 13 000 ₽',
        'price_label_en' => 'Integration price: from 13,000 RUB',
    ],
    [
        'slug' => 'ai-assistant-for-business',
        'title_ru' => 'AI ассистент для бизнес-процессов',
        'title_en' => 'AI assistant for business processes',
        'subtitle_ru' => 'Интеграция AI в поддержку, продажи и внутренние операции',
        'subtitle_en' => 'AI integration into support, sales and internal operations',
        'excerpt_ru' => 'Внедряю AI-помощника с правилами безопасности, источниками знаний и контролем качества ответов.',
        'excerpt_en' => 'I implement AI assistants with safety controls, knowledge grounding and response-quality governance.',
        'price_label_ru' => 'Стоимость интеграции: от 19 000 ₽',
        'price_label_en' => 'Integration price: from 19,000 RUB',
    ],
];

$titleBySlugRu = [];
$titleBySlugEn = [];
foreach ($offersData['items'] as $tmpOffer) {
    $tmpSlug = (string)($tmpOffer['slug'] ?? '');
    if ($tmpSlug === '') {
        continue;
    }
    $titleBySlugRu[$tmpSlug] = (string)($tmpOffer['title_ru'] ?? '');
    $titleBySlugEn[$tmpSlug] = (string)($tmpOffer['title_en'] ?? '');
}

$relatedOfferMap = [
    'vpn-traffic-site-detection' => ['geoip-api-for-website', 'antifraud-tracker-rollout', 'bitrix24-crm-integration'],
    'geoip-api-for-website' => ['vpn-traffic-site-detection', 'antifraud-tracker-rollout', 'bitrix24-crm-integration'],
    'postforge-seo-pipeline' => ['technical-seo-audit-for-website', 'website-speed-optimization', 'website-development-bitrix'],
    'website-development-bitrix' => ['bitrix-seo-safe-migration', 'technical-seo-audit-for-website', 'bitrix24-crm-integration'],
    'bitrix-seo-safe-migration' => ['website-development-bitrix', 'technical-seo-audit-for-website', 'onec-bitrix-order-sync'],
    'bitrix24-sales-automation' => ['bitrix24-crm-integration', 'telegram-bot-for-sales', 'ai-assistant-for-business'],
    'bitrix24-crm-integration' => ['bitrix24-sales-automation', 'onec-bitrix-order-sync', 'telegram-bot-for-sales'],
    'onec-bitrix-order-sync' => ['bitrix24-crm-integration', 'website-development-bitrix', 'webapp-architecture-audit'],
    'telegram-bot-for-sales' => ['bitrix24-crm-integration', 'bitrix24-sales-automation', 'ai-assistant-for-business'],
    'telegram-support-bot-sla' => ['telegram-bot-for-sales', 'bitrix24-sales-automation', 'ai-assistant-for-business'],
    'webapp-architecture-audit' => ['website-speed-optimization', 'mvp-development-for-startup', 'ai-assistant-for-business'],
    'mvp-launch-8-weeks' => ['mvp-development-for-startup', 'webapp-architecture-audit', 'ai-presale-assistant'],
    'mvp-development-for-startup' => ['mvp-launch-8-weeks', 'webapp-architecture-audit', 'ai-presale-assistant'],
    'ai-presale-assistant' => ['ai-assistant-for-business', 'mvp-development-for-startup', 'postforge-seo-pipeline'],
    'ai-assistant-for-business' => ['ai-presale-assistant', 'telegram-bot-for-sales', 'bitrix24-sales-automation'],
    'website-speed-optimization' => ['technical-seo-audit-for-website', 'website-development-bitrix', 'webapp-architecture-audit'],
    'technical-seo-audit-for-website' => ['postforge-seo-pipeline', 'website-speed-optimization', 'website-development-bitrix'],
    'antifraud-tracker-rollout' => ['vpn-traffic-site-detection', 'geoip-api-for-website', 'bitrix24-crm-integration'],
];

$offerSeoProfiles = [
    'vpn-traffic-site-detection' => ['qru' => 'определение VPN/Proxy-подключений для сайта', 'qen' => 'VPN/Proxy connection detection for website', 'nru' => 'маркетинговых и лидогенерирующих сайтов', 'nen' => 'lead-generation websites', 'riskru' => 'мусорные лиды и искаженная атрибуция', 'risken' => 'junk leads and distorted attribution', 'resru' => 'чистый поток лидов и управляемый compliance-контур', 'resen' => 'clean lead flow and auditable compliance controls', 'focusru' => ['GeoIP/ASN и datacenter-сигналы', 'правила allow/challenge/block', 'risk-tagging лидов в CRM'], 'focusen' => ['GeoIP/ASN and datacenter signals', 'allow/challenge/block rules', 'CRM risk tagging']],
    'geoip-api-for-website' => ['qru' => 'GeoIP API для сайта', 'qen' => 'GeoIP API for website', 'nru' => 'проектов с региональной сегментацией трафика', 'nen' => 'projects with geo-segmented traffic', 'riskru' => 'слепая маршрутизация заявок по регионам', 'risken' => 'blind regional lead routing', 'resru' => 'точная geo-маршрутизация и выше качество трафика', 'resen' => 'accurate geo routing and higher traffic quality', 'focusru' => ['определение страны/ASN/прокси', 'проверка точек входа формы и auth', 'интеграция в трекеры и CRM'], 'focusen' => ['country/ASN/proxy detection', 'form and auth entry-point checks', 'tracker and CRM integration']],
    'postforge-seo-pipeline' => ['qru' => 'SEO контент для сайта', 'qen' => 'SEO content for website', 'nru' => 'коммерческих B2B-сайтов и сервисов', 'nen' => 'commercial B2B websites', 'riskru' => 'контент-спам без конверсии и кластерной логики', 'risken' => 'content spam without conversion logic', 'resru' => 'управляемый SEO pipeline с ростом трафика и заявок', 'resen' => 'controlled SEO pipeline with traffic and lead growth', 'focusru' => ['кластеризация тем под интент', 'quality gates и антидубли', 'связка статьи -> оффер -> лид'], 'focusen' => ['intent-driven topic clustering', 'quality gates and deduplication', 'article -> offer -> lead mapping']],
    'technical-seo-audit-for-website' => ['qru' => 'технический SEO аудит сайта', 'qen' => 'technical SEO audit for website', 'nru' => 'сайтов с падением индексации и позиций', 'nen' => 'websites with indexation and ranking issues', 'riskru' => 'потеря органики из-за техдолга', 'risken' => 'organic loss caused by technical debt', 'resru' => 'дорожная карта SEO-фиксов с приоритетом по выручке', 'resen' => 'SEO fix roadmap prioritized by revenue impact', 'focusru' => ['crawl/index audit', 'canonical/redirect/internal link fixes', 'Core Web Vitals и рендер'], 'focusen' => ['crawl/index audit', 'canonical/redirect/internal link fixes', 'Core Web Vitals and render performance']],
    'website-development-bitrix' => ['qru' => 'разработка сайта на 1С-Битрикс', 'qen' => '1C-Bitrix website development', 'nru' => 'сервисных компаний и B2B-отделов продаж', 'nen' => 'service businesses and B2B sales teams', 'riskru' => 'неуправляемая структура страниц и слабая конверсия', 'risken' => 'unstructured pages and weak conversion', 'resru' => 'рабочий коммерческий сайт с SEO-архитектурой', 'resen' => 'commercial website with conversion-ready SEO architecture', 'focusru' => ['инфоархитектура услуг', 'компонентная верстка и админ-контур', 'формы и CRM-интеграция'], 'focusen' => ['service information architecture', 'component-based templates and admin flow', 'forms and CRM integration']],
    'bitrix-seo-safe-migration' => ['qru' => 'SEO-safe миграция сайта на 1С-Битрикс', 'qen' => 'SEO-safe migration to 1C-Bitrix', 'nru' => 'проектов после редизайна или смены CMS', 'nen' => 'projects after redesign or CMS migration', 'riskru' => 'просадка трафика и потери URL-веса', 'risken' => 'traffic drops and lost URL equity', 'resru' => 'миграция без критичной просадки органики', 'resen' => 'migration without critical organic loss', 'focusru' => ['карта 301-редиректов', 'контроль canonical и sitemap', 'пострелизный мониторинг индексации'], 'focusen' => ['301 redirect map', 'canonical and sitemap control', 'post-release indexation monitoring']],
    'bitrix24-sales-automation' => ['qru' => 'автоматизация продаж в Битрикс24', 'qen' => 'Bitrix24 sales automation', 'nru' => 'команд с высоким потоком входящих лидов', 'nen' => 'teams with high inbound lead flow', 'riskru' => 'потери лидов и срыв SLA ответа', 'risken' => 'lead leakage and SLA breaches', 'resru' => 'ускорение обработки лидов и прозрачная воронка', 'resen' => 'faster lead processing with transparent pipeline', 'focusru' => ['маршрутизация заявок по правилам', 'роботы/триггеры стадий', 'дедлайны и эскалации SLA'], 'focusen' => ['rule-based lead routing', 'stage automation robots', 'SLA deadlines and escalation']],
    'bitrix24-crm-integration' => ['qru' => 'интеграция сайта с Битрикс24 CRM', 'qen' => 'website integration with Bitrix24 CRM', 'nru' => 'сайтов с несколькими источниками лидов', 'nen' => 'websites with multiple lead sources', 'riskru' => 'дубли лидов и потеря источника', 'risken' => 'lead duplicates and source loss', 'resru' => 'единый CRM-контур с чистой атрибуцией', 'resen' => 'single CRM flow with clean attribution', 'focusru' => ['формы/квизы/чаты в единый поток', 'idempotency и валидация событий', 'UTM и source-маркировка'], 'focusen' => ['forms/quizzes/chats into single flow', 'idempotency and event validation', 'UTM and source tagging']],
    'onec-bitrix-order-sync' => ['qru' => 'интеграция 1С и сайта без дублей заказов', 'qen' => '1C-website sync without duplicate orders', 'nru' => 'ecommerce и B2B-каталогов', 'nen' => 'ecommerce and B2B catalog projects', 'riskru' => 'конфликт статусов и расхождение остатков', 'risken' => 'status conflicts and inventory mismatch', 'resru' => 'устойчивый обмен заказами и статусами', 'resen' => 'reliable order and status synchronization', 'focusru' => ['очереди и retry-политика', 'идемпотентные ключи обмена', 'журнал ошибок и reconciliation'], 'focusen' => ['queue and retry policy', 'idempotent exchange keys', 'error journal and reconciliation']],
    'telegram-bot-for-sales' => ['qru' => 'Telegram бот для продаж', 'qen' => 'Telegram bot for sales', 'nru' => 'пресейл-команд и заявочных воронок', 'nen' => 'presale teams and lead funnels', 'riskru' => 'потеря горячих лидов на первом касании', 'risken' => 'hot leads lost on first touch', 'resru' => 'быстрый квалификационный контур и CRM-передача', 'resen' => 'fast qualification flow with CRM handoff', 'focusru' => ['ветки диалога по интенту', 'автоквалификация и scoring', 'маршрутизация в ответственного менеджера'], 'focusen' => ['intent-based dialog branches', 'auto qualification and scoring', 'routing to responsible manager']],
    'telegram-support-bot-sla' => ['qru' => 'Telegram-бот поддержки с SLA', 'qen' => 'Telegram support bot with SLA', 'nru' => 'сервисных команд и helpdesk-процессов', 'nen' => 'service teams and helpdesk flows', 'riskru' => 'задержка ответа и рост неразобранных тикетов', 'risken' => 'response delay and ticket backlog', 'resru' => 'SLA-дисциплина и предсказуемая поддержка', 'resen' => 'SLA discipline and predictable support', 'focusru' => ['категоризация обращений', 'приоритеты и очереди', 'эскалации при просрочке SLA'], 'focusen' => ['request categorization', 'priority queues', 'SLA breach escalations']],
    'webapp-architecture-audit' => ['qru' => 'архитектурный аудит веб-приложения', 'qen' => 'web application architecture audit', 'nru' => 'продуктов с техдолгом и нестабильными релизами', 'nen' => 'products with tech debt and unstable releases', 'riskru' => 'регрессии и непредсказуемая стоимость изменений', 'risken' => 'regressions and unpredictable change cost', 'resru' => 'roadmap рефакторинга с контролем рисков', 'resen' => 'risk-aware refactor roadmap', 'focusru' => ['картирование доменов и узких мест', 'приоритизация quick wins / deep fixes', 'контур release-quality и observability'], 'focusen' => ['domain and bottleneck mapping', 'quick wins vs deep fixes prioritization', 'release-quality and observability controls']],
    'mvp-launch-8-weeks' => ['qru' => 'запуск MVP за 8 недель', 'qen' => 'MVP launch in 8 weeks', 'nru' => 'стартапов и новых продуктовых направлений', 'nen' => 'startups and new product lines', 'riskru' => 'раздувание scope и срыв срока запуска', 'risken' => 'scope creep and delayed launch', 'resru' => 'быстрый выход в пилот с измеримыми KPI', 'resen' => 'fast pilot launch with measurable KPIs', 'focusru' => ['жесткий scope baseline', 'итерации по бизнес-гипотезе', 'готовность к production-эволюции'], 'focusen' => ['strict scope baseline', 'iterations on business hypothesis', 'production-ready evolution path']],
    'mvp-development-for-startup' => ['qru' => 'разработка MVP для стартапа', 'qen' => 'MVP development for startup', 'nru' => 'продуктов на ранней стадии', 'nen' => 'early-stage products', 'riskru' => 'инженерный оверхед до валидации спроса', 'risken' => 'engineering overhead before demand validation', 'resru' => 'MVP с фокусом на проверку рынка и unit-метрик', 'resen' => 'MVP focused on market validation and unit metrics', 'focusru' => ['ядро сценариев первой версии', 'событийная аналитика с первого релиза', 'дизайн под масштабирование'], 'focusen' => ['core first-version scenarios', 'event analytics from day one', 'scaling-ready design']],
    'ai-presale-assistant' => ['qru' => 'AI-ассистент для пресейла', 'qen' => 'AI assistant for presale', 'nru' => 'команд оценки проектов и discovery', 'nen' => 'teams handling estimates and discovery', 'riskru' => 'долгий пресейл и несогласованные оценки', 'risken' => 'slow presale and inconsistent estimates', 'resru' => 'быстрая структурированная оценка с risk memo', 'resen' => 'fast structured estimation with risk memo', 'focusru' => ['разбор брифа на сущности', 'template-based estimate output', 'контроль качества AI-ответов'], 'focusen' => ['brief decomposition into entities', 'template-based estimate output', 'AI response quality controls']],
    'ai-assistant-for-business' => ['qru' => 'AI ассистент для бизнес-процессов', 'qen' => 'AI assistant for business processes', 'nru' => 'операционных и клиентских команд', 'nen' => 'operations and customer-facing teams', 'riskru' => 'хаотичное внедрение AI без governance', 'risken' => 'chaotic AI rollout without governance', 'resru' => 'контролируемый AI-контур с бизнес-пользой', 'resen' => 'governed AI workflow with business value', 'focusru' => ['источники знаний и доступы', 'policy-ограничения и safety', 'измерение полезности по KPI'], 'focusen' => ['knowledge sources and access scope', 'policy constraints and safety', 'KPI-based utility measurement']],
    'website-speed-optimization' => ['qru' => 'ускорение сайта и оптимизация рендера', 'qen' => 'website speed optimization', 'nru' => 'сайтов с просадкой Core Web Vitals', 'nen' => 'sites with poor Core Web Vitals', 'riskru' => 'медленная загрузка и падение конверсии', 'risken' => 'slow load and conversion drop', 'resru' => 'быстрый рендер и рост качества UX/SEO', 'resen' => 'faster render and stronger UX/SEO quality', 'focusru' => ['критический путь рендера', 'оптимизация JS/CSS/изображений', 'бэкенд-кэш и TTFB'], 'focusen' => ['critical rendering path', 'JS/CSS/image optimization', 'backend caching and TTFB']],
    'antifraud-tracker-rollout' => ['qru' => 'внедрение ANTIFRAUD TRACKER', 'qen' => 'ANTIFRAUD TRACKER rollout', 'nru' => 'команд risk-ops и adtech/ecom-потоков', 'nen' => 'risk-ops teams in adtech/ecom flows', 'riskru' => 'пропуск мошеннических паттернов и потери бюджета', 'risken' => 'fraud pattern leakage and budget loss', 'resru' => 'управляемый антифрод-контур с SLA-инцидентами', 'resen' => 'measurable anti-fraud workflow with SLA incidents', 'focusru' => ['event scoring и сегментация', 'очереди инцидентов и triage', 'правила эскалации и отчетность'], 'focusen' => ['event scoring and segmentation', 'incident queues and triage', 'escalation rules and reporting']],
];

$ensureRichOffer = static function (array $offer, array $titlesRu, array $titlesEn, array $relatedMap, array $profiles): array {
    $titleRu = trim((string)($offer['title_ru'] ?? ''));
    $titleEn = trim((string)($offer['title_en'] ?? ''));
    $subtitleRu = trim((string)($offer['subtitle_ru'] ?? ''));
    $subtitleEn = trim((string)($offer['subtitle_en'] ?? ''));
    $excerptRu = trim((string)($offer['excerpt_ru'] ?? ''));
    $excerptEn = trim((string)($offer['excerpt_en'] ?? ''));
    $slug = trim((string)($offer['slug'] ?? ''));

    $profile = (array)($profiles[$slug] ?? []);
    if (!empty($profile)) {
        $qru = (string)($profile['qru'] ?? $titleRu);
        $qen = (string)($profile['qen'] ?? $titleEn);
        $nru = (string)($profile['nru'] ?? 'бизнес-проектов');
        $nen = (string)($profile['nen'] ?? 'business projects');
        $riskru = (string)($profile['riskru'] ?? 'потери конверсии');
        $risken = (string)($profile['risken'] ?? 'conversion losses');
        $resru = (string)($profile['resru'] ?? 'прозрачный измеримый результат');
        $resen = (string)($profile['resen'] ?? 'transparent measurable outcome');
        $focusru = (array)($profile['focusru'] ?? []);
        $focusen = (array)($profile['focusen'] ?? []);
        if (count($focusru) < 3) { $focusru = array_merge($focusru, ['техническая схема внедрения', 'контроль качества после релиза', 'связка с коммерческими KPI']); }
        if (count($focusen) < 3) { $focusen = array_merge($focusen, ['technical rollout scheme', 'post-release quality control', 'commercial KPI mapping']); }

        $introPoolRu = [
            'Типовой сценарий из практики: у ',
            'Классический кейс для ',
            'Один из самых частых запросов от ',
            'Повторяющийся рабочий сценарий у ',
            'Практика показывает: у ',
            'Наблюдаемый в проектах паттерн у ',
            'Реалистичный кейс для ',
            'Характерная ситуация у ',
            'Часто встречаемая картина у ',
            'Сценарий, который регулярно вижу у ',
            'Показательный случай для ',
            'Бизнес-кейс, который часто повторяется у ',
            'Системный симптом, типичный для ',
            'Ситуация, с которой регулярно приходят ',
            'Из практики внедрений: у ',
            'Сценарий из операционной рутины ',
            'Реальный рабочий паттерн у ',
            'Один из базовых кейсов для ',
            'Типичная стартовая точка у ',
            'Практически учебный кейс для ',
        ];
        $introPoolEn = [
            'A typical practical scenario in ',
            'A classic case for ',
            'One of the most common requests from ',
            'A recurring delivery pattern in ',
            'In practice, this often appears in ',
            'A frequently observed pattern in ',
            'A realistic case for ',
            'A characteristic situation in ',
            'A common operating picture in ',
            'A scenario we repeatedly see in ',
            'A representative case for ',
            'A business case that often repeats in ',
            'A systemic symptom typical for ',
            'A situation teams regularly bring from ',
            'From rollout practice, this appears in ',
            'An operational routine scenario in ',
            'A real-world pattern in ',
            'A baseline case for ',
            'A typical starting point in ',
            'A near-textbook case for ',
        ];
        $introRuPrefix = $introPoolRu[random_int(0, count($introPoolRu) - 1)];
        $introEnPrefix = $introPoolEn[random_int(0, count($introPoolEn) - 1)];

        $offer['overview_ru'] = [
            $introRuPrefix . $nru . ': постепенно накапливается симптом "' . $riskru . '". Снаружи кажется, что система работает, но воронка деградирует, а управленческие решения всё чаще принимаются на неполных данных.',
            'На этом этапе бизнес обычно пытается лечить последствия точечными правками: меняют отдельные правила, добавляют ручные проверки, перераспределяют нагрузку по людям. Проблема в том, что без архитектурного контура это усиливает хаос и делает результат менее предсказуемым.',
            ($titleRu !== '' ? $titleRu : $qru) . ' закрывает задачу как внедренческий сценарий: ' . implode(', ', array_slice($focusru, 0, 3)) . '. Важный момент: это не "одна доработка", а согласованный операционный слой, который можно масштабировать.',
            'Я закладываю в реализацию контрольные точки качества, чтобы решение жило после релиза: кто отвечает за корректность, как фиксируются отклонения, какие пороги запускают эскалацию и где проходит граница между автологикой и ручной валидацией.',
            'Результат выражается не только в технической стабильности. Команда получает ' . $resru . ', а бизнес — более чистую картину по каналам, скорости обработки и ценности каждого следующего шага.',
        ];
        $offer['overview_en'] = [
            $introEnPrefix . $nen . ': the issue "' . $risken . '" accumulates slowly. On the surface, operations still look fine, but funnel quality degrades and decisions are made on incomplete signals.',
            'At this point, teams often patch symptoms with local fixes and manual workarounds. Without a systems layer, this increases operational noise and reduces predictability.',
            ($titleEn !== '' ? $titleEn : $qen) . ' addresses the problem as an implementation workflow centered on ' . implode(', ', array_slice($focusen, 0, 3)) . '. This is not a one-off tweak but an operating model that scales.',
            'The rollout includes explicit quality gates: ownership, deviation handling, escalation thresholds and a clear boundary between automated and manual decisions.',
            'The outcome is not just technical stability. The team gets ' . $resen . ' and the business gets cleaner decision signals across channels, processing speed and ROI visibility.',
        ];
        $offer['sections_ru'] = [
            ['title' => 'Гипотетическая проблема клиента', 'text' => 'Компания приходит с симптомом "' . $riskru . '". Внутри это проявляется как ручные костыли, споры между маркетингом и продажами, и неочевидные причины потерь на переходе между этапами.'],
            ['title' => 'Почему стандартные решения не срабатывают', 'text' => 'Точечные доработки не дают устойчивого эффекта, потому что не связаны в единый сценарий принятия решений. Через 2-4 недели команда снова возвращается к тем же инцидентам, но уже с большей стоимостью исправления.'],
            ['title' => 'Как оффер решает задачу', 'text' => 'Внедрение строится по этапам: ' . implode(', ', array_slice($focusru, 0, 3)) . '. На каждом этапе фиксируются критерии приемки и условия безопасного расширения охвата.'],
            ['title' => 'Что меняется в операционной работе', 'text' => 'После запуска снижается доля ручной рутины, уменьшается шум в CRM/трекерах, а у команды появляется единый язык для обсуждения качества входящего потока и приоритета действий.'],
            ['title' => 'Риск-модель и контроль качества', 'text' => 'Я добавляю не только основную логику, но и страховочный контур: fallback-сценарии, журнал решений, мониторинг деградаций и регламент эскалации. Это защищает от "тихих" регрессий после релизов.'],
            ['title' => 'Практический бизнес-итог', 'text' => 'Финальный эффект: ' . $resru . '. Плюс — прозрачная аналитика для руководителя и меньше потерь времени у команды, которая раньше уходила в ручной triage.'],
        ];
        $offer['sections_en'] = [
            ['title' => 'Hypothetical client problem', 'text' => 'The team enters with "' . $risken . '" as the visible symptom. Underneath, this means fragmented handoffs, noisy attribution and unstable conversion outcomes.'],
            ['title' => 'Why default fixes fail', 'text' => 'Local fixes treat individual incidents but do not create a stable decision system. Within weeks, the same issue returns with higher operational cost.'],
            ['title' => 'How this offer resolves it', 'text' => 'Rollout is executed through ' . implode(', ', array_slice($focusen, 0, 3)) . ' with explicit acceptance criteria and controlled expansion.'],
            ['title' => 'Operational impact after rollout', 'text' => 'Manual overhead drops, CRM/tracker noise declines and teams get a shared operating model for prioritization and execution.'],
            ['title' => 'Risk and quality controls', 'text' => 'The implementation includes fallback behavior, decision logs, degradation monitoring and escalation rules to prevent silent regressions.'],
            ['title' => 'Business-level outcome', 'text' => 'The practical result is ' . $resen . ' with cleaner management visibility and lower time waste in manual triage.'],
        ];
        $offer['compliance_table_ru'] = [
            ['scenario' => 'Без системного внедрения', 'without' => 'Сохраняется риск: ' . $riskru, 'with' => 'Формируется: ' . $resru],
            ['scenario' => 'Нет единого сценария обработки', 'without' => 'Ручные действия, разрозненные решения и потери времени', 'with' => 'Единый операционный контур и предсказуемое исполнение'],
            ['scenario' => 'Нет технического контроля', 'without' => 'Регрессии после релиза и ручные правки', 'with' => 'Контур: ' . implode(', ', array_slice($focusru, 0, 3))],
        ];
        $offer['compliance_table_en'] = [
            ['scenario' => 'No system rollout', 'without' => 'Risk persists: ' . $risken, 'with' => 'Outcome delivered: ' . $resen],
            ['scenario' => 'No unified execution flow', 'without' => 'Manual operations and fragmented decisions', 'with' => 'Single operational flow with predictable execution'],
            ['scenario' => 'No technical controls', 'without' => 'Post-release regressions and manual fixes', 'with' => 'Control layer: ' . implode(', ', array_slice($focusen, 0, 3))],
        ];
        $offer['checklist_ru'] = [
            'Зафиксировать KPI и текущие точки потерь до внедрения.',
            'Согласовать точки интеграции и схему данных.',
            'Реализовать ' . (string)$focusru[0] . '.',
            'Подключить ' . (string)$focusru[1] . ' и тестовый rollout.',
            'Закрепить ' . (string)$focusru[2] . ' в регламенте команды.',
        ];
        $offer['checklist_en'] = [
            'Lock KPI and current loss points before rollout.',
            'Confirm integration points and data flow.',
            'Implement ' . (string)$focusen[0] . '.',
            'Enable ' . (string)$focusen[1] . ' with staged rollout.',
            'Document ' . (string)$focusen[2] . ' in team runbook.',
        ];

        $offer['case_story_ru'] = [
            'Сценарий из практики выглядит так: бизнес видит, что заявок много, но доля действительно полезных лидов падает. Маркетинг уверен, что трафик "нормальный", продажи говорят об обратном, а продуктовая команда перегружена запросами на срочные доработки.',
            'На старте я провожу короткую диагностику и собираю карту потерь: где ломается сигнал, где решения принимаются интуитивно, где нет проверяемых критериев. Это позволяет не распыляться и сразу строить контур вокруг узких мест.',
            'Дальше внедрение идёт не "большим взрывом", а управляемыми итерациями: сначала контрольные точки, затем автоматизация, потом масштабирование. Команда видит эффект по метрикам уже на ранних шагах, без риска парализовать текущие процессы.',
            'Финальная стадия — закрепление результата в операционном ритме: кто и как поддерживает логику, как отслеживаются отклонения и какие улучшения дают наибольший прирост в следующем цикле.',
        ];
        $offer['case_story_en'] = [
            'A familiar scenario: lead volume looks healthy, but useful lead share drops. Marketing sees acceptable traffic quality, sales sees low conversion quality, and product engineering gets overloaded with urgent requests.',
            'I start with a compact diagnostic and build a loss map: where signals degrade, where decisions are intuitive, and where no testable criteria exist. This prevents scope dilution and keeps rollout focused on bottlenecks.',
            'Implementation is delivered in controlled iterations, not a big-bang launch: control points first, automation second, scaling third. Teams see metric impact early without destabilizing day-to-day operations.',
            'The final stage is operationalization: ownership, deviation handling and a practical optimization loop that keeps improvements compounding instead of fading after release.',
        ];

        $offer['implementation_phases_ru'] = [
            ['title' => 'Фаза 1. Диагностика и проектирование', 'text' => 'Фиксируются baseline-метрики, карта рисков, точки интеграции и критерии приемки. На выходе — согласованный сценарий запуска и ограничений.'],
            ['title' => 'Фаза 2. Внедрение ядра', 'text' => 'Подключается ключевая логика и минимальный рабочий контур: правила обработки, валидация данных, первичный мониторинг, безопасный fallback.'],
            ['title' => 'Фаза 3. Тестовый rollout', 'text' => 'Решение запускается поэтапно: часть потока, контроль метрик, корректировка порогов, проверка нагрузочного поведения и edge-case сценариев.'],
            ['title' => 'Фаза 4. Масштабирование и регламент', 'text' => 'Контур расширяется на весь объем, формируется runbook команды, SLA-эскалации и регулярный цикл улучшений по KPI.'],
        ];
        $offer['implementation_phases_en'] = [
            ['title' => 'Phase 1. Diagnostic and design', 'text' => 'Baseline metrics, risk map, integration points and acceptance criteria are locked. Output is a clear rollout scenario with constraints.'],
            ['title' => 'Phase 2. Core implementation', 'text' => 'Key logic and minimum operating layer are deployed: handling rules, validation, primary monitoring and safe fallback behavior.'],
            ['title' => 'Phase 3. Staged rollout', 'text' => 'The system is released gradually with metric control, threshold tuning, load checks and edge-case validation.'],
            ['title' => 'Phase 4. Scale and governance', 'text' => 'The workflow is expanded to full scope with team runbook, SLA escalation and a recurring KPI optimization loop.'],
        ];

        $offer['anti_patterns_ru'] = [
            'Запускать автоматизацию без baseline-метрик и понятного KPI.',
            'Смешивать критичные правила с экспериментальными в одном релизе.',
            'Оставлять решение без fallback-сценария на случай деградации.',
            'Не фиксировать decision trail и объяснимость принятых действий.',
            'Оценивать эффект только по одному показателю, игнорируя операционную нагрузку.',
        ];
        $offer['anti_patterns_en'] = [
            'Launching automation without baseline metrics and explicit KPI.',
            'Mixing critical and experimental rules in a single release.',
            'Skipping fallback behavior for degradation scenarios.',
            'No decision trail for explainability and auditability.',
            'Measuring outcome by one metric while ignoring operational load.',
        ];
    }

    if (empty($offer['overview_ru']) || !is_array($offer['overview_ru'])) {
        $offer['overview_ru'] = [
            ($titleRu !== '' ? $titleRu : 'Этот оффер') . ' закрывает прикладную бизнес-задачу: от проектирования сценария до внедрения и контроля результата.',
            ($subtitleRu !== '' ? $subtitleRu : 'Решение строится поэтапно') . '. Сначала фиксируются требования и риски, затем подключаются интеграции и контур контроля качества.',
            ($excerptRu !== '' ? $excerptRu : 'В результате получается управляемый и прозрачный процесс.') . ' Вы получаете не отдельную доработку, а рабочий production-процесс.',
        ];
    }
    if (empty($offer['overview_en']) || !is_array($offer['overview_en'])) {
        $offer['overview_en'] = [
            ($titleEn !== '' ? $titleEn : 'This offer') . ' is built as a practical business implementation from design to rollout and measurable control.',
            ($subtitleEn !== '' ? $subtitleEn : 'Delivery is phased and controlled') . '. We lock requirements and risks first, then deploy integrations and quality gates.',
            ($excerptEn !== '' ? $excerptEn : 'The outcome is an operational and auditable workflow.') . ' You get a production process, not an isolated patch.',
        ];
    }

    if (empty($offer['sections_ru']) || !is_array($offer['sections_ru'])) {
        $offer['sections_ru'] = [
            ['title' => 'Бизнес-обоснование', 'text' => 'Внедрение строится вокруг метрик качества лидов, скорости обработки и снижения операционных потерь.'],
            ['title' => 'Технический контур внедрения', 'text' => 'Подключаются точки интеграции, настраиваются правила обработки и вводится поэтапный rollout с контролем регрессий.'],
            ['title' => 'Результат для команды', 'text' => 'Команда получает прозрачный процесс, повторяемые сценарии и понятный контроль качества на ежедневной работе.'],
        ];
    }
    if (empty($offer['sections_en']) || !is_array($offer['sections_en'])) {
        $offer['sections_en'] = [
            ['title' => 'Business rationale', 'text' => 'Implementation is aligned to lead quality, processing speed and lower operational waste.'],
            ['title' => 'Technical rollout model', 'text' => 'Integration points, handling rules and phased rollout are delivered with regression controls.'],
            ['title' => 'Team-level outcome', 'text' => 'The team gets a transparent, repeatable workflow with measurable daily quality control.'],
        ];
    }

    if (empty($offer['compliance_table_ru']) || !is_array($offer['compliance_table_ru'])) {
        $offer['compliance_table_ru'] = [
            ['scenario' => 'Сценарий без формальных правил', 'without' => 'Решения принимаются вручную и непоследовательно', 'with' => 'Единая логика обработки и фиксированные критерии'],
            ['scenario' => 'Нет прозрачного журнала событий', 'without' => 'Сложно объяснять ошибки и инциденты', 'with' => 'Появляется трассируемость и доказуемость решений'],
            ['scenario' => 'Нет приоритетов обработки', 'without' => 'Теряется время команды и SLA', 'with' => 'Заявки и задачи маршрутизируются по приоритету'],
            ['scenario' => 'Нет quality-контроля после релиза', 'without' => 'Проблемы обнаруживаются постфактум', 'with' => 'Мониторинг и контроль результата с первого дня'],
        ];
    }
    if (empty($offer['compliance_table_en']) || !is_array($offer['compliance_table_en'])) {
        $offer['compliance_table_en'] = [
            ['scenario' => 'No formal handling rules', 'without' => 'Decisions stay manual and inconsistent', 'with' => 'Unified logic with explicit criteria'],
            ['scenario' => 'No event traceability', 'without' => 'Incidents are hard to explain', 'with' => 'Clear audit trail and explainability'],
            ['scenario' => 'No processing priorities', 'without' => 'Team time and SLA are wasted', 'with' => 'Priority-aware routing and triage'],
            ['scenario' => 'No post-release quality control', 'without' => 'Issues are discovered too late', 'with' => 'Monitoring and measurable control from day one'],
        ];
    }

    if (empty($offer['checklist_ru']) || !is_array($offer['checklist_ru'])) {
        $offer['checklist_ru'] = [
            'Зафиксировать цели внедрения и KPI.',
            'Утвердить точки интеграции и контуры данных.',
            'Настроить правила обработки и исключения.',
            'Провести тестовый rollout на части трафика.',
            'Подключить мониторинг и журнал инцидентов.',
            'Зафиксировать регламент сопровождения и ответственных.',
        ];
    }
    if (empty($offer['checklist_en']) || !is_array($offer['checklist_en'])) {
        $offer['checklist_en'] = [
            'Define rollout objectives and KPI.',
            'Confirm integration points and data flows.',
            'Configure handling rules and exception paths.',
            'Run phased rollout on limited traffic.',
            'Enable monitoring and incident logging.',
            'Document ownership and support runbook.',
        ];
    }

    if (empty($offer['case_story_ru']) || !is_array($offer['case_story_ru'])) {
        $offer['case_story_ru'] = [
            'Гипотетический кейс: в проекте накопились технические и процессные искажения, из-за которых бизнес видит нестабильный результат при вроде бы нормальном трафике и активности команды.',
            'Вместо точечных исправлений строится единый контур внедрения: фиксируются критерии, выстраивается операционная логика и подключается измеримый контроль качества.',
            'После запуска решение перестает зависеть от ручного "героизма": команда работает по понятному сценарию, а отклонения обнаруживаются и устраняются быстрее.',
            'Это дает эффект не только в цифрах, но и в управляемости: меньше споров о причинах проблем и больше предсказуемых действий по улучшению.',
        ];
    }
    if (empty($offer['case_story_en']) || !is_array($offer['case_story_en'])) {
        $offer['case_story_en'] = [
            'A practical scenario: technical and process distortions accumulate, so business outcomes stay unstable despite healthy-looking traffic and activity.',
            'Instead of isolated fixes, rollout is built as one operating workflow with explicit criteria, stable execution logic and measurable quality controls.',
            'After launch, the system no longer depends on manual heroics: the team executes by design and deviations are detected faster.',
            'This improves not only metrics, but governance: less debate about causes and more predictable optimization actions.',
        ];
    }

    if (empty($offer['implementation_phases_ru']) || !is_array($offer['implementation_phases_ru'])) {
        $offer['implementation_phases_ru'] = [
            ['title' => 'Фаза 1. Подготовка', 'text' => 'Формируется контекст задачи, baseline и критерии приемки.'],
            ['title' => 'Фаза 2. Реализация', 'text' => 'Внедряется рабочее ядро решения с безопасными ограничениями.'],
            ['title' => 'Фаза 3. Проверка', 'text' => 'Запуск в тестовом контуре, анализ отклонений и корректировки.'],
            ['title' => 'Фаза 4. Масштабирование', 'text' => 'Перевод в регулярную эксплуатацию с регламентом и KPI-циклом.'],
        ];
    }
    if (empty($offer['implementation_phases_en']) || !is_array($offer['implementation_phases_en'])) {
        $offer['implementation_phases_en'] = [
            ['title' => 'Phase 1. Preparation', 'text' => 'Problem context, baseline and acceptance criteria are defined.'],
            ['title' => 'Phase 2. Implementation', 'text' => 'Core solution is deployed with safe operational constraints.'],
            ['title' => 'Phase 3. Validation', 'text' => 'Staged launch, deviation analysis and controlled tuning.'],
            ['title' => 'Phase 4. Scale', 'text' => 'Operational rollout with runbook and KPI improvement loop.'],
        ];
    }

    if (empty($offer['anti_patterns_ru']) || !is_array($offer['anti_patterns_ru'])) {
        $offer['anti_patterns_ru'] = [
            'Внедрять без метрик и критериев успешности.',
            'Перегружать первый релиз второстепенным функционалом.',
            'Игнорировать риск деградации после запуска.',
            'Оставлять процесс без ответственного и регламента.',
        ];
    }
    if (empty($offer['anti_patterns_en']) || !is_array($offer['anti_patterns_en'])) {
        $offer['anti_patterns_en'] = [
            'Implementing without metrics and success criteria.',
            'Overloading the first release with secondary scope.',
            'Ignoring post-launch degradation risks.',
            'No ownership or operational runbook.',
        ];
    }

    if (empty($offer['additional_offers']) || !is_array($offer['additional_offers'])) {
        $offer['additional_offers'] = [];
        $related = (array)($relatedMap[$slug] ?? []);
        foreach ($related as $relatedSlug) {
            $ruTitle = trim((string)($titlesRu[$relatedSlug] ?? ''));
            $enTitle = trim((string)($titlesEn[$relatedSlug] ?? ''));
            if ($ruTitle === '' && $enTitle === '') {
                continue;
            }
            $offer['additional_offers'][] = [
                'slug' => $relatedSlug,
                'title_ru' => $ruTitle !== '' ? $ruTitle : $enTitle,
                'title_en' => $enTitle !== '' ? $enTitle : $ruTitle,
            ];
        }
    }

    return $offer;
};

foreach ($offersData['items'] as $k => $offerItem) {
    $offersData['items'][$k] = $ensureRichOffer((array)$offerItem, $titleBySlugRu, $titleBySlugEn, $relatedOfferMap, $offerSeoProfiles);
}

foreach ($offersData['items'] as &$itemRef) {
    $itemRef['title'] = $isRu ? (string)($itemRef['title_ru'] ?? '') : (string)($itemRef['title_en'] ?? '');
    $itemRef['subtitle'] = $isRu ? (string)($itemRef['subtitle_ru'] ?? '') : (string)($itemRef['subtitle_en'] ?? '');
    $itemRef['excerpt'] = $isRu ? (string)($itemRef['excerpt_ru'] ?? '') : (string)($itemRef['excerpt_en'] ?? '');
    $itemRef['price_label'] = $isRu ? (string)($itemRef['price_label_ru'] ?? '') : (string)($itemRef['price_label_en'] ?? '');
}
unset($itemRef);

$selectedSlug = trim((string)($_GET['offer'] ?? $_GET['slug'] ?? ''));
if ($selectedSlug === '') {
    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? trim($requestPath) : '';
    $segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
        return $value !== '';
    }));
    if (isset($segments[0]) && strtolower((string)$segments[0]) === 'offers' && isset($segments[1])) {
        $selectedSlug = trim((string)$segments[1]);
    }
}

$selectedSlug = strtolower((string)preg_replace('/[^a-z0-9_-]/', '', $selectedSlug));
if ($selectedSlug !== '') {
    foreach ((array)$offersData['items'] as $item) {
        if ((string)($item['slug'] ?? '') === $selectedSlug) {
            $offersData['selected'] = $item;
            $offersData['selected_slug'] = $selectedSlug;
            break;
        }
    }
}

if (!empty($offersData['selected_slug']) && isset($FRMWRK) && is_object($FRMWRK) && method_exists($FRMWRK, 'DB') && method_exists($FRMWRK, 'DBRecords')) {
    $db = $FRMWRK->DB();
    if ($db instanceof mysqli) {
        $hostSafe = mysqli_real_escape_string($db, (string)$offersData['host']);
        $langSafe = mysqli_real_escape_string($db, (string)$offersData['lang']);
        $hasCluster = false;
        $clusterCheck = mysqli_query($db, "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='examples_articles' AND COLUMN_NAME='cluster_code' LIMIT 1");
        if ($clusterCheck && mysqli_num_rows($clusterCheck) > 0) {
            $hasCluster = true;
        }
        $clusterSelect = $hasCluster ? ', cluster_code' : ", '' AS cluster_code";
        $rows = $FRMWRK->DBRecords(
            "SELECT title, slug{$clusterSelect}
             FROM examples_articles
             WHERE is_published = 1
               AND title <> ''
               AND slug <> ''
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
             ORDER BY id DESC
             LIMIT 6"
        );
        foreach ($rows as $row) {
            $title = trim((string)($row['title'] ?? ''));
            $slug = trim((string)($row['slug'] ?? ''));
            if ($title === '' || $slug === '') {
                continue;
            }
            $cluster = trim((string)($row['cluster_code'] ?? ''));
            $url = $cluster !== ''
                ? ('/blog/' . rawurlencode($cluster) . '/' . rawurlencode($slug) . '/')
                : ('/blog/' . rawurlencode($slug) . '/');
            $offersData['related_blog'][] = ['title' => $title, 'url' => $url];
        }
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = preg_replace('/^www\./', '', (string)$offersData['host']);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$selected = is_array($offersData['selected']) ? (array)$offersData['selected'] : [];
$isDetail = !empty($selected);

$selectedTitle = trim((string)($selected['title'] ?? ''));
$selectedExcerpt = trim((string)($selected['excerpt'] ?? ''));
$canonical = $isDetail
    ? ($baseUrl . '/offers/' . rawurlencode((string)($selected['slug'] ?? '')) . '/')
    : ($baseUrl . '/offers/');

if (empty($ModelPage['title'])) {
    $ModelPage['title'] = $isDetail
        ? ($selectedTitle !== '' ? $selectedTitle : ($isRu ? 'Офферы' : 'Offers'))
        : ($isRu ? 'Офферы — прикладные интеграции' : 'Offers — implementation-ready integrations');
}
if (empty($ModelPage['description'])) {
    $ModelPage['description'] = $isDetail
        ? ($selectedExcerpt !== '' ? $selectedExcerpt : ($isRu ? 'Детальный оффер интеграции.' : 'Detailed implementation offer.'))
        : ($isRu ? 'Раздел офферов: интеграции, автоматизация, архитектура и AI-решения.' : 'Offers catalog: integrations, automation, architecture and AI delivery.');
}
if (empty($ModelPage['keywords'])) {
    $ModelPage['keywords'] = $isRu
        ? 'офферы, интеграции, bitrix, bitrix24, 1с, telegram бот, mvp, geoip, antifraud, seo'
        : 'offers, integrations, bitrix, bitrix24, 1c, telegram bot, mvp, geoip, antifraud, seo';
}
if (empty($ModelPage['canonical'])) {
    $ModelPage['canonical'] = $canonical;
}

$ModelPage['offers_catalog'] = $offersData;
