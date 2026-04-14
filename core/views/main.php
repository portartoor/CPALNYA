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
$reviewsItems = array_values((array)($home['reviews_items'] ?? []));
$funItems = array_values((array)($home['fun_items'] ?? []));
$latestComments = array_values((array)($home['latest_comments'] ?? []));
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
        'reviews' => '/reviews/',
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
$formatDate = static function (array $item) use ($isRu): string {
    $raw = trim((string)($item['published_at'] ?? $item['created_at'] ?? ''));
    if ($raw === '') {
        return '';
    }

    try {
        $date = new DateTimeImmutable($raw);
        return $date->format($isRu ? 'd.m.Y' : 'M j, Y');
    } catch (Throwable $e) {
        return $raw;
    }
};
$formatCommentDate = static function (string $raw) use ($isRu): string {
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    try {
        $date = new DateTimeImmutable($raw);
        return $date->format($isRu ? 'd.m.Y' : 'M j, Y');
    } catch (Throwable $e) {
        return $raw;
    }
};
$commentExcerpt = static function (string $html, int $limit = 220): string {
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
};
$statIcon = static function (string $type): string {
    $icons = [
        'eye' => '<svg class="home-z-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 5.2c5.2 0 8.7 4.4 9.7 5.8.4.6.4 1.4 0 2-1 1.4-4.5 5.8-9.7 5.8S3.3 14.4 2.3 13a1.8 1.8 0 0 1 0-2c1-1.4 4.5-5.8 9.7-5.8Zm0 2C7.9 7.2 5 10.6 4 12c1 1.4 3.9 4.8 8 4.8s7-3.4 8-4.8c-1-1.4-3.9-4.8-8-4.8Zm0 1.7a3.1 3.1 0 1 1 0 6.2 3.1 3.1 0 0 1 0-6.2Zm0 2a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2Z"/></svg>',
        'comments' => '<svg class="home-z-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 4h14a3 3 0 0 1 3 3v7.2a3 3 0 0 1-3 3h-7.1l-5.2 3.9a1 1 0 0 1-1.6-.8v-3.1H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Zm14 2H5a1 1 0 0 0-1 1v7.2a1 1 0 0 0 1 1h1.1a1 1 0 0 1 1 1v2.1l3.9-2.9a1 1 0 0 1 .6-.2H19a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1Z"/></svg>',
    ];
    return $icons[$type] ?? '';
};
$telegramIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.94 4.66a1.5 1.5 0 0 0-1.68-.2L3.2 12.84a1 1 0 0 0 .1 1.85l4.34 1.49 1.66 5.06a1 1 0 0 0 1.73.33l2.42-3 4.76 3.48a1.5 1.5 0 0 0 2.36-.92l2.3-14.5a1.5 1.5 0 0 0-.93-1.97ZM9.4 15.47l-.56 3.03-.96-2.91 9.88-7.42-8.36 7.3Z"/></svg>';
$heroCard = is_array($heroFeature) ? $heroFeature : (is_array($cover) ? $cover : null);
$heroSection = trim((string)($heroCard['source_section'] ?? 'journal'));
$heroSectionTitles = [
    'journal' => $t('Журнал', 'Journal'),
    'playbooks' => $t('Практика', 'Playbooks'),
    'signals' => $t('Повестка', 'Signals'),
    'fun' => $t('Фан', 'Fun'),
];
?>
<style>
.home-z{max-width:1240px;margin:0 auto;padding:28px 18px 0;color:var(--shell-text)}
.home-z-shell{display:grid;gap:22px}
.home-z-hero,.home-z-block{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.home-z-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:18px;padding:28px;align-items:start}
.home-z-copy{display:grid;gap:12px}
.home-z-kicker,.home-z-tag,.home-z-meta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;max-height:30px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.home-z h1{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.home-z h2{margin:0;font:700 1.5rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.home-z-copy p,.home-z-card p{margin:0;color:var(--shell-muted);line-height:1.65}
.home-z-hero-lead{display:grid;gap:10px;max-width:72ch}
.home-z-cover{align-self:start;border:1px solid rgba(255,255,255,.08);overflow:hidden;background:radial-gradient(circle at 50% 22%,rgba(103,200,255,.16),transparent 26%),linear-gradient(180deg,rgba(6,11,20,.96),rgba(4,8,16,.92))}
.home-z-cover img{display:block;width:100%;height:auto}
.home-z-actions{display:flex;gap:10px;flex-wrap:wrap}
.home-z-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:9px 18px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));color:var(--shell-text);text-decoration:none;font-weight:700;font-size:13px;letter-spacing:.04em;text-transform:uppercase}
.home-z-btn-icon{display:inline-flex;align-items:center;justify-content:center;width:14px;min-width:14px;color:#f4d56b;font-size:12px;line-height:1}
.home-z-feature{display:grid;grid-template-columns:196px minmax(0,1fr);gap:14px;padding:14px 16px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(122,180,255,.05));align-items:start}
.home-z-feature-media{width:196px;aspect-ratio:1/1;align-self:start;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));overflow:hidden}
.home-z-feature-media img{display:block;width:100%;height:100%;object-fit:cover}
.home-z-feature-copy{display:grid;gap:10px;min-width:0;align-content:start}
.home-z-feature-top{display:flex;flex-wrap:wrap;gap:10px 14px;align-items:center}
.home-z-feature h3{margin:0;font:700 1.1rem/1.14 "Space Grotesk","Sora",sans-serif}
.home-z-feature a{color:inherit;text-decoration:none}
.home-z-feature-top .home-z-tag,.home-z-feature-top .home-z-meta{margin:0}
.home-z-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.home-z-block{padding:22px;display:grid;gap:18px;align-content:start}
.home-z-block-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:start;min-height:74px;margin-bottom:22px}
.home-z-block-title{display:grid;gap:10px;align-content:start}
.home-z-block-link{display:inline-flex;align-items:center;justify-content:center;padding:9px 12px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text);text-decoration:none;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
.home-z-list{display:grid;gap:14px;padding-left:0}
.home-z-card{display:grid;grid-template-columns:160px minmax(0,1fr);gap:14px;align-items:start;padding:0;border:0;background:transparent;text-decoration:none;color:inherit}
.home-z-card-media-wrap{display:grid;gap:8px;align-content:start}
.home-z-card-media{width:160px;aspect-ratio:1/1;align-self:start;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));overflow:hidden}
.home-z-card-media img{display:block;width:100%;height:100%;object-fit:cover}
.home-z-card-copy{display:grid;gap:8px;min-width:0;align-content:start}
.home-z-card-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.home-z-card h3{margin:0;font:700 1.06rem/1.16 "Space Grotesk","Sora",sans-serif}
.home-z-stat{display:inline-flex;align-items:center;gap:7px;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.home-z-card-stats{display:inline-flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.home-z-stat-icon{width:15px;height:15px;display:block;flex:0 0 15px;opacity:.86}
.home-z-comments{padding:24px;display:grid;gap:18px}
.home-z-block-wide{grid-column:1/-1}
.home-z-block-wide .home-z-list{grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.home-z-join{grid-column:1/-1;display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:18px;padding:24px;border:1px solid rgba(122,180,255,.16);background:linear-gradient(180deg,rgba(10,18,34,.92),rgba(6,12,24,.82));box-shadow:var(--shell-shadow)}
.home-z-join-copy{display:grid;gap:12px;align-content:start}
.home-z-join-copy p{margin:0;color:var(--shell-muted);line-height:1.68;max-width:70ch}
.home-z-join-actions{display:flex;flex-wrap:wrap;gap:10px}
.home-z-join-note{display:grid;gap:10px;align-content:start;padding:18px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(122,180,255,.05))}
.home-z-join-note strong{font:700 1rem/1.2 "Space Grotesk","Sora",sans-serif}
.home-z-join-note p{margin:0;color:var(--shell-muted);line-height:1.62}
.home-z-btn-telegram{background:linear-gradient(135deg,rgba(91,189,255,.22),rgba(57,141,255,.18));border-color:rgba(91,189,255,.28)}
.home-z-btn-telegram .home-z-btn-icon{width:16px;min-width:16px;height:16px;color:#7fd7ff;font-size:0}
.home-z-btn-telegram .home-z-btn-icon svg{display:block;width:16px;height:16px}
.home-z-comments-head{display:grid;gap:10px;max-width:880px}
.home-z-comments-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.home-z-comment-card{display:grid;gap:14px;padding:18px 18px 20px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(122,180,255,.05));text-decoration:none;color:inherit}
.home-z-comment-top{display:flex;align-items:center;justify-content:space-between;gap:12px}
.home-z-comment-author{display:flex;align-items:center;gap:12px;min-width:0}
.home-z-comment-avatar{width:42px;height:42px;border-radius:50%;overflow:hidden;flex:0 0 42px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.05)}
.home-z-comment-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.home-z-comment-person{display:grid;gap:3px;min-width:0}
.home-z-comment-person strong{font:700 .98rem/1.1 "Space Grotesk","Sora",sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.home-z-comment-person span{color:var(--shell-muted);font-size:12px;letter-spacing:.08em;text-transform:uppercase}
.home-z-comment-score{display:inline-flex;align-items:center;gap:6px;color:#f4d56b;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.home-z-comment-quote{position:relative;margin:0;padding-left:18px;font:600 1.04rem/1.6 "Space Grotesk","Sora",sans-serif;letter-spacing:-.02em}
.home-z-comment-quote::before{content:"";position:absolute;left:0;top:2px;bottom:2px;width:3px;background:linear-gradient(180deg,rgba(244,213,107,.9),rgba(115,184,255,.85))}
.home-z-comment-article{display:grid;gap:5px;padding-top:6px;border-top:1px solid rgba(122,180,255,.12)}
.home-z-comment-article span{color:var(--shell-muted);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.home-z-comment-article strong{font:700 .96rem/1.35 "Space Grotesk","Sora",sans-serif}
@media (max-width:1180px){.home-z-hero,.home-z-grid{grid-template-columns:1fr}}
@media (max-width:720px){
    .home-z{padding:18px 14px 52px}
    .home-z-hero{padding:0 0 18px;overflow:hidden}
    .home-z-cover{order:-1;border-top:0;border-left:0;border-right:0}
    .home-z-copy{padding:0 18px}
    .home-z-block-head{grid-template-columns:1fr}
    .home-z-join{grid-template-columns:1fr}
    .home-z-feature{grid-template-columns:1fr}
    .home-z-feature-media{width:100%;aspect-ratio:16/10}
    .home-z-card{grid-template-columns:1fr}
    .home-z-block-wide .home-z-list{grid-template-columns:1fr}
    .home-z-card-media-wrap{width:100%}
    .home-z-card-media{width:100%;aspect-ratio:1/1}
    .home-z-comments-grid{grid-template-columns:1fr}
}
</style>

<section class="home-z">
    <div class="home-z-shell">
        <header class="home-z-hero">
            <div class="home-z-copy">
                <span class="home-z-kicker"><?= htmlspecialchars((string)($issue['issue_kicker'] ?? $t('APRIL ISSUE / ЦПАЛЬНЯ', 'APRIL ISSUE / ЦПАЛЬНЯ')), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars((string)($issue['issue_title'] ?? $t("Апрель '26", "April '26")), ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="home-z-hero-lead">
                    <p><?= htmlspecialchars($t('Журнал про арбитраж трафика как живую редакционную среду: здесь важен не фасад отрасли, а внутренний ритм affiliate-команд, где источники дрейфуют, связки пересобираются, а решения принимаются под давлением реального рынка.', 'A journal about traffic arbitrage as a living editorial environment: less about the showcase layer, more about the working rhythm of affiliate teams under real market pressure.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars((string)($issue['issue_subtitle'] ?? $t('ЦПАЛЬНЯ собирает это не как ленту, а как номер. Апрель ’26 посвящен backstage affiliate-операций: источникам трафика, фарму, креативным связкам, трекерам, модерации и playbooks, к которым возвращаются, когда рынок ускоряется.', 'ЦПАЛЬНЯ assembles this not as a feed, but as an issue. April ’26 stays focused on the backstage of affiliate operations: sources, farm, creative bundles, trackers, moderation, and playbooks worth returning to when the market speeds up.')), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="home-z-actions">
                    <a class="home-z-btn" href="/journal/"><span class="home-z-btn-icon" aria-hidden="true">✦</span><span><?= htmlspecialchars($t('В журнал', 'Journal'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/playbooks/"><span class="home-z-btn-icon" aria-hidden="true">⚙</span><span><?= htmlspecialchars($t('Практика', 'Playbooks'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/signals/"><span class="home-z-btn-icon" aria-hidden="true">⌁</span><span><?= htmlspecialchars($t('Повестка', 'Signals'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/fun/"><span class="home-z-btn-icon" aria-hidden="true">✺</span><span><?= htmlspecialchars($t('Фан', 'Fun'), ENT_QUOTES, 'UTF-8') ?></span></a>
                    <a class="home-z-btn" href="/discussion/"><span class="home-z-btn-icon" aria-hidden="true">☰</span><span><?= htmlspecialchars($t('Обсуждение', 'Discussion'), ENT_QUOTES, 'UTF-8') ?></span></a>
                </div>
                <?php if (is_array($heroCard)): ?>
                    <?php $heroCardImage = $imageSrc((array)$heroCard); ?>
                    <?php $heroCardDate = $formatDate((array)$heroCard); ?>
                    <div class="home-z-feature">
                        <div class="home-z-feature-media">
                            <?php if ($heroCardImage !== ''): ?>
                                <img src="<?= htmlspecialchars($heroCardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($heroCard['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                        </div>
                        <div class="home-z-feature-copy">
                            <div class="home-z-feature-top">
                                <span class="home-z-tag"><?= htmlspecialchars($t('Редакция рекомендует', 'Editors recommend'), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($heroCardDate !== ''): ?>
                                    <span class="home-z-meta"><?= htmlspecialchars($heroCardDate, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <span class="home-z-card-stats">
                                    <span class="home-z-stat"><?= $statIcon('eye') ?><?= (int)($heroCard['view_count'] ?? 0) ?></span>
                                    <span class="home-z-stat"><?= $statIcon('comments') ?><?= (int)($heroCard['comment_count'] ?? 0) ?></span>
                                </span>
                            </div>
                            <h3><a href="<?= htmlspecialchars($buildArticleUrl((array)$heroCard, $heroSection), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($heroCard['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></h3>
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
                        <h2><?= htmlspecialchars($t('Материалы номера', 'Issue stories'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/journal/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($journalItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <?php $cardDate = $formatDate((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'journal'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media-wrap">
                                <?php if ($cardDate !== ''): ?>
                                    <p class="home-z-meta"><?= htmlspecialchars($cardDate, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <div class="home-z-card-media">
                                    <?php if ($cardImage !== ''): ?>
                                        <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-card-stats">
                                        <span class="home-z-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                        <span class="home-z-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                    </span>
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
                        <h2><?= htmlspecialchars($t('Практика и how-to', 'How-to playbooks'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/playbooks/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($playbookItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <?php $cardDate = $formatDate((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'playbooks'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media-wrap">
                                <?php if ($cardDate !== ''): ?>
                                    <p class="home-z-meta"><?= htmlspecialchars($cardDate, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <div class="home-z-card-media">
                                    <?php if ($cardImage !== ''): ?>
                                        <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-card-stats">
                                        <span class="home-z-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                        <span class="home-z-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                    </span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="home-z-join">
                <div class="home-z-join-copy">
                    <span class="home-z-tag"><?= htmlspecialchars($t('Войти в обсуждение', 'Join the discussion'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($t('Зарегистрируйся, чтобы спорить, добавлять практику и продолжать материалы вместе с ЦПАЛЬНЯ.', 'Create an account to argue, add field notes, and keep the material going with the rest of CPALNYA.'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($t('Личный кабинет нужен не для галочки, а чтобы оставлять комментарии, оценивать чужие реплики, возвращаться к болевым темам и наблюдать, как материалы доживают в обсуждении.', 'The account is not there for ceremony. It lets readers comment, rate replies, return to the sharper threads, and keep the material alive after publication.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="home-z-join-actions">
                        <a class="home-z-btn" href="/account/"><span class="home-z-btn-icon" aria-hidden="true">+</span><span><?= htmlspecialchars($t('Вход / регистрация', 'Sign in / register'), ENT_QUOTES, 'UTF-8') ?></span></a>
                        <a class="home-z-btn home-z-btn-telegram" href="https://t.me/cpalnya_journal" target="_blank" rel="noopener noreferrer">
                            <span class="home-z-btn-icon" aria-hidden="true"><?= $telegramIcon ?></span>
                            <span><?= htmlspecialchars($t('Обсуждать в Telegram', 'Discuss in Telegram'), ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    </div>
                </div>
                <div class="home-z-join-note">
                    <span class="home-z-meta"><?= htmlspecialchars($t('Telegram-канал обсуждений', 'Telegram discussion room'), ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= htmlspecialchars($t('Если хочется обсудить материалы быстрее и живее, иди прямо в чат.', 'If you want a faster, louder discussion around the issue, jump straight into the Telegram room.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($t('Там обычно появляются те наблюдения, которые не доходят до формального комментария: короткие споры, тихие edge cases, дополнения по связкам и настоящая операционка без витрины.', 'That is where the smaller observations usually surface first: quick arguments, edge cases, extra setup detail, and the operational reality before it turns into a formal comment.'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </section>

            <section class="home-z-block">
                <div class="home-z-block-head">
                    <div class="home-z-block-title">
                        <span class="home-z-tag"><?= htmlspecialchars($t('Повестка', 'Signals'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Новости и сигналы', 'News and signals'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/signals/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($signalsItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <?php $cardDate = $formatDate((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'signals'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media-wrap">
                                <?php if ($cardDate !== ''): ?>
                                    <p class="home-z-meta"><?= htmlspecialchars($cardDate, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <div class="home-z-card-media">
                                    <?php if ($cardImage !== ''): ?>
                                        <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-card-stats">
                                        <span class="home-z-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                        <span class="home-z-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                    </span>
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
                        <h2><?= htmlspecialchars($t('Легкая редакция', 'Light editorial'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/fun/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($funItems, 0, 5) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <?php $cardDate = $formatDate((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'fun'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media-wrap">
                                <?php if ($cardDate !== ''): ?>
                                    <p class="home-z-meta"><?= htmlspecialchars($cardDate, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <div class="home-z-card-media">
                                    <?php if ($cardImage !== ''): ?>
                                        <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-card-stats">
                                        <span class="home-z-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                        <span class="home-z-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                    </span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="home-z-block home-z-block-wide">
                <div class="home-z-block-head">
                    <div class="home-z-block-title">
                        <span class="home-z-tag"><?= htmlspecialchars($t('Обзоры', 'Reviews'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($t('Реальные сравнения и подборки', 'Real comparisons and shortlists'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <a class="home-z-block-link" href="/reviews/"><?= htmlspecialchars($t('Все материалы', 'All materials'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="home-z-list">
                    <?php foreach (array_slice($reviewsItems, 0, 4) as $item): ?>
                        <?php $cardImage = $imageSrc((array)$item); ?>
                        <?php $cardDate = $formatDate((array)$item); ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'reviews'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-media-wrap">
                                <?php if ($cardDate !== ''): ?>
                                    <p class="home-z-meta"><?= htmlspecialchars($cardDate, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <div class="home-z-card-media">
                                    <?php if ($cardImage !== ''): ?>
                                        <img src="<?= htmlspecialchars($cardImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="home-z-card-copy">
                                <div class="home-z-card-head">
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="home-z-card-stats">
                                        <span class="home-z-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                        <span class="home-z-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                    </span>
                                </div>
                                <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 170), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <?php if (!empty($latestComments)): ?>
            <section class="home-z-block home-z-comments">
                <div class="home-z-comments-head">
                    <span class="home-z-tag"><?= htmlspecialchars($t('Обсуждение номера', 'From the discussion'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($t('Последние реплики под материалами', 'Latest comments across the issue'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($t('Здесь в фокусе не статья, а то, как ее дочитывают вслух: короткие возражения, уточнения из практики и тихие замечания, которые продолжают материал уже в обсуждении.', 'The focus here is not the article itself, but how readers continue it out loud: short objections, practical clarifications and the quieter notes that carry the piece forward in discussion.'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="home-z-comments-grid">
                    <?php foreach ($latestComments as $comment): ?>
                        <?php
                        $commentAuthor = trim((string)($comment['display_name'] ?? ''));
                        if ($commentAuthor === '') {
                            $commentAuthor = trim((string)($comment['username'] ?? ''));
                        }
                        if ($commentAuthor === '') {
                            $commentAuthor = $t('Участник', 'Member');
                        }
                        $commentDate = $formatCommentDate((string)($comment['created_at'] ?? ''));
                        $commentBody = $commentExcerpt((string)($comment['body_html'] ?? ''), 210);
                        $articleTitle = trim((string)($comment['article_title'] ?? $t('Материал', 'Article')));
                        ?>
                        <a class="home-z-comment-card" href="<?= htmlspecialchars((string)($comment['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-comment-top">
                                <div class="home-z-comment-author">
                                    <span class="home-z-comment-avatar">
                                        <img src="<?= htmlspecialchars((string)($comment['avatar_src'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($commentAuthor, ENT_QUOTES, 'UTF-8') ?>">
                                    </span>
                                    <span class="home-z-comment-person">
                                        <strong><?= htmlspecialchars($commentAuthor, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars($commentDate, ENT_QUOTES, 'UTF-8') ?></span>
                                    </span>
                                </div>
                                <span class="home-z-comment-score"><?= (int)($comment['rating_score'] ?? 0) ?></span>
                            </div>
                            <blockquote class="home-z-comment-quote"><?= htmlspecialchars($commentBody, ENT_QUOTES, 'UTF-8') ?></blockquote>
                            <div class="home-z-comment-article">
                                <span><?= htmlspecialchars($t('К материалу', 'In article'), ENT_QUOTES, 'UTF-8') ?></span>
                                <strong><?= htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>
