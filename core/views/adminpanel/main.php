<style>
    .visit-logs-table {
        font-size: 12px;
        line-height: 1.35;
    }
    .visit-logs-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .03em;
        white-space: nowrap;
    }
    .visit-logs-table td {
        vertical-align: top;
    }
    .visit-logs-table .visit-logs-break {
        max-width: 220px;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .visit-logs-table .visit-logs-path {
        min-width: 320px;
        max-width: 520px;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .visit-logs-table .visit-logs-query {
        max-width: 260px;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row g-3 mb-2">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card h-100"><div class="card-body d-flex flex-column justify-content-between">
                <h6 class="text-muted mb-1"><i class="ti ti-activity me-1"></i>Human Visits (Today / 30d)</h6>
                <h3 class="mb-0"><?= (int)$kpi['today_visits'] ?> / <?= (int)$kpi['visits_30d'] ?></h3>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card h-100"><div class="card-body d-flex flex-column justify-content-between">
                <h6 class="text-muted mb-1"><i class="ti ti-users me-1"></i>Human Uniques (Today / 30d)</h6>
                <h3 class="mb-0"><?= (int)$kpi['today_unique_visitors'] ?> / <?= (int)$kpi['unique_visitors_30d'] ?></h3>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card h-100"><div class="card-body d-flex flex-column justify-content-between">
                <h6 class="text-muted mb-1"><i class="ti ti-robot me-1"></i>Bot Visits 30d</h6>
                <h3 class="mb-0"><?= (int)$kpi['bot_visits_30d'] ?></h3>
            </div></div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card h-100"><div class="card-body d-flex flex-column justify-content-between">
                <div class="text-muted mb-1"><i class="ti ti-device-desktop me-1"></i>Device Split (Desktop/Mobile)</div>
                <div class="h5 mb-0"><?= number_format((float)$kpi['desktop_share'], 1) ?>% / <?= number_format((float)$kpi['mobile_share'], 1) ?>%</div>
                <small class="text-muted">Tablet: <?= number_format((float)$kpi['tablet_share'], 1) ?>%</small>
            </div></div>
        </div>
    </div>

    <?php if (false): // temporary hidden ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="ti ti-bell me-1"></i>Admin Notifications</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark">Unread: <?= (int)($adminNotificationsUnread ?? 0) ?></span>
                <form method="post">
                    <input type="hidden" name="action" value="admin_notification_mark_all_read">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Mark all read</button>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle visit-logs-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($adminNotifications)): foreach ($adminNotifications as $row): ?>
                    <?php $isUnread = ((int)($row['is_read'] ?? 0) === 0); ?>
                    <tr class="<?= $isUnread ? 'table-warning' : '' ?>">
                        <td class="text-nowrap"><?= htmlspecialchars((string)($row['created_at'] ?? '')) ?></td>
                        <td><code><?= htmlspecialchars((string)($row['type'] ?? '')) ?></code></td>
                        <td>
                            <a href="/adminpanel/?admin_notification_open=<?= (int)$row['id'] ?>" target="_blank" rel="noopener noreferrer">
                                <?= htmlspecialchars((string)($row['title'] ?? '')) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars((string)($row['message'] ?? '')) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-success" href="/adminpanel/?admin_notification_open=<?= (int)$row['id'] ?>" target="_blank" rel="noopener noreferrer">Open</a>
                            <?php if ($isUnread): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="admin_notification_mark_read">
                                    <input type="hidden" name="notification_id" value="<?= (int)$row['id'] ?>">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Mark read</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-muted">No notifications yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="ti ti-chart-line me-1"></i>Traffic Trend (Last 30 Days)</h5></div>
        <div class="card-body"><div id="chart-visits-30d" style="height: 300px;"></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="ti ti-world me-1"></i>Traffic By Domain (Last 30 Days)</h5></div>
        <div class="card-body"><div id="chart-domains-30d" style="height: 320px;"></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="ti ti-file-text me-1"></i>All Blog Posts Trend (Last 30 Days)</h5></div>
        <div class="card-body"><div id="chart-top-articles-30d" style="height: 320px;"></div></div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0"><i class="ti ti-layout-grid me-1"></i>Sections Traffic (Today / 30d)</h6></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="text-muted small">Visits</div>
                            <div class="h5 mb-0"><?= (int)$kpi['sections_today_visits'] ?> / <?= (int)$kpi['sections_30d_visits'] ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Uniques</div>
                            <div class="h5 mb-0"><?= (int)$kpi['sections_today_uniques'] ?> / <?= (int)$kpi['sections_30d_uniques'] ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Bots</div>
                            <div class="h5 mb-0"><?= (int)$kpi['sections_today_bots'] ?> / <?= (int)$kpi['sections_30d_bots'] ?></div>
                        </div>
                    </div>
                    <div id="chart-sections-traffic" style="height: 260px;" class="mt-3"></div>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th class="text-end">Visits T/30d</th>
                                    <th class="text-end">Uniques T/30d</th>
                                    <th class="text-end">Bots T/30d</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ((array)($chart['sectionStats'] ?? []) as $row): ?>
                                <?php
                                    $tv = (int)($row['today']['visits'] ?? 0);
                                    $tu = (int)($row['today']['uniques'] ?? 0);
                                    $tb = (int)($row['today']['bots'] ?? 0);
                                    $v30 = (int)($row['d30']['visits'] ?? 0);
                                    $u30 = (int)($row['d30']['uniques'] ?? 0);
                                    $b30 = (int)($row['d30']['bots'] ?? 0);
                                    if (($tv + $tu + $tb + $v30 + $u30 + $b30) === 0) { continue; }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row['label'] ?? 'Section')) ?></td>
                                    <td class="text-end"><?= $tv ?> / <?= $v30 ?></td>
                                    <td class="text-end"><?= $tu ?> / <?= $u30 ?></td>
                                    <td class="text-end"><?= $tb ?> / <?= $b30 ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ti ti-devices me-1"></i>Devices Share</h6></div>
                <div class="card-body"><div id="chart-devices" style="height: 260px;"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ti ti-flag me-1"></i>Top Countries</h6></div>
                <div class="card-body"><div id="chart-countries" style="height: 260px;"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0"><i class="ti ti-affiliate me-1"></i>Traffic Sources</h6></div>
                <div class="card-body"><div id="chart-sources" style="height: 260px;"></div></div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="ti ti-file-text me-1"></i>Visit Logs (Last 30 Days)</h6>
            <form method="get" class="d-flex align-items-center gap-2">
                <input type="hidden" name="logs_page" value="1">
                <label for="logs_per_page" class="text-muted mb-0">Per page:</label>
                <select id="logs_per_page" name="logs_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach (($logsPagination['page_sizes'] ?? [50,100,500,1000]) as $size): ?>
                        <option value="<?= (int)$size ?>" <?= ((int)$logsPagination['per_page'] === (int)$size) ? 'selected' : '' ?>><?= (int)$size ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php if (!empty($logsActionMessage)): ?>
            <div class="px-3 pt-3">
                <div class="alert alert-<?= htmlspecialchars((string)$logsActionType) ?> mb-0"><?= htmlspecialchars((string)$logsActionMessage) ?></div>
            </div>
        <?php endif; ?>
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle visit-logs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Time</th>
                        <th>IP</th>
                        <th>Domain</th>
                        <th>Path</th>
                        <th>Flag</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Source</th>
                        <th>Referrer</th>
                        <th>UTM Source</th>
                        <th>UTM Medium</th>
                        <th>UTM Campaign</th>
                        <th>UTM Term</th>
                        <th>UTM Content</th>
                        <th>Search Query</th>
                        <th>Device</th>
                        <th>Bot</th>
                        <th>Suspect</th>
                        <th>Reason</th>
                        <th>User Agent</th>
                        <th>Query</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($logsRows)): foreach ($logsRows as $row): ?>
                    <tr>
                        <?php
                            $countryIso2 = strtoupper(trim((string)($row['country_iso2'] ?? '')));
                            $countryName = trim((string)($row['country_name'] ?? ''));
                            $countryNameNormalized = strtolower($countryName);
                            if (in_array($countryNameNormalized, ['unknown', 'unkonwn', 'n/a', 'na', 'none', 'null', '-'], true)) {
                                $countryName = '';
                            }
                            $cityName = trim((string)($row['city_name'] ?? ''));
                            $cityNameNormalized = strtolower($cityName);
                            if (in_array($cityNameNormalized, ['unknown', 'unkonwn', 'n/a', 'na', 'none', 'null', '-'], true)) {
                                $cityName = '';
                            }
                            $countryDisplay = $countryName !== '' ? $countryName : ($countryIso2 !== '' ? $countryIso2 : 'Unknown');
                            $countryFlag = '';
                            if ($countryIso2 !== '' && preg_match('/^[A-Z]{2}$/', $countryIso2)) {
                                $countryFlag = 'https://flagcdn.com/w20/' . strtolower($countryIso2) . '.png';
                            }
                            $botReason = trim((string)($row['suspect_reason'] ?? ''));
                            if ($botReason === '' && (int)($row['is_bot'] ?? 0) === 1) {
                                $botReason = 'ua_pattern';
                            }
                            $userAgentFull = (string)($row['user_agent'] ?? '');
                            $userAgentShort = function_exists('mb_substr') ? mb_substr($userAgentFull, 0, 160) : substr($userAgentFull, 0, 160);
                        ?>
                        <td><?= (int)($row['id'] ?? 0) ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars((string)($row['visited_at'] ?? '')) ?></td>
                        <td><code><?= htmlspecialchars((string)($row['ip'] ?? '')) ?></code></td>
                        <td><code><?= htmlspecialchars((string)($row['host'] ?? '')) ?></code></td>
                        <td class="visit-logs-path"><?= htmlspecialchars((string)($row['path'] ?? '')) ?></td>
                        <td>
                            <?php if ($countryFlag !== ''): ?>
                                <img src="<?= htmlspecialchars($countryFlag) ?>" alt="<?= htmlspecialchars($countryIso2) ?> flag" loading="lazy" decoding="async" style="width:16px;height:12px;object-fit:cover;border-radius:2px;vertical-align:-2px;">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($countryDisplay) ?></td>
                        <td><?= htmlspecialchars($cityName !== '' ? $cityName : '-') ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['source_type'] ?? '')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['referrer_host'] ?? 'direct')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['utm_source'] ?? '')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['utm_medium'] ?? '')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['utm_campaign'] ?? '')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['utm_term'] ?? '')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['utm_content'] ?? '')) ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars((string)($row['search_query'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($row['device_type'] ?? '')) ?></td>
                        <td><?= ((int)($row['is_bot'] ?? 0) === 1) ? 'yes' : 'no' ?></td>
                        <td><?= ((int)($row['is_suspect'] ?? 0) === 1) ? 'yes' : 'no' ?></td>
                        <td class="visit-logs-break"><?= htmlspecialchars($botReason) ?></td>
                        <td class="visit-logs-break" title="<?= htmlspecialchars($userAgentFull) ?>">
                            <?= htmlspecialchars($userAgentShort) ?>
                        </td>
                        <td class="position-relative pb-4 visit-logs-query">
                            <small class="d-block pe-2"><?= htmlspecialchars((string)($row['query_string'] ?? '')) ?></small>
                            <form method="post" class="position-absolute" style="right:6px;bottom:6px;">
                                <input type="hidden" name="action" value="add_ip_threat">
                                <input type="hidden" name="visit_id" value="<?= (int)($row['id'] ?? 0) ?>">
                                <input type="hidden" name="logs_page" value="<?= (int)($logsPagination['page'] ?? 1) ?>">
                                <input type="hidden" name="logs_per_page" value="<?= (int)($logsPagination['per_page'] ?? 100) ?>">
                                <button class="btn btn-sm btn-outline-danger py-0 px-1" type="submit">Add to threats</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="22" class="text-muted">No logs yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <?php
                $currentPage = (int)($logsPagination['page'] ?? 1);
                $totalPages = (int)($logsPagination['total_pages'] ?? 1);
                $perPage = (int)($logsPagination['per_page'] ?? 100);
                $prevPage = max(1, $currentPage - 1);
                $nextPage = min($totalPages, $currentPage + 1);
            ?>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    Total logs 30d: <?= (int)($logsPagination['total_rows'] ?? 0) ?>,
                    page <?= $currentPage ?> / <?= $totalPages ?>
                </small>
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-secondary <?= ($currentPage <= 1) ? 'disabled' : '' ?>" href="?logs_page=1&logs_per_page=<?= $perPage ?>">First</a>
                    <a class="btn btn-outline-secondary <?= ($currentPage <= 1) ? 'disabled' : '' ?>" href="?logs_page=<?= $prevPage ?>&logs_per_page=<?= $perPage ?>">Prev</a>
                    <a class="btn btn-outline-secondary <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>" href="?logs_page=<?= $nextPage ?>&logs_per_page=<?= $perPage ?>">Next</a>
                    <a class="btn btn-outline-secondary <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>" href="?logs_page=<?= $totalPages ?>&logs_per_page=<?= $perPage ?>">Last</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = <?= json_encode($chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const visitsOptions = {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [
            { name: 'Human Visits', data: data.visits30d || [] },
            { name: 'Human Uniques', data: data.uniques30d || [] },
            { name: 'Bot Visits', data: data.botVisits30d || [] }
        ],
        xaxis: { categories: data.labels30d || [] },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#1f8ef1', '#00b894', '#ff7675'],
        dataLabels: { enabled: false },
        grid: { strokeDashArray: 4 },
        legend: { position: 'top' }
    };
    new ApexCharts(document.querySelector('#chart-visits-30d'), visitsOptions).render();

    const topArticlesSeries = (data.articleTopTrendSeries30d || []).map(function (s, idx) {
        const rawName = String((s && s.name) || ('Article ' + (idx + 1)));
        const name = rawName.length > 48 ? (rawName.slice(0, 45) + '...') : rawName;
        return {
            name: name,
            data: (s && s.data ? s.data : []).map(function (v) { return Number(v || 0); })
        };
    });
    const topArticlesEl = document.querySelector('#chart-top-articles-30d');
    if (topArticlesEl) {
        if (topArticlesSeries.length > 0) {
            new ApexCharts(topArticlesEl, {
                chart: { type: 'line', height: 320, toolbar: { show: false } },
                series: topArticlesSeries,
                xaxis: { categories: data.labels30d || [] },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                legend: { position: 'top' },
                grid: { strokeDashArray: 4 },
                tooltip: { shared: true, intersect: false }
            }).render();
        } else {
            topArticlesEl.innerHTML = '<div class="text-muted">No blog posts trend data for the last 30 days</div>';
        }
    }

    const deviceLabels = (data.devices || []).map(x => x.label);
    const deviceValues = (data.devices || []).map(x => Number(x.value || 0));
    new ApexCharts(document.querySelector('#chart-devices'), {
        chart: { type: 'donut', height: 260 },
        labels: deviceLabels,
        series: deviceValues,
        dataLabels: { enabled: true }
    }).render();

    const countryLabels = (data.countries || []).map(x => x.label);
    const countryValues = (data.countries || []).map(x => Number(x.value || 0));
    new ApexCharts(document.querySelector('#chart-countries'), {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        series: [{ name: 'Visits', data: countryValues }],
        xaxis: { categories: countryLabels },
        dataLabels: { enabled: false }
    }).render();

    const sourceLabels = (data.sources || []).map(x => x.label);
    const sourceValues = (data.sources || []).map(x => Number(x.value || 0));
    new ApexCharts(document.querySelector('#chart-sources'), {
        chart: { type: 'pie', height: 260 },
        labels: sourceLabels,
        series: sourceValues
    }).render();

    const sectionStats = data.sectionStats || [];
    const activeSections = sectionStats.filter(function (x) {
        const v = Number((x && x.d30 && x.d30.visits) || 0);
        const b = Number((x && x.d30 && x.d30.bots) || 0);
        return v > 0 || b > 0;
    });
    const sectionLabels = activeSections.map(x => String(x.label || 'section'));
    const sectionVisits = activeSections.map(x => Number((x.d30 && x.d30.visits) || 0));
    const sectionBots = activeSections.map(x => Number((x.d30 && x.d30.bots) || 0));
    const chartSectionsEl = document.querySelector('#chart-sections-traffic');
    if (chartSectionsEl) {
        if (sectionLabels.length > 0) {
            new ApexCharts(chartSectionsEl, {
                chart: { type: 'bar', stacked: true, height: 260, toolbar: { show: false } },
                plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '56%' } },
                series: [
                    { name: 'Visits 30d', data: sectionVisits },
                    { name: 'Bots 30d', data: sectionBots }
                ],
                xaxis: { categories: sectionLabels },
                dataLabels: { enabled: false },
                colors: ['#00b894', '#f39c12'],
                legend: { position: 'top' },
                grid: { strokeDashArray: 4 }
            }).render();
        } else {
            chartSectionsEl.innerHTML = '<div class="text-muted">No section data yet</div>';
        }
    }

    const domainSeries = (data.domainTrendSeries || []).map((s, idx) => ({
        name: String((s && s.name) || ('Domain ' + (idx + 1))),
        data: (s.data || []).map(v => Number(v || 0))
    }));
    if (domainSeries.length > 0) {
        new ApexCharts(document.querySelector('#chart-domains-30d'), {
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            series: domainSeries,
            xaxis: { categories: data.labels30d || [] },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            legend: {
                show: true,
                showForSingleSeries: true,
                position: 'top'
            },
            grid: { strokeDashArray: 4 }
        }).render();
    } else {
        const el = document.querySelector('#chart-domains-30d');
        if (el) {
            el.innerHTML = '<div class="text-muted">No domain traffic yet</div>';
        }
    }

});
</script>

