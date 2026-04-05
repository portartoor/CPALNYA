<?php

function mirror_domain_normalize_host(string $host): string
{
    $host = strtolower(trim($host));
    if ($host === '') {
        return '';
    }
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    return trim($host, '.');
}

function mirror_domain_is_valid_public_host(string $host): bool
{
    if ($host === '') {
        return false;
    }
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return false;
    }
    if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $host)) {
        return false;
    }
    return true;
}

function mirror_domain_is_service_subdomain(string $host): bool
{
    $firstLabel = explode('.', $host, 2)[0] ?? '';
    $firstLabel = strtolower(trim((string)$firstLabel));
    if ($firstLabel === '') {
        return false;
    }
    $serviceLabels = [
        'autodiscover', 'mail', 'owa', 'webmail', 'cpanel', 'whm',
        'imap', 'pop', 'smtp', 'mx', 'ns1', 'ns2'
    ];
    return in_array($firstLabel, $serviceLabels, true);
}

function mirror_domain_current_host(): string
{
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    return mirror_domain_normalize_host($host);
}

function mirror_domain_is_local_allowed(string $host): bool
{
    if ($host === '') {
        return true;
    }
    $allowed = ['localhost', '127.0.0.1', '::1'];
    if (in_array($host, $allowed, true)) {
        return true;
    }
    return substr($host, -6) === '.local';
}

function mirror_domains_ensure_schema($FRMWRK): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $DB = $FRMWRK->DB();
    if (!$DB) {
        return;
    }

    mysqli_query($DB, "
        CREATE TABLE IF NOT EXISTS mirror_domains (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            domain VARCHAR(190) NOT NULL,
            template_view VARCHAR(32) NOT NULL DEFAULT 'simple',
            google_tag_code MEDIUMTEXT NULL,
            yandex_counter_code MEDIUMTEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_domain (domain),
            KEY idx_active (is_active),
            KEY idx_template (template_view)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    mysqli_query($DB, "
        ALTER TABLE mirror_domains
        ADD COLUMN IF NOT EXISTS google_tag_code MEDIUMTEXT NULL AFTER template_view
    ");
    mysqli_query($DB, "
        ALTER TABLE mirror_domains
        ADD COLUMN IF NOT EXISTS yandex_counter_code MEDIUMTEXT NULL AFTER google_tag_code
    ");

    mysqli_query($DB, "
        CREATE TABLE IF NOT EXISTS system_bootstrap_flags (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            flag_key VARCHAR(128) NOT NULL,
            flag_value VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_flag_key (flag_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    mysqli_query($DB, "
        CREATE TABLE IF NOT EXISTS mirror_templates (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            template_key VARCHAR(64) NOT NULL,
            display_name VARCHAR(190) NOT NULL,
            shell_view VARCHAR(32) NOT NULL DEFAULT 'simple',
            main_view_file VARCHAR(128) NOT NULL DEFAULT 'main.php',
            model_file VARCHAR(128) NOT NULL DEFAULT 'main.php',
            control_file VARCHAR(128) NOT NULL DEFAULT 'main.php',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_template_key (template_key),
            KEY idx_template_active (is_active),
            KEY idx_template_shell (shell_view)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $currentHost = mirror_domain_current_host();
    $bootstrapRows = $FRMWRK->DBRecords(
        "SELECT id
         FROM system_bootstrap_flags
         WHERE flag_key='mirror_domains_seed_done'
         LIMIT 1"
    );
    $bootstrapDone = !empty($bootstrapRows);
    if (!$bootstrapDone) {
        if (
            $currentHost !== ''
            && !mirror_domain_is_local_allowed($currentHost)
            && mirror_domain_is_valid_public_host($currentHost)
            && !mirror_domain_is_service_subdomain($currentHost)
        ) {
            $currentHostSafe = mysqli_real_escape_string($DB, $currentHost);
            mysqli_query($DB, "INSERT IGNORE INTO mirror_domains (domain, template_view, is_active, created_at) VALUES ('{$currentHostSafe}', 'simple', 1, NOW())");
        }
        mysqli_query(
            $DB,
            "INSERT INTO system_bootstrap_flags (flag_key, flag_value, created_at, updated_at)
             VALUES ('mirror_domains_seed_done', '1', NOW(), NOW())
             ON DUPLICATE KEY UPDATE flag_value='1', updated_at=NOW()"
        );
    }

    mysqli_query($DB, "
        INSERT IGNORE INTO mirror_templates
            (template_key, display_name, shell_view, main_view_file, model_file, control_file, is_active, created_at, updated_at)
        VALUES
            ('simple', 'Public Site', 'simple', 'main.php', 'main.php', 'main.php', 1, NOW(), NOW()),
            ('dashboard', 'Dashboard UI', 'dashboard', 'main.php', 'main.php', 'main.php', 1, NOW(), NOW()),
            ('enterprise', 'Enterprise Site', 'enterprise', 'main.php', 'main.php', 'main.php', 1, NOW(), NOW())
    ");
}

function mirror_domain_resolve($FRMWRK): array
{
    static $cached = null;
    if (is_array($cached)) {
        return $cached;
    }

    $host = mirror_domain_current_host();
    $result = [
        'host' => $host,
        'allowed' => false,
        'domain' => null,
        'template_view' => 'simple',
    ];

    if (mirror_domain_is_local_allowed($host)) {
        $result['allowed'] = true;
        $cached = $result;
        return $cached;
    }

    if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
        $cached = $result;
        return $cached;
    }

    mirror_domains_ensure_schema($FRMWRK);
    $DB = $FRMWRK->DB();
    if (!$DB || $host === '') {
        $cached = $result;
        return $cached;
    }

    $hostSafe = mysqli_real_escape_string($DB, $host);
    $rows = $FRMWRK->DBRecords(
        "SELECT id, domain, template_view, google_tag_code, yandex_counter_code, is_active
         FROM mirror_domains
         WHERE domain='{$hostSafe}'
           AND is_active=1
         LIMIT 1"
    );

    if (!empty($rows)) {
        $domain = $rows[0];
        $template = strtolower((string)($domain['template_view'] ?? 'simple'));
        if (!in_array($template, ['simple', 'dashboard', 'enterprise'], true)) {
            $template = 'simple';
        }
        $result['allowed'] = true;
        $result['domain'] = $domain;
        $result['template_view'] = $template;
    }

    $cached = $result;
    return $cached;
}

function mirror_template_builtin(string $templateKey): ?array
{
    $map = [
        'simple' => [
            'template_key' => 'simple',
            'display_name' => 'Public Site',
            'shell_view' => 'simple',
            'main_view_file' => 'main.php',
            'model_file' => 'main.php',
            'control_file' => 'main.php',
            'is_active' => 1,
        ],
        'dashboard' => [
            'template_key' => 'dashboard',
            'display_name' => 'Dashboard UI',
            'shell_view' => 'dashboard',
            'main_view_file' => 'main.php',
            'model_file' => 'main.php',
            'control_file' => 'main.php',
            'is_active' => 1,
        ],
        'enterprise' => [
            'template_key' => 'enterprise',
            'display_name' => 'Enterprise Site',
            'shell_view' => 'enterprise',
            'main_view_file' => 'main.php',
            'model_file' => 'main.php',
            'control_file' => 'main.php',
            'is_active' => 1,
        ],
    ];
    return $map[$templateKey] ?? null;
}

function mirror_template_sanitize_row(array $row): array
{
    $key = strtolower(trim((string)($row['template_key'] ?? 'simple')));
    $shell = strtolower(trim((string)($row['shell_view'] ?? 'simple')));
    if (!in_array($shell, ['simple', 'dashboard', 'enterprise'], true)) {
        $shell = 'simple';
    }
    if (in_array($key, ['simple', 'dashboard', 'enterprise'], true)) {
        $shell = $key;
    }

    $cleanFile = static function (string $file, string $fallback = 'main.php'): string {
        $file = trim($file);
        if ($file === '') {
            return $fallback;
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]+\.php$/', $file)) {
            return $fallback;
        }
        return $file;
    };

    return [
        'template_key' => $key !== '' ? $key : 'simple',
        'display_name' => (string)($row['display_name'] ?? $key),
        'shell_view' => $shell,
        'main_view_file' => $cleanFile((string)($row['main_view_file'] ?? ''), 'main.php'),
        'model_file' => $cleanFile((string)($row['model_file'] ?? ''), 'main.php'),
        'control_file' => $cleanFile((string)($row['control_file'] ?? ''), 'main.php'),
        'is_active' => (int)($row['is_active'] ?? 0) === 1 ? 1 : 0,
    ];
}

function mirror_template_resolve($FRMWRK, string $templateKey): array
{
    $templateKey = strtolower(trim($templateKey));
    if ($templateKey === '') {
        $templateKey = 'simple';
    }

    if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
        return mirror_template_builtin($templateKey) ?? mirror_template_builtin('simple');
    }

    mirror_domains_ensure_schema($FRMWRK);
    $DB = $FRMWRK->DB();
    if (!$DB) {
        return mirror_template_builtin($templateKey) ?? mirror_template_builtin('simple');
    }

    $safeKey = mysqli_real_escape_string($DB, $templateKey);
    $rows = $FRMWRK->DBRecords(
        "SELECT template_key, display_name, shell_view, main_view_file, model_file, control_file, is_active
         FROM mirror_templates
         WHERE template_key='{$safeKey}'
           AND is_active=1
         LIMIT 1"
    );
    if (!empty($rows)) {
        return mirror_template_sanitize_row($rows[0]);
    }

    return mirror_template_builtin($templateKey) ?? mirror_template_builtin('simple');
}

function mirror_domain_can_preview($FRMWRK): bool
{
    $token = (string)($_COOKIE['adminpanel_token'] ?? '');
    if ($token === '' || !is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
        return false;
    }
    $DB = $FRMWRK->DB();
    if (!$DB) {
        return false;
    }
    $safe = mysqli_real_escape_string($DB, $token);
    $rows = $FRMWRK->DBRecords(
        "SELECT id
         FROM adminpanel_users
         WHERE token='{$safe}'
           AND is_active=1
           AND token_expires > NOW()
         LIMIT 1"
    );
    return !empty($rows);
}

function mirror_template_preview_key($FRMWRK): ?string
{
    $raw = trim((string)($_GET['template_preview'] ?? ''));
    if ($raw === '' || !preg_match('/^[a-z0-9_-]{2,64}$/', $raw)) {
        return null;
    }
    // Public preview by key: admin panel generates links for fast side-by-side checks.
    // If key does not exist or template is disabled, resolver falls back to default template.
    return strtolower($raw);
}
