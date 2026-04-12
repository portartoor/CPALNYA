<?php

if (!function_exists('cpalnya_author_profiles')) {
    function cpalnya_author_profiles(): array
    {
        return [
            [
                'nickname' => 'sleeper.ops',
                'display_name' => 'Илья "sleeper.ops" Воронцов',
                'role_ru' => 'Операционка, handoff, запуск',
                'role_en' => 'Ops, handoff, launch',
                'bio_ru' => 'Собирает хаос запуска в рабочие SOP. Любит, когда у команды есть не героизм, а нормальная операционная память.',
                'bio_en' => 'Turns launch chaos into working SOPs. Prefers operational memory over heroics.',
            ],
            [
                'nickname' => 'mila.triage',
                'display_name' => 'Мила "mila.triage" Седова',
                'role_ru' => 'Трафик, triage, source mix',
                'role_en' => 'Traffic, triage, source mix',
                'bio_ru' => 'Смотрит на связки как на систему симптомов. Пишет про то, где трафик начинает врать и как это быстро ловить.',
                'bio_en' => 'Reads setups as symptom systems. Writes about where traffic starts lying and how to catch it early.',
            ],
            [
                'nickname' => 'ghost.creo',
                'display_name' => 'Лев "ghost.creo" Аникеев',
                'role_ru' => 'Креативы, fatigue, review loop',
                'role_en' => 'Creatives, fatigue, review loop',
                'bio_ru' => 'Разбирает креативы без романтики. Интересуется не вдохновением, а тем, что держит CTR, trust и повторяемость.',
                'bio_en' => 'Breaks down creatives without romance. Cares about what holds CTR, trust and repeatability.',
            ],
            [
                'nickname' => 'route.zero',
                'display_name' => 'Артур "route.zero" Мельник',
                'role_ru' => 'Роутинг, отказоустойчивость, клоакинг',
                'role_en' => 'Routing, resilience, cloaking',
                'bio_ru' => 'Пишет про маршрутизацию, резервные контуры и те failure modes, которые обычно вспоминают уже после падения.',
                'bio_en' => 'Writes about routing, fallback contours and the failure modes teams remember only after things break.',
            ],
            [
                'nickname' => 'daria.signal',
                'display_name' => 'Дарья "daria.signal" Нечаева',
                'role_ru' => 'Сигналы рынка, policy, регуляторка',
                'role_en' => 'Market signals, policy, regulation',
                'bio_ru' => 'Переводит новости, policy-апдейты и регуляторный шум в язык операционных последствий для affiliate-команд.',
                'bio_en' => 'Translates news, policy updates and regulatory noise into operational consequences for affiliate teams.',
            ],
            [
                'nickname' => 'farmkeeper',
                'display_name' => 'Олег "farmkeeper" Жуков',
                'role_ru' => 'Фарм, trust, account hygiene',
                'role_en' => 'Farm, trust, account hygiene',
                'bio_ru' => 'Любит стабильность больше, чем скорость. Разбирает farm-экономику, trust и дисциплину, без которой все разваливается.',
                'bio_en' => 'Values stability over speed. Covers farm economics, trust and the discipline that keeps everything from collapsing.',
            ],
            [
                'nickname' => 'postback.witch',
                'display_name' => 'Ася "postback.witch" Королёва',
                'role_ru' => 'Трекинг, постбеки, атрибуция',
                'role_en' => 'Tracking, postbacks, attribution',
                'bio_ru' => 'Специалист по местам, где цифры расходятся с реальностью. Любит ковырять постбеки, макросы и грязную атрибуцию.',
                'bio_en' => 'Specializes in places where numbers diverge from reality. Likes tearing into postbacks, macros and dirty attribution.',
            ],
            [
                'nickname' => 'bm.afterdark',
                'display_name' => 'Роман "bm.afterdark" Климов',
                'role_ru' => 'BM, модерация, trust recovery',
                'role_en' => 'BM, moderation, trust recovery',
                'bio_ru' => 'Пишет о том, как переживать банвейвы и не превращать recovery в шаманский ритуал без логики.',
                'bio_en' => 'Writes about surviving banwaves without turning recovery into a ritual with no logic.',
            ],
            [
                'nickname' => 'nora.memo',
                'display_name' => 'Нора "nora.memo" Лебедева',
                'role_ru' => 'Редакция, decision memo, разборы',
                'role_en' => 'Editorial, decision memo, breakdowns',
                'bio_ru' => 'Собирает материалы так, чтобы между кейсом, выводом и действием не было пустой риторики.',
                'bio_en' => 'Builds articles so there is no empty rhetoric between case, conclusion and action.',
            ],
            [
                'nickname' => 'void.audit',
                'display_name' => 'Сергей "void.audit" Тихонов',
                'role_ru' => 'Аудит, архитектура, техразбор',
                'role_en' => 'Audit, architecture, technical review',
                'bio_ru' => 'Смотрит на стек как на карту скрытых рисков. Обычно приходит туда, где все "вроде работает", но уже пахнет аварией.',
                'bio_en' => 'Looks at a stack as a map of hidden risk. Usually shows up where everything "kind of works" but already smells like failure.',
            ],
        ];
    }
}

if (!function_exists('cpalnya_author_profile_map')) {
    function cpalnya_author_profile_map(): array
    {
        static $map = null;
        if (is_array($map)) {
            return $map;
        }
        $map = [];
        foreach (cpalnya_author_profiles() as $profile) {
            $nickname = trim((string)($profile['nickname'] ?? ''));
            if ($nickname === '') {
                continue;
            }
            $map[$nickname] = $profile;
        }
        return $map;
    }
}

if (!function_exists('cpalnya_author_resolve')) {
    function cpalnya_author_resolve(string $authorName, string $lang = 'ru'): ?array
    {
        $authorName = trim($authorName);
        if ($authorName === '') {
            return null;
        }
        $map = cpalnya_author_profile_map();
        if (!isset($map[$authorName])) {
            return null;
        }
        $profile = $map[$authorName];
        $isRu = strtolower(trim($lang)) === 'ru';
        $profile['resolved_name'] = trim((string)($profile['display_name'] ?? $profile['nickname'] ?? $authorName));
        $profile['resolved_role'] = trim((string)($profile[$isRu ? 'role_ru' : 'role_en'] ?? ''));
        $profile['resolved_bio'] = trim((string)($profile[$isRu ? 'bio_ru' : 'bio_en'] ?? ''));
        return $profile;
    }
}

if (!function_exists('cpalnya_random_author_profile')) {
    function cpalnya_random_author_profile(): array
    {
        $profiles = array_values(cpalnya_author_profiles());
        if (empty($profiles)) {
            return [
                'nickname' => 'cpalnya.editorial',
                'display_name' => 'Редакция ЦПАЛЬНЯ',
                'role_ru' => 'Редакция',
                'role_en' => 'Editorial',
                'bio_ru' => 'Редакционный профиль по умолчанию.',
                'bio_en' => 'Default editorial profile.',
            ];
        }
        return $profiles[random_int(0, count($profiles) - 1)];
    }
}
