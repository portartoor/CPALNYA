<?php

if (!function_exists('portal_section_build')) {
    function portal_section_build($FRMWRK, array &$ModelPage, string $sectionKey, array $meta = []): array
    {
        $dataKey = $sectionKey;
        $data = [
            'section_key' => $sectionKey,
            'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
            'lang' => 'en',
            'page' => 1,
            'per_page' => 16,
            'current_cluster' => '',
            'clusters' => [],
            'total' => 0,
            'total_pages' => 1,
            'items' => [],
            'selected' => null,
            'issue' => [],
            'error' => '',
        ];

        if (strpos((string)$data['host'], ':') !== false) {
            $data['host'] = explode(':', (string)$data['host'], 2)[0];
        }

        $examplesCommon = DIR . 'core/controls/examples/_common.php';
        if (!is_file($examplesCommon)) {
            $data['error'] = $sectionKey . ' dependencies not found';
            $ModelPage[$dataKey] = $data;
            return $data;
        }

        require_once $examplesCommon;
        $authorsLib = DIR . 'core/libs/cpalnya_authors.php';
        if (is_file($authorsLib)) {
            require_once $authorsLib;
        }

        $db = $FRMWRK->DB();
        if (!$db || !function_exists('examples_table_exists') || !examples_table_exists($db)) {
            $data['error'] = 'examples_articles table is missing';
            $ModelPage[$dataKey] = $data;
            return $data;
        }

        $data['lang'] = function_exists('examples_resolve_lang')
            ? examples_resolve_lang((string)$data['host'])
            : ((preg_match('/\.ru$/', (string)$data['host']) ? 'ru' : 'en'));
        $isRu = ((string)$data['lang'] === 'ru');

        $data['issue'] = [
            'issue_kicker' => $isRu ? (string)($meta['issue_kicker_ru'] ?? $sectionKey) : (string)($meta['issue_kicker_en'] ?? $sectionKey),
            'hero_title' => $isRu ? (string)($meta['hero_title_ru'] ?? $sectionKey) : (string)($meta['hero_title_en'] ?? $sectionKey),
            'hero_description' => $isRu ? (string)($meta['hero_description_ru'] ?? '') : (string)($meta['hero_description_en'] ?? ''),
            'issue_title' => $isRu ? (string)($meta['issue_title_ru'] ?? $sectionKey) : (string)($meta['issue_title_en'] ?? $sectionKey),
            'issue_subtitle' => $isRu ? (string)($meta['issue_subtitle_ru'] ?? '') : (string)($meta['issue_subtitle_en'] ?? ''),
            'hero_note' => $isRu ? (string)($meta['hero_note_ru'] ?? '') : (string)($meta['hero_note_en'] ?? ''),
            'hero_image_url' => '',
            'hero_image_data' => '',
        ];

        $data['page'] = max(1, (int)($_GET['page'] ?? 1));
        $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $requestPath = is_string($requestPath) ? trim($requestPath) : '';
        $segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
            return $value !== '';
        }));
        $pathCluster = '';
        $pathSlug = '';
        if (isset($segments[0]) && strtolower((string)$segments[0]) === $sectionKey) {
            $pathCluster = trim((string)($segments[1] ?? ''));
            $pathSlug = trim((string)($segments[2] ?? ''));
        }

        $clusterParam = trim((string)($_GET['cluster'] ?? ''));
        if ($clusterParam === '') {
            $clusterParam = $pathCluster;
        }
        $clusterParam = $clusterParam !== '' && function_exists('examples_normalize_cluster')
            ? examples_normalize_cluster($clusterParam, (string)$data['lang'])
            : '';
        $data['current_cluster'] = $clusterParam;

        $slugParam = trim((string)($_GET['slug'] ?? ''));
        if ($slugParam === '') {
            $slugParam = $pathSlug;
        }

        if (function_exists('examples_fetch_clusters')) {
            $data['clusters'] = (array)examples_fetch_clusters($FRMWRK, (string)$data['host'], (string)$data['lang'], 40, $sectionKey);
        }

        if ($slugParam === '' && $clusterParam !== '' && function_exists('examples_fetch_published_by_slug')) {
            $legacySlug = function_exists('examples_slugify') ? examples_slugify($clusterParam) : $clusterParam;
            if ($legacySlug !== '') {
                $legacySelected = examples_fetch_published_by_slug(
                    $FRMWRK,
                    (string)$data['host'],
                    $legacySlug,
                    (string)$data['lang'],
                    '',
                    $sectionKey
                );
                if (is_array($legacySelected)) {
                    $data['selected'] = $legacySelected;
                    $resolvedCluster = trim((string)($legacySelected['cluster_code'] ?? ''));
                    if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                        $data['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$data['lang']);
                    }
                }
            }
        }

        if ($slugParam !== '' && function_exists('examples_fetch_published_by_slug')) {
            $slugSafe = function_exists('examples_slugify') ? examples_slugify($slugParam) : $slugParam;
            if ($slugSafe !== '') {
                $selected = examples_fetch_published_by_slug(
                    $FRMWRK,
                    (string)$data['host'],
                    $slugSafe,
                    (string)$data['lang'],
                    (string)$data['current_cluster'],
                    $sectionKey
                );
                if (!is_array($selected) && (string)$data['current_cluster'] !== '') {
                    $selected = examples_fetch_published_by_slug($FRMWRK, (string)$data['host'], $slugSafe, (string)$data['lang'], '', $sectionKey);
                }
                if (is_array($selected)) {
                    $data['selected'] = $selected;
                    $resolvedCluster = trim((string)($selected['cluster_code'] ?? ''));
                    if ($resolvedCluster !== '' && function_exists('examples_normalize_cluster')) {
                        $data['current_cluster'] = examples_normalize_cluster($resolvedCluster, (string)$data['lang']);
                    }
                }
            }
        }

        if (function_exists('examples_fetch_published_count') && function_exists('examples_fetch_published_page')) {
            $data['total'] = (int)examples_fetch_published_count(
                $FRMWRK,
                (string)$data['host'],
                (string)$data['lang'],
                '',
                false,
                (string)$data['current_cluster'],
                $sectionKey
            );
            $data['total_pages'] = max(1, (int)ceil($data['total'] / max(1, $data['per_page'])));
            if ($data['page'] > $data['total_pages']) {
                $data['page'] = $data['total_pages'];
            }
            $items = examples_fetch_published_page(
                $FRMWRK,
                (string)$data['host'],
                (string)$data['lang'],
                (int)$data['page'],
                (int)$data['per_page'],
                '',
                false,
                (string)$data['current_cluster'],
                $sectionKey
            );
            foreach ((array)$items as $row) {
                $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
                $full = trim((string)($row['preview_image_url'] ?? ''));
                $base = trim((string)($row['preview_image_data'] ?? ''));
                $row['image_src'] = $thumb !== '' ? $thumb : ($full !== '' ? $full : $base);
                $data['items'][] = $row;
            }
            if (function_exists('examples_popularity_attach_views')) {
                $data['items'] = examples_popularity_attach_views(
                    $FRMWRK,
                    (string)$data['host'],
                    (string)$data['lang'],
                    $sectionKey,
                    (array)$data['items']
                );
            }
        }

        $scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
        $host = preg_replace('/^www\./', '', (string)$data['host']);
        $baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');

        $pickArticleImage = static function (array $row): string {
            $full = trim((string)($row['preview_image_url'] ?? ''));
            if ($full !== '') {
                return $full;
            }
            $thumb = trim((string)($row['preview_image_thumb_url'] ?? ''));
            if ($thumb !== '') {
                return $thumb;
            }
            return trim((string)($row['preview_image_data'] ?? ''));
        };

        $normalizeIso = static function ($value): string {
            $raw = trim((string)$value);
            if ($raw === '') {
                return '';
            }
            $ts = strtotime($raw);
            return $ts === false ? '' : gmdate('c', $ts);
        };

        $selectedArticle = is_array($data['selected'] ?? null) ? $data['selected'] : null;
        if ($selectedArticle) {
            $selectedArticle['hero_image_src'] = $pickArticleImage($selectedArticle);
            if (function_exists('examples_popularity_attach_single_view')) {
                $selectedArticle = examples_popularity_attach_single_view(
                    $FRMWRK,
                    (string)$data['host'],
                    (string)$data['lang'],
                    $sectionKey,
                    $selectedArticle
                );
            }
            $data['selected'] = $selectedArticle;
            $articleTitle = trim((string)($selectedArticle['title'] ?? ''));
            if ($articleTitle === '') {
                $articleTitle = $isRu ? (string)($meta['fallback_article_title_ru'] ?? 'Статья') : (string)($meta['fallback_article_title_en'] ?? 'Article');
            }
            $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selectedArticle['excerpt_html'] ?? $selectedArticle['content_html'] ?? ''))));
            if ($desc === '') {
                $desc = $isRu ? (string)($meta['selected_description_ru'] ?? 'Статья раздела.') : (string)($meta['selected_description_en'] ?? 'Section article.');
            }
            if (mb_strlen($desc, 'UTF-8') > 290) {
                $desc = trim((string)mb_substr($desc, 0, 287, 'UTF-8')) . '...';
            }

            $articleSlug = trim((string)($selectedArticle['slug'] ?? ''));
            $articleCluster = trim((string)($selectedArticle['cluster_code'] ?? ''));
            if ($articleCluster !== '' && function_exists('examples_normalize_cluster')) {
                $articleCluster = examples_normalize_cluster($articleCluster, (string)$data['lang']);
            }

            $ModelPage['title'] = $ModelPage['title'] ?? $articleTitle;
            $ModelPage['description'] = $ModelPage['description'] ?? $desc;
            $ModelPage['canonical'] = $ModelPage['canonical'] ?? ($baseUrl . examples_article_url_path($articleSlug, $articleCluster, (string)$data['host'], $sectionKey));
            $ModelPage['og_type'] = $ModelPage['og_type'] ?? 'article';
            $ModelPage['og_title'] = $ModelPage['og_title'] ?? $articleTitle;
            $ModelPage['og_description'] = $ModelPage['og_description'] ?? $desc;
            $articleImage = $pickArticleImage($selectedArticle);
            if ($articleImage !== '') {
                $ModelPage['og_image'] = $ModelPage['og_image'] ?? $articleImage;
            }
            if (function_exists('cpalnya_author_resolve')) {
                $ModelPage['article_author_profile'] = $ModelPage['article_author_profile'] ?? cpalnya_author_resolve((string)($selectedArticle['author_name'] ?? ''), (string)$data['lang']);
            }
            $ModelPage['article_author'] = $ModelPage['article_author'] ?? trim((string)($selectedArticle['author_name'] ?? ''));
            $ModelPage['article_published_time'] = $ModelPage['article_published_time'] ?? $normalizeIso($selectedArticle['published_at'] ?? $selectedArticle['created_at'] ?? '');
            $ModelPage['article_modified_time'] = $ModelPage['article_modified_time'] ?? $normalizeIso($selectedArticle['updated_at'] ?? '');
            $ModelPage['article_section'] = $ModelPage['article_section'] ?? (string)($meta['article_section'] ?? ucfirst($sectionKey));
            if ($articleCluster !== '') {
                $ModelPage['article_tags'] = $ModelPage['article_tags'] ?? [$articleCluster];
            }
        } else {
            $title = $isRu ? (string)($meta['list_title_ru'] ?? ucfirst($sectionKey)) : (string)($meta['list_title_en'] ?? ucfirst($sectionKey));
            $description = $isRu ? (string)($meta['list_description_ru'] ?? '') : (string)($meta['list_description_en'] ?? '');
            $canonicalPath = examples_cluster_list_path((string)$data['current_cluster'], (string)$data['host'], $sectionKey);
            $ModelPage['title'] = $ModelPage['title'] ?? $title;
            $ModelPage['description'] = $ModelPage['description'] ?? $description;
            $ModelPage['canonical'] = $ModelPage['canonical'] ?? ($baseUrl . $canonicalPath);
            $ModelPage['og_title'] = $ModelPage['og_title'] ?? $title;
            $ModelPage['og_description'] = $ModelPage['og_description'] ?? $description;
            $ModelPage['article_section'] = $ModelPage['article_section'] ?? (string)($meta['article_section'] ?? ucfirst($sectionKey));
        }

        $ModelPage[$dataKey] = $data;
        return $data;
    }
}
