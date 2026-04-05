-- Tools pages schema and initial seed (EN defaults)

CREATE TABLE IF NOT EXISTS `public_tools` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_host` VARCHAR(255) NOT NULL DEFAULT '',
  `lang_code` VARCHAR(5) NOT NULL DEFAULT 'en',
  `slug` VARCHAR(180) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description_text` TEXT NOT NULL,
  `icon_emoji` VARCHAR(24) NOT NULL DEFAULT 'IP',
  `page_heading` VARCHAR(255) NOT NULL DEFAULT '',
  `page_subheading` TEXT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) NULL,
  `seo_description` TEXT NULL,
  `seo_keywords` VARCHAR(255) NULL,
  `og_title` VARCHAR(255) NULL,
  `og_description` TEXT NULL,
  `og_image` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_public_tools_domain_slug_lang` (`domain_host`, `slug`, `lang_code`),
  KEY `idx_public_tools_published` (`is_published`, `sort_order`, `id`),
  KEY `idx_public_tools_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `public_tools`
(`domain_host`,`lang_code`,`slug`,`name`,`description_text`,`icon_emoji`,`page_heading`,`page_subheading`,`is_published`,`sort_order`,`seo_title`,`seo_description`,`seo_keywords`,`og_title`,`og_description`,`created_at`,`updated_at`)
VALUES
('', 'en', 'ip-lookup', 'IP Lookup', 'Find geolocation, network and risk signals for IPv4/IPv6 in one compact view.', 'IP', 'IP Lookup Tool', 'Inspect geolocation, ASN context, proxy hints and antifraud signals for any IP address.', 1, 1000, 'IP Lookup Tool | GeoIP.space', 'Lookup geolocation, ASN and risk signals for any IP address in a clean interface.', 'ip lookup, geoip lookup, asn lookup, ip intelligence', 'IP Lookup Tool', 'Lookup geolocation, ASN and risk signals for any IP address.', NOW(), NOW()),
('', 'en', 'asn-lookup', 'ASN Lookup', 'Resolve ASN number and organization for any IPv4/IPv6 address.', 'AS', 'ASN Lookup Tool', 'Check autonomous system number, network owner and related context.', 1, 990, 'ASN Lookup Tool | GeoIP.space', 'Lookup ASN number and ISP organization by IP address.', 'asn lookup, as number lookup, isp asn checker', 'ASN Lookup Tool', 'Lookup ASN number and ISP organization by IP address.', NOW(), NOW()),
('', 'en', 'proxy-vpn-tor-check', 'Proxy VPN TOR Check', 'Quickly check if an IP looks like proxy/VPN/TOR traffic.', 'PX', 'Proxy VPN TOR Check', 'Fast verification for suspicious network routing and anonymization.', 1, 980, 'Proxy VPN TOR Check | GeoIP.space', 'Check proxy, VPN and TOR risk indicators for any IP.', 'proxy checker, vpn detection, tor ip check', 'Proxy VPN TOR Check', 'Check proxy, VPN and TOR risk indicators for any IP.', NOW(), NOW()),
('', 'en', 'ip-risk-explainer', 'IP Risk Explainer', 'Convert risk score and signals into a clear readable decision.', 'RS', 'IP Risk Explainer', 'Understand why an IP is risky and what to do next.', 1, 970, 'IP Risk Score Explainer | GeoIP.space', 'Explain IP risk score with confidence and antifraud signals.', 'ip risk score, fraud score ip, ip risk analysis', 'IP Risk Explainer', 'Explain IP risk score with confidence and antifraud signals.', NOW(), NOW()),
('', 'en', 'ip-compare', 'IP Compare', 'Compare two IPs by country, ASN, timezone and risk side-by-side.', 'CMP', 'IP Compare Tool', 'Compare two addresses to quickly spot suspicious differences.', 1, 960, 'Compare IP Addresses | GeoIP.space', 'Compare two IP addresses by geolocation and risk signals.', 'compare ip addresses, ip difference checker', 'IP Compare Tool', 'Compare two IP addresses by geolocation and risk signals.', NOW(), NOW()),
('', 'en', 'timezone-by-ip', 'Timezone by IP', 'Get timezone and local offset for a target IP address.', 'TZ', 'Timezone by IP', 'Resolve current timezone context to improve delivery timing and checks.', 1, 950, 'Timezone by IP | GeoIP.space', 'Lookup timezone and local time context from IP address.', 'timezone by ip, local time by ip', 'Timezone by IP', 'Lookup timezone and local time context from IP address.', NOW(), NOW()),
('', 'en', 'country-enrichment-viewer', 'Country Enrichment Viewer', 'Show country metadata: currency, calling code, TLD and flags by IP.', 'CTY', 'Country Enrichment Viewer', 'Inspect country-level metadata used in checkout and localization.', 1, 940, 'Country Enrichment by IP | GeoIP.space', 'View country code, currency, calling code and TLD by IP.', 'country code by ip, currency by ip, tld by country', 'Country Enrichment Viewer', 'View country code, currency, calling code and TLD by IP.', NOW(), NOW()),
('', 'en', 'crypto-local-price', 'Crypto Local Price', 'Estimate BTC/ETH/TON/BNB in local fiat context from country data.', 'CR', 'Crypto Local Price by IP', 'Get crypto price context in local currency from geolocation enrichment.', 1, 930, 'Crypto Local Price by IP | GeoIP.space', 'See BTC, ETH and TON values in local fiat by country context.', 'btc to local currency, crypto local price', 'Crypto Local Price by IP', 'See BTC, ETH and TON values in local fiat by country context.', NOW(), NOW()),
('', 'en', 'suspicious-login-helper', 'Suspicious Login Helper', 'Generate a simple allow/review/block recommendation for login IP.', 'LGN', 'Suspicious Login Helper', 'Practical recommendation for authentication risk decisions.', 1, 920, 'Suspicious Login Check | GeoIP.space', 'Assess if login IP is safe, requires review, or should be blocked.', 'suspicious login check, login fraud detection', 'Suspicious Login Helper', 'Assess if login IP is safe, requires review, or should be blocked.', NOW(), NOW()),
('', 'en', 'ip-batch-mini-checker', 'IP Batch Mini Checker', 'Check up to 20 IPs in one run and get compact risk summary.', 'BCH', 'IP Batch Mini Checker', 'Daily support and operations utility for quick multi-IP checks.', 1, 910, 'Bulk IP Checker | GeoIP.space', 'Batch check IP addresses for geolocation and antifraud indicators.', 'bulk ip checker, batch ip lookup', 'IP Batch Mini Checker', 'Batch check IP addresses for geolocation and antifraud indicators.', NOW(), NOW()),
('', 'en', 'utm-seo-landing-validator', 'UTM SEO Landing Validator', 'Validate UTM tags and basic SEO tags for any landing URL.', 'SEO', 'UTM SEO Landing Validator', 'Check campaign URL quality and critical meta tags quickly.', 1, 900, 'UTM and SEO Landing Validator | GeoIP.space', 'Validate UTM query and core SEO tags for marketing landing pages.', 'utm checker, seo meta checker, landing validator', 'UTM SEO Landing Validator', 'Validate UTM query and core SEO tags for marketing landing pages.', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `updated_at` = NOW();
