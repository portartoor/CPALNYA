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
$excerpt = static function (string $html, int $limit = 220): string {
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
$clusters = array_slice($clusters, 0, 7);
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
.cpb{max-width:1280px;margin:0 auto;padding:26px 16px 56px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.cpb-shell{display:grid;gap:18px}
.cpb-hero,.cpb-lead-card,.cpb-feed,.cpb-article,.cpb-comments,.cpb-side-panel,.cpb-auth-grid form{position:relative;overflow:hidden;border:1px solid var(--shell-border);background:var(--shell-panel);backdrop-filter:blur(16px);box-shadow:var(--shell-shadow)}
.cpb-hero{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:18px;padding:28px;border-radius:34px;background:
radial-gradient(circle at 10% 15%,rgba(122,180,255,.18),transparent 28%),
radial-gradient(circle at 84% 12%,rgba(44,224,199,.14),transparent 28%),
linear-gradient(145deg,rgba(7,14,28,.96),rgba(10,22,41,.92))}
.cpb-kicker,.cpb-cluster,.cpb-card-kicker,.cpb-comment-meta span{display:inline-flex;align-items:center;padding:7px 11px;border-radius:999px;border:1px solid rgba(122,180,255,.22);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.cpb-hero h1{margin:14px 0 10px;max-width:12ch;font:700 clamp(3.1rem,7vw,5.6rem)/.9 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.cpb-hero p,.cpb-lead-card p,.cpb-feed-card p,.cpb-side-panel p,.cpb-article-copy,.cpb-comments p,.cpb-comment-body{color:var(--shell-muted);line-height:1.72}
.cpb-clusters{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.cpb-side-rail{display:grid;gap:14px}
.cpb-side-panel{padding:16px;border-radius:22px;background:rgba(255,255,255,.03)}
.cpb-side-panel h3{margin:10px 0 8px;font:700 1.2rem/1.06 "Space Grotesk","Sora",sans-serif}
.cpb-side-panel ul{margin:0;padding-left:18px}
.cpb-side-panel li{color:var(--shell-muted);line-height:1.6}

.cpb-layout{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:18px}
.cpb-lead-card,.cpb-feed,.cpb-article,.cpb-comments{padding:22px;border-radius:28px}
.cpb-lead-card h2,.cpb-feed h2,.cpb-article h2,.cpb-comments h3{margin:12px 0 8px;font:700 clamp(1.7rem,3vw,2.6rem)/.95 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.cpb-link,.cpb-btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 15px;border-radius:14px;font-weight:700;text-decoration:none}
.cpb-link{background:linear-gradient(135deg,#7ab4ff,#2ce0c7);color:#07111f}
.cpb-btn{border:1px solid var(--shell-border);background:rgba(255,255,255,.05);color:var(--shell-text)}
.cpb-feed-list{display:grid;gap:14px}
.cpb-feed-card{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;padding:14px 0;border-top:1px solid rgba(255,255,255,.07)}
.cpb-feed-card:first-child{padding-top:0;border-top:0}
.cpb-feed-card h3{margin:10px 0 8px;font:700 1.35rem/1.08 "Space Grotesk","Sora",sans-serif}
.cpb-feed-arrow{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:14px;border:1px solid var(--shell-border);background:rgba(255,255,255,.04)}
.cpb-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:16px 0}
.cpb-meta div{padding:12px;border-radius:16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)}
.cpb-meta b{display:block;margin-bottom:4px;color:var(--shell-accent);font-size:11px;letter-spacing:.14em;text-transform:uppercase}
.cpb-article img{width:100%;border-radius:20px;border:1px solid var(--shell-border);margin:8px 0 16px}
.cpb-comments{margin-top:18px}
.cpb-auth-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.cpb-auth-grid form,.cpb-comment-form{display:grid;gap:10px}
.cpb-auth-grid input,.cpb-comment-form input,.cpb-comment-form select,.cpb-comment-form textarea{width:100%;box-sizing:border-box;padding:12px 14px;border-radius:14px;border:1px solid var(--shell-border);background:rgba(4,8,18,.58);color:var(--shell-text)}
.cpb-comment-form textarea{min-height:150px;resize:vertical}
.cpb-toolbar{display:flex;gap:8px;flex-wrap:wrap}
.cpb-toolbar button{padding:8px 10px;border-radius:10px;border:1px solid var(--shell-border);background:rgba(255,255,255,.05);color:var(--shell-text);cursor:pointer}
.cpb-comment{margin-top:14px;padding:14px 16px;border-left:2px solid rgba(122,180,255,.35);margin-left:calc(var(--depth) * 18px);background:rgba(255,255,255,.03);border-radius:0 16px 16px 0}
.cpb-comment-meta{display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:12px;color:var(--shell-muted);text-transform:uppercase;letter-spacing:.08em}
.cpb-comment-body{margin-top:8px}
.cpb-flash{padding:12px 14px;border-radius:14px}
.cpb-flash.ok{background:rgba(23,182,166,.12);border:1px solid rgba(23,182,166,.35);color:#9fe9df}
.cpb-flash.error{background:rgba(255,106,127,.12);border:1px solid rgba(255,106,127,.35);color:#ffb5c0}

@media (max-width: 1100px){
    .cpb-hero,.cpb-layout,.cpb-meta,.cpb-auth-grid{grid-template-columns:1fr}
}
@media (max-width: 720px){
    .cpb{padding-top:18px}
    .cpb-hero{padding:22px;border-radius:28px}
    .cpb-hero h1{max-width:none;font-size:clamp(2.5rem,12vw,4rem)}
    .cpb-feed-card{grid-template-columns:1fr}
}
</style>

<section class="cpb">
    <div class="cpb-shell">
        <header class="cpb-hero">
            <div>
                <span class="cpb-kicker"><?= htmlspecialchars($t('Editorial / CPALNYA', 'Editorial / CPALNYA'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars($selected ? (string)($selected['title'] ?? '') : $t('Редакционный поток про affiliate-backstage, а не очередной “блог о трафике”', 'An editorial stream about affiliate backstage, not another generic traffic blog'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($selected ? $excerpt((string)($selected['excerpt_html'] ?? $selected['content_html'] ?? ''), 260) : $t('Лента построена по логике нишевых top-сайтов: сильный headline, кластерная навигация, плотная editorial-подача и быстрые маршруты в utility-слой портала.', 'The stream follows the strongest niche-site patterns: heavyweight headlines, cluster navigation, dense editorial treatment and fast routes into the portal’s utility layer.'), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($clusters)): ?>
                    <div class="cpb-clusters">
                        <?php foreach ($clusters as $cluster): ?>
                            <span class="cpb-cluster"><?= htmlspecialchars($cluster, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <aside class="cpb-side-rail">
                <article class="cpb-side-panel">
                    <span class="cpb-card-kicker"><?= htmlspecialchars($t('Signal', 'Signal'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($t('Что считывается с первого экрана', 'What the first screen communicates'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul>
                        <li><?= htmlspecialchars($t('редакционная доминанта', 'editorial dominance'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><?= htmlspecialchars($t('кластерная структура вместо хаоса', 'cluster structure instead of chaos'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><?= htmlspecialchars($t('переход из чтения в полезность', 'route from reading into utility'), ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </article>
                <article class="cpb-side-panel">
                    <span class="cpb-card-kicker"><?= htmlspecialchars($t('Next step', 'Next step'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($t('Маршрут после статьи', 'Post-article route'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($t('Каждый материал должен вести в related-поток, готовое решение или обсуждение. Иначе это просто контент без product-motion.', 'Every article should lead into a related stream, a ready-made asset or a discussion. Otherwise it is just content without product motion.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            </aside>
        </header>

        <?php if (!empty($portalFlash['message'])): ?>
            <div class="cpb-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($selected): ?>
            <div class="cpb-layout">
                <article class="cpb-article">
                    <span class="cpb-card-kicker"><?= htmlspecialchars((string)($selected['cluster_code'] ?? $t('Article', 'Article')), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="cpb-meta">
                        <div><b><?= htmlspecialchars($t('Published', 'Published'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['published_at'] ?? $selected['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= htmlspecialchars($t('Views', 'Views'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['view_count'] ?? 0) ?></span></div>
                        <div><b><?= htmlspecialchars($t('Comments', 'Comments'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['comment_count'] ?? 0) ?></span></div>
                    </div>
                    <?php if (!empty($selected['hero_image_src'])): ?><img src="<?= htmlspecialchars((string)$selected['hero_image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                    <div class="cpb-article-copy"><?= (string)($selected['content_html'] ?? '') ?></div>
                    <a class="cpb-btn" href="/blog/"><?= htmlspecialchars($t('Назад в editorial-ленту', 'Back to editorial feed'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>

                <aside>
                    <?php if ($featured && is_array($featured)): ?>
                        <article class="cpb-lead-card">
                            <span class="cpb-card-kicker"><?= htmlspecialchars($t('Related route', 'Related route'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2><?= htmlspecialchars($t('После чтения', 'After reading'), ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($t('Логичный следующий шаг здесь: перейти в решения, найти шаблон под тему статьи или вернуться в related-кластер и углубиться в поток.', 'The logical next step here is to move into solutions, open a related template or dive deeper into the cluster stream.'), ENT_QUOTES, 'UTF-8') ?></p>
                            <a class="cpb-link" href="/solutions/"><?= htmlspecialchars($t('Открыть раздел решений', 'Open solutions area'), ENT_QUOTES, 'UTF-8') ?></a>
                        </article>
                    <?php endif; ?>

                    <section class="cpb-comments" id="comments">
                        <h3><?= htmlspecialchars($t('Комментарии и полевая практика', 'Comments and field practice'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($t('Нужна не просто форма комментирования, а knowledge-layer поверх статьи: уточнения, практические дополнения, ветки обсуждений и секции.', 'What matters here is not just a comment form, but a knowledge layer on top of the article: clarifications, practical additions, threaded discussion and sections.'), ENT_QUOTES, 'UTF-8') ?></p>
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
                                <button class="cpb-link" type="submit"><?= htmlspecialchars($t('Опубликовать', 'Publish'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                            <form method="post" style="margin-top:10px">
                                <input type="hidden" name="action" value="public_portal_logout">
                                <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                <button class="cpb-btn" type="submit"><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button>
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
                                    <button class="cpb-link" type="submit"><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="action" value="public_portal_login">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="email" name="email" placeholder="Email" required>
                                    <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <button class="cpb-link" type="submit"><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
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
                    <article class="cpb-lead-card">
                        <span class="cpb-card-kicker"><?= htmlspecialchars($featuredCluster !== '' ? $featuredCluster : $t('Feature', 'Feature'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars((string)($featured['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($excerpt((string)($featured['excerpt_html'] ?? $featured['content_html'] ?? ''), 280), ENT_QUOTES, 'UTF-8') ?></p>
                        <a class="cpb-link" href="/blog/<?= $featuredCluster !== '' ? rawurlencode($featuredCluster) . '/' : '' ?><?= rawurlencode((string)($featured['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть материал', 'Open feature'), ENT_QUOTES, 'UTF-8') ?></a>
                    </article>
                <?php endif; ?>
                <section class="cpb-feed">
                    <span class="cpb-card-kicker"><?= htmlspecialchars($t('Feed', 'Feed'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($t('Поток материалов', 'Article stream'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="cpb-feed-list">
                        <?php foreach ($listItems as $item): ?>
                            <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                            <a class="cpb-feed-card" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/">
                                <div>
                                    <span class="cpb-card-kicker"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Article', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <span class="cpb-feed-arrow">01</span>
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
