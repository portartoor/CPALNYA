<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Subscriptions</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan Details</th>
                        <th>Payment Details</th>
                        <th>Status</th>
                        <th>Created / Tech</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $s): ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td><?= htmlspecialchars((string)($s['user_email'] ?? ('user#' . $s['user_id']))) ?></td>
                            <td>
                                <div><strong><?= htmlspecialchars((string)($s['plan_name'] ?? 'n/a')) ?></strong></div>
                                <div class="text-muted">Type: <?= htmlspecialchars((string)($s['plan_type'] ?? 'n/a')) ?></div>
                                <div class="text-muted">Duration: <?= (int)$s['duration'] ?> month(s)</div>
                                <div class="text-muted">Base: $<?= htmlspecialchars((string)($s['price'] ?? '0')) ?></div>
                                <div class="text-muted">Discount: <?= htmlspecialchars((string)($s['discount'] ?? '0')) ?>%</div>
                                <div><strong>Final: $<?= htmlspecialchars((string)($s['final_price'] ?? '0')) ?></strong></div>
                                <?php if (isset($s['amount_usd'])): ?>
                                    <div class="text-muted">USD exact: <?= htmlspecialchars((string)$s['amount_usd']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars((string)($s['wallet_name'] ?? 'n/a')) ?></strong></div>
                                <div class="text-muted">Wallet ID: <?= (int)($s['wallet_id'] ?? 0) ?></div>
                                <div class="text-muted">Address: <code><?= htmlspecialchars((string)($s['wallet_address'] ?? $s['wallet_db_address'] ?? 'n/a')) ?></code></div>
                                <div class="text-muted">Currency: <?= htmlspecialchars((string)($s['currency_code'] ?? 'n/a')) ?></div>
                                <?php if (isset($s['amount_in_currency'])): ?>
                                    <div class="text-muted">Amount: <?= htmlspecialchars((string)$s['amount_in_currency']) ?></div>
                                <?php endif; ?>
                                <?php if (isset($s['amount_crypto'])): ?>
                                    <div class="text-muted">Crypto amount: <?= htmlspecialchars((string)$s['amount_crypto']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($s['source_domain'])): ?>
                                    <div class="text-muted">Domain: <code><?= htmlspecialchars((string)$s['source_domain']) ?></code></div>
                                <?php endif; ?>
                                <div class="text-muted">TX: <code><?= htmlspecialchars((string)($s['tx_hash'] ?? 'n/a')) ?></code></div>
                            </td>
                            <td>
                                <?php if ((int)$s['status'] === 1): ?>Active<?php elseif ((int)$s['status'] === 2): ?>Disabled<?php else: ?>Pending<?php endif; ?>
                            </td>
                            <td>
                                <div><?= htmlspecialchars((string)$s['created_at']) ?></div>
                                <div class="text-muted">Sub ID: <?= (int)$s['id'] ?></div>
                                <div class="text-muted">User ID: <?= (int)$s['user_id'] ?></div>
                                <div class="text-muted">Plan ID: <?= (int)$s['plan_id'] ?></div>
                                <?php if (!empty($s['expires_at'])): ?>
                                    <div class="text-muted">Expires: <?= htmlspecialchars((string)$s['expires_at']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="d-flex gap-1">
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="subscription_id" value="<?= (int)$s['id'] ?>">
                                    <input type="hidden" name="status" value="1">
                                    <button class="btn btn-sm btn-success" type="submit">Activate</button>
                                </form>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="subscription_id" value="<?= (int)$s['id'] ?>">
                                    <input type="hidden" name="status" value="2">
                                    <button class="btn btn-sm btn-danger" type="submit">Deactivate</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
