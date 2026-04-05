-- SEO generator strategy update (recommendation-driven)
-- Date: 2026-04-04
-- Backup first:
-- SELECT settings_json INTO OUTFILE '/tmp/seo_generator_settings_backup_2026_04_04.json' FROM seo_generator_settings WHERE id=1;

UPDATE seo_generator_settings
SET settings_json = JSON_SET(
    settings_json,
    '$.prompt_version', 'seo-cron-v2-intent-services-x9',
    '$.openai_proxy_enabled', false,
    '$.openai_proxy_pool_enabled', false,

    '$.portfolio_bofu_weight', 30,
    '$.portfolio_mofu_weight', 30,
    '$.portfolio_authority_weight', 20,
    '$.portfolio_case_weight', 10,
    '$.portfolio_product_weight', 10,

    '$.service_focus_ru', JSON_ARRAY(
        'разработка сайтов под коммерческие задачи',
        'корпоративные порталы и личные кабинеты',
        'интеграции CMS CRM и учетных систем',
        'автоматизация процессов и разработка ботов',
        'MVP запуск и валидация',
        'архитектурный аудит веб-приложений',
        'техническое SEO для коммерческих страниц',
        'ИИ в разработке и проектировании'
    ),
    '$.service_focus_en', JSON_ARRAY(
        'business websites and conversion architecture',
        'corporate portals and account areas',
        'CMS and CRM integration',
        'workflow automation and bots',
        'MVP delivery and validation',
        'web application architecture audits',
        'technical SEO implementation',
        'AI-assisted engineering workflows'
    ),

    '$.forbidden_topics_ru', JSON_ARRAY(
        'SaaS как основная тема статьи',
        'сравнение брендов и конкурентов',
        'рейтинги вендоров'
    ),
    '$.forbidden_topics_en', JSON_ARRAY(
        'SaaS as the primary article angle',
        'competitor brand comparisons',
        'vendor ranking lists'
    ),

    '$.clusters_ru', JSON_ARRAY(
        'разработка сайтов под бизнес-задачи',
        'архитектура веб-приложений и масштабирование',
        'аудитирование веб-проектов и технический долг',
        'MVP: запуск, валидация и эволюция',
        'интеграции CMS, CRM и учетных систем',
        'автоматизация процессов и workflow-дизайн',
        'боты для продаж, поддержки и внутренних процессов',
        'техническое SEO для коммерческих сайтов',
        'контентная архитектура и SEO-структура',
        'использование ИИ в разработке и проектировании',
        'инженерные практики качества и релизов',
        'безопасность веб-приложений и операционная устойчивость'
    ),
    '$.clusters_en', JSON_ARRAY(
        'business website engineering',
        'web application architecture and scaling',
        'technical audits and debt remediation',
        'MVP launch and product validation',
        'CMS CRM ERP integration patterns',
        'workflow automation for operations',
        'bots for sales support and internal ops',
        'technical SEO for commercial websites',
        'content architecture for organic growth',
        'AI-assisted engineering and design workflows',
        'quality engineering and release reliability',
        'web security and operational resilience'
    ),

    '$.article_cluster_taxonomy_ru', JSON_ARRAY(
        JSON_OBJECT('key','website_dev','weight',1.0,'label_en','Website Development','label_ru','Разработка сайтов','keywords','разработка сайта, корпоративный сайт, UX, конверсия, CMS, frontend, backend'),
        JSON_OBJECT('key','integrations','weight',0.95,'label_en','Integrations','label_ru','Интеграции','keywords','CRM интеграция, API, webhook, обмен данными, ERP, Битрикс, 1С'),
        JSON_OBJECT('key','audit_architecture','weight',0.9,'label_en','Audit & Architecture','label_ru','Аудит и архитектура','keywords','аудит, архитектура, технический долг, рефакторинг, масштабирование, reliability'),
        JSON_OBJECT('key','mvp_delivery','weight',0.85,'label_en','MVP Delivery','label_ru','Запуск MVP','keywords','MVP, time-to-market, валидация гипотез, итерации, roadmap'),
        JSON_OBJECT('key','bots_automation','weight',0.85,'label_en','Bots & Automation','label_ru','Боты и автоматизация','keywords','боты, автоматизация, workflow, операционные процессы, лиды, поддержка'),
        JSON_OBJECT('key','seo_content','weight',0.9,'label_en','Technical SEO','label_ru','Техническое SEO','keywords','техническое seo, перелинковка, структура контента, коммерческие запросы, конверсия'),
        JSON_OBJECT('key','ai_engineering','weight',0.85,'label_en','AI Engineering','label_ru','ИИ в разработке','keywords','ИИ в разработке, ai-assisted coding, проектирование, ускорение поставки, quality gates'),
        JSON_OBJECT('key','case_study','weight',0.8,'label_en','Case Study','label_ru','Кейсы','keywords','кейс, до после, ограничения, внедрение, результат, KPI')
    ),

    '$.article_cluster_taxonomy_en', JSON_ARRAY(
        JSON_OBJECT('key','website_dev','weight',1.0,'label_en','Website Development','label_ru','Разработка сайтов','keywords','website development, conversion architecture, CMS, UX, frontend, backend'),
        JSON_OBJECT('key','integrations','weight',0.95,'label_en','Integrations','label_ru','Интеграции','keywords','CRM integration, API, webhook, ERP sync, data exchange'),
        JSON_OBJECT('key','audit_architecture','weight',0.9,'label_en','Audit & Architecture','label_ru','Аудит и архитектура','keywords','technical audit, architecture, debt remediation, refactor, scalability, reliability'),
        JSON_OBJECT('key','mvp_delivery','weight',0.85,'label_en','MVP Delivery','label_ru','Запуск MVP','keywords','MVP, validation, time-to-market, iteration planning, rollout'),
        JSON_OBJECT('key','bots_automation','weight',0.85,'label_en','Bots & Automation','label_ru','Боты и автоматизация','keywords','bots, automation, workflow, ops optimization, support, lead routing'),
        JSON_OBJECT('key','seo_content','weight',0.9,'label_en','Technical SEO','label_ru','Техническое SEO','keywords','technical SEO, content architecture, internal linking, commercial intent'),
        JSON_OBJECT('key','ai_engineering','weight',0.85,'label_en','AI Engineering','label_ru','ИИ в разработке','keywords','AI in engineering, AI-assisted workflows, quality gates, architecture decisions'),
        JSON_OBJECT('key','case_study','weight',0.8,'label_en','Case Study','label_ru','Кейсы','keywords','case study, before after, constraints, implementation, business outcomes')
    )
)
WHERE id = 1;
