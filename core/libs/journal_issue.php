<?php

if (!function_exists('journal_issue_normalize_lang')) {
    function journal_issue_normalize_lang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        return $lang === 'ru' ? 'ru' : 'en';
    }
}

if (!function_exists('journal_issue_table_exists')) {
    function journal_issue_table_exists(mysqli $db): bool
    {
        $res = mysqli_query(
            $db,
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'journal_issue_settings'
             LIMIT 1"
        );
        return $res && mysqli_num_rows($res) > 0;
    }
}

if (!function_exists('journal_issue_ensure_table')) {
    function journal_issue_ensure_table(mysqli $db): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS journal_issue_settings (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            lang_code VARCHAR(8) NOT NULL,
            issue_kicker VARCHAR(191) NOT NULL DEFAULT '',
            issue_title VARCHAR(255) NOT NULL DEFAULT '',
            issue_subtitle TEXT NULL,
            hero_title VARCHAR(255) NOT NULL DEFAULT '',
            hero_description TEXT NULL,
            hero_note VARCHAR(255) NOT NULL DEFAULT '',
            hero_image_url TEXT NULL,
            hero_image_data LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_journal_issue_lang (lang_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        return (bool)mysqli_query($db, $sql);
    }
}

if (!function_exists('journal_issue_defaults')) {
    function journal_issue_defaults(string $lang = 'ru'): array
    {
        $lang = journal_issue_normalize_lang($lang);
        if ($lang === 'ru') {
            return [
                'lang_code' => 'ru',
                'issue_kicker' => 'Journal / ЦПАЛЬНЯ',
                'issue_title' => "Апрель '26. Backstage affiliate-операций",
                'issue_subtitle' => "Апрель '26. Выпуск о том, как сегодня устроена живая affiliate-операционка: источники трафика, медиабаинг, фарм аккаунтов, креативные связки, трекеры, модерация и операционные playbooks.\n\nНе витрина обещаний, а карта backstage-процессов для CPA-команд, которым нужен не случайный запуск, а темп, масштаб и контроль в турбулентной среде.",
                'hero_title' => 'Журнал про операционку affiliate-команд',
                'hero_description' => "Журнал про арбитраж трафика, affiliate-операции, медиабаинг, креативы, фарм аккаунтов, трекинг и модерацию. Здесь выходят редакционные разборы, market-intelligence материалы и прикладные кейсы для CPA-команд, собранные как цельный выпуск, а не как лента случайных публикаций.\n\nЭто монтажная комната ниши: здесь разбираются ритм тестов, выгорание креативов, слепые зоны трекеров, Telegram-дистрибуция, farm-рутина, модерационные качели и вся backstage-механика, которая обычно остается за кадром, но решает результат.",
                'hero_note' => 'Новый выпуск',
                'hero_image_url' => '',
                'hero_image_data' => '',
            ];
        }

        return [
            'lang_code' => 'en',
            'issue_kicker' => 'Journal / ЦПАЛЬНЯ',
            'issue_title' => 'Issue: affiliate operations backstage',
            'issue_subtitle' => 'An editorial issue about traffic, farms, creatives, trackers, bundles and reusable operating playbooks.',
            'hero_title' => 'A journal for affiliate team operations',
            'hero_description' => 'An editorial front for traffic, creative systems, farming, moderation, tracking and team workflows. The journal should feel like an issue, not a flat stream of posts.',
            'hero_note' => 'Current issue',
            'hero_image_url' => '',
            'hero_image_data' => '',
        ];
    }
}

if (!function_exists('journal_issue_get')) {
    function journal_issue_get(mysqli $db, string $lang = 'ru'): array
    {
        journal_issue_ensure_table($db);
        $lang = journal_issue_normalize_lang($lang);
        $langSafe = mysqli_real_escape_string($db, $lang);
        $rows = mysqli_query(
            $db,
            "SELECT *
             FROM journal_issue_settings
             WHERE lang_code = '{$langSafe}'
             LIMIT 1"
        );
        if ($rows && ($row = mysqli_fetch_assoc($rows))) {
            return array_merge(journal_issue_defaults($lang), $row);
        }

        return journal_issue_defaults($lang);
    }
}

if (!function_exists('journal_issue_save')) {
    function journal_issue_save(mysqli $db, string $lang, array $payload): bool
    {
        journal_issue_ensure_table($db);
        $lang = journal_issue_normalize_lang($lang);
        $current = journal_issue_get($db, $lang);
        $data = array_merge($current, $payload, ['lang_code' => $lang]);

        $issueKicker = mysqli_real_escape_string($db, trim((string)($data['issue_kicker'] ?? '')));
        $issueTitle = mysqli_real_escape_string($db, trim((string)($data['issue_title'] ?? '')));
        $issueSubtitle = mysqli_real_escape_string($db, trim((string)($data['issue_subtitle'] ?? '')));
        $heroTitle = mysqli_real_escape_string($db, trim((string)($data['hero_title'] ?? '')));
        $heroDescription = mysqli_real_escape_string($db, trim((string)($data['hero_description'] ?? '')));
        $heroNote = mysqli_real_escape_string($db, trim((string)($data['hero_note'] ?? '')));
        $heroImageUrl = mysqli_real_escape_string($db, trim((string)($data['hero_image_url'] ?? '')));
        $heroImageData = mysqli_real_escape_string($db, trim((string)($data['hero_image_data'] ?? '')));
        $langSafe = mysqli_real_escape_string($db, $lang);

        return (bool)mysqli_query(
            $db,
            "INSERT INTO journal_issue_settings
                (lang_code, issue_kicker, issue_title, issue_subtitle, hero_title, hero_description, hero_note, hero_image_url, hero_image_data, created_at, updated_at)
             VALUES
                ('{$langSafe}', '{$issueKicker}', '{$issueTitle}', '{$issueSubtitle}', '{$heroTitle}', '{$heroDescription}', '{$heroNote}', '{$heroImageUrl}', '{$heroImageData}', NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                issue_kicker = VALUES(issue_kicker),
                issue_title = VALUES(issue_title),
                issue_subtitle = VALUES(issue_subtitle),
                hero_title = VALUES(hero_title),
                hero_description = VALUES(hero_description),
                hero_note = VALUES(hero_note),
                hero_image_url = VALUES(hero_image_url),
                hero_image_data = VALUES(hero_image_data),
                updated_at = NOW()"
        );
    }
}
