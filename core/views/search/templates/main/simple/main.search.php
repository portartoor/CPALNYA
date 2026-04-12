<?php
$search = (array)($ModelPage['search'] ?? []);
$items = array_values((array)($search['items'] ?? []));
$featured = is_array($search['featured'] ?? null) ? $search['featured'] : null;
$fallbackFeatured = is_array($search['fallback_featured'] ?? null) ? $search['fallback_featured'] : null;
$fallbackItems = array_values((array)($search['fallback_items'] ?? []));
$query = trim((string)($search['query'] ?? ''));
$total = (int)($search['total'] ?? 0);
$error = trim((string)($search['error'] ?? ''));
$page = max(1, (int)($search['page'] ?? 1));
$totalPages = max(1, (int)($search['total_pages'] ?? 1));
$isRu = ((string)($search['lang'] ?? 'en') === 'ru');

$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};
$buildPageLink = static function (string $query, int $page): string {
    $params = [];
    if (trim($query) !== '') {
        $params['q'] = $query;
    }
    if ($page > 1) {
        $params['page'] = $page;
    }
    return '/search/' . (!empty($params) ? ('?' . http_build_query($params)) : '');
};
$sectionLabel = static function (array $row, bool $isRu): string {
    $section = trim((string)($row['material_section'] ?? 'journal'));
    $map = $isRu
        ? ['journal' => 'Журнал', 'playbooks' => 'Практика', 'signals' => 'Повестка', 'fun' => 'Фан']
        : ['journal' => 'Journal', 'playbooks' => 'Playbooks', 'signals' => 'Signals', 'fun' => 'Fun'];
    return $map[$section] ?? ($isRu ? 'Материал' : 'Article');
};
$statIcon = static function (string $type): string {
    $icons = [
        'eye' => '<svg class="search-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 5.2c5.2 0 8.7 4.4 9.7 5.8.4.6.4 1.4 0 2-1 1.4-4.5 5.8-9.7 5.8S3.3 14.4 2.3 13a1.8 1.8 0 0 1 0-2c1-1.4 4.5-5.8 9.7-5.8Zm0 2C7.9 7.2 5 10.6 4 12c1 1.4 3.9 4.8 8 4.8s7-3.4 8-4.8c-1-1.4-3.9-4.8-8-4.8Zm0 1.7a3.1 3.1 0 1 1 0 6.2 3.1 3.1 0 0 1 0-6.2Zm0 2a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2Z"/></svg>',
        'comments' => '<svg class="search-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 4h14a3 3 0 0 1 3 3v7.2a3 3 0 0 1-3 3h-7.1l-5.2 3.9a1 1 0 0 1-1.6-.8v-3.1H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Zm14 2H5a1 1 0 0 0-1 1v7.2a1 1 0 0 0 1 1h1.1a1 1 0 0 1 1 1v2.1l3.9-2.9a1 1 0 0 1 .6-.2H19a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1Z"/></svg>',
    ];
    return $icons[$type] ?? '';
};
?>
<style>
.search-shell{max-width:1240px;margin:0 auto;padding:28px 18px 44px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.search-hero,.search-featured,.search-card,.search-empty,.search-form{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.search-hero{padding:22px;display:grid;gap:12px}
.search-hero h1,.search-featured-title,.search-card-title{margin:0;font-family:"Space Grotesk","Sora",sans-serif;color:var(--shell-text)}
.search-hero h1{font-size:clamp(1.35rem,2.4vw,2rem);line-height:1}
.search-hero p,.search-card p,.search-featured-copy p,.search-empty,.search-results-head{color:var(--shell-muted)}
.search-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;padding:10px}
.search-form input{min-width:0;width:100%;padding:14px 16px;border:1px solid var(--shell-border);background:rgba(255,255,255,.03);color:var(--shell-text);outline:0}
.search-form button{padding:14px 18px;border:1px solid var(--shell-border);background:linear-gradient(135deg,rgba(122,180,255,.26),rgba(44,224,199,.18));color:var(--shell-text);font-weight:700;cursor:pointer}
.search-results-head{margin:18px 0 10px;font-size:14px}
.search-featured{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:18px;padding:18px}
.search-featured-media{display:block;min-height:280px;border:1px solid var(--shell-border);background:rgba(255,255,255,.03);overflow:hidden}
.search-featured-media img{display:block;width:100%;height:100%;object-fit:cover}
.search-featured-copy{display:grid;align-content:start;gap:12px}
.search-kicker{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--shell-accent)}
.search-kicker::before{content:"";width:24px;height:1px;background:currentColor;opacity:.55}
.search-featured-title{font-size:clamp(1.5rem,2.4vw,2.4rem);line-height:.98}
.search-link{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:1px solid var(--shell-border);background:linear-gradient(135deg,rgba(122,180,255,.24),rgba(44,224,199,.16));color:var(--shell-text);text-decoration:none;font-weight:700}
.search-meta-row{display:flex;flex-wrap:wrap;gap:8px 10px;align-items:center}
.search-meta-pill,.search-stat{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--shell-muted)}
.search-stat{padding:8px 10px}
.search-stat-icon{width:15px;height:15px;display:block;flex:0 0 15px;opacity:.86}
.search-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.search-card{display:grid;gap:12px;padding:14px}
.search-thumb{display:block;aspect-ratio:16/9;border:1px solid var(--shell-border);background:rgba(255,255,255,.03);overflow:hidden}
.search-thumb img{display:block;width:100%;height:100%;object-fit:cover}
.search-card-title{font-size:1.08rem;line-height:1.1}
.search-card p{margin:0;line-height:1.6}
.search-empty{margin-top:18px;padding:18px}
.search-pagination{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
.search-pagination a,.search-pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:40px;min-height:38px;padding:8px 12px;border:1px solid var(--shell-border);background:rgba(255,255,255,.03);color:var(--shell-text);text-decoration:none}
.search-pagination .active{background:linear-gradient(135deg,rgba(122,180,255,.26),rgba(44,224,199,.18))}
@media (max-width:980px){.search-featured{grid-template-columns:1fr}.search-grid{grid-template-columns:1fr 1fr}}
@media (max-width:680px){.search-shell{padding:18px 14px 34px}.search-form{grid-template-columns:1fr}.search-grid{grid-template-columns:1fr}.search-featured{padding:14px}.search-featured-media{min-height:220px}}
</style>

<section class="search-shell">
    <div class="search-hero">
        <h1><?= $escape($isRu ? 'Поиск по выпуску' : 'Search the issue') ?></h1>
        <p><?= $escape($isRu ? 'Ищем по журналу, практике, повестке и фан-блоку. Если точного попадания нет, все равно подкидываем сильные материалы, чтобы не упираться в пустую выдачу.' : 'Search across journal, playbooks, signals and fun. If there is no exact hit, we still surface strong material worth opening next.') ?></p>
        <form class="search-form" method="get" action="/search/">
            <input type="text" name="q" value="<?= $escape($query) ?>" placeholder="<?= $escape($isRu ? 'Поиск: Facebook farm, tracker setup, nutra funnel, anti-detect' : 'Search: Facebook farm, tracker setup, nutra funnel, anti-detect') ?>" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
            <button type="submit"><?= $escape($isRu ? 'Искать' : 'Search') ?></button>
        </form>
    </div>

    <?php if ($error !== ''): ?>
        <div class="search-empty"><?= $escape($error) ?></div>
    <?php elseif (!empty($items) && $featured): ?>
        <div class="search-results-head">
            <?= $escape($isRu ? ('Найдено материалов: ' . $total) : ('Results found: ' . $total)) ?>
        </div>
        <article class="search-featured">
            <a class="search-featured-media" href="<?= $escape((string)($featured['article_url'] ?? '/journal/')) ?>">
                <?php if (trim((string)($featured['image_src'] ?? '')) !== ''): ?>
                    <img src="<?= $escape((string)$featured['image_src']) ?>" alt="<?= $escape((string)($featured['title'] ?? '')) ?>">
                <?php endif; ?>
            </a>
            <div class="search-featured-copy">
                <span class="search-kicker"><?= $escape($isRu ? 'Лучшее совпадение' : 'Best match') ?> / <?= $escape($sectionLabel($featured, $isRu)) ?></span>
                <h2 class="search-featured-title"><?= $escape((string)($featured['title'] ?? '')) ?></h2>
                <div class="search-meta-row">
                    <span class="search-meta-pill"><?= $escape($sectionLabel($featured, $isRu)) ?></span>
                    <span class="search-stat"><?= $statIcon('eye') ?><?= (int)($featured['view_count'] ?? 0) ?></span>
                    <span class="search-stat"><?= $statIcon('comments') ?><?= (int)($featured['comment_count'] ?? 0) ?></span>
                </div>
                <p><?= $escape((string)($featured['search_snippet'] ?? '')) ?></p>
                <a class="search-link" href="<?= $escape((string)($featured['article_url'] ?? '/journal/')) ?>"><?= $escape($isRu ? 'Открыть материал' : 'Open article') ?></a>
            </div>
        </article>

        <?php if (count($items) > 1): ?>
            <div class="search-results-head"><?= $escape($isRu ? 'Другие результаты' : 'More results') ?></div>
            <div class="search-grid">
                <?php foreach (array_slice($items, 1) as $item): ?>
                    <article class="search-card">
                        <a class="search-thumb" href="<?= $escape((string)($item['article_url'] ?? '/journal/')) ?>">
                            <?php if (trim((string)($item['image_src'] ?? '')) !== ''): ?>
                                <img src="<?= $escape((string)$item['image_src']) ?>" alt="<?= $escape((string)($item['title'] ?? '')) ?>">
                            <?php endif; ?>
                        </a>
                        <div class="search-meta-row">
                            <span class="search-meta-pill"><?= $escape($sectionLabel((array)$item, $isRu)) ?></span>
                            <span class="search-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                            <span class="search-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                        </div>
                        <h3 class="search-card-title"><?= $escape((string)($item['title'] ?? '')) ?></h3>
                        <p><?= $escape((string)($item['search_snippet'] ?? '')) ?></p>
                        <a class="search-link" href="<?= $escape((string)($item['article_url'] ?? '/journal/')) ?>"><?= $escape($isRu ? 'Читать' : 'Read') ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <div class="search-pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= $escape($buildPageLink($query, $i)) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="search-empty">
            <?= $escape($isRu ? ('По запросу "' . $query . '" точных совпадений нет. Ниже один расширенный материал и еще несколько случайных заходов из всех четырех разделов.') : ('No exact matches for "' . $query . '". Here is one broader article plus a few random ways in from all four sections.')) ?>
        </div>

        <?php if ($fallbackFeatured): ?>
            <article class="search-featured">
                <a class="search-featured-media" href="<?= $escape((string)($fallbackFeatured['article_url'] ?? '/journal/')) ?>">
                    <?php if (trim((string)($fallbackFeatured['image_src'] ?? '')) !== ''): ?>
                        <img src="<?= $escape((string)$fallbackFeatured['image_src']) ?>" alt="<?= $escape((string)($fallbackFeatured['title'] ?? '')) ?>">
                    <?php endif; ?>
                </a>
                <div class="search-featured-copy">
                    <span class="search-kicker"><?= $escape($isRu ? 'Случайный расширенный материал' : 'Random expanded article') ?> / <?= $escape($sectionLabel($fallbackFeatured, $isRu)) ?></span>
                    <h2 class="search-featured-title"><?= $escape((string)($fallbackFeatured['title'] ?? '')) ?></h2>
                    <div class="search-meta-row">
                        <span class="search-meta-pill"><?= $escape($sectionLabel($fallbackFeatured, $isRu)) ?></span>
                        <span class="search-stat"><?= $statIcon('eye') ?><?= (int)($fallbackFeatured['view_count'] ?? 0) ?></span>
                        <span class="search-stat"><?= $statIcon('comments') ?><?= (int)($fallbackFeatured['comment_count'] ?? 0) ?></span>
                    </div>
                    <p><?= $escape((string)($fallbackFeatured['search_snippet'] ?? '')) ?></p>
                    <a class="search-link" href="<?= $escape((string)($fallbackFeatured['article_url'] ?? '/journal/')) ?>"><?= $escape($isRu ? 'Открыть материал' : 'Open article') ?></a>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!empty($fallbackItems)): ?>
            <div class="search-results-head"><?= $escape($isRu ? 'Еще можно открыть' : 'More to open') ?></div>
            <div class="search-grid">
                <?php foreach ($fallbackItems as $item): ?>
                    <article class="search-card">
                        <a class="search-thumb" href="<?= $escape((string)($item['article_url'] ?? '/journal/')) ?>">
                            <?php if (trim((string)($item['image_src'] ?? '')) !== ''): ?>
                                <img src="<?= $escape((string)$item['image_src']) ?>" alt="<?= $escape((string)($item['title'] ?? '')) ?>">
                            <?php endif; ?>
                        </a>
                        <div class="search-meta-row">
                            <span class="search-meta-pill"><?= $escape($sectionLabel((array)$item, $isRu)) ?></span>
                            <span class="search-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                            <span class="search-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                        </div>
                        <h3 class="search-card-title"><?= $escape((string)($item['title'] ?? '')) ?></h3>
                        <p><?= $escape((string)($item['search_snippet'] ?? '')) ?></p>
                        <a class="search-link" href="<?= $escape((string)($item['article_url'] ?? '/journal/')) ?>"><?= $escape($isRu ? 'Читать' : 'Read') ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
