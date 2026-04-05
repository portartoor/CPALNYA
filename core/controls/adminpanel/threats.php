<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';
$editRule = null;

if ($DB && function_exists('analytics_ensure_schema')) {
    analytics_ensure_schema($DB);
}

$allowedMatchTypes = [
    'path_or_query_contains',
    'path_contains',
    'query_contains',
    'ua_contains',
    'ip_equals',
    'regex_path',
    'regex_query',
    'regex_path_or_query',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $DB) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_rule') {
        $ruleId = (int)($_POST['rule_id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $matchType = trim((string)($_POST['match_type'] ?? 'path_or_query_contains'));
        $pattern = trim((string)($_POST['pattern'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));
        $isActive = ((int)($_POST['is_active'] ?? 0) === 1) ? 1 : 0;

        if (!in_array($matchType, $allowedMatchTypes, true)) {
            $matchType = 'path_or_query_contains';
        }
        if ($title === '' || $pattern === '') {
            $message = 'Title and pattern are required.';
            $messageType = 'danger';
        } else {
            $titleSafe = mysqli_real_escape_string($DB, mb_substr($title, 0, 190));
            $matchTypeSafe = mysqli_real_escape_string($DB, $matchType);
            $patternSafe = mysqli_real_escape_string($DB, mb_substr($pattern, 0, 512));
            $notesSafe = mysqli_real_escape_string($DB, mb_substr($notes, 0, 255));

            if ($ruleId > 0) {
                mysqli_query(
                    $DB,
                    "UPDATE analytics_threat_rules
                     SET title = '{$titleSafe}',
                         match_type = '{$matchTypeSafe}',
                         pattern = '{$patternSafe}',
                         notes = '{$notesSafe}',
                         is_active = " . (int)$isActive . ",
                         updated_at = NOW()
                     WHERE id = " . (int)$ruleId . "
                     LIMIT 1"
                );
                $message = 'Threat rule updated.';
                $messageType = 'success';
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO analytics_threat_rules
                        (title, match_type, pattern, notes, is_active, match_count, created_at, updated_at)
                     VALUES
                        ('{$titleSafe}', '{$matchTypeSafe}', '{$patternSafe}', '{$notesSafe}', " . (int)$isActive . ", 0, NOW(), NOW())"
                );
                $message = 'Threat rule created.';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'delete_rule') {
        $ruleId = (int)($_POST['rule_id'] ?? 0);
        if ($ruleId > 0) {
            mysqli_query($DB, "DELETE FROM analytics_threat_rules WHERE id = " . (int)$ruleId . " LIMIT 1");
            $message = 'Threat rule deleted.';
            $messageType = 'success';
        }
    }
}

if ($DB) {
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $rows = $FRMWRK->DBRecords("SELECT * FROM analytics_threat_rules WHERE id = {$editId} LIMIT 1");
        if (!empty($rows)) {
            $editRule = $rows[0];
        }
    }
}

$rulesRows = [];
if ($DB) {
    $rulesRows = $FRMWRK->DBRecords(
        "SELECT id, title, match_type, pattern, notes, is_active, match_count, last_matched_at, updated_at
         FROM analytics_threat_rules
         ORDER BY is_active DESC, id DESC"
    );
}
?>
