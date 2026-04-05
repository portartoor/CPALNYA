    <?php
    $publicLayoutSite = 'portfolio';
    $publicLayoutTheme = 'enterprise';
    $publicLayoutLang = (preg_match('/\.ru$/', (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')) === 1) ? 'ru' : 'en';
    include DIR . '/template/views/simple/partials/public_footer.php';
    include DIR . '/template/views/simple/partials/terrain_polygon_map.php';
    ?>
    </main>
</body>
</html>
