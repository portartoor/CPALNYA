<?
$section = $routes['routes'][1] ?? '';
$subroute = $routes['routes'][2] ?? '';
if ((($section=='dashboard') && ($subroute=='auth')) || (($section=='adminpanel') && ($subroute=='auth'))) {
    include (DIR.'/template/views/dashboard_auth/footer.php');
}
else {
$dashboardNotifyLib = DIR . '/core/libs/dashboard_notifications.php';
if (file_exists($dashboardNotifyLib)) {
    require_once $dashboardNotifyLib;
}
$supportUrl = function_exists('dashboard_support_link') ? dashboard_support_link() : 'https://t.me/apigeoip';
$brand = function_exists('dashboard_branding') ? dashboard_branding() : ['title' => 'CODERS'];
?>

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 text-center text-md-start">
                                <script>document.write(new Date().getFullYear())</script>
                                &copy; <span class="fw-bold text-decoration-underline text-uppercase text-reset fs-12"><?= htmlspecialchars((string)$brand['title']) ?></span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-none d-md-flex justify-content-end gap-3">
                                    <a href="<?= htmlspecialchars((string)$supportUrl) ?>" target="_blank" rel="noopener" class="link-reset">Support</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>

            </div>

        </div>
        <script src="/template/views/dashboard/assets/js/vendors.min.js"></script>
        <script src="/template/views/dashboard/assets/js/app.js"></script>
        <script src="/template/views/dashboard/assets/plugins/apexcharts/apexcharts.min.js"></script>
        <script src="/template/views/dashboard/assets/plugins/jsvectormap/jsvectormap.min.js"></script>
        <script src="/template/views/dashboard/assets/js/maps/world-merc.js"></script>
        <script src="/template/views/dashboard/assets/js/maps/world.js"></script>
        <script src="/template/views/dashboard/assets/js/pages/custom-table.js"></script>
<?php if (($section === 'dashboard') && ($subroute === '' || $subroute === 'main')): ?>
        <script src="/template/views/dashboard/assets/js/pages/dashboard-ecommerce.js"></script>
<?php endif; ?>
    </body>
</html>
<?
}
?>
