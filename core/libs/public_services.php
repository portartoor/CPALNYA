<?php

if (!function_exists('public_services_slugify')) {
    function public_services_slugify(string $raw): string
    {
        $raw = trim(mb_strtolower($raw, 'UTF-8'));
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
        ];
        $raw = strtr($raw, $map);
        $raw = preg_replace('/[^a-z0-9]+/', '-', (string)$raw);
        $raw = trim((string)$raw, '-');
        if ($raw === '') {
            $raw = 'service-' . date('YmdHis');
        }
        return substr($raw, 0, 180);
    }
}

if (!function_exists('public_services_normalize_lang')) {
    function public_services_normalize_lang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        return in_array($lang, ['ru', 'en'], true) ? $lang : 'en';
    }
}

if (!function_exists('public_services_resolve_lang')) {
    function public_services_resolve_lang(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host !== '' && preg_match('/\.ru$/', $host)) {
            return 'ru';
        }
        $fromQuery = (string)($_GET['lang'] ?? '');
        if ($fromQuery !== '') {
            return public_services_normalize_lang($fromQuery);
        }
        return 'en';
    }
}

if (!function_exists('public_services_normalize_group')) {
    function public_services_normalize_group(string $group): string
    {
        $group = public_services_slugify($group);
        if ($group === '') {
            $group = 'general';
        }
        return substr($group, 0, 96);
    }
}

if (!function_exists('public_services_group_label')) {
    function public_services_group_label(string $groupCode, string $lang = 'en'): string
    {
        $groupCode = trim(strtolower($groupCode));
        $lang = public_services_normalize_lang($lang);

        $labels = [
            'en' => [
                '' => 'General',
                'general' => 'General',
                'ai' => 'AI',
                'analytics' => 'Analytics',
                'architecture' => 'Architecture',
                'automation' => 'Automation',
                'b2b' => 'B2B',
                'bitrix24' => 'Bitrix24',
                'bitrix-b24' => 'Bitrix24',
                'bitrix-bus' => '1C-Bitrix CMS',
                'devops' => 'DevOps',
                'ecommerce' => 'E-commerce',
                'growth' => 'Growth',
                'integration' => 'Integration',
                'api-integration' => 'API Integration',
                'operations' => 'Operations',
                'performance' => 'Performance',
                'platform' => 'Platform',
                'product' => 'Product',
                'web-platforms' => 'Web Platforms',
                'telegram-bots' => 'Telegram Bots',
                'saas-platforms' => 'SaaS Platforms',
                'consulting-expertise' => 'Consulting',
                'mvp' => 'MVP',
                'e-com' => 'E-com',
                'ecom' => 'E-com',
                'e-commerce' => 'E-com',
                'security' => 'Security',
                'seo' => 'SEO',
                'web' => 'Web',
            ],
            'ru' => [
                '' => 'Общее',
                'general' => 'Общее',
                'ai' => 'AI',
                'analytics' => 'Аналитика',
                'architecture' => 'Архитектура',
                'automation' => 'Автоматизация',
                'b2b' => 'B2B',
                'bitrix24' => 'Bitrix24',
                'bitrix-b24' => 'Bitrix24',
                'bitrix-bus' => '1C Битрикс (БУС)',
                'devops' => 'DevOps',
                'ecommerce' => 'E-commerce',
                'growth' => 'Рост',
                'integration' => 'Интеграции',
                'api-integration' => 'API интеграции',
                'operations' => 'Операции',
                'performance' => 'Производительность',
                'platform' => 'Платформа',
                'product' => 'Продукт',
                'web-platforms' => 'Сайты и платформы',
                'telegram-bots' => 'Telegram-боты',
                'saas-platforms' => 'SaaS платформы',
                'consulting-expertise' => 'Консалтинг',
                'mvp' => 'MVP',
                'e-com' => 'E-com',
                'ecom' => 'E-com',
                'e-commerce' => 'E-com',
                'security' => 'Безопасность',
                'seo' => 'SEO',
                'web' => 'Веб-разработка',
            ],
        ];
        if (isset($labels[$lang][$groupCode])) {
            return $labels[$lang][$groupCode];
        }
        if ($groupCode === 'ai') {
            return 'AI';
        }
        if ($groupCode === 'bitrix24' || $groupCode === 'bitrix-b24') {
            return 'Bitrix24';
        }
        if ($groupCode === 'bitrix-bus') {
            return $lang === 'ru' ? '1C Битрикс (БУС)' : '1C-Bitrix CMS';
        }
        if ($groupCode === '' || $groupCode === 'general') {
            return $lang === 'ru' ? 'Общее' : 'General';
        }
        $parts = array_filter(explode('-', $groupCode), static function ($v): bool {
            return trim((string)$v) !== '';
        });
        if (empty($parts)) {
            return $lang === 'ru' ? 'Общее' : 'General';
        }
        return ucwords(implode(' ', $parts));
    }
}

if (!function_exists('public_services_table_exists')) {
    function public_services_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'public_services'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_services_group_scheme')) {
    function public_services_group_scheme(string $groupCode, string $lang = 'en'): array
    {
        $groupCode = trim(strtolower($groupCode));
        $lang = public_services_normalize_lang($lang);

        $commonRu = [
            'title' => 'Схема реализации',
            'steps' => [
                'Диагностика и карта текущего состояния',
                'Проектирование целевого процесса и архитектуры',
                'Внедрение, запуск и контроль метрик',
            ],
        ];
        $commonEn = [
            'title' => 'Implementation Scheme',
            'steps' => ['Discovery and baseline mapping', 'Target architecture and process design', 'Implementation, launch and KPI control'],
        ];

        $map = [
            'bitrix24' => [
                'ru' => ['title' => 'Схема Bitrix24 внедрения', 'steps' => ['Аудит CRM/процессов и воронок', 'Настройка сущностей, ролей, роботов и интеграций', 'Пилот, обучение команды, масштабирование']],
                'en' => ['title' => 'Bitrix24 Delivery Scheme', 'steps' => ['CRM/process audit and funnel mapping', 'Entities, roles, automation and integrations', 'Pilot run, team enablement, scale rollout']],
            ],
            'bitrix-b24' => [
                'ru' => ['title' => 'Схема Bitrix/B24 внедрения', 'steps' => ['Аудит CRM/процессов и воронок', 'Настройка сущностей, ролей, роботов и интеграций', 'Пилот, обучение команды, масштабирование']],
                'en' => ['title' => 'Bitrix/B24 Delivery Scheme', 'steps' => ['CRM/process audit and funnel mapping', 'Entities, roles, automation and integrations', 'Pilot run, team enablement, scale rollout']],
            ],
            'bitrix-bus' => [
                'ru' => ['title' => 'Схема 1С-Битрикс (БУС)', 'steps' => ['Аудит текущего сайта и архитектуры БУС', 'Разработка/доработка модулей, шаблонов и интеграций', 'Запуск, поддержка и SLA-контроль']],
                'en' => ['title' => '1C-Bitrix CMS Delivery Scheme', 'steps' => ['Current stack and CMS architecture audit', 'Module/template development and integrations', 'Launch, support and SLA control']],
            ],
            'telegram-bots' => [
                'ru' => ['title' => 'Схема разработки Telegram-бота', 'steps' => ['Сценарии диалогов и бизнес-правила', 'Интеграции и обработка событий', 'Запуск, мониторинг, донастройка конверсии']],
                'en' => ['title' => 'Telegram Bot Delivery Scheme', 'steps' => ['Conversation flows and business rules', 'Integrations and event handling', 'Go-live, monitoring, conversion tuning']],
            ],
            'api-integration' => [
                'ru' => ['title' => 'Схема API-интеграции', 'steps' => ['Контракт API и модель данных', 'Надежность: retry, idempotency, observability', 'Промышленный запуск и SLA-контроль']],
                'en' => ['title' => 'API Integration Scheme', 'steps' => ['API contract and data model', 'Reliability layer: retry, idempotency, observability', 'Production launch and SLA control']],
            ],
            'ai' => [
                'ru' => ['title' => 'Схема AI-внедрения', 'steps' => ['Определение сценариев и контекста данных', 'Интеграция с системами и контроль качества', 'Запуск с метриками и циклом улучшений']],
                'en' => ['title' => 'AI Delivery Scheme', 'steps' => ['Use-case and context definition', 'System integration and quality controls', 'Launch with metrics and improvement loop']],
            ],
        ];

        if (isset($map[$groupCode][$lang])) {
            return $map[$groupCode][$lang];
        }
        return ($lang === 'ru') ? $commonRu : $commonEn;
    }
}

if (!function_exists('public_services_group_results')) {
    function public_services_group_results(string $groupCode, string $lang = 'en'): array
    {
        $groupCode = trim(strtolower($groupCode));
        $lang = public_services_normalize_lang($lang);

        $commonRu = ['Сокращение ручных операций и ошибок', 'Рост прозрачности процесса и качества данных', 'Ускорение цикла от запроса до результата'];
        $commonEn = ['Reduced manual operations and error rate', 'Higher process transparency and data quality', 'Faster request-to-outcome cycle'];

        $map = [
            'bitrix24' => [
                'ru' => ['+20–40% к скорости обработки лидов', 'Более предсказуемая дисциплина воронки и задач', 'Единая картина по продажам и операционным SLA'],
                'en' => ['+20–40% faster lead handling velocity', 'More predictable funnel and execution discipline', 'Unified visibility into sales and SLA performance'],
            ],
            'bitrix-b24' => [
                'ru' => ['+20–40% к скорости обработки лидов', 'Более предсказуемая дисциплина воронки и задач', 'Единая картина по продажам и операционным SLA'],
                'en' => ['+20–40% faster lead handling velocity', 'More predictable funnel and execution discipline', 'Unified visibility into sales and SLA performance'],
            ],
            'bitrix-bus' => [
                'ru' => ['Стабильные релизы на 1С-Битрикс без деградации производительности', 'Предсказуемая поддержка и контроль технического долга', 'Ускорение вывода изменений в продакшн'],
                'en' => ['Stable 1C-Bitrix releases without performance regression', 'Predictable support and technical debt control', 'Faster production delivery of changes'],
            ],
            'telegram-bots' => [
                'ru' => ['Снижение времени первого ответа', 'Рост доли квалифицированных обращений', 'Меньше потерь контекста при передаче в команду'],
                'en' => ['Lower first-response time', 'Higher share of qualified inbound requests', 'Less context loss during handoff'],
            ],
            'api-integration' => [
                'ru' => ['Снижение количества интеграционных инцидентов', 'Стабильная синхронизация ключевых данных', 'Прозрачные логи и ускоренная диагностика'],
                'en' => ['Fewer integration incidents', 'Stable synchronization of critical data', 'Clear logs and faster diagnostics'],
            ],
            'ai' => [
                'ru' => ['Снижение нагрузки на операционные команды', 'Более быстрый ответ в типовых сценариях', 'Контролируемый рост AI-функций без хаоса'],
                'en' => ['Lower load on operations teams', 'Faster response in repeatable scenarios', 'Controlled AI capability growth without operational chaos'],
            ],
        ];

        if (isset($map[$groupCode][$lang])) {
            return $map[$groupCode][$lang];
        }
        return ($lang === 'ru') ? $commonRu : $commonEn;
    }
}

if (!function_exists('public_services_ensure_schema')) {
    function public_services_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_services (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                domain_host VARCHAR(190) NOT NULL DEFAULT '',
                lang_code VARCHAR(8) NOT NULL DEFAULT 'en',
                service_group VARCHAR(96) NOT NULL DEFAULT 'general',
                title VARCHAR(255) NOT NULL DEFAULT '',
                slug VARCHAR(190) NOT NULL DEFAULT '',
                excerpt_html TEXT NULL,
                content_html MEDIUMTEXT NULL,
                sort_order INT NOT NULL DEFAULT 100,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_services_domain_lang_slug (domain_host, lang_code, slug),
                KEY idx_public_services_host_lang (domain_host, lang_code),
                KEY idx_public_services_group (service_group),
                KEY idx_public_services_publish_sort (is_published, sort_order, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_services_table_exists($db);
    }
}

if (!function_exists('public_services_fetch_groups')) {
    function public_services_fetch_groups($FRMWRK, string $host, string $lang): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_services_ensure_schema($db)) {
            return [];
        }
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, public_services_normalize_lang($lang));
        $rows = $FRMWRK->DBRecords(
            "SELECT service_group, COUNT(*) AS cnt
             FROM public_services
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
             GROUP BY service_group
             ORDER BY cnt DESC, service_group ASC"
        );
        $out = [];
        foreach ($rows as $row) {
            $code = trim((string)($row['service_group'] ?? ''));
            if ($code === '') {
                $code = 'general';
            }
            $out[] = [
                'code' => $code,
                'label' => public_services_group_label($code, $lang),
                'count' => (int)($row['cnt'] ?? 0),
            ];
        }
        return $out;
    }
}

if (!function_exists('public_services_fetch_published_count')) {
    function public_services_fetch_published_count($FRMWRK, string $host, string $lang = 'en', string $group = ''): int
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_services_ensure_schema($db)) {
            return 0;
        }
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, public_services_normalize_lang($lang));
        $groupSql = '';
        if (trim($group) !== '') {
            $groupSafe = mysqli_real_escape_string($db, public_services_normalize_group($group));
            $groupSql = " AND service_group = '{$groupSafe}'";
        }
        $rows = $FRMWRK->DBRecords(
            "SELECT COUNT(*) AS cnt
             FROM public_services
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               {$groupSql}"
        );
        return (int)($rows[0]['cnt'] ?? 0);
    }
}

if (!function_exists('public_services_fetch_published_page')) {
    function public_services_fetch_published_page(
        $FRMWRK,
        string $host,
        string $lang = 'en',
        int $page = 1,
        int $perPage = 12,
        string $group = ''
    ): array {
        $db = $FRMWRK->DB();
        if (!$db || !public_services_ensure_schema($db)) {
            return [];
        }
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, public_services_normalize_lang($lang));
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $groupSql = '';
        if (trim($group) !== '') {
            $groupSafe = mysqli_real_escape_string($db, public_services_normalize_group($group));
            $groupSql = " AND service_group = '{$groupSafe}'";
        }

        return $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at
             FROM public_services
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               {$groupSql}
             ORDER BY sort_order ASC, id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }
}

if (!function_exists('public_services_fetch_published_by_slug')) {
    function public_services_fetch_published_by_slug($FRMWRK, string $host, string $slug, string $lang = 'en'): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_services_ensure_schema($db)) {
            return null;
        }
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, public_services_normalize_lang($lang));
        $slugRaw = trim(rawurldecode($slug));
        $slugExactSafe = mysqli_real_escape_string($db, $slugRaw);
        $slugSafe = mysqli_real_escape_string($db, public_services_slugify($slugRaw));
        $slugWhere = $slugExactSafe === $slugSafe
            ? "slug = '{$slugSafe}'"
            : "(slug = '{$slugExactSafe}' OR slug = '{$slugSafe}')";

        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at
             FROM public_services
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND {$slugWhere}
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
             ORDER BY (domain_host = '{$hostSafe}') DESC, id DESC
             LIMIT 1"
        );
        return !empty($rows) ? $rows[0] : null;
    }
}

if (!function_exists('public_services_fetch_related_by_group')) {
    function public_services_fetch_related_by_group(
        $FRMWRK,
        string $host,
        string $lang,
        string $group,
        string $excludeSlug = '',
        int $limit = 3
    ): array {
        $db = $FRMWRK->DB();
        if (!$db || !public_services_ensure_schema($db)) {
            return [];
        }

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, public_services_normalize_lang($lang));
        $groupSafe = mysqli_real_escape_string($db, public_services_normalize_group($group));
        $limit = max(1, min(12, (int)$limit));
        $excludeSlug = trim($excludeSlug);
        $excludeSql = '';
        if ($excludeSlug !== '') {
            $excludeSlugSafe = mysqli_real_escape_string($db, public_services_slugify($excludeSlug));
            $excludeSql = " AND slug <> '{$excludeSlugSafe}'";
        }

        return $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at
             FROM public_services
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND service_group = '{$groupSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               {$excludeSql}
             ORDER BY sort_order ASC, id DESC
             LIMIT {$limit}"
        );
    }
}

