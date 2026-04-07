<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/controls/examples/_common.php';
$seoSettingsLib = DIR . '/core/libs/seo_generator_settings.php';
if (is_file($seoSettingsLib)) {
    require_once $seoSettingsLib;
}
$indexNowLib = DIR . '/core/libs/indexnow.php';
if (is_file($indexNowLib)) {
    require_once $indexNowLib;
}
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';
$editArticle = null;
$adminExamplesSection = isset($adminExamplesSection) ? trim((string)$adminExamplesSection) : trim((string)($_GET['section'] ?? ''));
if (!in_array($adminExamplesSection, ['journal', 'playbooks'], true)) {
    $adminExamplesSection = '';
}
$adminExamplesTitle = isset($adminExamplesTitle) ? (string)$adminExamplesTitle : ($adminExamplesSection === 'playbooks' ? 'Playbooks' : ($adminExamplesSection === 'journal' ? 'Journal' : 'Articles'));
$adminExamplesBackUrl = isset($adminExamplesBackUrl) ? (string)$adminExamplesBackUrl : ($adminExamplesSection === 'playbooks' ? '/adminpanel/playbooks/' : ($adminExamplesSection === 'journal' ? '/adminpanel/journal/' : '/adminpanel/examples/'));

if (!examples_table_exists($DB)) {
    $message = 'Table examples_articles is missing. Run migration examples_articles_setup.sql first.';
    $messageType = 'warning';
}

function admin_examples_clean_host(string $raw): string
{
    $host = strtolower(trim($raw));
    if (strpos($host, ':') !== false) {
        $host = explode(':', $host, 2)[0];
    }
    if ($host !== '' && !preg_match('/^[a-z0-9.-]+$/', $host)) {
        return '';
    }
    return trim($host, '.');
}

function admin_examples_unique_slug(mysqli $DB, string $slugBase, string $domainHost, string $langCode, int $currentId = 0): string
{
    $slug = examples_slugify($slugBase);
    $suffix = 1;
    $domainSafe = mysqli_real_escape_string($DB, $domainHost);
    $langSafe = mysqli_real_escape_string($DB, examples_normalize_lang($langCode));
    while (true) {
        $slugSafe = mysqli_real_escape_string($DB, $slug);
        $idCond = $currentId > 0 ? " AND id <> " . (int)$currentId : "";
        $rows = mysqli_query(
            $DB,
            "SELECT id
             FROM examples_articles
             WHERE slug = '{$slugSafe}'
               AND COALESCE(domain_host, '') = '{$domainSafe}'
               AND COALESCE(lang_code, 'en') = '{$langSafe}'
               {$idCond}
             LIMIT 1"
        );
        if ($rows && mysqli_num_rows($rows) === 0) {
            return $slug;
        }
        $suffix++;
        $slug = substr($slugBase, 0, 170) . '-' . $suffix;
    }
}

function admin_examples_has_column(mysqli $DB, string $column): bool
{
    $columnSafe = mysqli_real_escape_string($DB, $column);
    $res = mysqli_query(
        $DB,
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'examples_articles'
           AND COLUMN_NAME = '{$columnSafe}'
         LIMIT 1"
    );
    return $res && mysqli_num_rows($res) > 0;
}

if (examples_table_exists($DB) && !admin_examples_has_column($DB, 'material_section')) {
    @mysqli_query($DB, "ALTER TABLE examples_articles ADD COLUMN material_section VARCHAR(32) NOT NULL DEFAULT 'journal' AFTER cluster_code");
    @mysqli_query($DB, "ALTER TABLE examples_articles ADD KEY idx_examples_material_section (material_section)");
}

function admin_examples_public_url(string $domainHost, string $langCode, string $slug, string $clusterCode = '', string $materialSection = 'journal'): string
{
    $slug = trim($slug);
    if ($slug === '') {
        return '';
    }
    $host = admin_examples_clean_host($domainHost);
    if ($host === '') {
        $lang = examples_normalize_lang($langCode);
        $host = ($lang === 'ru') ? 'portcore.ru' : 'portcore.online';
    }
    $clusterCode = examples_slugify($clusterCode);
    $base = ($materialSection === 'playbooks') ? '/playbooks/' : '/journal/';
    if ($clusterCode === '') {
        return 'https://' . $host . $base . rawurlencode($slug);
    }
    return 'https://' . $host . $base . rawurlencode($clusterCode) . '/' . rawurlencode($slug);
}

function admin_examples_indexnow_enabled(mysqli $DB): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    if (!function_exists('seo_gen_settings_get')) {
        $cached = false;
        return $cached;
    }
    $settings = seo_gen_settings_get($DB);
    $cached = is_array($settings) && !empty($settings['indexnow_enabled']) && !empty($settings['indexnow_ping_on_publish']);
    return $cached;
}

function admin_examples_indexnow_enqueue(mysqli $DB, string $url, string $langCode, string $eventType): void
{
    if (!function_exists('indexnow_queue_enqueue')) {
        return;
    }
    if (!admin_examples_indexnow_enabled($DB)) {
        return;
    }
    indexnow_queue_enqueue($DB, $url, [
        'lang_code' => examples_normalize_lang($langCode),
        'source' => 'admin_examples',
        'event_type' => $eventType,
    ]);
}

if (examples_table_exists($DB) && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
    $action = (string)($_POST['action'] ?? '');
    $hasLangColumn = examples_table_has_lang_column($DB);
    $hasClusterColumn = admin_examples_has_column($DB, 'cluster_code');
    $hasSectionColumn = admin_examples_has_column($DB, 'material_section');

    if ($action === 'save_article') {
        $articleId = (int)($_POST['article_id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $slugInput = trim((string)($_POST['slug'] ?? ''));
        $domainHost = admin_examples_clean_host((string)($_POST['domain_host'] ?? ''));
        $langCode = examples_normalize_lang((string)($_POST['lang_code'] ?? 'en'));
        $excerptHtml = trim((string)($_POST['excerpt_html'] ?? ''));
        $contentHtml = trim((string)($_POST['content_html'] ?? ''));
        $authorName = trim((string)($_POST['author_name'] ?? ''));
        $materialSection = trim((string)($_POST['material_section'] ?? ($adminExamplesSection !== '' ? $adminExamplesSection : 'journal')));
        if (!in_array($materialSection, ['journal', 'playbooks'], true)) {
            $materialSection = 'journal';
        }
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isPublished = ((int)($_POST['is_published'] ?? 0) === 1) ? 1 : 0;
        $publishedAtInput = trim((string)($_POST['published_at'] ?? ''));

        if ($title === '' || $contentHtml === '') {
            $message = 'Title and content are required.';
            $messageType = 'danger';
        } else {
            $slugBase = $slugInput !== '' ? examples_slugify($slugInput) : examples_slugify($title);
            $slug = admin_examples_unique_slug($DB, $slugBase, $domainHost, $langCode, $articleId);

            if ($excerptHtml === '') {
                $excerptHtml = htmlspecialchars(examples_build_excerpt($contentHtml, 260), ENT_QUOTES, 'UTF-8');
            }

            $titleSafe = mysqli_real_escape_string($DB, $title);
            $slugSafe = mysqli_real_escape_string($DB, $slug);
            $domainSafe = mysqli_real_escape_string($DB, $domainHost);
            $langSafe = mysqli_real_escape_string($DB, $langCode);
            $excerptSafe = mysqli_real_escape_string($DB, $excerptHtml);
            $contentSafe = mysqli_real_escape_string($DB, $contentHtml);
            $authorSafe = mysqli_real_escape_string($DB, $authorName);

            $publishedAtSql = 'NULL';
            if ($isPublished === 1) {
                if ($publishedAtInput !== '') {
                    $publishedAtSql = "'" . mysqli_real_escape_string($DB, $publishedAtInput) . "'";
                } else {
                    $publishedAtSql = 'NOW()';
                }
            }

            if ($articleId > 0) {
                $existingRows = $FRMWRK->DBRecords("SELECT id, slug, domain_host, " . ($hasLangColumn ? "lang_code," : "'en' AS lang_code,") . ($hasClusterColumn ? "cluster_code" : "'' AS cluster_code") . " FROM examples_articles WHERE id = " . (int)$articleId . " LIMIT 1");
                $oldUrl = '';
                if (!empty($existingRows)) {
                    $old = $existingRows[0];
                    $oldUrl = admin_examples_public_url((string)($old['domain_host'] ?? ''), (string)($old['lang_code'] ?? 'en'), (string)($old['slug'] ?? ''), (string)($old['cluster_code'] ?? ''), (string)($old['material_section'] ?? 'journal'));
                }
                mysqli_query(
                    $DB,
                    "UPDATE examples_articles
                     SET domain_host = '{$domainSafe}',
                         " . ($hasLangColumn ? "lang_code = '{$langSafe}'," : "") . "
                         title = '{$titleSafe}',
                         slug = '{$slugSafe}',
                         excerpt_html = '{$excerptSafe}',
                         content_html = '{$contentSafe}',
                        author_name = '{$authorSafe}',
                        " . ($hasSectionColumn ? "material_section = '" . mysqli_real_escape_string($DB, $materialSection) . "'," : "") . "
                        sort_order = " . (int)$sortOrder . ",
                         is_published = " . (int)$isPublished . ",
                         published_at = {$publishedAtSql},
                         updated_at = NOW()
                     WHERE id = " . (int)$articleId . "
                     LIMIT 1"
                );
                if ($isPublished === 1) {
                    $newClusterCode = '';
                    if ($hasClusterColumn) {
                        $newClusterRows = $FRMWRK->DBRecords("SELECT cluster_code FROM examples_articles WHERE id = " . (int)$articleId . " LIMIT 1");
                        if (!empty($newClusterRows)) {
                            $newClusterCode = (string)($newClusterRows[0]['cluster_code'] ?? '');
                        }
                    }
                    $newUrl = admin_examples_public_url($domainHost, $langCode, $slug, $newClusterCode, $materialSection);
                    if ($oldUrl !== '' && $oldUrl !== $newUrl) {
                        admin_examples_indexnow_enqueue($DB, $oldUrl, $langCode, 'update');
                    }
                    admin_examples_indexnow_enqueue($DB, $newUrl, $langCode, 'update');
                }
                $message = 'Article updated.';
                $messageType = 'success';
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO examples_articles
                        (" . ($hasLangColumn ? "domain_host, lang_code," : "domain_host,") . ($hasSectionColumn ? " material_section," : "") . " title, slug, excerpt_html, content_html, author_name, sort_order, is_published, published_at, created_at, updated_at)
                     VALUES
                        ('{$domainSafe}', " . ($hasLangColumn ? "'{$langSafe}'," : "") . ($hasSectionColumn ? " '" . mysqli_real_escape_string($DB, $materialSection) . "'," : "") . " '{$titleSafe}', '{$slugSafe}', '{$excerptSafe}', '{$contentSafe}', '{$authorSafe}', " . (int)$sortOrder . ", " . (int)$isPublished . ", {$publishedAtSql}, NOW(), NOW())"
                );
                if ($isPublished === 1) {
                    $newClusterCode = '';
                    if ($hasClusterColumn) {
                        $newClusterRows = $FRMWRK->DBRecords("SELECT cluster_code FROM examples_articles WHERE id = LAST_INSERT_ID() LIMIT 1");
                        if (!empty($newClusterRows)) {
                            $newClusterCode = (string)($newClusterRows[0]['cluster_code'] ?? '');
                        }
                    }
                    $newUrl = admin_examples_public_url($domainHost, $langCode, $slug, $newClusterCode, $materialSection);
                    admin_examples_indexnow_enqueue($DB, $newUrl, $langCode, 'publish');
                }
                $message = 'Article created.';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'delete_article') {
        $articleId = (int)($_POST['article_id'] ?? 0);
        if ($articleId > 0) {
            $rows = $FRMWRK->DBRecords("SELECT slug, domain_host, " . ($hasLangColumn ? "lang_code," : "'en' AS lang_code,") . ($hasClusterColumn ? "cluster_code" : "'' AS cluster_code") . " FROM examples_articles WHERE id = {$articleId} LIMIT 1");
            $deletedUrl = '';
            $deletedLang = 'en';
            if (!empty($rows)) {
                $row = $rows[0];
                $deletedLang = (string)($row['lang_code'] ?? 'en');
                $deletedUrl = admin_examples_public_url((string)($row['domain_host'] ?? ''), $deletedLang, (string)($row['slug'] ?? ''), (string)($row['cluster_code'] ?? ''), (string)($row['material_section'] ?? 'journal'));
            }
            mysqli_query($DB, "DELETE FROM examples_articles WHERE id = {$articleId} LIMIT 1");
            if ($deletedUrl !== '') {
                admin_examples_indexnow_enqueue($DB, $deletedUrl, $deletedLang, 'delete');
            }
            $message = 'Article deleted.';
            $messageType = 'success';
        }
    }
}

if (examples_table_exists($DB)) {
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $sectionWhere = '';
        if ($adminExamplesSection !== '' && admin_examples_has_column($DB, 'material_section')) {
            $sectionWhere = " AND material_section = '" . mysqli_real_escape_string($DB, $adminExamplesSection) . "'";
        }
        $editRows = $FRMWRK->DBRecords("SELECT * FROM examples_articles WHERE id = {$editId}{$sectionWhere} LIMIT 1");
        if (!empty($editRows)) {
            $editArticle = $editRows[0];
        }
    }
}

$articles = [];
if (examples_table_exists($DB)) {
    $hasLangColumn = examples_table_has_lang_column($DB);
    $hasAiColumn = admin_examples_has_column($DB, 'is_ai_generated');
    $hasSectionColumn = admin_examples_has_column($DB, 'material_section');
    $sectionFilterSql = '';
    if ($adminExamplesSection !== '' && $hasSectionColumn) {
        $sectionFilterSql = " WHERE material_section = '" . mysqli_real_escape_string($DB, $adminExamplesSection) . "'";
    }
    $articles = $FRMWRK->DBRecords(
        "SELECT id, domain_host, "
        . ($hasLangColumn ? "lang_code," : "'en' AS lang_code,")
        . ($hasAiColumn ? " COALESCE(is_ai_generated, 0) AS is_ai_generated," : " 0 AS is_ai_generated,")
        . ($hasSectionColumn ? " material_section," : " 'journal' AS material_section,")
        . " title, slug, is_published, sort_order, published_at, updated_at
         FROM examples_articles
         {$sectionFilterSql}
         ORDER BY updated_at DESC, id DESC"
    );
}
