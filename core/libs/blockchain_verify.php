<?php

function blockchain_http_get_json(string $url, int $timeout = 15): ?array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $status < 200 || $status >= 300) {
            return null;
        }
        $decoded = json_decode((string)$raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return null;
    }
    $decoded = json_decode((string)$raw, true);
    return is_array($decoded) ? $decoded : null;
}

function blockchain_http_get_text(string $url, int $timeout = 15): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $status < 200 || $status >= 300) {
            return null;
        }
        return trim((string)$raw);
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
            'ignore_errors' => true,
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return null;
    }
    return trim((string)$raw);
}

function blockchain_value_to_decimal($rawValue, int $decimals): float
{
    $value = is_numeric($rawValue) ? (float)$rawValue : 0.0;
    if ($decimals <= 0) {
        return $value;
    }
    return $value / pow(10, $decimals);
}

function blockchain_normalize_amount(array $sub): float
{
    foreach (['amount_in_currency', 'amount_crypto', 'amount_usd', 'final_price'] as $key) {
        $v = $sub[$key] ?? null;
        if ($v !== null && $v !== '' && is_numeric($v)) {
            return (float)$v;
        }
    }
    return 0.0;
}

function blockchain_wallet_matches(string $expected, string $actual): bool
{
    $e = strtolower(trim($expected));
    $a = strtolower(trim($actual));
    if ($e === '' || $a === '') {
        return false;
    }
    return $e === $a;
}

function blockchain_amount_matches(float $expected, float $actual, float $tolerancePct = 0.015): bool
{
    if ($expected <= 0 || $actual <= 0) {
        return false;
    }

    $minAllowed = $expected * (1 - max(0.0, $tolerancePct));
    // Overpayment is accepted.
    return $actual >= $minAllowed;
}

function blockchain_verify_btc(string $txHash, string $wallet, float $expectedAmount, int $minConfirmations): array
{
    $tx = blockchain_http_get_json('https://blockstream.info/api/tx/' . rawurlencode($txHash));
    if (!is_array($tx)) {
        return ['ok' => false, 'reason' => 'btc_tx_not_found'];
    }

    $txConfirmed = (bool)($tx['status']['confirmed'] ?? false);
    $blockHeight = (int)($tx['status']['block_height'] ?? 0);
    $confirmations = 0;
    if ($txConfirmed && $blockHeight > 0) {
        $tipText = blockchain_http_get_text('https://blockstream.info/api/blocks/tip/height');
        $tipHeight = is_string($tipText) && is_numeric($tipText) ? (int)$tipText : 0;
        if ($tipHeight > 0) {
            $confirmations = max(0, $tipHeight - $blockHeight + 1);
        }
    }

    $matchedAmount = 0.0;
    foreach (($tx['vout'] ?? []) as $vout) {
        $script = strtolower((string)($vout['scriptpubkey_address'] ?? ''));
        if (!blockchain_wallet_matches($wallet, $script)) {
            continue;
        }
        $matchedAmount += blockchain_value_to_decimal($vout['value'] ?? 0, 8);
    }

    if ($matchedAmount <= 0) {
        return ['ok' => false, 'reason' => 'btc_wallet_mismatch', 'confirmations' => $confirmations, 'actual_amount' => $matchedAmount];
    }
    if (!blockchain_amount_matches($expectedAmount, $matchedAmount)) {
        return ['ok' => false, 'reason' => 'btc_amount_mismatch', 'confirmations' => $confirmations, 'actual_amount' => $matchedAmount];
    }
    if ($confirmations < $minConfirmations) {
        return ['ok' => false, 'reason' => 'btc_not_enough_confirmations', 'confirmations' => $confirmations, 'actual_amount' => $matchedAmount];
    }

    return ['ok' => true, 'confirmations' => $confirmations, 'actual_amount' => $matchedAmount, 'network' => 'BTC'];
}

function blockchain_verify_eth(string $txHash, string $wallet, float $expectedAmount, int $minConfirmations): array
{
    $url = 'https://api.blockchair.com/ethereum/dashboards/transaction/' . rawurlencode($txHash) . '?limit=1';
    $payload = blockchain_http_get_json($url);
    if (!is_array($payload)) {
        return ['ok' => false, 'reason' => 'eth_tx_not_found'];
    }

    $data = $payload['data'][$txHash] ?? null;
    if (!is_array($data) && isset($payload['data']) && is_array($payload['data'])) {
        $first = reset($payload['data']);
        if (is_array($first)) {
            $data = $first;
        }
    }
    if (!is_array($data)) {
        return ['ok' => false, 'reason' => 'eth_tx_not_found'];
    }
    $tx = $data['transaction'] ?? [];
    $to = (string)($tx['recipient'] ?? '');
    $actualAmount = blockchain_value_to_decimal($tx['value'] ?? 0, 18);
    $blockId = (int)($tx['block_id'] ?? 0);

    $state = $payload['context']['state'] ?? 0;
    $confirmations = 0;
    if (is_numeric($state) && $blockId > 0) {
        $confirmations = max(0, ((int)$state) - $blockId + 1);
    }

    if (!blockchain_wallet_matches($wallet, $to)) {
        return ['ok' => false, 'reason' => 'eth_wallet_mismatch', 'confirmations' => $confirmations, 'actual_amount' => $actualAmount];
    }
    if (!blockchain_amount_matches($expectedAmount, $actualAmount)) {
        return ['ok' => false, 'reason' => 'eth_amount_mismatch', 'confirmations' => $confirmations, 'actual_amount' => $actualAmount];
    }
    if ($confirmations < $minConfirmations) {
        return ['ok' => false, 'reason' => 'eth_not_enough_confirmations', 'confirmations' => $confirmations, 'actual_amount' => $actualAmount];
    }

    return ['ok' => true, 'confirmations' => $confirmations, 'actual_amount' => $actualAmount, 'network' => 'ETH'];
}

function blockchain_verify_subscription_payment(array $sub): array
{
    $txHash = trim((string)($sub['tx_hash'] ?? ''));
    if ($txHash === '') {
        return ['ok' => false, 'reason' => 'tx_hash_empty'];
    }

    $currency = strtoupper(trim((string)($sub['currency_code'] ?? '')));
    $wallet = trim((string)($sub['wallet_address'] ?? ''));
    $expectedAmount = blockchain_normalize_amount($sub);

    if ($wallet === '' || $expectedAmount <= 0) {
        return ['ok' => false, 'reason' => 'invalid_subscription_data'];
    }

    $minConfirmationsMap = [
        'BTC' => isset($GLOBALS['SubscriptionTxMinConfBTC']) ? (int)$GLOBALS['SubscriptionTxMinConfBTC'] : 2,
        'ETH' => isset($GLOBALS['SubscriptionTxMinConfETH']) ? (int)$GLOBALS['SubscriptionTxMinConfETH'] : 12,
    ];
    $minConfirmations = (int)($minConfirmationsMap[$currency] ?? 0);

    if ($currency === 'BTC') {
        return blockchain_verify_btc($txHash, $wallet, $expectedAmount, $minConfirmations);
    }
    if ($currency === 'ETH') {
        return blockchain_verify_eth($txHash, $wallet, $expectedAmount, $minConfirmations);
    }

    return ['ok' => false, 'reason' => 'unsupported_currency_' . $currency];
}
