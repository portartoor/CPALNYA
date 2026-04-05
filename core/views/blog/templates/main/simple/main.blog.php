<?php
$blog = (array)($ModelPage['blog'] ?? []);
$items = (array)($blog['items'] ?? []);
$selected = is_array($blog['selected'] ?? null) ? $blog['selected'] : null;
$lang = (string)($blog['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalFlash = (array)($ModelPage['portal_flash'] ?? []);
$portalComments = (array)($ModelPage['portal_comments'] ?? []);
$csrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$sections = function_exists('public_portal_comment_sections') ? public_portal_comment_sections($lang) : ['discussion' => 'Discussion'];
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
$excerpt = static function (string $html, int $limit = 240): string {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
        return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
    }
    return $text;
};
$clusters = [];
foreach ($items as $item) {
    $cluster = trim((string)($item['cluster_code'] ?? ''));
    if ($cluster !== '' && !in_array($cluster, $clusters, true)) {
        $clusters[] = $cluster;
    }
}
$clusters = array_slice($clusters, 0, 8);
$featured = $selected ?: (isset($items[0]) && is_array($items[0]) ? $items[0] : null);
$listItems = $selected ? $items : array_slice($items, 1);
$commentTree = function(array $nodes, int $depth = 0) use (&$commentTree): void {
    foreach ($nodes as $node) {
        echo '<article class="cpb-comment" style="--depth:' . (int)$depth . '">';
        echo '<div class="cpb-comment-meta"><strong>' . htmlspecialchars((string)($node['display_name'] ?? 'Member'), ENT_QUOTES, 'UTF-8') . '</strong><span>' . htmlspecialchars((string)($node['section_code'] ?? 'discussion'), ENT_QUOTES, 'UTF-8') . '</span><time>' . htmlspecialchars((string)($node['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') . '</time></div>';
        echo '<div class="cpb-comment-body">' . (string)($node['body_html'] ?? '') . '</div>';
        if (!empty($node['children'])) {
            echo '<div class="cpb-comment-children">';
            $commentTree((array)$node['children'], $depth + 1);
            echo '</div>';
        }
        echo '</article>';
    }
};
?>
<style>
.cpb{max-width:1380px;margin:0 auto;padding:28px 18px 64px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.cpb-shell{display:grid;gap:18px}
.cpb-hero,.cpb-feature,.cpb-stream,.cpb-article,.cpb-comments,.cpb-side-panel,.cpb-auth-grid form{position:relative;overflow:hidden;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.86),rgba(5,10,20,.72));backdrop-filter:blur(18px);box-shadow:var(--shell-shadow)}
.cpb-hero,.cpb-feature,.cpb-stream,.cpb-article,.cpb-comments{border-radius:30px}
.cpb-hero{display:grid;grid-template-columns:minmax(0,1.18fr) minmax(340px,.82fr);gap:18px;padding:28px;clip-path:polygon(0 0,95% 0,100% 12%,100% 100%,5% 100%,0 88%)}
.cpb-kicker,.cpb-chip,.cpb-meta-pill,.cpb-comment-meta span{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase}
.cpb-icon{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:999px;background:rgba(255,255,255,.06);font-style:normal}
.cpb-hero-copy{display:grid;gap:16px}
.cpb-hero h1{margin:0;max-width:16ch;font:700 clamp(3.15rem,7vw,5.9rem)/.92 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.cpb-hero p,.cpb-feature p,.cpb-feed-card p,.cpb-side-panel p,.cpb-article-copy,.cpb-comments p,.cpb-comment-body{margin:0;color:var(--shell-muted);line-height:1.8}
.cpb-clusters{display:flex;flex-wrap:wrap;gap:10px}
.cpb-side-rail{display:grid;gap:14px}
.cpb-side-panel{padding:18px;border-radius:24px;background:rgba(255,255,255,.035);clip-path:polygon(0 0,100% 0,100% 84%,93% 100%,0 100%)}
.cpb-side-panel h3,.cpb-feature h2,.cpb-stream h2,.cpb-article h2,.cpb-comments h3{margin:0 0 10px;font:700 clamp(1.65rem,3vw,2.7rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.cpb-side-panel ul{margin:0;padding-left:18px}
.cpb-side-panel li{color:var(--shell-muted);line-height:1.62}

.cpb-layout{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:18px}
.cpb-feature,.cpb-stream,.cpb-article,.cpb-comments{padding:22px}
.cpb-link,.cpb-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 16px;border-radius:16px;font-weight:700;text-decoration:none}
.cpb-link{background:linear-gradient(135deg,#82b6ff,#3ae9ca);color:#04111a}
.cpb-btn{border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text)}
.cpb-feed-list{display:grid;gap:14px}
.cpb-feed-card{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:16px;padding:18px;border-radius:24px;background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,255,255,.025));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,95% 0,100% 12%,100% 100%,5% 100%,0 88%)}
.cpb-feed-card h3{margin:0 0 10px;font:700 1.45rem/1.08 "Space Grotesk","Sora",sans-serif}
.cpb-index,.cpb-feed-arrow{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:18px;border:1px solid rgba(122,180,255,.18);background:rgba(7,18,36,.72);font:700 1rem/1 "Space Grotesk","Sora",sans-serif}
.cpb-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:16px 0}
.cpb-meta div{padding:13px;border-radius:18px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)}
.cpb-meta b{display:block;margin-bottom:5px;color:var(--shell-accent);font-size:11px;letter-spacing:.15em;text-transform:uppercase}
.cpb-article img{width:100%;border-radius:22px;border:1px solid rgba(122,180,255,.14);margin:10px 0 18px}
.cpb-comments{margin-top:18px}
.cpb-auth-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.cpb-auth-grid form,.cpb-comment-form{display:grid;gap:10px}
.cpb-auth-grid input,.cpb-comment-form input,.cpb-comment-form select,.cpb-comment-form textarea{width:100%;box-sizing:border-box;padding:13px 14px;border-radius:16px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.58);color:var(--shell-text)}
.cpb-comment-form textarea{min-height:160px;resize:vertical}
.cpb-toolbar{display:flex;gap:8px;flex-wrap:wrap}
.cpb-toolbar button{padding:9px 11px;border-radius:12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.05);color:var(--shell-text);cursor:pointer}
.cpb-comment{margin-top:14px;padding:16px 18px;border-left:2px solid rgba(122,180,255,.35);margin-left:calc(var(--depth) * 18px);background:rgba(255,255,255,.03);border-radius:0 18px 18px 0}
.cpb-comment-meta{display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:12px;color:var(--shell-muted);text-transform:uppercase;letter-spacing:.08em}
.cpb-comment-body{margin-top:8px}
.cpb-flash{padding:12px 14px;border-radius:14px}
.cpb-flash.ok{background:rgba(23,182,166,.12);border:1px solid rgba(23,182,166,.35);color:#9fe9df}
.cpb-flash.error{background:rgba(255,106,127,.12);border:1px solid rgba(255,106,127,.35);color:#ffb5c0}

@media (max-width: 1120px){
    .cpb-hero,.cpb-layout,.cpb-meta,.cpb-auth-grid{grid-template-columns:1fr}
}
@media (max-width: 760px){
    .cpb{padding:18px 14px 54px}
    .cpb-hero{padding:22px;border-radius:28px}
    .cpb-hero h1{max-width:none;font-size:clamp(2.6rem,13vw,4.25rem)}
    .cpb-feed-card{grid-template-columns:1fr}
}
</style>

<section class="cpb">
    <div class="cpb-shell">
        <header class="cpb-hero">
            <div class="cpb-hero-copy">
                <span class="cpb-kicker"><span class="cpb-icon">✦</span><?= htmlspecialchars($t('Editorial / CPALNYA', 'Editorial / CPALNYA'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="neo-title">
                    <?php if ($selected): ?>
                        <?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <?php else: ?>
                        <?= htmlspecialchars($t('Редакционный слой про арбитражное backstage, где ', 'An editorial layer about affiliate backstage where '), ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('длинные заголовки', 'long headlines'), ENT_QUOTES, 'UTF-8') ?></strong><?= htmlspecialchars($t(' ведут в ', ' lead into '), ENT_QUOTES, 'UTF-8') ?><em><?= htmlspecialchars($t('кластер, решение и обсуждение', 'cluster, solution and discussion'), ENT_QUOTES, 'UTF-8') ?></em>
                    <?php endif; ?>
                </h1>
                <p><?= htmlspecialchars($selected ? $excerpt((string)($selected['excerpt_html'] ?? $selected['content_html'] ?? ''), 280) : $t('Лента намеренно выглядит не как стандартный блог из плиток, а как редакционный фронт с плотным входом в темы, заметной иконографикой, большими смысловыми заголовками и маршрутами в utility-слой.', 'The stream intentionally avoids the look of a standard tile-based blog and instead acts like an editorial front with dense entry points, visible iconography, long-form headlines and routes into the utility layer.'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($clusters)): ?>
                    <div class="cpb-clusters">
                        <?php foreach ($clusters as $cluster): ?>
                            <span class="cpb-chip"><span class="cpb-icon">◈</span><?= htmlspecialchars($cluster, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="cpb-side-rail">
                <article class="cpb-side-panel">
                    <span class="cpb-chip"><span class="cpb-icon">◎</span><?= htmlspecialchars($t('Signal', 'Signal'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3 class="neo-title"><?= htmlspecialchars($t('Что этот экран должен ', 'What this screen should ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('сообщать мгновенно', 'communicate immediately'), ENT_QUOTES, 'UTF-8') ?></strong></h3>
                    <ul>
                        <li><?= htmlspecialchars($t('это не поток случайных статей, а карта кластеров', 'this is not a stream of random posts, but a map of clusters'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><?= htmlspecialchars($t('каждая статья должна вести дальше, а не заканчиваться сама в себе', 'each article should lead onward instead of ending in itself'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><?= htmlspecialchars($t('визуальная иерархия должна помогать сканировать, а не мешать', 'visual hierarchy should help scanning, not fight it'), ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </article>
                <article class="cpb-side-panel">
                    <span class="cpb-chip"><span class="cpb-icon">↯</span><?= htmlspecialchars($t('Route', 'Route'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3 class="neo-title"><?= htmlspecialchars($t('После чтения материал должен ', 'After reading, the material should ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('толкать к действию', 'push toward action'), ENT_QUOTES, 'UTF-8') ?></strong></h3>
                    <p><?= htmlspecialchars($t('Следующий шаг здесь не прячется: related-кластер, готовое решение, ветка обсуждения или продуктовый сценарий. Это и отличает сильное нишевое медиа от обычного блога.', 'The next step is not hidden here: a related cluster, a ready-made solution, a discussion thread or a product route. That is what separates strong niche media from an ordinary blog.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            </aside>
        </header>

        <?php if (!empty($portalFlash['message'])): ?>
            <div class="cpb-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($selected): ?>
            <div class="cpb-layout">
                <article class="cpb-article">
                    <span class="cpb-chip"><span class="cpb-icon">◉</span><?= htmlspecialchars((string)($selected['cluster_code'] ?? $t('Article', 'Article')), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="cpb-meta">
                        <div><b><?= htmlspecialchars($t('Published', 'Published'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['published_at'] ?? $selected['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= htmlspecialchars($t('Views', 'Views'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['view_count'] ?? 0) ?></span></div>
                        <div><b><?= htmlspecialchars($t('Comments', 'Comments'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['comment_count'] ?? 0) ?></span></div>
                    </div>
                    <?php if (!empty($selected['hero_image_src'])): ?><img src="<?= htmlspecialchars((string)$selected['hero_image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                    <div class="cpb-article-copy"><?= (string)($selected['content_html'] ?? '') ?></div>
                    <a class="cpb-btn" href="/blog/"><span>↗</span><?= htmlspecialchars($t('Назад в editorial-поток', 'Back to editorial stream'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>

                <aside>
                    <?php if ($featured && is_array($featured)): ?>
                        <article class="cpb-feature">
                            <span class="cpb-chip"><span class="cpb-icon">⬢</span><?= htmlspecialchars($t('Next route', 'Next route'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="neo-title"><?= htmlspecialchars($t('Статья не должна обрываться, она должна ', 'The article should not end abruptly, it should ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('переводить читателя дальше', 'move the reader onward'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                            <p><?= htmlspecialchars($t('Логичный следующий шаг здесь: открыть раздел решений, забрать готовый шаблон, сравнить подходы в смежном кластере или продолжить обсуждение на уровне практики.', 'The logical next step here is to open the solutions section, grab a working template, compare approaches in an adjacent cluster or continue the discussion at the level of practice.'), ENT_QUOTES, 'UTF-8') ?></p>
                            <a class="cpb-link" href="/solutions/"><span>⬢</span><?= htmlspecialchars($t('Открыть раздел решений', 'Open solutions area'), ENT_QUOTES, 'UTF-8') ?></a>
                        </article>
                    <?php endif; ?>

                    <section class="cpb-comments" id="comments">
                        <span class="cpb-chip"><span class="cpb-icon">✦</span><?= htmlspecialchars($t('Knowledge layer', 'Knowledge layer'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="neo-title"><?= htmlspecialchars($t('Комментарии должны быть не хвостом статьи, а ', 'Comments should not be an article tail, but ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('полевым слоем знаний', 'a field knowledge layer'), ENT_QUOTES, 'UTF-8') ?></strong></h3>
                        <p><?= htmlspecialchars($t('Здесь важны уточнения, рабочие дополнения, развилки мнений и практика внедрения. Поэтому форма и древовидная структура остаются видимыми и функциональными.', 'Clarifications, tactical additions, diverging opinions and implementation practice matter here. That is why the form and threaded structure stay visible and functional.'), ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if ($portalUser): ?>
                            <form method="post" class="cpb-comment-form">
                                <input type="hidden" name="action" value="public_portal_comment">
                                <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="content_type" value="blog">
                                <input type="hidden" name="content_id" value="<?= (int)($selected['id'] ?? 0) ?>">
                                <div class="cpb-toolbar">
                                    <button type="button" data-wrap="**" data-target="cpb-comment-text">B</button>
                                    <button type="button" data-wrap="*" data-target="cpb-comment-text">I</button>
                                    <button type="button" data-wrap="`" data-target="cpb-comment-text">{ }</button>
                                    <button type="button" data-prefix="- " data-target="cpb-comment-text">List</button>
                                </div>
                                <select name="section_code"><?php foreach ($sections as $code => $label): ?><option value="<?= htmlspecialchars((string)$code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                                <textarea id="cpb-comment-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Комментарий, вопрос, практический инсайт', 'Comment, question, practical insight'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                                <button class="cpb-link" type="submit"><span>↗</span><?= htmlspecialchars($t('Опубликовать', 'Publish'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                            <form method="post" style="margin-top:10px">
                                <input type="hidden" name="action" value="public_portal_logout">
                                <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                <button class="cpb-btn" type="submit"><span>◌</span><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                        <?php else: ?>
                            <div class="cpb-auth-grid">
                                <form method="post">
                                    <input type="hidden" name="action" value="public_portal_register">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="text" name="display_name" placeholder="<?= htmlspecialchars($t('Никнейм', 'Display name'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <input type="email" name="email" placeholder="Email" required>
                                    <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <button class="cpb-link" type="submit"><span>✦</span><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="action" value="public_portal_login">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="email" name="email" placeholder="Email" required>
                                    <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <button class="cpb-link" type="submit"><span>↗</span><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($portalComments)) { $commentTree($portalComments); } ?>
                    </section>
                </aside>
            </div>
        <?php else: ?>
            <div class="cpb-layout">
                <?php if ($featured && is_array($featured)): ?>
                    <?php $featuredCluster = trim((string)($featured['cluster_code'] ?? '')); ?>
                    <article class="cpb-feature">
                        <span class="cpb-chip"><span class="cpb-icon">◎</span><?= htmlspecialchars($featuredCluster !== '' ? $featuredCluster : $t('Feature', 'Feature'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="neo-title"><?= htmlspecialchars((string)($featured['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($excerpt((string)($featured['excerpt_html'] ?? $featured['content_html'] ?? ''), 300), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cpb-link" href="/blog/<?= $featuredCluster !== '' ? rawurlencode($featuredCluster) . '/' : '' ?><?= rawurlencode((string)($featured['slug'] ?? '')) ?>/"><span>↗</span><?= htmlspecialchars($t('Открыть материал', 'Open feature'), ENT_QUOTES, 'UTF-8') ?></a>
                    </article>
                <?php endif; ?>

                <section class="cpb-stream">
                    <span class="cpb-chip"><span class="cpb-icon">◈</span><?= htmlspecialchars($t('Stream', 'Stream'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars($t('Поток материалов должен читаться как ', 'The article flow should read like ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('редакционная карта', 'an editorial map'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                    <div class="cpb-feed-list">
                        <?php foreach ($listItems as $index => $item): ?>
                            <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                            <a class="cpb-feed-card" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/">
                                <span class="cpb-index"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                <div>
                                    <div class="cpb-clusters" style="margin-bottom:10px">
                                        <span class="cpb-meta-pill"><span class="cpb-icon">◉</span><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Article', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="cpb-meta-pill"><span class="cpb-icon">◌</span><?= (int)($item['view_count'] ?? 0) ?> views</span>
                                    </div>
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <span class="cpb-feed-arrow">↗</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </div>
</section>
<script>
document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-target]');
    if (!button) return;
    var target = document.getElementById(button.getAttribute('data-target'));
    if (!target) return;
    var prefix = button.getAttribute('data-prefix');
    var wrap = button.getAttribute('data-wrap');
    var start = target.selectionStart || 0;
    var end = target.selectionEnd || 0;
    var value = target.value || '';
    var selected = value.slice(start, end);
    if (prefix) {
        var addition = prefix + (selected || '');
        target.value = value.slice(0, start) + addition + value.slice(end);
        target.setSelectionRange(start + prefix.length, start + addition.length);
        target.focus();
        return;
    }
    if (wrap) {
        var insertion = wrap + selected + wrap;
        target.value = value.slice(0, start) + insertion + value.slice(end);
        target.setSelectionRange(start + wrap.length, start + wrap.length + selected.length);
        target.focus();
    }
});
</script>
