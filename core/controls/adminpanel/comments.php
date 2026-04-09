<?php
session_start();

$FRMWRK = new FRMWRK();
$message = '';
$messageType = 'success';

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$db = $FRMWRK->DB();
if ($db && function_exists('public_portal_users_ensure_schema')) {
    public_portal_users_ensure_schema($db);
}
if ($db && function_exists('public_portal_comments_ensure_schema')) {
    public_portal_comments_ensure_schema($db);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $db) {
    $action = trim((string)($_POST['comments_action'] ?? ''));
    $commentId = max(0, (int)($_POST['comment_id'] ?? 0));
    $userId = max(0, (int)($_POST['user_id'] ?? 0));

    if ($action === 'delete_comment' && $commentId > 0) {
        $commentMeta = (array)($FRMWRK->DBRecords("SELECT user_id FROM public_comments WHERE id = {$commentId} LIMIT 1")[0] ?? []);
        mysqli_query($db, "UPDATE public_comments SET is_deleted = 1, updated_at = NOW() WHERE id = {$commentId} LIMIT 1");
        if (function_exists('public_portal_recalculate_comment_stats')) {
            public_portal_recalculate_comment_stats($db, $commentId);
        }
        if (function_exists('public_portal_recalculate_user_rating')) {
            public_portal_recalculate_user_rating($db, (int)($commentMeta['user_id'] ?? 0));
        }
        $message = 'Comment deleted.';
    } elseif ($action === 'toggle_ban_user' && $userId > 0) {
        mysqli_query($db, "UPDATE public_users SET is_banned = IF(is_banned = 1, 0, 1), updated_at = NOW() WHERE id = {$userId} LIMIT 1");
        $message = 'User ban status updated.';
    } elseif ($action === 'edit_comment' && $commentId > 0) {
        $body = trim((string)($_POST['body_markdown'] ?? ''));
        if ($body === '' || (function_exists('mb_strlen') ? mb_strlen($body, 'UTF-8') : strlen($body)) < 4) {
            $message = 'Comment text is too short.';
            $messageType = 'danger';
        } else {
            $bodySafe = mysqli_real_escape_string($db, $body);
            $htmlSafe = mysqli_real_escape_string($db, function_exists('public_portal_markdown_to_html') ? public_portal_markdown_to_html($body) : nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')));
            mysqli_query($db, "UPDATE public_comments SET body_markdown = '{$bodySafe}', body_html = '{$htmlSafe}', edited_at = NOW(), updated_at = NOW() WHERE id = {$commentId} LIMIT 1");
            $message = 'Comment updated.';
        }
    }
}

$filterArticleId = max(0, (int)($_GET['article_id'] ?? 0));
$filterUserId = max(0, (int)($_GET['user_id'] ?? 0));
$commentsRows = function_exists('public_portal_admin_fetch_comments') ? public_portal_admin_fetch_comments($FRMWRK, $filterArticleId, $filterUserId) : [];
$articleOptions = [];
if ($db && function_exists('examples_table_exists') && examples_table_exists($db)) {
    $articleOptions = (array)$FRMWRK->DBRecords(
        "SELECT id, title, material_section
         FROM examples_articles
         WHERE is_published = 1
         ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
         LIMIT 200"
    );
}
