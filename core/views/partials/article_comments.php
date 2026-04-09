<?php
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalFlash = (array)($ModelPage['portal_flash'] ?? []);
$portalCaptcha = (array)($ModelPage['portal_captcha'] ?? []);
$portalComments = (array)($ModelPage['portal_comments'] ?? []);
$portalCommentTotal = (int)($ModelPage['portal_comment_total'] ?? 0);
$portalContentType = trim((string)($ModelPage['portal_content_type'] ?? 'examples'));
$portalContentId = (int)($ModelPage['portal_content_id'] ?? 0);
$portalLang = isset($lang) ? (string)$lang : (((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? '') !== '' && preg_match('/\.ru$/', (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? ''))) ? 'ru' : 'en');
$portalIsRu = ($portalLang === 'ru');
$portalCsrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$portalSections = $portalIsRu
    ? ['discussion' => 'Обсуждение', 'question' => 'Вопрос', 'idea' => 'Идея', 'feedback' => 'Отзыв', 'case' => 'Практика']
    : ['discussion' => 'Discussion', 'question' => 'Question', 'idea' => 'Idea', 'feedback' => 'Feedback', 'case' => 'Practice'];
$portalCurrentUrl = (string)($_SERVER['REQUEST_URI'] ?? '/');
$t = static function (string $ru, string $en) use ($portalIsRu): string { return $portalIsRu ? $ru : $en; };
$portalCommentTree = static function (array $nodes, int $depth = 0) use (&$portalCommentTree, $portalUser, $portalSections, $portalCsrf, $portalCurrentUrl, $t): void {
    foreach ($nodes as $node) {
        $commentId = (int)($node['id'] ?? 0);
        $author = trim((string)($node['display_name'] ?? $node['username'] ?? 'Member'));
        $avatar = trim((string)($node['avatar_src'] ?? ''));
        $profileUrl = trim((string)($node['profile_url'] ?? '/account/'));
        $section = trim((string)($node['section_code'] ?? 'discussion'));
        $sectionLabel = (string)($portalSections[$section] ?? $section);
        $time = trim((string)($node['created_at'] ?? ''));
        $commentScore = (int)($node['rating_score'] ?? 0);
        $commentUp = (int)($node['votes_up'] ?? 0);
        $commentDown = (int)($node['votes_down'] ?? 0);
        $currentVote = (int)($node['current_user_vote'] ?? 0);
        $userScore = (int)($node['comment_rating'] ?? 0);
        $rankLabel = (string)($node['rank_meta']['label'] ?? $t('Участник обсуждения', 'Discussion member'));
        ?>
        <article class="pcmt-node" style="--pcmt-depth:<?= (int)$depth ?>">
            <div class="pcmt-node-line" aria-hidden="true"></div>
            <div class="pcmt-node-card">
                <div class="pcmt-node-head">
                    <div class="pcmt-node-author">
                        <span class="pcmt-avatar"><?php if ($avatar !== ''): ?><img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></span>
                        <div>
                            <strong><a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?></a></strong>
                            <div class="pcmt-node-meta">
                                <span><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= htmlspecialchars($rankLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= $userScore ?></span>
                                <time><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></time>
                            </div>
                        </div>
                    </div>
                    <div class="pcmt-node-actions">
                        <div class="pcmt-rating">
                            <span class="pcmt-rating-score"><?= $commentScore ?></span>
                            <span class="pcmt-rating-meta">+<?= $commentUp ?> / -<?= $commentDown ?></span>
                            <?php if ($portalUser): ?>
                                <form method="post" class="pcmt-vote-form">
                                    <input type="hidden" name="action" value="public_portal_comment_vote">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="comment_id" value="<?= $commentId ?>">
                                    <button type="submit" name="vote_value" value="1" class="<?= $currentVote > 0 ? 'is-active' : '' ?>">+</button>
                                    <button type="submit" name="vote_value" value="-1" class="<?= $currentVote < 0 ? 'is-active' : '' ?>">-</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php if ($portalUser): ?>
                            <button class="pcmt-reply" type="button" data-comment-reply="<?= $commentId ?>" data-comment-author="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Ответить', 'Reply'), ENT_QUOTES, 'UTF-8') ?></button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="pcmt-node-body"><?= (string)($node['body_html'] ?? '') ?></div>
            </div>
            <?php if (!empty($node['children'])): ?>
                <div class="pcmt-children"><?php $portalCommentTree((array)$node['children'], $depth + 1); ?></div>
            <?php endif; ?>
        </article>
        <?php
    }
};
?>
<style>
.pcmt{margin-top:28px;padding:28px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.pcmt-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px}
.pcmt-head h2{margin:0;font:700 clamp(1.6rem,2.6vw,2.4rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.pcmt-kicker,.pcmt-stat,.pcmt-node-meta span,.pcmt-node-meta time,.pcmt-section-select,.pcmt-auth-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.pcmt-copy{display:grid;gap:12px}
.pcmt-copy p{margin:0;color:var(--shell-muted);line-height:1.7}
.pcmt-summary{display:grid;gap:10px;margin-bottom:18px}
.pcmt-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.pcmt-summary-card{padding:14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-summary-card strong{display:block;margin-bottom:6px;font:700 1.2rem/1 "Space Grotesk","Sora",sans-serif}
.pcmt-summary-card span{color:var(--shell-muted);font-size:13px;line-height:1.45}
.pcmt-guest-cta{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.pcmt-btn,.pcmt-btn-ghost,.pcmt-toolbar button,.pcmt-reply,.pcmt-vote-form button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 16px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.05);color:var(--shell-text);text-decoration:none;cursor:pointer}
.pcmt-btn{background:linear-gradient(135deg,rgba(115,184,255,.24),rgba(39,223,192,.18));font-weight:700}
.pcmt-btn-ghost{background:rgba(255,255,255,.03)}
.pcmt-auth-shell{display:grid;gap:14px;margin-bottom:18px}
.pcmt-auth-tease{display:grid;gap:12px;padding:18px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-auth-form{display:none;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;padding:18px;border:1px solid rgba(122,180,255,.12);background:rgba(8,14,26,.74);opacity:0;transform:translateY(10px);transition:opacity .24s ease,transform .24s ease}
.pcmt-auth-shell.is-open .pcmt-auth-form{display:grid;opacity:1;transform:none}
.pcmt-auth-form .pcmt-field-full{grid-column:1 / -1}
.pcmt-auth-form input,.pcmt-auth-form textarea,.pcmt-form input,.pcmt-form textarea,.pcmt-form select{width:100%;padding:13px 14px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.58);color:var(--shell-text)}
.pcmt-captcha{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px;border:1px solid rgba(122,180,255,.12);background:linear-gradient(135deg,rgba(115,184,255,.08),rgba(39,223,192,.06))}
.pcmt-captcha-code{display:flex;align-items:center;gap:10px;font:700 1.15rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:.04em}
.pcmt-captcha-code span{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:42px;padding:0 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04)}
.pcmt-form{display:grid;gap:12px}
.pcmt-form-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.pcmt-author-chip{display:inline-flex;align-items:center;gap:10px}
.pcmt-author-chip .pcmt-avatar{width:42px;height:42px}
.pcmt-toolbar{display:flex;flex-wrap:wrap;gap:8px}
.pcmt-toolbar button{min-height:38px;padding:0 12px}
.pcmt-form textarea{min-height:160px;resize:vertical}
.pcmt-form-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.pcmt-reply-state{display:none;align-items:center;gap:10px;padding:10px 12px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-reply-state.is-visible{display:flex}
.pcmt-list{display:grid;gap:14px}
.pcmt-node{position:relative;padding-left:24px}
.pcmt-node-line{position:absolute;left:9px;top:0;bottom:-14px;width:1px;background:linear-gradient(180deg,rgba(122,180,255,.34),rgba(122,180,255,.06))}
.pcmt-node::before{content:"";position:absolute;left:9px;top:28px;width:16px;height:1px;background:rgba(122,180,255,.34)}
.pcmt-node-card{padding:16px 18px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-node-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.pcmt-node-author{display:flex;align-items:flex-start;gap:12px}
.pcmt-avatar{display:inline-flex;align-items:center;justify-content:center;overflow:hidden;width:46px;height:46px;border:1px solid rgba(122,180,255,.14);background:rgba(255,255,255,.04)}
.pcmt-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.pcmt-node-author strong{display:block;font-size:15px}
.pcmt-node-author a{color:var(--shell-text);text-decoration:none}
.pcmt-node-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:6px}
.pcmt-node-body{margin-top:12px;color:var(--shell-text);line-height:1.75}
.pcmt-node-body p{margin:0 0 12px}
.pcmt-node-body p:last-child{margin-bottom:0}
.pcmt-node-body ul{margin:12px 0 0;padding-left:18px}
.pcmt-node-body a{color:var(--shell-accent)}
.pcmt-children{display:grid;gap:12px;margin-top:12px;margin-left:24px}
.pcmt-node-actions{display:grid;justify-items:end;gap:10px}
.pcmt-rating{display:grid;gap:6px;justify-items:end}
.pcmt-rating-score{font:700 1.2rem/1 "Space Grotesk","Sora",sans-serif}
.pcmt-rating-meta{font-size:12px;color:var(--shell-muted)}
.pcmt-vote-form{display:flex;gap:6px}
.pcmt-vote-form button{min-height:34px;min-width:34px;padding:0 10px}
.pcmt-vote-form button.is-active{background:rgba(39,223,192,.18);border-color:rgba(39,223,192,.32)}
.pcmt-empty{padding:18px;border:1px dashed rgba(122,180,255,.16);color:var(--shell-muted);background:rgba(255,255,255,.02)}
.pcmt-flash{margin-bottom:14px;padding:12px 14px;border:1px solid rgba(122,180,255,.16)}
.pcmt-flash.ok{background:rgba(39,223,192,.10);color:#9fe9df}
.pcmt-flash.error{background:rgba(255,106,127,.10);color:#ffc0cb}
@media (max-width:980px){.pcmt-summary-grid,.pcmt-auth-form{grid-template-columns:1fr}.pcmt-auth-form .pcmt-field-full{grid-column:auto}}
@media (max-width:720px){.pcmt{padding:20px 16px}.pcmt-form-foot,.pcmt-head,.pcmt-form-top,.pcmt-node-head{align-items:flex-start}.pcmt-children{margin-left:14px}.pcmt-node-actions{justify-items:start}}
</style>
<section class="pcmt" id="article-comments">
    <div class="pcmt-head">
        <div class="pcmt-copy">
            <span class="pcmt-kicker"><?= htmlspecialchars($t('Комментарии / discussion layer', 'Comments / discussion layer'), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($t('Разбор под статьей продолжается в комментариях', 'The discussion continues under the article'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t('Здесь видны вопросы, практические ответы, уточнения по связкам и тихие нюансы, которые редко попадают в сам текст статьи. Теперь у каждого комментария есть рейтинг, а у каждого автора — общий уровень и звание.', 'Questions, practical additions and implementation nuance live here. Each comment now has a rating, and each author has a cumulative score and title.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <span class="pcmt-stat"><?= (int)$portalCommentTotal ?> <?= htmlspecialchars($t('комм.', 'comments'), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <?php if (!empty($portalFlash['message'])): ?>
        <div class="pcmt-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$portalUser): ?>
        <div class="pcmt-summary">
            <div class="pcmt-summary-grid">
                <div class="pcmt-summary-card"><strong><?= (int)$portalCommentTotal ?></strong><span><?= htmlspecialchars($t('видимых комментариев у материала', 'visible comments on this article'), ENT_QUOTES, 'UTF-8') ?></span></div>
                <div class="pcmt-summary-card"><strong><?= htmlspecialchars($t('Рейтинг', 'Rating'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars($t('голоса поднимают комментарии и суммируются в общий уровень автора', 'votes change comment scores and accumulate into the author rating'), ENT_QUOTES, 'UTF-8') ?></span></div>
                <div class="pcmt-summary-card"><strong><?= htmlspecialchars($t('Профили', 'Profiles'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars($t('по имени автора можно открыть его публичный профиль и статистику', 'author names open public profiles and stats'), ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
            <div class="pcmt-auth-shell" id="pcmt-auth-shell">
                <div class="pcmt-auth-tease">
                    <p><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Комментарии открыты для чтения. Чтобы ответить, поставить оценку или оставить свою идею, нужен быстрый мягкий вход.', 'Comments are public to read. To reply, vote or leave your own note, use the light registration flow.') : $t('Комментариев пока нет. Можно оставить первый и сразу открыть обсуждение.', 'No comments yet. You can leave the first one and start the thread.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="pcmt-guest-cta">
                        <button class="pcmt-btn" type="button" data-comment-auth-open><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Войти и комментировать', 'Sign in to comment') : $t('Оставить первый комментарий', 'Leave the first comment'), ENT_QUOTES, 'UTF-8') ?></button>
                        <a class="pcmt-btn-ghost" href="/account/"><?= htmlspecialchars($t('Открыть кабинет', 'Open account area'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </div>
                <form class="pcmt-auth-form" method="post">
                    <input type="hidden" name="action" value="public_portal_register">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <div><span class="pcmt-auth-kicker"><?= htmlspecialchars($t('Быстрая регистрация', 'Quick registration'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div><input type="text" name="username" placeholder="<?= htmlspecialchars($t('Логин', 'Login'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full"><input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full pcmt-captcha">
                        <div><strong><?= htmlspecialchars((string)($portalCaptcha[$portalIsRu ? 'prompt_ru' : 'prompt_en'] ?? $t('Сложите два числа рядом со знаками', 'Add the two numbers next to the symbols')), ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="pcmt-captcha-code">
                            <span><?= htmlspecialchars((string)($portalCaptcha['glyph_left'] ?? '◧'), ENT_QUOTES, 'UTF-8') ?><?= (int)($portalCaptcha['left'] ?? 0) ?></span>
                            <span>+</span>
                            <span><?= htmlspecialchars((string)($portalCaptcha['glyph_right'] ?? '◩'), ENT_QUOTES, 'UTF-8') ?><?= (int)($portalCaptcha['right'] ?? 0) ?></span>
                        </div>
                        <input type="text" name="captcha_answer" placeholder="<?= htmlspecialchars($t('Ответ', 'Answer'), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="pcmt-field-full pcmt-guest-cta">
                        <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('Создать аккаунт и войти', 'Create account and sign in'), ENT_QUOTES, 'UTF-8') ?></button>
                        <a class="pcmt-btn-ghost" href="/account/"><?= htmlspecialchars($t('Полная форма в кабинете', 'Full form in account area'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <form class="pcmt-form" method="post" id="pcmt-comment-form">
            <input type="hidden" name="action" value="public_portal_comment">
            <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
            <input type="hidden" name="parent_id" value="0" id="pcmt-parent-id">
            <div class="pcmt-form-top">
                <div class="pcmt-author-chip">
                    <span class="pcmt-avatar"><img src="<?= htmlspecialchars(function_exists('public_portal_user_avatar') ? public_portal_user_avatar($portalUser) : '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($portalUser['display_name'] ?? $portalUser['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?>"></span>
                    <div>
                        <strong><a href="<?= htmlspecialchars(function_exists('public_portal_profile_url') ? public_portal_profile_url($portalUser) : '/account/', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($portalUser['display_name'] ?? $portalUser['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?></a></strong>
                        <div class="pcmt-node-meta">
                            <span><?= htmlspecialchars((string)((function_exists('public_portal_rank_meta') ? public_portal_rank_meta((int)($portalUser['comment_rating'] ?? 0), $portalLang) : ['label' => $t('Участник обсуждения', 'Discussion member')])['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span><?= (int)($portalUser['comment_rating'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                <select name="section_code" class="pcmt-section-select">
                    <?php foreach ($portalSections as $sectionCode => $sectionLabel): ?>
                        <option value="<?= htmlspecialchars((string)$sectionCode, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$sectionLabel, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pcmt-toolbar">
                <button type="button" data-wrap="**" data-target="pcmt-text">B</button>
                <button type="button" data-wrap="*" data-target="pcmt-text">I</button>
                <button type="button" data-wrap="~~" data-target="pcmt-text">S</button>
                <button type="button" data-link-target="pcmt-text"><?= htmlspecialchars($t('Ссылка', 'Link'), ENT_QUOTES, 'UTF-8') ?></button>
                <button type="button" data-prefix="🙂 " data-target="pcmt-text">🙂</button>
                <button type="button" data-prefix="🔥 " data-target="pcmt-text">🔥</button>
                <button type="button" data-prefix="🧠 " data-target="pcmt-text">🧠</button>
                <button type="button" data-prefix="⚙️ " data-target="pcmt-text">⚙️</button>
            </div>
            <div class="pcmt-reply-state" id="pcmt-reply-state">
                <span id="pcmt-reply-text"></span>
                <button class="pcmt-btn-ghost" type="button" data-comment-reply-clear><?= htmlspecialchars($t('Снять ответ', 'Clear reply'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
            <textarea id="pcmt-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Комментарий, вопрос, дополнение по практике или тихий backstage-нюанс', 'Comment, question or practical add-on'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
            <div class="pcmt-form-foot">
                <span class="pcmt-node-meta"><span><?= htmlspecialchars($t('Разрешены: жирный, курсив, перечеркнутый, ссылка, эмодзи', 'Supports bold, italic, strike, links and emoji'), ENT_QUOTES, 'UTF-8') ?></span></span>
                <div class="pcmt-guest-cta">
                    <a class="pcmt-btn-ghost" href="/account/"><?= htmlspecialchars($t('Личный кабинет', 'Account'), ENT_QUOTES, 'UTF-8') ?></a>
                    <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('Опубликовать комментарий', 'Publish comment'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($portalComments)): ?>
        <div class="pcmt-list"><?php $portalCommentTree($portalComments); ?></div>
    <?php else: ?>
        <div class="pcmt-empty"><?= htmlspecialchars($t('Под этой статьей пока нет комментариев. Можно открыть обсуждение первым.', 'No comments under this article yet. You can open the thread first.'), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
</section>
<script>
document.addEventListener('click', function (event) {
    var authOpen = event.target.closest('[data-comment-auth-open]');
    if (authOpen) {
        var authShell = document.getElementById('pcmt-auth-shell');
        if (authShell) {
            authShell.classList.add('is-open');
        }
        return;
    }

    var wrapButton = event.target.closest('[data-wrap][data-target]');
    if (wrapButton) {
        var target = document.getElementById(wrapButton.getAttribute('data-target'));
        if (!target) { return; }
        var wrap = wrapButton.getAttribute('data-wrap') || '';
        var start = target.selectionStart || 0;
        var end = target.selectionEnd || 0;
        var value = target.value || '';
        var selected = value.substring(start, end);
        target.value = value.substring(0, start) + wrap + selected + wrap + value.substring(end);
        target.focus();
        target.selectionStart = start + wrap.length;
        target.selectionEnd = end + wrap.length;
        return;
    }

    var prefixButton = event.target.closest('[data-prefix][data-target]');
    if (prefixButton) {
        var prefixTarget = document.getElementById(prefixButton.getAttribute('data-target'));
        if (!prefixTarget) { return; }
        var prefix = prefixButton.getAttribute('data-prefix') || '';
        var prefixStart = prefixTarget.selectionStart || 0;
        var prefixEnd = prefixTarget.selectionEnd || 0;
        var prefixValue = prefixTarget.value || '';
        prefixTarget.value = prefixValue.substring(0, prefixStart) + prefix + prefixValue.substring(prefixStart, prefixEnd) + prefixValue.substring(prefixEnd);
        prefixTarget.focus();
        prefixTarget.selectionStart = prefixStart + prefix.length;
        prefixTarget.selectionEnd = prefixEnd + prefix.length;
        return;
    }

    var linkButton = event.target.closest('[data-link-target]');
    if (linkButton) {
        var linkTarget = document.getElementById(linkButton.getAttribute('data-link-target'));
        if (!linkTarget) { return; }
        var url = window.prompt('https://');
        if (!url) { return; }
        var text = window.prompt('Текст ссылки') || url;
        var linkInsert = '[' + text + '](' + url + ')';
        var linkStart = linkTarget.selectionStart || 0;
        var linkEnd = linkTarget.selectionEnd || 0;
        var linkValue = linkTarget.value || '';
        linkTarget.value = linkValue.substring(0, linkStart) + linkInsert + linkValue.substring(linkEnd);
        linkTarget.focus();
        return;
    }

    var replyButton = event.target.closest('[data-comment-reply]');
    if (replyButton) {
        var parentInput = document.getElementById('pcmt-parent-id');
        var replyState = document.getElementById('pcmt-reply-state');
        var replyText = document.getElementById('pcmt-reply-text');
        var textarea = document.getElementById('pcmt-text');
        if (parentInput && replyState && replyText && textarea) {
            parentInput.value = replyButton.getAttribute('data-comment-reply') || '0';
            replyText.textContent = '↳ ' + (replyButton.getAttribute('data-comment-author') || '');
            replyState.classList.add('is-visible');
            textarea.focus();
        }
        return;
    }

    var clearReply = event.target.closest('[data-comment-reply-clear]');
    if (clearReply) {
        var clearParent = document.getElementById('pcmt-parent-id');
        var clearState = document.getElementById('pcmt-reply-state');
        var clearText = document.getElementById('pcmt-reply-text');
        if (clearParent) { clearParent.value = '0'; }
        if (clearState) { clearState.classList.remove('is-visible'); }
        if (clearText) { clearText.textContent = ''; }
    }
});
</script>
