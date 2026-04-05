<div class="container-fluid mt-4">
    <?php
    $isEditorScreen =
        !empty($editTool)
        || isset($_GET['create'])
        || (
            (string)($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && (string)($_POST['action'] ?? '') === 'save_tool'
        );
    ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <?php if ($isEditorScreen): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><?= !empty($editTool) ? 'Edit Tool' : 'Create Tool' ?></h5>
            <a class="btn btn-outline-secondary" href="/adminpanel/tools/">Back to list</a>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="save_tool">
                    <input type="hidden" name="tool_id" value="<?= (int)($editTool['id'] ?? 0) ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input class="form-control" name="name" required value="<?= htmlspecialchars((string)($editTool['name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Slug</label>
                            <input class="form-control" name="slug" placeholder="auto-from-name" value="<?= htmlspecialchars((string)($editTool['slug'] ?? '')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Domain Host</label>
                            <input class="form-control" name="domain_host" placeholder="empty = all domains" value="<?= htmlspecialchars((string)($editTool['domain_host'] ?? '')) ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Lang</label>
                            <?php $editLang = strtolower((string)($editTool['lang_code'] ?? 'en')); ?>
                            <select class="form-select" name="lang_code">
                                <option value="en" <?= ($editLang === 'en') ? 'selected' : '' ?>>EN</option>
                                <option value="ru" <?= ($editLang === 'ru') ? 'selected' : '' ?>>RU</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Icon</label>
                            <input class="form-control" name="icon_emoji" value="<?= htmlspecialchars((string)($editTool['icon_emoji'] ?? 'IP')) ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Sort</label>
                            <input class="form-control" type="number" name="sort_order" value="<?= (int)($editTool['sort_order'] ?? 0) ?>">
                        </div>

                        <div class="col-md-10">
                            <label class="form-label">Short Description</label>
                            <input class="form-control" name="description_text" required value="<?= htmlspecialchars((string)($editTool['description_text'] ?? '')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Published</label>
                            <select class="form-select" name="is_published">
                                <option value="0" <?= ((int)($editTool['is_published'] ?? 1) === 0) ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= ((int)($editTool['is_published'] ?? 1) === 1) ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Page Heading</label>
                            <input class="form-control" name="page_heading" value="<?= htmlspecialchars((string)($editTool['page_heading'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Page Subheading</label>
                            <input class="form-control" name="page_subheading" value="<?= htmlspecialchars((string)($editTool['page_subheading'] ?? '')) ?>">
                        </div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12"><h6 class="mb-0">SEO / Meta</h6></div>

                        <div class="col-md-6">
                            <label class="form-label">SEO Title</label>
                            <input class="form-control" name="seo_title" value="<?= htmlspecialchars((string)($editTool['seo_title'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SEO Keywords</label>
                            <input class="form-control" name="seo_keywords" value="<?= htmlspecialchars((string)($editTool['seo_keywords'] ?? '')) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">SEO Description</label>
                            <textarea class="form-control" rows="2" name="seo_description"><?= htmlspecialchars((string)($editTool['seo_description'] ?? '')) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">OG Title</label>
                            <input class="form-control" name="og_title" value="<?= htmlspecialchars((string)($editTool['og_title'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">OG Image URL</label>
                            <input class="form-control" name="og_image" value="<?= htmlspecialchars((string)($editTool['og_image'] ?? '')) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">OG Description</label>
                            <textarea class="form-control" rows="2" name="og_description"><?= htmlspecialchars((string)($editTool['og_description'] ?? '')) ?></textarea>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><?= !empty($editTool) ? 'Update Tool' : 'Create Tool' ?></button>
                            <a class="btn btn-outline-secondary" href="/adminpanel/tools/">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-primary" href="/adminpanel/tools/?create=1">Create Tool</a>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Tools</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tool</th>
                            <th>Slug</th>
                            <th>Domain</th>
                            <th>Lang</th>
                            <th>Published</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($toolsRows)): foreach ($toolsRows as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars((string)($row['icon_emoji'] ?? 'IP')) ?> <?= htmlspecialchars((string)$row['name']) ?></td>
                                <td><code><?= htmlspecialchars((string)$row['slug']) ?></code></td>
                                <td><?= htmlspecialchars((string)($row['domain_host'] ?: 'all')) ?></td>
                                <td><?= htmlspecialchars(strtoupper((string)($row['lang_code'] ?? 'en'))) ?></td>
                                <td><?= ((int)$row['is_published'] === 1) ? 'yes' : 'no' ?></td>
                                <td><?= htmlspecialchars((string)$row['updated_at']) ?></td>
                                <td class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="/adminpanel/tools/?edit=<?= (int)$row['id'] ?>">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Delete this tool?');">
                                        <input type="hidden" name="action" value="delete_tool">
                                        <input type="hidden" name="tool_id" value="<?= (int)$row['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="8" class="text-muted">No tools yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
