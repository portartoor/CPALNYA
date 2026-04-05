<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">Add Wallet</h5></div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <input type="hidden" name="action" value="add">
                <div class="col-md-4"><input class="form-control" name="name" placeholder="Wallet Name" required></div>
                <div class="col-md-6"><input class="form-control" name="address" placeholder="Wallet Address" required></div>
                <div class="col-md-2"><button class="btn btn-success w-100" type="submit">Add</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Wallets</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Save</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wallets as $w): ?>
                        <tr>
                            <form method="POST">
                                <td>
                                    <?= (int)$w['id'] ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="wallet_id" value="<?= (int)$w['id'] ?>">
                                </td>
                                <td><input class="form-control" name="name" value="<?= htmlspecialchars((string)$w['name']) ?>" required></td>
                                <td><input class="form-control" name="address" value="<?= htmlspecialchars((string)$w['address']) ?>" required></td>
                                <td><button class="btn btn-sm btn-primary" type="submit">Update</button></td>
                            </form>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete wallet #<?= (int)$w['id'] ?>?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="wallet_id" value="<?= (int)$w['id'] ?>">
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
