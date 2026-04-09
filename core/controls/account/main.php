<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$portalLang = function_exists('public_portal_lang') ? public_portal_lang() : 'en';
$portalIsRu = ($portalLang === 'ru');
$portalUser = function_exists('public_portal_current_user') ? public_portal_current_user($FRMWRK) : null;
$portalFlash = function_exists('public_portal_flash_get') ? public_portal_flash_get('portal') : [];
$portalCaptcha = function_exists('public_portal_captcha_get') ? public_portal_captcha_get() : [];
$portalProfileQuery = trim((string)($_GET['user'] ?? ''));
$portalPublicProfile = ($portalProfileQuery !== '' && function_exists('public_portal_fetch_public_profile'))
    ? public_portal_fetch_public_profile($FRMWRK, $portalProfileQuery, $portalLang)
    : null;
$portalOwnPublicProfile = (!$portalPublicProfile && is_array($portalUser) && !empty($portalUser['username']) && function_exists('public_portal_fetch_public_profile'))
    ? public_portal_fetch_public_profile($FRMWRK, (string)$portalUser['username'], $portalLang)
    : null;
$portalOwnRank = (is_array($portalUser) && function_exists('public_portal_rank_meta'))
    ? public_portal_rank_meta((int)($portalUser['comment_rating'] ?? 0), $portalLang)
    : null;

if (is_array($portalFlash) && !empty($portalFlash['message'])) {
    $flashMessage = trim((string)$portalFlash['message']);
    $dropMessages = [
        'Комментарий опубликован.',
        'Рейтинг комментария обновлен.',
        'Comment published.',
        'Comment rating updated.',
    ];
    if (in_array($flashMessage, $dropMessages, true)) {
        $portalFlash = [];
    }
    if (!preg_match('/Аккаунт создан|Account created/i', $flashMessage)) {
        unset($portalFlash['pin_code']);
    }
}

$ModelPage['account'] = [
    'lang' => $portalLang,
    'user' => $portalUser,
    'flash' => $portalFlash,
    'captcha' => $portalCaptcha,
    'public_profile' => $portalPublicProfile,
    'own_public_profile' => $portalOwnPublicProfile,
    'own_rank' => $portalOwnRank,
];

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$ModelPage['title'] = $portalIsRu ? 'Профиль участника' : 'Member profile';
$ModelPage['description'] = $portalIsRu
    ? 'Профиль участника обсуждений: открытая карточка, рейтинг, звание и опубликованные комментарии.'
    : 'Public member profile with rating, title and published comments.';
$ModelPage['canonical'] = $scheme . '://' . $host . '/account/';

if (is_array($portalPublicProfile) && !empty($portalPublicProfile['username'])) {
    $profileName = (string)($portalPublicProfile['display_name'] ?? $portalPublicProfile['username']);
    $ModelPage['title'] = $portalIsRu ? ('Профиль участника: ' . $profileName) : ('Member profile: ' . $profileName);
    $ModelPage['description'] = $portalIsRu
        ? ('Открытая страница участника ' . $profileName . ': рейтинг, звание, ссылки и последние комментарии.')
        : ('Public member page for ' . $profileName . ': rating, title, links and recent comments.');
    $ModelPage['canonical'] = $scheme . '://' . $host . '/member/' . rawurlencode((string)$portalPublicProfile['username']) . '/';
}
