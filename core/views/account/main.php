<?php
$account = (array)($ModelPage['account'] ?? []);
$lang = (string)($account['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$user = is_array($account['user'] ?? null) ? $account['user'] : null;
$flash = (array)($account['flash'] ?? []);
$captcha = (array)($account['captcha'] ?? []);
$publicProfile = is_array($account['public_profile'] ?? null) ? $account['public_profile'] : null;
$ownPublicProfile = is_array($account['own_public_profile'] ?? null) ? $account['own_public_profile'] : null;
$ownRank = is_array($account['own_rank'] ?? null) ? $account['own_rank'] : null;
$ownFavorites = (array)($account['own_favorites'] ?? []);
$csrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$avatar = $user && function_exists('public_portal_user_avatar') ? public_portal_user_avatar($user) : '';
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
$publicContact = static function (?string $value, string $type = 'text'): string {
    if (function_exists('public_portal_public_contact_value')) {
        return public_portal_public_contact_value((string)$value, $type);
    }
    return trim((string)$value);
};
$ownEmailValue = '';
if ($user) {
    $ownEmailValue = $publicContact((string)($user['email'] ?? ''), 'email');
}
?>
<style>
.pacc{max-width:1240px;margin:0 auto;padding:30px 18px 64px;color:var(--shell-text)}
.pacc-shell{display:grid;gap:18px}
.pacc-hero,.pacc-card,.pacc-auth{padding:24px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.pacc-hero h1{margin:0 0 10px;font:700 clamp(1.45rem,2.1vw,1.9rem)/.98 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.pacc-card h2,.pacc-auth h2{margin:0 0 10px;font:700 clamp(.95rem,1.25vw,1.08rem)/1.04 "Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.pacc-card h3{margin:0 0 10px;font:700 .95rem/1.04 "Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.pacc-hero p,.pacc-card p{margin:0;color:var(--shell-muted);line-height:1.7}
.pacc-flash{padding:12px 14px;border:1px solid rgba(122,180,255,.16)}
.pacc-flash.ok{background:rgba(39,223,192,.10);color:#9fe9df}
.pacc-flash.error{background:rgba(255,106,127,.10);color:#ffc0cb}
.pacc-grid{display:grid;grid-template-columns:minmax(280px,.78fr) minmax(0,1.22fr);gap:18px}
.pacc-stack{display:grid;gap:18px}
.pacc-avatar{width:96px;height:96px;overflow:hidden;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04)}
.pacc-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.pacc-userhead{display:grid;gap:14px}
.pacc-meta{display:grid;gap:10px}
.pacc-meta-row{padding:12px 14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pacc-meta-row strong{display:block;margin-bottom:5px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--shell-accent)}
.pacc-rank{display:grid;gap:10px}
.pacc-rank-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid rgba(122,180,255,.14);background:rgba(255,255,255,.03);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.pacc-rank-note{color:var(--shell-muted);font-size:14px;line-height:1.6}
.pacc-mini{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;color:var(--shell-muted);font-size:13px}
.pacc-mini a,.pacc-favorite-title,.pacc-recent-item a{color:var(--shell-accent);text-decoration:none}
.pacc-actions{display:flex;flex-wrap:wrap;gap:10px}
.pacc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 16px;border:1px solid rgba(122,180,255,.16);text-decoration:none;color:var(--shell-text);background:linear-gradient(135deg,rgba(115,184,255,.24),rgba(39,223,192,.18));font-weight:700}
.pacc-switcher{display:flex;flex-wrap:wrap;gap:10px}
.pacc-switcher button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.03);color:var(--shell-text);cursor:pointer}
.pacc-switcher button.is-active{background:linear-gradient(135deg,rgba(115,184,255,.24),rgba(39,223,192,.18));font-weight:700}
.pacc-form{display:grid;gap:12px}
.pacc-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.pacc-form input{width:100%;padding:13px 14px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.58);color:var(--shell-text)}
.pacc-form .pacc-field-full{grid-column:1 / -1}
.pacc-captcha{display:grid;gap:8px;padding:14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pacc-captcha-row{display:flex;align-items:center;gap:10px;font:700 1.05rem/1 "Space Grotesk","Sora",sans-serif}
.pacc-captcha-row span{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:42px;padding:0 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04)}
.pacc-auth-stack{display:grid;gap:14px}
.pacc-auth-pane{display:none}
.pacc-auth-pane.is-active{display:block}
.pacc-recent,.pacc-favorites{display:grid;gap:12px}
.pacc-recent-item,.pacc-favorite-item{padding:16px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pacc-favorites-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.pacc-favorite-item{display:grid;grid-template-columns:88px minmax(0,1fr);gap:12px;align-items:start}
.pacc-favorite-media{display:block;aspect-ratio:1/1;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03);overflow:hidden}
.pacc-favorite-media img{display:block;width:100%;height:100%;object-fit:cover}
.pacc-favorite-meta{display:flex;flex-wrap:wrap;gap:8px;color:var(--shell-muted);font-size:12px}
@media (max-width: 920px){
    .pacc-grid,.pacc-form-grid,.pacc-favorites-grid{grid-template-columns:1fr}
    .pacc-form .pacc-field-full{grid-column:auto}
}
</style>

<section class="pacc">
    <div class="pacc-shell">
        <header class="pacc-hero">
            <h1><?= htmlspecialchars($publicProfile ? $t('Публичная часть профиля', 'Public profile') : ($user ? $t('Управление аккаунтом', 'Account management') : $t('Вход в обсуждение', 'Access discussion')), ENT_QUOTES, 'UTF-8') ?></h1>
            <p>
                <?= htmlspecialchars(
                    $publicProfile
                        ? $t('Здесь видна открытая часть профиля: имя, репутация в обсуждениях, избранные материалы и заметные реплики под статьями.', 'This page shows the public side of the profile: name, discussion reputation, saved articles and visible replies.')
                        : ($user
                            ? $t('Здесь вы управляете своей открытой карточкой, контактами, паролем и личной подборкой материалов.', 'Manage your public card, contacts, password and saved articles here.')
                            : $t('Войдите в аккаунт или создайте его, чтобы участвовать в обсуждениях, сохранять статьи и собирать собственный профиль автора.', 'Sign in or create an account to join discussions, save articles and build your public author profile.')),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </header>

        <?php if (!empty($flash['message'])): ?>
            <div class="pacc-flash <?= htmlspecialchars((string)($flash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars((string)$flash['message'], ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($flash['pin_code'])): ?>
                    <div style="margin-top:8px;font-weight:700;">PIN: <?= htmlspecialchars((string)$flash['pin_code'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($publicProfile): ?>
            <section class="pacc-card">
                <div class="pacc-grid">
                    <aside class="pacc-card">
                        <div class="pacc-userhead">
                            <span class="pacc-avatar"><img src="<?= htmlspecialchars((string)($publicProfile['avatar_src'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($publicProfile['display_name'] ?? $publicProfile['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?>"></span>
                            <div class="pacc-meta">
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Никнейм', 'Nickname'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string)($publicProfile['display_name'] ?? $publicProfile['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Рейтинг', 'Rating'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($publicProfile['comment_rating'] ?? 0) ?></span></div>
                                <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Комментарии', 'Comments'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($publicProfile['comments_count'] ?? 0) ?></span></div>
                            </div>
                        </div>
                    </aside>
                    <section class="pacc-card">
                        <h2><?= htmlspecialchars($t('Открытая карточка автора', 'Open author card'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <div class="pacc-rank">
                            <span class="pacc-rank-badge"><?= htmlspecialchars((string)($publicProfile['rank_meta']['label'] ?? $t('Участник обсуждения', 'Discussion member')), ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="pacc-rank-note"><?= htmlspecialchars($t('Публично видны только те контакты и следы участия, которые человек решил оставить открытыми: рейтинг, избранные материалы и заметные ответы под статьями.', 'Only the contacts and participation traces the member decided to keep public are visible here: rating, saved reads and notable replies.'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="pacc-mini">
                                <?php if (!empty($publicProfile['telegram_public'])): ?><span>Telegram: <?= htmlspecialchars((string)$publicProfile['telegram_public'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                                <?php if (!empty($publicProfile['website_public'])): ?><span><a href="<?= htmlspecialchars((string)$publicProfile['website_public'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="nofollow noopener"><?= htmlspecialchars($t('Сайт', 'Website'), ENT_QUOTES, 'UTF-8') ?></a></span><?php endif; ?>
                                <?php if (!empty($publicProfile['email_public'])): ?><span><?= htmlspecialchars((string)$publicProfile['email_public'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                                <span>+<?= (int)($publicProfile['comment_votes_up'] ?? 0) ?> / -<?= (int)($publicProfile['comment_votes_down'] ?? 0) ?></span>
                            </div>
                        </div>
                    </section>
                </div>
            </section>

            <section class="pacc-card">
                <h2><?= htmlspecialchars($t('Избранные материалы', 'Saved reads'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="pacc-favorites">
                    <?php if (!empty($publicProfile['favorites'])): ?>
                        <div class="pacc-favorites-grid">
                            <?php foreach ((array)$publicProfile['favorites'] as $favorite): ?>
                                <article class="pacc-favorite-item">
                                    <a class="pacc-favorite-media" href="<?= htmlspecialchars((string)($favorite['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if (!empty($favorite['image_src'])): ?><img src="<?= htmlspecialchars((string)$favorite['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($favorite['title'] ?? 'Article'), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                                    </a>
                                    <div class="pacc-stack">
                                        <a class="pacc-favorite-title" href="<?= htmlspecialchars((string)($favorite['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($favorite['title'] ?? $t('Материал', 'Article')), ENT_QUOTES, 'UTF-8') ?></a>
                                        <div class="pacc-favorite-meta">
                                            <span><?= htmlspecialchars((string)($favorite['material_section'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                            <span><?= htmlspecialchars((string)($favorite['saved_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?= htmlspecialchars($t('Пока в открытой подборке нет сохраненных материалов.', 'There are no saved articles in this public selection yet.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
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
                        <p><?= htmlspecialchars($t('У этого автора пока нет открытых комментариев.', 'This author has no public comments yet.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php elseif ($user): ?>
            <div class="pacc-grid">
                <aside class="pacc-card">
                    <div class="pacc-userhead">
                        <span class="pacc-avatar"><?php if ($avatar !== ''): ?><img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($user['display_name'] ?? $user['username'] ?? 'Member'), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></span>
                        <div class="pacc-meta">
                            <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Никнейм', 'Nickname'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string)($user['display_name'] ?? $user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Рейтинг', 'Rating'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($user['comment_rating'] ?? 0) ?></span></div>
                            <div class="pacc-meta-row"><strong><?= htmlspecialchars($t('Комментарии', 'Comments'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int)($user['comments_count'] ?? 0) ?></span></div>
                        </div>
                    </div>
                </aside>

                <div class="pacc-stack">
                    <section class="pacc-card">
                        <h2><?= htmlspecialchars($t('Открытая карточка и управление аккаунтом', 'Public card and account controls'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <?php if ($ownRank): ?>
                            <div class="pacc-rank">
                                <span class="pacc-rank-badge"><?= htmlspecialchars((string)($ownRank['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="pacc-rank-note">
                                    <?= htmlspecialchars($t('В этой части вы настраиваете то, что видно людям при переходе в ваш профиль из комментариев: никнейм, контакты, аватар и личную подборку сохраненных материалов.', 'This is where you manage what people see when they open your profile from comments: nickname, contacts, avatar and your saved reading list.'), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($ownRank['next']['label'])): ?>
                                        <?= ' ' . htmlspecialchars($t('До следующего звания осталось', 'Until the next title'), ENT_QUOTES, 'UTF-8') . ' ' . (int)($ownRank['to_next'] ?? 0) . '.' ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <form class="pacc-form" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="public_portal_profile_update">
                            <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="return_path" value="/account/">
                            <div class="pacc-form-grid">
                                <input type="text" name="display_name" value="<?= htmlspecialchars((string)($user['display_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t('Никнейм', 'Nickname'), ENT_QUOTES, 'UTF-8') ?>" required>
                                <input type="email" name="email" value="<?= htmlspecialchars($ownEmailValue, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t('Email', 'Email'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="text" name="telegram_handle" value="<?= htmlspecialchars((string)($user['telegram_handle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Telegram">
                                <input type="url" name="website_url" value="<?= htmlspecialchars((string)($user['website_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($t('Сайт', 'Website'), ENT_QUOTES, 'UTF-8') ?>">
                                <input class="pacc-field-full" type="file" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp">
                            </div>
                            <div class="pacc-actions">
                                <button class="pacc-btn" type="submit"><?= htmlspecialchars($t('Сохранить открытую карточку', 'Save public card'), ENT_QUOTES, 'UTF-8') ?></button>
                                <a class="pacc-btn" href="<?= htmlspecialchars(function_exists('public_portal_profile_url') ? public_portal_profile_url($user) : '/member/', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Открыть публичную страницу', 'Open public page'), ENT_QUOTES, 'UTF-8') ?></a>
                            </div>
                        </form>
                    </section>

                    <section class="pacc-card">
                        <h2><?= htmlspecialchars($t('Смена пароля', 'Change password'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <form class="pacc-form" method="post">
                            <input type="hidden" name="action" value="public_portal_password_change_v2">
                            <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="return_path" value="/account/">
                            <div class="pacc-form-grid">
                                <input type="password" name="current_password" placeholder="<?= htmlspecialchars($t('Текущий пароль', 'Current password'), ENT_QUOTES, 'UTF-8') ?>" required>
                                <input type="password" name="new_password" placeholder="<?= htmlspecialchars($t('Новый пароль, минимум 8 символов', 'New password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="pacc-actions">
                                <button class="pacc-btn" type="submit"><?= htmlspecialchars($t('Обновить пароль', 'Update password'), ENT_QUOTES, 'UTF-8') ?></button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <section class="pacc-card">
                <h2><?= htmlspecialchars($t('Избранные материалы', 'Saved reads'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="pacc-favorites">
                    <?php if (!empty($ownFavorites)): ?>
                        <div class="pacc-favorites-grid">
                            <?php foreach ($ownFavorites as $favorite): ?>
                                <article class="pacc-favorite-item">
                                    <a class="pacc-favorite-media" href="<?= htmlspecialchars((string)($favorite['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if (!empty($favorite['image_src'])): ?><img src="<?= htmlspecialchars((string)$favorite['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($favorite['title'] ?? 'Article'), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                                    </a>
                                    <div class="pacc-stack">
                                        <a class="pacc-favorite-title" href="<?= htmlspecialchars((string)($favorite['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($favorite['title'] ?? $t('Материал', 'Article')), ENT_QUOTES, 'UTF-8') ?></a>
                                        <div class="pacc-favorite-meta">
                                            <span><?= htmlspecialchars((string)($favorite['material_section'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                            <span><?= htmlspecialchars((string)($favorite['saved_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?= htmlspecialchars($t('Пока в избранном пусто. Сохраняйте материалы прямо из статьи, рядом с кнопками поделиться.', 'Your saved list is empty so far. Add articles from the detail page next to the share buttons.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="pacc-card">
                <h2><?= htmlspecialchars($t('Ваши последние комментарии', 'Your recent comments'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="pacc-recent">
                    <?php foreach ((array)($ownPublicProfile['recent_comments'] ?? []) as $comment): ?>
                        <article class="pacc-recent-item">
                            <strong><a href="<?= htmlspecialchars((string)($comment['article_url'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($comment['article_title'] ?? $t('Материал', 'Article')), ENT_QUOTES, 'UTF-8') ?></a></strong>
                            <div class="pacc-mini"><span><?= htmlspecialchars((string)($comment['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><span><?= (int)($comment['rating_score'] ?? 0) ?></span></div>
                            <div><?= (string)($comment['body_html'] ?? '') ?></div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (empty($ownPublicProfile['recent_comments'])): ?>
                        <p><?= htmlspecialchars($t('Пока здесь нет опубликованных реплик.', 'No published replies yet.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <div class="pacc-auth-stack" id="pacc-auth-stack">
                <div class="pacc-switcher" role="tablist" aria-label="<?= htmlspecialchars($t('Переключение формы', 'Auth form switcher'), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="button" class="is-active" data-auth-target="signin" aria-pressed="true"><?= htmlspecialchars($t('Вход', 'Sign in'), ENT_QUOTES, 'UTF-8') ?></button>
                    <button type="button" data-auth-target="register" aria-pressed="false"><?= htmlspecialchars($t('Регистрация', 'Registration'), ENT_QUOTES, 'UTF-8') ?></button>
                </div>

                <section class="pacc-auth pacc-auth-pane is-active" data-auth-pane="signin">
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

                <section class="pacc-auth pacc-auth-pane" data-auth-pane="register">
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
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!$user && !$publicProfile): ?>
<script>
(function () {
    var stack = document.getElementById('pacc-auth-stack');
    if (!stack) {
        return;
    }
    var buttons = stack.querySelectorAll('[data-auth-target]');
    var panes = stack.querySelectorAll('[data-auth-pane]');
    function setPane(name) {
        panes.forEach(function (pane) {
            pane.classList.toggle('is-active', pane.getAttribute('data-auth-pane') === name);
        });
        buttons.forEach(function (button) {
            var active = button.getAttribute('data-auth-target') === name;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            setPane(button.getAttribute('data-auth-target') || 'signin');
        });
    });
    setPane('signin');
})();
</script>
<?php endif; ?>
