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
$utilityNotes = [
    $t('короткая ценность без “воды”', 'short value proposition'),
    $t('стек и формат сразу на карточке', 'stack and format on-card'),
    $t('маршрут: изучить -> внедрить -> обсудить', 'route: study -> deploy -> discuss'),
];
?>
<style>
.sol{max-width:1280px;margin:0 auto;padding:26px 16px 56px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.sol-shell{display:grid;gap:18px}
.sol-hero,.sol-board,.sol-list,.sol-card,.sol-detail,.sol-comments,.sol-side-panel,.sol-auth-grid form{position:relative;overflow:hidden;border:1px solid var(--shell-border);background:var(--shell-panel);backdrop-filter:blur(16px);box-shadow:var(--shell-shadow)}
.sol-hero{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:18px;padding:28px;border-radius:34px;background:
radial-gradient(circle at 12% 14%,rgba(44,224,199,.18),transparent 24%),
radial-gradient(circle at 84% 10%,rgba(122,180,255,.2),transparent 26%),
linear-gradient(145deg,rgba(7,14,28,.96),rgba(10,22,41,.92))}
.sol-kicker,.sol-tab,.sol-type,.sol-board-list span,.sol-comment-meta span{display:inline-flex;align-items:center;padding:7px 11px;border-radius:999px;border:1px solid rgba(122,180,255,.22);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.sol-hero h1{margin:14px 0 10px;max-width:11ch;font:700 clamp(3rem,7vw,5.8rem)/.9 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.sol-hero p,.sol-card p,.sol-detail-copy,.sol-comments p,.sol-side-panel p,.sol-comment-body{color:var(--shell-muted);line-height:1.72}
.sol-tab-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.sol-tab{padding:12px 16px;text-decoration:none;color:var(--shell-text);font-size:12px}
.sol-tab.is-active{background:linear-gradient(135deg,#7ab4ff,#2ce0c7);color:#07111f;border-color:transparent}
.sol-hero-side{display:grid;gap:14px}
.sol-side-panel{padding:16px;border-radius:22px;background:rgba(255,255,255,.03)}
.sol-side-panel h3{margin:10px 0 8px;font:700 1.18rem/1.06 "Space Grotesk","Sora",sans-serif}
.sol-board-list{display:grid;gap:10px}
.sol-board-list span{width:max-content;color:var(--shell-accent)}

.sol-layout{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:18px}
.sol-list,.sol-board,.sol-detail,.sol-comments{padding:22px;border-radius:28px}
.sol-list-head,.sol-board-head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:16px}
.sol-list-head h2,.sol-board-head h2,.sol-detail h2,.sol-comments h3{margin:0;font:700 clamp(1.8rem,3vw,2.8rem)/.95 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.sol-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.sol-card{padding:18px;border-radius:24px}
.sol-card h3{margin:12px 0 10px;font:700 1.45rem/1.06 "Space Grotesk","Sora",sans-serif}
.sol-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:14px}
.sol-meta div{padding:12px;border-radius:16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)}
.sol-meta b{display:block;margin-bottom:4px;color:var(--shell-accent);font-size:11px;letter-spacing:.14em;text-transform:uppercase}
.sol-link,.sol-btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 15px;border-radius:14px;font-weight:700;text-decoration:none}
.sol-link{margin-top:14px;background:linear-gradient(135deg,#7ab4ff,#2ce0c7);color:#07111f}
.sol-btn{border:1px solid var(--shell-border);background:rgba(255,255,255,.05);color:var(--shell-text)}

.sol-detail-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.sol-comments{margin-top:18px}
.sol-auth-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.sol-auth-grid form,.solutions-comment-form{display:grid;gap:10px}
.sol-auth-grid input,.solutions-comment-form input,.solutions-comment-form select,.solutions-comment-form textarea{width:100%;box-sizing:border-box;padding:12px 14px;border-radius:14px;border:1px solid var(--shell-border);background:rgba(4,8,18,.58);color:var(--shell-text)}
.solutions-comment-form textarea{min-height:150px;resize:vertical}
.sol-toolbar{display:flex;gap:8px;flex-wrap:wrap}
.sol-toolbar button{padding:8px 10px;border-radius:10px;border:1px solid var(--shell-border);background:rgba(255,255,255,.05);color:var(--shell-text);cursor:pointer}
.sol-comment{margin-top:14px;padding:14px 16px;border-left:2px solid rgba(122,180,255,.35);margin-left:calc(var(--comment-depth) * 18px);background:rgba(255,255,255,.03);border-radius:0 16px 16px 0}
.sol-comment-meta{display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:12px;color:var(--shell-muted);text-transform:uppercase;letter-spacing:.08em}
.sol-comment-body{margin-top:8px}
.sol-flash{padding:12px 14px;border-radius:14px}
.sol-flash.ok{background:rgba(23,182,166,.12);border:1px solid rgba(23,182,166,.35);color:#9fe9df}
.sol-flash.error{background:rgba(255,106,127,.12);border:1px solid rgba(255,106,127,.35);color:#ffb5c0}

@media (max-width: 1100px){
    .sol-hero,.sol-layout,.sol-grid,.sol-meta,.sol-auth-grid{grid-template-columns:1fr}
}
@media (max-width: 720px){
    .sol{padding-top:18px}
    .sol-hero{padding:22px;border-radius:28px}
    .sol-hero h1{max-width:none;font-size:clamp(2.5rem,12vw,4rem)}
}
</style>

<section class="sol">
    <div class="sol-shell">
        <header class="sol-hero">
            <div>
                <span class="sol-kicker"><?= htmlspecialchars($t('Utility / CPALNYA Lab', 'Utility / CPALNYA Lab'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars($t('Раздел решений должен ощущаться как рабочий directory, а не как вторая версия блога', 'The solutions area should feel like a working directory, not a second version of the blog'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($t('Здесь пользователь должен быстро считывать ценность, сложность, стек и следующий шаг. Поэтому секция собрана по логике top utility-площадок: плотные карточки, ясная иерархия и route в внедрение или обсуждение.', 'Users should immediately understand the value, complexity, stack and next step. That is why the section follows top utility-property logic: dense cards, clear hierarchy and a route into implementation or discussion.'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="sol-tab-row">
                    <a class="sol-tab <?= $tab === 'downloads' ? 'is-active' : '' ?>" href="/solutions/downloads/"><?= htmlspecialchars($t('Скачиваемые assets', 'Downloadable assets'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="sol-tab <?= $tab === 'articles' ? 'is-active' : '' ?>" href="/solutions/articles/"><?= htmlspecialchars($t('Playbooks и статьи', 'Playbooks and articles'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>
            <aside class="sol-hero-side">
                <article class="sol-side-panel">
                    <span class="sol-type"><?= htmlspecialchars($t('Utility notes', 'Utility notes'), ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="sol-board-list">
                        <?php foreach ($utilityNotes as $note): ?>
                            <span><?= htmlspecialchars($note, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                </article>
                <article class="sol-side-panel">
                    <span class="sol-type"><?= htmlspecialchars($t('Route', 'Route'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($t('Маршрут пользователя', 'User route'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($t('Не просто открыть карточку, а перейти из решения в внедрение, комментарии или смежный кластер материалов. Так utility-раздел начинает работать как продуктовая воронка.', 'The goal is not just opening a card, but moving from a solution into implementation, comments or an adjacent cluster. That is how a utility section starts behaving like a product funnel.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            </aside>
        </header>

        <?php if (!empty($portalFlash['message'])): ?>
            <div class="sol-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($selected): ?>
            <div class="sol-layout">
                <article class="sol-detail">
                    <span class="sol-type"><?= htmlspecialchars($selected['solution_type'] === 'article' ? $t('Playbook', 'Playbook') : $t('Download', 'Download'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="sol-meta">
                        <div><b><?= htmlspecialchars($t('Category', 'Category'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['category_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= htmlspecialchars($t('Difficulty', 'Difficulty'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($selected['difficulty_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                        <div><b><?= htmlspecialchars($t('Views', 'Views'), ENT_QUOTES, 'UTF-8') ?></b><span><?= (int)($selected['view_count'] ?? 0) ?></span></div>
                    </div>
                    <div class="sol-detail-copy">
                        <?= (string)($selected['excerpt_html'] ?? '') ?>
                        <?= (string)($selected['content_html'] ?? '') ?>
                    </div>
                    <div class="sol-detail-actions">
                        <a class="sol-btn" href="/solutions/<?= $tab === 'articles' ? 'articles' : 'downloads' ?>/"><?= htmlspecialchars($t('Назад к списку', 'Back to list'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </article>

                <aside>
                    <article class="sol-board">
                        <div class="sol-board-head">
                            <div>
                                <span class="sol-type"><?= htmlspecialchars($t('Operator note', 'Operator note'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h2><?= htmlspecialchars($t('После открытия', 'After opening'), ENT_QUOTES, 'UTF-8') ?></h2>
                            </div>
                        </div>
                        <p><?= htmlspecialchars($t('Детальная страница должна не только объяснять решение, но и подталкивать к следующему действию: скачать, внедрить, обсудить, сравнить с related-материалом.', 'A detail page should not only explain the solution, but also push the next action: download, deploy, discuss or compare with a related asset.'), ENT_QUOTES, 'UTF-8') ?></p>
                    </article>

                    <section class="sol-comments" id="comments">
                        <h3><?= htmlspecialchars($t('Комментарии и полевая практика', 'Comments and field practice'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($t('Тут комментарии работают как knowledge-layer поверх решения: что реально сработало, где были ошибки и как адаптировать asset под команду.', 'Comments here operate as a knowledge layer on top of the solution: what actually worked, where the pitfalls were and how to adapt the asset for a team.'), ENT_QUOTES, 'UTF-8') ?></p>
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
                                <select name="section_code"><?php foreach ($sections as $code => $label): ?><option value="<?= htmlspecialchars((string)$code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                                <textarea id="sol-comment-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Комментарий, вопрос, практический инсайт', 'Comment, question, practical insight'), ENT_QUOTES, 'UTF-8') ?>"></textarea>
                                <button class="sol-link" type="submit"><?= htmlspecialchars($t('Опубликовать', 'Publish'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                            <form method="post" style="margin-top:10px">
                                <input type="hidden" name="action" value="public_portal_logout">
                                <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                <button class="sol-btn" type="submit"><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                        <?php else: ?>
                            <div class="sol-auth-grid">
                                <form method="post">
                                    <input type="hidden" name="action" value="public_portal_register">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="text" name="display_name" placeholder="<?= htmlspecialchars($t('Никнейм', 'Display name'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <input type="email" name="email" placeholder="Email" required>
                                    <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <button class="sol-link" type="submit"><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="action" value="public_portal_login">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="email" name="email" placeholder="Email" required>
                                    <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <button class="sol-link" type="submit"><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($comments)): ?><div class="sol-comment-list"><?php $renderComments($comments); ?></div><?php endif; ?>
                    </section>
                </aside>
            </div>
        <?php else: ?>
            <div class="sol-layout">
                <section class="sol-list">
                    <div class="sol-list-head">
                        <div>
                            <span class="sol-type"><?= htmlspecialchars($tab === 'articles' ? $t('Playbooks', 'Playbooks') : $t('Downloads', 'Downloads'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2><?= htmlspecialchars($tab === 'articles' ? $t('Практические разборы', 'Practical playbooks') : $t('Рабочие assets', 'Working assets'), ENT_QUOTES, 'UTF-8') ?></h2>
                        </div>
                    </div>
                    <div class="sol-grid">
                        <?php foreach ($items as $item): ?>
                            <article class="sol-card">
                                <span class="sol-type"><?= htmlspecialchars((string)($item['solution_type'] ?? 'download'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars(trim(strip_tags((string)($item['excerpt_html'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="sol-meta">
                                    <div><b><?= htmlspecialchars($t('Category', 'Category'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['category_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div><b><?= htmlspecialchars($t('Stack', 'Stack'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['stack_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div><b><?= htmlspecialchars($t('Format', 'Format'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['file_format'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                </div>
                                <a class="sol-link" href="/solutions/<?= $tab === 'articles' ? 'articles' : 'downloads' ?>/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><?= htmlspecialchars($t('Открыть', 'Open'), ENT_QUOTES, 'UTF-8') ?></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <aside class="sol-board">
                    <div class="sol-board-head">
                        <div>
                            <span class="sol-type"><?= htmlspecialchars($t('Why this layout', 'Why this layout'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2><?= htmlspecialchars($t('Что должно ощущаться', 'What it should feel like'), ENT_QUOTES, 'UTF-8') ?></h2>
                        </div>
                    </div>
                    <p><?= htmlspecialchars($t('Этот экран должен восприниматься ближе к operator catalog и solution directory, чем к обычной статье. Пользователь должен буквально “сканировать” карточки и быстро принимать решение.', 'This screen should feel closer to an operator catalog and a solution directory than to a normal article list. Users should be able to scan cards and decide quickly.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="sol-board-list">
                        <?php foreach ($utilityNotes as $note): ?>
                            <span><?= htmlspecialchars($note, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <a class="sol-link" href="/contact/"><?= htmlspecialchars($t('Запросить кастомный asset', 'Request custom asset'), ENT_QUOTES, 'UTF-8') ?></a>
                </aside>
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
