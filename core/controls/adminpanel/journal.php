<?php
$adminExamplesSection = 'journal';
$adminExamplesTitle = 'Journal';
$adminExamplesBackUrl = '/adminpanel/journal/';
$adminExamplesExtraActions = [
    ['label' => 'Issue Settings', 'href' => '/adminpanel/journal-issue/', 'class' => 'btn-outline-secondary'],
    ['label' => 'Open Journal', 'href' => '/journal/', 'class' => 'btn-outline-primary', 'target' => '_blank'],
];
require __DIR__ . '/examples.php';
