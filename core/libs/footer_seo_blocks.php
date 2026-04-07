<?php

if (!function_exists('footer_seo_blocks_table_name')) {
    function footer_seo_blocks_table_name(): string
    {
        return 'public_footer_seo_blocks';
    }
}

if (!function_exists('footer_seo_blocks_ensure_schema')) {
    function footer_seo_blocks_ensure_schema(mysqli $db): void
    {
        $table = footer_seo_blocks_table_name();
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `domain_host` VARCHAR(190) NOT NULL DEFAULT '',
            `lang_code` VARCHAR(5) NOT NULL DEFAULT 'ru',
            `section_scope` VARCHAR(32) NOT NULL DEFAULT 'all',
            `style_variant` VARCHAR(64) NOT NULL DEFAULT 'editorial-note',
            `block_kicker` VARCHAR(190) NOT NULL DEFAULT '',
            `block_title` VARCHAR(255) NOT NULL DEFAULT '',
            `body_html` MEDIUMTEXT NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_footer_blocks_lookup` (`lang_code`, `section_scope`, `is_active`, `sort_order`),
            KEY `idx_footer_blocks_domain` (`domain_host`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        @mysqli_query($db, $sql);
    }
}

if (!function_exists('footer_seo_blocks_detect_section')) {
    function footer_seo_blocks_detect_section(string $path): string
    {
        $path = (string)parse_url($path, PHP_URL_PATH);
        if (strpos($path, '/journal/') === 0) {
            return 'journal';
        }
        if (strpos($path, '/playbooks/') === 0) {
            return 'playbooks';
        }
        if (strpos($path, '/signals/') === 0) {
            return 'signals';
        }
        if (strpos($path, '/fun/') === 0) {
            return 'fun';
        }
        if (strpos($path, '/contact/') === 0) {
            return 'contact';
        }
        return 'all';
    }
}

if (!function_exists('footer_seo_blocks_default_seed_rows')) {
    function footer_seo_blocks_default_seed_rows(): array
    {
        $themes = [
            ['section' => 'journal', 'title' => 'источники, которые дрейфуют быстрее прогнозов', 'subject' => 'источники трафика', 'detail' => 'дрейф источников и усталость старых карт рынка'],
            ['section' => 'journal', 'title' => 'фарм как скрытая дисциплина команды', 'subject' => 'фарм', 'detail' => 'ритм прогрева, trust и невидимая цена стабильности'],
            ['section' => 'playbooks', 'title' => 'handoff как место, где ломается половина системы', 'subject' => 'handoff', 'detail' => 'стык ролей, потери контекста и цена неописанных решений'],
            ['section' => 'playbooks', 'title' => 'postback как язык, на котором команда спорит с реальностью', 'subject' => 'трекеры и postback', 'detail' => 'макросы, отладка и восстановление сигнала'],
            ['section' => 'signals', 'title' => 'новости, после которых меняется не заголовок, а операционка', 'subject' => 'policy и news', 'detail' => 'апдейты платформ, enforcement и последствия для команд'],
            ['section' => 'signals', 'title' => 'регуляторика, которая приходит в чат раньше, чем в план', 'subject' => 'регуляторика', 'detail' => 'российские и СНГ-изменения, влияющие на рекламу, выплаты и ходы команд'],
            ['section' => 'signals', 'title' => 'рынок, который разговаривает через биржи и крипту', 'subject' => 'биржи и криптовалюты', 'detail' => 'движение капитала, риск-аппетит и нерв рынка'],
            ['section' => 'fun', 'title' => 'внутренний фольклор affiliate-команд', 'subject' => 'командная культура', 'detail' => 'ночные смены, ритуалы запуска и маленькие суеверия операционки'],
            ['section' => 'fun', 'title' => 'драма модерации как жанр', 'subject' => 'модерация', 'detail' => 'повторяющиеся сцены, где у команды уже давно есть любимые реплики'],
            ['section' => 'journal', 'title' => 'редакция, которая смотрит на рынок изнутри механики', 'subject' => 'редакционный взгляд', 'detail' => 'не фасад ниши, а ее backstage и нервная система'],
        ];

        $frames = [
            [
                'style' => 'editorial-note',
                'label' => 'редакционная заметка',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / редакционная заметка',
                        'title' => 'Когда ' . $theme['title'] . ' перестают быть фоном',
                        'body' => '<p>У журнала про affiliate-операционку всегда есть одна скрытая задача: не переписывать шум, а показывать, где в нем начинается практический смысл. Так в ЦПАЛЬНЕ работают и тексты про ' . $theme['subject'] . ': мы смотрим на ' . $theme['detail'] . ' как на часть редакционного ритма, а не как на случайную новость дня.</p><p>Этот слой нужен не для украшения страницы, а для длинной дистанции. Он помогает видеть не только событие, но и его шлейф: какие роли оно затронет, где команде понадобится новая дисциплина и почему вчерашняя уверенность иногда ломается тише, чем падают цифры в трекере.</p>',
                    ];
                },
            ],
            [
                'style' => 'mini-story',
                'label' => 'мини-рассказ',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / мини-рассказ',
                        'title' => 'Ночная смена, в которой снова всплыло: ' . $theme['title'],
                        'body' => '<p>Обычно это начинается не с катастрофы, а с тихой детали. Кто-то в команде замечает, что в истории про ' . $theme['subject'] . ' появилось лишнее напряжение: цифры еще держатся, но интуиция уже подсказывает, что рынок повернул. Из таких моментов и складывается настоящая редакционная хроника ниши.</p><p>ЦПАЛЬНЯ держит подобные сцены рядом с практикой не случайно. Именно в них видно, как ' . $theme['detail'] . ' превращаются из абстракции в рабочую реальность, где каждый новый текст становится не комментарием, а способом чуть точнее собрать систему из очередной порции хаоса.</p>',
                    ];
                },
            ],
            [
                'style' => 'memo',
                'label' => 'рабочее memo',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / memo',
                        'title' => 'Memo для тех, кто работает с темой: ' . $theme['title'],
                        'body' => '<p>Если смотреть на журнал как на операционный архив, то материалы про ' . $theme['subject'] . ' нужны не только для чтения, но и для калибровки решений. Мы собираем их так, чтобы из текста можно было вынести не только ощущение момента, но и рабочую логику: где накапливается риск, кто в команде почувствует его первым и как не пропустить ранний сигнал.</p><p>Именно поэтому даже SEO-блок здесь работает как часть редакционной среды. Он напоминает, что ' . $theme['detail'] . ' редко живут в изоляции: они тянут за собой маршрутизацию, коммуникацию, handoff, креативные циклы и всю ту невидимую механику, на которой держится ежедневная affiliate-операционка.</p>',
                    ];
                },
            ],
            [
                'style' => 'allegory',
                'label' => 'аллюзия',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / аллюзия',
                        'title' => 'Иногда рынок выглядит как сцена, где ' . $theme['title'],
                        'body' => '<p>Есть темы, которые в affiliate-среде похожи на смену света в большом цехе: никто не объявляет тревогу, но все постепенно видят детали по-другому. Так работают истории про ' . $theme['subject'] . ' в ЦПАЛЬНЕ. Они нужны, чтобы зафиксировать тот момент, когда прежняя карта перестает быть точной, а новая еще только собирается в редакционных заметках и рабочих наблюдениях.</p><p>Из такого материала вырастает не просто настроение номера, а способ ориентироваться. Потому что ' . $theme['detail'] . ' важны не сами по себе, а как часть общей сцены, где команда каждый день заново соотносит риск, скорость, дисциплину и способность удерживать контроль, когда шум снаружи становится громче.</p>',
                    ];
                },
            ],
            [
                'style' => 'field-note',
                'label' => 'полевая запись',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / полевая запись',
                        'title' => 'Полевая запись о том, как ощущаются ' . $theme['title'],
                        'body' => '<p>Журналу про арбитраж мало просто перечислять темы. Важно фиксировать их на уровне рабочей фактуры: как они звучат в разговорах, как меняют порядок действий и почему команда начинает перестраивать рутину еще до того, как это решение можно будет красиво объяснить на ретроспективе. Так в ЦПАЛЬНЕ устроены тексты про ' . $theme['subject'] . '.</p><p>Здесь редакционный тон нужен не ради декоративности, а ради глубины наблюдения. Через него ' . $theme['detail'] . ' оказываются не фоном, а материалом для более точных, спокойных и взрослых решений, которые переживают и шум ленты, и очередной перегрев рынка.</p>',
                    ];
                },
            ],
        ];

        $rows = [];
        $sort = 1000;
        foreach ($themes as $theme) {
            foreach ($frames as $frame) {
                $rendered = $frame['render']($theme);
                $rows[] = [
                    'domain_host' => 'cpalnya.ru',
                    'lang_code' => 'ru',
                    'section_scope' => (($sort % 2) === 0) ? 'all' : $theme['section'],
                    'style_variant' => $frame['style'],
                    'block_kicker' => $rendered['kicker'],
                    'block_title' => $rendered['title'],
                    'body_html' => $rendered['body'],
                    'is_active' => 1,
                    'sort_order' => $sort,
                ];
                $sort--;
            }
        }

        return array_slice($rows, 0, 50);
    }
}

if (!function_exists('footer_seo_blocks_seed_defaults')) {
    function footer_seo_blocks_seed_defaults(mysqli $db): void
    {
        footer_seo_blocks_ensure_schema($db);
        $table = footer_seo_blocks_table_name();
        $res = @mysqli_query($db, "SELECT COUNT(*) AS cnt FROM `{$table}`");
        $count = 0;
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $count = (int)($row['cnt'] ?? 0);
        }
        if ($count > 0) {
            return;
        }

        foreach (footer_seo_blocks_default_seed_rows() as $item) {
            $domainHost = mysqli_real_escape_string($db, (string)($item['domain_host'] ?? ''));
            $langCode = mysqli_real_escape_string($db, (string)($item['lang_code'] ?? 'ru'));
            $sectionScope = mysqli_real_escape_string($db, (string)($item['section_scope'] ?? 'all'));
            $styleVariant = mysqli_real_escape_string($db, (string)($item['style_variant'] ?? 'editorial-note'));
            $blockKicker = mysqli_real_escape_string($db, (string)($item['block_kicker'] ?? ''));
            $blockTitle = mysqli_real_escape_string($db, (string)($item['block_title'] ?? ''));
            $bodyHtml = mysqli_real_escape_string($db, (string)($item['body_html'] ?? ''));
            $isActive = !empty($item['is_active']) ? 1 : 0;
            $sortOrder = (int)($item['sort_order'] ?? 0);
            @mysqli_query(
                $db,
                "INSERT INTO `{$table}`
                    (`domain_host`, `lang_code`, `section_scope`, `style_variant`, `block_kicker`, `block_title`, `body_html`, `is_active`, `sort_order`, `created_at`, `updated_at`)
                 VALUES
                    ('{$domainHost}', '{$langCode}', '{$sectionScope}', '{$styleVariant}', '{$blockKicker}', '{$blockTitle}', '{$bodyHtml}', {$isActive}, {$sortOrder}, NOW(), NOW())"
            );
        }
    }
}

if (!function_exists('footer_seo_blocks_fetch_random')) {
    function footer_seo_blocks_fetch_random(?mysqli $db, string $host, string $langCode, string $sectionScope = 'all'): ?array
    {
        if (!$db instanceof mysqli) {
            return null;
        }
        footer_seo_blocks_ensure_schema($db);
        footer_seo_blocks_seed_defaults($db);

        $table = footer_seo_blocks_table_name();
        $host = strtolower(trim($host));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        $langCode = trim($langCode) !== '' ? trim($langCode) : 'ru';
        $sectionScope = trim($sectionScope) !== '' ? trim($sectionScope) : 'all';

        $hostSafe = mysqli_real_escape_string($db, $host);
        $langSafe = mysqli_real_escape_string($db, $langCode);
        $sectionSafe = mysqli_real_escape_string($db, $sectionScope);
        $sectionSql = '';
        if (!in_array($sectionScope, ['*', 'any'], true)) {
            $sectionSql = " AND (`section_scope` = 'all' OR `section_scope` = '{$sectionSafe}')";
        }
        $rows = [];
        $sql = "SELECT *
                FROM `{$table}`
                WHERE `is_active` = 1
                  AND `lang_code` = '{$langSafe}'
                  AND (`domain_host` = '' OR `domain_host` = '{$hostSafe}')
                  {$sectionSql}
                ORDER BY `sort_order` DESC, `id` DESC";
        $res = @mysqli_query($db, $sql);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $rows[] = $row;
            }
        }
        if (empty($rows)) {
            return null;
        }
        return $rows[array_rand($rows)];
    }
}

if (!function_exists('footer_seo_blocks_render_html')) {
    function footer_seo_blocks_render_html(?array $block): string
    {
        if (!is_array($block)) {
            return '';
        }
        $style = trim((string)($block['style_variant'] ?? 'editorial-note'));
        $kicker = trim((string)($block['block_kicker'] ?? ''));
        $title = trim((string)($block['block_title'] ?? ''));
        $bodyHtml = (string)($block['body_html'] ?? '');

        if ($style === '') {
            $style = 'editorial-note';
        }

        ob_start();
        ?>
        <section class="public-footer-seo-block public-footer-seo-block--<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($kicker !== ''): ?>
                <span class="public-footer-seo-kicker"><?= htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if ($title !== ''): ?>
                <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
            <?php endif; ?>
            <div class="public-footer-seo-body"><?= $bodyHtml ?></div>
        </section>
        <?php
        return (string)ob_get_clean();
    }
}

if (!function_exists('footer_seo_blocks_handle_dynamic_request')) {
    function footer_seo_blocks_handle_dynamic_request(?mysqli $db): bool
    {
        if (!isset($_GET['footer_seo_block'])) {
            return false;
        }
        if (!$db instanceof mysqli) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'error' => 'db_unavailable'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return true;
        }

        $host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        $langCode = (bool)preg_match('/\.ru$/', $host) ? 'ru' : 'en';
        $block = footer_seo_blocks_fetch_random($db, $host, $langCode, 'any');
        $html = footer_seo_blocks_render_html($block);

        if (!headers_sent()) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }

        $format = strtolower(trim((string)($_GET['format'] ?? 'html')));
        if ($format === 'json') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'ok' => $html !== '',
                'html' => $html,
                'style' => (string)($block['style_variant'] ?? ''),
                'title' => (string)($block['block_title'] ?? ''),
                'kicker' => (string)($block['block_kicker'] ?? ''),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return true;
        }

        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        return true;
    }
}
