<?php
$commentsRows = is_array($commentsRows ?? null) ? $commentsRows : [];
$articleOptions = is_array($articleOptions ?? null) ? $articleOptions : [];
$filterArticleId = (int)($filterArticleId ?? 0);
$filterUserId = (int)($filterUserId ?? 0);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Public Comments</h4>
            </div>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)($messageType ?? 'success'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Article</label>
                    <select class="form-select" name="article_id">
                        <option value="0">All articles</option>
                        <?php foreach ($articleOptions as $article): ?>
                            <option value="<?= (int)($article['id'] ?? 0) ?>" <?= ((int)($article['id'] ?? 0) === $filterArticleId) ? 'selected' : '' ?>>
                                #<?= (int)($article['id'] ?? 0) ?> <?= htmlspecialchars((string)($article['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?> [<?= htmlspecialchars((string)($article['material_section'] ?? ''), ENT_QUOTES, 'UTF-8') ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">User ID</label>
                    <input class="form-control" type="number" min="0" name="user_id" value="<?= $filterUserId > 0 ? $filterUserId : '' ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="/adminpanel/comments/">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Article</th>
                            <th>User</th>
                            <th>Meta</th>
                            <th style="min-width: 360px;">Comment</th>
                            <th style="min-width: 210px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commentsRows as $row): ?>
                            <tr>
                                <td>#<?= (int)($row['id'] ?? 0) ?></td>
                                <td>
                                    <div><strong>#<?= (int)($row['content_id'] ?? 0) ?></strong></div>
                                    <div><?= htmlspecialchars((string)($row['article_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars((string)($row['material_section'] ?? ''), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string)($row['cluster_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars((string)($row['display_name'] ?? $row['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                    <div class="text-muted">@<?= htmlspecialchars((string)($row['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="d-block text-muted">rating: <?= (int)($row['comment_rating'] ?? 0) ?> / comments: <?= (int)($row['comments_count'] ?? 0) ?></small>
                                    <small class="<?= ((int)($row['is_banned'] ?? 0) === 1) ? 'text-danger' : 'text-success' ?>"><?= ((int)($row['is_banned'] ?? 0) === 1) ? 'banned' : 'active' ?></small>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars((string)($row['section_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <small class="d-block text-muted">score: <?= (int)($row['rating_score'] ?? 0) ?> / +<?= (int)($row['votes_up'] ?? 0) ?> / -<?= (int)($row['votes_down'] ?? 0) ?></small>
                                    <small class="text-muted"><?= htmlspecialchars((string)($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td>
                                    <form method="post" class="d-grid gap-2">
                                        <input type="hidden" name="comments_action" value="edit_comment">
                                        <input type="hidden" name="comment_id" value="<?= (int)($row['id'] ?? 0) ?>">
                                        <textarea class="form-control" name="body_markdown" rows="6"><?= htmlspecialchars((string)($row['body_markdown'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save text</button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-grid gap-2">
                                        <form method="post">
                                            <input type="hidden" name="comments_action" value="toggle_ban_user">
                                            <input type="hidden" name="user_id" value="<?= (int)($row['user_id'] ?? 0) ?>">
                                            <button class="btn btn-sm btn-warning" type="submit"><?= ((int)($row['is_banned'] ?? 0) === 1) ? 'Unban user' : 'Ban user' ?></button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="comments_action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?= (int)($row['id'] ?? 0) ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete comment</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($commentsRows)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No comments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
