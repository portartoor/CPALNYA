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

$signalBoard = [
    ['icon' => '◉', 'value' => 'Live', 'label' => $t('новый слой редакционного сигнала', 'new editorial signal layer')],
    ['icon' => '⬢', 'value' => '2x', 'label' => $t('ветки utility: assets и playbooks', 'utility branches: assets and playbooks')],
    ['icon' => '↯', 'value' => 'UGC', 'label' => $t('древовидные обсуждения внутри материалов', 'threaded discussion inside materials')],
];

$clusters = [
    ['icon' => '◈', 'label' => $t('Трекеры', 'Trackers')],
    ['icon' => '▣', 'label' => $t('Фарм и аккаунты', 'Farm & accounts')],
    ['icon' => '◌', 'label' => $t('Креативы', 'Creatives')],
    ['icon' => '◎', 'label' => $t('Аналитика', 'Analytics')],
    ['icon' => '△', 'label' => $t('Команда', 'Team')],
    ['icon' => '◇', 'label' => $t('SEO-кластеры', 'SEO clusters')],
];

$funnel = [
    [
        'code' => '01',
        'icon' => '↗',
        'title' => $t('Забираем поисковый и комьюнити-спрос в широкие кластеры', 'Capture search and community demand in wide clusters'),
        'copy' => $t('Главная и блог работают как операторский newsroom: длинные заголовки, сильный контекст, маршрут в смежные темы и полезные слои.', 'Home and blog behave like an operator newsroom: long headlines, strong context and a route into adjacent topics and utility layers.'),
    ],
    [
        'code' => '02',
        'icon' => '⬢',
        'title' => $t('Переводим внимание в рабочие решения, которые можно внедрить сразу', 'Turn attention into working solutions that can be deployed immediately'),
        'copy' => $t('Скачиваемые assets и playbooks дают быстрый time-to-value вместо еще одной статьи без действия.', 'Downloadable assets and playbooks create fast time-to-value instead of yet another article with no action.'),
    ],
    [
        'code' => '03',
        'icon' => '✦',
        'title' => $t('Возвращаем пользователей через обсуждение, related-потоки и кейсы', 'Bring users back through discussion, related streams and cases'),
        'copy' => $t('Комментарии, кейсы и соседние кластеры работают как knowledge-loop и удерживают аудиторию в системе.', 'Comments, case studies and adjacent clusters act as a knowledge loop and keep the audience inside the system.'),
    ],
];
?>
<style>
.cp-home{max-width:1380px;margin:0 auto;padding:28px 18px 64px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.cp-home a{text-decoration:none}
.cp-home-grid{display:grid;gap:18px}
.cp-panel,.cp-hero,.cp-signal-grid,.cp-cluster-board,.cp-editorial-stream,.cp-utility-wall,.cp-funnel-stage,.cp-case-zone,.cp-products-grid article{position:relative;overflow:hidden;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.86),rgba(5,10,20,.72));backdrop-filter:blur(18px);box-shadow:var(--shell-shadow)}
.cp-panel::before,.cp-hero::before,.cp-signal-grid::before,.cp-cluster-board::before,.cp-editorial-stream::before,.cp-utility-wall::before,.cp-funnel-stage::before,.cp-case-zone::before,.cp-products-grid article::before{content:"";position:absolute;inset:auto -18% -38% 28%;height:180px;background:radial-gradient(circle,rgba(61,233,202,.14),transparent 68%);pointer-events:none}
.cp-hero{display:grid;grid-template-columns:minmax(0,1.24fr) minmax(360px,.76fr);gap:18px;padding:28px;border-radius:34px;clip-path:polygon(0 0,94% 0,100% 10%,100% 100%,6% 100%,0 90%)}
.cp-kicker,.cp-chip,.cp-meta-chip,.cp-eye,.cp-stage-code{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase}
.cp-kicker-icon,.cp-chip i,.cp-card-icon,.cp-block-icon,.cp-stage-icon{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:999px;background:rgba(255,255,255,.06);font-style:normal}
.cp-hero-copy{display:grid;align-content:start;gap:16px}
.cp-hero h1{margin:0;max-width:15ch;font:700 clamp(3.5rem,8vw,6.7rem)/.9 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.cp-hero p{max-width:74ch;margin:0;color:var(--shell-muted);font-size:15px;line-height:1.82}
.cp-actions{display:flex;flex-wrap:wrap;gap:12px}
.cp-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:15px 18px;border-radius:18px;font-weight:700;transition:transform .22s ease,border-color .22s ease,background .22s ease}
.cp-btn.primary{background:linear-gradient(135deg,#82b6ff,#3ae9ca);color:#04111a}
.cp-btn.secondary{border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text)}
.cp-btn:hover{transform:translateY(-2px)}
.cp-hero-side{display:grid;gap:14px}
.cp-signal-grid{padding:18px;border-radius:28px;clip-path:polygon(0 0,100% 0,100% 88%,94% 100%,0 100%)}
.cp-signal-head,.cp-cluster-head,.cp-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px}
.cp-signal-head h2,.cp-cluster-head h2,.cp-section-head h2,.cp-editorial-stream h2,.cp-utility-wall h2,.cp-funnel-stage h2,.cp-case-zone h2{margin:0;font:700 clamp(1.7rem,3vw,2.85rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.cp-signal-list{display:grid;gap:12px;margin-top:16px}
.cp-signal-row{display:grid;grid-template-columns:72px 1fr;gap:12px;padding:14px 0;border-top:1px solid rgba(255,255,255,.07)}
.cp-signal-row:first-child{padding-top:0;border-top:0}
.cp-signal-row strong{display:inline-flex;align-items:center;gap:10px;font:700 1.4rem/1 "Space Grotesk","Sora",sans-serif;color:var(--shell-highlight)}
.cp-signal-row span{color:var(--shell-muted);line-height:1.58}
.cp-cluster-board{padding:18px;border-radius:28px;clip-path:polygon(0 0,94% 0,100% 16%,100% 100%,0 100%)}
.cp-clusters{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
.cp-chip{font-size:12px;letter-spacing:.08em}

.cp-scan-grid{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:18px}
.cp-editorial-stream,.cp-utility-wall,.cp-funnel-stage,.cp-case-zone{padding:22px;border-radius:30px}
.cp-stream-list,.cp-utility-columns,.cp-products-grid,.cp-case-grid{display:grid;gap:14px}
.cp-stream-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:16px;padding:18px;border-radius:24px;background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,255,255,.025));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,94% 0,100% 12%,100% 100%,6% 100%,0 88%)}
.cp-card-index{display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;border-radius:18px;border:1px solid rgba(122,180,255,.18);background:rgba(7,18,36,.72);font:700 1rem/1 "Space Grotesk","Sora",sans-serif}
.cp-stream-item h3,.cp-utility-card h3,.cp-products-grid h3,.cp-case-card h3{margin:0 0 10px;font:700 1.45rem/1.08 "Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.cp-stream-item p,.cp-utility-card p,.cp-products-grid p,.cp-case-card p,.cp-funnel-card p{margin:0;color:var(--shell-muted);line-height:1.68}
.cp-stream-arrow,.cp-link{display:inline-flex;align-items:center;gap:8px;font-weight:700;color:var(--shell-accent)}
.cp-meta-row{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
.cp-eye{letter-spacing:.08em}

.cp-utility-wall{display:grid;gap:16px}
.cp-utility-columns{grid-template-columns:repeat(2,minmax(0,1fr))}
.cp-utility-card{padding:18px;border-radius:24px;background:linear-gradient(165deg,rgba(8,18,33,.88),rgba(9,15,24,.68));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,100% 0,100% 82%,92% 100%,0 100%)}
.cp-utility-card .cp-meta-row{margin-top:12px;margin-bottom:0}

.cp-products-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
.cp-products-grid article{padding:18px;border-radius:26px;clip-path:polygon(0 0,96% 0,100% 14%,100% 100%,4% 100%,0 86%)}

.cp-funnel-stage{display:grid;gap:16px}
.cp-funnel-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.cp-funnel-card{padding:18px;border-radius:24px;background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.06)}
.cp-stage-code{margin-bottom:12px}
.cp-stage-code b{font:700 12px/1 "Space Grotesk","Sora",sans-serif}
.cp-funnel-card h3{margin:0 0 10px;font:700 1.35rem/1.06 "Space Grotesk","Sora",sans-serif}

.cp-case-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.cp-case-card{padding:18px;border-radius:24px;background:linear-gradient(145deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,100% 0,100% 88%,95% 100%,0 100%)}

@media (max-width: 1160px){
    .cp-hero,.cp-scan-grid,.cp-utility-columns,.cp-products-grid,.cp-funnel-grid,.cp-case-grid{grid-template-columns:1fr}
}
@media (max-width: 760px){
    .cp-home{padding:18px 14px 54px}
    .cp-hero{padding:22px;border-radius:28px}
    .cp-hero h1{max-width:none;font-size:clamp(2.8rem,14vw,4.5rem)}
    .cp-stream-item{grid-template-columns:1fr}
}
</style>

<section class="cp-home">
    <div class="cp-home-grid">
        <header class="cp-hero">
            <div class="cp-hero-copy">
                <span class="cp-kicker"><span class="cp-kicker-icon">◈</span><?= htmlspecialchars($t('CPA Backstage City', 'CPA Backstage City'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="neo-title">
                    <?= htmlspecialchars($t('Не просто блог про трафик, а длинная операторская поверхность, где ', 'Not just a traffic blog, but a long operator surface where '), ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('сигналы рынка', 'market signals'), ENT_QUOTES, 'UTF-8') ?></strong><?= htmlspecialchars($t(' переходят в ', ' turn into '), ENT_QUOTES, 'UTF-8') ?><em><?= htmlspecialchars($t('рабочие решения и маршруты команды', 'working solutions and team routes'), ENT_QUOTES, 'UTF-8') ?></em>
                </h1>
                <p><?= htmlspecialchars($t('CPALNYA должен ощущаться как живая неоновая карта арбитражного города: кварталы знаний, узлы влияния, редакционные потоки, скачиваемые инструменты и полевая практика в комментариях. Поэтому дизайн уводится от стандартных прямоугольных блоков в более жесткую, многослойную, асимметричную композицию.', 'CPALNYA should feel like a living neon map of an affiliate city: knowledge districts, influence nodes, editorial flows, downloadable tools and field practice in the comments. That is why the interface moves away from standard rectangles into a harder, layered and asymmetric composition.'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="cp-actions">
                    <a class="cp-btn primary" href="/solutions/downloads/"><span>⬢</span><?= htmlspecialchars($t('Открыть готовые assets', 'Open ready-made assets'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="cp-btn secondary" href="/blog/"><span>↗</span><?= htmlspecialchars($t('Перейти в editorial-поток', 'Go to editorial stream'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>

            <div class="cp-hero-side">
                <section class="cp-signal-grid">
                    <div class="cp-signal-head">
                        <div>
                            <span class="cp-chip"><i>↯</i><?= htmlspecialchars($t('Signal board', 'Signal board'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="neo-title"><?= htmlspecialchars($t('Что должно считываться ', 'What should be visible ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('с первого экрана', 'from the first screen'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                        </div>
                    </div>
                    <div class="cp-signal-list">
                        <?php foreach ($signalBoard as $row): ?>
                            <div class="cp-signal-row">
                                <strong><span class="cp-card-icon"><?= htmlspecialchars($row['icon'], ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="cp-cluster-board">
                    <div class="cp-cluster-head">
                        <div>
                            <span class="cp-chip"><i>◎</i><?= htmlspecialchars($t('Map', 'Map'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="neo-title"><?= htmlspecialchars($t('Главные районы ', 'Main districts ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('контентного города', 'of the content city'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                        </div>
                    </div>
                    <div class="cp-clusters">
                        <?php foreach ($clusters as $cluster): ?>
                            <span class="cp-chip"><i><?= htmlspecialchars($cluster['icon'], ENT_QUOTES, 'UTF-8') ?></i><?= htmlspecialchars($cluster['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </header>

        <div class="cp-scan-grid">
            <section class="cp-editorial-stream">
                <div class="cp-section-head">
                    <div>
                        <span class="cp-chip"><i>✦</i><?= htmlspecialchars($t('Editorial stream', 'Editorial stream'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="neo-title"><?= htmlspecialchars($t('Плотная лента материалов, где ', 'A dense article stream where ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('каждый вход', 'every entry point'), ENT_QUOTES, 'UTF-8') ?></strong><?= htmlspecialchars($t(' ведет дальше', ' leads further'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="cp-link" href="/blog/"><?= htmlspecialchars($t('Весь блог', 'All blog'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                </div>
                <div class="cp-stream-list">
                    <?php foreach (array_slice($blogItems, 0, 4) as $index => $item): ?>
                        <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                        <a class="cp-stream-item" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/">
                            <span class="cp-card-index"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <div>
                                <div class="cp-meta-row">
                                    <span class="cp-meta-chip"><span class="cp-card-icon">◉</span><?= htmlspecialchars($cluster !== '' ? $cluster : $t('article', 'article'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="cp-eye"><span class="cp-card-icon">◌</span><?= (int)($item['view_count'] ?? 0) ?> views</span>
                                </div>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <span class="cp-stream-arrow">↗</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="cp-utility-wall">
                <div class="cp-section-head">
                    <div>
                        <span class="cp-chip"><i>⬢</i><?= htmlspecialchars($t('Utility layer', 'Utility layer'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="neo-title"><?= htmlspecialchars($t('Не второй блог, а ', 'Not a second blog, but ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('рабочий инструментальный слой', 'a working utility layer'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                    </div>
                </div>
                <div class="cp-utility-columns">
                    <?php foreach (array_slice($downloads, 0, 2) as $item): ?>
                        <article class="cp-utility-card">
                            <span class="cp-chip"><i>↓</i><?= htmlspecialchars($t('Download', 'Download'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="cp-meta-row">
                                <span class="cp-eye"><span class="cp-card-icon">◎</span><?= htmlspecialchars((string)($item['category_code'] ?? $t('asset', 'asset')), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <a class="cp-link" href="/solutions/downloads/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть asset', 'Open asset'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                        </article>
                    <?php endforeach; ?>
                    <?php foreach (array_slice($solutionArticles, 0, 2) as $item): ?>
                        <article class="cp-utility-card">
                            <span class="cp-chip"><i>▣</i><?= htmlspecialchars($t('Playbook', 'Playbook'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="cp-meta-row">
                                <span class="cp-eye"><span class="cp-card-icon">◇</span><?= htmlspecialchars((string)($item['category_code'] ?? $t('guide', 'guide')), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <a class="cp-link" href="/solutions/articles/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Читать playbook', 'Read playbook'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>

        <section class="cp-funnel-stage">
            <div class="cp-section-head">
                <div>
                    <span class="cp-chip"><i>↯</i><?= htmlspecialchars($t('Growth loop', 'Growth loop'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars($t('Портал должен вести пользователя ', 'The portal should move the user ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('от сигнала к действию', 'from signal to action'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                </div>
            </div>
            <div class="cp-funnel-grid">
                <?php foreach ($funnel as $step): ?>
                    <article class="cp-funnel-card">
                        <span class="cp-stage-code"><span class="cp-stage-icon"><?= htmlspecialchars($step['icon'], ENT_QUOTES, 'UTF-8') ?></span><b><?= htmlspecialchars($step['code'], ENT_QUOTES, 'UTF-8') ?></b></span>
                        <h3><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($step['copy'], ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="cp-products-grid">
            <?php foreach (array_slice($projects, 0, 3) as $item): ?>
                <article>
                    <span class="cp-chip"><i>◈</i><?= htmlspecialchars($t('Product edge', 'Product edge'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="cp-link" href="/projects/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Смотреть продукт', 'View product'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="cp-case-zone">
            <div class="cp-section-head">
                <div>
                    <span class="cp-chip"><i>✦</i><?= htmlspecialchars($t('Caseboard', 'Caseboard'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars($t('Разборы должны выглядеть как ', 'Breakdowns should look like ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('операторские досье', 'operator dossiers'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                </div>
                <a class="cp-link" href="/cases/"><?= htmlspecialchars($t('Все кейсы', 'All cases'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
            </div>
            <div class="cp-case-grid">
                <?php foreach (array_slice($cases, 0, 4) as $item): ?>
                    <article class="cp-case-card">
                        <span class="cp-chip"><i>◎</i><?= htmlspecialchars($t('Case study', 'Case study'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cp-link" href="/cases/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть разбор', 'Open breakdown'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>
