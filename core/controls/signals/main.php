<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$portalHelper = DIR . 'core/controls/examples/_portal_section.php';
if (!is_file($portalHelper)) {
    $ModelPage['signals'] = ['error' => 'signals dependencies not found'];
    return;
}

require_once $portalHelper;

portal_section_build($FRMWRK, $ModelPage, 'signals', [
    'issue_kicker_ru' => 'SIGNALS / Р¦РџРђР›Р¬РќРЇ',
    'issue_kicker_en' => 'SIGNALS / CPALNYA',
    'hero_title_ru' => 'РџРѕР»РёС‚РёРєР°, РЅРѕРІРѕСЃС‚Рё Рё СЃРёРіРЅР°Р»С‹ СЂС‹РЅРєР°',
    'hero_title_en' => 'Policy, news and market signals',
    'hero_description_ru' => \"Раздел для тех, кто следит не только за цифрами открута, но и за тем, как меняется сама сцена: policy-повороты платформ, регуляторные движения в СНГ, новые ограничения, payment-сдвиги и сигналы, которые первыми бьют по affiliate-операционке.\n\nМы собираем новости не ради шума, а ради ориентира: кого апдейт заденет первым, где начнет шататься устойчивость и какие решения команде нужно принять раньше, чем рынок вынудит реагировать в спешке.\",
    'hero_description_en' => "A section for people who watch not only traffic numbers, but the stage itself: platform policy turns, CIS regulatory moves, new restrictions, payment shifts, and market signals that hit affiliate operations first.\n\nWe collect news here not for noise, but for orientation. What is really changing, who gets hit first, which geos and sources start wobbling, and where the team needs to adapt before it becomes a universal pain point.\n\nThis is not a news feed but an editorial radar for buyers, operators, farmers and everyone making decisions under platform pressure.",
    'issue_title_ru' => 'Р Р°РґР°СЂ РїР»Р°С‚С„РѕСЂРј Рё СЂРµРіСѓР»СЏС‚РѕСЂРёРєРё',
    'issue_title_en' => 'Platform and regulation radar',
    'issue_subtitle_ru' => \"Meta, TikTok, Telegram, privacy-апдейты, compliance, СНГ-регуляторика и мировые сдвиги, которые меняют правила игры быстрее, чем успевают устаревать вчерашние связки.\n\nЗдесь важны не формулировки пресс-релизов, а то, как новости превращаются в реальный операционный риск или в новое окно возможностей.\",
    'issue_subtitle_en' => "Meta, TikTok, Telegram, privacy updates, compliance, CIS regulation and global shifts that change the rules faster than yesterday's bundles can expire.\n\nWhat matters here is not the press-release wording, but how news becomes operational risk or, just as often, a new opening.",
    'hero_note_ru' => 'policy, РЅРѕРІРѕСЃС‚Рё, regulation, enforcement',
    'hero_note_en' => 'policy, news, regulation, enforcement',
    'fallback_article_title_ru' => 'РЎРёРіРЅР°Р»',
    'fallback_article_title_en' => 'Signal',
    'selected_description_ru' => 'РњР°С‚РµСЂРёР°Р» СЂР°Р·РґРµР»Р° РїСЂРѕ policy, РЅРѕРІРѕСЃС‚Рё Рё СЃРёРіРЅР°Р»С‹ СЂС‹РЅРєР° РІ affiliate-РѕРїРµСЂР°С†РёРѕРЅРєРµ.',
    'selected_description_en' => 'A section article about policy, news and market signals in affiliate operations.',
    'list_title_ru' => 'РџРѕРІРµСЃС‚РєР° Р°СЂР±РёС‚СЂР°Р¶Р°: policy Рё РЅРѕРІРѕСЃС‚Рё',
    'list_title_en' => 'Affiliate agenda: policy and news',
    'list_description_ru' => 'РџРѕР»РёС‚РёРєР° РїР»Р°С‚С„РѕСЂРј, СЂРµРіСѓР»СЏС‚РѕСЂРЅС‹Рµ РґРІРёР¶РµРЅРёСЏ Рё СЂС‹РЅРѕС‡РЅС‹Рµ СЃРёРіРЅР°Р»С‹ РґР»СЏ Р°СЂР±РёС‚СЂР°Р¶Р° С‚СЂР°С„РёРєР° РІ РЎРќР“ Рё РіР»РѕР±Р°Р»СЊРЅРѕР№ РїСЂР°РєС‚РёРєРµ.',
    'list_description_en' => 'Platform policy, regulatory moves and market signals for traffic arbitrage across CIS and global practice.',
    'article_section' => 'Signals',
]);

