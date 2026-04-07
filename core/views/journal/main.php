<?php
$__mirrorSite = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple')));
$__templateFile = __DIR__ . '/templates/main/' . $__mirrorSite . '/main.journal.php';
if (is_file($__templateFile)) {
    include $__templateFile;
    return;
}
$__fallbackTemplateFile = __DIR__ . '/templates/main/simple/main.journal.php';
if (is_file($__fallbackTemplateFile)) {
    include $__fallbackTemplateFile;
    return;
}
?>
<section class="container py-5">
    <h1>Journal</h1>
    <p>Template file not found: <code><?= htmlspecialchars($__templateFile, ENT_QUOTES, 'UTF-8') ?></code></p>
</section>
