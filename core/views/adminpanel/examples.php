<div class="container-fluid mt-4">
    <?php
    $isEditorScreen =
        !empty($editArticle)
        || isset($_GET['create'])
        || (
            (string)($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && (string)($_POST['action'] ?? '') === 'save_article'
        );
    ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <?php if ($isEditorScreen): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><?= !empty($editArticle) ? 'Edit ' . htmlspecialchars((string)$adminExamplesTitle) . ' Item' : 'Create ' . htmlspecialchars((string)$adminExamplesTitle) . ' Item' ?></h5>
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars((string)$adminExamplesBackUrl) ?>">Back to list</a>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="save_article">
                    <input type="hidden" name="article_id" value="<?= (int)($editArticle['id'] ?? 0) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" required value="<?= htmlspecialchars((string)($editArticle['title'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Slug</label>
                            <input class="form-control" name="slug" placeholder="auto-from-title" value="<?= htmlspecialchars((string)($editArticle['slug'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Domain Host</label>
                            <input class="form-control" name="domain_host" placeholder="empty = all domains" value="<?= htmlspecialchars((string)($editArticle['domain_host'] ?? '')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Language</label>
                            <?php $editLang = strtolower((string)($editArticle['lang_code'] ?? 'en')); ?>
                            <select class="form-select" name="lang_code">
                                <option value="en" <?= ($editLang === 'en') ? 'selected' : '' ?>>English</option>
                                <option value="ru" <?= ($editLang === 'ru') ? 'selected' : '' ?>>Russian</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Author</label>
                            <input class="form-control" name="author_name" value="<?= htmlspecialchars((string)($editArticle['author_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <?php $editSection = (string)($editArticle['material_section'] ?? ($adminExamplesSection !== '' ? $adminExamplesSection : 'journal')); ?>
                            <select class="form-select" name="material_section">
                                <option value="journal" <?= $editSection === 'journal' ? 'selected' : '' ?>>Journal</option>
                                <option value="playbooks" <?= $editSection === 'playbooks' ? 'selected' : '' ?>>Playbooks</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Sort</label>
                            <input class="form-control" type="number" name="sort_order" value="<?= (int)($editArticle['sort_order'] ?? 0) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Published</label>
                            <select class="form-select" name="is_published">
                                <option value="0" <?= ((int)($editArticle['is_published'] ?? 1) === 0) ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= ((int)($editArticle['is_published'] ?? 1) === 1) ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Published At</label>
                            <input class="form-control" name="published_at" placeholder="YYYY-mm-dd HH:ii:ss" value="<?= htmlspecialchars((string)($editArticle['published_at'] ?? '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Excerpt HTML</label>
                            <textarea class="form-control" rows="3" name="excerpt_html" placeholder="<p>Short preview text...</p>"><?= htmlspecialchars((string)($editArticle['excerpt_html'] ?? '')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content HTML</label>
                            <div class="mb-2 d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<h2>" data-close="</h2>">H2</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<h3>" data-close="</h3>">H3</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<p>" data-close="</p>">P</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<strong>" data-close="</strong>">Bold</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<em>" data-close="</em>">Italic</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<code>" data-close="</code>">Inline code</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<ul>\n  <li>" data-close="</li>\n</ul>">UL</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<ol>\n  <li>" data-close="</li>\n</ol>">OL</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<blockquote>" data-close="</blockquote>">Quote</button>
                                <button class="btn btn-sm btn-outline-secondary js-wrap" type="button" data-open="<pre class=&quot;code-line&quot;><code class=&quot;language-js&quot;>\n" data-close="\n</code></pre>">Code block</button>
                                <button class="btn btn-sm btn-outline-primary" id="js-preview-btn" type="button">Preview</button>
                            </div>
                            <textarea id="content_html_editor" class="form-control font-monospace" rows="16" name="content_html" required><?= htmlspecialchars((string)($editArticle['content_html'] ?? '')) ?></textarea>
                            <div id="content_html_preview" class="border rounded mt-3 p-3 bg-light" style="display:none;"></div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><?= !empty($editArticle) ? 'Update Item' : 'Create Item' ?></button>
                            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars((string)$adminExamplesBackUrl) ?>">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-end gap-2 mb-3">
            <?php foreach ((array)($adminExamplesExtraActions ?? []) as $action): ?>
                <a
                    class="btn <?= htmlspecialchars((string)($action['class'] ?? 'btn-outline-secondary')) ?>"
                    href="<?= htmlspecialchars((string)($action['href'] ?? '#')) ?>"
                    <?php if (!empty($action['target'])): ?>target="<?= htmlspecialchars((string)$action['target']) ?>" rel="noopener noreferrer"<?php endif; ?>
                ><?= htmlspecialchars((string)($action['label'] ?? 'Open')) ?></a>
            <?php endforeach; ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars((string)$adminExamplesBackUrl) ?>?create=1">Create Item</a>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><?= htmlspecialchars((string)$adminExamplesTitle) ?></h5></div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Domain</th>
                            <th>Lang</th>
                            <th>Section</th>
                            <th>AI</th>
                            <th>Published</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($articles)): foreach ($articles as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars((string)$row['title']) ?></td>
                                <td><code><?= htmlspecialchars((string)$row['slug']) ?></code></td>
                                <td><?= htmlspecialchars((string)($row['domain_host'] ?: 'all')) ?></td>
                                <td><?= htmlspecialchars(strtoupper((string)($row['lang_code'] ?? 'en'))) ?></td>
                                <td><?= htmlspecialchars((string)($row['material_section'] ?? 'journal')) ?></td>
                                <td>
                                    <?php if ((int)($row['is_ai_generated'] ?? 0) === 1): ?>
                                        <span class="badge bg-info text-dark">AI</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= ((int)$row['is_published'] === 1) ? 'yes' : 'no' ?></td>
                                <td><?= htmlspecialchars((string)$row['updated_at']) ?></td>
                                <td class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars((string)$adminExamplesBackUrl) ?>?edit=<?= (int)$row['id'] ?>">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Delete this article?');">
                                        <input type="hidden" name="action" value="delete_article">
                                        <input type="hidden" name="article_id" value="<?= (int)$row['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="9" class="text-muted">No articles yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const editor = document.getElementById('content_html_editor');
    if (!editor) return;

    const wrapButtons = document.querySelectorAll('.js-wrap');
    const previewBtn = document.getElementById('js-preview-btn');
    const preview = document.getElementById('content_html_preview');

    function wrapSelection(openTag, closeTag) {
        const start = editor.selectionStart || 0;
        const end = editor.selectionEnd || 0;
        const selected = editor.value.substring(start, end);
        const insertion = openTag + selected + closeTag;
        editor.setRangeText(insertion, start, end, 'end');
        editor.focus();
    }

    wrapButtons.forEach((btn) => {
        btn.addEventListener('click', function () {
            const openTag = btn.getAttribute('data-open') || '';
            const closeTag = btn.getAttribute('data-close') || '';
            wrapSelection(openTag.replace(/\\n/g, "\n"), closeTag.replace(/\\n/g, "\n"));
        });
    });

    if (previewBtn && preview) {
        previewBtn.addEventListener('click', function () {
            if (preview.style.display === 'none') {
                preview.innerHTML = editor.value;
                preview.style.display = 'block';
                previewBtn.textContent = 'Hide Preview';
            } else {
                preview.style.display = 'none';
                previewBtn.textContent = 'Preview';
            }
        });
    }
})();
</script>
