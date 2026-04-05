<?php
if (!isset($ModelPage) || !is_array($ModelPage)) {
    $ModelPage = [];
}

$casesData = [
    'host' => strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '')),
    'lang' => 'en',
    'total' => 0,
    'items' => [],
    'selected' => null,
    'selected_code' => '',
    'error' => '',
];

if (strpos((string)$casesData['host'], ':') !== false) {
    $casesData['host'] = explode(':', (string)$casesData['host'], 2)[0];
}

if (!function_exists('public_cases_ensure_schema')) {
    $casesData['error'] = 'public_cases module not found';
    $ModelPage['cases_catalog'] = $casesData;
    return;
}

$db = $FRMWRK->DB();
if (!$db || !public_cases_ensure_schema($db)) {
    $casesData['error'] = 'public_cases table is missing';
    $ModelPage['cases_catalog'] = $casesData;
    return;
}

$casesData['lang'] = public_cases_resolve_lang((string)$casesData['host']);
if (function_exists('public_cases_sync_from_projects')) {
    public_cases_sync_from_projects(
        $FRMWRK,
        (string)$casesData['host'],
        (string)$casesData['lang']
    );
}
$selectedCode = trim((string)($_GET['code'] ?? ''));
if ($selectedCode === '') {
    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? trim($requestPath) : '';
    $segments = array_values(array_filter(explode('/', (string)$requestPath), static function ($value): bool {
        return $value !== '';
    }));
    if (isset($segments[0]) && strtolower((string)$segments[0]) === 'cases' && isset($segments[1])) {
        $selectedCode = trim((string)$segments[1]);
    }
}
if ($selectedCode !== '') {
    $selectedCode = public_cases_slugify($selectedCode);
}

$truncateText = static function (string $value, int $max = 220): string {
    $value = trim((string)preg_replace('/\s+/u', ' ', strip_tags($value)));
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value, 'UTF-8') > $max) {
            return rtrim((string)mb_substr($value, 0, $max - 1, 'UTF-8')) . '...';
        }
        return $value;
    }
    if (strlen($value) > $max) {
        return rtrim(substr($value, 0, $max - 1)) . '...';
    }
    return $value;
};

$projectToCase = static function (array $project) use ($truncateText): array {
    $url = trim((string)($project['project_url'] ?? ''));
    $host = $url !== '' ? (string)parse_url($url, PHP_URL_HOST) : '';
    if ($host === '') {
        $host = preg_replace('~^https?://~i', '', $url);
        $host = trim((string)strtok($host, '/'));
    }
    $host = trim((string)$host);
    $challengeHtml = (string)($project['challenge_html'] ?? '');
    $impactHtml = (string)($project['impact_html'] ?? '');
    $solutionHtml = (string)($project['solution_html'] ?? '');

    return [
        'id' => (int)($project['id'] ?? 0),
        'domain_host' => (string)($project['domain_host'] ?? ''),
        'lang_code' => (string)($project['lang_code'] ?? 'en'),
        'title' => (string)($project['title'] ?? ''),
        'slug' => (string)($project['slug'] ?? ''),
        'symbolic_code' => (string)($project['symbolic_code'] ?? ''),
        'client_name' => $host,
        'industry_summary' => (string)($project['industry_summary'] ?? ''),
        'period_summary' => (string)($project['period_summary'] ?? ''),
        'role_summary' => (string)($project['role_summary'] ?? ''),
        'stack_summary' => (string)($project['stack_summary'] ?? ''),
        'problem_summary' => (string)$truncateText($challengeHtml, 160),
        'result_summary' => (string)($project['result_summary'] ?? ''),
        'seo_title' => (string)($project['title'] ?? ''),
        'seo_description' => (string)$truncateText((string)($project['excerpt_html'] ?? $project['result_summary'] ?? ''), 220),
        'excerpt_html' => (string)($project['excerpt_html'] ?? ''),
        'challenge_html' => $challengeHtml,
        'solution_html' => $solutionHtml,
        'architecture_html' => $truncateText((string)($project['stack_summary'] ?? ''), 220),
        'results_html' => $impactHtml,
        'metrics_html' => (string)($project['metrics_html'] ?? ''),
        'deliverables_html' => (string)($project['deliverables_html'] ?? ''),
        'sort_order' => (int)($project['sort_order'] ?? 100),
        'is_published' => (int)($project['is_published'] ?? 1),
        'created_at' => (string)($project['created_at'] ?? ''),
        'updated_at' => (string)($project['updated_at'] ?? ''),
        'source_kind' => 'project',
    ];
};

$nativeCases = public_cases_fetch_published(
    $FRMWRK,
    (string)$casesData['host'],
    (string)$casesData['lang']
);
$casesData['items'] = is_array($nativeCases) ? $nativeCases : [];

$enrichSelectedCaseNarrative = static function (array $case, bool $isRu): array {
    $title = trim((string)($case['title'] ?? ''));
    $problem = trim((string)($case['problem_summary'] ?? ''));
    $result = trim((string)($case['result_summary'] ?? ''));
    $stack = trim((string)($case['stack_summary'] ?? ''));
    $industry = trim((string)($case['industry_summary'] ?? ''));
    $role = trim((string)($case['role_summary'] ?? ''));

    $append = static function (string $html, string $extra): string {
        $html = trim($html);
        $extra = trim($extra);
        if ($extra === '') {
            return $html;
        }
        if ($html === '') {
            return '<p>' . $extra . '</p>';
        }
        return $html . "\n" . '<p>' . $extra . '</p>';
    };
    $appendList = static function (string $html, array $items): string {
        $clean = [];
        foreach ($items as $it) {
            $t = trim((string)$it);
            if ($t !== '') {
                $clean[] = $t;
            }
        }
        if (empty($clean)) {
            return $html;
        }
        $list = '<ul>';
        foreach ($clean as $it) {
            $list .= '<li>' . htmlspecialchars($it, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $list .= '</ul>';
        return trim($html) . "\n" . $list;
    };

    if ($isRu) {
        $challengeExtra = 'Когда я подключился к проекту "' . ($title !== '' ? $title : 'этому кейсу') . '", картина была типичной для перегруженных систем: локальные решения уже существовали, но между бизнес-целью и техническим исполнением не было общей модели. Это приводило к повторяющимся инцидентам и росту ручной операционки.';
        $challengeExtra2 = 'Я отдельно разложил проблему на управляемые слои: входящие данные, правила обработки, точки принятия решений и контроль качества после релиза. Такой подход быстро показал, где именно теряется эффективность и почему прежние попытки стабилизации давали только краткосрочный эффект.';

        $solutionExtra = 'Вместо точечного "ремонта" я собрал последовательный сценарий внедрения: сначала зафиксировал критерии приемки, затем реализовал минимальное рабочее ядро и только после стабилизации перешел к расширению охвата. За счет этого команда получала измеримый прогресс на каждом этапе.';
        $solutionExtra2 = 'Особое внимание уделил эксплуатационному контуру: кто отвечает за качество, как фиксируются отклонения, где проходит граница между автоматикой и ручной валидацией. Именно этот слой сделал решение повторяемым и пригодным для масштабирования.';

        $architectureExtra = 'Архитектурно я опирался на принцип "сначала наблюдаемость, потом усложнение логики". Это позволило видеть влияние изменений в реальном времени и не терять управляемость при росте нагрузки.';
        $architectureExtra2 = 'Технологический стек (' . ($stack !== '' ? $stack : 'прикладной backend + интеграции') . ') использовался не как самоцель, а как средство контролируемой эволюции: каждое решение оценивалось по влиянию на скорость изменений, стабильность и стоимость сопровождения.';

        $resultsExtra = 'На уровне бизнеса это дало не только локальное улучшение метрик, но и понятную модель развития: стало ясно, какие действия действительно двигают проект, а какие создают шум. Команда начала принимать решения быстрее и с меньшим риском регрессий.';
        $resultsExtra2 = 'Я фиксировал результат в формате "до/после" и привязывал изменения к практическим KPI, чтобы у руководителя была прозрачная связь между инженерными шагами и коммерческим эффектом.';

        $metricsItems = [
            'Скорость реакции команды на отклонения и инциденты.',
            'Доля ручных операций до и после внедрения.',
            'Стабильность ключевого пользовательского сценария под нагрузкой.',
            'Предсказуемость релизов и число регрессий.',
            'Качество входящего потока: меньше шума, выше полезный результат.',
        ];

        $deliverablesItems = [
            'Архитектурная схема целевого контура с приоритетами внедрения.',
            'Пошаговый план rollout с критериями приемки по этапам.',
            'Регламент эксплуатации и эскалаций для команды.',
            'Набор практических чеклистов контроля качества после релиза.',
            'Список следующих итераций для роста эффекта в горизонте 30/60 дней.',
        ];
    } else {
        $challengeExtra = 'When I joined "' . ($title !== '' ? $title : 'this case') . '", the pattern was familiar: local fixes existed, but there was no shared model connecting business goals to technical execution. That gap kept incidents recurring and manual overhead growing.';
        $challengeExtra2 = 'I decomposed the issue into controllable layers: input signals, decision rules, handoff points and post-release quality control. This immediately clarified where performance was being lost and why previous fixes did not hold.';

        $solutionExtra = 'Instead of patching symptoms, I implemented a phased model: acceptance criteria first, minimum viable core second, and scale expansion only after stability was proven. This created measurable progress at each stage.';
        $solutionExtra2 = 'Operational governance was part of the implementation itself: ownership boundaries, deviation handling and explicit escalation logic. That made the outcome repeatable rather than person-dependent.';

        $architectureExtra = 'Architecturally, the key principle was "observability before complexity". It allowed the team to see real impact of each change and keep control while scaling.';
        $architectureExtra2 = 'The stack (' . ($stack !== '' ? $stack : 'application backend + integrations') . ') was treated as an enabler, not a goal: every decision was evaluated by impact on delivery speed, stability and support cost.';

        $resultsExtra = 'Business impact was not limited to isolated metric gains. The team received a practical operating model with clearer priorities, faster decisions and lower regression risk.';
        $resultsExtra2 = 'I documented outcomes in a before/after format tied to practical KPIs, so leadership could directly map engineering work to commercial value.';

        $metricsItems = [
            'Team response speed to deviations and incidents.',
            'Manual overhead share before vs after rollout.',
            'Stability of critical user flow under load.',
            'Release predictability and regression frequency.',
            'Input quality: less noise, higher useful outcome.',
        ];

        $deliverablesItems = [
            'Target architecture map with implementation priorities.',
            'Phased rollout plan with acceptance criteria.',
            'Operational runbook and escalation model.',
            'Post-release quality checklists.',
            '30/60-day optimization backlog.',
        ];
    }

    $case['challenge_html'] = $append((string)($case['challenge_html'] ?? ''), $challengeExtra);
    $case['challenge_html'] = $append((string)$case['challenge_html'], $challengeExtra2);

    $case['solution_html'] = $append((string)($case['solution_html'] ?? ''), $solutionExtra);
    $case['solution_html'] = $append((string)$case['solution_html'], $solutionExtra2);

    $case['architecture_html'] = $append((string)($case['architecture_html'] ?? ''), $architectureExtra);
    $case['architecture_html'] = $append((string)$case['architecture_html'], $architectureExtra2);

    $case['results_html'] = $append((string)($case['results_html'] ?? ''), $resultsExtra);
    $case['results_html'] = $append((string)$case['results_html'], $resultsExtra2);

    $case['metrics_html'] = $appendList((string)($case['metrics_html'] ?? ''), $metricsItems);
    $case['deliverables_html'] = $appendList((string)($case['deliverables_html'] ?? ''), $deliverablesItems);

    if ($isRu) {
        $case['excerpt_html'] = (string)($case['excerpt_html'] ?? '') . '<p>В этом кейсе я показываю не только итог, но и логику инженерных решений: почему был выбран именно такой путь, как снижались риски и как решение переводилось в устойчивую эксплуатацию.</p>';
    } else {
        $case['excerpt_html'] = (string)($case['excerpt_html'] ?? '') . '<p>This case explains not only the final outcome, but also the engineering logic behind decisions, risk handling and operationalization.</p>';
    }

    return $case;
};

if ($selectedCode !== '') {
    $casesData['selected'] = public_cases_fetch_published_by_code(
        $FRMWRK,
        (string)$casesData['host'],
        $selectedCode,
        (string)$casesData['lang']
    );
    $casesData['selected_code'] = $selectedCode;
}

if (function_exists('public_projects_fetch_published')) {
    $projectRows = public_projects_fetch_published($FRMWRK, (string)$casesData['host'], (string)$casesData['lang']);
    if (is_array($projectRows) && !empty($projectRows)) {
        $mappedProjectCases = [];
        foreach ($projectRows as $projectRow) {
            if (!is_array($projectRow)) {
                continue;
            }
            $mappedProjectCases[] = $projectToCase($projectRow);
        }

        if (!empty($mappedProjectCases)) {
            $existingCodes = [];
            foreach ($casesData['items'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $itemCode = trim((string)($item['symbolic_code'] ?? $item['slug'] ?? ''));
                if ($itemCode !== '') {
                    $existingCodes[strtolower($itemCode)] = true;
                }
            }
            foreach ($mappedProjectCases as $mappedItem) {
                $mappedCode = trim((string)($mappedItem['symbolic_code'] ?? $mappedItem['slug'] ?? ''));
                $mappedKey = strtolower($mappedCode);
                if ($mappedKey !== '' && isset($existingCodes[$mappedKey])) {
                    continue;
                }
                if ($mappedKey !== '') {
                    $existingCodes[$mappedKey] = true;
                }
                $casesData['items'][] = $mappedItem;
            }
        }

        if ($selectedCode !== '' && !is_array($casesData['selected'])) {
            $selectedProject = public_projects_fetch_published_by_code(
                $FRMWRK,
                (string)$casesData['host'],
                $selectedCode,
                (string)$casesData['lang']
            );
            if (is_array($selectedProject)) {
                $casesData['selected'] = $projectToCase($selectedProject);
            }
        }
    }
}

if (is_array($casesData['selected'])) {
    $casesData['selected'] = $enrichSelectedCaseNarrative((array)$casesData['selected'], ((string)$casesData['lang'] === 'ru'));
}

$casesData['total'] = count((array)$casesData['items']);

$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = preg_replace('/^www\./', '', (string)$casesData['host']);
$baseUrl = $scheme . '://' . ($host !== '' ? $host : 'localhost');
$isRu = ((string)$casesData['lang'] === 'ru');
$selectedCase = is_array($casesData['selected'] ?? null) ? $casesData['selected'] : null;

if ($selectedCase) {
    $caseTitle = trim((string)($selectedCase['seo_title'] ?? $selectedCase['title'] ?? ''));
    if ($caseTitle === '') {
        $caseTitle = $isRu ? 'Кейс' : 'Case Study';
    }
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $caseTitle;
    }
    if (empty($ModelPage['description'])) {
        $desc = trim((string)($selectedCase['seo_description'] ?? ''));
        if ($desc === '') {
            $desc = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($selectedCase['excerpt_html'] ?? $selectedCase['result_summary'] ?? ''))));
        }
        if ($desc === '') {
            $desc = $isRu
                ? 'Кейс с разбором задачи, системного подхода, архитектуры решения и измеримого бизнес-результата.'
                : 'Case study with problem framing, systems approach, architecture and measurable business outcome.';
        }
        $ModelPage['description'] = $desc;
    }
    if (empty($ModelPage['keywords'])) {
        $ModelPage['keywords'] = $isRu
            ? 'кейс внедрения, системный подход, архитектура решения, интеграции backend, бизнес результат'
            : 'delivery case study, systems approach, solution architecture, backend integrations, business impact';
    }
    if (empty($ModelPage['canonical'])) {
        $code = trim((string)($selectedCase['symbolic_code'] ?? $selectedCase['slug'] ?? ''));
        $ModelPage['canonical'] = $baseUrl . '/cases/' . rawurlencode($code) . '/';
    }
} else {
    if (empty($ModelPage['title'])) {
        $ModelPage['title'] = $isRu
            ? 'Кейсы по продуктам и разработке: задачи, решения и рост метрик'
            : 'Case studies by product and engineering teams: problems, solutions and measurable growth';
    }
    if (empty($ModelPage['description'])) {
        $ModelPage['description'] = $isRu
            ? 'Кейсы внедрений: постановка задачи, системный подход, архитектура, интеграции и результат в цифрах.'
            : 'Delivery case studies with problem framing, systems approach, architecture, integrations and measurable results.';
    }
    if (empty($ModelPage['keywords'])) {
        $ModelPage['keywords'] = $isRu
            ? 'кейсы разработки, кейсы внедрения, архитектура решений, интеграции API, измеримый результат'
            : 'engineering case studies, implementation cases, solution architecture, API integrations, measurable outcomes';
    }
    if (empty($ModelPage['canonical'])) {
        $ModelPage['canonical'] = $baseUrl . '/cases/';
    }
}

$ModelPage['cases_catalog'] = $casesData;
