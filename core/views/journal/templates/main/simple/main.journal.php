<?php
$journal = (array)($ModelPage['journal'] ?? []);
$items = (array)($journal['items'] ?? []);
$selected = is_array($journal['selected'] ?? null) ? $journal['selected'] : null;
$issue = (array)($journal['issue'] ?? []);
$clusters = (array)($journal['clusters'] ?? []);
$lang = (string)($journal['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$page = max(1, (int)($journal['page'] ?? 1));
$totalPages = max(1, (int)($journal['total_pages'] ?? 1));
$currentCluster = trim((string)($journal['current_cluster'] ?? ''));
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
$strip = static function (string $html, int $limit = 170): string {
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
};
$issueImage = trim((string)($issue['hero_image_url'] ?? ''));
if ($issueImage === '') {
    $issueImage = trim((string)($issue['hero_image_data'] ?? ''));
}
if ($issueImage === '') {
    $issueImage = '/april2026.png';
}
$buildPageUrl = static function (?string $cluster = '', int $pageNum = 1): string {
    $base = function_exists('examples_cluster_list_path')
        ? examples_cluster_list_path((string)$cluster, null)
        : '/journal/';
    if ($pageNum > 1) {
        $base .= '?' . http_build_query(['page' => $pageNum]);
    }
    return $base;
};
$relatedItems = [];
if ($selected) {
    foreach ($items as $item) {
        if ((int)($item['id'] ?? 0) === (int)($selected['id'] ?? 0)) {
            continue;
        }
        $relatedItems[] = $item;
        if (count($relatedItems) >= 4) {
            break;
        }
    }
}
?>
<style>
.jrnl{max-width:1480px;margin:0 auto;padding:28px 18px 64px;color:var(--shell-text)}
.jrnl-shell{display:grid;gap:24px}
.jrnl-hero,.jrnl-detail,.jrnl-card,.jrnl-related,.jrnl-empty{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.jrnl-hero,.jrnl-detail,.jrnl-related,.jrnl-empty{padding:28px}
.jrnl-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:22px}
.jrnl-kicker,.jrnl-tag,.jrnl-meta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.jrnl-copy{display:grid;gap:16px}
.jrnl-copy h1,.jrnl-copy h2,.jrnl-detail h1,.jrnl-related h2{margin:0;font:700 clamp(2.1rem,4.2vw,4.1rem)/.98 "Space Grotesk","Sora",sans-serif;letter-spacing:-.055em}
.jrnl-copy p,.jrnl-detail p,.jrnl-card p{margin:0;color:var(--shell-muted);line-height:1.75}
.jrnl-cover{min-height:0;border:1px solid rgba(255,255,255,.08);background:
radial-gradient(circle at 50% 22%,rgba(103,200,255,.16),transparent 26%),
linear-gradient(180deg,rgba(6,11,20,.96),rgba(4,8,16,.92));
display:block;align-self:start;overflow:hidden;position:relative}
.jrnl-cover::before,.jrnl-cover::after{content:"";position:absolute;inset:-1px;border-radius:inherit;pointer-events:none;opacity:0}
.jrnl-cover::before{background:
radial-gradient(circle at 0% 50%,rgba(99,244,255,.95) 0,rgba(99,244,255,.22) 12%,transparent 24%),
radial-gradient(circle at 100% 50%,rgba(109,255,200,.88) 0,rgba(109,255,200,.18) 10%,transparent 22%);
filter:blur(8px) saturate(1.15)}
.jrnl-cover::after{inset:0;border:1px solid rgba(121,196,255,.16);box-shadow:0 0 0 1px rgba(105,196,255,.05) inset}
.jrnl-cover-button{display:block;padding:0;cursor:zoom-in;width:100%;text-align:left;appearance:none;line-height:0}
.jrnl-cover-button:hover::before,.jrnl-cover-button:focus-visible::before{opacity:1;animation:jrnlBorderFireflies 3.6s linear infinite}
.jrnl-cover-button:hover::after,.jrnl-cover-button:focus-visible::after{border-color:rgba(122,214,255,.38);box-shadow:0 0 24px rgba(74,205,255,.22),0 0 52px rgba(58,255,198,.12),0 0 0 1px rgba(105,196,255,.1) inset}
.jrnl-cover img{position:relative;display:block;width:100%;height:auto;max-width:none;object-fit:contain;object-position:center center;padding:0;transform-origin:50% 0;animation:jrnlCoverIntro 1.15s cubic-bezier(.16,1,.3,1) both}
.jrnl-cover-note{position:relative;z-index:1;max-width:22ch;margin:18px;padding:14px 16px;background:rgba(4,9,18,.78);border:1px solid rgba(122,180,255,.18);font:700 1rem/1.35 "Space Grotesk","Sora",sans-serif}
.jrnl-tags{display:flex;flex-wrap:wrap;gap:10px}
.jrnl-tag{color:var(--shell-muted);text-decoration:none}
.jrnl-tag.is-active,.jrnl-tag:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
.jrnl-card{display:grid;gap:14px;padding:16px;text-decoration:none;color:inherit;min-height:100%}
.jrnl-card-media{aspect-ratio:16/10;background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));border:1px solid rgba(255,255,255,.08);overflow:hidden}
.jrnl-card-media img{width:100%;height:100%;object-fit:cover;display:block}
.jrnl-card h3{margin:0;font:700 1.28rem/1.15 "Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.jrnl-card-foot{display:flex;justify-content:space-between;gap:10px;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.jrnl-pager{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}
.jrnl-pager a,.jrnl-pager span{display:inline-flex;align-items:center;justify-content:center;min-width:44px;padding:10px 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-muted);text-decoration:none}
.jrnl-pager .is-active{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.12)}
.jrnl-detail{display:grid;gap:18px}
.jrnl-detail-body{display:grid;gap:18px}
.jrnl-detail-cover{max-height:540px;overflow:hidden;border:1px solid rgba(255,255,255,.08)}
.jrnl-detail-cover img{width:100%;display:block}
.jrnl-detail-content{font-size:16px;line-height:1.82;color:var(--shell-text)}
.jrnl-detail-content h2,.jrnl-detail-content h3{font-family:"Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.jrnl-actions{display:flex;gap:12px;flex-wrap:wrap}
.jrnl-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.22),rgba(39,223,192,.18));color:var(--shell-text);text-decoration:none;font-weight:700}
.jrnl-related-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.jrnl-empty{text-align:center}
.jrnl-lightbox{position:fixed;inset:0;z-index:2147483000;display:grid;place-items:center;padding:clamp(16px,4vw,48px);pointer-events:none;opacity:0;visibility:hidden;transition:opacity .52s ease,visibility .52s ease}
.jrnl-lightbox.is-open{opacity:1;visibility:visible;pointer-events:auto}
.jrnl-lightbox-backdrop{position:absolute;inset:0;background:
radial-gradient(circle at 50% 45%,rgba(91,210,255,.18),transparent 22%),
radial-gradient(circle at 50% 50%,rgba(32,255,191,.11),transparent 34%),
rgba(1,4,10,.965);
backdrop-filter:blur(20px) saturate(1.24) brightness(.28)}
.jrnl-lightbox-bloom{position:absolute;inset:0;opacity:0;mix-blend-mode:screen;background:
conic-gradient(from 90deg at 50% 50%,rgba(107,193,255,.0) 0deg,rgba(107,193,255,.55) 34deg,rgba(68,255,220,.0) 88deg,rgba(68,255,220,.4) 150deg,rgba(107,193,255,.0) 220deg,rgba(107,193,255,.36) 292deg,rgba(68,255,220,.0) 360deg);
transform:scale(.78) rotate(-18deg);filter:blur(42px)}
.jrnl-lightbox.is-open .jrnl-lightbox-bloom{opacity:1;animation:jrnlBloomIn .95s cubic-bezier(.16,1,.3,1) forwards}
.jrnl-lightbox-shell{position:relative;z-index:2;width:min(1320px,94vw);display:grid;gap:18px;justify-items:center}
.jrnl-lightbox-frame{position:relative;width:min(1240px,92vw);max-height:82vh;padding:18px;border-radius:34px;background:linear-gradient(180deg,rgba(8,16,30,.88),rgba(4,8,18,.76));border:1px solid rgba(143,210,255,.24);box-shadow:0 40px 120px rgba(0,0,0,.58),0 0 0 1px rgba(141,229,255,.08) inset;overflow:hidden;transform:translateY(24px) scale(.9) rotateX(16deg);opacity:0}
.jrnl-lightbox.is-open .jrnl-lightbox-frame{animation:jrnlFrameIn .88s cubic-bezier(.18,1,.22,1) forwards}
.jrnl-lightbox-frame::before{content:"";position:absolute;inset:0;background:
linear-gradient(130deg,rgba(255,255,255,.18),transparent 20%,transparent 72%,rgba(96,210,255,.12)),
radial-gradient(circle at 50% 0%,rgba(98,219,255,.18),transparent 42%);
pointer-events:none}
.jrnl-lightbox-image-wrap{position:relative;overflow:auto;max-height:calc(82vh - 36px);border-radius:22px;background:#050914}
.jrnl-lightbox-image-wrap img{display:block;width:100%;height:auto;max-height:none;object-fit:contain}
.jrnl-lightbox-close{position:absolute;right:18px;top:18px;z-index:4;display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;border-radius:999px;border:1px solid rgba(255,255,255,.22);background:rgba(8,14,24,.72);backdrop-filter:blur(14px);color:#f6fbff;font-size:24px;line-height:1;cursor:pointer;box-shadow:0 14px 40px rgba(0,0,0,.34)}
.jrnl-lightbox-close:hover{transform:scale(1.06);background:rgba(14,23,38,.84)}
.jrnl-lightbox-caption{position:relative;z-index:3;display:inline-flex;align-items:center;gap:12px;padding:12px 16px;border-radius:999px;background:rgba(5,10,18,.58);border:1px solid rgba(255,255,255,.16);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#dcecff}
.jrnl-lightbox-caption::before{content:"expanded cover";opacity:.74}
.jrnl-lightbox.is-closing .jrnl-lightbox-frame{animation:jrnlFrameOut .72s cubic-bezier(.7,0,.84,0) forwards}
.jrnl-lightbox.is-closing .jrnl-lightbox-bloom{animation:jrnlBloomOut .72s cubic-bezier(.7,0,.84,0) forwards}
.jrnl-lightbox.is-closing{pointer-events:none}
@keyframes jrnlBloomIn{
0%{opacity:0;transform:scale(.58) rotate(-26deg);filter:blur(56px)}
55%{opacity:1;transform:scale(1.08) rotate(10deg);filter:blur(34px)}
100%{opacity:.82;transform:scale(1.18) rotate(16deg);filter:blur(46px)}
}
@keyframes jrnlBloomOut{
0%{opacity:.82;transform:scale(1.18) rotate(16deg);filter:blur(46px)}
100%{opacity:0;transform:scale(.44) rotate(34deg);filter:blur(68px)}
}
@keyframes jrnlFrameIn{
0%{opacity:0;transform:translateY(34px) scale(.82) rotateX(18deg) rotate(-5deg);clip-path:polygon(50% 50%,50% 50%,50% 50%,50% 50%)}
36%{opacity:1;transform:translateY(0) scale(.94) rotateX(6deg) rotate(1deg);clip-path:polygon(10% 18%,88% 8%,92% 82%,14% 90%)}
68%{transform:translateY(-8px) scale(1.01) rotateX(0deg) rotate(0deg);clip-path:polygon(0 4%,100% 0,100% 100%,0 96%)}
100%{opacity:1;transform:translateY(0) scale(1) rotateX(0deg) rotate(0deg);clip-path:inset(0 round 34px)}
}
@keyframes jrnlFrameOut{
0%{opacity:1;transform:translateY(0) scale(1) rotateX(0deg) rotate(0deg);clip-path:inset(0 round 34px)}
38%{opacity:1;transform:translateY(8px) scale(.98) rotateX(0deg) rotate(-1deg);clip-path:polygon(0 4%,100% 0,100% 100%,0 96%)}
100%{opacity:0;transform:translateY(42px) scale(.8) rotateX(20deg) rotate(6deg);clip-path:polygon(50% 50%,50% 50%,50% 50%,50% 50%)}
}
@keyframes jrnlCoverIntro{
0%{opacity:0;transform:translateY(28px) scale(.965);filter:blur(14px) saturate(.86)}
55%{opacity:1;transform:translateY(0) scale(1.006);filter:blur(0) saturate(1)}
100%{opacity:1;transform:translateY(0) scale(1);filter:blur(0) saturate(1)}
}
@keyframes jrnlBorderFireflies{
0%{background-position:-14% 0,114% 100%}
25%{background-position:30% 0,70% 100%}
50%{background-position:112% 0,-12% 100%}
75%{background-position:70% 0,26% 100%}
100%{background-position:-14% 0,114% 100%}
}
@media (max-width:1180px){.jrnl-hero,.jrnl-grid,.jrnl-related-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.jrnl-hero{grid-template-columns:1fr}}
@media (max-width:720px){.jrnl{padding:18px 14px 52px}.jrnl-grid,.jrnl-related-grid{grid-template-columns:1fr}.jrnl-copy h1,.jrnl-copy h2,.jrnl-detail h1{font-size:clamp(2rem,12vw,3.2rem)}.jrnl-lightbox-frame{padding:12px;border-radius:24px}.jrnl-lightbox-close{right:12px;top:12px;width:48px;height:48px}}
</style>

<section class="jrnl">
    <div class="jrnl-shell">
        <?php if ($selected): ?>
            <article class="jrnl-detail">
                <span class="jrnl-kicker"><?= htmlspecialchars((string)($issue['issue_title'] ?? $t('Журнал', 'Journal')), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="jrnl-tags">
                    <?php if (!empty($selected['cluster_code'])): ?>
                        <a class="jrnl-tag" href="<?= htmlspecialchars($buildPageUrl((string)$selected['cluster_code']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$selected['cluster_code'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                    <span class="jrnl-meta"><?= htmlspecialchars((string)($selected['published_at'] ?? $selected['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (!empty($selected['author_name'])): ?><span class="jrnl-meta"><?= htmlspecialchars((string)$selected['author_name'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                </div>
                <?php if (!empty($selected['hero_image_src'])): ?>
                    <div class="jrnl-detail-cover">
                        <img src="<?= htmlspecialchars((string)$selected['hero_image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                <?php endif; ?>
                <div class="jrnl-detail-body">
                    <div class="jrnl-detail-content"><?= (string)($selected['content_html'] ?? '') ?></div>
                    <div class="jrnl-actions">
                        <a class="jrnl-btn" href="<?= htmlspecialchars($buildPageUrl($currentCluster), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Назад в journal', 'Back to journal'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </div>
            </article>

            <?php if (!empty($relatedItems)): ?>
                <section class="jrnl-related">
                    <h2><?= htmlspecialchars($t('Дальше по теме', 'Continue reading'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="jrnl-related-grid">
                        <?php foreach ($relatedItems as $item): ?>
                            <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                            <a class="jrnl-card" href="<?= htmlspecialchars(function_exists('examples_article_url_path') ? examples_article_url_path((string)($item['slug'] ?? ''), $cluster) : '/journal/', ENT_QUOTES, 'UTF-8') ?>">
                                <div class="jrnl-card-media"><?php if (!empty($item['image_src'])): ?><img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                                <span class="jrnl-tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Материал', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($strip((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 140), ENT_QUOTES, 'UTF-8') ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php else: ?>
            <header class="jrnl-hero">
                <div class="jrnl-copy">
                    <?php if (!empty($issue['issue_kicker'])): ?><span class="jrnl-kicker"><?= htmlspecialchars((string)$issue['issue_kicker'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                    <h1><?= htmlspecialchars((string)($issue['hero_title'] ?? $issue['issue_title'] ?? $t('Журнал', 'Journal')), ENT_QUOTES, 'UTF-8') ?></h1>
                    <?php if (!empty($issue['hero_description'])): ?><p><?= htmlspecialchars((string)$issue['hero_description'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    <?php if (!empty($issue['issue_title'])): ?><h2><?= htmlspecialchars((string)$issue['issue_title'], ENT_QUOTES, 'UTF-8') ?></h2><?php endif; ?>
                    <?php if (!empty($issue['issue_subtitle'])): ?><p><?= htmlspecialchars((string)$issue['issue_subtitle'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                </div>
                <button
                    class="jrnl-cover jrnl-cover-button"
                    type="button"
                    data-jrnl-cover-open
                    data-jrnl-cover-src="<?= htmlspecialchars($issueImage, ENT_QUOTES, 'UTF-8') ?>"
                    data-jrnl-cover-alt="<?= htmlspecialchars((string)($issue['issue_title'] ?? $issue['hero_title'] ?? 'Journal cover'), ENT_QUOTES, 'UTF-8') ?>"
                    aria-label="<?= htmlspecialchars($t('Открыть обложку выпуска', 'Open issue cover'), ENT_QUOTES, 'UTF-8') ?>"
                >
                    <?php if ($issueImage !== ''): ?><img src="<?= htmlspecialchars($issueImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($issue['issue_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                </button>
            </header>

            <?php if (!empty($clusters)): ?>
                <div class="jrnl-tags" aria-label="<?= htmlspecialchars($t('Темы выпуска', 'Issue topics'), ENT_QUOTES, 'UTF-8') ?>">
                    <a class="jrnl-tag <?= $currentCluster === '' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($buildPageUrl(''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Все темы', 'All topics'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php foreach ($clusters as $cluster): ?>
                        <?php $clusterCode = trim((string)($cluster['code'] ?? '')); ?>
                        <?php if ($clusterCode === '') { continue; } ?>
                        <a class="jrnl-tag <?= $currentCluster === $clusterCode ? 'is-active' : '' ?>" href="<?= htmlspecialchars($buildPageUrl($clusterCode), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string)($cluster['label'] ?? $clusterCode), ENT_QUOTES, 'UTF-8') ?>
                            <?php if (isset($cluster['count'])): ?> · <?= (int)$cluster['count'] ?><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($items)): ?>
                <div class="jrnl-grid">
                    <?php foreach ($items as $item): ?>
                        <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                        <a class="jrnl-card" href="<?= htmlspecialchars(function_exists('examples_article_url_path') ? examples_article_url_path((string)($item['slug'] ?? ''), $cluster) : '/journal/', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="jrnl-card-media"><?php if (!empty($item['image_src'])): ?><img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                            <span class="jrnl-tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Материал', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($strip((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 160), ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="jrnl-card-foot">
                                <span><?= htmlspecialchars((string)($item['published_at'] ?? $item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= htmlspecialchars($t('Открыть', 'Open'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="jrnl-pager" aria-label="<?= htmlspecialchars($t('Пагинация', 'Pagination'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($page > 1): ?>
                            <a href="<?= htmlspecialchars($buildPageUrl($currentCluster, $page - 1), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Назад', 'Prev'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i < $page - 2 || $i > $page + 2) { continue; } ?>
                            <?php if ($i === $page): ?>
                                <span class="is-active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($buildPageUrl($currentCluster, $i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= htmlspecialchars($buildPageUrl($currentCluster, $page + 1), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Вперед', 'Next'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="jrnl-empty">
                    <h2><?= htmlspecialchars($t('Пока нет материалов', 'No journal materials yet'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($t('Когда статьи появятся, они будут выведены здесь как выпуск журнала с фильтрацией по темам.', 'Published articles will appear here as a journal issue with topic filters.'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<div class="jrnl-lightbox" id="jrnlCoverLightbox" aria-hidden="true">
    <div class="jrnl-lightbox-backdrop" data-jrnl-cover-close></div>
    <div class="jrnl-lightbox-bloom"></div>
    <div class="jrnl-lightbox-shell">
        <div class="jrnl-lightbox-frame" role="dialog" aria-modal="true" aria-label="<?= htmlspecialchars($t('Обложка выпуска', 'Issue cover'), ENT_QUOTES, 'UTF-8') ?>">
            <button class="jrnl-lightbox-close" type="button" data-jrnl-cover-close aria-label="<?= htmlspecialchars($t('Закрыть', 'Close'), ENT_QUOTES, 'UTF-8') ?>">×</button>
            <div class="jrnl-lightbox-image-wrap">
                <img id="jrnlCoverLightboxImage" src="" alt="">
            </div>
        </div>
        <div class="jrnl-lightbox-caption"></div>
    </div>
</div>

<script>
(function () {
    var lightbox = document.getElementById('jrnlCoverLightbox');
    var image = document.getElementById('jrnlCoverLightboxImage');
    if (!lightbox || !image) {
        return;
    }

    var lastActive = null;
    var closingTimer = null;
    var isOpen = false;
    var scrollY = 0;
    var activeTrigger = null;

    if (lightbox.parentNode !== document.body) {
        document.body.appendChild(lightbox);
    }

    function lockScroll(lock) {
        if (lock) {
            scrollY = window.scrollY || window.pageYOffset || 0;
            document.documentElement.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + scrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
            return;
        }

        document.documentElement.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.body.style.overflow = '';
        window.scrollTo(0, scrollY);
    }

    function openLightbox(trigger, event) {
        if (event) {
            event.preventDefault();
        }
        if (isOpen || !trigger) {
            return;
        }
        activeTrigger = trigger;
        lastActive = document.activeElement;
        image.src = trigger.getAttribute('data-jrnl-cover-src') || '';
        image.alt = trigger.getAttribute('data-jrnl-cover-alt') || '';
        lightbox.classList.remove('is-closing');
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        lockScroll(true);
        isOpen = true;
        var closeButton = lightbox.querySelector('.jrnl-lightbox-close');
        if (closeButton) {
            setTimeout(function () {
                try {
                    closeButton.focus({ preventScroll: true });
                } catch (e) {
                    closeButton.focus();
                }
            }, 120);
        }
    }

    function finishClose() {
        lightbox.classList.remove('is-open', 'is-closing');
        lightbox.setAttribute('aria-hidden', 'true');
        image.src = '';
        image.alt = '';
        activeTrigger = null;
        lockScroll(false);
        isOpen = false;
        if (lastActive && typeof lastActive.focus === 'function') {
            try {
                lastActive.focus({ preventScroll: true });
            } catch (e) {
                lastActive.focus();
            }
        }
    }

    function closeLightbox() {
        if (!isOpen) {
            return;
        }
        lightbox.classList.add('is-closing');
        clearTimeout(closingTimer);
        closingTimer = setTimeout(finishClose, 720);
    }

    document.addEventListener('click', function (event) {
        var openTrigger = event.target.closest('[data-jrnl-cover-open]');
        if (openTrigger) {
            openLightbox(openTrigger, event);
            return;
        }

        if (!isOpen) {
            return;
        }

        if (event.target.closest('[data-jrnl-cover-close]')) {
            event.preventDefault();
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!isOpen) {
            return;
        }
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
})();
</script>
