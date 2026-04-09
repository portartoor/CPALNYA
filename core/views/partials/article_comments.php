<?php
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalFlash = (array)($ModelPage['portal_flash'] ?? []);
$portalCaptcha = (array)($ModelPage['portal_captcha'] ?? []);
$portalComments = (array)($ModelPage['portal_comments'] ?? []);
$portalCommentTotal = (int)($ModelPage['portal_comment_total'] ?? 0);
$portalContentType = trim((string)($ModelPage['portal_content_type'] ?? 'examples'));
$portalContentId = (int)($ModelPage['portal_content_id'] ?? 0);
$portalLang = isset($lang) ? (string)$lang : 'en';
$portalIsRu = ($portalLang === 'ru');
$portalIsLoggedIn = is_array($portalUser) && (int)($portalUser['id'] ?? 0) > 0;
$portalCsrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$portalSections = $portalIsRu
    ? ['discussion' => 'РћР±СЃСѓР¶РґРµРЅРёРµ', 'question' => 'Р’РѕРїСЂРѕСЃ', 'idea' => 'РРґРµСЏ', 'feedback' => 'РћС‚Р·С‹РІ', 'case' => 'РџСЂР°РєС‚РёРєР°']
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
$portalEmptyCtas = $portalIsRu
    ? [
        'РџРµСЂРІСѓСЋ СЂРµРїР»РёРєСѓ Р·РґРµСЃСЊ РѕР±С‹С‡РЅРѕ РѕСЃС‚Р°РІР»СЏРµС‚ С‚РѕС‚, РєС‚Рѕ РїСЂРѕС‡РёС‚Р°Р» РјРµР¶РґСѓ СЃС‚СЂРѕРє.',
        'Р•СЃР»Рё РјР°С‚РµСЂРёР°Р» С…РѕС‡РµС‚СЃСЏ РЅРµ РїСЂРѕСЃС‚Рѕ РґРѕС‡РёС‚Р°С‚СЊ, Р° СЃРґРІРёРЅСѓС‚СЊ СЃ РјРµСЃС‚Р°, РєРѕРјРјРµРЅС‚Р°СЂРёР№ РїРѕРґС…РѕРґРёС‚ Р»СѓС‡С€Рµ РІСЃРµРіРѕ.',
        'РџРѕРґ СЃС‚Р°С‚СЊРµР№ РїРѕРєР° С‚РёС€РёРЅР°. РЎР°РјРѕРµ РІСЂРµРјСЏ РёСЃРїРѕСЂС‚РёС‚СЊ РµРµ С‚РѕС‡РЅС‹Рј РЅР°Р±Р»СЋРґРµРЅРёРµРј.',
        'РРЅРѕРіРґР° РѕРґРёРЅ РєРѕРјРјРµРЅС‚Р°СЂРёР№ РїРѕР»РµР·РЅРµРµ Р»РёС€РЅРµРіРѕ Р°Р±Р·Р°С†Р° РІ С‚РµРєСЃС‚Рµ.',
        'Р РµРґР°РєС†РёСЏ СѓР¶Рµ СЃРєР°Р·Р°Р»Р° СЃРІРѕРµ. РўРµРїРµСЂСЊ РёРЅС‚РµСЂРµСЃРЅРµРµ СѓСЃР»С‹С€Р°С‚СЊ С‡РµР»РѕРІРµРєР° РёР· РїСЂРѕС†РµСЃСЃР°.',
        'Р•СЃР»Рё РІ РјР°С‚РµСЂРёР°Р»Рµ С‡РµРіРѕ-С‚Рѕ РЅРµ С…РІР°С‚Р°РµС‚, РЅРµРґРѕСЃС‚Р°СЋС‰Р°СЏ СЃС‚СЂРѕРєР° РІРїРѕР»РЅРµ РјРѕР¶РµС‚ РїРѕСЏРІРёС‚СЊСЃСЏ РЅРёР¶Рµ.',
        'Р—РґРµСЃСЊ РїРѕРєР° РЅРёРєС‚Рѕ РЅРµ РІРѕР·СЂР°Р·РёР» СЃС‚Р°С‚СЊРµ. РџРѕРґРѕР·СЂРёС‚РµР»СЊРЅРѕ СѓРґРѕР±РЅР°СЏ СЃРёС‚СѓР°С†РёСЏ, С‡С‚РѕР±С‹ РЅР°С‡Р°С‚СЊ РїРµСЂРІС‹Рј.',
        'Р‘С‹РІР°РµС‚, С‡С‚Рѕ Р»СѓС‡С€РёР№ С„СЂР°РіРјРµРЅС‚ РЅРѕРјРµСЂР° РїРѕСЏРІР»СЏРµС‚СЃСЏ РёРјРµРЅРЅРѕ РІ РєРѕРјРјРµРЅС‚Р°СЂРёСЏС….',
        'Р•СЃР»Рё Сѓ РІР°СЃ РµСЃС‚СЊ СЂР°Р±РѕС‡РµРµ РІРѕР·СЂР°Р¶РµРЅРёРµ, РЅР°Р±Р»СЋРґРµРЅРёРµ РёР»Рё С‚РёС…РёР№ РёРЅСЃР°Р№Рґ, РµРјСѓ РєР°Рє СЂР°Р· СЃСЋРґР°.',
        'РљРѕРјРјРµРЅС‚Р°СЂРёР№ Р·РґРµСЃСЊ РјРѕР¶РµС‚ Р±С‹С‚СЊ РєРѕСЂРѕС‡Рµ СЃС‚Р°С‚СЊРё, РЅРѕ С‚РѕС‡РЅРµРµ РµРµ РїРѕРІРѕСЂРѕС‚Р°.',
        'РРЅРѕРіРґР° РѕР±СЃСѓР¶РґРµРЅРёРµ РЅР°С‡РёРЅР°РµС‚СЃСЏ РЅРµ СЃ РіСЂРѕРјРєРѕРіРѕ С‚РµР·РёСЃР°, Р° СЃ Р°РєРєСѓСЂР°С‚РЅРѕРіРѕ вЂњРІРѕРѕР±С‰Рµ-С‚РѕвЂќ.',
        'Р•СЃР»Рё СЌС‚РѕС‚ С‚РµРєСЃС‚ Р·Р°РґРµР» РїСЂРѕС„РµСЃСЃРёРѕРЅР°Р»СЊРЅСѓСЋ РїСЂРёРІС‹С‡РєСѓ, СЃС‚РѕРёС‚ РѕСЃС‚Р°РІРёС‚СЊ СЃР»РµРґ РїРѕРґ РЅРёРј.',
        'РџРѕРґ С‚Р°РєРёРј РјР°С‚РµСЂРёР°Р»РѕРј С†РµРЅСЏС‚СЃСЏ РЅРµ Р°РїР»РѕРґРёСЃРјРµРЅС‚С‹, Р° С‚РѕС‡РЅС‹Рµ РґРѕРїРѕР»РЅРµРЅРёСЏ.',
        'РҐРѕСЂРѕС€РёР№ РєРѕРјРјРµРЅС‚Р°СЂРёР№ Р·РґРµСЃСЊ СЂР°Р±РѕС‚Р°РµС‚ РєР°Рє СЂРµРґР°РєС‚РѕСЂСЃРєР°СЏ РїРѕРјРµС‚РєР° РЅР° РїРѕР»СЏС….',
        'РњРѕР¶РЅРѕ РѕСЃС‚Р°РІРёС‚СЊ РІРѕРїСЂРѕСЃ, РєРѕС‚РѕСЂС‹Р№ РЅРµСѓРґРѕР±РЅРѕ Р·Р°РґР°С‚СЊ РІСЃР»СѓС…. РћР±С‹С‡РЅРѕ РёРјРµРЅРЅРѕ С‚Р°РєРёРµ Рё РґРІРёРіР°СЋС‚ СЂР°Р·РіРѕРІРѕСЂ.',
        'Р•СЃР»Рё РІС‹ РґРѕС‡РёС‚Р°Р»Рё РґРѕ РєРѕРЅС†Р° Рё РЅРµ СЃРѕРіР»Р°СЃРёР»РёСЃСЊ С…РѕС‚СЏ Р±С‹ СЃ РѕРґРЅРёРј РїРѕРІРѕСЂРѕС‚РѕРј, РїРѕСЂР° РѕС‚РєСЂС‹С‚СЊ РѕР±СЃСѓР¶РґРµРЅРёРµ.',
        'РўРёС€РёРЅР° РїРѕРґ СЃС‚Р°С‚СЊРµР№ РІС‹РіР»СЏРґРёС‚ РєСЂР°СЃРёРІРѕ, РЅРѕ РїРѕР»РµР·РЅРѕР№ РѕРЅР° Р±С‹РІР°РµС‚ СЂРµРґРєРѕ.',
        'РџРѕРґ СЌС‚РёРј С‚РµРєСЃС‚РѕРј РµС‰Рµ РЅРµС‚ СЂРµРїР»РёРєРё, РєРѕС‚РѕСЂР°СЏ РїРµСЂРµРІРµР»Р° Р±С‹ С‚РµРѕСЂРёСЋ РІ РїСЂР°РєС‚РёРєСѓ.',
        'РЎР°РјС‹Рµ Р¶РёРІС‹Рµ РјР°С‚РµСЂРёР°Р»С‹ СЂРµРґРєРѕ Р·Р°РєР°РЅС‡РёРІР°СЋС‚СЃСЏ С‚РѕС‡РєРѕР№. Р§Р°С‰Рµ РѕРЅРё РїСЂРѕРґРѕР»Р¶Р°СЋС‚СЃСЏ РІ РєРѕРјРјРµРЅС‚Р°СЂРёСЏС….',
        'Р•СЃР»Рё СЃС‚Р°С‚СЊСЏ РІС‹Р·РІР°Р»Р° РІРЅСѓС‚СЂРµРЅРЅРµРµ вЂњРґР°, РЅРѕвЂ¦вЂќ, СЌС‚Рѕ РѕС‚Р»РёС‡РЅС‹Р№ СЃС‚Р°СЂС‚ РґР»СЏ РїРµСЂРІРѕР№ СЂРµРїР»РёРєРё.',
        'РљРѕРјРјРµРЅС‚Р°СЂРёРё Р·РґРµСЃСЊ РЅСѓР¶РЅС‹ РЅРµ РґР»СЏ РІРµР¶Р»РёРІРѕСЃС‚Рё, Р° РґР»СЏ РїСЂРѕРґРѕР»Р¶РµРЅРёСЏ РјС‹СЃР»Рё.',
        'Р’РѕРїСЂРѕСЃ РїРѕРґ С‚Р°РєРёРј РјР°С‚РµСЂРёР°Р»РѕРј РёРЅРѕРіРґР° С†РµРЅРЅРµРµ СѓРІРµСЂРµРЅРЅРѕРіРѕ РІС‹РІРѕРґР°.',
        'Р•СЃР»Рё С…РѕС‡РµС‚СЃСЏ РґРѕР±Р°РІРёС‚СЊ РєРѕРЅС‚РµРєСЃС‚, РїСЂРёРјРµСЂ РёР»Рё РјСЏРіРєРѕРµ РЅРµСЃРѕРіР»Р°СЃРёРµ, РјРµСЃС‚Рѕ СѓР¶Рµ РіРѕС‚РѕРІРѕ.',
        'Р‘С‹РІР°РµС‚, С‡С‚Рѕ РїРѕРґ СЃС‚Р°С‚СЊРµР№ СЂРѕР¶РґР°РµС‚СЃСЏ РµРµ Р±РѕР»РµРµ С‡РµСЃС‚РЅР°СЏ РІРµСЂСЃРёСЏ.',
        'РћСЃС‚Р°РІСЊС‚Рµ СЂРµРїР»РёРєСѓ С‚Р°Рє, Р±СѓРґС‚Рѕ РїСЂРѕРґРѕР»Р¶Р°РµС‚Рµ РєРѕР»РѕРЅРєСѓ, Р° РЅРµ РїСЂРѕСЃС‚Рѕ РѕС‚РІРµС‡Р°РµС‚Рµ РІРЅРёР·Сѓ СЃС‚СЂР°РЅРёС†С‹.',
        'РџРѕРґ СЃС‚Р°С‚СЊРµР№ РїРѕРєР° РїСѓСЃС‚Рѕ. Р СЌС‚Рѕ СЂРµРґРєРѕРµ РїСЂРѕСЃС‚СЂР°РЅСЃС‚РІРѕ РґР»СЏ РїРµСЂРІРѕР№ СѓРјРЅРѕР№ СЂРµРїР»РёРєРё.',
        'Р•СЃР»Рё С‚РµРєСЃС‚ РїРѕРїР°Р» РІ РЅРµСЂРІ, РєРѕРјРјРµРЅС‚Р°СЂРёР№ РјРѕР¶РµС‚ РїРѕРїР°СЃС‚СЊ РїСЂСЏРјРѕ РІ СЃСѓС‚СЊ.',
        'РРЅРѕРіРґР° РѕР±СЃСѓР¶РґРµРЅРёРµ РЅР°С‡РёРЅР°РµС‚СЃСЏ СЃ С„СЂР°Р·С‹, РєРѕС‚РѕСЂСѓСЋ Р°РІС‚РѕСЂСѓ СЃР°РјРѕРјСѓ С…РѕС‚РµР»РѕСЃСЊ Р±С‹ РїСЂРѕС‡РёС‚Р°С‚СЊ.',
        'РџРµСЂРІС‹Р№ РєРѕРјРјРµРЅС‚Р°СЂРёР№ Р·РґРµСЃСЊ РјРѕР¶РµС‚ Р±С‹С‚СЊ РЅРµ РіСЂРѕРјРєРёРј, Р° РїСЂРѕСЃС‚Рѕ С‚РѕС‡РЅС‹Рј.',
        'РџРѕРґ РјР°С‚РµСЂРёР°Р»РѕРј РµС‰Рµ РЅРµС‚ РЅРё РѕРґРЅРѕРіРѕ РІРѕРїСЂРѕСЃР°. Р—РЅР°С‡РёС‚, РјРѕР¶РЅРѕ Р·Р°РґР°С‚СЊ С‚РѕС‚ СЃР°РјС‹Р№.',
        'Р•СЃР»Рё СЃС‚Р°С‚СЊСЏ РїРѕРєР°Р·Р°Р»Р°СЃСЊ СЃР»РёС€РєРѕРј СѓРІРµСЂРµРЅРЅРѕР№, РєРѕРјРјРµРЅС‚Р°СЂРёР№ вЂ” С…РѕСЂРѕС€РµРµ РјРµСЃС‚Рѕ РґР»СЏ РєРѕРЅС‚СЂР°СЂРіСѓРјРµРЅС‚Р°.',
        'Р›СѓС‡С€РёРµ СЂР°Р·РіРѕРІРѕСЂС‹ РїРѕРґ СЃС‚Р°С‚СЊСЏРјРё РЅР°С‡РёРЅР°СЋС‚СЃСЏ Р±РµР· СЂР°Р·СЂРµС€РµРЅРёСЏ. РџСЂРѕСЃС‚Рѕ СЃ РїРµСЂРІРѕР№ С„СЂР°Р·С‹.',
        'РўСѓС‚ РїРѕРєР° РЅРµС‚ РЅРё РІРѕР·СЂР°Р¶РµРЅРёР№, РЅРё РёРЅСЃР°Р№РґРѕРІ, РЅРё СЂРµРґР°РєС‚РѕСЂСЃРєРѕРіРѕ С€РµРїРѕС‚Р° СЃРЅРёР·Сѓ. РњРѕР¶РЅРѕ РёСЃРїСЂР°РІРёС‚СЊ.',
        'РљРѕРјРјРµРЅС‚Р°СЂРёР№ вЂ” СЌС‚Рѕ Р±С‹СЃС‚СЂС‹Р№ СЃРїРѕСЃРѕР± РїРѕРєР°Р·Р°С‚СЊ, С‡С‚Рѕ СЃС‚Р°С‚СЊСЏ РїРѕРїР°Р»Р° РЅРµ РІ РїСѓСЃС‚РѕС‚Сѓ.',
        'Р•СЃР»Рё Сѓ РјР°С‚РµСЂРёР°Р»Р° РµСЃС‚СЊ РІС‚РѕСЂРѕРµ РґРЅРѕ, РµРіРѕ РѕР±С‹С‡РЅРѕ РІСЃРєСЂС‹РІР°СЋС‚ РЅРµ РІ С‚РµРєСЃС‚Рµ, Р° РІ РѕР±СЃСѓР¶РґРµРЅРёРё.',
        'РџРѕРґРµР»РёС‚РµСЃСЊ С‚РµРј, С‡С‚Рѕ РІ СЂР°Р±РѕС‡РёС… С‡Р°С‚Р°С… СЃРєР°Р·Р°Р»Рё Р±С‹ РїРѕР»СѓС€РµРїРѕС‚РѕРј.',
        'Р•СЃР»Рё С‚РµРєСЃС‚ С…РѕС‡РµС‚СЃСЏ РїСЂРѕРґРѕР»Р¶РёС‚СЊ РїСЂРёРјРµСЂРѕРј РёР· Р¶РёР·РЅРё, СЌС‚Рѕ Р»СѓС‡С€РµРµ РјРµСЃС‚Рѕ.',
        'РРЅРѕРіРґР° РѕРґРЅРѕ С‚РѕС‡РЅРѕРµ вЂњСѓ РЅР°СЃ Р±С‹Р»Рѕ РёРЅР°С‡РµвЂќ СЃРѕР±РёСЂР°РµС‚ РѕР±СЃСѓР¶РґРµРЅРёРµ Р»СѓС‡С€Рµ Р»СЋР±РѕРіРѕ Р»РѕРЅРіСЂРёРґР°.',
        'РџРѕРґ РјР°С‚РµСЂРёР°Р»РѕРј РїРѕРєР° РёРґРµР°Р»СЊРЅР°СЏ РїСѓСЃС‚РѕС‚Р° РґР»СЏ РїРµСЂРІРѕР№ СѓРјРЅРѕР№ РїСЂРѕРІРѕРєР°С†РёРё.',
        'Р•СЃР»Рё СЃС‚Р°С‚СЊСЏ РїРѕРЅСЂР°РІРёР»Р°СЃСЊ, РјРѕР¶РЅРѕ РїРѕРґРґРµСЂР¶Р°С‚СЊ РµРµ Р°СЂРіСѓРјРµРЅС‚РѕРј. Р•СЃР»Рё РЅРµС‚ вЂ” С‚РµРј Р±РѕР»РµРµ.',
        'РљРѕРјРјРµРЅС‚Р°СЂРёРё Р·РґРµСЃСЊ РѕСЃРѕР±РµРЅРЅРѕ С…РѕСЂРѕС€Рё, РєРѕРіРґР° РІ РЅРёС… РјРµРЅСЊС€Рµ С€СѓРјР° Рё Р±РѕР»СЊС€Рµ СЂРµРјРµСЃР»Р°.',
        'РџРѕРґ СЌС‚РѕР№ СЃС‚Р°С‚СЊРµР№ РїРѕРєР° РЅРµС‚ РЅРё РѕРґРЅРѕР№ СЂРµРїР»РёРєРё, РєРѕС‚РѕСЂР°СЏ Р±С‹ РёР·РјРµРЅРёР»Р° РµРµ СѓРіРѕР».',
        'Р•СЃР»Рё Сѓ РІР°СЃ РµСЃС‚СЊ СЃРІРѕР№ backstage Рє СЌС‚РѕР№ С‚РµРјРµ, РѕРЅ РІРїРѕР»РЅРµ РґРѕСЃС‚РѕРёРЅ РІС‹Р№С‚Рё РёР· С‚РµРЅРё.',
        'РќРёР¶Рµ РµС‰Рµ РЅРµ РїСЂРѕР·РІСѓС‡Р°Р»Р° С„СЂР°Р·Р°, РїРѕСЃР»Рµ РєРѕС‚РѕСЂРѕР№ СЃС‚Р°С‚СЊСЏ С‡РёС‚Р°РµС‚СЃСЏ РїРѕ-РґСЂСѓРіРѕРјСѓ.',
        'РРЅРѕРіРґР° Р»СѓС‡С€РёР№ РєРѕРјРїР»РёРјРµРЅС‚ С‚РµРєСЃС‚Сѓ вЂ” СѓРјРЅС‹Р№ РєРѕРјРјРµРЅС‚Р°СЂРёР№ РїРѕРґ РЅРёРј.',
        'РўСЂРµРґ РїРѕРєР° РїСѓСЃС‚, Р° Р·РЅР°С‡РёС‚, Сѓ РїРµСЂРІРѕР№ СЂРµРїР»РёРєРё РµСЃС‚СЊ СЂРѕСЃРєРѕС€СЊ Р·Р°РґР°С‚СЊ С‚РѕРЅ.',
        'Р•СЃР»Рё РІС‹ Р·РЅР°РµС‚Рµ РґРµС‚Р°Р»СЊ, РєРѕС‚РѕСЂСѓСЋ СЃС‚Р°С‚СЊСЏ С‚РѕР»СЊРєРѕ РЅР°С‰СѓРїР°Р»Р°, РµР№ РїРѕСЂР° РїРѕСЏРІРёС‚СЊСЃСЏ Р·РґРµСЃСЊ.',
        'РџРѕРґ РјР°С‚РµСЂРёР°Р»РѕРј РїРѕРєР° РЅРµ С…РІР°С‚Р°РµС‚ Р¶РёРІРѕРіРѕ С‡РµР»РѕРІРµС‡РµСЃРєРѕРіРѕ СЃР»РµРґР°. РњРѕР¶РЅРѕ РѕСЃС‚Р°РІРёС‚СЊ РµРіРѕ РїРµСЂРІС‹Рј.',
        'РўРёС€РёРЅР° Р·РґРµСЃСЊ СЃР»РёС€РєРѕРј Р°РєРєСѓСЂР°С‚РЅР°СЏ. Р”Р°РІР°Р№С‚Рµ РґРѕР±Р°РІРёРј РІ РЅРµРµ СЃРјС‹СЃР».',
        'Р РµРґР°РєС†РёСЏ Р»СЋР±РёС‚, РєРѕРіРґР° РѕР±СЃСѓР¶РґРµРЅРёРµ РЅР°С‡РёРЅР°РµС‚СЃСЏ РєСЂР°СЃРёРІРѕ: СЃ РјС‹СЃР»Рё, Р° РЅРµ СЃ С€СѓРјР°.',
    ]
    : [
        'The first note here usually comes from the person who read between the lines.',
        'If the article deserves more than a silent nod, the comment box is the right place.',
        'It is still quiet below. A precise observation would improve that immediately.',
        'Sometimes one comment is more useful than an extra paragraph in the piece.',
        'The editors already had their say. Now the interesting part is yours.',
        'If something is missing from the article, this is where the missing line belongs.',
        'No one has argued with the piece yet. That feels suspiciously convenient.',
        'Some of the best parts of an issue appear in the comments, not the draft.',
        'If you have a practical objection, an extra angle or a quiet insight, this is where it lands.',
        'A comment can be shorter than the article and still move it further.',
        'A good thread often starts with a careful вЂњyes, butвЂќ.',
        'If the piece touched a professional nerve, leave a trace under it.',
        'What matters here is not applause, but a sharper addition.',
        'A strong comment works like an editorвЂ™s pencil in the margin.',
        'You can leave the question that is awkward to say out loud. Those are often the useful ones.',
        'If you reached the end and disagreed with at least one turn, this is your opening line.',
        'Silence under a piece looks elegant, but it is rarely useful.',
        'The thread is still missing the comment that turns theory into practice.',
        'The liveliest articles rarely end with a period. They continue below.',
        'If the piece triggered an internal вЂњyes, butвЂ¦вЂќ, that is already a strong first comment.',
        'Comments are not here for politeness. They are here to continue the thought.',
        'A sharp question under a piece can matter more than a confident conclusion.',
        'If you want to add context, an example or a calm disagreement, the space is ready.',
        'Sometimes the more honest version of a piece is written in the thread below it.',
        'Write the first note as if you are extending the column, not merely replying to it.',
        'There is generous empty space here for the first intelligent interruption.',
        'If the article hit the nerve, the comment can hit the point.',
        'Some discussions begin with the sentence the author hoped someone would add.',
        'The first comment does not need to be loud. It only needs to be exact.',
        'No one has asked the obvious question yet. That makes it available.',
        'If the article sounds too certain, the thread is a good place for a counterweight.',
        'The best discussions under articles rarely wait for permission.',
        'There is still no insight, no objection, no quiet editorial whisper below. That can change.',
        'A comment is a fast way to show the piece did not land in a vacuum.',
        'If the article has a second layer, it is often opened in the thread, not in the copy.',
        'Share the thought you would usually say half-quietly in a work chat.',
        'If the text wants a real-life example, this is where it should arrive.',
        'A precise вЂњwe saw it differentlyвЂќ can build a better thread than a long essay.',
        'The empty thread is a rare luxury: your first note gets to set the tone.',
        'If you liked the piece, support it with an argument. If not, even better.',
        'Comments work best here when they carry less noise and more craft.',
        'There is still no line below this piece that changes how it reads.',
        'Sometimes the smartest compliment to an article is a sharper comment under it.',
        'The first reply has the rare privilege of defining the mood of the thread.',
        'If you know the detail the piece only brushed against, this is the place for it.',
        'The article is still missing a human trace under it. You can leave the first one.',
        'This silence is a little too tidy. LetвЂ™s add something useful to it.',
        'The editors prefer discussions that begin with a thought, not a performance.',
        'If your backstage version of this topic is stronger than the polished one, bring it in.',
        'A stylish thread often starts with a single exact sentence.',
    ];
$portalEmptyCta = (string)$portalEmptyCtas[array_rand($portalEmptyCtas)];
$portalCommentTree = static function (array $nodes, int $depth = 0) use (&$portalCommentTree, $portalUser, $portalSections, $portalCsrf, $portalCurrentUrl, $t): void {
    foreach ($nodes as $node) {
        $commentId = (int)($node['id'] ?? 0);
        $author = trim((string)($node['display_name'] ?? $node['username'] ?? 'Member'));
        $avatar = trim((string)($node['avatar_src'] ?? ''));
        $profileUrl = trim((string)($node['profile_url'] ?? '/member/'));
        $section = trim((string)($node['section_code'] ?? 'discussion'));
        $sectionLabel = (string)($portalSections[$section] ?? $section);
        $time = trim((string)($node['created_at'] ?? ''));
        $commentScore = (int)($node['rating_score'] ?? 0);
        $commentUp = (int)($node['votes_up'] ?? 0);
        $commentDown = (int)($node['votes_down'] ?? 0);
        $currentVote = (int)($node['current_user_vote'] ?? 0);
        $userScore = (int)($node['comment_rating'] ?? 0);
        $rankLabel = (string)($node['rank_meta']['label'] ?? $t('РЈС‡Р°СЃС‚РЅРёРє РѕР±СЃСѓР¶РґРµРЅРёСЏ', 'Discussion member'));
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
                            <button class="pcmt-reply" type="button" data-comment-reply="<?= $commentId ?>" data-comment-author="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('РћС‚РІРµС‚РёС‚СЊ', 'Reply'), ENT_QUOTES, 'UTF-8') ?></button>
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
.pcmt-summary{display:flex;flex-wrap:wrap;gap:14px 18px;align-items:center;margin-top:2px}
.pcmt-summary-item{display:inline-flex;align-items:center;gap:8px;color:var(--shell-muted);font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.pcmt-summary-item strong{font:700 1rem/1 "Space Grotesk","Sora",sans-serif;color:var(--shell-text);letter-spacing:0}
.pcmt-summary-glyph{display:inline-flex;align-items:center;justify-content:center;color:#f4d56b;font-size:13px;line-height:1}
.pcmt-summary-shell{margin-bottom:18px}
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
.pcmt-compose{display:grid;gap:12px;margin-bottom:18px}
.pcmt-compose-tease{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-compose-shell .pcmt-form{display:none}
.pcmt-compose-shell.is-open .pcmt-form{display:grid}
.pcmt-form{display:grid;gap:12px}
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
@media (max-width:980px){.pcmt-auth-form{grid-template-columns:1fr}.pcmt-auth-form .pcmt-field-full{grid-column:auto}}
@media (max-width:720px){.pcmt{padding:20px 16px}.pcmt-form-foot,.pcmt-head,.pcmt-node-head,.pcmt-compose-tease{align-items:flex-start}.pcmt-children{margin-left:14px}.pcmt-node-actions{justify-items:start}}
</style>
<section class="pcmt" id="article-comments" data-portal-auth="<?= $portalIsLoggedIn ? '1' : '0' ?>" data-content-type="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>" data-content-id="<?= $portalContentId ?>">
    <div class="pcmt-head">
        <div class="pcmt-copy">
            <span class="pcmt-kicker"><?= htmlspecialchars($t('Р РµРґР°РєС†РёРѕРЅРЅРѕРµ РѕР±СЃСѓР¶РґРµРЅРёРµ', 'Editorial discussion'), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($t('РџРѕРґ С‚РµРєСЃС‚РѕРј РЅР°С‡РёРЅР°РµС‚СЃСЏ Р¶РёРІР°СЏ С‡Р°СЃС‚СЊ СЂР°Р·РіРѕРІРѕСЂР°', 'The live part of the conversation starts below'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t('Р—РґРµСЃСЊ РѕР±С‹С‡РЅРѕ РїРѕСЏРІР»СЏСЋС‚СЃСЏ РЅР°Р±Р»СЋРґРµРЅРёСЏ, РІСЃС‚СЂРµС‡РЅС‹Рµ РёСЃС‚РѕСЂРёРё, РЅРµСЃРѕРіР»Р°СЃРёСЏ, С‚РёС…РёРµ СѓС‚РѕС‡РЅРµРЅРёСЏ Рё С‚Рµ РґРµС‚Р°Р»Рё, СЂР°РґРё РєРѕС‚РѕСЂС‹С… РјР°С‚РµСЂРёР°Р» С…РѕС‡РµС‚СЃСЏ РїРµСЂРµС‡РёС‚Р°С‚СЊ СѓР¶Рµ РІРјРµСЃС‚Рµ СЃ РґСЂСѓРіРёРјРё. Р•СЃР»Рё РµСЃС‚СЊ СЃРІРѕР№ РѕРїС‹С‚, РІРѕРїСЂРѕСЃ РёР»Рё Р°РєРєСѓСЂР°С‚РЅРѕРµ РІРѕР·СЂР°Р¶РµРЅРёРµ вЂ” СЌС‚Рѕ РєР°Рє СЂР°Р· С‚Рѕ РјРµСЃС‚Рѕ.', 'This is where lived detail, disagreements, useful follow-ups and the human part of the story usually show up. If you have experience, a question or a thoughtful counterpoint, this is the right place for it.'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="pcmt-summary" aria-label="<?= htmlspecialchars($t('РЎРІРѕРґРєР° РѕР±СЃСѓР¶РґРµРЅРёСЏ', 'Discussion summary'), ENT_QUOTES, 'UTF-8') ?>">
                <span class="pcmt-summary-item"><span class="pcmt-summary-glyph" aria-hidden="true">◉</span><strong><?= (int)$portalCommentTotal ?></strong></span>
                <span class="pcmt-summary-item"><span class="pcmt-summary-glyph" aria-hidden="true">↕</span><strong><?= (int)$portalCommentScoreTotal ?></strong></span>
            </div>
        </div>
    </div>

    <?php if (!empty($portalFlash['message'])): ?>
        <div class="pcmt-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$portalIsLoggedIn): ?>
        <div class="pcmt-summary-shell">
            <div class="pcmt-auth-shell" id="pcmt-auth-shell">
                <div class="pcmt-auth-tease">
                    <p><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Р’РѕР№РґРёС‚Рµ, С‡С‚РѕР±С‹ РѕС‚РІРµС‚РёС‚СЊ, РїРѕРґРґРµСЂР¶Р°С‚СЊ С‡СѓР¶СѓСЋ РјС‹СЃР»СЊ РёР»Рё РїСЂРёРЅРµСЃС‚Рё РІ СЂР°Р·РіРѕРІРѕСЂ СЃРІРѕР№ РѕРїС‹С‚.', 'Sign in to reply, support a point or bring your own experience into the thread.') : $t('Р’РѕР№РґРёС‚Рµ, С‡С‚РѕР±С‹ РѕСЃС‚Р°РІРёС‚СЊ РїРµСЂРІСѓСЋ СЂРµРїР»РёРєСѓ Рё РѕС‚РєСЂС‹С‚СЊ СЌС‚Рѕ РѕР±СЃСѓР¶РґРµРЅРёРµ.', 'Sign in to leave the first note and open this discussion.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="pcmt-guest-cta">
                        <button class="pcmt-btn" type="button" data-comment-auth-open><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Р’РѕР№С‚Рё Рё РѕР±СЃСѓРґРёС‚СЊ', 'Join the discussion') : $t('РћСЃС‚Р°РІРёС‚СЊ РїРµСЂРІС‹Р№ РєРѕРјРјРµРЅС‚Р°СЂРёР№', 'Leave the first comment'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </div>
                <form class="pcmt-auth-form" method="post">
                    <input type="hidden" name="action" value="public_portal_register">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
                    <div><span class="pcmt-auth-kicker"><?= htmlspecialchars($t('Р‘С‹СЃС‚СЂС‹Р№ РІС…РѕРґ РІ РѕР±СЃСѓР¶РґРµРЅРёРµ', 'Quick access to discussion'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div><input type="text" name="username" placeholder="<?= htmlspecialchars($t('Р›РѕРіРёРЅ', 'Login'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full"><input type="password" name="password" placeholder="<?= htmlspecialchars($t('РџР°СЂРѕР»СЊ РѕС‚ 8 СЃРёРјРІРѕР»РѕРІ', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full pcmt-captcha">
                        <div><strong><?= htmlspecialchars((string)($portalCaptcha[$portalIsRu ? 'prompt_ru' : 'prompt_en'] ?? $t('РЎР»РѕР¶РёС‚Рµ РґРІР° С‡РёСЃР»Р° СЂСЏРґРѕРј СЃРѕ Р·РЅР°РєР°РјРё', 'Add the two numbers next to the symbols')), ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="pcmt-captcha-code">
                            <span><?= htmlspecialchars((string)($portalCaptcha['glyph_left'] ?? 'в—§'), ENT_QUOTES, 'UTF-8') ?><?= (int)($portalCaptcha['left'] ?? 0) ?></span>
                            <span>+</span>
                            <span><?= htmlspecialchars((string)($portalCaptcha['glyph_right'] ?? 'в—©'), ENT_QUOTES, 'UTF-8') ?><?= (int)($portalCaptcha['right'] ?? 0) ?></span>
                        </div>
                        <input type="text" name="captcha_answer" placeholder="<?= htmlspecialchars($t('РћС‚РІРµС‚', 'Answer'), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="pcmt-field-full pcmt-guest-cta">
                        <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('РЎРѕР·РґР°С‚СЊ Р°РєРєР°СѓРЅС‚ Рё РїСЂРѕРґРѕР»Р¶РёС‚СЊ', 'Create account and continue'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="pcmt-compose">
            <div class="pcmt-compose-tease">
                <p><?= htmlspecialchars($t('Р•СЃР»Рё С…РѕС‡РµС‚СЃСЏ РїСЂРѕРґРѕР»Р¶РёС‚СЊ РјС‹СЃР»СЊ, Р·Р°РґР°С‚СЊ РІСЃС‚СЂРµС‡РЅС‹Р№ РІРѕРїСЂРѕСЃ РёР»Рё РѕСЃС‚Р°РІРёС‚СЊ СЃРІРѕСЋ РїСЂР°РєС‚РёС‡РµСЃРєСѓСЋ РїРѕРјРµС‚РєСѓ, РѕС‚РєСЂРѕР№С‚Рµ С„РѕСЂРјСѓ Рё РґРѕР±Р°РІСЊС‚Рµ СЂРµРїР»РёРєСѓ.', 'If you want to continue the point, ask a follow-up question or leave a practical note, open the form and add your reply.'), ENT_QUOTES, 'UTF-8') ?></p>
                <button class="pcmt-btn" type="button" data-comment-open><?= htmlspecialchars($t('РћСЃС‚Р°РІРёС‚СЊ РєРѕРјРјРµРЅС‚Р°СЂРёР№', 'Leave a comment'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
            <div class="pcmt-compose-shell" id="pcmt-compose-shell">
                <form class="pcmt-form" method="post" id="pcmt-comment-form">
                    <input type="hidden" name="action" value="public_portal_comment">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
                    <input type="hidden" name="parent_id" value="0" id="pcmt-parent-id">
                    <select name="section_code" class="pcmt-section-select">
                        <?php foreach ($portalSections as $sectionCode => $sectionLabel): ?>
                            <option value="<?= htmlspecialchars((string)$sectionCode, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$sectionLabel, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pcmt-toolbar">
                        <button type="button" data-wrap="**" data-target="pcmt-text">B</button>
                        <button type="button" data-wrap="*" data-target="pcmt-text">I</button>
                        <button type="button" data-wrap="~~" data-target="pcmt-text">S</button>
                        <button type="button" data-link-target="pcmt-text"><?= htmlspecialchars($t('РЎСЃС‹Р»РєР°', 'Link'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="button" data-prefix="рџ™‚ " data-target="pcmt-text">рџ™‚</button>
                        <button type="button" data-prefix="рџ”Ґ " data-target="pcmt-text">рџ”Ґ</button>
                        <button type="button" data-prefix="рџ§  " data-target="pcmt-text">рџ§ </button>
                        <button type="button" data-prefix="вљ™пёЏ " data-target="pcmt-text">вљ™пёЏ</button>
                    </div>
                    <div class="pcmt-reply-state" id="pcmt-reply-state">
                        <span id="pcmt-reply-text"></span>
                        <button class="pcmt-btn-ghost" type="button" data-comment-reply-clear><?= htmlspecialchars($t('РЎРЅСЏС‚СЊ РѕС‚РІРµС‚', 'Clear reply'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                    <textarea id="pcmt-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('РџРѕРґРµР»РёС‚РµСЃСЊ СЃРІРѕРёРј РЅР°Р±Р»СЋРґРµРЅРёРµРј, РІРѕРїСЂРѕСЃРѕРј, РЅРµСЃРѕРіР»Р°СЃРёРµРј РёР»Рё РїСЂР°РєС‚РёС‡РµСЃРєРѕР№ РёСЃС‚РѕСЂРёРµР№ РїРѕ С‚РµРјРµ РјР°С‚РµСЂРёР°Р»Р°', 'Share an observation, question, disagreement or practical story related to the article'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                    <div class="pcmt-form-foot">
                        <span class="pcmt-node-meta"><span><?= htmlspecialchars($t('Р Р°Р·СЂРµС€РµРЅС‹: Р¶РёСЂРЅС‹Р№, РєСѓСЂСЃРёРІ, РїРµСЂРµС‡РµСЂРєРЅСѓС‚С‹Р№, СЃСЃС‹Р»РєР°, СЌРјРѕРґР·Рё', 'Supports bold, italic, strike, links and emoji'), ENT_QUOTES, 'UTF-8') ?></span></span>
                        <div class="pcmt-guest-cta">
                            <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('РћРїСѓР±Р»РёРєРѕРІР°С‚СЊ РєРѕРјРјРµРЅС‚Р°СЂРёР№', 'Publish comment'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($portalComments)): ?>
        <div class="pcmt-list"><?php $portalCommentTree($portalComments); ?></div>
    <?php else: ?>
        <div class="pcmt-empty"><?= htmlspecialchars($t('РџРѕРґ СЌС‚РёРј РјР°С‚РµСЂРёР°Р»РѕРј РїРѕРєР° С‚РёС…Рѕ. РњРѕР¶РЅРѕ РѕСЃС‚Р°РІРёС‚СЊ РїРµСЂРІСѓСЋ СЂРµРїР»РёРєСѓ Рё РѕС‚РєСЂС‹С‚СЊ РѕР±СЃСѓР¶РґРµРЅРёРµ.', 'It is still quiet under this article. You can leave the first note and open the thread.'), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
</section>
<script>
(function () {
    function bindPortalComments(root) {
        if (!root || root.dataset.bound === '1') {
            return;
        }
        root.dataset.bound = '1';
        var emptyCtas = <?= json_encode(array_values($portalEmptyCtas), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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

        function hasPortalSessionCookie() {
            return /(?:^|;\s*)PHPSESSID=/.test(document.cookie || '');
        }

        function refreshCommentsBlock(callback) {
            var contentType = root.getAttribute('data-content-type') || 'examples';
            var contentId = root.getAttribute('data-content-id') || '0';
            var url = new URL(window.location.href);
            url.searchParams.set('portal_comments_block', '1');
            url.searchParams.set('content_type', contentType);
            url.searchParams.set('content_id', contentId);
            url.searchParams.set('_ts', String(Date.now()));
            withLoading(true);
            fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                if (payload && payload.html) {
                    replaceCommentsHtml(payload.html);
                    if (typeof callback === 'function') {
                        callback(!!payload.logged_in);
                    }
                    return;
                }
                if (typeof callback === 'function') {
                    callback(false);
                }
            }).catch(function () {
                if (typeof callback === 'function') {
                    callback(false);
                }
            }).finally(function () {
                withLoading(false);
            });
        }

        function openAuth() {
            var authShell = root.querySelector('#pcmt-auth-shell');
            if (authShell) {
                authShell.classList.add('is-open');
            }
        }

        function openCompose() {
            var shell = root.querySelector('#pcmt-compose-shell');
            var textarea = root.querySelector('#pcmt-text');
            if (shell) {
                shell.classList.add('is-open');
            }
            if (textarea) {
                window.setTimeout(function () { textarea.focus(); }, 40);
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

        var emptyBox = root.querySelector('.pcmt-empty');
        if (emptyBox && emptyCtas && emptyCtas.length) {
            emptyBox.textContent = emptyCtas[Math.floor(Math.random() * emptyCtas.length)];
        }

        root.addEventListener('click', function (event) {
            var authOpen = event.target.closest('[data-comment-auth-open]');
            if (authOpen) {
                if ((root.getAttribute('data-portal-auth') || '0') === '1') {
                    openCompose();
                    return;
                }
                if (hasPortalSessionCookie()) {
                    refreshCommentsBlock(function (loggedIn) {
                        if (loggedIn) {
                            var refreshedRoot = document.getElementById('article-comments');
                            if (refreshedRoot) {
                                var composeButton = refreshedRoot.querySelector('[data-comment-open]');
                                if (composeButton) {
                                    composeButton.click();
                                    return;
                                }
                            }
                        }
                        openAuth();
                    });
                    return;
                }
                openAuth();
                return;
            }

            var composeOpen = event.target.closest('[data-comment-open]');
            if (composeOpen) {
                openCompose();
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
                var text = window.prompt('РўРµРєСЃС‚ СЃСЃС‹Р»РєРё') || url;
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
                if (parentInput && replyState && replyText) {
                    openCompose();
                    parentInput.value = replyButton.getAttribute('data-comment-reply') || '0';
                    replyText.textContent = 'в†і ' + (replyButton.getAttribute('data-comment-author') || '');
                    replyState.classList.add('is-visible');
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
