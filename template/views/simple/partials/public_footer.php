<?php
$host = function_exists('public_portal_host') ? public_portal_host() : 'cpalnya.local';
$isRu = function_exists('public_portal_lang') ? (public_portal_lang($host) === 'ru') : false;
$t = static function (string $ru, string $en) use ($isRu): string {
    return $isRu ? $ru : $en;
};
$year = date('Y');
$nav = [
    ['href' => '/', 'label' => $t('Главная', 'Home')],
    ['href' => '/blog/', 'label' => $t('Блог', 'Blog')],
    ['href' => '/solutions/downloads/', 'label' => $t('Решения', 'Solutions')],
    ['href' => '/projects/', 'label' => $t('Продукты', 'Products')],
    ['href' => '/cases/', 'label' => $t('Кейсы', 'Cases')],
    ['href' => '/contact/', 'label' => $t('Контакты', 'Contact')],
];
?>
<div class="public-layout-footer public-layout-footer--simple">
    <div id="contact-form" class="public-contact-wrap">
        <div class="public-contact-shell">
            <div class="public-contact-intro">
                <h3><?= htmlspecialchars($t('Следующий шаг', 'Next step'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($t('Если нужен отдельный раздел, партнерская витрина, SEO-кластер или продуктовая воронка под ваш affiliate-проект, используйте контактную форму. CPALNYA задуман как портал, который можно масштабировать и в медиа, и в B2B-инструментарий.', 'If you need a dedicated section, partner showcase, SEO cluster or product funnel for your affiliate project, use the contact form. CPALNYA is designed as a portal that can scale both as media and as a B2B tooling layer.'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <form method="post" action="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>" class="public-contact-form">
                <input type="hidden" name="action" value="public_contact_submit">
                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="contact_form_anchor" value="#contact-form">
                <input type="hidden" name="contact_interest" value="cpalnya">
                <input type="hidden" name="contact_csrf" value="<?= htmlspecialchars(function_exists('public_contact_form_token') ? public_contact_form_token() : '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="contact_started_at" value="<?= time() ?>">
                <input type="text" name="contact_company" value="" autocomplete="new-password" tabindex="-1" class="contact-hp" aria-hidden="true">
                <input type="text" name="contact_name" placeholder="<?= htmlspecialchars($t('Имя', 'Name'), ENT_QUOTES, 'UTF-8') ?>" required>
                <input type="text" name="contact_campaign" placeholder="<?= htmlspecialchars($t('Проект или бренд', 'Project or brand'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="email" name="contact_email" placeholder="Email" required>
                <textarea name="contact_message" placeholder="<?= htmlspecialchars($t('Что нужно построить: портал, витрину, хаб, комьюнити или продуктовый раздел', 'What needs to be built: portal, showcase, hub, community or product section'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                <button type="submit"><?= htmlspecialchars($t('Отправить запрос', 'Send request'), ENT_QUOTES, 'UTF-8') ?></button>
            </form>
        </div>
    </div>

    <footer class="public-site-footer site-footer">
        <div class="public-site-footer-copy">&copy; <?= htmlspecialchars((string)$year, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($host . ' / CPALNYA', ENT_QUOTES, 'UTF-8') ?>. <?= htmlspecialchars($t('CPA, affiliate, media buying, SEO и закулисье performance-рынка.', 'CPA, affiliate, media buying, SEO and the backstage of performance marketing.'), ENT_QUOTES, 'UTF-8') ?></div>
        <nav class="public-site-footer-nav" aria-label="Footer navigation">
            <?php foreach ($nav as $item): ?>
                <a href="<?= htmlspecialchars((string)$item['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$item['label'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </nav>
    </footer>
</div>
<button type="button" class="public-back-to-top" id="publicBackToTop" aria-label="<?= htmlspecialchars($t('Наверх', 'Back to top'), ENT_QUOTES, 'UTF-8') ?>">
    <span class="public-back-to-top-icon" aria-hidden="true">↑</span>
    <span class="public-back-to-top-label"><?= htmlspecialchars($t('Наверх', 'Top'), ENT_QUOTES, 'UTF-8') ?></span>
</button>

<style>
.public-layout-footer{max-width:1240px;margin:28px auto 22px;padding:0 16px}
.public-contact-wrap{padding:20px;border:1px solid rgba(127,164,223,.22);border-radius:22px;background:rgba(8,14,28,.82);backdrop-filter:blur(10px)}
.public-contact-shell{display:grid;grid-template-columns:minmax(260px,.42fr) minmax(0,.58fr);gap:18px;align-items:start}
.public-contact-intro h3{margin:0 0 12px;font:700 28px/1 "Space Grotesk","IBM Plex Sans",sans-serif;color:#edf3fb}
.public-contact-intro p{margin:0;color:#9fb2ce;line-height:1.65}
.public-contact-form{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.public-contact-form input,.public-contact-form textarea{width:100%;box-sizing:border-box;padding:12px 14px;border-radius:12px;border:1px solid rgba(132,159,198,.25);background:rgba(4,8,18,.72);color:#eef5ff;font:inherit}
.public-contact-form textarea{grid-column:1/-1;min-height:120px;resize:vertical}
.public-contact-form button{grid-column:1/-1;border:0;border-radius:12px;padding:12px 14px;background:linear-gradient(135deg,#78a6ff,#17b6a6);color:#07111f;font-weight:700;cursor:pointer}
.public-site-footer{margin-top:14px;padding-top:14px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;border-top:1px solid rgba(127,164,223,.18)}
.public-site-footer-copy,.public-site-footer-nav a{color:#94aac8}
.public-site-footer-nav{display:flex;gap:14px;flex-wrap:wrap}
.public-site-footer-nav a{text-decoration:none}
.public-back-to-top{position:fixed;right:28px;bottom:28px;z-index:50;display:inline-flex;gap:8px;align-items:center;padding:10px 12px;border-radius:999px;border:1px solid rgba(127,164,223,.26);background:rgba(7,13,24,.84);color:#e7eff9;opacity:0;visibility:hidden;transform:translateY(10px);transition:all .2s ease;cursor:pointer}
.public-back-to-top.is-visible{opacity:1;visibility:visible;transform:none}
.contact-hp{position:absolute!important;left:-9999px!important}
@media (max-width:980px){.public-contact-shell,.public-contact-form{grid-template-columns:1fr}}
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
