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
            ['section' => 'journal', 'title' => 'источники, которые дрейфуют быстрее прогнозов', 'subject' => 'источники трафика', 'detail' => 'дрейф источников, усталость старых карт рынка и необходимость заново читать сигналы'],
            ['section' => 'journal', 'title' => 'фарм как скрытая дисциплина команды', 'subject' => 'фарм', 'detail' => 'ритм прогрева, trust и невидимая цена стабильности'],
            ['section' => 'playbooks', 'title' => 'handoff как место, где ломается половина системы', 'subject' => 'handoff', 'detail' => 'стык ролей, потери контекста и цена неописанных решений'],
            ['section' => 'playbooks', 'title' => 'postback как язык, на котором команда спорит с реальностью', 'subject' => 'трекеры и postback', 'detail' => 'макросы, отладка и восстановление сигнала, когда цифры перестают совпадать с интуицией'],
            ['section' => 'signals', 'title' => 'новости, после которых меняется не заголовок, а операционка', 'subject' => 'policy и новости', 'detail' => 'апдейты платформ, enforcement и последствия для команды уже на следующей смене'],
            ['section' => 'signals', 'title' => 'регуляторика, которая приходит в чат раньше, чем в план', 'subject' => 'регуляторика', 'detail' => 'российские и СНГ-изменения, влияющие на рекламу, платежи, коммуникацию и рутину команд'],
            ['section' => 'signals', 'title' => 'рынок, который разговаривает через биржи и крипту', 'subject' => 'биржи и криптовалюты', 'detail' => 'движение капитала, нерв ликвидности и то, как это меняет аппетит к риску'],
            ['section' => 'fun', 'title' => 'внутренний фольклор affiliate-команд', 'subject' => 'командная культура', 'detail' => 'ночные смены, ритуалы запуска и маленькие суеверия операционки'],
            ['section' => 'fun', 'title' => 'драма модерации как повторяющийся жанр', 'subject' => 'модерация', 'detail' => 'сцены, где у команды уже есть любимые реплики, но выводы все равно стоят дорого'],
            ['section' => 'journal', 'title' => 'редакция, которая смотрит на рынок изнутри механики', 'subject' => 'редакционный взгляд', 'detail' => 'не фасад ниши, а ее backstage, ритм и нервная система'],
        ];

        $frames = [
            [
                'style' => 'editorial-note',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / редакционная заметка',
                        'title' => 'Когда ' . $theme['title'] . ' перестают быть фоном',
                        'body' => '<p>У журнального текста про affiliate-операционку есть одна честная обязанность: не пересказывать шум, а показывать, где именно этот шум начинает менять рабочие решения. Поэтому материалы про ' . $theme['subject'] . ' у нас строятся не как очередная заметка «по теме», а как попытка внимательно разобрать момент, когда ' . $theme['detail'] . ' уже невозможно считать внешним обстоятельством. В этот момент меняется не только тон разговоров в чатах. Меняется порядок приоритетов, меняется цена ошибки, меняется само чувство устойчивости, на котором команда держалась еще вчера.</p><p>Полезность такого блока не в том, чтобы впечатлить красивой формулировкой. Он нужен, чтобы собрать мысль в рабочий фокус: что в теме уже дрогнуло, какая роль почувствует это первой, где нужен более трезвый темп, а где, наоборот, нельзя опоздать с решением. Журнальный стиль здесь работает как инструмент точности. Через него проще увидеть, почему даже спокойная история про ' . $theme['subject'] . ' на деле всегда оказывается разговором о дисциплине, внимании к деталям и способности команды не терять форму в момент, когда рынок начинает вести себя неровно.</p>',
                    ];
                },
            ],
            [
                'style' => 'mini-story',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / мини-рассказ',
                        'title' => 'Ночная смена, в которой снова всплыло: ' . $theme['title'],
                        'body' => '<p>Обычно все начинается не с большого обвала, а с почти незаметной детали. Кто-то в ночной смене первым говорит, что история про ' . $theme['subject'] . ' звучит как-то иначе, чем неделю назад. Цифры еще не обвалились, dashboard еще держит лицо, но воздух в операционке уже меняется: чат становится осторожнее, решения формулируются суше, а простые вещи внезапно требуют лишней проверки. Именно в такие моменты тема перестает быть абстрактной. Она входит в смену, садится рядом с командой и начинает влиять на темп.</p><p>Мы любим этот журнальный формат за то, что он позволяет не терять человеческую фактуру. Через маленький сюжет легче показать, как ' . $theme['detail'] . ' превращаются из фонового знания в конкретный выбор: сейчас тормознуть или ускориться, предупредить команду заранее или дать ситуации раскрыться, перепроверить старый маршрут или признать, что старая уверенность уже не работает. Такой текст не драматизирует рынок, а помогает прожить его изменения без лишнего героизма и без опасной самоуверенности.</p>',
                    ];
                },
            ],
            [
                'style' => 'memo',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / memo',
                        'title' => 'Memo для тех, кто работает с темой: ' . $theme['title'],
                        'body' => '<p>Если смотреть на журнал как на рабочий архив, то материалы про ' . $theme['subject'] . ' нужны не ради красивого сопровождения страницы, а ради калибровки решений. Полезный memo должен делать три вещи сразу: поднимать тему из общего шума, связывать ее с реальными ролями внутри команды и возвращать разговор к вопросу «что именно теперь придется делать иначе». Поэтому здесь мы смотрим на ' . $theme['detail'] . ' не как на повод для абстрактного вывода, а как на узел, в котором сходятся скорость, риск, коммуникация и качество ежедневной дисциплины.</p><p>Художественный тон в таком тексте не для украшения, а для запоминания. Он помогает удержать мысль дольше, чем сухой список пунктов. После хорошего memo у читателя остается не только формула проблемы, но и чувство ее масштаба: где начнется первый перекос, на какой привычке команда скорее всего споткнется, какой маленький сигнал нельзя пропустить сейчас, пока он еще выглядит невинно. Если текст помогает увидеть это заранее, значит он уже работает как часть операционной пользы, а не как еще один декоративный SEO-слой.</p>',
                    ];
                },
            ],
            [
                'style' => 'allegory',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / аллюзия',
                        'title' => 'Иногда рынок выглядит как сцена, где ' . $theme['title'],
                        'body' => '<p>Есть темы, которые в affiliate-среде похожи на смену света в большом зале: никто еще не объявил тревогу, но предметы уже начинают выглядеть иначе. То, что днем казалось устойчивым, к вечеру приобретает новые тени. Именно так часто входят в рабочую жизнь сюжеты про ' . $theme['subject'] . '. Снаружи они могут выглядеть как частная новость, узкий сбой или один не слишком громкий сдвиг. Но внутри операционки это часто оказывается моментом, когда старая карта перестает быть надежной, а новая еще только собирается из коротких наблюдений, реплик и неровных цифр.</p><p>Аллюзия полезна здесь тем, что помогает увидеть общий рисунок, не теряя практический нерв. Через нее проще почувствовать, что ' . $theme['detail'] . ' важны не сами по себе, а как часть сцены, где ежедневно встречаются риск, темп, усталость, дисциплина и способность команды не распадаться на отдельные реакции. Хороший журнальный текст не размывает проблему метафорой, а наоборот, дает ей форму. После него читатель яснее понимает, где в этой красивой картинке находится реальная точка давления и какое решение стоит принять, пока сцена не сменилась окончательно.</p>',
                    ];
                },
            ],
            [
                'style' => 'field-note',
                'render' => static function (array $theme): array {
                    return [
                        'kicker' => 'ЦПАЛЬНЯ / полевая запись',
                        'title' => 'Полевая запись о том, как ощущаются ' . $theme['title'],
                        'body' => '<p>Журналу про арбитраж мало просто перечислять темы. Гораздо важнее фиксировать их на уровне рабочей фактуры: как они звучат в разговорах команды, как меняют темп решений, почему внезапно требуют дополнительных проверок и в какой момент начинают влиять на рутину сильнее, чем на настроение. Так устроены наши тексты про ' . $theme['subject'] . '. В них важна не только тема сама по себе, но и ее температура: где она уже давит на процесс, где пока только намекает на будущий перекос, а где из нее еще можно извлечь спокойное и полезное преимущество.</p><p>Полевая запись хороша тем, что в ней нет лишней витринности. Она ближе к опыту смены, чем к готовой легенде. Через такой формат ' . $theme['detail'] . ' перестают выглядеть как сторонняя аналитика и становятся материалом для более зрелого решения: что перепроверить сегодня, что перестать откладывать, где команде нужен более чистый handoff, а где просто честный разговор без красивых самоописаний. Это и есть тот тип полезного журнального текста, к которому хочется возвращаться не ради тона, а ради ясности.</p>',
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

if (!function_exists('footer_seo_blocks_refresh_defaults')) {
    function footer_seo_blocks_refresh_defaults(mysqli $db): int
    {
        footer_seo_blocks_ensure_schema($db);
        $table = footer_seo_blocks_table_name();
        @mysqli_query(
            $db,
            "DELETE FROM `{$table}`
             WHERE `domain_host` = 'cpalnya.ru'
               AND `lang_code` = 'ru'
               AND `sort_order` BETWEEN 951 AND 1000"
        );

        $inserted = 0;
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
            $ok = @mysqli_query(
                $db,
                "INSERT INTO `{$table}`
                    (`domain_host`, `lang_code`, `section_scope`, `style_variant`, `block_kicker`, `block_title`, `body_html`, `is_active`, `sort_order`, `created_at`, `updated_at`)
                 VALUES
                    ('{$domainHost}', '{$langCode}', '{$sectionScope}', '{$styleVariant}', '{$blockKicker}', '{$blockTitle}', '{$bodyHtml}', {$isActive}, {$sortOrder}, NOW(), NOW())"
            );
            if ($ok) {
                $inserted++;
            }
        }

        return $inserted;
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
