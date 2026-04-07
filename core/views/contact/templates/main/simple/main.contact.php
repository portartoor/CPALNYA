<?php
$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
if (strpos($host, ':') !== false) {
    $host = explode(':', $host, 2)[0];
}
$isRu = (bool)preg_match('/\.ru$/', $host);

$title = $isRu ? 'Связь' : 'Contact';
$lead = $isRu
    ? 'Опишите задачу, идею или запрос на сотрудничество. Форма ниже отправит заявку в тот же поток обработки, что и раньше.'
    : 'Describe your task, idea, or collaboration request. The form below sends it into the same request pipeline as before.';
$contactToken = function_exists('public_contact_form_token') ? public_contact_form_token() : '';
$contactFlash = function_exists('public_contact_form_flash') ? public_contact_form_flash() : [];
$contactType = (string)($contactFlash['type'] ?? '');
$contactMsg = (string)($contactFlash['message'] ?? '');
$returnPath = '/contact/';
$turnstileSiteKey = trim((string)($GLOBALS['ContactTurnstileSiteKey'] ?? ''));
$contactOld = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    $contactOld = is_array($_SESSION['contact_form_old'] ?? null) ? $_SESSION['contact_form_old'] : [];
}
$old = static function (string $key, string $fallback = '') use ($contactOld): string {
    return trim((string)($contactOld[$key] ?? $fallback));
};
?>
<style>
.contact-basic{max-width:1180px;box-sizing:border-box;margin:0 auto;padding:24px 18px 48px;color:var(--shell-text)}
.contact-basic-shell{display:grid;gap:18px}
.contact-basic-hero,.contact-basic-form-wrap{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow);border-radius:0}
.contact-basic-hero,.contact-basic-form-wrap{padding:24px}
.contact-basic-hero h1{margin:0 0 10px;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.04em}
.contact-basic-hero p{margin:0;max-width:72ch;color:var(--shell-muted);line-height:1.72}
.contact-basic-alert{padding:12px 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-text);border-radius:0}
.contact-basic-alert.ok{border-color:rgba(60,210,140,.28);background:rgba(60,210,140,.08)}
.contact-basic-alert.error{border-color:rgba(255,120,120,.24);background:rgba(255,120,120,.08)}
.contact-basic-form{display:grid;gap:14px}
.contact-basic-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.contact-basic-field{display:grid;gap:6px}
.contact-basic-field label{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--shell-muted)}
.contact-basic-field input,.contact-basic-field textarea{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.56);color:var(--shell-text);font:inherit;border-radius:0}
.contact-basic-field textarea{min-height:180px;resize:vertical}
.contact-basic-field input::placeholder,.contact-basic-field textarea::placeholder{color:rgba(190,208,230,.52)}
.contact-basic-submit{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.22),rgba(39,223,192,.18));color:var(--shell-text);text-decoration:none;font-weight:700;font-size:13px;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;border-radius:0}
.contact-hp{position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important}
@media (max-width:780px){
    .contact-basic{padding:18px 14px 42px}
    .contact-basic-grid{grid-template-columns:1fr}
}
</style>

<section class="contact-basic">
    <div class="contact-basic-shell">
        <header class="contact-basic-hero">
            <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') ?></p>
        </header>

        <div class="contact-basic-form-wrap" id="contact-form">
            <?php if ($contactMsg !== ''): ?>
                <div class="contact-basic-alert <?= $contactType === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($contactMsg, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form class="contact-basic-form" method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="public_contact_submit">
                <input type="hidden" name="return_path" value="<?= htmlspecialchars($returnPath, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="contact_form_anchor" value="#contact-form">
                <input type="hidden" name="contact_interest" value="general">
                <input type="hidden" name="contact_csrf" value="<?= htmlspecialchars($contactToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="contact_started_at" value="<?= time() ?>">
                <input type="hidden" name="contact_campaign" value="contact:page">
                <input type="text" name="contact_company" value="" autocomplete="off" tabindex="-1" class="contact-hp" aria-hidden="true">

                <div class="contact-basic-grid">
                    <div class="contact-basic-field">
                        <label for="contact_name"><?= htmlspecialchars($isRu ? 'Имя' : 'Name', ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="contact_name" type="text" name="contact_name" value="<?= htmlspecialchars($old('contact_name'), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="contact-basic-field">
                        <label for="contact_email">Email</label>
                        <input id="contact_email" type="email" name="contact_email" value="<?= htmlspecialchars($old('contact_email'), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="contact-basic-field">
                        <label for="contact_campaign_hint"><?= htmlspecialchars($isRu ? 'Компания / проект' : 'Company / project', ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="contact_campaign_hint" type="text" name="contact_campaign_hint" value="<?= htmlspecialchars($old('contact_campaign_hint'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="contact-basic-field">
                        <label for="contact_subject"><?= htmlspecialchars($isRu ? 'Тема' : 'Subject', ENT_QUOTES, 'UTF-8') ?></label>
                        <input id="contact_subject" type="text" name="contact_subject" value="<?= htmlspecialchars($old('contact_subject'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="contact-basic-field">
                    <label for="contact_message"><?= htmlspecialchars($isRu ? 'Сообщение' : 'Message', ENT_QUOTES, 'UTF-8') ?></label>
                    <textarea id="contact_message" name="contact_message" required><?= htmlspecialchars($old('contact_message'), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="contact-basic-field">
                    <label for="contact_files"><?= htmlspecialchars($isRu ? 'Файлы' : 'Files', ENT_QUOTES, 'UTF-8') ?></label>
                    <input id="contact_files" type="file" name="contact_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ods,.txt">
                </div>

                <?php if ($turnstileSiteKey !== ''): ?>
                    <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
                <?php endif; ?>

                <div>
                    <button type="submit" class="contact-basic-submit"><?= htmlspecialchars($isRu ? 'Отправить заявку' : 'Send request', ENT_QUOTES, 'UTF-8') ?></button>
                </div>
            </form>
        </div>
    </div>
</section>
