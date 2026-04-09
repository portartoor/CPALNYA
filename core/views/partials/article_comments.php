<?php
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalFlash = (array)($ModelPage['portal_flash'] ?? []);
$portalCaptcha = (array)($ModelPage['portal_captcha'] ?? []);
$portalComments = (array)($ModelPage['portal_comments'] ?? []);
$portalCommentTotal = (int)($ModelPage['portal_comment_total'] ?? 0);
$portalContentType = trim((string)($ModelPage['portal_content_type'] ?? 'examples'));
$portalContentId = (int)($ModelPage['portal_content_id'] ?? 0);
$portalLang = isset($lang)
    ? (string)$lang
    : (((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? '') !== '' && preg_match('/\.ru$/', (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? ''))) ? 'ru' : 'en');
$portalIsRu = ($portalLang === 'ru');
$portalCsrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$portalSections = $portalIsRu
    ? ['discussion' => 'Обсуждение', 'question' => 'Вопрос', 'idea' => 'Идея', 'feedback' => 'Отзыв', 'case' => 'Практика']
    : ['discussion' => 'Discussion', 'question' => 'Question', 'idea' => 'Idea', 'feedback' => 'Feedback', 'case' => 'Practice'];
$portalCurrentUrl = (string)($_SERVER['REQUEST_URI'] ?? '/');
$t = static function (string $ru, string $en) use ($portalIsRu): string {
    return $portalIsRu ? $ru : $en;
};
$portalCommentScoreTotal = 0;
$portalCollectScore = static function (array $nodes) use (&$portalCollectScore, &$portalCommentScoreTotal): void {
    foreach ($nodes as $node) {
        $portalCommentScoreTotal += (int)($node['rating_score'] ?? 0);
        if (!empty($node['children']) && is_array($node['children'])) {
            $portalCollectScore((array)$node['children']);
        }
    }
};
$portalCollectScore($portalComments);
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
        <article class="pcmt-node" id="comment-<?= $commentId ?>" style="--pcmt-depth:<?= (int)$depth ?>">
            <div class="pcmt-node-line" aria-hidden="true"></div>
            <div class="pcmt-node-card">
                <div class="pcmt-node-head">
                    <div class="pcmt-node-author">
                        <a class="pcmt-avatar" href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>"><?php if ($avatar !== ''): ?><img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></a>
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
                        <a class="pcmt-anchor" href="#comment-<?= $commentId ?>">#<?= $commentId ?></a>
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
.pcmt{position:relative;margin-top:28px;padding:28px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow);transition:opacity .24s ease,transform .24s ease}
.pcmt.is-loading{opacity:.72;pointer-events:none}
.pcmt.is-loading::after{content:"";position:absolute;inset:0;border:1px solid rgba(122,180,255,.12);background:linear-gradient(90deg,rgba(255,255,255,0),rgba(122,180,255,.12),rgba(255,255,255,0));animation:pcmtLoad 1.1s linear infinite}
.pcmt-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px}
.pcmt-head h2{margin:0;font:700 clamp(1.6rem,2.6vw,2.4rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.pcmt-kicker,.pcmt-node-meta span,.pcmt-node-meta time,.pcmt-section-select,.pcmt-auth-kicker,.pcmt-anchor{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.pcmt-copy{display:grid;gap:12px}
.pcmt-copy p{margin:0;color:var(--shell-muted);line-height:1.72}
.pcmt-summary{display:grid;gap:10px;margin-bottom:18px}
.pcmt-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.pcmt-summary-card{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-summary-icon{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:1px solid rgba(122,180,255,.14);background:rgba(255,255,255,.04);font-size:16px;line-height:1}
.pcmt-summary-number{display:block;font:700 1.35rem/1 "Space Grotesk","Sora",sans-serif;color:var(--shell-text)}
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
.pcmt-node{position:relative;padding-left:24px;scroll-margin-top:120px}
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
.pcmt-anchor{color:var(--shell-muted);text-decoration:none}
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
@keyframes pcmtLoad{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
@media (max-width:980px){.pcmt-summary-grid,.pcmt-auth-form{grid-template-columns:1fr}.pcmt-auth-form .pcmt-field-full{grid-column:auto}}
@media (max-width:720px){.pcmt{padding:20px 16px}.pcmt-form-foot,.pcmt-head,.pcmt-form-top,.pcmt-node-head{align-items:flex-start}.pcmt-children{margin-left:14px}.pcmt-node-actions{justify-items:start}}
</style>
<section class="pcmt" id="article-comments">
    <div class="pcmt-head">
        <div class="pcmt-copy">
            <span class="pcmt-kicker"><?= htmlspecialchars($t('Редакционное обсуждение', 'Editorial discussion'), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($t('Под текстом начинается живая часть разговора', 'The live part of the conversation starts below'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t('Здесь обычно появляются наблюдения, встречные истории, несогласия, тихие уточнения и те детали, ради которых материал хочется перечитать уже вместе с другими. Если есть свой опыт, вопрос или аккуратное возражение — это как раз то место.', 'This is where lived detail, disagreements, useful follow-ups and the human part of the story usually show up. If you have experience, a question or a thoughtful counterpoint, this is the right place for it.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <?php if (!empty($portalFlash['message'])): ?>
        <div class="pcmt-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$portalUser): ?>
        <div class="pcmt-summary">
            <div class="pcmt-summary-grid">
                <div class="pcmt-summary-card">
                    <span class="pcmt-summary-icon" aria-hidden="true">◉</span>
                    <span class="pcmt-summary-number"><?= (int)$portalCommentTotal ?></span>
                </div>
                <div class="pcmt-summary-card">
                    <span class="pcmt-summary-icon" aria-hidden="true">↕</span>
                    <span class="pcmt-summary-number"><?= (int)$portalCommentScoreTotal ?></span>
                </div>
            </div>
            <div class="pcmt-auth-shell" id="pcmt-auth-shell">
                <div class="pcmt-auth-tease">
                    <p><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Войдите, чтобы ответить, поддержать чужую мысль или принести в разговор свой опыт.', 'Sign in to reply, support a point or bring your own experience into the thread.') : $t('Войдите, чтобы оставить первую реплику и открыть это обсуждение.', 'Sign in to leave the first note and open this discussion.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="pcmt-guest-cta">
                        <button class="pcmt-btn" type="button" data-comment-auth-open><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Войти и обсудить', 'Join the discussion') : $t('Оставить первый комментарий', 'Leave the first comment'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </div>
                <form class="pcmt-auth-form" method="post">
                    <input type="hidden" name="action" value="public_portal_register">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
                    <div><span class="pcmt-auth-kicker"><?= htmlspecialchars($t('Быстрый вход в обсуждение', 'Quick access to discussion'), ENT_QUOTES, 'UTF-8') ?></span></div>
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
                        <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('Создать аккаунт и продолжить', 'Create account and continue'), ENT_QUOTES, 'UTF-8') ?></button>
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
            <textarea id="pcmt-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Поделитесь своим наблюдением, вопросом, несогласием или практической историей по теме материала', 'Share an observation, question, disagreement or practical story related to the article'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
            <div class="pcmt-form-foot">
                <span class="pcmt-node-meta"><span><?= htmlspecialchars($t('Разрешены: жирный, курсив, перечеркнутый, ссылка, эмодзи', 'Supports bold, italic, strike, links and emoji'), ENT_QUOTES, 'UTF-8') ?></span></span>
                <div class="pcmt-guest-cta">
                    <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('Опубликовать комментарий', 'Publish comment'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($portalComments)): ?>
        <div class="pcmt-list"><?php $portalCommentTree($portalComments); ?></div>
    <?php else: ?>
        <div class="pcmt-empty"><?= htmlspecialchars($t('Под этим материалом пока тихо. Можно оставить первую реплику и открыть обсуждение.', 'It is still quiet under this article. You can leave the first note and open the thread.'), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
</section>
<script>
(function () {
    function bindPortalComments(root) {
        if (!root || root.dataset.bound === '1') {
            return;
        }
        root.dataset.bound = '1';

        function withLoading(active) {
            root.classList.toggle('is-loading', !!active);
        }

        function replaceCommentsHtml(html) {
            if (!html) {
                return;
            }
            var holder = document.createElement('div');
            holder.innerHTML = html;
            var nextRoot = holder.firstElementChild;
            if (!nextRoot) {
                return;
            }
            root.replaceWith(nextRoot);
            bindPortalComments(nextRoot);
        }

        function openAuth() {
            var authShell = root.querySelector('#pcmt-auth-shell');
            if (authShell) {
                authShell.classList.add('is-open');
            }
        }

        function clearReplyState() {
            var parentInput = root.querySelector('#pcmt-parent-id');
            var replyState = root.querySelector('#pcmt-reply-state');
            var replyText = root.querySelector('#pcmt-reply-text');
            if (parentInput) {
                parentInput.value = '0';
            }
            if (replyState) {
                replyState.classList.remove('is-visible');
            }
            if (replyText) {
                replyText.textContent = '';
            }
        }

        root.addEventListener('click', function (event) {
            var authOpen = event.target.closest('[data-comment-auth-open]');
            if (authOpen) {
                openAuth();
                return;
            }

            var wrapButton = event.target.closest('[data-wrap][data-target]');
            if (wrapButton) {
                var target = root.querySelector('#' + wrapButton.getAttribute('data-target'));
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
                var prefixTarget = root.querySelector('#' + prefixButton.getAttribute('data-target'));
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
                var linkTarget = root.querySelector('#' + linkButton.getAttribute('data-link-target'));
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
                var parentInput = root.querySelector('#pcmt-parent-id');
                var replyState = root.querySelector('#pcmt-reply-state');
                var replyText = root.querySelector('#pcmt-reply-text');
                var textarea = root.querySelector('#pcmt-text');
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
                clearReplyState();
            }
        });

        root.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            var actionInput = form.querySelector('input[name="action"]');
            var action = actionInput ? actionInput.value : '';
            if (['public_portal_register', 'public_portal_comment', 'public_portal_comment_vote'].indexOf(action) === -1) {
                return;
            }

            event.preventDefault();
            withLoading(true);

            var formData = new FormData(form);
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                if (!payload || typeof payload !== 'object') {
                    window.location.reload();
                    return;
                }
                if (payload.html) {
                    replaceCommentsHtml(payload.html);
                } else {
                    withLoading(false);
                }
                if (payload.comment_anchor) {
                    if (history && history.replaceState) {
                        history.replaceState(null, '', payload.comment_anchor);
                    }
                    window.setTimeout(function () {
                        var target = document.querySelector(payload.comment_anchor);
                        if (target) {
                            target.scrollIntoView({behavior: 'smooth', block: 'start'});
                        }
                    }, 60);
                }
                if (payload.pin_code) {
                    window.setTimeout(function () {
                        window.alert('PIN: ' + payload.pin_code);
                    }, 80);
                }
            }).catch(function () {
                window.location.reload();
            });
        });
    }

    bindPortalComments(document.getElementById('article-comments'));
})();
</script>
