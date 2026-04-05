<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Plans</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Save</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $p): ?>
                        <tr>
                            <form method="POST">
                                <td>
                                    <?= (int)$p['id'] ?>
                                    <input type="hidden" name="plan_id" value="<?= (int)$p['id'] ?>">
                                </td>
                                <td><input class="form-control" name="name" value="<?= htmlspecialchars((string)$p['name']) ?>" required></td>
                                <td><input class="form-control" name="description" value="<?= htmlspecialchars((string)$p['description']) ?>"></td>
                                <td><input class="form-control" name="price" type="number" step="0.01" value="<?= htmlspecialchars((string)$p['price']) ?>" required></td>
                                <td>
                                    <select class="form-select" name="type">
                                        <option value="geo" <?= ($p['type'] === 'geo' ? 'selected' : '') ?>>geo</option>
                                        <option value="antifraud" <?= ($p['type'] === 'antifraud' ? 'selected' : '') ?>>antifraud</option>
                                    </select>
                                </td>
                                <td><button class="btn btn-sm btn-primary" type="submit">Update</button></td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
