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

if (!function_exists('public_services_default_catalog')) {
    function public_services_default_catalog(string $lang = 'en'): array
    {
        $lang = public_services_normalize_lang($lang);

        $ru = [
            [
                'group' => 'operations',
                'title' => 'Гарант по CPA и affiliate-сделкам',
                'slug' => 'garant-po-cpa-i-affiliate-sdelkam',
                'excerpt_html' => '<p>Подключаемся как гарант в сделках по аккаунтам, расходникам, командам, доступам, интеграциям и медиабаингу. Фиксируем предмет сделки, этапы, артефакты и условия разблокировки оплаты.</p>',
                'content_html' => '<p>Это услуга для ситуаций, где у сделки слишком много серых зон: доступы, фарм, агентские кабинеты, расходники, интеграции, креативные поставки, team handoff или техническая сборка под запуск. Мы не изображаем абстрактный "консалтинг", а доводим стороны до понятной схемы исполнения.</p><div class="svc-block"><strong>Что делаем</strong><ul><li>фиксируем предмет сделки и состав доступа;</li><li>собираем этапы передачи и критерии приемки;</li><li>определяем, что считается выполнением и что считается срывом;</li><li>ведем коммуникацию до закрытия обязательств.</li></ul></div><div class="svc-block"><strong>Где особенно полезно</strong><ul><li>покупка и аренда фарм-ресурса;</li><li>передача трекера, postback и routing-сборки;</li><li>подключение media buyer, farmer, integrator, closer или creative team;</li><li>разовые технические работы с чувствительными доступами.</li></ul></div><div class="svc-block"><strong>Результат</strong><p>У сторон есть понятная карта сделки, сроки, контрольные точки и меньше пространства для "мы не так поняли".</p></div>',
            ],
            [
                'group' => 'analytics',
                'title' => 'Технический аудит трекера, postback и атрибуции',
                'slug' => 'tekhnicheskiy-audit-trekera-postback-i-atributsii',
                'excerpt_html' => '<p>Проверяем, почему режется статистика, теряются конверсии, ломается атрибуция и расходится картина между трекером, CRM и источником.</p>',
                'content_html' => '<p>Это именно экспертный аудит с нашей стороны. Разбираем события, postback-цепочку, макросы, dedup, client/server-side передачу, sticky-параметры, метки и точки потери данных.</p><div class="svc-block"><strong>Смотрим</strong><ul><li>маршрут клика от источника до оффера;</li><li>postback и callback-цепочки;</li><li>UTM/SubID/FBCLID/GCLID и их сохранность;</li><li>расхождения между таблицами визитов, графиками и источниками.</li></ul></div><div class="svc-block"><strong>На выходе</strong><p>Даем список проблем по приоритету, гипотезы, быстрые фиксы и архитектурные доработки, если проблема глубже одного поля в postback.</p></div>',
            ],
            [
                'group' => 'architecture',
                'title' => 'Архитектурный аудит affiliate-инфраструктуры',
                'slug' => 'arkhitekturnyy-audit-affiliate-infrastruktury',
                'excerpt_html' => '<p>Разбираем, где у команды хрупкая архитектура: routing, доступы, домены, трекер, формы, CRM, Telegram и ручные процессы.</p>',
                'content_html' => '<p>Это вторая аудитная услуга, где мы даем собственное техническое и архитектурное мнение. Не сводим с подрядчиком, а смотрим на систему целиком: где она ломается, где зависит от одного человека и где мешает масштабироваться.</p><div class="svc-block"><strong>Разбираем</strong><ul><li>доменные контуры и роли доменов;</li><li>трекер, лендинги, прокладки и интеграции;</li><li>распределение доступов и handoff-процессы;</li><li>SOP, точки контроля и recoverability.</li></ul></div><div class="svc-block"><strong>Итог</strong><p>Получаете архитектурное заключение, список узких мест и рекомендуемую схему, которая переживет рост, ротацию людей и аварии.</p></div>',
            ],
            [
                'group' => 'operations',
                'title' => 'Сведение с media buyer или team lead',
                'slug' => 'svedenie-s-media-buyer-ili-team-lead',
                'excerpt_html' => '<p>Если нужен не "еще один чат в Telegram", а конкретный buyer или lead под вертикаль, гео, бюджет и темп запуска — сведем с теми, кто реально работает руками.</p>',
                'content_html' => '<p>Формат услуги — сведение с людьми, а не попытка продать вам воздух. Уточняем вертикаль, source mix, бюджет, требуемый опыт, ожидания по дневному темпу и составляем короткий пул релевантных кандидатов.</p><div class="svc-block"><strong>Подходит для</strong><ul><li>in-house команды, которой нужен новый buyer;</li><li>запуска нового направления;</li><li>перехвата управления после выхода key person;</li></ul></div><div class="svc-block"><strong>Итог</strong><p>Экономим недели на хаотичный поиск и снижаем шанс, что вы сольетесь на красивом резюме без операционной базы.</p></div>',
            ],
            [
                'group' => 'operations',
                'title' => 'Сведение с фарм-оператором и аккаунт-командой',
                'slug' => 'svedenie-s-farm-operatorom-i-akkaunt-komandoy',
                'excerpt_html' => '<p>Подбираем людей под farm, warming, sustain, account rotation и бережную работу с доступами без вечного режима "доверимся на слово".</p>',
                'content_html' => '<p>Если нужен farm-оператор, owner команды прогрева или люди под sustain кабинетов, мы помогаем собрать понятный запрос и сводим с теми, кто работает в этом контуре регулярно.</p><div class="svc-block"><strong>Помогаем определить</strong><ul><li>какой именно профиль нужен: farmer, warmer, support или owner;</li><li>какие доступы можно давать и как их сегментировать;</li><li>какие KPI и правила handoff закладывать сразу.</li></ul></div>',
            ],
            [
                'group' => 'integration',
                'title' => 'Сведение с трекер-специалистом и integrator',
                'slug' => 'svedenie-s-treker-spetsialistom-i-integrator',
                'excerpt_html' => '<p>Если нужен человек, который соберет tracker, postback, KPI-дашборды, Telegram-уведомления и рабочую связку с CRM — сведем с профильным integrator.</p>',
                'content_html' => '<p>Под задачу подбираем не просто "технаря", а человека, который уже делал аналогичный стек: Keitaro/Binom/Voluum, формы, CRM, Telegram, лендинги, webhook и SLA на поддержку.</p><div class="svc-block"><strong>Особенно актуально</strong><ul><li>когда надо быстро поднять рабочую схему под запуск;</li><li>когда старый integrator пропал вместе с контекстом;</li><li>когда трекер живет отдельно от остальной команды.</li></ul></div>',
            ],
            [
                'group' => 'automation',
                'title' => 'Сведение с разработчиком автоматизации и антидетект-рутин',
                'slug' => 'svedenie-s-razrabotchikom-avtomatizatsii-i-antidetekt-rutin',
                'excerpt_html' => '<p>Подбираем разработчиков под внутренние утилиты, антидетект-рутины, скрипты для farm/ops и сервисную автоматизацию вокруг affiliate-процессов.</p>',
                'content_html' => '<p>Когда команде нужен не "fullstack вообще", а человек под конкретные операционные боли, мы формулируем задачу, ограничения и сводим с разработчиком, у которого есть релевантный опыт именно в affiliate-операционке.</p>',
            ],
            [
                'group' => 'operations',
                'title' => 'Сведение с creative team под affiliate-задачи',
                'slug' => 'svedenie-s-creative-team-pod-affiliate-zadachi',
                'excerpt_html' => '<p>Сводим с дизайнерами, motion-специалистами и креативными командами, которые понимают performance-ритм, а не просто красиво оформляют баннеры.</p>',
                'content_html' => '<p>Если вам нужна команда, которая умеет жить в режиме постоянных итераций, source feedback и policy-ограничений, поможем быстро выйти на нужных людей и сократить путь до первого рабочего пула креативов.</p>',
            ],
            [
                'group' => 'security',
                'title' => 'Сведение с compliance, legal и risk-специалистом',
                'slug' => 'svedenie-s-compliance-legal-i-risk-spetsialistom',
                'excerpt_html' => '<p>Когда вопрос уже не только в запуске, а в рисках по платежам, структуре договоренностей, KYC/KYB и внутренней дисциплине доступа — сведем с нужным специалистом.</p>',
                'content_html' => '<p>Это услуга для команд, которым нужен человек под конкретный риск-контур: платежи, разбор договоренностей, юридическая рамка сотрудничества, доступы и внутренние правила фиксации обязательств.</p>',
            ],
            [
                'group' => 'analytics',
                'title' => 'Разбор source mix, воронки и unit-логики',
                'slug' => 'razbor-source-mix-voronki-i-unit-logiki',
                'excerpt_html' => '<p>Смотрим, где у вас источник, воронка или экономика начинают врать друг другу, и помогаем понять, что именно надо чинить первым.</p>',
                'content_html' => '<p>Разбираем трафик не как красивую презентацию, а как рабочую систему: source mix, EPC/CPA/CR, точки отказа, лаги в данных и качество handoff от клика до продажи.</p><div class="svc-block"><strong>Что получаете</strong><p>Список приоритетов: что надо править в логике воронки, что в аналитике, а что уже упирается в исполнителей.</p></div>',
            ],
            [
                'group' => 'architecture',
                'title' => 'Разбор cloaking, routing и fallback-сценариев',
                'slug' => 'razbor-cloaking-routing-i-fallback-stsenariev',
                'excerpt_html' => '<p>Помогаем понять, где хрупкая логика маршрутизации, как у вас проходят fallback-переходы и что случится при сбое домена, фильтра или интеграции.</p>',
                'content_html' => '<p>Это прикладной разбор для тех, у кого routing уже стал критической частью бизнеса. Смотрим дерево маршрутов, правила перекидывания, обработку ошибок, заглушки и то, насколько схема переживает банальные аварии.</p>',
            ],
            [
                'group' => 'integration',
                'title' => 'Интеграция CRM, tracker, Telegram и форм',
                'slug' => 'integratsiya-crm-tracker-telegram-i-form',
                'excerpt_html' => '<p>Помогаем собрать рабочую связку между лендингами, формами, трекером, CRM и Telegram-операционкой без ручного хаоса.</p>',
                'content_html' => '<p>Если задача уже понятна и нужен исполнитель, сведем с integrator. Если сначала нужен технический разбор — включаем аудитный формат. В любом случае цель одна: чтобы данные и уведомления двигались по понятной схеме.</p>',
            ],
            [
                'group' => 'architecture',
                'title' => 'SOP, handoff и операционная архитектура команды',
                'slug' => 'sop-handoff-i-operatsionnaya-arkhitektura-komandy',
                'excerpt_html' => '<p>Помогаем разложить роли, handoff, доступы, контрольные точки и правила эскалации, чтобы команда не держалась на памяти пары людей.</p>',
                'content_html' => '<p>Это услуга для команд, которые доросли до точки, где надо не просто "еще одного человека", а понятную структуру работы. Смотрим роли, SLA, handoff между buyer/farm/integration/editorial и закладываем схему, которую можно масштабировать.</p>',
            ],
        ];

        $en = [
            [
                'group' => 'operations',
                'title' => 'CPA and affiliate escrow service',
                'slug' => 'cpa-and-affiliate-escrow-service',
                'excerpt_html' => '<p>We act as an escrow layer for CPA deals involving accounts, access, team handoff, integrations and execution-sensitive delivery.</p>',
                'content_html' => '<p>We structure the deal, define deliverables, acceptance checkpoints and release conditions, then keep both sides aligned until obligations are closed.</p>',
            ],
            [
                'group' => 'analytics',
                'title' => 'Tracker, postback and attribution audit',
                'slug' => 'tracker-postback-and-attribution-audit',
                'excerpt_html' => '<p>We audit attribution gaps, postback chains, data loss and reporting mismatches across tracker, CRM and traffic source.</p>',
                'content_html' => '<p>This is our expert audit service focused on technical diagnosis, not vendor matching.</p>',
            ],
            [
                'group' => 'architecture',
                'title' => 'Affiliate infrastructure architecture audit',
                'slug' => 'affiliate-infrastructure-architecture-audit',
                'excerpt_html' => '<p>We review routing, access model, domains, tracker, ops flows and recovery risks across the whole affiliate stack.</p>',
                'content_html' => '<p>This is our own technical and architectural opinion on how the system should be stabilized and scaled.</p>',
            ],
            ['group' => 'operations', 'title' => 'Matching with a media buyer or team lead', 'slug' => 'matching-with-a-media-buyer-or-team-lead', 'excerpt_html' => '<p>We match teams with vetted buyers and leads based on vertical, budget, geo and launch pace.</p>', 'content_html' => '<p>This is a people-matching service built around execution fit.</p>'],
            ['group' => 'operations', 'title' => 'Matching with farm and account operators', 'slug' => 'matching-with-farm-and-account-operators', 'excerpt_html' => '<p>We help define the right farm profile and connect you with operators who work inside that loop daily.</p>', 'content_html' => '<p>Useful for warming, sustain and access-sensitive account operations.</p>'],
            ['group' => 'integration', 'title' => 'Matching with tracker integrators', 'slug' => 'matching-with-tracker-integrators', 'excerpt_html' => '<p>We connect teams with specialists who can assemble tracker, postback, CRM and alerting workflows.</p>', 'content_html' => '<p>Best for teams that need a working setup fast and cannot afford integration chaos.</p>'],
            ['group' => 'automation', 'title' => 'Matching with automation and antidetect developers', 'slug' => 'matching-with-automation-and-antidetect-developers', 'excerpt_html' => '<p>We connect you with developers for internal automation, operational tooling and antidetect-adjacent routines.</p>', 'content_html' => '<p>Designed for affiliate ops use cases, not generic software staffing.</p>'],
            ['group' => 'operations', 'title' => 'Matching with affiliate creative teams', 'slug' => 'matching-with-affiliate-creative-teams', 'excerpt_html' => '<p>We connect teams with creative operators who understand performance rhythm, iteration pressure and policy constraints.</p>', 'content_html' => '<p>Useful when you need output velocity together with execution discipline.</p>'],
            ['group' => 'security', 'title' => 'Matching with compliance, legal and risk specialists', 'slug' => 'matching-with-compliance-legal-and-risk-specialists', 'excerpt_html' => '<p>We connect teams with specialists for payment risk, legal framing, access discipline and operational safeguards.</p>', 'content_html' => '<p>Best when your bottleneck is governance rather than traffic volume.</p>'],
            ['group' => 'analytics', 'title' => 'Source mix, funnel and unit-logic review', 'slug' => 'source-mix-funnel-and-unit-logic-review', 'excerpt_html' => '<p>We review where source, funnel and unit economics stop telling the same story.</p>', 'content_html' => '<p>A practical review to identify what should be fixed first.</p>'],
            ['group' => 'architecture', 'title' => 'Cloaking, routing and fallback review', 'slug' => 'cloaking-routing-and-fallback-review', 'excerpt_html' => '<p>We review routing logic, fallback handling and failure modes across the delivery chain.</p>', 'content_html' => '<p>Useful when routing has become a critical dependency in the business.</p>'],
            ['group' => 'integration', 'title' => 'CRM, tracker, Telegram and form integrations', 'slug' => 'crm-tracker-telegram-and-form-integrations', 'excerpt_html' => '<p>We help connect landing pages, forms, tracker, CRM and Telegram operations into one workable flow.</p>', 'content_html' => '<p>Can be handled as expert review first or as matching with the right integrator.</p>'],
            ['group' => 'architecture', 'title' => 'SOP, handoff and team operating architecture', 'slug' => 'sop-handoff-and-team-operating-architecture', 'excerpt_html' => '<p>We help structure roles, handoff, access model and escalation logic so the team does not depend on a few people remembering everything.</p>', 'content_html' => '<p>Designed for teams that need operating structure, not just another hire.</p>'],
        ];

        return $lang === 'ru' ? $ru : $en;
    }
}

if (!function_exists('public_services_seed_default_catalog')) {
    function public_services_seed_default_catalog($FRMWRK, string $host, string $lang = 'en'): bool
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_services_ensure_schema($db)) {
            return false;
        }

        $host = strtolower(trim($host));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        $hostSafe = mysqli_real_escape_string($db, $host);
        $langSafe = mysqli_real_escape_string($db, public_services_normalize_lang($lang));
        $items = public_services_default_catalog($lang);
        if (empty($items)) {
            return false;
        }

        foreach ($items as $index => $item) {
            $group = mysqli_real_escape_string($db, public_services_normalize_group((string)($item['group'] ?? 'general')));
            $title = mysqli_real_escape_string($db, trim((string)($item['title'] ?? '')));
            $slug = mysqli_real_escape_string($db, trim((string)($item['slug'] ?? public_services_slugify((string)($item['title'] ?? '')))));
            $excerpt = mysqli_real_escape_string($db, trim((string)($item['excerpt_html'] ?? '')));
            $content = mysqli_real_escape_string($db, trim((string)($item['content_html'] ?? '')));
            $sortOrder = 10 + ($index * 10);

            if ($title === '' || $slug === '') {
                continue;
            }

            $exists = mysqli_query(
                $db,
                "SELECT id
                 FROM public_services
                 WHERE domain_host = '{$hostSafe}'
                   AND lang_code = '{$langSafe}'
                   AND slug = '{$slug}'
                 LIMIT 1"
            );
            if ($exists && mysqli_num_rows($exists) > 0) {
                continue;
            }

            mysqli_query(
                $db,
                "INSERT INTO public_services
                    (domain_host, lang_code, service_group, title, slug, excerpt_html, content_html, sort_order, is_published, created_at, updated_at)
                 VALUES
                    ('{$hostSafe}', '{$langSafe}', '{$group}', '{$title}', '{$slug}', '{$excerpt}', '{$content}', {$sortOrder}, 1, NOW(), NOW())"
            );
        }

        return true;
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

