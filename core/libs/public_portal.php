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
        $lifetime = 7 * 24 * 60 * 60;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (function_exists('session_cache_limiter')) {
                @session_cache_limiter('');
            }
            if (!headers_sent()) {
                @ini_set('session.gc_maxlifetime', (string)$lifetime);
                $params = session_get_cookie_params();
                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path' => (string)($params['path'] ?? '/'),
                    'domain' => (string)($params['domain'] ?? ''),
                    'secure' => (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            session_start();
        }
        if (!headers_sent() && session_id() !== '') {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), [
                'expires' => time() + $lifetime,
                'path' => (string)($params['path'] ?? '/'),
                'domain' => (string)($params['domain'] ?? ''),
                'secure' => (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
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

if (!function_exists('public_portal_column_exists')) {
    function public_portal_column_exists(mysqli $db, string $table, string $column): bool
    {
        $tableSafe = mysqli_real_escape_string($db, $table);
        $columnSafe = mysqli_real_escape_string($db, $column);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = '{$tableSafe}'
               AND COLUMN_NAME = '{$columnSafe}'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_portal_index_exists')) {
    function public_portal_index_exists(mysqli $db, string $table, string $index): bool
    {
        $tableSafe = mysqli_real_escape_string($db, $table);
        $indexSafe = mysqli_real_escape_string($db, $index);
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = '{$tableSafe}'
               AND INDEX_NAME = '{$indexSafe}'
             LIMIT 1"
        );
        return $res ? (mysqli_num_rows($res) > 0) : false;
    }
}

if (!function_exists('public_portal_ensure_column')) {
    function public_portal_ensure_column(mysqli $db, string $table, string $column, string $definition): bool
    {
        if (public_portal_column_exists($db, $table, $column)) {
            return true;
        }
        return (bool)mysqli_query($db, "ALTER TABLE `{$table}` ADD COLUMN {$definition}");
    }
}

if (!function_exists('public_portal_ensure_index')) {
    function public_portal_ensure_index(mysqli $db, string $table, string $indexName, string $definition): bool
    {
        if (public_portal_index_exists($db, $table, $indexName)) {
            return true;
        }
        return (bool)mysqli_query($db, "ALTER TABLE `{$table}` ADD {$definition}");
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

if (!function_exists('public_portal_username_normalize')) {
    function public_portal_username_normalize(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        $raw = preg_replace('/[^A-Za-z0-9_\-.]+/', '-', $raw);
        $raw = trim((string)$raw, '-._');
        return substr((string)$raw, 0, 64);
    }
}

if (!function_exists('public_portal_generate_pin')) {
    function public_portal_generate_pin(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('public_portal_avatar_svg')) {
    function public_portal_avatar_svg(string $seed, string $label = ''): string
    {
        $seed = trim($seed) !== '' ? trim($seed) : 'member';
        $hash = md5($seed);
        $colors = ['#73b8ff', '#27dfc0', '#ff9a5f', '#f4d56b', '#b38cff', '#ff6d8a'];
        $primary = $colors[hexdec(substr($hash, 0, 2)) % count($colors)];
        $secondary = $colors[hexdec(substr($hash, 2, 2)) % count($colors)];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160" fill="none">'
            . '<defs><linearGradient id="g" x1="18" y1="18" x2="142" y2="142" gradientUnits="userSpaceOnUse">'
            . '<stop stop-color="' . htmlspecialchars($primary, ENT_QUOTES, 'UTF-8') . '"/>'
            . '<stop offset="1" stop-color="' . htmlspecialchars($secondary, ENT_QUOTES, 'UTF-8') . '"/>'
            . '</linearGradient></defs>'
            . '<rect width="160" height="160" rx="22" fill="#081120"/>'
            . '<rect x="10" y="10" width="140" height="140" rx="18" fill="url(#g)" opacity=".95"/>'
            . '<circle cx="120" cy="38" r="18" fill="white" opacity=".12"/>'
            . '<circle cx="80" cy="66" r="24" fill="white" opacity=".24"/>'
            . '<path d="M38 124c12-18 26-28 42-31 16-3 30-2 42 4 10 5 18 13 24 27" fill="white" fill-opacity=".18"/>'
            . '<path d="M42 124c12-16 25-24 40-27 15-3 28-2 39 4 10 5 17 12 21 23" stroke="white" stroke-opacity=".18" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}

if (!function_exists('public_portal_user_avatar')) {
    function public_portal_user_avatar(array $user): string
    {
        $avatarUrl = trim((string)($user['avatar_url'] ?? ''));
        if ($avatarUrl !== '') {
            return $avatarUrl;
        }
        $name = trim((string)($user['display_name'] ?? $user['username'] ?? 'Member'));
        $seedParts = [
            trim((string)($user['username'] ?? '')),
            trim((string)($user['email'] ?? '')),
            trim((string)($user['pin_code'] ?? '')),
            trim((string)($user['created_at'] ?? '')),
            (string)($user['id'] ?? ''),
        ];
        $seed = trim(implode('|', array_filter($seedParts, static function ($value): bool {
            return $value !== '';
        })));
        if ($seed === '') {
            $seed = $name;
        }
        return public_portal_avatar_svg($seed, $name);
    }
}

if (!function_exists('public_portal_captcha_build')) {
    function public_portal_captcha_build(): array
    {
        public_portal_session_boot();
        $left = random_int(2, 9);
        $right = random_int(1, 8);
        $glyphs = ['▲', '●', '■', '◆', '✦', '✳'];
        $challenge = [
            'left' => $left,
            'right' => $right,
            'glyph_left' => $glyphs[array_rand($glyphs)],
            'glyph_right' => $glyphs[array_rand($glyphs)],
            'prompt_ru' => 'Сложите два числа рядом со знаками',
            'prompt_en' => 'Add the two numbers next to the symbols',
            'answer' => (string)($left + $right),
        ];
        $_SESSION['public_portal_captcha'] = $challenge;
        return $challenge;
    }
}

if (!function_exists('public_portal_captcha_get')) {
    function public_portal_captcha_get(): array
    {
        public_portal_session_boot();
        $captcha = $_SESSION['public_portal_captcha'] ?? null;
        if (!is_array($captcha) || empty($captcha['answer'])) {
            $captcha = public_portal_captcha_build();
        }
        return $captcha;
    }
}

if (!function_exists('public_portal_captcha_check')) {
    function public_portal_captcha_check(string $answer): bool
    {
        $captcha = public_portal_captcha_get();
        $expected = trim((string)($captcha['answer'] ?? ''));
        $given = trim($answer);
        $ok = ($expected !== '' && hash_equals($expected, $given));
        public_portal_captcha_build();
        return $ok;
    }
}

if (!function_exists('public_portal_users_ensure_schema')) {
    function public_portal_users_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_users (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(64) NOT NULL DEFAULT '',
                email VARCHAR(190) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(120) NOT NULL DEFAULT '',
                pin_code VARCHAR(16) NOT NULL DEFAULT '',
                avatar_mode VARCHAR(16) NOT NULL DEFAULT 'generated',
                avatar_url VARCHAR(255) NOT NULL DEFAULT '',
                telegram_handle VARCHAR(120) NOT NULL DEFAULT '',
                website_url VARCHAR(255) NOT NULL DEFAULT '',
                about_text TEXT NULL,
                nickname_changed_at DATETIME NULL DEFAULT NULL,
                comment_rating INT NOT NULL DEFAULT 0,
                comment_votes_up INT NOT NULL DEFAULT 0,
                comment_votes_down INT NOT NULL DEFAULT 0,
                comments_count INT NOT NULL DEFAULT 0,
                role_code VARCHAR(32) NOT NULL DEFAULT 'member',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                is_banned TINYINT(1) NOT NULL DEFAULT 0,
                banned_reason VARCHAR(255) NOT NULL DEFAULT '',
                last_login_at DATETIME NULL DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_users_username (username),
                UNIQUE KEY uniq_public_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        public_portal_ensure_column($db, 'public_users', 'username', "username VARCHAR(64) NOT NULL DEFAULT '' AFTER id");
        public_portal_ensure_column($db, 'public_users', 'pin_code', "pin_code VARCHAR(16) NOT NULL DEFAULT '' AFTER display_name");
        public_portal_ensure_column($db, 'public_users', 'avatar_mode', "avatar_mode VARCHAR(16) NOT NULL DEFAULT 'generated' AFTER pin_code");
        public_portal_ensure_column($db, 'public_users', 'avatar_url', "avatar_url VARCHAR(255) NOT NULL DEFAULT '' AFTER avatar_mode");
        public_portal_ensure_column($db, 'public_users', 'telegram_handle', "telegram_handle VARCHAR(120) NOT NULL DEFAULT '' AFTER avatar_url");
        public_portal_ensure_column($db, 'public_users', 'website_url', "website_url VARCHAR(255) NOT NULL DEFAULT '' AFTER telegram_handle");
        public_portal_ensure_column($db, 'public_users', 'about_text', "about_text TEXT NULL AFTER website_url");
        public_portal_ensure_column($db, 'public_users', 'nickname_changed_at', "nickname_changed_at DATETIME NULL DEFAULT NULL AFTER about_text");
        public_portal_ensure_column($db, 'public_users', 'comment_rating', "comment_rating INT NOT NULL DEFAULT 0 AFTER nickname_changed_at");
        public_portal_ensure_column($db, 'public_users', 'comment_votes_up', "comment_votes_up INT NOT NULL DEFAULT 0 AFTER comment_rating");
        public_portal_ensure_column($db, 'public_users', 'comment_votes_down', "comment_votes_down INT NOT NULL DEFAULT 0 AFTER comment_votes_up");
        public_portal_ensure_column($db, 'public_users', 'comments_count', "comments_count INT NOT NULL DEFAULT 0 AFTER comment_votes_down");
        public_portal_ensure_column($db, 'public_users', 'is_banned', "is_banned TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
        public_portal_ensure_column($db, 'public_users', 'banned_reason', "banned_reason VARCHAR(255) NOT NULL DEFAULT '' AFTER is_banned");
        public_portal_ensure_column($db, 'public_users', 'last_login_at', "last_login_at DATETIME NULL DEFAULT NULL AFTER banned_reason");
        if (public_portal_column_exists($db, 'public_users', 'username')) {
            mysqli_query(
                $db,
                "UPDATE public_users
                 SET username = CONCAT('member', id)
                 WHERE username = '' OR username IS NULL"
            );
        }
        public_portal_ensure_index($db, 'public_users', 'uniq_public_users_username', 'UNIQUE KEY uniq_public_users_username (username)');
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
                rating_score INT NOT NULL DEFAULT 0,
                votes_up INT NOT NULL DEFAULT 0,
                votes_down INT NOT NULL DEFAULT 0,
                is_deleted TINYINT(1) NOT NULL DEFAULT 0,
                is_hidden TINYINT(1) NOT NULL DEFAULT 0,
                edited_by_admin_id BIGINT UNSIGNED NULL DEFAULT NULL,
                edited_at DATETIME NULL DEFAULT NULL,
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
        public_portal_ensure_column($db, 'public_comments', 'rating_score', "rating_score INT NOT NULL DEFAULT 0 AFTER body_html");
        public_portal_ensure_column($db, 'public_comments', 'votes_up', "votes_up INT NOT NULL DEFAULT 0 AFTER rating_score");
        public_portal_ensure_column($db, 'public_comments', 'votes_down', "votes_down INT NOT NULL DEFAULT 0 AFTER votes_up");
        public_portal_ensure_column($db, 'public_comments', 'is_hidden', "is_hidden TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted");
        public_portal_ensure_column($db, 'public_comments', 'edited_by_admin_id', "edited_by_admin_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER is_hidden");
        public_portal_ensure_column($db, 'public_comments', 'edited_at', "edited_at DATETIME NULL DEFAULT NULL AFTER edited_by_admin_id");
        public_portal_ensure_index($db, 'public_comments', 'idx_public_comments_visible', 'KEY idx_public_comments_visible (content_type, content_id, is_deleted, is_hidden, created_at)');
        return public_portal_table_exists($db, 'public_comments');
    }
}

if (!function_exists('public_portal_comment_votes_ensure_schema')) {
    function public_portal_comment_votes_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_comment_votes (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                comment_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                vote_value SMALLINT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_comment_votes_pair (comment_id, user_id),
                KEY idx_public_comment_votes_user (user_id),
                KEY idx_public_comment_votes_comment (comment_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_portal_table_exists($db, 'public_comment_votes');
    }
}

if (!function_exists('public_portal_favorites_ensure_schema')) {
    function public_portal_favorites_ensure_schema(mysqli $db): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS public_user_favorites (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                content_type VARCHAR(40) NOT NULL DEFAULT 'examples',
                content_id BIGINT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_public_user_favorites_pair (user_id, content_type, content_id),
                KEY idx_public_user_favorites_user (user_id, created_at),
                KEY idx_public_user_favorites_content (content_type, content_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        if (!mysqli_query($db, $sql)) {
            return false;
        }
        return public_portal_table_exists($db, 'public_user_favorites');
    }
}

if (!function_exists('public_portal_profile_url')) {
    function public_portal_profile_url(array $user): string
    {
        $username = trim((string)($user['username'] ?? ''));
        if ($username === '') {
            return '/account/';
        }
        return '/member/' . rawurlencode($username) . '/';
    }
}

if (!function_exists('public_portal_is_placeholder_email')) {
    function public_portal_is_placeholder_email(string $email): bool
    {
        $email = strtolower(trim($email));
        return $email === '' || substr($email, -13) === '@portal.local';
    }
}

if (!function_exists('public_portal_public_contact_value')) {
    function public_portal_public_contact_value(?string $value, string $type = 'text'): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        if ($type === 'email' && public_portal_is_placeholder_email($value)) {
            return '';
        }
        return $value;
    }
}

if (!function_exists('public_portal_article_url')) {
    function public_portal_article_url(array $article): string
    {
        $section = trim((string)($article['material_section'] ?? 'journal'));
        if (!in_array($section, ['journal', 'playbooks', 'signals', 'fun'], true)) {
            $section = 'journal';
        }
        $slug = trim((string)($article['slug'] ?? ''));
        $cluster = trim((string)($article['cluster_code'] ?? ''));
        if ($slug === '') {
            return '/' . $section . '/';
        }
        if ($cluster !== '') {
            return '/' . $section . '/' . rawurlencode($cluster) . '/' . rawurlencode($slug) . '/';
        }
        return '/' . $section . '/' . rawurlencode($slug) . '/';
    }
}

if (!function_exists('public_portal_rank_meta')) {
    function public_portal_rank_meta(int $rating, string $lang = 'en'): array
    {
        $levels = ($lang === 'ru')
            ? [
                ['min' => 1000, 'code' => 'legend', 'label' => 'Легенда редакции'],
                ['min' => 500, 'code' => 'master', 'label' => 'Магистр разбора'],
                ['min' => 200, 'code' => 'archivist', 'label' => 'Архивариус практики'],
                ['min' => 100, 'code' => 'practitioner', 'label' => 'Практик сигнала'],
                ['min' => 50, 'code' => 'navigator', 'label' => 'Навигатор темы'],
                ['min' => 10, 'code' => 'scout', 'label' => 'Разведчик комментариев'],
                ['min' => 0, 'code' => 'member', 'label' => 'Участник обсуждения'],
            ]
            : [
                ['min' => 1000, 'code' => 'legend', 'label' => 'Editorial legend'],
                ['min' => 500, 'code' => 'master', 'label' => 'Breakdown master'],
                ['min' => 200, 'code' => 'archivist', 'label' => 'Practice archivist'],
                ['min' => 100, 'code' => 'practitioner', 'label' => 'Signal practitioner'],
                ['min' => 50, 'code' => 'navigator', 'label' => 'Topic navigator'],
                ['min' => 10, 'code' => 'scout', 'label' => 'Comment scout'],
                ['min' => 0, 'code' => 'member', 'label' => 'Discussion member'],
            ];
        $current = $levels[count($levels) - 1];
        $next = null;
        foreach ($levels as $index => $level) {
            if ($rating >= (int)$level['min']) {
                $current = $level;
                if ($index > 0) {
                    $next = $levels[$index - 1];
                }
                break;
            }
        }
        return [
            'rating' => $rating,
            'code' => (string)$current['code'],
            'label' => (string)$current['label'],
            'next' => $next,
            'to_next' => $next ? max(0, (int)$next['min'] - $rating) : 0,
        ];
    }
}

if (!function_exists('public_portal_recalculate_comment_stats')) {
    function public_portal_recalculate_comment_stats(mysqli $db, int $commentId): void
    {
        if ($commentId <= 0 || !public_portal_comments_ensure_schema($db) || !public_portal_comment_votes_ensure_schema($db)) {
            return;
        }
        mysqli_query(
            $db,
            "UPDATE public_comments c
             LEFT JOIN (
                 SELECT comment_id,
                        COALESCE(SUM(vote_value), 0) AS rating_score,
                        SUM(CASE WHEN vote_value > 0 THEN 1 ELSE 0 END) AS votes_up,
                        SUM(CASE WHEN vote_value < 0 THEN 1 ELSE 0 END) AS votes_down
                 FROM public_comment_votes
                 WHERE comment_id = {$commentId}
                 GROUP BY comment_id
             ) v ON v.comment_id = c.id
             SET c.rating_score = COALESCE(v.rating_score, 0),
                 c.votes_up = COALESCE(v.votes_up, 0),
                 c.votes_down = COALESCE(v.votes_down, 0),
                 c.updated_at = NOW()
             WHERE c.id = {$commentId}
             LIMIT 1"
        );
    }
}

if (!function_exists('public_portal_recalculate_user_rating')) {
    function public_portal_recalculate_user_rating(mysqli $db, int $userId): void
    {
        if ($userId <= 0 || !public_portal_users_ensure_schema($db) || !public_portal_comments_ensure_schema($db)) {
            return;
        }
        mysqli_query(
            $db,
            "UPDATE public_users u
             LEFT JOIN (
                 SELECT c.user_id,
                        COUNT(*) AS comments_count,
                        COALESCE(SUM(c.rating_score), 0) AS comment_rating,
                        COALESCE(SUM(c.votes_up), 0) AS votes_up,
                        COALESCE(SUM(c.votes_down), 0) AS votes_down
                 FROM public_comments c
                 WHERE c.user_id = {$userId}
                   AND c.is_deleted = 0
                   AND c.is_hidden = 0
                 GROUP BY c.user_id
             ) s ON s.user_id = u.id
             SET u.comments_count = COALESCE(s.comments_count, 0),
                 u.comment_rating = COALESCE(s.comment_rating, 0),
                 u.comment_votes_up = COALESCE(s.votes_up, 0),
                 u.comment_votes_down = COALESCE(s.votes_down, 0),
                 u.updated_at = NOW()
             WHERE u.id = {$userId}
             LIMIT 1"
        );
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
        $cacheKey = 'public_portal_current_user_cache';
        if (array_key_exists($cacheKey, $GLOBALS)) {
            return is_array($GLOBALS[$cacheKey]) ? $GLOBALS[$cacheKey] : null;
        }
        public_portal_session_boot();
        $userId = (int)($_SESSION['public_user_id'] ?? 0);
        if ($userId <= 0) {
            $GLOBALS[$cacheKey] = null;
            return null;
        }
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_users_ensure_schema($db)) {
            $GLOBALS[$cacheKey] = null;
            return null;
        }
        $aboutTextSelect = public_portal_column_exists($db, 'public_users', 'about_text')
            ? 'about_text'
            : "'' AS about_text";
        $rows = $FRMWRK->DBRecords(
            "SELECT id, username, email, display_name, pin_code, avatar_mode, avatar_url,
                    telegram_handle, website_url, {$aboutTextSelect}, nickname_changed_at, comment_rating, comment_votes_up,
                    comment_votes_down, comments_count, role_code, is_active,
                    is_banned, banned_reason, last_login_at, created_at
             FROM public_users
             WHERE id = {$userId}
               AND is_active = 1
               AND is_banned = 0
             LIMIT 1"
        );
        if (!empty($rows[0]) && is_array($rows[0])) {
            $user = $rows[0];
            public_portal_recalculate_user_rating($db, (int)$user['id']);
            $refresh = $FRMWRK->DBRecords(
                "SELECT id, username, email, display_name, pin_code, avatar_mode, avatar_url,
                        telegram_handle, website_url, {$aboutTextSelect}, nickname_changed_at, comment_rating, comment_votes_up,
                        comment_votes_down, comments_count, role_code, is_active,
                        is_banned, banned_reason, last_login_at, created_at
                 FROM public_users
                 WHERE id = {$userId}
                   AND is_active = 1
                   AND is_banned = 0
                 LIMIT 1"
            );
            if (!empty($refresh[0]) && is_array($refresh[0])) {
                $user = $refresh[0];
            }
            $GLOBALS[$cacheKey] = $user;
            return $user;
        }
        unset($_SESSION['public_user_id']);
        $GLOBALS[$cacheKey] = null;
        return null;
    }
}

if (!function_exists('public_portal_set_current_user')) {
    function public_portal_set_current_user(int $userId): void
    {
        public_portal_session_boot();
        if ($userId > 0 && function_exists('session_regenerate_id')) {
            @session_regenerate_id(true);
        }
        $_SESSION['public_user_id'] = $userId;
        unset($GLOBALS['public_portal_current_user_cache']);
    }
}

if (!function_exists('public_portal_find_user_by_login')) {
    function public_portal_find_user_by_login($FRMWRK, string $login): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_users_ensure_schema($db)) {
            return null;
        }
        $login = trim($login);
        if ($login === '') {
            return null;
        }
        $loginSafe = mysqli_real_escape_string($db, strtolower($login));
        $rows = $FRMWRK->DBRecords(
            "SELECT *
             FROM public_users
             WHERE LOWER(username) = '{$loginSafe}'
                OR LOWER(email) = '{$loginSafe}'
             LIMIT 1"
        );
        return (!empty($rows[0]) && is_array($rows[0])) ? $rows[0] : null;
    }
}

if (!function_exists('public_portal_fetch_user_by_id')) {
    function public_portal_fetch_user_by_id($FRMWRK, int $userId): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_users_ensure_schema($db) || $userId <= 0) {
            return null;
        }
        $rows = $FRMWRK->DBRecords("SELECT * FROM public_users WHERE id = {$userId} LIMIT 1");
        return (!empty($rows[0]) && is_array($rows[0])) ? $rows[0] : null;
    }
}

if (!function_exists('public_portal_fetch_user_by_username')) {
    function public_portal_fetch_user_by_username($FRMWRK, string $username): ?array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_users_ensure_schema($db)) {
            return null;
        }
        $username = public_portal_username_normalize($username);
        if ($username === '') {
            return null;
        }
        $usernameSafe = mysqli_real_escape_string($db, strtolower($username));
        $rows = $FRMWRK->DBRecords(
            "SELECT *
             FROM public_users
             WHERE LOWER(username) = '{$usernameSafe}'
               AND is_active = 1
               AND is_banned = 0
             LIMIT 1"
        );
        return (!empty($rows[0]) && is_array($rows[0])) ? $rows[0] : null;
    }
}

if (!function_exists('public_portal_fetch_public_profile')) {
    function public_portal_fetch_public_profile($FRMWRK, string $username, string $lang = 'en'): ?array
    {
        $user = public_portal_fetch_user_by_username($FRMWRK, $username);
        if (!$user) {
            return null;
        }
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_comments_ensure_schema($db)) {
            return null;
        }
        $userId = (int)($user['id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }
        public_portal_recalculate_user_rating($db, $userId);
        $user = public_portal_fetch_user_by_id($FRMWRK, $userId) ?: $user;
        $recentComments = [];
        if (public_portal_table_exists($db, 'examples_articles')) {
            $recentComments = (array)$FRMWRK->DBRecords(
                "SELECT c.id, c.body_html, c.rating_score, c.votes_up, c.votes_down, c.created_at,
                        a.title AS article_title, a.slug AS article_slug, a.material_section, a.cluster_code
                 FROM public_comments c
                 LEFT JOIN examples_articles a ON a.id = c.content_id
                 WHERE c.user_id = {$userId}
                   AND c.content_type = 'examples'
                   AND c.is_deleted = 0
                   AND c.is_hidden = 0
                 ORDER BY c.created_at DESC, c.id DESC
                 LIMIT 12"
            );
        }
        foreach ($recentComments as &$comment) {
            if (!is_array($comment)) {
                continue;
            }
            $comment['article_url'] = public_portal_article_url((array)$comment);
        }
        unset($comment);

        $favorites = function_exists('public_portal_fetch_user_favorites')
            ? public_portal_fetch_user_favorites($FRMWRK, $userId, 8)
            : [];

        $rank = public_portal_rank_meta((int)($user['comment_rating'] ?? 0), $lang);
        $user['avatar_src'] = public_portal_user_avatar($user);
        $user['profile_url'] = public_portal_profile_url($user);
        $user['rank_meta'] = $rank;
        $user['recent_comments'] = $recentComments;
        $user['email_public'] = public_portal_public_contact_value((string)($user['email'] ?? ''), 'email');
        $user['telegram_public'] = public_portal_public_contact_value((string)($user['telegram_handle'] ?? ''));
        $user['website_public'] = public_portal_public_contact_value((string)($user['website_url'] ?? ''));
        $user['about_text_public'] = trim((string)($user['about_text'] ?? ''));
        $user['favorites'] = $favorites;
        return $user;
    }
}

if (!function_exists('public_portal_fetch_latest_comments')) {
    function public_portal_fetch_latest_comments($FRMWRK, string $host, string $lang = 'en', int $limit = 4): array
    {
        $db = $FRMWRK->DB();
        if (
            !$db
            || $limit <= 0
            || !public_portal_table_exists($db, 'public_comments')
            || !public_portal_table_exists($db, 'public_users')
            || !public_portal_table_exists($db, 'examples_articles')
        ) {
            return [];
        }

        $lang = in_array($lang, ['ru', 'en'], true) ? $lang : 'en';
        $host = strtolower(trim($host));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }

        $hasLang = function_exists('examples_table_has_lang_column') ? examples_table_has_lang_column($db) : false;
        $hasHost = function_exists('examples_table_has_column') ? examples_table_has_column($db, 'domain_host') : false;
        $langWhere = $hasLang ? " AND a.lang_code = '" . mysqli_real_escape_string($db, $lang) . "'" : '';
        $hostWhere = ($hasHost && $host !== '') ? " AND (a.domain_host = '' OR a.domain_host = '" . mysqli_real_escape_string($db, $host) . "')" : '';
        $limit = max(1, min(12, $limit));

        $rows = $FRMWRK->DBRecords(
            "SELECT c.id, c.body_html, c.created_at, c.rating_score,
                    u.username, u.display_name, u.email, u.pin_code, u.created_at AS user_created_at, u.id AS user_id,
                    a.id AS article_id, a.title AS article_title, a.slug, a.cluster_code, a.material_section
             FROM public_comments c
             INNER JOIN public_users u ON u.id = c.user_id
             INNER JOIN examples_articles a ON a.id = c.content_id
             WHERE c.content_type = 'examples'
               AND c.is_deleted = 0
               AND c.is_hidden = 0
               AND u.is_active = 1
               AND u.is_banned = 0
               {$langWhere}
               {$hostWhere}
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT {$limit}"
        );

        foreach ($rows as &$row) {
            if (!is_array($row)) {
                continue;
            }
            $row['article_url'] = public_portal_article_url((array)$row) . '#comment-' . (int)($row['id'] ?? 0);
            $row['profile_url'] = public_portal_profile_url((array)$row);
            $row['avatar_src'] = public_portal_user_avatar((array)$row);
        }
        unset($row);

        return array_values($rows);
    }
}

if (!function_exists('public_portal_user_has_favorite')) {
    function public_portal_user_has_favorite($FRMWRK, int $userId, string $contentType, int $contentId): bool
    {
        $db = $FRMWRK->DB();
        if (!$db || $userId <= 0 || $contentId <= 0 || !public_portal_table_exists($db, 'public_user_favorites')) {
            return false;
        }
        $typeSafe = mysqli_real_escape_string($db, public_portal_slugify($contentType, 'examples'));
        $rows = $FRMWRK->DBRecords(
            "SELECT id
             FROM public_user_favorites
             WHERE user_id = {$userId}
               AND content_type = '{$typeSafe}'
               AND content_id = {$contentId}
             LIMIT 1"
        );
        return !empty($rows[0]);
    }
}

if (!function_exists('public_portal_toggle_favorite')) {
    function public_portal_toggle_favorite($FRMWRK, int $userId, string $contentType, int $contentId): bool
    {
        $db = $FRMWRK->DB();
        if (!$db || $userId <= 0 || $contentId <= 0 || !public_portal_favorites_ensure_schema($db)) {
            return false;
        }
        $typeSafe = mysqli_real_escape_string($db, public_portal_slugify($contentType, 'examples'));
        $exists = $FRMWRK->DBRecords(
            "SELECT id
             FROM public_user_favorites
             WHERE user_id = {$userId}
               AND content_type = '{$typeSafe}'
               AND content_id = {$contentId}
             LIMIT 1"
        );
        if (!empty($exists[0]['id'])) {
            mysqli_query($db, "DELETE FROM public_user_favorites WHERE id = " . (int)$exists[0]['id'] . " LIMIT 1");
            return false;
        }
        mysqli_query(
            $db,
            "INSERT IGNORE INTO public_user_favorites (user_id, content_type, content_id, created_at)
             VALUES ({$userId}, '{$typeSafe}', {$contentId}, NOW())"
        );
        return true;
    }
}

if (!function_exists('public_portal_fetch_user_favorites')) {
    function public_portal_fetch_user_favorites($FRMWRK, int $userId, int $limit = 12): array
    {
        $db = $FRMWRK->DB();
        if (
            !$db
            || $userId <= 0
            || !public_portal_table_exists($db, 'public_user_favorites')
            || !public_portal_table_exists($db, 'examples_articles')
        ) {
            return [];
        }
        $limit = max(1, min(50, $limit));
        $rows = (array)$FRMWRK->DBRecords(
            "SELECT f.content_id, f.created_at AS saved_at,
                    a.id, a.title, a.slug, a.cluster_code, a.material_section,
                    a.preview_image_thumb_url, a.preview_image_url, a.preview_image_data, a.created_at
             FROM public_user_favorites f
             INNER JOIN examples_articles a ON a.id = f.content_id
             WHERE f.user_id = {$userId}
               AND f.content_type = 'examples'
               AND COALESCE(a.is_published, 1) = 1
             ORDER BY f.created_at DESC, f.id DESC
             LIMIT {$limit}"
        );
        foreach ($rows as &$row) {
            if (!is_array($row)) {
                continue;
            }
            $row['article_url'] = public_portal_article_url($row);
            $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
            $full = trim((string)($row['preview_image_url'] ?? ''));
            $base = trim((string)($row['preview_image_data'] ?? ''));
            $row['image_src'] = $thumb !== '' ? $thumb : ($full !== '' ? $full : $base);
        }
        unset($row);
        return $rows;
    }
}

if (!function_exists('public_portal_avatar_upload_dir')) {
    function public_portal_avatar_upload_dir(): array
    {
        $ym = date('Y/m');
        $root = rtrim((string)DIR, '/\\');
        $candidates = [
            [
                'dir' => $root . '/uploads/public_avatars/' . $ym . '/',
                'public' => '/uploads/public_avatars/' . $ym . '/',
            ],
            [
                'dir' => $root . '/cache/public_avatars/' . $ym . '/',
                'public' => '/cache/public_avatars/' . $ym . '/',
            ],
        ];
        foreach ($candidates as $candidate) {
            if (is_dir((string)($candidate['dir'] ?? ''))) {
                return $candidate;
            }
        }
        return $candidates[0];
    }
}

if (!function_exists('public_portal_store_avatar_upload')) {
    function public_portal_store_avatar_upload(array $file, string $seed = 'avatar', ?string &$error = null): string
    {
        $error = null;
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = 'Не удалось получить файл аватарки.';
            return '';
        }
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            $error = 'Аватарка должна быть изображением до 5 МБ.';
            return '';
        }
        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp)) {
            $error = 'Временный файл аватарки не найден.';
            return '';
        }
        $info = @getimagesize($tmp);
        if (!$info) {
            $error = 'Аватарка должна быть изображением JPG, PNG, GIF или WebP.';
            return '';
        }
        $width = (int)($info[0] ?? 0);
        $height = (int)($info[1] ?? 0);
        if ($width <= 0 || $height <= 0 || $width > 4096 || $height > 4096) {
            $error = 'Аватарка слишком большая. Максимум 4096px по стороне.';
            return '';
        }
        $mime = (string)($info['mime'] ?? '');
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (!isset($extMap[$mime])) {
            $error = 'Аватарка должна быть изображением JPG, PNG, GIF или WebP.';
            return '';
        }
        $paths = public_portal_avatar_upload_dir();
        if (false && !is_dir($paths['dir']) && !@mkdir($paths['dir'], 0775, true) && !is_dir($paths['dir'])) {
            $error = 'Не удалось создать папку для аватарок.';
            return '';
        }
        if (!is_dir($paths['dir']) && !@mkdir($paths['dir'], 0775, true) && !is_dir($paths['dir'])) {
            $fallbackDir = rtrim((string)DIR, '/\\') . '/cache/public_avatars/' . date('Y/m') . '/';
            $fallbackPublic = '/cache/public_avatars/' . date('Y/m') . '/';
            if ($fallbackDir !== $paths['dir'] && (is_dir($fallbackDir) || @mkdir($fallbackDir, 0775, true) || is_dir($fallbackDir))) {
                $paths = ['dir' => $fallbackDir, 'public' => $fallbackPublic];
            } else {
                $error = 'РќРµ СѓРґР°Р»РѕСЃСЊ СЃРѕР·РґР°С‚СЊ РїР°РїРєСѓ РґР»СЏ Р°РІР°С‚Р°СЂРѕРє.';
                return '';
            }
        }
        $name = public_portal_slugify($seed, 'avatar') . '-' . date('YmdHis') . '-' . random_int(1000, 9999) . '.' . $extMap[$mime];
        $target = $paths['dir'] . $name;
        $moved = @move_uploaded_file($tmp, $target);
        if (!$moved) {
            $moved = @copy($tmp, $target);
        }
        if (!$moved) {
            $error = 'Не удалось сохранить файл аватарки.';
            return '';
        }
        return $paths['public'] . $name;
    }
}

if (!function_exists('public_portal_format_inline')) {
    function public_portal_format_inline(string $raw): string
    {
        $safe = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
        $patterns = [
            '/\*\*(.+?)\*\*/su' => '<strong>$1</strong>',
            '/\*(.+?)\*/su' => '<em>$1</em>',
            '/~~(.+?)~~/su' => '<s>$1</s>',
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

if (!function_exists('public_portal_return_path')) {
    function public_portal_return_path(string $fallback = '/'): string
    {
        $returnPath = trim((string)($_POST['return_path'] ?? $_SERVER['REQUEST_URI'] ?? $fallback));
        if ($returnPath === '' || $returnPath[0] !== '/') {
            $returnPath = $fallback;
        }
        return $returnPath;
    }
}

if (!function_exists('public_portal_is_ajax_request')) {
    function public_portal_is_ajax_request(): bool
    {
        $xhr = strtolower(trim((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')));
        if ($xhr === 'xmlhttprequest') {
            return true;
        }
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        return strpos($accept, 'application/json') !== false;
    }
}

if (!function_exists('public_portal_comments_view_data')) {
    function public_portal_comments_view_data($FRMWRK, string $contentType, int $contentId, array $flash = []): array
    {
        return [
            'portal_user' => public_portal_current_user($FRMWRK),
            'portal_flash' => $flash,
            'portal_captcha' => public_portal_captcha_challenge(),
            'portal_comments' => ($contentId > 0) ? public_portal_fetch_comments($FRMWRK, $contentType, $contentId) : [],
            'portal_comment_total' => ($contentId > 0) ? public_portal_comment_total_for_content($FRMWRK, $contentType, $contentId) : 0,
            'portal_content_type' => $contentType,
            'portal_content_id' => $contentId,
            'lang' => public_portal_lang(),
        ];
    }
}

if (!function_exists('public_portal_render_comments_block')) {
    function public_portal_render_comments_block($FRMWRK, string $contentType, int $contentId, array $flash = []): string
    {
        $partial = rtrim((string)DIR, '/\\') . '/core/views/partials/article_comments.php';
        if (!is_file($partial)) {
            return '';
        }
        $ModelPage = public_portal_comments_view_data($FRMWRK, $contentType, $contentId, $flash);
        $lang = (string)($ModelPage['lang'] ?? public_portal_lang());
        ob_start();
        include $partial;
        return (string)ob_get_clean();
    }
}

if (!function_exists('public_portal_respond')) {
    function public_portal_respond($FRMWRK, array $flash, string $fallback = '/', string $contentType = '', int $contentId = 0, array $extra = []): void
    {
        if (public_portal_is_ajax_request()) {
            $payload = [
                'ok' => (($flash['type'] ?? 'ok') !== 'error'),
                'flash' => $flash,
                'redirect_url' => public_portal_return_path($fallback),
            ];
            if ($contentType !== '' && $contentId > 0) {
                $payload['html'] = public_portal_render_comments_block($FRMWRK, $contentType, $contentId, $flash);
                $payload['content_type'] = $contentType;
                $payload['content_id'] = $contentId;
                $payload['comment_total'] = public_portal_comment_total_for_content($FRMWRK, $contentType, $contentId);
            }
            foreach ($extra as $key => $value) {
                $payload[$key] = $value;
            }
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        public_portal_flash_set('portal', $flash);
        public_portal_redirect_back($fallback);
    }
}

if (!function_exists('public_portal_handle_ajax_action')) {
    function public_portal_handle_ajax_action($FRMWRK, mysqli $db, string $action): void
    {
        if (!public_portal_is_ajax_request()) {
            return;
        }

        if ($action === 'public_portal_register' || $action === 'public_portal_login') {
            $existingUser = public_portal_current_user($FRMWRK);
            if ($existingUser) {
                $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'examples'), 'examples');
                $contentId = (int)($_POST['content_id'] ?? 0);
                public_portal_respond($FRMWRK, ['type' => 'ok', 'message' => 'Вы уже вошли в аккаунт.'], '/', $contentType, $contentId);
            }
        }

        if ($action === 'public_portal_register') {
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'examples'), 'examples');
            $contentId = (int)($_POST['content_id'] ?? 0);
            $emailInput = trim((string)($_POST['email'] ?? ''));
            $username = public_portal_username_normalize((string)($_POST['username'] ?? $_POST['display_name'] ?? ''));
            if ($username === '' && $emailInput !== '' && strpos($emailInput, '@') !== false) {
                $username = public_portal_username_normalize((string)substr($emailInput, 0, strpos($emailInput, '@')));
            }
            $displayName = trim((string)($_POST['display_name'] ?? $username));
            $password = (string)($_POST['password'] ?? '');
            $captchaAnswer = (string)($_POST['captcha_answer'] ?? '');

            if ($username === '' || (function_exists('mb_strlen') ? mb_strlen($username, 'UTF-8') : strlen($username)) < 3 || (function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password)) < 8) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РЈРєР°Р¶РёС‚Рµ Р»РѕРіРёРЅ РЅРµ РєРѕСЂРѕС‡Рµ 3 СЃРёРјРІРѕР»РѕРІ Рё РїР°СЂРѕР»СЊ РЅРµ РєРѕСЂРѕС‡Рµ 8 СЃРёРјРІРѕР»РѕРІ.'], '/', $contentType, $contentId);
            }
            if ($emailInput === '' && !public_portal_captcha_check($captchaAnswer)) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РљР°РїС‡Р° РЅРµ СЃРѕРІРїР°Р»Р°. РџРѕРїСЂРѕР±СѓР№С‚Рµ РµС‰Рµ СЂР°Р·.'], '/', $contentType, $contentId);
            }
            if ($emailInput !== '' && !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Email Р·Р°РїРѕР»РЅРµРЅ РЅРµРєРѕСЂСЂРµРєС‚РЅРѕ.'], '/', $contentType, $contentId);
            }

            $usernameSafe = mysqli_real_escape_string($db, $username);
            $displayName = $displayName !== '' ? $displayName : $username;
            $displayNameSafe = mysqli_real_escape_string($db, $displayName);
            $emailValue = $emailInput !== '' ? strtolower($emailInput) : ($username . '+' . date('YmdHis') . '@portal.local');
            $emailSafe = mysqli_real_escape_string($db, $emailValue);

            $exists = $FRMWRK->DBRecords("SELECT id FROM public_users WHERE username = '{$usernameSafe}' LIMIT 1");
            if (!empty($exists)) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Р­С‚РѕС‚ Р»РѕРіРёРЅ СѓР¶Рµ Р·Р°РЅСЏС‚.'], '/', $contentType, $contentId);
            }
            $emailExists = $FRMWRK->DBRecords("SELECT id FROM public_users WHERE email = '{$emailSafe}' LIMIT 1");
            if (!empty($emailExists) && $emailInput !== '') {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Р­С‚РѕС‚ email СѓР¶Рµ РёСЃРїРѕР»СЊР·СѓРµС‚СЃСЏ.'], '/', $contentType, $contentId);
            }

            $passwordSafe = mysqli_real_escape_string($db, password_hash($password, PASSWORD_DEFAULT));
            $pinCode = public_portal_generate_pin();
            $pinSafe = mysqli_real_escape_string($db, $pinCode);
            mysqli_query(
                $db,
                "INSERT INTO public_users (username, email, password_hash, display_name, pin_code, avatar_mode, avatar_url, role_code, is_active, is_banned, created_at, updated_at)
                 VALUES ('{$usernameSafe}', '{$emailSafe}', '{$passwordSafe}', '{$displayNameSafe}', '{$pinSafe}', 'generated', '', 'member', 1, 0, NOW(), NOW())"
            );
            $newUserId = (int)mysqli_insert_id($db);
            public_portal_set_current_user($newUserId);
            public_portal_respond($FRMWRK, [
                'type' => 'ok',
                'message' => 'РђРєРєР°СѓРЅС‚ СЃРѕР·РґР°РЅ. РЎРѕС…СЂР°РЅРёС‚Рµ PIN: ' . $pinCode,
                'pin_code' => $pinCode,
            ], '/', $contentType, $contentId, ['pin_code' => $pinCode]);
        }

        if ($action === 'public_portal_login') {
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'examples'), 'examples');
            $contentId = (int)($_POST['content_id'] ?? 0);
            $login = trim((string)($_POST['login'] ?? $_POST['email'] ?? $_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $row = public_portal_find_user_by_login($FRMWRK, $login);
            if (!$row || (int)($row['is_active'] ?? 0) !== 1 || (int)($row['is_banned'] ?? 0) === 1 || !password_verify($password, (string)($row['password_hash'] ?? ''))) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РќРµРІРµСЂРЅС‹Р№ Р»РѕРіРёРЅ РёР»Рё РїР°СЂРѕР»СЊ.'], '/', $contentType, $contentId);
            }
            mysqli_query($db, "UPDATE public_users SET last_login_at = NOW(), updated_at = NOW() WHERE id = " . (int)$row['id'] . " LIMIT 1");
            public_portal_set_current_user((int)$row['id']);
            public_portal_respond($FRMWRK, ['type' => 'ok', 'message' => 'Р’С‹ РІРѕС€Р»Рё РІ Р°РєРєР°СѓРЅС‚.'], '/', $contentType, $contentId);
        }

        if ($action === 'public_portal_favorite_toggle') {
            $user = public_portal_current_user($FRMWRK);
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'examples'), 'examples');
            $contentId = max(1, (int)($_POST['content_id'] ?? 0));
            if (!$user) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Р’РѕР№РґРёС‚Рµ, С‡С‚РѕР±С‹ СЃРѕС…СЂР°РЅСЏС‚СЊ РјР°С‚РµСЂРёР°Р»С‹.'], '/', $contentType, $contentId);
            }
            $saved = public_portal_toggle_favorite($FRMWRK, (int)$user['id'], $contentType, $contentId);
            public_portal_respond($FRMWRK, [
                'type' => 'ok',
                'message' => $saved ? 'РњР°С‚РµСЂРёР°Р» РґРѕР±Р°РІР»РµРЅ РІ РёР·Р±СЂР°РЅРЅРѕРµ.' : 'РњР°С‚РµСЂРёР°Р» СѓР±СЂР°РЅ РёР· РёР·Р±СЂР°РЅРЅРѕРіРѕ.',
            ], '/', '', 0, [
                'saved' => $saved,
                'content_type' => $contentType,
                'content_id' => $contentId,
            ]);
        }

        if ($action === 'public_portal_comment') {
            $user = public_portal_current_user($FRMWRK);
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'article'), 'article');
            $contentId = max(1, (int)($_POST['content_id'] ?? 0));
            if (!$user) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Р’РѕР№РґРёС‚Рµ, С‡С‚РѕР±С‹ РєРѕРјРјРµРЅС‚РёСЂРѕРІР°С‚СЊ.'], '/', $contentType, $contentId);
            }
            $parentId = max(0, (int)($_POST['parent_id'] ?? 0));
            $sectionCode = public_portal_slugify((string)($_POST['section_code'] ?? 'discussion'), 'discussion');
            $body = trim((string)($_POST['body_markdown'] ?? ''));
            if ($body === '' || (function_exists('mb_strlen') ? mb_strlen($body, 'UTF-8') : strlen($body)) < 4) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РљРѕРјРјРµРЅС‚Р°СЂРёР№ СЃР»РёС€РєРѕРј РєРѕСЂРѕС‚РєРёР№.'], '/', $contentType, $contentId);
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
                "INSERT INTO public_comments (content_type, content_id, user_id, parent_id, section_code, body_markdown, body_html, is_deleted, is_hidden, created_at, updated_at)
                 VALUES ('{$typeSafe}', {$contentId}, " . (int)$user['id'] . ", " . ($parentId > 0 ? $parentId : 'NULL') . ", '{$sectionSafe}', '{$bodySafe}', '{$htmlSafe}', 0, 0, NOW(), NOW())"
            );
            $newCommentId = (int)mysqli_insert_id($db);
            if ($newCommentId > 0) {
                public_portal_recalculate_comment_stats($db, $newCommentId);
            }
            public_portal_recalculate_user_rating($db, (int)$user['id']);
            public_portal_respond($FRMWRK, ['type' => 'ok', 'message' => 'РљРѕРјРјРµРЅС‚Р°СЂРёР№ РѕРїСѓР±Р»РёРєРѕРІР°РЅ.'], '/', $contentType, $contentId, [
                'comment_id' => $newCommentId,
                'comment_anchor' => $newCommentId > 0 ? ('#comment-' . $newCommentId) : '',
            ]);
        }

        if ($action === 'public_portal_comment_vote') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Р’РѕР№РґРёС‚Рµ, С‡С‚РѕР±С‹ РіРѕР»РѕСЃРѕРІР°С‚СЊ Р·Р° РєРѕРјРјРµРЅС‚Р°СЂРёРё.'], '/');
            }
            $commentId = max(1, (int)($_POST['comment_id'] ?? 0));
            $voteValue = (int)($_POST['vote_value'] ?? 0);
            if (!in_array($voteValue, [-1, 1], true)) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РќРµРєРѕСЂСЂРµРєС‚РЅС‹Р№ РіРѕР»РѕСЃ.'], '/');
            }
            $commentRows = $FRMWRK->DBRecords(
                "SELECT id, user_id, content_type, content_id
                 FROM public_comments
                 WHERE id = {$commentId}
                   AND is_deleted = 0
                   AND is_hidden = 0
                 LIMIT 1"
            );
            $commentRow = (!empty($commentRows[0]) && is_array($commentRows[0])) ? $commentRows[0] : null;
            if (!$commentRow) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РљРѕРјРјРµРЅС‚Р°СЂРёР№ РЅРµ РЅР°Р№РґРµРЅ.'], '/');
            }
            $authorId = (int)($commentRow['user_id'] ?? 0);
            if ($authorId === (int)$user['id']) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РќРµР»СЊР·СЏ РіРѕР»РѕСЃРѕРІР°С‚СЊ Р·Р° СЃРѕР±СЃС‚РІРµРЅРЅС‹Р№ РєРѕРјРјРµРЅС‚Р°СЂРёР№.'], '/');
            }
            $existingVoteRows = $FRMWRK->DBRecords(
                "SELECT id
                 FROM public_comment_votes
                 WHERE comment_id = {$commentId}
                   AND user_id = " . (int)$user['id'] . "
                 LIMIT 1"
            );
            if (!empty($existingVoteRows[0])) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РћС†РµРЅРєР° СѓР¶Рµ СѓС‡С‚РµРЅР°. РР·РјРµРЅРёС‚СЊ РµРµ РЅРµР»СЊР·СЏ.'], '/', public_portal_slugify((string)($commentRow['content_type'] ?? 'examples'), 'examples'), (int)($commentRow['content_id'] ?? 0), [
                    'comment_id' => $commentId,
                    'comment_anchor' => '#comment-' . $commentId,
                ]);
            }
            $voteInserted = mysqli_query(
                $db,
                "INSERT INTO public_comment_votes (comment_id, user_id, vote_value, created_at, updated_at)
                 VALUES ({$commentId}, " . (int)$user['id'] . ", {$voteValue}, NOW(), NOW())"
            );
            if (!$voteInserted) {
                public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'РќРµ СѓРґР°Р»РѕСЃСЊ СѓС‡РµСЃС‚СЊ РѕС†РµРЅРєСѓ. РџРѕРїСЂРѕР±СѓР№С‚Рµ РѕР±РЅРѕРІРёС‚СЊ СЃС‚СЂР°РЅРёС†Сѓ.'], '/', public_portal_slugify((string)($commentRow['content_type'] ?? 'examples'), 'examples'), (int)($commentRow['content_id'] ?? 0), [
                    'comment_id' => $commentId,
                    'comment_anchor' => '#comment-' . $commentId,
                ]);
            }
            public_portal_recalculate_comment_stats($db, $commentId);
            public_portal_recalculate_user_rating($db, $authorId);
            public_portal_respond($FRMWRK, ['type' => 'ok', 'message' => 'Р РµР№С‚РёРЅРі РєРѕРјРјРµРЅС‚Р°СЂРёСЏ РѕР±РЅРѕРІР»РµРЅ.'], '/', public_portal_slugify((string)($commentRow['content_type'] ?? 'examples'), 'examples'), (int)($commentRow['content_id'] ?? 0), [
                'comment_id' => $commentId,
                'comment_anchor' => '#comment-' . $commentId,
            ]);
        }
    }
}

if (!function_exists('public_portal_handle_request')) {
    function public_portal_handle_request($FRMWRK): void
    {
        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'GET') {
            if ((int)($_GET['portal_comments_block'] ?? 0) === 1) {
                $contentType = public_portal_slugify((string)($_GET['content_type'] ?? 'examples'), 'examples');
                $contentId = max(1, (int)($_GET['content_id'] ?? 0));
                $html = ($contentId > 0) ? public_portal_render_comments_block($FRMWRK, $contentType, $contentId) : '';
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                header('Content-Type: application/json; charset=UTF-8');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                echo json_encode([
                    'ok' => ($html !== ''),
                    'html' => $html,
                    'logged_in' => (bool)public_portal_current_user($FRMWRK),
                    'content_type' => $contentType,
                    'content_id' => $contentId,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            return;
        }
        $action = trim((string)($_POST['action'] ?? ''));
        if (strpos($action, 'public_portal_') !== 0) {
            return;
        }
        if (!public_portal_csrf_check(trim((string)($_POST['portal_csrf'] ?? '')), 'portal')) {
            public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Security token mismatch.'], '/');
        }
        $db = $FRMWRK->DB();
        if (!$db) {
            public_portal_respond($FRMWRK, ['type' => 'error', 'message' => 'Database unavailable.'], '/');
        }
        public_portal_users_ensure_schema($db);
        public_portal_comments_ensure_schema($db);
        public_portal_comment_votes_ensure_schema($db);
        public_portal_handle_ajax_action($FRMWRK, $db, $action);
        if ($action === 'public_portal_register') {
            $emailInput = trim((string)($_POST['email'] ?? ''));
            $username = public_portal_username_normalize((string)($_POST['username'] ?? $_POST['display_name'] ?? ''));
            if ($username === '' && $emailInput !== '' && strpos($emailInput, '@') !== false) {
                $username = public_portal_username_normalize((string)substr($emailInput, 0, strpos($emailInput, '@')));
            }
            $displayName = trim((string)($_POST['display_name'] ?? $username));
            $password = (string)($_POST['password'] ?? '');
            $captchaAnswer = (string)($_POST['captcha_answer'] ?? '');
            if ($username === '' || (function_exists('mb_strlen') ? mb_strlen($username, 'UTF-8') : strlen($username)) < 3 || (function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password)) < 8) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Укажите логин не короче 3 символов и пароль не короче 8 символов.']);
                public_portal_redirect_back('/');
            }
            $requiresCaptcha = ($emailInput === '');
            if ($requiresCaptcha && !public_portal_captcha_check($captchaAnswer)) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Капча не совпала. Попробуйте еще раз.']);
                public_portal_redirect_back('/');
            }
            $usernameSafe = mysqli_real_escape_string($db, $username);
            $displayName = $displayName !== '' ? $displayName : $username;
            $displayNameSafe = mysqli_real_escape_string($db, $displayName);
            $passwordSafe = mysqli_real_escape_string($db, password_hash($password, PASSWORD_DEFAULT));
            $pinCode = public_portal_generate_pin();
            $pinSafe = mysqli_real_escape_string($db, $pinCode);
            if ($emailInput !== '' && !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Email заполнен некорректно.']);
                public_portal_redirect_back('/');
            }
            $emailValue = $emailInput !== '' ? strtolower($emailInput) : ($username . '+' . date('YmdHis') . '@portal.local');
            $emailSafe = mysqli_real_escape_string($db, $emailValue);
            $exists = $FRMWRK->DBRecords("SELECT id FROM public_users WHERE username = '{$usernameSafe}' LIMIT 1");
            if (!empty($exists)) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Этот логин уже занят.']);
                public_portal_redirect_back('/');
            }
            $emailExists = $FRMWRK->DBRecords("SELECT id FROM public_users WHERE email = '{$emailSafe}' LIMIT 1");
            if (!empty($emailExists) && $emailInput !== '') {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Этот email уже используется.']);
                public_portal_redirect_back('/');
            }
            mysqli_query(
                $db,
                "INSERT INTO public_users (username, email, password_hash, display_name, pin_code, avatar_mode, avatar_url, role_code, is_active, is_banned, created_at, updated_at)
                 VALUES ('{$usernameSafe}', '{$emailSafe}', '{$passwordSafe}', '{$displayNameSafe}', '{$pinSafe}', 'generated', '', 'member', 1, 0, NOW(), NOW())"
            );
            $newUserId = (int)mysqli_insert_id($db);
            public_portal_set_current_user($newUserId);
            public_portal_flash_set('portal', [
                'type' => 'ok',
                'message' => 'Аккаунт создан. Сохраните PIN: ' . $pinCode,
                'pin_code' => $pinCode,
            ]);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_login') {
            $login = trim((string)($_POST['login'] ?? $_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $row = public_portal_find_user_by_login($FRMWRK, $login);
            if (!$row || (int)($row['is_active'] ?? 0) !== 1 || (int)($row['is_banned'] ?? 0) === 1 || !password_verify($password, (string)($row['password_hash'] ?? ''))) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Неверный логин или пароль.']);
                public_portal_redirect_back('/');
            }
            mysqli_query($db, "UPDATE public_users SET last_login_at = NOW(), updated_at = NOW() WHERE id = " . (int)$row['id'] . " LIMIT 1");
            public_portal_set_current_user((int)$row['id']);
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Вы вошли в аккаунт.']);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_logout') {
            public_portal_session_boot();
            unset($_SESSION['public_user_id']);
            unset($GLOBALS['public_portal_current_user_cache']);
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Вы вышли из аккаунта.']);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_profile_update') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Сначала войдите в аккаунт.']);
                public_portal_redirect_back('/account/');
            }
            $displayName = trim((string)($_POST['display_name'] ?? ''));
            $telegram = trim((string)($_POST['telegram_handle'] ?? ''));
            $website = trim((string)($_POST['website_url'] ?? ''));
            $aboutText = trim((string)($_POST['about_text'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $currentDisplayName = trim((string)($user['display_name'] ?? ''));
            $nicknameChangedAt = trim((string)($user['nickname_changed_at'] ?? ''));
            if ($displayName === '') {
                $displayName = $currentDisplayName !== '' ? $currentDisplayName : (string)($user['username'] ?? 'Member');
            }
            if ($displayName !== $currentDisplayName && $nicknameChangedAt !== '') {
                $changedTs = strtotime($nicknameChangedAt);
                if ($changedTs !== false && $changedTs > strtotime('-30 days')) {
                    public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Никнейм можно менять не чаще одного раза в 30 дней.']);
                    public_portal_redirect_back('/account/');
                }
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Email заполнен некорректно.']);
                public_portal_redirect_back('/account/');
            }
            if ($email !== '') {
                $emailCheckSafe = mysqli_real_escape_string($db, strtolower($email));
                $emailCheck = $FRMWRK->DBRecords("SELECT id FROM public_users WHERE email = '{$emailCheckSafe}' AND id <> " . (int)$user['id'] . " LIMIT 1");
                if (!empty($emailCheck)) {
                    public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Этот email уже привязан к другому аккаунту.']);
                    public_portal_redirect_back('/account/');
                }
            }
            $avatarUrl = trim((string)($user['avatar_url'] ?? ''));
            $avatarChanged = false;
            if (!empty($_FILES['avatar']['name'])) {
                $avatarError = null;
                $uploaded = public_portal_store_avatar_upload((array)$_FILES['avatar'], (string)($user['username'] ?? 'avatar'), $avatarError);
                if ($uploaded !== '') {
                    $avatarUrl = $uploaded;
                    $avatarChanged = true;
                } else {
                    public_portal_flash_set('portal', ['type' => 'error', 'message' => $avatarError ?: 'Не удалось сохранить аватарку.']);
                    public_portal_redirect_back('/account/');
                }
            }
            $displayNameSafe = mysqli_real_escape_string($db, $displayName);
            $telegramSafe = mysqli_real_escape_string($db, $telegram);
            $websiteSafe = mysqli_real_escape_string($db, $website);
            $aboutTextPrepared = function_exists('mb_substr')
                ? mb_substr($aboutText, 0, 2000, 'UTF-8')
                : substr($aboutText, 0, 2000);
            $aboutTextSafe = mysqli_real_escape_string($db, $aboutTextPrepared);
            $avatarSafe = mysqli_real_escape_string($db, $avatarUrl);
            $emailFallback = (string)($user['username'] ?? 'member') . '+' . (int)($user['id'] ?? 0) . '@portal.local';
            $emailSafe = mysqli_real_escape_string($db, $email !== '' ? strtolower($email) : $emailFallback);
            $nicknameSql = ($displayName !== $currentDisplayName) ? ", nickname_changed_at = NOW()" : '';
            $aboutTextSql = public_portal_column_exists($db, 'public_users', 'about_text')
                ? ", about_text = '{$aboutTextSafe}'"
                : '';
            mysqli_query(
                $db,
                "UPDATE public_users
                 SET display_name = '{$displayNameSafe}',
                     telegram_handle = '{$telegramSafe}',
                     website_url = '{$websiteSafe}'
                     {$aboutTextSql},
                     email = '{$emailSafe}',
                     avatar_mode = '" . ($avatarUrl !== '' ? 'upload' : 'generated') . "',
                     avatar_url = '{$avatarSafe}',
                     updated_at = NOW()
                     {$nicknameSql}
                 WHERE id = " . (int)$user['id'] . "
                 LIMIT 1"
            );
            if ($avatarChanged && function_exists('page_html_cache_purge_all')) {
                page_html_cache_purge_all();
            }
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Профиль обновлен.']);
            public_portal_redirect_back('/account/');
        }

        if ($action === 'public_portal_password_change_v2') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Сначала войдите в аккаунт.']);
                public_portal_redirect_back('/account/');
            }
            $currentPassword = (string)($_POST['current_password'] ?? '');
            $password = (string)($_POST['new_password'] ?? '');
            $userRow = public_portal_fetch_user_by_id($FRMWRK, (int)($user['id'] ?? 0));
            if (!$userRow || !password_verify($currentPassword, (string)($userRow['password_hash'] ?? ''))) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Текущий пароль неверный.']);
                public_portal_redirect_back('/account/');
            }
            if ((function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password)) < 8) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Новый пароль слишком короткий.']);
                public_portal_redirect_back('/account/');
            }
            $passwordSafe = mysqli_real_escape_string($db, password_hash($password, PASSWORD_DEFAULT));
            mysqli_query($db, "UPDATE public_users SET password_hash = '{$passwordSafe}', updated_at = NOW() WHERE id = " . (int)$user['id'] . " LIMIT 1");
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Пароль обновлен.']);
            public_portal_redirect_back('/account/');
        }

        if ($action === 'public_portal_favorite_toggle') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Войдите, чтобы сохранять материалы.']);
                public_portal_redirect_back('/');
            }
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'examples'), 'examples');
            $contentId = max(1, (int)($_POST['content_id'] ?? 0));
            if ($contentId <= 0) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Не удалось определить материал.']);
                public_portal_redirect_back('/');
            }
            $saved = public_portal_toggle_favorite($FRMWRK, (int)$user['id'], $contentType, $contentId);
            public_portal_flash_set('portal', [
                'type' => 'ok',
                'message' => $saved ? 'Материал добавлен в избранное.' : 'Материал убран из избранного.',
            ]);
            public_portal_redirect_back('/');
        }

        if ($action === 'public_portal_password_change') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Сначала войдите в аккаунт.']);
                public_portal_redirect_back('/account/');
            }
            $currentPassword = (string)($_POST['current_password'] ?? '');
            $password = (string)($_POST['new_password'] ?? '');
            $userRow = public_portal_fetch_user_by_id($FRMWRK, (int)($user['id'] ?? 0));
            if (!$userRow || !password_verify($currentPassword, (string)($userRow['password_hash'] ?? ''))) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'PIN-код не совпал.']);
                public_portal_redirect_back('/account/');
            }
            if ((function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password)) < 8) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Новый пароль слишком короткий.']);
                public_portal_redirect_back('/account/');
            }
            $passwordSafe = mysqli_real_escape_string($db, password_hash($password, PASSWORD_DEFAULT));
            mysqli_query($db, "UPDATE public_users SET password_hash = '{$passwordSafe}', updated_at = NOW() WHERE id = " . (int)$user['id'] . " LIMIT 1");
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Пароль обновлен.']);
            public_portal_redirect_back('/account/');
        }

        if ($action === 'public_portal_comment') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Войдите, чтобы комментировать.']);
                public_portal_redirect_back('/');
            }
            $contentType = public_portal_slugify((string)($_POST['content_type'] ?? 'article'), 'article');
            $contentId = max(1, (int)($_POST['content_id'] ?? 0));
            $parentId = max(0, (int)($_POST['parent_id'] ?? 0));
            $sectionCode = public_portal_slugify((string)($_POST['section_code'] ?? 'discussion'), 'discussion');
            $body = trim((string)($_POST['body_markdown'] ?? ''));
            if ($body === '' || (function_exists('mb_strlen') ? mb_strlen($body, 'UTF-8') : strlen($body)) < 4) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Комментарий слишком короткий.']);
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
                "INSERT INTO public_comments (content_type, content_id, user_id, parent_id, section_code, body_markdown, body_html, is_deleted, is_hidden, created_at, updated_at)
                 VALUES ('{$typeSafe}', {$contentId}, " . (int)$user['id'] . ", " . ($parentId > 0 ? $parentId : 'NULL') . ", '{$sectionSafe}', '{$bodySafe}', '{$htmlSafe}', 0, 0, NOW(), NOW())"
            );
            $newCommentId = (int)mysqli_insert_id($db);
            if ($newCommentId > 0) {
                public_portal_recalculate_comment_stats($db, $newCommentId);
            }
            public_portal_recalculate_user_rating($db, (int)$user['id']);
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Комментарий опубликован.']);
            public_portal_redirect_back('/');
        }
        if ($action === 'public_portal_comment_vote') {
            $user = public_portal_current_user($FRMWRK);
            if (!$user) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Войдите, чтобы голосовать за комментарии.']);
                public_portal_redirect_back('/');
            }
            $commentId = max(1, (int)($_POST['comment_id'] ?? 0));
            $voteValue = (int)($_POST['vote_value'] ?? 0);
            if (!in_array($voteValue, [-1, 1], true)) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Некорректный голос.']);
                public_portal_redirect_back('/');
            }
            $commentRows = $FRMWRK->DBRecords(
                "SELECT id, user_id
                 FROM public_comments
                 WHERE id = {$commentId}
                   AND is_deleted = 0
                   AND is_hidden = 0
                 LIMIT 1"
            );
            $commentRow = (!empty($commentRows[0]) && is_array($commentRows[0])) ? $commentRows[0] : null;
            if (!$commentRow) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Комментарий не найден.']);
                public_portal_redirect_back('/');
            }
            $authorId = (int)($commentRow['user_id'] ?? 0);
            if ($authorId === (int)$user['id']) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Нельзя голосовать за собственный комментарий.']);
                public_portal_redirect_back('/');
            }
            $existingVoteRows = $FRMWRK->DBRecords(
                "SELECT id
                 FROM public_comment_votes
                 WHERE comment_id = {$commentId}
                   AND user_id = " . (int)$user['id'] . "
                 LIMIT 1"
            );
            if (!empty($existingVoteRows[0])) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Оценка уже учтена. Изменить ее нельзя.']);
                public_portal_redirect_back('/');
            }
            $voteInserted = mysqli_query(
                $db,
                "INSERT INTO public_comment_votes (comment_id, user_id, vote_value, created_at, updated_at)
                 VALUES ({$commentId}, " . (int)$user['id'] . ", {$voteValue}, NOW(), NOW())"
            );
            if (!$voteInserted) {
                public_portal_flash_set('portal', ['type' => 'error', 'message' => 'Не удалось учесть оценку. Попробуйте обновить страницу.']);
                public_portal_redirect_back('/');
            }
            public_portal_recalculate_comment_stats($db, $commentId);
            public_portal_recalculate_user_rating($db, $authorId);
            public_portal_flash_set('portal', ['type' => 'ok', 'message' => 'Рейтинг комментария обновлен.']);
            public_portal_redirect_back('/');
        }
    }
}

if (!function_exists('public_portal_record_view')) {
    function public_portal_record_view($FRMWRK, string $contentType, int $contentId): int
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_table_exists($db, 'public_content_views')) {
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
        if (
            !$db
            || !public_portal_table_exists($db, 'public_comments')
            || !public_portal_table_exists($db, 'public_users')
            || !public_portal_table_exists($db, 'public_comment_votes')
        ) {
            return [];
        }
        $typeSafe = mysqli_real_escape_string($db, public_portal_slugify($contentType, 'article'));
        $currentUser = public_portal_current_user($FRMWRK);
        $currentUserId = (int)($currentUser['id'] ?? 0);
        $voteJoin = $currentUserId > 0
            ? "LEFT JOIN public_comment_votes cv ON cv.comment_id = c.id AND cv.user_id = {$currentUserId}"
            : "LEFT JOIN (SELECT 0 AS id, 0 AS comment_id, 0 AS vote_value) cv ON cv.comment_id = c.id";
        $rows = $FRMWRK->DBRecords(
            "SELECT c.id, c.parent_id, c.section_code, c.body_markdown, c.body_html, c.created_at, c.updated_at,
                    c.rating_score, c.votes_up, c.votes_down,
                    u.id AS user_id, u.username, u.display_name, u.email, u.avatar_url, u.avatar_mode,
                    u.comment_rating, u.comment_votes_up, u.comment_votes_down, u.comments_count,
                    cv.id AS current_user_vote_id, cv.vote_value AS current_user_vote
             FROM public_comments c
             INNER JOIN public_users u ON u.id = c.user_id
             {$voteJoin}
             WHERE c.content_type = '{$typeSafe}'
               AND c.content_id = {$contentId}
               AND c.is_deleted = 0
               AND c.is_hidden = 0
               AND u.is_active = 1
               AND u.is_banned = 0
             ORDER BY c.created_at ASC, c.id ASC"
        );
        $items = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['avatar_src'] = public_portal_user_avatar($row);
            $row['profile_url'] = public_portal_profile_url($row);
            $row['rank_meta'] = public_portal_rank_meta((int)($row['comment_rating'] ?? 0), public_portal_lang());
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

if (!function_exists('public_portal_comment_total_for_content')) {
    function public_portal_comment_total_for_content($FRMWRK, string $contentType, int $contentId): int
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_table_exists($db, 'public_comments') || !public_portal_table_exists($db, 'public_users')) {
            return 0;
        }
        $typeSafe = mysqli_real_escape_string($db, public_portal_slugify($contentType, 'article'));
        $rows = $FRMWRK->DBRecords(
            "SELECT COUNT(*) AS total
             FROM public_comments c
             INNER JOIN public_users u ON u.id = c.user_id
             WHERE c.content_type = '{$typeSafe}'
               AND c.content_id = {$contentId}
               AND c.is_deleted = 0
               AND c.is_hidden = 0
               AND u.is_active = 1
               AND u.is_banned = 0"
        );
        return (int)($rows[0]['total'] ?? 0);
    }
}

if (!function_exists('public_portal_admin_fetch_comments')) {
    function public_portal_admin_fetch_comments($FRMWRK, int $articleId = 0, int $userId = 0): array
    {
        $db = $FRMWRK->DB();
        if (!$db || !public_portal_table_exists($db, 'public_comments') || !public_portal_table_exists($db, 'public_users')) {
            return [];
        }
        $articleJoin = public_portal_table_exists($db, 'examples_articles')
            ? 'LEFT JOIN examples_articles a ON a.id = c.content_id'
            : "LEFT JOIN (SELECT 0 AS id, '' AS title, '' AS slug, '' AS material_section, '' AS cluster_code) a ON a.id = c.content_id";
        $where = ["c.content_type = 'examples'"];
        if ($articleId > 0) {
            $where[] = 'c.content_id = ' . $articleId;
        }
        if ($userId > 0) {
            $where[] = 'c.user_id = ' . $userId;
        }
        $whereSql = implode(' AND ', $where);
        return (array)$FRMWRK->DBRecords(
            "SELECT c.*, u.username, u.display_name, u.avatar_url, u.is_banned, u.banned_reason,
                    u.comment_rating, u.comment_votes_up, u.comment_votes_down, u.comments_count,
                    a.title AS article_title, a.slug AS article_slug, a.material_section, a.cluster_code
             FROM public_comments c
             INNER JOIN public_users u ON u.id = c.user_id
             {$articleJoin}
             WHERE {$whereSql}
             ORDER BY c.created_at DESC, c.id DESC"
        );
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
                $values[] = "'" . mysqli_real_escape_string($db, 'ЦПАЛЬНЯ Editorial') . "'";
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
