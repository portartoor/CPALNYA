<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/controls/examples/_common.php';

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();

if (!$DB || !examples_table_exists($DB)) {
    exit(0);
}

function img_backfill_opt(string $name, $default = null)
{
    $argv = isset($GLOBALS['argv']) && is_array($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
    $prefix = '--' . $name . '=';
    foreach ($argv as $arg) {
        if (!is_string($arg)) {
            continue;
        }
        if ($arg === '--' . $name) {
            return true;
        }
        if (strpos($arg, $prefix) === 0) {
            return substr($arg, strlen($prefix));
        }
    }
    return $default;
}

function img_backfill_log(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function img_backfill_safe_glob(string $pattern): array
{
    $list = glob($pattern);
    return is_array($list) ? $list : [];
}

function img_backfill_mkdir(string $dir): bool
{
    if (is_dir($dir)) {
        return true;
    }
    return @mkdir($dir, 0775, true);
}

function img_backfill_is_root_relative(string $value): bool
{
    $value = trim($value);
    if ($value === '') {
        return false;
    }
    if (stripos($value, 'data:') === 0) {
        return false;
    }
    if (preg_match('/^https?:\/\//i', $value)) {
        return false;
    }
    return (strpos($value, '/') === 0);
}

function img_backfill_parse_data_uri(string $value): ?array
{
    $value = trim($value);
    if (stripos($value, 'data:image/') !== 0) {
        return null;
    }
    $parts = explode(',', $value, 2);
    if (count($parts) !== 2) {
        return null;
    }
    $meta = strtolower($parts[0]);
    $payload = trim($parts[1]);
    if ($payload === '') {
        return null;
    }
    $mime = 'image/png';
    if (preg_match('/^data:([^;]+)/i', $parts[0], $m)) {
        $mime = strtolower(trim((string)$m[1]));
    }
    $isBase64 = (strpos($meta, ';base64') !== false);
    if ($isBase64) {
        $bin = base64_decode($payload, true);
        if ($bin === false || $bin === '') {
            return null;
        }
        return ['binary' => $bin, 'mime' => $mime, 'source' => 'data_uri'];
    }
    $bin = urldecode($payload);
    if ($bin === '') {
        return null;
    }
    return ['binary' => $bin, 'mime' => $mime, 'source' => 'data_uri'];
}

function img_backfill_try_base64_blob(string $value): ?array
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    if (strlen($value) < 128) {
        return null;
    }
    if (preg_match('/^https?:\/\//i', $value)) {
        return null;
    }
    if (strpos($value, '/') !== false || strpos($value, '\\') !== false) {
        return null;
    }
    $bin = base64_decode($value, true);
    if ($bin === false || strlen($bin) < 128) {
        return null;
    }
    return ['binary' => $bin, 'mime' => '', 'source' => 'base64_blob'];
}

function img_backfill_http_fetch(string $url, int $timeoutSec = 20): ?string
{
    $url = trim($url);
    if (!preg_match('/^https?:\/\//i', $url)) {
        return null;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(8, $timeoutSec));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'geoip-image-backfill/1.0');
        $raw = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (is_string($raw) && $raw !== '' && $code >= 200 && $code < 300) {
            return $raw;
        }
        return null;
    }

    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => ['timeout' => $timeoutSec, 'follow_location' => 1],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if (is_string($raw) && $raw !== '') {
            return $raw;
        }
    }

    return null;
}

function img_backfill_detect_ext(string $binary, string $mimeHint = ''): string
{
    $mime = strtolower(trim($mimeHint));
    if ($mime === '' && function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $detected = finfo_buffer($fi, $binary);
            if (is_string($detected) && $detected !== '') {
                $mime = strtolower(trim($detected));
            }
            finfo_close($fi);
        }
    }

    $map = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/svg+xml' => 'svg',
    ];
    if (isset($map[$mime])) {
        return $map[$mime];
    }

    if (strncmp($binary, "\x89PNG\x0D\x0A\x1A\x0A", 8) === 0) {
        return 'png';
    }
    if (strncmp($binary, "\xFF\xD8\xFF", 3) === 0) {
        return 'jpg';
    }
    if (strncmp($binary, "GIF87a", 6) === 0 || strncmp($binary, "GIF89a", 6) === 0) {
        return 'gif';
    }
    if (strncmp($binary, "RIFF", 4) === 0 && substr($binary, 8, 4) === 'WEBP') {
        return 'webp';
    }

    return 'png';
}

function img_backfill_store_binary(
    int $articleId,
    string $binary,
    string $mimeHint,
    string $filesDirAbs,
    string $filesDirRel,
    bool $dryRun,
    string $suffix = ''
): ?array {
    if ($binary === '' || strlen($binary) < 64) {
        return null;
    }
    $ext = img_backfill_detect_ext($binary, $mimeHint);
    $hash = substr(sha1($binary), 0, 16);
    $suffixSafe = $suffix !== '' ? ('-' . preg_replace('/[^a-z0-9_-]+/i', '', $suffix)) : '';
    $fileName = 'example-' . $articleId . $suffixSafe . '-' . $hash . '.' . $ext;
    $fileAbs = rtrim($filesDirAbs, '/\\') . '/' . $fileName;
    $fileRel = rtrim($filesDirRel, '/\\') . '/' . $fileName;

    if (!$dryRun) {
        if (!is_file($fileAbs)) {
            $written = @file_put_contents($fileAbs, $binary);
            if ($written === false || $written <= 0) {
                return null;
            }
        }
    }

    return [
        'file_abs' => $fileAbs,
        'file_rel' => $fileRel,
        'hash' => $hash,
        'ext' => $ext,
    ];
}

function img_backfill_rewrite_content_images(
    int $articleId,
    string $contentHtml,
    string $filesDirAbs,
    string $filesDirRel,
    bool $dryRun
): array {
    $out = [
        'html' => $contentHtml,
        'replaced' => 0,
        'first_file_rel' => '',
    ];
    if ($contentHtml === '' || stripos($contentHtml, 'data:image/') === false) {
        return $out;
    }

    $pattern = '~data:image/[a-z0-9.+-]+;base64,[a-z0-9+/=\r\n]+~i';
    if (!preg_match_all($pattern, $contentHtml, $m) || empty($m[0])) {
        return $out;
    }

    $replaceMap = [];
    foreach (array_unique($m[0]) as $idx => $uri) {
        $parsed = img_backfill_parse_data_uri((string)$uri);
        if (!is_array($parsed)) {
            continue;
        }
        $stored = img_backfill_store_binary(
            $articleId,
            (string)($parsed['binary'] ?? ''),
            (string)($parsed['mime'] ?? ''),
            $filesDirAbs,
            $filesDirRel,
            $dryRun,
            'inbody' . (string)($idx + 1)
        );
        if (!is_array($stored) || empty($stored['file_rel'])) {
            continue;
        }
        $fileRel = (string)$stored['file_rel'];
        $replaceMap[(string)$uri] = $fileRel;
        if ($out['first_file_rel'] === '') {
            $out['first_file_rel'] = $fileRel;
        }
    }

    if (empty($replaceMap)) {
        return $out;
    }

    $newHtml = strtr($contentHtml, $replaceMap);
    if ($newHtml !== $contentHtml) {
        $out['html'] = $newHtml;
        $out['replaced'] = count($replaceMap);
    }

    return $out;
}

$limit = (int)img_backfill_opt('limit', 100);
$limit = max(1, min(1000, $limit));
$dryRun = (bool)img_backfill_opt('dry-run', false);
$onlyPublished = ((string)img_backfill_opt('only-published', '1') !== '0');
$articleId = (int)img_backfill_opt('id', 0);
$clearData = ((string)img_backfill_opt('clear-data', '1') !== '0');
$repairFromFiles = (bool)img_backfill_opt('repair-from-files', false);
$rewriteContent = ((string)img_backfill_opt('rewrite-content', '1') !== '0');

$hasPreviewUrl = examples_table_has_column($DB, 'preview_image_url');
$hasPreviewData = examples_table_has_column($DB, 'preview_image_data');
$hasContentHtml = examples_table_has_column($DB, 'content_html');
$hasUpdatedAt = examples_table_has_column($DB, 'updated_at');
$hasPublished = examples_table_has_column($DB, 'is_published');

if (!$hasPreviewUrl && !$hasPreviewData) {
    img_backfill_log('No preview image columns found.');
    exit(0);
}

$filesDirAbs = DIR . 'pictures/examples';
$filesDirRel = '/pictures/examples';
if (!$dryRun && !img_backfill_mkdir($filesDirAbs)) {
    img_backfill_log('Failed to create folder: ' . $filesDirAbs);
    exit(1);
}

if ($repairFromFiles) {
    $processed = 0;
    $updated = 0;
    $skipped = 0;
    $failed = 0;
    $files = img_backfill_safe_glob($filesDirAbs . '/example-*.*');
    rsort($files, SORT_STRING);
    img_backfill_log('Repair mode from files. files=' . count($files) . ', dry_run=' . ($dryRun ? '1' : '0'));

    foreach ($files as $fileAbs) {
        if ($processed >= $limit) {
            break;
        }
        $base = basename($fileAbs);
        if (!preg_match('/^example-(\d+)-[a-f0-9]{8,}\.[a-z0-9]+$/i', $base, $m)) {
            continue;
        }
        $id = (int)$m[1];
        if ($id <= 0) {
            continue;
        }
        if ($articleId > 0 && $id !== $articleId) {
            continue;
        }
        $processed++;

        $exists = $FRMWRK->DBRecords("SELECT id FROM examples_articles WHERE id = {$id} LIMIT 1");
        if (empty($exists)) {
            $skipped++;
            img_backfill_log("Skip #{$id}: article not found.");
            continue;
        }

        $fileRel = $filesDirRel . '/' . $base;
        $set = [];
        if ($hasPreviewUrl) {
            $set[] = "preview_image_url = '" . mysqli_real_escape_string($DB, $fileRel) . "'";
            if ($hasPreviewData && $clearData) {
                $set[] = "preview_image_data = NULL";
            }
        } elseif ($hasPreviewData) {
            $set[] = "preview_image_data = '" . mysqli_real_escape_string($DB, $fileRel) . "'";
        }
        if ($hasUpdatedAt) {
            $set[] = "updated_at = NOW()";
        }
        if (empty($set)) {
            $failed++;
            img_backfill_log("Fail #{$id}: no update fields.");
            continue;
        }

        if (!$dryRun) {
            $sql = "UPDATE examples_articles SET " . implode(', ', $set) . " WHERE id = {$id} LIMIT 1";
            $ok = mysqli_query($DB, $sql);
            if (!$ok) {
                $failed++;
                img_backfill_log("Fail #{$id}: DB update error: " . mysqli_error($DB));
                continue;
            }
        }

        $updated++;
        img_backfill_log("OK #{$id}: {$fileRel}");
    }

    img_backfill_log("Done (repair). processed={$processed}, updated={$updated}, skipped={$skipped}, failed={$failed}");
    exit(0);
}

$where = [];
if ($hasPublished && $onlyPublished) {
    $where[] = 'is_published = 1';
}
if ($articleId > 0) {
    $where[] = 'id = ' . $articleId;
}
$needParts = [];
if ($hasPreviewData) {
    $needParts[] = "COALESCE(preview_image_data, '') <> ''";
}
if ($hasPreviewUrl) {
    $needParts[] = "COALESCE(preview_image_url, '') <> ''";
}
if ($hasContentHtml && $rewriteContent) {
    $needParts[] = "LOCATE('data:image/', COALESCE(content_html, '')) > 0";
}
if (!empty($needParts)) {
    $where[] = '(' . implode(' OR ', $needParts) . ')';
}
$whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$selectFields = ['id', 'title'];
if ($hasPreviewUrl) {
    $selectFields[] = 'preview_image_url';
}
if ($hasPreviewData) {
    $selectFields[] = 'preview_image_data';
}
if ($hasContentHtml && $rewriteContent) {
    $selectFields[] = 'content_html';
}

$rows = $FRMWRK->DBRecords(
    "SELECT " . implode(', ', $selectFields) . "
     FROM examples_articles
     {$whereSql}
     ORDER BY id DESC
     LIMIT {$limit}"
);

$processed = 0;
$updated = 0;
$skipped = 0;
$failed = 0;

img_backfill_log('Start. rows=' . count($rows) . ', dry_run=' . ($dryRun ? '1' : '0'));

foreach ($rows as $row) {
    $processed++;
    $id = (int)($row['id'] ?? 0);
    $title = trim((string)($row['title'] ?? ''));
    $urlVal = trim((string)($row['preview_image_url'] ?? ''));
    $dataVal = trim((string)($row['preview_image_data'] ?? ''));
    $contentHtml = (string)($row['content_html'] ?? '');

    $hasConvertiblePreview = true;
    if ($urlVal !== '' && img_backfill_is_root_relative($urlVal)) {
        $hasConvertiblePreview = false;
    }

    $contentRewrite = ['html' => $contentHtml, 'replaced' => 0, 'first_file_rel' => ''];
    if ($rewriteContent && $hasContentHtml) {
        $contentRewrite = img_backfill_rewrite_content_images($id, $contentHtml, $filesDirAbs, $filesDirRel, $dryRun);
    }

    if (!$hasConvertiblePreview && (int)($contentRewrite['replaced'] ?? 0) <= 0) {
        $skipped++;
        img_backfill_log("Skip #{$id}: nothing to convert.");
        continue;
    }

    $source = null;
    if ($hasConvertiblePreview && $urlVal !== '') {
        $source = img_backfill_parse_data_uri($urlVal);
        if ($source === null) {
            $blob = img_backfill_try_base64_blob($urlVal);
            if ($blob !== null) {
                $source = $blob;
            }
        }
        if ($source === null && preg_match('/^https?:\/\//i', $urlVal)) {
            $fetched = img_backfill_http_fetch($urlVal);
            if ($fetched !== null) {
                $source = ['binary' => $fetched, 'mime' => '', 'source' => 'remote_url'];
            }
        }
    }
    if ($source === null && $dataVal !== '') {
        $source = img_backfill_parse_data_uri($dataVal);
        if ($source === null) {
            $blob = img_backfill_try_base64_blob($dataVal);
            if ($blob !== null) {
                $source = $blob;
            }
        }
    }

    $fileRel = '';
    if ($source !== null) {
        $stored = img_backfill_store_binary(
            $id,
            (string)($source['binary'] ?? ''),
            (string)($source['mime'] ?? ''),
            $filesDirAbs,
            $filesDirRel,
            $dryRun
        );
        if (!is_array($stored) || empty($stored['file_rel'])) {
            $failed++;
            img_backfill_log("Fail #{$id}: file write error.");
            continue;
        }
        $fileRel = (string)$stored['file_rel'];
    } elseif (!empty($contentRewrite['first_file_rel'])) {
        $fileRel = (string)$contentRewrite['first_file_rel'];
    }

    $set = [];
    if ($fileRel !== '' && $hasPreviewUrl) {
        $set[] = "preview_image_url = '" . mysqli_real_escape_string($DB, $fileRel) . "'";
        if ($hasPreviewData && $clearData) {
            $set[] = "preview_image_data = NULL";
        }
    } elseif ($fileRel !== '' && $hasPreviewData) {
        // Fallback for legacy schema without preview_image_url column.
        $set[] = "preview_image_data = '" . mysqli_real_escape_string($DB, $fileRel) . "'";
    }
    if ($hasContentHtml && $rewriteContent && (int)($contentRewrite['replaced'] ?? 0) > 0) {
        $set[] = "content_html = '" . mysqli_real_escape_string($DB, (string)($contentRewrite['html'] ?? '')) . "'";
    }
    if ($hasUpdatedAt) {
        $set[] = "updated_at = NOW()";
    }

    if (empty($set)) {
        $failed++;
        img_backfill_log("Fail #{$id}: no update fields.");
        continue;
    }

    if (!$dryRun) {
        $sql = "UPDATE examples_articles SET " . implode(', ', $set) . " WHERE id = {$id} LIMIT 1";
        $ok = mysqli_query($DB, $sql);
        if (!$ok) {
            $failed++;
            img_backfill_log("Fail #{$id}: DB update error: " . mysqli_error($DB));
            continue;
        }
    }

    $updated++;
    $titleShort = $title !== '' ? mb_substr($title, 0, 70) : '';
    $msg = "OK #{$id}:";
    if ($fileRel !== '') {
        $msg .= ' ' . $fileRel;
    }
    if ((int)($contentRewrite['replaced'] ?? 0) > 0) {
        $msg .= ' | content_replaced=' . (int)$contentRewrite['replaced'];
    }
    if ($titleShort !== '') {
        $msg .= ' | ' . $titleShort;
    }
    img_backfill_log($msg);
}

img_backfill_log("Done. processed={$processed}, updated={$updated}, skipped={$skipped}, failed={$failed}");
exit(0);
