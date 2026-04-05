<?php
$seoLogsPage = (array)($SEO_GENERATOR_LOGS_PAGE ?? []);
$perPageAllowed = (array)($seoLogsPage['perPageAllowed'] ?? [10, 20, 50, 100, 200]);
$perPage = (int)($seoLogsPage['perPage'] ?? 10);
if (!in_array($perPage, $perPageAllowed, true)) {
    $perPage = 10;
}
$page = max(1, (int)($seoLogsPage['page'] ?? 1));
$tableExists = !empty($seoLogsPage['tableExists']);
$listSqlError = trim((string)($seoLogsPage['listSqlError'] ?? ''));
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">SEO Generator Logs</h5>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="/adminpanel/seo-generator/">Generator Settings</a>
        </div>
    </div>

    <?php if ($listSqlError !== ''): ?>
        <div class="alert alert-danger">SQL error while loading logs: <?= htmlspecialchars($listSqlError) ?></div>
    <?php endif; ?>
    <?php if (!$tableExists): ?>
        <div class="alert alert-warning">Table <code>seo_generator_logs</code> not found yet. It will be created automatically by cron.</div>
    <?php else: ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>Total: <span id="seoLogsTotal">...</span></div>
                <form id="seoLogsPerPageForm" class="d-flex gap-2" onsubmit="return false;">
                    <input type="hidden" name="page" value="1">
                    <select class="form-select form-select-sm" id="seoLogsPerPage" name="per_page">
                        <?php foreach ($perPageAllowed as $n): ?>
                            <option value="<?= (int)$n ?>" <?= ($perPage === (int)$n) ? 'selected' : '' ?>><?= (int)$n ?>/page</option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Lang/Slot</th>
                            <th>Article</th>
                            <th>Words</th>
                            <th>Structure</th>
                            <th>Topic</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="seoLogsTbody">
                        <tr><td colspan="9" class="text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div>Page <span id="seoLogsPageNow">1</span> / <span id="seoLogsTotalPages">1</span></div>
                <div class="d-flex gap-2">
                    <button id="seoLogsPrev" type="button" class="btn btn-sm btn-outline-secondary">Prev</button>
                    <button id="seoLogsNext" type="button" class="btn btn-sm btn-outline-secondary">Next</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($tableExists): ?>
<script>
(function () {
    const tbody = document.getElementById('seoLogsTbody');
    const totalEl = document.getElementById('seoLogsTotal');
    const pageNowEl = document.getElementById('seoLogsPageNow');
    const totalPagesEl = document.getElementById('seoLogsTotalPages');
    const perPageEl = document.getElementById('seoLogsPerPage');
    const prevBtn = document.getElementById('seoLogsPrev');
    const nextBtn = document.getElementById('seoLogsNext');
    if (!tbody || !totalEl || !pageNowEl || !totalPagesEl || !perPageEl || !prevBtn || !nextBtn) {
        return;
    }

    const state = {
        page: <?= (int)$page ?>,
        perPage: <?= (int)$perPage ?>,
        total: 0,
        totalPages: 1,
        loading: false
    };

    function esc(v) {
        const s = String(v ?? '');
        return s.replace(/[&<>"']/g, function (m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m];
        });
    }

    function parseJson(raw) {
        try {
            const v = JSON.parse(String(raw || '{}'));
            return (v && typeof v === 'object') ? v : {};
        } catch (e) {
            return {};
        }
    }

    function pretty(obj) {
        return esc(JSON.stringify(obj || {}, null, 2));
    }

    function renderRowHtml(r) {
        const req = parseJson(r.image_request_json || '{}');
        const res = parseJson(r.image_result_json || '{}');
        const snap = parseJson(r.settings_snapshot_json || '{}');
        const tg = parseJson(r.tg_preview_result_json || '{}');
        const colorScheme = String(res.color_scheme || req.color_scheme || '').trim();
        const composition = String(res.composition || req.composition || '').trim();
        const tgStatus = String(tg.status || '').trim();
        const status = String(r.status || '');
        const dryRun = Number(r.is_dry_run || 0) === 1;
        const articleUrl = String(r.article_url || '').trim();
        let html = '';
        html += '<tr>';
        html += '<td>' + Number(r.id || 0) + '</td>';
        html += '<td class="text-nowrap">' + esc(r.created_at || '') + '</td>';
        html += '<td>' +
            (status === 'success' ? '<span class="badge bg-success">success</span>' : '<span class="badge bg-danger">failed</span>') +
            (dryRun ? ' <span class="badge bg-secondary">dry-run</span>' : '') +
            '</td>';
        html += '<td><div><code>' + esc(String(r.lang_code || 'en').toUpperCase()) + '</code></div><div class="text-muted small">slot ' + Number(r.slot_index || 0) + '</div></td>';
        html += '<td style="min-width:260px;">' +
            '<div class="fw-semibold">' + esc(r.title || '') + '</div>' +
            '<div class="small text-muted"><code>' + esc(r.slug || '') + '</code></div>' +
            (articleUrl !== '' ? '<a href="' + esc(articleUrl) + '" target="_blank" rel="noopener">Open Article</a>' : '') +
            '</td>';
        html += '<td>' + Number(r.words_initial || 0) + ' -> ' + Number(r.words_final || 0) + '</td>';
        html += '<td style="min-width:220px;">' + esc(r.structure_used || '') + '</td>';
        html += '<td>' +
            '<div>' + esc(r.topic_analysis_source || '') + '</div>' +
            '<div class="small text-muted">bans: ' + Number(r.topic_bans_count || 0) + '</div>' +
            (colorScheme !== '' ? '<div class="small text-muted">color: <code>' + esc(colorScheme) + '</code></div>' : '') +
            (composition !== '' ? '<div class="small text-muted">composition: <code>' + esc(composition) + '</code></div>' : '') +
            (tgStatus !== '' ? '<div class="small text-muted">tg: <code>' + esc(tgStatus) + '</code></div>' : '') +
            '</td>';
        html += '<td style="min-width:320px;">' +
            (status !== 'success' ? '<div class="text-danger small mb-2">' + esc(r.error_message || '') + '</div>' : '') +
            '<details><summary class="small">Image Request/Result</summary><pre class="small mb-1">' + pretty(req) + '</pre><pre class="small mb-0">' + pretty(res) + '</pre></details>' +
            '<details class="mt-1"><summary class="small">Settings Snapshot</summary><pre class="small mb-0">' + pretty(snap) + '</pre></details>' +
            '<details class="mt-1"><summary class="small">TG Preview Result</summary><pre class="small mb-0">' + pretty(tg) + '</pre></details>' +
            '</td>';
        html += '</tr>';
        return html;
    }

    function setLoading(on) {
        state.loading = !!on;
        prevBtn.disabled = state.loading || state.page <= 1;
        nextBtn.disabled = state.loading || state.page >= state.totalPages;
        if (state.loading) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-muted">Loading...</td></tr>';
        }
    }

    function syncHeader() {
        totalEl.textContent = String(state.total);
        pageNowEl.textContent = String(state.page);
        totalPagesEl.textContent = String(state.totalPages);
        prevBtn.disabled = state.loading || state.page <= 1;
        nextBtn.disabled = state.loading || state.page >= state.totalPages;
    }

    function syncUrl() {
        const u = new URL(window.location.href);
        u.searchParams.set('page', String(state.page));
        u.searchParams.set('per_page', String(state.perPage));
        history.replaceState(null, '', u.pathname + u.search);
    }

    function fetchJson(url) {
        return fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (raw) {
                const cleaned = String(raw || '').replace(/^\uFEFF/, '').trim();
                return JSON.parse(cleaned);
            });
    }

    function load() {
        if (state.loading) return;
        setLoading(true);
        state.requestToken = (state.requestToken || 0) + 1;
        const token = state.requestToken;
        const idsUrl = new URL(window.location.origin + '/adminpanel/seo-generator-logs/');
        idsUrl.searchParams.set('ajax', '1');
        idsUrl.searchParams.set('mode', 'ids');
        idsUrl.searchParams.set('page', String(state.page));
        idsUrl.searchParams.set('per_page', String(state.perPage));
        fetchJson(idsUrl.toString())
            .then(function (data) {
                if (token !== state.requestToken) {
                    return;
                }
                if (!data || data.ok !== true) {
                    throw new Error('Invalid response');
                }
                if (String(data.listSqlError || '').trim() !== '') {
                    throw new Error(String(data.listSqlError || 'SQL error'));
                }
                if (!data.tableExists) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-warning">Table seo_generator_logs not found yet.</td></tr>';
                    state.total = 0;
                    state.totalPages = 1;
                    state.page = 1;
                    syncHeader();
                    return;
                }
                state.total = Number(data.total || 0);
                state.totalPages = Math.max(1, Number(data.totalPages || 1));
                state.page = Math.min(Math.max(1, Number(data.page || 1)), state.totalPages);
                syncHeader();
                syncUrl();
                const ids = Array.isArray(data.rows)
                    ? data.rows.map(function (x) { return Number((x && x.id) || 0); }).filter(function (v) { return v > 0; })
                    : [];
                if (ids.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-muted">No logs yet.</td></tr>';
                    return;
                }
                tbody.innerHTML = '';
                let chain = Promise.resolve();
                ids.forEach(function (id) {
                    chain = chain.then(function () {
                        if (token !== state.requestToken) {
                            return;
                        }
                        const rowUrl = new URL(window.location.origin + '/adminpanel/seo-generator-logs/');
                        rowUrl.searchParams.set('ajax', '1');
                        rowUrl.searchParams.set('mode', 'row');
                        rowUrl.searchParams.set('id', String(id));
                        rowUrl.searchParams.set('page', String(state.page));
                        rowUrl.searchParams.set('per_page', String(state.perPage));
                        return fetchJson(rowUrl.toString()).then(function (rowData) {
                            if (token !== state.requestToken) {
                                return;
                            }
                            if (rowData && rowData.ok === true && Array.isArray(rowData.rows) && rowData.rows.length > 0) {
                                tbody.insertAdjacentHTML('beforeend', renderRowHtml(rowData.rows[0]));
                            }
                        });
                    });
                });
                return chain;
            })
            .catch(function (e) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-danger">Failed to load logs: ' + esc(e.message || 'unknown error') + '</td></tr>';
            })
            .finally(function () {
                state.loading = false;
                syncHeader();
            });
    }

    perPageEl.addEventListener('change', function () {
        state.perPage = Number(perPageEl.value || state.perPage);
        state.page = 1;
        load();
    });
    prevBtn.addEventListener('click', function () {
        if (state.page <= 1 || state.loading) return;
        state.page -= 1;
        load();
    });
    nextBtn.addEventListener('click', function () {
        if (state.page >= state.totalPages || state.loading) return;
        state.page += 1;
        load();
    });

    load();
})();
</script>
<?php endif; ?>
