<?php
$solutions = (array)($ModelPage['solutions'] ?? []);
$items = (array)($solutions['items'] ?? []);
$selected = is_array($solutions['selected'] ?? null) ? $solutions['selected'] : null;
$comments = (array)($ModelPage['portal_comments'] ?? []);
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalFlash = (array)($ModelPage['portal_flash'] ?? []);
$lang = (string)($solutions['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$tab = (string)($solutions['tab'] ?? 'downloads');
$sections = function_exists('public_portal_comment_sections') ? public_portal_comment_sections($lang) : ['discussion' => 'Discussion'];
$csrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';

$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
$renderComments = static function (array $nodes, int $depth = 0) use (&$renderComments): void {
    foreach ($nodes as $node) {
        $name = trim((string)($node['display_name'] ?? 'Member'));
        $html = (string)($node['body_html'] ?? '');
        $section = trim((string)($node['section_code'] ?? 'discussion'));
        ?>
        <article class="sol-comment" style="--comment-depth:<?= (int)$depth ?>">
            <div class="sol-comment-meta">
                <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($section, ENT_QUOTES, 'UTF-8') ?></span>
                <time><?= htmlspecialchars((string)($node['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></time>
            </div>
            <div class="sol-comment-body"><?= $html ?></div>
            <?php if (!empty($node['children'])): ?>
                <div class="sol-comment-children"><?php $renderComments((array)$node['children'], $depth + 1); ?></div>
            <?php endif; ?>
        </article>
        <?php
    }
};
?>
<style>
.solutions-page{max-width:1220px;margin:0 auto;padding:24px 16px 42px;color:#e8eef7;font-family:"IBM Plex Sans",system-ui,sans-serif}
.solutions-hero{position:relative;overflow:hidden;border:1px solid rgba(120,169,255,.28);border-radius:28px;padding:28px;background:radial-gradient(circle at top left,rgba(79,147,255,.2),transparent 42%),linear-gradient(135deg,rgba(10,18,34,.96),rgba(13,34,58,.92))}
.solutions-hero h1{margin:0;font-size:44px;line-height:1.05;font-family:"Space Grotesk","IBM Plex Sans",sans-serif}
.solutions-hero p{max-width:78ch;color:#aab8d0;line-height:1.65}
.solutions-tabs,.solutions-grid,.solutions-meta,.solutions-auth-forms{display:grid;gap:12px}
.solutions-tabs{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:18px}
.solutions-tab,.solutions-card-link,.solutions-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
.solutions-tab{padding:12px 14px;border-radius:14px;border:1px solid rgba(127,164,223,.28);background:rgba(10,18,34,.62);color:#bcd0ee;font-weight:700}
.solutions-tab.is-active{background:linear-gradient(135deg,#78a6ff,#17b6a6);color:#08111d;border-color:transparent}
.solutions-grid{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:18px}
.solutions-card,.solutions-detail,.solutions-comments{border:1px solid rgba(127,164,223,.22);border-radius:22px;background:rgba(8,14,28,.86);backdrop-filter:blur(10px)}
.solutions-card{padding:18px}
.solutions-card-type,.solutions-detail-type{display:inline-flex;padding:5px 10px;border-radius:999px;background:rgba(111,166,255,.15);border:1px solid rgba(111,166,255,.3);color:#89b3ff;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.solutions-card h2,.solutions-detail h2{margin:14px 0 10px;font-family:"Space Grotesk","IBM Plex Sans",sans-serif}
.solutions-card p,.solutions-detail-copy,.solutions-detail p,.solutions-comments p,.sol-auth-note{color:#a6b5cb;line-height:1.66}
.solutions-meta{grid-template-columns:repeat(3,minmax(0,1fr));margin-top:12px}
.solutions-meta div{padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.03)}
.solutions-meta b{display:block;font-size:12px;color:#7f96b7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
.solutions-card-link,.solutions-btn{margin-top:14px;padding:10px 14px;border-radius:12px;font-weight:700}
.solutions-card-link{background:linear-gradient(135deg,#78a6ff,#17b6a6);color:#08111d}
.solutions-detail{margin-top:20px;padding:22px}
.solutions-detail-actions{display:flex;gap:10px;flex-wrap:wrap}
.solutions-btn{background:rgba(255,255,255,.05);border:1px solid rgba(127,164,223,.22);color:#dce7f7}
.solutions-comments{margin-top:18px;padding:20px}
.sol-auth-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.solutions-auth-forms form,.solutions-comment-form{display:grid;gap:10px}
.solutions-auth-forms input,.solutions-comment-form input,.solutions-comment-form select,.solutions-comment-form textarea{width:100%;box-sizing:border-box;padding:12px 14px;border-radius:12px;border:1px solid rgba(132,159,198,.25);background:rgba(4,8,18,.72);color:#eef5ff}
.solutions-comment-form textarea{min-height:150px;resize:vertical}
.sol-toolbar{display:flex;gap:8px;flex-wrap:wrap}
.sol-toolbar button{padding:8px 10px;border-radius:10px;border:1px solid rgba(127,164,223,.25);background:rgba(255,255,255,.04);color:#dce7f7;cursor:pointer}
.sol-comment{margin-top:14px;padding:14px;border-left:2px solid rgba(102,165,255,.35);margin-left:calc(var(--comment-depth) * 18px);background:rgba(255,255,255,.02);border-radius:0 12px 12px 0}
.sol-comment-meta{display:flex;gap:10px;flex-wrap:wrap;font-size:12px;color:#8ca4c7;text-transform:uppercase;letter-spacing:.06em}
.sol-comment-body{margin-top:8px;color:#dce6f6;line-height:1.66}
.sol-comment-body p,.sol-comment-body ul{margin:0 0 10px}
.sol-flash{margin-top:12px;padding:12px 14px;border-radius:12px}
.sol-flash.ok{background:rgba(23,182,166,.12);border:1px solid rgba(23,182,166,.35);color:#9fe9df}
.sol-flash.error{background:rgba(255,106,127,.12);border:1px solid rgba(255,106,127,.35);color:#ffb5c0}
@media (max-width:980px){.solutions-grid,.sol-auth-grid,.solutions-meta{grid-template-columns:1fr}.solutions-hero h1{font-size:34px}}
</style>

<section class="solutions-page">
    <header class="solutions-hero">
        <span class="solutions-detail-type"><?= htmlspecialchars($t('CPALNYA Lab', 'CPALNYA Lab'), ENT_QUOTES, 'UTF-8') ?></span>
        <h1><?= htmlspecialchars($t('Готовые решения и закрытая техничка для арбитража', 'Ready-made solutions and behind-the-scenes affiliate tech'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($t('Раздел разделен на две части: загрузки и операционные шаблоны для быстрого внедрения, а также практические статьи с архитектурой, SEO-логикой и разбором рабочих процессов арбитражной команды.', 'This section is split into two tracks: downloadable operational assets for fast rollout and practical articles covering architecture, SEO logic and real affiliate team workflows.'), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="solutions-tabs">
            <a class="solutions-tab <?= $tab === 'downloads' ? 'is-active' : '' ?>" href="/solutions/downloads/"><?= htmlspecialchars($t('Скачать решения', 'Download assets'), ENT_QUOTES, 'UTF-8') ?></a>
            <a class="solutions-tab <?= $tab === 'articles' ? 'is-active' : '' ?>" href="/solutions/articles/"><?= htmlspecialchars($t('Читать разборы', 'Read playbooks'), ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </header>

    <?php if (!empty($portalFlash['message'])): ?>
        <div class="sol-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($selected): ?>
        <article class="solutions-detail">
            <span class="solutions-detail-type"><?= htmlspecialchars($selected['solution_type'] === 'article' ? $t('Практический разбор', 'Playbook article') : $t('Готовая загрузка', 'Ready download'), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="solutions-meta">
                <div><b><?= htmlspecialchars($t('Категория', 'Category'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['category_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><b><?= htmlspecialchars($t('Сложность', 'Difficulty'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['difficulty_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><b><?= htmlspecialchars($t('Просмотры', 'Views'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['view_count'] ?? 0) ?></span></div>
            </div>
            <div class="solutions-detail-copy">
                <?= (string)($selected['excerpt_html'] ?? '') ?>
                <?= (string)($selected['content_html'] ?? '') ?>
            </div>
            <div class="solutions-detail-actions">
                <a class="solutions-btn" href="/solutions/<?= $tab === 'articles' ? 'articles' : 'downloads' ?>/"><?= htmlspecialchars($t('Назад к списку', 'Back to list'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </article>

        <section class="solutions-comments" id="comments">
            <h3><?= htmlspecialchars($t('Комментарии и полевой опыт', 'Comments and field notes'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($t('Комментарии доступны только зарегистрированным участникам. Внутри можно задавать вопросы, делиться практикой и раскладывать гипотезы по секциям.', 'Comments are available to registered members only. Use them for questions, field notes and structured hypotheses by section.'), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($portalUser): ?>
                <form method="post" class="solutions-comment-form">
                    <input type="hidden" name="action" value="public_portal_comment">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="solutions">
                    <input type="hidden" name="content_id" value="<?= (int)($selected['id'] ?? 0) ?>">
                    <div class="sol-toolbar">
                        <button type="button" data-wrap="**" data-target="sol-comment-text">B</button>
                        <button type="button" data-wrap="*" data-target="sol-comment-text">I</button>
                        <button type="button" data-wrap="`" data-target="sol-comment-text">{ }</button>
                        <button type="button" data-prefix="- " data-target="sol-comment-text">List</button>
                    </div>
                    <select name="section_code">
                        <?php foreach ($sections as $code => $label): ?>
                            <option value="<?= htmlspecialchars((string)$code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea id="sol-comment-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Напишите комментарий, вопрос или практический инсайт. Можно использовать **жирный**, *курсив*, `код` и списки.', 'Write a comment, question or practical insight. You can use **bold**, *italic*, `code` and lists.'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                    <button class="solutions-card-link" type="submit"><?= htmlspecialchars($t('Опубликовать комментарий', 'Publish comment'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            <?php else: ?>
                <div class="solutions-auth-forms">
                    <p class="sol-auth-note"><?= htmlspecialchars($t('Чтобы комментировать, зарегистрируйтесь или войдите. Это также позволяет считать возвращаемость аудитории и строить закрытые ветки под будущие комьюнити-механики.', 'To comment, register or sign in. This also enables return-visitor tracking and future gated community mechanics.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="sol-auth-grid">
                        <form method="post">
                            <input type="hidden" name="action" value="public_portal_register">
                            <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="text" name="display_name" placeholder="<?= htmlspecialchars($t('Никнейм', 'Display name'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <input type="email" name="email" placeholder="Email" required>
                            <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <button class="solutions-card-link" type="submit"><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button>
                        </form>
                        <form method="post">
                            <input type="hidden" name="action" value="public_portal_login">
                            <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="email" name="email" placeholder="Email" required>
                            <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <button class="solutions-card-link" type="submit"><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($portalUser): ?>
                <form method="post" style="margin-top:10px">
                    <input type="hidden" name="action" value="public_portal_logout">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                    <button class="solutions-btn" type="submit"><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            <?php endif; ?>

            <?php if (!empty($comments)): ?>
                <div class="sol-comment-list"><?php $renderComments($comments); ?></div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <div class="solutions-grid">
            <?php foreach ($items as $item): ?>
                <article class="solutions-card">
                    <span class="solutions-card-type"><?= htmlspecialchars((string)($item['solution_type'] ?? 'download'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= trim(strip_tags((string)($item['excerpt_html'] ?? ''))) ?></p>
                    <div class="solutions-meta">
                        <div><b><?= htmlspecialchars($t('Категория', 'Category'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['category_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= htmlspecialchars($t('Стек', 'Stack'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['stack_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= htmlspecialchars($t('Формат', 'Format'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['file_format'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                    </div>
                    <a class="solutions-card-link" href="/solutions/<?= $tab === 'articles' ? 'articles' : 'downloads' ?>/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть', 'Open'), ENT_QUOTES, 'UTF-8') ?></a>
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
        target.focus();
        target.setSelectionRange(start + prefix.length, start + addition.length);
        return;
    }
    if (wrap) {
        var insertion = wrap + selected + wrap;
        target.value = value.slice(0, start) + insertion + value.slice(end);
        target.focus();
        target.setSelectionRange(start + wrap.length, start + wrap.length + selected.length);
    }
});
</script>
