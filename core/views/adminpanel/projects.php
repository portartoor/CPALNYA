<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Products Catalog</h5>
            <a class="btn btn-primary btn-sm" href="/adminpanel/projects-edit/">Create Product</a>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Lang</label>
                    <select name="lang" class="form-select">
                        <option value="all" <?= $filterLang === 'all' ? 'selected' : '' ?>>all</option>
                        <option value="ru" <?= $filterLang === 'ru' ? 'selected' : '' ?>>ru</option>
                        <option value="en" <?= $filterLang === 'en' ? 'selected' : '' ?>>en</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" class="form-control" value="<?= htmlspecialchars((string)$q) ?>" placeholder="title / slug / symbolic code">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per page</label>
                    <select name="per_page" class="form-select">
                        <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Products</h5>
            <div class="small text-muted">Total: <?= (int)$totalRows ?></div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lang</th>
                        <th>Title</th>
                        <th>Symbolic Code</th>
                        <th>Slug</th>
                        <th>Domain</th>
                        <th>Sort</th>
                        <th>Published</th>
                        <th>Updated</th>
                        <th style="min-width:280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($projectsRows)): foreach ($projectsRows as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string)$row['lang_code']) ?></span></td>
                        <td><?= htmlspecialchars((string)$row['title']) ?></td>
                        <td><code><?= htmlspecialchars((string)$row['symbolic_code']) ?></code></td>
                        <td><code><?= htmlspecialchars((string)$row['slug']) ?></code></td>
                        <td><?= htmlspecialchars((string)($row['domain_host'] ?: '*')) ?></td>
                        <td><?= (int)$row['sort_order'] ?></td>
                        <td><?= (int)$row['is_published'] === 1 ? 'yes' : 'no' ?></td>
                        <td><?= htmlspecialchars((string)($row['updated_at'] ?? $row['created_at'] ?? '')) ?></td>
                        <td class="d-flex gap-1 flex-wrap">
                            <a class="btn btn-sm btn-outline-primary" href="/adminpanel/projects-edit/?id=<?= (int)$row['id'] ?>">Edit</a>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="toggle_publish">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-warning">Toggle</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="duplicate">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info">Duplicate</button>
                            </form>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="10" class="text-muted">No products found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="small text-muted">Page <?= (int)$page ?> / <?= (int)$totalPages ?></div>
            <div class="d-flex gap-2">
                <?php
                    $qs = 'lang=' . urlencode((string)$filterLang) . '&q=' . urlencode((string)$q) . '&per_page=' . (int)$perPage;
                    $prevPage = max(1, (int)$page - 1);
                    $nextPage = min((int)$totalPages, (int)$page + 1);
                ?>
                <a class="btn btn-sm btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>" href="?<?= $qs ?>&page=<?= $prevPage ?>">Prev</a>
                <a class="btn btn-sm btn-outline-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?<?= $qs ?>&page=<?= $nextPage ?>">Next</a>
            </div>
        </div>
    </div>
</div>
