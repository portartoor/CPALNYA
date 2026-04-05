<?php
//paths
$TemplatePath = DIR.'template/';
$FilesPath = DIR.'core/files/';
$LibsPath = DIR.'core/libs/';
$ModelsPath = DIR.'core/models/';
$ControlsPath = DIR.'core/controls/';
$ViewsPath = DIR.'core/views/';
$CachePath = DIR.'cache/';

//template_routes
$TemplateRoutes = array(
   'config' => 'config',
   'dashboard' => 'dashboard',
   'adminpanel' => 'dashboard',
);

//no UI mode for concrete routes
$NoUImode = array(
   'debug',
   'api'
);

//database
$DatabaseHost = 'localhost';
$DatabaseUser = 'cpalnya';
$DatabasePassword = 'EwnZSb6RomFizeVgUoOzFSmur';
$DatabaseName = 'cpalnya';
$DatabaseTimezone = '+03:00'; // Europe/Moscow
$AppTimezone = 'Europe/Moscow';

// mail
$MailFromEmail = 'noreply@portcore.online';
$MailFromName = 'portcore.online';
$MailSMTPEnabled = true;
$MailSMTPHost = 'smtp.mailgun.org';
$MailSMTPPort = 587;
$MailSMTPSecure = 'tls'; // tls | ssl
$MailSMTPAuth = true;
$MailSMTPUsername = 'noreply@portcore.online';
$MailSMTPPassword = 'e53a04fcb500b30f3bd76a7806144907-f9517a64-7de51698';
$MailSMTPTimeout = 15;

// telegram notifications
$TelegramNotifyEnabled = true;
$TelegramBotToken = '7616551607:AAHHoI-hkCA5xB2gG5GZA0HTY72oQg5rTFo';
$TelegramChatId = '-1003652654755';
$TelegramApiBase = 'https://api.telegram.org';
$TelegramNotifyTimeout = 12;
$TelegramWebhookSecretToken = 'geoip_tg_webhook_2026';
$TelegramActionSecret = 'geoip_tg_action_secret_2026';

// support
$SupportTelegramName = 'theportcore';
$SupportTelegramUrl = '';

// public contact form anti-spam (optional Turnstile)
$ContactTurnstileSiteKey = '';
$ContactTurnstileSecretKey = '';

// GeoIP.Space API (external provider for analytics enrichment)
$GeoIpSpaceApiBaseUrl = 'https://geoip.space/api';
$GeoIpSpaceApiKey = 'js223piMm9M3wGQBo5O3Gj5kpFJf7Qim236XraQG';
$GeoIpSpaceApiTimeout = 8;

// LLM provider
$LLMProvider = 'openrouter'; // openai | openrouter

// OpenAI (server-side only)
$OpenAIApiKey = '';
$OpenAIBaseUrl = 'https://api.openai.com/v1';
$OpenAIModel = 'gpt-4.1-mini';
$OpenAIHttpTimeout = 120;

// OpenRouter (OpenAI-compatible API)
$OpenRouterApiKey = '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';
$OpenRouterBaseUrl = 'https://openrouter.ai/api/v1';
$OpenRouterModel = 'openai/gpt-4o-2024-11-20';
$OpenRouterHttpReferer = 'https://portcore.online';
$OpenRouterAppTitle = 'portcore.online SEO Cron';
$OpenAIProxyEnabled = true;
$OpenAIProxyHost = '139.144.26.187';
$OpenAIProxyPort = 10761;
$OpenAIProxyType = 'http'; // http | socks5
$OpenAIProxyUsername = 'modeler_pOFdGR';
$OpenAIProxyPassword = 'RB3uc2qEncMB';
$OpenAIProxyPoolEnabled = false;
$OpenAIProxyPool = [
    ['host' => '139.144.26.187', 'port' => 10761, 'type' => 'http', 'username' => 'modeler_pOFdGR', 'password' => 'RB3uc2qEncMB']
];

// SEO articles auto-generation cron
$SeoArticleCronEnabled = true; // set true after API key is configured
$SeoArticleCronLanguages = ['en', 'ru'];
$SeoArticleCronDailyMin = 1;
$SeoArticleCronDailyMax = 3;
$SeoArticleCronWordMin = 2000;
$SeoArticleCronWordMax = 5000;
$SeoArticleCronAutoExpandRetries = 3; // extra LLM retries when article is below minimum words
$SeoArticleCronExpandContextChars = 7000; // tail context size sent to LLM during auto-expand
$SeoArticleCronNotifySchedule = false; // optional per-slot Telegram message when a slot is created
$SeoArticleCronNotifyDailySchedule = true; // send one daily Telegram summary with full schedule
$SeoArticleCronTodayFirstDelayMinutes = 15; // first slot for today starts no earlier than now + N minutes (UTC)
$SeoArticleTelegramPreviewEnabled = true; // publish generated article preview post to dedicated Telegram channel
$SeoArticleTelegramPreviewChatId = '-1003652654755';
$SeoArticlePreviewImageEnabled = true; // try to generate preview image and attach to Telegram post
$SeoArticlePreviewImageModel = 'google/gemini-2.5-flash-image';
$SeoArticlePreviewImageSize = '768x512'; // smaller image for Telegram preview posts
$SeoArticlePreviewImagePromptTemplate = 'Create a {{image_style}} premium editorial illustration for article "{{title}}" ({{lang}}). Focus on GeoIP and antifraud B2B context. Excerpt: {{excerpt}}. Context: {{context}}. No text, no logos, clean modern style, 16:9.';
$SeoArticleTelegramPreviewPostMaxWords = 220; // hard limit <= 700 words for text-only preview post
$SeoArticleTelegramPreviewCaptionMaxWords = 80; // short caption for photo posts
$SeoArticleTelegramPreviewPostMinWords = 70; // if preview text is too short, auto-extend it
$SeoArticleTelegramPreviewCaptionMinWords = 26; // if caption is too short, auto-extend it
$SeoArticleTelegramPreviewUseLLM = true; // generate Telegram preview text via LLM from full article content
$SeoArticleTelegramPreviewModel = ''; // empty = use OpenRouter/OpenAI model from main cron config
$SeoArticleTelegramPreviewContextChars = 14000; // how much article context is sent to LLM for preview generation
$SeoArticleCronAuthorName = 'GeoIP Team';
$SeoArticleCronDomainHost = ''; // empty = all domains
$SeoArticleCronMaxPerRun = 2; // safety guard per single cron execution
$SeoArticleCronSeedSalt = 'geoip-seo-articles-2026';

// IndexNow
$IndexNowEnabled = false;
$IndexNowKey = '';
$IndexNowKeyLocation = '';
$IndexNowEndpoint = '';
$IndexNowHosts = ['portcore.ru', 'portcore.online'];
$IndexNowPingOnPublish = true;
$IndexNowSubmitLimit = 100;
$IndexNowRetryDelayMinutes = 15;

//globals
$GLOBAL=array(
	'app_name'=>'Framework PoW hello-world app',
	'app_version'=>'1.0',
);
?>
