<?php
// update_tor_nodes.php
$DB_HOST = 'localhost';
$DB_USER = 'geoip';
$DB_PASS = 'rMPZgqCyhOVyfJFKtzR2wFgq5';
$DB_NAME = 'geoip';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Connect failed: " . $mysqli->connect_error);
}

// Загружаем список exit nodes с TorProject
$url = 'https://check.torproject.org/exit-addresses';
$content = file_get_contents($url);
if (!$content) {
    die("Failed to fetch Tor exit nodes");
}

$lines = explode("\n", $content);
$nodes = [];
foreach ($lines as $line) {
    if (str_starts_with($line, 'ExitAddress')) {
        $parts = explode(' ', $line);
        if (!empty($parts[1])) {
            $nodes[] = trim($parts[1]);
        }
    }
}

$now = date('Y-m-d H:i:s');
if (!empty($nodes)) {
    // Начинаем транзакцию
    $mysqli->begin_transaction();

    // Помечаем все существующие ноды как устаревшие (optional)
    $mysqli->query("UPDATE tor_exit_nodes SET last_seen = NULL");

    // Вставляем/обновляем
    $stmt = $mysqli->prepare("
        INSERT INTO tor_exit_nodes (ip, last_seen, created_at)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE last_seen = VALUES(last_seen)
    ");

    foreach ($nodes as $ip) {
        $stmt->bind_param('sss', $ip, $now, $now);
        $stmt->execute();
    }

    $mysqli->commit();
    echo "Tor exit nodes updated: " . count($nodes) . "\n";
} else {
    echo "No nodes found\n";
}
