<?php
$home = (array)($ModelPage['home_portal'] ?? []);
$lang = (string)($home['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$blogItems = array_values((array)($home['blog_items'] ?? []));
$downloads = array_values((array)($home['downloads'] ?? []));
$solutionArticles = array_values((array)($home['solution_articles'] ?? []));
$projects = array_values((array)($home['projects'] ?? []));
$cases = array_values((array)($home['cases'] ?? []));

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

$cover = $blogItems[0] ?? null;
$secondaryLead = $blogItems[1] ?? null;
$streamItems = array_slice($blogItems, 2);

$topicMap = [
    'tracker' => ['label' => $t('Трекеры и аналитика', 'Trackers and analytics'), 'icon' => '◎'],
    'trackers' => ['label' => $t('Трекеры и аналитика', 'Trackers and analytics'), 'icon' => '◎'],
    'analytics' => ['label' => $t('Трекеры и аналитика', 'Trackers and analytics'), 'icon' => '◎'],
    'farm' => ['label' => $t('Фарм, аккаунты и антидетект', 'Farm, accounts and anti-detect'), 'icon' => '◈'],
    'accounts' => ['label' => $t('Фарм, аккаунты и антидетект', 'Farm, accounts and anti-detect'), 'icon' => '◈'],
    'anti' => ['label' => $t('Фарм, аккаунты и антидетект', 'Farm, accounts and anti-detect'), 'icon' => '◈'],
    'creative' => ['label' => $t('Креативы и воронки', 'Creatives and funnels'), 'icon' => '✦'],
    'creatives' => ['label' => $t('Креативы и воронки', 'Creatives and funnels'), 'icon' => '✦'],
    'funnel' => ['label' => $t('Креативы и воронки', 'Creatives and funnels'), 'icon' => '✦'],
    'seo' => ['label' => $t('SEO и editorial clusters', 'SEO and editorial clusters'), 'icon' => '◇'],
    'team' => ['label' => $t('Команда и процессы', 'Team and processes'), 'icon' => '▣'],
];

$topicSections = [];
foreach ($streamItems as $item) {
    $cluster = strtolower(trim((string)($item['cluster_code'] ?? '')));
    $sectionKey = 'misc';
    $sectionMeta = ['label' => $t('Полевые темы', 'Field notes'), 'icon' => '◌'];
    foreach ($topicMap as $needle => $meta) {
        if ($cluster !== '' && strpos($cluster, $needle) !== false) {
            $sectionKey = $needle;
            $sectionMeta = $meta;
            break;
        }
    }
    if (!isset($topicSections[$sectionKey])) {
        $topicSections[$sectionKey] = [
            'label' => $sectionMeta['label'],
            'icon' => $sectionMeta['icon'],
            'items' => [],
        ];
    }
    if (count($topicSections[$sectionKey]['items']) < 3) {
        $topicSections[$sectionKey]['items'][] = $item;
    }
}
$topicSections = array_slice(array_values($topicSections), 0, 4);

$issueChips = [
    $t('арбитраж трафика', 'traffic arbitrage'),
    $t('редакционные разборы', 'editorial breakdowns'),
    $t('готовые решения', 'ready-made assets'),
    $t('командная практика', 'team practice'),
];
?>
<style>
.cpz{max-width:1400px;margin:0 auto;padding:28px 18px 68px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.cpz a{text-decoration:none}
.cpz-shell{display:grid;gap:18px}
.cpz-panel,.cpz-cover,.cpz-issue-rail,.cpz-lead-card,.cpz-topic-card,.cpz-asset-card,.cpz-case-card,.cpz-products article{position:relative;overflow:hidden;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(7,12,23,.9),rgba(4,10,18,.76));backdrop-filter:blur(18px);box-shadow:var(--shell-shadow)}
.cpz-panel::before,.cpz-cover::before,.cpz-issue-rail::before,.cpz-lead-card::before,.cpz-topic-card::before,.cpz-asset-card::before,.cpz-case-card::before,.cpz-products article::before{content:"";position:absolute;inset:auto -18% -40% 25%;height:210px;background:radial-gradient(circle,rgba(61,233,202,.14),transparent 70%);pointer-events:none}
.cpz-kicker,.cpz-tag,.cpz-meta,.cpz-section-tag{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase}
.cpz-icon{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:999px;background:rgba(255,255,255,.06);font-style:normal}

.cpz-cover-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:18px}
.cpz-cover{padding:28px;border-radius:34px;clip-path:polygon(0 0,95% 0,100% 12%,100% 100%,5% 100%,0 88%)}
.cpz-cover h1{margin:0;max-width:12ch;font:700 clamp(3.8rem,8vw,7rem)/.88 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.cpz-cover-copy{display:grid;gap:16px}
.cpz-cover-intro{max-width:72ch;margin:0;color:var(--shell-muted);font-size:15px;line-height:1.82}
.cpz-actions{display:flex;flex-wrap:wrap;gap:12px}
.cpz-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 18px;border-radius:18px;font-weight:700;transition:transform .22s ease}
.cpz-btn.primary{background:linear-gradient(135deg,#82b6ff,#3ae9ca);color:#04111a}
.cpz-btn.secondary{border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text)}
.cpz-btn:hover{transform:translateY(-2px)}
.cpz-issue-chips{display:flex;flex-wrap:wrap;gap:10px}
.cpz-issue-rail{display:grid;gap:14px;padding:18px;border-radius:30px;clip-path:polygon(0 0,100% 0,100% 86%,94% 100%,0 100%)}
.cpz-issue-rail h2,.cpz-section-head h2,.cpz-lead-card h2,.cpz-assets h2,.cpz-cases h2{margin:0;font:700 clamp(1.8rem,3vw,2.8rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.cpz-issue-list{display:grid;gap:12px}
.cpz-issue-row{display:grid;grid-template-columns:52px 1fr;gap:12px;padding:12px 0;border-top:1px solid rgba(255,255,255,.07)}
.cpz-issue-row:first-child{border-top:0;padding-top:0}
.cpz-issue-row strong{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:18px;border:1px solid rgba(122,180,255,.18);background:rgba(7,18,36,.74);font:700 1rem/1 "Space Grotesk","Sora",sans-serif}
.cpz-issue-row span{color:var(--shell-muted);line-height:1.58}

.cpz-magazine{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(300px,.9fr);gap:18px}
.cpz-lead-card,.cpz-assets,.cpz-cases{padding:22px;border-radius:30px}
.cpz-lead-card{display:grid;gap:14px}
.cpz-lead-card p,.cpz-topic-card p,.cpz-asset-card p,.cpz-case-card p,.cpz-products p{margin:0;color:var(--shell-muted);line-height:1.72}
.cpz-link{display:inline-flex;align-items:center;gap:8px;font-weight:700;color:var(--shell-accent)}

.cpz-sections{display:grid;gap:16px}
.cpz-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px}
.cpz-topic-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.cpz-topic-card{padding:18px;border-radius:24px;background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,255,255,.025));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,95% 0,100% 12%,100% 100%,5% 100%,0 88%)}
.cpz-topic-card h3,.cpz-asset-card h3,.cpz-case-card h3,.cpz-products h3{margin:0 0 10px;font:700 1.4rem/1.08 "Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.cpz-topic-list{display:grid;gap:12px;margin-top:14px}
.cpz-topic-item{padding-top:12px;border-top:1px solid rgba(255,255,255,.07)}
.cpz-topic-item:first-child{padding-top:0;border-top:0}

.cpz-sidebar{display:grid;gap:18px}
.cpz-assets-list,.cpz-case-list,.cpz-products{display:grid;gap:14px}
.cpz-asset-card,.cpz-case-card{padding:18px;border-radius:24px;background:linear-gradient(165deg,rgba(8,18,33,.88),rgba(9,15,24,.68));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,100% 0,100% 84%,93% 100%,0 100%)}
.cpz-products{grid-template-columns:repeat(3,minmax(0,1fr))}
.cpz-products article{padding:18px;border-radius:24px;clip-path:polygon(0 0,96% 0,100% 14%,100% 100%,4% 100%,0 86%)}

@media (max-width: 1180px){
    .cpz-cover-grid,.cpz-magazine,.cpz-topic-grid,.cpz-products{grid-template-columns:1fr}
}
@media (max-width: 760px){
    .cpz{padding:18px 14px 54px}
    .cpz-cover{padding:22px;border-radius:28px}
    .cpz-cover h1{max-width:none;font-size:clamp(2.9rem,14vw,4.8rem)}
}
</style>

<section class="cpz">
    <div class="cpz-shell">
        <div class="cpz-cover-grid">
            <header class="cpz-cover">
                <div class="cpz-cover-copy">
                    <span class="cpz-kicker"><span class="cpz-icon">Z</span><?= htmlspecialchars($t('CPALNYA ZIN / issue 01', 'CPALNYA ZIN / issue 01'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h1 class="neo-title"><?= htmlspecialchars($t('CPA-журнал про трафик, фарм, креативы и backstage affiliate-команд, где ', 'A CPA magazine about traffic, farms, creatives and affiliate-team backstage where '), ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('темы разбиты как в хорошем зине', 'topics are split like a strong zine'), ENT_QUOTES, 'UTF-8') ?></strong></h1>
                    <p class="cpz-cover-intro"><?= htmlspecialchars($t('Главная страница теперь должна ощущаться не как типовой портал, а как цифровой CPA-zine: обложка номера, большие cover-stories, тематические колонки, issue-rail со смыслами и отдельный utility-слой с тем, что можно сразу забрать и применить.', 'The homepage should feel less like a generic portal and more like a digital CPA zine: issue cover, cover stories, themed columns, an issue rail with context and a separate utility layer with assets that can be used right away.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="cpz-issue-chips">
                        <?php foreach ($issueChips as $chip): ?>
                            <span class="cpz-tag"><span class="cpz-icon">◌</span><?= htmlspecialchars($chip, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="cpz-actions">
                        <a class="cpz-btn primary" href="/blog/"><span>↗</span><?= htmlspecialchars($t('Открыть журнал', 'Open the zine'), ENT_QUOTES, 'UTF-8') ?></a>
                        <a class="cpz-btn secondary" href="/solutions/downloads/"><span>↓</span><?= htmlspecialchars($t('Забрать ready-made assets', 'Get ready-made assets'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </div>
            </header>

            <aside class="cpz-issue-rail">
                <span class="cpz-section-tag"><span class="cpz-icon">◎</span><?= htmlspecialchars($t('Issue contents', 'Issue contents'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2 class="neo-title"><?= htmlspecialchars($t('Этот выпуск построен вокруг ', 'This issue is built around ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('четырех редакционных линий', 'four editorial lines'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                <div class="cpz-issue-list">
                    <div class="cpz-issue-row">
                        <strong>01</strong>
                        <span><?= htmlspecialchars($t('Источники, трекеры, аналитика и реальные сигналы по трафику.', 'Sources, trackers, analytics and real traffic signals.'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="cpz-issue-row">
                        <strong>02</strong>
                        <span><?= htmlspecialchars($t('Фарм, антидетект, аккаунты и кухня команды без глянца.', 'Farm, anti-detect, accounts and the unpolished team kitchen.'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="cpz-issue-row">
                        <strong>03</strong>
                        <span><?= htmlspecialchars($t('Креативы, связки, funnel-логика и why-it-converts разборы.', 'Creatives, setups, funnel logic and why-it-converts breakdowns.'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="cpz-issue-row">
                        <strong>04</strong>
                        <span><?= htmlspecialchars($t('Готовые решения, шаблоны, SOP и инструменты для внедрения.', 'Ready-made solutions, templates, SOPs and deployable tools.'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </aside>
        </div>

        <div class="cpz-magazine">
            <div class="cpz-sections">
                <?php if ($cover): ?>
                    <?php $coverCluster = trim((string)($cover['cluster_code'] ?? '')); ?>
                    <article class="cpz-lead-card">
                        <span class="cpz-section-tag"><span class="cpz-icon">✦</span><?= htmlspecialchars($t('Cover story', 'Cover story'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="neo-title"><?= htmlspecialchars((string)($cover['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($excerpt((string)($cover['excerpt_html'] ?? $cover['content_html'] ?? ''), 290), ENT_QUOTES, 'UTF-8') ?></p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <span class="cpz-meta"><span class="cpz-icon">◈</span><?= htmlspecialchars($coverCluster !== '' ? $coverCluster : $t('feature', 'feature'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="cpz-meta"><span class="cpz-icon">◌</span><?= (int)($cover['view_count'] ?? 0) ?> views</span>
                        </div>
                        <a class="cpz-link" href="/blog/<?= $coverCluster !== '' ? rawurlencode($coverCluster) . '/' : '' ?><?= rawurlencode((string)($cover['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Читать cover-story', 'Read cover story'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                    </article>
                <?php endif; ?>

                <section class="cpz-panel" style="padding:22px;border-radius:30px">
                    <div class="cpz-section-head">
                        <div>
                            <span class="cpz-section-tag"><span class="cpz-icon">▣</span><?= htmlspecialchars($t('Themes', 'Themes'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="neo-title"><?= htmlspecialchars($t('Журнал разбит на ', 'The zine is split into ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('тематические полосы', 'thematic columns'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                        </div>
                        <a class="cpz-link" href="/blog/"><?= htmlspecialchars($t('Все материалы', 'All articles'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                    </div>
                    <div class="cpz-topic-grid">
                        <?php foreach ($topicSections as $section): ?>
                            <article class="cpz-topic-card">
                                <span class="cpz-tag"><span class="cpz-icon"><?= htmlspecialchars((string)$section['icon'], ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars((string)$section['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="cpz-topic-list">
                                    <?php foreach ((array)$section['items'] as $item): ?>
                                        <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                                        <div class="cpz-topic-item">
                                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 120), ENT_QUOTES, 'UTF-8') ?></p>
                                            <a class="cpz-link" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть тему', 'Open topic'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <aside class="cpz-sidebar">
                <?php if ($secondaryLead): ?>
                    <?php $secondaryCluster = trim((string)($secondaryLead['cluster_code'] ?? '')); ?>
                    <article class="cpz-lead-card">
                        <span class="cpz-section-tag"><span class="cpz-icon">◎</span><?= htmlspecialchars($t('Feature note', 'Feature note'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="neo-title"><?= htmlspecialchars((string)($secondaryLead['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($excerpt((string)($secondaryLead['excerpt_html'] ?? $secondaryLead['content_html'] ?? ''), 170), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cpz-link" href="/blog/<?= $secondaryCluster !== '' ? rawurlencode($secondaryCluster) . '/' : '' ?><?= rawurlencode((string)($secondaryLead['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Читать заметку', 'Read feature'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                    </article>
                <?php endif; ?>

                <section class="cpz-assets">
                    <span class="cpz-section-tag"><span class="cpz-icon">↓</span><?= htmlspecialchars($t('Utility shelf', 'Utility shelf'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars($t('Рядом с журналом должен стоять ', 'Next to the zine there should be ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('рабочий shelf с решениями', 'a working solutions shelf'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                    <div class="cpz-assets-list">
                        <?php foreach (array_slice($downloads, 0, 2) as $item): ?>
                            <article class="cpz-asset-card">
                                <span class="cpz-tag"><span class="cpz-icon">↓</span><?= htmlspecialchars($t('Download', 'Download'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? ''), 110), ENT_QUOTES, 'UTF-8') ?></p>
                                <a class="cpz-link" href="/solutions/downloads/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть asset', 'Open asset'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                            </article>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($solutionArticles, 0, 2) as $item): ?>
                            <article class="cpz-asset-card">
                                <span class="cpz-tag"><span class="cpz-icon">▣</span><?= htmlspecialchars($t('Playbook', 'Playbook'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? ''), 110), ENT_QUOTES, 'UTF-8') ?></p>
                                <a class="cpz-link" href="/solutions/articles/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Читать playbook', 'Read playbook'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </aside>
        </div>

        <section class="cpz-cases">
            <div class="cpz-section-head">
                <div>
                    <span class="cpz-section-tag"><span class="cpz-icon">◇</span><?= htmlspecialchars($t('Field files', 'Field files'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars($t('Ниже идут кейсы и продукты как ', 'Below sit cases and products as ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('практическое продолжение выпуска', 'the practical continuation of the issue'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                </div>
            </div>
            <div class="cpz-assets-list">
                <?php foreach (array_slice($cases, 0, 2) as $item): ?>
                    <article class="cpz-case-card">
                        <span class="cpz-tag"><span class="cpz-icon">✦</span><?= htmlspecialchars($t('Case study', 'Case study'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cpz-link" href="/cases/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть кейс', 'Open case'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="cpz-products" style="margin-top:18px">
                <?php foreach (array_slice($projects, 0, 3) as $item): ?>
                    <article>
                        <span class="cpz-tag"><span class="cpz-icon">◎</span><?= htmlspecialchars($t('Product edge', 'Product edge'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars((string)($item['result_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cpz-link" href="/projects/<?= rawurlencode((string)($item['symbolic_code'] ?? $item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Смотреть продукт', 'View product'), ENT_QUOTES, 'UTF-8') ?> <span>↗</span></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>
