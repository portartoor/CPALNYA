<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/libs/page_html_cache.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = 'info';

if (!($DB instanceof mysqli)) {
    $message = 'Database connection failed.';
    $messageType = 'danger';
    $pageHtmlCacheSettings = page_html_cache_defaults();
    $pageHtmlCacheStats = page_html_cache_stats();
    return;
}

page_html_cache_table_ensure($DB);
$pageHtmlCacheSettings = page_html_cache_get($DB);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save_page_html_cache_settings') {
        $enabled = isset($_POST['enabled']) && (string)$_POST['enabled'] === '1';
        $defaultTtl = (int)($_POST['default_ttl'] ?? 120);
        $maxFileSize = (int)($_POST['max_file_size'] ?? 2097152);

        $excludedRaw = trim((string)($_POST['excluded_prefixes'] ?? ''));
        $excludedPrefixes = [];
        if ($excludedRaw !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $excludedRaw);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = page_html_cache_normalize_prefix((string)$line);
                    if ($line !== '') {
                        $excludedPrefixes[$line] = true;
                    }
                }
            }
        }

        $ttlMapRaw = trim((string)($_POST['ttl_by_prefix'] ?? ''));
        $ttlByPrefix = [];
        if ($ttlMapRaw !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $ttlMapRaw);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = trim((string)$line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = array_map('trim', explode('|', $line));
                    if (count($parts) < 2) {
                        continue;
                    }
                    $prefix = page_html_cache_normalize_prefix((string)$parts[0]);
                    $ttl = (int)$parts[1];
                    if ($prefix === '' || $ttl <= 0) {
                        continue;
                    }
                    $ttlByPrefix[$prefix] = $ttl;
                }
            }
        }

        $dynamicRaw = trim((string)($_POST['dynamic_prefixes'] ?? ''));
        $dynamicPrefixes = [];
        if ($dynamicRaw !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $dynamicRaw);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = page_html_cache_normalize_prefix((string)$line);
                    if ($line !== '') {
                        $dynamicPrefixes[$line] = true;
                    }
                }
            }
        }

        $incoming = [
            'enabled' => $enabled,
            'default_ttl' => $defaultTtl,
            'excluded_prefixes' => array_keys($excludedPrefixes),
            'dynamic_prefixes' => array_keys($dynamicPrefixes),
            'ttl_by_prefix' => $ttlByPrefix,
            'max_file_size' => $maxFileSize,
        ];

        if (page_html_cache_save($DB, $incoming, (int)($adminpanelUser['id'] ?? 0))) {
            $message = 'HTML cache settings saved.';
            $messageType = 'success';
            $pageHtmlCacheSettings = page_html_cache_get($DB);
        } else {
            $message = 'Failed to save settings.';
            $messageType = 'danger';
        }
    } elseif ($action === 'purge_all') {
        $deleted = page_html_cache_purge_all();
        $message = 'Cache purged (all). Deleted files: ' . (int)$deleted . '.';
        $messageType = 'success';
    } elseif ($action === 'purge_prefix') {
        $prefix = trim((string)($_POST['purge_prefix'] ?? ''));
        $deleted = page_html_cache_purge_prefix($prefix);
        $message = 'Cache purged by prefix "' . $prefix . '". Deleted files: ' . (int)$deleted . '.';
        $messageType = 'success';
    } elseif ($action === 'purge_url') {
        $url = trim((string)($_POST['purge_url'] ?? ''));
        $deleted = page_html_cache_purge_url($url);
        $message = 'Cache purged by URL "' . $url . '". Deleted files: ' . (int)$deleted . '.';
        $messageType = 'success';
    }
}

$pageHtmlCacheStats = page_html_cache_stats();
