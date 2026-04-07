$ErrorActionPreference='Stop'
function ToHash($obj){ if($null -eq $obj){ return $null } if($obj -is [System.Collections.IDictionary]){ $h=@{}; foreach($k in $obj.Keys){ $h[$k]=ToHash $obj[$k] }; return $h } if($obj -is [System.Collections.IEnumerable] -and -not ($obj -is [string])){ $a=@(); foreach($i in $obj){ $a += ,(ToHash $i) }; return ,$a } if($obj.PSObject -and $obj.PSObject.Properties.Count -gt 0 -and -not ($obj -is [string])){ $h=@{}; foreach($p in $obj.PSObject.Properties){ $h[$p.Name]=ToHash $p.Value }; return $h } return $obj }
function Dedupe([object[]]$items){ $seen=@{}; $out=@(); foreach($x in $items){ if($x -is [string]){ $k=$x.Trim().ToLowerInvariant(); if($k -and -not $seen.ContainsKey($k)){ $seen[$k]=$true; $out += $x } } else { $out += $x } }; return ,$out }
function Expand([object[]]$items,[string[]]$pfx,[string[]]$sfx,[int]$limit=220){ $seed=Dedupe $items; $out=@($seed); foreach($i in $seed){ foreach($p in $pfx){ $out += "$p $i" }; foreach($s in $sfx){ $out += "$i $s" } }; $out=Dedupe $out; if($out.Count -gt $limit){ return ,($out[0..($limit-1)]) }; return ,$out }
function NormalizeTree($obj){
  if($null -eq $obj){ return $null }
  if($obj -is [System.Collections.IDictionary]){
    $h=@{}
    foreach($k in $obj.Keys){ $h[$k]=NormalizeTree $obj[$k] }
    return $h
  }
  if($obj -is [System.Collections.IEnumerable] -and -not ($obj -is [string])){
    $items=@()
    foreach($i in $obj){ $items += ,(NormalizeTree $i) }
    while($items.Count -eq 1 -and $items[0] -is [System.Collections.IEnumerable] -and -not ($items[0] -is [string]) -and -not ($items[0] -is [System.Collections.IDictionary])){
      $flattened=@()
      foreach($inner in $items[0]){ $flattened += ,(NormalizeTree $inner) }
      $items=$flattened
    }
    return ,$items
  }
  return $obj
}
$root='c:\Projects\myrepo\CPALNYA'; $basePath=Join-Path $root 'docs\cpalnya_generator_settings.json'; $outPath=Join-Path $root 'docs\cpalnya_generator_settings_expanded.json'; $base=ToHash ((Get-Content -Raw $basePath | ConvertFrom-Json))
$base['enabled']=$true; $base['langs']=@('ru'); $base['domain_host']='cpalnya.ru'; $base['domain_host_en']='cpalnya.ru'; $base['domain_host_ru']='cpalnya.ru'; $base['author_name']='CPALNYA Editorial Desk'; $base['daily_min']=8; $base['daily_max']=20; $base['max_per_run']=24; $base['duplicate_retry_attempts']=2; $base['word_min']=1400; $base['word_max']=4800; $base['today_first_delay_min']=10; $base['auto_expand_retries']=10; $base['expand_context_chars']=36000; $base['prompt_version']='cpalnya-generator-v3-wide-spectrum'; $base['seed_salt']='cpalnya-affiliate-content'; $base['narrative_person']='first_person_plural'; $base['tone_variability']=92; $base['topic_analysis_enabled']=$true; $base['topic_analysis_limit']=1200; $base['topic_analysis_user_prompt_append']='Prioritize CPA, affiliate operations, media buying workflows, trackers, creatives, moderation, farm, source volatility and 2026 platform shifts. Expand aggressively into policy shifts, CIS regulation, platform enforcement, Telegram distribution, buyer routines, anti-ban behavior, tracking failures, payment fragility, routing, recovery scenarios, operator folklore, memeable pain points, backstage team systems, politics, business analysis, stock markets, crypto market structure, Russian business news and Russian legislation when they create direct operator consequences. Avoid generic SaaS, fintech, antifraud and enterprise architecture angles unless they are directly tied to affiliate operations.'; $base['openai_api_key']='YOUR_OPENAI_OR_OPENROUTER_KEY'; $base['openrouter_api_key']='YOUR_OPENROUTER_KEY'; $base['llm_provider']='openrouter'; $base['openai_model']='gpt-4.1-mini'; $base['openrouter_model']='openai/gpt-4.1-mini'; $base['openrouter_fallback_model']='openai/gpt-4o-2024-11-20'; $base['preview_channel_enabled']=$true; $base['preview_channel_chat_id']='-1003821804232'; $base['preview_image_enabled']=$true; $base['preview_image_model']='google/gemini-2.5-flash-image'; $base['preview_image_size']='1536x1024'; $base['article_user_prompt_append_ru']='Пиши как редакция ниши, а не как механический SEO-генератор. Каждый материал должен иметь собственный угол, собственную практическую задачу, собственный набор tradeoff и собственный прикладной вывод. Запрещено перефразировать уже опубликованные материалы и повторять один и тот же тезис под новым заголовком.'; $base['expand_user_prompt_append_ru']='При расширении запрещено повторять уже существующие тезисы, подзаголовки, списки и заходы. Добавляй только новые детали: edge cases, failure modes, anti-patterns, QA checks, rollback-план, handoff-риски, операционные tradeoffs и прикладные решения.'; $base['updated_at']='2026-04-07 00:00:00'
$ruP=@('операционный','редакционный','практический','полевой','сигнальный','углубленный','кризисный','тактический'); $ruS=@('для команд','в 2026','под давлением рынка','изнутри','с чеклистом','для operators','как сигнал','без воды'); $enP=@('operator','editorial','practical','field'); $enS=@('for teams','in 2026','under pressure','from backstage')
foreach($k in @('styles_en','clusters_en','intent_verticals_en','intent_scenarios_en','intent_objectives_en','intent_constraints_en','intent_artifacts_en','intent_outcomes_en','service_focus_en','forbidden_topics_en','article_structures_en')){ $base[$k]=@(Expand $base[$k] $enP $enS 240) }
foreach($k in @('styles_ru','clusters_ru','intent_verticals_ru','intent_scenarios_ru','intent_objectives_ru','intent_constraints_ru','intent_artifacts_ru','intent_outcomes_ru','service_focus_ru','forbidden_topics_ru','article_structures_ru')){ $base[$k]=@(Expand $base[$k] $ruP $ruS 240) }
$base['preview_image_style_options']=@(Dedupe ($base['preview_image_style_options'] + @('newsroom','policy_map','market_terminal','legal_brief','exchange_heatmap','crypto_signal','editorial_collage','operator_desk','response_room','macro_watch')))
if(-not $base.ContainsKey('campaigns')){ $base['campaigns']=@{} }
$defs=@{ journal=@{daily_min=8;daily_max=16;max_per_run=5;duplicate_retry_attempts=1;word_min=1800;word_max=3800}; playbooks=@{daily_min=10;daily_max=20;max_per_run=6;duplicate_retry_attempts=1;word_min=1600;word_max=4200}; signals=@{daily_min=6;daily_max=16;max_per_run=6;duplicate_retry_attempts=5;word_min=1400;word_max=3200}; fun=@{daily_min=3;daily_max=8;max_per_run=4;duplicate_retry_attempts=4;word_min=1000;word_max=2600} }
foreach($name in $defs.Keys){ if(-not $base['campaigns'].ContainsKey($name)){ $base['campaigns'][$name]=@{ key=$name } }; $c=$base['campaigns'][$name]; foreach($kv in $defs[$name].GetEnumerator()){ $c[$kv.Key]=$kv.Value }; $c['styles_ru']=@(Expand $c['styles_ru'] $ruP $ruS[0..4] 120); $c['clusters_ru']=@(Expand $c['clusters_ru'] $ruP[0..4] $ruS 120); $c['article_structures_ru']=@(Expand $c['article_structures_ru'] @('вариант','рабочая схема','формат') @('для редакции','для оператора','с акцентом на действие') 80) }
$base['campaigns']['signals']['title']='Signals'
$base['campaigns']['signals']['title_ru']='Повестка'
$base['campaigns']['signals']['description']='Policy, regulation and market signals with operator impact.'
$base['campaigns']['signals']['description_ru']='Новости, регуляторика и рыночные сигналы с прямым влиянием на операторов.'
$base['campaigns']['signals']['material_section']='signals'
$base['campaigns']['signals']['enabled']=$true
$base['campaigns']['signals']['seed_salt_suffix']='signals'
$base['campaigns']['signals']['styles_ru']=@(Expand @(
  'policy-impact memo',
  'regulatory watch',
  'operator checklist',
  'что делать сейчас',
  'сигнальная записка',
  'разбор последствия решения',
  'операционный brief',
  'market watch',
  'биржевой сигнал для операторов',
  'crypto watch для affiliate-команд',
  'разбор новостей законодательства',
  'редакционный сигнал рынка',
  'сводка enforcement-паттерна',
  'быстрый response memo',
  'риск-мемо для команды',
  'операционная сводка по событию'
) $ruP $ruS 180)
$base['campaigns']['signals']['clusters_ru']=@(Expand @(
  'policy shifts Meta и последствия для affiliate-команд',
  'изменения TikTok Ads и новые ограничения для закупа',
  'Telegram: правила монетизации, ограничения и сигналы для рынка',
  'регуляторика СНГ и влияние на арбитраж трафика',
  'бизнес-новости России и их влияние на digital-рекламу',
  'новости законодательства России для рекламы, данных и платежей',
  'свежие политические решения и их эффект на digital-рынок',
  'аналитика бирж и поведение рекламного капитала',
  'Bitcoin, Ethereum и крупные криптовалюты как рыночный сигнал для affiliate',
  'изменения privacy и атрибуции как сигнал для команд',
  'payment-комплаенс и риск блокировок',
  'новости мирового бизнеса, влияющие на performance-маркетинг',
  'макроэкономические сигналы и volatility в affiliate-нишах',
  'санкции, ограничения и инфраструктурные последствия для рынка',
  'новые правила платформ и enforcement-паттерны',
  'политика маркетплейсов, app-экосистем и ad-networks'
) $ruP[0..4] $ruS 180)
$base['campaigns']['signals']['article_structures_ru']=@(Expand @(
  'Сигнал -> что изменилось -> кто под ударом -> что делать сейчас',
  'Новость -> влияние на рынок -> влияние на оператора -> checklist',
  'Policy shift -> скрытые риски -> ответ команды -> контрольный список',
  'Регуляторика -> кто почувствует первым -> practical response -> next step',
  'Биржевой или crypto-сдвиг -> последствия для ниши -> action memo',
  'Событие -> сломанные гипотезы -> операционный ответ -> safeguard',
  'Разбор новости -> сценарии -> риск-карта -> действия на сегодня',
  'Сигнал повестки -> шум и реальность -> прикладной вывод -> action items'
) @('вариант','рабочая схема','формат') @('для редакции','для оператора','с акцентом на действие') 120)
$base['campaigns']['fun']['title']='Fun'
$base['campaigns']['fun']['title_ru']='Фан'
$base['campaigns']['fun']['description']='Light editorial, satire and playful formats about affiliate culture.'
$base['campaigns']['fun']['description_ru']='Легкая редакция, ирония и развлекательные форматы про культуру affiliate-команд.'
$base['campaigns']['fun']['material_section']='fun'
$base['campaigns']['fun']['enabled']=$true
$base['campaigns']['fun']['seed_salt_suffix']='fun'
$base['campaigns']['fun']['styles_ru']=@(Expand @(
  'сатирический памфлет',
  'иронический обзор',
  'редакционный фельетон',
  'мемный разбор',
  'юмористическая колонка',
  'трагикомедия модерации',
  'командный фольклор',
  'пародийный postmortem',
  'мем-эссе',
  'backstage-комедия',
  'редакционная шутка с фактами',
  'легкий операторский текст'
) $ruP $ruS[0..4] 140)
$base['campaigns']['fun']['clusters_ru']=@(Expand @(
  'мемы команды и внутренний фольклор',
  'драма модерации как жанр',
  'абсурд handoff между ролями',
  'хаос трекеров и великие postback-провалы',
  'фарм как театр дисциплины и суеверий',
  'креативное выгорание и бесконечный recycle hooks',
  'ночная смена affiliate-команды',
  'ритуалы запуска и operator folklore',
  'пародии на роли баеров, фармеров и ассистентов',
  'Telegram-хаос, чаты и backstage-комедия',
  'что чувствует команда, когда платформа снова меняет правила',
  'мемный взгляд на business as usual в affiliate'
) $ruP[0..4] $ruS 140)
$base['campaigns']['fun']['article_structures_ru']=@(Expand @(
  'Сетап -> абсурдное обострение -> узнаваемая боль -> финальный вывод',
  'Персонаж -> хаос вокруг него -> мемный payoff -> нишевая правда',
  'Пародия -> реальная боль -> смешная развязка -> вывод для своих',
  'Наблюдение -> операторская комедия -> скрытая мораль -> closing note',
  'Сцена из команды -> escalation -> узнавание -> мягкий postmortem',
  'Мемный кейс -> симптом -> преувеличение -> реальная механика'
) @('вариант','рабочая схема','формат') @('для редакции','для оператора','с акцентом на действие') 100)
$base['campaigns']['signals']['article_user_prompt_append_ru']='Отслеживай policy, модерацию, регуляторику и рыночные сигналы вокруг арбитража трафика. В этот же слой включай свежие политические новости, бизнес-аналитику, аналитику бирж, движения по отдельным криптовалютам, бизнес-новости России и новости законодательства России, если у них есть реальное последствие для digital-рынка, affiliate-операций, платежей, комплаенса, источников трафика или поведения платформ. Принудительно смещай угол в policy-impact memo, regulatory watch, operator checklist, enforcement watch или формат «что делать сейчас», а не в очередной общий обзор. Каждый материал должен отвечать на вопросы: что изменилось, кто почувствует удар первым, что ломается в операционке и что команде делать дальше.'
$base['campaigns']['playbooks']['article_user_prompt_append_ru']='Смещай акцент в сторону практической реализации, troubleshooting, SOP, handoff, rollback, QA и точных шагов оператора. Если тема уже встречалась, выбирай более узкий и новый угол: другой failure mode, другой этап пайплайна, другой тип команды, другой контекст запуска или другой источник сбоя.'
$base['campaigns']['journal']['article_user_prompt_append_ru']='Фокус на трендах арбитража трафика, операционных решениях команды, качестве сигналов, платформенных сдвигах и прикладных стратегических выводах. Если тема уже затрагивалась, находи новый угол: другой гео-контекст, другую роль, другой tradeoff, другой слой backstage-операционки.'
$base['campaigns']['fun']['article_user_prompt_append_ru']='Держи тон живым, ироничным и нишевым. Юмор должен рождаться из реальной affiliate-операционки, ролевого поведения команды, handoff-сбоев, модерационного абсурда и хаоса трекеров, а не из общих шуток.'
$base=NormalizeTree $base
$base | ConvertTo-Json -Depth 100 | Set-Content -Path $outPath -Encoding utf8
Write-Output $outPath; Write-Output ('styles_ru ' + $base['styles_ru'].Count); Write-Output ('clusters_ru ' + $base['clusters_ru'].Count); Write-Output ('intent_scenarios_ru ' + $base['intent_scenarios_ru'].Count); Write-Output ('article_structures_ru ' + $base['article_structures_ru'].Count); Write-Output ('signals clusters ' + $base['campaigns']['signals']['clusters_ru'].Count)
