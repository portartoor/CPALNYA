<?php
$__templateShell = strtolower((string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple'));
$__templateFile = __DIR__ . '/templates/main/' . $__templateShell . '/main.playbooks.php';
if (is_file($__templateFile)) {
    require $__templateFile;
    return;
}

$__fallbackTemplateFile = __DIR__ . '/templates/main/simple/main.playbooks.php';
if (is_file($__fallbackTemplateFile)) {
    require $__fallbackTemplateFile;
    return;
}

echo '<div style="max-width:960px;margin:48px auto;padding:0 16px;color:#fff">Playbooks view is not available for the current template.</div>';
