<?php
$playbooks = (array)($ModelPage['playbooks'] ?? []);
$items = (array)($playbooks['items'] ?? []);
$selected = is_array($playbooks['selected'] ?? null) ? $playbooks['selected'] : null;
$authorProfile = is_array($ModelPage['article_author_profile'] ?? null) ? $ModelPage['article_author_profile'] : null;
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalIsFavorite = !empty($ModelPage['portal_is_favorite']);
$portalCommentTotal = (int)($ModelPage['portal_comment_total'] ?? 0);
$portalCsrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$issue = (array)($playbooks['issue'] ?? []);
$clusters = (array)($playbooks['clusters'] ?? []);
$lang = (string)($playbooks['lang'] ?? 'en');
$isRu = ($lang === 'ru');
$page = max(1, (int)($playbooks['page'] ?? 1));
$totalPages = max(1, (int)($playbooks['total_pages'] ?? 1));
$currentCluster = trim((string)($playbooks['current_cluster'] ?? ''));
$sectionKey = trim((string)($playbooks['section_key'] ?? 'playbooks'));
if ($sectionKey === '') {
    $sectionKey = 'playbooks';
}
$authorDisplayName = trim((string)($authorProfile['resolved_name'] ?? ($ModelPage['article_author'] ?? ($selected['author_name'] ?? ''))));
$authorNickname = trim((string)($authorProfile['nickname'] ?? ''));
$authorRole = trim((string)($authorProfile['resolved_role'] ?? ''));
$authorBio = trim((string)($authorProfile['resolved_bio'] ?? ''));
if ($authorDisplayName === '') {
    $authorDisplayName = $isRu ? 'Редакция ЦПАЛЬНЯ' : 'CPALNYA Editorial';
}
$authorInitials = '';
if ($authorDisplayName !== '') {
    $authorWords = preg_split('/[\s"\'.@_-]+/u', $authorDisplayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    foreach ($authorWords as $authorWord) {
        $authorInitials .= mb_strtoupper((string)mb_substr((string)$authorWord, 0, 1, 'UTF-8'), 'UTF-8');
        if (mb_strlen($authorInitials, 'UTF-8') >= 2) {
            break;
        }
    }
}
if ($authorInitials === '' && $authorNickname !== '') {
    $authorInitials = mb_strtoupper((string)mb_substr($authorNickname, 0, 2, 'UTF-8'), 'UTF-8');
}
$t = static function (string $ru, string $en) use ($isRu): string { return $isRu ? $ru : $en; };
$strip = static function (string $html, int $limit = 170): string {
    $text = trim((string)preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim((string)mb_substr($text, 0, $limit - 1, 'UTF-8')) . '...';
};
$renderIssueText = static function (string $text, string $class = ''): string {
    $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
    if ($text === '') {
        return '';
    }
    $parts = preg_split("/\n\s*\n/u", $text) ?: [];
    if (!$parts) {
        $parts = [$text];
    }
    $html = '';
    foreach ($parts as $part) {
        $part = trim((string)$part);
        if ($part === '') {
            continue;
        }
        $part = preg_replace("/\n+/u", "<br>", htmlspecialchars($part, ENT_QUOTES, 'UTF-8'));
        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $html .= '<p' . $classAttr . '>' . $part . '</p>';
    }
    return $html;
};
$splitContentByFirstH2 = static function (string $html): array {
    $html = trim($html);
    if ($html === '') {
        return ['intro' => '', 'body' => ''];
    }

    $prevUseInternal = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<div id="cp-content-root">' . $html . '</div>';
    $loaded = $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return ['intro' => '', 'body' => $html];
    }

    $root = $doc->getElementById('cp-content-root');
    if (!$root instanceof DOMElement) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return ['intro' => '', 'body' => $html];
    }

    $nodes = [];
    foreach ($root->childNodes as $childNode) {
        if ($childNode instanceof DOMText && trim((string)$childNode->nodeValue) === '') {
            continue;
        }
        $nodes[] = $childNode;
    }

    $firstH2Removed = false;
    $filtered = [];
    foreach ($nodes as $node) {
        $isH2 = ($node instanceof DOMElement) && strtolower($node->tagName) === 'h2';
        if ($isH2 && !$firstH2Removed) {
            $firstH2Removed = true;
            continue;
        }
        $filtered[] = $node;
    }

    $introDoc = new DOMDocument('1.0', 'UTF-8');
    $introWrap = $introDoc->createElement('div');
    $introDoc->appendChild($introWrap);

    $bodyDoc = new DOMDocument('1.0', 'UTF-8');
    $bodyWrap = $bodyDoc->createElement('div');
    $bodyDoc->appendChild($bodyWrap);

    $introMode = true;
    foreach ($filtered as $node) {
        $isParagraph = ($node instanceof DOMElement) && strtolower($node->tagName) === 'p';
        if ($introMode && $isParagraph) {
            $introWrap->appendChild($introDoc->importNode($node, true));
            continue;
        }
        $introMode = false;
        $bodyWrap->appendChild($bodyDoc->importNode($node, true));
    }

    $readInner = static function (DOMNode $node, DOMDocument $ownerDoc): string {
        $out = '';
        foreach ($node->childNodes as $child) {
            $out .= (string)$ownerDoc->saveHTML($child);
        }
        return trim($out);
    };

    $introHtml = $readInner($introWrap, $introDoc);
    $bodyHtml = $readInner($bodyWrap, $bodyDoc);

    libxml_clear_errors();
    libxml_use_internal_errors($prevUseInternal);

    return ['intro' => $introHtml, 'body' => $bodyHtml];
};
$stripChecklistBrackets = static function (string $html): string {
    $html = trim($html);
    if ($html === '' || strpos($html, '[') === false) {
        return $html;
    }

    $prevUseInternal = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<div id="cp-checklist-root">' . $html . '</div>';
    $loaded = $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return preg_replace('/\[\s*\]\s*/u', '', $html) ?? $html;
    }

    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query('//text()');
    if ($nodes instanceof DOMNodeList) {
        foreach ($nodes as $textNode) {
            $parentName = strtolower((string)($textNode->parentNode->nodeName ?? ''));
            if (in_array($parentName, ['script', 'style', 'code', 'pre'], true)) {
                continue;
            }
            $textNode->nodeValue = (string)(preg_replace('/\[\s*\]\s*/u', '', (string)$textNode->nodeValue) ?? (string)$textNode->nodeValue);
        }
    }

    $root = $doc->getElementById('cp-checklist-root');
    if (!$root instanceof DOMElement) {
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);
        return preg_replace('/\[\s*\]\s*/u', '', $html) ?? $html;
    }

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= (string)$doc->saveHTML($child);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($prevUseInternal);
    return trim($out);
};
$issueImage = trim((string)($issue['hero_image_url'] ?? ''));
if ($issueImage === '') {
    $issueImage = trim((string)($issue['hero_image_data'] ?? ''));
}
if ($issueImage === '') {
    $issueImage = '/april2026_new2.png';
}
$buildPageUrl = static function (?string $cluster = '', int $pageNum = 1) use ($sectionKey): string {
    $cluster = trim((string)$cluster);
    if ($cluster === '') {
        $base = '/' . trim($sectionKey, '/') . '/';
    } else {
        $base = function_exists('examples_cluster_list_path')
            ? examples_cluster_list_path($cluster, null, $sectionKey)
            : '/playbooks/';
    }
    if ($pageNum > 1) {
        $base .= '?' . http_build_query(['page' => $pageNum]);
    }
    return $base;
};
$buildArticleUrl = static function (string $slug, string $cluster = '') use ($sectionKey): string {
    return function_exists('examples_article_url_path')
        ? examples_article_url_path($slug, $cluster, null, $sectionKey)
        : '/playbooks/';
};
$selectedArticleUrl = '';
if ($selected) {
    $selectedArticleUrl = $buildArticleUrl((string)($selected['slug'] ?? ''), (string)($selected['cluster_code'] ?? ''));
}
$selectedShareUrl = $selectedArticleUrl !== ''
    ? (((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http') . '://' . (string)($_SERVER['HTTP_HOST'] ?? '') . $selectedArticleUrl)
    : '';
$selectedShareTitle = trim((string)($selected['title'] ?? ''));
$clusterLabelByCode = static function (string $clusterCode) use ($clusters): string {
    $clusterCode = trim($clusterCode);
    if ($clusterCode === '') {
        return '';
    }
    foreach ($clusters as $cluster) {
        $candidate = trim((string)($cluster['code'] ?? ''));
        if ($candidate !== '' && $candidate === $clusterCode) {
            return trim((string)($cluster['label'] ?? $candidate));
        }
    }
    $fallback = str_replace(['-', '_'], ' ', $clusterCode);
    return trim(mb_convert_case($fallback, MB_CASE_TITLE, 'UTF-8'));
};
$sectionCrumbLabel = isset($issueTitle) && trim((string)$issueTitle) !== ''
    ? trim((string)$issueTitle)
    : $t('Р–СѓСЂРЅР°Р»', 'Journal');
if ($sectionKey === 'playbooks') {
    $sectionCrumbLabel = $t('РџСЂР°РєС‚РёРєР°', 'Playbooks');
} elseif ($sectionKey === 'signals') {
    $sectionCrumbLabel = $t('РџРѕРІРµСЃС‚РєР°', 'Signals');
} elseif ($sectionKey === 'fun') {
    $sectionCrumbLabel = $t('Р¤Р°РЅ', 'Fun');
}
$selectedClusterCode = trim((string)($selected['cluster_code'] ?? ''));
$selectedClusterLabel = $clusterLabelByCode($selectedClusterCode);
$topicCloudItems = [];
if (!empty($clusters)) {
    $clusterPool = [];
    $maxClusterCount = 1;
    foreach ($clusters as $cluster) {
        if (!is_array($cluster)) {
            continue;
        }
        $clusterCode = trim((string)($cluster['code'] ?? ''));
        if ($clusterCode === '') {
            continue;
        }
        $count = max(1, (int)($cluster['count'] ?? 1));
        $maxClusterCount = max($maxClusterCount, $count);
        $clusterPool[] = [
            'code' => $clusterCode,
            'label' => trim((string)($cluster['label'] ?? $clusterCode)),
            'count' => $count,
        ];
    }
    shuffle($clusterPool);
    $topicCloudItems[] = [
        'code' => '',
        'label' => $t('Р’СЃРµ С‚РµРјС‹', 'All topics'),
        'count' => max(1, $maxClusterCount),
        'is_all' => true,
        'basis' => 18,
        'grow' => 1.1,
        'font' => 12,
    ];
    foreach ($clusterPool as $cluster) {
        $ratio = $maxClusterCount > 0 ? ((float)$cluster['count'] / (float)$maxClusterCount) : 0;
        $tier = 1;
        if ($ratio >= 0.85) {
            $tier = 5;
        } elseif ($ratio >= 0.58) {
            $tier = 4;
        } elseif ($ratio >= 0.33) {
            $tier = 3;
        } elseif ($ratio >= 0.16) {
            $tier = 2;
        }
        $cluster['basis'] = [14, 16, 20, 24, 30][$tier - 1];
        $cluster['grow'] = [0.85, 1, 1.15, 1.35, 1.6][$tier - 1];
        $cluster['font'] = [11, 12, 13, 15, 17][$tier - 1];
        $topicCloudItems[] = $cluster;
    }
}
$detailBreadcrumbs = [];
if ($selected) {
    $detailBreadcrumbs[] = [
        'label' => $sectionCrumbLabel,
        'url' => $buildPageUrl(),
    ];
    if ($selectedClusterLabel !== '') {
        $detailBreadcrumbs[] = [
            'label' => $selectedClusterLabel,
            'url' => $buildPageUrl($selectedClusterCode),
        ];
    }
    $detailBreadcrumbs[] = [
        'label' => $selectedShareTitle !== '' ? $selectedShareTitle : $t('Статья', 'Article'),
        'url' => '',
    ];
}
$shareIcon = static function (string $network): string {
    $icons = [
        'telegram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.94 4.66a1.5 1.5 0 0 0-1.68-.2L3.2 12.84a1 1 0 0 0 .1 1.85l4.34 1.49 1.66 5.06a1 1 0 0 0 1.73.33l2.42-3 4.76 3.48a1.5 1.5 0 0 0 2.36-.92l2.3-14.5a1.5 1.5 0 0 0-.93-1.97ZM9.4 15.47l-.56 3.03-.96-2.91 9.88-7.42-8.36 7.3Z"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M18.9 2H22l-6.78 7.75L23.2 22h-6.26l-4.9-7.4L5.56 22H2.44l7.25-8.28L2 2h6.42l4.42 6.74L18.9 2Zm-1.1 18h1.74L7.47 3.9H5.6L17.8 20Z"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13.5 22v-8h2.7l.4-3h-3.1V9.08c0-.87.25-1.46 1.5-1.46h1.72V4.93c-.3-.04-1.32-.13-2.5-.13-2.47 0-4.16 1.5-4.16 4.28V11H7v3h3.04v8h3.46Z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.94 8.5A1.72 1.72 0 1 1 6.9 5.06a1.72 1.72 0 0 1 .04 3.44ZM5.3 9.82h3.28V22H5.3V9.82Zm5.34 0h3.14v1.66h.05c.44-.83 1.5-1.7 3.09-1.7 3.3 0 3.9 2.17 3.9 5V22h-3.27v-6.4c0-1.52-.03-3.49-2.12-3.49-2.12 0-2.44 1.66-2.44 3.38V22h-3.28V9.82Z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.04 2a9.93 9.93 0 0 0-8.48 15.1L2 22l5.06-1.53A10 10 0 1 0 12.04 2Zm0 18.17a8.16 8.16 0 0 1-4.16-1.14l-.3-.18-3 .9.94-2.93-.2-.3a8.18 8.18 0 1 1 6.72 3.65Zm4.49-6.12c-.24-.12-1.44-.71-1.67-.8-.22-.08-.38-.12-.54.12-.16.23-.62.8-.76.96-.14.15-.28.17-.52.05-.24-.12-1-.37-1.9-1.19-.7-.62-1.17-1.39-1.31-1.62-.14-.24-.01-.36.1-.48.1-.1.24-.28.36-.41.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.23-.84.82-.84 2 0 1.18.86 2.33.98 2.49.12.16 1.69 2.58 4.1 3.62.57.25 1.02.4 1.37.51.58.19 1.1.16 1.52.1.46-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z"/></svg>',
    ];
    return $icons[$network] ?? '';
};
$statIcon = static function (string $type): string {
    $icons = [
        'eye' => '<svg class="jrnl-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 5.2c5.2 0 8.7 4.4 9.7 5.8.4.6.4 1.4 0 2-1 1.4-4.5 5.8-9.7 5.8S3.3 14.4 2.3 13a1.8 1.8 0 0 1 0-2c1-1.4 4.5-5.8 9.7-5.8Zm0 2C7.9 7.2 5 10.6 4 12c1 1.4 3.9 4.8 8 4.8s7-3.4 8-4.8c-1-1.4-3.9-4.8-8-4.8Zm0 1.7a3.1 3.1 0 1 1 0 6.2 3.1 3.1 0 0 1 0-6.2Zm0 2a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2Z"/></svg>',
        'comments' => '<svg class="jrnl-stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 4h14a3 3 0 0 1 3 3v7.2a3 3 0 0 1-3 3h-7.1l-5.2 3.9a1 1 0 0 1-1.6-.8v-3.1H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Zm14 2H5a1 1 0 0 0-1 1v7.2a1 1 0 0 0 1 1h1.1a1 1 0 0 1 1 1v2.1l3.9-2.9a1 1 0 0 1 .6-.2H19a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1Z"/></svg>',
    ];
    return $icons[$type] ?? '';
};
$relatedItems = [];
if ($selected) {
    foreach ($items as $item) {
        if ((int)($item['id'] ?? 0) === (int)($selected['id'] ?? 0)) {
            continue;
        }
        $relatedItems[] = $item;
        if (count($relatedItems) >= 4) {
            break;
        }
    }
}

$heroKicker = $t('PLAYBOOKS / ПРАКТИКА', 'PLAYBOOKS / PRACTICE');
$heroTitle = $t('Практические playbooks для арбитражных команд', 'Operational playbooks for affiliate teams');
$heroDescription = $t(
    "Раздел для команд, которые строят affiliate-операционку не на удаче, а на повторяемых системах: setup-процедурах, чеклистах, handoff-сценариях, tracking-дисциплине, farm-ритме, creative review loop и решениях, которые должны переживать смену офферов, баны и турбулентность платформ.\n\nЗдесь практика собрана не как россыпь советов, а как рабочая опора для всей команды. Один материал помогает быстрее собрать запуск, другой удерживает читаемым трекинг, третий возвращает контроль, когда связка проседает или модерация начинает давить.\n\nЭто не сухой архив инструкций, а backstage-библиотека, где шаги, роли, rollback-планы и troubleshooting всегда под рукой.",
    "A section for teams building affiliate operations around repeatable systems rather than lucky runs: setups, checklists, handoffs, tracking discipline, farm cadence, creative loops, and solutions that must survive offer churn, bans, and platform shifts.\n\nHere, practice is treated not as a loose pile of advice but as a support system for the whole operation. One piece helps assemble a launch faster, another keeps tracking readable, and a third restores control when a bundle slips or moderation tightens.\n\nThis is not a dry archive of instructions, but a working backstage library where steps, roles, rollback plans, and troubleshooting stay close at hand."
);
$issueTitle = $t('Навигатор по backstage-операционке', 'Backstage operations index');
$issueSubtitle = $t(
    "How-to заметки, troubleshooting-разборы, рабочие мемо и переиспользуемые playbooks для баеров, фармеров, tracking-операторов, creative-команд и всех, кто держит ежедневную механику affiliate-производства.\n\nСюда возвращаются не за вдохновением, а чтобы проверить следующий шаг, восстановить логику setup, сравнить решения или удержать темп в шумной ежедневной работе.\n\nЗдесь нужен не общий совет, а точное следующее действие.",
    "How-to guides, troubleshooting notes, working memos, and reusable operating playbooks for buyers, farmers, tracking operators, creative teams, and everyone holding the daily mechanics of affiliate production together.\n\nThis is a library you return to not for inspiration, but to verify a step, restore setup logic, compare solutions, or keep tempo inside noisy daily operations.\n\nPeople come here not for generic advice, but for the exact next move."
);
if ($sectionKey !== 'playbooks') {
    $heroKicker = trim((string)($issue['issue_kicker'] ?? '')) !== '' ? (string)$issue['issue_kicker'] : $heroKicker;
    $heroTitle = trim((string)($issue['hero_title'] ?? '')) !== '' ? (string)$issue['hero_title'] : $heroTitle;
    $heroDescription = trim((string)($issue['hero_description'] ?? '')) !== '' ? (string)$issue['hero_description'] : $heroDescription;
    $issueTitle = trim((string)($issue['issue_title'] ?? '')) !== '' ? (string)$issue['issue_title'] : $issueTitle;
    $issueSubtitle = trim((string)($issue['issue_subtitle'] ?? '')) !== '' ? (string)$issue['issue_subtitle'] : $issueSubtitle;
}
?>
<style>
.jrnl{max-width:1240px;margin:0 auto;padding:28px 18px 64px;color:var(--shell-text)}
.jrnl-shell{display:grid;gap:24px}
.jrnl-hero,.jrnl-detail,.jrnl-card,.jrnl-related,.jrnl-empty{border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow)}
.jrnl-hero,.jrnl-detail,.jrnl-related,.jrnl-empty{padding:28px}
.jrnl-hero{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:16px}
.jrnl-kicker,.jrnl-tag,.jrnl-meta{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;max-height:30px;border:1px solid rgba(122,180,255,.2);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.jrnl-copy{display:grid;gap:8px}
.jrnl-copy h1,.jrnl-detail h1{margin:0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.048em}
.jrnl-copy h2{margin:8px 0 0;font:700 2rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.048em}
.jrnl-related h2{margin:0;font:700 1.5rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:-.048em}
.jrnl-copy p,.jrnl-detail p,.jrnl-card p{margin:0;color:var(--shell-muted);line-height:1.62}
.jrnl-copy p + p{margin-top:2px}
.jrnl-copy .jrnl-hero-description{max-width:66ch}
.jrnl-copy .jrnl-issue-subtitle{font-size:clamp(1.02rem,1.6vw,1.24rem);line-height:1.56;color:rgba(233,242,255,.9)}
.jrnl-cover{min-height:0;border:1px solid rgba(255,255,255,.08);background:radial-gradient(circle at 50% 22%,rgba(103,200,255,.16),transparent 26%),linear-gradient(180deg,rgba(6,11,20,.96),rgba(4,8,16,.92));display:block;align-self:start;overflow:hidden;position:relative}
.jrnl-cover img{position:relative;display:block;width:100%;height:auto;max-width:none;object-fit:contain;object-position:center center;padding:0;transform-origin:50% 0;animation:jrnlCoverIntro 1.15s cubic-bezier(.16,1,.3,1) both}
.jrnl-tags{display:flex;flex-wrap:wrap;gap:10px}
.jrnl-author{display:grid;grid-template-columns:52px minmax(0,1fr);gap:14px;align-items:start;padding:16px 18px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.035)}
.jrnl-author-mark{display:flex;align-items:center;justify-content:center;width:52px;height:52px;border:1px solid rgba(122,180,255,.24);background:linear-gradient(135deg,rgba(115,184,255,.16),rgba(39,223,192,.12));font:700 1rem/1 "Space Grotesk","Sora",sans-serif;color:var(--shell-text);text-transform:uppercase}
.jrnl-author-copy{display:grid;gap:4px;min-width:0}
.jrnl-author-top{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
.jrnl-author-name{margin:0;font:700 1rem/1.1 "Space Grotesk","Sora",sans-serif;color:var(--shell-text)}
.jrnl-author-handle,.jrnl-author-role{color:var(--shell-muted);font-size:12px;letter-spacing:.08em;text-transform:uppercase}
.jrnl-author-bio{margin:0;color:rgba(233,242,255,.88);line-height:1.58}
.jrnl-tag{color:var(--shell-muted);text-decoration:none}
.jrnl-tag.is-active,.jrnl-tag:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-topic-cloud{display:flex;flex-wrap:wrap;align-items:stretch;gap:10px 12px}
.jrnl-topic-pill{display:inline-flex;align-items:center;justify-content:flex-start;gap:8px;min-height:0;padding:8px 12px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:rgba(196,214,238,.78);text-decoration:none;flex:0 0 auto;font-size:calc(var(--topic-font,12) * 1px);font-weight:700;line-height:1.08;letter-spacing:0;white-space:nowrap}
.jrnl-topic-pill strong,.jrnl-topic-pill span{display:inline;color:inherit;font-size:1em;font-weight:inherit}
.jrnl-topic-pill span{opacity:.76;white-space:nowrap}
.jrnl-topic-pill.is-active,.jrnl-topic-pill:hover{color:var(--shell-text)}
.jrnl-topic-pill.is-all{opacity:.82}
.jrnl-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
.jrnl-card{display:grid;gap:14px;padding:16px;text-decoration:none;color:inherit;min-height:100%}
.jrnl-card-media{aspect-ratio:16/10;background:linear-gradient(135deg,rgba(115,184,255,.18),rgba(39,223,192,.12));border:1px solid rgba(255,255,255,.08);overflow:hidden}
.jrnl-card-media img{width:100%;height:100%;object-fit:cover;display:block}
.jrnl-card h3{margin:0;font:700 1.28rem/1.15 "Space Grotesk","Sora",sans-serif;letter-spacing:-.03em}
.jrnl-card-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;color:var(--shell-muted);font-size:12px;text-transform:uppercase;letter-spacing:.12em}
.jrnl-card-stats{display:inline-flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.jrnl-stat{display:inline-flex;align-items:center;gap:7px}
.jrnl-stat-icon{width:15px;height:15px;display:block;flex:0 0 15px;opacity:.86}
.jrnl-pager{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}
.jrnl-pager a,.jrnl-pager span{display:inline-flex;align-items:center;justify-content:center;min-width:44px;padding:10px 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-muted);text-decoration:none}
.jrnl-pager .is-active{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.12)}
.jrnl-detail{display:grid;gap:18px}
.jrnl-detail > *{order:100}
.jrnl-detail-top{order:10}
.jrnl-detail > .jrnl-author{order:20}
.jrnl-detail > .jrnl-kicker{order:30}
.jrnl-detail > h1{order:40}
.jrnl-detail > .jrnl-tags{order:50}
.jrnl-detail > .jrnl-detail-intro{order:60}
.jrnl-detail > .jrnl-detail-cover{order:70}
.jrnl-detail > .jrnl-share{order:80}
.jrnl-detail > .jrnl-content{order:90}
.jrnl-detail-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap}
.jrnl-detail-stats{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-left:auto}
.jrnl-stat-link{color:var(--shell-muted);text-decoration:none}
.jrnl-stat-link:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-breadcrumbs{display:flex;flex-wrap:wrap;gap:10px;align-items:center;color:rgba(214,235,255,.78);font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.jrnl-breadcrumbs a{color:rgba(214,235,255,.72);text-decoration:none}
.jrnl-detail .jrnl-author + .jrnl-detail-intro{margin-top:2px}
.jrnl-breadcrumbs a:hover{color:var(--shell-text)}
.jrnl-breadcrumb-sep{opacity:.42}
.jrnl-breadcrumb-current{color:var(--shell-text)}
.jrnl-detail-body{display:grid;gap:18px}
.jrnl-detail-cover{overflow:visible;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.02)}
.jrnl-detail-cover img{width:100%;height:auto;display:block;object-fit:contain}
.jrnl-detail-content{font-size:16px;line-height:1.82;color:var(--shell-text)}
.jrnl-detail-intro{display:grid;gap:0;margin-top:2px}
.jrnl-detail-intro p{margin:0 0 14px;color:rgba(224,236,255,.92);font-size:18px;line-height:1.86}
.jrnl-detail-content h1,.jrnl-detail-content h2,.jrnl-detail-content h3,.jrnl-detail-content h4{font-family:"Space Grotesk","Sora",sans-serif;color:var(--shell-text);line-height:1.18;letter-spacing:-.03em;margin:22px 0 12px}
.jrnl-detail-content p{margin:0 0 14px;line-height:1.82;color:rgba(221,233,250,.92)}
.jrnl-detail-content > p,.jrnl-detail-content > ul,.jrnl-detail-content > ol,.jrnl-detail-content > blockquote,.jrnl-detail-content > table,.jrnl-detail-content > pre,.jrnl-detail-content > hr,.jrnl-detail-content > figure,.jrnl-detail-content > img,.jrnl-detail-content > div,.jrnl-detail-content > section{padding:6px 28px;box-sizing:border-box}
.jrnl-detail-content > p:empty,.jrnl-detail-content > div:empty,.jrnl-detail-content > section:empty{display:none}
.jrnl-detail-content > ul li,.jrnl-detail-content > ol li{padding:0}
.jrnl-detail-content p strong{color:#f4f8ff}
.jrnl-detail-content p a,.jrnl-detail-content li a,.jrnl-detail-content blockquote a,.jrnl-detail-content td a,.jrnl-detail-content th a{color:#d6ebff;text-decoration:none;display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(122,180,255,.28);background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(122,180,255,.1));padding:1px 8px;font-weight:600;line-height:1.4;font-size:.95em;transition:color .18s ease,border-color .18s ease,background .18s ease,transform .18s ease;vertical-align:baseline}
.jrnl-detail-content p a::after,.jrnl-detail-content li a::after,.jrnl-detail-content blockquote a::after,.jrnl-detail-content td a::after,.jrnl-detail-content th a::after{content:"\2197";font-size:11px;opacity:.76;transform:translateY(-1px)}
.jrnl-detail-content p a:hover,.jrnl-detail-content li a:hover,.jrnl-detail-content blockquote a:hover,.jrnl-detail-content td a:hover,.jrnl-detail-content th a:hover{color:#fff;border-color:rgba(122,180,255,.52);background:linear-gradient(180deg,rgba(122,180,255,.18),rgba(39,223,192,.18));transform:translateY(-1px)}
.jrnl-detail-content .related-links a{text-transform:lowercase}
.jrnl-detail-content .related-links a::first-letter{text-transform:uppercase}
.jrnl-detail-content ul,.jrnl-detail-content ol{margin:0 0 14px;padding:0}
.jrnl-detail-content ul{padding-left:28px;list-style:none}
.jrnl-detail-content p + ul{padding-left:50px}
.jrnl-detail-content h2 + ul{padding-left:28px}
.jrnl-detail-content ul li,.jrnl-detail-content ol li{margin:0 0 8px;line-height:1.7;color:rgba(221,233,250,.92)}
.jrnl-detail-content ul li{position:relative;padding-left:22px}
.jrnl-detail-content ul li::before{content:"\2022";position:absolute;left:0;top:0;color:#78dfff;font-weight:700}
.jrnl-detail-content ol{padding-left:74px}
.jrnl-detail-content img{max-width:100%;height:auto;border-radius:12px;border:1px solid rgba(122,180,255,.18)}
.jrnl-detail-content blockquote{margin:0 0 14px;padding:12px 14px 12px 16px;border-left:3px solid rgba(122,180,255,.48);background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(122,180,255,.05));color:rgba(207,224,247,.92);border-radius:6px;position:relative}
.jrnl-detail-content blockquote::before{content:"\201C";position:absolute;left:8px;top:-4px;font-size:28px;color:rgba(122,180,255,.52);line-height:1}
.jrnl-detail-content table{width:100%;max-width:100%;display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;border-collapse:collapse;margin:12px 0 12px 28px;padding:5px;background:rgba(5,10,20,.88);border:1px solid rgba(122,180,255,.18)}
.jrnl-detail-content thead,.jrnl-detail-content tbody,.jrnl-detail-content tr{display:table;width:100%;table-layout:fixed}
.jrnl-detail-content th,.jrnl-detail-content td{border:1px solid rgba(122,180,255,.18);padding:8px 10px;vertical-align:top;min-width:140px;word-break:break-word;overflow-wrap:anywhere}
.jrnl-detail-content th{background:rgba(122,180,255,.1);color:#eef7ff;font-weight:700}
.jrnl-detail-content code{background:rgba(122,180,255,.12);color:#d6ebff;padding:2px 6px;border-radius:5px}
.jrnl-detail-content pre{margin:0 0 14px;padding:12px;background:#0a1527;color:#dbeaff;overflow-x:auto;border-radius:8px;border:1px solid rgba(122,180,255,.14)}
.jrnl-detail-content hr{border:0;border-top:1px solid rgba(122,180,255,.16);margin:18px 0}
.jrnl-actions{display:flex;gap:12px;flex-wrap:wrap}
.jrnl-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 16px;border:1px solid rgba(122,180,255,.18);background:linear-gradient(135deg,rgba(115,184,255,.22),rgba(39,223,192,.18));color:var(--shell-text);text-decoration:none;font-weight:700}
.jrnl-share{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
.jrnl-share a{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-width:46px;height:46px;padding:0 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-muted);text-decoration:none}
.jrnl-share a:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-share svg{width:18px;height:18px;display:block;flex:0 0 18px}
.jrnl-share-label{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.jrnl-favorite-form{display:inline-flex}
.jrnl-favorite-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-width:46px;height:46px;padding:0 14px;border:1px solid rgba(122,180,255,.18);background:rgba(255,255,255,.04);color:var(--shell-muted);text-decoration:none;font:inherit;cursor:pointer}
.jrnl-favorite-btn:hover{color:var(--shell-text);border-color:rgba(122,180,255,.38);background:rgba(122,180,255,.1)}
.jrnl-favorite-btn.is-active{color:#f4d56b;border-color:rgba(244,213,107,.34);background:rgba(244,213,107,.12)}
.jrnl-favorite-btn svg{width:18px;height:18px;display:block;flex:0 0 18px}
.jrnl-related-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.jrnl-empty{text-align:center}
@keyframes jrnlCoverIntro{
0%{opacity:0;transform:translateY(28px) scale(.965);filter:blur(14px) saturate(.86)}
55%{opacity:1;transform:translateY(0) scale(1.006);filter:blur(0) saturate(1)}
100%{opacity:1;transform:translateY(0) scale(1);filter:blur(0) saturate(1)}
}
@media (max-width:1180px){.jrnl-hero,.jrnl-grid,.jrnl-related-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.jrnl-hero{grid-template-columns:1fr}}
@media (max-width:720px){.jrnl{padding:18px 14px 52px}.jrnl-hero{padding:0 0 18px;overflow:hidden}.jrnl-cover{order:-1;border-top:0;border-left:0;border-right:0}.jrnl-copy{padding:0 18px}.jrnl-grid,.jrnl-related-grid{grid-template-columns:1fr}.jrnl-topic-pill{flex-basis:auto}.jrnl-author{grid-template-columns:1fr;padding:14px}.jrnl-author-mark{width:44px;height:44px}.jrnl-author-top{gap:8px}}
</style>

<section class="jrnl">
    <div class="jrnl-shell">
        <?php if ($selected): ?>
            <?php
            $selectedContentHtml = (string)($selected['content_html'] ?? '');
            $selectedSplit = $splitContentByFirstH2($selectedContentHtml);
            $selectedIntroHtml = $stripChecklistBrackets((string)($selectedSplit['intro'] ?? ''));
            $selectedBodyHtml = $stripChecklistBrackets((string)($selectedSplit['body'] ?? $selectedContentHtml));
            $selectedExcerptHtml = $stripChecklistBrackets(trim((string)($selected['excerpt_html'] ?? '')));
            if ($selectedIntroHtml === '' && $selectedExcerptHtml !== '') {
                $selectedIntroHtml = $selectedExcerptHtml;
            }
            ?>
            <article class="jrnl-detail">
                <div class="jrnl-detail-top">
                    <?php if (!empty($detailBreadcrumbs)): ?>
                        <nav class="jrnl-breadcrumbs" aria-label="<?= htmlspecialchars($t('Хлебные крошки', 'Breadcrumbs'), ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ($detailBreadcrumbs as $index => $crumb): ?>
                                <?php if ($index > 0): ?><span class="jrnl-breadcrumb-sep">/</span><?php endif; ?>
                                <?php if (!empty($crumb['url'])): ?>
                                    <a href="<?= htmlspecialchars((string)$crumb['url'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$crumb['label'], ENT_QUOTES, 'UTF-8') ?></a>
                                <?php else: ?>
                                    <span class="jrnl-breadcrumb-current"><?= htmlspecialchars((string)$crumb['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                    <div class="jrnl-detail-stats">
                        <span class="jrnl-meta jrnl-stat"><?= $statIcon('eye') ?><?= (int)($selected['view_count'] ?? 0) ?></span>
                        <a class="jrnl-meta jrnl-stat jrnl-stat-link" href="#article-comments"><?= $statIcon('comments') ?><?= (int)$portalCommentTotal ?></a>
                    </div>
                </div>
                <span class="jrnl-kicker"><?= htmlspecialchars((string)($issue['issue_title'] ?? $issueTitle), ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="jrnl-tags">
                    <?php if ($authorDisplayName !== ''): ?>
                        <span class="jrnl-meta"><?= htmlspecialchars($authorDisplayName, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($selectedClusterCode !== '' && $selectedClusterLabel !== ''): ?>
                        <a class="jrnl-tag" href="<?= htmlspecialchars($buildPageUrl($selectedClusterCode), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($selectedClusterLabel, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                    <span class="jrnl-meta"><?= htmlspecialchars((string)($selected['published_at'] ?? $selected['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php if ($selectedIntroHtml !== ''): ?>
                    <div class="jrnl-detail-intro"><?= $selectedIntroHtml ?></div>
                <?php endif; ?>
                <?php if (!empty($selected['hero_image_src'])): ?>
                    <div class="jrnl-detail-cover">
                        <img src="<?= htmlspecialchars((string)$selected['hero_image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($selected['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                <?php endif; ?>
                <?php if ($selectedShareUrl !== ''): ?>
                    <div class="jrnl-share">
                        <a href="https://t.me/share/url?url=<?= rawurlencode($selectedShareUrl) ?>&text=<?= rawurlencode($selectedShareTitle) ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
                            <?= $shareIcon('telegram') ?>
                            <span class="jrnl-share-label">Telegram</span>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($selectedShareUrl) ?>&text=<?= rawurlencode($selectedShareTitle) ?>" target="_blank" rel="noopener noreferrer" aria-label="X">
                            <?= $shareIcon('x') ?>
                            <span class="jrnl-share-label">X</span>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($selectedShareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <?= $shareIcon('facebook') ?>
                            <span class="jrnl-share-label">Facebook</span>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($selectedShareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                            <?= $shareIcon('linkedin') ?>
                            <span class="jrnl-share-label">LinkedIn</span>
                        </a>
                        <a href="https://wa.me/?text=<?= rawurlencode($selectedShareTitle . ' ' . $selectedShareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                            <?= $shareIcon('whatsapp') ?>
                            <span class="jrnl-share-label">WhatsApp</span>
                        </a>
                        <?php if ($portalUser && !empty($selected['id'])): ?>
                            <form class="jrnl-favorite-form" method="post" data-favorite-active-label="<?= htmlspecialchars($t('Р’ РёР·Р±СЂР°РЅРЅРѕРј', 'Saved'), ENT_QUOTES, 'UTF-8') ?>" data-favorite-idle-label="<?= htmlspecialchars($t('Р”РѕР±Р°РІРёС‚СЊ РІ РёР·Р±СЂР°РЅРЅРѕРµ', 'Save to favorites'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="public_portal_favorite_toggle">
                                <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="return_path" value="<?= htmlspecialchars((string)($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="content_type" value="examples">
                                <input type="hidden" name="content_id" value="<?= (int)$selected['id'] ?>">
                                <button class="jrnl-favorite-btn <?= $portalIsFavorite ? 'is-active' : '' ?>" type="submit" aria-label="<?= htmlspecialchars($t('Р”РѕР±Р°РІРёС‚СЊ РІ РёР·Р±СЂР°РЅРЅРѕРµ', 'Save to favorites'), ENT_QUOTES, 'UTF-8') ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3.4l2.63 5.34 5.9.86-4.27 4.16 1 5.87L12 16.88 6.74 19.63l1-5.87L3.47 9.6l5.9-.86L12 3.4Z"/></svg>
                                    <span class="jrnl-share-label"><?= htmlspecialchars($portalIsFavorite ? $t('Р’ РёР·Р±СЂР°РЅРЅРѕРј', 'Saved') : $t('Р”РѕР±Р°РІРёС‚СЊ РІ РёР·Р±СЂР°РЅРЅРѕРµ', 'Save to favorites'), ENT_QUOTES, 'UTF-8') ?></span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="jrnl-detail-body">
                    <div class="jrnl-detail-content"><?= $selectedBodyHtml ?></div>
                    <div class="jrnl-actions">
                        <a class="jrnl-btn" href="<?= htmlspecialchars($buildPageUrl($currentCluster), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('РќР°Р·Р°Рґ РІ СЂР°Р·РґРµР»', 'Back to section'), ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                </div>
            </article>

            <?php
            $commentsPartial = DIR . 'core/views/partials/article_comments.php';
            if (is_file($commentsPartial)) {
                include $commentsPartial;
            }
            ?>

            

            <?php if (!empty($relatedItems)): ?>
                <section class="jrnl-related">
                    <h2><?= htmlspecialchars($t('Р”Р°Р»СЊС€Рµ РїРѕ С‚РµРјРµ', 'Continue reading'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="jrnl-related-grid">
                        <?php foreach ($relatedItems as $item): ?>
                            <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                            <a class="jrnl-card" href="<?= htmlspecialchars($buildArticleUrl((string)($item['slug'] ?? ''), $cluster), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="jrnl-card-media"><?php if (!empty($item['image_src'])): ?><img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                                <span class="jrnl-tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('РњР°С‚РµСЂРёР°Р»', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($strip((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 140), ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="jrnl-card-foot">
                                    <span class="jrnl-card-stats">
                                        <span class="jrnl-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                        <span class="jrnl-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php else: ?>
            <header class="jrnl-hero">
                <div class="jrnl-copy">
                    <span class="jrnl-kicker"><?= htmlspecialchars($heroKicker, ENT_QUOTES, 'UTF-8') ?></span>
                    <h1><?= htmlspecialchars($heroTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                    <?= $renderIssueText($heroDescription, 'jrnl-hero-description') ?>
                    <h2><?= htmlspecialchars($issueTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                    <?= $renderIssueText($issueSubtitle, 'jrnl-issue-subtitle') ?>
                </div>
                <div class="jrnl-cover">
                    <?php if ($issueImage !== ''): ?><img src="<?= htmlspecialchars($issueImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($issueTitle, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
                </div>
            </header>

            <?php if (!empty($topicCloudItems)): ?>
                <div class="jrnl-topic-cloud" aria-label="<?= htmlspecialchars($t('РўРµРјС‹ РІС‹РїСѓСЃРєР°', 'Issue topics'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($topicCloudItems as $topicItem): ?>
                        <?php $topicCode = trim((string)($topicItem['code'] ?? '')); ?>
                        <?php $isAllTopic = !empty($topicItem['is_all']); ?>
                        <a
                            class="jrnl-topic-pill <?= $currentCluster === $topicCode ? 'is-active' : '' ?> <?= $isAllTopic ? 'is-all' : '' ?>"
                            href="<?= htmlspecialchars($buildPageUrl($topicCode), ENT_QUOTES, 'UTF-8') ?>"
                            style="--topic-basis:<?= (int)($topicItem['basis'] ?? ($isAllTopic ? 18 : 16)) ?>;--topic-grow:<?= htmlspecialchars((string)($topicItem['grow'] ?? ($isAllTopic ? '1.1' : '1')), ENT_QUOTES, 'UTF-8') ?>;--topic-font:<?= (int)($topicItem['font'] ?? 12) ?>"
                        >
                            <strong><?= htmlspecialchars((string)($topicItem['label'] ?? $topicCode), ENT_QUOTES, 'UTF-8') ?></strong><span><?= ' (' . (int)($topicItem['count'] ?? 0) . ')' ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (false && !empty($clusters)): ?>
                <div class="jrnl-tags" aria-label="<?= htmlspecialchars($t('РўРµРјС‹ СЂР°Р·РґРµР»Р°', 'Section topics'), ENT_QUOTES, 'UTF-8') ?>">
                    <a class="jrnl-tag <?= $currentCluster === '' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($buildPageUrl(''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Р’СЃРµ С‚РµРјС‹', 'All topics'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php foreach ($clusters as $cluster): ?>
                        <?php $clusterCode = trim((string)($cluster['code'] ?? '')); ?>
                        <?php if ($clusterCode === '') { continue; } ?>
                        <a class="jrnl-tag <?= $currentCluster === $clusterCode ? 'is-active' : '' ?>" href="<?= htmlspecialchars($buildPageUrl($clusterCode), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string)($cluster['label'] ?? $clusterCode), ENT_QUOTES, 'UTF-8') ?>
                            <?php if (isset($cluster['count'])): ?> В· <?= (int)$cluster['count'] ?><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($items)): ?>
                <div class="jrnl-grid">
                    <?php foreach ($items as $item): ?>
                        <?php $cluster = trim((string)($item['cluster_code'] ?? '')); ?>
                        <a class="jrnl-card" href="<?= htmlspecialchars($buildArticleUrl((string)($item['slug'] ?? ''), $cluster), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="jrnl-card-media"><?php if (!empty($item['image_src'])): ?><img src="<?= htmlspecialchars((string)$item['image_src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                            <span class="jrnl-tag"><?= htmlspecialchars($cluster !== '' ? $cluster : $t('РњР°С‚РµСЂРёР°Р»', 'Article'), ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($strip((string)($item['excerpt_html'] ?? $item['content_html'] ?? ''), 160), ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="jrnl-card-foot">
                                <span><?= htmlspecialchars((string)($item['published_at'] ?? $item['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="jrnl-card-stats">
                                    <span class="jrnl-stat"><?= $statIcon('eye') ?><?= (int)($item['view_count'] ?? 0) ?></span>
                                    <span class="jrnl-stat"><?= $statIcon('comments') ?><?= (int)($item['comment_count'] ?? 0) ?></span>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="jrnl-pager" aria-label="<?= htmlspecialchars($t('РџР°РіРёРЅР°С†РёСЏ', 'Pagination'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($page > 1): ?>
                            <a href="<?= htmlspecialchars($buildPageUrl($currentCluster, $page - 1), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('РќР°Р·Р°Рґ', 'Prev'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i < $page - 2 || $i > $page + 2) { continue; } ?>
                            <?php if ($i === $page): ?>
                                <span class="is-active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($buildPageUrl($currentCluster, $i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= htmlspecialchars($buildPageUrl($currentCluster, $page + 1), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Р’РїРµСЂРµРґ', 'Next'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="jrnl-empty">
                    <h2><?= htmlspecialchars($t('РџРѕРєР° РЅРµС‚ РјР°С‚РµСЂРёР°Р»РѕРІ', 'No playbook materials yet'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($t('РћРїСѓР±Р»РёРєРѕРІР°РЅРЅС‹Рµ РјР°С‚РµСЂРёР°Р»С‹ РїРѕСЏРІСЏС‚СЃСЏ Р·РґРµСЃСЊ РєР°Рє РѕС‚РґРµР»СЊРЅС‹Р№ СЂР°Р·РґРµР» РїСЂР°РєС‚РёРєРё СЃ С„РёР»СЊС‚СЂР°С†РёРµР№ РїРѕ С‚РµРјР°Рј.', 'Published pieces will appear here as a dedicated practical section with topic filters.'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
