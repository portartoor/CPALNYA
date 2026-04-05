-- RU migration from EN examples_articles
-- Safe to run multiple times
-- Requirements addressed:
-- 1) Dedicated RU rows only (lang_code='ru')
-- 2) RU SEO title/excerpt/content
-- 3) RU slug in translit

SET NAMES utf8mb4;

SET @db_name = DATABASE();

SET @has_lang_col = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'examples_articles'
    AND COLUMN_NAME = 'lang_code'
);

SET @sql_lang = IF(
  @has_lang_col = 0,
  'ALTER TABLE `examples_articles` ADD COLUMN `lang_code` VARCHAR(5) NOT NULL DEFAULT ''en'' AFTER `domain_host`',
  'SELECT 1'
);
PREPARE stmt_lang FROM @sql_lang;
EXECUTE stmt_lang;
DEALLOCATE PREPARE stmt_lang;

DROP TEMPORARY TABLE IF EXISTS tmp_ru_examples_map;
CREATE TEMPORARY TABLE tmp_ru_examples_map (
  en_slug VARCHAR(191) NOT NULL,
  ru_title VARCHAR(255) NOT NULL,
  ru_slug VARCHAR(191) NOT NULL,
  ru_excerpt TEXT NULL,
  ru_content MEDIUMTEXT NOT NULL,
  PRIMARY KEY (en_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_ru_examples_map (en_slug, ru_title, ru_slug, ru_excerpt, ru_content) VALUES
(
  'impossible-travel-detection-geoip-user-id',
  'Как построить детект impossible travel с GeoIP и user_id',
  'kak-postroit-detekt-impossible-travel-s-geoip-i-user-id',
  '<p>Практическое руководство для B2B-команд: как считать impossible travel, снижать ATO и не ломать конверсию при входе и оплате.</p>',
  '<h2>Как построить детект impossible travel с GeoIP и user_id</h2><p>Impossible travel — один из ключевых антифрод-сигналов: пользователь не может физически оказаться в двух удаленных точках за короткий интервал. В продакшене важно учитывать не только расстояние и время, но и контекст сети, ASN и признаки прокси/VPN.</p><p>Рекомендуемый подход: фиксируйте стабильный <code>user_id</code>, храните последнюю доверенную геоточку, считайте скорость перемещения, добавляйте веса за proxy/VPN, аномальную смену ASN и резкие геоскачки. Для бизнес-процесса используйте пороги allow / step-up / review / block и регулярно калибруйте их по фактическим инцидентам.</p>'
),
(
  'fraud-scoring-architecture-ip-geo-behavior',
  'Архитектура fraud scoring: IP-репутация, geo-аномалии и поведенческие сигналы',
  'arkhitektura-fraud-scoring-ip-reputatsiya-geo-anomalii-i-povedencheskie-signaly',
  '<p>Как собрать объяснимый fraud scoring: объединить IP reputation, гео-контекст и поведенческие признаки в стабильные решения allow/review/block.</p>',
  '<h2>Архитектура fraud scoring: IP-репутация, geo-аномалии и поведенческие сигналы</h2><p>Надежный fraud scoring состоит из 5 слоев: ingestion событий, enrichment (GeoIP/ASN/proxy), feature engineering, decision engine и feedback loop. Такая архитектура дает не просто высокий score, а управляемое решение для реального бизнеса.</p><p>Для SEO и коммерческого эффекта фокусируйтесь на терминах: fraud scoring model, IP reputation API, anti-fraud orchestration. В операционной части внедряйте отдельные пороги для login, signup, checkout и payout — это снижает false positive и повышает точность блокировок.</p>'
),
(
  'geoip-laravel-middleware-risk-aware-flows',
  'GeoIP в Laravel: middleware для risk-aware авторизации, checkout и изменений аккаунта',
  'geoip-v-laravel-middleware-dlya-risk-aware-avtorizatsii-checkout-i-izmeneniy-akkaunta',
  '<p>Интеграция GeoIP в Laravel middleware: единый риск-контекст для login, checkout и критичных действий в аккаунте.</p>',
  '<h2>GeoIP в Laravel: middleware для risk-aware авторизации, checkout и изменений аккаунта</h2><p>Laravel middleware — удобная точка для обогащения запроса геоданными и antifraud-сигналами. Один вызов API дает общий контекст для контроллеров, policy-слоя и бизнес-правил.</p><p>Практика: корректно извлекайте IP через trusted proxy, передавайте <code>ip</code> и <code>user_id</code>, логируйте источник решения и применяйте step-up проверку только там, где риск действительно повышен.</p>'
),
(
  'geoip-django-fastapi-trusted-ip-antifraud-hooks',
  'GeoIP в Django и FastAPI: trusted IP, antifraud hooks и аудит',
  'geoip-v-django-i-fastapi-trusted-ip-antifraud-hooks-i-audit',
  '<p>Python-подход для Django/FastAPI: как правильно извлекать IP за CDN/proxy и внедрять antifraud hooks без деградации UX.</p>',
  '<h2>GeoIP в Django и FastAPI: trusted IP, antifraud hooks и аудит</h2><p>В Python-бэкендах критично правильно определять реальный клиентский IP. Ошибки на этом этапе ухудшают качество риска и приводят к ложным блокировкам.</p><p>Внедрите стандарт: trust boundaries для прокси, единый enrichment-hook, аудит решений с полями request_id/user_id/ip/risk_score/confidence. Это повышает воспроизводимость антифрод-решений и ускоряет расследование инцидентов.</p>'
),
(
  'geoip-nodejs-express-nestjs-real-time-risk-gates',
  'GeoIP в Node.js Express и NestJS: real-time risk gates перед login и payment',
  'geoip-v-nodejs-express-i-nestjs-real-time-risk-gates-pered-login-i-payment',
  '<p>Как внедрить risk gates в Express/NestJS перед авторизацией и платежами: быстро, прозрачно и с контролем ложных срабатываний.</p>',
  '<h2>GeoIP в Node.js Express и NestJS: real-time risk gates перед login и payment</h2><p>Node.js позволяет принимать risk-решения в реальном времени до критичных действий. Это снижает фрод-потери и повышает устойчивость аккаунтов.</p><p>Рекомендуется разделить enrichment и decision: middleware/interceptor собирает контекст, guard/policy применяет пороги. Для команды важно логировать, почему действие было разрешено, отправлено на step-up или заблокировано.</p>'
),
(
  'chargeback-prevention-playbook-geo-checks-payment-flows',
  'Playbook по снижению chargeback: где включать geo-проверки в payment flow',
  'playbook-po-snizheniyu-chargeback-gde-vklyuchat-geo-proverki-v-payment-flow',
  '<p>Пошаговый B2B-playbook: как распределить GeoIP-проверки по всему платежному пути и сократить chargeback.</p>',
  '<h2>Playbook по снижению chargeback: где включать geo-проверки в payment flow</h2><p>Одна проверка на этапе оплаты не решает проблему chargeback. Нужна многоуровневая стратегия: signup, login, checkout, смена платежных данных и пост-платежный мониторинг.</p><p>Приоритизируйте сигналы: новая страна, proxy/VPN на checkout, резкая смена ASN, аномальная активность по user_id. Такой подход повышает качество одобрений и снижает финансовые потери.</p>'
),
(
  'detect-multi-account-farms-user-id-ip-graph',
  'Как выявлять multi-account фермы через user_id linkage и IP graph heuristics',
  'kak-vyyavlyat-multi-account-fermy-cherez-user-id-linkage-i-ip-graph-heuristics',
  '<p>Графовый подход к anti-abuse: выявление связанных аккаунтов по user_id/IP/ASN и поведенческим паттернам.</p>',
  '<h2>Как выявлять multi-account фермы через user_id linkage и IP graph heuristics</h2><p>Multi-account фермы создают бонус-абьюз, партнерский фрод и повторные атаки. Событийные проверки в одном запросе часто не видят сетевую структуру злоупотреблений.</p><p>Используйте граф связей: узлы user_id, IP, ASN, device, payment token; ребра по временным окнам; веса по частоте и риску. Это помогает точнее отбирать кейсы для ручной проверки и автоматических действий.</p>'
),
(
  'kyc-step-up-triggers-geoip-verification',
  'KYC step-up триггеры: когда GeoIP должен запускать дополнительную верификацию',
  'kyc-step-up-triggery-kogda-geoip-dolzhen-zapuskat-dopolnitelnuyu-verifikatsiyu',
  '<p>Когда включать step-up KYC на основе GeoIP и antifraud-сигналов, чтобы защитить операции и сохранить конверсию.</p>',
  '<h2>KYC step-up триггеры: когда GeoIP должен запускать дополнительную верификацию</h2><p>Step-up проверка должна включаться выборочно, а не для всех. Иначе растут потери конверсии и нагрузка на поддержку.</p><p>Сильные триггеры: impossible travel с высокой confidence, новая страна на payout, proxy/VPN при изменении платежных данных, burst неудачных логинов. Внедряйте cooldown после успешной проверки и калибруйте пороги по сегментам.</p>'
),
(
  'false-positive-reduction-antifraud-adaptive-thresholds',
  'Снижение false positive в antifraud: allowlist, confidence bands и adaptive thresholds',
  'snizhenie-false-positive-v-antifraud-allowlist-confidence-bands-i-adaptive-thresholds',
  '<p>Практики снижения ложных срабатываний: confidence bands, адаптивные пороги и управляемые allowlist-процессы.</p>',
  '<h2>Снижение false positive в antifraud: allowlist, confidence bands и adaptive thresholds</h2><p>False positive напрямую бьют по выручке и доверию клиентов. Цель зрелого antifraud — блокировать злоупотребления без лишнего трения для честных пользователей.</p><p>Используйте confidence bands, разные пороги по сегментам и аудитируемый allowlist с владельцем и сроком действия. Измеряйте результат через false-positive rate, approval rate и влияние на конверсию.</p>'
);

SET @ru_common_long_html = CONCAT(
  '<h3>Практическая архитектура внедрения в B2B</h3>',
  '<p>Ниже приведен единый подход, который можно применить в ecommerce, fintech, SaaS и marketplace-проектах. Он построен так, чтобы командам разработки, аналитики и антифрода было удобно работать с одним и тем же контекстом риска: IP, ASN, география, поведение и история пользователя. Главная идея в том, что API-ответ не должен жить отдельно от бизнес-решения: каждое поле должно вести к конкретному действию в воронке.</p>',
  '<p>В production хорошо работает модель с тремя слоями: слой данных, слой решений и слой операционного контроля. Слой данных отвечает за качество сигналов: корректный real client IP, стабильный user_id, валидное время события, устранение дублей и нормализация географии. Слой решений отвечает за policy-правила: пороги, исключения, step-up-триггеры, правила блокировки. Слой операционного контроля отвечает за метрики: false positive, conversion impact, chargeback delta, время ручной проверки и скорость реакции на инциденты.</p>',
  '<h3>Кейсы, где это дает максимальный эффект</h3>',
  '<p><strong>Кейс 1: Защита входа в аккаунт.</strong> При логине из новой страны не всегда нужен блок. В большинстве B2B-сценариев лучше применить ступенчатую логику: сначала low-friction challenge, затем step-up, и только на высоком score полный запрет действия. Это сохраняет UX для легитимных пользователей, которые реально путешествуют, но резко снижает вероятность account takeover.</p>',
  '<p><strong>Кейс 2: Checkout и платежи.</strong> Для платежных потоков важно учитывать не только текущую географию, но и контекст пользователя: возраст аккаунта, историю успешных оплат, тип устройства, частоту retry и смену платежных реквизитов. Комбинация гео-сигналов и поведенческих признаков обычно дает лучшее качество, чем жесткое правило “страна не совпала = отказ”.</p>',
  '<p><strong>Кейс 3: Изменение критичных данных.</strong> Смена email, пароля, телефона, 2FA-настроек или payout-реквизитов должна иметь отдельные risk-пороги. То, что допустимо для чтения каталога, недопустимо для изменения платежных данных. Разделение порогов по типу действия уменьшает риск компрометации аккаунтов без массовых ложных блокировок.</p>',
  '<h3>Сравнение стратегий: правила против скоринга</h3>',
  '<p><strong>Статические правила</strong> быстро внедряются и легко объясняются, но плохо адаптируются к новым сценариям атак и часто создают избыточные отказы при росте трафика. <strong>Скоринговая модель</strong> гибче и точнее, но требует дисциплины в данных и регулярной калибровки. На практике лучший результат дает гибрид: обязательные hard-rules для критичных паттернов плюс weighted scoring для большинства событий.</p>',
  '<p>Например, hard-rule можно включать для комбинации “proxy_suspected=true + высокая скорость перемещения + свежий аккаунт + попытка изменения payout”. Для остальных сценариев используйте score-bands: allow, monitor, step-up, manual review, block. Такой дизайн помогает снизить шум для аналитиков и поддерживать прозрачность решений для бизнеса.</p>',
  '<h3>Пример API-запроса с точечной фильтрацией</h3>',
  '<pre class=\"code-line language-bash\"><code class=\"language-bash\">curl -G \"https://apigeoip.ru/api/\" \\',
  '  --data-urlencode \"ip=8.8.8.8\" \\',
  '  --data-urlencode \"user_id=user_9fd4c3b1\" \\',
  '  --data-urlencode \"fields=geo.country,geo.country_enrichment.tld,antifraud.risk_score,antifraud.confidence,antifraud.signals\" \\',
  '  --data-urlencode \"crypto=BTC,ETH\" \\',
  '  -H \"Authorization: Bearer YOUR_API_KEY\"</code></pre>',
  '<p>Точечная фильтрация особенно полезна в high-load сервисах: вы получаете только нужные поля, снижаете размер ответа и упрощаете трассировку. Для мобильных потоков и latency-чувствительных маршрутов это дает заметный операционный выигрыш.</p>',
  '<h3>Пример принятия решения в backend</h3>',
  '<pre class=\"code-line language-js\"><code class=\"language-js\">function decideRisk(ctx, actionType) {',
  '  const af = ctx.antifraud || {};',
  '  const score = Number(af.risk_score || 0);',
  '  const confidence = Number(af.confidence || 0);',
  '  const proxy = Boolean(af.proxy_suspected);',
  '  const signals = Array.isArray(af.signals) ? af.signals : [];',
  '',
  '  let weighted = score;',
  '  if (confidence &lt; 40) weighted -= 5;',
  '  if (proxy) weighted += 15;',
  '  if (signals.includes(\"impossible_travel\")) weighted += 20;',
  '',
  '  if (actionType === \"payout\" && weighted &gt;= 70) return \"step_up\";',
  '  if (actionType === \"account_change\" && weighted &gt;= 65) return \"step_up\";',
  '  if (weighted &gt;= 85) return \"block\";',
  '  if (weighted &gt;= 60) return \"manual_review\";',
  '  return \"allow\";',
  '}</code></pre>',
  '<p>Важно: решение должно быть explainable. Сохраняйте в логах не только итоговое действие, но и причины: какие сигналы сработали, какие пороги превысили и почему выбран конкретный маршрут.</p>',
  '<h3>Python-пример для аналитического пайплайна</h3>',
  '<pre class=\"code-line language-python\"><code class=\"language-python\">def risk_band(score: int) -> str:',
  '    if score &gt;= 85:',
  '        return \"block\"',
  '    if score &gt;= 60:',
  '        return \"review\"',
  '    if score &gt;= 40:',
  '        return \"step_up\"',
  '    return \"allow\"',
  '',
  'def enrich_event(event: dict, api_payload: dict) -> dict:',
  '    af = (api_payload or {}).get(\"antifraud\", {})',
  '    event[\"risk_score\"] = int(af.get(\"risk_score\") or 0)',
  '    event[\"risk_band\"] = risk_band(event[\"risk_score\"])',
  '    event[\"proxy_suspected\"] = bool(af.get(\"proxy_suspected\"))',
  '    event[\"signals\"] = af.get(\"signals\") or []',
  '    return event</code></pre>',
  '<p>Такой код полезен для ETL и BI: вы заранее приводите события к унифицированному виду и строите стабильные отчеты по качеству защиты и влиянию на конверсию.</p>',
  '<h3>SQL-паттерн для ретроспективного анализа</h3>',
  '<pre class=\"code-line language-sql\"><code class=\"language-sql\">SELECT',
  '  decision,',
  '  COUNT(*) AS events,',
  '  ROUND(AVG(risk_score), 2) AS avg_score,',
  '  SUM(CASE WHEN chargeback = 1 THEN 1 ELSE 0 END) AS chargeback_events',
  'FROM antifraud_event_log',
  'WHERE created_at &gt;= NOW() - INTERVAL 30 DAY',
  'GROUP BY decision',
  'ORDER BY events DESC;</code></pre>',
  '<p>Ретроспективный анализ помогает понять, где политика слишком жесткая или слишком мягкая. Если в зоне review слишком много “чистых” событий, пороги можно сместить. Если в зоне allow растет доля инцидентов, нужно усилить веса отдельных сигналов.</p>',
  '<h3>Сравнение по стекам: Laravel, Django/FastAPI, Node.js</h3>',
  '<p><strong>Laravel</strong> удобен для middleware-обогащения запроса и policy в контроллерах. <strong>Django/FastAPI</strong> хорошо подходят для строгих trust boundaries и аналитических пайплайнов на Python. <strong>Node.js</strong> эффективен в real-time сценариях с минимальной задержкой. При этом бизнес-логика риска должна быть одинаковой независимо от стека, иначе метрики сравнивать сложно.</p>',
  '<p>Рекомендуется держать “policy contract” в одном месте: фиксированные названия risk-band, список причин step-up, минимальный набор полей для аудита, формат логов. Это упрощает масштабирование команды и ускоряет запуск новых сервисов.</p>',
  '<h3>Операционные рекомендации для снижения false positive</h3>',
  '<ul>',
  '<li>Внедряйте rollout поэтапно: monitor-only, затем soft-action, потом enforcement.</li>',
  '<li>Разделяйте пороги по сегментам: новые пользователи, VIP, B2B-аккаунты, регионы.</li>',
  '<li>Используйте allowlist с TTL, владельцем и обязательным аудитом изменений.</li>',
  '<li>Проверяйте влияние на бизнес: CR, approval rate, средний чек, время поддержки.</li>',
  '<li>Регулярно пересматривайте правила после релизов продукта и изменений трафика.</li>',
  '</ul>',
  '<p>В зрелой системе антифрод не должен быть “черным ящиком”. Прозрачность, наблюдаемость и регулярная калибровка дают больше эффекта, чем попытка один раз “идеально” настроить модель и не возвращаться к ней.</p>',
  '<h3>Частые ошибки внедрения</h3>',
  '<ol>',
  '<li>Нестабильный user_id и потеря связи между событиями.</li>',
  '<li>Сырые IP-данные без trusted proxy правил.</li>',
  '<li>Одинаковые пороги для всех типов действий.</li>',
  '<li>Отсутствие explainability в логах и тикетах аналитиков.</li>',
  '<li>Отсутствие связи антифрод-метрик с коммерческими KPI.</li>',
  '</ol>',
  '<h3>Развернутые сценарии и сравнения подходов</h3>',
  '<p><strong>Сценарий A: международный SaaS с распределенной командой клиентов.</strong> Если применять жесткое правило по стране, вы получите много false positive, потому что легитимные пользователи часто входят из разных регионов. Более эффективен подход с historical baseline по user_id: сравнивайте новое событие не с “идеальной страной”, а с профильной историей аккаунта, временем активности и типом действия.</p>',
  '<p><strong>Сценарий B: marketplace с высоким объемом регистраций.</strong> На этапе signup полезно применять мягкие пороги и собирать telemetry, а при первых финансовых действиях усиливать контроль. Такой “прогрессивный риск” дает лучшее соотношение роста и безопасности, чем тотальная проверка всех новых регистраций одинаковым уровнем строгости.</p>',
  '<p><strong>Сценарий C: fintech-продукт с payout.</strong> Для вывода средств важна комбинация двух факторов: накопленный trust пользователя и текущее отклонение от профиля. Если trust высокий, но текущий риск средний, чаще достаточно step-up. Если trust низкий и одновременно срабатывают несколько сильных сигналов, рациональнее блокировать до ручной проверки.</p>',
  '<p>Сравнение стратегий по стоимости владения: простые правила дешевле на старте, но при росте трафика растет цена ручной обработки, так как в review уходит много “чистых” событий. Скоринг требует больше дисциплины на старте, зато масштабируется лучше и снижает операционные расходы за счет более точной приоритизации. Гибридная модель обычно дает наилучший TCO в горизонте 6-12 месяцев.</p>',
  '<p>Отдельно стоит сравнить подходы к данным. Вариант “храним только итоговый score” удобен, но плохо подходит для аудита и ретроспективы. Вариант “храним score + сигналы + решение + rule_id + confidence” лучше для объяснимости, повторного обучения и юридической прозрачности. Для B2B-продуктов второй вариант почти всегда предпочтительнее.</p>',
  '<p>Для команд, которые ведут A/B-тесты, полезно разделять потоки на control и treatment по policy-версии. Тогда можно объективно измерить эффект каждого изменения: насколько снизились инциденты, как изменилась конверсия, выросло или уменьшилось время обработки кейсов. Без этого антифрод часто превращается в набор субъективных правок без статистического подтверждения.</p>',
  '<p>Рекомендованный ритм улучшений: еженедельный разбор инцидентов, двухнедельная калибровка порогов, ежемесячный пересмотр весов сигналов и ежеквартальный аудит качества данных. Такой цикл помогает держать модель в рабочем состоянии даже при сезонных всплесках трафика, маркетинговых акциях и изменениях продуктовой воронки.</p>',
  '<p>Если бизнес работает в нескольких регионах, настройте региональные профили риска вместо единой глобальной шкалы. В одних странах уровень анонимизирующего трафика выше, в других чаще встречаются резкие ASN-переходы из-за мобильных операторов. Региональная адаптация снижает ложные срабатывания и делает решения более справедливыми для пользователей.</p>',
  '<p>Для enterprise-клиентов полезно включать в отчеты не только стандартные метрики, но и “поясняющие” показатели: доля решений с высоким confidence, доля step-up с успешным завершением, частота повторных попыток после блокировки, вклад каждого сигнала в общий риск. Эти данные помогают обсуждать антифрод на уровне бизнеса, а не только на уровне технических логов.</p>',
  '<p>В итоге зрелый антифрод-процесс выглядит как непрерывная инженерная система: качественные данные, прозрачные решения, измеримые KPI и регулярная обратная связь. Именно такая модель дает устойчивый коммерческий эффект в долгую, а не только кратковременное снижение инцидентов.</p>',
  '<p>Если устранить эти ошибки, антифрод-система быстрее начинает давать измеримый результат: меньше потерь от фрода, меньше ручной рутины, выше качество клиентского опыта.</p>',
  '<h3>Смотрите также</h3>',
  '<ul class=\"related-links\">',
  '<li><a href=\"/examples/article/kak-postroit-detekt-impossible-travel-s-geoip-i-user-id/\">Impossible travel с GeoIP и user_id</a></li>',
  '<li><a href=\"/examples/article/arkhitektura-fraud-scoring-ip-reputatsiya-geo-anomalii-i-povedencheskie-signaly/\">Архитектура fraud scoring</a></li>',
  '<li><a href=\"/examples/article/geoip-v-laravel-middleware-dlya-risk-aware-avtorizatsii-checkout-i-izmeneniy-akkaunta/\">GeoIP в Laravel</a></li>',
  '<li><a href=\"/examples/article/geoip-v-django-i-fastapi-trusted-ip-antifraud-hooks-i-audit/\">GeoIP в Django и FastAPI</a></li>',
  '<li><a href=\"/examples/article/geoip-v-nodejs-express-i-nestjs-real-time-risk-gates-pered-login-i-payment/\">GeoIP в Node.js Express и NestJS</a></li>',
  '<li><a href=\"/examples/article/playbook-po-snizheniyu-chargeback-gde-vklyuchat-geo-proverki-v-payment-flow/\">Playbook по снижению chargeback</a></li>',
  '<li><a href=\"/examples/article/kak-vyyavlyat-multi-account-fermy-cherez-user-id-linkage-i-ip-graph-heuristics/\">Выявление multi-account ферм</a></li>',
  '<li><a href=\"/examples/article/kyc-step-up-triggery-kogda-geoip-dolzhen-zapuskat-dopolnitelnuyu-verifikatsiyu/\">KYC step-up триггеры</a></li>',
  '<li><a href=\"/examples/article/snizhenie-false-positive-v-antifraud-allowlist-confidence-bands-i-adaptive-thresholds/\">Снижение false positive</a></li>',
  '</ul>'
);

UPDATE tmp_ru_examples_map
SET ru_content = CONCAT(ru_content, @ru_common_long_html);

-- Update already existing RU rows that still have EN slug for mapped records
UPDATE examples_articles ru
JOIN tmp_ru_examples_map m
  ON ru.lang_code = 'ru'
 AND CONVERT(ru.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(m.en_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
SET
  ru.title = m.ru_title,
  ru.slug = m.ru_slug,
  ru.excerpt_html = m.ru_excerpt,
  ru.content_html = m.ru_content,
  ru.updated_at = NOW();

-- Refresh already existing RU rows by RU slug as well (idempotent content update)
UPDATE examples_articles ru
JOIN tmp_ru_examples_map m
  ON ru.lang_code = 'ru'
 AND CONVERT(ru.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(m.ru_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
SET
  ru.title = m.ru_title,
  ru.excerpt_html = m.ru_excerpt,
  ru.content_html = m.ru_content,
  ru.updated_at = NOW();

-- Insert missing RU rows from EN rows
INSERT INTO examples_articles
(domain_host, lang_code, title, slug, excerpt_html, content_html, author_name, sort_order, is_published, published_at, created_at, updated_at)
SELECT
  en.domain_host,
  'ru' AS lang_code,
  m.ru_title,
  m.ru_slug,
  m.ru_excerpt,
  m.ru_content,
  COALESCE(en.author_name, 'GeoIP Team') AS author_name,
  en.sort_order,
  en.is_published,
  COALESCE(en.published_at, NOW()) AS published_at,
  NOW(),
  NOW()
FROM examples_articles en
JOIN tmp_ru_examples_map m
  ON CONVERT(m.en_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(en.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN examples_articles ru
  ON (ru.domain_host <=> en.domain_host)
 AND COALESCE(ru.lang_code, 'en') = 'ru'
 AND CONVERT(ru.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(m.ru_slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
WHERE COALESCE(en.lang_code, 'en') = 'en'
  AND ru.id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_ru_examples_map;
