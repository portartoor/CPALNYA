UPDATE seo_generator_settings
SET settings_json = JSON_SET(
    settings_json,
    '$.prompt_version', 'cpalnya-generator-v4-topic-spectrum',
    '$.campaigns.journal.styles_ru', JSON_ARRAY(
        'редакционный разбор','аналитическая записка','trend briefing','стратегический обзор',
        'открывающая статья номера','редакционный блокнот','карта рынка','закулисный отчет',
        'операторская колонка','полевой memo','сигнальное эссе','нишевой dispatch'
    ),
    '$.campaigns.journal.clusters_ru', JSON_ARRAY(
        'Meta Ads в 2026: фарм, BM-устойчивость и антибан',
        'TikTok и short-form воронки под affiliate-офферы',
        'Telegram Mini Apps, комьюнити и retention в арбитраже',
        'AI-креативы, compliance и контроль prompt-пайплайна',
        'трекеры, атрибуция и потеря сигналов после privacy-сдвигов',
        'сдвиги спроса по nutra, finance, iGaming и crypto в 2026',
        'срок жизни креативов и экономика постоянной пересборки',
        'волатильность источников и адаптация баеров',
        'выбор офферов в условиях сжатия маржи',
        'дисциплина handoff внутри affiliate-операционки',
        'найм операторов и backstage-структура команды',
        'whitehat-оболочки и compliance-буферы',
        'давление модерации и деградация account trust',
        'Telegram как owned media-инфраструктура для команды',
        'экономика фарма и стратегия срока жизни аккаунтов',
        'карта гео, офферов и source mix для команды',
        'качество решений после деградации атрибуции',
        'рабочий ритм внутри медиабаинг-команд под давлением'
    ),
    '$.campaigns.playbooks.styles_ru', JSON_ARRAY(
        'пошаговый how-to','гайд по устранению проблем','операционный playbook','технический кейс',
        'setup memo','launch checklist','rollback memo','implementation guide',
        'runbook','SOP blueprint','дневник отладки','гайд по восстановлению'
    ),
    '$.campaigns.playbooks.clusters_ru', JSON_ARRAY(
        'настройка и прогрев Facebook farm',
        'cloaking и безопасная маршрутизация под модерацию',
        'tracker postback-шаблоны, макросы и отладка',
        'anti-detect браузеры, роли команды и SOP',
        'матрица тестирования креативов и цикл итераций',
        'платежки, домены, хостинг и восстановление лендингов',
        'launch QA и preflight-проверки перед запуском',
        'восстановление trust у BM после банов',
        'handoff-шаблоны для баеров и ассистентов',
        'failover-маршрутизация и резервная инфраструктура',
        'creative review loop и контроль выгорания',
        'workflow скрининга офферов для операторов',
        'day-one setup для junior media buyer',
        'непрерывность checkout после сбоев платежей',
        'hygiene трекинга и валидация макросов',
        'rollback-план после неудачных релизов',
        'миграция связки между командами',
        'операторский дашборд и рутина алертов'
    ),
    '$.campaigns.signals.styles_ru', JSON_ARRAY(
        'policy brief','обзор сигнала рынка','регуляторная записка','сводка изменений платформ',
        'policy-impact memo','operator checklist','что делать сейчас','enforcement watch',
        'risk memo','compliance update','операционный bulletin','market alert',
        'government watch','биржевой watch','crypto signal memo','законодательный трекер'
    ),
    '$.campaigns.signals.clusters_ru', JSON_ARRAY(
        'изменения policy в Meta и ужесточение модерации',
        'сдвиги Telegram, правила монетизации и новые ограничения',
        'ограничения TikTok Ads и апдейты commerce-экосистемы',
        'регуляторные движения в СНГ и их эффект на affiliate-рынок',
        'privacy-сдвиги в мире и последствия для атрибуции',
        'сигналы по payment, compliance и волатильности источников',
        'сдвиги trust-механик и давление на качество аккаунтов',
        'волатильность источников после новых правил модерации',
        'потери измерения после privacy-ограничений и data retention-сдвигов',
        'Telegram enforcement-паттерны для affiliate-команд',
        'операционный response plan при резких ограничениях платформ',
        'свежие политические новости и решения власти с влиянием на рынок трафика',
        'бизнес-аналитика по компаниям, секторам и рыночному стрессу',
        'аналитика бирж, структуры рынка и движения ликвидности',
        'отдельные криптовалюты и их новостной фон как рыночный сигнал',
        'свежие бизнес-новости России с последствиями для digital-рынка',
        'новости законодательства России, законопроекты и практика применения',
        'санкции, платежи и риски расчетов для операторов',
        'сигналы центробанков и повороты финансового регулирования',
        'перераспределение рекламных бюджетов после макро-событий',
        'правила мессенджеров и экономика дистрибуции',
        'политические циклы и смена поведения платформ'
    ),
    '$.campaigns.fun.styles_ru', JSON_ARRAY(
        'сатирический памфлет','юмористический обзор','мем-редакционка','операционная пародия',
        'заметка про офисный фольклор','комическая backstage-колонка','темная комедия',
        'абсурдный dispatch','набросок командной мифологии','mock memo',
        'трагикомический обзор','ритуальный блокнот'
    ),
    '$.campaigns.fun.clusters_ru', JSON_ARRAY(
        'агентская жизнь и фольклор медиабаеров',
        'драма фарма и суеверия операторов',
        'выгорание креативов как темная комедия',
        'хаос трекеров и абсурд postback-ошибок',
        'командные мемы и backstage-ритуалы',
        'модерация платформ как трагикомедия',
        'ночной делирий у дашбордов',
        'фольклор недопонимания между ассистентом и баером',
        'ритуалы launch-day и управление паникой',
        'creative review как офисный театр',
        'восстановление BM как героический квест',
        'Telegram-чаты как племенная память команды',
        'выгорание операторов как нишевая комедия',
        'культ таблиц и суеверия вокруг метрик',
        'anti-detect-дисциплина как рабочая мифология',
        'скрининг офферов как сатирический портрет команды',
        'голосовые команды и фольклор кризисов',
        'митинг после banwave как трагическая буффонада'
    )
),
updated_at = NOW()
WHERE id = (
    SELECT id
    FROM (
        SELECT id
        FROM seo_generator_settings
        ORDER BY id DESC
        LIMIT 1
    ) AS t
);

SELECT
  JSON_LENGTH(JSON_EXTRACT(settings_json, '$.campaigns.journal.clusters_ru')) AS journal_clusters,
  JSON_LENGTH(JSON_EXTRACT(settings_json, '$.campaigns.playbooks.clusters_ru')) AS playbooks_clusters,
  JSON_LENGTH(JSON_EXTRACT(settings_json, '$.campaigns.signals.clusters_ru')) AS signals_clusters,
  JSON_LENGTH(JSON_EXTRACT(settings_json, '$.campaigns.fun.clusters_ru')) AS fun_clusters
FROM seo_generator_settings
ORDER BY id DESC
LIMIT 1;
