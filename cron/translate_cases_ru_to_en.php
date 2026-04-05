<?php
ini_set('display_errors', '0');

define('DIR', dirname(__DIR__) . '/');
require_once DIR . 'core/config.php';
require_once DIR . 'core/libs/frmwrk/frmwrk.php';

$seoSettingsLib = DIR . 'core/libs/seo_generator_settings.php';
if (is_file($seoSettingsLib)) {
    require_once $seoSettingsLib;
}

function tr_cases_echo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function tr_cases_runtime_options(): array
{
    $opts = [
        'limit' => 50,
        'dry_run' => false,
        'force_update' => false,
    ];
    $argv = isset($GLOBALS['argv']) && is_array($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
    foreach ($argv as $arg) {
        if (!is_string($arg) || strpos($arg, '--') !== 0) {
            continue;
        }
        if ($arg === '--dry-run') {
            $opts['dry_run'] = true;
            continue;
        }
        if ($arg === '--force-update') {
            $opts['force_update'] = true;
            continue;
        }
        if (strpos($arg, '--limit=') === 0) {
            $v = (int)substr($arg, 8);
            if ($v > 0) {
                $opts['limit'] = max(1, min(500, $v));
            }
        }
    }
    return $opts;
}

function tr_cases_llm_call(string $apiKey, string $baseUrl, string $model, string $systemPrompt, string $userPrompt, int $timeout = 120): string
{
    $url = rtrim($baseUrl, '/') . '/chat/completions';
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ],
        'temperature' => 0.2,
        'response_format' => ['type' => 'json_object'],
        'max_tokens' => 12000,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => max(30, $timeout),
    ]);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $err !== '') {
        throw new RuntimeException('LLM transport error: ' . $err);
    }
    if ($http < 200 || $http >= 300) {
        throw new RuntimeException('LLM HTTP ' . $http . ': ' . mb_substr((string)$body, 0, 300));
    }
    $json = json_decode((string)$body, true);
    if (!is_array($json)) {
        throw new RuntimeException('LLM invalid JSON response');
    }
    $content = (string)($json['choices'][0]['message']['content'] ?? '');
    if ($content === '') {
        throw new RuntimeException('LLM empty content');
    }
    return $content;
}

function tr_cases_decode_json(string $raw): array
{
    $raw = trim($raw);
    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }
    if (preg_match('/\{[\s\S]*\}/u', $raw, $m)) {
        $json = json_decode((string)$m[0], true);
        if (is_array($json)) {
            return $json;
        }
    }
    throw new RuntimeException('Translation JSON decode failed');
}

$runtime = tr_cases_runtime_options();
$FRMWRK = new FRMWRK();
$DB = $FRMWRK->DB();
if (!($DB instanceof mysqli)) {
    tr_cases_echo('DB connection is unavailable.');
    exit(1);
}

$apiKey = trim((string)($GLOBALS['OpenRouterApiKey'] ?? $GLOBALS['OpenAIApiKey'] ?? ''));
$baseUrl = trim((string)($GLOBALS['OpenRouterBaseUrl'] ?? $GLOBALS['OpenAIBaseUrl'] ?? 'https://openrouter.ai/api/v1'));
$model = trim((string)($GLOBALS['OpenRouterModel'] ?? $GLOBALS['OpenAIModel'] ?? 'openai/gpt-4.1-mini'));

if (function_exists('seo_gen_settings_get')) {
    $seo = seo_gen_settings_get($DB);
    if (is_array($seo)) {
        $apiKey = trim((string)($seo['openrouter_api_key'] ?? $seo['openai_api_key'] ?? $apiKey));
        $baseUrl = trim((string)($seo['openrouter_base_url'] ?? $seo['openai_base_url'] ?? $baseUrl));
        $model = trim((string)($seo['openrouter_model'] ?? $seo['openai_model'] ?? $model));
    }
}
if ($apiKey === '') {
    tr_cases_echo('API key is empty. Fill openrouter_api_key/openai_api_key in SEO generator settings.');
    exit(1);
}

$enHost = 'portcore.online';
$enHostSafe = mysqli_real_escape_string($DB, $enHost);

$rows = $FRMWRK->DBRecords(
    "SELECT ru.*
     FROM public_cases ru
     LEFT JOIN public_cases en
       ON en.lang_code='en'
      AND en.symbolic_code = ru.symbolic_code
      AND en.domain_host = '{$enHostSafe}'
     WHERE ru.lang_code='ru'
       AND ru.is_published = 1
       AND en.id IS NULL
     ORDER BY ru.id ASC
     LIMIT " . (int)$runtime['limit']
);

if (empty($rows)) {
    tr_cases_echo('No missing RU->EN cases found.');
    exit(0);
}

tr_cases_echo('Found missing cases: ' . count($rows));
$systemPrompt = 'You are a senior technical editor. Translate RU case-study content to high-quality business English. Return JSON only.';

$ok = 0;
$fail = 0;
foreach ($rows as $row) {
    $code = trim((string)($row['symbolic_code'] ?? ''));
    if ($code === '') {
        $code = trim((string)($row['slug'] ?? ''));
    }
    if ($code === '') {
        $fail++;
        continue;
    }

    $input = [
        'title' => (string)($row['title'] ?? ''),
        'client_name' => (string)($row['client_name'] ?? ''),
        'industry_summary' => (string)($row['industry_summary'] ?? ''),
        'period_summary' => (string)($row['period_summary'] ?? ''),
        'role_summary' => (string)($row['role_summary'] ?? ''),
        'stack_summary' => (string)($row['stack_summary'] ?? ''),
        'problem_summary' => (string)($row['problem_summary'] ?? ''),
        'result_summary' => (string)($row['result_summary'] ?? ''),
        'seo_title' => (string)($row['seo_title'] ?? ''),
        'seo_description' => (string)($row['seo_description'] ?? ''),
        'excerpt_html' => (string)($row['excerpt_html'] ?? ''),
        'challenge_html' => (string)($row['challenge_html'] ?? ''),
        'solution_html' => (string)($row['solution_html'] ?? ''),
        'architecture_html' => (string)($row['architecture_html'] ?? ''),
        'results_html' => (string)($row['results_html'] ?? ''),
        'metrics_html' => (string)($row['metrics_html'] ?? ''),
        'deliverables_html' => (string)($row['deliverables_html'] ?? ''),
    ];
    $userPrompt = "Translate the JSON object fields from Russian to natural business English.\n"
        . "Rules:\n"
        . "1) Keep HTML tags in *_html fields valid.\n"
        . "2) Preserve technical meaning and specificity.\n"
        . "3) Do not add brands or external links.\n"
        . "4) Keep first-person style where present.\n"
        . "Return strict JSON with the same keys.\n\n"
        . json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    try {
        $raw = tr_cases_llm_call($apiKey, $baseUrl, $model, $systemPrompt, $userPrompt, 120);
        $tr = tr_cases_decode_json($raw);

        $title = mysqli_real_escape_string($DB, trim((string)($tr['title'] ?? $input['title'])));
        $slug = mysqli_real_escape_string($DB, trim((string)($row['slug'] ?? '')));
        $symbolic = mysqli_real_escape_string($DB, trim((string)($row['symbolic_code'] ?? $code)));
        $client = mysqli_real_escape_string($DB, trim((string)($tr['client_name'] ?? $input['client_name'])));
        $industry = mysqli_real_escape_string($DB, trim((string)($tr['industry_summary'] ?? $input['industry_summary'])));
        $period = mysqli_real_escape_string($DB, trim((string)($tr['period_summary'] ?? $input['period_summary'])));
        $role = mysqli_real_escape_string($DB, trim((string)($tr['role_summary'] ?? $input['role_summary'])));
        $stack = mysqli_real_escape_string($DB, trim((string)($tr['stack_summary'] ?? $input['stack_summary'])));
        $problem = mysqli_real_escape_string($DB, trim((string)($tr['problem_summary'] ?? $input['problem_summary'])));
        $result = mysqli_real_escape_string($DB, trim((string)($tr['result_summary'] ?? $input['result_summary'])));
        $seoTitle = mysqli_real_escape_string($DB, trim((string)($tr['seo_title'] ?? $title)));
        $seoDesc = mysqli_real_escape_string($DB, trim((string)($tr['seo_description'] ?? $input['seo_description'])));
        $excerpt = mysqli_real_escape_string($DB, (string)($tr['excerpt_html'] ?? $input['excerpt_html']));
        $challenge = mysqli_real_escape_string($DB, (string)($tr['challenge_html'] ?? $input['challenge_html']));
        $solution = mysqli_real_escape_string($DB, (string)($tr['solution_html'] ?? $input['solution_html']));
        $architecture = mysqli_real_escape_string($DB, (string)($tr['architecture_html'] ?? $input['architecture_html']));
        $resultsHtml = mysqli_real_escape_string($DB, (string)($tr['results_html'] ?? $input['results_html']));
        $metrics = mysqli_real_escape_string($DB, (string)($tr['metrics_html'] ?? $input['metrics_html']));
        $deliverables = mysqli_real_escape_string($DB, (string)($tr['deliverables_html'] ?? $input['deliverables_html']));
        $sortOrder = (int)($row['sort_order'] ?? 100);
        $published = (int)($row['is_published'] ?? 1);

        $sql = "INSERT INTO public_cases
            (domain_host, lang_code, title, slug, symbolic_code, client_name, industry_summary, period_summary, role_summary, stack_summary, problem_summary, result_summary, seo_title, seo_description, excerpt_html, challenge_html, solution_html, architecture_html, results_html, metrics_html, deliverables_html, sort_order, is_published, created_at, updated_at)
            VALUES
            ('{$enHostSafe}', 'en', '{$title}', '{$slug}', '{$symbolic}', '{$client}', '{$industry}', '{$period}', '{$role}', '{$stack}', '{$problem}', '{$result}', '{$seoTitle}', '{$seoDesc}', '{$excerpt}', '{$challenge}', '{$solution}', '{$architecture}', '{$resultsHtml}', '{$metrics}', '{$deliverables}', {$sortOrder}, {$published}, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                title=VALUES(title),
                client_name=VALUES(client_name),
                industry_summary=VALUES(industry_summary),
                period_summary=VALUES(period_summary),
                role_summary=VALUES(role_summary),
                stack_summary=VALUES(stack_summary),
                problem_summary=VALUES(problem_summary),
                result_summary=VALUES(result_summary),
                seo_title=VALUES(seo_title),
                seo_description=VALUES(seo_description),
                excerpt_html=VALUES(excerpt_html),
                challenge_html=VALUES(challenge_html),
                solution_html=VALUES(solution_html),
                architecture_html=VALUES(architecture_html),
                results_html=VALUES(results_html),
                metrics_html=VALUES(metrics_html),
                deliverables_html=VALUES(deliverables_html),
                sort_order=VALUES(sort_order),
                is_published=VALUES(is_published),
                updated_at=NOW()";

        if ($runtime['dry_run']) {
            tr_cases_echo('[DRY] ' . $code . ' translated');
            $ok++;
            continue;
        }
        if (!mysqli_query($DB, $sql)) {
            throw new RuntimeException('DB error: ' . mysqli_error($DB));
        }
        tr_cases_echo('[OK] ' . $code . ' translated and saved');
        $ok++;
    } catch (Throwable $e) {
        $fail++;
        tr_cases_echo('[FAIL] ' . $code . ' -> ' . $e->getMessage());
    }
}

tr_cases_echo('Done. success=' . $ok . ', failed=' . $fail . ', dry_run=' . (int)$runtime['dry_run']);
exit($fail > 0 ? 2 : 0);

