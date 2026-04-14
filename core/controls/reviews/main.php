<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$portalHelper = DIR . 'core/controls/examples/_portal_section.php';
if (!is_file($portalHelper)) {
    $ModelPage['reviews'] = ['error' => 'reviews dependencies not found'];
    return;
}

require_once $portalHelper;

portal_section_build($FRMWRK, $ModelPage, 'reviews', [
    'issue_kicker_ru' => 'REVIEWS / ЦПАЛЬНЯ',
    'issue_kicker_en' => 'REVIEWS / CPALNYA',
    'hero_title_ru' => 'Обзоры, сравнения и подборки по арбитражному стеку',
    'hero_title_en' => 'Reviews, comparisons and shortlists for affiliate stacks',
    'hero_description_ru' => "Раздел для реальных обзоров по арбитражной инфраструктуре: партнеркам, клоакам, трекерам, ИИ-генераторам, серверам, доменным поставщикам, proxy-сеткам, anti-detect браузерам и другим рабочим инструментам.\n\nЗдесь важны не общие описания, а прикладная разница между вариантами: кому что подходит, где скрытые риски, у кого сильнее саппорт, где узкое место в масштабировании и какие компромиссы всплывают на практике.\n\nВ обзорах допустимы ссылки на сторонние ресурсы, если они помогают собрать честное сравнение и полезную подборку.",
    'hero_description_en' => "A section for real reviews of affiliate infrastructure: partner programs, cloakers, trackers, AI generators, servers, domain vendors, proxy networks, anti-detect browsers and the rest of the working stack.\n\nThe point is not generic description but practical differences: who each option fits, where the hidden risks are, how support behaves, what breaks at scale and which tradeoffs matter in production.\n\nExternal links are allowed when they make the comparison more useful and honest.",
    'issue_title_ru' => 'Радар стеков и поставщиков',
    'issue_title_en' => 'Stack and vendor radar',
    'issue_subtitle_ru' => "Сравнительные обзоры, benchmark-материалы и shortlist-подборки по реальным инструментам для арбитража трафика.\n\nФокус на том, что помогает выбирать стек и поставщиков без лишнего шума: критерии оценки, реальный fit под сценарий, tradeoff, ограничения и выводы для команды.",
    'issue_subtitle_en' => "Comparison reviews, benchmark pieces and shortlist curation for real affiliate tools and providers.\n\nFocused on what helps teams choose stacks without noise: evaluation criteria, real fit by use case, tradeoffs, limits and practical recommendations.",
    'hero_note_ru' => 'reviews, benchmarks, shortlists, vendor fit',
    'hero_note_en' => 'reviews, benchmarks, shortlists, vendor fit',
    'fallback_article_title_ru' => 'Обзор',
    'fallback_article_title_en' => 'Review',
    'selected_description_ru' => 'Материал раздела с обзорами, сравнениями и подборками инструментов и поставщиков для арбитража.',
    'selected_description_en' => 'A section article with reviews, comparisons and curated shortlists for affiliate tools and providers.',
    'list_title_ru' => 'Обзоры для арбитража: инструменты, сервисы и поставщики',
    'list_title_en' => 'Affiliate reviews: tools, services and vendors',
    'list_description_ru' => 'Реальные обзоры, сравнения и подборки по партнеркам, клоакам, трекерам, AI-инструментам, серверам, доменам и инфраструктуре арбитража.',
    'list_description_en' => 'Real reviews, comparisons and shortlists for partner programs, cloakers, trackers, AI tools, servers, domains and affiliate infrastructure.',
    'article_section' => 'Reviews',
]);
