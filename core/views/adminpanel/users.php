<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Dashboard Users</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Confirmed</th>
                        <th>API Key</th>
                        <th>Domain</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars((string)$u['email']) ?></td>
                            <td><?= (int)$u['is_confirmed'] === 1 ? 'Yes' : 'No' ?></td>
                            <td><code><?= htmlspecialchars((string)($u['api_key'] ?? '')) ?></code></td>
                            <td><code><?= htmlspecialchars((string)($u['registration_domain'] ?? 'n/a')) ?></code></td>
                            <td><?= htmlspecialchars((string)$u['created_at']) ?></td>
                            <td>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="action" value="resend_system_notifications">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Resend notifications</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

