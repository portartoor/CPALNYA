<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$portalHelper = DIR . 'core/controls/examples/_portal_section.php';
if (!is_file($portalHelper)) {
    $ModelPage['fun'] = ['error' => 'fun dependencies not found'];
    return;
}

require_once $portalHelper;

portal_section_build($FRMWRK, $ModelPage, 'fun', [
    'issue_kicker_ru' => 'FUN / Р¦РџРђР›Р¬РќРЇ',
    'issue_kicker_en' => 'FUN / CPALNYA',
    'hero_title_ru' => 'РћС‚РґС‹С…, С„Р°РЅ Рё РЅРёС€РµРІРѕР№ Р°Р±СЃСѓСЂРґ',
    'hero_title_en' => 'Fun, satire and niche absurdity',
    'hero_description_ru' => "Зона для тех моментов, когда индустрия слишком серьезно относится к себе. Здесь живут сатирические тексты, мем-редакционка и ироничные материалы, которые знают внутренний язык affiliate-операционки лучше любого внешнего наблюдателя.\n\nЭто не уход от темы, а другой способ говорить о ней: через юмор здесь лучше видно суеверия фарма, ритуалы баеров, абсурд postback-хаоса и драму модерации, слишком узнаваемую для любой команды.",
    'hero_description_en' => "A zone for those moments when the industry starts taking itself too seriously. This is where satirical pamphlets, humorous reviews, meme editorials, team portraits and texts fluent in affiliate operations live.\n\nThis is not an escape from the topic but another way to talk about it. Through jokes, exaggeration and meme logic, the section reveals what standard formats often hide: farm superstitions, buyer rituals, postback absurdity, moderation drama and the eternal war with burned-out creatives.\n\nA breather that stays inside the niche and laughs with the people who actually live in this tempo.",
    'issue_title_ru' => 'Р¤Р°РЅ-Р·РѕРЅР° Рё РјРµРј-СЂРµРґР°РєС†РёСЏ',
    'issue_title_en' => 'Fun desk and meme editorial',
    'issue_subtitle_ru' => "Памфлеты, пародии, мем-арт и ироничные форматы про арбитраж, медиабаинг, фарм и backstage-фольклор команд.\n\nНе общая развлекаловка, а внутренняя культурная зона ниши: смешная именно потому, что слишком узнаваемая.",
    'issue_subtitle_en' => "Pamphlets, parodies, meme-art, humorous reviews and playful formats about arbitrage, media buying, farm routines and team backstage folklore.\n\nNot generic entertainment but the niche's own cultural zone: funny precisely because it feels painfully familiar.",
    'hero_note_ru' => 'РјРµРјС‹, СЃР°С‚РёСЂР°, backstage, niche folklore',
    'hero_note_en' => 'memes, satire, backstage, niche folklore',
    'fallback_article_title_ru' => 'Р¤Р°РЅ-РјР°С‚РµСЂРёР°Р»',
    'fallback_article_title_en' => 'Fun piece',
    'selected_description_ru' => 'Р Р°Р·РІР»РµРєР°С‚РµР»СЊРЅС‹Р№ РјР°С‚РµСЂРёР°Р» РїСЂРѕ Р°СЂР±РёС‚СЂР°Р¶, РєРѕРјР°РЅРґС‹ Рё РІРЅСѓС‚СЂРµРЅРЅРёР№ С„РѕР»СЊРєР»РѕСЂ РЅРёС€Рё.',
    'selected_description_en' => 'An entertainment piece about arbitrage, teams and niche folklore.',
    'list_title_ru' => 'РћС‚РґС‹С… / С„Р°РЅ РґР»СЏ affiliate-РєРѕРјР°РЅРґ',
    'list_title_en' => 'Fun for affiliate teams',
    'list_description_ru' => 'РЎР°С‚РёСЂРёС‡РµСЃРєРёРµ РѕР±Р·РѕСЂС‹, РјРµРј-СЂРµРґР°РєС†РёРѕРЅРєР° Рё СЂР°Р·РІР»РµРєР°С‚РµР»СЊРЅС‹Рµ РјР°С‚РµСЂРёР°Р»С‹ РїСЂРѕ Р°СЂР±РёС‚СЂР°Р¶, РјРµРґРёР°Р±Р°РёРЅРі, С„Р°СЂРј Рё РєСЂРµР°С‚РёРІС‹.',
    'list_description_en' => 'Satirical reviews, meme editorials and entertainment formats about arbitrage, media buying, farm and creatives.',
    'article_section' => 'Fun',
]);

