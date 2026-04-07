<?php
$settings = is_array($pageHtmlCacheSettings ?? null) ? $pageHtmlCacheSettings : page_html_cache_defaults();
$stats = is_array($pageHtmlCacheStats ?? null) ? $pageHtmlCacheStats : page_html_cache_stats();

$excludedText = implode("\n", (array)($settings['excluded_prefixes'] ?? []));
$dynamicText = implode("\n", (array)($settings['dynamic_prefixes'] ?? []));
$ttlRows = [];
foreach ((array)($settings['ttl_by_prefix'] ?? []) as $prefix => $ttl) {
    $ttlRows[] = (string)$prefix . ' | ' . (int)$ttl;
}
$ttlText = implode("\n", $ttlRows);
?>
<div class="container-fluid mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars((string)$messageType) ?>"><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">HTML Page Cache</h5>
            <span class="badge text-bg-<?= !empty($settings['enabled']) ? 'success' : 'secondary' ?>">
                <?= !empty($settings['enabled']) ? 'Enabled' : 'Disabled' ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Total Files</div>
                        <div class="h4 mb-0"><?= (int)($stats['files'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">HTML Files</div>
                        <div class="h4 mb-0"><?= (int)($stats['html_files'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Meta Files</div>
                        <div class="h4 mb-0"><?= (int)($stats['meta_files'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Size</div>
                        <div class="h4 mb-0"><?= htmlspecialchars((string)($stats['size_human'] ?? '0 B')) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Settings</h6></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="save_page_html_cache_settings">
                <div class="col-md-3">
                    <label class="form-label">Cache Enabled</label>
                    <select class="form-select" name="enabled">
                        <option value="1" <?= !empty($settings['enabled']) ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= empty($settings['enabled']) ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Default TTL (seconds)</label>
                    <input type="number" min="30" max="86400" class="form-control" name="default_ttl" value="<?= (int)($settings['default_ttl'] ?? 120) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Max HTML size (bytes)</label>
                    <input type="number" min="16384" max="15728640" class="form-control" name="max_file_size" value="<?= (int)($settings['max_file_size'] ?? 2097152) ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Excluded path prefixes (one per line)</label>
                    <textarea class="form-control font-monospace" rows="6" name="excluded_prefixes"><?= htmlspecialchars($excludedText, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="form-text">Examples: <code>/adminpanel/</code>, <code>/api/</code>, <code>/audit/</code>.</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Dynamic / randomized prefixes (do not cache)</label>
                    <textarea class="form-control font-monospace" rows="4" name="dynamic_prefixes"><?= htmlspecialchars($dynamicText, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="form-text">Use this for pages with random content blocks or content that must stay live. Example: <code>/</code></div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">TTL by prefix (prefix | ttl_seconds)</label>
                    <textarea class="form-control font-monospace" rows="8" name="ttl_by_prefix"><?= htmlspecialchars($ttlText, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="form-text">Example: <code>/blog/ | 600</code></div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Purge</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <form method="post" class="border rounded p-3 h-100">
                        <input type="hidden" name="action" value="purge_all">
                        <div class="fw-semibold mb-2">Purge all cache</div>
                        <div class="text-muted small mb-3">Deletes all HTML and metadata files.</div>
                        <button type="submit" class="btn btn-outline-danger">Purge All</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="post" class="border rounded p-3 h-100">
                        <input type="hidden" name="action" value="purge_prefix">
                        <label class="form-label">Purge by prefix</label>
                        <input type="text" class="form-control mb-2" name="purge_prefix" placeholder="/blog/">
                        <button type="submit" class="btn btn-outline-warning">Purge Prefix</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="post" class="border rounded p-3 h-100">
                        <input type="hidden" name="action" value="purge_url">
                        <label class="form-label">Purge by exact URL / URI</label>
                        <input type="text" class="form-control mb-2" name="purge_url" placeholder="https://portcore.ru/blog/my-post/">
                        <button type="submit" class="btn btn-outline-info">Purge URL</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
