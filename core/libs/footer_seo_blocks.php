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
        if (strpos($path, '/reviews/') === 0) {
            return 'reviews';
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

if (!function_exists('footer_seo_blocks_theme_library')) {
    function footer_seo_blocks_theme_library(): array
    {
        return [
            ['section' => 'journal', 'title' => 'источники, которые дрейфуют быстрее прогнозов', 'subject' => 'источники трафика', 'detail' => 'дрейф источников, усталость старых карт рынка и необходимость заново читать сигналы'],
            ['section' => 'journal', 'title' => 'фарм как скрытая дисциплина команды', 'subject' => 'фарм', 'detail' => 'ритм прогрева, trust и невидимая цена стабильности'],
            ['section' => 'journal', 'title' => 'механика связок после недели перегрева', 'subject' => 'связки', 'detail' => 'усталость рабочих комбинаций и цена слишком поздней пересборки'],
            ['section' => 'journal', 'title' => 'внутренний ритм media buying-команды', 'subject' => 'ритм команды', 'detail' => 'как темп решений влияет на качество запуска сильнее, чем красивые созвоны'],
            ['section' => 'journal', 'title' => 'карта ниши после тихих рыночных сдвигов', 'subject' => 'карта рынка', 'detail' => 'слабые сигналы, которые поздно замечают даже опытные команды'],
            ['section' => 'journal', 'title' => 'creative shelf life как нерв сезона', 'subject' => 'срок жизни креативов', 'detail' => 'момент, когда креативы умирают быстрее, чем команда успевает назвать это проблемой'],
            ['section' => 'journal', 'title' => 'Telegram как редакционная и операционная среда', 'subject' => 'Telegram', 'detail' => 'каналы, чаты и внутренние контуры дистрибуции, которые давно уже больше, чем просто источник трафика'],
            ['section' => 'journal', 'title' => 'backstage рынка, который не попадает в витрину', 'subject' => 'backstage affiliate', 'detail' => 'тихие решения, короткие обсуждения и невидимые ставки, на которых держится вся конструкция'],
            ['section' => 'journal', 'title' => 'платформенный trust как отдельный ресурс', 'subject' => 'trust', 'detail' => 'ресурс, который расходуется тише бюджета, но восстанавливается дольше'],
            ['section' => 'journal', 'title' => 'переутомление операционки под давлением новостей', 'subject' => 'операционка', 'detail' => 'состояние, в котором команде труднее отличить важный сигнал от просто громкого шума'],

            ['section' => 'playbooks', 'title' => 'handoff как место, где ломается половина системы', 'subject' => 'handoff', 'detail' => 'стык ролей, потери контекста и цена неописанных решений'],
            ['section' => 'playbooks', 'title' => 'postback как язык, на котором команда спорит с реальностью', 'subject' => 'трекеры и postback', 'detail' => 'макросы, отладка и восстановление сигнала, когда цифры перестают совпадать с интуицией'],
            ['section' => 'playbooks', 'title' => 'launch day без романтики и без хаоса', 'subject' => 'launch day', 'detail' => 'порядок мелких действий, на которых держится качество запуска'],
            ['section' => 'playbooks', 'title' => 'routing как защита от плохих сюрпризов', 'subject' => 'routing', 'detail' => 'запасные маршруты, резервная логика и дисциплина, которая спасает не в теории, а в ночь запуска'],
            ['section' => 'playbooks', 'title' => 'антипаттерны QA, которые все знают и все равно повторяют', 'subject' => 'QA', 'detail' => 'сбои, рождающиеся не из сложности, а из спешки и слепых пятен'],
            ['section' => 'playbooks', 'title' => 'day one setup без красивых легенд', 'subject' => 'онбординг нового баера', 'detail' => 'минимум, без которого человек не входит в ритм команды'],
            ['section' => 'playbooks', 'title' => 'anti-detect как повседневная рутина, а не магия', 'subject' => 'anti-detect', 'detail' => 'дисциплина маленьких правил, которые скучны до первого крупного сбоя'],
            ['section' => 'playbooks', 'title' => 'domain recovery как спокойное ремесло', 'subject' => 'восстановление доменов', 'detail' => 'серия решений, где паника всегда мешает больше, чем бан'],
            ['section' => 'playbooks', 'title' => 'creative review loop, который не съедает команду', 'subject' => 'review loop', 'detail' => 'как ревью перестает быть болтовней и становится рабочей системой'],
            ['section' => 'playbooks', 'title' => 'rollback-план, о котором вспоминают слишком поздно', 'subject' => 'rollback', 'detail' => 'сценарии возврата, которые выглядят скучно до первого серьезного перекоса'],

            ['section' => 'signals', 'title' => 'новости, после которых меняется не заголовок, а операционка', 'subject' => 'policy и новости', 'detail' => 'апдейты платформ, enforcement и последствия для команды уже на следующей смене'],
            ['section' => 'signals', 'title' => 'регуляторика, которая приходит в чат раньше, чем в план', 'subject' => 'регуляторика', 'detail' => 'российские и СНГ-изменения, влияющие на рекламу, платежи, коммуникацию и рутину команд'],
            ['section' => 'signals', 'title' => 'рынок, который разговаривает через биржи и крипту', 'subject' => 'биржи и криптовалюты', 'detail' => 'движение капитала, нерв ликвидности и то, как это меняет аппетит к риску'],
            ['section' => 'signals', 'title' => 'российские бизнес-новости как сигнал для digital-команд', 'subject' => 'бизнес-новости России', 'detail' => 'новости компаний, банков, логистики и платежей, которые меняют операционную среду незаметнее, чем кажется'],
            ['section' => 'signals', 'title' => 'политика как источник побочных операционных эффектов', 'subject' => 'политические события', 'detail' => 'решения вне рынка, которые неожиданно меняют рынок сильнее, чем отраслевые анонсы'],
            ['section' => 'signals', 'title' => 'новое законодательство и старая привычка недооценивать его последствия', 'subject' => 'законодательство России', 'detail' => 'правовые изменения, которые сначала кажутся формальностью, а потом меняют темп и структуру работы'],
            ['section' => 'signals', 'title' => 'биржевая нервозность как фон для решений в affiliate', 'subject' => 'биржевые сдвиги', 'detail' => 'изменение настроения рынка, после которого команды начинают осторожнее относиться к тем же самым вещам'],
            ['section' => 'signals', 'title' => 'криптовалюта как термометр общего аппетита к риску', 'subject' => 'крипторынок', 'detail' => 'движения, по которым можно читать не только цену, но и общий климат решений'],
            ['section' => 'signals', 'title' => 'platform watch без суеты и без самообмана', 'subject' => 'platform watch', 'detail' => 'умение смотреть на апдейты платформ как на рабочий материал, а не как на повод к панике'],
            ['section' => 'signals', 'title' => 'санкции, платежи и новые контуры осторожности', 'subject' => 'платежная среда', 'detail' => 'изменения внешнего контура, после которых внутренние маршруты команды перестают быть очевидными'],

            ['section' => 'fun', 'title' => 'внутренний фольклор affiliate-команд', 'subject' => 'командная культура', 'detail' => 'ночные смены, ритуалы запуска и маленькие суеверия операционки'],
            ['section' => 'fun', 'title' => 'драма модерации как повторяющийся жанр', 'subject' => 'модерация', 'detail' => 'сцены, где у команды уже есть любимые реплики, но выводы все равно стоят дорого'],
            ['section' => 'fun', 'title' => 'мемы команды как способ пережить тяжелую неделю', 'subject' => 'командные мемы', 'detail' => 'юмор, который оказывается формой коллективной диагностики'],
            ['section' => 'fun', 'title' => 'фарм как территория легенд и маленьких ритуалов', 'subject' => 'фарм', 'detail' => 'внутренние правила, в которые никто не верит вслух, но все соблюдают'],
            ['section' => 'fun', 'title' => 'креативное выгорание как темная бытовая комедия', 'subject' => 'выгорание креативов', 'detail' => 'переутомление, которое сначала смешит, а потом обнуляет темп команды'],
            ['section' => 'fun', 'title' => 'postback-абсурд как отдельный поджанр боли', 'subject' => 'postback-ошибки', 'detail' => 'случаи, где реальность упрямо не желает укладываться в логику таблиц'],
            ['section' => 'fun', 'title' => 'модерация платформ как театр повторяющихся жестов', 'subject' => 'модерация платформ', 'detail' => 'цикл ситуаций, где все уже знают сюжет, но финал все равно бьет по нервам'],
            ['section' => 'fun', 'title' => 'ритуалы ночной смены и магия последнего кофе', 'subject' => 'ночная смена', 'detail' => 'моменты, когда усталость и мастерство начинают звучать почти одинаково'],
            ['section' => 'fun', 'title' => 'backstage-ирония как последняя форма устойчивости', 'subject' => 'ирония команды', 'detail' => 'внутренний способ не распадаться на фоне перегрузки'],
            ['section' => 'fun', 'title' => 'операционная пародия на бесконечный созвон', 'subject' => 'созвоны и процессы', 'detail' => 'комедия лишних слов, за которыми прячется нехватка ясных решений'],
        ];
    }
}

if (!function_exists('footer_seo_blocks_frame_library')) {
    function footer_seo_blocks_frame_library(): array
    {
        return [
            [
                'style' => 'editorial-note',
                'title' => static function (array $theme): string {
                    return 'Когда ' . $theme['title'] . ' перестают быть фоном';
                },
                'kicker' => 'ЦПАЛЬНЯ / редакционная заметка',
                'body' => static function (array $theme): string {
                    return '<p>У журнального текста про affiliate-операционку есть одна честная обязанность: не пересказывать шум, а показывать, где именно этот шум начинает менять рабочие решения. Поэтому материалы про ' . $theme['subject'] . ' у нас строятся не как очередная заметка «по теме», а как попытка внимательно разобрать момент, когда ' . $theme['detail'] . ' уже невозможно считать внешним обстоятельством. В этот момент меняется не только тон разговоров в чатах. Меняется порядок приоритетов, меняется цена ошибки, меняется само чувство устойчивости, на котором команда держалась еще вчера.</p><p>Полезность такого блока не в том, чтобы впечатлить красивой формулировкой. Он нужен, чтобы собрать мысль в рабочий фокус: что в теме уже дрогнуло, какая роль почувствует это первой, где нужен более трезвый темп, а где, наоборот, нельзя опоздать с решением. Журнальный стиль здесь работает как инструмент точности. Через него проще увидеть, почему даже спокойная история про ' . $theme['subject'] . ' на деле всегда оказывается разговором о дисциплине, внимании к деталям и способности команды не терять форму в момент, когда рынок начинает вести себя неровно.</p>';
                },
            ],
            [
                'style' => 'mini-story',
                'title' => static function (array $theme): string {
                    return 'Ночная смена, в которой снова всплыло: ' . $theme['title'];
                },
                'kicker' => 'ЦПАЛЬНЯ / мини-рассказ',
                'body' => static function (array $theme): string {
                    return '<p>Обычно все начинается не с большого обвала, а с почти незаметной детали. Кто-то в ночной смене первым говорит, что история про ' . $theme['subject'] . ' звучит как-то иначе, чем неделю назад. Цифры еще не обвалились, dashboard еще держит лицо, но воздух в операционке уже меняется: чат становится осторожнее, решения формулируются суше, а простые вещи внезапно требуют лишней проверки. Именно в такие моменты тема перестает быть абстрактной. Она входит в смену, садится рядом с командой и начинает влиять на темп.</p><p>Мы любим этот журнальный формат за то, что он позволяет не терять человеческую фактуру. Через маленький сюжет легче показать, как ' . $theme['detail'] . ' превращаются из фонового знания в конкретный выбор: сейчас тормознуть или ускориться, предупредить команду заранее или дать ситуации раскрыться, перепроверить старый маршрут или признать, что старая уверенность уже не работает. Такой текст не драматизирует рынок, а помогает прожить его изменения без лишнего героизма и без опасной самоуверенности.</p>';
                },
            ],
            [
                'style' => 'memo',
                'title' => static function (array $theme): string {
                    return 'Memo для тех, кто работает с темой: ' . $theme['title'];
                },
                'kicker' => 'ЦПАЛЬНЯ / memo',
                'body' => static function (array $theme): string {
                    return '<p>Если смотреть на журнал как на рабочий архив, то материалы про ' . $theme['subject'] . ' нужны не ради красивого сопровождения страницы, а ради калибровки решений. Полезный memo должен делать три вещи сразу: поднимать тему из общего шума, связывать ее с реальными ролями внутри команды и возвращать разговор к вопросу «что именно теперь придется делать иначе». Поэтому здесь мы смотрим на ' . $theme['detail'] . ' не как на повод для абстрактного вывода, а как на узел, в котором сходятся скорость, риск, коммуникация и качество ежедневной дисциплины.</p><p>Художественный тон в таком тексте не для украшения, а для запоминания. Он помогает удержать мысль дольше, чем сухой список пунктов. После хорошего memo у читателя остается не только формула проблемы, но и чувство ее масштаба: где начнется первый перекос, на какой привычке команда скорее всего споткнется, какой маленький сигнал нельзя пропустить сейчас, пока он еще выглядит невинно. Если текст помогает увидеть это заранее, значит он уже работает как часть операционной пользы, а не как еще один декоративный SEO-слой.</p>';
                },
            ],
            [
                'style' => 'allegory',
                'title' => static function (array $theme): string {
                    return 'Иногда рынок выглядит как сцена, где ' . $theme['title'];
                },
                'kicker' => 'ЦПАЛЬНЯ / аллюзия',
                'body' => static function (array $theme): string {
                    return '<p>Есть темы, которые в affiliate-среде похожи на смену света в большом зале: никто еще не объявил тревогу, но предметы уже начинают выглядеть иначе. То, что днем казалось устойчивым, к вечеру приобретает новые тени. Именно так часто входят в рабочую жизнь сюжеты про ' . $theme['subject'] . '. Снаружи они могут выглядеть как частная новость, узкий сбой или один не слишком громкий сдвиг. Но внутри операционки это часто оказывается моментом, когда старая карта перестает быть надежной, а новая еще только собирается из коротких наблюдений, реплик и неровных цифр.</p><p>Аллюзия полезна здесь тем, что помогает увидеть общий рисунок, не теряя практический нерв. Через нее проще почувствовать, что ' . $theme['detail'] . ' важны не сами по себе, а как часть сцены, где ежедневно встречаются риск, темп, усталость, дисциплина и способность команды не распадаться на отдельные реакции. Хороший журнальный текст не размывает проблему метафорой, а наоборот, дает ей форму. После него читатель яснее понимает, где в этой красивой картинке находится реальная точка давления и какое решение стоит принять, пока сцена не сменилась окончательно.</p>';
                },
            ],
            [
                'style' => 'field-note',
                'title' => static function (array $theme): string {
                    return 'Полевая запись о том, как ощущаются ' . $theme['title'];
                },
                'kicker' => 'ЦПАЛЬНЯ / полевая запись',
                'body' => static function (array $theme): string {
                    return '<p>Журналу про арбитраж мало просто перечислять темы. Гораздо важнее фиксировать их на уровне рабочей фактуры: как они звучат в разговорах команды, как меняют темп решений, почему внезапно требуют дополнительных проверок и в какой момент начинают влиять на рутину сильнее, чем на настроение. Так устроены наши тексты про ' . $theme['subject'] . '. В них важна не только тема сама по себе, но и ее температура: где она уже давит на процесс, где пока только намекает на будущий перекос, а где из нее еще можно извлечь спокойное и полезное преимущество.</p><p>Полевая запись хороша тем, что в ней нет лишней витринности. Она ближе к опыту смены, чем к готовой легенде. Через такой формат ' . $theme['detail'] . ' перестают выглядеть как сторонняя аналитика и становятся материалом для более зрелого решения: что перепроверить сегодня, что перестать откладывать, где команде нужен более чистый handoff, а где просто честный разговор без красивых самоописаний. Это и есть тот тип полезного журнального текста, к которому хочется возвращаться не ради тона, а ради ясности.</p>';
                },
            ],
        ];
    }
}

if (!function_exists('footer_seo_blocks_default_seed_rows')) {
    function footer_seo_blocks_default_seed_rows(): array
    {
        $themes = footer_seo_blocks_theme_library();
        $frames = footer_seo_blocks_frame_library();
        $rows = [];
        $sort = 1000;

        foreach ($themes as $theme) {
            foreach ($frames as $frame) {
                $rows[] = [
                    'domain_host' => 'cpalnya.ru',
                    'lang_code' => 'ru',
                    'section_scope' => 'all',
                    'style_variant' => (string)$frame['style'],
                    'block_kicker' => (string)$frame['kicker'],
                    'block_title' => (string)$frame['title']($theme),
                    'body_html' => (string)$frame['body']($theme),
                    'is_active' => 1,
                    'sort_order' => $sort,
                ];
                $sort--;
            }
        }

        return $rows;
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
               AND `sort_order` BETWEEN 851 AND 1000"
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
        $table = footer_seo_blocks_table_name();
        $host = strtolower(trim($host));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        $langCode = trim($langCode) !== '' ? trim($langCode) : 'ru';

        $hostSafe = mysqli_real_escape_string($db, $host);
        $langSafe = mysqli_real_escape_string($db, $langCode);
        $rows = [];
        $sql = "SELECT *
                FROM `{$table}`
                WHERE `is_active` = 1
                  AND `lang_code` = '{$langSafe}'
                  AND (`domain_host` = '' OR `domain_host` = '{$hostSafe}')
                ORDER BY `sort_order` DESC, `id` DESC";
        $res = @mysqli_query($db, $sql);
        if (!$res) {
            return null;
        }
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
        if (empty($rows)) {
            return null;
        }
        return $rows[array_rand($rows)];
    }
}

if (!function_exists('footer_tarot_source_path')) {
    function footer_tarot_source_path(): string
    {
        return defined('DIR') ? (DIR . 'tarro.png') : '';
    }
}

if (!function_exists('footer_tarot_card_labels')) {
    function footer_tarot_card_labels(string $langCode = 'ru'): array
    {
        $labels = [];
        for ($i = 1; $i <= 28; $i++) {
            $labels[] = ($langCode === 'ru' ? 'Аркан ' : 'Arcana ') . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        }
        return $labels;
    }
}

if (!function_exists('footer_tarot_pick_variants')) {
    function footer_tarot_pick_variants(array $variants, int $count = 1): array
    {
        $variants = array_values(array_filter(array_map('strval', $variants), static function (string $value): bool {
            return trim($value) !== '';
        }));
        if (empty($variants)) {
            return [];
        }
        shuffle($variants);
        return array_slice($variants, 0, max(1, $count));
    }
}

if (!function_exists('footer_tarot_title_variants')) {
    function footer_tarot_title_variants(string $langCode = 'ru'): array
    {
        if ($langCode !== 'ru') {
            return [
                'Tarot spread for today',
                'A quiet spread for this page',
                'Four cards for luck and timing',
            ];
        }

        $starts = [
            'Расклад на удачу и рабочий ритм',
            'Небольшой расклад перед следующей ставкой',
            'Карты на день, когда рынок слишком шумный',
            'Таро для тех, кто любит читать намеки',
            'Небольшое предсказание перед следующей сменой',
            'Расклад на настроение, темп и внутренний сигнал',
            'Четыре карты на случай, если нужен знак',
            'Тихий расклад про удачу, фокус и выбор момента',
            'Карточный жест на удачу и спокойствие',
            'Небольшой расклад для тех, кто работает в шуме',
        ];
        $middles = [
            'перед запуском',
            'перед новой попыткой',
            'на фоне рыночного шума',
            'между сигналами и сомнениями',
            'для аккуратного движения вперед',
            'для спокойной головы',
            'когда хочется поймать удачный ритм',
            'когда нужен маленький знак',
            'для чтения атмосферы дня',
            'на удачу и внутренний фокус',
        ];
        $ends = [
            'с оттенком редакционной мистики',
            'без лишней серьезности, но с настроением',
            'в тоне небольшого журнального ритуала',
            'для тех, кто умеет читать между строк',
            'как короткий знак перед следующим ходом',
            'с намеком на хорошие исходы',
            'для тех, кто любит символы и спокойный тон',
            'как маленькая пауза перед решением',
            'в духе темной редакционной витрины',
            'с вниманием к удаче, таймингу и интуиции',
        ];

        $variants = [];
        foreach ($starts as $start) {
            foreach ($middles as $middle) {
                foreach ($ends as $end) {
                    $variants[] = $start . ' ' . $middle . ' ' . $end;
                    if (count($variants) >= 100) {
                        return $variants;
                    }
                }
            }
        }
        return $variants;
    }
}

if (!function_exists('footer_tarot_intro_variants')) {
    function footer_tarot_intro_variants(string $langCode = 'ru'): array
    {
        if ($langCode !== 'ru') {
            return [
                'Some pages deserve a softer entrance. Four cards are enough to shift the mood from pure routine to a calmer, luck-aware reading of the moment.',
                'Treat this spread as a small atmospheric pause: not as a rule, but as a hint about timing, focus, and what kind of energy should be protected next.',
            ];
        }

        $subjects = [
            'удача',
            'внутренний тайминг',
            'спокойствие перед решением',
            'чувство верного момента',
            'маленькие знаки дня',
            'эмоциональный ритм',
            'интуитивный фокус',
            'атмосфера страницы',
            'ощущение потока',
            'тихая уверенность',
        ];
        $images = [
            'иногда приходит не как победный крик, а как едва заметный поворот воздуха',
            'часто ощущается не как чудо, а как правильная пауза перед нужным ходом',
            'редко выглядит громко, но почти всегда чувствуется в темпе решений',
            'живет в мелких совпадениях, которым обычно не дают имени',
            'лучше всего читается в коротких символах, а не в длинных обещаниях',
            'любит не суету, а мягкую собранность',
            'начинается с ощущения, что мир вдруг стал на полтона спокойнее',
            'иногда прячется в тех самых маленьких деталях, которые обычно пролистывают',
            'любит точность взгляда сильнее, чем громкие декларации',
            'появляется там, где человек готов заметить знак, а не требовать гарантии',
        ];
        $frames = [
            'Этот блок с картами нужен не для пророческой важности, а для атмосферы: он напоминает, что даже в самом плотном ритме полезно оставить место для символа, намека и красивого совпадения.',
            'Смысл этого маленького расклада не в том, чтобы предсказать все на свете, а в том, чтобы добавить странице мягкий второй слой: настроение, знак и ощущение скрытой логики происходящего.',
            'Небольшой tarot-мотив здесь работает как декоративная редакционная пауза: чуть-чуть мистики, чуть-чуть игры и ровно столько намека, чтобы день читался интереснее.',
            'Это не жесткая система ответов, а скорее дружелюбный ритуал: четыре карты, немного тени, немного света и пространство для собственного внутреннего толкования.',
            'Такой расклад нужен не для буквальной инструкции, а для интонации: чтобы на секунду выйти из сухого режима и услышать, в какую сторону сегодня склоняется удача.',
            'Здесь важен не буквальный оккультный смысл, а эффект присутствия: будто у страницы есть собственное послесвечение, а у текущего момента есть свой негромкий знак.',
            'Этот визуальный жест работает как редакционная примета: он не спорит с реальностью, а просто добавляет ей второй план, где случайность выглядит чуть более осмысленной.',
            'Карты здесь не обещают контроля над миром, но создают правильную температуру чтения: спокойную, внимательную и немного волшебную.',
            'Небольшой расклад напоминает простую вещь: иногда полезно не ускоряться, а сначала почувствовать рисунок дня, его напряжение и его мягкие шансы.',
            'В этой вставке важна сама идея символа: короткий образ, который помогает смотреть на день не только через действия, но и через знаки.',
        ];

        $variants = [];
        foreach ($subjects as $subject) {
            foreach ($images as $image) {
                $variants[] = 'В таких мини-раскладах ' . $subject . ' ' . $image . '.';
                if (count($variants) >= 100) {
                    break 2;
                }
            }
        }
        foreach ($frames as $frame) {
            $variants[] = $frame;
            if (count($variants) >= 200) {
                break;
            }
        }
        while (count($variants) < 200) {
            foreach ($subjects as $subject) {
                foreach ($frames as $frame) {
                    $variants[] = 'Иногда ' . $subject . ' проще уловить через образ, а не через объяснение. ' . $frame;
                    if (count($variants) >= 200) {
                        break 2;
                    }
                }
            }
        }
        return $variants;
    }
}

if (!function_exists('footer_tarot_prediction_variants')) {
    function footer_tarot_prediction_variants(string $langCode = 'ru'): array
    {
        if ($langCode !== 'ru') {
            return ['Prediction: today is better for quiet precision than for dramatic moves.'];
        }

        $openings = [
            'Предсказание',
            'Небольшое предсказание',
            'Знак расклада',
            'Шепот этого расклада',
            'Что обещают карты',
            'Тон сегодняшнего знака',
            'Смысл выпавшего рисунка',
            'Короткое чтение дня',
            'Сигнал этого мини-ритуала',
            'Итоговый намек карт',
        ];
        $directions = [
            'удача придет через спокойное действие',
            'лучший исход спрятан в терпеливом выборе момента',
            'день любит точность больше, чем напор',
            'правильное совпадение случится там, где не будет суеты',
            'ответ приблизится, если перестать торопить события',
            'хорошая развязка идет через мягкую настойчивость',
            'поворот к лучшему начинается с маленького доверия себе',
            'наиболее верный ход окажется самым тихим',
            'ритм дня награждает тех, кто замечает тонкие сигналы',
            'сегодня лучше слушать настроение момента, чем ломать его силой',
        ];
        $tones = [
            'Не спорь с этим темпом: он дает не быстрый блеск, а устойчивую удачу.',
            'Если идти в этом ключе, день ответит не драмой, а красивой собранностью.',
            'Такой вектор редко выглядит громко, зато часто оказывается самым счастливым.',
            'Это предсказание не про чудо, а про редкую форму своевременности.',
            'Именно в таком режиме карты обычно обещают самый чистый результат.',
            'Подход здесь простой: меньше шума, больше точного внутреннего слуха.',
            'Это история не про контроль, а про точное попадание в свой момент.',
            'Считай это благоприятным знаком на аккуратный и красивый ход.',
            'Скорее всего, удача сегодня будет выглядеть именно так: спокойно и уверенно.',
            'У такого предсказания мягкий голос, но обычно очень верное направление.',
        ];
        $additions = [
            'Если захочется резкого рывка, лучше сначала проверить, не просит ли день более точной траектории.',
            'Если появится соблазн ускориться, полезнее будет на мгновение остановиться и прислушаться.',
            'Если выборов окажется слишком много, ориентируйся на то, что дает внутреннюю ясность, а не только внешний блеск.',
            'Если интуиция шепчет тише привычного, все равно стоит дать ей место.',
            'Если пространство вокруг начнет шуметь, именно спокойствие станет самой редкой формой силы.',
            'Если впереди покажется красивая случайность, не отмахивайся от нее слишком быстро.',
            'Если день предложит паузу, это может быть не задержка, а удачный зазор для правильного решения.',
            'Если что-то пойдет мягче обычного, не пытайся сделать это жестче ради привычки.',
            'Если станет тревожно из-за медленного темпа, помни: хорошие совпадения редко любят спешку.',
            'Если будет ощущение, что все складывается слишком тонко, возможно, именно так и выглядит верный путь.',
        ];

        $variants = [];
        foreach ($openings as $opening) {
            foreach ($directions as $direction) {
                foreach ($tones as $tone) {
                    foreach ($additions as $addition) {
                        $variants[] = $opening . ': ' . $direction . '. ' . $tone . ' ' . $addition;
                        if (count($variants) >= 500) {
                            return $variants;
                        }
                    }
                }
            }
        }
        return $variants;
    }
}

if (!function_exists('footer_tarot_matrix_cards')) {
    function footer_tarot_matrix_cards(string $sourceImage, string $langCode = 'ru'): array
    {
        if ($sourceImage === '' || !is_file($sourceImage)) {
            return [];
        }
        $imgInfo = @getimagesize($sourceImage);
        if (!$imgInfo || empty($imgInfo[0]) || empty($imgInfo[1])) {
            return [];
        }

        $imgW = (int)$imgInfo[0];
        $imgH = (int)$imgInfo[1];
        if ($imgW <= 0 || $imgH <= 0) {
            return [];
        }

        $fullCols = 6;
        $fullRows = 4;
        $bottomCols = 4;
        $bottomShift = 1;

        $leftPad = (int)round($imgW * 0.018);
        $rightPad = (int)round($imgW * 0.018);
        $topPad = (int)round($imgH * 0.085);
        $bottomPad = (int)round($imgH * 0.045);
        $gapX = (int)round($imgW * 0.010);
        $gapY = (int)round($imgH * 0.012);
        $cardAspect = 1.76;

        $cardW = (int)floor(($imgW - $leftPad - $rightPad - ($gapX * ($fullCols - 1))) / $fullCols);
        $cardH = (int)round($cardW * $cardAspect);
        $totalRows = $fullRows + 1;
        $maxPossibleCardH = (int)floor(($imgH - $topPad - $bottomPad - ($gapY * ($totalRows - 1))) / $totalRows);
        if ($cardH > $maxPossibleCardH) {
            $cardH = $maxPossibleCardH;
        }

        $labels = footer_tarot_card_labels($langCode);
        $cards = [];
        $index = 1;

        for ($row = 0; $row < $fullRows; $row++) {
            for ($col = 0; $col < $fullCols; $col++) {
                $cards[] = [
                    'index' => $index,
                    'name' => $labels[$index - 1] ?? (($langCode === 'ru' ? 'Аркан ' : 'Arcana ') . str_pad((string)$index, 2, '0', STR_PAD_LEFT)),
                    'row' => $row,
                    'col' => $col,
                    'x' => $leftPad + $col * ($cardW + $gapX),
                    'y' => $topPad + $row * ($cardH + $gapY),
                    'w' => $cardW,
                    'h' => $cardH,
                ];
                $index++;
            }
        }

        $bottomRowIndex = $fullRows;
        for ($col = 0; $col < $bottomCols; $col++) {
            $visualCol = $bottomShift + $col;
            $cards[] = [
                'index' => $index,
                'name' => $labels[$index - 1] ?? (($langCode === 'ru' ? 'Аркан ' : 'Arcana ') . str_pad((string)$index, 2, '0', STR_PAD_LEFT)),
                'row' => $bottomRowIndex,
                'col' => $visualCol,
                'x' => $leftPad + $visualCol * ($cardW + $gapX),
                'y' => $topPad + $bottomRowIndex * ($cardH + $gapY),
                'w' => $cardW,
                'h' => $cardH,
            ];
            $index++;
        }

        return $cards;
    }
}

if (!function_exists('footer_tarot_crop_card_data_url')) {
    function footer_tarot_crop_card_data_url($src, array $card): string
    {
        $w = (int)($card['w'] ?? 0);
        $h = (int)($card['h'] ?? 0);
        if ($w <= 0 || $h <= 0 || (!is_resource($src) && !is_object($src))) {
            return '';
        }

        $dst = imagecreatetruecolor($w, $h);
        if (!$dst) {
            return '';
        }
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);

        $zoomRatio = 0.90;
        $cropW = max(1, (int)round($w * $zoomRatio));
        $cropH = max(1, (int)round($h * $zoomRatio));
        $cropX = (int)$card['x'] + (int)floor(($w - $cropW) / 2);
        $cropY = (int)$card['y'] + (int)floor(($h - $cropH) / 2);

        imagecopyresampled(
            $dst,
            $src,
            0,
            0,
            $cropX,
            $cropY,
            $w,
            $h,
            $cropW,
            $cropH
        );

        ob_start();
        imagepng($dst, null, 7);
        $pngData = ob_get_clean();
        imagedestroy($dst);
        if (!is_string($pngData) || $pngData === '') {
            return '';
        }
        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}

if (!function_exists('footer_tarot_pick_random')) {
    function footer_tarot_pick_random(string $langCode = 'ru', int $count = 4): array
    {
        if (!function_exists('imagecreatefrompng')) {
            return [];
        }

        $sourceImage = footer_tarot_source_path();
        $cards = footer_tarot_matrix_cards($sourceImage, $langCode);
        if (empty($cards)) {
            return [];
        }

        $imgInfo = @getimagesize($sourceImage);
        if (!$imgInfo) {
            return [];
        }
        $imgType = (int)($imgInfo[2] ?? 0);
        if ($imgType === IMAGETYPE_PNG) {
            $src = @imagecreatefrompng($sourceImage);
        } elseif ($imgType === IMAGETYPE_JPEG) {
            $src = @imagecreatefromjpeg($sourceImage);
        } elseif (defined('IMAGETYPE_WEBP') && $imgType === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($sourceImage);
        } else {
            $src = null;
        }
        if (!$src) {
            return [];
        }

        $count = max(1, min(8, $count));
        if (count($cards) < $count) {
            $count = count($cards);
        }
        $randomIndexes = array_rand($cards, $count);
        if (!is_array($randomIndexes)) {
            $randomIndexes = [$randomIndexes];
        }

        $selected = [];
        foreach ($randomIndexes as $idx) {
            $card = $cards[(int)$idx] ?? null;
            if (!is_array($card)) {
                continue;
            }
            $card['image'] = footer_tarot_crop_card_data_url($src, $card);
            $selected[] = $card;
        }
        imagedestroy($src);
        return $selected;
    }
}

if (!function_exists('footer_tarot_render_html')) {
    function footer_tarot_render_html(array $cards, string $langCode = 'ru'): string
    {
        if (empty($cards)) {
            return '';
        }
        $predictionVariants = footer_tarot_prediction_variants($langCode);
        $prediction = (footer_tarot_pick_variants($predictionVariants, 1)[0] ?? '');
        ob_start();
        ?>
        <section class="public-footer-tarot" aria-label="<?= htmlspecialchars($langCode === 'ru' ? 'Случайный расклад таро' : 'Random tarot spread', ENT_QUOTES, 'UTF-8') ?>">
            <div class="public-footer-tarot-row">
                <?php foreach ($cards as $card): ?>
                    <figure class="public-footer-tarot-card" data-card-index="<?= (int)($card['index'] ?? 0) ?>">
                        <div class="public-footer-tarot-visual">
                            <img src="<?= htmlspecialchars((string)($card['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($card['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <figcaption class="public-footer-tarot-caption">
                            <span class="public-footer-tarot-name"><?= htmlspecialchars((string)($card['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="public-footer-tarot-index">#<?= str_pad((string)(int)($card['index'] ?? 0), 2, '0', STR_PAD_LEFT) ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <?php if ($prediction !== ''): ?>
                <div class="public-footer-tarot-prediction">
                    <strong><?= htmlspecialchars($langCode === 'ru' ? 'Предсказание' : 'Prediction', ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($prediction, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>
        </section>
        <?php
        return (string)ob_get_clean();
    }
}

if (!function_exists('footer_seo_blocks_render_html')) {
    function footer_seo_blocks_render_html(?array $block, array $tarotCards = [], string $langCode = 'ru'): string
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
        <div class="public-footer-seo-stack">
            <section class="public-footer-seo-block public-footer-seo-block--<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($kicker !== ''): ?>
                    <span class="public-footer-seo-kicker"><?= htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if ($title !== ''): ?>
                    <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php endif; ?>
                <div class="public-footer-seo-body"><?= $bodyHtml ?></div>
            </section>
        </div>
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
        $html = footer_seo_blocks_render_html($block, [], $langCode);

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
