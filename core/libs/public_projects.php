<?php

if (!function_exists('public_projects_slugify')) {
    function public_projects_slugify(string $raw): string
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
            $raw = 'project-' . date('YmdHis');
        }
        return substr($raw, 0, 190);
    }
}

if (!function_exists('public_projects_normalize_lang')) {
    function public_projects_normalize_lang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        return in_array($lang, ['ru', 'en'], true) ? $lang : 'en';
    }
}

if (!function_exists('public_projects_resolve_lang')) {
    function public_projects_resolve_lang(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host !== '' && preg_match('/\.ru$/', $host)) {
            return 'ru';
        }
        $fromQuery = (string)($_GET['lang'] ?? '');
        if ($fromQuery !== '') {
            return public_projects_normalize_lang($fromQuery);
        }
        return 'en';
    }
}

if (!function_exists('public_projects_table_exists')) {
    function public_projects_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'public_projects'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_projects_column_exists')) {
    function public_projects_column_exists(mysqli $db, string $column): bool
    {
        $columnSafe = mysqli_real_escape_string($db, $column);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'public_projects'
               AND COLUMN_NAME = '{$columnSafe}'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_projects_index_exists')) {
    function public_projects_index_exists(mysqli $db, string $index): bool
    {
        $indexSafe = mysqli_real_escape_string($db, $index);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'public_projects'
               AND INDEX_NAME = '{$indexSafe}'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_projects_ensure_schema')) {
    function public_projects_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_projects (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                domain_host VARCHAR(190) NOT NULL DEFAULT '',
                lang_code VARCHAR(8) NOT NULL DEFAULT 'en',
                title VARCHAR(255) NOT NULL DEFAULT '',
                slug VARCHAR(190) NOT NULL DEFAULT '',
                symbolic_code VARCHAR(190) NOT NULL DEFAULT '',
                project_url VARCHAR(255) NOT NULL DEFAULT '',
                role_summary VARCHAR(255) NOT NULL DEFAULT '',
                industry_summary VARCHAR(255) NOT NULL DEFAULT '',
                period_summary VARCHAR(255) NOT NULL DEFAULT '',
                stack_summary VARCHAR(255) NOT NULL DEFAULT '',
                result_summary VARCHAR(255) NOT NULL DEFAULT '',
                excerpt_html TEXT NULL,
                challenge_html MEDIUMTEXT NULL,
                solution_html MEDIUMTEXT NULL,
                impact_html MEDIUMTEXT NULL,
                metrics_html MEDIUMTEXT NULL,
                deliverables_html MEDIUMTEXT NULL,
                sort_order INT NOT NULL DEFAULT 100,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_projects_domain_lang_slug (domain_host, lang_code, slug),
                UNIQUE KEY uniq_public_projects_domain_lang_symbolic_code (domain_host, lang_code, symbolic_code),
                KEY idx_public_projects_host_lang (domain_host, lang_code),
                KEY idx_public_projects_publish_sort (is_published, sort_order, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }

        $alters = [
            'symbolic_code' => "ALTER TABLE public_projects ADD COLUMN symbolic_code VARCHAR(190) NOT NULL DEFAULT '' AFTER slug",
            'industry_summary' => "ALTER TABLE public_projects ADD COLUMN industry_summary VARCHAR(255) NOT NULL DEFAULT '' AFTER role_summary",
            'period_summary' => "ALTER TABLE public_projects ADD COLUMN period_summary VARCHAR(255) NOT NULL DEFAULT '' AFTER industry_summary",
            'metrics_html' => "ALTER TABLE public_projects ADD COLUMN metrics_html MEDIUMTEXT NULL AFTER impact_html",
            'deliverables_html' => "ALTER TABLE public_projects ADD COLUMN deliverables_html MEDIUMTEXT NULL AFTER metrics_html",
        ];
        foreach ($alters as $column => $alterSql) {
            if (!public_projects_column_exists($db, $column)) {
                mysqli_query($db, $alterSql);
            }
        }

        mysqli_query(
            $db,
            "UPDATE public_projects
             SET symbolic_code = slug
             WHERE symbolic_code = ''
               AND slug <> ''"
        );
        if (!public_projects_index_exists($db, 'uniq_public_projects_domain_lang_symbolic_code')) {
            mysqli_query(
                $db,
                "ALTER TABLE public_projects
                 ADD UNIQUE KEY uniq_public_projects_domain_lang_symbolic_code (domain_host, lang_code, symbolic_code)"
            );
        }
        return public_projects_table_exists($db);
    }
}

if (!function_exists('public_projects_fetch_published')) {
    function public_projects_fetch_published($FRMWRK, string $host, string $lang = 'en'): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_projects_ensure_schema($db)) {
            return [];
        }

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langNorm = public_projects_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $langNorm);
        $fallbackLangSafe = mysqli_real_escape_string($db, $langNorm === 'ru' ? 'en' : 'ru');

        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary,
                    period_summary, stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html,
                    metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at
             FROM public_projects
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
             ORDER BY (domain_host = '{$hostSafe}') DESC, sort_order ASC, id DESC"
        );

        if (!empty($rows)) {
            return public_projects_deduplicate_rows((array)$rows);
        }

        $fallbackRows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary,
                    period_summary, stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html,
                    metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at
             FROM public_projects
             WHERE is_published = 1
               AND lang_code = '{$fallbackLangSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
             ORDER BY (domain_host = '{$hostSafe}') DESC, sort_order ASC, id DESC"
        );
        return public_projects_deduplicate_rows((array)$fallbackRows);
    }
}

if (!function_exists('public_projects_fetch_published_by_code')) {
    function public_projects_fetch_published_by_code($FRMWRK, string $host, string $code, string $lang = 'en'): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_projects_ensure_schema($db)) {
            return null;
        }

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langNorm = public_projects_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $langNorm);
        $fallbackLangSafe = mysqli_real_escape_string($db, $langNorm === 'ru' ? 'en' : 'ru');
        $codeSafe = mysqli_real_escape_string($db, public_projects_slugify($code));
        if ($codeSafe === '') {
            return null;
        }

        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary,
                    period_summary, stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html,
                    metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at
             FROM public_projects
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               AND (symbolic_code = '{$codeSafe}' OR slug = '{$codeSafe}')
             ORDER BY (domain_host = '{$hostSafe}') DESC, id DESC
             LIMIT 1"
        );
        if (is_array($rows) && !empty($rows) && isset($rows[0]) && is_array($rows[0])) {
            return $rows[0];
        }

        $fallbackRows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary,
                    period_summary, stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html,
                    metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at
             FROM public_projects
             WHERE is_published = 1
               AND lang_code = '{$fallbackLangSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               AND (symbolic_code = '{$codeSafe}' OR slug = '{$codeSafe}')
             ORDER BY (domain_host = '{$hostSafe}') DESC, id DESC
             LIMIT 1"
        );
        if (is_array($fallbackRows) && !empty($fallbackRows) && isset($fallbackRows[0]) && is_array($fallbackRows[0])) {
            return $fallbackRows[0];
        }
        return null;
    }
}

if (!function_exists('public_projects_deduplicate_rows')) {
    function public_projects_deduplicate_rows(array $rows): array
    {
        $seen = [];
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $code = trim((string)($row['symbolic_code'] ?? ''));
            if ($code === '') {
                $code = trim((string)($row['slug'] ?? ''));
            }
            if ($code === '') {
                $code = trim((string)($row['title'] ?? ''));
            }
            $key = strtolower(public_projects_slugify($code));
            if ($key === '') {
                $key = 'row-' . (string)count($out);
            }
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $row;
        }
        return $out;
    }
}

if (!function_exists('public_projects_default_products')) {
    function public_projects_default_products(string $lang = 'en'): array
    {
        $lang = public_projects_normalize_lang($lang);
        $isRu = ($lang === 'ru');

        $geoipExcerpt = $isRu
            ? '<p>GeoIP + antifraud API для входа, checkout и риск-маршрутизации: гео, ASN, прокси/VPN/tor сигналы, device контекст и explainable scoring.</p>'
            : '<p>GeoIP + antifraud API for login, checkout and risk routing: geo, ASN, proxy/VPN/tor signals, device context and explainable scoring.</p>';
        $postforgeExcerpt = $isRu
            ? '<p>AI-платформа для управляемого SEO-производства: кластеры спроса, качество контента, расписание публикаций и прозрачная аналитика эффективности.</p>'
            : '<p>AI platform for governed SEO production: demand clustering, content quality controls, publish scheduling and transparent performance analytics.</p>';
        $trackerExcerpt = $isRu
            ? '<p>ANTIFRAUD TRACKER для risk-ops команд: очереди инцидентов, SLA-эскалации, аудит действий и связка с CRM/SIEM/helpdesk.</p>'
            : '<p>ANTIFRAUD TRACKER for risk-ops teams: incident queues, SLA escalations, audit trails and CRM/SIEM/helpdesk integrations.</p>';

        return [
            [
                'title' => 'geoip.space',
                'slug' => 'geoip-space',
                'symbolic_code' => 'geoip-space',
                'project_url' => 'https://geoip.space/',
                'role_summary' => 'Geo API + antifraud scoring',
                'industry_summary' => $isRu ? 'Fintech, e-commerce, SaaS, adtech' : 'Fintech, e-commerce, SaaS, adtech',
                'period_summary' => 'Starter from $29/mo; Growth from $99/mo; Enterprise custom',
                'stack_summary' => 'REST API (JSON), webhooks, SDK snippets for PHP/Node/Python/Go/Java',
                'result_summary' => $isRu ? 'Снижает fraud-losses и false-positive блокировки за счет объяснимого risk-score.' : 'Reduces fraud losses and false-positive blocks with explainable risk scoring.',
                'excerpt_html' => $geoipExcerpt,
                'challenge_html' => $isRu ? '<ul><li>Тарифы по числу проверок и SLA.</li><li>Отдельные лимиты для real-time и batch.</li><li>Enterprise: выделенный контур и compliance-пакет.</li></ul>' : '<ul><li>Pricing by checks volume and SLA.</li><li>Separate limits for real-time and batch usage.</li><li>Enterprise includes dedicated environment and compliance package.</li></ul>',
                'solution_html' => $isRu ? '<ul><li>Интеграция в любой backend через REST.</li><li>Webhook-сценарии для SIEM/CRM/BI.</li><li>Поддержка feature flags для безопасного rollout.</li></ul>' : '<ul><li>Integrates with any backend over REST.</li><li>Webhook flows for SIEM/CRM/BI pipelines.</li><li>Feature-flag rollout support for safe adoption.</li></ul>',
                'impact_html' => $isRu ? '<p>Оптимален для auth, onboarding, payout и order-risk потоков, где критичны скорость решения и трассируемость причин.</p>' : '<p>Best fit for auth, onboarding, payout and order-risk flows where decision speed and traceability are critical.</p>',
                'metrics_html' => $isRu ? '<ul><li>Снижение ручной верификации.</li><li>Сокращение времени ответа risk-движка.</li><li>Рост точности сегментации трафика.</li></ul>' : '<ul><li>Less manual verification workload.</li><li>Faster risk engine response time.</li><li>Higher traffic segmentation accuracy.</li></ul>',
                'deliverables_html' => $isRu ? '<ul><li>API ключи и окружения.</li><li>Правила скоринга и webhook-схемы.</li><li>Пакет мониторинга и runbook.</li></ul>' : '<ul><li>API keys and environments.</li><li>Scoring rules and webhook schemes.</li><li>Monitoring package and runbook.</li></ul>',
                'sort_order' => 10,
                'is_published' => 1,
            ],
            [
                'title' => 'postforge.ru',
                'slug' => 'postforge-ru',
                'symbolic_code' => 'postforge-ru',
                'project_url' => 'https://postforge.ru/',
                'role_summary' => 'AI SEO publishing pipeline',
                'industry_summary' => $isRu ? 'Media, in-house marketing, B2B content teams' : 'Media, in-house marketing, B2B content teams',
                'period_summary' => 'Starter from $49/mo; Pro from $199/mo; Enterprise custom',
                'stack_summary' => 'WordPress/headless CMS/custom CMS, webhooks, queue workers, scheduled jobs',
                'result_summary' => $isRu ? 'Дает стабильный SEO throughput без контентного шума и с контролем качества.' : 'Delivers stable SEO throughput without content noise and with quality governance.',
                'excerpt_html' => $postforgeExcerpt,
                'challenge_html' => $isRu ? '<ul><li>Оплата по объему выпуска и редактурным этапам.</li><li>Пакеты для single-brand и multi-site.</li><li>Enterprise: бренд-гайд, tone и юридические ограничения.</li></ul>' : '<ul><li>Pricing by output volume and editorial stages.</li><li>Packages for single-brand and multi-site teams.</li><li>Enterprise includes brand style, tone and legal constraints.</li></ul>',
                'solution_html' => $isRu ? '<ul><li>Интеграция в любой CMS или backend.</li><li>Внешние workflow через API/webhooks.</li><li>Поддержка moderation gates и human-in-the-loop.</li></ul>' : '<ul><li>Integrates with any CMS or backend.</li><li>External workflow support via API/webhooks.</li><li>Moderation gates and human-in-the-loop stages.</li></ul>',
                'impact_html' => $isRu ? '<p>Подходит командам, которым нужно наращивать SEO-покрытие контролируемо, а не просто увеличивать количество публикаций.</p>' : '<p>Built for teams that need controlled SEO coverage growth, not just higher publishing volume.</p>',
                'metrics_html' => $isRu ? '<ul><li>Ускорение time-to-publish.</li><li>Снижение доли контента на доработку.</li><li>Рост доли страниц в индексе.</li></ul>' : '<ul><li>Faster time-to-publish.</li><li>Lower rework ratio.</li><li>Higher indexed pages coverage.</li></ul>',
                'deliverables_html' => $isRu ? '<ul><li>Контентный pipeline и шаблоны.</li><li>Интеграции с CMS и аналитикой.</li><li>Календарь публикаций и SLA.</li></ul>' : '<ul><li>Content pipeline and templates.</li><li>CMS and analytics integrations.</li><li>Publishing calendar and SLA.</li></ul>',
                'sort_order' => 20,
                'is_published' => 1,
            ],
            [
                'title' => 'ANTIFRAUD TRACKER',
                'slug' => 'antifraud-tracker',
                'symbolic_code' => 'antifraud-tracker',
                'project_url' => '',
                'role_summary' => 'Risk event tracking and incident workflows',
                'industry_summary' => $isRu ? 'Adtech, fintech, marketplaces, support/risk teams' : 'Adtech, fintech, marketplaces, support/risk teams',
                'period_summary' => 'Team from $79/mo; Scale from $299/mo; Enterprise custom',
                'stack_summary' => 'Event bus/REST/webhooks, SSO, audit exports, CRM/SIEM/helpdesk connectors',
                'result_summary' => $isRu ? 'Сокращает время от детекта до решения и делает антифрод-процессы управляемыми.' : 'Cuts time from detection to resolution and makes antifraud operations measurable.',
                'excerpt_html' => $trackerExcerpt,
                'challenge_html' => $isRu ? '<ul><li>Тарифы по числу активных инцидентов и команде.</li><li>Отдельная стоимость enterprise-интеграций.</li><li>SLA и регламенты эскалаций настраиваются под процесс.</li></ul>' : '<ul><li>Pricing by active incidents and team size.</li><li>Enterprise connectors are scoped separately.</li><li>SLA and escalation policies are configurable.</li></ul>',
                'solution_html' => $isRu ? '<ul><li>Интеграция с любым backend через event ingestion.</li><li>Синхронизация инцидентов в CRM/SIEM/helpdesk.</li><li>API фильтры и роли доступа для команд.</li></ul>' : '<ul><li>Connects to any backend via event ingestion.</li><li>Incident sync with CRM/SIEM/helpdesk.</li><li>API filters and role-based access for ops teams.</li></ul>',
                'impact_html' => $isRu ? '<p>Особенно полезен в high-risk трафике: быстрые эскалации, прозрачная история решений и контроль false-positive стоимости.</p>' : '<p>Best for high-risk traffic operations: fast escalations, transparent decision history and false-positive cost control.</p>',
                'metrics_html' => $isRu ? '<ul><li>MTTR по инцидентам снижается.</li><li>Меньше ручных передач между командами.</li><li>Больше повторно используемых playbook-сценариев.</li></ul>' : '<ul><li>Lower incident MTTR.</li><li>Fewer manual handoffs across teams.</li><li>More reusable response playbooks.</li></ul>',
                'deliverables_html' => $isRu ? '<ul><li>Модель инцидентов и приоритеты.</li><li>Правила эскалаций и SLA.</li><li>Интеграции и аудит-отчеты.</li></ul>' : '<ul><li>Incident model and priorities.</li><li>Escalation rules and SLA.</li><li>Integrations and audit reports.</li></ul>',
                'sort_order' => 30,
                'is_published' => 1,
            ],
        ];
    }
}

if (!function_exists('public_projects_seed_default_products')) {
    function public_projects_seed_default_products($FRMWRK, string $host, string $lang = 'en'): bool
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_projects_ensure_schema($db)) {
            return false;
        }

        $langNorm = public_projects_normalize_lang($lang);
        $hostNorm = strtolower(trim($host));
        if (strpos($hostNorm, ':') !== false) {
            $hostNorm = explode(':', $hostNorm, 2)[0];
        }
        $hostNorm = trim($hostNorm);

        $langs = [$langNorm, ($langNorm === 'ru') ? 'en' : 'ru'];
        foreach ($langs as $datasetLang) {
            $items = public_projects_default_products($datasetLang);
            foreach ((array)$items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $title = trim((string)($item['title'] ?? ''));
                if ($title === '') {
                    continue;
                }
                $rowLang = public_projects_normalize_lang((string)($item['lang_code'] ?? $datasetLang));
                $slug = public_projects_slugify((string)($item['slug'] ?? $title));
                $symbolicCode = public_projects_slugify((string)($item['symbolic_code'] ?? $slug));
                $sortOrder = (int)($item['sort_order'] ?? 100);
                $isPublished = ((int)($item['is_published'] ?? 1) === 1) ? 1 : 0;

                $domainHost = '';
                if (isset($item['domain_host'])) {
                    $domainHost = strtolower(trim((string)$item['domain_host']));
                }
                if ($domainHost === '' && $hostNorm !== '') {
                    $domainHost = '';
                }

                $domainSafe = mysqli_real_escape_string($db, $domainHost);
                $langSafe = mysqli_real_escape_string($db, $rowLang);
                $titleSafe = mysqli_real_escape_string($db, $title);
                $slugSafe = mysqli_real_escape_string($db, $slug);
                $symbolicSafe = mysqli_real_escape_string($db, $symbolicCode);
                $projectUrlSafe = mysqli_real_escape_string($db, (string)($item['project_url'] ?? ''));
                $roleSafe = mysqli_real_escape_string($db, (string)($item['role_summary'] ?? ''));
                $industrySafe = mysqli_real_escape_string($db, (string)($item['industry_summary'] ?? ''));
                $periodSafe = mysqli_real_escape_string($db, (string)($item['period_summary'] ?? ''));
                $stackSafe = mysqli_real_escape_string($db, (string)($item['stack_summary'] ?? ''));
                $resultSafe = mysqli_real_escape_string($db, (string)($item['result_summary'] ?? ''));
                $excerptSafe = mysqli_real_escape_string($db, (string)($item['excerpt_html'] ?? ''));
                $challengeSafe = mysqli_real_escape_string($db, (string)($item['challenge_html'] ?? ''));
                $solutionSafe = mysqli_real_escape_string($db, (string)($item['solution_html'] ?? ''));
                $impactSafe = mysqli_real_escape_string($db, (string)($item['impact_html'] ?? ''));
                $metricsSafe = mysqli_real_escape_string($db, (string)($item['metrics_html'] ?? ''));
                $deliverablesSafe = mysqli_real_escape_string($db, (string)($item['deliverables_html'] ?? ''));

                mysqli_query(
                    $db,
                    "INSERT IGNORE INTO public_projects
                        (domain_host, lang_code, title, slug, symbolic_code, project_url, role_summary, industry_summary, period_summary,
                         stack_summary, result_summary, excerpt_html, challenge_html, solution_html, impact_html, metrics_html, deliverables_html,
                         sort_order, is_published, created_at, updated_at)
                     VALUES
                        ('{$domainSafe}', '{$langSafe}', '{$titleSafe}', '{$slugSafe}', '{$symbolicSafe}', '{$projectUrlSafe}', '{$roleSafe}', '{$industrySafe}', '{$periodSafe}',
                         '{$stackSafe}', '{$resultSafe}', '{$excerptSafe}', '{$challengeSafe}', '{$solutionSafe}', '{$impactSafe}', '{$metricsSafe}', '{$deliverablesSafe}',
                         {$sortOrder}, {$isPublished}, NOW(), NOW())"
                );
            }
        }

        return true;
    }
}
