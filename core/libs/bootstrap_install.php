<?php
if (!function_exists('portcore_bootstrap_if_needed')) {
    function portcore_bootstrap_if_needed(): void
    {
        $dumpPath = DIR . '/portcore.sql';
        $doneMarkerPath = DIR . '/cache/.portcore_bootstrap_done';

        if (!file_exists($dumpPath) || file_exists($doneMarkerPath)) {
            return;
        }

        require DIR . '/core/config.php';

        $db = @mysqli_connect($DatabaseHost, $DatabaseUser, $DatabasePassword, $DatabaseName);
        if (!$db) {
            return;
        }

        @mysqli_set_charset($db, 'utf8mb4');

        $bootstrapTable = 'adminpanel_users';
        $bootstrapTableSafe = mysqli_real_escape_string($db, $bootstrapTable);
        $bootstrapTableRes = @mysqli_query($db, "SHOW TABLES LIKE '{$bootstrapTableSafe}'");
        $shouldImportDump = ($bootstrapTableRes && mysqli_num_rows($bootstrapTableRes) === 0);
        if ($bootstrapTableRes) {
            mysqli_free_result($bootstrapTableRes);
        }

        if (!$shouldImportDump) {
            mysqli_close($db);
            return;
        }

        $sql = @file_get_contents($dumpPath);
        if (!is_string($sql) || trim($sql) === '') {
            mysqli_close($db);
            return;
        }

        $importOk = @mysqli_multi_query($db, $sql);
        if ($importOk) {
            do {
                if ($result = @mysqli_store_result($db)) {
                    mysqli_free_result($result);
                }
            } while (@mysqli_next_result($db));
        } else {
            error_log('[bootstrap_install] SQL import failed: ' . mysqli_error($db));
            mysqli_close($db);
            return;
        }

        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($host, ':') !== false) {
            $host = explode(':', $host, 2)[0];
        }
        $host = trim($host);
        if ($host !== '') {
            $hostSafe = mysqli_real_escape_string($db, $host);
            @mysqli_query(
                $db,
                "INSERT INTO mirror_domains (domain, template_view, is_active, created_at)
                 VALUES ('{$hostSafe}', 'simple', 1, NOW())
                 ON DUPLICATE KEY UPDATE is_active = VALUES(is_active), updated_at = NOW()"
            );
        }

        @file_put_contents($doneMarkerPath, date('c'));
        @unlink($dumpPath);
        mysqli_close($db);
    }
}
?>
