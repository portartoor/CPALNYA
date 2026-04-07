<?php
$home = (array)($ModelPage['home_portal'] ?? []);
$lang = (string)($home['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$issue = (array)($home['issue'] ?? []);
$issueImage = trim((string)($home['issue_image'] ?? '/april2026.png'));
$journalItems = array_values((array)($home['journal_items'] ?? []));
$playbookItems = array_values((array)($home['playbook_items'] ?? []));
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
    if ($slug === '') {
        return $section === 'playbooks' ? '/playbooks/' : '/journal/';
    }
    return function_exists('examples_article_url_path')
        ? examples_article_url_path($slug, $cluster, null, $section)
        : ($section === 'playbooks' ? '/playbooks/' : '/journal/');
};
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
.home-z-cover{border:1px solid rgba(255,255,255,.08);overflow:hidden;background:radial-gradient(circle at 50% 22%,rgba(103,200,255,.16),transparent 26%),linear-gradient(180deg,rgba(6,11,20,.96),rgba(4,8,16,.92))}
.home-z-cover img{display:block;width:100%;height:auto}
.home-z-actions{display:flex;gap:12px;flex-wrap:wrap}
.home-z-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.22),rgba(39,223,192,.18));color:var(--shell-text);text-decoration:none;font-weight:700}
.home-z-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.home-z-block{padding:22px;display:grid;gap:16px}
.home-z-list{display:grid;gap:14px}
.home-z-card{display:grid;gap:10px;padding:16px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);text-decoration:none;color:inherit}
.home-z-card-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.home-z-card h3{margin:0;font:700 1.2rem/1.12 "Space Grotesk","Sora",sans-serif}
.home-z-stat{display:inline-flex;align-items:center;gap:7px;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.home-z-stat-eye{font-style:normal;line-height:1}
@media (max-width:1180px){.home-z-hero,.home-z-grid{grid-template-columns:1fr}}
@media (max-width:720px){.home-z{padding:18px 14px 52px}}
</style>

<section class="home-z">
    <div class="home-z-shell">
        <header class="home-z-hero">
            <div class="home-z-copy">
                <span class="home-z-kicker"><?= htmlspecialchars((string)($issue['issue_kicker'] ?? $t('APRIL ISSUE / CPALNYA', 'APRIL ISSUE / CPALNYA')), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars((string)($issue['issue_title'] ?? $t("Апрель '26", "April '26")), ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars((string)($issue['issue_subtitle'] ?? $t('Сборный номер про backstage affiliate-операций, журнальные разборы и практику команд.', 'A composite issue about affiliate backstage, editorial breakdowns and operational playbooks.')), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (is_array($cover)): ?>
                    <h2><?= htmlspecialchars((string)($cover['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($excerpt((string)($cover['excerpt_html'] ?? $cover['content_html'] ?? ''), 260), ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <div class="home-z-actions">
                    <a class="home-z-btn" href="/journal/"><?= htmlspecialchars($t('Открыть журнал', 'Open journal'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="home-z-btn" href="/playbooks/"><?= htmlspecialchars($t('Открыть практику', 'Open playbooks'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>
            <div class="home-z-cover">
                <img src="<?= htmlspecialchars($issueImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($issue['issue_title'] ?? 'April issue cover'), ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </header>

        <div class="home-z-grid">
            <section class="home-z-block">
                <span class="home-z-tag"><?= htmlspecialchars($t('Журнал', 'Journal'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2><?= htmlspecialchars($t('Редакционные материалы номера', 'Editorial stories from the issue'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="home-z-list">
                    <?php foreach (array_slice($journalItems, 0, 4) as $item): ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'journal'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-head">
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">◉</i><?= (int)($item['view_count'] ?? 0) ?></span>
                            </div>
                            <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="home-z-block">
                <span class="home-z-tag"><?= htmlspecialchars($t('Практика', 'Playbooks'), ENT_QUOTES, 'UTF-8') ?></span>
                <h2><?= htmlspecialchars($t('Рабочие материалы и how-to', 'Working notes and how-to playbooks'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="home-z-list">
                    <?php foreach (array_slice($playbookItems, 0, 4) as $item): ?>
                        <a class="home-z-card" href="<?= htmlspecialchars($buildArticleUrl((array)$item, 'playbooks'), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="home-z-card-head">
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <span class="home-z-stat"><i class="home-z-stat-eye" aria-hidden="true">◉</i><?= (int)($item['view_count'] ?? 0) ?></span>
                            </div>
                            <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 150), ENT_QUOTES, 'UTF-8') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</section>
