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
$excerpt = static function (string $html, int $limit = 180): string {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
        return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
    }
    return $text;
};
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
.cpb{max-width:1180px;margin:0 auto;padding:22px 16px 38px;color:#eaf1fb;font-family:"IBM Plex Sans",system-ui,sans-serif}
.cpb-hero,.cpb-article,.cpb-card,.cpb-comments{border:1px solid rgba(127,164,223,.22);border-radius:24px;background:rgba(8,14,28,.84);backdrop-filter:blur(10px)}
.cpb-hero{padding:24px;background:radial-gradient(circle at top left,rgba(120,166,255,.16),transparent 30%),linear-gradient(135deg,rgba(7,12,22,.96),rgba(13,30,52,.92))}
.cpb-hero h1{margin:12px 0 8px;font:700 42px/1.05 "Space Grotesk","IBM Plex Sans",sans-serif}
.cpb-hero p,.cpb-card p,.cpb-article-copy,.cpb-comments p{color:#a7b6cc;line-height:1.68}
.cpb-grid,.cpb-meta,.cpb-auth-grid{display:grid;gap:14px}
.cpb-grid{grid-template-columns:repeat(3,minmax(0,1fr));margin-top:18px}
.cpb-card{padding:18px}
.cpb-card .tag,.cpb-article .tag{display:inline-flex;padding:5px 10px;border-radius:999px;border:1px solid rgba(127,164,223,.25);background:rgba(255,255,255,.04);color:#87b2ff;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.cpb-card h2,.cpb-article h2{margin:14px 0 10px;font:700 24px/1.15 "Space Grotesk","IBM Plex Sans",sans-serif}
.cpb-link,.cpb-btn{display:inline-flex;align-items:center;justify-content:center;margin-top:14px;padding:10px 14px;border-radius:12px;text-decoration:none;font-weight:700}
.cpb-link{background:linear-gradient(135deg,#78a6ff,#17b6a6);color:#07111f}
.cpb-article{margin-top:20px;padding:22px}
.cpb-meta{grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:14px}
.cpb-meta div{padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.03)}
.cpb-meta b{display:block;font-size:12px;color:#7f96b7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
.cpb-article img{width:100%;border-radius:16px;border:1px solid rgba(127,164,223,.18);margin:10px 0 16px}
.cpb-comments{margin-top:18px;padding:20px}
.cpb-auth-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.cpb-auth-grid form,.cpb-comment-form{display:grid;gap:10px}
.cpb-auth-grid input,.cpb-comment-form input,.cpb-comment-form select,.cpb-comment-form textarea{width:100%;box-sizing:border-box;padding:12px 14px;border-radius:12px;border:1px solid rgba(132,159,198,.25);background:rgba(4,8,18,.72);color:#eef5ff}
.cpb-comment-form textarea{min-height:140px;resize:vertical}
.cpb-toolbar{display:flex;gap:8px;flex-wrap:wrap}
.cpb-toolbar button,.cpb-btn{background:rgba(255,255,255,.05);border:1px solid rgba(127,164,223,.22);color:#dce7f7;cursor:pointer}
.cpb-comment{margin-top:14px;padding:14px;border-left:2px solid rgba(102,165,255,.35);margin-left:calc(var(--depth) * 18px);background:rgba(255,255,255,.02);border-radius:0 12px 12px 0}
.cpb-comment-meta{display:flex;gap:10px;flex-wrap:wrap;font-size:12px;color:#8ca4c7;text-transform:uppercase;letter-spacing:.06em}
.cpb-comment-body{margin-top:8px;color:#dce6f6;line-height:1.66}
.cpb-flash{margin-top:12px;padding:12px 14px;border-radius:12px}.cpb-flash.ok{background:rgba(23,182,166,.12);border:1px solid rgba(23,182,166,.35);color:#9fe9df}.cpb-flash.error{background:rgba(255,106,127,.12);border:1px solid rgba(255,106,127,.35);color:#ffb5c0}
@media (max-width:980px){.cpb-grid,.cpb-meta,.cpb-auth-grid{grid-template-columns:1fr}}
</style>
<section class="cpb">
    <header class="cpb-hero">
        <span class="tag"><?= htmlspecialchars($t('Редакция CPALNYA', 'CPALNYA editorial'), ENT_QUOTES, 'UTF-8') ?></span>
        <h1><?= htmlspecialchars($selected ? (string)($selected['title'] ?? '') : $t('Статьи, разборы и backstage affiliate-рынка', 'Articles, breakdowns and affiliate-market backstage'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($selected ? $excerpt((string)($selected['excerpt_html'] ?? $selected['content_html'] ?? '')) : $t('Редакционный хаб по арбитражу трафика: SEO-кластеры, инфраструктура, аналитика, командные процессы, офферы и готовые решения.', 'Editorial hub for affiliate traffic: SEO clusters, infrastructure, analytics, team operations, offers and ready-made solutions.'), ENT_QUOTES, 'UTF-8') ?></p>
    </header>

    <?php if (!empty($portalFlash['message'])): ?>
        <div class="cpb-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($selected): ?>
        <article class="cpb-article">
            <span class="tag"><?= htmlspecialchars((string)($selected['cluster_code'] ?? $t('Статья', 'Article')), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="cpb-meta">
                <div><b><?= htmlspecialchars($t('Дата', 'Published'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['published_at'] ?? $selected['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><b><?= htmlspecialchars($t('Просмотры', 'Views'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['view_count'] ?? 0) ?></span></div>
                <div><b><?= htmlspecialchars($t('Комментарии', 'Comments'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['comment_count'] ?? 0) ?></span></div>
            </div>
            <?php if (!empty($selected['hero_image_src'])): ?><img src="<?= htmlspecialchars((string)$selected['hero_image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
            <div class="cpb-article-copy"><?= (string)($selected['content_html'] ?? '') ?></div>
            <a class="cpb-btn" href="/blog/"><?= htmlspecialchars($t('Назад в блог', 'Back to blog'), ENT_QUOTES, 'UTF-8') ?></a>
        </article>

        <section class="cpb-comments" id="comments">
            <h3><?= htmlspecialchars($t('Комментарии и практика', 'Comments and field notes'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($t('Для комментариев нужна регистрация. Это дает структурированную комьюнити-слойку поверх статей: вопросы, идеи и практические дополнения.', 'Comments require registration. That creates a structured community layer on top of articles: questions, ideas and practical add-ons.'), ENT_QUOTES, 'UTF-8') ?></p>
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
                    <textarea id="cpb-comment-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Комментарий, вопрос или практический инсайт', 'Comment, question or practical insight'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                    <button class="cpb-link" type="submit"><?= htmlspecialchars($t('Опубликовать', 'Publish'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
                <form method="post" style="margin-top:10px"><input type="hidden" name="action" value="public_portal_logout"><input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><button class="cpb-btn" type="submit"><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button></form>
            <?php else: ?>
                <div class="cpb-auth-grid">
                    <form method="post"><input type="hidden" name="action" value="public_portal_register"><input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><input type="text" name="display_name" placeholder="<?= htmlspecialchars($t('Никнейм', 'Display name'), ENT_QUOTES, 'UTF-8') ?>" required><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required><button class="cpb-link" type="submit"><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button></form>
                    <form method="post"><input type="hidden" name="action" value="public_portal_login"><input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required><button class="cpb-link" type="submit"><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button></form>
                </div>
            <?php endif; ?>
            <?php if (!empty($portalComments)) { $commentTree($portalComments); } ?>
        </section>
    <?php else: ?>
        <div class="cpb-grid">
            <?php foreach ($items as $item): $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                <article class="cpb-card">
                    <span class="tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('Статья', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($excerpt((string)($item['excerpt_html'] ?? $item['content_html'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="cpb-link" href="/blog/<?= $cluster !== '' ? rawurlencode($cluster) . '/' : '' ?><?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть статью', 'Open article'), ENT_QUOTES, 'UTF-8') ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
