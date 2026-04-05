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
$excerpt = static function (string $html, int $limit = 180): string {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
        return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
    }
    return $text;
};
$clusterLabels = [
    $t('Трекеры', 'Trackers'),
    $t('Фарм и аккаунты', 'Farm & accounts'),
    $t('Креативы', 'Creatives'),
    $t('Аналитика', 'Analytics'),
    $t('Команда', 'Team'),
    $t('SEO-кластеры', 'SEO clusters'),
];
$marketSignals = [
    [
        'value' => '72h',
        'label' => $t('цикл обновления editorial-ленты', 'editorial refresh cycle'),
    ],
    [
        'value' => '2-track',
        'label' => $t('каталог: загрузки + playbooks', 'catalog split: downloads + playbooks'),
    ],
    [
        'value' => 'UGC',
        'label' => $t('древовидные комментарии с секциями', 'threaded comments with sections'),
    ],
];
$funnelSteps = [
    [
        'code' => 'TOFU',
        'title' => $t('Забираем спрос', 'Capture demand'),
        'copy' => $t('Кластеры по арбитражу, источникам, трекерам, антидетекту, клоакингу и affiliate-операционке.', 'Clusters for traffic buying, sources, trackers, anti-detect, cloaking and affiliate operations.'),
    ],
    [
        'code' => 'MOFU',
        'title' => $t('Конвертируем в полезность', 'Convert into utility'),
        'copy' => $t('Шаблоны, таблицы, SOP, чеклисты, техничка и готовые решения с быстрым time-to-value.', 'Templates, spreadsheets, SOPs, checklists, technical assets and ready-made solutions with fast time-to-value.'),
    ],
    [
        'code' => 'BOFU',
        'title' => $t('Удерживаем и возвращаем', 'Retain and bring back'),
        'copy' => $t('Комментарии, ветки обсуждений, related-рекомендации и переходы в продуктовые сценарии.', 'Comments, discussion threads, related recommendations and transitions into product-led scenarios.'),
    ],
];
?>
<style>
.cp-home{max-width:1280px;margin:0 auto;padding:26px 16px 54px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.cp-home a{text-decoration:none}
.cp-shell{display:grid;gap:18px}
.cp-card,.cp-masthead,.cp-market-map,.cp-feed,.cp-side-stack,.cp-funnel-band,.cp-operator-grid article,.cp-case-board{position:relative;overflow:hidden;border:1px solid var(--shell-border);background:var(--shell-panel);backdrop-filter:blur(16px);box-shadow:var(--shell-shadow)}
.cp-masthead{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:20px;padding:30px;border-radius:34px;background:
radial-gradient(circle at 12% 18%,rgba(44,224,199,.18),transparent 26%),
radial-gradient(circle at 84% 12%,rgba(122,180,255,.18),transparent 28%),
linear-gradient(145deg,rgba(7,14,28,.96),rgba(10,22,41,.92))}
.cp-kicker,.cp-pill,.cp-card-tag,.cp-feed-meta span,.cp-board-tag,.cp-case-tag{display:inline-flex;align-items:center;gap:8px;padding:7px 11px;border-radius:999px;border:1px solid rgba(122,180,255,.22);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.cp-masthead h1{margin:14px 0 12px;max-width:11ch;font:700 clamp(3.6rem,8vw,6.5rem)/.88 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.cp-masthead p{max-width:74ch;margin:0;color:var(--shell-muted);font-size:15px;line-height:1.78}
.cp-masthead-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}
.cp-btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border-radius:16px;font-weight:700;transition:transform .2s ease,border-color .2s ease}
.cp-btn.primary{background:linear-gradient(135deg,#7ab4ff,#2ce0c7);color:#07111f}
.cp-btn.secondary{border:1px solid var(--shell-border);background:rgba(255,255,255,.05);color:var(--shell-text)}
.cp-btn:hover{transform:translateY(-1px)}
.cp-masthead-grid{display:grid;gap:14px}
.cp-signal-board{padding:18px;border-radius:26px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.03)}
.cp-signal-board h2{margin:0 0 10px;font:700 1.1rem/1.1 "Space Grotesk","Sora",sans-serif}
.cp-signal-list{display:grid;gap:10px}
.cp-signal{display:grid;grid-template-columns:74px 1fr;gap:12px;padding:12px 0;border-top:1px solid rgba(255,255,255,.06)}
.cp-signal:first-child{border-top:0;padding-top:0}
.cp-signal strong{font:700 1.4rem/1 "Space Grotesk","Sora",sans-serif;color:var(--shell-highlight)}
.cp-signal span{color:var(--shell-muted);font-size:13px;line-height:1.55}
.cp-cluster-cloud{display:flex;flex-wrap:wrap;gap:10px}
.cp-pill{letter-spacing:.08em;font-size:12px;color:var(--shell-text)}

.cp-section-title{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-top:8px}
.cp-section-title h2{margin:0;font:700 clamp(2rem,4vw,3rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.06em}
.cp-section-title p{max-width:70ch;margin:0;color:var(--shell-muted)}

.cp-market-map{padding:22px;border-radius:28px}
.cp-market-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:16px}
.cp-card{padding:18px;border-radius:24px}
.cp-card h3{margin:14px 0 10px;font:700 1.55rem/1.04 "Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.cp-card p,.cp-card li{color:var(--shell-muted);line-height:1.65}
.cp-card ul{margin:0;padding-left:18px}

.cp-editorial{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:18px}
.cp-feed{padding:22px;border-radius:28px}
.cp-feed-header{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:16px}
.cp-feed-header h2,.cp-side-stack h2,.cp-funnel-band h2,.cp-case-board h2{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.cp-feed-list{display:grid;gap:14px}
.cp-feed-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;padding:16px 0;border-top:1px solid rgba(255,255,255,.07)}
.cp-feed-item:first-child{border-top:0;padding-top:0}
.cp-feed-item h3{margin:10px 0 8px;font:700 1.4rem/1.08 "Space Grotesk","Sora",sans-serif}
.cp-feed-item p{margin:0;color:var(--shell-muted);line-height:1.65}
.cp-feed-meta{display:flex;gap:8px;flex-wrap:wrap}
.cp-feed-arrow{align-self:center;display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:14px;border:1px solid var(--shell-border);background:rgba(255,255,255,.04);font-weight:700}
.cp-side-stack{display:grid;gap:14px;padding:22px;border-radius:28px}
.cp-side-panel{padding:16px;border-radius:20px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)}
.cp-side-panel h3{margin:10px 0 10px;font:700 1.25rem/1.04 "Space Grotesk","Sora",sans-serif}
.cp-side-panel p,.cp-side-panel li{color:var(--shell-muted);line-height:1.62}
.cp-side-panel ul{margin:0;padding-left:18px}
.cp-side-link{display:inline-flex;margin-top:12px;color:var(--shell-accent);font-weight:700}

.cp-operator-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.cp-operator-grid article{padding:18px;border-radius:24px}
.cp-operator-grid h3{margin:12px 0 8px;font:700 1.4rem/1.08 "Space Grotesk","Sora",sans-serif}
.cp-operator-grid p{margin:0;color:var(--shell-muted);line-height:1.64}

.cp-funnel-band{padding:22px;border-radius:28px}
.cp-funnel-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:18px}
.cp-funnel-step{padding:18px;border-radius:22px;border:1px solid rgba(255,255,255,.06);background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,255,255,.02))}
.cp-funnel-step strong{display:block;font:700 12px/1 "Sora",sans-serif;letter-spacing:.18em;text-transform:uppercase;color:var(--shell-accent)}
.cp-funnel-step h3{margin:10px 0 8px;font:700 1.3rem/1.02 "Space Grotesk","Sora",sans-serif}
.cp-funnel-step p{margin:0;color:var(--shell-muted);line-height:1.62}

.cp-case-board{padding:22px;border-radius:28px}
.cp-case-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}
.cp-case-card{padding:18px;border-radius:22px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)}
.cp-case-card h3{margin:12px 0 8px;font:700 1.35rem/1.08 "Space Grotesk","Sora",sans-serif}
.cp-case-card p{margin:0;color:var(--shell-muted);line-height:1.62}

@media (max-width: 1120px){
    .cp-masthead,.cp-editorial,.cp-market-grid,.cp-operator-grid,.cp-funnel-list,.cp-case-grid{grid-template-columns:1fr}
}
@media (max-width: 720px){
    .cp-home{padding-top:18px}
    .cp-masthead{padding:22px;border-radius:26px}
    .cp-masthead h1{max-width:none;font-size:clamp(2.8rem,14vw,4.2rem)}
    .cp-feed,.cp-side-stack,.cp-funnel-band,.cp-case-board,.cp-market-map{padding:18px}
    .cp-feed-item{grid-template-columns:1fr}
}
</style>

<section class="cp-home">
    <div class="cp-shell">
        <header class="cp-masthead">
            <div>
                <span class="cp-kicker"><?= htmlspecialchars($t('Affiliate Market Intelligence', 'Affiliate Market Intelligence'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars($t('Портал, который выглядит как newsroom и работает как operator desk', 'A portal that looks like a newsroom and works like an operator desk'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($t('CPALNYA собирает редакционный трафик, утилитарные решения и комьюнити-механику в один контур. Визуально это должно считываться как backstage-система рынка: сигналы, связи, решения, маршруты и полезность без шумного “блогового” вайба.', 'CPALNYA merges editorial traffic, utility assets and community mechanics into one system. Visually it should read like a backstage market console: signals, routes, solutions and operator-grade usefulness instead of a generic blog shell.'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="cp-masthead-actions">
                    <a class="cp-btn primary" href="/solutions/downloads/"><?= htmlspecialchars($t('Открыть ready-made раздел', 'Open ready-made assets'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="cp-btn secondary" href="/blog/"><?= htmlspecialchars($t('Смотреть editorial-хаб', 'Explore editorial hub'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>
            <div class="cp-masthead-grid">
                <div class="cp-signal-board">
                    <h2><?= htmlspecialchars($t('Market signal board', 'Market signal board'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="cp-signal-list">
                        <?php foreach ($marketSignals as $signal): ?>
                            <div class="cp-signal">
                                <strong><?= htmlspecialchars($signal['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars($signal['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="cp-cluster-cloud">
                    <?php foreach ($clusterLabels as $label): ?>
                        <span class="cp-pill"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </header>

        <section class="cp-market-map">
            <div class="cp-section-title">
                <div>
                    <h2><?= htmlspecialchars($t('Market Map', 'Market Map'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($t('По мотивам top-паттернов ниши структура собрана как гибрид Partnerkin-style media, utility-directory в духе OfferVault и community-механики форумного слоя. Не копия, а сборка сильных паттернов в одном продукте.', 'Using the strongest patterns from the niche, the structure acts like a hybrid of Partnerkin-style media, OfferVault-like utility discovery and a forum-grade community layer. Not a copy, but a concentrated blend of what actually works.'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <div class="cp-market-grid">
                <article class="cp-card">
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Editorial', 'Editorial'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($t('Плотная editorial-лента', 'Dense editorial feed'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($t('Не “карточки блога”, а лента с большим headline, контекстом, кластером и быстрым переходом к related-потоку. Это паттерн, который держит внимание у нишевых медиа.', 'Not generic blog tiles, but a feed with a strong headline, context, cluster marker and fast route into related streams. This is what keeps niche media sticky.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
                <article class="cp-card">
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Utility', 'Utility'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($t('Каталог как рабочая база', 'Catalog as working base'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($t('Раздел решений должен выглядеть как рабочий directory: короткая ценность, стек, формат, сложность, путь внедрения. Это приближает UX к offer-db и tool-db.', 'The solutions section should look like a working directory: short value proposition, stack, format, difficulty and implementation path. That moves the UX closer to an offer-db and tool-db.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
                <article class="cp-card">
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Community', 'Community'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($t('Комментарии как knowledge-layer', 'Comments as knowledge layer'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($t('Ветка комментариев здесь не вторична. Это слой практики, который возвращает пользователей и делает материал живым, как у сильных community-driven нишевых площадок.', 'Comment threads are not secondary here. They are the practice layer that brings users back and makes every asset feel alive, just like strong community-driven niche properties.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            </div>
        </section>

        <section class="cp-editorial">
            <div class="cp-feed">
                <div class="cp-feed-header">
                    <div>
                        <span class="cp-card-tag"><?= htmlspecialchars($t('Editorial Feed', 'Editorial Feed'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Свежие кластеры и материалы', 'Fresh clusters and materials'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="cp-side-link" href="/blog/"><?= htmlspecialchars($t('Весь блог', 'All blog'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="cp-feed-list">
                    <?php foreach (array_slice($blogItems, 0, 4) as $item): ?>
                        <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                        <a class="cp-feed-item" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/">
                            <div>
                                <div class="cp-feed-meta">
                                    <span><?= htmlspecialchars($cluster !== '' ? $cluster : $t('article', 'article'), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <span class="cp-feed-arrow">01</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="cp-side-stack">
                <div>
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Utility Layer', 'Utility Layer'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($t('Скачивания и playbooks', 'Downloads and playbooks'), ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <?php foreach (array_slice($downloads, 0, 2) as $item): ?>
                    <article class="cp-side-panel">
                        <span class="cp-board-tag"><?= htmlspecialchars($t('Download', 'Download'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cp-side-link" href="/solutions/downloads/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть asset', 'Open asset'), ENT_QUOTES, 'UTF-8') ?></a>
                    </article>
                <?php endforeach; ?>
                <?php foreach (array_slice($solutionArticles, 0, 2) as $item): ?>
                    <article class="cp-side-panel">
                        <span class="cp-board-tag"><?= htmlspecialchars($t('Playbook', 'Playbook'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cp-side-link" href="/solutions/articles/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Читать разбор', 'Read playbook'), ENT_QUOTES, 'UTF-8') ?></a>
                    </article>
                <?php endforeach; ?>
            </aside>
        </section>

        <section class="cp-funnel-band">
            <div class="cp-section-title">
                <div>
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Funnels', 'Funnels'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($t('Как портал монетизирует внимание', 'How the portal monetizes attention'), ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <p><?= htmlspecialchars($t('Контент здесь не должен просто читаться. Он должен вести в полезность, регистрацию, обсуждение и обратно в релевантный контур.', 'Content here should not just be consumed. It should route users into utility, registration, discussion and then back into the relevant cluster.'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="cp-funnel-list">
                <?php foreach ($funnelSteps as $step): ?>
                    <article class="cp-funnel-step">
                        <strong><?= htmlspecialchars($step['code'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($step['copy'], ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="cp-operator-grid">
            <?php foreach (array_slice($projects, 0, 3) as $item): ?>
                <article>
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Product', 'Product'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="cp-side-link" href="/projects/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Смотреть продукт', 'View product'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="cp-case-board">
            <div class="cp-section-title">
                <div>
                    <span class="cp-card-tag"><?= htmlspecialchars($t('Caseboard', 'Caseboard'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($t('Кейсы и операторские разборы', 'Cases and operator breakdowns'), ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <a class="cp-side-link" href="/cases/"><?= htmlspecialchars($t('Все кейсы', 'All cases'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
            <div class="cp-case-grid">
                <?php foreach (array_slice($cases, 0, 4) as $item): ?>
                    <article class="cp-case-card">
                        <span class="cp-case-tag"><?= htmlspecialchars($t('Case study', 'Case study'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cp-side-link" href="/cases/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть кейс', 'Open case'), ENT_QUOTES, 'UTF-8') ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>
