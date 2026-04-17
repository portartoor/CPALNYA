<?php

define('DIR', dirname(__DIR__) . '/');

function review_manual_usage(): void
{
    echo "Usage:\n";
    echo "  php cron/run_reviews_manual.php --date=YYYY-MM-DD [--lang=ru] [--mode=queue|generate|queue-and-generate] [--slot-index=1] [--planned-at=\"YYYY-MM-DD HH:MM:SS\"] [--max-per-run=1] [--force] [--dry-run]\n";
}

function review_manual_options(array $argv): array
{
    $opts = [
        'date' => date('Y-m-d'),
        'lang' => 'ru',
        'mode' => 'queue-and-generate',
        'slot_index' => 1,
        'planned_at' => '',
        'max_per_run' => 1,
        'force' => false,
        'dry_run' => false,
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $opts['help'] = true;
            continue;
        }
        if ($arg === '--force') {
            $opts['force'] = true;
            continue;
        }
        if ($arg === '--dry-run') {
            $opts['dry_run'] = true;
            continue;
        }
        if (strpos($arg, '--date=') === 0) {
            $value = trim(substr($arg, 7));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $opts['date'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--lang=') === 0) {
            $value = strtolower(trim(substr($arg, 7)));
            if (in_array($value, ['ru', 'en'], true)) {
                $opts['lang'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--mode=') === 0) {
            $value = strtolower(trim(substr($arg, 7)));
            if (in_array($value, ['queue', 'generate', 'queue-and-generate'], true)) {
                $opts['mode'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--slot-index=') === 0) {
            $value = (int)trim(substr($arg, 13));
            if ($value > 0) {
                $opts['slot_index'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--planned-at=') === 0) {
            $value = trim(substr($arg, 13));
            if ($value !== '') {
                $opts['planned_at'] = $value;
            }
            continue;
        }
        if (strpos($arg, '--max-per-run=') === 0) {
            $value = (int)trim(substr($arg, 14));
            if ($value > 0) {
                $opts['max_per_run'] = $value;
            }
            continue;
        }
    }

    return $opts;
}

function review_manual_run(array $command): int
{
    $parts = array_map('escapeshellarg', $command);
    $cmd = implode(' ', $parts);
    passthru($cmd, $exitCode);
    return (int)$exitCode;
}

$opts = review_manual_options($argv ?? []);
if (!empty($opts['help'])) {
    review_manual_usage();
    exit(0);
}

$phpBinary = defined('PHP_BINARY') && PHP_BINARY !== '' ? PHP_BINARY : 'php';
$queueScript = DIR . 'cron/seo_article_queue.php';
$generateScript = DIR . 'cron/generate_seo_articles.php';

$queueCommand = [
    $phpBinary,
    $queueScript,
    '--enqueue',
    '--campaign=reviews',
    '--date=' . $opts['date'],
    '--lang=' . $opts['lang'],
    '--max-per-run=' . (int)$opts['max_per_run'],
];
if (!empty($opts['planned_at'])) {
    $queueCommand[] = '--planned-at=' . $opts['planned_at'];
}
if (!empty($opts['force'])) {
    $queueCommand[] = '--force';
}
if (!empty($opts['dry_run'])) {
    $queueCommand[] = '--dry-run';
}

$generateCommand = [
    $phpBinary,
    $generateScript,
    '--campaign=reviews',
    '--date=' . $opts['date'],
    '--lang=' . $opts['lang'],
    '--slot-index=' . (int)$opts['slot_index'],
    '--max-per-run=' . (int)$opts['max_per_run'],
];
if (!empty($opts['force'])) {
    $generateCommand[] = '--force';
}
if (!empty($opts['dry_run'])) {
    $generateCommand[] = '--dry-run';
}

$exitCode = 0;
if ($opts['mode'] === 'queue' || $opts['mode'] === 'queue-and-generate') {
    echo "[reviews] queue\n";
    $exitCode = review_manual_run($queueCommand);
    if ($exitCode !== 0) {
        exit($exitCode);
    }
}

if ($opts['mode'] === 'generate' || $opts['mode'] === 'queue-and-generate') {
    echo "[reviews] generate\n";
    $exitCode = review_manual_run($generateCommand);
}

exit($exitCode);
