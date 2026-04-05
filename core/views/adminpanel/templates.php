<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">Add Template</h5></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="save_template">
                <input type="hidden" name="template_id" value="0">
                <div class="col-lg-2">
                    <label class="form-label">Key</label>
                    <input type="text" name="template_key" class="form-control" placeholder="landing_v2" required>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Display Name</label>
                    <input type="text" name="display_name" class="form-control" placeholder="Landing V2" required>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Shell</label>
                    <select name="shell_view" class="form-select">
                        <option value="simple">simple</option>
                        <option value="dashboard">dashboard</option>
                        <option value="enterprise">enterprise</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Main View File</label>
                    <input type="text" name="main_view_file" class="form-control" value="main.php" required>
                </div>
                <div class="col-lg-1">
                    <label class="form-label">Model</label>
                    <input type="text" name="model_file" class="form-control" value="main.php" required>
                </div>
                <div class="col-lg-1">
                    <label class="form-label">Control</label>
                    <input type="text" name="control_file" class="form-control" value="main.php" required>
                </div>
                <div class="col-lg-1">
                    <label class="form-label">On</label>
                    <select name="is_active" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Add Template</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Templates</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Key</th>
                        <th>Name</th>
                        <th>Shell</th>
                        <th>Main View</th>
                        <th>Model</th>
                        <th>Control</th>
                        <th>Status</th>
                        <th>Preview</th>
                        <th style="min-width: 380px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($templates)): foreach ($templates as $tpl): ?>
                    <?php $tplKey = (string)($tpl['template_key'] ?? ''); ?>
                    <tr>
                        <td><?= (int)$tpl['id'] ?></td>
                        <td><code><?= htmlspecialchars($tplKey) ?></code></td>
                        <td><?= htmlspecialchars((string)$tpl['display_name']) ?></td>
                        <td><?= htmlspecialchars((string)$tpl['shell_view']) ?></td>
                        <td><code><?= htmlspecialchars((string)$tpl['main_view_file']) ?></code></td>
                        <td><code><?= htmlspecialchars((string)$tpl['model_file']) ?></code></td>
                        <td><code><?= htmlspecialchars((string)$tpl['control_file']) ?></code></td>
                        <td><?= ((int)$tpl['is_active'] === 1) ? 'active' : 'disabled' ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" href="/?template_preview=<?= urlencode($tplKey) ?>">Open</a>
                        </td>
                        <td>
                            <form method="post" class="row g-1">
                                <input type="hidden" name="action" value="save_template">
                                <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">
                                <div class="col-2"><input type="text" name="template_key" class="form-control form-control-sm" value="<?= htmlspecialchars($tplKey) ?>" required></div>
                                <div class="col-2"><input type="text" name="display_name" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$tpl['display_name']) ?>" required></div>
                                <div class="col-1">
                                    <select name="shell_view" class="form-select form-select-sm">
                                        <option value="simple" <?= ((string)$tpl['shell_view'] === 'simple') ? 'selected' : '' ?>>simple</option>
                                        <option value="dashboard" <?= ((string)$tpl['shell_view'] === 'dashboard') ? 'selected' : '' ?>>dashboard</option>
                                        <option value="enterprise" <?= ((string)$tpl['shell_view'] === 'enterprise') ? 'selected' : '' ?>>enterprise</option>
                                    </select>
                                </div>
                                <div class="col-2"><input type="text" name="main_view_file" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$tpl['main_view_file']) ?>" required></div>
                                <div class="col-1"><input type="text" name="model_file" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$tpl['model_file']) ?>" required></div>
                                <div class="col-1"><input type="text" name="control_file" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$tpl['control_file']) ?>" required></div>
                                <div class="col-1">
                                    <select name="is_active" class="form-select form-select-sm">
                                        <option value="1" <?= ((int)$tpl['is_active'] === 1) ? 'selected' : '' ?>>On</option>
                                        <option value="0" <?= ((int)$tpl['is_active'] === 0) ? 'selected' : '' ?>>Off</option>
                                    </select>
                                </div>
                                <div class="col-1">
                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Save</button>
                                </div>
                            </form>
                            <form method="post" class="mt-1" onsubmit="return confirm('Delete template?');">
                                <input type="hidden" name="action" value="delete_template">
                                <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="10" class="text-muted">No templates configured</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
