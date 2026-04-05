-- Expand and unique service descriptions (RU + EN)
-- Apply after base services seed/import

UPDATE public_services
SET excerpt_html='Проектирую SEO-каталог как канал продаж: структура разделов, посадочные под спрос, фильтры и коммерческие сценарии перехода в заявку.',
    content_html='<p>Каталог становится эффективным только тогда, когда он одновременно удобен для пользователя и предсказуем для поисковых систем. Я выстраиваю структуру категорий, карточек и фильтров так, чтобы каждая страница закрывала понятный интент и приводила целевой трафик, а не случайные переходы.</p><p>Отдельно прорабатываю SEO-логику: индексируемые и неиндексируемые комбинации фильтров, каноникал, шаблоны мета-тегов, внутреннюю перелинковку и правила пагинации. Это позволяет масштабировать ассортимент без размножения дублей и без потери управляемости в аналитике.</p><p>На коммерческом уровне усиливаю каталог блоками доверия, сравнением решений, быстрыми формами и CTA по этапам воронки. В итоге каталог работает как инструмент роста: вы видите, какие разделы приносят лиды, и можете системно усиливать их по данным.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='katalog-produkta-s-filtrami-i-seo';

UPDATE public_services
SET excerpt_html='Автоматизирую партнерский канал в Telegram: онбординг, верификация, статусы, уведомления и управляемая операционная воронка.',
    content_html='<p>Когда партнерская сеть растет, ручное сопровождение быстро становится узким местом: теряются заявки, замедляется обратная связь, снижается прозрачность. Я внедряю Telegram-бота, который берет на себя регистрацию, сбор необходимых данных, первичную верификацию и маршрутизацию по ролям.</p><p>Для команды настраиваю статусы этапов, систему уведомлений и контроль SLA. Менеджер видит текущее состояние каждого партнера, историю действий и причины задержек. Это сокращает хаос в коммуникациях и позволяет масштабировать канал без пропорционального роста операционных затрат.</p><p>В результате партнерский контур становится прогнозируемым: быстрее запуск новых партнеров, меньше потерь на этапах, выше качество исполнения обязательств и лучше управляемость по метрикам.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='telegram-bot-dlya-partnerskoj-seti';

UPDATE public_services
SET excerpt_html='Организую сервисный прием заявок в Telegram с приоритизацией, SLA-контролем и прозрачной эскалацией в команду.',
    content_html='<p>Сервисная поддержка теряет скорость, когда заявки поступают в разрозненные каналы без единого стандарта. Я строю Telegram-бота, который собирает структурированный контекст обращения, определяет приоритет и автоматически направляет кейс в нужную очередь.</p><p>Для руководителя и исполнителей настраиваются метрики времени реакции, статусы решения и контроль просрочек. Это позволяет не просто принимать обращения, а реально управлять качеством поддержки и загрузкой команды в режиме реального времени.</p><p>Итогом становится стабильный операционный процесс: меньше «потерянных» обращений, быстрее закрытие инцидентов и предсказуемый клиентский опыт без ручного хаоса.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='telegram-bot-dlya-servisnyh-zayavok';

UPDATE public_services
SET excerpt_html='Проектирую SaaS-админку и рольовую модель доступа: безопасные операции, права, аудит действий и контроль критичных изменений.',
    content_html='<p>С ростом SaaS-продукта административный контур становится критически важным: ошибочные права доступа и непрозрачные действия могут стоить бизнесу денег и репутации. Я проектирую админ-панель, где роли, уровни доступа и бизнес-ограничения формализованы и проверяемы.</p><p>Отдельное внимание уделяю безопасным операциям: подтверждение критичных действий, логирование, аудит событий, ограничение рисковых сценариев. Это снижает вероятность инцидентов и упрощает расследование, если нестандартная ситуация все же произошла.</p><p>На выходе команда получает админ-контур, который масштабируется вместе с продуктом, а не блокирует рост. Операционные задачи выполняются быстрее, а контроль качества и безопасности становится системным.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='saas-adminka-i-rolevaya-model';

UPDATE public_services
SET excerpt_html='Внедряю продуктовую аналитику SaaS: события, KPI, дашборды и причинно-следственные связи для решений по росту.',
    content_html='<p>Без правильно собранной аналитики SaaS-команда двигается на интуиции. Я строю событийную модель, связывающую поведение пользователя с бизнес-метриками: активацией, удержанием, доходом, оттоком и ключевыми этапами воронки.</p><p>Далее формируется набор дашбордов для продуктовой, маркетинговой и операционной команд. Каждый показатель закрепляется за конкретным решением: что масштабируем, что отключаем, где тестируем гипотезы. Это сокращает время от наблюдения к действию.</p><p>В результате аналитика становится рабочим инструментом управления ростом: понятные сигналы, прозрачные приоритеты и измеримые изменения в юнит-экономике.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='saas-analitika-i-produktovye-metriki';

UPDATE public_services
SET excerpt_html='Реализую надежный webhook-контур: retry, идемпотентность, очереди, дедупликация и наблюдаемость доставки событий.',
    content_html='<p>Интеграции через webhooks часто ломаются не в «идеальных» сценариях, а под нагрузкой и при нестабильной сети. Я внедряю слой надежности, который гарантирует корректную обработку событий даже при временных сбоях внешних систем.</p><p>В архитектуру входят ретраи с контролем лимитов, идемпотентность, дедупликация, очередь обработки и журналы состояния. Благодаря этому команда всегда понимает, какое событие доставлено, где возникла проблема и как быстро ее устранить.</p><p>Итог — предсказуемая интеграционная инфраструктура: меньше инцидентов, быстрее диагностика, выше доверие к данным между связанными системами.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='webhook-integracii-s-garantiej-dostavki';

UPDATE public_services
SET excerpt_html='Создаю единый API-шлюз для внешних клиентов и партнеров: доступ, лимиты, версионирование и централизованная политика безопасности.',
    content_html='<p>Когда внешние интеграции растут, прямые подключения к внутренним сервисам становятся рискованными и дорогими в сопровождении. Я выношу внешний контур в API gateway: единые правила аутентификации, маршрутизации, лимитирования и версионирования.</p><p>Это дает управляемость по SLA, безопасность и предсказуемость для партнеров. Команда получает один контрольный слой вместо множества разрозненных входов, а изменения в backend можно проводить без разрушения внешних контрактов.</p><p>Результат — стабильная платформа для масштабирования партнерской экосистемы: меньше интеграционных конфликтов, проще governance и быстрее подключение новых клиентов.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='edinyj-api-shlyuz-dlya-vneshnih-sistem';

UPDATE public_services
SET excerpt_html='Провожу технический due diligence перед сделкой: архитектура, код, процессы, риски и реалистичная оценка стоимости изменений.',
    content_html='<p>Перед инвестициями или приобретением важно понимать не презентацию, а реальное техническое состояние актива. Я провожу аудит архитектуры, качества кода, зрелости процессов, надежности инфраструктуры и операционных рисков.</p><p>Отчет фокусируется на практических выводах: где критичные зоны, сколько стоят исправления, какие риски влияют на сроки и unit-экономику, что обязательно закрыть до масштабирования. Это дает предметную основу для переговоров и планирования пост-сделочных действий.</p><p>Вы получаете не абстрактный «чеклист», а управленческий документ для принятия решений: обоснованную оценку рисков, стоимости и последовательности изменений.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='tehdyudilidzhens-pered-sdelkoj';

UPDATE public_services
SET excerpt_html='Аудит инженерных процессов и команды: нахожу узкие места delivery и формирую план улучшений с измеримым эффектом.',
    content_html='<p>Проблемы скорости и качества релизов чаще всего связаны не с одним человеком, а с системными разрывами в процессе. Я анализирую полный цикл delivery: планирование, разработку, тестирование, релизы, обратную связь и ответственность ролей.</p><p>По итогам формируется практический roadmap: какие изменения дают быстрый эффект, какие требуют этапного внедрения, как измерять прогресс и закреплять новую рабочую дисциплину. Это помогает команде выйти из «пожарного режима» и восстановить предсказуемость.</p><p>Результат — более стабильные релизы, меньше регрессий и более высокая скорость доставки изменений в продукт без потери качества.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='audit-komandy-i-inzhenernyh-processov';

UPDATE public_services
SET excerpt_html='Внедряю AI-ассистента для внутренней базы знаний: быстрый поиск ответов, единый контекст и снижение нагрузки на экспертов.',
    content_html='<p>С ростом команды критичные знания рассеиваются по документам, чатам и личному опыту сотрудников. Я внедряю AI-ассистента, который работает с вашей внутренней базой и выдает релевантные ответы с учетом роли пользователя и контекста запроса.</p><p>Отдельно настраиваются правила доступа, актуализация источников и контроль качества ответов. Это важно, чтобы ассистент не только «отвечал быстро», но и оставался надежным инструментом для операционных и продуктовых команд.</p><p>В результате снижается нагрузка на ключевых экспертов, ускоряется онбординг и повышается скорость выполнения типовых задач по всей организации.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='ai-assistent-dlya-vnutrennej-bazy-znanij';

UPDATE public_services
SET excerpt_html='AI-модерация входящих обращений: классификация, приоритизация и интеллектуальная маршрутизация для поддержки и продаж.',
    content_html='<p>Когда поток обращений растет, ручная сортировка становится медленной и нестабильной. Я внедряю AI-слой, который автоматически определяет тематику, срочность и тип запроса, а затем направляет его в правильную очередь с нужным приоритетом.</p><p>Система может дополнять карточку обращения структурированным контекстом и предлагать шаблон ответа, сокращая время реакции первой линии. При этом сохраняется контроль качества и возможность ручной корректировки.</p><p>Итог — более ровный SLA, меньше перегрузки команды и более предсказуемая обработка входящего потока в критичных точках бизнеса.</p>',
    updated_at=NOW()
WHERE lang_code='ru' AND slug='ai-moderaciya-vhodyashih-obrashchenij';

UPDATE public_services
SET excerpt_html='Build a structured documentation portal with searchable architecture, role-aware navigation and long-term content governance.',
    content_html='<p>I design documentation hubs as operational infrastructure, not as static pages. Information architecture, section hierarchy, and navigation patterns are mapped to real support and onboarding flows so users can find the right answer quickly.</p><p>The implementation includes search strategy, cross-linking conventions, update workflows, and version visibility. This keeps content maintainable as the product evolves and prevents knowledge decay across teams.</p><p>The result is a documentation system that reduces support pressure, accelerates adoption, and improves consistency in how customers and internal teams execute routine tasks.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='knowledge-base-and-documentation-portal';

UPDATE public_services
SET excerpt_html='Build SEO-ready product catalog architecture with faceted navigation, canonical control and conversion-focused landing patterns.',
    content_html='<p>I architect catalogs to satisfy both user intent and search engine constraints. Category logic, listing depth, and filter behavior are designed to maximize discoverability without generating index bloat.</p><p>The delivery covers canonical strategy, pagination rules, index/noindex policy, metadata templates, and internal linking for high-value intent clusters. This creates a scalable foundation for organic growth as inventory expands.</p><p>On the commercial side, I structure category pages with trust and decision-support blocks that improve lead quality and conversion efficiency, turning catalog traffic into measurable pipeline impact.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='product-catalog-with-seo-facets';

UPDATE public_services
SET excerpt_html='Automate partner operations in Telegram: onboarding, verification, status orchestration and scalable communication workflows.',
    content_html='<p>I build Telegram partner bots that handle intake, qualification, and workflow coordination from first registration through recurring execution. This replaces fragmented manual communication with a controlled operating model.</p><p>The system tracks lifecycle stages, sends role-based notifications, and keeps managers informed about bottlenecks, SLA risk, and next actions. Teams gain operational visibility without adding process overhead.</p><p>The outcome is faster partner activation, reduced coordination friction, and a more predictable partner channel that can scale without proportional staffing growth.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='telegram-bot-for-partner-operations';

UPDATE public_services
SET excerpt_html='Implement Telegram service-desk intake with triage, ownership routing and SLA-driven operational control.',
    content_html='<p>I design service-request bots that collect actionable context upfront and classify urgency before handoff. This removes ambiguity in the first-response stage and helps teams focus on high-impact issues first.</p><p>Each request is routed with ownership and status tracking, enabling transparent queue management and measurable response discipline. Escalation paths are explicit, so complex incidents do not stall in communication loops.</p><p>The result is a cleaner support operation with lower response variance, faster resolution cycles, and higher confidence in service quality under growing inbound volume.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='telegram-bot-for-service-requests';

UPDATE public_services
SET excerpt_html='Design a scalable SaaS admin layer with role-based access, secure operations and full action auditability.',
    content_html='<p>I build admin systems that keep control as products scale. Access models, role boundaries, and operational permissions are aligned with real team responsibilities and risk surfaces.</p><p>Critical actions are protected by explicit policies and auditable event trails, reducing the chance of accidental high-impact changes. This creates a safer operating environment for support and product teams.</p><p>You get a back-office that supports growth, improves execution speed, and preserves governance without introducing unnecessary friction for day-to-day operations.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='saas-admin-layer-and-role-model';

UPDATE public_services
SET excerpt_html='Set up product analytics for SaaS with event taxonomy, KPI dashboards and evidence-driven growth decisions.',
    content_html='<p>I implement analytics architecture that maps user behavior to business outcomes: activation, retention, churn and revenue contribution. Events are defined with governance so data remains comparable over time.</p><p>Dashboards are tailored for product, marketing, and operations teams with clear decision context, not vanity metrics. This shortens the loop from signal detection to roadmap action.</p><p>The result is higher strategic clarity, better prioritization, and measurable improvement in growth execution quality across teams.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='saas-product-analytics-setup';

UPDATE public_services
SET excerpt_html='Engineer webhook reliability with retries, idempotency, deduplication and delivery observability.',
    content_html='<p>I build webhook processing pipelines that remain stable under provider instability, network fluctuation, and burst traffic. Reliability controls ensure events are not lost and are not applied multiple times.</p><p>The delivery includes retry strategy, dead-letter handling, deduplication logic, and traceable processing states for diagnostics. Teams can quickly identify and recover from integration issues.</p><p>You gain predictable data flow between systems, fewer incident escalations, and stronger confidence in automation that depends on event consistency.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='webhook-reliability-architecture';

UPDATE public_services
SET excerpt_html='Implement a unified API gateway for partner traffic with policy control, security and scalable integration governance.',
    content_html='<p>I design API gateway layers that standardize external access patterns across services. Authentication, rate limiting, routing and versioning are centralized to reduce integration drift and operational risk.</p><p>This architecture protects internal systems from inconsistent partner behavior while preserving a stable developer experience for external consumers. Governance becomes enforceable rather than aspirational.</p><p>The result is safer partner expansion, cleaner contract management, and faster onboarding of new integrations without destabilizing core backend services.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='unified-api-gateway-for-partners';

UPDATE public_services
SET excerpt_html='Run technical due diligence before acquisition or investment with architecture, code and operational risk validation.',
    content_html='<p>I provide an independent technical risk profile covering architecture quality, codebase maintainability, delivery maturity and infrastructure resilience. The focus is on practical decision impact, not generic scoring.</p><p>Findings are translated into remediation cost and timeline implications so stakeholders can model realistic post-deal scenarios. This reduces the chance of hidden technical liabilities after capital commitment.</p><p>You receive a decision-grade report with clear risk ranking, priority actions and execution implications for growth, integration, and long-term platform economics.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='technical-due-diligence-before-acquisition';

UPDATE public_services
SET excerpt_html='Audit engineering process and team execution to remove delivery bottlenecks and improve release reliability.',
    content_html='<p>I assess the full delivery system: planning discipline, implementation flow, testing quality, release mechanics, incident response and ownership clarity. The goal is to identify systemic causes, not isolated symptoms.</p><p>The output is a phased improvement plan with measurable checkpoints and expected business effect per change. Teams know what to fix first and how to validate impact quickly.</p><p>The result is more predictable delivery, fewer regressions, and stronger throughput without sacrificing product quality or increasing organizational chaos.</p>',
    updated_at=NOW()
WHERE lang_code='en' AND slug='engineering-process-and-team-audit';
