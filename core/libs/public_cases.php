<?php

if (!function_exists('public_cases_slugify')) {
    function public_cases_slugify(string $raw): string
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
            $raw = 'case-' . date('YmdHis');
        }
        return substr($raw, 0, 190);
    }
}

if (!function_exists('public_cases_normalize_lang')) {
    function public_cases_normalize_lang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        return in_array($lang, ['ru', 'en'], true) ? $lang : 'en';
    }
}

if (!function_exists('public_cases_resolve_lang')) {
    function public_cases_resolve_lang(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host !== '' && preg_match('/\.ru$/', $host)) {
            return 'ru';
        }
        $fromQuery = (string)($_GET['lang'] ?? '');
        if ($fromQuery !== '') {
            return public_cases_normalize_lang($fromQuery);
        }
        return 'en';
    }
}

if (!function_exists('public_cases_table_exists')) {
    function public_cases_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'public_cases'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_cases_ensure_schema')) {
    function public_cases_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_cases (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                domain_host VARCHAR(190) NOT NULL DEFAULT '',
                lang_code VARCHAR(8) NOT NULL DEFAULT 'en',
                title VARCHAR(255) NOT NULL DEFAULT '',
                slug VARCHAR(190) NOT NULL DEFAULT '',
                symbolic_code VARCHAR(190) NOT NULL DEFAULT '',
                client_name VARCHAR(255) NOT NULL DEFAULT '',
                industry_summary VARCHAR(255) NOT NULL DEFAULT '',
                period_summary VARCHAR(255) NOT NULL DEFAULT '',
                role_summary VARCHAR(255) NOT NULL DEFAULT '',
                stack_summary VARCHAR(255) NOT NULL DEFAULT '',
                problem_summary VARCHAR(255) NOT NULL DEFAULT '',
                result_summary VARCHAR(255) NOT NULL DEFAULT '',
                seo_title VARCHAR(255) NOT NULL DEFAULT '',
                seo_description VARCHAR(255) NOT NULL DEFAULT '',
                excerpt_html TEXT NULL,
                challenge_html MEDIUMTEXT NULL,
                solution_html MEDIUMTEXT NULL,
                architecture_html MEDIUMTEXT NULL,
                results_html MEDIUMTEXT NULL,
                metrics_html MEDIUMTEXT NULL,
                deliverables_html MEDIUMTEXT NULL,
                sort_order INT NOT NULL DEFAULT 100,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_cases_domain_lang_slug (domain_host, lang_code, slug),
                UNIQUE KEY uniq_public_cases_domain_lang_symbolic_code (domain_host, lang_code, symbolic_code),
                KEY idx_public_cases_host_lang (domain_host, lang_code),
                KEY idx_public_cases_publish_sort (is_published, sort_order, id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_cases_table_exists($db);
    }
}

if (!function_exists('public_cases_fetch_published')) {
    function public_cases_fetch_published($FRMWRK, string $host, string $lang = 'en'): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_cases_ensure_schema($db)) {
            return [];
        }

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langNorm = public_cases_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $langNorm);
        $fallbackLangSafe = mysqli_real_escape_string($db, $langNorm === 'ru' ? 'en' : 'ru');

        $sql = "SELECT id, domain_host, lang_code, title, slug, symbolic_code, client_name, industry_summary,
                       period_summary, role_summary, stack_summary, problem_summary, result_summary,
                       seo_title, seo_description, excerpt_html, challenge_html, solution_html,
                       architecture_html, results_html, metrics_html, deliverables_html,
                       sort_order, is_published, created_at, updated_at
                FROM public_cases
                WHERE is_published = 1
                  AND lang_code = '%s'
                  AND (domain_host = '' OR domain_host = '%s')
                ORDER BY sort_order ASC, id DESC";

        $rows = $FRMWRK->DBRecords(sprintf($sql, $langSafe, $hostSafe));
        if (!empty($rows)) {
            return $rows;
        }

        return $FRMWRK->DBRecords(sprintf($sql, $fallbackLangSafe, $hostSafe));
    }
}

if (!function_exists('public_cases_fetch_published_by_code')) {
    function public_cases_fetch_published_by_code($FRMWRK, string $host, string $code, string $lang = 'en'): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_cases_ensure_schema($db)) {
            return null;
        }

        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langNorm = public_cases_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $langNorm);
        $fallbackLangSafe = mysqli_real_escape_string($db, $langNorm === 'ru' ? 'en' : 'ru');
        $codeSafe = mysqli_real_escape_string($db, public_cases_slugify($code));
        if ($codeSafe === '') {
            return null;
        }

        $sql = "SELECT id, domain_host, lang_code, title, slug, symbolic_code, client_name, industry_summary,
                       period_summary, role_summary, stack_summary, problem_summary, result_summary,
                       seo_title, seo_description, excerpt_html, challenge_html, solution_html,
                       architecture_html, results_html, metrics_html, deliverables_html,
                       sort_order, is_published, created_at, updated_at
                FROM public_cases
                WHERE is_published = 1
                  AND lang_code = '%s'
                  AND (domain_host = '' OR domain_host = '%s')
                  AND (symbolic_code = '%s' OR slug = '%s')
                ORDER BY (domain_host = '%s') DESC, id DESC
                LIMIT 1";

        $rows = $FRMWRK->DBRecords(sprintf($sql, $langSafe, $hostSafe, $codeSafe, $codeSafe, $hostSafe));
        if (is_array($rows) && !empty($rows) && isset($rows[0]) && is_array($rows[0])) {
            return $rows[0];
        }

        $fallbackRows = $FRMWRK->DBRecords(sprintf($sql, $fallbackLangSafe, $hostSafe, $codeSafe, $codeSafe, $hostSafe));
        if (is_array($fallbackRows) && !empty($fallbackRows) && isset($fallbackRows[0]) && is_array($fallbackRows[0])) {
            return $fallbackRows[0];
        }

        return null;
    }
}

if (!function_exists('public_cases_map_from_project')) {
    function public_cases_map_from_project(array $project): array
    {
        $title = trim((string)($project['title'] ?? ''));
        $slug = public_cases_slugify((string)($project['slug'] ?? $title));
        $symbolic = public_cases_slugify((string)($project['symbolic_code'] ?? $slug));
        $projectUrl = trim((string)($project['project_url'] ?? ''));
        $clientName = trim((string)parse_url($projectUrl, PHP_URL_HOST));
        if ($clientName === '') {
            $clientName = trim((string)preg_replace('~^https?://~i', '', $projectUrl));
            $clientName = trim((string)strtok($clientName, '/'));
        }
        $challengeHtml = (string)($project['challenge_html'] ?? '');
        $impactHtml = (string)($project['impact_html'] ?? '');
        $resultSummary = trim((string)($project['result_summary'] ?? ''));
        $excerptHtml = (string)($project['excerpt_html'] ?? '');
        $seoDescription = trim((string)preg_replace('/\s+/u', ' ', strip_tags($excerptHtml)));
        if ($seoDescription === '') {
            $seoDescription = $resultSummary;
        }
        if ($seoDescription === '') {
            $seoDescription = trim((string)preg_replace('/\s+/u', ' ', strip_tags($challengeHtml)));
        }

        $problemSummary = trim((string)preg_replace('/\s+/u', ' ', strip_tags($challengeHtml)));
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($problemSummary, 'UTF-8') > 220) {
                $problemSummary = rtrim((string)mb_substr($problemSummary, 0, 219, 'UTF-8')) . '...';
            }
        } elseif (strlen($problemSummary) > 220) {
            $problemSummary = rtrim(substr($problemSummary, 0, 219)) . '...';
        }

        $architecture = (string)($project['stack_summary'] ?? '');
        if ($architecture !== '' && strip_tags($architecture) === $architecture) {
            $architecture = '<p>' . htmlspecialchars($architecture, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'symbolic_code' => $symbolic,
            'client_name' => $clientName,
            'industry_summary' => (string)($project['industry_summary'] ?? ''),
            'period_summary' => (string)($project['period_summary'] ?? ''),
            'role_summary' => (string)($project['role_summary'] ?? ''),
            'stack_summary' => (string)($project['stack_summary'] ?? ''),
            'problem_summary' => $problemSummary,
            'result_summary' => $resultSummary,
            'seo_title' => $title,
            'seo_description' => $seoDescription,
            'excerpt_html' => $excerptHtml,
            'challenge_html' => $challengeHtml,
            'solution_html' => (string)($project['solution_html'] ?? ''),
            'architecture_html' => $architecture,
            'results_html' => $impactHtml,
            'metrics_html' => (string)($project['metrics_html'] ?? ''),
            'deliverables_html' => (string)($project['deliverables_html'] ?? ''),
            'sort_order' => (int)($project['sort_order'] ?? 100),
            'is_published' => ((int)($project['is_published'] ?? 1) === 1) ? 1 : 0,
        ];
    }
}

if (!function_exists('public_cases_sync_from_projects')) {
    function public_cases_sync_from_projects($FRMWRK, string $host, string $lang = 'en'): array
    {
        static $done = [];
        $db = $FRMWRK->DB();
        if (!$db || !public_cases_ensure_schema($db) || !function_exists('public_projects_fetch_published')) {
            return ['inserted' => 0, 'updated' => 0];
        }

        $hostNorm = strtolower(trim($host));
        if (strpos($hostNorm, ':') !== false) {
            $hostNorm = explode(':', $hostNorm, 2)[0];
        }
        $langNorm = public_cases_normalize_lang($lang);
        $guardKey = $hostNorm . '|' . $langNorm;
        if (isset($done[$guardKey])) {
            return ['inserted' => 0, 'updated' => 0];
        }
        $done[$guardKey] = true;

        $projectRows = public_projects_fetch_published($FRMWRK, $hostNorm, $langNorm);
        if (empty($projectRows)) {
            return ['inserted' => 0, 'updated' => 0];
        }

        $hostSafe = mysqli_real_escape_string($db, $hostNorm);
        $langSafe = mysqli_real_escape_string($db, $langNorm);
        $inserted = 0;
        $updated = 0;

        $textFields = [
            'client_name', 'industry_summary', 'period_summary', 'role_summary', 'stack_summary',
            'problem_summary', 'result_summary', 'seo_title', 'seo_description',
        ];
        $htmlFields = [
            'excerpt_html', 'challenge_html', 'solution_html', 'architecture_html',
            'results_html', 'metrics_html', 'deliverables_html',
        ];

        foreach ($projectRows as $projectRow) {
            if (!is_array($projectRow)) {
                continue;
            }
            $mapped = public_cases_map_from_project($projectRow);
            $symbolicCode = public_cases_slugify((string)($mapped['symbolic_code'] ?? ''));
            $slug = public_cases_slugify((string)($mapped['slug'] ?? ''));
            if ($symbolicCode === '' || $slug === '') {
                continue;
            }
            $symbolicSafe = mysqli_real_escape_string($db, $symbolicCode);
            $slugSafe = mysqli_real_escape_string($db, $slug);

            $existingRows = $FRMWRK->DBRecords(
                "SELECT *
                 FROM public_cases
                 WHERE lang_code = '{$langSafe}'
                   AND (domain_host = '' OR domain_host = '{$hostSafe}')
                   AND (symbolic_code = '{$symbolicSafe}' OR slug = '{$slugSafe}')
                 ORDER BY (domain_host = '{$hostSafe}') DESC, id DESC
                 LIMIT 1"
            );

            if (is_array($existingRows) && !empty($existingRows) && isset($existingRows[0]) && is_array($existingRows[0])) {
                $existing = $existingRows[0];
                $updates = [];

                foreach ($textFields as $field) {
                    $old = trim((string)($existing[$field] ?? ''));
                    $new = trim((string)($mapped[$field] ?? ''));
                    if ($old === '' && $new !== '') {
                        $updates[$field] = $new;
                    }
                }
                foreach ($htmlFields as $field) {
                    $oldRaw = (string)($existing[$field] ?? '');
                    $newRaw = (string)($mapped[$field] ?? '');
                    $oldLen = strlen(trim(strip_tags($oldRaw)));
                    $newLen = strlen(trim(strip_tags($newRaw)));
                    if ($newLen <= 0) {
                        continue;
                    }
                    if ($oldLen === 0 || ($oldLen < 24 && $newLen > $oldLen)) {
                        $updates[$field] = $newRaw;
                    }
                }

                if (!empty($updates)) {
                    $set = [];
                    foreach ($updates as $field => $value) {
                        $set[] = "{$field}='" . mysqli_real_escape_string($db, (string)$value) . "'";
                    }
                    $set[] = 'updated_at=NOW()';
                    $id = (int)($existing['id'] ?? 0);
                    if ($id > 0) {
                        mysqli_query($db, "UPDATE public_cases SET " . implode(', ', $set) . " WHERE id={$id} LIMIT 1");
                        if (!mysqli_error($db)) {
                            $updated++;
                        }
                    }
                }
                continue;
            }

            $pairs = [
                'domain_host' => '',
                'lang_code' => $langNorm,
                'title' => (string)($mapped['title'] ?? ''),
                'slug' => $slug,
                'symbolic_code' => $symbolicCode,
                'client_name' => (string)($mapped['client_name'] ?? ''),
                'industry_summary' => (string)($mapped['industry_summary'] ?? ''),
                'period_summary' => (string)($mapped['period_summary'] ?? ''),
                'role_summary' => (string)($mapped['role_summary'] ?? ''),
                'stack_summary' => (string)($mapped['stack_summary'] ?? ''),
                'problem_summary' => (string)($mapped['problem_summary'] ?? ''),
                'result_summary' => (string)($mapped['result_summary'] ?? ''),
                'seo_title' => (string)($mapped['seo_title'] ?? ''),
                'seo_description' => (string)($mapped['seo_description'] ?? ''),
                'excerpt_html' => (string)($mapped['excerpt_html'] ?? ''),
                'challenge_html' => (string)($mapped['challenge_html'] ?? ''),
                'solution_html' => (string)($mapped['solution_html'] ?? ''),
                'architecture_html' => (string)($mapped['architecture_html'] ?? ''),
                'results_html' => (string)($mapped['results_html'] ?? ''),
                'metrics_html' => (string)($mapped['metrics_html'] ?? ''),
                'deliverables_html' => (string)($mapped['deliverables_html'] ?? ''),
            ];

            $safe = [];
            foreach ($pairs as $key => $value) {
                $safe[$key] = mysqli_real_escape_string($db, (string)$value);
            }
            $sortOrder = (int)($mapped['sort_order'] ?? 100);
            $isPublished = ((int)($mapped['is_published'] ?? 1) === 1) ? 1 : 0;

            mysqli_query(
                $db,
                "INSERT INTO public_cases
                    (domain_host, lang_code, title, slug, symbolic_code, client_name, industry_summary, period_summary, role_summary,
                     stack_summary, problem_summary, result_summary, seo_title, seo_description, excerpt_html, challenge_html,
                     solution_html, architecture_html, results_html, metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at)
                 VALUES
                    ('{$safe['domain_host']}', '{$safe['lang_code']}', '{$safe['title']}', '{$safe['slug']}', '{$safe['symbolic_code']}',
                     '{$safe['client_name']}', '{$safe['industry_summary']}', '{$safe['period_summary']}', '{$safe['role_summary']}',
                     '{$safe['stack_summary']}', '{$safe['problem_summary']}', '{$safe['result_summary']}', '{$safe['seo_title']}',
                     '{$safe['seo_description']}', '{$safe['excerpt_html']}', '{$safe['challenge_html']}', '{$safe['solution_html']}',
                     '{$safe['architecture_html']}', '{$safe['results_html']}', '{$safe['metrics_html']}', '{$safe['deliverables_html']}',
                     {$sortOrder}, {$isPublished}, NOW(), NOW())"
            );
            if (!mysqli_error($db)) {
                $inserted++;
            }
        }

        return ['inserted' => $inserted, 'updated' => $updated];
    }
}
