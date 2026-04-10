<?php
$portalUser = is_array($ModelPage['portal_user'] ?? null) ? $ModelPage['portal_user'] : null;
$portalFlash = (array)($ModelPage['portal_flash'] ?? []);
$portalCaptcha = (array)($ModelPage['portal_captcha'] ?? []);
$portalComments = (array)($ModelPage['portal_comments'] ?? []);
$portalCommentTotal = (int)($ModelPage['portal_comment_total'] ?? 0);
$portalContentType = trim((string)($ModelPage['portal_content_type'] ?? 'examples'));
$portalContentId = (int)($ModelPage['portal_content_id'] ?? 0);
$portalLang = isset($lang) ? (string)$lang : 'en';
$portalIsRu = ($portalLang === 'ru');
$portalIsLoggedIn = is_array($portalUser) && (int)($portalUser['id'] ?? 0) > 0;
$portalCsrf = function_exists('public_portal_csrf_token') ? public_portal_csrf_token('portal') : '';
$portalSections = $portalIsRu
    ? ['discussion' => 'Обсуждение', 'question' => 'Вопрос', 'idea' => 'Идея', 'feedback' => 'Отзыв', 'case' => 'Практика']
    : ['discussion' => 'Discussion', 'question' => 'Question', 'idea' => 'Idea', 'feedback' => 'Feedback', 'case' => 'Practice'];
$portalCurrentUrl = (string)($_SERVER['REQUEST_URI'] ?? '/');
$t = static function (string $ru, string $en) use ($portalIsRu): string {
    return $portalIsRu ? $ru : $en;
};
$portalCommentScoreTotal = 0;
$portalCollectScore = static function (array $nodes) use (&$portalCollectScore, &$portalCommentScoreTotal): void {
    foreach ($nodes as $node) {
        $portalCommentScoreTotal += (int)($node['rating_score'] ?? 0);
        if (!empty($node['children']) && is_array($node['children'])) {
            $portalCollectScore((array)$node['children']);
        }
    }
};
$portalCollectScore($portalComments);
$portalEmptyCtas = $portalIsRu
    ? [
        'Первую реплику здесь обычно оставляет тот, кто прочитал между строк.',
        'Если материал хочется не просто дочитать, а сдвинуть с места, комментарий подходит лучше всего.',
        'Под статьей пока тишина. Самое время испортить ее точным наблюдением.',
        'Иногда один комментарий полезнее лишнего абзаца в тексте.',
        'Редакция уже сказала свое. Теперь интереснее услышать человека из процесса.',
        'Если в материале чего-то не хватает, недостающая строка вполне может появиться ниже.',
        'Здесь пока никто не возразил статье. Подозрительно удобная ситуация, чтобы начать первым.',
        'Бывает, что лучший фрагмент номера появляется именно в комментариях.',
        'Если у вас есть рабочее возражение, наблюдение или тихий инсайд, ему как раз сюда.',
        'Комментарий здесь может быть короче статьи, но точнее ее поворота.',
        'Иногда обсуждение начинается не с громкого тезиса, а с аккуратного “вообще-то”.',
        'Если этот текст задел профессиональную привычку, стоит оставить след под ним.',
        'Под таким материалом ценятся не аплодисменты, а точные дополнения.',
        'Хороший комментарий здесь работает как редакторская пометка на полях.',
        'Можно оставить вопрос, который неудобно задать вслух. Обычно именно такие и двигают разговор.',
        'Если вы дочитали до конца и не согласились хотя бы с одним поворотом, пора открыть обсуждение.',
        'Тишина под статьей выглядит красиво, но полезной она бывает редко.',
        'Под этим текстом еще нет реплики, которая перевела бы теорию в практику.',
        'Самые живые материалы редко заканчиваются точкой. Чаще они продолжаются в комментариях.',
        'Если статья вызвала внутреннее “да, но…”, это отличный старт для первой реплики.',
        'Комментарии здесь нужны не для вежливости, а для продолжения мысли.',
        'Вопрос под таким материалом иногда ценнее уверенного вывода.',
        'Если хочется добавить контекст, пример или мягкое несогласие, место уже готово.',
        'Бывает, что под статьей рождается ее более честная версия.',
        'Оставьте реплику так, будто продолжаете колонку, а не просто отвечаете внизу страницы.',
        'Под статьей пока пусто. И это редкое пространство для первой умной реплики.',
        'Если текст попал в нерв, комментарий может попасть прямо в суть.',
        'Иногда обсуждение начинается с фразы, которую автору самому хотелось бы прочитать.',
        'Первый комментарий здесь может быть не громким, а просто точным.',
        'Под материалом еще нет ни одного вопроса. Значит, можно задать тот самый.',
        'Если статья показалась слишком уверенной, комментарий — хорошее место для контраргумента.',
        'Лучшие разговоры под статьями начинаются без разрешения. Просто с первой фразы.',
        'Тут пока нет ни возражений, ни инсайдов, ни редакторского шепота снизу. Можно исправить.',
        'Комментарий — это быстрый способ показать, что статья попала не в пустоту.',
        'Если у материала есть второе дно, его обычно вскрывают не в тексте, а в обсуждении.',
        'Поделитесь тем, что в рабочих чатах сказали бы полушепотом.',
        'Если текст хочется продолжить примером из жизни, это лучшее место.',
        'Иногда одно точное “у нас было иначе” собирает обсуждение лучше любого лонгрида.',
        'Под материалом пока идеальная пустота для первой умной провокации.',
        'Если статья понравилась, можно поддержать ее аргументом. Если нет — тем более.',
        'Комментарии здесь особенно хороши, когда в них меньше шума и больше ремесла.',
        'Под этой статьей пока нет ни одной реплики, которая бы изменила ее угол.',
        'Если у вас есть свой backstage к этой теме, он вполне достоин выйти из тени.',
        'Ниже еще не прозвучала фраза, после которой статья читается по-другому.',
        'Иногда лучший комплимент тексту — умный комментарий под ним.',
        'Тред пока пуст, а значит, у первой реплики есть роскошь задать тон.',
        'Если вы знаете деталь, которую статья только нащупала, ей пора появиться здесь.',
        'Под материалом пока не хватает живого человеческого следа. Можно оставить его первым.',
        'Тишина здесь слишком аккуратная. Давайте добавим в нее смысл.',
        'Редакция любит, когда обсуждение начинается красиво: с мысли, а не с шума.',
    ]
    : [
        'The first note here usually comes from the person who read between the lines.',
        'If the article deserves more than a silent nod, the comment box is the right place.',
        'It is still quiet below. A precise observation would improve that immediately.',
        'Sometimes one comment is more useful than an extra paragraph in the piece.',
        'The editors already had their say. Now the interesting part is yours.',
        'If something is missing from the article, this is where the missing line belongs.',
        'No one has argued with the piece yet. That feels suspiciously convenient.',
        'Some of the best parts of an issue appear in the comments, not the draft.',
        'If you have a practical objection, an extra angle or a quiet insight, this is where it lands.',
        'A comment can be shorter than the article and still move it further.',
        'A good thread often starts with a careful “yes, but”.',
        'If the piece touched a professional nerve, leave a trace under it.',
        'What matters here is not applause, but a sharper addition.',
        'A strong comment works like an editor’s pencil in the margin.',
        'You can leave the question that is awkward to say out loud. Those are often the useful ones.',
        'If you reached the end and disagreed with at least one turn, this is your opening line.',
        'Silence under a piece looks elegant, but it is rarely useful.',
        'The thread is still missing the comment that turns theory into practice.',
        'The liveliest articles rarely end with a period. They continue below.',
        'If the piece triggered an internal “yes, but…”, that is already a strong first comment.',
        'Comments are not here for politeness. They are here to continue the thought.',
        'A sharp question under a piece can matter more than a confident conclusion.',
        'If you want to add context, an example or a calm disagreement, the space is ready.',
        'Sometimes the more honest version of a piece is written in the thread below it.',
        'Write the first note as if you are extending the column, not merely replying to it.',
        'There is generous empty space here for the first intelligent interruption.',
        'If the article hit the nerve, the comment can hit the point.',
        'Some discussions begin with the sentence the author hoped someone would add.',
        'The first comment does not need to be loud. It only needs to be exact.',
        'No one has asked the obvious question yet. That makes it available.',
        'If the article sounds too certain, the thread is a good place for a counterweight.',
        'The best discussions under articles rarely wait for permission.',
        'There is still no insight, no objection, no quiet editorial whisper below. That can change.',
        'A comment is a fast way to show the piece did not land in a vacuum.',
        'If the article has a second layer, it is often opened in the thread, not in the copy.',
        'Share the thought you would usually say half-quietly in a work chat.',
        'If the text wants a real-life example, this is where it should arrive.',
        'A precise “we saw it differently” can build a better thread than a long essay.',
        'The empty thread is a rare luxury: your first note gets to set the tone.',
        'If you liked the piece, support it with an argument. If not, even better.',
        'Comments work best here when they carry less noise and more craft.',
        'There is still no line below this piece that changes how it reads.',
        'Sometimes the smartest compliment to an article is a sharper comment under it.',
        'The first reply has the rare privilege of defining the mood of the thread.',
        'If you know the detail the piece only brushed against, this is the place for it.',
        'The article is still missing a human trace under it. You can leave the first one.',
        'This silence is a little too tidy. Let’s add something useful to it.',
        'The editors prefer discussions that begin with a thought, not a performance.',
        'If your backstage version of this topic is stronger than the polished one, bring it in.',
        'A stylish thread often starts with a single exact sentence.',
    ];
$portalEmptyCta = (string)$portalEmptyCtas[array_rand($portalEmptyCtas)];
$portalCommentTree = static function (array $nodes, int $depth = 0) use (&$portalCommentTree, $portalUser, $portalSections, $portalCsrf, $portalCurrentUrl, $t): void {
    foreach ($nodes as $node) {
        $commentId = (int)($node['id'] ?? 0);
        $author = trim((string)($node['display_name'] ?? $node['username'] ?? 'Member'));
        $avatar = trim((string)($node['avatar_src'] ?? ''));
        $profileUrl = trim((string)($node['profile_url'] ?? '/member/'));
        $section = trim((string)($node['section_code'] ?? 'discussion'));
        $sectionLabel = (string)($portalSections[$section] ?? $section);
        $time = trim((string)($node['created_at'] ?? ''));
        $commentScore = (int)($node['rating_score'] ?? 0);
        $commentUp = (int)($node['votes_up'] ?? 0);
        $commentDown = (int)($node['votes_down'] ?? 0);
        $currentVote = (int)($node['current_user_vote'] ?? 0);
        $userScore = (int)($node['comment_rating'] ?? 0);
        $rankLabel = (string)($node['rank_meta']['label'] ?? $t('Участник обсуждения', 'Discussion member'));
        ?>
        <article class="pcmt-node" id="comment-<?= $commentId ?>" style="--pcmt-depth:<?= (int)$depth ?>">
            <div class="pcmt-node-line" aria-hidden="true"></div>
            <div class="pcmt-node-card">
                <div class="pcmt-node-head">
                    <div class="pcmt-node-author">
                        <a class="pcmt-avatar" href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>"><?php if ($avatar !== ''): ?><img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></a>
                        <div>
                            <strong><a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?></a></strong>
                            <div class="pcmt-node-meta">
                                <span><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= htmlspecialchars($rankLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= $userScore ?></span>
                                <time><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></time>
                            </div>
                        </div>
                    </div>
                    <div class="pcmt-node-actions">
                        <a class="pcmt-anchor" href="#comment-<?= $commentId ?>">#<?= $commentId ?></a>
                        <div class="pcmt-rating">
                            <span class="pcmt-rating-score"><?= $commentScore ?></span>
                            <span class="pcmt-rating-meta">+<?= $commentUp ?> / -<?= $commentDown ?></span>
                            <?php if ($portalUser): ?>
                                <form method="post" class="pcmt-vote-form">
                                    <input type="hidden" name="action" value="public_portal_comment_vote">
                                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="comment_id" value="<?= $commentId ?>">
                                    <button type="submit" name="vote_value" value="1" class="<?= $currentVote > 0 ? 'is-active' : '' ?>">+</button>
                                    <button type="submit" name="vote_value" value="-1" class="<?= $currentVote < 0 ? 'is-active' : '' ?>">-</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php if ($portalUser): ?>
                            <button class="pcmt-reply" type="button" data-comment-reply="<?= $commentId ?>" data-comment-author="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t('Ответить', 'Reply'), ENT_QUOTES, 'UTF-8') ?></button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="pcmt-node-body"><?= (string)($node['body_html'] ?? '') ?></div>
            </div>
            <?php if (!empty($node['children'])): ?>
                <div class="pcmt-children"><?php $portalCommentTree((array)$node['children'], $depth + 1); ?></div>
            <?php endif; ?>
        </article>
        <?php
    }
};
?>
<style>
.pcmt{position:relative;margin-top:28px;padding:28px;border:1px solid rgba(122,180,255,.14);background:linear-gradient(180deg,rgba(6,12,24,.88),rgba(5,10,20,.76));box-shadow:var(--shell-shadow);transition:opacity .24s ease,transform .24s ease}
.pcmt.is-loading{opacity:.72;pointer-events:none}
.pcmt.is-loading::after{content:"";position:absolute;inset:0;border:1px solid rgba(122,180,255,.12);background:linear-gradient(90deg,rgba(255,255,255,0),rgba(122,180,255,.12),rgba(255,255,255,0));animation:pcmtLoad 1.1s linear infinite}
.pcmt-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px}
.pcmt-head h2{margin:0;font:700 clamp(1.6rem,2.6vw,2.4rem)/.96 "Space Grotesk","Sora",sans-serif;letter-spacing:-.05em}
.pcmt-kicker,.pcmt-node-meta span,.pcmt-node-meta time,.pcmt-section-select,.pcmt-auth-kicker,.pcmt-anchor{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04);font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.pcmt-copy{display:grid;gap:12px}
.pcmt-copy p{margin:0;color:var(--shell-muted);line-height:1.72}
.pcmt-summary{display:flex;flex-wrap:wrap;gap:14px 18px;align-items:center;margin-top:2px}
.pcmt-summary-item{display:inline-flex;align-items:center;gap:8px;color:var(--shell-muted);font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.pcmt-summary-item strong{font:700 1rem/1 "Space Grotesk","Sora",sans-serif;color:var(--shell-text);letter-spacing:0}
.pcmt-summary-glyph{display:inline-flex;align-items:center;justify-content:center;color:#f4d56b;font-size:13px;line-height:1}
.pcmt-summary-shell{margin-bottom:18px}
.pcmt-guest-cta{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.pcmt-btn,.pcmt-btn-ghost,.pcmt-toolbar button,.pcmt-reply,.pcmt-vote-form button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 16px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.05);color:var(--shell-text);text-decoration:none;cursor:pointer}
.pcmt-btn{background:linear-gradient(135deg,rgba(115,184,255,.24),rgba(39,223,192,.18));font-weight:700}
.pcmt-btn-ghost{background:rgba(255,255,255,.03)}
.pcmt-auth-shell{display:grid;gap:14px;margin-bottom:18px}
.pcmt-auth-tease{display:grid;gap:12px;padding:18px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-auth-form{display:none;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;padding:18px;border:1px solid rgba(122,180,255,.12);background:rgba(8,14,26,.74);opacity:0;transform:translateY(10px);transition:opacity .24s ease,transform .24s ease}
.pcmt-auth-shell.is-open .pcmt-auth-form{display:grid;opacity:1;transform:none}
.pcmt-auth-form .pcmt-field-full{grid-column:1 / -1}
.pcmt-auth-form input,.pcmt-auth-form textarea,.pcmt-form input,.pcmt-form textarea,.pcmt-form select{width:100%;padding:13px 14px;border:1px solid rgba(122,180,255,.16);background:rgba(4,8,18,.58);color:var(--shell-text)}
.pcmt-captcha{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px;border:1px solid rgba(122,180,255,.12);background:linear-gradient(135deg,rgba(115,184,255,.08),rgba(39,223,192,.06))}
.pcmt-captcha-code{display:flex;align-items:center;gap:10px;font:700 1.15rem/1 "Space Grotesk","Sora",sans-serif;letter-spacing:.04em}
.pcmt-captcha-code span{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:42px;padding:0 12px;border:1px solid rgba(122,180,255,.16);background:rgba(255,255,255,.04)}
.pcmt-compose{display:grid;gap:12px;margin-bottom:18px}
.pcmt-compose-tease{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:14px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-compose-shell .pcmt-form{display:none}
.pcmt-compose-shell.is-open .pcmt-form{display:grid}
.pcmt-form{display:grid;gap:12px}
.pcmt-toolbar{display:flex;flex-wrap:wrap;gap:8px}
.pcmt-toolbar button{min-height:38px;padding:0 12px}
.pcmt-form textarea{min-height:160px;resize:vertical}
.pcmt-form-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.pcmt-reply-state{display:none;align-items:center;gap:10px;padding:10px 12px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-reply-state.is-visible{display:flex}
.pcmt-list{display:grid;gap:14px}
.pcmt-node{position:relative;padding-left:24px;scroll-margin-top:120px}
.pcmt-node-line{position:absolute;left:9px;top:0;bottom:-14px;width:1px;background:linear-gradient(180deg,rgba(122,180,255,.34),rgba(122,180,255,.06))}
.pcmt-node::before{content:"";position:absolute;left:9px;top:28px;width:16px;height:1px;background:rgba(122,180,255,.34)}
.pcmt-node-card{padding:16px 18px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.03)}
.pcmt-node-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.pcmt-node-author{display:flex;align-items:flex-start;gap:12px}
.pcmt-avatar{display:inline-flex;align-items:center;justify-content:center;overflow:hidden;width:46px;height:46px;border:1px solid rgba(122,180,255,.14);background:rgba(255,255,255,.04)}
.pcmt-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.pcmt-node-author strong{display:block;font-size:15px}
.pcmt-node-author a{color:var(--shell-text);text-decoration:none}
.pcmt-node-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:6px}
.pcmt-node-body{margin-top:12px;color:var(--shell-text);line-height:1.75}
.pcmt-node-body p{margin:0 0 12px}
.pcmt-node-body p:last-child{margin-bottom:0}
.pcmt-node-body ul{margin:12px 0 0;padding-left:18px}
.pcmt-node-body a{color:var(--shell-accent)}
.pcmt-children{display:grid;gap:12px;margin-top:12px;margin-left:24px}
.pcmt-node-actions{display:grid;justify-items:end;gap:10px}
.pcmt-anchor{color:var(--shell-muted);text-decoration:none}
.pcmt-rating{display:grid;gap:6px;justify-items:end}
.pcmt-rating-score{font:700 1.2rem/1 "Space Grotesk","Sora",sans-serif}
.pcmt-rating-meta{font-size:12px;color:var(--shell-muted)}
.pcmt-vote-form{display:flex;gap:6px}
.pcmt-vote-form button{min-height:34px;min-width:34px;padding:0 10px}
.pcmt-vote-form button.is-active{background:rgba(39,223,192,.18);border-color:rgba(39,223,192,.32)}
.pcmt-empty{padding:18px;border:1px dashed rgba(122,180,255,.16);color:var(--shell-muted);background:rgba(255,255,255,.02)}
.pcmt-flash{margin-bottom:14px;padding:12px 14px;border:1px solid rgba(122,180,255,.16)}
.pcmt-flash.ok{background:rgba(39,223,192,.10);color:#9fe9df}
.pcmt-flash.error{background:rgba(255,106,127,.10);color:#ffc0cb}
@keyframes pcmtLoad{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
@media (max-width:980px){.pcmt-auth-form{grid-template-columns:1fr}.pcmt-auth-form .pcmt-field-full{grid-column:auto}}
@media (max-width:720px){.pcmt{padding:20px 16px}.pcmt-form-foot,.pcmt-head,.pcmt-node-head,.pcmt-compose-tease{align-items:flex-start}.pcmt-children{margin-left:14px}.pcmt-node-actions{justify-items:start}}
</style>
<section class="pcmt" id="article-comments" data-portal-auth="<?= $portalIsLoggedIn ? '1' : '0' ?>" data-content-type="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>" data-content-id="<?= $portalContentId ?>">
    <div class="pcmt-head">
        <div class="pcmt-copy">
            <span class="pcmt-kicker"><?= htmlspecialchars($t('Редакционное обсуждение', 'Editorial discussion'), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($t('Под текстом начинается живая часть разговора', 'The live part of the conversation starts below'), ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($t('Здесь обычно появляются наблюдения, встречные истории, несогласия, тихие уточнения и те детали, ради которых материал хочется перечитать уже вместе с другими. Если есть свой опыт, вопрос или аккуратное возражение — это как раз то место.', 'This is where lived detail, disagreements, useful follow-ups and the human part of the story usually show up. If you have experience, a question or a thoughtful counterpoint, this is the right place for it.'), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="pcmt-summary" aria-label="<?= htmlspecialchars($t('Сводка обсуждения', 'Discussion summary'), ENT_QUOTES, 'UTF-8') ?>">
                <span class="pcmt-summary-item"><span class="pcmt-summary-glyph" aria-hidden="true">◉</span><strong><?= (int)$portalCommentTotal ?></strong></span>
                <span class="pcmt-summary-item"><span class="pcmt-summary-glyph" aria-hidden="true">↕</span><strong><?= (int)$portalCommentScoreTotal ?></strong></span>
            </div>
        </div>
    </div>

    <?php if (!empty($portalFlash['message'])): ?>
        <div class="pcmt-flash <?= htmlspecialchars((string)($portalFlash['type'] ?? 'ok'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$portalFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$portalIsLoggedIn): ?>
        <div class="pcmt-summary-shell">
            <div class="pcmt-auth-shell" id="pcmt-auth-shell">
                <div class="pcmt-auth-tease">
                    <p><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Войдите, чтобы ответить, поддержать чужую мысль или принести в разговор свой опыт.', 'Sign in to reply, support a point or bring your own experience into the thread.') : $t('Войдите, чтобы оставить первую реплику и открыть это обсуждение.', 'Sign in to leave the first note and open this discussion.'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="pcmt-guest-cta">
                        <button class="pcmt-btn" type="button" data-comment-auth-open><?= htmlspecialchars($portalCommentTotal > 0 ? $t('Войти и обсудить', 'Join the discussion') : $t('Оставить первый комментарий', 'Leave the first comment'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </div>
                <form class="pcmt-auth-form" method="post">
                    <input type="hidden" name="action" value="public_portal_register">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
                    <div><span class="pcmt-auth-kicker"><?= htmlspecialchars($t('Быстрый вход в обсуждение', 'Quick access to discussion'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div><input type="text" name="username" placeholder="<?= htmlspecialchars($t('Логин', 'Login'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full"><input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль от 8 символов', 'Password, min 8 chars'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full pcmt-captcha">
                        <div><strong><?= htmlspecialchars((string)($portalCaptcha[$portalIsRu ? 'prompt_ru' : 'prompt_en'] ?? $t('Сложите два числа рядом со знаками', 'Add the two numbers next to the symbols')), ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="pcmt-captcha-code">
                            <span><?= htmlspecialchars((string)($portalCaptcha['glyph_left'] ?? '◧'), ENT_QUOTES, 'UTF-8') ?><?= (int)($portalCaptcha['left'] ?? 0) ?></span>
                            <span>+</span>
                            <span><?= htmlspecialchars((string)($portalCaptcha['glyph_right'] ?? '◩'), ENT_QUOTES, 'UTF-8') ?><?= (int)($portalCaptcha['right'] ?? 0) ?></span>
                        </div>
                        <input type="text" name="captcha_answer" placeholder="<?= htmlspecialchars($t('Ответ', 'Answer'), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="pcmt-field-full pcmt-guest-cta">
                        <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('Создать аккаунт и продолжить', 'Create account and continue'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
                <form class="pcmt-auth-form" method="post">
                    <input type="hidden" name="action" value="public_portal_login">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
                    <div><span class="pcmt-auth-kicker"><?= htmlspecialchars($t('Уже есть аккаунт', 'Already have an account'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div><input type="text" name="login" placeholder="<?= htmlspecialchars($t('Логин или email', 'Login or email'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div><input type="password" name="password" placeholder="<?= htmlspecialchars($t('Пароль', 'Password'), ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="pcmt-field-full pcmt-guest-cta">
                        <button class="pcmt-btn-ghost" type="submit"><?= htmlspecialchars($t('Войти и продолжить', 'Sign in and continue'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="pcmt-compose">
            <div class="pcmt-compose-tease">
                <p><?= htmlspecialchars($t('Если хочется продолжить мысль, задать встречный вопрос или оставить свою практическую пометку, откройте форму и добавьте реплику.', 'If you want to continue the point, ask a follow-up question or leave a practical note, open the form and add your reply.'), ENT_QUOTES, 'UTF-8') ?></p>
                <button class="pcmt-btn" type="button" data-comment-open><?= htmlspecialchars($t('Оставить комментарий', 'Leave a comment'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
            <div class="pcmt-compose-shell" id="pcmt-compose-shell">
                <form class="pcmt-form" method="post" id="pcmt-comment-form">
                    <input type="hidden" name="action" value="public_portal_comment">
                    <input type="hidden" name="portal_csrf" value="<?= htmlspecialchars($portalCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_path" value="<?= htmlspecialchars($portalCurrentUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_type" value="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="content_id" value="<?= $portalContentId ?>">
                    <input type="hidden" name="parent_id" value="0" id="pcmt-parent-id">
                    <select name="section_code" class="pcmt-section-select">
                        <?php foreach ($portalSections as $sectionCode => $sectionLabel): ?>
                            <option value="<?= htmlspecialchars((string)$sectionCode, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$sectionLabel, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pcmt-toolbar">
                        <button type="button" data-wrap="**" data-target="pcmt-text">B</button>
                        <button type="button" data-wrap="*" data-target="pcmt-text">I</button>
                        <button type="button" data-wrap="~~" data-target="pcmt-text">S</button>
                        <button type="button" data-link-target="pcmt-text"><?= htmlspecialchars($t('Ссылка', 'Link'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="button" data-prefix="🙂 " data-target="pcmt-text">🙂</button>
                        <button type="button" data-prefix="🔥 " data-target="pcmt-text">🔥</button>
                        <button type="button" data-prefix="🧠 " data-target="pcmt-text">🧠</button>
                        <button type="button" data-prefix="⚙️ " data-target="pcmt-text">⚙️</button>
                    </div>
                    <div class="pcmt-reply-state" id="pcmt-reply-state">
                        <span id="pcmt-reply-text"></span>
                        <button class="pcmt-btn-ghost" type="button" data-comment-reply-clear><?= htmlspecialchars($t('Снять ответ', 'Clear reply'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                    <textarea id="pcmt-text" name="body_markdown" placeholder="<?= htmlspecialchars($t('Поделитесь своим наблюдением, вопросом, несогласием или практической историей по теме материала', 'Share an observation, question, disagreement or practical story related to the article'), ENT_QUOTES, 'UTF-8') ?>" required></textarea>
                    <div class="pcmt-form-foot">
                        <span class="pcmt-node-meta"><span><?= htmlspecialchars($t('Разрешены: жирный, курсив, перечеркнутый, ссылка, эмодзи', 'Supports bold, italic, strike, links and emoji'), ENT_QUOTES, 'UTF-8') ?></span></span>
                        <div class="pcmt-guest-cta">
                            <button class="pcmt-btn" type="submit"><?= htmlspecialchars($t('Опубликовать комментарий', 'Publish comment'), ENT_QUOTES, 'UTF-8') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($portalComments)): ?>
        <div class="pcmt-list"><?php $portalCommentTree($portalComments); ?></div>
    <?php else: ?>
        <div class="pcmt-empty"><?= htmlspecialchars($t('Под этим материалом пока тихо. Можно оставить первую реплику и открыть обсуждение.', 'It is still quiet under this article. You can leave the first note and open the thread.'), ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
</section>
<script>
(function () {
    function bindPortalComments(root) {
        if (!root || root.dataset.bound === '1') {
            return;
        }
        root.dataset.bound = '1';
        var emptyCtas = <?= json_encode(array_values($portalEmptyCtas), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function withLoading(active) {
            root.classList.toggle('is-loading', !!active);
        }

        function replaceCommentsHtml(html) {
            if (!html) {
                return;
            }
            var holder = document.createElement('div');
            holder.innerHTML = html;
            var nextRoot = holder.firstElementChild;
            if (!nextRoot) {
                return;
            }
            root.replaceWith(nextRoot);
            bindPortalComments(nextRoot);
        }

        function hasPortalSessionCookie() {
            return /(?:^|;\s*)PHPSESSID=/.test(document.cookie || '');
        }

        function refreshCommentsBlock(callback) {
            var contentType = root.getAttribute('data-content-type') || 'examples';
            var contentId = root.getAttribute('data-content-id') || '0';
            var url = new URL(window.location.href);
            url.searchParams.set('portal_comments_block', '1');
            url.searchParams.set('content_type', contentType);
            url.searchParams.set('content_id', contentId);
            url.searchParams.set('_ts', String(Date.now()));
            withLoading(true);
            fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                if (payload && payload.html) {
                    replaceCommentsHtml(payload.html);
                    if (typeof callback === 'function') {
                        callback(!!payload.logged_in);
                    }
                    return;
                }
                if (typeof callback === 'function') {
                    callback(false);
                }
            }).catch(function () {
                if (typeof callback === 'function') {
                    callback(false);
                }
            }).finally(function () {
                withLoading(false);
            });
        }

        function openAuth() {
            var authShell = root.querySelector('#pcmt-auth-shell');
            if (authShell) {
                authShell.classList.add('is-open');
            }
        }

        function openCompose() {
            var shell = root.querySelector('#pcmt-compose-shell');
            var textarea = root.querySelector('#pcmt-text');
            if (shell) {
                shell.classList.add('is-open');
            }
            if (textarea) {
                window.setTimeout(function () { textarea.focus(); }, 40);
            }
        }

        function clearReplyState() {
            var parentInput = root.querySelector('#pcmt-parent-id');
            var replyState = root.querySelector('#pcmt-reply-state');
            var replyText = root.querySelector('#pcmt-reply-text');
            if (parentInput) {
                parentInput.value = '0';
            }
            if (replyState) {
                replyState.classList.remove('is-visible');
            }
            if (replyText) {
                replyText.textContent = '';
            }
        }

        var emptyBox = root.querySelector('.pcmt-empty');
        if (emptyBox && emptyCtas && emptyCtas.length) {
            emptyBox.textContent = emptyCtas[Math.floor(Math.random() * emptyCtas.length)];
        }

        root.addEventListener('click', function (event) {
            var authOpen = event.target.closest('[data-comment-auth-open]');
            if (authOpen) {
                if ((root.getAttribute('data-portal-auth') || '0') === '1') {
                    openCompose();
                    return;
                }
                refreshCommentsBlock(function (loggedIn) {
                    if (loggedIn) {
                        var refreshedRoot = document.getElementById('article-comments');
                        if (refreshedRoot) {
                            var composeButton = refreshedRoot.querySelector('[data-comment-open]');
                            if (composeButton) {
                                composeButton.click();
                                return;
                            }
                            var composeShell = refreshedRoot.querySelector('#pcmt-compose-shell');
                            var composeText = refreshedRoot.querySelector('#pcmt-text');
                            if (composeShell) {
                                composeShell.classList.add('is-open');
                            }
                            if (composeText) {
                                window.setTimeout(function () { composeText.focus(); }, 40);
                                return;
                            }
                        }
                    }
                    var refreshedRootForAuth = document.getElementById('article-comments');
                    if (refreshedRootForAuth) {
                        var authShell = refreshedRootForAuth.querySelector('#pcmt-auth-shell');
                        if (authShell) {
                            authShell.classList.add('is-open');
                            return;
                        }
                    }
                    openAuth();
                });
                return;
            }

            var composeOpen = event.target.closest('[data-comment-open]');
            if (composeOpen) {
                openCompose();
                return;
            }

            var wrapButton = event.target.closest('[data-wrap][data-target]');
            if (wrapButton) {
                var target = root.querySelector('#' + wrapButton.getAttribute('data-target'));
                if (!target) { return; }
                var wrap = wrapButton.getAttribute('data-wrap') || '';
                var start = target.selectionStart || 0;
                var end = target.selectionEnd || 0;
                var value = target.value || '';
                var selected = value.substring(start, end);
                target.value = value.substring(0, start) + wrap + selected + wrap + value.substring(end);
                target.focus();
                target.selectionStart = start + wrap.length;
                target.selectionEnd = end + wrap.length;
                return;
            }

            var prefixButton = event.target.closest('[data-prefix][data-target]');
            if (prefixButton) {
                var prefixTarget = root.querySelector('#' + prefixButton.getAttribute('data-target'));
                if (!prefixTarget) { return; }
                var prefix = prefixButton.getAttribute('data-prefix') || '';
                var prefixStart = prefixTarget.selectionStart || 0;
                var prefixEnd = prefixTarget.selectionEnd || 0;
                var prefixValue = prefixTarget.value || '';
                prefixTarget.value = prefixValue.substring(0, prefixStart) + prefix + prefixValue.substring(prefixStart, prefixEnd) + prefixValue.substring(prefixEnd);
                prefixTarget.focus();
                prefixTarget.selectionStart = prefixStart + prefix.length;
                prefixTarget.selectionEnd = prefixEnd + prefix.length;
                return;
            }

            var linkButton = event.target.closest('[data-link-target]');
            if (linkButton) {
                var linkTarget = root.querySelector('#' + linkButton.getAttribute('data-link-target'));
                if (!linkTarget) { return; }
                var url = window.prompt('https://');
                if (!url) { return; }
                var text = window.prompt('Текст ссылки') || url;
                var linkInsert = '[' + text + '](' + url + ')';
                var linkStart = linkTarget.selectionStart || 0;
                var linkEnd = linkTarget.selectionEnd || 0;
                var linkValue = linkTarget.value || '';
                linkTarget.value = linkValue.substring(0, linkStart) + linkInsert + linkValue.substring(linkEnd);
                linkTarget.focus();
                return;
            }

            var replyButton = event.target.closest('[data-comment-reply]');
            if (replyButton) {
                var parentInput = root.querySelector('#pcmt-parent-id');
                var replyState = root.querySelector('#pcmt-reply-state');
                var replyText = root.querySelector('#pcmt-reply-text');
                if (parentInput && replyState && replyText) {
                    openCompose();
                    parentInput.value = replyButton.getAttribute('data-comment-reply') || '0';
                    replyText.textContent = '↳ ' + (replyButton.getAttribute('data-comment-author') || '');
                    replyState.classList.add('is-visible');
                }
                return;
            }

            var clearReply = event.target.closest('[data-comment-reply-clear]');
            if (clearReply) {
                clearReplyState();
            }
        });

        root.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            var actionInput = form.querySelector('input[name="action"]');
            var action = actionInput ? actionInput.value : '';
            if (['public_portal_register', 'public_portal_login', 'public_portal_comment', 'public_portal_comment_vote'].indexOf(action) === -1) {
                return;
            }

            event.preventDefault();
            withLoading(true);

            var formData = new FormData(form);
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                if (!payload || typeof payload !== 'object') {
                    window.location.reload();
                    return;
                }
                if (payload.html) {
                    replaceCommentsHtml(payload.html);
                } else {
                    withLoading(false);
                }
                if (payload.comment_anchor) {
                    if (history && history.replaceState) {
                        history.replaceState(null, '', payload.comment_anchor);
                    }
                    window.setTimeout(function () {
                        var target = document.querySelector(payload.comment_anchor);
                        if (target) {
                            target.scrollIntoView({behavior: 'smooth', block: 'start'});
                        }
                    }, 60);
                }
                if (payload.pin_code) {
                    window.setTimeout(function () {
                        window.alert('PIN: ' + payload.pin_code);
                    }, 80);
                }
            }).catch(function () {
                window.location.reload();
            });
        });
    }

    bindPortalComments(document.getElementById('article-comments'));
})();
</script>
