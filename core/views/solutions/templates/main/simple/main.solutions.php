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
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
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
$notes = [
    ['icon' => '↓', 'text' => $t('скачать и внедрить без долгого вступления', 'download and deploy without a long intro')],
    ['icon' => '▣', 'text' => $t('видеть стек, формат и сложность прямо на карточке', 'see stack, format and difficulty directly on the card')],
    ['icon' => '↯', 'text' => $t('иметь маршрут в обсуждение и related-поток', 'have a route into discussion and related flow')],
];
?>
<style>
.sol{max-width:1380px;margin:0 auto;padding:28px 18px 64px;color:var(--shell-text);font-family:"Sora",system-ui,sans-serif}
.sol-shell{display:grid;gap:18px}
.sol-hero,.sol-board,.sol-list,.sol-card,.sol-detail,.sol-comments,.sol-side-panel,.sol-auth-grid form{position:relative;overflow:hidden;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.86),rgba(5,10,20,.72));backdrop-filter:blur(18px);box-shadow:var(--shell-shadow)}
.sol-hero,.sol-board,.sol-list,.sol-detail,.sol-comments{border-radius:30px}
.sol-hero{display:grid;grid-template-columns:minmax(0,1.18fr) minmax(340px,.82fr);gap:18px;padding:28px;clip-path:polygon(0 0,95% 0,100% 12%,100% 100%,5% 100%,0 88%)}
.sol-kicker,.sol-tab,.sol-type,.sol-note,.sol-comment-meta span{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase}
.sol-icon{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:999px;background:rgba(255,255,255,.06);font-style:normal}
.sol-hero-copy{display:grid;gap:16px}
.sol-hero h1{margin:0;max-width:16ch;font:700 clamp(3rem,7vw,5.8rem)/.92 "Space Grotesk","Sora",sans-serif;letter-spacing:-.08em}
.sol-hero p,.sol-card p,.sol-detail-copy,.sol-comments p,.sol-side-panel p,.sol-comment-body{margin:0;color:var(--shell-muted);line-height:1.8}
.sol-tab-row{display:flex;gap:10px;flex-wrap:wrap}
.sol-tab{padding:12px 16px;text-decoration:none;color:var(--shell-text);font-size:12px}
.sol-tab.is-active{background:linear-gradient(135deg,#82b6ff,#3ae9ca);color:#04111a;border-color:transparent}
.sol-hero-side{display:grid;gap:14px}
.sol-side-panel{padding:18px;border-radius:24px;background:rgba(255,255,255,.035);clip-path:polygon(0 0,100% 0,100% 84%,93% 100%,0 100%)}
.sol-side-panel h3,.sol-board-head h2,.sol-list-head h2,.sol-detail h2,.sol-comments h3{margin:0 0 10px;font:700 clamp(1.65rem,3vw,2.7rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.sol-notes{display:grid;gap:10px}
.sol-note{width:max-content}

.sol-layout{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(320px,.92fr);gap:18px}
.sol-list,.sol-board,.sol-detail,.sol-comments{padding:22px}
.sol-list-head,.sol-board-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px}
.sol-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.sol-card{padding:18px;border-radius:24px;background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,255,255,.025));border:1px solid rgba(255,255,255,.06);clip-path:polygon(0 0,95% 0,100% 12%,100% 100%,5% 100%,0 88%)}
.sol-card h3{margin:0 0 10px;font:700 1.4rem/1.08 "Space Grotesk","Sora",sans-serif}
.sol-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:14px}
.sol-meta div{padding:12px;border-radius:16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)}
.sol-meta b{display:block;margin-bottom:4px;color:var(--shell-accent);font-size:11px;letter-spacing:.14em;text-transform:uppercase}
.sol-link,.sol-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:12px 16px;border-radius:16px;font-weight:700;text-decoration:none}
.sol-link{margin-top:14px;background:linear-gradient(135deg,#82b6ff,#3ae9ca);color:#04111a}
.sol-btn{border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text)}

.sol-detail-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.sol-comments{margin-top:18px}
.sol-auth-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.sol-auth-grid form,.solutions-comment-form{display:grid;gap:10px}
.sol-auth-grid input,.solutions-comment-form input,.solutions-comment-form select,.solutions-comment-form textarea{width:100%;box-sizing:border-box;padding:13px 14px;border-radius:16px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.58);color:var(--shell-text)}
.solutions-comment-form textarea{min-height:160px;resize:vertical}
.sol-toolbar{display:flex;gap:8px;flex-wrap:wrap}
.sol-toolbar button{padding:9px 11px;border-radius:12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.05);color:var(--shell-text);cursor:pointer}
.sol-comment{margin-top:14px;padding:16px 18px;border-left:2px solid rgba(122,180,255,.35);margin-left:calc(var(--comment-depth) * 18px);background:rgba(255,255,255,.03);border-radius:0 18px 18px 0}
.sol-comment-meta{display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:12px;color:var(--shell-muted);text-transform:uppercase;letter-spacing:.08em}
.sol-comment-body{margin-top:8px}
.sol-flash{padding:12px 14px;border-radius:14px}
.sol-flash.ok{background:rgba(23,182,166,.12);border:1px solid rgba(23,182,166,.35);color:#9fe9df}
.sol-flash.error{background:rgba(255,106,127,.12);border:1px solid rgba(255,106,127,.35);color:#ffb5c0}

@media (max-width: 1120px){
    .sol-hero,.sol-layout,.sol-grid,.sol-meta,.sol-auth-grid{grid-template-columns:1fr}
}
@media (max-width: 760px){
    .sol{padding:18px 14px 54px}
    .sol-hero{padding:22px;border-radius:28px}
    .sol-hero h1{max-width:none;font-size:clamp(2.55rem,13vw,4.2rem)}
}
</style>

<section class="sol">
    <div class="sol-shell">
        <header class="sol-hero">
            <div class="sol-hero-copy">
                <span class="sol-kicker"><span class="sol-icon">⬢</span><?= htmlspecialchars($t('Utility / ЦПАЛЬНЯ Lab', 'Utility / ЦПАЛЬНЯ Lab'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="neo-title"><?= htmlspecialchars($t('Раздел решений должен ощущаться как ', 'The solutions section should feel like ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('операторский каталог', 'an operator catalog'), ENT_QUOTES, 'UTF-8') ?></strong><?= htmlspecialchars($t(', а не как повтор блога', ', not a repeat of the blog'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($t('Здесь пользователь должен сразу считывать ценность, стек, формат и следующий шаг. Поэтому секция собирается как плотный utility-layer с явной иконографикой, ломаной геометрией карточек и быстрым маршрутом в внедрение или обсуждение.', 'Users should read the value, stack, format and next step immediately. That is why the section is built as a dense utility layer with clear iconography, broken card geometry and a fast route into deployment or discussion.'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="sol-tab-row">
                    <a class="sol-tab <?= $tab === 'downloads' ? 'is-active' : '' ?>" href="/solutions/downloads/"><span class="sol-icon">↓</span><?= htmlspecialchars($t('Скачиваемые assets', 'Downloadable assets'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="sol-tab <?= $tab === 'articles' ? 'is-active' : '' ?>" href="/solutions/articles/"><span class="sol-icon">▣</span><?= htmlspecialchars($t('Playbooks и статьи', 'Playbooks and articles'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>

            <aside class="sol-hero-side">
                <article class="sol-side-panel">
                    <span class="sol-type"><span class="sol-icon">↯</span><?= htmlspecialchars($t('Utility notes', 'Utility notes'), ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="sol-notes">
                        <?php foreach ($notes as $note): ?>
                            <span class="sol-note"><span class="sol-icon"><?= htmlspecialchars($note['icon'], ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($note['text'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                </article>
                <article class="sol-side-panel">
                    <span class="sol-type"><span class="sol-icon">◎</span><?= htmlspecialchars($t('Route', 'Route'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h3 class="neo-title"><?= htmlspecialchars($t('После выбора решения пользователь должен ', 'After choosing a solution the user should ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('продолжать движение', 'keep moving'), ENT_QUOTES, 'UTF-8') ?></strong></h3>
                    <p><?= htmlspecialchars($t('Сценарий не заканчивается на открытии карточки. Нужен маршрут в скачивание, обсуждение, сравнение и смежные материалы, чтобы utility-раздел работал как продуктовый контур.', 'The scenario should not end with opening a card. It needs a route into downloading, discussion, comparison and adjacent content so the utility section behaves like a product loop.'), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            </aside>
        </header>

        <?php if (!empty($portalFlash['message'])): ?>
            <div class="sol-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($selected): ?>
            <div class="sol-layout">
                <article class="sol-detail">
                    <span class="sol-type"><span class="sol-icon"><?= htmlspecialchars($selected['solution_type'] === 'article' ? '▣' : '↓', ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($selected['solution_type'] === 'article' ? $t('Playbook', 'Playbook') : $t('Download', 'Download'), ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 class="neo-title"><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
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
                        <a class="sol-btn" href="/solutions/<?= $tab === 'articles' ? 'articles' : 'downloads' ?>/"><span>↗</span><?= htmlspecialchars($t('Назад к списку', 'Back to list'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </article>

                <aside>
                    <article class="sol-board">
                        <div class="sol-board-head">
                            <div>
                                <span class="sol-type"><span class="sol-icon">◈</span><?= htmlspecialchars($t('Operator note', 'Operator note'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h2 class="neo-title"><?= htmlspecialchars($t('Детальная страница должна не только объяснять, но и ', 'A detail page should not just explain, but also ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('подталкивать к следующему шагу', 'push the next step'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                            </div>
                        </div>
                        <p><?= htmlspecialchars($t('Следующее действие здесь должно быть очевидным: скачать, адаптировать, обсудить или сравнить с related-материалом. Это и делает решение рабочим, а не просто красивым контентом.', 'The next action should be obvious here: download, adapt, discuss or compare with a related asset. That is what makes a solution truly operational instead of merely attractive content.'), ENT_QUOTES, 'UTF-8') ?></p>
                    </article>

                    <section class="sol-comments" id="comments">
                        <span class="sol-type"><span class="sol-icon">✦</span><?= htmlspecialchars($t('Knowledge layer', 'Knowledge layer'), ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="neo-title"><?= htmlspecialchars($t('Комментарии к решению должны жить как ', 'Comments on a solution should live as ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('слой практики и адаптаций', 'a layer of practice and adaptation'), ENT_QUOTES, 'UTF-8') ?></strong></h3>
                        <p><?= htmlspecialchars($t('Именно здесь проявляются реальные ошибки, нюансы команды и рабочие адаптации под разные сценарии. Поэтому discussion-layer остается заметным и встроенным прямо в detail-view.', 'This is where real mistakes, team-specific nuances and working adaptations show up. That is why the discussion layer stays prominent and is embedded directly inside the detail view.'), ENT_QUOTES, 'UTF-8') ?></p>
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
                                <button class="sol-link" type="submit"><span>↗</span><?= htmlspecialchars($t('Опубликовать', 'Publish'), ENT_QUOTES, 'UTF-8') ?></button>
                            </form>
                            <form method="post" style="margin-top:10px">
                                <input type="hidden" name="action" value="public_portal_logout">
                                <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                <button class="sol-btn" type="submit"><span>◌</span><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button>
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
                                    <button class="sol-link" type="submit"><span>✦</span><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button>
                                </form>
                                <form method="post">
                                    <input type="hidden" name="action" value="public_portal_login">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="email" name="email" placeholder="Email" required>
                                    <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <button class="sol-link" type="submit"><span>↗</span><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
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
                            <span class="sol-type"><span class="sol-icon"><?= htmlspecialchars($tab === 'articles' ? '▣' : '↓', ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($tab === 'articles' ? $t('Playbooks', 'Playbooks') : $t('Downloads', 'Downloads'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="neo-title"><?= htmlspecialchars($tab === 'articles' ? $t('Практические playbooks с длинным маршрутом в внедрение', 'Practical playbooks with a long route into implementation') : $t('Рабочие assets, которые можно забрать и внедрить сразу', 'Working assets that can be taken and deployed immediately'), ENT_QUOTES, 'UTF-8') ?></h2>
                        </div>
                    </div>
                    <div class="sol-grid">
                        <?php foreach ($items as $item): ?>
                            <article class="sol-card">
                                <span class="sol-type"><span class="sol-icon"><?= htmlspecialchars((($item['solution_type'] ?? '') === 'article') ? '▣' : '↓', ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars((string)($item['solution_type'] ?? 'download'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars(trim(strip_tags((string)($item['excerpt_html'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="sol-meta">
                                    <div><b><?= htmlspecialchars($t('Category', 'Category'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['category_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div><b><?= htmlspecialchars($t('Stack', 'Stack'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['stack_summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div><b><?= htmlspecialchars($t('Format', 'Format'), ENT_QUOTES, 'UTF-8') ?></b><span><?= htmlspecialchars((string)($item['file_format'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                </div>
                                <a class="sol-link" href="/solutions/<?= $tab === 'articles' ? 'articles' : 'downloads' ?>/<?= rawurlencode((string)($item['slug'] ?? '')) ?>/"><span>↗</span><?= htmlspecialchars($t('Открыть', 'Open'), ENT_QUOTES, 'UTF-8') ?></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <aside class="sol-board">
                    <div class="sol-board-head">
                        <div>
                            <span class="sol-type"><span class="sol-icon">◎</span><?= htmlspecialchars($t('Experience', 'Experience'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h2 class="neo-title"><?= htmlspecialchars($t('Экран должен ощущаться как ', 'The screen should feel like ') , ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($t('сканируемый рабочий каталог', 'a scannable working catalog'), ENT_QUOTES, 'UTF-8') ?></strong></h2>
                        </div>
                    </div>
                    <p><?= htmlspecialchars($t('Пользователь не читает этот экран подряд. Он сканирует ценность, сложность и формат, поэтому визуальная логика здесь жестче, плотнее и утилитарнее, чем в блоге.', 'Users do not read this screen linearly. They scan value, difficulty and format, so the visual logic here is tighter, denser and more utilitarian than in the blog.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="sol-notes">
                        <?php foreach ($notes as $note): ?>
                            <span class="sol-note"><span class="sol-icon"><?= htmlspecialchars($note['icon'], ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($note['text'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <a class="sol-link" href="/contact/"><span>✦</span><?= htmlspecialchars($t('Запросить кастомный asset', 'Request custom asset'), ENT_QUOTES, 'UTF-8') ?></a>
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
