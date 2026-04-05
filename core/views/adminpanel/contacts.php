<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contact Requests</h5>
            <div class="small text-muted">Total: <?= (int)$statusCounts['all'] ?></div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All (<?= (int)$statusCounts['all'] ?>)</option>
                        <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New (<?= (int)$statusCounts['new'] ?>)</option>
                        <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>In Progress (<?= (int)$statusCounts['in_progress'] ?>)</option>
                        <option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>Closed (<?= (int)$statusCounts['closed'] ?>)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per page</label>
                    <select name="per_page" class="form-select">
                        <option value="20" <?= $perPage === 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Requests List</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Created</th>
                        <th>Name</th>
                        <th>Campaign</th>
                        <th>Subject</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th style="min-width: 230px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($contactRequests)): foreach ($contactRequests as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= htmlspecialchars((string)$row['created_at']) ?></td>
                        <td><?= htmlspecialchars((string)$row['name']) ?></td>
                        <td><?= htmlspecialchars((string)($row['campaign'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['subject'] ?? '')) ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars((string)$row['email']) ?>"><?= htmlspecialchars((string)$row['email']) ?></a>
                        </td>
                        <td style="max-width:360px; white-space:pre-wrap;">
                            <?= htmlspecialchars((string)$row['message']) ?>
                            <?php
                                $attachmentsRaw = (string)($row['attachments_json'] ?? '');
                                $attachments = [];
                                if ($attachmentsRaw !== '') {
                                    $decodedFiles = json_decode($attachmentsRaw, true);
                                    if (is_array($decodedFiles)) {
                                        $attachments = $decodedFiles;
                                    }
                                }
                            ?>
                            <?php if (!empty($attachments)): ?>
                                <div class="mt-2">
                                    <strong>Files:</strong>
                                    <?php foreach ($attachments as $file): ?>
                                        <?php
                                            $fileName = trim((string)($file['name'] ?? 'file'));
                                            $filePath = trim((string)($file['path'] ?? ''));
                                        ?>
                                        <?php if ($filePath !== '' && strpos($filePath, '/uploads/contact_requests/') === 0): ?>
                                            <div><a href="<?= htmlspecialchars($filePath) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($fileName) ?></a></div>
                                        <?php else: ?>
                                            <div><?= htmlspecialchars($fileName) ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><code><?= htmlspecialchars((string)$row['source_page']) ?></code></div>
                            <div class="small text-muted"><?= htmlspecialchars((string)$row['host']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars((string)$row['ip']) ?></div>
                        </td>
                        <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string)$row['status']) ?></span></td>
                        <td>
                            <form method="post" class="d-flex gap-1 mb-1">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="new" <?= (string)$row['status'] === 'new' ? 'selected' : '' ?>>new</option>
                                    <option value="in_progress" <?= (string)$row['status'] === 'in_progress' ? 'selected' : '' ?>>in_progress</option>
                                    <option value="closed" <?= (string)$row['status'] === 'closed' ? 'selected' : '' ?>>closed</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                            </form>
                            <form method="post" onsubmit="return confirm('Delete this request?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="10" class="text-muted">No requests found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="small text-muted">Page <?= (int)$page ?> / <?= (int)$totalPages ?>, total <?= (int)$totalRows ?></div>
            <div class="d-flex gap-2">
                <?php
                    $qsBase = 'status=' . urlencode((string)$statusFilter) . '&per_page=' . (int)$perPage;
                    $prevPage = max(1, (int)$page - 1);
                    $nextPage = min((int)$totalPages, (int)$page + 1);
                ?>
                <a class="btn btn-sm btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>" href="?<?= $qsBase ?>&page=<?= $prevPage ?>">Prev</a>
                <a class="btn btn-sm btn-outline-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?<?= $qsBase ?>&page=<?= $nextPage ?>">Next</a>
            </div>
        </div>
    </div>
</div>
