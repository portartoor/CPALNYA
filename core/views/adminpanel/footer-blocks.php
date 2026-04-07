<div class="container-fluid mt-4">
    <?php
    $isEditorScreen =
        !empty($editBlock)
        || isset($_GET['create'])
        || (
            (string)($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && (string)($_POST['action'] ?? '') === 'save_footer_block'
        );
    $allowedScopes = ['all', 'journal', 'playbooks', 'signals', 'fun', 'contact'];
    $allowedStyles = ['editorial-note', 'mini-story', 'memo', 'allegory', 'field-note'];
    ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <?php if ($isEditorScreen): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><?= !empty($editBlock) ? 'Edit Footer SEO Block' : 'Create Footer SEO Block' ?></h5>
            <a class="btn btn-outline-secondary" href="/adminpanel/footer-blocks/">Back to list</a>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="save_footer_block">
                    <input type="hidden" name="block_id" value="<?= (int)($editBlock['id'] ?? 0) ?>">

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="block_title" required value="<?= htmlspecialchars((string)($editBlock['block_title'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kicker</label>
                            <input class="form-control" name="block_kicker" value="<?= htmlspecialchars((string)($editBlock['block_kicker'] ?? '')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Domain Host</label>
                            <input class="form-control" name="domain_host" placeholder="cpalnya.ru or empty" value="<?= htmlspecialchars((string)($editBlock['domain_host'] ?? '')) ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Lang</label>
                            <?php $editLang = strtolower((string)($editBlock['lang_code'] ?? 'ru')); ?>
                            <select class="form-select" name="lang_code">
                                <option value="ru" <?= ($editLang === 'ru') ? 'selected' : '' ?>>RU</option>
                                <option value="en" <?= ($editLang === 'en') ? 'selected' : '' ?>>EN</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Section</label>
                            <?php $editScope = strtolower((string)($editBlock['section_scope'] ?? 'all')); ?>
                            <select class="form-select" name="section_scope">
                                <?php foreach ($allowedScopes as $scope): ?>
                                    <option value="<?= htmlspecialchars($scope) ?>" <?= ($editScope === $scope) ? 'selected' : '' ?>><?= htmlspecialchars($scope) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Sort</label>
                            <input class="form-control" type="number" name="sort_order" value="<?= (int)($editBlock['sort_order'] ?? 0) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Style Variant</label>
                            <?php $editStyle = strtolower((string)($editBlock['style_variant'] ?? 'editorial-note')); ?>
                            <select class="form-select" name="style_variant">
                                <?php foreach ($allowedStyles as $style): ?>
                                    <option value="<?= htmlspecialchars($style) ?>" <?= ($editStyle === $style) ? 'selected' : '' ?>><?= htmlspecialchars($style) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Active</label>
                            <select class="form-select" name="is_active">
                                <option value="1" <?= ((int)($editBlock['is_active'] ?? 1) === 1) ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= ((int)($editBlock['is_active'] ?? 1) === 0) ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Body HTML</label>
                            <textarea class="form-control" rows="10" name="body_html" required><?= htmlspecialchars((string)($editBlock['body_html'] ?? '')) ?></textarea>
                            <div class="form-text">Use simple HTML like <code>&lt;p&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>.</div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><?= !empty($editBlock) ? 'Update Block' : 'Create Block' ?></button>
                            <a class="btn btn-outline-secondary" href="/adminpanel/footer-blocks/">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-end gap-2 mb-3">
            <form method="POST" onsubmit="return confirm('Refresh the default footer block library for cpalnya.ru?');">
                <input type="hidden" name="action" value="refresh_default_footer_blocks">
                <button class="btn btn-outline-secondary" type="submit">Refresh Default Library</button>
            </form>
            <a class="btn btn-primary" href="/adminpanel/footer-blocks/?create=1">Create Block</a>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Footer SEO Blocks</h5>
                <div class="small text-muted">Random block above footer, DB-managed</div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Scope</th>
                            <th>Style</th>
                            <th>Lang</th>
                            <th>Domain</th>
                            <th>Active</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($footerBlocksRows)): foreach ($footerBlocksRows as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string)$row['block_title']) ?></div>
                                    <?php if ((string)($row['block_kicker'] ?? '') !== ''): ?>
                                        <div class="small text-muted"><?= htmlspecialchars((string)$row['block_kicker']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string)$row['section_scope']) ?></td>
                                <td><code><?= htmlspecialchars((string)$row['style_variant']) ?></code></td>
                                <td><?= htmlspecialchars(strtoupper((string)$row['lang_code'])) ?></td>
                                <td><?= htmlspecialchars((string)($row['domain_host'] ?: 'all')) ?></td>
                                <td><?= ((int)$row['is_active'] === 1) ? 'yes' : 'no' ?></td>
                                <td><?= htmlspecialchars((string)$row['updated_at']) ?></td>
                                <td class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="/adminpanel/footer-blocks/?edit=<?= (int)$row['id'] ?>">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Delete this block?');">
                                        <input type="hidden" name="action" value="delete_footer_block">
                                        <input type="hidden" name="block_id" value="<?= (int)$row['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="9" class="text-muted">No footer blocks yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
