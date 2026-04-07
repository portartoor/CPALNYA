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
$renderIssueText = static function (string $text, string $class = ''): string {
    $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
    if ($text === '') {
        return '';
    }

    $parts = preg_split("/\n\s*\n/u", $text) ?: [];
    if (!$parts) {
        $parts = [$text];
    }

    $html = '';
    foreach ($parts as $part) {
        $part = trim((string)$part);
        if ($part === '') {
            continue;
        }
        $part = preg_replace("/\n+/u", "<br>", htmlspecialchars($part, ENT_QUOTES, 'UTF-8'));
        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $html .= '<p' . $classAttr . '>' . $part . '</p>';
    }

    return $html;
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
$buildArticleUrl = static function (string $slug, string $cluster = ''): string {
    return function_exists('examples_article_url_path')
        ? examples_article_url_path($slug, $cluster)
        : '/journal/';
};
$selectedArticleUrl = '';
if ($selected) {
    $selectedArticleUrl = $buildArticleUrl((string)($selected['slug'] ?? ''), (string)($selected['cluster_code'] ?? ''));
}
$selectedShareUrl = $selectedArticleUrl !== ''
    ? (((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http') . '://' . (string)($_SERVER['HTTP_HOST'] ?? '') . $selectedArticleUrl)
    : '';
$selectedShareTitle = trim((string)($selected['title'] ?? ''));
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
.jrnl-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:16px}
.jrnl-kicker,.jrnl-tag,.jrnl-meta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;max-height:30px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.jrnl-copy{display:grid;gap:8px}
.jrnl-copy h1,.jrnl-detail h1{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.048em}
.jrnl-copy h2{margin:8px 0 0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.048em}
.jrnl-related h2{margin:0;font:700 1.5rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.048em}
.jrnl-copy p,.jrnl-detail p,.jrnl-card p{margin:0;color:var(--shell-muted);line-height:1.62}
.jrnl-copy p + p{margin-top:2px}
.jrnl-copy .jrnl-hero-description{max-width:66ch}
.jrnl-copy .jrnl-issue-subtitle{font-size:clamp(1.02rem,1.6vw,1.24rem);line-height:1.56;color:rgba(233,242,255,.9)}
.jrnl-cover{min-height:0;border:1px solid rgba(255,255,255,.08);background:radial-gradient(circle at 50% 22%,rgba(103,200,255,.16),transparent 26%),linear-gradient(180deg,rgba(6,11,20,.96),rgba(4,8,16,.92));display:block;align-self:start;overflow:hidden;position:relative}
.jrnl-cover img{position:relative;display:block;width:100%;height:auto;max-width:none;object-fit:contain;object-position:center center;padding:0;transform-origin:50% 0;animation:jrnlCoverIntro 1.15s cubic-bezier(.16,1,.3,1) both}
.jrnl-tags{display:flex;flex-wrap:wrap;gap:10px}
.jrnl-tag{color:var(--shell-muted);text-decoration:none}
.jrnl-tag.is-active,.jrnl-tag:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
.jrnl-card{display:grid;gap:14px;padding:16px;text-decoration:none;color:inherit;min-height:100%}
.jrnl-card-media{aspect-ratio:16/10;background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));border:1px solid rgba(255,255,255,.08);overflow:hidden}
.jrnl-card-media img{width:100%;height:100%;object-fit:cover;display:block}
.jrnl-card h3{margin:0;font:700 1.28rem/1.15 "Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.jrnl-card-foot{display:flex;justify-content:space-between;gap:10px;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.jrnl-stat{display:inline-flex;align-items:center;gap:7px}
.jrnl-stat-eye{display:inline-block;font-style:normal;font-size:13px;line-height:1;opacity:.82}
.jrnl-pager{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}
.jrnl-pager a,.jrnl-pager span{display:inline-flex;align-items:center;justify-content:center;min-width:44px;padding:10px 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-muted);text-decoration:none}
.jrnl-pager .is-active{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.12)}
.jrnl-detail{display:grid;gap:18px}
.jrnl-detail-body{display:grid;gap:18px}
.jrnl-detail-cover{overflow:visible;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.02)}
.jrnl-detail-cover img{width:100%;height:auto;display:block;object-fit:contain}
.jrnl-detail-content{font-size:16px;line-height:1.82;color:var(--shell-text)}
.jrnl-detail-content h2,.jrnl-detail-content h3{font-family:"Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.jrnl-actions{display:flex;gap:12px;flex-wrap:wrap}
.jrnl-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.22),rgba(39,223,192,.18));color:var(--shell-text);text-decoration:none;font-weight:700}
.jrnl-share{display:flex;flex-wrap:wrap;gap:10px}
.jrnl-share a{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-muted);text-decoration:none}
.jrnl-share a:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-related-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.jrnl-empty{text-align:center}
@keyframes jrnlCoverIntro{
0%{opacity:0;transform:translateY(28px) scale(.965);filter:blur(14px) saturate(.86)}
55%{opacity:1;transform:translateY(0) scale(1.006);filter:blur(0) saturate(1)}
100%{opacity:1;transform:translateY(0) scale(1);filter:blur(0) saturate(1)}
}
@media (max-width:1180px){.jrnl-hero,.jrnl-grid,.jrnl-related-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.jrnl-hero{grid-template-columns:1fr}}
@media (max-width:720px){.jrnl{padding:18px 14px 52px}.jrnl-grid,.jrnl-related-grid{grid-template-columns:1fr}}
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
                    <span class="jrnl-meta jrnl-stat"><i class="jrnl-stat-eye" aria-hidden="true">◉</i><?= (int)($selected['view_count'] ?? 0) ?></span>
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
                    <?php if ($selectedShareUrl !== ''): ?>
                        <div class="jrnl-share">
                            <a href="https://t.me/share/url?url=<?= rawurlencode($selectedShareUrl) ?>&text=<?= rawurlencode($selectedShareTitle) ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
                            <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($selectedShareUrl) ?>&text=<?= rawurlencode($selectedShareTitle) ?>" target="_blank" rel="noopener noreferrer">X</a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($selectedShareUrl) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($selectedShareUrl) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                            <a href="https://wa.me/?text=<?= rawurlencode($selectedShareTitle . ' ' . $selectedShareUrl) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <?php if (!empty($relatedItems)): ?>
                <section class="jrnl-related">
                    <h2><?= htmlspecialchars($t('Дальше по теме', 'Continue reading'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="jrnl-related-grid">
                        <?php foreach ($relatedItems as $item): ?>
                            <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                            <a class="jrnl-card" href="<?= htmlspecialchars($buildArticleUrl((string)($item['slug'] ?? ''), $cluster), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="jrnl-card-media"><?php if (!empty($item['image_src'])): ?><img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                                <span class="jrnl-tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Материал', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($strip((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 140), ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="jrnl-card-foot">
                                    <span class="jrnl-stat"><i class="jrnl-stat-eye" aria-hidden="true">◉</i><?= (int)($item['view_count'] ?? 0) ?></span>
                                </div>
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
                    <?php if (!empty($issue['hero_description'])): ?><?= $renderIssueText((string)$issue['hero_description'], 'jrnl-hero-description') ?><?php endif; ?>
                    <?php if (!empty($issue['issue_title'])): ?><h2><?= htmlspecialchars((string)$issue['issue_title'], ENT_QUOTES, 'UTF-8') ?></h2><?php endif; ?>
                    <?php if (!empty($issue['issue_subtitle'])): ?><?= $renderIssueText((string)$issue['issue_subtitle'], 'jrnl-issue-subtitle') ?><?php endif; ?>
                </div>
                <div class="jrnl-cover">
                    <?php if ($issueImage !== ''): ?><img src="<?= htmlspecialchars($issueImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($issue['issue_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                </div>
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
                        <a class="jrnl-card" href="<?= htmlspecialchars($buildArticleUrl((string)($item['slug'] ?? ''), $cluster), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="jrnl-card-media"><?php if (!empty($item['image_src'])): ?><img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                            <span class="jrnl-tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Материал', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($strip((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 160), ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="jrnl-card-foot">
                                <span><?= htmlspecialchars((string)($item['published_at'] ?? $item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="jrnl-stat"><i class="jrnl-stat-eye" aria-hidden="true">◉</i><?= (int)($item['view_count'] ?? 0) ?></span>
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
