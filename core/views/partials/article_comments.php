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
$portalHeaderVariants = $portalIsRu
    ? [
        ['title' => 'А теперь место, где связки перестают быть теорией', 'copy' => 'Если в тексте есть угол, который в реальном трафике работает иначе, несите его сюда. Комментарии под таким материалом нужны не для приличия, а чтобы отделить красивую механику от той, что выдерживает спенд, модерацию и понедельник утром.'],
        ['title' => 'Покажите, где эта логика треснет на живом трафике', 'copy' => 'Здесь можно спокойно спорить с тезисом, добавлять рабочий кейс или подсвечивать риск, который обычно всплывает уже после запуска. Хорошая реплика экономит больше бюджета, чем еще один уверенный абзац.'],
        ['title' => 'Ниже начинается разбор без витрины', 'copy' => 'В комментариях ценятся не аплодисменты, а точные наблюдения: что не взлетело, где просел CR, какой источник повел себя странно и почему очевидное решение оказалось дорогим. Если есть такой опыт, ему место здесь.'],
        ['title' => 'У материала есть оффер. У вас может быть контраргумент', 'copy' => 'Оставьте вопрос, сомнение или короткую историю из практики. Самые полезные обсуждения в CPA обычно начинаются там, где кто-то аккуратно говорит: на схеме красиво, но в кабинете было иначе.'],
        ['title' => 'Комментарии для тех, кто видел цифры после клика', 'copy' => 'Если вы знаете, как идея ведет себя на креативах, источниках, аппруве или возвратах, добавьте это ниже. Теория становится сильнее, когда рядом появляется опыт, за который уже заплатили бюджетом.'],
        ['title' => 'Сюда можно принести неудобную правку', 'copy' => 'Не каждую мысль стоит прятать в рабочем чате. Если в материале не хватает оговорки, кейса, риска или честного “у нас так не сработало”, комментарий ниже сделает текст полезнее для всех.'],
        ['title' => 'После текста начинается кабинет реальности', 'copy' => 'Здесь можно сравнить тезис с тем, что видно в источниках, лендингах, апруве и постбэках. Без шума, но с той самой деталью, которая меняет решение перед следующим тестом.'],
        ['title' => 'Если хочется возразить, это хороший знак', 'copy' => 'CPA-материалы редко становятся полезнее от молчания. Напишите, где бы вы проверяли гипотезу, какой риск поставили бы первым и что в реальном запуске может съесть маржу.'],
        ['title' => 'Здесь спорят не ради спора, а ради EPC', 'copy' => 'Любая аккуратная реплика, кейс или несогласие помогают отличить красивую формулу от рабочей. Если у вас есть наблюдение из воронки, источника или аналитики, оно может быть важнее самого вывода.'],
        ['title' => 'Под статьей открыта зона ручной модерации смысла', 'copy' => 'Добавляйте то, что обычно остается за кадром: странный паттерн трафика, слабое место оффера, неожиданный инсайт по креативу или спокойное возражение к авторской логике.'],
        ['title' => 'Дальше начинается часть для людей с логами', 'copy' => 'Если материал зацепил рабочую боль, не оставляйте ее без имени. Комментарий может быть коротким, но точным: где не совпали цифры, какой источник подвел и что пришлось менять руками.'],
        ['title' => 'Если тезис слишком гладкий, царапните его фактом', 'copy' => 'Здесь как раз место для фактов, которые не помещаются в красивый разбор. Один честный пример из запуска часто сильнее десятка универсальных советов.'],
        ['title' => 'Трафик любит проверять самоуверенные формулировки', 'copy' => 'Если вы уже видели, как похожая идея ломалась на модерации, аудитории, выплатах или трекинге, напишите об этом. Такие детали делают обсуждение взрослым.'],
        ['title' => 'Ниже можно вскрыть второй слой кейса', 'copy' => 'Расскажите, где в этом подходе скрывается реальная сложность: в креативе, источнике, оффере, аналитике или операционке. Хороший комментарий здесь работает как дополнительный тест гипотезы.'],
        ['title' => 'Пора сверить красивую мысль с грязной воронкой', 'copy' => 'Если у вас есть опыт, который подтверждает или ломает тезис, оставьте его ниже. Именно такие реплики помогают не путать удачную формулировку с масштабируемой механикой.'],
        ['title' => 'Здесь можно добавить то, что не видно на лендинге', 'copy' => 'В CPA слишком много важного происходит за красивым экраном: аппрув, качество лида, холд, возвраты, банальные сбои трекинга. Если текст это не задел, комментарии открыты.'],
        ['title' => 'После чтения начинается нормальный разбор полетов', 'copy' => 'Не обязательно соглашаться. Можно уточнить, привести встречный кейс, задать вопрос по источнику или добавить ту деталь, которая спасает бюджет от лишнего теста.'],
        ['title' => 'Оставьте здесь мысль, которую не покажет дашборд', 'copy' => 'Иногда важнейший сигнал приходит не из графика, а из опыта: почему аудитория не поверила, почему креатив устал, почему связка умерла раньше прогноза. Для этого и нужен тред.'],
        ['title' => 'Если у вас есть анти-кейс, он особенно ценен', 'copy' => 'Хорошее обсуждение не обязано гладить текст по голове. Напишите, где подход не сработал, какие условия были другими и что из этого стоит учесть перед следующим запуском.'],
        ['title' => 'Ниже можно проверить материал на арбитражную прочность', 'copy' => 'Тут уместны цифры, сомнения, уточнения по источникам и честные истории из тестов. Все, что помогает отделить рабочую гипотезу от красивой легенды, делает материал сильнее.'],
        ['title' => 'Тут начинается не комментирование, а докрутка', 'copy' => 'Если видите, как усилить тезис, где он недооценивает риск или какой шаг пропущен между идеей и запуском, оставьте это ниже. CPA любит конкретику больше эффектных фраз.'],
        ['title' => 'Давайте добавим к тексту реальный постбек', 'copy' => 'Комментарий здесь может быть тем самым сигналом обратной связи: что подтвердилось, что съело бюджет, где цифры разошлись с ожиданиями и какой вывод стоит забрать другим.'],
        ['title' => 'Если материал звучит уверенно, проверьте его запуском', 'copy' => 'А если такой запуск у вас уже был, расскажите ниже. Уверенная теория становится полезной только после встречи с источником, модерацией, аппрувом и скучными цифрами.'],
        ['title' => 'Ниже место для тех, кто не верит скриншотам без контекста', 'copy' => 'Можно добавить вопрос про условия, гео, источник, период, качество лида или экономику. Такие уточнения иногда ценнее громкого вывода, потому что возвращают разговор к реальности.'],
        ['title' => 'Здесь можно спокойно испортить слишком красивую картину', 'copy' => 'Если есть нюанс, который меняет вывод, напишите его. В CPA аккуратное возражение часто полезнее согласия, особенно когда за ним стоит опыт тестов, банов или просадки CR.'],
        ['title' => 'Тред открыт для умных провокаций', 'copy' => 'Не для шума, а для смысла: где тезис спорный, какой показатель важнее, что сломается при масштабе и какую проверку вы бы сделали первой. Такие комментарии двигают материал дальше.'],
        ['title' => 'Если знаете, где тут утечет бюджет, скажите', 'copy' => 'Подсветите слабое место, альтернативный сценарий или риск, который автор мог недооценить. В CPA честное предупреждение иногда стоит дороже готовой инструкции.'],
        ['title' => 'После статьи начинается проверка на ROI', 'copy' => 'Оставьте реплику, если у вас есть что добавить по окупаемости, источнику, качеству заявок или операционным мелочам. Именно из таких деталей складывается взрослая картина.'],
        ['title' => 'Здесь можно назвать то, что обычно прячут в примечаниях', 'copy' => 'Период теста, ограничения оффера, слабый сегмент, странное поведение модерации, усталость креативов. Любая такая деталь делает обсуждение ближе к реальной работе.'],
        ['title' => 'Материал закончен. Разбор только начинается', 'copy' => 'Если есть свой кейс, вопрос или аккуратное несогласие, добавьте его ниже. В CPA настоящая польза часто появляется после текста, когда люди начинают сверять выводы с практикой.'],
        ['title' => 'Ниже можно добавить недостающий риск-фактор', 'copy' => 'Пишите о том, что важно перед запуском: источник, гео, апрув, холд, скорость выгорания, качество лида. Один точный риск может спасти кому-то тестовый бюджет.'],
        ['title' => 'Если вы бы тестировали иначе, расскажите как', 'copy' => 'Не обязательно спорить целиком. Достаточно показать другой порядок проверки, другой срез аналитики или более честную метрику успеха. Это и есть полезный CPA-разговор.'],
        ['title' => 'Сюда можно вынести правду из рабочих чатов', 'copy' => 'Ту самую, которую обычно формулируют коротко и без презентаций: где просел лид, почему не прошла модерация, какой креатив внезапно спас связку и что лучше не повторять.'],
        ['title' => 'Комментарии как место для контрольного сплита', 'copy' => 'Добавьте свою версию, встречный опыт или вопрос, который меняет трактовку. Пусть под текстом появится не хор согласия, а нормальная проверка гипотезы.'],
        ['title' => 'Если вывод кажется слишком чистым, добавьте грязных данных', 'copy' => 'CPA редко бывает стерильным. Поделитесь тем, что происходило в реальном запуске: шумный трафик, странные отказы, задержки, неочевидный сегмент или неожиданный плюс.'],
        ['title' => 'Ниже можно оставить не мнение, а сигнал', 'copy' => 'Сигнал из кабинета, аналитики, саппорта, аккаунта или собственной воронки. Чем точнее наблюдение, тем сильнее оно помогает тем, кто будет тестировать похожую идею.'],
        ['title' => 'Тут ценятся возражения с руками в статистике', 'copy' => 'Если вы не согласны, прекрасно. Добавьте контекст: какие условия были у вас, какой показатель поплыл и что пришлось менять. Так спор становится полезным инструментом.'],
        ['title' => 'Здесь можно проверить, не пахнет ли кейсом из презентации', 'copy' => 'Спросите про условия, добавьте свой опыт или покажите слабое место логики. В арбитраже красивая история без операционных деталей слишком быстро становится дорогой.'],
        ['title' => 'После текста оставьте то, что меняет ставку', 'copy' => 'Иногда один комментарий помогает понять, стоит ли тестировать идею, менять источник или сразу искать другой угол. Если у вас есть такая деталь, не держите ее в тени.'],
        ['title' => 'Ниже начинается редактура через практику', 'copy' => 'Уточняйте, спорьте, добавляйте цифры и кейсы. Не ради громкости, а ради того, чтобы материал стал ближе к реальному запуску, где важна не красота тезиса, а выживаемость связки.'],
        ['title' => 'Если воронка сказала иначе, ей слово', 'copy' => 'Оставьте наблюдение из реального трафика: где пользователь отвалился, что не принял источник, почему лид стал дороже и какой маленький фикс неожиданно изменил картину.'],
        ['title' => 'Тут можно добавить честный дисклеймер к идее', 'copy' => 'Каждый рабочий подход имеет условия: гео, бюджет, источник, команда, скорость реакции. Если вы знаете такое ограничение для темы, комментарий ниже сделает текст честнее.'],
        ['title' => 'Комментарии для тех, кто считал не только клики', 'copy' => 'Расскажите, что было после клика: апрув, качество, возвраты, удержание, жалобы, ручные правки. В CPA именно эта часть часто решает, был ли тест победой.'],
        ['title' => 'Если есть спорный момент, не оставляйте его в голове', 'copy' => 'Сформулируйте его ниже. Даже короткое уточнение про источник, сегмент или метод подсчета может полностью изменить то, как следующий читатель применит материал.'],
        ['title' => 'Здесь можно добавить недостающую экономику', 'copy' => 'Если текст говорит о механике, а вы видите вопрос по марже, выплате, цене лида или сроку жизни связки, напишите об этом. Без экономики CPA-разговор слишком легко становится красивым.'],
        ['title' => 'После статьи начинается место для нормального скепсиса', 'copy' => 'Скепсис здесь не ломает разговор, а делает его полезнее. Добавьте точное сомнение, практический кейс или вопрос, который стоило бы задать до запуска.'],
        ['title' => 'Если вы уже обожглись на похожем, это важно', 'copy' => 'Расскажите, где именно: источник, креатив, оффер, трекинг, модерация или апрув. Чужая ошибка, записанная спокойно, иногда полезнее чужого успеха.'],
        ['title' => 'Тут можно превратить материал в рабочий спор', 'copy' => 'Не в шумный, а в точный: какие условия важны, какой вывод спорный, какая метрика недосказана и какую проверку вы бы поставили первой. Для этого комментарии и нужны.'],
        ['title' => 'Ниже оставляют не реакцию, а следующий тест', 'copy' => 'Если после чтения у вас появилась гипотеза, вопрос или альтернативный угол, запишите его. Хороший тред помогает не просто обсудить материал, а лучше запустить следующий эксперимент.'],
        ['title' => 'Сюда просится деталь, которую нельзя увидеть снаружи', 'copy' => 'Если у вас есть внутренняя механика, backstage, ограничение или неожиданный вывод по теме, добавьте его ниже. Именно такие комментарии превращают статью в живой рабочий материал.'],
    ]
    : [
        ['title' => 'Now comes the part where funnels stop being theory', 'copy' => 'If the piece has an angle that behaves differently in live traffic, bring it here. Comments under this kind of article are not decorative; they separate clean mechanics from ideas that survive spend, moderation and Monday morning.'],
        ['title' => 'Show where this logic cracks in real traffic', 'copy' => 'This is the place for a practical objection, a field note or a risk that usually appears after launch. A sharp comment can save more budget than another confident paragraph.'],
        ['title' => 'Below, the showroom lights go off', 'copy' => 'Useful comments here are not applause. They are notes on what failed, where CR dropped, which source behaved strangely and why the obvious move became expensive.'],
        ['title' => 'The article has an offer. You may have the counterargument', 'copy' => 'Leave the question, caveat or short field story. The best CPA discussions often start when someone says, carefully: it looks clean on paper, but the dashboard disagreed.'],
        ['title' => 'Comments for people who saw the numbers after the click', 'copy' => 'If you know how the idea behaves across creatives, sources, approval or returns, add it below. Theory gets better when experience paid for by budget stands next to it.'],
        ['title' => 'Bring the uncomfortable correction here', 'copy' => 'Not every useful thought belongs buried in a work chat. If the piece needs a caveat, a case, a risk or an honest “that did not work for us”, the thread is ready.'],
        ['title' => 'After the article comes the reality desk', 'copy' => 'Compare the thesis with what you see in sources, landing pages, approvals and postbacks. Quietly, but with the detail that changes the next test.'],
        ['title' => 'If you want to object, that is a good sign', 'copy' => 'CPA pieces rarely improve through silence. Say where you would test the hypothesis, which risk comes first and what could eat the margin in a real launch.'],
        ['title' => 'This is where we argue for EPC, not theater', 'copy' => 'A careful case, objection or field note helps separate a clean formula from a working one. If you have a funnel, source or analytics detail, it may matter more than the conclusion.'],
        ['title' => 'Open space for manual moderation of meaning', 'copy' => 'Add what usually stays offstage: a strange traffic pattern, a weak offer point, a creative insight or a calm objection to the author’s logic.'],
        ['title' => 'The part for people with logs starts here', 'copy' => 'If the piece hit an operational nerve, name it. A comment can be short and still precise: where numbers diverged, which source failed and what had to be fixed by hand.'],
        ['title' => 'If the thesis is too smooth, scratch it with a fact', 'copy' => 'Facts that do not fit the clean breakdown belong here. One honest launch example often beats ten universal tips.'],
        ['title' => 'Traffic loves testing overconfident sentences', 'copy' => 'If you have seen a similar idea fail on moderation, audience fit, payouts or tracking, write it down. Those details make the discussion mature.'],
        ['title' => 'Open the second layer of the case below', 'copy' => 'Say where the real complexity sits: creative, source, offer, analytics or operations. A good comment works like another hypothesis test.'],
        ['title' => 'Time to compare a clean thought with a messy funnel', 'copy' => 'If you have experience that confirms or breaks the thesis, leave it below. These notes help readers avoid confusing a good sentence with a scalable mechanic.'],
        ['title' => 'Add what the landing page cannot show', 'copy' => 'In CPA, the important part often lives behind the pretty screen: approval, lead quality, hold, returns and boring tracking failures. If the article missed it, the comments are open.'],
        ['title' => 'After reading, the real debrief begins', 'copy' => 'You do not have to agree. Clarify, bring a countercase, ask about the source or add the detail that saves someone from a useless test.'],
        ['title' => 'Leave the thought the dashboard will not show', 'copy' => 'Sometimes the strongest signal comes from experience: why the audience did not believe it, why the creative burned out, why the funnel died earlier than forecast.'],
        ['title' => 'Anti-cases are especially welcome', 'copy' => 'A good discussion does not need to flatter the article. Say where the approach failed, what conditions were different and what the next launch should account for.'],
        ['title' => 'Test the piece for affiliate durability', 'copy' => 'Numbers, doubts, source notes and honest test stories all belong here. Anything that separates a working hypothesis from a polished legend makes the material stronger.'],
        ['title' => 'This is not just commenting. It is iteration', 'copy' => 'If you see how to strengthen the thesis, where it underprices risk or which step is missing between idea and launch, add it below. CPA rewards specifics.'],
        ['title' => 'Let’s add a real postback to the article', 'copy' => 'A comment can be the feedback signal: what held, what burned budget, where numbers missed expectations and what others should take away.'],
        ['title' => 'If the article sounds certain, test it with a launch', 'copy' => 'And if you already ran that launch, tell the thread. Theory becomes useful after meeting sources, moderation, approval and boring numbers.'],
        ['title' => 'For people who do not trust screenshots without context', 'copy' => 'Ask about conditions, geo, source, period, lead quality or economics. These questions can matter more than a loud conclusion because they return the discussion to reality.'],
        ['title' => 'You may quietly ruin the pretty picture here', 'copy' => 'If there is a nuance that changes the conclusion, write it. In CPA, a careful objection often helps more than agreement, especially when it comes from tests, bans or CR drops.'],
        ['title' => 'Smart provocations go here', 'copy' => 'Not noise, but meaning: where the thesis is debatable, which metric matters more, what breaks at scale and which check you would run first.'],
        ['title' => 'If you know where budget leaks, say it', 'copy' => 'Point to the weak spot, alternative scenario or risk the author may have underpriced. In CPA, an honest warning can be worth more than a ready-made instruction.'],
        ['title' => 'After the article comes the ROI check', 'copy' => 'Leave a note if you can add something about payback, source behavior, lead quality or operational friction. Mature conclusions are built from those details.'],
        ['title' => 'Name what usually hides in the footnotes', 'copy' => 'Test period, offer limits, weak segment, odd moderation behavior, creative fatigue. Any such detail brings the discussion closer to real work.'],
        ['title' => 'The piece is finished. The debrief is not', 'copy' => 'If you have a case, question or calm disagreement, add it below. In CPA, the useful part often starts after the article, when people compare conclusions with practice.'],
        ['title' => 'Add the missing risk factor below', 'copy' => 'Write about what matters before launch: source, geo, approval, hold, burnout speed or lead quality. One precise risk can save someone’s test budget.'],
        ['title' => 'If you would test it differently, explain how', 'copy' => 'You do not need to reject the whole piece. Show another validation order, another analytics slice or a more honest success metric.'],
        ['title' => 'Bring the truth from work chats here', 'copy' => 'The short, unpolished truth: where the lead quality dropped, why moderation failed, which creative unexpectedly saved the funnel and what not to repeat.'],
        ['title' => 'Comments as a control split', 'copy' => 'Add your version, counterexperience or a question that changes the reading. Let the thread become a hypothesis check, not a chorus.'],
        ['title' => 'If the conclusion looks too clean, add messy data', 'copy' => 'CPA is rarely sterile. Share what happened in the real launch: noisy traffic, strange rejections, delays, a hidden segment or an unexpected upside.'],
        ['title' => 'Leave a signal, not just a reaction', 'copy' => 'A signal from the ad account, analytics, support, partners or your own funnel. The more specific the observation, the more useful it becomes.'],
        ['title' => 'Objections with hands in the stats are welcome', 'copy' => 'If you disagree, great. Add context: your conditions, the metric that moved and the fix that followed. That turns debate into a tool.'],
        ['title' => 'Check whether this smells like a slide-deck case', 'copy' => 'Ask about conditions, add experience or point to the weak logic. In affiliate work, a pretty story without operational detail gets expensive quickly.'],
        ['title' => 'Leave the detail that changes the stake', 'copy' => 'Sometimes one comment tells readers whether to test the idea, change the source or look for another angle. If you have that detail, do not keep it offstage.'],
        ['title' => 'Practice edits the article below', 'copy' => 'Clarify, disagree, add numbers and cases. Not for volume, but to bring the material closer to a real launch, where survival matters more than polish.'],
        ['title' => 'If the funnel said otherwise, let it speak', 'copy' => 'Leave a live-traffic observation: where the user dropped, what the source rejected, why the lead got more expensive and which small fix changed the picture.'],
        ['title' => 'Add an honest disclaimer to the idea', 'copy' => 'Every working approach has conditions: geo, budget, source, team and reaction speed. If you know a constraint here, the comment thread will make the piece more honest.'],
        ['title' => 'Comments for people who counted more than clicks', 'copy' => 'Tell us what happened after the click: approval, quality, returns, retention, complaints or manual fixes. That part often decides whether the test really won.'],
        ['title' => 'Do not leave the debatable part in your head', 'copy' => 'Put it below. Even a short note on source, segment or counting method can change how the next reader applies the material.'],
        ['title' => 'Add the missing economics', 'copy' => 'If the piece talks mechanics and you see a margin, payout, lead price or funnel lifespan question, say it. Without economics, CPA talk gets pretty too fast.'],
        ['title' => 'A place for useful skepticism', 'copy' => 'Skepticism does not break the conversation here; it makes it better. Add a precise doubt, a practical case or the question you would ask before launch.'],
        ['title' => 'If a similar test burned you, that matters', 'copy' => 'Tell the thread where: source, creative, offer, tracking, moderation or approval. A calm record of someone else’s mistake can be more useful than someone else’s win.'],
        ['title' => 'Turn the article into a working disagreement', 'copy' => 'Not a noisy one, a precise one: which conditions matter, which conclusion is debatable, which metric is missing and which check should come first.'],
        ['title' => 'Leave the next test below', 'copy' => 'If reading gave you a hypothesis, question or alternative angle, write it down. A good thread does more than discuss the piece; it improves the next experiment.'],
        ['title' => 'Bring the detail no outsider can see', 'copy' => 'If you have backstage mechanics, a constraint or an unexpected conclusion on the topic, add it below. That is how an article becomes working material.'],
    ];
$portalHeaderVariant = (array)$portalHeaderVariants[array_rand($portalHeaderVariants)];
$portalHeaderTitle = (string)($portalHeaderVariant['title'] ?? $t('Под текстом начинается живая часть разговора', 'The live part of the conversation starts below'));
$portalHeaderCopy = (string)($portalHeaderVariant['copy'] ?? $t('Здесь обычно появляются наблюдения, встречные истории, несогласия, тихие уточнения и те детали, ради которых материал хочется перечитать уже вместе с другими. Если есть свой опыт, вопрос или аккуратное возражение — это как раз то место.', 'This is where lived detail, disagreements, useful follow-ups and the human part of the story usually show up. If you have experience, a question or a thoughtful counterpoint, this is the right place for it.'));
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
        $authorId = (int)($node['user_id'] ?? 0);
        $currentUserId = (int)($portalUser['id'] ?? 0);
        $hasCurrentUserVote = (int)($node['current_user_vote_id'] ?? 0) > 0;
        $canVote = $currentUserId > 0 && $authorId > 0 && $authorId !== $currentUserId && !$hasCurrentUserVote;
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
                                <a class="pcmt-anchor" href="#comment-<?= $commentId ?>">#<?= $commentId ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pcmt-node-actions">
                    <div class="pcmt-rating">
                        <span class="pcmt-rating-label"><?= htmlspecialchars($t('Рейтинг', 'Rating'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="pcmt-rating-score"><?= $commentScore ?></span>
                        <span class="pcmt-rating-meta">+<?= $commentUp ?> / -<?= $commentDown ?></span>
                        <?php if ($canVote): ?>
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
.pcmt-node{position:relative;padding-left:18px;scroll-margin-top:120px}
.pcmt-node-line{display:none}
.pcmt-node::before{content:"";position:absolute;left:0;top:20px;width:18px;height:1px;background:rgba(122,180,255,.28)}
.pcmt-node::after{content:"";position:absolute;left:0;top:-7px;bottom:-14px;width:1px;background:rgba(122,180,255,.26)}
.pcmt-node:last-child::after{bottom:7px;background:linear-gradient(180deg,rgba(122,180,255,.26) 0%,rgba(122,180,255,.26) 58%,rgba(122,180,255,0) 100%)}
.pcmt-node-card{display:grid;grid-template-columns:minmax(0,1fr) 88px;gap:8px 12px;align-items:start;padding:9px 11px;border:1px solid rgba(122,180,255,.12);background:rgba(255,255,255,.025)}
.pcmt-node-head{grid-column:1;grid-row:1;display:block;min-width:0}
.pcmt-node-author{display:flex;align-items:center;gap:10px;min-width:0}
.pcmt-node-author > div{display:flex;align-items:center;gap:8px;min-width:0;flex-wrap:wrap}
.pcmt-avatar{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;width:36px;height:36px;border:1px solid rgba(122,180,255,.14);background:rgba(255,255,255,.04)}
.pcmt-avatar img{display:block;width:100%;height:100%;object-fit:cover}
.pcmt-node-author strong{display:block;font-size:14px;line-height:1.2;white-space:nowrap}
.pcmt-node-author a{color:var(--shell-text);text-decoration:none}
.pcmt-node-meta{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:0}
.pcmt-node-meta span,.pcmt-node-meta time,.pcmt-node-meta .pcmt-anchor{padding:6px 9px;font-size:10px;letter-spacing:.1em}
.pcmt-node-body{grid-column:1;grid-row:2;margin-top:2px;padding-left:46px;color:var(--shell-text);font-weight:700;line-height:1.4}
.pcmt-node-body p{margin:0 0 8px}
.pcmt-node-body p:last-child{margin-bottom:0}
.pcmt-node-body ul{margin:12px 0 0;padding-left:18px}
.pcmt-node-body a{color:var(--shell-accent)}
.pcmt-children{position:relative;display:grid;gap:10px;margin-top:12px;margin-left:24px}
.pcmt-children::before{content:"";position:absolute;left:-24px;top:-12px;width:24px;height:12px;border-left:1px solid rgba(122,180,255,.26);border-bottom:1px solid rgba(122,180,255,.24)}
.pcmt-children::after{content:"";position:absolute;left:0;top:-11px;width:1px;height:4px;background:rgba(122,180,255,.26)}
.pcmt-node-actions{grid-column:2;grid-row:1 / 3;display:grid;justify-items:stretch;gap:7px;align-content:start}
.pcmt-anchor{color:var(--shell-muted);text-decoration:none}
.pcmt-rating{display:grid;gap:6px;justify-items:center}
.pcmt-rating-label{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--shell-muted)}
.pcmt-rating-score{font:700 1.35rem/1 "Space Grotesk","Sora",sans-serif}
.pcmt-rating-meta{font-size:12px;color:var(--shell-muted);line-height:1}
.pcmt-vote-form{display:grid;grid-template-columns:1fr 1fr;gap:6px;width:100%}
.pcmt-vote-form button{min-height:34px;min-width:0;padding:0 10px}
.pcmt-vote-form button.is-active{background:rgba(39,223,192,.18);border-color:rgba(39,223,192,.32)}
.pcmt-node-actions .pcmt-reply{width:100%;min-height:34px;padding:0 8px;font-size:11px;font-weight:700}
.pcmt-empty{padding:18px;border:1px dashed rgba(122,180,255,.16);color:var(--shell-muted);background:rgba(255,255,255,.02)}
.pcmt-flash{margin-bottom:14px;padding:12px 14px;border:1px solid rgba(122,180,255,.16)}
.pcmt-flash.ok{background:rgba(39,223,192,.10);color:#9fe9df}
.pcmt-flash.error{background:rgba(255,106,127,.10);color:#ffc0cb}
@keyframes pcmtLoad{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
@media (max-width:980px){.pcmt-auth-form{grid-template-columns:1fr}.pcmt-auth-form .pcmt-field-full{grid-column:auto}}
@media (max-width:720px){.pcmt{padding:20px 16px}.pcmt-form-foot,.pcmt-head,.pcmt-compose-tease{align-items:flex-start}.pcmt-node-card{grid-template-columns:1fr}.pcmt-node-body{grid-column:1;padding-left:0}.pcmt-node-actions{grid-column:1;grid-row:auto;display:flex;flex-wrap:wrap;align-items:center}.pcmt-node-actions .pcmt-anchor,.pcmt-node-actions .pcmt-reply{width:auto}.pcmt-vote-form{width:auto;min-width:74px}.pcmt-children{margin-left:12px}}
</style>
<section class="pcmt" id="article-comments" data-portal-auth="<?= $portalIsLoggedIn ? '1' : '0' ?>" data-content-type="<?= htmlspecialchars($portalContentType, ENT_QUOTES, 'UTF-8') ?>" data-content-id="<?= $portalContentId ?>">
    <div class="pcmt-head">
        <div class="pcmt-copy">
            <span class="pcmt-kicker"><?= htmlspecialchars($t('Редакционное обсуждение', 'Editorial discussion'), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($portalHeaderTitle, ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($portalHeaderCopy, ENT_QUOTES, 'UTF-8') ?></p>
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
            var submitter = event.submitter || document.activeElement;
            if (submitter && submitter.form === form && submitter.name) {
                formData.set(submitter.name, submitter.value);
            }
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
