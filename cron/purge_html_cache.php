<?php
define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/libs/page_html_cache.php';

$argvList = isset($argv) && is_array($argv) ? $argv : [];
$action = 'all';
$value = '';

foreach ($argvList as $arg) {
    if (!is_string($arg) || $arg === '' || strpos($arg, '--') !== 0) {
        continue;
    }
    if ($arg === '--all') {
        $action = 'all';
        continue;
    }
    if (strpos($arg, '--prefix=') === 0) {
        $action = 'prefix';
        $value = trim((string)substr($arg, strlen('--prefix=')));
        continue;
    }
    if (strpos($arg, '--url=') === 0) {
        $action = 'url';
        $value = trim((string)substr($arg, strlen('--url=')));
        continue;
    }
}

$deleted = 0;
if ($action === 'prefix') {
    $deleted = page_html_cache_purge_prefix($value);
    echo '[HTML Cache] Purged prefix "' . $value . '", deleted=' . (int)$deleted . PHP_EOL;
    exit(0);
}
if ($action === 'url') {
    $deleted = page_html_cache_purge_url($value);
    echo '[HTML Cache] Purged URL "' . $value . '", deleted=' . (int)$deleted . PHP_EOL;
    exit(0);
}

$deleted = page_html_cache_purge_all();
echo '[HTML Cache] Purged all, deleted=' . (int)$deleted . PHP_EOL;
exit(0);

