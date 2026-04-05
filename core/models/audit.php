<?php

if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$isRuAudit = (bool)preg_match('/\.ru$/', strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')));
$ModelPage = array_merge([
    'title' => $isRuAudit ? 'Проверка сайта без AI | Технический и SEO-аудит' : 'Non-AI Website Audit | Technical and SEO Report',
    'description' => $isRuAudit
        ? 'Структурированный аудит сайта: индексация, метатеги, заголовки, безопасность, robots.txt, sitemap.xml, скорость ответа и рекомендации.'
        : 'Structured website audit: indexability, metadata, headings, security headers, robots.txt, sitemap.xml, response time and actions.',
    'keywords' => $isRuAudit
        ? 'аудит сайта, seo аудит, robots.txt, sitemap.xml, технический анализ сайта, проверка сайта'
        : 'website audit, technical seo audit, robots.txt checker, sitemap.xml checker, non-ai site analysis',
], $ModelPage);

