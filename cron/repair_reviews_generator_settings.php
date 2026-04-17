<?php
ini_set('display_errors', '1');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/libs/seo_generator_settings.php';

function repair_reviews_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function repair_reviews_maybe_fix_mojibake(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return $value;
    }

    if (!preg_match('/(?:Р.|С.|вЂ|Ѓ|‚|€|™|њ|ќ)/u', $value)) {
        return $value;
    }

    $fixed = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
    if (!is_string($fixed) || $fixed === '') {
        return $value;
    }

    $origScore = preg_match_all('/[А-Яа-яЁё]/u', $value, $m1);
    $fixedScore = preg_match_all('/[А-Яа-яЁё]/u', $fixed, $m2);
    if ((int)$fixedScore < (int)$origScore) {
        return $value;
    }

    return $fixed;
}

function repair_reviews_walk($value)
{
    if (is_array($value)) {
        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = repair_reviews_walk($item);
        }
        return $out;
    }
    if (is_string($value)) {
        return repair_reviews_maybe_fix_mojibake($value);
    }
    return $value;
}

function repair_reviews_default_campaign(): array
{
    return [
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
        'styles_en' => ['comparison review', 'tool benchmark', 'provider shortlist', 'buyer guide', 'stack review', 'field comparison', 'decision matrix', 'vendor radar', 'pricing teardown', 'use-case match review', 'workflow fit review', 'support quality audit', 'scaling stress test', 'operator verdict', 'category leaderboard', 'best-for list', 'procurement memo', 'migration comparison', 'cost-of-stack review', 'hands-on test note'],
        'styles_ru' => ['сравнительный обзор', 'бенчмарк инструментов', 'шортлист поставщиков', 'buyer guide', 'разбор стека', 'полевое сравнение', 'матрица выбора', 'радар поставщиков', 'разбор ценообразования', 'обзор fit под use-case', 'разбор fit в workflow', 'аудит качества саппорта', 'стресс-тест на масштаб', 'вердикт оператора', 'лидерборд категории', 'подборка best-for', 'мемо по закупке', 'сравнение для миграции', 'обзор стоимости стека', 'заметка по hands-on тесту'],
        'clusters_en' => ['affiliate networks and partner programs', 'cloaking tools and routing stacks', 'tracker platforms and attribution quality', 'ai generators for creatives and workflows', 'server providers and hosting reliability', 'domain registrars and domain vendors', 'anti-detect browsers and operator environments', 'spy tools and ad intelligence suites', 'proxy providers and mobile proxy pools', 'landing builders and page infrastructure', 'payment tools and routing helpers', 'telegram tools and team utilities', 'hosting panels and deployment workflows', 'cdn and edge delivery for landers', 'analytics dashboards and reporting layers', 'team knowledge bases and SOP tools', 'creative storage and asset pipelines', 'link rotators and redirect managers', 'captcha solving services and automation helpers', 'warmup tools and account farming utilities', 'offer intelligence and payout comparison tools', 'notification systems and alerting stacks', 'moderation monitoring and compliance helpers', 'comment management and community tools', 'crm tools for affiliate operators', 'checkout recovery and conversion helpers', 'template libraries and landing kits', 'browser automation and QA helpers', 'content generation assistants for operators', 'geo-routing and localization helpers'],
        'clusters_ru' => ['партнерки и affiliate-сети', 'клоаки и routing-стеки', 'трекеры и качество атрибуции', 'ИИ-генераторы для креативов и workflow', 'серверные провайдеры и надежность хостинга', 'регистраторы доменов и доменные поставщики', 'anti-detect браузеры и среда оператора', 'spy-сервисы и ad-intelligence наборы', 'proxy-поставщики и mobile proxy-пулы', 'конструкторы лендингов и инфраструктура страниц', 'платежные сервисы и routing helpers', 'Telegram-инструменты и утилиты команды', 'панели хостинга и workflow деплоя', 'CDN и edge-доставка для лендингов', 'аналитические дашборды и reporting-слой', 'базы знаний команды и SOP-инструменты', 'хранилища креативов и asset-пайплайны', 'ротаторы ссылок и redirect-менеджеры', 'сервисы решения captcha и automation helpers', 'warmup-инструменты и утилиты для фарма аккаунтов', 'сервисы по офферам и сравнению payout', 'системы уведомлений и alerting-стеки', 'мониторинг модерации и compliance-инструменты', 'сервисы комментариев и community-утилиты', 'CRM для affiliate-операторов', 'checkout-recovery и conversion helpers', 'библиотеки шаблонов и landing-kits', 'browser-automation и QA-утилиты', 'content-assist инструменты для операторов', 'geo-routing и localization helpers'],
        'article_structures_en' => ['Category -> evaluation criteria -> shortlist -> comparison table -> recommendation', 'Use case -> stack candidates -> benchmarks -> tradeoffs -> verdict', 'Problem -> providers -> strengths and weaknesses -> risk notes -> best fit', 'Need -> operator constraints -> candidate stack -> elimination logic -> final pick', 'Budget tier -> options -> hidden costs -> scaling ceiling -> recommendation', 'Scenario -> review criteria -> top picks -> red flags -> deployment notes', 'Role-based review -> who needs it -> best options -> workflow fit -> conclusion', 'Benchmark setup -> measured results -> interpretation -> tradeoffs -> operator verdict', 'Migration path -> current pain -> replacement options -> switching cost -> recommendation', 'Comparison table -> strongest use cases -> support and pricing -> final shortlist', 'Field test -> onboarding -> daily usage -> failure modes -> verdict', 'Best-for ranking -> category leaders -> edge cases -> who should skip -> close', 'Procurement memo -> must-have criteria -> vendor review -> negotiation points -> recommendation', 'Stack layer -> available tools -> compatibility notes -> risk map -> final shortlist', 'Problem map -> tool classes -> best fit by team maturity -> recommendation'],
        'article_structures_ru' => ['Категория -> критерии оценки -> шортлист -> таблица сравнения -> рекомендация', 'Use-case -> кандидаты в стек -> бенчмарки -> tradeoff -> вердикт', 'Проблема -> провайдеры -> сильные и слабые стороны -> риски -> лучший fit', 'Потребность -> ограничения оператора -> кандидаты в стек -> логика отсева -> финальный выбор', 'Бюджетный уровень -> варианты -> скрытые расходы -> потолок масштаба -> рекомендация', 'Сценарий -> критерии обзора -> лучшие варианты -> red flags -> заметки по внедрению', 'Обзор по ролям -> кому подходит -> лучшие опции -> fit в workflow -> вывод', 'Схема бенчмарка -> измеренные результаты -> интерпретация -> tradeoff -> вердикт оператора', 'Путь миграции -> текущая боль -> варианты замены -> стоимость перехода -> рекомендация', 'Таблица сравнения -> сильные use-case -> саппорт и цены -> финальный шортлист', 'Полевой тест -> онбординг -> ежедневное использование -> failure modes -> вердикт', 'Рейтинг best-for -> лидеры категории -> edge cases -> кому не подходит -> итог', 'Мемо по закупке -> must-have критерии -> обзор вендоров -> точки переговоров -> рекомендация', 'Слой стека -> доступные инструменты -> совместимость -> карта рисков -> финальный шортлист', 'Карта проблемы -> классы инструментов -> fit по зрелости команды -> рекомендация'],
        'article_system_prompt_en' => '',
        'article_system_prompt_ru' => '',
        'article_user_prompt_append_en' => 'Write real reviews, comparisons and curated shortlists for affiliate operators. Compare tools and vendors by concrete criteria: routing reliability, moderation resilience, attribution quality, pricing logic, onboarding friction, geo-fit, support quality, scaling limits, hidden risks and who each option is actually for. Push for angle diversity: rotate between leaderboards, decision matrices, role-based picks, budget tiers, migration reviews, category radars, field tests, pricing teardowns, use-case match guides and stack-layer comparisons. External resources are allowed when useful.',
        'article_user_prompt_append_ru' => 'Пиши реальные обзоры, сравнения и подборки для affiliate-операторов. Сравнивай инструменты и поставщиков по конкретным критериям: надежность routing, устойчивость под модерацией, качество атрибуции, логика цен, сложность онбординга, fit по гео, качество саппорта, ограничения по масштабу, скрытые риски и для кого подходит каждый вариант. Принудительно повышай вариативность углов: чередуй leaderboards, decision matrix, обзоры по ролям команды, бюджетные уровни, сценарии миграции, category radar, полевые тесты, разборы ценообразования, use-case match guide и сравнение отдельных слоев стека. Если тема уже поднималась, меняй не только инструмент, но и контекст выбора: другой бюджет, другая зрелость команды, другой GEO-fit, другой масштаб, другой failure mode, другой слой пайплайна. В этом разделе допустимы ссылки на сторонние ресурсы, если они помогают обзору.',
    ];
}

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!($DB instanceof mysqli)) {
    repair_reviews_echo('DB connection is not available.');
    exit(1);
}

if (!function_exists('seo_gen_settings_get') || !function_exists('seo_gen_settings_save') || !function_exists('seo_gen_default_campaigns')) {
    repair_reviews_echo('SEO generator settings library is not available.');
    exit(2);
}

$settings = seo_gen_settings_get($DB);
$beforeCampaigns = is_array($settings['campaigns'] ?? null) ? $settings['campaigns'] : [];
$defaults = seo_gen_default_campaigns();
$reviewsDefault = is_array($defaults['reviews'] ?? null) ? $defaults['reviews'] : repair_reviews_default_campaign();

if (empty($reviewsDefault)) {
    repair_reviews_echo('Reviews campaign defaults are missing.');
    exit(3);
}

$settings = repair_reviews_walk($settings);
$settings['campaigns'] = is_array($settings['campaigns'] ?? null) ? $settings['campaigns'] : [];

$currentReviews = is_array($settings['campaigns']['reviews'] ?? null) ? $settings['campaigns']['reviews'] : [];
$settings['campaigns']['reviews'] = array_merge($reviewsDefault, $currentReviews, [
    'key' => 'reviews',
    'material_section' => 'reviews',
    'styles_en' => (array)($reviewsDefault['styles_en'] ?? []),
    'styles_ru' => (array)($reviewsDefault['styles_ru'] ?? []),
    'clusters_en' => (array)($reviewsDefault['clusters_en'] ?? []),
    'clusters_ru' => (array)($reviewsDefault['clusters_ru'] ?? []),
    'article_structures_en' => (array)($reviewsDefault['article_structures_en'] ?? []),
    'article_structures_ru' => (array)($reviewsDefault['article_structures_ru'] ?? []),
    'article_user_prompt_append_en' => (string)($reviewsDefault['article_user_prompt_append_en'] ?? ''),
    'article_user_prompt_append_ru' => (string)($reviewsDefault['article_user_prompt_append_ru'] ?? ''),
]);

if (!seo_gen_settings_save($DB, $settings, 0)) {
    repair_reviews_echo('Failed to save settings.');
    exit(4);
}

$reloaded = seo_gen_settings_get($DB);
$reviews = is_array($reloaded['campaigns']['reviews'] ?? null) ? $reloaded['campaigns']['reviews'] : [];

repair_reviews_echo('Reviews campaign repaired and expanded.');
repair_reviews_echo('Styles EN: ' . count((array)($reviews['styles_en'] ?? [])));
repair_reviews_echo('Styles RU: ' . count((array)($reviews['styles_ru'] ?? [])));
repair_reviews_echo('Clusters EN: ' . count((array)($reviews['clusters_en'] ?? [])));
repair_reviews_echo('Clusters RU: ' . count((array)($reviews['clusters_ru'] ?? [])));
repair_reviews_echo('Structures EN: ' . count((array)($reviews['article_structures_en'] ?? [])));
repair_reviews_echo('Structures RU: ' . count((array)($reviews['article_structures_ru'] ?? [])));
repair_reviews_echo('Material section: ' . (string)($reviews['material_section'] ?? ''));
repair_reviews_echo('Previously had reviews: ' . (isset($beforeCampaigns['reviews']) ? 'yes' : 'no'));

exit(0);
