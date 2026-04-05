<div class="container-fluid mt-4">
    <?php
    $isEditorScreen =
        !empty($editRule)
        || isset($_GET['create'])
        || (
            (string)($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && (string)($_POST['action'] ?? '') === 'save_rule'
        );
    ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <?php if ($isEditorScreen): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><?= !empty($editRule) ? 'Edit Threat Rule' : 'Create Threat Rule' ?></h5>
            <a class="btn btn-outline-secondary" href="/adminpanel/threats/">Back to list</a>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="save_rule">
                    <input type="hidden" name="rule_id" value="<?= (int)($editRule['id'] ?? 0) ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" required value="<?= htmlspecialchars((string)($editRule['title'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Match Type</label>
                            <?php $editType = (string)($editRule['match_type'] ?? 'path_or_query_contains'); ?>
                            <select class="form-select" name="match_type">
                                <option value="path_or_query_contains" <?= ($editType === 'path_or_query_contains') ? 'selected' : '' ?>>Path or query contains</option>
                                <option value="path_contains" <?= ($editType === 'path_contains') ? 'selected' : '' ?>>Path contains</option>
                                <option value="query_contains" <?= ($editType === 'query_contains') ? 'selected' : '' ?>>Query contains</option>
                                <option value="ua_contains" <?= ($editType === 'ua_contains') ? 'selected' : '' ?>>User-Agent contains</option>
                                <option value="ip_equals" <?= ($editType === 'ip_equals') ? 'selected' : '' ?>>IP equals</option>
                                <option value="regex_path" <?= ($editType === 'regex_path') ? 'selected' : '' ?>>Regex path</option>
                                <option value="regex_query" <?= ($editType === 'regex_query') ? 'selected' : '' ?>>Regex query</option>
                                <option value="regex_path_or_query" <?= ($editType === 'regex_path_or_query') ? 'selected' : '' ?>>Regex path or query</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Active</label>
                            <select class="form-select" name="is_active">
                                <option value="1" <?= ((int)($editRule['is_active'] ?? 1) === 1) ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= ((int)($editRule['is_active'] ?? 1) === 0) ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Matches</label>
                            <input class="form-control" value="<?= (int)($editRule['match_count'] ?? 0) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pattern</label>
                            <input class="form-control" name="pattern" required value="<?= htmlspecialchars((string)($editRule['pattern'] ?? '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" rows="2" name="notes"><?= htmlspecialchars((string)($editRule['notes'] ?? '')) ?></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><?= !empty($editRule) ? 'Update Rule' : 'Create Rule' ?></button>
                            <a class="btn btn-outline-secondary" href="/adminpanel/threats/">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-primary" href="/adminpanel/threats/?create=1">Create Rule</a>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Threat Rules</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Match Type</th>
                            <th>Pattern</th>
                            <th>Active</th>
                            <th>Matches</th>
                            <th>Last Matched</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($rulesRows)): foreach ($rulesRows as $row): ?>
                        <tr>
                            <td><?= (int)($row['id'] ?? 0) ?></td>
                            <td><?= htmlspecialchars((string)($row['title'] ?? '')) ?></td>
                            <td><code><?= htmlspecialchars((string)($row['match_type'] ?? '')) ?></code></td>
                            <td><code><?= htmlspecialchars((string)($row['pattern'] ?? '')) ?></code></td>
                            <td><?= ((int)($row['is_active'] ?? 0) === 1) ? 'yes' : 'no' ?></td>
                            <td><?= (int)($row['match_count'] ?? 0) ?></td>
                            <td><?= htmlspecialchars((string)($row['last_matched_at'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($row['updated_at'] ?? '')) ?></td>
                            <td class="d-flex gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="/adminpanel/threats/?edit=<?= (int)($row['id'] ?? 0) ?>">Edit</a>
                                <form method="POST" onsubmit="return confirm('Delete this rule?');">
                                    <input type="hidden" name="action" value="delete_rule">
                                    <input type="hidden" name="rule_id" value="<?= (int)($row['id'] ?? 0) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="9" class="text-muted">No threat rules yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
