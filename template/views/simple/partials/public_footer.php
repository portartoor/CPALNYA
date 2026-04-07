<?php
$host = function_exists('public_portal_host') ? public_portal_host() : 'cpalnya.local';
$isRu = function_exists('public_portal_lang') ? (public_portal_lang($host) === 'ru') : false;
$lang = $isRu ? 'ru' : 'en';
$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
$year = date('Y');

$opsLinks = [];
if (isset($FRMWRK) && function_exists('examples_popularity_fetch_top_clusters')) {
    foreach ((array)examples_popularity_fetch_top_clusters($FRMWRK, $host, $lang, 'journal', 3) as $row) {
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '') {
            continue;
        }
        $opsLinks[] = [
            'href' => function_exists('examples_cluster_list_path') ? examples_cluster_list_path($code, $host, 'journal') : '/journal/',
            'label' => (string)($row['label'] ?? $code),
        ];
    }
}
if (count($opsLinks) < 3) {
    $opsLinks = [
        ['href' => '/journal/', 'label' => $t('Источники', 'Sources')],
        ['href' => '/journal/', 'label' => $t('Фарм', 'Farm')],
        ['href' => '/journal/', 'label' => $t('Креативы', 'Creatives')],
    ];
}

$howToLinks = [];
if (isset($FRMWRK) && function_exists('examples_popularity_fetch_top_clusters')) {
    foreach ((array)examples_popularity_fetch_top_clusters($FRMWRK, $host, $lang, 'playbooks', 3) as $row) {
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '') {
            continue;
        }
        $howToLinks[] = [
            'href' => function_exists('examples_cluster_list_path') ? examples_cluster_list_path($code, $host, 'playbooks') : '/playbooks/',
            'label' => (string)($row['label'] ?? $code),
        ];
    }
}
if (count($howToLinks) < 3) {
    $howToLinks = [
        ['href' => '/playbooks/?topic=farm', 'label' => $t('Фарм-гайды', 'Farm Guides')],
        ['href' => '/playbooks/?topic=tracking', 'label' => $t('Трекинг', 'Tracking')],
        ['href' => '/playbooks/?topic=creatives', 'label' => $t('Креативы', 'Creatives')],
    ];
}

$footerSections = [
    [
        'title' => $t('Выпуск', 'Issue'),
        'links' => [
            ['href' => '/', 'label' => $t('Главная', 'Home')],
            ['href' => '/journal/', 'label' => $t('Журнал', 'Journal')],
            ['href' => '/playbooks/', 'label' => $t('Практика', 'Playbooks')],
        ],
    ],
    [
        'title' => $t('Операционка', 'Ops'),
        'links' => $opsLinks,
    ],
    [
        'title' => 'HowTo',
        'links' => array_merge(
            [['href' => '/playbooks/', 'label' => $t('Все HowTo', 'All HowTo')]],
            $howToLinks
        ),
    ],
    [
        'title' => $t('Связь', 'Reach'),
        'links' => [
            ['href' => '/contact/', 'label' => $t('Контакты', 'Contact')],
        ],
    ],
];
?>
<div class="public-layout-footer public-layout-footer--simple">
    <footer class="public-editorial-footer site-footer">
        <div class="public-editorial-footer-top">
            <div class="public-editorial-intro">
                <span class="public-editorial-kicker"><?= htmlspecialchars($t('CPALNYA / editorial footer', 'CPALNYA / editorial footer'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars($t('Журнал про арбитраж трафика, backstage affiliate-команд и практику, которая переживает рыночный шум.', 'A magazine about traffic arbitrage, affiliate-team backstage, and practical systems that survive market noise.'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('CPALNYA собран как редакционная площадка для тех, кто живет внутри операционки: между связками, креативными циклами, farm-ритмом, трекингом, модерацией и постоянной необходимостью принимать решения на ходу. Нас интересует не витринная версия рынка, а его реальная механика: как команды держат темп, где ломаются процессы и что на самом деле помогает возвращать контроль.', 'CPALNYA is built as an editorial place for people working inside the machine: between bundles, creative cycles, farm cadence, tracking, moderation, and constant operational decisions. We are interested not in the polished version of the market, but in its real mechanics: how teams keep tempo, where systems fail, and what actually restores control.'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($t('Здесь журнальные разборы соседствуют с практическими playbooks не случайно. Для нас это одна сцена: сначала мы фиксируем движение ниши, ее язык, ритм и скрытые сдвиги, а затем переводим это в конкретные сценарии, рабочие заметки, checklists и how-to-материалы, к которым можно возвращаться без ощущения, что читаешь архив ради архива.', 'Here, editorial breakdowns sit next to practical playbooks for a reason. For us, this is one stage: first we trace the movement of the niche, its language, tempo, and hidden shifts, and then translate that into concrete scenarios, working notes, checklists, and how-to materials you can return to without feeling you are reading an archive for the archive’s sake.'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars($t('Журнал нужен не для того, чтобы красиво сопровождать индустрию со стороны, а чтобы собирать ее живую память: удачные конструкции, операционные ошибки, дисциплину команды, цену решений и те сигналы, которые становятся заметны только тем, кто достаточно долго смотрит внутрь. В этом смысле CPALNYA — не лента публикаций, а редакционный инструмент ориентации в шумной affiliate-среде.', 'The magazine exists not to decorate the industry from a distance, but to collect its living memory: strong systems, operational mistakes, team discipline, the cost of decisions, and the signals that become visible only to those who look inside long enough. In that sense, CPALNYA is not just a feed of posts, but an editorial tool for navigating a noisy affiliate environment.'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="public-editorial-map">
                <?php foreach ($footerSections as $section): ?>
                    <section class="public-editorial-map-group">
                        <h4><?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <nav aria-label="<?= htmlspecialchars((string)$section['title'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ((array)$section['links'] as $link): ?>
                                <a href="<?= htmlspecialchars((string)$link['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$link['label'], ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endforeach; ?>
                        </nav>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="public-editorial-footer-bottom">
            <div class="public-editorial-copy">
                <?= htmlspecialchars((string)$year, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($host . ' / CPALNYA', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($t('редакционный журнал о CPA, affiliate-операциях, медиабаинге и backstage performance-команд', 'editorial journal about CPA, affiliate operations, media buying, and performance-team backstage'), ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </footer>
</div>
<button type="button" class="public-back-to-top" id="publicBackToTop" aria-label="<?= htmlspecialchars($t('Наверх', 'Back to top'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="public-back-to-top-icon" aria-hidden="true">↑</span>
    <span class="public-back-to-top-label"><?= htmlspecialchars($t('Наверх', 'Top'), ENT_QUOTES, 'UTF-8') ?></span>
</button>

<style>
.public-layout-footer{max-width:1480px;margin:28px auto 22px;padding:0 18px}
.public-editorial-footer{padding:24px;border:1px solid rgba(127,164,223,.22);border-radius:24px;background:rgba(8,14,28,.82);backdrop-filter:blur(10px)}
.public-editorial-footer-top{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:28px}
.public-editorial-kicker{display:inline-flex;align-items:center;padding:8px 12px;border:1px solid rgba(127,164,223,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#b8c7dc}
.public-editorial-intro{display:grid;gap:14px}
.public-editorial-intro h3{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;color:#edf3fb;letter-spacing:-.04em}
.public-editorial-intro p{margin:0;color:#9fb2ce;line-height:1.72}
.public-editorial-map{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.public-editorial-map-group{padding:14px;border:1px solid rgba(127,164,223,.16);background:rgba(255,255,255,.03)}
.public-editorial-map-group h4{margin:0 0 12px;font:700 1rem/1 "Space Grotesk","Sora",sans-serif;color:#edf3fb;letter-spacing:-.02em}
.public-editorial-map-group nav{display:grid;gap:8px}
.public-editorial-map-group a{color:#94aac8;text-decoration:none}
.public-editorial-map-group a:hover{color:#edf3fb}
.public-editorial-footer-bottom{margin-top:18px;padding-top:16px;border-top:1px solid rgba(127,164,223,.18)}
.public-editorial-copy{color:#94aac8;font-size:13px;letter-spacing:.08em;text-transform:uppercase}
.public-back-to-top{position:fixed;right:28px;bottom:28px;z-index:50;display:inline-flex;gap:8px;align-items:center;padding:10px 12px;border-radius:999px;border:1px solid rgba(127,164,223,.26);background:rgba(7,13,24,.84);color:#e7eff9;opacity:0;visibility:hidden;transform:translateY(10px);transition:all .2s ease;cursor:pointer}
.public-back-to-top.is-visible{opacity:1;visibility:visible;transform:none}
@media (max-width:980px){.public-editorial-footer-top,.public-editorial-map{grid-template-columns:1fr}}
</style>
<script>
(function(){
  var btn=document.getElementById('publicBackToTop');
  if(!btn)return;
  var sync=function(){ if(window.scrollY>420){btn.classList.add('is-visible');}else{btn.classList.remove('is-visible');}};
  window.addEventListener('scroll',sync,{passive:true}); sync();
  btn.addEventListener('click',function(){ window.scrollTo({top:0,behavior:'smooth'}); });
})();
</script>
