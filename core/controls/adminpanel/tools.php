<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
require_once DIR . '/core/controls/tools/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$message = '';
$messageType = '';
$editTool = null;

if ($DB) {
    tools_ensure_schema($DB);
}

function admin_tools_clean_host(string $raw): string
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

function admin_tools_unique_slug(mysqli $DB, string $slugBase, string $domainHost, string $langCode, int $currentId = 0): string
{
    $slug = tools_slugify($slugBase);
    $suffix = 1;
    $domainSafe = mysqli_real_escape_string($DB, $domainHost);
    $langSafe = mysqli_real_escape_string($DB, tools_normalize_lang($langCode));
    while (true) {
        $slugSafe = mysqli_real_escape_string($DB, $slug);
        $idCond = $currentId > 0 ? " AND id <> " . (int)$currentId : "";
        $rows = mysqli_query(
            $DB,
            "SELECT id
             FROM public_tools
             WHERE slug = '{$slugSafe}'
               AND domain_host = '{$domainSafe}'
               AND lang_code = '{$langSafe}'
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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $DB) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_tool') {
        $toolId = (int)($_POST['tool_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $slugInput = trim((string)($_POST['slug'] ?? ''));
        $domainHost = admin_tools_clean_host((string)($_POST['domain_host'] ?? ''));
        $langCode = tools_normalize_lang((string)($_POST['lang_code'] ?? 'en'));
        $descriptionText = trim((string)($_POST['description_text'] ?? ''));
        $iconEmoji = trim((string)($_POST['icon_emoji'] ?? 'IP'));
        $pageHeading = trim((string)($_POST['page_heading'] ?? ''));
        $pageSubheading = trim((string)($_POST['page_subheading'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isPublished = ((int)($_POST['is_published'] ?? 0) === 1) ? 1 : 0;

        $seoTitle = trim((string)($_POST['seo_title'] ?? ''));
        $seoDescription = trim((string)($_POST['seo_description'] ?? ''));
        $seoKeywords = trim((string)($_POST['seo_keywords'] ?? ''));
        $ogTitle = trim((string)($_POST['og_title'] ?? ''));
        $ogDescription = trim((string)($_POST['og_description'] ?? ''));
        $ogImage = trim((string)($_POST['og_image'] ?? ''));

        if ($name === '' || $descriptionText === '') {
            $message = 'Name and description are required.';
            $messageType = 'danger';
        } else {
            $slugBase = $slugInput !== '' ? tools_slugify($slugInput) : tools_slugify($name);
            $slug = admin_tools_unique_slug($DB, $slugBase, $domainHost, $langCode, $toolId);

            if ($pageHeading === '') {
                $pageHeading = $name;
            }
            if (mb_strlen($iconEmoji) > 24) {
                $iconEmoji = mb_substr($iconEmoji, 0, 24);
            }

            $nameSafe = mysqli_real_escape_string($DB, $name);
            $slugSafe = mysqli_real_escape_string($DB, $slug);
            $domainSafe = mysqli_real_escape_string($DB, $domainHost);
            $langSafe = mysqli_real_escape_string($DB, $langCode);
            $descriptionSafe = mysqli_real_escape_string($DB, $descriptionText);
            $iconSafe = mysqli_real_escape_string($DB, $iconEmoji);
            $headingSafe = mysqli_real_escape_string($DB, $pageHeading);
            $subheadingSafe = mysqli_real_escape_string($DB, $pageSubheading);
            $seoTitleSafe = mysqli_real_escape_string($DB, $seoTitle);
            $seoDescriptionSafe = mysqli_real_escape_string($DB, $seoDescription);
            $seoKeywordsSafe = mysqli_real_escape_string($DB, $seoKeywords);
            $ogTitleSafe = mysqli_real_escape_string($DB, $ogTitle);
            $ogDescriptionSafe = mysqli_real_escape_string($DB, $ogDescription);
            $ogImageSafe = mysqli_real_escape_string($DB, $ogImage);

            if ($toolId > 0) {
                mysqli_query(
                    $DB,
                    "UPDATE public_tools
                     SET domain_host = '{$domainSafe}',
                         lang_code = '{$langSafe}',
                         slug = '{$slugSafe}',
                         name = '{$nameSafe}',
                         description_text = '{$descriptionSafe}',
                         icon_emoji = '{$iconSafe}',
                         page_heading = '{$headingSafe}',
                         page_subheading = '{$subheadingSafe}',
                         is_published = " . (int)$isPublished . ",
                         sort_order = " . (int)$sortOrder . ",
                         seo_title = '{$seoTitleSafe}',
                         seo_description = '{$seoDescriptionSafe}',
                         seo_keywords = '{$seoKeywordsSafe}',
                         og_title = '{$ogTitleSafe}',
                         og_description = '{$ogDescriptionSafe}',
                         og_image = '{$ogImageSafe}',
                         updated_at = NOW()
                     WHERE id = " . (int)$toolId . "
                     LIMIT 1"
                );
                $message = 'Tool updated.';
                $messageType = 'success';
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO public_tools
                        (domain_host, lang_code, slug, name, description_text, icon_emoji, page_heading, page_subheading, is_published, sort_order, seo_title, seo_description, seo_keywords, og_title, og_description, og_image, created_at, updated_at)
                     VALUES
                        ('{$domainSafe}', '{$langSafe}', '{$slugSafe}', '{$nameSafe}', '{$descriptionSafe}', '{$iconSafe}', '{$headingSafe}', '{$subheadingSafe}', " . (int)$isPublished . ", " . (int)$sortOrder . ", '{$seoTitleSafe}', '{$seoDescriptionSafe}', '{$seoKeywordsSafe}', '{$ogTitleSafe}', '{$ogDescriptionSafe}', '{$ogImageSafe}', NOW(), NOW())"
                );
                $message = 'Tool created.';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'delete_tool') {
        $toolId = (int)($_POST['tool_id'] ?? 0);
        if ($toolId > 0) {
            mysqli_query($DB, "DELETE FROM public_tools WHERE id = {$toolId} LIMIT 1");
            $message = 'Tool deleted.';
            $messageType = 'success';
        }
    }
}

if ($DB) {
    $editId = (int)($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $editRows = $FRMWRK->DBRecords("SELECT * FROM public_tools WHERE id = {$editId} LIMIT 1");
        if (!empty($editRows)) {
            $editTool = $editRows[0];
        }
    }
}

$toolsRows = [];
if ($DB) {
    $toolsRows = $FRMWRK->DBRecords(
        "SELECT id, domain_host, lang_code, slug, name, icon_emoji, is_published, sort_order, updated_at
         FROM public_tools
         ORDER BY sort_order DESC, updated_at DESC, id DESC"
    );
}
