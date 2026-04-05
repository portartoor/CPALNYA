-- RU migration for public_tools
-- Creates/updates Russian tool texts for apigeoip.ru
-- Safe to run multiple times

SET NAMES utf8mb4;

SET @db_name = DATABASE();

SET @has_lang_col = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'public_tools'
    AND COLUMN_NAME = 'lang_code'
);

SET @sql_lang = IF(
  @has_lang_col = 0,
  'ALTER TABLE `public_tools` ADD COLUMN `lang_code` VARCHAR(5) NOT NULL DEFAULT ''en'' AFTER `domain_host`',
  'SELECT 1'
);
PREPARE stmt_lang FROM @sql_lang;
EXECUTE stmt_lang;
DEALLOCATE PREPARE stmt_lang;

DROP TEMPORARY TABLE IF EXISTS tmp_ru_tools_map;
CREATE TEMPORARY TABLE tmp_ru_tools_map (
  slug VARCHAR(180) NOT NULL,
  ru_name VARCHAR(255) NOT NULL,
  ru_description TEXT NOT NULL,
  ru_heading VARCHAR(255) NOT NULL,
  ru_subheading TEXT NOT NULL,
  ru_seo_title VARCHAR(255) NOT NULL,
  ru_seo_description TEXT NOT NULL,
  ru_seo_keywords TEXT NOT NULL,
  PRIMARY KEY (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_ru_tools_map
(slug, ru_name, ru_description, ru_heading, ru_subheading, ru_seo_title, ru_seo_description, ru_seo_keywords)
VALUES
('ip-lookup', 'IP Lookup', 'Проверка IP-адреса: геолокация, ASN, timezone и антифрод-сигналы в одном ответе.', 'Проверка IP-адреса', 'Получите страну, город, ASN, риск-скор и сырой JSON-ответ для быстрой диагностики.', 'IP Lookup API: проверка IP, геолокация, ASN и риск-сигналы', 'Инструмент для мгновенной проверки IP: страна, город, ASN, timezone и антифрод-контекст для login и checkout.', 'ip lookup,проверка ip,геолокация ip,asn lookup,antifraud'),
('asn-lookup', 'ASN Lookup', 'Определение автономной системы и владельца сети по IP для расследований и фильтрации трафика.', 'Проверка ASN по IP', 'Показывает ASN-номер, организацию и сетевой контекст для anti-abuse и security-команд.', 'ASN Lookup: проверка автономной системы и владельца IP-сети', 'Быстрая проверка ASN и организации по IP для мониторинга трафика, аналитики и антифрод-правил.', 'asn lookup,asn проверка,ip сеть,антифрод,безопасность'),
('proxy-vpn-tor-check', 'Proxy VPN TOR Check', 'Детект прокси, VPN и TOR для защиты авторизации, регистрации и платежей.', 'Проверка Proxy/VPN/TOR', 'Помогает выявлять анонимизирующий трафик и принимать решения allow, step-up или block.', 'Proxy VPN TOR Check: проверка анонимизирующего трафика по IP', 'Определяйте proxy/vpn/tor-соединения и усиливайте антифрод-контроль на критичных этапах воронки.', 'proxy check,vpn check,tor check,анонимный трафик,antifraud'),
('ip-risk-explainer', 'IP Risk Explainer', 'Объяснение риск-скора IP: какие сигналы влияют на оценку и итоговое решение.', 'Расшифровка риск-скора IP', 'Показывает risk score, confidence, proxy-флаги и ключевые сигналы для аналитики.', 'IP Risk Explainer: расшифровка risk score и antifraud-сигналов', 'Инструмент для поддержки и антифрод-аналитики: прозрачное объяснение причин высокого риска по IP.', 'risk score,ip risk,antifraud signals,proxy suspected,ip security'),
('ip-compare', 'IP Compare', 'Сравнение двух IP-адресов по гео- и риск-параметрам для расследования инцидентов.', 'Сравнение двух IP', 'Сопоставляйте риск-профили и сетевой контекст по двум адресам в одном отчете.', 'IP Compare: сравнение двух IP по риску и геолокации', 'Сравнивайте два IP по antifraud-сигналам, географии и прокси-подозрениям для точных расследований.', 'ip compare,сравнение ip,ip risk,security analysis,fraud detection'),
('timezone-by-ip', 'Timezone by IP', 'Определение часового пояса и локального времени по IP-адресу.', 'Часовой пояс по IP', 'Используйте timezone-контекст для коммуникаций, алертов и поведенческого анализа.', 'Timezone by IP: определить часовой пояс и локальное время по IP', 'Быстрое определение timezone по IP для операций, уведомлений и анализа аномальной активности.', 'timezone by ip,локальное время,геолокация ip,операции,аналитика'),
('country-enrichment-viewer', 'Country Enrichment Viewer', 'Расширенные данные по стране: валюта, код, TLD, языки и дополнительный контекст.', 'Country Enrichment Viewer', 'Смотрите country enrichment для локализации продукта, биллинга и маршрутизации.', 'Country Enrichment: данные страны по IP для локализации и billing', 'Инструмент показывает расширенные country-данные: коды, валюты, языки, TLD и служебные признаки.', 'country enrichment,локализация,валюта,tld,ip geolocation'),
('crypto-local-price', 'Crypto Local Price', 'Локальные crypto-курсы (BTC/ETH/TON) в валюте страны по IP.', 'Крипто-курс в локальной валюте', 'Удобно для checkout-сценариев, где важен локальный fiat-контекст и сравнение цен.', 'Crypto Local Price: локальные BTC/ETH/TON курсы по геолокации IP', 'Определяйте локальные крипто-курсы по IP для коммерческих сценариев и международного checkout UX.', 'crypto price local,btc курс,eth курс,ton курс,geo pricing'),
('suspicious-login-helper', 'Suspicious Login Helper', 'Оценка подозрительного входа с подсказкой по решению: allow, review, step-up.', 'Проверка подозрительного входа', 'Инструмент анализирует сигналы риска и помогает быстрее принимать решение по login-событиям.', 'Suspicious Login Helper: антифрод-оценка входа по IP и user_id', 'Проверяйте login-события в реальном времени по risk score и сигналам, снижайте риск account takeover.', 'suspicious login,account takeover,login antifraud,risk score,user_id'),
('ip-batch-mini-checker', 'IP Batch Mini Checker', 'Пакетная проверка списка IP-адресов с быстрым обзором риска.', 'Пакетная проверка IP', 'Загрузите список адресов и получите краткий антифрод-срез по каждому IP.', 'IP Batch Checker: массовая проверка IP по риску и proxy-сигналам', 'Мини-инструмент для быстрой пакетной проверки IP в задачах поддержки, модерации и антиабьюза.', 'batch ip checker,массовая проверка ip,ip risk batch,antifraud'),
('utm-seo-landing-validator', 'UTM SEO Landing Validator', 'Проверка UTM-меток и SEO-параметров посадочной страницы до запуска рекламы.', 'Валидатор UTM и SEO посадочной страницы', 'Проверяет HTTP-статус, UTM-метки, title, description, canonical и H1.', 'UTM + SEO Validator: проверка landing page перед запуском кампаний', 'Инструмент маркетинга и SEO: валидация UTM и ключевых мета-тегов посадочной страницы.', 'utm validator,seo validator,landing page check,utm метки,seo аудит');

-- Update existing RU rows
UPDATE public_tools ru
JOIN tmp_ru_tools_map m
  ON COALESCE(ru.lang_code, 'en') = 'ru'
 AND CONVERT(ru.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(m.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
SET
  ru.name = m.ru_name,
  ru.description_text = m.ru_description,
  ru.page_heading = m.ru_heading,
  ru.page_subheading = m.ru_subheading,
  ru.seo_title = m.ru_seo_title,
  ru.seo_description = m.ru_seo_description,
  ru.seo_keywords = m.ru_seo_keywords,
  ru.og_title = m.ru_seo_title,
  ru.og_description = m.ru_seo_description,
  ru.updated_at = NOW();

-- Insert missing RU rows from EN
INSERT INTO public_tools
(domain_host, lang_code, slug, name, description_text, icon_emoji, page_heading, page_subheading, is_published, sort_order, seo_title, seo_description, seo_keywords, og_title, og_description, og_image, created_at, updated_at)
SELECT
  en.domain_host,
  'ru' AS lang_code,
  en.slug,
  m.ru_name,
  m.ru_description,
  en.icon_emoji,
  m.ru_heading,
  m.ru_subheading,
  en.is_published,
  en.sort_order,
  m.ru_seo_title,
  m.ru_seo_description,
  m.ru_seo_keywords,
  m.ru_seo_title,
  m.ru_seo_description,
  en.og_image,
  NOW(),
  NOW()
FROM public_tools en
JOIN tmp_ru_tools_map m
  ON CONVERT(en.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(m.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN public_tools ru
  ON (ru.domain_host <=> en.domain_host)
 AND COALESCE(ru.lang_code, 'en') = 'ru'
 AND CONVERT(ru.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(m.slug USING utf8mb4) COLLATE utf8mb4_unicode_ci
WHERE COALESCE(en.lang_code, 'en') = 'en'
  AND ru.id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_ru_tools_map;
