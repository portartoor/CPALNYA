<?php

function tg_notify_settings(): array
{
    global $TelegramNotifyEnabled, $TelegramBotToken, $TelegramChatId, $TelegramApiBase, $TelegramNotifyTimeout, $TelegramWebhookSecretToken, $TelegramActionSecret;

    $enabled = isset($GLOBALS['TelegramNotifyEnabled']) ? (bool)$GLOBALS['TelegramNotifyEnabled'] : (bool)($TelegramNotifyEnabled ?? false);
    $botToken = (string)($GLOBALS['TelegramBotToken'] ?? ($TelegramBotToken ?? ''));
    $chatId = (string)($GLOBALS['TelegramChatId'] ?? ($TelegramChatId ?? ''));
    $apiBase = rtrim((string)($GLOBALS['TelegramApiBase'] ?? ($TelegramApiBase ?? 'https://api.telegram.org')), '/');
    $timeout = isset($GLOBALS['TelegramNotifyTimeout']) ? (int)$GLOBALS['TelegramNotifyTimeout'] : (int)($TelegramNotifyTimeout ?? 12);
    $webhookSecret = (string)($GLOBALS['TelegramWebhookSecretToken'] ?? ($TelegramWebhookSecretToken ?? ''));
    $actionSecret = (string)($GLOBALS['TelegramActionSecret'] ?? ($TelegramActionSecret ?? ''));

    if (($botToken === '' || $chatId === '') && defined('DIR')) {
        $cfgFile = rtrim((string)DIR, '/\\') . '/core/config.php';
        if (is_file($cfgFile)) {
            include $cfgFile;
            if ($botToken === '' && isset($TelegramBotToken)) {
                $botToken = (string)$TelegramBotToken;
            }
            if ($chatId === '' && isset($TelegramChatId)) {
                $chatId = (string)$TelegramChatId;
            }
            if (isset($TelegramNotifyEnabled)) {
                $enabled = (bool)$TelegramNotifyEnabled;
            }
            if (isset($TelegramApiBase) && (string)$TelegramApiBase !== '') {
                $apiBase = rtrim((string)$TelegramApiBase, '/');
            }
            if (isset($TelegramNotifyTimeout)) {
                $timeout = (int)$TelegramNotifyTimeout;
            }
            if ($webhookSecret === '' && isset($TelegramWebhookSecretToken)) {
                $webhookSecret = (string)$TelegramWebhookSecretToken;
            }
            if ($actionSecret === '' && isset($TelegramActionSecret)) {
                $actionSecret = (string)$TelegramActionSecret;
            }
        }
    }

    return [
        'enabled' => $enabled,
        'bot_token' => $botToken,
        'chat_id' => $chatId,
        'api_base' => $apiBase,
        'timeout' => $timeout,
        'webhook_secret' => $webhookSecret,
        'action_secret' => $actionSecret,
    ];
}

function tg_notify_log(string $message): void
{
    $baseDir = defined('DIR') ? rtrim((string)DIR, '/\\') : dirname(__DIR__, 2);
    $logDir = $baseDir . '/cache';
    $logFile = $logDir . '/telegram_notify.log';
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logFile, $line, FILE_APPEND);
}

function tg_notify_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tg_notify_cut(string $value, int $limit = 512): string
{
    $value = trim($value);
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value, 'UTF-8') > $limit) {
            return mb_substr($value, 0, $limit - 1, 'UTF-8') . '...';
        }
        return $value;
    }
    if (strlen($value) > $limit) {
        return substr($value, 0, $limit - 1) . '...';
    }
    return $value;
}

function tg_notify_utf8_clean(string $value): string
{
    $value = str_replace("\xEF\xBB\xBF", '', $value);
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }
    if (function_exists('mb_convert_encoding')) {
        $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1251, CP1251, ISO-8859-1');
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }
    }
    if (function_exists('iconv')) {
        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }
    }
    return $value;
}

function tg_notify_clean_params(array $params): array
{
    foreach ($params as $k => $v) {
        if (is_string($v)) {
            $params[$k] = tg_notify_utf8_clean($v);
        }
    }
    return $params;
}

function tg_notify_send(string $text, ?array $settings = null, array $options = []): bool
{
    $settings = $settings ?? tg_notify_settings();
    if (!(bool)($settings['enabled'] ?? false)) {
        tg_notify_log('Skipped: notifications disabled');
        return false;
    }

    $botToken = (string)($settings['bot_token'] ?? '');
    $chatId = (string)($settings['chat_id'] ?? '');
    $apiBase = (string)($settings['api_base'] ?? 'https://api.telegram.org');
    $timeout = (int)($settings['timeout'] ?? 12);
    $maskedToken = ($botToken !== '') ? (substr($botToken, 0, 10) . '***') : 'empty';

    if ($botToken === '' || $chatId === '') {
        tg_notify_log('Skipped: missing bot token or chat id. token=' . $maskedToken . '; chat=' . ($chatId !== '' ? $chatId : 'empty'));
        return false;
    }

    $url = $apiBase . '/bot' . $botToken . '/sendMessage';
    $text = tg_notify_utf8_clean($text);
    $payload = http_build_query([
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => 'true',
    ], '', '&', PHP_QUERY_RFC3986);
    if (isset($options['reply_markup']) && is_array($options['reply_markup'])) {
        $payload .= '&reply_markup=' . rawurlencode(json_encode($options['reply_markup'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            tg_notify_log('Send failed via curl. HTTP=' . $status . '; err=' . $curlErr . '; resp=' . tg_notify_cut((string)$response, 1200));
            return false;
        }
        $decoded = json_decode((string)$response, true);
        if (is_array($decoded) && array_key_exists('ok', $decoded) && !$decoded['ok']) {
            tg_notify_log('Telegram API rejected message. response=' . tg_notify_cut((string)$response, 1200));
            return false;
        }
        tg_notify_log('Send success via curl. HTTP=' . $status . '; chat=' . $chatId);
        return true;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => $timeout,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        tg_notify_log('Send failed via file_get_contents');
        return false;
    }
    $decoded = json_decode((string)$response, true);
    if (is_array($decoded) && array_key_exists('ok', $decoded) && !$decoded['ok']) {
        tg_notify_log('Telegram API rejected message (fgc). response=' . tg_notify_cut((string)$response, 1200));
        return false;
    }
    tg_notify_log('Send success via file_get_contents; chat=' . $chatId);
    return true;
}

function tg_notify_action_signature(int $subscriptionId, ?array $settings = null): string
{
    $settings = $settings ?? tg_notify_settings();
    $secret = (string)($settings['action_secret'] ?? '');
    if ($secret === '') {
        $secret = (string)($settings['bot_token'] ?? '');
    }
    return substr(hash_hmac('sha256', 'sub_activate:' . $subscriptionId, $secret), 0, 24);
}

function tg_notify_validate_action_signature(int $subscriptionId, string $signature, ?array $settings = null): bool
{
    $expected = tg_notify_action_signature($subscriptionId, $settings);
    return hash_equals($expected, $signature);
}

function tg_notify_api_call(string $method, array $params, ?array $settings = null): array
{
    $settings = $settings ?? tg_notify_settings();
    $botToken = (string)($settings['bot_token'] ?? '');
    $apiBase = (string)($settings['api_base'] ?? 'https://api.telegram.org');
    $timeout = (int)($settings['timeout'] ?? 12);
    if ($botToken === '') {
        return ['ok' => false, 'error' => 'missing bot token'];
    }

    $url = $apiBase . '/bot' . $botToken . '/' . $method;
    $params = tg_notify_clean_params($params);
    $payload = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'http_status' => $status, 'error' => $curlErr];
        }
        $decoded = json_decode((string)$response, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'http_status' => $status, 'error' => 'invalid json', 'raw' => (string)$response];
        }
        $decoded['http_status'] = $status;
        return $decoded;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => $timeout,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['ok' => false, 'error' => 'file_get_contents failed'];
    }
    $decoded = json_decode((string)$response, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'invalid json', 'raw' => (string)$response];
    }
    return $decoded;
}

function tg_notify_registration(array $data): bool
{
    $lines = [
        '&#x1F195; <b>New Registration</b>',
        '',
        '&#x1F464; <b>User</b>',
        '&#8226; Email: <code>' . tg_notify_escape(tg_notify_cut((string)($data['email'] ?? 'n/a'), 160)) . '</code>',
        '&#8226; User ID: <code>' . tg_notify_escape((string)($data['user_id'] ?? 'n/a')) . '</code>',
        '',
        '&#x1F30D; <b>Source</b>',
        '&#8226; Domain: <code>' . tg_notify_escape((string)($data['domain'] ?? 'n/a')) . '</code>',
        '&#8226; IP: <code>' . tg_notify_escape((string)($data['ip'] ?? 'n/a')) . '</code>',
        '&#8226; Geo: <code>' . tg_notify_escape((string)($data['country_name'] ?? 'n/a')) . '</code>'
            . ' / <code>' . tg_notify_escape((string)($data['city_name'] ?? 'n/a')) . '</code>'
            . ' / <code>' . tg_notify_escape((string)($data['timezone'] ?? 'n/a')) . '</code>',
        '',
        '&#x1F552; <b>Meta</b>',
        '&#8226; Created: <code>' . tg_notify_escape((string)($data['created_at'] ?? date('Y-m-d H:i:s'))) . '</code>',
        '&#8226; UA: <code>' . tg_notify_escape(tg_notify_cut((string)($data['user_agent'] ?? 'n/a'), 260)) . '</code>',
    ];

    return tg_notify_send(implode("\n", $lines));
}

function tg_notify_subscription(array $data): bool
{
    $subId = (int)($data['subscription_id'] ?? 0);
    $txHash = trim((string)($data['tx_hash'] ?? ''));
    $lines = [
        '&#x1F4B3; <b>New Subscription Request</b>',
        '',
        '&#x1F9FE; <b>Order</b>',
        '&#8226; Sub ID: <code>' . tg_notify_escape((string)($data['subscription_id'] ?? 'n/a')) . '</code>',
        '&#8226; User: <code>' . tg_notify_escape((string)($data['user_email'] ?? ('#' . ($data['user_id'] ?? 'n/a')))) . '</code>',
        '&#8226; Status: <code>' . tg_notify_escape((string)($data['status'] ?? 'pending')) . '</code>',
        '',
        '&#x1F4E6; <b>Plan</b>',
        '&#8226; Plan: <b>' . tg_notify_escape((string)($data['plan_name'] ?? 'n/a')) . '</b> / ' . tg_notify_escape((string)($data['plan_type'] ?? 'n/a')),
        '&#8226; Duration: <code>' . tg_notify_escape((string)($data['duration'] ?? 'n/a')) . '</code> month(s)',
        '',
        '&#x1F4B0; <b>Payment</b>',
        '&#8226; Price USD: <code>' . tg_notify_escape((string)($data['amount_usd'] ?? $data['final_price'] ?? 'n/a')) . '</code>',
        '&#8226; Currency: <code>' . tg_notify_escape((string)($data['currency_code'] ?? 'n/a')) . '</code>',
        '&#8226; Amount (selected): <code>' . tg_notify_escape((string)($data['amount_in_currency'] ?? 'n/a')) . '</code>',
        '&#8226; Amount (crypto): <code>' . tg_notify_escape((string)($data['amount_crypto'] ?? 'n/a')) . '</code>',
        '&#8226; Wallet: <code>' . tg_notify_escape((string)($data['wallet_name'] ?? 'n/a')) . '</code>',
        '&#8226; Address: <code>' . tg_notify_escape(tg_notify_cut((string)($data['wallet_address'] ?? 'n/a'), 220)) . '</code>',
        '&#8226; TX Hash: <code>' . tg_notify_escape(tg_notify_cut($txHash !== '' ? $txHash : 'not provided', 220)) . '</code>',
        '',
        '&#x1F310; <b>Traffic</b>',
        '&#8226; Domain: <code>' . tg_notify_escape((string)($data['domain'] ?? 'n/a')) . '</code>',
        '&#8226; IP: <code>' . tg_notify_escape((string)($data['ip'] ?? 'n/a')) . '</code>',
        '&#8226; Created: <code>' . tg_notify_escape((string)($data['created_at'] ?? date('Y-m-d H:i:s'))) . '</code>',
    ];
    $options = [];
    if ($subId > 0) {
        $signature = tg_notify_action_signature($subId);
        $options['reply_markup'] = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Activate subscription',
                        'callback_data' => 'sub_activate:' . $subId . ':' . $signature
                    ]
                ]
            ]
        ];
    }

    return tg_notify_send(implode("\n", $lines), null, $options);
}
