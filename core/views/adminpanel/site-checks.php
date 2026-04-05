<?php
$checksRows = is_array($siteChecksRows ?? null) ? $siteChecksRows : (is_array($rows ?? null) ? $rows : []);
$checksTotalRows = (int)($siteChecksTotalRows ?? $totalRows ?? 0);
$checksTotalPages = (int)($siteChecksTotalPages ?? $totalPages ?? 1);
$checksPage = (int)($siteChecksPage ?? $page ?? 1);
$checksPerPage = (int)($siteChecksPerPage ?? $perPage ?? 20);
$checksSearch = (string)($siteChecksSearch ?? $search ?? '');
$checksDbName = (string)($siteChecksDbName ?? $dbName ?? '');
$checksTableExists = !empty($siteChecksTableExists ?? $tableExists ?? false);
?>
<div class="container-fluid mt-4">
    <div class="alert alert-light border">
        <strong>Diagnostics:</strong>
        DB: <code><?= htmlspecialchars($checksDbName, ENT_QUOTES, 'UTF-8') ?></code> |
        table <code>site_audit_checks</code>: <strong><?= $checksTableExists ? 'yes' : 'no' ?></strong> |
        rows fetched: <strong><?= count($checksRows) ?></strong> |
        total rows: <strong><?= $checksTotalRows ?></strong>
        <?php if (!empty($checksRows) && is_array($checksRows[0])): ?>
            | first row: <code><?= htmlspecialchars((string)json_encode([
                'id' => $checksRows[0]['id'] ?? null,
                'checked_url' => $checksRows[0]['checked_url'] ?? null,
                'host' => $checksRows[0]['host'] ?? null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></code>
        <?php endif; ?>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Site Checks</h5>
            <div class="small text-muted">Total: <?= $checksTotalRows ?></div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Search (URL / host / IP)</label>
                    <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($checksSearch, ENT_QUOTES, 'UTF-8') ?>" placeholder="example.com">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per page</label>
                    <select name="per_page" class="form-select">
                        <option value="20" <?= $checksPerPage === 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $checksPerPage === 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $checksPerPage === 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Checks List</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Website</th>
                        <th>Scores</th>
                        <th>IP</th>
                        <th>Geo</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($checksRows)): foreach ($checksRows as $row): ?>
                    <tr>
                        <td><?= (int)($row['id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($row['created_at'] ?? '')) ?></td>
                        <td>
                            <div><a href="<?= htmlspecialchars((string)($row['checked_url'] ?? '')) ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string)($row['checked_url'] ?? '')) ?></a></div>
                            <div class="small text-muted"><?= htmlspecialchars((string)($row['host'] ?? '')) ?></div>
                        </td>
                        <td>
                            <div class="small">Overall: <strong><?= (int)($row['score_overall'] ?? 0) ?></strong></div>
                            <div class="small">SEO: <?= (int)($row['score_seo'] ?? 0) ?> | Tech: <?= (int)($row['score_tech'] ?? 0) ?> | Security: <?= (int)($row['score_security'] ?? 0) ?></div>
                        </td>
                        <td><code><?= htmlspecialchars((string)($row['user_ip'] ?? '')) ?></code></td>
                        <td>
                            <div><?= htmlspecialchars((string)($row['country_iso2'] ?? '')) ?> <?= htmlspecialchars((string)($row['country_name'] ?? '')) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars((string)($row['city_name'] ?? '')) ?><?= (string)($row['timezone'] ?? '') !== '' ? (' | ' . htmlspecialchars((string)($row['timezone'] ?? ''))) : '' ?></div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-muted">No checks found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="small text-muted">Page <?= $checksPage ?> / <?= $checksTotalPages ?>, total <?= $checksTotalRows ?></div>
            <div class="d-flex gap-2">
                <?php
                    $qsBase = 'q=' . urlencode($checksSearch) . '&per_page=' . $checksPerPage;
                    $prevPage = max(1, $checksPage - 1);
                    $nextPage = min($checksTotalPages, $checksPage + 1);
                ?>
                <a class="btn btn-sm btn-outline-secondary <?= $checksPage <= 1 ? 'disabled' : '' ?>" href="?<?= $qsBase ?>&page=<?= $prevPage ?>">Prev</a>
                <a class="btn btn-sm btn-outline-secondary <?= $checksPage >= $checksTotalPages ? 'disabled' : '' ?>" href="?<?= $qsBase ?>&page=<?= $nextPage ?>">Next</a>
            </div>
        </div>
    </div>
</div>
