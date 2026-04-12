<?php
$discussion = (array)($ModelPage['discussion'] ?? []);
$selected = is_array($discussion['selected'] ?? null) ? $discussion['selected'] : null;
$groupedItems = (array)($discussion['grouped_items'] ?? []);
$sectionLabels = (array)($discussion['section_labels'] ?? []);
$lang = (string)($discussion['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$page = max(1, (int)($discussion['page'] ?? 1));
$totalPages = max(1, (int)($discussion['total_pages'] ?? 1));
$currentSection = trim((string)($discussion['current_section'] ?? ''));
$portalCommentTotal = (int)($ModelPage['portal_comment_total'] ?? 0);
$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
$formatDate = static function ($value) use ($isRu): string {
    $raw = trim((string)$value);
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
$buildListUrl = static function (string $section = '', int $pageNum = 1): string {
    $base = '/discussion/';
    if ($section !== '') {
        $base .= rawurlencode($section) . '/';
    }
    if ($pageNum > 1) {
        $base .= '?' . http_build_query(['page' => $pageNum]);
    }
    return $base;
};
$prevPageUrl = $page > 1 ? $buildListUrl($currentSection, $page - 1) : '';
$nextPageUrl = $page < $totalPages ? $buildListUrl($currentSection, $page + 1) : '';
?>
<style>
.dsc{max-width:1180px;margin:0 auto;padding:28px 18px 56px;color:var(--shell-text)}
.dsc-shell{display:grid;gap:22px}
.dsc-hero,.dsc-block,.dsc-thread{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.9),rgba(5,10,20,.78));box-shadow:var(--shell-shadow)}
.dsc-hero{display:grid;gap:14px;padding:26px}
.dsc-kicker,.dsc-chip,.dsc-meta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.dsc h1,.dsc h2,.dsc h3{margin:0;font-family:"Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.dsc h1{font-size:2rem;line-height:1}
.dsc h2{font-size:1.35rem;line-height:1.05}
.dsc h3{font-size:1.04rem;line-height:1.18}
.dsc p{margin:0;color:var(--shell-muted);line-height:1.65}
.dsc-filters{display:flex;flex-wrap:wrap;gap:10px}
.dsc-chip{text-decoration:none;color:var(--shell-muted)}
.dsc-chip.is-active,.dsc-chip:hover{color:var(--shell-text);border-color:rgba(122,180,255,.36);background:rgba(122,180,255,.1)}
.dsc-groups{display:grid;gap:20px}
.dsc-block{padding:20px;display:grid;gap:16px}
.dsc-block-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-end;flex-wrap:wrap}
.dsc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.dsc-card{display:grid;grid-template-columns:140px minmax(0,1fr);gap:14px;padding:14px;border:1px solid rgba(122,180,255,.12);background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(122,180,255,.05));text-decoration:none;color:inherit}
.dsc-card-media{width:140px;aspect-ratio:1/1;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12))}
.dsc-card-media img{display:block;width:100%;height:100%;object-fit:cover}
.dsc-card-copy{display:grid;gap:8px;align-content:start}
.dsc-card-top,.dsc-thread-top{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap}
.dsc-stats{display:flex;gap:10px;flex-wrap:wrap;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.dsc-link{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--shell-text);font-weight:700}
.dsc-pagination{display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap}
.dsc-page-btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text);text-decoration:none;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.dsc-thread{padding:22px;display:grid;gap:18px}
.dsc-thread-cover{overflow:hidden;border:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12))}
.dsc-thread-cover img{display:block;width:100%;height:auto}
.dsc-thread-actions{display:flex;gap:10px;flex-wrap:wrap}
.dsc-thread-btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));color:var(--shell-text);text-decoration:none;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
@media (max-width:900px){.dsc-grid{grid-template-columns:1fr}.dsc-card{grid-template-columns:1fr}.dsc-card-media{width:100%}}
@media (max-width:720px){.dsc{padding:18px 14px 48px}.dsc-hero,.dsc-block,.dsc-thread{padding:18px}}
</style>

<section class="dsc">
    <div class="dsc-shell">
        <?php if ($selected): ?>
            <?php $selectedSection = trim((string)($selected['material_section'] ?? 'journal')); ?>
            <header class="dsc-hero">
                <span class="dsc-kicker"><?= htmlspecialchars($t('Обсуждение материала', 'Thread discussion'), ENT_QUOTES, 'UTF-8') ?></span>
                <div class="dsc-thread-top">
                    <h1><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
                    <div class="dsc-stats">
                        <span><?= htmlspecialchars((string)($sectionLabels[$selectedSection] ?? $selectedSection), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= (int)$portalCommentTotal ?> <?= htmlspecialchars($t('комм.', 'comments'), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php $selectedDate = $formatDate($selected['published_at'] ?? $selected['created_at'] ?? ''); ?>
                        <?php if ($selectedDate !== ''): ?>
                            <span><?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($selected['image_src'])): ?>
                    <div class="dsc-thread-cover">
                        <img src="<?= htmlspecialchars((string)$selected['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                <?php endif; ?>
                <?php if (!empty($selected['short_excerpt'])): ?>
                    <p><?= htmlspecialchars((string)$selected['short_excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <div class="dsc-thread-actions">
                    <a class="dsc-thread-btn" href="<?= htmlspecialchars((string)($selected['source_url'] ?? '/journal/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Перейти к новости', 'Open full article'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="dsc-page-btn" href="<?= htmlspecialchars($buildListUrl($selectedSection), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Назад к темам', 'Back to threads'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </header>

            <article class="dsc-thread" id="discussion-comments">
                <?php
                $commentsPartial = DIR . 'core/views/partials/article_comments.php';
                if (is_file($commentsPartial)) {
                    include $commentsPartial;
                }
                ?>
            </article>
        <?php else: ?>
            <header class="dsc-hero">
                <span class="dsc-kicker"><?= htmlspecialchars($t('Обсуждение', 'Discussion'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars($t('Темы, где разговор уже начался', 'Threads where the conversation already started'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($t('Здесь собраны только те материалы, под которыми уже есть комментарии. Список идет от самых новых публикаций к более старым, а внутри видно, из какого раздела пришла тема.', 'Only materials that already have comments are shown here. The list is sorted from newest published articles to older ones, with a clear source section for every thread.'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="dsc-filters">
                    <a class="dsc-chip <?= $currentSection === '' ? 'is-active' : '' ?>" href="/discussion/"><?= htmlspecialchars($t('Все разделы', 'All sections'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php foreach ($sectionLabels as $sectionCode => $label): ?>
                        <a class="dsc-chip <?= $currentSection === $sectionCode ? 'is-active' : '' ?>" href="<?= htmlspecialchars($buildListUrl((string)$sectionCode), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </header>

            <div class="dsc-groups">
                <?php foreach ($groupedItems as $sectionCode => $sectionItems): ?>
                    <?php $sectionItems = array_values((array)$sectionItems); ?>
                    <?php if (empty($sectionItems)): continue; endif; ?>
                    <section class="dsc-block">
                        <div class="dsc-block-head">
                            <div>
                                <span class="dsc-kicker"><?= htmlspecialchars((string)($sectionLabels[$sectionCode] ?? $sectionCode), ENT_QUOTES, 'UTF-8') ?></span>
                                <h2><?= htmlspecialchars($t('Темы с обсуждением', 'Active threads'), ENT_QUOTES, 'UTF-8') ?></h2>
                            </div>
                            <?php if ($currentSection === ''): ?>
                                <a class="dsc-link" href="<?= htmlspecialchars($buildListUrl((string)$sectionCode), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Только этот раздел', 'Only this section'), ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="dsc-grid">
                            <?php foreach ($sectionItems as $item): ?>
                                <?php $itemDate = $formatDate($item['published_at'] ?? $item['created_at'] ?? ''); ?>
                                <a class="dsc-card" href="<?= htmlspecialchars((string)($item['discussion_url'] ?? '/discussion/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="dsc-card-media">
                                        <?php if (!empty($item['image_src'])): ?>
                                            <img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="dsc-card-copy">
                                        <div class="dsc-card-top">
                                            <span class="dsc-meta"><?= htmlspecialchars((string)($sectionLabels[$sectionCode] ?? $sectionCode), ENT_QUOTES, 'UTF-8') ?></span>
                                            <div class="dsc-stats">
                                                <span><?= (int)($item['comment_count'] ?? 0) ?> <?= htmlspecialchars($t('комм.', 'comments'), ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if ($itemDate !== ''): ?>
                                                    <span><?= htmlspecialchars($itemDate, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                        <?php if (!empty($item['short_excerpt'])): ?>
                                            <p><?= htmlspecialchars((string)$item['short_excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                        <span class="dsc-link"><?= htmlspecialchars($t('Открыть тред', 'Open thread'), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <?php if (empty(array_filter($groupedItems))): ?>
                <section class="dsc-block">
                    <h2><?= htmlspecialchars($t('Пока нет тем с комментариями', 'No discussion threads yet'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($t('Как только под материалами появятся комментарии, они соберутся здесь отдельными тредами.', 'As soon as comments appear under materials, they will show up here as separate threads.'), ENT_QUOTES, 'UTF-8') ?></p>
                </section>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
                <nav class="dsc-block dsc-pagination" aria-label="<?= htmlspecialchars($t('Постраничная навигация', 'Pagination'), ENT_QUOTES, 'UTF-8') ?>">
                    <div>
                        <span class="dsc-meta"><?= htmlspecialchars($t('Страница', 'Page'), ENT_QUOTES, 'UTF-8') ?> <?= $page ?> / <?= $totalPages ?></span>
                    </div>
                    <div class="dsc-thread-actions">
                        <?php if ($prevPageUrl !== ''): ?>
                            <a class="dsc-page-btn" href="<?= htmlspecialchars($prevPageUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Назад', 'Back'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                        <?php if ($nextPageUrl !== ''): ?>
                            <a class="dsc-page-btn" href="<?= htmlspecialchars($nextPageUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Вперед', 'Next'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
