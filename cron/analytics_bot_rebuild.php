<?php
$DB = new mysqli('localhost', 'geoip', 'rMPZgqCyhOVyfJFKtzR2wFgq5', 'geoip');
if ($DB->connect_errno) {
    die("DB error: " . $DB->connect_error);
}

echo "Rebuild started: " . date('Y-m-d H:i:s') . PHP_EOL;

$badPatterns = [
    // WP / CMS
    'wp-сontent',
    'wp-login',
    'wp-trackback',
    'wp-admin',
    'wordpress',
    'wp_filemanager.php',
    'xmlrpc',
	'feed',
	'txets.php',
	'archivarix',
	'boaform',
	'developmentserver',
	'actuator',
	'onvif-http',
	'jckeditor',

    // DB tools
    'phpmyadmin',
    'pma',
    'adminer',

    // shells
    'webshell',
    'shell',
    'cmd=',
    'exec=',

    // secrets
    '.env',
    '.aws',
    '.aws/credentials',
    '.npmrc',
    '.pypirc',

    // repos
    '.git',
    '.svn',

    // CI / configs
    'docker-compose.yml',
    '.travis.yml',
    'bitbucket-pipelines.yml',
    'configuration.php',
    'config.php',
    'web.config',
    'logon.aspx',
    'themes.php',
    'init.php',
    'robots.php',
    'Run.php',
    'run.php',
    'revealability.php',

    // misc
    'vendor/phpunit',
    'cgi-bin',
    'backup',
    'dump.sql'
];


// --------------------------------------------------------------------------
// 1. Пересчёт suspect IP по всей истории
// --------------------------------------------------------------------------

// формируем условия для SQL
$patternConditions = [];
foreach ($badPatterns as $pattern) {
    $patternEscaped = mysqli_real_escape_string($DB, $pattern);
    $patternConditions[] = "path LIKE '%{$patternEscaped}%'";
}
$patternSQL = implode(' OR ', $patternConditions);

// Собираем IP и количество совпадений
mysqli_query($DB, "
    INSERT INTO analytics_suspect_ip (ip, invalid_hits, first_seen, last_seen)
    SELECT 
        ip,
        COUNT(*) as invalid_hits,
        MIN(visited_at),
        MAX(visited_at)
    FROM analytics_visits
    WHERE {$patternSQL}
    GROUP BY ip
    HAVING COUNT(*) >= 3
    ON DUPLICATE KEY UPDATE
        invalid_hits = VALUES(invalid_hits),
        first_seen = VALUES(first_seen),
        last_seen = VALUES(last_seen)
");

echo "Invalid hits aggregated\n";

// --------------------------------------------------------------------------
// 2. Подтверждаем ботов
// --------------------------------------------------------------------------

mysqli_query($DB, "
    UPDATE analytics_suspect_ip
    SET is_confirmed_bot = 1
    WHERE invalid_hits >= 3
");

echo "Bots confirmed\n";

// --------------------------------------------------------------------------
// 3. Помечаем все визиты этих IP как ботов
// --------------------------------------------------------------------------

mysqli_query($DB, "
    UPDATE analytics_visits v
    JOIN analytics_suspect_ip s ON v.ip = s.ip
    SET 
        v.is_bot = 1,
        v.is_suspect = 1,
        v.suspect_reason = 'attack_signature'
    WHERE s.is_confirmed_bot = 1
");

echo "Visits updated\n";

// --------------------------------------------------------------------------
// 4. Дополнительно: помечаем отдельные визиты, которые сразу совпадают с паттернами
// --------------------------------------------------------------------------

foreach ($badPatterns as $pattern) {
    $patternEscaped = mysqli_real_escape_string($DB, $pattern);
    mysqli_query($DB, "
        UPDATE analytics_visits
        SET is_bot = 1,
            is_suspect = 1,
            suspect_reason = CONCAT('attack_signature:', '{$patternEscaped}')
        WHERE path LIKE '%{$patternEscaped}%'
    ");
}

echo "Direct attack pattern hits marked\n";

echo "Rebuild finished: " . date('Y-m-d H:i:s') . PHP_EOL;
