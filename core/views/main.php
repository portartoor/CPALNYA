<?php
$home = (array)($ModelPage['home_portal'] ?? []);
$lang = (string)($home['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$issue = (array)($home['issue'] ?? []);
$issueImage = trim((string)($home['issue_image'] ?? '/april2026_new2.png'));
$heroFeature = is_array($home['hero_feature'] ?? null) ? $home['hero_feature'] : null;
$journalItems = array_values((array)($home['journal_items'] ?? []));
$playbookItems = array_values((array)($home['playbook_items'] ?? []));
$signalsItems = array_values((array)($home['signals_items'] ?? []));
$funItems = array_values((array)($home['fun_items'] ?? []));
$cover = $journalItems[0] ?? null;
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
$excerpt = static function (string $html, int $limit = 180): string {
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
};
$buildArticleUrl = static function (array $item, string $section): string {
    $slug = trim((string)($item['slug'] ?? ''));
    $cluster = trim((string)($item['cluster_code'] ?? ''));
    $sectionBaseMap = [
        'journal' => '/journal/',
        'playbooks' => '/playbooks/',
        'signals' => '/signals/',
        'fun' => '/fun/',
    ];
    if ($slug === '') {
        return $sectionBaseMap[$section] ?? '/journal/';
    }
    return function_exists('examples_article_url_path')
        ? examples_article_url_path($slug, $cluster, null, $section)
        : ($sectionBaseMap[$section] ?? '/journal/');
};
$imageSrc = static function (array $item): string {
    $thumb = trim((string)($item['preview_image_thumb_url'] ?? ''));
    $full = trim((string)($item['preview_image_url'] ?? ''));
    $data = trim((string)($item['preview_image_data'] ?? ''));
    $legacy = trim((string)($item['image_src'] ?? ''));
    if ($thumb !== '') {
        return $thumb;
    }
    if ($full !== '') {
        return $full;
    }
    if ($data !== '') {
        return $data;
    }
    return $legacy;
};
$heroCard = is_array($heroFeature) ? $heroFeature : (is_array($cover) ? $cover : null);
?>
<style>
.home-z{max-width:1480px;margin:0 auto;padding:28px 18px 64px;color:var(--shell-text)}
.home-z-shell{display:grid;gap:22px}
.home-z-hero,.home-z-block{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.home-z-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:18px;padding:28px}
.home-z-copy{display:grid;gap:12px}
.home-z-kicker,.home-z-tag,.home-z-meta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;max-height:30px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.home-z h1{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.home-z h2{margin:0;font:700 1.5rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.home-z-copy p,.home-z-card p{margin:0;color:var(--shell-muted);line-height:1.65}
.home-z-hero-lead{display:grid;gap:10px;max-width:72ch}
.home-z-cover{border:1px solid rgba(255,255,255,.08);overflow:hidden;background:radial-gradient(circle at 50% 22%,rgba(103,200,255,.16),transparent 26%),linear-gradient(180deg,rgba(6,11,20,.96),rgba(4,8,16,.92))}
.home-z-cover img{display:block;width:100%;height:auto}
.home-z-actions{display:flex;gap:10px;flex-wrap:wrap}
.home-z-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:9px 18px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));color:var(--shell-text);text-decoration:none;font-weight:700;font-size:13px;letter-spacing:.04em;text-transform:uppercase}
.home-z-btn-icon{display:inline-flex;align-items:center;justify-content:center;width:14px;min-width:14px;color:#f4d56b;font-size:12px;line-height:1}
.home-z-feature{display:grid;grid-template-columns:180px minmax(0,1fr);gap:14px;padding:14px 16px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(122,180,255,.05));align-items:stretch}
.home-z-feature-media{width:180px;height:100%;min-height:100%;align-self:stretch;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));overflow:hidden}
.home-z-feature-media img{display:block;width:100%;height:100%;object-fit:cover}
.home-z-feature-copy{display:grid;gap:10px;min-width:0;align-content:start}
.home-z-feature-top{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.home-z-feature h3{margin:0;font:700 1.1rem/1.14 "Space Grotesk","Sora",sans-serif}
.home-z-feature a{color:inherit;text-decoration:none}
.home-z-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.home-z-block{padding:22px;display:grid;gap:18px;align-content:start}
.home-z-block-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:start;min-height:74px;margin-bottom:22px}
.home-z-block-title{display:grid;gap:10px;align-content:start}
.home-z-block-link{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text);text-decoration:none;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
.home-z-list{display:grid;gap:14px;padding-left:0}
.home-z-card{display:grid;grid-template-columns:148px minmax(0,1fr);gap:14px;align-items:stretch;padding:0;border:0;background:transparent;text-decoration:none;color:inherit}
.home-z-card-media{width:148px;height:100%;min-height:100%;align-self:stretch;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));overflow:hidden}
.home-z-card-media img{display:block;width:100%;height:100%;object-fit:cover}
.home-z-card-copy{display:grid;gap:8px;min-width:0;align-content:start}
.home-z-card-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.home-z-card h3{margin:0;font:700 1.06rem/1.16 "Space Grotesk","Sora",sans-serif}
.home-z-stat{display:inline-flex;align-items:center;gap:7px;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.home-z-stat-eye{font-style:normal;line-height:1}
@media (max-width:1180px){.home-z-hero,.home-z-grid{grid-template-columns:1fr}}
@media (max-width:720px){
    .home-z{padding:18px 14px 52px}
    .home-z-block-head{grid-template-columns:1fr}
    .home-z-feature{grid-template-columns:1fr}
    .home-z-feature-media{width:100%;height:180px;min-height:180px}
    .home-z-card{grid-template-columns:1fr}
    .home-z-card-media{width:100%;height:180px;min-height:180px}
}
</style>

<section class="home-z">
    <div class="home-z-shell">
        <header class="home-z-hero">
            <div class="home-z-copy">
                <span class="home-z-kicker"><?= htmlspecialchars((string)($issue['issue_kicker'] ?? $t('APRIL ISSUE / ЦПАЛЬНЯ', 'APRIL ISSUE / ЦПАЛЬНЯ')), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars((string)($issue['issue_title'] ?? $t("Апрель '26", "April '26")), ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="home-z-hero-lead">
                    <p><?= htmlspecialchars($t('Журнал про арбитраж трафика как про живую редакционную среду: здесь важны не витринные обещания, а внутренний ритм affiliate-команд, где источники дрейфуют, связки пересобираются, креативы стареют быстрее календаря, а решения принимаются под давлением реального рынка.', 'A journal about traffic arbitrage as a living editorial environment: not showcase promises, but the internal rhythm of affiliate teams, where sources drift, bundles get rebuilt, creatives age faster than the calendar, and decisions are made under real market pressure.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($t('ЦПАЛЬНЯ собирает это не как ленту, а как номер. Редакционные разборы, market notes, backstage-наблюдения и практические материалы работают здесь вместе, чтобы показывать механику affiliate-операций изнутри, а не пересказывать шум отрасли снаружи.', 'ЦПАЛЬНЯ assembles this not as a feed, but as an issue. Editorial breakdowns, market notes, backstage observations, and practical materials work together here to show affiliate operations from the inside rather than echoing industry noise from the outside.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars((string)($issue['issue_subtitle'] ?? $t('Лицо выпуска: трекинг, источники, срок жизни креативов, дисциплина командной операционки и те решения, к которым возвращаются, когда рынок снова начинает качать.', 'The face of the issue: tracking, sources, creative shelf life, team operations discipline, and the decisions people return to when the market starts shaking again.')), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="home-z-actions">
                    <a class="home-z-btn" href="/journal/"><span class="home-z-btn-icon" aria-hidden="true">✦</span><span><?= htmlspecialchars($t('В журнал', 'Journal'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/playbooks/"><span class="home-z-btn-icon" aria-hidden="true">⚙</span><span><?= htmlspecialchars($t('Практика', 'Playbooks'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/signals/"><span class="home-z-btn-icon" aria-hidden="true">⌁</span><span><?= htmlspecialchars($t('Повестка', 'Signals'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/fun/"><span class="home-z-btn-icon" aria-hidden="true">✺</span><span><?= htmlspecialchars($t('Фан', 'Fun'), ENT_QUOTES, 'UTF-8') ?></span></a>
                </div>
                <?php if (is_array($heroCard)): ?>
                    <?php $heroCardImage = $imageSrc((array)$heroCard); ?>
                    <div class="home-z-feature">
                        <div class="home-z-feature-media">
                            <?php if ($heroCardImage !== ''): ?>
                                <img src="<?= htmlspecialchars($heroCardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($heroCard['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                        </div>
                        <div class="home-z-feature-copy">
                            <div class="home-z-feature-top">
                                <span class="home-z-tag"><?= htmlspecialchars($t('Из журнала', 'From the journal'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">&#9673;</i><?= (int)($heroCard['view_count'] ?? 0) ?></span>
                            </div>
                            <h3><a href="<?= htmlspecialchars($buildArticleUrl((array)$heroCard, 'journal'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($heroCard['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></h3>
                            <p><?= htmlspecialchars($excerpt((string)($heroCard['excerpt_html'] ?? $heroCard['content_html'] ?? ''), 240), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="home-z-cover">
                <img src="<?= htmlspecialchars($issueImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($issue['issue_title'] ?? 'April issue cover'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </header>

        <div class="home-z-grid">
            <section class="home-z-block">
                <div class="home-z-block-head">
                    <div class="home-z-block-title">
                        <span class="home-z-tag"><?= htmlspecialchars($t('Журнал', 'Journal'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Редакционные материалы номера', 'Editorial stories from the issue'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/journal/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($journalItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'journal'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media">
                                <?php if ($cardImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">&#9673;</i><?= (int)($item['view_count'] ?? 0) ?></span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="home-z-block">
                <div class="home-z-block-head">
                    <div class="home-z-block-title">
                        <span class="home-z-tag"><?= htmlspecialchars($t('Практика', 'Playbooks'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Рабочие материалы и how-to', 'Working notes and how-to playbooks'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/playbooks/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($playbookItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'playbooks'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media">
                                <?php if ($cardImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">&#9673;</i><?= (int)($item['view_count'] ?? 0) ?></span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="home-z-block">
                <div class="home-z-block-head">
                    <div class="home-z-block-title">
                        <span class="home-z-tag"><?= htmlspecialchars($t('Повестка', 'Signals'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Политика, новости и сигналы', 'Policy, news and market signals'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/signals/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($signalsItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'signals'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media">
                                <?php if ($cardImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">&#9673;</i><?= (int)($item['view_count'] ?? 0) ?></span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="home-z-block">
                <div class="home-z-block-head">
                    <div class="home-z-block-title">
                        <span class="home-z-tag"><?= htmlspecialchars($t('Фан', 'Fun'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Отдых, мемы и нишевой абсурд', 'Satire, memes and niche absurdity'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/fun/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($funItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'fun'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media">
                                <?php if ($cardImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">&#9673;</i><?= (int)($item['view_count'] ?? 0) ?></span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</section>
