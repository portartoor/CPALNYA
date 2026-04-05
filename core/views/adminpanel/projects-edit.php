<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= (int)$form['id'] > 0 ? 'Edit Product' : 'Create Product' ?></h5>
            <a class="btn btn-sm btn-outline-secondary" href="/adminpanel/projects/">Back to products</a>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

                <div class="col-md-2">
                    <label class="form-label">Lang</label>
                    <select name="lang_code" class="form-select">
                        <option value="ru" <?= $form['lang_code'] === 'ru' ? 'selected' : '' ?>>ru</option>
                        <option value="en" <?= $form['lang_code'] === 'en' ? 'selected' : '' ?>>en</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Domain host (empty = all)</label>
                    <input type="text" name="domain_host" class="form-control" value="<?= htmlspecialchars((string)$form['domain_host']) ?>" placeholder="portcore.ru">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)$form['sort_order'] ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" <?= (int)$form['is_published'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_published">Published</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars((string)$form['title']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars((string)$form['slug']) ?>" placeholder="auto from title">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Symbolic code</label>
                    <input type="text" name="symbolic_code" class="form-control" value="<?= htmlspecialchars((string)$form['symbolic_code']) ?>" placeholder="product code for /projects/code/">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product URL</label>
                    <input type="text" name="project_url" class="form-control" value="<?= htmlspecialchars((string)$form['project_url']) ?>" placeholder="https://...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Product Tagline</label>
                    <input type="text" name="role_summary" class="form-control" value="<?= htmlspecialchars((string)$form['role_summary']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Target Audience</label>
                    <input type="text" name="industry_summary" class="form-control" value="<?= htmlspecialchars((string)$form['industry_summary']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pricing Summary</label>
                    <input type="text" name="period_summary" class="form-control" value="<?= htmlspecialchars((string)$form['period_summary']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Integrations Summary</label>
                    <input type="text" name="stack_summary" class="form-control" value="<?= htmlspecialchars((string)$form['stack_summary']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Value Summary</label>
                    <textarea name="result_summary" class="form-control" rows="2"><?= htmlspecialchars((string)$form['result_summary']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Excerpt (HTML allowed)</label>
                    <textarea name="excerpt_html" class="form-control" rows="3"><?= htmlspecialchars((string)$form['excerpt_html']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Pricing Details (HTML allowed)</label>
                    <textarea name="challenge_html" class="form-control" rows="4"><?= htmlspecialchars((string)$form['challenge_html']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Integration Details (HTML allowed)</label>
                    <textarea name="solution_html" class="form-control" rows="4"><?= htmlspecialchars((string)$form['solution_html']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">User Value Details (HTML allowed)</label>
                    <textarea name="impact_html" class="form-control" rows="4"><?= htmlspecialchars((string)$form['impact_html']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Metrics (HTML allowed)</label>
                    <textarea name="metrics_html" class="form-control" rows="4"><?= htmlspecialchars((string)$form['metrics_html']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Deliverables (HTML allowed)</label>
                    <textarea name="deliverables_html" class="form-control" rows="4"><?= htmlspecialchars((string)$form['deliverables_html']) ?></textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><?= (int)$form['id'] > 0 ? 'Update Product' : 'Create Product' ?></button>
                    <?php if ((int)$form['id'] > 0): ?>
                        <a class="btn btn-outline-secondary" href="/adminpanel/projects-edit/?id=<?= (int)$form['id'] ?>">Reload</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
