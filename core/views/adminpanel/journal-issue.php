<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">Journal Issue</h5>
            <div class="text-muted">Hero cover and issue title settings for the public `/journal/` page.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="/adminpanel/journal/">Journal Articles</a>
            <a class="btn btn-outline-secondary" href="/journal/" target="_blank" rel="noopener noreferrer">Open Journal</a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Language</label>
                    <select class="form-select" name="lang" onchange="this.form.submit()">
                        <option value="ru" <?= (($journalIssueLang ?? 'ru') === 'ru') ? 'selected' : '' ?>>Russian</option>
                        <option value="en" <?= (($journalIssueLang ?? 'ru') === 'en') ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </form>

            <form method="post">
                <input type="hidden" name="action" value="save_journal_issue">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Issue Kicker</label>
                        <input class="form-control" name="issue_kicker" value="<?= htmlspecialchars((string)($journalIssue['issue_kicker'] ?? '')) ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Issue Title</label>
                        <input class="form-control" name="issue_title" value="<?= htmlspecialchars((string)($journalIssue['issue_title'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Issue Subtitle</label>
                        <textarea class="form-control" rows="3" name="issue_subtitle"><?= htmlspecialchars((string)($journalIssue['issue_subtitle'] ?? '')) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Title</label>
                        <input class="form-control" name="hero_title" value="<?= htmlspecialchars((string)($journalIssue['hero_title'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Note</label>
                        <input class="form-control" name="hero_note" value="<?= htmlspecialchars((string)($journalIssue['hero_note'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hero Description</label>
                        <textarea class="form-control" rows="4" name="hero_description"><?= htmlspecialchars((string)($journalIssue['hero_description'] ?? '')) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hero Image URL</label>
                        <input class="form-control" name="hero_image_url" placeholder="https://..." value="<?= htmlspecialchars((string)($journalIssue['hero_image_url'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hero Image Data URI</label>
                        <textarea class="form-control font-monospace" rows="5" name="hero_image_data" placeholder="data:image/..."><?= htmlspecialchars((string)($journalIssue['hero_image_data'] ?? '')) ?></textarea>
                        <div class="form-text">Use either an external URL or a data URI. URL is preferable.</div>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Save Issue</button>
                        <a class="btn btn-outline-secondary" href="/journal/" target="_blank" rel="noopener noreferrer">Preview Journal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
