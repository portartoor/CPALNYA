<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$host = preg_replace('/^www\./', '', $host);
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$isRu = (bool)preg_match('/\.ru$/', (string)$host);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');

if (empty($ModelPage['title'])) {
    $ModelPage['title'] = $isRu ? 'Контакты' : 'Contact';
}
if (empty($ModelPage['description'])) {
    $ModelPage['description'] = $isRu
        ? 'Форма связи для запросов по услугам, проектам и сотрудничеству.'
        : 'Contact form for services, projects and collaboration requests.';
}
if (empty($ModelPage['canonical'])) {
    $ModelPage['canonical'] = $baseUrl . '/contact/';
}
