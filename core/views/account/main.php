<?php
$account = (array)($ModelPage['account'] ?? []);
$lang = (string)($account['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$user = is_array($account['user'] ?? null) ? $account['user'] : null;
$flash = (array)($account['flash'] ?? []);
$captcha = (array)($account['captcha'] ?? []);
$publicProfile = is_array($account['public_profile'] ?? null) ? $account['public_profile'] : null;
$ownRank = is_array($account['own_rank'] ?? null) ? $account['own_rank'] : null;
$csrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$avatar = $user && function_exists('public_portal_user_avatar') ? public_portal_user_avatar($user) : '';
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
?>
<style>
.pacc{max-width:1120px;margin:0 auto;padding:30px 18px 64px;color:var(--shell-text)}
.pacc-shell{display:grid;gap:20px}
.pacc-hero,.pacc-card,.pacc-auth{padding:26px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.pacc-hero h1,.pacc-card h2,.pacc-auth h2{margin:0 0 12px;font:700 clamp(2rem,4vw,3.4rem)/.94 "Space Grotesk","Sora",sans-serif;letter-spacing:-.06em}
.pacc-hero p,.pacc-card p{margin:0;color:var(--shell-muted);line-height:1.75}
.pacc-flash{padding:12px 14px;border:1px solid rgba(122,180,255,.16)}
.pacc-flash.ok{background:rgba(39,223,192,.10);color:#9fe9df}
.pacc-flash.error{background:rgba(255,106,127,.10);color:#ffc0cb}
.pacc-grid{display:grid;grid-template-columns:minmax(280px,.86fr) minmax(0,1.14fr);gap:20px}
.pacc-userhead{display:grid;gap:14px}
.pacc-avatar{width:96px;height:96px;overflow:hidden;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04)}
.pacc-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.pacc-meta{display:grid;gap:10px}
.pacc-meta-row{padding:12px 14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pacc-meta-row strong{display:block;margin-bottom:5px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--shell-accent)}
.pacc-rank{display:grid;gap:10px;margin-bottom:16px}
.pacc-rank-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid rgba(122,180,255,.14);background:rgba(255,255,255,.03);font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.pacc-rank-note{color:var(--shell-muted);font-size:14px;line-height:1.6}
.pacc-form{display:grid;gap:12px}
.pacc-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.pacc-form input{width:100%;padding:13px 14px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.58);color:var(--shell-text)}
.pacc-form .pacc-field-full{grid-column:1 / -1}
.pacc-actions{display:flex;flex-wrap:wrap;gap:10px}
.pacc-btn,.pacc-btn-ghost{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 16px;border:1px solid rgba(122,180,255,.16);text-decoration:none;color:var(--shell-text);background:rgba(255,255,255,.03)}
.pacc-btn{background:linear-gradient(135deg,rgba(115,184,255,.24),rgba(39,223,192,.18));font-weight:700}
.pacc-captcha{display:grid;gap:8px;padding:14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pacc-captcha-row{display:flex;align-items:center;gap:10px;font:700 1.1rem/1 "Space Grotesk","Sora",sans-serif}
.pacc-captcha-row span{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:42px;padding:0 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04)}
.pacc-recent{display:grid;gap:12px}
.pacc-recent-item{padding:16px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pacc-recent-item a{color:var(--shell-accent);text-decoration:none}
.pacc-mini{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;color:var(--shell-muted);font-size:13px}
@media (max-width: 920px){.pacc-grid,.pacc-form-grid{grid-template-columns:1fr}.pacc-form .pacc-field-full{grid-column:auto}}
</style>

<section class="pacc">
    <div class="pacc-shell">
        <header class="pacc-hero">
            <h1><?= htmlspecialchars($t('Личный кабинет читателя и автора комментариев', 'Reader account and comments profile'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($t('Здесь живет ваш публичный профиль: вход, регистрация, аватарка, контакты, рейтинг комментариев и PIN-код для смены пароля. По ссылке на профиль можно посмотреть статистику и недавние реплики любого комментатора.', 'This is your public profile: sign in, registration, avatar, contacts, comment rating and PIN-based password reset. Public profile links also open commentator stats and recent replies.'), ENT_QUOTES, 'UTF-8') ?></p>
        </header>

        <?php if (!empty($flash['message'])): ?>
            <div class="pacc-flash <?= htmlspecialchars((string)($flash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($publicProfile): ?>
            <section class="pacc-card">
                <div class="pacc-grid">
                    <aside class="pacc-card">
                        <div class="pacc-userhead">
                            <span class="pacc-avatar"><img src="<?= htmlspecialchars((string)($publicProfile['avatar_src'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($publicProfile['display_name'] ?? $publicProfile['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?>"></span>
                            <div class="pacc-meta">
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Профиль', 'Profile'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string)($publicProfile['display_name'] ?? $publicProfile['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Логин', 'Login'), ENT_QUOTES, 'UTF-8') ?></strong><span>@<?= htmlspecialchars((string)($publicProfile['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Рейтинг', 'Rating'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($publicProfile['comment_rating'] ?? 0) ?></span></div>
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Комментарии', 'Comments'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($publicProfile['comments_count'] ?? 0) ?></span></div>
                            </div>
                        </div>
                    </aside>
                    <section class="pacc-card">
                        <h2><?= htmlspecialchars($t('Публичный профиль комментатора', 'Public commenter profile'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <div class="pacc-rank">
                            <span class="pacc-rank-badge"><?= htmlspecialchars((string)($publicProfile['rank_meta']['label'] ?? $t('Участник обсуждения', 'Discussion member')), ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="pacc-rank-note"><?= htmlspecialchars($t('Репутация складывается из голосов за комментарии. Чем выше общий рейтинг, тем выше звание автора в обсуждениях.', 'Reputation is built from votes on comments. The higher the total score, the higher the commentator title.'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="pacc-mini">
                                <?php if (!empty($publicProfile['telegram_handle'])): ?><span>Telegram: <?= htmlspecialchars((string)$publicProfile['telegram_handle'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                                <?php if (!empty($publicProfile['website_url'])): ?><span><a href="<?= htmlspecialchars((string)$publicProfile['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="nofollow noopener"><?= htmlspecialchars($t('Сайт', 'Website'), ENT_QUOTES, 'UTF-8') ?></a></span><?php endif; ?>
                                <span>+<?= (int)($publicProfile['comment_votes_up'] ?? 0) ?> / -<?= (int)($publicProfile['comment_votes_down'] ?? 0) ?></span>
                            </div>
                        </div>
                    </section>
                </div>
            </section>

            <section class="pacc-card">
                <h2><?= htmlspecialchars($t('Последние комментарии', 'Recent comments'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="pacc-recent">
                    <?php foreach ((array)($publicProfile['recent_comments'] ?? []) as $comment): ?>
                        <article class="pacc-recent-item">
                            <strong><a href="<?= htmlspecialchars((string)($comment['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($comment['article_title'] ?? $t('Материал', 'Article')), ENT_QUOTES, 'UTF-8') ?></a></strong>
                            <div class="pacc-mini"><span><?= htmlspecialchars((string)($comment['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><span><?= (int)($comment['rating_score'] ?? 0) ?></span></div>
                            <div><?= (string)($comment['body_html'] ?? '') ?></div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($publicProfile['recent_comments'])): ?>
                        <p><?= htmlspecialchars($t('У этого автора пока нет открытых комментариев.', 'This profile has no public comments yet.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="pacc-grid">
                <aside class="pacc-card">
                    <div class="pacc-userhead">
                        <span class="pacc-avatar"><?php if ($avatar !== ''): ?><img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($user['display_name'] ?? $user['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></span>
                        <div class="pacc-meta">
                            <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Логин', 'Login'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string)($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Никнейм', 'Nickname'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string)($user['display_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="pacc-meta-row"><strong>PIN</strong><span><?= htmlspecialchars((string)($user['pin_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Рейтинг', 'Rating'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($user['comment_rating'] ?? 0) ?></span></div>
                        </div>
                    </div>
                </aside>
                <section class="pacc-card">
                    <h2><?= htmlspecialchars($t('Профиль', 'Profile'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <?php if ($ownRank): ?>
                        <div class="pacc-rank">
                            <span class="pacc-rank-badge"><?= htmlspecialchars((string)($ownRank['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="pacc-rank-note">
                                <?= htmlspecialchars($t('Это краткая сводка по вашему комментаторскому рейтингу. Все плюсы и минусы под комментариями суммируются в общий уровень.', 'This is the short summary of your comment reputation. Upvotes and downvotes across all comments accumulate into a single level.'), ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($ownRank['next']['label'])): ?>
                                    <?= ' ' . htmlspecialchars($t('До следующего звания осталось', 'Until the next title'), ENT_QUOTES, 'UTF-8') . ' ' . (int)($ownRank['to_next'] ?? 0) . '.' ?>
                                <?php endif; ?>
                            </div>
                            <div class="pacc-mini">
                                <span>+<?= (int)($user['comment_votes_up'] ?? 0) ?> / -<?= (int)($user['comment_votes_down'] ?? 0) ?></span>
                                <span><?= (int)($user['comments_count'] ?? 0) ?> <?= htmlspecialchars($t('комментариев', 'comments'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><a href="<?= htmlspecialchars(function_exists('public_portal_profile_url') ? public_portal_profile_url($user) : '/account/', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Открыть публичный профиль', 'Open public profile'), ENT_QUOTES, 'UTF-8') ?></a></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <form class="pacc-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="public_portal_profile_update">
                        <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="return_path" value="/account/">
                        <div class="pacc-form-grid">
                            <input type="text" name="display_name" value="<?= htmlspecialchars((string)($user['display_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t('Никнейм', 'Nickname'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="email" name="email" value="<?= htmlspecialchars((string)($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Email">
                            <input type="text" name="telegram_handle" value="<?= htmlspecialchars((string)($user['telegram_handle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Telegram">
                            <input type="url" name="website_url" value="<?= htmlspecialchars((string)($user['website_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t('Сайт', 'Website'), ENT_QUOTES, 'UTF-8') ?>">
                            <input class="pacc-field-full" type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">
                        </div>
                        <div class="pacc-actions">
                            <button class="pacc-btn" type="submit"><?= htmlspecialchars($t('Сохранить профиль', 'Save profile'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </form>
                </section>
            </div>

            <section class="pacc-card">
                <h2><?= htmlspecialchars($t('Смена пароля по PIN-коду', 'Change password using PIN'), ENT_QUOTES, 'UTF-8') ?></h2>
                <form class="pacc-form" method="post">
                    <input type="hidden" name="action" value="public_portal_password_change">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="/account/">
                    <div class="pacc-form-grid">
                        <input type="text" name="pin_code" placeholder="PIN" required>
                        <input type="password" name="new_password" placeholder="<?= htmlspecialchars($t('Новый пароль', 'New password'), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="pacc-actions">
                        <button class="pacc-btn" type="submit"><?= htmlspecialchars($t('Обновить пароль', 'Update password'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
            </section>

            <section class="pacc-card">
                <h2><?= htmlspecialchars($t('Сессия', 'Session'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="pacc-actions">
                    <form method="post">
                        <input type="hidden" name="action" value="public_portal_logout">
                        <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="return_path" value="/account/">
                        <button class="pacc-btn-ghost" type="submit"><?= htmlspecialchars($t('Выйти', 'Sign out'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </div>
            </section>
        <?php else: ?>
            <div class="pacc-grid">
                <section class="pacc-auth">
                    <h2><?= htmlspecialchars($t('Регистрация', 'Registration'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <form class="pacc-form" method="post">
                        <input type="hidden" name="action" value="public_portal_register">
                        <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="return_path" value="/account/">
                        <div class="pacc-form-grid">
                            <input type="text" name="username" placeholder="<?= htmlspecialchars($t('Логин', 'Login'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <div class="pacc-field-full pacc-captcha">
                                <strong><?= htmlspecialchars((string)($captcha[$isRu ? 'prompt_ru' : 'prompt_en'] ?? ($isRu ? 'Сложите два числа рядом со знаками' : 'Add the two numbers next to the symbols')), ENT_QUOTES, 'UTF-8') ?></strong>
                                <div class="pacc-captcha-row">
                                    <span><?= htmlspecialchars((string)($captcha['glyph_left'] ?? '◧'), ENT_QUOTES, 'UTF-8') ?><?= (int)($captcha['left'] ?? 0) ?></span>
                                    <span>+</span>
                                    <span><?= htmlspecialchars((string)($captcha['glyph_right'] ?? '◩'), ENT_QUOTES, 'UTF-8') ?><?= (int)($captcha['right'] ?? 0) ?></span>
                                </div>
                                <input type="text" name="captcha_answer" placeholder="<?= htmlspecialchars($t('Ответ', 'Answer'), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                        </div>
                        <div class="pacc-actions">
                            <button class="pacc-btn" type="submit"><?= htmlspecialchars($t('Создать аккаунт', 'Create account'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </form>
                </section>

                <section class="pacc-auth">
                    <h2><?= htmlspecialchars($t('Вход', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <form class="pacc-form" method="post">
                        <input type="hidden" name="action" value="public_portal_login">
                        <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="return_path" value="/account/">
                        <div class="pacc-form-grid">
                            <input type="text" name="login" placeholder="<?= htmlspecialchars($t('Логин или email', 'Login or email'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="pacc-actions">
                            <button class="pacc-btn" type="submit"><?= htmlspecialchars($t('Войти', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </form>
                </section>
            </div>
        <?php endif; ?>
    </div>
</section>
