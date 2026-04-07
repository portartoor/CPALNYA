<?php
session_start();

$FRMWRK = new FRMWRK();
$adminpanelUser = null;
$DB = $FRMWRK->DB();

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

function adminpanel_table_exists(mysqli $db, string $table): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

function adminpanel_table_has_column(mysqli $db, string $table, string $column): bool
{
    $tableSafe = mysqli_real_escape_string($db, $table);
    $columnSafe = mysqli_real_escape_string($db, $column);
    $res = mysqli_query(
        $db,
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$tableSafe}'
           AND COLUMN_NAME = '{$columnSafe}'
         LIMIT 1"
    );
    if (!$res) {
        return false;
    }
    return mysqli_num_rows($res) > 0;
}

function adminpanel_count_records($FRMWRK, mysqli $db, string $table, string $where = ''): int
{
    if (!adminpanel_table_exists($db, $table)) {
        return 0;
    }
    return (int)$FRMWRK->DBRecordsCount($table, $where);
}

function adminpanel_pct(int $part, int $total): float
{
    if ($total <= 0) {
        return 0.0;
    }
    return round(($part / $total) * 100, 2);
}

function adminpanel_int_in_list($value, array $allowed, int $default): int
{
    $intValue = (int)$value;
    return in_array($intValue, $allowed, true) ? $intValue : $default;
}

function adminpanel_section_key_from_path(string $path): string
{
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return 'home';
    }
    if (strpos($path, '/journal') === 0 || strpos($path, '/playbooks') === 0 || strpos($path, '/blog') === 0 || strpos($path, '/examples/article/') === 0 || strpos($path, '/articles/') === 0) {
        return 'blog';
    }
    if (strpos($path, '/services') === 0) {
        return 'services';
    }
    if (strpos($path, '/projects') === 0) {
        return 'projects';
    }
    if (strpos($path, '/cases') === 0) {
        return 'cases';
    }
    if (strpos($path, '/contact') === 0) {
        return 'contact';
    }
    if (strpos($path, '/audit') === 0) {
        return 'audit';
    }
    if (strpos($path, '/adminpanel') === 0 || strpos($path, '/dashboard') === 0) {
        return 'adminpanel';
    }
    if (strpos($path, '/robots.txt') === 0 || strpos($path, '/sitemap.xml') === 0) {
        return 'system';
    }
    return 'other';
}

function adminpanel_article_slug_from_path(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }
    if (preg_match('~^/(?:blog|journal|playbooks)/([^/]+)/?~', $path, $m)) {
        $slug = trim((string)$m[1]);
        if ($slug !== '' && $slug !== 'page') {
            return $slug;
        }
    }
    if (preg_match('~^/examples/article/([^/]+)/?~', $path, $m)) {
        $slug = trim((string)$m[1]);
        return $slug !== '' ? $slug : '';
    }
    if (preg_match('~^/articles/([^/]+)/([^/]+)/?~', $path, $m)) {
        $slug = trim((string)$m[2]);
        if ($slug !== '') {
            return $slug;
        }
    }
    if (preg_match('~^/articles/([^/]+)/?~', $path, $m)) {
        $slug = trim((string)$m[1]);
        if ($slug !== '' && !in_array($slug, ['article', 'suggest', 'research', 'b2b', 'general', 'obshchiy'], true)) {
            return $slug;
        }
    }
    return '';
}

$dashboardUsersTable = adminpanel_table_exists($DB, 'adminpanel_users')
    ? 'adminpanel_users'
    : (adminpanel_table_exists($DB, 'admins') ? 'admins' : '');
$counts = [
    'dashboard_users' => $dashboardUsersTable !== '' ? adminpanel_count_records($FRMWRK, $DB, $dashboardUsersTable, '') : 0,
    'subscriptions_total' => adminpanel_count_records($FRMWRK, $DB, 'subscriptions', ''),
    'subscriptions_active' => adminpanel_count_records($FRMWRK, $DB, 'subscriptions', "status='1'"),
    'plans_total' => adminpanel_count_records($FRMWRK, $DB, 'plans', ''),
    'wallets_total' => adminpanel_count_records($FRMWRK, $DB, 'wallets', ''),
];

$hasVisits = adminpanel_table_exists($DB, 'analytics_visits');
$hasLeads = adminpanel_table_exists($DB, 'analytics_lead_events');
$hasIpActivity = adminpanel_table_exists($DB, 'ip_activity');
$hasVisitsSearchQuery = $hasVisits && adminpanel_table_has_column($DB, 'analytics_visits', 'search_query');
$hasLeadsAttribution = $hasLeads
    && adminpanel_table_has_column($DB, 'analytics_lead_events', 'source_type')
    && adminpanel_table_has_column($DB, 'analytics_lead_events', 'utm_source');

$todayVisits = 0;
$todayUniqueVisitors = 0;
$visits30d = 0;
$uniqueVisitors30d = 0;
$nonBotUniqueVisitors30d = 0;
$botVisits30d = 0;
$desktop30d = 0;
$mobile30d = 0;
$tablet30d = 0;
$registrations30d = 0;
$orders30d = 0;
$registrationView30d = 0;
$apiLookups30d = 0;

$labels30d = [];
$seriesVisits30d = [];
$seriesUniques30d = [];
$seriesBotVisits30d = [];
$seriesApiLookups30d = [];
$seriesRegistrations30d = [];
$seriesOrders30d = [];
$trendFromDateSql = "CURDATE() - INTERVAL 29 DAY";
$trendToDateSql = "CURDATE() + INTERVAL 1 DAY";
$start = new DateTime('today -29 days');
for ($i = 0; $i < 30; $i++) {
    $d = clone $start;
    $d->modify("+{$i} day");
    $dateKey = $d->format('Y-m-d');
    $labels30d[] = $d->format('d M');
    $seriesVisits30d[$dateKey] = 0;
    $seriesUniques30d[$dateKey] = 0;
    $seriesBotVisits30d[$dateKey] = 0;
    $seriesApiLookups30d[$dateKey] = 0;
    $seriesRegistrations30d[$dateKey] = 0;
    $seriesOrders30d[$dateKey] = 0;
}

$countries = [];
$sources = [];
$devices = [];
$leadEvents = [];
$topApiClients = [];
$topSourceIps = [];
$referrersStats = [];
$referrersTotalUnique = 0;
$domainTrafficStats = [];
$domainTrendSeries = [];
$utmSourcesStats = [];
$utmMediumStats = [];
$utmCampaignStats = [];
$searchTermsStats = [];
$funnelBySource = [];
$revenueBySource = [];
$articleStats = [
    'today' => ['visits' => 0, 'uniques' => 0, 'bots' => 0],
    'd30' => ['visits' => 0, 'uniques' => 0, 'bots' => 0],
];
$toolsStats = [
    'today' => ['visits' => 0, 'uniques' => 0, 'bots' => 0],
    'd30' => ['visits' => 0, 'uniques' => 0, 'bots' => 0],
];
$topArticles30d = [];
$topTools30d = [];
$articleTopTrendSeries30d = [];

$sectionLabelsMap = [
    'home' => 'Home',
    'blog' => 'Journal',
    'services' => 'Services',
    'projects' => 'Products',
    'cases' => 'Cases',
    'contact' => 'Contact',
    'audit' => 'Audit',
    'adminpanel' => 'Adminpanel',
    'system' => 'System',
];
$sectionStats = [];
foreach ($sectionLabelsMap as $key => $label) {
    $sectionStats[$key] = [
        'key' => $key,
        'label' => $label,
        'today' => ['visits' => 0, 'uniques' => 0, 'bots' => 0],
        'd30' => ['visits' => 0, 'uniques' => 0, 'bots' => 0],
    ];
}

$logPageSizes = [50, 100, 500, 1000];
$logsPerPage = adminpanel_int_in_list($_GET['logs_per_page'] ?? 100, $logPageSizes, 100);
$logsPage = max(1, (int)($_GET['logs_page'] ?? 1));
$logsTotalRows = 0;
$logsTotalPages = 1;
$logsRows = [];
$logsActionMessage = '';
$logsActionType = '';
$hasAdminNotifications = adminpanel_table_exists($DB, 'admin_notifications');
$adminNotifications = [];
$adminNotificationsUnread = 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $hasAdminNotifications) {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'admin_notification_mark_read') {
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        if ($notificationId > 0) {
            $hasReadAt = adminpanel_table_has_column($DB, 'admin_notifications', 'read_at');
            $sql = "UPDATE admin_notifications
                    SET is_read = 1" . ($hasReadAt ? ", read_at = NOW()" : "") . "
                    WHERE id = " . $notificationId . "
                    LIMIT 1";
            mysqli_query($DB, $sql);
        }
        header('Location: /adminpanel/');
        exit;
    }
    if ($action === 'admin_notification_mark_all_read') {
        $hasReadAt = adminpanel_table_has_column($DB, 'admin_notifications', 'read_at');
        $sql = "UPDATE admin_notifications
                SET is_read = 1" . ($hasReadAt ? ", read_at = NOW()" : "") . "
                WHERE is_read = 0";
        mysqli_query($DB, $sql);
        header('Location: /adminpanel/');
        exit;
    }
}

if ($hasAdminNotifications) {
    $openNotificationId = (int)($_GET['admin_notification_open'] ?? 0);
    if ($openNotificationId > 0) {
        $openRows = $FRMWRK->DBRecords(
            "SELECT id, link_url
             FROM admin_notifications
             WHERE id = " . $openNotificationId . "
             LIMIT 1"
        );
        $targetUrl = '/adminpanel/examples/';
        if (!empty($openRows)) {
            $targetUrlRaw = trim((string)($openRows[0]['link_url'] ?? ''));
            if ($targetUrlRaw !== '') {
                $targetUrl = $targetUrlRaw;
            }
            $hasReadAt = adminpanel_table_has_column($DB, 'admin_notifications', 'read_at');
            mysqli_query(
                $DB,
                "UPDATE admin_notifications
                 SET is_read = 1" . ($hasReadAt ? ", read_at = NOW()" : "") . "
                 WHERE id = " . $openNotificationId . "
                 LIMIT 1"
            );
        }
        header('Location: ' . $targetUrl);
        exit;
    }

    $adminNotificationsUnreadRows = $FRMWRK->DBRecords("SELECT COUNT(*) AS c FROM admin_notifications WHERE is_read = 0");
    $adminNotificationsUnread = (int)($adminNotificationsUnreadRows[0]['c'] ?? 0);

    $hasLink = adminpanel_table_has_column($DB, 'admin_notifications', 'link_url');
    $hasPayload = adminpanel_table_has_column($DB, 'admin_notifications', 'payload_json');
    $adminNotifications = $FRMWRK->DBRecords(
        "SELECT id, type, title, message, is_read, created_at, "
        . ($hasLink ? "link_url" : "'' AS link_url") . ", "
        . ($hasPayload ? "payload_json" : "'{}' AS payload_json") . "
         FROM admin_notifications
         ORDER BY id DESC
         LIMIT 20"
    );
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $hasVisits) {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'add_ip_threat') {
        $visitId = (int)($_POST['visit_id'] ?? 0);
        $redirectPage = max(1, (int)($_POST['logs_page'] ?? $logsPage));
        $redirectPerPage = adminpanel_int_in_list($_POST['logs_per_page'] ?? $logsPerPage, $logPageSizes, $logsPerPage);

        if ($visitId > 0) {
            $visitRows = $FRMWRK->DBRecords(
                "SELECT id, ip
                 FROM analytics_visits
                 WHERE id = " . (int)$visitId . "
                 LIMIT 1"
            );
            $visitIp = trim((string)($visitRows[0]['ip'] ?? ''));
            if ($visitIp !== '' && filter_var($visitIp, FILTER_VALIDATE_IP)) {
                if (function_exists('analytics_create_ip_threat_rule')) {
                    analytics_create_ip_threat_rule($DB, $visitIp, 'Manual block from admin logs');
                }
                if (function_exists('analytics_mark_ip_as_threat')) {
                    analytics_mark_ip_as_threat($DB, $visitIp, 'manual_from_admin_logs');
                }
                $ipSafe = mysqli_real_escape_string($DB, $visitIp);
                mysqli_query(
                    $DB,
                    "UPDATE analytics_visits
                     SET is_bot = 1,
                         is_suspect = 1,
                         suspect_reason = 'manual_ip_threat'
                     WHERE ip = '{$ipSafe}'"
                );

                header('Location: /adminpanel/?logs_page=' . $redirectPage . '&logs_per_page=' . $redirectPerPage . '&threat_added=1');
                exit;
            }
        }
        header('Location: /adminpanel/?logs_page=' . $redirectPage . '&logs_per_page=' . $redirectPerPage . '&threat_added=0');
        exit;
    }
}

if (isset($_GET['threat_added'])) {
    if ((string)$_GET['threat_added'] === '1') {
        $logsActionMessage = 'IP added to threats. All visits from this IP are now excluded.';
        $logsActionType = 'success';
    } else {
        $logsActionMessage = 'Unable to add IP to threats.';
        $logsActionType = 'danger';
    }
}

if ($hasVisits) {
// 🔹 Считаем визиты по дням за 30 дней без ботов
$rows = $FRMWRK->DBRecords("
    SELECT DATE(visited_at) AS d, COUNT(*) AS c
    FROM analytics_visits
    WHERE visited_at >= {$trendFromDateSql}
      AND visited_at < {$trendToDateSql}
      AND is_bot = 0
      AND COALESCE(NULLIF(host,''), 'unknown') <> 'chlenix.ru'
    GROUP BY DATE(visited_at)
");

foreach ($rows as $r) {
    $key = (string)($r['d'] ?? '');
    if ($key !== '' && array_key_exists($key, $seriesVisits30d)) {
        $seriesVisits30d[$key] = (int)($r['c'] ?? 0);
    }
}

$botRows = $FRMWRK->DBRecords("
    SELECT DATE(visited_at) AS d, COUNT(*) AS c
    FROM analytics_visits
    WHERE visited_at >= {$trendFromDateSql}
      AND visited_at < {$trendToDateSql}
      AND is_bot = 1
    GROUP BY DATE(visited_at)
");
foreach ($botRows as $r) {
    $key = (string)($r['d'] ?? '');
    if ($key !== '' && array_key_exists($key, $seriesBotVisits30d)) {
        $seriesBotVisits30d[$key] = (int)($r['c'] ?? 0);
    }
}


// 🔹 Уникальные посетители без ботов
$uniqRows = $FRMWRK->DBRecords("
    SELECT DATE(visited_at) d, COUNT(DISTINCT ip) c 
    FROM analytics_visits 
    WHERE visited_at >= {$trendFromDateSql}
      AND visited_at < {$trendToDateSql}
      AND is_bot = 0
      AND COALESCE(NULLIF(host,''), 'unknown') <> 'chlenix.ru'
    GROUP BY DATE(visited_at)
");
foreach ($uniqRows as $r) {
    $key = (string)($r['d'] ?? '');
    if ($key !== '' && array_key_exists($key, $seriesUniques30d)) {
        $seriesUniques30d[$key] = (int)$r['c']; // уникальные без ботов
    }
}

// 🔹 Сегодняшние визиты без ботов
$today = $FRMWRK->DBRecords("
    SELECT 
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS total_non_bot,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniq_non_bot,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE DATE(visited_at)=CURDATE()
");

$todayVisits = (int)($today[0]['total_non_bot'] ?? 0);          // визиты без ботов
$todayUniqueVisitors = (int)($today[0]['uniq_non_bot'] ?? 0);   // уникальные не-боты
$todayBotVisits = (int)($today[0]['bots'] ?? 0);                // боты сегодня

// 🔹 30-дневная статистика: боты / не-боты
$sum = $FRMWRK->DBRecords("
    SELECT 
        SUM(CASE WHEN is_bot = 0 THEN 1 ELSE 0 END) AS total_non_bot,
        COUNT(DISTINCT CASE WHEN is_bot = 0 THEN ip END) AS uniq_non_bot,
        SUM(CASE WHEN is_bot = 1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
");

$visits30d = (int)($sum[0]['total_non_bot'] ?? 0);        // все не-бот визиты
$uniqueVisitors30d = (int)($sum[0]['uniq_non_bot'] ?? 0); // уникальные не-боты
$botVisits30d = (int)($sum[0]['bots'] ?? 0);              // визиты ботов


// 🔹 Дополнительно: уникальные посетители без ботов (для конверсий)
$sumNonBotUniq = $FRMWRK->DBRecords("
    SELECT COUNT(DISTINCT ip) uniq 
    FROM analytics_visits 
    WHERE visited_at >= NOW() - INTERVAL 30 DAY 
      AND is_bot=0
");
$nonBotUniqueVisitors30d = (int)($sumNonBotUniq[0]['uniq'] ?? 0);

// Article stats: /examples/article/*
$articlePathWhereSql = "(
    ((path LIKE '/blog/%' AND path NOT IN ('/blog', '/blog/')) OR (path LIKE '/journal/%' AND path NOT IN ('/journal', '/journal/')) OR (path LIKE '/playbooks/%' AND path NOT IN ('/playbooks', '/playbooks/')))
    OR
    path LIKE '/examples/article/%'
    OR (path LIKE '/articles/%' AND path NOT IN ('/articles', '/articles/'))
)";
$articleToday = $FRMWRK->DBRecords("
    SELECT
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniques,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE DATE(visited_at)=CURDATE()
      AND {$articlePathWhereSql}
");
$articleStats['today']['visits'] = (int)($articleToday[0]['visits'] ?? 0);
$articleStats['today']['uniques'] = (int)($articleToday[0]['uniques'] ?? 0);
$articleStats['today']['bots'] = (int)($articleToday[0]['bots'] ?? 0);

$article30d = $FRMWRK->DBRecords("
    SELECT
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniques,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND {$articlePathWhereSql}
");
$articleStats['d30']['visits'] = (int)($article30d[0]['visits'] ?? 0);
$articleStats['d30']['uniques'] = (int)($article30d[0]['uniques'] ?? 0);
$articleStats['d30']['bots'] = (int)($article30d[0]['bots'] ?? 0);

// Tools stats: /tools*
$toolsPathWhereSql = "(
    ((path LIKE '/tools%') OR (path LIKE '/use%'))
    AND path NOT IN ('/tools', '/tools/', '/use', '/use/')
)";
$toolsToday = $FRMWRK->DBRecords("
    SELECT
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniques,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE DATE(visited_at)=CURDATE()
      AND {$toolsPathWhereSql}
");
$toolsStats['today']['visits'] = (int)($toolsToday[0]['visits'] ?? 0);
$toolsStats['today']['uniques'] = (int)($toolsToday[0]['uniques'] ?? 0);
$toolsStats['today']['bots'] = (int)($toolsToday[0]['bots'] ?? 0);

$tools30d = $FRMWRK->DBRecords("
    SELECT
        SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
        COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniques,
        SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND {$toolsPathWhereSql}
");
$toolsStats['d30']['visits'] = (int)($tools30d[0]['visits'] ?? 0);
$toolsStats['d30']['uniques'] = (int)($tools30d[0]['uniques'] ?? 0);
$toolsStats['d30']['bots'] = (int)($tools30d[0]['bots'] ?? 0);

// Top tools by non-bot visits (30d)
$toolPathRows = $FRMWRK->DBRecords("
    SELECT path,
           SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
           SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND {$toolsPathWhereSql}
    GROUP BY path
    ORDER BY visits DESC
    LIMIT 10
");
foreach ($toolPathRows as $row) {
    $path = trim((string)($row['path'] ?? ''));
    $toolPart = preg_replace('~^/(tools|use)/?~', '', $path);
    $toolPart = preg_replace('~/.*$~', '', (string)$toolPart);
    $toolPart = trim((string)$toolPart, '/');
    if ($toolPart === '') {
        continue;
    }
    $label = ucwords(str_replace(['-', '_'], ' ', $toolPart));
    $topTools30d[] = [
        'label' => $label,
        'visits' => (int)($row['visits'] ?? 0),
        'bots' => (int)($row['bots'] ?? 0),
    ];
}

// Top articles by non-bot visits (30d): aggregate by slug across all article paths.
// Then select TOP 10 RU + TOP 10 EN and build trend by this ordered set.
$articlePathRows = $FRMWRK->DBRecords("
    SELECT path,
           SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
           SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND {$articlePathWhereSql}
    GROUP BY path
");
$articleBySlug = [];
foreach ($articlePathRows as $row) {
    $path = (string)($row['path'] ?? '');
    $slug = adminpanel_article_slug_from_path($path);
    if ($slug === '') {
        continue;
    }
    if (!isset($articleBySlug[$slug])) {
        $articleBySlug[$slug] = ['visits' => 0, 'bots' => 0];
    }
    $articleBySlug[$slug]['visits'] += (int)($row['visits'] ?? 0);
    $articleBySlug[$slug]['bots'] += (int)($row['bots'] ?? 0);
}
$slugSet = [];
foreach (array_keys($articleBySlug) as $slug) {
    $slugSet[$slug] = true;
}
$titleBySlug = [];
$langBySlug = [];
if (!empty($slugSet) && adminpanel_table_exists($DB, 'examples_articles')) {
    $hasLangCode = adminpanel_table_has_column($DB, 'examples_articles', 'lang_code');
    $slugParts = [];
    foreach (array_keys($slugSet) as $slug) {
        $slugParts[] = "'" . mysqli_real_escape_string($DB, $slug) . "'";
    }
    if (!empty($slugParts)) {
        $titleRows = $FRMWRK->DBRecords(
            "SELECT slug, title, " . ($hasLangCode ? "lang_code" : "'en' AS lang_code") . "
             FROM examples_articles
             WHERE slug IN (" . implode(',', $slugParts) . ")
             ORDER BY updated_at DESC, id DESC"
        );
        foreach ($titleRows as $row) {
            $slug = (string)($row['slug'] ?? '');
            if ($slug !== '' && !isset($titleBySlug[$slug])) {
                $titleBySlug[$slug] = (string)($row['title'] ?? $slug);
                $langRaw = strtolower(trim((string)($row['lang_code'] ?? 'en')));
                $langBySlug[$slug] = ($langRaw === 'ru') ? 'ru' : 'en';
            }
        }
    }
}

$articleRu = [];
$articleEn = [];
foreach ($articleBySlug as $slug => $stats) {
    $langCode = ((string)($langBySlug[$slug] ?? 'en') === 'ru') ? 'ru' : 'en';
    $row = [
        'slug' => $slug,
        'label' => (string)($titleBySlug[$slug] ?? $slug),
        'lang' => $langCode,
        'visits' => (int)($stats['visits'] ?? 0),
        'bots' => (int)($stats['bots'] ?? 0),
    ];
    if ($langCode === 'ru') {
        $articleRu[] = $row;
    } else {
        $articleEn[] = $row;
    }
}
$sortTopArticles = static function (array &$rows): void {
    usort($rows, static function (array $a, array $b): int {
        if ((int)$a['visits'] === (int)$b['visits']) {
            return (int)$b['bots'] <=> (int)$a['bots'];
        }
        return (int)$b['visits'] <=> (int)$a['visits'];
    });
};
$sortTopArticles($articleRu);
$sortTopArticles($articleEn);

$selectedTopArticles = array_merge($articleRu, $articleEn);

$topArticleSlugs = [];
$topArticleNamesBySlug = [];
foreach ($selectedTopArticles as $row) {
    $slug = (string)($row['slug'] ?? '');
    if ($slug === '' || isset($topArticleSlugs[$slug])) {
        continue;
    }
    $topArticleSlugs[$slug] = true;
    $topArticleNamesBySlug[$slug] = (string)($row['label'] ?? $slug);
    $topArticles30d[] = [
        'label' => (string)($row['label'] ?? $slug),
        'lang' => strtoupper((string)($row['lang'] ?? 'en')),
        'visits' => (int)($row['visits'] ?? 0),
        'bots' => (int)($row['bots'] ?? 0),
    ];
}

// Article activity trend (30d): one line per every article found in period.
if (!empty($topArticleSlugs)) {
    $trendRowsByPath = $FRMWRK->DBRecords("
        SELECT path,
               DATE(visited_at) AS d,
               SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits
        FROM analytics_visits
        WHERE visited_at >= NOW() - INTERVAL 30 DAY
          AND {$articlePathWhereSql}
        GROUP BY path, DATE(visited_at)
    ");
    $baseKeys = array_keys($seriesVisits30d);
    $trendBySlug = [];
    foreach (array_keys($topArticleSlugs) as $slug) {
        $trendBySlug[$slug] = array_fill_keys($baseKeys, 0);
    }
    foreach ($trendRowsByPath as $row) {
        $path = (string)($row['path'] ?? '');
        $d = (string)($row['d'] ?? '');
        if ($path === '' || $d === '') {
            continue;
        }
        $slug = adminpanel_article_slug_from_path($path);
        if ($slug === '' || !isset($trendBySlug[$slug]) || !array_key_exists($d, $trendBySlug[$slug])) {
            continue;
        }
        $trendBySlug[$slug][$d] += (int)($row['visits'] ?? 0);
    }
    foreach (array_keys($topArticleSlugs) as $slug) {
        $articleTopTrendSeries30d[] = [
            'slug' => $slug,
            'name' => (string)($topArticleNamesBySlug[$slug] ?? $slug),
            'data' => array_values($trendBySlug[$slug]),
        ];
    }
}

// Section stats (Examples = /examples/* including articles)
$sectionCaseSql = "
    CASE
        WHEN path IS NULL OR path = '' OR path = '/' THEN 'home'
        WHEN path LIKE '/journal%' OR path LIKE '/playbooks%' OR path LIKE '/blog%' OR path LIKE '/examples/article/%' OR path LIKE '/articles/%' THEN 'blog'
        WHEN path LIKE '/services%' THEN 'services'
        WHEN path LIKE '/projects%' THEN 'projects'
        WHEN path LIKE '/contact%' THEN 'contact'
        WHEN path LIKE '/audit%' THEN 'audit'
        WHEN path LIKE '/adminpanel%' OR path LIKE '/dashboard%' THEN 'adminpanel'
        WHEN path LIKE '/robots.txt%' OR path LIKE '/sitemap.xml%' THEN 'system'
        ELSE 'other'
    END
";
$sectionRows30d = $FRMWRK->DBRecords("
    SELECT {$sectionCaseSql} AS section_key,
           SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
           COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniques,
           SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
    GROUP BY section_key
");
foreach ($sectionRows30d as $row) {
    $key = (string)($row['section_key'] ?? 'other');
    if ($key === 'other' || !isset($sectionStats[$key])) {
        continue;
    }
    $sectionStats[$key]['d30']['visits'] = (int)($row['visits'] ?? 0);
    $sectionStats[$key]['d30']['uniques'] = (int)($row['uniques'] ?? 0);
    $sectionStats[$key]['d30']['bots'] = (int)($row['bots'] ?? 0);
}

$sectionRowsToday = $FRMWRK->DBRecords("
    SELECT {$sectionCaseSql} AS section_key,
           SUM(CASE WHEN is_bot=0 THEN 1 ELSE 0 END) AS visits,
           COUNT(DISTINCT CASE WHEN is_bot=0 THEN ip END) AS uniques,
           SUM(CASE WHEN is_bot=1 THEN 1 ELSE 0 END) AS bots
    FROM analytics_visits
    WHERE DATE(visited_at)=CURDATE()
    GROUP BY section_key
");
foreach ($sectionRowsToday as $row) {
    $key = (string)($row['section_key'] ?? 'other');
    if ($key === 'other' || !isset($sectionStats[$key])) {
        continue;
    }
    $sectionStats[$key]['today']['visits'] = (int)($row['visits'] ?? 0);
    $sectionStats[$key]['today']['uniques'] = (int)($row['uniques'] ?? 0);
    $sectionStats[$key]['today']['bots'] = (int)($row['bots'] ?? 0);
}


   // 🔹 Статистика по устройствам (все визиты, включая ботов)
$deviceRows = $FRMWRK->DBRecords("
    SELECT device_type, COUNT(*) c 
    FROM analytics_visits 
    WHERE visited_at >= NOW() - INTERVAL 30 DAY 
    GROUP BY device_type
");
$desktop30d = $mobile30d = $tablet30d = 0;
foreach ($deviceRows as $r) {
    $type = strtolower((string)($r['device_type'] ?? 'unknown'));
    $cnt = (int)($r['c'] ?? 0);
    $devices[] = ['label' => strtoupper($type), 'value' => $cnt];
    if ($type === 'desktop') $desktop30d = $cnt;
    if ($type === 'mobile') $mobile30d = $cnt;
    if ($type === 'tablet') $tablet30d = $cnt;
}

// 🔹 Топ стран (только уникальные не-боты)
$countriesRows = $FRMWRK->DBRecords("
    SELECT
        COALESCE(
            NULLIF(
                TRIM(
                    CASE
                        WHEN LOWER(TRIM(COALESCE(country_name, ''))) IN ('unknown', 'unkonwn', 'n/a', 'na', 'none', 'null', '-') THEN ''
                        ELSE COALESCE(country_name, '')
                    END
                ),
                ''
            ),
            NULLIF(TRIM(COALESCE(country_iso2, '')), ''),
            'Unknown'
        ) AS country,
        COUNT(*) AS c
    FROM analytics_visits 
    WHERE visited_at >= NOW() - INTERVAL 30 DAY 
      AND is_bot=0
    GROUP BY country 
    ORDER BY c DESC 
    LIMIT 10
");
$countries = [];
foreach ($countriesRows as $r) {
    $countries[] = [
        'label' => (string)$r['country'],
        'value' => (int)$r['c']
    ];
}

// 🔹 Источники трафика (только не-боты)
$sourceRows = $FRMWRK->DBRecords("
    SELECT COALESCE(NULLIF(source_type,''),'unknown') src, COUNT(*) c 
    FROM analytics_visits 
    WHERE visited_at >= NOW() - INTERVAL 30 DAY 
      AND is_bot=0
    GROUP BY src 
    ORDER BY c DESC
");
$sources = [];
foreach ($sourceRows as $r) {
    $sources[] = [
        'label' => (string)$r['src'], 
        'value' => (int)$r['c']
    ];
}

// 🔹 UTM источники (только не-боты)
$utmSourcesStats = $FRMWRK->DBRecords("
    SELECT COALESCE(NULLIF(utm_source,''), '(none)') AS label, COUNT(*) AS visits
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND is_bot = 0
    GROUP BY COALESCE(NULLIF(utm_source,''), '(none)')
    ORDER BY visits DESC
    LIMIT 15
");

// 🔹 UTM каналы (только не-боты)
$utmMediumStats = $FRMWRK->DBRecords("
    SELECT COALESCE(NULLIF(utm_medium,''), '(none)') AS label, COUNT(*) AS visits
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND is_bot = 0
    GROUP BY COALESCE(NULLIF(utm_medium,''), '(none)')
    ORDER BY visits DESC
    LIMIT 15
");

// 🔹 UTM кампании (только не-боты)
$utmCampaignStats = $FRMWRK->DBRecords("
    SELECT COALESCE(NULLIF(utm_campaign,''), '(none)') AS label, COUNT(*) AS visits
    FROM analytics_visits
    WHERE visited_at >= NOW() - INTERVAL 30 DAY
      AND is_bot = 0
    GROUP BY COALESCE(NULLIF(utm_campaign,''), '(none)')
    ORDER BY visits DESC
    LIMIT 15
");

// 🔹 Поисковые запросы (только не-боты)
if ($hasVisitsSearchQuery) {
    $searchTermsStats = $FRMWRK->DBRecords("
        SELECT search_query AS label, COUNT(*) AS visits
        FROM analytics_visits
        WHERE visited_at >= NOW() - INTERVAL 30 DAY
          AND is_bot = 0
          AND search_query IS NOT NULL
          AND search_query <> ''
        GROUP BY search_query
        ORDER BY visits DESC
        LIMIT 20
    ");
}

// 🔹 Топ доменов по визитам (все визиты, включая ботов)
$domainTrafficStats = $FRMWRK->DBRecords("
    SELECT COALESCE(NULLIF(host,''), 'unknown') AS domain_host, COUNT(*) AS visits
    FROM analytics_visits
    WHERE visited_at >= {$trendFromDateSql}
      AND visited_at < {$trendToDateSql}
      AND is_bot = 0
      AND COALESCE(NULLIF(host,''), 'unknown') <> 'chlenix.ru'
    GROUP BY COALESCE(NULLIF(host,''), 'unknown')
    ORDER BY visits DESC
    LIMIT 10
");

// 🔹 Топ доменов для трендов (все визиты)
$topDomainsForTrend = array_map(
    static fn($row) => (string)($row['domain_host'] ?? ''),
    array_slice($domainTrafficStats, 0, 5)
);
$topDomainsForTrend = array_values(array_filter($topDomainsForTrend, static fn($h) => $h !== ''));

if (!empty($topDomainsForTrend)) {
    $trendMapAll = [];
    foreach ($topDomainsForTrend as $domainHost) {
        $baseDays = array_fill_keys(array_keys($seriesVisits30d), 0);
        $trendMapAll[$domainHost] = $baseDays;
    }

    $rowsByDomainAll = $FRMWRK->DBRecords("
        SELECT
            COALESCE(NULLIF(host,''), 'unknown') AS domain_host,
            DATE(visited_at) AS d,
            COUNT(*) AS c
        FROM analytics_visits
        WHERE visited_at >= {$trendFromDateSql}
          AND visited_at < {$trendToDateSql}
          AND is_bot = 0
          AND COALESCE(NULLIF(host,''), 'unknown') <> 'chlenix.ru'
          AND COALESCE(NULLIF(host,''), 'unknown') IN ('" . implode("','", array_map(static fn($h) => mysqli_real_escape_string($DB, $h), $topDomainsForTrend)) . "')
        GROUP BY domain_host, DATE(visited_at)
    ");

    foreach ($rowsByDomainAll as $row) {
        $domainHost = (string)($row['domain_host'] ?? '');
        $dateKey = (string)($row['d'] ?? '');
        $count = (int)($row['c'] ?? 0);
        if ($domainHost !== '' && $dateKey !== '' && isset($trendMapAll[$domainHost][$dateKey])) {
            $trendMapAll[$domainHost][$dateKey] = $count;
        }
    }

    foreach ($topDomainsForTrend as $domainHost) {
        $domainTrendSeries[] = [
            'name' => $domainHost,
            'data' => array_values($trendMapAll[$domainHost] ?? []),
        ];
    }
}


// 🔹 Рефереры (все визиты, включая ботов)
$refTotalRows = $FRMWRK->DBRecords("
    SELECT COUNT(DISTINCT COALESCE(NULLIF(referrer_host,''), 'direct')) AS total
    FROM analytics_visits
");
$referrersTotalUnique = (int)($refTotalRows[0]['total'] ?? 0);
$refLimitSql = $referrersTotalUnique > 10 ? " LIMIT 10" : "";

$referrersStats = $FRMWRK->DBRecords("
    SELECT
        COALESCE(NULLIF(referrer_host,''), 'direct') AS referrer_host,
        COUNT(*) AS visits
    FROM analytics_visits
    GROUP BY COALESCE(NULLIF(referrer_host,''), 'direct')
    ORDER BY visits DESC
    $refLimitSql
");

// 🔹 Логи для таблицы (только не-боты и не подозрительные визиты)
$totalRows = $FRMWRK->DBRecords("
    SELECT COUNT(*) AS total 
    FROM analytics_visits
    WHERE (is_suspect IS NULL OR is_suspect = 0)
");
$logsTotalRows = (int)($totalRows[0]['total'] ?? 0);
$logsTotalPages = max(1, (int)ceil($logsTotalRows / $logsPerPage));
if ($logsPage > $logsTotalPages) {
    $logsPage = $logsTotalPages;
}
$offset = ($logsPage - 1) * $logsPerPage;
$logsSearchSelect = $hasVisitsSearchQuery ? "search_query" : "NULL AS search_query";

$logsRows = $FRMWRK->DBRecords("
    SELECT
        id, visited_at, host, ip, method, path, source_type, referrer_host, country_iso2, country_name, city_name, device_type, is_bot, user_agent, query_string,
        utm_source, utm_medium, utm_campaign, utm_term, utm_content, {$logsSearchSelect}
    FROM analytics_visits
    WHERE (is_suspect IS NULL OR is_suspect = 0)
    ORDER BY id DESC
    LIMIT " . (int)$logsPerPage . " OFFSET " . (int)$offset
);


}

if ($hasLeads) {
    $leadRows = $FRMWRK->DBRecords("SELECT event_type, COUNT(*) c FROM analytics_lead_events WHERE event_time >= NOW() - INTERVAL 30 DAY GROUP BY event_type ORDER BY c DESC");
    $leadMap = [];
    foreach ($leadRows as $r) {
        $event = (string)($r['event_type'] ?? 'unknown');
        $cnt = (int)($r['c'] ?? 0);
        $leadMap[$event] = $cnt;
        $leadEvents[] = ['label' => $event, 'value' => $cnt];
    }
    $registrationView30d = (int)($leadMap['registration_page_view'] ?? 0);
    $registrations30d = (int)($leadMap['registration_success'] ?? 0);
    $ordersRows = $FRMWRK->DBRecords(
        "SELECT
            COUNT(
                DISTINCT COALESCE(
                    NULLIF(CAST(subscription_id AS CHAR), ''),
                    CONCAT('req:', NULLIF(request_id, ''))
                )
            ) c
         FROM analytics_lead_events
         WHERE event_time >= NOW() - INTERVAL 30 DAY
           AND event_type = 'subscription_order_created'"
    );
    $orders30d = (int)($ordersRows[0]['c'] ?? 0);

    $dailyLeadRows = $FRMWRK->DBRecords(
        "SELECT DATE(event_time) d, event_type, COUNT(*) c
         FROM analytics_lead_events
         WHERE event_time >= NOW() - INTERVAL 30 DAY
           AND event_type = 'registration_success'
         GROUP BY DATE(event_time), event_type"
    );
    foreach ($dailyLeadRows as $r) {
        $key = (string)($r['d'] ?? '');
        $eventType = (string)($r['event_type'] ?? '');
        $count = (int)($r['c'] ?? 0);
        if ($key === '') {
            continue;
        }
        if ($eventType === 'registration_success' && array_key_exists($key, $seriesRegistrations30d)) {
            $seriesRegistrations30d[$key] = $count;
        }
    }

    $dailyOrdersRows = $FRMWRK->DBRecords(
        "SELECT
            DATE(event_time) d,
            COUNT(
                DISTINCT COALESCE(
                    NULLIF(CAST(subscription_id AS CHAR), ''),
                    CONCAT('req:', NULLIF(request_id, ''))
                )
            ) c
         FROM analytics_lead_events
         WHERE event_time >= NOW() - INTERVAL 30 DAY
           AND event_type = 'subscription_order_created'
         GROUP BY DATE(event_time)"
    );
    foreach ($dailyOrdersRows as $r) {
        $key = (string)($r['d'] ?? '');
        $count = (int)($r['c'] ?? 0);
        if ($key !== '' && array_key_exists($key, $seriesOrders30d)) {
            $seriesOrders30d[$key] = $count;
        }
    }

    if ($hasLeadsAttribution && $hasVisits) {
        $funnelBySource = $FRMWRK->DBRecords(
            "SELECT
                v.source_key,
                v.visits,
                COALESCE(r.registrations, 0) AS registrations,
                COALESCE(o.orders, 0) AS orders
             FROM (
                SELECT
                    COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct') AS source_key,
                    COUNT(*) AS visits
                FROM analytics_visits
                WHERE visited_at >= NOW() - INTERVAL 30 DAY
                  AND is_bot = 0
                GROUP BY COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct')
             ) v
             LEFT JOIN (
                SELECT
                    COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct') AS source_key,
                    COUNT(*) AS registrations
                FROM analytics_lead_events
                WHERE event_time >= NOW() - INTERVAL 30 DAY
                  AND event_type = 'registration_success'
                GROUP BY COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct')
             ) r ON r.source_key = v.source_key
             LEFT JOIN (
                SELECT
                    source_key,
                    COUNT(*) AS orders
                FROM (
                    SELECT
                        COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct') AS source_key,
                        COALESCE(NULLIF(CAST(subscription_id AS CHAR), ''), CONCAT('req:', NULLIF(request_id, ''))) AS order_key
                    FROM analytics_lead_events
                    WHERE event_time >= NOW() - INTERVAL 30 DAY
                      AND event_type = 'subscription_order_created'
                    GROUP BY
                        COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct'),
                        COALESCE(NULLIF(CAST(subscription_id AS CHAR), ''), CONCAT('req:', NULLIF(request_id, '')))
                ) q
                GROUP BY source_key
             ) o ON o.source_key = v.source_key
             ORDER BY visits DESC
             LIMIT 25"
        );

        $revenueBySource = $FRMWRK->DBRecords(
            "SELECT
                source_key,
                COUNT(*) AS orders,
                COALESCE(SUM(COALESCE(amount_usd, 0)), 0) AS revenue_usd
             FROM (
                SELECT
                    COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct') AS source_key,
                    COALESCE(NULLIF(CAST(subscription_id AS CHAR), ''), CONCAT('req:', NULLIF(request_id, ''))) AS order_key,
                    MAX(COALESCE(amount_usd, 0)) AS amount_usd
                FROM analytics_lead_events
                WHERE event_time >= NOW() - INTERVAL 30 DAY
                  AND event_type = 'subscription_order_created'
                GROUP BY
                    COALESCE(NULLIF(utm_source,''), NULLIF(referrer_host,''), NULLIF(source_type,''), 'direct'),
                    COALESCE(NULLIF(CAST(subscription_id AS CHAR), ''), CONCAT('req:', NULLIF(request_id, '')))
             ) x
             GROUP BY source_key
             ORDER BY revenue_usd DESC
             LIMIT 20"
        );
    }
}

if ($hasIpActivity) {
    $api = $FRMWRK->DBRecords("SELECT COUNT(*) c FROM ip_activity WHERE event='lookup' AND created_at >= NOW() - INTERVAL 30 DAY");
    $apiLookups30d = (int)($api[0]['c'] ?? 0);
    $apiDaily = $FRMWRK->DBRecords("
        SELECT DATE(created_at) AS d, COUNT(*) AS c
        FROM ip_activity
        WHERE event = 'lookup'
          AND created_at >= NOW() - INTERVAL 30 DAY
        GROUP BY DATE(created_at)
    ");
    foreach ($apiDaily as $r) {
        $key = (string)($r['d'] ?? '');
        if ($key !== '' && array_key_exists($key, $seriesApiLookups30d)) {
            $seriesApiLookups30d[$key] = (int)($r['c'] ?? 0);
        }
    }

    if (adminpanel_table_exists($DB, 'adminpanel_users')) {
        $topApiClients = $FRMWRK->DBRecords(
            "SELECT
                ia.account_id,
                COUNT(*) AS requests,
                MAX(ia.created_at) AS last_seen,
                a.email AS email
             FROM ip_activity ia
             LEFT JOIN adminpanel_users a ON a.id = ia.account_id
             WHERE ia.event='lookup'
               AND ia.created_at >= NOW() - INTERVAL 30 DAY
             GROUP BY ia.account_id
             ORDER BY requests DESC
             LIMIT 10"
        );
    } elseif (adminpanel_table_exists($DB, 'admins')) {
        $topApiClients = $FRMWRK->DBRecords(
            "SELECT
                ia.account_id,
                COUNT(*) AS requests,
                MAX(ia.created_at) AS last_seen,
                a.email AS email
             FROM ip_activity ia
             LEFT JOIN admins a ON a.id = ia.account_id
             WHERE ia.event='lookup'
               AND ia.created_at >= NOW() - INTERVAL 30 DAY
             GROUP BY ia.account_id
             ORDER BY requests DESC
             LIMIT 10"
        );
    } else {
        $topApiClients = $FRMWRK->DBRecords(
            "SELECT
                ia.account_id,
                COUNT(*) AS requests,
                MAX(ia.created_at) AS last_seen,
                '' AS email
             FROM ip_activity ia
             WHERE ia.event='lookup'
               AND ia.created_at >= NOW() - INTERVAL 30 DAY
             GROUP BY ia.account_id
             ORDER BY requests DESC
             LIMIT 10"
        );
    }

    $topSourceIps = $FRMWRK->DBRecords(
        "SELECT ip, COUNT(*) AS requests, MAX(created_at) AS last_seen
         FROM ip_activity
         WHERE event='lookup'
           AND created_at >= NOW() - INTERVAL 30 DAY
         GROUP BY ip
         ORDER BY requests DESC
         LIMIT 10"
    );
}

$nonBotVisits30d = max(0, $visits30d - $botVisits30d);
$conversionUniqueToReg = adminpanel_pct($registrations30d, max(1, $nonBotUniqueVisitors30d > 0 ? $nonBotUniqueVisitors30d : $uniqueVisitors30d));
$conversionRegToOrder = adminpanel_pct($orders30d, max(1, $registrations30d));
$activeSubscriptionRate = adminpanel_pct((int)$counts['subscriptions_active'], max(1, (int)$counts['subscriptions_total']));

$sourceFunnelChart = [];
if (!empty($funnelBySource)) {
    foreach (array_slice($funnelBySource, 0, 8) as $row) {
        $visits = (int)($row['visits'] ?? 0);
        $regs = (int)($row['registrations'] ?? 0);
        $orders = (int)($row['orders'] ?? 0);
        $sourceFunnelChart[] = [
            'label' => (string)($row['source_key'] ?? 'unknown'),
            'visits' => $visits,
            'registrations' => $regs,
            'orders' => $orders,
            'cr_visit_to_reg' => adminpanel_pct($regs, max(1, $visits)),
            'cr_reg_to_order' => adminpanel_pct($orders, max(1, $regs)),
        ];
    }
}

$funnelSequence = [
    ['step' => 'Visits', 'count' => (int)$visits30d],
    ['step' => 'Registration Page Views', 'count' => (int)$registrationView30d],
    ['step' => 'Registrations', 'count' => (int)$registrations30d],
    ['step' => 'Orders', 'count' => (int)$orders30d],
];

$chart = [
    'labels30d' => $labels30d,
    'visits30d' => array_values($seriesVisits30d),
    'uniques30d' => array_values($seriesUniques30d),
    'botVisits30d' => array_values($seriesBotVisits30d),
    'apiLookupsTrend30d' => array_values($seriesApiLookups30d),
    'registrations30d' => array_values($seriesRegistrations30d),
    'orders30d' => array_values($seriesOrders30d),
    'articleTopTrendSeries30d' => $articleTopTrendSeries30d,
    'devices' => $devices,
    'countries' => $countries,
    'sources' => $sources,
    'leadEvents' => $leadEvents,
    'domainTrendSeries' => $domainTrendSeries,
    'sourceFunnel' => $sourceFunnelChart,
    'funnelSequence' => $funnelSequence,
    'topArticles30d' => $topArticles30d,
    'topTools30d' => $topTools30d,
    'sectionStats' => array_values($sectionStats),
];

$sectionsTodayVisits = 0;
$sectionsTodayUniques = 0;
$sectionsTodayBots = 0;
$sections30dVisits = 0;
$sections30dUniques = 0;
$sections30dBots = 0;
foreach ($sectionStats as $row) {
    $sectionsTodayVisits += (int)($row['today']['visits'] ?? 0);
    $sectionsTodayUniques += (int)($row['today']['uniques'] ?? 0);
    $sectionsTodayBots += (int)($row['today']['bots'] ?? 0);
    $sections30dVisits += (int)($row['d30']['visits'] ?? 0);
    $sections30dUniques += (int)($row['d30']['uniques'] ?? 0);
    $sections30dBots += (int)($row['d30']['bots'] ?? 0);
}

$deviceTotal30d = max(1, $desktop30d + $mobile30d + $tablet30d);

$kpi = [
    'today_visits' => $todayVisits,
    'today_unique_visitors' => $todayUniqueVisitors,
    'visits_30d' => $visits30d,
    'unique_visitors_30d' => $uniqueVisitors30d,
    'bot_visits_30d' => $botVisits30d,
    'desktop_share' => adminpanel_pct($desktop30d, $deviceTotal30d),
    'mobile_share' => adminpanel_pct($mobile30d, $deviceTotal30d),
    'tablet_share' => adminpanel_pct($tablet30d, $deviceTotal30d),
    'registration_views_30d' => $registrationView30d,
    'registrations_30d' => $registrations30d,
    'orders_30d' => $orders30d,
    'conversion_unique_to_reg' => $conversionUniqueToReg,
    'conversion_reg_to_order' => $conversionRegToOrder,
    'active_subscription_rate' => $activeSubscriptionRate,
    'api_lookups_30d' => $apiLookups30d,
    'article_today_visits' => (int)($articleStats['today']['visits'] ?? 0),
    'article_today_uniques' => (int)($articleStats['today']['uniques'] ?? 0),
    'article_today_bots' => (int)($articleStats['today']['bots'] ?? 0),
    'article_30d_visits' => (int)($articleStats['d30']['visits'] ?? 0),
    'article_30d_uniques' => (int)($articleStats['d30']['uniques'] ?? 0),
    'article_30d_bots' => (int)($articleStats['d30']['bots'] ?? 0),
    'tools_today_visits' => (int)($toolsStats['today']['visits'] ?? 0),
    'tools_today_uniques' => (int)($toolsStats['today']['uniques'] ?? 0),
    'tools_today_bots' => (int)($toolsStats['today']['bots'] ?? 0),
    'tools_30d_visits' => (int)($toolsStats['d30']['visits'] ?? 0),
    'tools_30d_uniques' => (int)($toolsStats['d30']['uniques'] ?? 0),
    'tools_30d_bots' => (int)($toolsStats['d30']['bots'] ?? 0),
    'sections_today_visits' => $sectionsTodayVisits,
    'sections_today_uniques' => $sectionsTodayUniques,
    'sections_today_bots' => $sectionsTodayBots,
    'sections_30d_visits' => $sections30dVisits,
    'sections_30d_uniques' => $sections30dUniques,
    'sections_30d_bots' => $sections30dBots,
];

$logsPagination = [
    'page' => $logsPage,
    'per_page' => $logsPerPage,
    'total_rows' => $logsTotalRows,
    'total_pages' => $logsTotalPages,
    'page_sizes' => $logPageSizes,
];
?>
