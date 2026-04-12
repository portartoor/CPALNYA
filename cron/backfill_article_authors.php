<?php
ini_set('display_errors', '1');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/controls/examples/_common.php';
require_once DIR . 'core/libs/cpalnya_authors.php';

$FRMWRK = new FRMWRK();
$db = $FRMWRK->DB();
if (!$db) {
    fwrite(STDERR, "DB connection failed\n");
    exit(1);
}

$force = in_array('--force', $argv ?? [], true);
$dryRun = in_array('--dry-run', $argv ?? [], true);
$limit = 0;
foreach (($argv ?? []) as $arg) {
    if (strpos((string)$arg, '--limit=') === 0) {
        $limit = max(0, (int)substr((string)$arg, 8));
    }
}

$profiles = array_values(cpalnya_author_profiles());
if (empty($profiles)) {
    fwrite(STDERR, "No author profiles configured\n");
    exit(1);
}

$where = $force
    ? "WHERE is_published = 1"
    : "WHERE is_published = 1 AND (author_name IS NULL OR author_name = '' OR author_name = 'Редакция ЦПАЛЬНЯ')";
$limitSql = $limit > 0 ? ' LIMIT ' . (int)$limit : '';

$rows = $FRMWRK->DBRecords(
    "SELECT id, title, author_name
     FROM examples_articles
     {$where}
     ORDER BY id ASC{$limitSql}"
);

if (empty($rows)) {
    echo "No articles matched.\n";
    exit(0);
}

$updated = 0;
foreach ((array)$rows as $row) {
    $articleId = (int)($row['id'] ?? 0);
    if ($articleId <= 0) {
        continue;
    }
    $profile = $profiles[random_int(0, count($profiles) - 1)];
    $nickname = trim((string)($profile['nickname'] ?? ''));
    if ($nickname === '') {
        continue;
    }
    $title = trim((string)($row['title'] ?? ''));
    echo '#' . $articleId . ' -> ' . $nickname . ' :: ' . $title . PHP_EOL;
    if ($dryRun) {
        continue;
    }
    $nicknameSafe = mysqli_real_escape_string($db, $nickname);
    mysqli_query(
        $db,
        "UPDATE examples_articles
         SET author_name = '{$nicknameSafe}', updated_at = NOW()
         WHERE id = {$articleId}
         LIMIT 1"
    );
    if (mysqli_affected_rows($db) >= 0) {
        $updated++;
    }
}

echo ($dryRun ? 'Dry-run complete. ' : 'Updated ') . $updated . " articles.\n";
