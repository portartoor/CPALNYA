<?php
$isEditing = is_array($editRoute ?? null);
$form = [
    'id' => (int)($editRoute['id'] ?? 0),
    'route_type' => (string)($editRoute['route_type'] ?? 'page'),
    'route_name' => (string)($editRoute['route_name'] ?? ''),
    'page_name' => (string)($editRoute['page_name'] ?? ''),
    'display_name' => (string)($editRoute['display_name'] ?? ''),
    'view_name' => (string)($editRoute['view_name'] ?? 'main'),
    'sort_order' => (int)($editRoute['sort_order'] ?? 100),
    'is_active' => ((int)($editRoute['is_active'] ?? 1) === 1 ? 1 : 0),
    'seo_title' => (string)($editRoute['seo_title'] ?? ''),
    'seo_description' => (string)($editRoute['seo_description'] ?? ''),
    'seo_keywords' => (string)($editRoute['seo_keywords'] ?? ''),
    'og_title' => (string)($editRoute['og_title'] ?? ''),
    'og_description' => (string)($editRoute['og_description'] ?? ''),
    'og_image' => (string)($editRoute['og_image'] ?? ''),
    'seo_noindex' => ((int)($editRoute['seo_noindex'] ?? 0) === 1 ? 1 : 0),
];
?>

<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= $isEditing ? 'Edit Route' : 'Add Route' ?></h5>
            <?php if ($isEditing): ?><a href="/adminpanel/routes/" class="btn btn-sm btn-outline-secondary">New Route</a><?php endif; ?>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="save_route">
                <input type="hidden" name="route_id" value="<?= (int)$form['id'] ?>">
                <div class="col-12">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#route-general" type="button" role="tab">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#route-seo" type="button" role="tab">SEO</button>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                        <div class="tab-pane fade show active" id="route-general" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Type</label>
                                    <select name="route_type" class="form-select">
                                        <option value="page" <?= $form['route_type'] === 'page' ? 'selected' : '' ?>>page</option>
                                        <option value="section_page" <?= $form['route_type'] === 'section_page' ? 'selected' : '' ?>>section/page</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Route Name</label>
                                    <input class="form-control" name="route_name" value="<?= htmlspecialchars((string)$form['route_name']) ?>" placeholder="services" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Page Name</label>
                                    <input class="form-control" name="page_name" value="<?= htmlspecialchars((string)$form['page_name']) ?>" placeholder="main or pricing">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Display Name</label>
                                    <input class="form-control" name="display_name" value="<?= htmlspecialchars((string)$form['display_name']) ?>" placeholder="Services">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">View</label>
                                    <input class="form-control" name="view_name" value="<?= htmlspecialchars((string)$form['view_name']) ?>" placeholder="main">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Sort</label>
                                    <input class="form-control" name="sort_order" type="number" value="<?= (int)$form['sort_order'] ?>">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">On</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" <?= (int)$form['is_active'] === 1 ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= (int)$form['is_active'] === 0 ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="route-seo" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="seo_title" value="<?= htmlspecialchars((string)$form['seo_title']) ?>"></div>
                                <div class="col-md-6"><label class="form-label">Keywords</label><input class="form-control" name="seo_keywords" value="<?= htmlspecialchars((string)$form['seo_keywords']) ?>"></div>
                                <div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="seo_description"><?= htmlspecialchars((string)$form['seo_description']) ?></textarea></div>
                                <div class="col-md-6"><label class="form-label">OG Title</label><input class="form-control" name="og_title" value="<?= htmlspecialchars((string)$form['og_title']) ?>"></div>
                                <div class="col-md-6"><label class="form-label">OG Image URL</label><input class="form-control" name="og_image" value="<?= htmlspecialchars((string)$form['og_image']) ?>"></div>
                                <div class="col-md-12"><label class="form-label">OG Description</label><textarea class="form-control" rows="2" name="og_description"><?= htmlspecialchars((string)$form['og_description']) ?></textarea></div>
                                <div class="col-md-2">
                                    <label class="form-label">Noindex</label>
                                    <select name="seo_noindex" class="form-select">
                                        <option value="0" <?= (int)$form['seo_noindex'] === 0 ? 'selected' : '' ?>>No</option>
                                        <option value="1" <?= (int)$form['seo_noindex'] === 1 ? 'selected' : '' ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary"><?= $isEditing ? 'Update Route' : 'Create Route' ?></button>
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" value="1" id="create_scaffold" name="create_scaffold" checked>
                        <label class="form-check-label" for="create_scaffold">Create scaffold files</label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Routes</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Path</th>
                        <th>View</th>
                        <th>SEO Title</th>
                        <th>Status</th>
                        <th style="min-width:240px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($routesRows)): foreach ($routesRows as $r): ?>
                    <?php
                        $path = '/' . (string)($r['route_name'] ?? '') . '/';
                        if ((string)($r['route_type'] ?? '') === 'section_page' && (string)($r['page_name'] ?? '') !== 'main') {
                            $path = '/' . (string)($r['route_name'] ?? '') . '/' . (string)($r['page_name'] ?? '') . '/';
                        }
                    ?>
                    <tr>
                        <td><?= (int)($r['id'] ?? 0) ?></td>
                        <td><code><?= htmlspecialchars((string)($r['route_type'] ?? '')) ?></code></td>
                        <td><code><?= htmlspecialchars($path) ?></code></td>
                        <td><code><?= htmlspecialchars((string)($r['view_name'] ?? 'main')) ?></code></td>
                        <td><?= htmlspecialchars((string)($r['seo_title'] ?? '')) ?></td>
                        <td><?= ((int)($r['is_active'] ?? 0) === 1) ? 'active' : 'disabled' ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="/adminpanel/routes/?edit_id=<?= (int)($r['id'] ?? 0) ?>">Edit</a>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="scaffold_route">
                                <input type="hidden" name="route_id" value="<?= (int)($r['id'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Scaffold</button>
                            </form>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete route?');">
                                <input type="hidden" name="action" value="delete_route">
                                <input type="hidden" name="route_id" value="<?= (int)($r['id'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-muted">No routes configured yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
