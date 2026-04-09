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
$portalOwnRank = (is_array($portalUser) && function_exists('public_portal_rank_meta'))
    ? public_portal_rank_meta((int)($portalUser['comment_rating'] ?? 0), $portalLang)
    : null;

$ModelPage['account'] = [
    'lang' => $portalLang,
    'user' => $portalUser,
    'flash' => $portalFlash,
    'captcha' => $portalCaptcha,
    'public_profile' => $portalPublicProfile,
    'own_rank' => $portalOwnRank,
];

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$ModelPage['title'] = $portalIsRu ? 'Личный кабинет сообщества' : 'Community account';
$ModelPage['description'] = $portalIsRu
    ? 'Вход, регистрация и управление публичным аккаунтом для комментариев и профиля.'
    : 'Sign in, register and manage your public commenting profile.';
$ModelPage['canonical'] = $scheme . '://' . $host . '/account/';

if (is_array($portalPublicProfile) && !empty($portalPublicProfile['username'])) {
    $profileName = (string)($portalPublicProfile['display_name'] ?? $portalPublicProfile['username']);
    $ModelPage['title'] = $portalIsRu ? ('Профиль комментатора: ' . $profileName) : ('Commenter profile: ' . $profileName);
    $ModelPage['description'] = $portalIsRu
        ? ('Публичный профиль комментатора ' . $profileName . ': рейтинг, звание, ссылки и последние комментарии.')
        : ('Public commenter profile for ' . $profileName . ': rating, title, links and recent comments.');
    $ModelPage['canonical'] = $scheme . '://' . $host . '/account/?user=' . rawurlencode((string)$portalPublicProfile['username']);
}
