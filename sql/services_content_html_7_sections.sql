-- Rebuild service detail content into a consistent 7-section format.
-- DB: portcore
-- Table: public_services
-- Safety: make backup first.

CREATE TABLE IF NOT EXISTS public_services_backup_content_20260226 AS
SELECT * FROM public_services;

UPDATE public_services
SET content_html = CONCAT(
    CASE
        WHEN lang_code = 'ru' THEN CONCAT(
            '<section class="svc-block"><h2>1. Контекст и задача</h2><p>',
            COALESCE(NULLIF(excerpt_html, ''), 'Эта услуга направлена на достижение измеримого бизнес-результата и снижение операционных рисков.'),
            '</p></section>',
            '<section class="svc-block"><h2>2. Что вы получаете на выходе</h2><ul>',
            '<li>Прозрачную архитектуру решения под ваши цели и ограничения.</li>',
            '<li>План внедрения с этапами, сроками и зонами ответственности.</li>',
            '<li>Набор контрольных метрик для оценки прогресса и эффекта.</li>',
            '</ul></section>',
            '<section class="svc-block"><h2>3. Схема реализации</h2><p>Работа идет по итерациям: аудит текущего состояния, проектирование, внедрение, контроль качества и стабилизация. Для услуги в группе <strong>',
            COALESCE(NULLIF(service_group, ''), 'general'),
            '</strong> фиксируются точки интеграции и контрольные события.</p></section>',
            '<section class="svc-block"><h2>4. Внедрение и операционная поддержка</h2><p>Каждый этап завершается проверкой качества и результатом, который можно использовать сразу. При необходимости подключается сопровождение, чтобы ускорить выход на стабильный режим.</p></section>',
            '<section class="svc-block"><h2>5. Пояснительная схема</h2><ol>',
            '<li><strong>Вход:</strong> цели бизнеса, текущее состояние, ограничения.</li>',
            '<li><strong>Трансформация:</strong> архитектурные и продуктовые решения.</li>',
            '<li><strong>Выход:</strong> внедренные изменения, метрики, управляемый цикл улучшений.</li>',
            '</ol></section>',
            '<section class="svc-block"><h2>6. Потенциальные результаты для бизнеса</h2><ul>',
            '<li>Рост конверсии и качества входящих обращений.</li>',
            '<li>Снижение потерь времени на ручные операции.</li>',
            '<li>Повышение предсказуемости результатов по воронке и выручке.</li>',
            '</ul></section>',
            '<section class="svc-block"><h2>7. Следующий шаг</h2><p>Начинаем с короткой диагностики и фиксируем приоритеты на первый этап. После этого формируем дорожную карту и переходим к внедрению без лишних итераций.</p></section>'
        )
        ELSE CONCAT(
            '<section class="svc-block"><h2>1. Context and objective</h2><p>',
            COALESCE(NULLIF(excerpt_html, ''), 'This service is designed to deliver measurable business impact with predictable execution.'),
            '</p></section>',
            '<section class="svc-block"><h2>2. What you get</h2><ul>',
            '<li>A practical architecture aligned with your goals and constraints.</li>',
            '<li>A phased implementation plan with ownership and delivery checkpoints.</li>',
            '<li>Clear metrics to track progress and business impact.</li>',
            '</ul></section>',
            '<section class="svc-block"><h2>3. Delivery blueprint</h2><p>Execution follows short iterations: baseline assessment, design, implementation, quality control and stabilization. For the <strong>',
            COALESCE(NULLIF(service_group, ''), 'general'),
            '</strong> service stream, integration points and control events are explicitly defined.</p></section>',
            '<section class="svc-block"><h2>4. Implementation and operations</h2><p>Each phase ends with a deployable output. Optional support is included when needed to ensure stable post-launch operation and faster value realization.</p></section>',
            '<section class="svc-block"><h2>5. Explanatory scheme</h2><ol>',
            '<li><strong>Input:</strong> business goals, current baseline, constraints.</li>',
            '<li><strong>Transformation:</strong> architecture and product decisions.</li>',
            '<li><strong>Output:</strong> shipped improvements, tracked metrics and repeatable optimization loop.</li>',
            '</ol></section>',
            '<section class="svc-block"><h2>6. Potential business outcomes</h2><ul>',
            '<li>Higher conversion quality and stronger inbound pipeline.</li>',
            '<li>Lower operational friction and reduced manual overhead.</li>',
            '<li>More predictable performance across growth and delivery metrics.</li>',
            '</ul></section>',
            '<section class="svc-block"><h2>7. Next step</h2><p>We start with a focused diagnostic session, align priorities for phase one, then move to implementation with clear ownership and measurable outcomes.</p></section>'
        )
    END
)
WHERE is_published = 1;

-- Quick verification
SELECT id, lang_code, service_group, slug, LEFT(content_html, 240) AS content_preview
FROM public_services
ORDER BY id DESC
LIMIT 20;

