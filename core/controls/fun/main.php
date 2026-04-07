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
    'issue_kicker_ru' => 'FUN / ЦПАЛЬНЯ',
    'issue_kicker_en' => 'FUN / CPALNYA',
    'hero_title_ru' => 'Отдых, фан и нишевой абсурд',
    'hero_title_en' => 'Fun, satire and niche absurdity',
    'hero_description_ru' => "Зона для тех моментов, когда индустрия уже слишком серьезно относится к себе. Здесь живут сатирические памфлеты, юмористические обзоры, мем-редакционка, ироничные портреты команд и тексты, которые знают внутренний язык affiliate-операционки лучше любого внешнего наблюдателя.\n\nЭто не уход от темы, а другой способ говорить о ней. Через шутку, гротеск и мемный поворот здесь становится видно то, что в обычном формате часто прячется: суеверия фарма, ритуалы баеров, нелепость postback-хаоса, драму модерации, усталость ночных смен и вечную войну со сгоревшими креативами, которые почему-то всегда догорают в самый неподходящий момент.\n\nФан в этой нише работает как внутренний язык выживания. Команды смеются не потому, что им легко, а потому что иначе невозможно долго держать темп там, где каждый день приносит новый сбой, новую легенду, нового виноватого и новую смешную, но слишком узнаваемую боль.\n\nРаздел нужен как передышка, которая не выходит из контекста. Он остается внутри рынка, внутри его фольклора, внутри его темпа и смеется вместе с теми, кто действительно живет в этом ритме, а не наблюдает его с безопасного расстояния.",
    'hero_description_en' => "A zone for those moments when the industry starts taking itself too seriously. This is where satirical pamphlets, humorous reviews, meme editorials, team portraits and texts fluent in affiliate operations live.\n\nThis is not an escape from the topic but another way to talk about it. Through jokes, exaggeration and meme logic, the section reveals what standard formats often hide: farm superstitions, buyer rituals, postback absurdity, moderation drama and the eternal war with burned-out creatives.\n\nA breather that stays inside the niche and laughs with the people who actually live in this tempo.",
    'issue_title_ru' => 'Фан-зона и мем-редакция',
    'issue_title_en' => 'Fun desk and meme editorial',
    'issue_subtitle_ru' => "Памфлеты, пародии, мем-арт, юмористические обзоры и игровые форматы про арбитраж, медиабаинг, фарм и backstage-фольклор команд.\n\nНе общая развлекаловка, а внутренняя культурная зона ниши: смешная ровно потому, что слишком узнаваемая.\n\nЗдесь мемы работают как хроника, а ирония как еще один способ зафиксировать реальность рынка, где абсурд давно стал частью ежедневной операционки.",
    'issue_subtitle_en' => "Pamphlets, parodies, meme-art, humorous reviews and playful formats about arbitrage, media buying, farm routines and team backstage folklore.\n\nNot generic entertainment but the niche's own cultural zone: funny precisely because it feels painfully familiar.",
    'hero_note_ru' => 'мемы, сатира, backstage, niche folklore',
    'hero_note_en' => 'memes, satire, backstage, niche folklore',
    'fallback_article_title_ru' => 'Фан-материал',
    'fallback_article_title_en' => 'Fun piece',
    'selected_description_ru' => 'Развлекательный материал про арбитраж, команды и внутренний фольклор ниши.',
    'selected_description_en' => 'An entertainment piece about arbitrage, teams and niche folklore.',
    'list_title_ru' => 'Отдых / фан для affiliate-команд',
    'list_title_en' => 'Fun for affiliate teams',
    'list_description_ru' => 'Сатирические обзоры, мем-редакционка и развлекательные материалы про арбитраж, медиабаинг, фарм и креативы.',
    'list_description_en' => 'Satirical reviews, meme editorials and entertainment formats about arbitrage, media buying, farm and creatives.',
    'article_section' => 'Fun',
]);
