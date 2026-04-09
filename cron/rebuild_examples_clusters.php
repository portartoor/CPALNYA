<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';
require_once DIR . 'core/controls/examples/_common.php';
require_once DIR . 'core/libs/seo_generator_settings.php';
$pageHtmlCacheLib = DIR . 'core/libs/page_html_cache.php';
if (is_file($pageHtmlCacheLib)) {
    require_once $pageHtmlCacheLib;
}

function cluster_rebuild_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function cluster_rebuild_parse_args(array $argv): array
{
    $opts = [
        'dry_run' => false,
        'all' => false,
        'section' => '',
        'limit' => 500,
        'ids' => [],
    ];
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $opts['dry_run'] = true;
            continue;
        }
        if ($arg === '--all') {
            $opts['all'] = true;
            continue;
        }
        if (strpos($arg, '--section=') === 0) {
            $section = trim(substr($arg, 10));
            if (in_array($section, ['journal', 'playbooks', 'signals', 'fun'], true)) {
                $opts['section'] = $section;
            }
            continue;
        }
        if (strpos($arg, '--limit=') === 0) {
            $opts['limit'] = max(1, min(5000, (int)substr($arg, 8)));
            continue;
        }
        if (strpos($arg, '--ids=') === 0) {
            $rawIds = explode(',', (string)substr($arg, 6));
            foreach ($rawIds as $id) {
                $id = (int)trim($id);
                if ($id > 0) {
                    $opts['ids'][] = $id;
                }
            }
        }
    }
    $opts['ids'] = array_values(array_unique($opts['ids']));
    return $opts;
}

function cluster_rebuild_strip_text(string $html): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    return mb_strtolower($text, 'UTF-8');
}

function cluster_rebuild_taxonomy(): array
{
    return [
        'journal' => [
            ['code' => 'meta-ads', 'keywords' => ['meta ads', 'facebook ads', 'business manager', 'bm', 'meta']],
            ['code' => 'tiktok-funnels', 'keywords' => ['tiktok', 'short-form', 'short form']],
            ['code' => 'telegram-retention', 'keywords' => ['telegram', 'mini app', 'mini apps', 'retention', 'community']],
            ['code' => 'ai-creatives', 'keywords' => ['ai-креатив', 'ai creative', 'prompt', 'ugc', 'creative generation']],
            ['code' => 'tracking-loss', 'keywords' => ['атрибуц', 'attribution', 'postback', 'tracker', 'signal loss', 'privacy']],
            ['code' => 'market-demand', 'keywords' => ['nutra', 'finance', 'igaming', 'crypto', 'спрос']],
            ['code' => 'creative-shelf', 'keywords' => ['creative shelf', 'срок жизни креатив', 'выгорание креатив']],
            ['code' => 'source-volatility', 'keywords' => ['source mix', 'волатильность источников', 'источник трафика']],
            ['code' => 'offer-selection', 'keywords' => ['оффер', 'offer selection', 'маржа', 'margin']],
            ['code' => 'team-handoff', 'keywords' => ['handoff', 'передача', 'связк']],
            ['code' => 'team-structure', 'keywords' => ['найм', 'hiring', 'команда', 'operator', 'backstage']],
            ['code' => 'farm-economics', 'keywords' => ['фарм', 'farm economics', 'account lifespan', 'срок жизни аккаунтов']],
        ],
        'playbooks' => [
            ['code' => 'facebook-farm', 'keywords' => ['facebook farm', 'фарм', 'прогрев']],
            ['code' => 'cloaking-routing', 'keywords' => ['cloaking', 'routing', 'маршрутизац']],
            ['code' => 'tracker-postback', 'keywords' => ['tracker', 'postback', 'макрос', 'click id']],
            ['code' => 'anti-detect', 'keywords' => ['anti-detect', 'browser profile', 'антидетект']],
            ['code' => 'creative-testing', 'keywords' => ['матрица тестирования', 'creative testing', 'итерац', 'batch']],
            ['code' => 'payments-recovery', 'keywords' => ['платеж', 'checkout', 'домен', 'хостинг', 'лендинг']],
            ['code' => 'launch-qa', 'keywords' => ['launch qa', 'preflight', 'qa', 'чеклист запуска']],
            ['code' => 'bm-trust', 'keywords' => ['bm', 'trust', 'business manager', 'бан']],
            ['code' => 'handoff-templates', 'keywords' => ['handoff', 'template', 'ассистент', 'баер']],
            ['code' => 'failover-routing', 'keywords' => ['failover', 'reserve', 'резерв', 'routing']],
            ['code' => 'creative-review', 'keywords' => ['creative review', 'review loop', 'выгорание']],
            ['code' => 'offer-screening', 'keywords' => ['screening', 'скрининг офферов', 'approval', 'margin']],
            ['code' => 'tracking-hygiene', 'keywords' => ['hygiene', 'валидация макросов', 'tracker hygiene']],
            ['code' => 'rollback-planning', 'keywords' => ['rollback', 'релиз', 'неудачн']],
        ],
        'signals' => [
            ['code' => 'meta-policy', 'keywords' => ['meta', 'policy', 'модерац', 'enforcement']],
            ['code' => 'telegram-rules', 'keywords' => ['telegram', 'монетизац', 'restriction', 'ограничен']],
            ['code' => 'tiktok-restrictions', 'keywords' => ['tiktok', 'ads restriction', 'commerce']],
            ['code' => 'cis-regulation', 'keywords' => ['снг', 'regulation', 'регулятор', 'законопроект']],
            ['code' => 'privacy-attribution', 'keywords' => ['privacy', 'атрибуц', 'data retention']],
            ['code' => 'payments-compliance', 'keywords' => ['payment', 'compliance', 'платеж', 'комплаенс']],
            ['code' => 'account-trust', 'keywords' => ['trust', 'аккаунт', 'quality pressure']],
            ['code' => 'moderation-volatility', 'keywords' => ['moderation', 'модерац', 'source volatility']],
            ['code' => 'measurement-loss', 'keywords' => ['measurement', 'измерен', 'signal loss']],
            ['code' => 'politics', 'keywords' => ['политик', 'government', 'власти', 'sanctions', 'санкц']],
            ['code' => 'business-analysis', 'keywords' => ['business', 'компани', 'сектор', 'market stress', 'бизнес']],
            ['code' => 'exchange-analysis', 'keywords' => ['бирж', 'exchange', 'ликвидност', 'market structure']],
            ['code' => 'crypto-signals', 'keywords' => ['crypto', 'крипто', 'bitcoin', 'btc', 'eth', 'token']],
            ['code' => 'russia-business', 'keywords' => ['росси', 'business news', 'российск', 'рынок россии']],
            ['code' => 'russia-law', 'keywords' => ['закон', 'bill', 'law', 'законодательств', 'применения']],
            ['code' => 'central-bank', 'keywords' => ['центробанк', 'центральный банк', 'key rate', 'ставк']],
            ['code' => 'ad-budget-shifts', 'keywords' => ['рекламн', 'budget', 'бюджет', 'ad market']],
            ['code' => 'messenger-economics', 'keywords' => ['messenger', 'мессенджер', 'distribution']],
        ],
        'fun' => [
            ['code' => 'agency-folklore', 'keywords' => ['агентск', 'фольклор', 'media buyer', 'медиабаер']],
            ['code' => 'farm-drama', 'keywords' => ['фарм', 'суевер', 'drama']],
            ['code' => 'creative-burnout', 'keywords' => ['выгорание креатив', 'creative fatigue', 'dark comedy']],
            ['code' => 'tracker-chaos', 'keywords' => ['трекер', 'postback', 'хаос', 'абсурд']],
            ['code' => 'team-memes', 'keywords' => ['мем', 'ритуал', 'backstage ritual']],
            ['code' => 'moderation-tragicomedy', 'keywords' => ['модерац', 'tragicomedy', 'трагикомед']],
            ['code' => 'night-dashboard', 'keywords' => ['ночн', 'dashboard', 'dashboards']],
            ['code' => 'assistant-buyer', 'keywords' => ['ассистент', 'баер', 'buyer', 'assistant']],
            ['code' => 'launch-rituals', 'keywords' => ['launch', 'ритуал', 'panic']],
            ['code' => 'office-theatre', 'keywords' => ['office', 'театр', 'review']],
            ['code' => 'bm-quest', 'keywords' => ['bm', 'quest', 'героическ']],
            ['code' => 'telegram-tribe', 'keywords' => ['telegram', 'tribe', 'чат']],
            ['code' => 'burnout-comedy', 'keywords' => ['выгорание', 'burnout', 'комедия']],
            ['code' => 'spreadsheet-cult', 'keywords' => ['таблиц', 'spreadsheet', 'метрик', 'суевер']],
        ],
    ];
}

function cluster_rebuild_pick_code(array $row, array $taxonomy): string
{
    $title = cluster_rebuild_strip_text((string)($row['title'] ?? ''));
    $excerpt = cluster_rebuild_strip_text((string)($row['excerpt_html'] ?? ''));
    $content = cluster_rebuild_strip_text((string)($row['content_html'] ?? ''));
    $haystack = $title . "\n" . $excerpt . "\n" . $content;
    $bestCode = '';
    $bestScore = -1;
    foreach ($taxonomy as $entry) {
        $score = 0;
        foreach ((array)($entry['keywords'] ?? []) as $kw) {
            $kw = mb_strtolower(trim((string)$kw), 'UTF-8');
            if ($kw === '') {
                continue;
            }
            if ($title !== '' && mb_strpos($title, $kw) !== false) {
                $score += 5;
            }
            if ($excerpt !== '' && mb_strpos($excerpt, $kw) !== false) {
                $score += 3;
            }
            if ($content !== '' && mb_strpos($content, $kw) !== false) {
                $score += 1;
            }
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestCode = (string)($entry['code'] ?? '');
        }
    }
    return $bestScore > 0 ? $bestCode : '';
}

function cluster_rebuild_rebuild_popularity(mysqli $db, $framework): void
{
    require_once DIR . 'core/libs/examples_popularity.php';
    examples_popularity_ensure_tables($db);

    $hasLang = function_exists('examples_table_has_lang_column') ? examples_table_has_lang_column($db) : false;
    $hasCluster = function_exists('examples_table_has_column') ? examples_table_has_column($db, 'cluster_code') : false;
    $hasSection = function_exists('examples_table_has_column') ? examples_table_has_column($db, 'material_section') : false;

    $langSelect = $hasLang ? 'lang_code' : "'en' AS lang_code";
    $clusterSelect = $hasCluster ? 'cluster_code' : "'' AS cluster_code";
    $sectionSelect = $hasSection ? 'material_section' : "'journal' AS material_section";

    $articles = $framework->DBRecords(
        "SELECT id, domain_host, {$langSelect}, slug, {$clusterSelect}, {$sectionSelect}
         FROM examples_articles
         WHERE is_published = 1
           AND COALESCE(slug, '') <> ''
         ORDER BY id DESC"
    );

    $articleRows = [];
    $clusterAgg = [];
    foreach ((array)$articles as $row) {
        $articleId = (int)($row['id'] ?? 0);
        $host = strtolower(trim((string)($row['domain_host'] ?? '')));
        $lang = trim((string)($row['lang_code'] ?? 'en'));
        $slug = trim((string)($row['slug'] ?? ''));
        $cluster = trim((string)($row['cluster_code'] ?? ''));
        $section = trim((string)($row['material_section'] ?? 'journal'));
        if (!in_array($section, ['journal', 'playbooks', 'signals', 'fun'], true)) {
            $section = 'journal';
        }
        if ($articleId <= 0 || $slug === '') {
            continue;
        }
        $path = examples_article_url_path($slug, $cluster, $host, $section);
        $paths = array_values(array_unique([$path, rtrim($path, '/'), rtrim($path, '/') . '/']));
        $pathConditions = [];
        foreach ($paths as $variant) {
            $variant = trim((string)$variant);
            if ($variant === '') {
                continue;
            }
            $pathConditions[] = "path = '" . mysqli_real_escape_string($db, $variant) . "'";
        }
        if (empty($pathConditions)) {
            continue;
        }
        $hostWhere = $host !== ''
            ? "AND (host = '" . mysqli_real_escape_string($db, $host) . "' OR COALESCE(host, '') = '')"
            : '';
        $viewRows = $framework->DBRecords(
            "SELECT COUNT(*) AS cnt
             FROM analytics_visits
             WHERE COALESCE(is_bot, 0) = 0
               {$hostWhere}
               AND (" . implode(' OR ', $pathConditions) . ')'
        );
        $views = (int)($viewRows[0]['cnt'] ?? 0);
        $articleRows[] = [
            'article_id' => $articleId,
            'domain_host' => $host,
            'lang_code' => $lang,
            'material_section' => $section,
            'cluster_code' => $cluster,
            'slug' => $slug,
            'views_count' => $views,
        ];
        if ($cluster !== '') {
            $aggKey = $host . '|' . $lang . '|' . $section . '|' . $cluster;
            if (!isset($clusterAgg[$aggKey])) {
                $clusterAgg[$aggKey] = [
                    'domain_host' => $host,
                    'lang_code' => $lang,
                    'material_section' => $section,
                    'cluster_code' => $cluster,
                    'cluster_label' => examples_cluster_label($cluster, $lang),
                    'views_count' => 0,
                ];
            }
            $clusterAgg[$aggKey]['views_count'] += $views;
        }
    }

    @mysqli_query($db, "TRUNCATE TABLE examples_article_popularity_cache");
    @mysqli_query($db, "TRUNCATE TABLE examples_cluster_popularity_cache");

    foreach ($articleRows as $row) {
        $sql = sprintf(
            "INSERT INTO examples_article_popularity_cache
            (article_id, domain_host, lang_code, material_section, cluster_code, slug, views_count, updated_at)
            VALUES (%d, '%s', '%s', '%s', '%s', '%s', %d, NOW())",
            (int)$row['article_id'],
            mysqli_real_escape_string($db, (string)$row['domain_host']),
            mysqli_real_escape_string($db, (string)$row['lang_code']),
            mysqli_real_escape_string($db, (string)$row['material_section']),
            mysqli_real_escape_string($db, (string)$row['cluster_code']),
            mysqli_real_escape_string($db, (string)$row['slug']),
            (int)$row['views_count']
        );
        @mysqli_query($db, $sql);
    }

    foreach ($clusterAgg as $row) {
        $sql = sprintf(
            "INSERT INTO examples_cluster_popularity_cache
            (domain_host, lang_code, material_section, cluster_code, cluster_label, views_count, updated_at)
            VALUES ('%s', '%s', '%s', '%s', '%s', %d, NOW())",
            mysqli_real_escape_string($db, (string)$row['domain_host']),
            mysqli_real_escape_string($db, (string)$row['lang_code']),
            mysqli_real_escape_string($db, (string)$row['material_section']),
            mysqli_real_escape_string($db, (string)$row['cluster_code']),
            mysqli_real_escape_string($db, (string)$row['cluster_label']),
            (int)$row['views_count']
        );
        @mysqli_query($db, $sql);
    }
}

$opts = cluster_rebuild_parse_args(array_slice($_SERVER['argv'] ?? [], 1));
$framework = new FRMWRK();
$db = $framework->DB();
if (!$db) {
    cluster_rebuild_echo('DB connection failed.');
    exit(1);
}
if (!examples_table_exists($db)) {
    cluster_rebuild_echo('examples_articles table is missing.');
    exit(1);
}

$where = ["is_published = 1"];
if (!$opts['all']) {
    $where[] = "COALESCE(cluster_code, '') IN ('', 'general', 'obshchiy', 'b2b', 'research', 'dev', 'theory')";
}
if ($opts['section'] !== '' && examples_table_has_column($db, 'material_section')) {
    $where[] = "material_section = '" . mysqli_real_escape_string($db, $opts['section']) . "'";
}
if (!empty($opts['ids'])) {
    $where[] = 'id IN (' . implode(', ', array_map('intval', $opts['ids'])) . ')';
}

$rows = $framework->DBRecords(
    "SELECT id, title, excerpt_html, content_html, cluster_code, "
    . (examples_table_has_column($db, 'material_section') ? 'material_section' : "'journal' AS material_section")
    . ", "
    . (examples_table_has_lang_column($db) ? 'lang_code' : "'ru' AS lang_code")
    . "
     FROM examples_articles
     WHERE " . implode(' AND ', $where) . "
     ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
     LIMIT " . (int)$opts['limit']
);

$taxonomy = cluster_rebuild_taxonomy();
$updated = 0;
$skipped = 0;

foreach ((array)$rows as $row) {
    $id = (int)($row['id'] ?? 0);
    $section = trim((string)($row['material_section'] ?? 'journal'));
    if (!isset($taxonomy[$section])) {
        $section = 'journal';
    }
    $current = examples_normalize_cluster((string)($row['cluster_code'] ?? ''), 'ru');
    $newCode = cluster_rebuild_pick_code($row, $taxonomy[$section]);
    if ($newCode === '') {
        $skipped++;
        cluster_rebuild_echo("skip #{$id}: no strong theme match");
        continue;
    }
    if ($newCode === $current) {
        $skipped++;
        continue;
    }
    cluster_rebuild_echo(
        ($opts['dry_run'] ? 'plan' : 'update') . " #{$id} [{$section}] {$current} -> {$newCode} (" . examples_cluster_label($newCode, 'ru') . ')'
    );
    if (!$opts['dry_run']) {
        $sql = "UPDATE examples_articles
                SET cluster_code = '" . mysqli_real_escape_string($db, $newCode) . "',
                    updated_at = NOW()
                WHERE id = {$id}
                LIMIT 1";
        if (@mysqli_query($db, $sql)) {
            $updated++;
        }
    }
}

cluster_rebuild_echo('processed=' . count((array)$rows) . ', updated=' . $updated . ', skipped=' . $skipped . ', dry_run=' . ($opts['dry_run'] ? 'yes' : 'no'));

if (!$opts['dry_run'] && $updated > 0) {
    cluster_rebuild_rebuild_popularity($db, $framework);
    cluster_rebuild_echo('popularity caches rebuilt');
    if (function_exists('page_html_cache_purge_content_routes')) {
        $purged = page_html_cache_purge_content_routes();
        cluster_rebuild_echo('html cache purged: deleted=' . (int)$purged);
    }
}

exit(0);
