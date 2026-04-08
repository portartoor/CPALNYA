<?php
$host = function_exists('public_portal_host') ? public_portal_host() : 'cpalnya.local';
$isRu = function_exists('public_portal_lang') ? (public_portal_lang($host) === 'ru') : false;
$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
$year = date('Y');
$footerSeoBlock = null;
if (isset($FRMWRK) && is_object($FRMWRK) && method_exists($FRMWRK, 'DB') && function_exists('footer_seo_blocks_fetch_random')) {
    $db = $FRMWRK->DB();
    $footerSeoBlock = footer_seo_blocks_fetch_random($db, (string)$host, $isRu ? 'ru' : 'en', 'any');
}
$footerSeoEndpoint = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
if ($footerSeoEndpoint === '') {
    $footerSeoEndpoint = '/';
}
$footerSeoEndpoint .= (strpos($footerSeoEndpoint, '?') === false ? '?' : '&') . 'footer_seo_block=1';
$sectionTitles = [
    'journal' => $t('Журнал', 'Journal'),
    'playbooks' => $t('Практика', 'Playbooks'),
    'signals' => $t('Повестка', 'Signals'),
    'fun' => $t('Фан', 'Fun'),
];
$sectionIcons = [
    'home' => '⌂',
    'journal' => '✦',
    'playbooks' => '⚙',
    'signals' => '⌁',
    'fun' => '✺',
    'contact' => '✉',
];
$buildClusterLink = static function (array $cluster, string $section, string $fallbackIcon = '•'): array {
    $code = trim((string)($cluster['code'] ?? ''));
    $label = trim((string)($cluster['label'] ?? $code));
    if ($code === '' || $label === '') {
        return [];
    }
    $href = function_exists('examples_cluster_list_path')
        ? examples_cluster_list_path($code, null, $section)
        : '/' . trim($section, '/') . '/' . $code . '/';
    return [
        'href' => $href,
        'label' => $label,
        'icon' => $fallbackIcon,
    ];
};
$playbookClusters = function_exists('examples_fetch_clusters')
    ? array_values((array)examples_fetch_clusters($FRMWRK, (string)$host, $isRu ? 'ru' : 'en', 6, 'playbooks'))
    : [];
$journalClusters = function_exists('examples_fetch_clusters')
    ? array_values((array)examples_fetch_clusters($FRMWRK, (string)$host, $isRu ? 'ru' : 'en', 4, 'journal'))
    : [];
$signalsClusters = function_exists('examples_fetch_clusters')
    ? array_values((array)examples_fetch_clusters($FRMWRK, (string)$host, $isRu ? 'ru' : 'en', 3, 'signals'))
    : [];
$funClusters = function_exists('examples_fetch_clusters')
    ? array_values((array)examples_fetch_clusters($FRMWRK, (string)$host, $isRu ? 'ru' : 'en', 3, 'fun'))
    : [];
$practiceTopicLinks = [];
foreach (array_slice($playbookClusters, 0, 3) as $cluster) {
    $link = $buildClusterLink((array)$cluster, 'playbooks', '⚙');
    if ($link) {
        $practiceTopicLinks[] = $link;
    }
}
$otherTopicLinks = [];
foreach (array_slice($journalClusters, 0, 2) as $cluster) {
    $link = $buildClusterLink((array)$cluster, 'journal', '✦');
    if ($link) {
        $otherTopicLinks[] = $link;
    }
}
foreach (array_slice($signalsClusters, 0, 1) as $cluster) {
    $link = $buildClusterLink((array)$cluster, 'signals', '⌁');
    if ($link) {
        $otherTopicLinks[] = $link;
    }
}
foreach (array_slice($funClusters, 0, 1) as $cluster) {
    $link = $buildClusterLink((array)$cluster, 'fun', '✺');
    if ($link) {
        $otherTopicLinks[] = $link;
    }
}
$footerSections = [
    [
        'title' => $t('Выпуск', 'Issue'),
        'links' => [
            ['href' => '/', 'label' => $t('Главная', 'Home'), 'icon' => $sectionIcons['home']],
            ['href' => '/journal/', 'label' => $sectionTitles['journal'], 'icon' => $sectionIcons['journal']],
            ['href' => '/playbooks/', 'label' => $sectionTitles['playbooks'], 'icon' => $sectionIcons['playbooks']],
            ['href' => '/signals/', 'label' => $sectionTitles['signals'], 'icon' => $sectionIcons['signals']],
            ['href' => '/fun/', 'label' => $sectionTitles['fun'], 'icon' => $sectionIcons['fun']],
        ],
    ],
    [
        'title' => $t('Темы практики', 'Practice topics'),
        'links' => !empty($practiceTopicLinks) ? $practiceTopicLinks : [
            ['href' => '/playbooks/', 'label' => $sectionTitles['playbooks'], 'icon' => $sectionIcons['playbooks']],
        ],
    ],
    [
        'title' => $t('Остальные темы', 'Other topics'),
        'links' => !empty($otherTopicLinks) ? $otherTopicLinks : [
            ['href' => '/journal/', 'label' => $sectionTitles['journal'], 'icon' => $sectionIcons['journal']],
            ['href' => '/signals/', 'label' => $sectionTitles['signals'], 'icon' => $sectionIcons['signals']],
            ['href' => '/fun/', 'label' => $sectionTitles['fun'], 'icon' => $sectionIcons['fun']],
        ],
    ],
    [
        'title' => $t('Контакты', 'Contacts'),
        'links' => [
            ['href' => '/contact/', 'label' => $t('Контакты', 'Contact'), 'icon' => $sectionIcons['contact']],
        ],
    ],
];
?>
<div class="public-layout-footer public-layout-footer--simple">
    <div id="publicFooterSeoMount" data-endpoint="<?= htmlspecialchars($footerSeoEndpoint, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (is_array($footerSeoBlock) && function_exists('footer_seo_blocks_render_html')): ?>
            <?= footer_seo_blocks_render_html($footerSeoBlock, [], $isRu ? 'ru' : 'en') ?>
        <?php endif; ?>
    </div>
    <footer class="public-editorial-footer site-footer">
        <div class="public-editorial-footer-top">
            <div class="public-editorial-intro">
                <span class="public-editorial-kicker"><?= htmlspecialchars($t('ЦПАЛЬНЯ / editorial footer', 'ЦПАЛЬНЯ / editorial footer'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($t('Журнал про арбитраж трафика, backstage affiliate-команд и практику, которая переживает рыночный шум.', 'A magazine about traffic arbitrage, affiliate-team backstage, and practical systems that survive market noise.'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('ЦПАЛЬНЯ собрана как редакционная площадка для тех, кто живет внутри affiliate-операционки: между связками, креативными циклами, farm-ритмом, трекингом, модерацией и постоянной необходимостью принимать решения на ходу. Нас интересует не витрина рынка, а его реальная механика.', 'ЦПАЛЬНЯ is built as an editorial place for people working inside affiliate operations: between bundles, creative cycles, farm cadence, tracking, moderation, and constant operational decisions. We care less about the polished market facade than about its real mechanics.'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($t('Здесь журнальные разборы соседствуют с практическими playbooks, чтобы превращать шум ниши в понятные сценарии, рабочие заметки и материалы, к которым можно возвращаться. В этом смысле ЦПАЛЬНЯ не просто лента публикаций, а инструмент ориентации в шумной affiliate-среде.', 'Here, editorial breakdowns sit next to practical playbooks to turn niche noise into clear scenarios, working notes, and materials worth returning to. In that sense, ЦПАЛЬНЯ is not just a feed of posts, but a navigation tool for a noisy affiliate environment.'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="public-editorial-map">
                <?php foreach ($footerSections as $section): ?>
                    <section class="public-editorial-map-group">
                        <h4><?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <nav aria-label="<?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ((array)$section['links'] as $link): ?>
                                <a href="<?= htmlspecialchars((string)$link['href'], ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="public-editorial-link-icon" aria-hidden="true"><?= htmlspecialchars((string)($link['icon'] ?? '•'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span><?= htmlspecialchars((string)$link['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="public-editorial-footer-bottom">
            <div class="public-editorial-copy">
                <?= htmlspecialchars((string)$year, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($host . ' / ЦПАЛЬНЯ', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($t('редакционный журнал о CPA, affiliate-операциях, медиабаинге и backstage performance-команд', 'editorial journal about CPA, affiliate operations, media buying, and performance-team backstage'), ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </footer>
</div>
<button type="button" class="public-back-to-top" id="publicBackToTop" aria-label="<?= htmlspecialchars($t('Наверх', 'Back to top'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="public-back-to-top-icon" aria-hidden="true">&#10022;</span>
    <span class="public-back-to-top-label"><?= htmlspecialchars($t('Наверх', 'Top'), ENT_QUOTES, 'UTF-8') ?></span>
</button>

<style>
.public-layout-footer{max-width:1240px;margin:28px auto 22px;padding:0 18px}
.public-footer-seo-stack{display:grid;gap:14px}
.public-footer-seo-block{margin:0 0 18px;padding:24px 26px;border:1px solid rgba(127,164,223,.22);background:linear-gradient(180deg,rgba(8,14,28,.84),rgba(6,11,22,.76));box-shadow:var(--shell-shadow)}
.public-footer-seo-stack .public-footer-seo-block{margin:0}
.public-footer-seo-kicker{display:inline-flex;align-items:center;padding:8px 12px;margin-bottom:12px;border:1px solid rgba(127,164,223,.18);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#b8c7dc}
.public-footer-seo-block h3{margin:0 0 12px;font:700 1.7rem/1.02 "Space Grotesk","Sora",sans-serif;color:#edf3fb;letter-spacing:-.04em}
.public-footer-seo-body{display:grid;gap:12px;color:#aabbd2;line-height:1.74}
.public-footer-seo-body p{margin:0}
.public-footer-seo-block--mini-story{background:linear-gradient(180deg,rgba(10,15,27,.9),rgba(15,18,31,.78))}
.public-footer-seo-block--allegory{background:radial-gradient(circle at 18% 10%,rgba(244,213,107,.08),transparent 28%),linear-gradient(180deg,rgba(8,14,28,.88),rgba(7,11,20,.78))}
.public-footer-seo-block--memo{background:linear-gradient(180deg,rgba(7,12,24,.88),rgba(5,9,18,.8))}
.public-footer-seo-block--field-note{background:radial-gradient(circle at 82% 18%,rgba(120,223,255,.08),transparent 24%),linear-gradient(180deg,rgba(8,14,28,.84),rgba(6,11,22,.76))}
.public-editorial-footer{padding:24px;border:1px solid rgba(127,164,223,.22);border-radius:0;background:rgba(8,14,28,.82);backdrop-filter:blur(10px)}
.public-editorial-footer-top{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:28px}
.public-editorial-kicker{display:inline-flex;align-items:center;padding:8px 12px;border:1px solid rgba(127,164,223,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#b8c7dc}
.public-editorial-intro{display:grid;gap:14px}
.public-editorial-intro h3{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;color:#edf3fb;letter-spacing:-.04em}
.public-editorial-intro p{margin:0;color:#9fb2ce;line-height:1.72}
.public-editorial-map{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.public-editorial-map-group{padding:14px;border:1px solid rgba(127,164,223,.16);background:rgba(255,255,255,.03)}
.public-editorial-map-group h4{margin:0 0 12px;font:700 1rem/1 "Space Grotesk","Sora",sans-serif;color:#edf3fb;letter-spacing:-.02em}
.public-editorial-map-group nav{display:grid;gap:8px}
.public-editorial-map-group a{display:inline-flex;align-items:center;gap:8px;color:#94aac8;text-decoration:none}
.public-editorial-link-icon{display:inline-flex;align-items:center;justify-content:center;width:14px;min-width:14px;color:#f4d56b;font-size:12px;line-height:1}
.public-editorial-map-group a:hover{color:#edf3fb}
.public-editorial-footer-bottom{margin-top:18px;padding-top:16px;border-top:1px solid rgba(127,164,223,.18)}
.public-editorial-copy{color:#94aac8;font-size:13px;letter-spacing:.08em;text-transform:uppercase}
.public-back-to-top{position:fixed;right:28px;bottom:28px;z-index:1200;display:inline-flex;gap:8px;align-items:center;padding:10px 12px;border-radius:0;border:1px solid rgba(127,164,223,.26);background:rgba(7,13,24,.84);color:#e7eff9;opacity:0;visibility:hidden;transform:translateX(22px);transition:opacity .24s ease,visibility .24s ease,transform .28s cubic-bezier(.16,1,.3,1);cursor:pointer}
.public-back-to-top.is-visible{position:fixed;right:28px;bottom:28px;opacity:1;visibility:visible;transform:translateX(0)}
.public-back-to-top-icon{display:inline-flex;align-items:center;justify-content:center;width:14px;min-width:14px;font-size:14px;line-height:1;color:#f4d56b}
@media (max-width:980px){.public-editorial-footer-top,.public-editorial-map{grid-template-columns:1fr}}
</style>
<script>
(function(){
  var btn=document.getElementById('publicBackToTop');
  if(!btn)return;
  var header=document.querySelector('.simple-header') || document.querySelector('header');
  var getThreshold=function(){
    if(!header){return 140;}
    var rect=header.getBoundingClientRect();
    return Math.max(80, Math.round(rect.bottom + 24));
  };
  var sync=function(){
    var threshold=getThreshold();
    if(window.scrollY > threshold){
      btn.classList.add('is-visible');
    }else{
      btn.classList.remove('is-visible');
    }
  };
  window.addEventListener('scroll',sync,{passive:true});
  window.addEventListener('resize',sync,{passive:true});
  sync();
  btn.addEventListener('click',function(){ window.scrollTo({top:0,behavior:'smooth'}); });
})();
(function(){
  var mount=document.getElementById('publicFooterSeoMount');
  if(!mount){return;}
  var endpoint=String(mount.getAttribute('data-endpoint')||'').trim();
  if(!endpoint || typeof window.fetch!=='function'){return;}
  window.fetch(endpoint,{
    method:'GET',
    credentials:'same-origin',
    headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}
  }).then(function(res){
    if(!res.ok){return '';}
    return res.text();
  }).then(function(html){
    html=String(html||'').trim();
    if(html!==''){
      mount.innerHTML=html;
    }
  }).catch(function(){});
})();
</script>
