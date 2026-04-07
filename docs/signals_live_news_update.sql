UPDATE seo_generator_settings
SET settings_json = JSON_SET(
        settings_json,
        '$.signals_news_enabled', true,
        '$.signals_news_max_items', 14,
        '$.signals_news_lookback_hours', 96,
        '$.signals_news_timeout', 12,
        '$.signals_news_feeds', JSON_ARRAY(
            'https://news.google.com/rss/search?q=affiliate+marketing+OR+digital+advertising+policy&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=traffic+arbitrage+OR+adtech+regulation&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=Russia+business+news+law+digital&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=Russia+legislation+advertising+internet&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=crypto+market+regulation+exchange&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=stock+market+business+analysis+Russia&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=politics+business+sanctions+payments+Russia&hl=ru&gl=RU&ceid=RU:ru',
            'https://news.google.com/rss/search?q=telegram+policy+monetization+news&hl=ru&gl=RU&ceid=RU:ru'
        ),
        '$.campaigns.signals.styles_ru', JSON_ARRAY(
            'policy brief',
            'обзор сигнала рынка',
            'регуляторная записка',
            'сводка изменений платформ',
            'policy-impact memo',
            'operator checklist',
            'что делать сейчас',
            'enforcement watch',
            'risk memo',
            'compliance update',
            'операционный bulletin',
            'разбор последствий для команды'
        ),
        '$.campaigns.signals.styles_en', JSON_ARRAY(
            'policy brief',
            'market signal memo',
            'regulatory watch',
            'platform change dispatch',
            'policy-impact memo',
            'operator checklist',
            'what-to-do-now brief',
            'enforcement watch',
            'risk memo',
            'compliance update',
            'platform shift note',
            'impact bulletin'
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
            'потери измерения после privacy-ограничений и data retention сдвигов',
            'Telegram enforcement-паттерны для affiliate-команд',
            'свежие политические новости и решения власти с влиянием на рынок трафика',
            'бизнес-аналитика по компаниям, секторам и рыночному стрессу',
            'аналитика бирж, структуры рынка и движения ликвидности',
            'отдельные криптовалюты и их новостной фон как рыночный сигнал',
            'свежие бизнес-новости России с последствиями для digital-рынка',
            'новости законодательства России, законопроекты и практика применения'
        ),
        '$.campaigns.signals.clusters_en', JSON_ARRAY(
            'meta policy changes and moderation enforcement',
            'telegram platform shifts and monetization rules',
            'tiktok ads restrictions and commerce updates',
            'cis regulation and affiliate market implications',
            'global privacy moves and attribution fallout',
            'payment, compliance and source volatility signals',
            'platform trust shifts and account quality pressure',
            'source-side volatility after moderation rule changes',
            'measurement loss after privacy and data retention changes',
            'telegram enforcement patterns for affiliate operators',
            'fresh politics and government decisions affecting traffic markets',
            'business analysis around public companies, sectors and market stress',
            'exchange analytics and market structure shifts',
            'major cryptocurrency moves and asset-specific implications',
            'russian business news with operational market consequences',
            'new russian laws, bills and enforcement practice with digital impact'
        ),
        '$.campaigns.signals.article_structures_ru', JSON_ARRAY(
            'Сигнал -> что изменилось -> влияние на оператора -> затронутые гео -> чеклист действий',
            'Policy-апдейт -> паттерн enforcement -> скрытые риски -> план реакции',
            'Новость -> почему это важно -> кто почувствует первым -> шаги смягчения',
            'Policy-impact memo -> что изменилось -> что ломается -> что делать сейчас',
            'Regulatory watch -> краткий сигнал -> карта рисков -> чеклист реакции',
            'Operator checklist -> немедленные действия -> edge cases -> верификация',
            'Что делать сейчас -> сигнал -> последствия -> план на первые 24 часа'
        ),
        '$.campaigns.signals.article_structures_en', JSON_ARRAY(
            'Signal -> what changed -> operator impact -> affected geos -> action checklist',
            'Policy update -> enforcement pattern -> hidden risks -> response plan',
            'News brief -> why it matters -> who gets hit first -> mitigation steps',
            'Policy-impact memo -> what changed -> what breaks -> what to do now',
            'Regulatory watch -> signal summary -> risk map -> response checklist',
            'Operator checklist -> immediate actions -> edge cases -> verification',
            'What-to-do-now -> signal -> implications -> first 24 hours plan'
        ),
        '$.campaigns.signals.article_user_prompt_append_ru',
        'Отслеживай policy, модерацию, регуляторику и рыночные сигналы вокруг арбитража трафика. В этот же слой включай свежие политические новости, бизнес-аналитику, аналитику бирж, движения по отдельным криптовалютам, бизнес-новости России и новости законодательства России, если у них есть реальное последствие для digital-рынка, affiliate-операций, платежей, комплаенса, источников трафика или поведения платформ. Принудительно смещай угол в policy-impact memo, regulatory watch, operator checklist, enforcement watch или формат «что делать сейчас», а не в очередной общий обзор. Каждый материал должен отвечать на вопросы: что изменилось, кто почувствует удар первым, что ломается в операционке и что команде делать дальше.',
        '$.campaigns.signals.article_user_prompt_append_en',
        'Track policy, moderation, legal and market signals around affiliate traffic. Include fresh politics, business analysis, exchange analytics, major cryptocurrency developments, Russian business news and Russian legislation when they create real operator-relevant consequences. Force a non-generic angle: prefer policy-impact memo, regulatory watch, operator checklist, enforcement watch or what-to-do-now format over a generic overview. Every article must answer what changed, who gets hit first, what breaks operationally, and what the team should do next.'
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
