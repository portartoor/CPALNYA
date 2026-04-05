<?php
$__mirrorSite = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple')));
$__templateFile = __DIR__ . '/templates/main/' . $__mirrorSite . '/main.cases.php';
if (is_file($__templateFile)) {
    echo '<style>
body.ui-tone-dark .cases-simple{color:#dbe7f6}
body.ui-tone-dark .cases-simple-hero{border-color:#27415d;background:linear-gradient(145deg,#0f1d2e,#12263d)}
body.ui-tone-dark .cases-simple-hero h1{color:#e7f0fb}
body.ui-tone-dark .cases-simple-hero p{color:#9cb3cf}
body.ui-tone-dark .cases-simple-card,
body.ui-tone-dark .cases-simple-detail,
body.ui-tone-dark .cases-seo-block{border-color:#2b4662;background:#101f31}
body.ui-tone-dark .cases-simple-top h3,
body.ui-tone-dark .cases-simple-detail h2,
body.ui-tone-dark .cases-seo-block h2{color:#e6effb}
body.ui-tone-dark .cases-simple-link,
body.ui-tone-dark .cases-simple-back{border-color:#3a5f86;color:#bad4ef;background:#132841}
body.ui-tone-dark .cases-simple-link:hover,
body.ui-tone-dark .cases-simple-back:hover{background:#1a3552}
body.ui-tone-dark .cases-simple-summary div,
body.ui-tone-dark .cases-simple-detail-grid article,
body.ui-tone-dark .cases-simple-detail-copy article,
body.ui-tone-dark .cases-simple-sections article,
body.ui-tone-dark .cases-deep-block,
body.ui-tone-dark .cases-inline-contact{border-color:#2f4b68;background:#13263b}
body.ui-tone-dark .cases-simple-summary b,
body.ui-tone-dark .cases-simple-detail-grid h4,
body.ui-tone-dark .cases-simple-detail-copy h3,
body.ui-tone-dark .cases-simple-sections h4,
body.ui-tone-dark .cases-deep-block h3,
body.ui-tone-dark .cases-inline-contact h3{color:#dbeafe}
body.ui-tone-dark .cases-simple-summary span,
body.ui-tone-dark .cases-simple-detail-grid p,
body.ui-tone-dark .cases-simple-detail-copy div,
body.ui-tone-dark .cases-simple-excerpt,
body.ui-tone-dark .cases-simple-sections p,
body.ui-tone-dark .cases-deep-block p,
body.ui-tone-dark .cases-howto,
body.ui-tone-dark .cases-checklist,
body.ui-tone-dark .cases-inline-contact p,
body.ui-tone-dark .cases-seo-block p,
body.ui-tone-dark .cases-seo-list li{color:#9eb8d5}
body.ui-tone-dark .cases-deep-table{border-color:#365776;background:#10253a}
body.ui-tone-dark .cases-deep-table th,
body.ui-tone-dark .cases-deep-table td{border-color:#365776;color:#c8dbf2}
body.ui-tone-dark .cases-deep-table th{background:#17324b;color:#d7e8fb}
body.ui-tone-dark .cases-related a{border-color:#385a7f;background:#10253a;color:#c3d9f2}
body.ui-tone-dark .cases-related a:hover{border-color:#5a8ec8;color:#d9ebff}
body.ui-tone-dark .cases-inline-contact input,
body.ui-tone-dark .cases-inline-contact textarea{background:#0d1d2f;border-color:#395a7e;color:#dbe9f9}
body.ui-tone-dark .cases-inline-contact input::placeholder,
body.ui-tone-dark .cases-inline-contact textarea::placeholder{color:#7f9cbd}
body.ui-tone-dark .cases-inline-contact button{background:linear-gradient(135deg,#2c6fde,#1b8f8f)}
body.ui-tone-dark .cases-empty{border-color:#3a5c82;color:#a7c0db;background:#12263b}
</style>';
    include $__templateFile;
    return;
}
?>
<section class="container py-5">
    <h1>Cases Main</h1>
    <p>Template file not found: <code><?= htmlspecialchars($__templateFile, ENT_QUOTES, 'UTF-8') ?></code></p>
</section>
