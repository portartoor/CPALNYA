<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">Add Mirror Domain</h5></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="save_domain">
                <input type="hidden" name="domain_id" value="0">
                <div class="col-lg-5">
                    <label class="form-label">Domain</label>
                    <input type="text" name="domain" class="form-control" placeholder="example.com" required>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Template</label>
                    <select name="template_view" class="form-select">
                        <?php if (!empty($templateOptions)): foreach ($templateOptions as $tpl): ?>
                            <option value="<?= htmlspecialchars((string)$tpl['template_key']) ?>">
                                <?= htmlspecialchars((string)$tpl['display_name']) ?> [<?= htmlspecialchars((string)$tpl['shell_view']) ?>]
                            </option>
                        <?php endforeach; else: ?>
                            <option value="simple">Public Site [simple]</option>
                            <option value="dashboard">Dashboard UI [dashboard]</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Add Domain</button>
                </div>
                <div class="col-12">
                    <label class="form-label">Google Tag Code (per-site, optional)</label>
                    <textarea name="google_tag_code" class="form-control" rows="3" placeholder="&lt;script&gt;...google tag...&lt;/script&gt;"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Yandex Counter Code (per-site, optional)</label>
                    <textarea name="yandex_counter_code" class="form-control" rows="3" placeholder="&lt;script&gt;...metrika...&lt;/script&gt;"></textarea>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Mirror Domains</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Domain</th>
                        <th>Template</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th style="width: 560px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($domains)): foreach ($domains as $d): ?>
                        <tr>
                            <td><?= (int)$d['id'] ?></td>
                            <td><code><?= htmlspecialchars((string)$d['domain']) ?></code></td>
                            <td><?= htmlspecialchars((string)$d['template_view']) ?></td>
                            <td><?= ((int)$d['is_active'] === 1) ? 'active' : 'disabled' ?></td>
                            <td><?= htmlspecialchars((string)$d['created_at']) ?></td>
                            <td><?= htmlspecialchars((string)($d['updated_at'] ?? '')) ?></td>
                            <td>
                                <form method="post" class="row g-1">
                                    <input type="hidden" name="action" value="save_domain">
                                    <input type="hidden" name="domain_id" value="<?= (int)$d['id'] ?>">
                                    <div class="col-4">
                                        <input type="text" name="domain" class="form-control form-control-sm" value="<?= htmlspecialchars((string)$d['domain']) ?>" required>
                                    </div>
                                    <div class="col-3">
                                        <select name="template_view" class="form-select form-select-sm">
                                            <?php if (!empty($templateOptions)): foreach ($templateOptions as $tpl): ?>
                                                <?php $tplKey = (string)($tpl['template_key'] ?? ''); ?>
                                                <option value="<?= htmlspecialchars($tplKey) ?>" <?= ((string)$d['template_view'] === $tplKey) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars((string)$tpl['display_name']) ?> [<?= htmlspecialchars((string)$tpl['shell_view']) ?>]
                                                </option>
                                            <?php endforeach; else: ?>
                                                <option value="simple" <?= ((string)$d['template_view'] === 'simple') ? 'selected' : '' ?>>Public Site</option>
                                                <option value="dashboard" <?= ((string)$d['template_view'] === 'dashboard') ? 'selected' : '' ?>>Dashboard UI</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <select name="is_active" class="form-select form-select-sm">
                                            <option value="1" <?= ((int)$d['is_active'] === 1) ? 'selected' : '' ?>>On</option>
                                            <option value="0" <?= ((int)$d['is_active'] === 0) ? 'selected' : '' ?>>Off</option>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Save</button>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label mb-1 mt-1 small text-muted">Google Tag Code</label>
                                        <textarea name="google_tag_code" class="form-control form-control-sm" rows="3"><?= htmlspecialchars((string)($d['google_tag_code'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label mb-1 mt-1 small text-muted">Yandex Counter Code</label>
                                        <textarea name="yandex_counter_code" class="form-control form-control-sm" rows="3"><?= htmlspecialchars((string)($d['yandex_counter_code'] ?? '')) ?></textarea>
                                    </div>
                                </form>
                                <form method="post" class="mt-1" onsubmit="return confirm('Delete domain?');">
                                    <input type="hidden" name="action" value="delete_domain">
                                    <input type="hidden" name="domain_id" value="<?= (int)$d['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-muted">No domains configured</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
