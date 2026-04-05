<?php

if (!function_exists('site_audit_normalize_url')) {
    function site_audit_normalize_url(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        if (!preg_match('#^https?://#i', $input)) {
            $input = 'https://' . $input;
        }
        $parts = parse_url($input);
        if (!is_array($parts)) {
            return null;
        }
        $host = strtolower(trim((string)($parts['host'] ?? '')));
        if ($host === '') {
            return null;
        }
        if (!preg_match('/^[a-z0-9.-]+$/i', $host)) {
            return null;
        }
        $scheme = strtolower((string)($parts['scheme'] ?? 'https'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            $scheme = 'https';
        }
        $path = (string)($parts['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }
        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
        return $scheme . '://' . $host . $path . $query;
    }
}

if (!function_exists('site_audit_host_allowed')) {
    function site_audit_host_allowed(string $host): bool
    {
        $host = strtolower(trim($host));
        if ($host === '' || $host === 'localhost' || substr($host, -6) === '.local') {
            return false;
        }
        $ips = @gethostbynamel($host);
        if (!is_array($ips) || empty($ips)) {
            return false;
        }
        foreach ($ips as $ip) {
            $ok = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            if ($ok === false) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('site_audit_http_fetch')) {
    function site_audit_http_fetch(string $url, int $timeout = 12): array
    {
        $result = [
            'ok' => false,
            'error' => '',
            'status' => 0,
            'final_url' => $url,
            'headers' => [],
            'body' => '',
            'ttfb_ms' => 0,
            'total_ms' => 0,
            'redirects' => 0,
            'content_type' => '',
            'content_length' => 0,
        ];

        if (!function_exists('curl_init')) {
            $result['error'] = 'curl_unavailable';
            return $result;
        }

        $responseHeaders = [];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 6,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'PORTCORE-SiteAudit/1.0',
            CURLOPT_ENCODING => '',
            CURLOPT_HEADERFUNCTION => static function ($curl, $headerLine) use (&$responseHeaders): int {
                $len = strlen($headerLine);
                $trimmed = trim($headerLine);
                if ($trimmed === '' || stripos($trimmed, 'HTTP/') === 0) {
                    return $len;
                }
                $parts = explode(':', $trimmed, 2);
                if (count($parts) === 2) {
                    $key = strtolower(trim($parts[0]));
                    $value = trim($parts[1]);
                    $responseHeaders[$key] = $value;
                }
                return $len;
            },
        ]);

        $body = curl_exec($ch);
        if ($body === false) {
            $result['error'] = (string)curl_error($ch);
        } else {
            $result['ok'] = true;
            $result['body'] = (string)$body;
        }
        $result['status'] = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['final_url'] = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $result['ttfb_ms'] = (int)round(((float)curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME)) * 1000);
        $result['total_ms'] = (int)round(((float)curl_getinfo($ch, CURLINFO_TOTAL_TIME)) * 1000);
        $result['redirects'] = (int)curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
        $result['content_type'] = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $result['headers'] = $responseHeaders;
        $result['content_length'] = strlen((string)$body);
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('site_audit_text_len')) {
    function site_audit_text_len(string $s): int
    {
        return function_exists('mb_strlen') ? (int)mb_strlen($s, 'UTF-8') : (int)strlen($s);
    }
}

if (!function_exists('site_audit_header_get')) {
    function site_audit_header_get(array $headers, string $name): string
    {
        $headers = array_change_key_case($headers, CASE_LOWER);
        return trim((string)($headers[strtolower($name)] ?? ''));
    }
}

if (!function_exists('site_audit_parse_sitemap')) {
    function site_audit_parse_sitemap(string $xmlBody): array
    {
        $result = [
            'xml_ok' => false,
            'url_count' => 0,
            'sitemap_count' => 0,
            'lastmod_count' => 0,
        ];
        if (trim($xmlBody) === '') {
            return $result;
        }
        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlBody);
        if ($xml === false) {
            libxml_clear_errors();
            return $result;
        }
        $result['xml_ok'] = true;
        $nodesUrl = $xml->xpath('//*[local-name()="url"]');
        $nodesSitemap = $xml->xpath('//*[local-name()="sitemap"]');
        $nodesLastmod = $xml->xpath('//*[local-name()="lastmod"]');
        $result['url_count'] = is_array($nodesUrl) ? count($nodesUrl) : 0;
        $result['sitemap_count'] = is_array($nodesSitemap) ? count($nodesSitemap) : 0;
        $result['lastmod_count'] = is_array($nodesLastmod) ? count($nodesLastmod) : 0;
        libxml_clear_errors();
        return $result;
    }
}

if (!function_exists('site_audit_html')) {
    function site_audit_html(string $html, string $baseUrl): array
    {
        $out = [
            'title' => '',
            'title_length' => 0,
            'description' => '',
            'description_length' => 0,
            'h1_count' => 0,
            'h2_count' => 0,
            'word_count' => 0,
            'canonical' => '',
            'lang' => '',
            'meta_robots' => '',
            'viewport' => '',
            'og_title' => '',
            'og_description' => '',
            'json_ld_count' => 0,
            'internal_links' => 0,
            'external_links' => 0,
            'broken_link_candidates' => 0,
            'images' => 0,
            'images_without_alt' => 0,
            'favicon_present' => false,
            'internal_link_samples' => [],
            'rating_value' => null,
            'review_count' => null,
            'canonical_host' => '',
            'canonical_is_absolute' => false,
            'meta_robots_noindex' => false,
            'meta_robots_nofollow' => false,
            'hreflang_count' => 0,
            'hreflang_self_present' => false,
            'hreflang_xdefault_present' => false,
            'hreflang_duplicates' => 0,
            'hreflang_invalid' => 0,
            'mixed_content_links' => 0,
            'http_form_actions' => 0,
            'images_missing_dimensions' => 0,
        ];
        if (trim($html) === '') {
            return $out;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = @$dom->loadHTML($html);
        if (!$loaded) {
            libxml_clear_errors();
            return $out;
        }
        $xp = new DOMXPath($dom);

        $titleNode = $xp->query('//title')->item(0);
        if ($titleNode) {
            $out['title'] = trim((string)$titleNode->textContent);
            $out['title_length'] = site_audit_text_len($out['title']);
        }

        $getMeta = static function (DOMXPath $xp, string $attr, string $name): string {
            $list = $xp->query('//meta[@' . $attr . '="' . $name . '"]');
            if ($list && $list->length > 0) {
                return trim((string)$list->item(0)->getAttribute('content'));
            }
            return '';
        };
        $out['description'] = $getMeta($xp, 'name', 'description');
        $out['description_length'] = site_audit_text_len($out['description']);
        $out['meta_robots'] = $getMeta($xp, 'name', 'robots');
        $out['viewport'] = $getMeta($xp, 'name', 'viewport');
        $out['og_title'] = $getMeta($xp, 'property', 'og:title');
        $out['og_description'] = $getMeta($xp, 'property', 'og:description');

        $htmlNode = $xp->query('//html')->item(0);
        if ($htmlNode) {
            $out['lang'] = trim((string)$htmlNode->getAttribute('lang'));
        }

        $canonicalNode = $xp->query('//link[@rel="canonical"]')->item(0);
        if ($canonicalNode) {
            $out['canonical'] = trim((string)$canonicalNode->getAttribute('href'));
            $out['canonical_is_absolute'] = (bool)preg_match('#^https?://#i', $out['canonical']);
            $out['canonical_host'] = strtolower((string)parse_url($out['canonical'], PHP_URL_HOST));
        }
        $faviconNode = $xp->query('//link[contains(translate(@rel,"ICON","icon"),"icon")]')->item(0);
        $out['favicon_present'] = (bool)$faviconNode;
        $metaRobotsLower = strtolower((string)$out['meta_robots']);
        $out['meta_robots_noindex'] = (strpos($metaRobotsLower, 'noindex') !== false);
        $out['meta_robots_nofollow'] = (strpos($metaRobotsLower, 'nofollow') !== false);

        $h1 = $xp->query('//h1');
        $h2 = $xp->query('//h2');
        $out['h1_count'] = $h1 ? (int)$h1->length : 0;
        $out['h2_count'] = $h2 ? (int)$h2->length : 0;
        $jsonLd = $xp->query('//script[@type="application/ld+json"]');
        $out['json_ld_count'] = $jsonLd ? (int)$jsonLd->length : 0;

        $bodyText = trim((string)$xp->evaluate('string(//body)'));
        $wordCount = preg_match_all('/[\p{L}\p{N}_-]+/u', $bodyText, $m);
        $out['word_count'] = $wordCount !== false ? (int)$wordCount : 0;

        $baseHost = strtolower((string)parse_url($baseUrl, PHP_URL_HOST));
        $sampleMap = [];
        $links = $xp->query('//a[@href]');
        if ($links) {
            foreach ($links as $a) {
                $href = trim((string)$a->getAttribute('href'));
                if ($href === '' || strpos($href, 'javascript:') === 0 || strpos($href, '#') === 0) {
                    continue;
                }
                if (stripos($href, 'http://') === 0 && stripos($baseUrl, 'https://') === 0) {
                    $out['mixed_content_links']++;
                }
                if (preg_match('#^https?://#i', $href)) {
                    $h = strtolower((string)parse_url($href, PHP_URL_HOST));
                    if ($h !== '' && $h !== $baseHost) {
                        $out['external_links']++;
                    } else {
                        $out['internal_links']++;
                        $path = (string)(parse_url($href, PHP_URL_PATH) ?: '/');
                        if ($path === '') {
                            $path = '/';
                        }
                        if (!isset($sampleMap[$path])) {
                            $label = trim((string)$a->textContent);
                            if ($label === '') {
                                $label = $path;
                            }
                            $sampleMap[$path] = [
                                'label' => $label,
                                'href' => $path,
                            ];
                        }
                    }
                } else {
                    $out['internal_links']++;
                    $path = $href;
                    if (strpos($path, '/') !== 0) {
                        $path = '/' . ltrim($path, '/');
                    }
                    $path = (string)(parse_url($path, PHP_URL_PATH) ?: '/');
                    if (!isset($sampleMap[$path])) {
                        $label = trim((string)$a->textContent);
                        if ($label === '') {
                            $label = $path;
                        }
                        $sampleMap[$path] = [
                            'label' => $label,
                            'href' => $path,
                        ];
                    }
                }
                if (preg_match('/\s/', $href) || strpos($href, '??') !== false) {
                    $out['broken_link_candidates']++;
                }
            }
        }
        if (!empty($sampleMap)) {
            $out['internal_link_samples'] = array_values(array_slice($sampleMap, 0, 8, true));
        }

        $hreflangNodes = $xp->query('//link[@rel="alternate" and @hreflang]');
        if ($hreflangNodes) {
            $seen = [];
            foreach ($hreflangNodes as $lnk) {
                $code = strtolower(trim((string)$lnk->getAttribute('hreflang')));
                if ($code === '') {
                    continue;
                }
                $out['hreflang_count']++;
                if (isset($seen[$code])) {
                    $out['hreflang_duplicates']++;
                }
                $seen[$code] = true;
                if (!preg_match('/^([a-z]{2,3}(-[a-z]{2})?|x-default)$/', $code)) {
                    $out['hreflang_invalid']++;
                }
                if ($code === 'x-default') {
                    $out['hreflang_xdefault_present'] = true;
                }
                if (($out['lang'] !== '') && ($code === strtolower($out['lang']) || strpos($code, strtolower($out['lang']) . '-') === 0)) {
                    $out['hreflang_self_present'] = true;
                }
            }
        }

        $forms = $xp->query('//form[@action]');
        if ($forms) {
            foreach ($forms as $form) {
                $action = trim((string)$form->getAttribute('action'));
                if (stripos($action, 'http://') === 0 && stripos($baseUrl, 'https://') === 0) {
                    $out['http_form_actions']++;
                }
            }
        }

        $images = $xp->query('//img');
        if ($images) {
            $out['images'] = (int)$images->length;
            foreach ($images as $img) {
                if (!trim((string)$img->getAttribute('alt'))) {
                    $out['images_without_alt']++;
                }
                $w = trim((string)$img->getAttribute('width'));
                $h = trim((string)$img->getAttribute('height'));
                if ($w === '' || $h === '') {
                    $out['images_missing_dimensions']++;
                }
            }
        }

        if ($jsonLd) {
            foreach ($jsonLd as $scriptNode) {
                $json = trim((string)$scriptNode->textContent);
                if ($json === '') {
                    continue;
                }
                $decoded = json_decode($json, true);
                if (!is_array($decoded)) {
                    continue;
                }

                $queue = [$decoded];
                while (!empty($queue)) {
                    $node = array_shift($queue);
                    if (!is_array($node)) {
                        continue;
                    }
                    if (isset($node['aggregateRating']) && is_array($node['aggregateRating'])) {
                        $rating = $node['aggregateRating'];
                        $val = isset($rating['ratingValue']) ? (float)$rating['ratingValue'] : null;
                        $cnt = isset($rating['reviewCount']) ? (int)$rating['reviewCount'] : null;
                        if (($cnt === null || $cnt <= 0) && isset($rating['ratingCount'])) {
                            $cnt = (int)$rating['ratingCount'];
                        }
                        if ($val !== null && $val > 0) {
                            $out['rating_value'] = $val;
                        }
                        if ($cnt !== null && $cnt > 0) {
                            $out['review_count'] = $cnt;
                        }
                    }
                    foreach ($node as $child) {
                        if (is_array($child)) {
                            $queue[] = $child;
                        }
                    }
                }
            }
        }

        libxml_clear_errors();
        return $out;
    }
}

if (!function_exists('site_audit_dns')) {
    function site_audit_dns(string $host): array
    {
        $types = ['A', 'AAAA', 'MX', 'NS', 'TXT'];
        $data = [];
        foreach ($types as $t) {
            $records = @dns_get_record($host, constant('DNS_' . $t));
            $data[$t] = is_array($records) ? count($records) : 0;
        }
        return $data;
    }
}

if (!function_exists('site_audit_score')) {
    function site_audit_score(array $report): array
    {
        $seo = 0;
        $tech = 0;
        $sec = 0;

        $html = (array)($report['html'] ?? []);
        $https = (array)($report['https'] ?? []);
        $robots = (array)($report['robots'] ?? []);
        $headers = array_change_key_case((array)($https['headers'] ?? []), CASE_LOWER);

        if (($https['status'] ?? 0) >= 200 && ($https['status'] ?? 0) < 400) { $tech += 20; }
        if (($https['ttfb_ms'] ?? 0) > 0 && ($https['ttfb_ms'] ?? 0) < 800) { $tech += 15; }
        if (($https['ttfb_ms'] ?? 0) >= 800 && ($https['ttfb_ms'] ?? 0) < 1600) { $tech += 8; }
        if (($robots['robots_ok'] ?? false) === true) { $tech += 10; }
        if (($robots['sitemap_ok'] ?? false) === true) { $tech += 10; }
        if (($robots['sitemap_declared'] ?? false) === true) { $tech += 10; }
        if (($robots['sitemap_xml_ok'] ?? false) === true) { $tech += 10; }
        if (($robots['http_to_https_redirect'] ?? false) === true) { $tech += 10; }
        if (($html['viewport'] ?? '') !== '') { $tech += 10; }
        if (($html['favicon_present'] ?? false) === true) { $tech += 5; }
        if (($https['content_length'] ?? 0) > 0 && ($https['content_length'] ?? 0) <= 1048576) { $tech += 5; }
        if (site_audit_header_get($headers, 'content-encoding') !== '') { $tech += 5; }
        if (site_audit_header_get($headers, 'cache-control') !== '') { $tech += 5; }

        if (($html['title_length'] ?? 0) >= 25 && ($html['title_length'] ?? 0) <= 65) { $seo += 15; }
        if (($html['description_length'] ?? 0) >= 80 && ($html['description_length'] ?? 0) <= 170) { $seo += 15; }
        if (($html['h1_count'] ?? 0) === 1) { $seo += 12; }
        if (($html['canonical'] ?? '') !== '') { $seo += 10; }
        if (($html['canonical_is_absolute'] ?? false) === true) { $seo += 6; }
        if (($html['hreflang_duplicates'] ?? 0) === 0 && ($html['hreflang_invalid'] ?? 0) === 0) { $seo += 6; }
        if (($html['lang'] ?? '') !== '') { $seo += 6; }
        if (($html['word_count'] ?? 0) >= 350) { $seo += 12; }
        if (($html['internal_links'] ?? 0) >= 5) { $seo += 10; }
        if (($html['images_without_alt'] ?? 0) === 0) { $seo += 10; }
        if (($html['images_missing_dimensions'] ?? 0) === 0) { $seo += 5; }
        if (($html['json_ld_count'] ?? 0) > 0) { $seo += 10; }

        $mustSec = ['strict-transport-security', 'content-security-policy', 'x-content-type-options', 'referrer-policy'];
        foreach ($mustSec as $h) {
            if (!empty($headers[$h])) {
                $sec += 20;
            }
        }
        if (!empty($headers['x-frame-options']) || !empty($headers['content-security-policy'])) {
            $sec += 10;
        }
        if (!empty($headers['permissions-policy'])) {
            $sec += 10;
        }
        if (($html['mixed_content_links'] ?? 0) === 0) {
            $sec += 10;
        }
        if (($html['http_form_actions'] ?? 0) === 0) {
            $sec += 10;
        }

        $seo = min(100, $seo);
        $tech = min(100, $tech);
        $sec = min(100, $sec);
        $overall = (int)round(($seo * 0.45) + ($tech * 0.35) + ($sec * 0.20));
        return ['overall' => $overall, 'seo' => $seo, 'tech' => $tech, 'security' => $sec];
    }
}

if (!function_exists('site_audit_issues')) {
    function site_audit_issues(array $report): array
    {
        $issues = [];
        $html = (array)($report['html'] ?? []);
        $https = (array)($report['https'] ?? []);
        $robots = (array)($report['robots'] ?? []);
        $headers = array_change_key_case((array)($https['headers'] ?? []), CASE_LOWER);

        $add = static function (string $severity, string $title, string $details, string $fix) use (&$issues): void {
            $issues[] = [
                'severity' => $severity,
                'title' => $title,
                'details' => $details,
                'fix' => $fix,
            ];
        };

        if (($https['status'] ?? 0) < 200 || ($https['status'] ?? 0) >= 400) {
            $add('critical', 'HTTPS page is not reachable with 2xx/3xx', 'Current status: ' . (int)($https['status'] ?? 0), 'Fix TLS/cert/edge routing and ensure canonical URL returns 200.');
        }
        if (($robots['http_to_https_redirect'] ?? false) !== true) {
            $add('high', 'HTTP to HTTPS redirect is missing', 'HTTP entry does not consistently redirect to HTTPS.', 'Add 301 redirect from HTTP to HTTPS for all routes.');
        }
        if (($html['meta_robots_noindex'] ?? false) === true) {
            $add('critical', 'Page has meta robots noindex', 'Search engines may drop this page from index.', 'Remove noindex for pages that should rank.');
        }
        if (($robots['robots_ok'] ?? false) !== true) {
            $add('high', 'robots.txt is unavailable', 'robots.txt did not return 200.', 'Expose robots.txt at site root and keep it reachable.');
        }
        if (($robots['sitemap_ok'] ?? false) !== true) {
            $add('high', 'sitemap.xml is unavailable', 'sitemap.xml did not return 200.', 'Publish sitemap.xml and include valid URLs.');
        }
        if (($robots['sitemap_xml_ok'] ?? false) !== true && ($robots['sitemap_ok'] ?? false) === true) {
            $add('medium', 'sitemap.xml is not valid XML', 'Crawler may ignore malformed sitemap.', 'Validate XML structure and namespaces.');
        }
        if (($html['canonical'] ?? '') === '') {
            $add('high', 'Canonical is missing', 'No rel=canonical found in head.', 'Add absolute canonical URL for this page.');
        }
        if (($html['canonical'] ?? '') !== '' && ($html['canonical_is_absolute'] ?? false) !== true) {
            $add('medium', 'Canonical is not absolute', 'Relative canonical can cause ambiguity.', 'Use full absolute canonical URL.');
        }
        if (($html['hreflang_invalid'] ?? 0) > 0 || ($html['hreflang_duplicates'] ?? 0) > 0) {
            $add('medium', 'hreflang has quality issues', 'Invalid or duplicate hreflang values detected.', 'Normalize hreflang codes and keep one URL per language.');
        }
        if (($html['h1_count'] ?? 0) !== 1) {
            $add('medium', 'H1 structure is suboptimal', 'Expected exactly one H1, got ' . (int)($html['h1_count'] ?? 0) . '.', 'Use one descriptive H1 matching page intent.');
        }
        if (($html['title_length'] ?? 0) < 25 || ($html['title_length'] ?? 0) > 65) {
            $add('medium', 'Title length is outside recommended range', 'Current title length: ' . (int)($html['title_length'] ?? 0) . '.', 'Target 25-65 characters with clear intent.');
        }
        if (($html['description_length'] ?? 0) < 80 || ($html['description_length'] ?? 0) > 170) {
            $add('low', 'Meta description length is outside recommended range', 'Current length: ' . (int)($html['description_length'] ?? 0) . '.', 'Target 80-170 characters with CTA/value proposition.');
        }
        if (($https['ttfb_ms'] ?? 0) >= 1200) {
            $add('high', 'High TTFB', 'Current TTFB: ' . (int)($https['ttfb_ms'] ?? 0) . ' ms.', 'Review backend latency, cache strategy and DB indexes.');
        }
        if (site_audit_header_get($headers, 'content-encoding') === '') {
            $add('medium', 'Compression is not detected', 'No content-encoding header on HTML response.', 'Enable gzip/brotli at app or reverse proxy layer.');
        }
        if (site_audit_header_get($headers, 'cache-control') === '') {
            $add('low', 'Cache policy is not explicit', 'No cache-control header found.', 'Set explicit cache-control for HTML and static assets.');
        }
        if (($html['images_without_alt'] ?? 0) > 0) {
            $add('medium', 'Images without ALT', 'Images without alt: ' . (int)($html['images_without_alt'] ?? 0) . '.', 'Add meaningful alt attributes for accessibility and image SEO.');
        }
        if (($html['images_missing_dimensions'] ?? 0) > 0) {
            $add('low', 'Images missing width/height', 'Count: ' . (int)($html['images_missing_dimensions'] ?? 0) . '.', 'Set width/height to reduce CLS and improve rendering stability.');
        }
        if (($html['mixed_content_links'] ?? 0) > 0) {
            $add('high', 'Mixed content links detected', 'HTTP links on HTTPS page: ' . (int)($html['mixed_content_links'] ?? 0) . '.', 'Replace internal HTTP links with HTTPS.');
        }
        if (($html['http_form_actions'] ?? 0) > 0) {
            $add('high', 'Insecure form actions detected', 'Forms posting to HTTP endpoints: ' . (int)($html['http_form_actions'] ?? 0) . '.', 'Change form actions to HTTPS endpoints only.');
        }
        foreach (['strict-transport-security', 'content-security-policy', 'x-content-type-options', 'referrer-policy'] as $secHeader) {
            if (empty($headers[$secHeader])) {
                $add('medium', 'Missing security header: ' . $secHeader, 'Header not present in response.', 'Set ' . $secHeader . ' in edge/app config.');
            }
        }

        usort($issues, static function (array $a, array $b): int {
            $w = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return ($w[$b['severity']] ?? 0) <=> ($w[$a['severity']] ?? 0);
        });
        return $issues;
    }
}

if (!function_exists('site_audit_recommendations')) {
    function site_audit_recommendations(array $report): array
    {
        $tips = [];
        $html = (array)($report['html'] ?? []);
        $https = (array)($report['https'] ?? []);
        $headers = array_change_key_case((array)($https['headers'] ?? []), CASE_LOWER);
        $robots = (array)($report['robots'] ?? []);

        if (($html['title_length'] ?? 0) < 25 || ($html['title_length'] ?? 0) > 65) {
            $tips[] = 'Adjust <title> length to 25-65 characters for stable SERP rendering.';
        }
        if (($html['description_length'] ?? 0) < 80 || ($html['description_length'] ?? 0) > 170) {
            $tips[] = 'Keep meta description in 80-170 characters and include core value proposition.';
        }
        if (($html['h1_count'] ?? 0) !== 1) {
            $tips[] = 'Use exactly one H1 per page and align it with the primary intent.';
        }
        if (($html['canonical'] ?? '') === '') {
            $tips[] = 'Add rel=canonical to avoid duplicate indexing signals.';
        }
        if (($https['ttfb_ms'] ?? 0) >= 1200) {
            $tips[] = 'Reduce TTFB (target < 800ms): review cache strategy, DB indexes, and edge/CDN configuration.';
        }
        if (($robots['robots_ok'] ?? false) !== true) {
            $tips[] = 'Expose robots.txt at site root and validate crawl directives.';
        }
        if (($robots['sitemap_ok'] ?? false) !== true) {
            $tips[] = 'Publish sitemap.xml and ensure 200 status for indexability.';
        }
        if (($robots['sitemap_declared'] ?? false) !== true) {
            $tips[] = 'Declare sitemap location inside robots.txt.';
        }
        if (empty($headers['strict-transport-security'])) {
            $tips[] = 'Enable HSTS header for HTTPS hardening.';
        }
        if (empty($headers['content-security-policy'])) {
            $tips[] = 'Add Content-Security-Policy to reduce XSS/injection surface.';
        }
        if (empty($headers['x-content-type-options'])) {
            $tips[] = 'Set X-Content-Type-Options: nosniff.';
        }
        if (($html['images_without_alt'] ?? 0) > 0) {
            $tips[] = 'Provide ALT attributes for all images (accessibility + image search context).';
        }
        if (($html['json_ld_count'] ?? 0) === 0) {
            $tips[] = 'Add structured data (JSON-LD) for eligible entities/pages.';
        }
        if (($html['mixed_content_links'] ?? 0) > 0) {
            $tips[] = 'Replace HTTP links on HTTPS pages to remove mixed content signals.';
        }
        if (($html['http_form_actions'] ?? 0) > 0) {
            $tips[] = 'Move all form actions to HTTPS endpoints.';
        }
        if (($robots['http_to_https_redirect'] ?? false) !== true) {
            $tips[] = 'Enforce strict 301 redirect from HTTP to HTTPS.';
        }
        return $tips;
    }
}

if (!function_exists('site_audit_run')) {
    function site_audit_run(string $input): array
    {
        $normalized = site_audit_normalize_url($input);
        if ($normalized === null) {
            return ['ok' => false, 'error' => 'invalid_url'];
        }
        $host = strtolower((string)parse_url($normalized, PHP_URL_HOST));
        if (!site_audit_host_allowed($host)) {
            return ['ok' => false, 'error' => 'host_not_allowed'];
        }
        $path = (string)(parse_url($normalized, PHP_URL_PATH) ?: '/');
        $query = (string)(parse_url($normalized, PHP_URL_QUERY) ?: '');
        $httpsUrl = 'https://' . $host . $path . ($query !== '' ? '?' . $query : '');
        $httpUrl = 'http://' . $host . $path . ($query !== '' ? '?' . $query : '');

        $https = site_audit_http_fetch($httpsUrl);
        $http = site_audit_http_fetch($httpUrl);
        $primary = $https['ok'] ? $https : $http;
        $html = site_audit_html((string)($primary['body'] ?? ''), (string)($primary['final_url'] ?? $httpsUrl));

        $robotsUrl = 'https://' . $host . '/robots.txt';
        $sitemapUrl = 'https://' . $host . '/sitemap.xml';
        $robotsRes = site_audit_http_fetch($robotsUrl, 8);
        $sitemapRes = site_audit_http_fetch($sitemapUrl, 8);
        $robotsBody = (string)($robotsRes['body'] ?? '');
        $sitemapDeclared = (bool)preg_match('/^\s*Sitemap:\s*https?:\/\/\S+/mi', $robotsBody);
        $sitemapStats = site_audit_parse_sitemap((string)($sitemapRes['body'] ?? ''));
        $httpToHttpsRedirect = false;
        if (($http['ok'] ?? false) === true) {
            $httpFinal = strtolower((string)($http['final_url'] ?? ''));
            $httpToHttpsRedirect = (strpos($httpFinal, 'https://') === 0);
        }
        $httpsHeaders = array_change_key_case((array)($https['headers'] ?? []), CASE_LOWER);

        $report = [
            'ok' => true,
            'input' => $input,
            'normalized_url' => $normalized,
            'host' => $host,
            'generated_at' => gmdate('c'),
            'dns' => site_audit_dns($host),
            'https' => $https,
            'http' => $http,
            'html' => $html,
            'robots' => [
                'robots_url' => $robotsUrl,
                'robots_ok' => (($robotsRes['status'] ?? 0) >= 200 && ($robotsRes['status'] ?? 0) < 300),
                'sitemap_url' => $sitemapUrl,
                'sitemap_ok' => (($sitemapRes['status'] ?? 0) >= 200 && ($sitemapRes['status'] ?? 0) < 300),
                'sitemap_declared' => $sitemapDeclared,
                'sitemap_xml_ok' => (bool)($sitemapStats['xml_ok'] ?? false),
                'sitemap_url_count' => (int)($sitemapStats['url_count'] ?? 0),
                'sitemap_lastmod_count' => (int)($sitemapStats['lastmod_count'] ?? 0),
                'robots_disallow_all' => (bool)preg_match('/^\s*Disallow:\s*\/\s*$/mi', $robotsBody),
                'http_to_https_redirect' => $httpToHttpsRedirect,
                'x_robots_tag' => (string)(($https['headers']['x-robots-tag'] ?? '') ?: ($http['headers']['x-robots-tag'] ?? '')),
            ],
            'tech_profile' => [
                'content_encoding' => site_audit_header_get($httpsHeaders, 'content-encoding'),
                'cache_control' => site_audit_header_get($httpsHeaders, 'cache-control'),
                'etag' => site_audit_header_get($httpsHeaders, 'etag'),
                'last_modified' => site_audit_header_get($httpsHeaders, 'last-modified'),
                'server' => site_audit_header_get($httpsHeaders, 'server'),
            ],
        ];
        $report['scores'] = site_audit_score($report);
        $report['recommendations'] = site_audit_recommendations($report);
        $report['issues'] = site_audit_issues($report);
        return $report;
    }
}

if (!function_exists('site_audit_checks_table_ensure')) {
    function site_audit_log_file(string $message): void
    {
        $root = defined('DIR') ? rtrim((string)DIR, '/\\') : dirname(__DIR__, 2);
        $logDir = $root . '/cache';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        $line = '[' . gmdate('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        @file_put_contents($logDir . '/site_audit_store.log', $line, FILE_APPEND);
    }

    function site_audit_checks_table_ensure(mysqli $db): void
    {
        $ok = mysqli_query($db, "
            CREATE TABLE IF NOT EXISTS site_audit_checks (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                checked_url VARCHAR(2048) NOT NULL,
                host VARCHAR(190) NOT NULL DEFAULT '',
                score_overall TINYINT UNSIGNED NOT NULL DEFAULT 0,
                score_seo TINYINT UNSIGNED NOT NULL DEFAULT 0,
                score_tech TINYINT UNSIGNED NOT NULL DEFAULT 0,
                score_security TINYINT UNSIGNED NOT NULL DEFAULT 0,
                user_ip VARCHAR(64) NOT NULL DEFAULT '',
                user_agent VARCHAR(500) NOT NULL DEFAULT '',
                country_iso2 CHAR(2) NOT NULL DEFAULT '',
                country_name VARCHAR(120) NOT NULL DEFAULT '',
                city_name VARCHAR(120) NOT NULL DEFAULT '',
                timezone VARCHAR(64) NOT NULL DEFAULT '',
                result_json LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_site_audit_checks_created (created_at),
                KEY idx_site_audit_checks_host (host),
                KEY idx_site_audit_checks_ip (user_ip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        if (!$ok) {
            site_audit_log_file('table_ensure_failed: ' . mysqli_error($db));
        }
    }
}

if (!function_exists('site_audit_store_check')) {
    function site_audit_store_check(mysqli $db, array $data): bool
    {
        site_audit_checks_table_ensure($db);

        $checkedUrl = (string)($data['checked_url'] ?? '');
        $host = (string)($data['host'] ?? '');
        $overall = max(0, min(100, (int)($data['score_overall'] ?? 0)));
        $seo = max(0, min(100, (int)($data['score_seo'] ?? 0)));
        $tech = max(0, min(100, (int)($data['score_tech'] ?? 0)));
        $security = max(0, min(100, (int)($data['score_security'] ?? 0)));
        $userIp = (string)($data['user_ip'] ?? '');
        $userAgent = (string)($data['user_agent'] ?? '');
        $countryIso2 = strtoupper(substr((string)($data['country_iso2'] ?? ''), 0, 2));
        $countryName = (string)($data['country_name'] ?? '');
        $cityName = (string)($data['city_name'] ?? '');
        $timezone = (string)($data['timezone'] ?? '');
        $resultJson = (string)($data['result_json'] ?? '');

        $sql = "
            INSERT INTO site_audit_checks (
                checked_url, host, score_overall, score_seo, score_tech, score_security,
                user_ip, user_agent, country_iso2, country_name, city_name, timezone, result_json
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = mysqli_prepare($db, $sql);
        if (!$stmt) {
            site_audit_log_file('insert_prepare_failed: ' . mysqli_error($db));
            return false;
        }
        mysqli_stmt_bind_param(
            $stmt,
            'ssiiiisssssss',
            $checkedUrl,
            $host,
            $overall,
            $seo,
            $tech,
            $security,
            $userIp,
            $userAgent,
            $countryIso2,
            $countryName,
            $cityName,
            $timezone,
            $resultJson
        );
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            site_audit_log_file(
                'insert_execute_failed: ' . mysqli_stmt_error($stmt)
                . '; host=' . $host
                . '; ip=' . $userIp
                . '; url_len=' . strlen($checkedUrl)
            );
        }
        mysqli_stmt_close($stmt);
        return (bool)$ok;
    }
}
