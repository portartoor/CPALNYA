<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/libs/seo_generator_settings.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = 'info';

function admin_seo_gen_post_bool(string $name, bool $default = false): bool
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    $v = (string)$_POST[$name];
    return in_array($v, ['1', 'true', 'on', 'yes'], true);
}

function admin_seo_gen_post_int(string $name, int $default): int
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    return (int)$_POST[$name];
}

function admin_seo_gen_post_string(string $name, string $default = ''): string
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    return trim((string)$_POST[$name]);
}

function admin_seo_gen_table_exists(mysqli $db, string $table): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $sql = "SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tableSafe}'
            LIMIT 1";
    $res = mysqli_query($db, $sql);
    return $res && mysqli_num_rows($res) > 0;
}

function admin_seo_gen_parse_moods(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 4) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $labelEn = (string)$parts[2];
        $labelRu = (string)$parts[3];
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'label_en' => $labelEn !== '' ? $labelEn : $key,
            'label_ru' => $labelRu !== '' ? $labelRu : $key,
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_parse_color_schemes(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 2) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $instruction = (string)($parts[2] ?? $key);
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'instruction' => $instruction !== '' ? $instruction : $key,
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_parse_image_compositions(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 4) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $labelEn = (string)$parts[2];
        $labelRu = (string)$parts[3];
        $instruction = (string)($parts[4] ?? '');
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'label_en' => $labelEn !== '' ? $labelEn : $key,
            'label_ru' => $labelRu !== '' ? $labelRu : $key,
            'instruction' => $instruction !== '' ? $instruction : ($labelEn !== '' ? $labelEn : $key),
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_parse_image_scene_families(string $raw): array
{
    $rows = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    if (!is_array($rows)) {
        return $out;
    }
    foreach ($rows as $line) {
        $line = trim((string)$line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 4) {
            continue;
        }
        $key = strtolower((string)$parts[0]);
        if ($key === '' || !preg_match('/^[a-z0-9_\\-]{2,64}$/', $key)) {
            continue;
        }
        $weight = (float)$parts[1];
        $labelEn = (string)$parts[2];
        $labelRu = (string)$parts[3];
        $instruction = (string)($parts[4] ?? '');
        $out[] = [
            'key' => $key,
            'weight' => $weight > 0 ? $weight : 1.0,
            'label_en' => $labelEn !== '' ? $labelEn : $key,
            'label_ru' => $labelRu !== '' ? $labelRu : $key,
            'instruction' => $instruction !== '' ? $instruction : ($labelEn !== '' ? $labelEn : $key),
        ];
        if (count($out) >= 80) {
            break;
        }
    }
    return $out;
}

function admin_seo_gen_reviews_campaign_fallback(): array
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
            'hosting panels and deployment workflows',
            'cdn and edge delivery for landers',
            'analytics dashboards and reporting layers',
            'team knowledge bases and SOP tools',
            'creative storage and asset pipelines',
            'link rotators and redirect managers',
            'captcha solving services and automation helpers',
            'warmup tools and account farming utilities',
            'offer intelligence and payout comparison tools',
            'notification systems and alerting stacks',
            'moderation monitoring and compliance helpers',
            'comment management and community tools',
            'crm tools for affiliate operators',
            'checkout recovery and conversion helpers',
            'template libraries and landing kits',
            'browser automation and QA helpers',
            'content generation assistants for operators',
            'geo-routing and localization helpers',
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
            'панели хостинга и workflow деплоя',
            'CDN и edge-доставка для лендингов',
            'аналитические дашборды и reporting-слой',
            'базы знаний команды и SOP-инструменты',
            'хранилища креативов и asset-пайплайны',
            'ротаторы ссылок и redirect-менеджеры',
            'сервисы решения captcha и automation helpers',
            'warmup-инструменты и утилиты для фарма аккаунтов',
            'сервисы по офферам и сравнению payout',
            'системы уведомлений и alerting-стеки',
            'мониторинг модерации и compliance-инструменты',
            'сервисы комментариев и community-утилиты',
            'CRM для affiliate-операторов',
            'checkout-recovery и conversion helpers',
            'библиотеки шаблонов и landing-kits',
            'browser-automation и QA-утилиты',
            'content-assist инструменты для операторов',
            'geo-routing и localization helpers',
        ],
        'article_structures_en' => [
            'Category -> evaluation criteria -> shortlist -> comparison table -> recommendation',
            'Use case -> stack candidates -> benchmarks -> tradeoffs -> verdict',
            'Problem -> providers -> strengths and weaknesses -> risk notes -> best fit',
            'Need -> operator constraints -> candidate stack -> elimination logic -> final pick',
            'Budget tier -> options -> hidden costs -> scaling ceiling -> recommendation',
            'Scenario -> review criteria -> top picks -> red flags -> deployment notes',
            'Role-based review -> who needs it -> best options -> workflow fit -> conclusion',
            'Benchmark setup -> measured results -> interpretation -> tradeoffs -> operator verdict',
            'Migration path -> current pain -> replacement options -> switching cost -> recommendation',
            'Comparison table -> strongest use cases -> support and pricing -> final shortlist',
            'Field test -> onboarding -> daily usage -> failure modes -> verdict',
            'Best-for ranking -> category leaders -> edge cases -> who should skip -> close',
            'Procurement memo -> must-have criteria -> vendor review -> negotiation points -> recommendation',
            'Stack layer -> available tools -> compatibility notes -> risk map -> final shortlist',
            'Problem map -> tool classes -> best fit by team maturity -> recommendation',
        ],
        'article_structures_ru' => [
            'Категория -> критерии оценки -> шортлист -> таблица сравнения -> рекомендация',
            'Use-case -> кандидаты в стек -> бенчмарки -> tradeoff -> вердикт',
            'Проблема -> провайдеры -> сильные и слабые стороны -> риски -> лучший fit',
            'Потребность -> ограничения оператора -> кандидаты в стек -> логика отсева -> финальный выбор',
            'Бюджетный уровень -> варианты -> скрытые расходы -> потолок масштаба -> рекомендация',
            'Сценарий -> критерии обзора -> лучшие варианты -> red flags -> заметки по внедрению',
            'Обзор по ролям -> кому подходит -> лучшие опции -> fit в workflow -> вывод',
            'Схема бенчмарка -> измеренные результаты -> интерпретация -> tradeoff -> вердикт оператора',
            'Путь миграции -> текущая боль -> варианты замены -> стоимость перехода -> рекомендация',
            'Таблица сравнения -> сильные use-case -> саппорт и цены -> финальный шортлист',
            'Полевой тест -> онбординг -> ежедневное использование -> failure modes -> вердикт',
            'Рейтинг best-for -> лидеры категории -> edge cases -> кому не подходит -> итог',
            'Мемо по закупке -> must-have критерии -> обзор вендоров -> точки переговоров -> рекомендация',
            'Слой стека -> доступные инструменты -> совместимость -> карта рисков -> финальный шортлист',
            'Карта проблемы -> классы инструментов -> fit по зрелости команды -> рекомендация',
        ],
        'article_system_prompt_en' => '',
        'article_system_prompt_ru' => '',
        'article_user_prompt_append_en' => 'Write real reviews, comparisons and curated shortlists for affiliate operators. Compare tools and vendors by concrete criteria: routing reliability, moderation resilience, attribution quality, pricing logic, onboarding friction, geo-fit, support quality, scaling limits, hidden risks and who each option is actually for. External resources are allowed when useful.',
        'article_user_prompt_append_ru' => 'Пиши реальные обзоры, сравнения и подборки для affiliate-операторов. Сравнивай инструменты и поставщиков по конкретным критериям: надежность routing, устойчивость под модерацией, качество атрибуции, логика цен, сложность онбординга, fit по гео, качество саппорта, ограничения по масштабу, скрытые риски и для кого подходит каждый вариант. Принудительно повышай вариативность углов: чередуй leaderboards, decision matrix, обзоры по ролям команды, бюджетные уровни, сценарии миграции, category radar, полевые тесты, разборы ценообразования, use-case match guide и сравнение отдельных слоев стека. Если тема уже поднималась, меняй не только инструмент, но и контекст выбора: другой бюджет, другая зрелость команды, другой GEO-fit, другой масштаб, другой failure mode, другой слой пайплайна. В этом разделе допустимы ссылки на сторонние ресурсы, если они помогают обзору.',
    ];
}

function admin_seo_gen_reviews_campaign_payload(): array
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
        'article_structures_ru' => ['Категория -> критерии оценки -> шортлист -> таблица сравнения -> рекомендация', 'Use-case -> кандидаты в стек -> бенчмарки -> tradeoffs -> вердикт', 'Проблема -> провайдеры -> сильные и слабые стороны -> риски -> лучший fit', 'Потребность -> ограничения оператора -> кандидаты в стек -> логика отсева -> финальный выбор', 'Бюджетный уровень -> варианты -> скрытые расходы -> потолок масштаба -> рекомендация', 'Сценарий -> критерии обзора -> лучшие варианты -> red flags -> заметки по внедрению', 'Обзор по ролям -> кому подходит -> лучшие опции -> fit в workflow -> вывод', 'Схема бенчмарка -> измеренные результаты -> интерпретация -> tradeoffs -> вердикт оператора', 'Путь миграции -> текущая боль -> варианты замены -> стоимость перехода -> рекомендация', 'Таблица сравнения -> сильные use-case -> саппорт и цены -> финальный шортлист', 'Полевой тест -> онбординг -> ежедневное использование -> failure modes -> вердикт', 'Рейтинг best-for -> лидеры категории -> edge cases -> кому не подходит -> итог', 'Мемо по закупке -> must-have критерии -> обзор вендоров -> точки переговоров -> рекомендация', 'Слой стека -> доступные инструменты -> совместимость -> карта рисков -> финальный шортлист', 'Карта проблемы -> классы инструментов -> fit по зрелости команды -> рекомендация'],
        'article_system_prompt_en' => '',
        'article_system_prompt_ru' => '',
        'article_user_prompt_append_en' => 'Write real reviews, comparisons and curated shortlists for affiliate operators. Compare tools and vendors by concrete criteria: routing reliability, moderation resilience, attribution quality, pricing logic, onboarding friction, geo-fit, support quality, scaling limits, hidden risks and who each option is actually for. External resources are allowed when useful.',
        'article_user_prompt_append_ru' => 'Пиши реальные обзоры, сравнения и подборки для affiliate-операторов. Сравнивай инструменты и поставщиков по конкретным критериям: надежность routing, устойчивость под модерацией, качество атрибуции, логика цен, сложность онбординга, fit по гео, качество саппорта, ограничения по масштабу, скрытые риски и для кого подходит каждый вариант. В этом разделе допустимы ссылки на сторонние ресурсы, если они помогают обзору.',
    ];
}

function admin_seo_gen_merge_missing_recursive(array $base, array $overlay): array
{
    foreach ($overlay as $key => $value) {
        if (!array_key_exists($key, $base)) {
            $base[$key] = $value;
            continue;
        }
        if (is_array($base[$key]) && is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
            $base[$key] = admin_seo_gen_merge_missing_recursive($base[$key], $value);
        }
    }
    return $base;
}

if (!$DB) {
    $message = 'Database connection failed.';
    $messageType = 'danger';
    $seoGeneratorSettings = seo_gen_settings_default();
    return;
}

seo_gen_settings_table_ensure($DB);
if (function_exists('seo_gen_cron_runs_table_ensure')) {
    seo_gen_cron_runs_table_ensure($DB);
}
$seoGeneratorSettings = seo_gen_settings_get($DB);
$seoGeneratorSettingsRaw = function_exists('seo_gen_settings_get_raw') ? seo_gen_settings_get_raw($DB) : [];

$rawSettingsForMigration = is_array($seoGeneratorSettingsRaw) ? $seoGeneratorSettingsRaw : [];
$defaultSettingsForMigration = seo_gen_settings_default();
if (isset($defaultSettingsForMigration['campaigns']) && is_array($defaultSettingsForMigration['campaigns'])) {
    $defaultSettingsForMigration['campaigns']['reviews'] = admin_seo_gen_reviews_campaign_payload();
}
$mergedSettingsForMigration = admin_seo_gen_merge_missing_recursive($rawSettingsForMigration, $defaultSettingsForMigration);
$migrationBeforeJson = json_encode($rawSettingsForMigration, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$migrationAfterJson = json_encode($mergedSettingsForMigration, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (is_string($migrationAfterJson) && $migrationAfterJson !== $migrationBeforeJson) {
    $migrationAdminId = is_array($adminpanelUser) ? (int)($adminpanelUser['id'] ?? 0) : 0;
    if (seo_gen_settings_save($DB, $mergedSettingsForMigration, $migrationAdminId)) {
        $seoGeneratorSettingsRaw = function_exists('seo_gen_settings_get_raw') ? seo_gen_settings_get_raw($DB) : $mergedSettingsForMigration;
        $seoGeneratorSettings = seo_gen_settings_get($DB);
    }
}
$seoGeneratorSettings['__raw_campaigns'] = is_array($seoGeneratorSettingsRaw['campaigns'] ?? null) ? $seoGeneratorSettingsRaw['campaigns'] : [];
$scheduleDate = trim((string)($_GET['schedule_date'] ?? gmdate('Y-m-d')));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $scheduleDate)) {
    $scheduleDate = gmdate('Y-m-d');
}
$scheduleRows = [];
$queueRows = [];
$hasCronRunsTable = admin_seo_gen_table_exists($DB, 'seo_article_cron_runs');
$hasQueueTable = admin_seo_gen_table_exists($DB, 'seo_article_generation_queue');
$hasExamplesTable = admin_seo_gen_table_exists($DB, 'examples_articles');
$allowedCampaigns = function_exists('seo_gen_allowed_campaign_keys')
    ? seo_gen_allowed_campaign_keys()
    : ['journal', 'playbooks', 'signals', 'reviews', 'fun'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'update_queue_time') {
        if (!$hasQueueTable) {
            $message = 'Table seo_article_generation_queue is missing.';
            $messageType = 'danger';
        } else {
            $queueId = (int)($_POST['queue_id'] ?? 0);
            $rowDate = trim((string)($_POST['job_date'] ?? ''));
            $time = trim((string)($_POST['planned_time'] ?? ''));
            $campaignKey = trim((string)($_POST['campaign_key'] ?? ''));
            $langCode = examples_normalize_lang((string)($_POST['lang_code'] ?? 'ru'));
            $slotIndex = (int)($_POST['slot_index'] ?? 0);
            if ($queueId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $rowDate) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
                $message = 'Invalid schedule update payload.';
                $messageType = 'danger';
            } else {
                $plannedAt = $rowDate . ' ' . $time . ':00';
                $plannedAtSafe = mysqli_real_escape_string($DB, $plannedAt);
                $jobDateSafe = mysqli_real_escape_string($DB, $rowDate);
                $sql = "UPDATE seo_article_generation_queue
                        SET planned_at = '{$plannedAtSafe}', updated_at = NOW()
                        WHERE id = {$queueId} AND job_date = '{$jobDateSafe}'
                        LIMIT 1";
                if (mysqli_query($DB, $sql)) {
                    if ($hasCronRunsTable && $slotIndex > 0 && in_array($campaignKey, $allowedCampaigns, true)) {
                        $campaignSafe = mysqli_real_escape_string($DB, $campaignKey);
                        $langSafe = mysqli_real_escape_string($DB, $langCode);
                        mysqli_query(
                            $DB,
                            "UPDATE seo_article_cron_runs
                             SET planned_at = '{$plannedAtSafe}', updated_at = NOW()
                             WHERE job_date = '{$jobDateSafe}'
                               AND lang_code = '{$langSafe}'
                               AND campaign_key = '{$campaignSafe}'
                               AND slot_index = {$slotIndex}
                             LIMIT 1"
                        );
                    }
                    $message = 'Schedule time updated.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update schedule time: ' . mysqli_error($DB);
                    $messageType = 'danger';
                }
            }
        }
    }
    if ($action === 'save_seo_generator_settings') {
        $incoming = $seoGeneratorSettings;
        $incoming['enabled'] = admin_seo_gen_post_bool('enabled', false);
        $incomingLangs = seo_gen_settings_parse_lines(admin_seo_gen_post_string('langs', 'ru'), 10);
        $incoming['langs'] = in_array('ru', $incomingLangs, true) ? ['ru'] : ['ru'];
        $incoming['domain_host'] = admin_seo_gen_post_string('domain_host');
        $incoming['domain_host_en'] = admin_seo_gen_post_string('domain_host_en');
        $incoming['domain_host_ru'] = admin_seo_gen_post_string('domain_host_ru');
        $incoming['author_name'] = admin_seo_gen_post_string('author_name');
        $incoming['daily_min'] = admin_seo_gen_post_int('daily_min', 1);
        $incoming['daily_max'] = admin_seo_gen_post_int('daily_max', 3);
        $incoming['max_per_run'] = admin_seo_gen_post_int('max_per_run', 2);
        $incoming['word_min'] = admin_seo_gen_post_int('word_min', 2000);
        $incoming['word_max'] = admin_seo_gen_post_int('word_max', 5000);
        $incoming['today_first_delay_min'] = admin_seo_gen_post_int('today_first_delay_min', 15);
        $incoming['auto_expand_retries'] = admin_seo_gen_post_int('auto_expand_retries', 1);
        $incoming['expand_context_chars'] = admin_seo_gen_post_int('expand_context_chars', 7000);
        $incoming['prompt_version'] = admin_seo_gen_post_string('prompt_version', 'cpalnya-generator-v1');
        $incoming['seed_salt'] = admin_seo_gen_post_string('seed_salt', 'cpalnya-affiliate-content');
        $incoming['tone_variability'] = admin_seo_gen_post_int('tone_variability', 60);
        $incoming['portfolio_bofu_weight'] = admin_seo_gen_post_int('portfolio_bofu_weight', 30);
        $incoming['portfolio_mofu_weight'] = admin_seo_gen_post_int('portfolio_mofu_weight', 30);
        $incoming['portfolio_authority_weight'] = admin_seo_gen_post_int('portfolio_authority_weight', 20);
        $incoming['portfolio_case_weight'] = admin_seo_gen_post_int('portfolio_case_weight', 10);
        $incoming['portfolio_product_weight'] = admin_seo_gen_post_int('portfolio_product_weight', 10);
        $incoming['notify_schedule'] = admin_seo_gen_post_bool('notify_schedule', false);
        $incoming['notify_daily_schedule'] = admin_seo_gen_post_bool('notify_daily_schedule', false);
        $incoming['indexnow_enabled'] = admin_seo_gen_post_bool('indexnow_enabled', false);
        $incoming['indexnow_key'] = admin_seo_gen_post_string('indexnow_key');
        $incoming['indexnow_key_location'] = admin_seo_gen_post_string('indexnow_key_location');
        $incoming['indexnow_endpoint'] = admin_seo_gen_post_string('indexnow_endpoint');
        $incoming['indexnow_hosts'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('indexnow_hosts'), 40);
        $incoming['indexnow_ping_on_publish'] = admin_seo_gen_post_bool('indexnow_ping_on_publish', true);
        $incoming['indexnow_submit_limit'] = admin_seo_gen_post_int('indexnow_submit_limit', 100);
        $incoming['indexnow_retry_delay_minutes'] = admin_seo_gen_post_int('indexnow_retry_delay_minutes', 15);

        $incoming['llm_provider'] = admin_seo_gen_post_string('llm_provider', 'openai');
        $incoming['openai_api_key'] = admin_seo_gen_post_string('openai_api_key');
        $incoming['openai_base_url'] = admin_seo_gen_post_string('openai_base_url');
        $incoming['openai_model'] = admin_seo_gen_post_string('openai_model');
        $incoming['openai_timeout'] = admin_seo_gen_post_int('openai_timeout', 120);
        $incoming['openai_headers'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('openai_headers'), 100);
        $incoming['openrouter_api_key'] = admin_seo_gen_post_string('openrouter_api_key');
        $incoming['openrouter_base_url'] = admin_seo_gen_post_string('openrouter_base_url');
        $incoming['openrouter_model'] = admin_seo_gen_post_string('openrouter_model');
        $incoming['openrouter_fallback_model'] = admin_seo_gen_post_string('openrouter_fallback_model', 'openai/gpt-4o-2024-11-20');

        $incoming['openai_proxy_enabled'] = admin_seo_gen_post_bool('openai_proxy_enabled', false);
        $incoming['openai_proxy_host'] = admin_seo_gen_post_string('openai_proxy_host');
        $incoming['openai_proxy_port'] = admin_seo_gen_post_int('openai_proxy_port', 0);
        $incoming['openai_proxy_type'] = admin_seo_gen_post_string('openai_proxy_type', 'http');
        $incoming['openai_proxy_username'] = admin_seo_gen_post_string('openai_proxy_username');
        $incoming['openai_proxy_password'] = admin_seo_gen_post_string('openai_proxy_password');
        $incoming['openai_proxy_pool_enabled'] = admin_seo_gen_post_bool('openai_proxy_pool_enabled', false);
        $incoming['openai_proxy_pool'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('openai_proxy_pool'), 300);

        $incoming['topic_analysis_enabled'] = admin_seo_gen_post_bool('topic_analysis_enabled', true);
        $incoming['topic_analysis_limit'] = admin_seo_gen_post_int('topic_analysis_limit', 120);
        $incoming['topic_analysis_system_prompt'] = admin_seo_gen_post_string('topic_analysis_system_prompt');
        $incoming['topic_analysis_user_prompt_append'] = admin_seo_gen_post_string('topic_analysis_user_prompt_append');
        $incoming['signals_news_enabled'] = admin_seo_gen_post_bool('signals_news_enabled', true);
        $incoming['signals_news_max_items'] = admin_seo_gen_post_int('signals_news_max_items', 12);
        $incoming['signals_news_lookback_hours'] = admin_seo_gen_post_int('signals_news_lookback_hours', 96);
        $incoming['signals_news_timeout'] = admin_seo_gen_post_int('signals_news_timeout', 12);
        $incoming['signals_news_feeds'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('signals_news_feeds'), 80);

        $incoming['styles_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('styles_en'), 1200);
        $incoming['styles_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('styles_ru'), 1200);
        $incoming['clusters_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('clusters_en'), 1200);
        $incoming['clusters_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('clusters_ru'), 1200);
        $incoming['intent_verticals_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_verticals_en'), 150);
        $incoming['intent_verticals_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_verticals_ru'), 150);
        $incoming['intent_scenarios_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_scenarios_en'), 150);
        $incoming['intent_scenarios_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_scenarios_ru'), 150);
        $incoming['intent_objectives_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_objectives_en'), 150);
        $incoming['intent_objectives_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_objectives_ru'), 150);
        $incoming['intent_constraints_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_constraints_en'), 150);
        $incoming['intent_constraints_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_constraints_ru'), 150);
        $incoming['intent_artifacts_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_artifacts_en'), 150);
        $incoming['intent_artifacts_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_artifacts_ru'), 150);
        $incoming['intent_outcomes_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_outcomes_en'), 150);
        $incoming['intent_outcomes_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('intent_outcomes_ru'), 150);
        $incoming['service_focus_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('service_focus_en'), 150);
        $incoming['service_focus_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('service_focus_ru'), 150);
        $incoming['forbidden_topics_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('forbidden_topics_en'), 150);
        $incoming['forbidden_topics_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('forbidden_topics_ru'), 150);
        $incoming['article_structures_en'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('article_structures_en'), 120);
        $incoming['article_structures_ru'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('article_structures_ru'), 120);
        $incoming['moods'] = admin_seo_gen_parse_moods(admin_seo_gen_post_string('moods'));

        $incoming['article_system_prompt_en'] = admin_seo_gen_post_string('article_system_prompt_en');
        $incoming['article_system_prompt_ru'] = admin_seo_gen_post_string('article_system_prompt_ru');
        $incoming['article_user_prompt_append_en'] = admin_seo_gen_post_string('article_user_prompt_append_en');
        $incoming['article_user_prompt_append_ru'] = admin_seo_gen_post_string('article_user_prompt_append_ru');

        $incoming['expand_system_prompt_en'] = admin_seo_gen_post_string('expand_system_prompt_en');
        $incoming['expand_system_prompt_ru'] = admin_seo_gen_post_string('expand_system_prompt_ru');
        $incoming['expand_user_prompt_append_en'] = admin_seo_gen_post_string('expand_user_prompt_append_en');
        $incoming['expand_user_prompt_append_ru'] = admin_seo_gen_post_string('expand_user_prompt_append_ru');

        $incoming['preview_channel_enabled'] = admin_seo_gen_post_bool('preview_channel_enabled', false);
        $incoming['preview_channel_chat_id'] = admin_seo_gen_post_string('preview_channel_chat_id');
        $incoming['preview_public_channel_enabled'] = admin_seo_gen_post_bool('preview_public_channel_enabled', false);
        $incoming['preview_public_channel_chat_id'] = admin_seo_gen_post_string('preview_public_channel_chat_id');
        $incoming['preview_public_channel_bot_token'] = admin_seo_gen_post_string('preview_public_channel_bot_token');
        $incoming['preview_public_channel_api_base'] = admin_seo_gen_post_string('preview_public_channel_api_base', 'https://api.telegram.org');
        $incoming['preview_post_max_words'] = admin_seo_gen_post_int('preview_post_max_words', 220);
        $incoming['preview_caption_max_words'] = admin_seo_gen_post_int('preview_caption_max_words', 80);
        $incoming['preview_post_min_words'] = admin_seo_gen_post_int('preview_post_min_words', 70);
        $incoming['preview_caption_min_words'] = admin_seo_gen_post_int('preview_caption_min_words', 26);
        $incoming['preview_use_llm'] = admin_seo_gen_post_bool('preview_use_llm', true);
        $incoming['preview_llm_model'] = admin_seo_gen_post_string('preview_llm_model');
        $incoming['preview_context_chars'] = admin_seo_gen_post_int('preview_context_chars', 14000);

        $incoming['preview_image_enabled'] = admin_seo_gen_post_bool('preview_image_enabled', false);
        $incoming['preview_image_model'] = admin_seo_gen_post_string('preview_image_model');
        $incoming['preview_image_size'] = admin_seo_gen_post_string('preview_image_size', '768x512');
        $incoming['preview_image_anchor_enforced'] = admin_seo_gen_post_bool('preview_image_anchor_enforced', true);
        $incoming['preview_image_anchor_append'] = admin_seo_gen_post_string('preview_image_anchor_append');
        $incoming['preview_image_style_options'] = seo_gen_settings_parse_lines(admin_seo_gen_post_string('preview_image_style_options'), 60);
        $incoming['image_color_schemes'] = admin_seo_gen_parse_color_schemes(admin_seo_gen_post_string('image_color_schemes'));
        $incoming['image_compositions'] = admin_seo_gen_parse_image_compositions(admin_seo_gen_post_string('image_compositions'));
        $incoming['image_scene_families'] = admin_seo_gen_parse_image_scene_families(admin_seo_gen_post_string('image_scene_families'));
        $incoming['image_scenarios'] = admin_seo_gen_parse_image_scene_families(admin_seo_gen_post_string('image_scenarios'));
        $incoming['preview_image_prompt_template'] = admin_seo_gen_post_string('preview_image_prompt_template');

        $rawCampaigns = is_array($seoGeneratorSettings['__raw_campaigns'] ?? null) ? $seoGeneratorSettings['__raw_campaigns'] : [];
        $normalizedCampaigns = is_array($seoGeneratorSettings['campaigns'] ?? null) ? $seoGeneratorSettings['campaigns'] : [];
        $campaignDefaults = function_exists('seo_gen_default_campaigns')
            ? seo_gen_default_campaigns()
            : [];
        $campaignKeys = function_exists('seo_gen_allowed_campaign_keys')
            ? seo_gen_allowed_campaign_keys()
            : array_keys($normalizedCampaigns);
        $incomingCampaigns = [];
        foreach ($campaignKeys as $campaignKey) {
            $rawCampaign = is_array($rawCampaigns[$campaignKey] ?? null) ? $rawCampaigns[$campaignKey] : [];
            $campaignDefault = is_array($normalizedCampaigns[$campaignKey] ?? null)
                ? $normalizedCampaigns[$campaignKey]
                : (is_array($campaignDefaults[$campaignKey] ?? null) ? $campaignDefaults[$campaignKey] : $rawCampaign);
            $prefix = 'campaign_' . $campaignKey . '_';
            $incomingCampaigns[$campaignKey] = [
                'key' => $campaignKey,
                'title' => admin_seo_gen_post_string($prefix . 'title', (string)($campaignDefault['title'] ?? $campaignKey)),
                'title_ru' => admin_seo_gen_post_string($prefix . 'title_ru', (string)($campaignDefault['title_ru'] ?? $campaignKey)),
                'description' => admin_seo_gen_post_string($prefix . 'description', (string)($campaignDefault['description'] ?? '')),
                'description_ru' => admin_seo_gen_post_string($prefix . 'description_ru', (string)($campaignDefault['description_ru'] ?? '')),
                'material_section' => admin_seo_gen_post_string($prefix . 'material_section', (string)($campaignDefault['material_section'] ?? $campaignKey)),
                'enabled' => admin_seo_gen_post_bool($prefix . 'enabled', !empty($campaignDefault['enabled'])),
                'daily_min' => admin_seo_gen_post_int($prefix . 'daily_min', (int)($campaignDefault['daily_min'] ?? 4)),
                'daily_max' => admin_seo_gen_post_int($prefix . 'daily_max', (int)($campaignDefault['daily_max'] ?? 6)),
                'max_per_run' => admin_seo_gen_post_int($prefix . 'max_per_run', (int)($campaignDefault['max_per_run'] ?? 2)),
                'word_min' => admin_seo_gen_post_int($prefix . 'word_min', (int)($campaignDefault['word_min'] ?? 1800)),
                'word_max' => admin_seo_gen_post_int($prefix . 'word_max', (int)($campaignDefault['word_max'] ?? 3200)),
                'seed_salt_suffix' => admin_seo_gen_post_string($prefix . 'seed_salt_suffix', (string)($campaignDefault['seed_salt_suffix'] ?? $campaignKey)),
                'styles_en' => seo_gen_settings_parse_lines(admin_seo_gen_post_string($prefix . 'styles_en'), 1200),
                'styles_ru' => seo_gen_settings_parse_lines(admin_seo_gen_post_string($prefix . 'styles_ru'), 1200),
                'clusters_en' => seo_gen_settings_parse_lines(admin_seo_gen_post_string($prefix . 'clusters_en'), 1200),
                'clusters_ru' => seo_gen_settings_parse_lines(admin_seo_gen_post_string($prefix . 'clusters_ru'), 1200),
                'article_structures_en' => seo_gen_settings_parse_lines(admin_seo_gen_post_string($prefix . 'article_structures_en'), 1200),
                'article_structures_ru' => seo_gen_settings_parse_lines(admin_seo_gen_post_string($prefix . 'article_structures_ru'), 1200),
                'article_system_prompt_en' => admin_seo_gen_post_string($prefix . 'article_system_prompt_en'),
                'article_system_prompt_ru' => admin_seo_gen_post_string($prefix . 'article_system_prompt_ru'),
                'article_user_prompt_append_en' => admin_seo_gen_post_string($prefix . 'article_user_prompt_append_en'),
                'article_user_prompt_append_ru' => admin_seo_gen_post_string($prefix . 'article_user_prompt_append_ru'),
            ];
        }
        $incoming['campaigns'] = $incomingCampaigns;

        if (seo_gen_settings_save($DB, $incoming, (int)($adminpanelUser['id'] ?? 0))) {
            $seoGeneratorSettings = seo_gen_settings_get($DB);
            $seoGeneratorSettingsRaw = function_exists('seo_gen_settings_get_raw') ? seo_gen_settings_get_raw($DB) : [];
            $seoGeneratorSettings['__raw_campaigns'] = is_array($seoGeneratorSettingsRaw['campaigns'] ?? null) ? $seoGeneratorSettingsRaw['campaigns'] : [];
            $message = 'SEO generator settings saved.';
            $messageType = 'success';
        } else {
            $message = 'Failed to save settings.';
            $messageType = 'danger';
        }
    }
}

if ($hasCronRunsTable) {
    $dateSafe = mysqli_real_escape_string($DB, $scheduleDate);
    $selectArticle = $hasExamplesTable
        ? "ea.slug AS article_slug, ea.lang_code AS article_lang, ea.title AS article_title"
        : "NULL AS article_slug, NULL AS article_lang, NULL AS article_title";
    $joinArticle = $hasExamplesTable
        ? "LEFT JOIN examples_articles ea ON ea.id = r.article_id"
        : "";
    $sql = "SELECT r.id, r.job_date, r.lang_code, r.campaign_key, r.slot_index, r.planned_at, r.status, r.attempts, r.article_id, r.message,
                   {$selectArticle}
            FROM seo_article_cron_runs r
            {$joinArticle}
            WHERE r.job_date = '{$dateSafe}'
            ORDER BY
                CASE r.campaign_key WHEN 'journal' THEN 1 WHEN 'playbooks' THEN 2 WHEN 'signals' THEN 3 WHEN 'reviews' THEN 4 WHEN 'fun' THEN 5 ELSE 9 END,
                r.lang_code ASC, r.slot_index ASC, r.id ASC";
    $res = mysqli_query($DB, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $scheduleRows[] = $row;
        }
        mysqli_free_result($res);
    }
}

if ($hasQueueTable) {
    $dateSafe = mysqli_real_escape_string($DB, $scheduleDate);
    $sql = "SELECT id, job_date, lang_code, campaign_key, force_mode, dry_run, max_per_run, slot_index, status, attempts,
                   planned_at, started_at, finished_at, last_exit_code, last_output, last_error, created_at, updated_at
            FROM seo_article_generation_queue
            WHERE job_date = '{$dateSafe}'
            ORDER BY
                CASE campaign_key WHEN 'journal' THEN 1 WHEN 'playbooks' THEN 2 WHEN 'signals' THEN 3 WHEN 'reviews' THEN 4 WHEN 'fun' THEN 5 ELSE 9 END,
                planned_at ASC,
                slot_index ASC,
                id ASC";
    $res = mysqli_query($DB, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $queueRows[] = $row;
        }
        mysqli_free_result($res);
    }
}
