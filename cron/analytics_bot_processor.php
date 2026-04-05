<?php
// update_tor_nodes.php
$DB_HOST = 'localhost';
$DB_USER = 'geoip';
$DB_PASS = 'rMPZgqCyhOVyfJFKtzR2wFgq5';
$DB_NAME = 'geoip';

$DB = new mysqli('localhost', 'geoip', 'rMPZgqCyhOVyfJFKtzR2wFgq5', 'geoip');
if ($DB->connect_errno) {
    die("DB error: " . $DB->connect_error);
}

if (!$DB) {
    exit;
}

// 1. Подтверждаем ботов (3+ invalid за 10 минут)
mysqli_query($DB, "
    UPDATE analytics_suspect_ip
    SET is_confirmed_bot = 1
    WHERE invalid_hits >= 3
    AND TIMESTAMPDIFF(MINUTE, first_seen, last_seen) <= 10
    AND is_confirmed_bot = 0
");

// 2. Помечаем визиты
mysqli_query($DB, "
    UPDATE analytics_visits v
    JOIN analytics_suspect_ip s ON v.ip = s.ip
    SET 
        v.is_bot = 1,
        v.is_suspect = 1,
        v.suspect_reason = 'invalid_routes'
    WHERE s.is_confirmed_bot = 1
    AND v.is_suspect = 0
");
