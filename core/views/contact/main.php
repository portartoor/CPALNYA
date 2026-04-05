<?php
$__mirrorSite = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple')));
$__templateFile = __DIR__ . '/templates/main/' . $__mirrorSite . '/main.contact.php';
if (is_file($__templateFile)) {
    include $__templateFile;
    return;
}
?>
<section class="container py-5">
    <h1>Contact Main</h1>
    <p>Template file not found: <code><?= htmlspecialchars($__templateFile, ENT_QUOTES, 'UTF-8') ?></code></p>
</section>
