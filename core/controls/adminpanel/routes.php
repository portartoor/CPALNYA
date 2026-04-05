<?php
session_start();

$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
$adminpanelUser = null;

require_once __DIR__ . '/_common.php';
$adminpanelUser = adminpanel_require_auth($FRMWRK);

$mirrorDomainsLib = DIR . '/core/libs/mirror_domains.php';
if (file_exists($mirrorDomainsLib)) {
    require_once $mirrorDomainsLib;
}
$mirrorRoutesLib = DIR . '/core/libs/mirror_routes.php';
if (file_exists($mirrorRoutesLib)) {
    require_once $mirrorRoutesLib;
}

if (function_exists('mirror_domains_ensure_schema')) {
    mirror_domains_ensure_schema($FRMWRK);
}
if (function_exists('mirror_routes_ensure_schema')) {
    mirror_routes_ensure_schema($FRMWRK);
}

$message = '';
$messageType = 'info';

function admin_routes_str(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function admin_routes_int(string $key, int $default = 0): int
{
    return (int)($_POST[$key] ?? $default);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_route') {
        $routeId = admin_routes_int('route_id', 0);
        $routeType = strtolower(admin_routes_str('route_type', 'page'));
        $routeName = function_exists('mirror_routes_slug') ? mirror_routes_slug(admin_routes_str('route_name')) : '';
        $pageName = function_exists('mirror_routes_slug') ? mirror_routes_slug(admin_routes_str('page_name')) : '';
        $displayName = admin_routes_str('display_name');
        $viewName = function_exists('mirror_routes_slug') ? mirror_routes_slug(admin_routes_str('view_name', 'main')) : 'main';
        $sortOrder = admin_routes_int('sort_order', 100);
        $isActive = admin_routes_int('is_active', 1) === 1 ? 1 : 0;
        $seoNoindex = admin_routes_int('seo_noindex', 0) === 1 ? 1 : 0;
        $shouldScaffold = admin_routes_int('create_scaffold', 1) === 1;

        if (!in_array($routeType, ['page', 'section_page'], true)) {
            $message = 'Invalid route type.';
            $messageType = 'danger';
        } elseif ($routeName === '') {
            $message = 'Route name is required.';
            $messageType = 'danger';
        } elseif (in_array($routeName, function_exists('mirror_routes_reserved_roots') ? mirror_routes_reserved_roots() : [], true)) {
            $message = 'Route name is reserved by system.';
            $messageType = 'danger';
        } else {
            if ($routeType === 'page') {
                $pageName = '';
            } elseif ($pageName === '') {
                $pageName = 'main';
            }
            if ($viewName === '') {
                $viewName = 'main';
            }
            if ($displayName === '') {
                $displayName = function_exists('mirror_routes_humanize')
                    ? mirror_routes_humanize($routeType === 'section_page' ? ($routeName . ' ' . $pageName) : $routeName)
                    : $routeName;
            }

            $typeSafe = mysqli_real_escape_string($DB, $routeType);
            $routeSafe = mysqli_real_escape_string($DB, $routeName);
            $pageSafe = mysqli_real_escape_string($DB, $pageName);
            $displaySafe = mysqli_real_escape_string($DB, $displayName);
            $viewSafe = mysqli_real_escape_string($DB, $viewName);
            $titleSafe = mysqli_real_escape_string($DB, admin_routes_str('seo_title'));
            $descSafe = mysqli_real_escape_string($DB, admin_routes_str('seo_description'));
            $keywordsSafe = mysqli_real_escape_string($DB, admin_routes_str('seo_keywords'));
            $ogTitleSafe = mysqli_real_escape_string($DB, admin_routes_str('og_title'));
            $ogDescSafe = mysqli_real_escape_string($DB, admin_routes_str('og_description'));
            $ogImageSafe = mysqli_real_escape_string($DB, admin_routes_str('og_image'));

            if ($routeId > 0) {
                mysqli_query(
                    $DB,
                    "UPDATE mirror_routes
                     SET route_type='{$typeSafe}',
                         route_name='{$routeSafe}',
                         page_name='{$pageSafe}',
                         display_name='{$displaySafe}',
                         view_name='{$viewSafe}',
                         sort_order={$sortOrder},
                         is_active={$isActive},
                         seo_title='{$titleSafe}',
                         seo_description='{$descSafe}',
                         seo_keywords='{$keywordsSafe}',
                         og_title='{$ogTitleSafe}',
                         og_description='{$ogDescSafe}',
                         og_image='{$ogImageSafe}',
                         seo_noindex={$seoNoindex},
                         updated_at=NOW()
                     WHERE id={$routeId}
                     LIMIT 1"
                );
                if (mysqli_errno($DB) === 1062) {
                    $message = 'Route already exists.';
                    $messageType = 'warning';
                } else {
                    $message = 'Route updated.';
                    $messageType = 'success';
                }
            } else {
                mysqli_query(
                    $DB,
                    "INSERT INTO mirror_routes
                        (route_type, route_name, page_name, display_name, view_name, sort_order, is_active, seo_title, seo_description, seo_keywords, og_title, og_description, og_image, seo_noindex, created_at, updated_at)
                     VALUES
                        ('{$typeSafe}', '{$routeSafe}', '{$pageSafe}', '{$displaySafe}', '{$viewSafe}', {$sortOrder}, {$isActive}, '{$titleSafe}', '{$descSafe}', '{$keywordsSafe}', '{$ogTitleSafe}', '{$ogDescSafe}', '{$ogImageSafe}', {$seoNoindex}, NOW(), NOW())"
                );
                if (mysqli_errno($DB) === 1062) {
                    $message = 'Route already exists.';
                    $messageType = 'warning';
                } else {
                    $routeId = (int)mysqli_insert_id($DB);
                    $message = 'Route created.';
                    $messageType = 'success';
                }
            }

            if ($routeId > 0 && $shouldScaffold && function_exists('mirror_routes_scaffold')) {
                $row = $FRMWRK->DBRecords("SELECT * FROM mirror_routes WHERE id={$routeId} LIMIT 1");
                if (!empty($row)) {
                    $scaffold = mirror_routes_scaffold($row[0], ['simple', 'enterprise']);
                    $createdCount = count((array)($scaffold['created'] ?? []));
                    $errorsCount = count((array)($scaffold['errors'] ?? []));
                    $message .= ' Scaffold: created ' . $createdCount . ' file(s).';
                    if ($errorsCount > 0) {
                        $message .= ' Errors: ' . implode(' | ', array_slice((array)$scaffold['errors'], 0, 3));
                        $messageType = 'warning';
                    }
                }
            }
        }
    } elseif ($action === 'delete_route') {
        $routeId = admin_routes_int('route_id', 0);
        if ($routeId > 0) {
            mysqli_query($DB, "DELETE FROM mirror_routes WHERE id={$routeId} LIMIT 1");
            $message = 'Route deleted.';
            $messageType = 'success';
        }
    } elseif ($action === 'scaffold_route') {
        $routeId = admin_routes_int('route_id', 0);
        $row = $FRMWRK->DBRecords("SELECT * FROM mirror_routes WHERE id={$routeId} LIMIT 1");
        if (!empty($row) && function_exists('mirror_routes_scaffold')) {
            $scaffold = mirror_routes_scaffold($row[0], ['simple', 'enterprise']);
            $message = 'Scaffold completed: created ' . count((array)($scaffold['created'] ?? [])) . ' file(s), existing ' . count((array)($scaffold['existing'] ?? [])) . '.';
            if (!empty($scaffold['errors'])) {
                $message .= ' Errors: ' . implode(' | ', array_slice((array)$scaffold['errors'], 0, 3));
                $messageType = 'warning';
            } else {
                $messageType = 'success';
            }
        }
    }
}

$editId = (int)($_GET['edit_id'] ?? 0);
$editRoute = null;
if ($editId > 0) {
    $editRows = $FRMWRK->DBRecords("SELECT * FROM mirror_routes WHERE id={$editId} LIMIT 1");
    if (!empty($editRows)) {
        $editRoute = $editRows[0];
    }
}

$routesRows = $FRMWRK->DBRecords(
    "SELECT *
     FROM mirror_routes
     ORDER BY route_name ASC, page_name ASC, sort_order ASC, id ASC"
);

