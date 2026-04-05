<?php

if (!function_exists('public_contact_form_include_support_libs')) {
    function public_contact_form_include_support_libs(): void
    {
        if (!defined('DIR')) {
            return;
        }
        $adminNotificationsLib = rtrim((string)DIR, '/\\') . '/core/libs/admin_notifications.php';
        if (is_file($adminNotificationsLib)) {
            require_once $adminNotificationsLib;
        }
        $telegramNotifyLib = rtrim((string)DIR, '/\\') . '/core/libs/telegram_notify.php';
        if (is_file($telegramNotifyLib)) {
            require_once $telegramNotifyLib;
        }
    }
}

if (!function_exists('public_contact_form_table_ensure')) {
    function public_contact_form_table_ensure(mysqli $db): void
    {
        mysqli_query($db, "
            CREATE TABLE IF NOT EXISTS contact_requests (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(190) NOT NULL,
                campaign VARCHAR(190) NOT NULL DEFAULT '',
                subject VARCHAR(190) NOT NULL DEFAULT '',
                email VARCHAR(190) NOT NULL,
                message TEXT NOT NULL,
                attachments_json MEDIUMTEXT NULL,
                source_page VARCHAR(255) NOT NULL DEFAULT '/',
                host VARCHAR(190) NOT NULL DEFAULT '',
                ip VARCHAR(64) NOT NULL DEFAULT '',
                user_agent VARCHAR(500) NOT NULL DEFAULT '',
                status VARCHAR(24) NOT NULL DEFAULT 'new',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_contact_status (status),
                KEY idx_contact_created (created_at),
                KEY idx_contact_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        if (function_exists('public_contact_form_ensure_column')) {
            public_contact_form_ensure_column($db, 'contact_requests', 'subject', "VARCHAR(190) NOT NULL DEFAULT '' AFTER campaign");
            public_contact_form_ensure_column($db, 'contact_requests', 'attachments_json', "MEDIUMTEXT NULL AFTER message");
        }
    }
}

if (!function_exists('public_contact_form_ensure_column')) {
    function public_contact_form_ensure_column(mysqli $db, string $table, string $column, string $definition): void
    {
        $tableSafe = mysqli_real_escape_string($db, $table);
        $columnSafe = mysqli_real_escape_string($db, $column);
        $existsRes = mysqli_query($db, "SHOW COLUMNS FROM `{$tableSafe}` LIKE '{$columnSafe}'");
        $exists = ($existsRes instanceof mysqli_result) && ($existsRes->num_rows > 0);
        if ($existsRes instanceof mysqli_result) {
            $existsRes->free();
        }
        if ($exists) {
            return;
        }
        mysqli_query($db, "ALTER TABLE `{$tableSafe}` ADD COLUMN `{$columnSafe}` {$definition}");
    }
}

if (!function_exists('public_contact_form_files_from_upload')) {
    function public_contact_form_files_from_upload(array $fileField): array
    {
        $out = [];
        $names = $fileField['name'] ?? null;
        $tmps = $fileField['tmp_name'] ?? null;
        $sizes = $fileField['size'] ?? null;
        $errs = $fileField['error'] ?? null;
        $types = $fileField['type'] ?? null;

        if (is_array($names)) {
            $count = count($names);
            for ($i = 0; $i < $count; $i++) {
                $out[] = [
                    'name' => (string)($names[$i] ?? ''),
                    'tmp_name' => (string)($tmps[$i] ?? ''),
                    'size' => (int)($sizes[$i] ?? 0),
                    'error' => (int)($errs[$i] ?? UPLOAD_ERR_NO_FILE),
                    'type' => (string)($types[$i] ?? ''),
                ];
            }
            return $out;
        }

        $out[] = [
            'name' => (string)($fileField['name'] ?? ''),
            'tmp_name' => (string)($fileField['tmp_name'] ?? ''),
            'size' => (int)($fileField['size'] ?? 0),
            'error' => (int)($fileField['error'] ?? UPLOAD_ERR_NO_FILE),
            'type' => (string)($fileField['type'] ?? ''),
        ];
        return $out;
    }
}

if (!function_exists('public_contact_form_handle_uploads')) {
    function public_contact_form_handle_uploads(string $host, array &$errors): array
    {
        if (empty($_FILES['contact_files']) || !is_array($_FILES['contact_files'])) {
            return [];
        }
        if (!defined('DIR')) {
            $errors[] = 'Upload storage is not configured.';
            return [];
        }
        $files = public_contact_form_files_from_upload($_FILES['contact_files']);
        if (count($files) > 5) {
            $errors[] = 'Too many files. Maximum is 5.';
            return [];
        }

        $allowedExt = [
            'pdf' => true,
            'doc' => true,
            'docx' => true,
            'xls' => true,
            'xlsx' => true,
            'csv' => true,
            'ods' => true,
        ];

        $totalBytes = 0;
        $saved = [];
        $ym = date('Y/m');
        $storageDir = rtrim((string)DIR, '/\\') . '/uploads/contact_requests/' . $ym;
        $publicPrefix = '/uploads/contact_requests/' . $ym . '/';
        if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            $errors[] = 'Cannot create upload directory.';
            return [];
        }

        foreach ($files as $f) {
            $err = (int)($f['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($err !== UPLOAD_ERR_OK) {
                $errors[] = 'File upload failed.';
                continue;
            }
            $origName = trim((string)($f['name'] ?? ''));
            $tmpName = (string)($f['tmp_name'] ?? '');
            $size = (int)($f['size'] ?? 0);
            if ($origName === '' || $tmpName === '' || $size <= 0) {
                $errors[] = 'Invalid uploaded file.';
                continue;
            }
            if ($size > 8 * 1024 * 1024) {
                $errors[] = 'Each file must be <= 8MB.';
                continue;
            }
            $totalBytes += $size;
            if ($totalBytes > 20 * 1024 * 1024) {
                $errors[] = 'Total upload size must be <= 20MB.';
                break;
            }
            $ext = strtolower((string)pathinfo($origName, PATHINFO_EXTENSION));
            if ($ext === '' || !isset($allowedExt[$ext])) {
                $errors[] = 'Only PDF, DOC, DOCX, XLS, XLSX, CSV, ODS files are allowed.';
                continue;
            }
            $base = (string)pathinfo($origName, PATHINFO_FILENAME);
            $base = preg_replace('/[^A-Za-z0-9\-_\.]+/', '_', $base);
            $base = trim((string)$base, '._-');
            if ($base === '') {
                $base = 'file';
            }
            if (function_exists('mb_substr')) {
                $base = mb_substr($base, 0, 60, 'UTF-8');
            } else {
                $base = substr($base, 0, 60);
            }
            try {
                $rnd = bin2hex(random_bytes(5));
            } catch (Throwable $e) {
                $rnd = substr(md5((string)mt_rand()), 0, 10);
            }
            $finalName = date('His') . '_' . $rnd . '_' . $base . '.' . $ext;
            $target = $storageDir . '/' . $finalName;
            if (!@move_uploaded_file($tmpName, $target)) {
                $errors[] = 'Unable to save uploaded file.';
                continue;
            }
            $saved[] = [
                'name' => $origName,
                'size' => $size,
                'path' => $publicPrefix . $finalName,
                'mime' => (string)($f['type'] ?? ''),
                'host' => $host,
            ];
        }

        return $saved;
    }
}

if (!function_exists('public_contact_form_token')) {
    function public_contact_form_csrf_secret(): string
    {
        $candidate = trim((string)($GLOBALS['TelegramActionSecret'] ?? ''));
        if ($candidate !== '') {
            return $candidate;
        }
        return 'public_contact_form_default_secret';
    }
}

if (!function_exists('public_contact_form_csrf_token_make')) {
    function public_contact_form_csrf_token_make(int $nowTs = 0): string
    {
        $nowTs = $nowTs > 0 ? $nowTs : time();
        try {
            $nonce = bin2hex(random_bytes(8));
        } catch (Throwable $e) {
            $nonce = bin2hex(pack('N', mt_rand())) . bin2hex(pack('N', mt_rand()));
        }
        $payload = $nowTs . '.' . $nonce;
        $sig = hash_hmac('sha256', $payload, public_contact_form_csrf_secret());
        return $payload . '.' . $sig;
    }
}

if (!function_exists('public_contact_form_csrf_token_verify')) {
    function public_contact_form_csrf_token_verify(string $token, int $nowTs = 0): bool
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        $tsRaw = trim((string)$parts[0]);
        $nonce = trim((string)$parts[1]);
        $sig = trim((string)$parts[2]);
        if (!ctype_digit($tsRaw) || $nonce === '' || $sig === '') {
            return false;
        }
        $ts = (int)$tsRaw;
        $nowTs = $nowTs > 0 ? $nowTs : time();
        // token lifetime: 4 hours, and allow 5 min clock skew
        if ($ts > ($nowTs + 300) || ($nowTs - $ts) > 14400) {
            return false;
        }
        $payload = $tsRaw . '.' . $nonce;
        $expected = hash_hmac('sha256', $payload, public_contact_form_csrf_secret());
        return hash_equals($expected, $sig);
    }
}

if (!function_exists('public_contact_form_token')) {
    function public_contact_form_token(): string
    {
        return public_contact_form_csrf_token_make(time());
    }
}

if (!function_exists('public_contact_form_flash')) {
    function public_contact_form_flash(): array
    {
        if (array_key_exists('public_contact_form_flash_cached', $GLOBALS) && is_array($GLOBALS['public_contact_form_flash_cached'])) {
            return $GLOBALS['public_contact_form_flash_cached'];
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $sessionKey = session_name();
            if ($sessionKey === '' || empty($_COOKIE[$sessionKey])) {
                $GLOBALS['public_contact_form_flash_cached'] = [];
                return [];
            }
            if (function_exists('session_cache_limiter')) {
                @session_cache_limiter('');
            }
            session_start();
        }
        $flash = $_SESSION['contact_form_flash'] ?? [];
        unset($_SESSION['contact_form_flash']);
        $GLOBALS['public_contact_form_flash_cached'] = is_array($flash) ? $flash : [];
        return $GLOBALS['public_contact_form_flash_cached'];
    }
}

if (!function_exists('public_contact_form_handle_request')) {
    function public_contact_form_handle_request($FRMWRK): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }
        if ((string)($_POST['action'] ?? '') !== 'public_contact_submit') {
            return;
        }
        if (!is_object($FRMWRK) || !method_exists($FRMWRK, 'DB')) {
            return;
        }

        $requestPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/');
        $skipRoutes = ['/dashboard/', '/adminpanel/', '/api/', '/debug/'];
        foreach ($skipRoutes as $skip) {
            if (strpos($requestPath, $skip) === 0) {
                return;
            }
        }

        $returnPath = trim((string)($_POST['return_path'] ?? '/'));
        if ($returnPath === '' || $returnPath[0] !== '/') {
            $returnPath = '/';
        }
        if (strpos($returnPath, '//') === 0) {
            $returnPath = '/';
        }
        $returnPath = str_replace(["\r", "\n"], '', $returnPath);
        $redirectBase = (string)preg_replace('/#.*$/', '', $returnPath);
        if ($redirectBase === '') {
            $redirectBase = '/';
        }
        $redirect = $returnPath;
        $formAnchor = trim((string)($_POST['contact_form_anchor'] ?? ''));
        if ($formAnchor !== '' && preg_match('/^#[A-Za-z][A-Za-z0-9\-\_\:\.]*$/', $formAnchor)) {
            $redirect = $redirectBase . $formAnchor;
        } elseif (strpos($redirect, '#') === false) {
            $redirect = $redirectBase . '#contact-form';
        }
        $redirectWithStatus = (static function (string $url): string {
            $parts = parse_url($url);
            $path = (string)($parts['path'] ?? '/');
            $query = [];
            if (!empty($parts['query'])) {
                parse_str((string)$parts['query'], $query);
            }
            $query['contact_result'] = '1';
            $qs = http_build_query($query);
            $anchor = '';
            if (isset($parts['fragment']) && is_string($parts['fragment']) && $parts['fragment'] !== '') {
                $anchor = '#' . $parts['fragment'];
            }
            return $path . ($qs !== '' ? ('?' . $qs) : '') . $anchor;
        })($redirect);

        $hostForLang = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        if (strpos($hostForLang, ':') !== false) {
            $hostForLang = explode(':', $hostForLang, 2)[0];
        }
        $isRu = (bool)preg_match('/\.ru$/', $hostForLang);
        $t = static function (string $en, string $ru) use ($isRu): string {
            return $isRu ? $ru : $en;
        };

        $name = trim((string)($_POST['contact_name'] ?? ''));
        $campaign = trim((string)($_POST['contact_campaign'] ?? ''));
        $campaignHint = trim((string)($_POST['contact_campaign_hint'] ?? ''));
        $subject = trim((string)($_POST['contact_subject'] ?? ''));
        $email = trim((string)($_POST['contact_email'] ?? ''));
        $message = trim((string)($_POST['contact_message'] ?? ''));
        $auditUrl = trim((string)($_POST['contact_audit_url'] ?? ''));
        $interest = strtolower(trim((string)($_POST['contact_interest'] ?? 'general')));
        if (!in_array($interest, ['general', 'enterprise', 'products', 'cases', 'ai-integration', 'wizard'], true)) {
            $interest = 'general';
        }
        $wizardPayloadRaw = trim((string)($_POST['contact_wizard_payload'] ?? ''));
        $hp = trim((string)($_POST['contact_company'] ?? ''));
        $csrf = trim((string)($_POST['contact_csrf'] ?? ''));
        $startedAt = (int)($_POST['contact_started_at'] ?? 0);
        $now = time();
        $uploadedFiles = [];

        $errors = [];
        if ($hp !== '') {
            $hpLooksBot = (mb_strlen($hp) > 80)
                || preg_match('/https?:\/\//i', $hp)
                || preg_match('/@/u', $hp)
                || preg_match('/\d{4,}/u', $hp);
            if ($hpLooksBot) {
                $errors[] = $t('Spam detected.', 'Обнаружен спам.');
            }
        }
        if (!public_contact_form_csrf_token_verify($csrf, $now)) {
            // Fallback for cached pages: allow same-host POST by Referer.
            $refererRaw = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
            $refererHost = strtolower((string)parse_url($refererRaw, PHP_URL_HOST));
            $requestHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
            if (strpos($requestHost, ':') !== false) {
                $requestHost = explode(':', $requestHost, 2)[0];
            }
            if ($refererHost === '' || $requestHost === '' || $refererHost !== $requestHost) {
                $errors[] = $t('Invalid form token.', 'Неверный токен формы.');
            }
        }
        if ($startedAt <= 0) {
            $startedAt = $now - 10;
        }
        if (($now - $startedAt) > 86400) {
            $errors[] = $t('Suspicious form timing.', 'Подозрительное время заполнения формы.');
        }
        if ($name === '' || mb_strlen($name) < 2) {
            $errors[] = $t('Name is required.', 'Укажите имя.');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $t('Valid email is required.', 'Укажите корректный email.');
        }
        if ($message === '' || mb_strlen($message) < 10) {
            $errors[] = $t('Message is too short.', 'Сообщение слишком короткое.');
        }
        if (mb_strlen($name) > 190 || mb_strlen($campaign) > 190 || mb_strlen($campaignHint) > 190 || mb_strlen($subject) > 190 || mb_strlen($email) > 190 || mb_strlen($message) > 4000) {
            $errors[] = $t('Input is too long.', 'Введены слишком длинные данные.');
        }
        $wizardPayload = null;
        if ($wizardPayloadRaw !== '') {
            if (mb_strlen($wizardPayloadRaw) > 12000) {
                $errors[] = $t('Wizard data is too large.', 'Данные мастера слишком большие.');
            } else {
                $decoded = json_decode($wizardPayloadRaw, true);
                if (is_array($decoded)) {
                    $wizardPayload = $decoded;
                }
            }
        }
        if ($auditUrl !== '' && !preg_match('#^https?://#i', $auditUrl)) {
            $auditUrl = '';
        }

        $turnstileSecret = trim((string)($GLOBALS['ContactTurnstileSecretKey'] ?? ''));
        if ($turnstileSecret !== '') {
            $turnstileResponse = trim((string)($_POST['cf-turnstile-response'] ?? ''));
            if ($turnstileResponse === '') {
                $errors[] = $t('Captcha is required.', 'Подтвердите, что вы не робот.');
            } elseif (function_exists('curl_init')) {
                $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'secret' => $turnstileSecret,
                    'response' => $turnstileResponse,
                    'remoteip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
                ]));
                $verifyRaw = curl_exec($ch);
                curl_close($ch);
                $verify = is_string($verifyRaw) ? json_decode($verifyRaw, true) : null;
                if (!is_array($verify) || (($verify['success'] ?? false) !== true)) {
                    $errors[] = $t('Captcha verification failed.', 'Проверка captcha не пройдена.');
                }
            } else {
                $errors[] = $t('Captcha service unavailable.', 'Сервис captcha недоступен.');
            }
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (function_exists('session_cache_limiter')) {
                @session_cache_limiter('');
            }
            session_start();
        }

        $_SESSION['contact_form_old'] = [
            'contact_name' => $name,
            'contact_campaign' => $campaign,
            'contact_campaign_hint' => $campaignHint,
            'contact_subject' => $subject,
            'contact_email' => $email,
            'contact_message' => $message,
            'contact_interest' => $interest,
        ];

        if (function_exists('analytics_log_lead_event')) {
            analytics_log_lead_event($FRMWRK, 'contact_request_attempt', [
                'email' => $email !== '' ? $email : null,
                'meta' => [
                    'interest' => $interest,
                    'source_page' => $requestPath,
                    'campaign' => $campaign,
                ],
            ]);
        }

        if (!empty($errors)) {
            $_SESSION['contact_form_flash'] = [
                'type' => 'error',
                'message' => implode(' ', $errors),
            ];
            header('Location: ' . $redirectWithStatus);
            exit;
        }

        $DB = $FRMWRK->DB();
        if (!$DB) {
            $_SESSION['contact_form_flash'] = [
                'type' => 'error',
                'message' => $t('Database connection failed.', 'Ошибка подключения к базе данных.'),
            ];
            header('Location: ' . $redirectWithStatus);
            exit;
        }

        public_contact_form_table_ensure($DB);
        if ($campaignHint !== '') {
            if ($campaign === '') {
                $campaign = $campaignHint;
            } else {
                $campaign .= ' | ' . $campaignHint;
            }
        }
        $uploadedFiles = public_contact_form_handle_uploads($hostForLang, $errors);
        if (!empty($errors)) {
            $_SESSION['contact_form_flash'] = [
                'type' => 'error',
                'message' => implode(' ', $errors),
            ];
            header('Location: ' . $redirectWithStatus);
            exit;
        }
        if ($interest === 'enterprise') {
            $message = '[Enterprise interest]' . "\n" . $message;
            if ($campaign === '') {
                $campaign = 'enterprise_interest';
            } elseif (stripos($campaign, 'enterprise') === false) {
                $campaign .= ' | enterprise_interest';
            }
        }
        $requestScheme = (string)($_SERVER['REQUEST_SCHEME'] ?? '');
        if ($requestScheme === '') {
            $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
                || ((string)($_SERVER['SERVER_PORT'] ?? '') === '443')
                || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
            $requestScheme = $isHttps ? 'https' : 'http';
        }
        $domainHostForLinks = (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '');
        $domainHostForLinks = preg_replace('/:\d+$/', '', $domainHostForLinks);
        $publicBaseUrl = $domainHostForLinks !== '' ? ($requestScheme . '://' . $domainHostForLinks) : '';

        if ($subject !== '') {
            $message = $subject . "\n" . $message;
        }
        if (is_array($wizardPayload) && !empty($wizardPayload)) {
            $wizardLabels = $isRu ? [
                'need' => 'Тип запроса',
                'timeline' => 'Сроки',
                'budget_range' => 'Бюджет',
                'product_type' => 'Тип продукта',
                'stage' => 'Стадия',
                'stack' => 'Текущий стек',
                'refactor_problem' => 'Проблема',
                'audit_type' => 'Тип аудита',
                'audit_target' => 'Объект аудита',
                'docs_type' => 'Тип документации',
                'docs_audience' => 'Аудитория документации',
                'seo_site' => 'Сайт',
                'seo_priority' => 'Приоритет SEO',
                'services' => 'Выбранные услуги',
                'links' => 'Ссылки',
                'comment' => 'Комментарий',
                'contact_channel' => 'Контакт в мессенджере',
            ] : [
                'need' => 'Request type',
                'timeline' => 'Timeline',
                'budget_range' => 'Budget',
                'product_type' => 'Product type',
                'stage' => 'Stage',
                'stack' => 'Current stack',
                'refactor_problem' => 'Issue',
                'audit_type' => 'Audit type',
                'audit_target' => 'Audit target',
                'docs_type' => 'Documentation type',
                'docs_audience' => 'Documentation audience',
                'seo_site' => 'Website',
                'seo_priority' => 'SEO priority',
                'services' => 'Selected services',
                'links' => 'Links',
                'comment' => 'Comment',
                'contact_channel' => 'Messenger contact',
            ];
            $wizardLines = [];
            foreach ($wizardLabels as $key => $label) {
                $valRaw = $wizardPayload[$key] ?? null;
                if (is_array($valRaw)) {
                    $parts = [];
                    foreach ($valRaw as $item) {
                        if (is_scalar($item)) {
                            $part = trim((string)$item);
                            if ($part !== '') {
                                $parts[] = $part;
                            }
                        }
                    }
                    if (!empty($parts)) {
                        $wizardLines[] = $label . ': ' . implode('; ', $parts);
                    }
                    continue;
                }
                if (is_scalar($valRaw)) {
                    $val = trim((string)$valRaw);
                    if ($val !== '') {
                        $wizardLines[] = $label . ': ' . $val;
                    }
                }
            }
            if (!empty($wizardLines)) {
                $message .= "\n\n" . ($isRu ? 'Детали запроса:' : 'Request details:') . "\n- " . implode("\n- ", $wizardLines);
            }
        }
        if ($auditUrl !== '') {
            $message .= "\n\n" . '[Audited URL] ' . $auditUrl;
        }
        if (!empty($uploadedFiles)) {
            $message .= "\n\n" . ($isRu ? 'Файлы:' : 'Files:') . "\n";
            foreach ($uploadedFiles as $fileRow) {
                $path = (string)($fileRow['path'] ?? '');
                $full = ($publicBaseUrl !== '' && strpos($path, '/') === 0) ? ($publicBaseUrl . $path) : $path;
                $message .= '- ' . (string)($fileRow['name'] ?? 'file') . ': ' . $full . "\n";
            }
            $message = rtrim($message);
        }
        $safeName = mysqli_real_escape_string($DB, $name);
        $safeCampaign = mysqli_real_escape_string($DB, $campaign);
        $safeSubject = mysqli_real_escape_string($DB, $subject);
        $safeEmail = mysqli_real_escape_string($DB, $email);
        $safeMessage = mysqli_real_escape_string($DB, $message);
        $attachmentsJson = !empty($uploadedFiles) ? json_encode($uploadedFiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $safeAttachments = mysqli_real_escape_string($DB, (string)$attachmentsJson);
        $attachmentsSql = $attachmentsJson !== '' ? ("'{$safeAttachments}'") : 'NULL';
        $safeSource = mysqli_real_escape_string($DB, $requestPath);
        $safeHost = mysqli_real_escape_string($DB, strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')));
        $safeIp = mysqli_real_escape_string($DB, (string)($_SERVER['REMOTE_ADDR'] ?? ''));
        $safeUa = mysqli_real_escape_string($DB, substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500));

        mysqli_query($DB, "
            INSERT INTO contact_requests
                (name, campaign, subject, email, message, attachments_json, source_page, host, ip, user_agent, status, created_at, updated_at)
            VALUES
                ('{$safeName}', '{$safeCampaign}', '{$safeSubject}', '{$safeEmail}', '{$safeMessage}', {$attachmentsSql}, '{$safeSource}', '{$safeHost}', '{$safeIp}', '{$safeUa}', 'new', NOW(), NOW())
        ");

        if (mysqli_error($DB)) {
            $_SESSION['contact_form_flash'] = [
                'type' => 'error',
                'message' => $t('Unable to save request.', 'Не удалось сохранить заявку.'),
            ];
            header('Location: ' . $redirectWithStatus);
            exit;
        }

        unset($_SESSION['contact_form_old']);
        $insertedId = (int)mysqli_insert_id($DB);

        public_contact_form_include_support_libs();

        if (function_exists('admin_notifications_create')) {
            $title = 'New contact request';
            $shortMessage = trim(preg_replace('/\s+/u', ' ', $message));
            if (function_exists('mb_substr')) {
                $shortMessage = mb_substr($shortMessage, 0, 220, 'UTF-8');
            } else {
                $shortMessage = substr($shortMessage, 0, 220);
            }
            $linkUrl = '/adminpanel/contacts/';
            $eventKey = ($insertedId > 0)
                ? ('contact_request_' . $insertedId)
                : ('contact_request_' . md5($email . '|' . $safeSource . '|' . date('Y-m-d H:i:s')));
            $hostForMsg = (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'site');
            $hostForMsg = preg_replace('/:\d+$/', '', $hostForMsg);
            admin_notifications_create(
                $DB,
                'contact_request',
                $title,
                'Lead from ' . ($hostForMsg !== '' ? $hostForMsg : 'site') . ': ' . $shortMessage,
                $linkUrl,
                [
                    'contact_request_id' => $insertedId > 0 ? $insertedId : null,
                    'name' => $name,
                    'email' => $email,
                    'campaign' => $campaign,
                    'subject' => $subject !== '' ? $subject : null,
                    'audit_url' => $auditUrl !== '' ? $auditUrl : null,
                    'attachments_count' => count($uploadedFiles),
                    'source_page' => $requestPath,
                    'host' => (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''),
                    'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
                ],
                $eventKey
            );
        }

        if (function_exists('tg_notify_send')) {
            $domainHost = (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '');
            $domainHost = preg_replace('/:\d+$/', '', $domainHost);
            $adminContactsUrl = '/adminpanel/contacts/';
            if ($domainHost !== '') {
                $adminContactsUrl = $requestScheme . '://' . $domainHost . '/adminpanel/contacts/';
            }
            $telegramFileLines = [];
            if (!empty($uploadedFiles)) {
                $telegramFileLines[] = '';
                $telegramFileLines[] = '<b>Files:</b>';
                foreach ($uploadedFiles as $fileRow) {
                    $fileName = trim((string)($fileRow['name'] ?? 'file'));
                    $path = trim((string)($fileRow['path'] ?? ''));
                    $full = ($publicBaseUrl !== '' && strpos($path, '/') === 0) ? ($publicBaseUrl . $path) : $path;
                    if ($full !== '') {
                        $telegramFileLines[] = '&#8226; <a href="' . tg_notify_escape($full) . '">' . tg_notify_escape($fileName !== '' ? $fileName : $full) . '</a>';
                    } else {
                        $telegramFileLines[] = '&#8226; ' . tg_notify_escape($fileName);
                    }
                }
            }
            $telegramLines = [
                '&#x1F4E9; <b>New Contact Request</b>',
                '',
                '&#8226; <b>Domain:</b> <code>' . tg_notify_escape((string)$domainHost) . '</code>',
                '&#8226; <b>Name:</b> ' . tg_notify_escape($name),
                '&#8226; <b>Email:</b> <code>' . tg_notify_escape($email) . '</code>',
                '&#8226; <b>Campaign:</b> ' . tg_notify_escape($campaign !== '' ? $campaign : '-'),
                '&#8226; <b>Subject:</b> ' . tg_notify_escape($subject !== '' ? $subject : '-'),
                '&#8226; <b>Interest:</b> ' . tg_notify_escape($interest),
                '&#8226; <b>Page:</b> <code>' . tg_notify_escape($requestPath) . '</code>',
                ($auditUrl !== '' ? ('&#8226; <b>Audited URL:</b> <code>' . tg_notify_escape($auditUrl) . '</code>') : ''),
                '&#8226; <b>IP:</b> <code>' . tg_notify_escape((string)($_SERVER['REMOTE_ADDR'] ?? '')) . '</code>',
                '',
                '<b>Message:</b>',
                tg_notify_escape(tg_notify_cut($message, 1200)),
            ];
            if (!empty($telegramFileLines)) {
                $telegramLines = array_merge($telegramLines, $telegramFileLines);
            }
            $telegramLines = array_merge($telegramLines, [
                '',
                '&#128279; <a href="' . tg_notify_escape($adminContactsUrl) . '">Open in admin panel</a>'
            ]);
            tg_notify_send(implode("\n", $telegramLines));
        }

        if (function_exists('analytics_log_lead_event')) {
            analytics_log_lead_event($FRMWRK, 'contact_request_submitted', [
                'email' => $email,
                'meta' => [
                    'interest' => $interest,
                    'source_page' => $requestPath,
                    'campaign' => $campaign,
                    'contact_request_id' => $insertedId > 0 ? $insertedId : null,
                ],
            ]);
        }
        $_SESSION['contact_form_flash'] = [
            'type' => 'ok',
            'message' => $t('Thank you. Your request has been sent.', 'Спасибо! Заявка отправлена.'),
        ];
        header('Location: ' . $redirectWithStatus);
        exit;
    }
}
