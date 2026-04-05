<?php

if (!function_exists('public_portal_host')) {
    function public_portal_host(): string
    {
        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        return preg_replace('/^www\./', '', trim($host));
    }
}

if (!function_exists('public_portal_lang')) {
    function public_portal_lang(?string $host = null): string
    {
        $host = $host !== null ? strtolower(trim($host)) : public_portal_host();
        return ($host !== '' && preg_match('/\.ru$/', $host)) ? 'ru' : 'en';
    }
}

if (!function_exists('public_portal_session_boot')) {
    function public_portal_session_boot(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (function_exists('session_cache_limiter')) {
                @session_cache_limiter('');
            }
            session_start();
        }
    }
}

if (!function_exists('public_portal_csrf_token')) {
    function public_portal_csrf_token(string $scope = 'portal'): string
    {
        public_portal_session_boot();
        if (!isset($_SESSION['public_portal_csrf']) || !is_array($_SESSION['public_portal_csrf'])) {
            $_SESSION['public_portal_csrf'] = [];
        }
        if (empty($_SESSION['public_portal_csrf'][$scope])) {
            $_SESSION['public_portal_csrf'][$scope] = bin2hex(random_bytes(16));
        }
        return (string)$_SESSION['public_portal_csrf'][$scope];
    }
}

if (!function_exists('public_portal_csrf_check')) {
    function public_portal_csrf_check(string $token, string $scope = 'portal'): bool
    {
        public_portal_session_boot();
        $expected = (string)($_SESSION['public_portal_csrf'][$scope] ?? '');
        return $expected !== '' && hash_equals($expected, $token);
    }
}

if (!function_exists('public_portal_flash_set')) {
    function public_portal_flash_set(string $key, array $payload): void
    {
        public_portal_session_boot();
        $_SESSION['public_portal_flash'][$key] = $payload;
    }
}

if (!function_exists('public_portal_flash_get')) {
    function public_portal_flash_get(string $key): array
    {
        public_portal_session_boot();
        $payload = [];
        if (isset($_SESSION['public_portal_flash'][$key]) && is_array($_SESSION['public_portal_flash'][$key])) {
            $payload = $_SESSION['public_portal_flash'][$key];
            unset($_SESSION['public_portal_flash'][$key]);
        }
        return $payload;
    }
}

if (!function_exists('public_portal_table_exists')) {
    function public_portal_table_exists(mysqli $db, string $table): bool
    {
        $tableSafe = mysqli_real_escape_string($db, $table);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = '{$tableSafe}'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_portal_slugify')) {
    function public_portal_slugify(string $raw, string $fallbackPrefix = 'item'): string
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
            $raw = $fallbackPrefix . '-' . date('YmdHis');
        }
        return substr($raw, 0, 180);
    }
}

if (!function_exists('public_portal_users_ensure_schema')) {
    function public_portal_users_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_users (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(190) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(120) NOT NULL DEFAULT '',
                role_code VARCHAR(32) NOT NULL DEFAULT 'member',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_portal_table_exists($db, 'public_users');
    }
}

if (!function_exists('public_portal_views_ensure_schema')) {
    function public_portal_views_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_content_views (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                content_type VARCHAR(40) NOT NULL,
                content_id BIGINT UNSIGNED NOT NULL,
                session_key VARCHAR(190) NOT NULL,
                view_date DATE NOT NULL,
                ip_hash VARCHAR(64) NOT NULL DEFAULT '',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_content_view (content_type, content_id, session_key, view_date),
                KEY idx_public_content_views_content (content_type, content_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_portal_table_exists($db, 'public_content_views');
    }
}

if (!function_exists('public_portal_comments_ensure_schema')) {
    function public_portal_comments_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_comments (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                content_type VARCHAR(40) NOT NULL,
                content_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                parent_id BIGINT UNSIGNED NULL DEFAULT NULL,
                section_code VARCHAR(32) NOT NULL DEFAULT 'discussion',
                body_markdown MEDIUMTEXT NOT NULL,
                body_html MEDIUMTEXT NOT NULL,
                is_deleted TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_public_comments_content (content_type, content_id, created_at),
                KEY idx_public_comments_parent (parent_id),
                KEY idx_public_comments_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_portal_table_exists($db, 'public_comments');
    }
}

if (!function_exists('public_portal_solutions_ensure_schema')) {
    function public_portal_solutions_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_solutions (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                domain_host VARCHAR(190) NOT NULL DEFAULT '',
                lang_code VARCHAR(8) NOT NULL DEFAULT 'en',
                solution_type VARCHAR(24) NOT NULL DEFAULT 'download',
                title VARCHAR(255) NOT NULL DEFAULT '',
                slug VARCHAR(190) NOT NULL DEFAULT '',
                symbolic_code VARCHAR(190) NOT NULL DEFAULT '',
                category_code VARCHAR(64) NOT NULL DEFAULT '',
                excerpt_html TEXT NULL,
                content_html MEDIUMTEXT NULL,
                stack_summary VARCHAR(255) NOT NULL DEFAULT '',
                difficulty_summary VARCHAR(120) NOT NULL DEFAULT '',
                file_format VARCHAR(120) NOT NULL DEFAULT '',
                download_url VARCHAR(255) NOT NULL DEFAULT '',
                repo_url VARCHAR(255) NOT NULL DEFAULT '',
                demo_url VARCHAR(255) NOT NULL DEFAULT '',
                sort_order INT NOT NULL DEFAULT 100,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_solutions_host_lang_slug (domain_host, lang_code, slug),
                UNIQUE KEY uniq_public_solutions_host_lang_code (domain_host, lang_code, symbolic_code),
                KEY idx_public_solutions_type (solution_type, is_published, sort_order, id),
                KEY idx_public_solutions_host_lang (domain_host, lang_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_portal_table_exists($db, 'public_solutions');
    }
}

if (!function_exists('public_portal_current_user')) {
    function public_portal_current_user($FRMWRK): ?array
    {
        static $resolved = false;
        static $user = null;
        if ($resolved) {
            return $user;
        }
        $resolved = true;
        public_portal_session_boot();
        $userId = (int)($_SESSION['public_user_id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_users_ensure_schema($db)) {
            return null;
        }
        $rows = $FRMWRK->DBRecords(
            "SELECT id, email, display_name, role_code, is_active, created_at
             FROM public_users
             WHERE id = {$userId}
               AND is_active = 1
             LIMIT 1"
        );
        if (!empty($rows[0]) && is_array($rows[0])) {
            $user = $rows[0];
            return $user;
        }
        unset($_SESSION['public_user_id']);
        return null;
    }
}

if (!function_exists('public_portal_format_inline')) {
    function public_portal_format_inline(string $raw): string
    {
        $safe = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        $patterns = [
            '/\*\*(.+?)\*\*/su' => '<strong>$1</strong>',
            '/\*(.+?)\*/su' => '<em>$1</em>',
            '/`(.+?)`/su' => '<code>$1</code>',
            '/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/su' => '<a href="$2" target="_blank" rel="nofollow noopener">$1</a>',
        ];
        foreach ($patterns as $pattern => $replace) {
            $safe = preg_replace($pattern, $replace, $safe);
        }
        return $safe;
    }
}

if (!function_exists('public_portal_markdown_to_html')) {
    function public_portal_markdown_to_html(string $raw): string
    {
        $raw = trim(str_replace("\r", '', $raw));
        $raw = preg_replace('/\n{3,}/', "\n\n", (string)$raw);
        $parts = preg_split('/\n{2,}/', $raw) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $lines = preg_split('/\n/', trim((string)$part)) ?: [];
            $isList = true;
            $listItems = [];
            foreach ($lines as $line) {
                $line = trim((string)$line);
                if ($line === '') {
                    continue;
                }
                if (preg_match('/^[-*]\s+(.+)$/u', $line, $m)) {
                    $listItems[] = '<li>' . public_portal_format_inline($m[1]) . '</li>';
                } else {
                    $isList = false;
                    break;
                }
            }
            if ($isList && !empty($listItems)) {
                $out[] = '<ul>' . implode('', $listItems) . '</ul>';
                continue;
            }
            $joined = implode("<br>\n", array_map('trim', $lines));
            $out[] = '<p>' . public_portal_format_inline($joined) . '</p>';
        }
        return implode("\n", $out);
    }
}

if (!function_exists('public_portal_comment_sections')) {
    function public_portal_comment_sections(string $lang = 'en'): array
    {
        if ($lang === 'ru') {
            return [
                'discussion' => 'Обсуждение',
                'question' => 'Вопрос',
                'idea' => 'Идея',
                'case' => 'Практика',
            ];
        }
        return [
            'discussion' => 'Discussion',
            'question' => 'Question',
            'idea' => 'Idea',
            'case' => 'Practice',
        ];
    }
}

if (!function_exists('public_portal_redirect_back')) {
    function public_portal_redirect_back(string $fallback = '/'): void
    {
        $returnPath = trim((string)($_POST['return_path'] ?? $_SERVER['REQUEST_URI'] ?? $fallback));
        if ($returnPath === '' || $returnPath[0] !== '/') {
            $returnPath = $fallback;
        }
        header('Location: ' . $returnPath, true, 302);
        exit;
    }
}

if (!function_exists('public_portal_handle_request')) {
    function public_portal_handle_request($FRMWRK): void
    {
        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            return;
        }
        $action = trim((string)($_POST['action'] ?? ''));
        if (strpos($action, 'public_portal_') !== 0) {
            return;
        }
        if (!public_portal_csrf_check(trim((string)($_POST['portal_csrf'] ?? '')), 'portal')) {
            public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Security token mismatch.']);
            public_portal_redirect_back('/');
        }
        $db = $FRMWRK->DB();
        if (!$db) {
            public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Database unavailable.']);
            public_portal_redirect_back('/');
        }
        public_portal_users_ensure_schema($db);
        public_portal_comments_ensure_schema($db);

        if ($action === 'public_portal_register') {
            $email = trim((string)($_POST['email'] ?? ''));
            $name = trim((string)($_POST['display_name'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password, 'UTF-8') < 8 || $name === '') {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Fill all fields correctly.']);
                public_portal_redirect_back('/');
            }
            $emailSafe = mysqli_real_escape_string($db, strtolower($email));
            $nameSafe = mysqli_real_escape_string($db, $name);
            $passwordSafe = mysqli_real_escape_string($db, password_hash($password, PASSWORD_DEFAULT));
            $exists = $FRMWRK->DBRecords("SELECT id FROM public_users WHERE email = '{$emailSafe}' LIMIT 1");
            if (!empty($exists)) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Email already registered.']);
                public_portal_redirect_back('/');
            }
            mysqli_query(
                $db,
                "INSERT INTO public_users (email, password_hash, display_name, role_code, is_active, created_at, updated_at)
                 VALUES ('{$emailSafe}', '{$passwordSafe}', '{$nameSafe}', 'member', 1, NOW(), NOW())"
            );
            public_portal_session_boot();
            $_SESSION['public_user_id'] = (int)mysqli_insert_id($db);
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Account created.']);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_login') {
            $emailSafe = mysqli_real_escape_string($db, strtolower(trim((string)($_POST['email'] ?? ''))));
            $password = (string)($_POST['password'] ?? '');
            $rows = $FRMWRK->DBRecords(
                "SELECT id, password_hash, is_active
                 FROM public_users
                 WHERE email = '{$emailSafe}'
                 LIMIT 1"
            );
            $row = (!empty($rows[0]) && is_array($rows[0])) ? $rows[0] : null;
            if (!$row || (int)($row['is_active'] ?? 0) !== 1 || !password_verify($password, (string)($row['password_hash'] ?? ''))) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Invalid credentials.']);
                public_portal_redirect_back('/');
            }
            public_portal_session_boot();
            $_SESSION['public_user_id'] = (int)$row['id'];
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Signed in.']);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_logout') {
            public_portal_session_boot();
            unset($_SESSION['public_user_id']);
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Signed out.']);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_comment') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Sign in to post comments.']);
                public_portal_redirect_back('/');
            }
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'article'), 'article');
            $contentId = max(1, (int)($_POST['content_id'] ?? 0));
            $parentId = max(0, (int)($_POST['parent_id'] ?? 0));
            $sectionCode = public_portal_slugify((string)($_POST['section_code'] ?? 'discussion'), 'discussion');
            $body = trim((string)($_POST['body_markdown'] ?? ''));
            if ($body === '' || mb_strlen($body, 'UTF-8') < 4) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Comment is too short.']);
                public_portal_redirect_back('/');
            }
            $allowedSections = public_portal_comment_sections(public_portal_lang());
            if (!isset($allowedSections[$sectionCode])) {
                $sectionCode = 'discussion';
            }
            $typeSafe = mysqli_real_escape_string($db, $contentType);
            $sectionSafe = mysqli_real_escape_string($db, $sectionCode);
            $bodySafe = mysqli_real_escape_string($db, $body);
            $htmlSafe = mysqli_real_escape_string($db, public_portal_markdown_to_html($body));
            mysqli_query(
                $db,
                "INSERT INTO public_comments (content_type, content_id, user_id, parent_id, section_code, body_markdown, body_html, is_deleted, created_at, updated_at)
                 VALUES ('{$typeSafe}', {$contentId}, " . (int)$user['id'] . ", " . ($parentId > 0 ? $parentId : 'NULL') . ", '{$sectionSafe}', '{$bodySafe}', '{$htmlSafe}', 0, NOW(), NOW())"
            );
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Comment published.']);
            public_portal_redirect_back('/');
        }
    }
}

if (!function_exists('public_portal_record_view')) {
    function public_portal_record_view($FRMWRK, string $contentType, int $contentId): int
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_views_ensure_schema($db)) {
            return 0;
        }
        public_portal_session_boot();
        $sessionKey = session_id();
        if ($sessionKey === '') {
            $sessionKey = bin2hex(random_bytes(12));
        }
        $typeSafe = mysqli_real_escape_string($db, public_portal_slugify($contentType, 'article'));
        $sessionSafe = mysqli_real_escape_string($db, $sessionKey);
        $ipHashSafe = mysqli_real_escape_string($db, hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'na')));
        mysqli_query(
            $db,
            "INSERT IGNORE INTO public_content_views (content_type, content_id, session_key, view_date, ip_hash, created_at)
             VALUES ('{$typeSafe}', {$contentId}, '{$sessionSafe}', CURDATE(), '{$ipHashSafe}', NOW())"
        );
        $rows = $FRMWRK->DBRecords(
            "SELECT COUNT(*) AS total
             FROM public_content_views
             WHERE content_type = '{$typeSafe}'
               AND content_id = {$contentId}"
        );
        return (int)($rows[0]['total'] ?? 0);
    }
}

if (!function_exists('public_portal_fetch_comments')) {
    function public_portal_fetch_comments($FRMWRK, string $contentType, int $contentId): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_comments_ensure_schema($db)) {
            return [];
        }
        $typeSafe = mysqli_real_escape_string($db, public_portal_slugify($contentType, 'article'));
        $rows = $FRMWRK->DBRecords(
            "SELECT c.id, c.parent_id, c.section_code, c.body_markdown, c.body_html, c.created_at,
                    u.display_name, u.email
             FROM public_comments c
             INNER JOIN public_users u ON u.id = c.user_id
             WHERE c.content_type = '{$typeSafe}'
               AND c.content_id = {$contentId}
               AND c.is_deleted = 0
             ORDER BY c.created_at ASC, c.id ASC"
        );
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['children'] = [];
            $items[(int)$row['id']] = $row;
        }
        $tree = [];
        foreach ($items as $id => $row) {
            $parentId = (int)($row['parent_id'] ?? 0);
            if ($parentId > 0 && isset($items[$parentId])) {
                $items[$parentId]['children'][] = &$items[$id];
            } else {
                $tree[] = &$items[$id];
            }
        }
        return array_values($tree);
    }
}

if (!function_exists('public_portal_comment_count')) {
    function public_portal_comment_count(array $tree): int
    {
        $count = 0;
        foreach ($tree as $row) {
            $count++;
            $count += public_portal_comment_count((array)($row['children'] ?? []));
        }
        return $count;
    }
}

if (!function_exists('public_portal_seed_solutions')) {
    function public_portal_seed_solutions(mysqli $db, string $host, string $lang): void
    {
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, $lang);
        $exists = mysqli_query($db, "SELECT id FROM public_solutions WHERE lang_code = '{$langSafe}' AND (domain_host = '' OR domain_host = '{$hostSafe}') LIMIT 1");
        if ($exists && mysqli_num_rows($exists) > 0) {
            return;
        }
        $isRu = ($lang === 'ru');
        $rows = [
            ['download', $isRu ? 'Пакет антидетект-проверок для ленда' : 'Landing anti-detect validation kit', 'landing-antidetect-validation-kit', 'tracking', $isRu ? '<p>Чек-лист, JS-хуки и шаблон логирования для проверки антидетект-браузеров, прокси и подмены окружения.</p>' : '<p>Checklist, JS hooks and logging template for anti-detect browsers, proxy signals and environment spoofing.</p>', $isRu ? '<p>Готовый комплект для команды медиабаинга: карта полей, события на submit/click, сегментация по источникам и шаблон выгрузки для аналитика.</p>' : '<p>Ready-to-use kit for media buying teams: field map, submit/click events, source segmentation and analyst export template.</p>', 'JS, tracker, BI', $isRu ? 'Быстрый старт' : 'Quick start', 'ZIP / MD / JS'],
            ['download', $isRu ? 'Шаблон KPI-доски арбитражной команды' : 'Affiliate team KPI dashboard template', 'affiliate-team-kpi-dashboard-template', 'analytics', $isRu ? '<p>Структура дашборда для связки байеров, тимлида и аналитика: ROI, EPC, hold, апрув и контроль по GEO/офферам.</p>' : '<p>Dashboard structure for buyers, team leads and analysts: ROI, EPC, hold, approval rate and GEO/offer control.</p>', $isRu ? '<p>Содержит блоки оперативного контроля, недельной динамики и тревог по связкам, которые выгорают или масштабируются слишком агрессивно.</p>' : '<p>Contains operations, weekly dynamics and alert blocks for combinations that are burning out or scaling too aggressively.</p>', 'Looker Studio, Sheets, SQL', $isRu ? 'Средний' : 'Intermediate', 'XLSX / SQL'],
            ['article', $isRu ? 'Как строить SEO-хаб под арбитраж трафика без каннибализации' : 'How to build an SEO hub for affiliate media without cannibalization', 'seo-hub-for-affiliate-media-without-cannibalization', 'seo', $isRu ? '<p>Архитектура хаба: словарь, статьи, кейсы, сервисы и готовые решения так, чтобы каждый кластер вел к другому этапу воронки.</p>' : '<p>Hub architecture for glossary, articles, case studies, tools and ready-made solutions so each cluster feeds the next funnel step.</p>', $isRu ? '<p>Разделяем TOFU, MOFU и BOFU на уровне URL, внутренних анкоров и CTA-блоков. Для CPALNYA это значит: глоссарий и разборы получают информационный спрос, а ready-made секция конвертирует теплую аудиторию в регистрацию и повторные визиты.</p>' : '<p>Separate TOFU, MOFU and BOFU at the URL, internal anchor and CTA-block level. For CPALNYA that means glossary and breakdowns capture informational demand while the ready-made section converts warm readers into registered repeat visitors.</p>', 'SEO, IA, content ops', $isRu ? 'Стратегия' : 'Strategy', ''],
            ['article', $isRu ? 'Каркас редакции CPA-медиа: от инсайта до готового мануала' : 'CPA editorial workflow: from insider note to publishable playbook', 'cpa-editorial-workflow-insight-to-playbook', 'workflow', $isRu ? '<p>Редакционная модель для портала, который публикует не поверхностные обзоры, а рабочие связки, фреймворки и техничку с контролем качества.</p>' : '<p>Editorial model for a portal that publishes working setups, frameworks and technical guides rather than shallow overviews.</p>', $isRu ? '<p>Контент проходит четыре фильтра: достоверность, применимость, коммерческий потенциал и способность породить новые материалы. Так формируется сеть статей, решений и комментариев, а не одиночные публикации.</p>' : '<p>Content passes four filters: credibility, usability, commercial upside and ability to spawn follow-up assets. That produces a network of articles, solutions and discussions instead of isolated posts.</p>', 'Content ops, editorial', $isRu ? 'Стратегия' : 'Strategy', ''],
        ];
        foreach ($rows as $index => $row) {
            mysqli_query(
                $db,
                "INSERT INTO public_solutions
                (domain_host, lang_code, solution_type, title, slug, symbolic_code, category_code, excerpt_html, content_html, stack_summary, difficulty_summary, file_format, download_url, repo_url, demo_url, sort_order, is_published, created_at, updated_at)
                 VALUES
                ('{$hostSafe}', '{$langSafe}', '" . mysqli_real_escape_string($db, $row[0]) . "', '" . mysqli_real_escape_string($db, $row[1]) . "', '" . mysqli_real_escape_string($db, $row[2]) . "', '" . mysqli_real_escape_string($db, $row[2]) . "', '" . mysqli_real_escape_string($db, $row[3]) . "', '" . mysqli_real_escape_string($db, $row[4]) . "', '" . mysqli_real_escape_string($db, $row[5]) . "', '" . mysqli_real_escape_string($db, $row[6]) . "', '" . mysqli_real_escape_string($db, $row[7]) . "', '" . mysqli_real_escape_string($db, $row[8]) . "', '', '', '', " . (($index + 1) * 10) . ", 1, NOW(), NOW())"
            );
        }
    }
}

if (!function_exists('public_portal_fetch_solutions')) {
    function public_portal_fetch_solutions($FRMWRK, string $host, string $lang, string $type = ''): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_solutions_ensure_schema($db)) {
            return [];
        }
        public_portal_seed_solutions($db, $host, $lang);
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, $lang);
        $whereType = '';
        if ($type !== '') {
            $whereType = " AND solution_type = '" . mysqli_real_escape_string($db, public_portal_slugify($type, 'download')) . "'";
        }
        return $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, solution_type, title, slug, symbolic_code, category_code, excerpt_html, content_html, stack_summary, difficulty_summary, file_format, download_url, repo_url, demo_url, sort_order, is_published, created_at, updated_at
             FROM public_solutions
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               {$whereType}
             ORDER BY (domain_host = '{$hostSafe}') DESC, sort_order ASC, id DESC"
        );
    }
}

if (!function_exists('public_portal_fetch_solution_by_code')) {
    function public_portal_fetch_solution_by_code($FRMWRK, string $host, string $lang, string $code): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_solutions_ensure_schema($db)) {
            return null;
        }
        public_portal_seed_solutions($db, $host, $lang);
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, $lang);
        $codeSafe = mysqli_real_escape_string($db, public_portal_slugify($code, 'solution'));
        $rows = $FRMWRK->DBRecords(
            "SELECT id, domain_host, lang_code, solution_type, title, slug, symbolic_code, category_code, excerpt_html, content_html, stack_summary, difficulty_summary, file_format, download_url, repo_url, demo_url, sort_order, is_published, created_at, updated_at
             FROM public_solutions
             WHERE is_published = 1
               AND lang_code = '{$langSafe}'
               AND (domain_host = '' OR domain_host = '{$hostSafe}')
               AND (symbolic_code = '{$codeSafe}' OR slug = '{$codeSafe}')
             ORDER BY (domain_host = '{$hostSafe}') DESC, id DESC
             LIMIT 1"
        );
        return (!empty($rows[0]) && is_array($rows[0])) ? $rows[0] : null;
    }
}

if (!function_exists('public_portal_seed_blog_articles')) {
    function public_portal_seed_blog_articles($FRMWRK, string $host, string $lang): void
    {
        if (!function_exists('examples_table_exists') || !function_exists('examples_table_has_lang_column') || !function_exists('examples_table_has_column')) {
            return;
        }
        $db = $FRMWRK->DB();
        if (!$db || !examples_table_exists($db)) {
            return;
        }
        $hostSafe = mysqli_real_escape_string($db, strtolower($host));
        $langSafe = mysqli_real_escape_string($db, $lang);
        $hasLang = examples_table_has_lang_column($db);
        $hasCluster = examples_table_has_column($db, 'cluster_code');
        $hasAuthor = examples_table_has_column($db, 'author_name');
        $hasSort = examples_table_has_column($db, 'sort_order');
        $hasPublishedAt = examples_table_has_column($db, 'published_at');
        $where = $hasLang
            ? "domain_host = '{$hostSafe}' AND lang_code = '{$langSafe}'"
            : "domain_host = '{$hostSafe}'";
        $exists = mysqli_query($db, "SELECT id FROM examples_articles WHERE {$where} LIMIT 1");
        if ($exists && mysqli_num_rows($exists) > 0) {
            return;
        }
        $isRu = ($lang === 'ru');
        $rows = [
            ['seo', $isRu ? 'SEO-структура CPA-портала: как разложить спрос на кластеры' : 'SEO structure for a CPA portal: how to split demand into clusters', $isRu ? '<p>Как построить ядро для арбитражного медиа: словарь, статьи, готовые решения, кейсы и продуктовые страницы без каннибализации.</p>' : '<p>How to build a semantic core for affiliate media: glossary, articles, ready-made solutions, case studies and product pages without cannibalization.</p>', $isRu ? '<p>Базовая ошибка CPA-порталов в том, что весь спрос складывают в один блоговый мешок. В CPALNYA ядро нужно делить минимум на пять слоев: словарь терминов, учебные статьи, техничка, готовые решения и BOFU-страницы под продуктовые переходы.</p><p>Такой подход позволяет строить воронки на уровне архитектуры сайта: каждая статья получает next step, каждая категория имеет свою роль в индексации и конверсии, а ready-made раздел перестает быть складом файлов и становится точкой регистрации.</p>' : '<p>The base mistake in many affiliate portals is throwing all demand into one blog bucket. CPALNYA should split the semantic core into at least five layers: glossary, educational articles, technical guides, ready-made solutions and BOFU pages for product transitions.</p><p>That approach lets you build funnels at the information architecture level: each article gets a next step, each category has a clear role in indexation and conversion, and the ready-made section becomes a registration point instead of a file dump.</p>'],
            ['tracking', $isRu ? 'Трекеры, постбеки и KPI: что должно быть в базовой аналитике арбитражника' : 'Trackers, postbacks and KPI: what belongs in baseline affiliate analytics', $isRu ? '<p>Каркас аналитики для команды арбитража: от источника и креатива до hold, апрува, ROI и повторяемости связок.</p>' : '<p>Analytics framework for an affiliate team: from source and creative to hold, approval rate, ROI and repeatable setups.</p>', $isRu ? '<p>Аналитика нужна не ради отчетов, а ради ускорения решений. Поэтому главные блоки данных строятся вокруг вопросов байера и тимлида: где связка умирает, где съедается маржа, что масштабируется, что дает ложный рост и что нужно отключать немедленно.</p><p>Внутри CPALNYA такой материал должен вести в готовый шаблон KPI-доски и в обсуждение с комментариями, где участники делятся собственной схемой разметки и triage-логикой.</p>' : '<p>Analytics is not for reporting vanity metrics. It is for faster decisions. The main data blocks should answer buyer and team-lead questions: where the setup dies, where margin is lost, what scales, what creates false growth and what must be killed immediately.</p><p>Inside CPALNYA this article should lead into a KPI dashboard template and a comment thread where members share their own markup and triage logic.</p>'],
            ['operations', $isRu ? 'Редакция affiliate-медиа: как превращать инсайты команды в публикуемые playbooks' : 'Affiliate editorial workflow: turning team insights into publishable playbooks', $isRu ? '<p>Редакционная система для CPA-портала, который публикует не рерайты, а рабочие схемы, решения и backstage-практику.</p>' : '<p>Editorial system for a CPA portal that publishes working setups, solutions and backstage practice instead of rewrites.</p>', $isRu ? '<p>Контент CPALNYA должен рождаться из полевых заметок, внутренних чатов, ревью связок и обсуждений технички. Дальше каждый инсайт проходит четыре фильтра: полезность, применимость, SEO-интент и потенциал породить новый asset или комьюнити-обсуждение.</p><p>Так выстраивается сеть материалов, где статьи, решения и комментарии подпитывают друг друга, а не живут в изоляции.</p>' : '<p>CPALNYA content should originate from field notes, internal chats, setup reviews and technical discussions. Then each insight passes four filters: usefulness, applicability, SEO intent and ability to produce a new asset or community discussion.</p><p>That creates a network where articles, assets and comments feed each other instead of living in isolation.</p>'],
        ];
        foreach ($rows as $index => $row) {
            $cluster = $row[0];
            $title = $row[1];
            $excerptHtml = $row[2];
            $contentHtml = $row[3];
            $slug = public_portal_slugify($title, 'article');
            $columns = ['domain_host'];
            $values = ["'{$hostSafe}'"];
            if ($hasLang) {
                $columns[] = 'lang_code';
                $values[] = "'{$langSafe}'";
            }
            $columns[] = 'title';
            $values[] = "'" . mysqli_real_escape_string($db, $title) . "'";
            $columns[] = 'slug';
            $values[] = "'" . mysqli_real_escape_string($db, $slug) . "'";
            if ($hasCluster) {
                $columns[] = 'cluster_code';
                $values[] = "'" . mysqli_real_escape_string($db, $cluster) . "'";
            }
            $columns[] = 'excerpt_html';
            $values[] = "'" . mysqli_real_escape_string($db, $excerptHtml) . "'";
            $columns[] = 'content_html';
            $values[] = "'" . mysqli_real_escape_string($db, $contentHtml) . "'";
            if ($hasAuthor) {
                $columns[] = 'author_name';
                $values[] = "'" . mysqli_real_escape_string($db, 'CPALNYA Editorial') . "'";
            }
            if ($hasSort) {
                $columns[] = 'sort_order';
                $values[] = (string)(($index + 1) * 10);
            }
            $columns[] = 'is_published';
            $values[] = '1';
            if ($hasPublishedAt) {
                $columns[] = 'published_at';
                $values[] = 'NOW()';
            }
            $columns[] = 'created_at';
            $values[] = 'NOW()';
            $columns[] = 'updated_at';
            $values[] = 'NOW()';
            mysqli_query(
                $db,
                "INSERT INTO examples_articles (" . implode(', ', $columns) . ")
                 VALUES (" . implode(', ', $values) . ")"
            );
        }
    }
}
