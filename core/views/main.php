<?php
$home = (array)($ModelPage['home_portal'] ?? []);
$lang = (string)($home['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$blogItems = (array)($home['blog_items'] ?? []);
$downloads = (array)($home['downloads'] ?? []);
$solutionArticles = (array)($home['solution_articles'] ?? []);
$projects = (array)($home['projects'] ?? []);
$cases = (array)($home['cases'] ?? []);

$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
$excerpt = static function (string $html, int $limit = 170): string {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
        return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
    }
    return $text;
};
?>
<style>
.cp-home{max-width:1240px;margin:0 auto;padding:22px 16px 38px;color:#edf3fb;font-family:"IBM Plex Sans",system-ui,sans-serif}
.cp-hero{position:relative;overflow:hidden;border:1px solid rgba(110,154,243,.28);border-radius:32px;padding:34px;background:radial-gradient(circle at 10% 10%,rgba(34,216,194,.14),transparent 32%),radial-gradient(circle at 90% 15%,rgba(110,154,243,.18),transparent 34%),linear-gradient(135deg,rgba(6,12,22,.96),rgba(14,32,56,.92))}
.cp-hero:before,.cp-hero:after{content:"";position:absolute;border-radius:999px;filter:blur(26px);opacity:.65}
.cp-hero:before{width:220px;height:220px;background:rgba(18,182,166,.18);right:-30px;top:-50px}
.cp-hero:after{width:180px;height:180px;background:rgba(127,164,255,.18);left:-20px;bottom:-40px}
.cp-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;border:1px solid rgba(116,159,240,.28);background:rgba(255,255,255,.04);font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.cp-hero h1{max-width:15ch;margin:16px 0 10px;font:700 68px/0.95 "Space Grotesk","IBM Plex Sans",sans-serif}
.cp-hero p{max-width:78ch;color:#aab9cf;line-height:1.7}
.cp-actions,.cp-metrics,.cp-grid,.cp-columns{display:grid;gap:14px}
.cp-actions{grid-template-columns:repeat(2,max-content);margin-top:24px}
.cp-btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border-radius:14px;text-decoration:none;font-weight:700}
.cp-btn.primary{background:linear-gradient(135deg,#78a6ff,#17b6a6);color:#07111f}
.cp-btn.secondary{background:rgba(255,255,255,.05);border:1px solid rgba(127,164,223,.24);color:#dbe7f8}
.cp-metrics{grid-template-columns:repeat(3,minmax(0,1fr));margin-top:24px}
.cp-metric{padding:16px;border-radius:18px;background:rgba(255,255,255,.03);border:1px solid rgba(127,164,223,.18)}
.cp-metric strong{display:block;font:700 34px/1 "Space Grotesk","IBM Plex Sans",sans-serif}
.cp-metric span{display:block;margin-top:8px;color:#97acc8;line-height:1.45}
.cp-section{margin-top:24px}
.cp-section h2{margin:0 0 8px;font:700 34px/1.05 "Space Grotesk","IBM Plex Sans",sans-serif;color:#f5f8fd}
.cp-section > p{max-width:82ch;margin:0 0 16px;color:#9eb0c9;line-height:1.65}
.cp-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
.cp-card{padding:18px;border-radius:22px;border:1px solid rgba(127,164,223,.2);background:rgba(8,14,28,.82);backdrop-filter:blur(10px)}
.cp-card .tag{display:inline-flex;padding:5px 10px;border-radius:999px;border:1px solid rgba(127,164,223,.25);background:rgba(255,255,255,.04);color:#85b2ff;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.cp-card h3{margin:14px 0 10px;font:700 24px/1.14 "Space Grotesk","IBM Plex Sans",sans-serif}
.cp-card p,.cp-card li{color:#a8b7cb;line-height:1.62}
.cp-card ul{margin:10px 0 0;padding-left:18px}
.cp-card a.link{display:inline-flex;margin-top:14px;color:#7ecfff;text-decoration:none;font-weight:700}
.cp-columns{grid-template-columns:1.15fr .85fr}
.cp-funnel{display:grid;gap:12px}
.cp-funnel-step{padding:16px;border-radius:18px;background:linear-gradient(135deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(127,164,223,.18)}
.cp-funnel-step b{display:block;margin-bottom:6px;color:#7dcfff;font-size:12px;letter-spacing:.1em;text-transform:uppercase}
@media (max-width:980px){.cp-hero h1{font-size:48px}.cp-grid,.cp-columns,.cp-metrics{grid-template-columns:1fr}.cp-actions{grid-template-columns:1fr}}
</style>

<section class="cp-home">
    <header class="cp-hero">
        <span class="cp-kicker"><?= htmlspecialchars($t('CPALNYA / закулисье affiliate', 'CPALNYA / affiliate backstage'), ENT_QUOTES, 'UTF-8') ?></span>
        <h1><?= htmlspecialchars($t('Портал, который вскрывает механику CPA и арбитража трафика', 'A portal that exposes how CPA and affiliate traffic actually works'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($t('CPALNYA соединяет статьи, готовые решения, техничку, кейсы и SEO-хабы в единый портал. Здесь информационный трафик прогревается через редакционные кластеры, превращается в регистрацию через загрузки и возвращается в комьюнити через комментарии, практику и обновляемые playbooks.', 'CPALNYA connects articles, ready-made assets, technical guides, case studies and SEO hubs into one portal. Informational traffic is warmed up through editorial clusters, converted into registrations via downloads and retained through comments, field notes and evolving playbooks.'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="cp-actions">
            <a class="cp-btn primary" href="/solutions/downloads/"><?= htmlspecialchars($t('Открыть готовые решения', 'Open ready-made assets'), ENT_QUOTES, 'UTF-8') ?></a>
            <a class="cp-btn secondary" href="/blog/"><?= htmlspecialchars($t('Читать статьи', 'Read articles'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
        <div class="cp-metrics">
            <div class="cp-metric"><strong>TOFU</strong><span><?= htmlspecialchars($t('Словарь, обзоры, разборы сеток, трекеров, креативов и моделей закупки.', 'Glossary, reviews and breakdowns of traffic sources, trackers, creatives and buying models.'), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="cp-metric"><strong>MOFU</strong><span><?= htmlspecialchars($t('Готовые решения, шаблоны, чек-листы, аналитические каркасы и техничка для команды.', 'Ready-made assets, templates, checklists, analytics frameworks and technical operations packs.'), ENT_QUOTES, 'UTF-8') ?></span></div>
            <div class="cp-metric"><strong>BOFU</strong><span><?= htmlspecialchars($t('Регистрация, комментарии, повторные визиты и переходы в продуктовые/партнерские сценарии.', 'Registration, comments, repeat visits and transitions into product or partner scenarios.'), ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>
    </header>

    <section class="cp-section">
        <h2><?= htmlspecialchars($t('Архитектура портала', 'Portal architecture'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($t('Прямые конкуренты держат либо статьи, либо каталоги офферов, либо форумную часть. Для CPALNYA собрана гибридная модель: редакционный хаб как у контентных медиа, структурные каталоги как у offer-баз, и комьюнити-механика вокруг комментариев и инсайтов.', 'Direct competitors usually focus on either editorial, offer discovery or forum/community mechanics. CPALNYA uses a hybrid model: editorial hub like content media, structured catalogs like offer databases and community mechanics around comments and insider notes.'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="cp-grid">
            <article class="cp-card">
                <span class="tag"><?= htmlspecialchars($t('Контент', 'Content'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($t('Тематические кластеры', 'Topic clusters'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('Статьи делятся по сценариям закупки, аналитике, трекерам, клоакингу, SEO, команде и инфраструктуре. Это закрывает как инфозапросы, так и глубокий middle-funnel intent.', 'Articles are grouped by buying workflows, analytics, trackers, cloaking, SEO, team operations and infrastructure. That covers both informational demand and deeper middle-funnel intent.'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="cp-card">
                <span class="tag"><?= htmlspecialchars($t('Решения', 'Assets'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($t('Секция из двух половин', 'Two-track solutions section'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('Одна часть содержит скачиваемые материалы и шаблоны, вторая - практические статьи, объясняющие как внедрять и масштабировать рабочие схемы.', 'One track contains downloadable materials and templates, the other delivers practical articles explaining how to implement and scale working setups.'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="cp-card">
                <span class="tag"><?= htmlspecialchars($t('Комьюнити', 'Community'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($t('Древовидные комментарии', 'Threaded comments'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('Комментарии работают только для зарегистрированных пользователей. Это дает слой повторных визитов, удержание и накопление прикладной базы знаний вокруг материалов.', 'Comments are available to registered users only. That creates repeat visits, retention and an accumulating applied knowledge layer around each asset.'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </div>
    </section>

    <section class="cp-section">
        <h2><?= htmlspecialchars($t('Воронки роста', 'Growth funnels'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="cp-columns">
            <div class="cp-funnel">
                <div class="cp-funnel-step"><b><?= htmlspecialchars($t('Воронка 01', 'Funnel 01'), ENT_QUOTES, 'UTF-8') ?></b><div><?= htmlspecialchars($t('SEO-статья -> внутренняя перелинковка -> ready-made asset -> регистрация -> комментарий -> возврат в related-материалы.', 'SEO article -> internal links -> ready-made asset -> registration -> comment -> return to related materials.'), ENT_QUOTES, 'UTF-8') ?></div></div>
                <div class="cp-funnel-step"><b><?= htmlspecialchars($t('Воронка 02', 'Funnel 02'), ENT_QUOTES, 'UTF-8') ?></b><div><?= htmlspecialchars($t('Кейс/разбор -> секция “что использовать прямо сейчас” -> загрузка шаблона -> переход в проекты/продукты.', 'Case study/breakdown -> “use this now” section -> asset download -> transition into products/projects.'), ENT_QUOTES, 'UTF-8') ?></div></div>
                <div class="cp-funnel-step"><b><?= htmlspecialchars($t('Воронка 03', 'Funnel 03'), ENT_QUOTES, 'UTF-8') ?></b><div><?= htmlspecialchars($t('Страница решения -> вопрос в комментариях -> ответ/ветка -> повторный визит -> углубление в related cluster.', 'Solution page -> question in comments -> answer/thread -> repeat visit -> deeper dive into the related cluster.'), ENT_QUOTES, 'UTF-8') ?></div></div>
            </div>
            <article class="cp-card">
                <span class="tag"><?= htmlspecialchars($t('SEO ядро', 'SEO core'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($t('Кластеры, которые нужно доминировать', 'Clusters to dominate'), ENT_QUOTES, 'UTF-8') ?></h3>
                <ul>
                    <li><?= htmlspecialchars($t('Арбитраж трафика: гайды, словарь, источники, ошибки, масштабирование.', 'Traffic arbitrage: guides, glossary, sources, mistakes and scaling.'), ENT_QUOTES, 'UTF-8') ?></li>
                    <li><?= htmlspecialchars($t('Инфраструктура: трекеры, антидетект, прокси, клоакинг, аналитика, BI.', 'Infrastructure: trackers, anti-detect, proxies, cloaking, analytics and BI.'), ENT_QUOTES, 'UTF-8') ?></li>
                    <li><?= htmlspecialchars($t('Операционка: команда, KPI, ревью связок, контроль качества, legal/compliance.', 'Operations: team, KPI, setup reviews, quality control and legal/compliance.'), ENT_QUOTES, 'UTF-8') ?></li>
                </ul>
            </article>
        </div>
    </section>

    <section class="cp-section">
        <h2><?= htmlspecialchars($t('Стартовое наполнение', 'Initial content'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="cp-grid">
            <?php foreach ($downloads as $item): ?>
                <article class="cp-card">
                    <span class="tag"><?= htmlspecialchars($t('Загрузка', 'Download'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="link" href="/solutions/downloads/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть решение', 'Open asset'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
            <?php foreach ($solutionArticles as $item): ?>
                <article class="cp-card">
                    <span class="tag"><?= htmlspecialchars($t('Разбор', 'Playbook'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="link" href="/solutions/articles/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Читать', 'Read'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cp-section">
        <h2><?= htmlspecialchars($t('Свежие статьи и смежные разделы', 'Fresh articles and adjacent sections'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="cp-grid">
            <?php foreach (array_slice($blogItems, 0, 3) as $item): ?>
                <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                <article class="cp-card">
                    <span class="tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Статья', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="link" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('В блог', 'Open article'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
            <?php foreach (array_slice($cases, 0, 2) as $item): ?>
                <article class="cp-card">
                    <span class="tag"><?= htmlspecialchars($t('Кейс', 'Case'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="link" href="/cases/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть кейс', 'Open case'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
            <?php foreach (array_slice($projects, 0, 1) as $item): ?>
                <article class="cp-card">
                    <span class="tag"><?= htmlspecialchars($t('Продукт', 'Product'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="link" href="/projects/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Смотреть продукт', 'View product'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>
