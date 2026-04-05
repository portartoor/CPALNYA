-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 22, 2026 at 01:40 PM
-- Server version: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portcore`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminpanel_users`
--

CREATE TABLE `adminpanel_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash_sha256` char(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `token` varchar(128) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adminpanel_users`
--

INSERT INTO `adminpanel_users` (`id`, `email`, `password_hash_sha256`, `is_active`, `token`, `token_expires`, `last_login_at`, `created_at`) VALUES
(1, 'portartoor@gmail.com', '7240bda9df922c3565afa95a47a45ffc341a505295b977e1403311035a813fff', 1, '', NULL, '2026-02-22 14:29:57', '2026-02-13 13:38:10');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_lead_events`
--

CREATE TABLE `analytics_lead_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` varchar(64) DEFAULT NULL,
  `event_type` varchar(64) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `subscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount_usd` decimal(28,12) DEFAULT NULL,
  `currency_code` varchar(16) DEFAULT NULL,
  `amount_in_currency` decimal(28,12) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `host` varchar(190) DEFAULT NULL,
  `path` varchar(512) DEFAULT NULL,
  `referrer_host` varchar(190) DEFAULT NULL,
  `source_type` varchar(32) DEFAULT NULL,
  `utm_source` varchar(190) DEFAULT NULL,
  `utm_medium` varchar(190) DEFAULT NULL,
  `utm_campaign` varchar(190) DEFAULT NULL,
  `utm_term` varchar(190) DEFAULT NULL,
  `utm_content` varchar(190) DEFAULT NULL,
  `search_query` varchar(190) DEFAULT NULL,
  `meta_json` longtext DEFAULT NULL,
  `event_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `analytics_suspect_ip`
--

CREATE TABLE `analytics_suspect_ip` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `invalid_hits` int(11) DEFAULT 0,
  `first_seen` datetime DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `is_confirmed_bot` tinyint(1) DEFAULT 0,
  `source` varchar(32) DEFAULT NULL,
  `reason` varchar(190) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analytics_suspect_ip`
--

INSERT INTO `analytics_suspect_ip` (`id`, `ip`, `invalid_hits`, `first_seen`, `last_seen`, `is_confirmed_bot`, `source`, `reason`) VALUES
(1, '128.90.170.10', 16, '2026-02-14 18:34:55', '2026-02-14 18:34:59', 1, NULL, NULL),
(2, '20.197.60.23', 6, '2026-02-14 22:52:28', '2026-02-14 22:52:36', 1, NULL, NULL),
(3, '20.238.87.60', 10, '2026-02-16 22:03:00', '2026-02-17 04:55:40', 1, NULL, NULL),
(4, '2a06:98c0:3600::103', 1045, '2026-02-15 09:00:10', '2026-02-18 20:23:14', 1, NULL, NULL),
(5, '4.197.75.18', 20, '2026-02-14 18:41:32', '2026-02-14 18:42:18', 1, NULL, NULL),
(6, '47.129.236.133', 8, '2026-02-17 03:17:40', '2026-02-17 03:57:41', 1, NULL, NULL),
(7, '79.127.129.203', 4, '2026-02-16 09:47:09', '2026-02-16 10:14:48', 1, NULL, NULL),
(8, '79.127.129.223', 4, '2026-02-17 07:11:02', '2026-02-17 18:26:18', 1, NULL, NULL),
(16, '138.199.60.171', 19, '2026-02-16 17:45:19', '2026-02-16 17:45:21', 1, NULL, NULL),
(17, '138.199.60.181', 38, '2026-02-16 06:53:06', '2026-02-16 13:17:07', 1, NULL, NULL),
(18, '138.199.60.183', 38, '2026-02-17 11:46:25', '2026-02-17 12:24:48', 1, NULL, NULL),
(19, '185.254.75.32', 3, '2026-02-17 14:30:07', '2026-02-17 14:30:07', 1, NULL, NULL),
(20, '195.178.110.132', 8, '2026-02-16 05:05:22', '2026-02-16 05:05:23', 1, NULL, NULL),
(21, '195.178.110.160', 22, '2026-02-16 00:16:02', '2026-02-16 00:16:09', 1, NULL, NULL),
(22, '195.178.110.54', 91, '2026-02-17 16:56:35', '2026-02-17 16:56:38', 1, NULL, NULL),
(23, '2a02:c207:2308:5629::1', 21, '2026-02-17 04:22:56', '2026-02-17 04:23:28', 1, NULL, NULL),
(24, '45.92.1.178', 4, '2026-02-16 11:16:06', '2026-02-18 11:39:04', 1, NULL, NULL),
(25, '77.83.39.170', 10, '2026-02-17 05:35:49', '2026-02-17 05:36:48', 1, NULL, NULL),
(32, '195.178.110.64', 23, '2026-02-18 15:17:58', '2026-02-18 15:18:00', 1, NULL, NULL),
(33, '45.148.10.238', 35, '2026-02-17 12:25:10', '2026-02-17 12:25:18', 1, NULL, NULL),
(34, '79.127.129.220', 4, '2026-02-18 16:34:04', '2026-02-18 17:05:48', 1, NULL, NULL),
(39, '13.74.146.113', 4, '2026-02-16 02:38:09', '2026-02-16 02:38:10', 1, NULL, NULL),
(40, '212.30.36.98', 3, '2026-02-14 19:01:47', '2026-02-14 19:01:47', 1, NULL, NULL),
(41, '79.127.129.214', 4, '2026-02-14 18:58:29', '2026-02-14 19:06:15', 1, NULL, NULL),
(47, '20.111.44.121', 3, '2026-02-18 00:55:21', '2026-02-18 00:55:23', 1, NULL, NULL),
(48, '4.232.88.90', 3, '2026-02-17 17:27:43', '2026-02-17 17:27:45', 1, NULL, NULL),
(49, '68.221.64.54', 3, '2026-02-17 18:24:58', '2026-02-17 18:25:00', 1, NULL, NULL),
(53, '74.248.34.156', 1, '2026-02-20 00:18:11', '2026-02-20 00:18:11', 1, 'manual', 'manual_from_admin_logs'),
(54, '4.232.90.25', 1, '2026-02-20 00:18:43', '2026-02-20 00:18:43', 1, 'manual', 'manual_from_admin_logs'),
(55, '93.123.109.214', 1, '2026-02-20 05:38:42', '2026-02-20 05:38:42', 1, 'manual', 'manual_from_admin_logs'),
(56, '20.203.180.135', 1, '2026-02-20 18:20:31', '2026-02-20 18:20:31', 1, 'manual', 'manual_from_admin_logs'),
(57, '89.42.231.200', 1, '2026-02-20 18:21:10', '2026-02-20 18:21:10', 1, 'manual', 'manual_from_admin_logs'),
(58, '20.194.29.45', 1, '2026-02-21 01:59:32', '2026-02-21 01:59:32', 1, 'manual', 'manual_from_admin_logs'),
(59, '20.214.158.14', 1, '2026-02-21 20:54:23', '2026-02-21 20:54:23', 1, 'manual', 'manual_from_admin_logs');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_threat_rules`
--

CREATE TABLE `analytics_threat_rules` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `match_type` varchar(32) NOT NULL,
  `pattern` varchar(512) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `match_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `last_matched_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analytics_threat_rules`
--

INSERT INTO `analytics_threat_rules` (`id`, `title`, `match_type`, `pattern`, `notes`, `is_active`, `match_count`, `last_matched_at`, `created_at`, `updated_at`) VALUES
(1, 'WordPress login/admin', 'path_or_query_contains', 'wp-login', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(2, 'WordPress admin', 'path_or_query_contains', 'wp-admin', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(3, 'WordPress xmlrpc', 'path_or_query_contains', 'xmlrpc', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(4, 'PhpMyAdmin probe', 'path_or_query_contains', 'phpmyadmin', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(5, 'PMA probe', 'path_or_query_contains', 'pma', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(6, 'Adminer probe', 'path_or_query_contains', 'adminer', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(7, 'ENV leakage probe', 'path_or_query_contains', '.env', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(8, 'PHPUnit probe', 'path_or_query_contains', 'vendor/phpunit', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(9, 'CGI bin probe', 'path_or_query_contains', 'cgi-bin', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(10, 'Config probe', 'path_or_query_contains', 'config.php', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(11, 'Backup dump probe', 'path_or_query_contains', 'dump.sql', 'Legacy hardcoded signature', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(12, 'Manual block from admin logs', 'ip_equals', '74.248.34.156', 'Created from admin visits list', 1, 0, NULL, '2026-02-20 00:18:11', '2026-02-20 00:18:11'),
(13, 'Manual block from admin logs', 'ip_equals', '4.232.90.25', 'Created from admin visits list', 1, 0, NULL, '2026-02-20 00:18:43', '2026-02-20 00:18:43'),
(14, 'Manual block from admin logs', 'ip_equals', '93.123.109.214', 'Created from admin visits list', 1, 0, NULL, '2026-02-20 05:38:42', '2026-02-20 05:38:42'),
(15, 'Manual block from admin logs', 'ip_equals', '20.203.180.135', 'Created from admin visits list', 1, 0, NULL, '2026-02-20 18:20:31', '2026-02-20 18:20:31'),
(16, 'Manual block from admin logs', 'ip_equals', '89.42.231.200', 'Created from admin visits list', 1, 0, NULL, '2026-02-20 18:21:10', '2026-02-20 18:21:10'),
(17, 'Manual block from admin logs', 'ip_equals', '20.194.29.45', 'Created from admin visits list', 1, 0, NULL, '2026-02-21 01:59:32', '2026-02-21 01:59:32'),
(18, 'Manual block from admin logs', 'ip_equals', '20.214.158.14', 'Created from admin visits list', 1, 0, NULL, '2026-02-21 20:54:23', '2026-02-21 20:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_visits`
--

CREATE TABLE `analytics_visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` varchar(64) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `scheme` varchar(10) DEFAULT NULL,
  `host` varchar(190) DEFAULT NULL,
  `uri` text DEFAULT NULL,
  `path` varchar(512) DEFAULT NULL,
  `query_string` text DEFAULT NULL,
  `get_params_json` longtext DEFAULT NULL,
  `request_headers_json` longtext DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `referrer_host` varchar(190) DEFAULT NULL,
  `source_type` varchar(32) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(16) DEFAULT NULL,
  `is_bot` tinyint(1) NOT NULL DEFAULT 0,
  `accept_language` varchar(255) DEFAULT NULL,
  `country_iso2` char(2) DEFAULT NULL,
  `country_name` varchar(120) DEFAULT NULL,
  `city_name` varchar(120) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `utm_source` varchar(190) DEFAULT NULL,
  `utm_medium` varchar(190) DEFAULT NULL,
  `utm_campaign` varchar(190) DEFAULT NULL,
  `utm_term` varchar(190) DEFAULT NULL,
  `utm_content` varchar(190) DEFAULT NULL,
  `search_query` varchar(190) DEFAULT NULL,
  `visited_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_suspect` tinyint(1) DEFAULT 0,
  `suspect_reason` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analytics_visits`
--

INSERT INTO `analytics_visits` (`id`, `request_id`, `ip`, `method`, `scheme`, `host`, `uri`, `path`, `query_string`, `get_params_json`, `request_headers_json`, `referrer`, `referrer_host`, `source_type`, `user_agent`, `device_type`, `is_bot`, `accept_language`, `country_iso2`, `country_name`, `city_name`, `timezone`, `latitude`, `longitude`, `session_id`, `user_id`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `search_query`, `visited_at`, `is_suspect`, `suspect_reason`) VALUES
(1, '5d20c6504f590f31031a', '188.214.122.10', 'GET', 'http', 'portcore.ru', '/', '/', NULL, '[]', '{\"Cookie\":\"PHPSESSID=v973oi0egbudqceiidms479mdh\",\"Accept-Language\":\"ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7\",\"Accept-Encoding\":\"gzip, deflate\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\",\"Upgrade-Insecure-Requests\":\"1\",\"Dnt\":\"1\",\"Cache-Control\":\"no-cache\",\"Pragma\":\"no-cache\",\"Connection\":\"keep-alive\",\"Host\":\"portcore.ru\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'desktop', 0, 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, 'v973oi0egbudqceiidms479mdh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 14:33:37', 0, NULL),
(2, '1b776491b650ffa6c23f', '104.28.192.94', 'GET', 'https', 'portcore.online', '/', '/', NULL, '[]', '{\"Host\":\"portcore.online\",\"X-Forwarded-Proto\":\"https\",\"Cf-Visitor\":\"{\\\"scheme\\\":\\\"https\\\"}\",\"Cf-Ipcountry\":\"UA\",\"Cf-Connecting-Ip\":\"104.28.192.94\",\"Cdn-Loop\":\"cloudflare; loops=1\",\"Accept-Encoding\":\"gzip, br\",\"Cf-Ray\":\"9d1e4a25184fca59-KBP\",\"User-Agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36\",\"X-Forwarded-For\":\"104.28.192.94\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'mnftetst1mm7oioodtc0g2vke2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 14:54:36', 0, NULL),
(3, '781dd2699eaaf0e4247c', '94.231.206.202', 'GET', 'https', '109.71.247.34', '/', '/', NULL, '[]', '{\"Accept-Language\":\"zh-CN,zh;q=0.8\",\"Accept-Charset\":\"GBK,utf-8;q=0.7,*;q=0.3\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\",\"User-Agent\":\"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0\",\"Connection\":\"close\",\"Host\":\"109.71.247.34\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0', 'desktop', 0, 'zh-CN,zh;q=0.8', NULL, NULL, NULL, NULL, NULL, NULL, 'v5efpnk8t02gpmgo8ohp0ktkjj', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 14:59:43', 0, NULL),
(4, '0a57bef9161809ca5d5d', '43.166.250.187', 'GET', 'https', 'portcore.online', '/', '/', NULL, '[]', '{\"Host\":\"portcore.online\",\"Cf-Visitor\":\"{\\\"scheme\\\":\\\"https\\\"}\",\"Cf-Ipcountry\":\"US\",\"Cf-Connecting-Ip\":\"43.166.250.187\",\"Cdn-Loop\":\"cloudflare; loops=1\",\"Accept-Encoding\":\"gzip, br\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1\",\"Pragma\":\"no-cache\",\"X-Forwarded-Proto\":\"https\",\"Cf-Ray\":\"9d1e56b2ce33fbd0-IAD\",\"Referer\":\"http://www.portcore.online\",\"Upgrade-Insecure-Requests\":\"1\",\"Cache-Control\":\"no-cache\",\"Accept-Language\":\"zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7\",\"X-Forwarded-For\":\"43.166.250.187\",\"Authorization\":\"\"}', 'http://www.portcore.online', 'www.portcore.online', 'referral', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'mobile', 0, 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, '6n398t7bpke2qlk7uqpdmpvunq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:03:10', 0, NULL),
(5, 'c72cae6c66ce1e19cd4c', '96.126.104.20', 'GET', 'https', '109.71.247.34', '/dbNH', '/dbNH', NULL, '[]', '{\"Connection\":\"close\",\"Accept-Encoding\":\"gzip\",\"Accept-Charset\":\"utf-8\",\"User-Agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36\",\"Host\":\"109.71.247.34\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'b2ilmvemkpta372i16k57kuh1c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:15:14', 0, NULL),
(6, 'f8ef3f7bc17f23d0a58c', '188.214.122.10', 'GET', 'http', 'portcore.ru', '/', '/', NULL, '[]', '{\"Cookie\":\"PHPSESSID=v973oi0egbudqceiidms479mdh\",\"Accept-Language\":\"ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7\",\"Accept-Encoding\":\"gzip, deflate\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36\",\"Upgrade-Insecure-Requests\":\"1\",\"Dnt\":\"1\",\"Cache-Control\":\"no-cache\",\"Pragma\":\"no-cache\",\"Connection\":\"keep-alive\",\"Host\":\"portcore.ru\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'desktop', 0, 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, 'v973oi0egbudqceiidms479mdh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:20:45', 0, NULL),
(7, '0565de8f7298b42baebc', '136.109.56.27', 'GET', 'http', 'portcore.ru', '/', '/', NULL, '[]', '{\"Accept-Encoding\":\"gzip\",\"User-Agent\":\"Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)\",\"Host\":\"portcore.ru\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'dobbdnml39o6trg2r4099q6p9n', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:40:40', 0, NULL),
(8, '136764f53ccb60d30b6d', '44.202.207.21', 'GET', 'https', 'portcore.ru', '/', '/', NULL, '[]', '{\"Host\":\"portcore.ru\",\"Priority\":\"u=0, i\",\"Accept-Language\":\"en-US,en;q=0.9\",\"Accept-Encoding\":\"gzip, deflate, br, zstd\",\"Sec-Fetch-Dest\":\"document\",\"Sec-Fetch-User\":\"?1\",\"Sec-Fetch-Mode\":\"navigate\",\"Sec-Fetch-Site\":\"none\",\"Sec-Ch-Ua-Platform\":\"\\\"iOS\\\"\",\"Sec-Ch-Ua-Mobile\":\"?1\",\"Sec-Ch-Ua\":\"\\\"Chromium\\\";v=\\\"141\\\", \\\"Not?A_Brand\\\";v=\\\"8\\\"\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148\",\"Upgrade-Insecure-Requests\":\"1\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', 'mobile', 0, 'en-US,en;q=0.9', NULL, NULL, NULL, NULL, NULL, NULL, '75csmkv58ml1nbbbm00mhhi1n5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:44:52', 0, NULL),
(9, '77d21bc299f21953d056', '98.84.105.163', 'GET', 'https', 'portcore.ru', '/', '/', NULL, '[]', '{\"Host\":\"portcore.ru\",\"Priority\":\"u=0, i\",\"Accept-Language\":\"en-US,en;q=0.9\",\"Accept-Encoding\":\"gzip, deflate, br, zstd\",\"Sec-Fetch-Dest\":\"document\",\"Sec-Fetch-User\":\"?1\",\"Sec-Fetch-Mode\":\"navigate\",\"Sec-Fetch-Site\":\"none\",\"Sec-Ch-Ua-Platform\":\"\\\"Windows\\\"\",\"Sec-Ch-Ua-Mobile\":\"?0\",\"Sec-Ch-Ua\":\"\\\"Chromium\\\";v=\\\"141\\\", \\\"Not?A_Brand\\\";v=\\\"8\\\"\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36\",\"Upgrade-Insecure-Requests\":\"1\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'desktop', 0, 'en-US,en;q=0.9', NULL, NULL, NULL, NULL, NULL, NULL, 'l2ngn5i3bolg727jvp6ih7sm32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:44:53', 0, NULL),
(10, '9fd33f045c89c49ac4e4', '43.130.31.17', 'GET', 'http', 'portcore.online', '/', '/', NULL, '[]', '{\"Connection\":\"Keep-Alive\",\"X-Forwarded-Proto\":\"http\",\"Cf-Visitor\":\"{\\\"scheme\\\":\\\"http\\\"}\",\"Cf-Ipcountry\":\"US\",\"Cf-Connecting-Ip\":\"43.130.31.17\",\"Cdn-Loop\":\"cloudflare; loops=1\",\"Accept-Encoding\":\"gzip\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1\",\"Host\":\"portcore.online\",\"Cf-Ray\":\"9d1e9c22feaaddff-SJC\",\"Pragma\":\"no-cache\",\"Upgrade-Insecure-Requests\":\"1\",\"Cache-Control\":\"no-cache\",\"Accept-Language\":\"zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7\",\"X-Forwarded-For\":\"43.130.31.17\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'mobile', 0, 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, 'l29m5dj5qentmc1drtg6eab7lt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 15:50:35', 0, NULL),
(11, '7f771ed3fe47f7363723', '204.76.203.8', 'GET', 'https', '109.71.247.34', '/', '/', NULL, '[]', '{\"Connection\":\"close\",\"User-Agent\":\"Mozilla/5.0\",\"Host\":\"109.71.247.34:443\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hve1l52jp9su87r71ncm569pj4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:00:58', 0, NULL),
(12, '5008a11df766f3219c14', '134.209.74.205', 'GET', 'http', '109.71.247.34', '/', '/', NULL, '[]', '{\"Accept-Encoding\":\"gzip, deflate\",\"Sec-Fetch-Dest\":\"document\",\"Sec-Fetch-User\":\"?1\",\"Sec-Fetch-Mode\":\"navigate\",\"Sec-Fetch-Site\":\"none\",\"Accept-Language\":\"en-US,en;q=0.5\",\"Sec-Gpc\":\"1\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8\",\"User-Agent\":\"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36\",\"Upgrade-Insecure-Requests\":\"1\",\"Sec-Ch-Ua-Platform\":\"\\\"Linux\\\"\",\"Sec-Ch-Ua-Mobile\":\"?0\",\"Sec-Ch-Ua\":\"\\\"Google Chrome\\\";v=\\\"142\\\", \\\"Not-A.Brand\\\";v=\\\"8\\\", \\\"Chromium\\\";v=\\\"142\\\"\",\"Connection\":\"keep-alive\",\"Host\":\"109.71.247.34\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'desktop', 0, 'en-US,en;q=0.5', NULL, NULL, NULL, NULL, NULL, NULL, 'mqpaovkj644e03qi2tko7sqlp9', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:01:32', 0, NULL),
(13, 'd789fd273fa27c745b29', '43.130.139.136', 'GET', 'http', 'portcore.online', '/', '/', NULL, '[]', '{\"Connection\":\"Keep-Alive\",\"X-Forwarded-Proto\":\"http\",\"Cf-Visitor\":\"{\\\"scheme\\\":\\\"http\\\"}\",\"Cf-Ipcountry\":\"US\",\"Cf-Connecting-Ip\":\"43.130.139.136\",\"Cdn-Loop\":\"cloudflare; loops=1\",\"Accept-Encoding\":\"gzip\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\",\"User-Agent\":\"Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1\",\"Host\":\"portcore.online\",\"Cf-Ray\":\"9d1eb0937afa07e3-IAD\",\"Pragma\":\"no-cache\",\"Upgrade-Insecure-Requests\":\"1\",\"Cache-Control\":\"no-cache\",\"Accept-Language\":\"zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7\",\"X-Forwarded-For\":\"43.130.139.136\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'mobile', 0, 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, 'uknbleduanuurccnuaed7lqd52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:04:37', 0, NULL),
(14, '1f133ae763894027572a', '185.200.241.108', 'GET', 'http', '109.71.247.34', '/', '/', NULL, '[]', '{\"Cache-Control\":\"no-cache\",\"Upgrade-Insecure-Requests\":\"1\",\"Connection\":\"keep-alive\",\"Accept-Encoding\":\"gzip, deflate, br\",\"Accept-Language\":\"zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\",\"User-Agent\":\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15\",\"Host\":\"109.71.247.34\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15', 'desktop', 0, 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, 'bi4q486jljcf9b31lv27o8f0k5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:06:26', 0, NULL),
(15, '9357f485921d743ef996', '185.200.241.108', 'GET', 'https', '109.71.247.34', '/', '/', NULL, '[]', '{\"Cache-Control\":\"no-cache\",\"Upgrade-Insecure-Requests\":\"1\",\"Connection\":\"keep-alive\",\"Accept-Encoding\":\"gzip, deflate, br\",\"Accept-Language\":\"zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7\",\"Accept\":\"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\",\"User-Agent\":\"Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Mobile Safari/537.36\",\"Host\":\"109.71.247.34\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Mobile Safari/537.36', 'mobile', 0, 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7', NULL, NULL, NULL, NULL, NULL, NULL, 'uh3849gl7262pdrdbkv73r6m2l', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:06:27', 0, NULL),
(16, '727904a87b99928fe7dd', '104.196.98.245', 'GET', 'http', 'mail.portcore.ru', '/', '/', NULL, '[]', '{\"Accept-Encoding\":\"gzip\",\"User-Agent\":\"Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)\",\"Host\":\"mail.portcore.ru\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4jupdqk1q1toj5n535ic121ucs', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:12:24', 0, NULL),
(17, '780f94c7da0de8729d5c', '62.84.127.48', 'GET', 'https', 'cloudflare.com', '/cdn-cgi/trace', '/cdn-cgi/trace', NULL, '[]', '{\"Host\":\"cloudflare.com\",\"User-Agent\":\"Mozilla/5.0\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'mecpkdmreeqv5tqv8a3mbt3u35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:32:48', 0, NULL),
(18, '7a7bd6e0399eb50a93a2', '79.124.40.174', 'GET', 'http', '109.71.247.34', '/', '/', NULL, '[]', '{\"Connection\":\"close\",\"Accept-Encoding\":\"gzip\",\"User-Agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36\",\"Host\":\"109.71.247.34:80\",\"Authorization\":\"\"}', NULL, NULL, 'direct', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'desktop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pthcso9khrfqudnmb157ii55tn', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-22 16:34:24', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `analytics_visits_recovered`
--

CREATE TABLE `analytics_visits_recovered` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` varchar(64) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `scheme` varchar(10) DEFAULT NULL,
  `host` varchar(190) DEFAULT NULL,
  `uri` text DEFAULT NULL,
  `path` varchar(512) DEFAULT NULL,
  `query_string` text DEFAULT NULL,
  `get_params_json` longtext DEFAULT NULL,
  `request_headers_json` longtext DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `referrer_host` varchar(190) DEFAULT NULL,
  `source_type` varchar(32) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(16) DEFAULT NULL,
  `is_bot` tinyint(1) NOT NULL DEFAULT 0,
  `accept_language` varchar(255) DEFAULT NULL,
  `country_iso2` char(2) DEFAULT NULL,
  `country_name` varchar(120) DEFAULT NULL,
  `city_name` varchar(120) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `utm_source` varchar(190) DEFAULT NULL,
  `utm_medium` varchar(190) DEFAULT NULL,
  `utm_campaign` varchar(190) DEFAULT NULL,
  `utm_term` varchar(190) DEFAULT NULL,
  `utm_content` varchar(190) DEFAULT NULL,
  `search_query` varchar(190) DEFAULT NULL,
  `visited_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_suspect` tinyint(1) DEFAULT 0,
  `suspect_reason` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `examples_articles`
--

CREATE TABLE `examples_articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `domain_host` varchar(255) DEFAULT '',
  `lang_code` varchar(5) NOT NULL DEFAULT 'en',
  `cluster_code` varchar(64) NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `excerpt_html` text DEFAULT NULL,
  `content_html` mediumtext NOT NULL,
  `preview_image_url` longtext DEFAULT NULL,
  `author_name` varchar(120) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `preview_image_data` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fx_rates_cron_log`
--

CREATE TABLE `fx_rates_cron_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_name` varchar(80) NOT NULL,
  `status` enum('ok','error') NOT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `fiat_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `crypto_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fx_rates_cron_log`
--

INSERT INTO `fx_rates_cron_log` (`id`, `job_name`, `status`, `message`, `fiat_rows`, `crypto_rows`, `created_at`) VALUES
(1, 'update_fx_rates', 'ok', 'FX updated: fiat=166, crypto=4', 166, 4, '2026-02-14 11:05:45'),
(2, 'update_fx_rates', 'ok', 'FX updated: fiat=166, crypto=4', 166, 4, '2026-02-14 11:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `fx_rates_crypto_usd`
--

CREATE TABLE `fx_rates_crypto_usd` (
  `crypto_symbol` varchar(10) NOT NULL,
  `coingecko_id` varchar(64) NOT NULL,
  `usd_per_coin` decimal(28,12) NOT NULL COMMENT 'How many USD for 1 coin',
  `coin_per_usd` decimal(28,12) NOT NULL COMMENT 'How many coins for 1 USD',
  `provider` varchar(64) NOT NULL,
  `provider_updated_at` datetime DEFAULT NULL,
  `fetched_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fx_rates_crypto_usd`
--

INSERT INTO `fx_rates_crypto_usd` (`crypto_symbol`, `coingecko_id`, `usd_per_coin`, `coin_per_usd`, `provider`, `provider_updated_at`, `fetched_at`, `updated_at`) VALUES
('BNB', 'binancecoin', 628.370000000000, 0.001591419068, 'api.coingecko.com', '2026-02-14 11:08:00', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BTC', 'bitcoin', 69718.000000000000, 0.000014343498, 'api.coingecko.com', '2026-02-14 11:08:00', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ETH', 'ethereum', 2077.700000000000, 0.000481301439, 'api.coingecko.com', '2026-02-14 11:08:01', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TON', 'the-open-network', 1.490000000000, 0.671140939597, 'api.coingecko.com', '2026-02-14 11:08:05', '2026-02-14 11:08:08', '2026-02-14 11:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `fx_rates_fiat_usd`
--

CREATE TABLE `fx_rates_fiat_usd` (
  `currency_code` char(3) NOT NULL,
  `units_per_usd` decimal(28,12) NOT NULL COMMENT 'How many currency units for 1 USD',
  `usd_per_unit` decimal(28,12) NOT NULL COMMENT 'How many USD for 1 currency unit',
  `provider` varchar(64) NOT NULL,
  `provider_updated_at` datetime DEFAULT NULL,
  `fetched_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fx_rates_fiat_usd`
--

INSERT INTO `fx_rates_fiat_usd` (`currency_code`, `units_per_usd`, `usd_per_unit`, `provider`, `provider_updated_at`, `fetched_at`, `updated_at`) VALUES
('AED', 3.672500000000, 0.272294077604, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('AFN', 64.813921000000, 0.015428784196, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ALL', 81.106995000000, 0.012329392798, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('AMD', 377.446751000000, 0.002649380336, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ANG', 1.790000000000, 0.558659217877, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('AOA', 921.535335000000, 0.001085145585, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ARS', 1452.250000000000, 0.000688586676, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('AUD', 1.414549000000, 0.706939102145, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('AWG', 1.790000000000, 0.558659217877, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('AZN', 1.700310000000, 0.588128047238, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BAM', 1.648197000000, 0.606723589474, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BBD', 2.000000000000, 0.500000000000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BDT', 122.343627000000, 0.008173699150, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BGN', 1.597964000000, 0.625796325825, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BHD', 0.376000000000, 2.659574468085, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BIF', 2967.818049000000, 0.000336947880, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BMD', 1.000000000000, 1.000000000000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BND', 1.262867000000, 0.791849022898, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BOB', 6.933228000000, 0.144232960462, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BRL', 5.217563000000, 0.191660359444, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BSD', 1.000000000000, 1.000000000000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BTN', 90.629017000000, 0.011033993671, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BWP', 13.538553000000, 0.073863137368, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BYN', 2.856045000000, 0.350134539197, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('BZD', 2.000000000000, 0.500000000000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CAD', 1.361345000000, 0.734567651844, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CDF', 2245.799616000000, 0.000445275702, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CHF', 0.768386000000, 1.301429229580, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CLF', 0.021658000000, 46.172315079878, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CLP', 856.078549000000, 0.001168117109, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CNH', 6.903594000000, 0.144852087188, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CNY', 6.914921000000, 0.144614811941, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('COP', 3674.165166000000, 0.000272170671, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CRC', 491.380397000000, 0.002035083219, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CUP', 24.000000000000, 0.041666666667, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CVE', 92.921373000000, 0.010761786742, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('CZK', 20.442645000000, 0.048917349003, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('DJF', 177.721000000000, 0.005626797058, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('DKK', 6.289942000000, 0.158983977913, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('DOP', 62.733916000000, 0.015940340788, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('DZD', 129.532677000000, 0.007720059704, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('EGP', 46.849311000000, 0.021345031093, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ERN', 15.000000000000, 0.066666666667, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ETB', 155.180937000000, 0.006444090488, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('EUR', 0.842710000000, 1.186647838521, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('FJD', 2.182936000000, 0.458098634133, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('FKP', 0.733326000000, 1.363650000136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('FOK', 6.290028000000, 0.158981804215, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GBP', 0.733327000000, 1.363648140598, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GEL', 2.682280000000, 0.372817155554, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GGP', 0.733326000000, 1.363650000136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GHS', 10.993999000000, 0.090958713022, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GIP', 0.733326000000, 1.363650000136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GMD', 74.129519000000, 0.013489902720, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GNF', 8766.484712000000, 0.000114070809, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GTQ', 7.674291000000, 0.130305196923, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('GYD', 209.243824000000, 0.004779113576, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('HKD', 7.816847000000, 0.127928818359, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('HNL', 26.444532000000, 0.037815000848, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('HRK', 6.349395000000, 0.157495320420, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('HTG', 131.065934000000, 0.007629747635, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('HUF', 319.375564000000, 0.003131109931, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('IDR', 16826.280463000000, 0.000059430841, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ILS', 3.087279000000, 0.323909824800, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('IMP', 0.733326000000, 1.363650000136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('INR', 90.629061000000, 0.011033988314, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('IQD', 1309.715883000000, 0.000763524374, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('IRR', 1260283.351505000000, 0.000000793472, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ISK', 122.237362000000, 0.008180804818, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('JEP', 0.733326000000, 1.363650000136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('JMD', 156.214309000000, 0.006401462237, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('JOD', 0.709000000000, 1.410437235543, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('JPY', 152.897349000000, 0.006540335765, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KES', 128.964804000000, 0.007754053579, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KGS', 87.425761000000, 0.011438276185, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KHR', 4019.149807000000, 0.000248808840, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KID', 1.414545000000, 0.706941101202, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KMF', 414.585940000000, 0.002412045136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KRW', 1442.878815000000, 0.000693058897, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KWD', 0.306550000000, 3.262110585549, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KYD', 0.833333000000, 1.200000480000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('KZT', 494.524864000000, 0.002022143016, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('LAK', 21625.666322000000, 0.000046241350, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('LBP', 89500.000000000000, 0.000011173184, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('LKR', 309.241032000000, 0.003233723525, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('LRD', 186.592317000000, 0.005359277467, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('LSL', 15.962058000000, 0.062648563237, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('LYD', 6.304151000000, 0.158625642057, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MAD', 9.140819000000, 0.109399387517, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MDL', 16.936073000000, 0.059045565049, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MGA', 4402.562230000000, 0.000227140458, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MKD', 51.902240000000, 0.019266991174, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MMK', 2102.349156000000, 0.000475658383, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MNT', 3599.009139000000, 0.000277854254, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MOP', 8.051352000000, 0.124202742595, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MRU', 39.868369000000, 0.025082540999, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MUR', 45.623627000000, 0.021918467815, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MVR', 15.449627000000, 0.064726481746, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MWK', 1744.871524000000, 0.000573108098, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MXN', 17.183303000000, 0.058196029017, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MYR', 3.907121000000, 0.255942930869, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('MZN', 63.591513000000, 0.015725368887, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('NAD', 15.962058000000, 0.062648563237, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('NGN', 1353.168163000000, 0.000739006450, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('NIO', 36.815487000000, 0.027162481920, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('NOK', 9.512722000000, 0.105122382426, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('NPR', 145.006427000000, 0.006896246054, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('NZD', 1.656090000000, 0.603831917347, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('OMR', 0.384497000000, 2.600800526402, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PAB', 1.000000000000, 1.000000000000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PEN', 3.354105000000, 0.298142127334, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PGK', 4.293425000000, 0.232914281721, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PHP', 57.928642000000, 0.017262617687, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PKR', 279.627266000000, 0.003576189169, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PLN', 3.549814000000, 0.281704900595, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('PYG', 6620.610771000000, 0.000151043466, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('QAR', 3.640000000000, 0.274725274725, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('RON', 4.293779000000, 0.232895079137, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('RSD', 98.937788000000, 0.010107361608, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('RUB', 77.204946000000, 0.012952538041, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('RWF', 1460.944765000000, 0.000684488575, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SAR', 3.750000000000, 0.266666666667, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SBD', 7.924432000000, 0.126192009724, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SCR', 13.732763000000, 0.072818558072, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SDG', 510.719196000000, 0.001958023133, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SEK', 8.930325000000, 0.111978007519, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SGD', 1.262867000000, 0.791849022898, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SHP', 0.733326000000, 1.363650000136, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SLE', 24.619110000000, 0.040618852591, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SLL', 24619.109631000000, 0.000040618853, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SOS', 570.778886000000, 0.001751991926, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SRD', 37.926360000000, 0.026366885723, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SSP', 4582.062810000000, 0.000218242316, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('STN', 20.646385000000, 0.048434629113, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SYP', 114.012369000000, 0.008770978173, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('SZL', 15.962058000000, 0.062648563237, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('THB', 31.070125000000, 0.032185258347, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TJS', 9.349487000000, 0.106957740034, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TMT', 3.499758000000, 0.285734042182, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TND', 2.846541000000, 0.351303564572, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TOP', 2.354256000000, 0.424762642635, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TRY', 43.719470000000, 0.022873104363, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TTD', 6.739556000000, 0.148377726960, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TVD', 1.414545000000, 0.706941101202, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TWD', 31.367841000000, 0.031879784139, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('TZS', 2567.605897000000, 0.000389467870, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('UAH', 43.109849000000, 0.023196555386, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('UGX', 3511.760430000000, 0.000284757466, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('USD', 1.000000000000, 1.000000000000, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('UYU', 38.353516000000, 0.026073228853, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('UZS', 12235.439624000000, 0.000081729797, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('VES', 396.367400000000, 0.002522911824, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('VND', 25942.719198000000, 0.000038546460, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('VUV', 119.258312000000, 0.008385159770, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('WST', 2.671622000000, 0.374304448758, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('XAF', 552.781253000000, 0.001809033853, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('XCD', 2.700000000000, 0.370370370370, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('XCG', 1.790000000000, 0.558659217877, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('XDR', 0.725187000000, 1.378954669623, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('XOF', 552.781253000000, 0.001809033853, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('XPF', 100.562221000000, 0.009944092225, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('YER', 238.344129000000, 0.004195614149, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ZAR', 15.958708000000, 0.062661714219, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ZMW', 18.450328000000, 0.054199578457, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ZWG', 25.564300000000, 0.039117049949, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08'),
('ZWL', 25.564300000000, 0.039117049949, 'open.er-api.com', '2026-02-14 00:02:31', '2026-02-14 11:08:08', '2026-02-14 11:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `mirror_domains`
--

CREATE TABLE `mirror_domains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `domain` varchar(190) NOT NULL,
  `template_view` varchar(32) NOT NULL DEFAULT 'simple',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mirror_domains`
--

INSERT INTO `mirror_domains` (`id`, `domain`, `template_view`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'portcore.ru', 'simple', 1, '2026-02-22 14:29:19', NULL),
(30, '109.71.247.34', 'simple', 1, '2026-02-22 14:31:06', NULL),
(38, 'portcore.online', 'simple', 1, '2026-02-22 14:37:49', NULL),
(3267, 'autodiscover.portcore.ru', 'simple', 1, '2026-02-22 16:00:20', NULL),
(3270, 'mail.portcore.ru', 'simple', 1, '2026-02-22 16:01:23', NULL),
(3272, 'owa.portcore.ru', 'simple', 1, '2026-02-22 16:01:45', NULL),
(3293, 'cloudflare.com', 'simple', 1, '2026-02-22 16:32:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mirror_routes`
--

CREATE TABLE `mirror_routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `route_type` enum('page','section_page') NOT NULL DEFAULT 'page',
  `route_name` varchar(64) NOT NULL,
  `page_name` varchar(64) NOT NULL DEFAULT '',
  `display_name` varchar(190) NOT NULL DEFAULT '',
  `view_name` varchar(64) NOT NULL DEFAULT 'main',
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `seo_description` text DEFAULT NULL,
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `og_title` varchar(255) NOT NULL DEFAULT '',
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) NOT NULL DEFAULT '',
  `seo_noindex` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mirror_templates`
--

CREATE TABLE `mirror_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_key` varchar(64) NOT NULL,
  `display_name` varchar(190) NOT NULL,
  `shell_view` varchar(32) NOT NULL DEFAULT 'simple',
  `main_view_file` varchar(128) NOT NULL DEFAULT 'main.php',
  `model_file` varchar(128) NOT NULL DEFAULT 'main.php',
  `control_file` varchar(128) NOT NULL DEFAULT 'main.php',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mirror_templates`
--

INSERT INTO `mirror_templates` (`id`, `template_key`, `display_name`, `shell_view`, `main_view_file`, `model_file`, `control_file`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'simple', 'Public Site', 'simple', 'main.php', 'main.php', 'main.php', 1, '2026-02-22 14:29:19', '2026-02-22 14:29:19'),
(2, 'dashboard', 'Dashboard UI', 'dashboard', 'main.php', 'main.php', 'main.php', 1, '2026-02-22 14:29:19', '2026-02-22 14:29:19'),
(3, 'enterprise', 'Enterprise Site', 'enterprise', 'main.php', 'main.php', 'main.php', 1, '2026-02-22 14:29:19', '2026-02-22 14:29:19');

-- --------------------------------------------------------

--
-- Table structure for table `seo_article_cron_runs`
--

CREATE TABLE `seo_article_cron_runs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_date` date NOT NULL,
  `lang_code` varchar(5) NOT NULL,
  `slot_index` tinyint(3) UNSIGNED NOT NULL,
  `planned_at` datetime NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `message` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_generator_logs`
--

CREATE TABLE `seo_generator_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_date` date DEFAULT NULL,
  `lang_code` varchar(5) NOT NULL DEFAULT 'en',
  `slot_index` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(24) NOT NULL DEFAULT 'success',
  `is_dry_run` tinyint(1) NOT NULL DEFAULT 0,
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `article_url` varchar(1024) NOT NULL DEFAULT '',
  `words_final` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `words_initial` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `structure_used` varchar(1024) NOT NULL DEFAULT '',
  `topic_analysis_source` varchar(64) NOT NULL DEFAULT '',
  `topic_analysis_summary` text DEFAULT NULL,
  `topic_bans_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image_request_json` longtext DEFAULT NULL,
  `image_result_json` longtext DEFAULT NULL,
  `article_request_json` longtext DEFAULT NULL,
  `article_result_json` longtext DEFAULT NULL,
  `llm_usage_json` longtext DEFAULT NULL,
  `llm_requests_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `llm_prompt_tokens` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `llm_completion_tokens` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `llm_total_tokens` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `settings_snapshot_json` longtext DEFAULT NULL,
  `tg_preview_result_json` longtext DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_generator_settings`
--

CREATE TABLE `seo_generator_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `settings_json` longtext NOT NULL,
  `updated_by_admin_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo_generator_settings`
--

INSERT INTO `seo_generator_settings` (`id`, `settings_json`, `updated_by_admin_id`, `created_at`, `updated_at`) VALUES
(1, '{\"enabled\":true,\"langs\":[\"en\",\"ru\"],\"domain_host\":\"\",\"author_name\":\"GeoIP Team\",\"daily_min\":2,\"daily_max\":3,\"max_per_run\":10,\"word_min\":2000,\"word_max\":5000,\"today_first_delay_min\":15,\"auto_expand_retries\":4,\"expand_context_chars\":7000,\"prompt_version\":\"seo-cron-v1\",\"seed_salt\":\"geoip-seo-articles\",\"notify_schedule\":false,\"notify_daily_schedule\":true,\"llm_provider\":\"openrouter\",\"openai_api_key\":\"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\",\"openai_base_url\":\"https://openrouter.ai/api/v1\",\"openai_model\":\"openai/gpt-4o-2024-11-20\",\"openai_timeout\":120,\"openai_headers\":[],\"openrouter_api_key\":\"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\",\"openrouter_base_url\":\"https://openrouter.ai/api/v1\",\"openrouter_model\":\"google/gemini-2.0-flash-001\",\"openai_proxy_enabled\":false,\"openai_proxy_host\":\"\",\"openai_proxy_port\":0,\"openai_proxy_type\":\"http\",\"openai_proxy_username\":\"\",\"openai_proxy_password\":\"\",\"openai_proxy_pool_enabled\":true,\"openai_proxy_pool\":[\"139.144.26.187:10761:modeler_pOFdGR:RB3uc2qEncMB\"],\"topic_analysis_enabled\":true,\"topic_analysis_limit\":120,\"topic_analysis_system_prompt\":\"\",\"topic_analysis_user_prompt_append\":\"\",\"styles_en\":[\"technical guide\",\"advanced technical guide\",\"deep dive technical analysis\",\"engineering handbook\",\"implementation tutorial\",\"step-by-step implementation\",\"hands-on lab guide\",\"practical coding walkthrough\",\"API integration guide\",\"SDK integration walkthrough\",\"architecture note\",\"architecture blueprint\",\"reference architecture\",\"system design document\",\"solution design memo\",\"technical design review\",\"infrastructure playbook\",\"operational playbook\",\"security playbook\",\"fraud prevention playbook\",\"SOC playbook\",\"incident response guide\",\"postmortem breakdown\",\"root cause analysis\",\"threat modeling guide\",\"risk assessment framework\",\"decision-making framework\",\"governance guideline\",\"compliance checklist\",\"audit preparation guide\",\"benchmark study\",\"performance optimization guide\",\"scalability guide\",\"high-availability design note\",\"resilience engineering guide\",\"production hardening manual\",\"monitoring and observability guide\",\"DevOps integration note\",\"CI/CD integration guide\",\"data engineering manual\",\"ML feature engineering guide\",\"model validation report\",\"research paper style\",\"evidence-based analysis\",\"case study deep dive\",\"real-world case analysis\",\"before-and-after comparison\",\"migration guide\",\"upgrade roadmap\",\"best practices compendium\",\"anti-pattern catalog\",\"myth-busting article\",\"opinionated expert column\",\"industry outlook analysis\",\"trend report\",\"executive brief\",\"C-level overview\",\"founder memo\",\"strategy paper\",\"product positioning note\",\"market analysis brief\",\"competitive landscape review\",\"thought leadership article\",\"vision paper\",\"future trends exploration\",\"technical FAQ\",\"extended FAQ guide\",\"knowledge base article\",\"quick reference sheet\",\"cheat sheet\",\"hands-on workshop outline\",\"training material format\",\"developer documentation style\",\"API reference style article\",\"framework-driven explanation\",\"methodology-driven article\",\"graph-based modeling walkthrough\",\"data-driven investigation\",\"experiment report\",\"simulation walkthrough\",\"field report\",\"lessons learned article\",\"retrospective analysis\",\"play-by-play breakdown\",\"checklist-driven guide\",\"minimal viable implementation guide\",\"zero-to-production guide\",\"advanced expert commentary\",\"practical field manual\",\"security advisory style\",\"technical bulletin\",\"engineering war story\",\"problem-solution format\",\"question-driven exploration\",\"use-case catalog\",\"integration blueprint\",\"technical whitepaper\",\"enterprise solution overview\",\"operational readiness guide\",\"runbook format\",\"automation handbook\",\"privacy impact assessment style\",\"risk operations memo\",\"architecture decision record style\"],\"styles_ru\":[\"экспертный\",\"глубоко экспертный\",\"архитектурный\",\"архитектурное ревью\",\"технический разбор\",\"инженерный разбор\",\"инфраструктурный\",\"security-ориентированный\",\"антифрод-разбор\",\"risk-ops формат\",\"пошаговый\",\"детальный пошаговый\",\"от нуля до продакшена\",\"практический\",\"практико-ориентированный\",\"практический playbook\",\"операционный playbook\",\"SOC playbook\",\"инцидентный разбор\",\"формат постмортема\",\"разбор root cause\",\"аналитический\",\"глубоко аналитический\",\"данные и метрики\",\"научно-аналитический\",\"исследовательский\",\"экспериментальный\",\"сравнительный\",\"бенчмаркинг\",\"аудиторский\",\"чеклист-ориентированный\",\"методологический\",\"framework-подход\",\"гайд по внедрению\",\"интеграционный гайд\",\"API-ориентированный\",\"DevOps-формат\",\"ML-разбор\",\"data science формат\",\"графовая модель\",\"whitepaper стиль\",\"короткий гайд\",\"расширенный гайд\",\"FAQ-формат\",\"мифы и реальность\",\"контр-интуитивный взгляд\",\"стратегический обзор\",\"обзор для руководителей\",\"C-level резюме\",\"консалтинговый стиль\",\"продуктовый разбор\",\"рынок и стратегия\",\"vision-статья\",\"прогнозный обзор\",\"ретроспектива\",\"кейс с цифрами\",\"кейсовый разбор\",\"реальный инцидент\",\"сценарный анализ\",\"боевой опыт\",\"war story формат\",\"каталог антипаттернов\",\"каталог use-case\",\"инструкция для продакшена\",\"hardening-гайд\",\"performance-разбор\",\"scalability-разбор\",\"observability-обзор\",\"compliance-ориентированный\",\"privacy-first формат\",\"zero-trust подход\",\"архитектурное решение (ADR)\",\"операционный runbook\",\"руководство по автоматизации\",\"технический бюллетень\",\"инженерная записка\"],\"clusters_en\":[\"Scalable SaaS product architecture\",\"High-availability microservices design\",\"Enterprise system business logic integration\",\"CI/CD automation strategies\",\"DevOps practices for high-load applications\",\"Event-driven platform design\",\"Cloud performance optimization\",\"Secure API integration architecture\",\"Application observability and monitoring\",\"Cross-platform system design\",\"ML-ready architecture for SaaS solutions\",\"Modular architecture for startups\",\"Secrets and configuration management\",\"Marketplace backend scalability\",\"Resilient system design\",\"Business process automation solutions\",\"Product dashboard and analytics architecture\",\"Third-party service integration\",\"Transactional workflow management\",\"Modular architecture for agile teams\",\"Zero-trust access architecture\",\"Legacy-to-cloud migration strategy\",\"Data quality monitoring tools\",\"Customer support workflow automation\",\"Complex B2B product design\",\"SDK and plugin development\",\"Application architecture consulting\",\"Caching and optimization strategies\",\"Microservice orchestration with SLA\",\"Observability and DevSecOps best practices\",\"FinTech and payment system integration\",\"API-first platform design\",\"Event sourcing and CQRS architecture\",\"Release process automation and testing\",\"Startup platform scaling strategies\",\"Message queue and event bus architecture\",\"Embedded business analytics architecture\",\"Security and access control frameworks\",\"Executive reporting automation\",\"Portfolio product showcase development\",\"Technology selection consulting\",\"Data pipeline and ETL orchestration\",\"Load balancing and high-performance design\",\"Resilient design pattern implementation\",\"Cloud cost optimization strategies\",\"Multi-channel platform architecture\",\"Agile workflow system design\",\"DevSecOps policy automation\",\"Technical solution consulting\",\"Integration test automation\",\"Logging and observability frameworks\",\"Emerging technology adoption\",\"Hybrid cloud architecture\",\"Legacy-modern system integration\",\"Portfolio and showcase tool development\",\"Product strategy and architecture workshops\",\"Business-oriented system design\",\"Continuous improvement for enterprise apps\",\"API governance frameworks\",\"Cloud-native application design\",\"Distributed transaction management\",\"Feature toggling and deployment strategies\",\"Real-time analytics architecture\",\"Enterprise workflow automation\",\"Data-driven decision architecture\",\"Application scalability benchmarking\",\"Resilient API architecture\",\"Event-driven automation pipelines\",\"Performance monitoring dashboards\",\"Cloud resource orchestration\",\"High-frequency transaction design\",\"User journey orchestration systems\",\"Security-by-design architecture\",\"Digital product compliance frameworks\",\"Integration architecture for multi-tenant SaaS\",\"Real-time feedback and alerting systems\",\"Portfolio case studies for clients\",\"Data observability and lineage tracking\",\"Automated reporting pipelines\",\"Continuous deployment for mission-critical systems\",\"Cloud cost and resource optimization frameworks\",\"Client onboarding workflow automation\",\"Enterprise feature store design\",\"Adaptive system architecture patterns\",\"Risk-aware application design\",\"Business continuity architecture\",\"Digital trust frameworks\",\"Cloud infrastructure strategy consulting\",\"Cross-functional team workflow design\",\"Full-stack architecture blueprinting\",\"System observability for enterprise apps\",\"Data-driven product architecture\",\"Infrastructure as code adoption\",\"Security and compliance automation\",\"Event stream monitoring and dashboards\",\"API versioning and lifecycle management\",\"High-performance caching strategies\",\"Resilient messaging systems\",\"Automation pipelines for digital products\",\"Business outcome-oriented architecture\"],\"clusters_ru\":[\"Scalable SaaS product architecture\",\"High-availability microservices design\",\"Enterprise system business logic integration\",\"CI/CD automation strategies\",\"DevOps practices for high-load applications\",\"Event-driven platform design\",\"Cloud performance optimization\",\"Secure API integration architecture\",\"Application observability and monitoring\",\"Cross-platform system design\",\"ML-ready architecture for SaaS solutions\",\"Modular architecture for startups\",\"Secrets and configuration management\",\"Marketplace backend scalability\",\"Resilient system design\",\"Business process automation solutions\",\"Product dashboard and analytics architecture\",\"Third-party service integration\",\"Transactional workflow management\",\"Modular architecture for agile teams\",\"Zero-trust access architecture\",\"Legacy-to-cloud migration strategy\",\"Data quality monitoring tools\",\"Customer support workflow automation\",\"Complex B2B product design\",\"SDK and plugin development\",\"Application architecture consulting\",\"Caching and optimization strategies\",\"Microservice orchestration with SLA\",\"Observability and DevSecOps best practices\",\"FinTech and payment system integration\",\"API-first platform design\",\"Event sourcing and CQRS architecture\",\"Release process automation and testing\",\"Startup platform scaling strategies\",\"Message queue and event bus architecture\",\"Embedded business analytics architecture\",\"Security and access control frameworks\",\"Executive reporting automation\",\"Portfolio product showcase development\",\"Technology selection consulting\",\"Data pipeline and ETL orchestration\",\"Load balancing and high-performance design\",\"Resilient design pattern implementation\",\"Cloud cost optimization strategies\",\"Multi-channel platform architecture\",\"Agile workflow system design\",\"DevSecOps policy automation\",\"Technical solution consulting\",\"Integration test automation\",\"Logging and observability frameworks\",\"Emerging technology adoption\",\"Hybrid cloud architecture\",\"Legacy-modern system integration\",\"Portfolio and showcase tool development\",\"Product strategy and architecture workshops\",\"Business-oriented system design\",\"Continuous improvement for enterprise apps\",\"API governance frameworks\",\"Cloud-native application design\",\"Distributed transaction management\",\"Feature toggling and deployment strategies\",\"Real-time analytics architecture\",\"Enterprise workflow automation\",\"Data-driven decision architecture\",\"Application scalability benchmarking\",\"Resilient API architecture\",\"Event-driven automation pipelines\",\"Performance monitoring dashboards\",\"Cloud resource orchestration\",\"High-frequency transaction design\",\"User journey orchestration systems\",\"Security-by-design architecture\",\"Digital product compliance frameworks\",\"Integration architecture for multi-tenant SaaS\",\"Real-time feedback and alerting systems\",\"Portfolio case studies for clients\",\"Data observability and lineage tracking\",\"Automated reporting pipelines\",\"Continuous deployment for mission-critical systems\",\"Cloud cost and resource optimization frameworks\",\"Client onboarding workflow automation\",\"Enterprise feature store design\",\"Adaptive system architecture patterns\",\"Risk-aware application design\",\"Business continuity architecture\",\"Digital trust frameworks\",\"Cloud infrastructure strategy consulting\",\"Cross-functional team workflow design\",\"Full-stack architecture blueprinting\",\"System observability for enterprise apps\",\"Data-driven product architecture\",\"Infrastructure as code adoption\",\"Security and compliance automation\",\"Event stream monitoring and dashboards\",\"API versioning and lifecycle management\",\"High-performance caching strategies\",\"Resilient messaging systems\",\"Automation pipelines for digital products\",\"Business outcome-oriented architecture\",\"Архитектура масштабируемых SaaS-продуктов\",\"Проектирование отказоустойчивых микросервисов\",\"Интеграция бизнес-логики в корпоративные системы\",\"Автоматизация CI/CD процессов\",\"DevOps практики для высоконагруженных приложений\",\"Проектирование event-driven платформ\",\"Оптимизация производительности облачных решений\",\"Безопасная интеграция API\",\"Системы наблюдаемости и мониторинга приложений\",\"Кросс-платформенный системный дизайн\",\"ML-ready архитектура для SaaS\",\"Модульная архитектура для стартапов\",\"Управление конфигурациями и секретами\",\"Масштабируемый бэкенд маркетплейсов\",\"Проектирование отказоустойчивых систем\",\"Автоматизация бизнес-процессов\",\"Архитектура дашбордов и аналитики\",\"Интеграция сторонних сервисов\",\"Управление транзакционными потоками\",\"Модульная архитектура для agile-команд\"],\"article_structures_en\":[\"Hook -> Market context -> Threat landscape -> Technical breakdown -> Implementation walkthrough -> Metrics -> Conclusion\",\"Scenario narrative -> Detection logic -> Architecture diagram explanation -> Code samples -> Validation strategy -> Summary\",\"Problem statement -> Data inputs -> Signal analysis -> Scoring model -> Integration guide -> Monitoring plan -> Wrap-up\",\"Executive overview -> Risk taxonomy -> System design -> API contract -> Edge cases -> Final thoughts\",\"Use case spotlight -> Risk indicators -> Data flow -> Deployment steps -> Observability -> CTA\",\"Comparison table -> Tradeoffs -> Reference architecture -> Code snippets -> Operational checklist -> Conclusion\",\"Architecture-first -> Components -> Data pipelines -> Failure modes -> Hardening tactics -> Outcome\",\"Research-backed intro -> Data evidence -> Modeling approach -> Feature engineering -> Production notes -> Summary\",\"Step-by-step lab -> Environment setup -> Sample payloads -> Risk evaluation -> Logging strategy -> Final notes\",\"Customer journey view -> Trust signals -> Risk gates -> Backend logic -> Dashboarding -> Recommendations\",\"Red team perspective -> Attack simulation -> Detection signals -> Countermeasures -> Code references -> Lessons learned\",\"Blue team guide -> Alert triage -> Investigation workflow -> Geo pivots -> Automation scripts -> Prevention\",\"Threat model canvas -> Assumptions -> Abuse paths -> Mitigation layers -> Implementation notes -> Conclusion\",\"API integration guide -> Authentication -> Request validation -> Geo enrichment -> Error handling -> Deployment checklist\",\"Data science angle -> Feature extraction -> Model training -> Evaluation metrics -> Drift detection -> Summary\",\"Zero to production -> MVP flow -> Scaling strategy -> Resilience design -> SLA considerations -> Wrap-up\",\"Audit-focused structure -> Control objectives -> Risk mapping -> Technical validation -> Reporting -> Outcome\",\"Case study deep dive -> Baseline state -> Fraud incident -> Geo signal analysis -> Remediation steps -> Insights\",\"Engineering memo -> Context -> Decision log -> Alternatives considered -> Final architecture -> Impact\",\"Migration playbook -> Legacy flow -> New geo engine -> Testing matrix -> Rollout phases -> Post-launch review\",\"Performance focus -> Latency budget -> Caching layer -> Load testing -> Optimization tactics -> Results\",\"Compliance-driven -> Regulatory need -> Geo validation rules -> Logging requirements -> Audit readiness -> Conclusion\",\"Product strategy view -> Market gap -> Geo differentiation -> Pricing impact -> Adoption plan -> Roadmap\",\"FAQ-driven deep dive -> Expanded answers -> Real configs -> Edge cases -> Reference code -> Wrap-up\",\"Checklist-first -> Environment checks -> Risk rule setup -> Integration steps -> Monitoring controls -> Conclusion\",\"Failure analysis -> Symptoms -> Root cause isolation -> Geo anomalies -> Patch implementation -> Safeguards\",\"DevOps narrative -> CI/CD integration -> Geo service dependency -> Observability stack -> Alert tuning -> Outcome\",\"Graph-based modeling -> Entity relationships -> Geo nodes -> Risk propagation -> Visualization -> Conclusion\",\"Benchmark study -> Dataset description -> Methodology -> Geo findings -> Performance metrics -> Summary\",\"Hands-on workshop -> Scenario setup -> Geo enrichment demo -> Risk scoring demo -> Debugging -> Takeaways\",\"Decision framework -> Risk appetite -> Geo thresholds -> Escalation logic -> Governance model -> Wrap-up\",\"Incident timeline -> Detection moment -> Geo trace reconstruction -> Fix rollout -> Long-term controls -> Lessons\",\"Design document style -> Requirements -> Constraints -> System blocks -> API schema -> Security review -> Conclusion\"],\"article_structures_ru\":[\"Хук -> Контекст рынка -> Карта угроз -> Технический разбор -> Внедрение -> Метрики -> Заключение\",\"Сценарий инцидента -> Логика детекта -> Архитектурная схема -> Примеры кода -> Валидация -> Итоги\",\"Постановка задачи -> Источники данных -> Анализ сигналов -> Модель скоринга -> Интеграция -> Мониторинг -> Вывод\",\"Обзор для руководства -> Классификация рисков -> Дизайн системы -> API-контракт -> Граничные случаи -> Итоги\",\"Фокус на кейсе -> Индикаторы риска -> Поток данных -> Шаги деплоя -> Наблюдаемость -> CTA\",\"Сравнительная таблица -> Компромиссы -> Референс-архитектура -> Сниппеты -> Операционный чеклист -> Заключение\",\"Архитектурный разбор -> Компоненты -> Data-пайплайны -> Точки отказа -> Усиление -> Результат\",\"Исследовательский формат -> Данные -> Методология -> Фичи -> Production-заметки -> Итоги\",\"Лабораторный формат -> Подготовка среды -> Пример payload -> Оценка риска -> Логирование -> Вывод\",\"Путь пользователя -> Trust-сигналы -> Risk-gates -> Backend-логика -> Дашборды -> Рекомендации\",\"Red team взгляд -> Симуляция атаки -> Сигналы детекта -> Контрмеры -> Код -> Уроки\",\"Blue team руководство -> Триаж алертов -> Расследование -> Geo pivot -> Автоматизация -> Профилактика\",\"Threat model canvas -> Допущения -> Векторы злоупотребления -> Слои защиты -> Реализация -> Итог\",\"Гайд по API интеграции -> Авторизация -> Валидация запроса -> Geo enrichment -> Обработка ошибок -> Чеклист\",\"Data science подход -> Извлечение фич -> Обучение модели -> Метрики -> Детект дрейфа -> Итоги\",\"От MVP к production -> Базовый флоу -> Масштабирование -> Отказоустойчивость -> SLA -> Вывод\",\"Аудиторский формат -> Контрольные цели -> Карта рисков -> Техпроверка -> Отчетность -> Результат\",\"Глубокий кейс -> Исходное состояние -> Инцидент -> Анализ геосигналов -> Исправление -> Инсайты\",\"Инженерная записка -> Контекст -> Лог решений -> Альтернативы -> Итоговая архитектура -> Эффект\",\"Миграционный playbook -> Старый процесс -> Новый geo-движок -> Матрица тестов -> Этапы запуска -> Анализ\",\"Фокус на производительности -> Бюджет задержки -> Кэш -> Нагрузочное тестирование -> Оптимизация -> Результат\",\"Комплаенс-ориентированный формат -> Регуляторное требование -> Geo-правила -> Логирование -> Готовность к аудиту -> Итог\",\"Продуктовый взгляд -> Рыночная ниша -> Geo-дифференциация -> Влияние на цену -> План внедрения -> Roadmap\",\"Расширенный FAQ -> Подробные ответы -> Реальные конфиги -> Edge-cases -> Код -> Вывод\",\"Чеклист-ориентированный формат -> Проверка среды -> Настройка правил -> Интеграция -> Контроль мониторинга -> Итог\",\"Анализ отказа -> Симптомы -> Изоляция причины -> Geo-аномалии -> Патч -> Защита\",\"DevOps-формат -> CI/CD -> Зависимости GeoIP -> Наблюдаемость -> Настройка алертов -> Результат\",\"Графовая модель -> Связи сущностей -> Geo-узлы -> Распространение риска -> Визуализация -> Итоги\",\"Бенчмаркинг -> Описание датасета -> Метод -> Geo-выводы -> Метрики -> Итог\",\"Практический воркшоп -> Подготовка сценария -> Демонстрация enrichment -> Скоринг -> Дебаг -> Вывод\",\"Фреймворк принятия решения -> Risk appetite -> Geo-пороги -> Эскалация -> Governance -> Заключение\",\"Хронология инцидента -> Момент детекта -> Реконструкция geo-трейса -> Релиз фикса -> Долгосрочные меры -> Уроки\",\"Формат дизайн-документа -> Требования -> Ограничения -> Блоки системы -> API-схема -> Security review -> Итог\"],\"moods\":[{\"key\":\"technical\",\"weight\":1,\"label_en\":\"technical article\",\"label_ru\":\"техническая статья\"},{\"key\":\"deep_technical\",\"weight\":0.95,\"label_en\":\"deep technical analysis\",\"label_ru\":\"глубокий технический разбор\"},{\"key\":\"architecture_focused\",\"weight\":0.95,\"label_en\":\"architecture-focused\",\"label_ru\":\"архитектурный разбор\"},{\"key\":\"code_heavy\",\"weight\":0.95,\"label_en\":\"code-heavy tutorial\",\"label_ru\":\"статья с упором на код\"},{\"key\":\"api_practical\",\"weight\":0.9,\"label_en\":\"API practical guide\",\"label_ru\":\"практическое руководство по API\"},{\"key\":\"infra_engineering\",\"weight\":0.9,\"label_en\":\"infrastructure engineering\",\"label_ru\":\"инфраструктурный разбор\"},{\"key\":\"security_engineering\",\"weight\":0.95,\"label_en\":\"security engineering\",\"label_ru\":\"security-инжиниринг\"},{\"key\":\"detection_engineering\",\"weight\":0.9,\"label_en\":\"detection engineering\",\"label_ru\":\"разбор detection engineering\"},{\"key\":\"data_engineering\",\"weight\":0.85,\"label_en\":\"data engineering\",\"label_ru\":\"data-инжиниринг\"},{\"key\":\"ml_engineering\",\"weight\":0.85,\"label_en\":\"ML engineering\",\"label_ru\":\"ML-инжиниринг\"},{\"key\":\"product_engineering\",\"weight\":0.85,\"label_en\":\"product engineering\",\"label_ru\":\"продуктово-технический\"},{\"key\":\"b2b_oriented\",\"weight\":0.9,\"label_en\":\"B2B-oriented\",\"label_ru\":\"b2b ориентированная\"},{\"key\":\"enterprise_focus\",\"weight\":0.9,\"label_en\":\"enterprise-focused\",\"label_ru\":\"ориентированная на enterprise\"},{\"key\":\"c_level_summary\",\"weight\":0.75,\"label_en\":\"C-level summary\",\"label_ru\":\"ориентированная на руководителей\"},{\"key\":\"founder_perspective\",\"weight\":0.75,\"label_en\":\"founder perspective\",\"label_ru\":\"взгляд основателя\"},{\"key\":\"consulting_style\",\"weight\":0.85,\"label_en\":\"consulting-style\",\"label_ru\":\"консалтинговый стиль\"},{\"key\":\"risk_management\",\"weight\":0.85,\"label_en\":\"risk management oriented\",\"label_ru\":\"риск-ориентированная\"},{\"key\":\"compliance_driven\",\"weight\":0.8,\"label_en\":\"compliance-driven\",\"label_ru\":\"комплаенс-ориентированная\"},{\"key\":\"fintech_oriented\",\"weight\":0.85,\"label_en\":\"fintech-oriented\",\"label_ru\":\"финтех-ориентированная\"},{\"key\":\"scientific\",\"weight\":0.7,\"label_en\":\"scientific\",\"label_ru\":\"научная\"},{\"key\":\"research_style\",\"weight\":0.8,\"label_en\":\"research-style paper\",\"label_ru\":\"формат исследования\"},{\"key\":\"data_backed\",\"weight\":0.85,\"label_en\":\"data-backed\",\"label_ru\":\"основанная на данных\"},{\"key\":\"evidence_based\",\"weight\":0.85,\"label_en\":\"evidence-based\",\"label_ru\":\"доказательная\"},{\"key\":\"analytical\",\"weight\":0.8,\"label_en\":\"analytical\",\"label_ru\":\"аналитическая\"},{\"key\":\"experimental\",\"weight\":0.7,\"label_en\":\"experimental\",\"label_ru\":\"экспериментальная\"},{\"key\":\"modeling_focus\",\"weight\":0.75,\"label_en\":\"modeling-focused\",\"label_ru\":\"фокус на моделировании\"},{\"key\":\"philosophical\",\"weight\":0.35,\"label_en\":\"philosophical\",\"label_ru\":\"философская\"},{\"key\":\"strategic_reflection\",\"weight\":0.5,\"label_en\":\"strategic reflection\",\"label_ru\":\"стратегическое размышление\"},{\"key\":\"future_trends\",\"weight\":0.6,\"label_en\":\"future trends\",\"label_ru\":\"взгляд в будущее\"},{\"key\":\"industry_outlook\",\"weight\":0.65,\"label_en\":\"industry outlook\",\"label_ru\":\"отраслевой обзор\"},{\"key\":\"visionary\",\"weight\":0.45,\"label_en\":\"visionary\",\"label_ru\":\"визионерская\"},{\"key\":\"historical_entertaining\",\"weight\":0.2,\"label_en\":\"historical-entertaining\",\"label_ru\":\"историческо-развлекательная\"},{\"key\":\"retrospective\",\"weight\":0.5,\"label_en\":\"retrospective\",\"label_ru\":\"ретроспективная\"},{\"key\":\"origin_story\",\"weight\":0.35,\"label_en\":\"origin story\",\"label_ru\":\"история происхождения\"},{\"key\":\"market_evolution\",\"weight\":0.55,\"label_en\":\"market evolution\",\"label_ru\":\"эволюция рынка\"},{\"key\":\"timeline_analysis\",\"weight\":0.55,\"label_en\":\"timeline analysis\",\"label_ru\":\"анализ по временной шкале\"},{\"key\":\"case_with_examples\",\"weight\":0.9,\"label_en\":\"case study with examples\",\"label_ru\":\"кейс с примерами\"},{\"key\":\"real_incident\",\"weight\":0.9,\"label_en\":\"real incident breakdown\",\"label_ru\":\"разбор реального инцидента\"},{\"key\":\"postmortem_style\",\"weight\":0.9,\"label_en\":\"incident postmortem\",\"label_ru\":\"формат постмортема\"},{\"key\":\"playbook_style\",\"weight\":0.9,\"label_en\":\"operational playbook\",\"label_ru\":\"формат playbook\"},{\"key\":\"step_by_step\",\"weight\":0.9,\"label_en\":\"step-by-step guide\",\"label_ru\":\"пошаговое руководство\"},{\"key\":\"checklist_driven\",\"weight\":0.85,\"label_en\":\"checklist-driven\",\"label_ru\":\"чеклист-ориентированная\"},{\"key\":\"practical_hands_on\",\"weight\":0.95,\"label_en\":\"hands-on practical\",\"label_ru\":\"практическая hands-on\"},{\"key\":\"comparison_style\",\"weight\":0.8,\"label_en\":\"comparison analysis\",\"label_ru\":\"сравнительный анализ\"},{\"key\":\"benchmarking\",\"weight\":0.8,\"label_en\":\"benchmarking study\",\"label_ru\":\"бенчмаркинг\"},{\"key\":\"audit_style\",\"weight\":0.85,\"label_en\":\"audit-style\",\"label_ru\":\"аудиторский формат\"},{\"key\":\"threat_intel_focus\",\"weight\":0.85,\"label_en\":\"threat intelligence focused\",\"label_ru\":\"с фокусом на threat intel\"},{\"key\":\"soc_oriented\",\"weight\":0.85,\"label_en\":\"SOC-oriented\",\"label_ru\":\"ориентированная на SOC\"},{\"key\":\"developer_friendly\",\"weight\":0.95,\"label_en\":\"developer-friendly\",\"label_ru\":\"ориентированная на разработчиков\"},{\"key\":\"minimalist_explainer\",\"weight\":0.65,\"label_en\":\"minimalist explainer\",\"label_ru\":\"лаконичное объяснение\"},{\"key\":\"advanced_expert\",\"weight\":0.95,\"label_en\":\"advanced expert level\",\"label_ru\":\"экспертный уровень\"},{\"key\":\"opinionated\",\"weight\":0.55,\"label_en\":\"opinionated\",\"label_ru\":\"с выраженной позицией автора\"},{\"key\":\"contrarian\",\"weight\":0.5,\"label_en\":\"contrarian view\",\"label_ru\":\"контр-интуитивный взгляд\"},{\"key\":\"framework_based\",\"weight\":0.85,\"label_en\":\"framework-based\",\"label_ru\":\"основанная на фреймворке\"},{\"key\":\"methodology_driven\",\"weight\":0.9,\"label_en\":\"methodology-driven\",\"label_ru\":\"методологическая\"},{\"key\":\"implementation_focused\",\"weight\":0.95,\"label_en\":\"implementation-focused\",\"label_ru\":\"ориентированная на внедрение\"},{\"key\":\"architecture_review\",\"weight\":0.9,\"label_en\":\"architecture review\",\"label_ru\":\"архитектурное ревью\"},{\"key\":\"governance_oriented\",\"weight\":0.8,\"label_en\":\"governance-oriented\",\"label_ru\":\"ориентированная на governance\"},{\"key\":\"risk_ops_style\",\"weight\":0.85,\"label_en\":\"risk operations style\",\"label_ru\":\"формат risk-ops\"},{\"key\":\"product_strategy\",\"weight\":0.75,\"label_en\":\"product strategy\",\"label_ru\":\"продуктовая стратегия\"},{\"key\":\"growth_security\",\"weight\":0.75,\"label_en\":\"growth-security balance\",\"label_ru\":\"баланс роста и безопасности\"},{\"key\":\"api_first\",\"weight\":0.9,\"label_en\":\"API-first\",\"label_ru\":\"API-ориентированная\"},{\"key\":\"performance_focused\",\"weight\":0.85,\"label_en\":\"performance-focused\",\"label_ru\":\"ориентированная на производительность\"},{\"key\":\"cost_optimization\",\"weight\":0.75,\"label_en\":\"cost optimization\",\"label_ru\":\"оптимизация затрат\"},{\"key\":\"scalability_focus\",\"weight\":0.9,\"label_en\":\"scalability-focused\",\"label_ru\":\"фокус на масштабировании\"},{\"key\":\"observability_focus\",\"weight\":0.85,\"label_en\":\"observability-focused\",\"label_ru\":\"фокус на наблюдаемости\"},{\"key\":\"automation_driven\",\"weight\":0.9,\"label_en\":\"automation-driven\",\"label_ru\":\"ориентированная на автоматизацию\"},{\"key\":\"zero_trust_style\",\"weight\":0.85,\"label_en\":\"zero-trust perspective\",\"label_ru\":\"взгляд через zero-trust\"},{\"key\":\"privacy_first\",\"weight\":0.85,\"label_en\":\"privacy-first\",\"label_ru\":\"privacy-first подход\"},{\"key\":\"business_architecture\",\"weight\":0.8,\"label_en\":\"business architecture\",\"label_ru\":\"бизнес-архитектура\"},{\"key\":\"product_innovation\",\"weight\":0.75,\"label_en\":\"product innovation\",\"label_ru\":\"продуктовая инновация\"},{\"key\":\"enterprise_scaling\",\"weight\":0.85,\"label_en\":\"enterprise scaling\",\"label_ru\":\"масштабирование enterprise\"},{\"key\":\"consulting_insight\",\"weight\":0.8,\"label_en\":\"consulting insight\",\"label_ru\":\"консалтинговая экспертиза\"},{\"key\":\"technical_vision\",\"weight\":0.8,\"label_en\":\"technical vision\",\"label_ru\":\"техническое видение\"},{\"key\":\"hands_on_architecture\",\"weight\":0.9,\"label_en\":\"hands-on architecture\",\"label_ru\":\"практический архитектурный разбор\"},{\"key\":\"real_world_application\",\"weight\":0.9,\"label_en\":\"real-world application\",\"label_ru\":\"разбор реальных кейсов\"},{\"key\":\"implementation_guide\",\"weight\":0.9,\"label_en\":\"implementation guide\",\"label_ru\":\"руководство по внедрению\"},{\"key\":\"operational_best_practices\",\"weight\":0.85,\"label_en\":\"operational best practices\",\"label_ru\":\"лучшие практики эксплуатации\"},{\"key\":\"strategic_architecture\",\"weight\":0.8,\"label_en\":\"strategic architecture\",\"label_ru\":\"стратегическая архитектура\"},{\"key\":\"developer_ops\",\"weight\":0.85,\"label_en\":\"developer operations\",\"label_ru\":\"девопс и разработка\"}],\"article_system_prompt_en\":\"\",\"article_system_prompt_ru\":\"\",\"article_user_prompt_append_en\":\"Topic selection policy: use clusters only as optional hints (~20% influence). Main topic choice (~80%) must be based on existing-articles analysis, full project context (GeoIP SaaS), and current SEO demand/trends. Prioritize novel, commercially relevant topics with implementation depth.\",\"article_user_prompt_append_ru\":\"Политика выбора темы: используй список clusters только как подсказки (~20% влияния). Основной выбор темы (~80%) делай на основе анализа текущих статей, контекста проекта (GeoIP SaaS) и актуального SEO-спроса/трендов. Приоритет: новизна, коммерческая ценность и практическая применимость.\",\"expand_system_prompt_en\":\"\",\"expand_system_prompt_ru\":\"\",\"expand_user_prompt_append_en\":\"\",\"expand_user_prompt_append_ru\":\"\",\"preview_channel_enabled\":true,\"preview_channel_chat_id\":\"-1003821804232\",\"preview_post_max_words\":220,\"preview_caption_max_words\":80,\"preview_post_min_words\":70,\"preview_caption_min_words\":26,\"preview_use_llm\":true,\"preview_llm_model\":\"\",\"preview_context_chars\":14000,\"preview_image_enabled\":true,\"preview_image_model\":\"google/gemini-2.5-flash-image\",\"preview_image_size\":\"1536x1024\",\"preview_image_style_options\":[\"schematic\",\"realistic\",\"abstract\",\"moody\"],\"image_color_schemes\":[{\"key\":\"corporate_navy\",\"weight\":0.88,\"instruction\":\"Corporate navy palette with structured contrast and restrained accent highlights.\"},{\"key\":\"slate_minimal\",\"weight\":0.72,\"instruction\":\"Slate gray minimalist palette with low-saturation blue accents.\"},{\"key\":\"graphite_gold\",\"weight\":0.76,\"instruction\":\"Graphite base with subtle brushed gold accent lines.\"},{\"key\":\"midnight_cyan\",\"weight\":0.67,\"instruction\":\"Dark midnight base with restrained cyan glow accents.\"},{\"key\":\"deep_plum\",\"weight\":0.52,\"instruction\":\"Deep plum and charcoal tones with elegant muted highlights.\"},{\"key\":\"storm_blue\",\"weight\":0.63,\"instruction\":\"Stormy blue-gray palette with cool layered shadows.\"},{\"key\":\"steel_teal\",\"weight\":0.6,\"instruction\":\"Steel gray base with muted teal accents.\"},{\"key\":\"carbon_orange\",\"weight\":0.58,\"instruction\":\"Carbon black palette with controlled industrial orange highlights.\"},{\"key\":\"arctic_silver\",\"weight\":0.61,\"instruction\":\"Icy silver palette with high-clarity cold highlights.\"},{\"key\":\"ocean_depth\",\"weight\":0.64,\"instruction\":\"Deep ocean blue gradients with subtle luminous accents.\"},{\"key\":\"executive_black\",\"weight\":0.83,\"instruction\":\"Executive black and white palette with sharp clarity and premium tone.\"},{\"key\":\"blueprint\",\"weight\":0.66,\"instruction\":\"Blueprint-inspired blue monochrome with technical white line accents.\"},{\"key\":\"night_mode_soft\",\"weight\":0.69,\"instruction\":\"Soft dark UI palette with reduced glare and smooth contrast.\"},{\"key\":\"clean_gray\",\"weight\":0.71,\"instruction\":\"Clean gray professional palette with subtle blue undertone.\"},{\"key\":\"signal_red_accent\",\"weight\":0.49,\"instruction\":\"Muted dark base with minimal red accent highlights for alert focus.\"},{\"key\":\"amber_focus\",\"weight\":0.57,\"instruction\":\"Neutral base with amber directional highlights.\"},{\"key\":\"cold_white\",\"weight\":0.55,\"instruction\":\"Crisp cold white palette with thin dark structural accents.\"},{\"key\":\"soft_beige_modern\",\"weight\":0.47,\"instruction\":\"Modern beige-neutral palette with muted sophistication.\"},{\"key\":\"matte_black\",\"weight\":0.62,\"instruction\":\"Matte black low-reflection palette with refined light edges.\"},{\"key\":\"minimalist_blue\",\"weight\":0.73,\"instruction\":\"Light modern blue palette with high readability and calm tone.\"},{\"key\":\"shadow_contrast\",\"weight\":0.59,\"instruction\":\"Deep shadow contrast palette with selective illuminated focal areas.\"},{\"key\":\"tech_slate\",\"weight\":0.65,\"instruction\":\"Tech slate palette with balanced cool tones and UI clarity.\"},{\"key\":\"dusty_blue\",\"weight\":0.51,\"instruction\":\"Dusty desaturated blue palette with soft gradients.\"},{\"key\":\"deep_green_black\",\"weight\":0.54,\"instruction\":\"Near-black green palette with subtle emerald highlights.\"},{\"key\":\"sandstone_modern\",\"weight\":0.46,\"instruction\":\"Warm sandstone neutral tones with low visual noise.\"},{\"key\":\"cool_gray_lime\",\"weight\":0.53,\"instruction\":\"Cool gray base with minimal lime accent indicators.\"},{\"key\":\"ink_blue\",\"weight\":0.68,\"instruction\":\"Dark ink blue base with subtle high-contrast text highlights.\"},{\"key\":\"warm_graphite\",\"weight\":0.5,\"instruction\":\"Warm graphite tones with muted copper hints.\"},{\"key\":\"frosted_glass\",\"weight\":0.56,\"instruction\":\"Frosted translucent whites with cool depth layering.\"},{\"key\":\"night_purple\",\"weight\":0.48,\"instruction\":\"Dark muted purple palette with restrained glow accents.\"},{\"key\":\"muted_coral\",\"weight\":0.44,\"instruction\":\"Low-saturation coral accents over neutral light base.\"},{\"key\":\"subtle_gradient_blue\",\"weight\":0.6,\"instruction\":\"Soft blue gradient palette with low aggression.\"},{\"key\":\"smoke_monochrome\",\"weight\":0.52,\"instruction\":\"Smoky grayscale palette with gentle tonal shifts.\"},{\"key\":\"olive_charcoal\",\"weight\":0.45,\"instruction\":\"Olive green highlights over charcoal base.\"},{\"key\":\"platinum_light\",\"weight\":0.58,\"instruction\":\"Platinum and white palette with subtle gray dimension.\"},{\"key\":\"crisp_ui_dark\",\"weight\":0.7,\"instruction\":\"Clean dark UI palette optimized for readability and depth.\"},{\"key\":\"navy_silver\",\"weight\":0.63,\"instruction\":\"Navy base with silver edge highlights.\"},{\"key\":\"midnight_amber\",\"weight\":0.55,\"instruction\":\"Deep midnight palette with warm amber accents.\"},{\"key\":\"balanced_neutral\",\"weight\":0.66,\"instruction\":\"Balanced neutral palette with low saturation and stable tones.\"},{\"key\":\"low_contrast_modern\",\"weight\":0.49,\"instruction\":\"Modern low-contrast palette with softened edges.\"},{\"key\":\"cyber_blue_subtle\",\"weight\":0.54,\"instruction\":\"Subtle cyber blue glow on dark structured base.\"},{\"key\":\"charcoal_teal\",\"weight\":0.61,\"instruction\":\"Charcoal foundation with muted teal emphasis.\"},{\"key\":\"light_sand_gray\",\"weight\":0.43,\"instruction\":\"Light sand and gray combination for soft editorial visuals.\"},{\"key\":\"dark_copper\",\"weight\":0.47,\"instruction\":\"Deep copper accent over dark matte background.\"},{\"key\":\"clean_teal_white\",\"weight\":0.59,\"instruction\":\"Teal and white modern B2B palette.\"},{\"key\":\"professional_indigo\",\"weight\":0.57,\"instruction\":\"Deep indigo with calm neutral overlays.\"},{\"key\":\"calm_neutral_light\",\"weight\":0.62,\"instruction\":\"Calm light neutral palette with gentle contrast.\"},{\"key\":\"obsidian_blue\",\"weight\":0.74,\"instruction\":\"Obsidian dark base with refined cool highlights.\"},{\"key\":\"foggy_morning\",\"weight\":0.42,\"instruction\":\"Fog-inspired pale cool palette with diffused contrast.\"},{\"key\":\"slate_cyan_focus\",\"weight\":0.6,\"instruction\":\"Slate gray with controlled cyan focus points.\"},{\"key\":\"minimal_beige_dark\",\"weight\":0.45,\"instruction\":\"Beige highlights over dark neutral structured background.\"},{\"key\":\"cool_shadow\",\"weight\":0.53,\"instruction\":\"Cool-toned shadow palette with moderate contrast emphasis.\"},{\"key\":\"clean_enterprise\",\"weight\":0.78,\"instruction\":\"Enterprise-grade neutral palette with clarity and restrained color dynamics.\"},{\"key\":\"deep_teal_black\",\"weight\":0.64,\"instruction\":\"Deep teal and near-black palette with subtle depth layering.\"},{\"key\":\"monochrome_blue\",\"weight\":0.67,\"instruction\":\"Blue-toned monochrome palette with technical editorial tone.\"},{\"key\":\"warm_light_editorial\",\"weight\":0.48,\"instruction\":\"Warm editorial light palette with natural tones.\"},{\"key\":\"dark\",\"weight\":1,\"instruction\":\"Dark cinematic palette, deep shadows, high contrast accents.\"},{\"key\":\"light\",\"weight\":0.9,\"instruction\":\"Light clean palette, high readability, soft contrast.\"},{\"key\":\"colordull\",\"weight\":0.6,\"instruction\":\"Muted desaturated palette, restrained color intensity.\"},{\"key\":\"noir\",\"weight\":0.45,\"instruction\":\"Noir monochrome leaning palette, dramatic lighting.\"},{\"key\":\"neon\",\"weight\":0.35,\"instruction\":\"Neon cyber palette with glowing accents on dark base.\"},{\"key\":\"pastel\",\"weight\":0.35,\"instruction\":\"Pastel palette, soft gradients, low harshness.\"},{\"key\":\"teal_orange\",\"weight\":0.55,\"instruction\":\"Teal and orange blockbuster palette with balanced warm/cool contrast.\"},{\"key\":\"earthy\",\"weight\":0.5,\"instruction\":\"Earthy natural palette: clay, olive, sand, and low saturation browns.\"},{\"key\":\"ice_blue\",\"weight\":0.48,\"instruction\":\"Cold ice-blue palette with crisp highlights and restrained warmth.\"},{\"key\":\"sunset\",\"weight\":0.5,\"instruction\":\"Sunset palette with amber, coral, and magenta gradients.\"},{\"key\":\"duotone\",\"weight\":0.42,\"instruction\":\"Strong duotone palette limited to two dominant colors plus neutrals.\"},{\"key\":\"monochrome\",\"weight\":0.44,\"instruction\":\"Monochrome palette with tonal depth and controlled contrast.\"},{\"key\":\"vaporwave\",\"weight\":0.34,\"instruction\":\"Vaporwave-inspired palette: pink, cyan, and retro glow atmosphere.\"},{\"key\":\"industrial\",\"weight\":0.46,\"instruction\":\"Industrial palette with graphite, steel blue, and warning accent tones.\"},{\"key\":\"forest\",\"weight\":0.43,\"instruction\":\"Forest palette with deep greens, moss, and subtle amber highlights.\"},{\"key\":\"high_key\",\"weight\":0.4,\"instruction\":\"High-key bright palette with airy whites and gentle contrast edges.\"}],\"image_compositions\":[{\"key\":\"center_weighted\",\"weight\":0.86,\"label_en\":\"Weighted center\",\"label_ru\":\"Центр с акцентом\",\"instruction\":\"Centered composition with weighted focal mass and subtle directional imbalance.\"},{\"key\":\"off_axis_center\",\"weight\":0.71,\"label_en\":\"Off-axis center\",\"label_ru\":\"Смещенная ось\",\"instruction\":\"Slightly rotated central axis creating modern tension.\"},{\"key\":\"deep_perspective_pull\",\"weight\":0.74,\"label_en\":\"Deep perspective pull\",\"label_ru\":\"Глубокая перспектива\",\"instruction\":\"Strong perspective vanishing point pulling viewer inward.\"},{\"key\":\"compressed_foreground\",\"weight\":0.59,\"label_en\":\"Compressed foreground\",\"label_ru\":\"Сжатый передний план\",\"instruction\":\"Large foreground mass framing distant focal subject.\"},{\"key\":\"vertical_dominance\",\"weight\":0.63,\"label_en\":\"Vertical dominance\",\"label_ru\":\"Вертикальное доминирование\",\"instruction\":\"Tall vertical composition emphasizing hierarchy and authority.\"},{\"key\":\"horizontal_flow\",\"weight\":0.61,\"label_en\":\"Horizontal flow\",\"label_ru\":\"Горизонтальный поток\",\"instruction\":\"Wide lateral flow guiding attention across the frame.\"},{\"key\":\"low_angle_power\",\"weight\":0.57,\"label_en\":\"Low-angle power\",\"label_ru\":\"Низкий ракурс\",\"instruction\":\"Low-angle composition enhancing authority and structural weight.\"},{\"key\":\"high_angle_overview\",\"weight\":0.69,\"label_en\":\"High-angle overview\",\"label_ru\":\"Вид сверху\",\"instruction\":\"Elevated overview composition for analytical dominance.\"},{\"key\":\"compressed_depth\",\"weight\":0.52,\"label_en\":\"Compressed depth\",\"label_ru\":\"Сжатая глубина\",\"instruction\":\"Flattened spatial layering for abstract data emphasis.\"},{\"key\":\"deep_field_focus\",\"weight\":0.66,\"label_en\":\"Deep field focus\",\"label_ru\":\"Глубина резкости\",\"instruction\":\"Extended depth of field emphasizing layered complexity.\"},{\"key\":\"foreground_frame_left\",\"weight\":0.55,\"label_en\":\"Left frame emphasis\",\"label_ru\":\"Левый фрейм\",\"instruction\":\"Strong foreground frame on left guiding inward movement.\"},{\"key\":\"foreground_frame_right\",\"weight\":0.55,\"label_en\":\"Right frame emphasis\",\"label_ru\":\"Правый фрейм\",\"instruction\":\"Mirrored framing emphasizing directional flow.\"},{\"key\":\"upper_weight_bias\",\"weight\":0.49,\"label_en\":\"Upper bias\",\"label_ru\":\"Верхний акцент\",\"instruction\":\"Heavy upper composition mass with grounded lower balance.\"},{\"key\":\"lower_weight_anchor\",\"weight\":0.48,\"label_en\":\"Lower anchor\",\"label_ru\":\"Нижний якорь\",\"instruction\":\"Strong base anchor supporting upward visual hierarchy.\"},{\"key\":\"diagonal_split\",\"weight\":0.67,\"label_en\":\"Diagonal split\",\"label_ru\":\"Диагональное разделение\",\"instruction\":\"Clear diagonal division between conceptual zones.\"},{\"key\":\"center_void\",\"weight\":0.46,\"label_en\":\"Central void\",\"label_ru\":\"Пустой центр\",\"instruction\":\"Intentional empty center surrounded by active perimeter.\"},{\"key\":\"edge_framing\",\"weight\":0.58,\"label_en\":\"Edge framing\",\"label_ru\":\"Краевое обрамление\",\"instruction\":\"Active borders guiding attention inward.\"},{\"key\":\"compressed_side_panels\",\"weight\":0.44,\"label_en\":\"Side panel compression\",\"label_ru\":\"Боковые панели\",\"instruction\":\"Narrow side panels emphasizing central narrative.\"},{\"key\":\"deep_shadow_corner\",\"weight\":0.39,\"label_en\":\"Shadowed corner\",\"label_ru\":\"Темный угол\",\"instruction\":\"Corner shadow anchoring visual gravity.\"},{\"key\":\"reverse_symmetry\",\"weight\":0.51,\"label_en\":\"Reverse symmetry\",\"label_ru\":\"Инвертированная симметрия\",\"instruction\":\"Near symmetry with subtle inversion tension.\"},{\"key\":\"staggered_alignment\",\"weight\":0.62,\"label_en\":\"Staggered alignment\",\"label_ru\":\"Ступенчатое выравнивание\",\"instruction\":\"Offset alignment blocks creating rhythmic flow.\"},{\"key\":\"vertical_split_dual\",\"weight\":0.53,\"label_en\":\"Vertical split dual\",\"label_ru\":\"Вертикальное разделение\",\"instruction\":\"Two vertical conceptual halves in contrast.\"},{\"key\":\"radial_offset\",\"weight\":0.47,\"label_en\":\"Offset radial\",\"label_ru\":\"Смещенный радиал\",\"instruction\":\"Radial expansion with displaced core.\"},{\"key\":\"clustered_mass\",\"weight\":0.6,\"label_en\":\"Clustered mass\",\"label_ru\":\"Кластерная масса\",\"instruction\":\"Dense grouping of elements forming weighted focus.\"},{\"key\":\"micro_macro_contrast\",\"weight\":0.42,\"label_en\":\"Micro-macro contrast\",\"label_ru\":\"Контраст масштаба\",\"instruction\":\"Small detailed foreground vs large simplified background.\"},{\"key\":\"overlapping_planes\",\"weight\":0.64,\"label_en\":\"Overlapping planes\",\"label_ru\":\"Перекрывающиеся плоскости\",\"instruction\":\"Layered planes intersecting for dynamic depth.\"},{\"key\":\"central_spine\",\"weight\":0.57,\"label_en\":\"Central spine\",\"label_ru\":\"Центральный стержень\",\"instruction\":\"Strong vertical spine organizing composition.\"},{\"key\":\"perimeter_activity\",\"weight\":0.43,\"label_en\":\"Perimeter activity\",\"label_ru\":\"Активный периметр\",\"instruction\":\"Core minimal center with active surrounding ring.\"},{\"key\":\"corner_to_corner_flow\",\"weight\":0.72,\"label_en\":\"Corner flow\",\"label_ru\":\"Поток по диагонали\",\"instruction\":\"Visual movement connecting opposite corners.\"},{\"key\":\"dual_axis_balance\",\"weight\":0.54,\"label_en\":\"Dual axis\",\"label_ru\":\"Две оси\",\"instruction\":\"Balanced tension across vertical and horizontal axes.\"},{\"key\":\"weight_shift_right\",\"weight\":0.49,\"label_en\":\"Right-weighted\",\"label_ru\":\"Правый перевес\",\"instruction\":\"Visual gravity shifted toward right third.\"},{\"key\":\"weight_shift_left\",\"weight\":0.49,\"label_en\":\"Left-weighted\",\"label_ru\":\"Левый перевес\",\"instruction\":\"Visual gravity shifted toward left third.\"},{\"key\":\"negative_space_top\",\"weight\":0.45,\"label_en\":\"Top negative space\",\"label_ru\":\"Верхний воздух\",\"instruction\":\"Large upper empty space for conceptual emphasis.\"},{\"key\":\"negative_space_side\",\"weight\":0.45,\"label_en\":\"Side negative space\",\"label_ru\":\"Боковой воздух\",\"instruction\":\"Dominant side emptiness guiding focus inward.\"},{\"key\":\"tilted_frame\",\"weight\":0.52,\"label_en\":\"Tilted frame\",\"label_ru\":\"Наклон кадра\",\"instruction\":\"Slight rotational tilt adding subtle tension.\"},{\"key\":\"compressed_center_strip\",\"weight\":0.41,\"label_en\":\"Center strip\",\"label_ru\":\"Центральная полоса\",\"instruction\":\"Narrow central strip dominating composition.\"},{\"key\":\"expanded_background\",\"weight\":0.66,\"label_en\":\"Expanded background\",\"label_ru\":\"Расширенный фон\",\"instruction\":\"Large atmospheric background with small focal subject.\"},{\"key\":\"island_focus\",\"weight\":0.58,\"label_en\":\"Island focus\",\"label_ru\":\"Изолированный фокус\",\"instruction\":\"Single isolated subject in open negative space.\"},{\"key\":\"floating_grid\",\"weight\":0.47,\"label_en\":\"Floating grid\",\"label_ru\":\"Плавающая сетка\",\"instruction\":\"Grid not aligned to edges creating abstraction.\"},{\"key\":\"cross_axis_intersection\",\"weight\":0.63,\"label_en\":\"Cross-axis\",\"label_ru\":\"Пересечение осей\",\"instruction\":\"Strong cross intersection dividing visual zones.\"},{\"key\":\"layered_horizon\",\"weight\":0.51,\"label_en\":\"Layered horizon\",\"label_ru\":\"Многоуровневый горизонт\",\"instruction\":\"Multiple horizontal bands with narrative layering.\"},{\"key\":\"compressed_top_weight\",\"weight\":0.44,\"label_en\":\"Top compression\",\"label_ru\":\"Сжатый верх\",\"instruction\":\"Heavy compressed upper band dominating visual hierarchy.\"},{\"key\":\"compressed_bottom_weight\",\"weight\":0.44,\"label_en\":\"Bottom compression\",\"label_ru\":\"Сжатый низ\",\"instruction\":\"Weighted lower strip anchoring composition.\"},{\"key\":\"center_glow_focus\",\"weight\":0.56,\"label_en\":\"Center glow\",\"label_ru\":\"Центральное свечение\",\"instruction\":\"Subtle luminance drawing attention to focal core.\"},{\"key\":\"peripheral_blur\",\"weight\":0.48,\"label_en\":\"Peripheral blur\",\"label_ru\":\"Размытие по краям\",\"instruction\":\"Soft blurred edges increasing central clarity.\"},{\"key\":\"depth_funnel\",\"weight\":0.61,\"label_en\":\"Depth funnel\",\"label_ru\":\"Воронка глубины\",\"instruction\":\"Perspective funnel guiding eye toward central vanishing point.\"},{\"key\":\"stepped_symmetry\",\"weight\":0.52,\"label_en\":\"Stepped symmetry\",\"label_ru\":\"Ступенчатая симметрия\",\"instruction\":\"Symmetry broken by stepped vertical offsets.\"},{\"key\":\"mirror_axis_soft\",\"weight\":0.46,\"label_en\":\"Soft mirror\",\"label_ru\":\"Мягкое зеркало\",\"instruction\":\"Subtle mirrored layout without strict reflection.\"},{\"key\":\"tri_axis_balance\",\"weight\":0.39,\"label_en\":\"Triple axis\",\"label_ru\":\"Тройная ось\",\"instruction\":\"Three balanced structural axes intersecting.\"},{\"key\":\"ring_offset_core\",\"weight\":0.37,\"label_en\":\"Offset ring core\",\"label_ru\":\"Смещенное кольцо\",\"instruction\":\"Circular composition with displaced focal core.\"},{\"key\":\"visual_weight_gradient\",\"weight\":0.58,\"label_en\":\"Weight gradient\",\"label_ru\":\"Градиент веса\",\"instruction\":\"Gradual shift of visual mass across frame.\"},{\"key\":\"edge_to_core_pull\",\"weight\":0.64,\"label_en\":\"Edge pull\",\"label_ru\":\"Тяга к центру\",\"instruction\":\"Directional elements pulling from edges inward.\"},{\"key\":\"compressed_corner_cluster\",\"weight\":0.36,\"label_en\":\"Corner cluster\",\"label_ru\":\"Кластер в углу\",\"instruction\":\"Dense element cluster anchored in one corner.\"},{\"key\":\"horizontal_band_stack\",\"weight\":0.53,\"label_en\":\"Band stacking\",\"label_ru\":\"Горизонтальные полосы\",\"instruction\":\"Stacked horizontal narrative layers.\"},{\"key\":\"centered_with_void_ring\",\"weight\":0.34,\"label_en\":\"Void ring\",\"label_ru\":\"Кольцо пустоты\",\"instruction\":\"Empty ring surrounding a dense central focus.\"},{\"key\":\"asymmetric_radial\",\"weight\":0.38,\"label_en\":\"Asymmetric radial\",\"label_ru\":\"Асимметричный радиал\",\"instruction\":\"Radial energy with uneven element distribution.\"},{\"key\":\"visual_corridor\",\"weight\":0.62,\"label_en\":\"Visual corridor\",\"label_ru\":\"Коридор взгляда\",\"instruction\":\"Corridor-like depth guiding attention forward.\"},{\"key\":\"balanced_dual_pillars\",\"weight\":0.49,\"label_en\":\"Dual pillars\",\"label_ru\":\"Двойные колонны\",\"instruction\":\"Two vertical anchors framing central action.\"},{\"key\":\"light_directional_bias\",\"weight\":0.44,\"label_en\":\"Directional light bias\",\"label_ru\":\"Направленный свет\",\"instruction\":\"Light shaping composition asymmetrically.\"},{\"key\":\"deep_center_anchor\",\"weight\":0.68,\"label_en\":\"Deep center anchor\",\"label_ru\":\"Глубокий центр\",\"instruction\":\"Heavy central anchor grounded by layered depth.\"},{\"key\":\"compressed_edge_frame\",\"weight\":0.42,\"label_en\":\"Edge compression\",\"label_ru\":\"Сжатие краев\",\"instruction\":\"Narrow active edges emphasizing interior content.\"},{\"key\":\"inverted_triangle_focus\",\"weight\":0.51,\"label_en\":\"Inverted triangle\",\"label_ru\":\"Перевернутый треугольник\",\"instruction\":\"Downward pointing triangular stability.\"},{\"key\":\"stacked_center_layers\",\"weight\":0.57,\"label_en\":\"Stacked center\",\"label_ru\":\"Слоистый центр\",\"instruction\":\"Multiple layered focal planes stacked vertically.\"},{\"key\":\"hollow_core_composition\",\"weight\":0.35,\"label_en\":\"Hollow core\",\"label_ru\":\"Полый центр\",\"instruction\":\"Central hollow surrounded by structural activity.\"},{\"key\":\"balanced_offset_horizon\",\"weight\":0.48,\"label_en\":\"Offset horizon\",\"label_ru\":\"Смещенный горизонт\",\"instruction\":\"Slightly elevated horizon for cinematic calm.\"},{\"key\":\"foreground_arc\",\"weight\":0.39,\"label_en\":\"Foreground arc\",\"label_ru\":\"Дуга переднего плана\",\"instruction\":\"Curved arc framing action from front.\"},{\"key\":\"narrow_depth_slice\",\"weight\":0.33,\"label_en\":\"Depth slice\",\"label_ru\":\"Срез глубины\",\"instruction\":\"Thin slice of focus cutting through layered background.\"},{\"key\":\"centered\",\"weight\":1,\"label_en\":\"Centered\",\"label_ru\":\"Центированная\",\"instruction\":\"Centered focal composition with clear subject priority.\"},{\"key\":\"dynamic_diagonal\",\"weight\":0.9,\"label_en\":\"Dynamic diagonal\",\"label_ru\":\"Динамическая\",\"instruction\":\"Strong diagonal flow with dynamic motion and tension.\"},{\"key\":\"broken_reflection\",\"weight\":0.45,\"label_en\":\"Broken reflection\",\"label_ru\":\"Ломаное отражение\",\"instruction\":\"Fragmented mirrored composition, asymmetry with reflective motifs.\"},{\"key\":\"golden_ratio\",\"weight\":0.75,\"label_en\":\"Golden ratio\",\"label_ru\":\"Золотое сечение\",\"instruction\":\"Golden ratio based layout for balanced visual hierarchy.\"},{\"key\":\"mosaic\",\"weight\":0.55,\"label_en\":\"Mosaic\",\"label_ru\":\"Мозаика\",\"instruction\":\"Mosaic modular blocks composition with structured segmentation.\"},{\"key\":\"expressionist\",\"weight\":0.38,\"label_en\":\"Expressionist\",\"label_ru\":\"Экспрессия\",\"instruction\":\"Expressive composition with dramatic rhythm and emotional contrast.\"},{\"key\":\"rule_of_thirds\",\"weight\":0.82,\"label_en\":\"Rule of thirds\",\"label_ru\":\"Правило третей\",\"instruction\":\"Rule-of-thirds placement with off-center focal points.\"},{\"key\":\"symmetrical\",\"weight\":0.62,\"label_en\":\"Symmetrical\",\"label_ru\":\"Симметрия\",\"instruction\":\"Symmetrical composition with strong vertical axis and balance.\"},{\"key\":\"asymmetrical_balance\",\"weight\":0.66,\"label_en\":\"Asymmetrical balance\",\"label_ru\":\"Асимметричный баланс\",\"instruction\":\"Asymmetrical but balanced layout with weighted visual masses.\"},{\"key\":\"radial_focus\",\"weight\":0.41,\"label_en\":\"Radial focus\",\"label_ru\":\"Радиальный фокус\",\"instruction\":\"Radial composition radiating from central core or hotspot.\"},{\"key\":\"spiral_flow\",\"weight\":0.34,\"label_en\":\"Spiral flow\",\"label_ru\":\"Спираль\",\"instruction\":\"Spiral flow guiding eye through layered content depth.\"},{\"key\":\"depth_layers\",\"weight\":0.64,\"label_en\":\"Depth layers\",\"label_ru\":\"Глубинные слои\",\"instruction\":\"Foreground-midground-background layering for spatial depth.\"},{\"key\":\"split_screen\",\"weight\":0.4,\"label_en\":\"Split screen\",\"label_ru\":\"Разделенный экран\",\"instruction\":\"Split-screen composition contrasting two states or scenarios.\"},{\"key\":\"timeline_sequence\",\"weight\":0.37,\"label_en\":\"Timeline sequence\",\"label_ru\":\"Последовательность\",\"instruction\":\"Sequential timeline composition with progressive narrative.\"}],\"preview_image_prompt_template\":\"Create a high-quality {{image_style}} hero image for article \\\"{{title}}\\\" ({{lang}}). Use context: {{excerpt}}. Additional context: {{context}}. No text, no logos.\",\"image_scenarios\":[{\"key\":\"data_crystal_matrix\",\"weight\":0.78,\"label_en\":\"Data crystal matrix\",\"label_ru\":\"Кристаллическая матрица данных\",\"instruction\":\"Futuristic crystal lattice formed by interconnected IP nodes and ASN signals symbolizing structured intelligence.\"},{\"key\":\"signal_flux_stream\",\"weight\":0.72,\"label_en\":\"Signal flux stream\",\"label_ru\":\"Поток сигналов\",\"instruction\":\"Continuous flowing stream of enriched IP signals passing through layered verification filters.\"},{\"key\":\"geo_heat_sphere\",\"weight\":0.83,\"label_en\":\"Global heat sphere\",\"label_ru\":\"Глобальная тепловая сфера\",\"instruction\":\"3D globe with anomaly heat gradients and fraud density overlays.\"},{\"key\":\"trust_barrier_field\",\"weight\":0.75,\"label_en\":\"Trust barrier field\",\"label_ru\":\"Поле доверительного барьера\",\"instruction\":\"Invisible protective field filtering suspicious traffic at system boundary.\"},{\"key\":\"risk_vector_field\",\"weight\":0.8,\"label_en\":\"Risk vector field\",\"label_ru\":\"Векторное поле риска\",\"instruction\":\"Directional arrows representing dynamic fraud pressure across regions.\"},{\"key\":\"identity_constellation\",\"weight\":0.76,\"label_en\":\"Identity constellation\",\"label_ru\":\"Созвездие идентичностей\",\"instruction\":\"Star-map style visualization of users, devices and IP connections.\"},{\"key\":\"policy_rule_grid\",\"weight\":0.7,\"label_en\":\"Policy rule grid\",\"label_ru\":\"Сетка правил политики\",\"instruction\":\"Structured matrix showing layered risk rules activating by context.\"},{\"key\":\"threat_signal_radar\",\"weight\":0.84,\"label_en\":\"Threat signal radar\",\"label_ru\":\"Радар угроз\",\"instruction\":\"Radar sweep detecting anomaly clusters in traffic patterns.\"},{\"key\":\"geo_cluster_islands\",\"weight\":0.77,\"label_en\":\"Geo cluster islands\",\"label_ru\":\"Острова гео-кластеров\",\"instruction\":\"Regional clusters of IP activity visualized as isolated anomaly islands.\"},{\"key\":\"decision_tree_depth\",\"weight\":0.82,\"label_en\":\"Decision tree depth\",\"label_ru\":\"Глубина decision tree\",\"instruction\":\"Layered decision tree visualization with risk scoring branches.\"},{\"key\":\"api_signal_highway\",\"weight\":0.73,\"label_en\":\"API signal highway\",\"label_ru\":\"Магистраль API сигналов\",\"instruction\":\"Data packets traveling along structured API lanes with verification checkpoints.\"},{\"key\":\"conversion_defense_wall\",\"weight\":0.74,\"label_en\":\"Conversion defense wall\",\"label_ru\":\"Стена защиты конверсии\",\"instruction\":\"Protective layered wall blocking fraud while allowing clean user flow.\"},{\"key\":\"risk_gravity_center\",\"weight\":0.79,\"label_en\":\"Risk gravity center\",\"label_ru\":\"Центр гравитации риска\",\"instruction\":\"Dense anomaly core pulling suspicious activity into analytical focus.\"},{\"key\":\"data_orbit_system\",\"weight\":0.71,\"label_en\":\"Data orbit system\",\"label_ru\":\"Орбитальная система данных\",\"instruction\":\"Signals orbiting around a central trust engine like planets.\"},{\"key\":\"adaptive_firewall_layers\",\"weight\":0.75,\"label_en\":\"Adaptive firewall layers\",\"label_ru\":\"Адаптивные слои firewall\",\"instruction\":\"Multi-layer filtering stack adapting to contextual risk levels.\"},{\"key\":\"anomaly_waveform_scan\",\"weight\":0.81,\"label_en\":\"Anomaly waveform scan\",\"label_ru\":\"Скан волны аномалий\",\"instruction\":\"Oscillating waveform highlighting abnormal traffic spikes.\"},{\"key\":\"network_bridge_gateway\",\"weight\":0.72,\"label_en\":\"Network bridge gateway\",\"label_ru\":\"Мост сетевого шлюза\",\"instruction\":\"Secure gateway bridging global IP flows into core infrastructure.\"},{\"key\":\"fraud_shadow_overlay\",\"weight\":0.68,\"label_en\":\"Fraud shadow overlay\",\"label_ru\":\"Теневая проекция мошенничества\",\"instruction\":\"Dark overlay revealing hidden fraud patterns over clean traffic.\"},{\"key\":\"geo_confidence_layers\",\"weight\":0.82,\"label_en\":\"Geo confidence layers\",\"label_ru\":\"Слои уверенности гео\",\"instruction\":\"Transparent overlays showing geolocation confidence scores.\"},{\"key\":\"risk_threshold_slider\",\"weight\":0.66,\"label_en\":\"Risk threshold slider\",\"label_ru\":\"Ползунок порога риска\",\"instruction\":\"Interactive control metaphor adjusting acceptance thresholds.\"},{\"key\":\"signal_enrichment_stack\",\"weight\":0.87,\"label_en\":\"Signal enrichment stack\",\"label_ru\":\"Стек обогащения сигналов\",\"instruction\":\"Layered pipeline enriching IP with ASN, proxy and risk metadata.\"},{\"key\":\"threat_landscape_map\",\"weight\":0.85,\"label_en\":\"Threat landscape map\",\"label_ru\":\"Ландшафт угроз\",\"instruction\":\"Wide threat landscape with risk elevation gradients.\"},{\"key\":\"identity_flow_chart\",\"weight\":0.74,\"label_en\":\"Identity flow chart\",\"label_ru\":\"Поток идентичности\",\"instruction\":\"Structured flow diagram mapping identity validation stages.\"},{\"key\":\"fraud_pattern_weave\",\"weight\":0.69,\"label_en\":\"Fraud pattern weave\",\"label_ru\":\"Переплетение паттернов\",\"instruction\":\"Interwoven suspicious behavior strands forming network tapestry.\"},{\"key\":\"trust_pyramid_model\",\"weight\":0.73,\"label_en\":\"Trust pyramid model\",\"label_ru\":\"Пирамида доверия\",\"instruction\":\"Hierarchical trust layers from base signals to executive decisions.\"},{\"key\":\"geo_signal_prism\",\"weight\":0.78,\"label_en\":\"Geo signal prism\",\"label_ru\":\"Призма гео-сигналов\",\"instruction\":\"Light prism metaphor splitting IP signal into contextual components.\"},{\"key\":\"decision_latency_meter\",\"weight\":0.7,\"label_en\":\"Decision latency meter\",\"label_ru\":\"Индикатор задержки решения\",\"instruction\":\"Visual gauge measuring risk decision timing.\"},{\"key\":\"ip_signal_compass\",\"weight\":0.76,\"label_en\":\"IP signal compass\",\"label_ru\":\"Компас IP-сигналов\",\"instruction\":\"Compass visualization aligning traffic by risk orientation.\"},{\"key\":\"fraud_heat_corridor\",\"weight\":0.8,\"label_en\":\"Fraud heat corridor\",\"label_ru\":\"Коридор тепла мошенничества\",\"instruction\":\"Corridor visualization showing rising anomaly heat zones.\"},{\"key\":\"data_vault_chamber\",\"weight\":0.72,\"label_en\":\"Data vault chamber\",\"label_ru\":\"Хранилище данных\",\"instruction\":\"Secure vault metaphor representing protected intelligence core.\"},{\"key\":\"adaptive_mesh_network\",\"weight\":0.77,\"label_en\":\"Adaptive mesh network\",\"label_ru\":\"Адаптивная mesh-сеть\",\"instruction\":\"Flexible interconnected mesh adjusting to signal changes.\"},{\"key\":\"geo_signal_bridge\",\"weight\":0.75,\"label_en\":\"Geo signal bridge\",\"label_ru\":\"Мост гео-сигналов\",\"instruction\":\"Structural bridge connecting regional traffic clusters.\"},{\"key\":\"identity_layer_stack\",\"weight\":0.73,\"label_en\":\"Identity layer stack\",\"label_ru\":\"Слои идентичности\",\"instruction\":\"Layered identity verification visualization.\"},{\"key\":\"risk_signal_fusion\",\"weight\":0.84,\"label_en\":\"Risk signal fusion\",\"label_ru\":\"Слияние сигналов риска\",\"instruction\":\"Multiple data streams merging into unified risk score.\"},{\"key\":\"anomaly_spike_field\",\"weight\":0.79,\"label_en\":\"Anomaly spike field\",\"label_ru\":\"Поле всплесков аномалий\",\"instruction\":\"Vertical spikes indicating suspicious traffic surges.\"},{\"key\":\"trust_matrix_cube\",\"weight\":0.71,\"label_en\":\"Trust matrix cube\",\"label_ru\":\"Куб матрицы доверия\",\"instruction\":\"3D matrix cube representing contextual trust dimensions.\"},{\"key\":\"signal_filter_chamber\",\"weight\":0.68,\"label_en\":\"Signal filter chamber\",\"label_ru\":\"Камера фильтрации сигналов\",\"instruction\":\"Enclosed chamber filtering malicious noise.\"},{\"key\":\"decision_engine_core\",\"weight\":0.88,\"label_en\":\"Decision engine core\",\"label_ru\":\"Ядро decision engine\",\"instruction\":\"Central engine hub processing enriched data streams.\"},{\"key\":\"geo_cluster_pulse\",\"weight\":0.82,\"label_en\":\"Geo cluster pulse\",\"label_ru\":\"Пульс гео-кластера\",\"instruction\":\"Pulsating anomaly regions across global map.\"},{\"key\":\"fraud_density_surface\",\"weight\":0.85,\"label_en\":\"Fraud density surface\",\"label_ru\":\"Поверхность плотности мошенничества\",\"instruction\":\"Surface graph visualizing fraud concentration layers.\"},{\"key\":\"identity_auth_arc\",\"weight\":0.7,\"label_en\":\"Identity auth arc\",\"label_ru\":\"Арка аутентификации\",\"instruction\":\"Curved authentication arc verifying identity signals.\"},{\"key\":\"signal_spectrum_field\",\"weight\":0.76,\"label_en\":\"Signal spectrum field\",\"label_ru\":\"Спектр сигналов\",\"instruction\":\"Spectrum visualization categorizing clean vs risky traffic.\"},{\"key\":\"risk_energy_grid\",\"weight\":0.81,\"label_en\":\"Risk energy grid\",\"label_ru\":\"Энергетическая сетка риска\",\"instruction\":\"Energy-like grid showing dynamic fraud tension.\"},{\"key\":\"geo_latency_flow\",\"weight\":0.74,\"label_en\":\"Geo latency flow\",\"label_ru\":\"Поток гео-задержек\",\"instruction\":\"Flow visualization highlighting latency and routing anomalies.\"},{\"key\":\"trust_anchor_node\",\"weight\":0.77,\"label_en\":\"Trust anchor node\",\"label_ru\":\"Якорный узел доверия\",\"instruction\":\"Central trust node stabilizing system integrity.\"},{\"key\":\"anomaly_detection_tower\",\"weight\":0.69,\"label_en\":\"Anomaly detection tower\",\"label_ru\":\"Башня детекции аномалий\",\"instruction\":\"Elevated monitoring structure scanning for anomalies.\"},{\"key\":\"signal_chain_validation\",\"weight\":0.83,\"label_en\":\"Signal chain validation\",\"label_ru\":\"Валидация цепочки сигналов\",\"instruction\":\"Sequential validation chain ensuring clean traffic flow.\"},{\"key\":\"risk_gradient_horizon\",\"weight\":0.78,\"label_en\":\"Risk gradient horizon\",\"label_ru\":\"Горизонт градиента риска\",\"instruction\":\"Expansive horizon with layered fraud gradients.\"},{\"key\":\"data_pipeline_cascade\",\"weight\":0.86,\"label_en\":\"Data pipeline cascade\",\"label_ru\":\"Каскадный пайплайн данных\",\"instruction\":\"Cascading enrichment and scoring modules visualized vertically.\"},{\"key\":\"identity_integrity_frame\",\"weight\":0.72,\"label_en\":\"Identity integrity frame\",\"label_ru\":\"Каркас целостности идентичности\",\"instruction\":\"Framed structure reinforcing secure identity validation.\"},{\"key\":\"geo_route_matrix\",\"weight\":0.84,\"label_en\":\"Geo route matrix\",\"label_ru\":\"Матрица гео-маршрутов\",\"instruction\":\"Structured grid mapping IP route intelligence.\"},{\"key\":\"fraud_signal_lock\",\"weight\":0.7,\"label_en\":\"Fraud signal lock\",\"label_ru\":\"Замок сигнала мошенничества\",\"instruction\":\"Lock mechanism securing risk signal output.\"},{\"key\":\"trust_spectrum_balance\",\"weight\":0.79,\"label_en\":\"Trust spectrum balance\",\"label_ru\":\"Баланс спектра доверия\",\"instruction\":\"Visual scale balancing multiple contextual trust indicators.street_market_scene\"},{\"key\":\"busy_airport_terminal\",\"weight\":0.81,\"label_en\":\"Airport terminal control\",\"label_ru\":\"Аэропорт и контроль\",\"instruction\":\"Airport security checkpoint metaphor for IP verification and trust screening.\"},{\"key\":\"train_station_departure\",\"weight\":0.72,\"label_en\":\"Train station departures\",\"label_ru\":\"Вокзал отправления\",\"instruction\":\"Departure board and crowd flow visual symbolizing routing decisions and geo filtering.\"},{\"key\":\"customs_border_control\",\"weight\":0.83,\"label_en\":\"Border customs control\",\"label_ru\":\"Таможенный контроль\",\"instruction\":\"Border inspection scene representing traffic validation and fraud screening.\"},{\"key\":\"bank_queue_risk_check\",\"weight\":0.76,\"label_en\":\"Bank queue verification\",\"label_ru\":\"Проверка в банке\",\"instruction\":\"Bank counter verification metaphor for payment risk assessment.\"},{\"key\":\"hospital_triage_room\",\"weight\":0.78,\"label_en\":\"Hospital triage system\",\"label_ru\":\"Триаж в больнице\",\"instruction\":\"Medical triage metaphor prioritizing risk signals by severity.\"},{\"key\":\"courtroom_verdict_scene\",\"weight\":0.79,\"label_en\":\"Courtroom verdict\",\"label_ru\":\"Судебное решение\",\"instruction\":\"Courtroom decision moment symbolizing rule engine verdict logic.\"},{\"key\":\"theater_stage_spotlight\",\"weight\":0.71,\"label_en\":\"Theater spotlight focus\",\"label_ru\":\"Сцена под софитом\",\"instruction\":\"Spotlight isolating suspicious actors on stage, editorial risk metaphor.\"},{\"key\":\"orchestra_conductor_balance\",\"weight\":0.82,\"label_en\":\"Orchestra conductor\",\"label_ru\":\"Дирижер оркестра\",\"instruction\":\"Conductor balancing multiple instruments as analogy for signal orchestration.\"},{\"key\":\"backstage_coordination\",\"weight\":0.68,\"label_en\":\"Backstage coordination\",\"label_ru\":\"Закулисная координация\",\"instruction\":\"Theater backstage teamwork visual representing microservices collaboration.\"},{\"key\":\"detective_interrogation_room\",\"weight\":0.77,\"label_en\":\"Interrogation analysis\",\"label_ru\":\"Допрос подозреваемого\",\"instruction\":\"Analytical questioning metaphor for anomaly detection.\"},{\"key\":\"city_traffic_intersection\",\"weight\":0.8,\"label_en\":\"City traffic intersection\",\"label_ru\":\"Городской перекресток\",\"instruction\":\"Traffic lights and flow control representing API gating decisions.\"},{\"key\":\"shipping_port_operations\",\"weight\":0.84,\"label_en\":\"Cargo port operations\",\"label_ru\":\"Грузовой порт\",\"instruction\":\"Container inspection metaphor for packet and IP validation.\"},{\"key\":\"airport_control_tower\",\"weight\":0.85,\"label_en\":\"Air traffic control tower\",\"label_ru\":\"Диспетчерская вышка\",\"instruction\":\"Control tower monitoring flights as analogy for SOC oversight.\"},{\"key\":\"restaurant_kitchen_pass\",\"weight\":0.69,\"label_en\":\"Restaurant kitchen pass\",\"label_ru\":\"Кухня ресторана\",\"instruction\":\"Head chef approving dishes as metaphor for risk approval workflow.\"},{\"key\":\"museum_security_guard\",\"weight\":0.73,\"label_en\":\"Museum security monitoring\",\"label_ru\":\"Охрана музея\",\"instruction\":\"Guard observing visitors representing trust evaluation.\"},{\"key\":\"newsroom_editorial_board\",\"weight\":0.75,\"label_en\":\"Newsroom editorial board\",\"label_ru\":\"Редакционный совет\",\"instruction\":\"Editorial decision metaphor for executive dashboard strategy.\"},{\"key\":\"fire_station_dispatch\",\"weight\":0.78,\"label_en\":\"Emergency dispatch center\",\"label_ru\":\"Пожарная диспетчерская\",\"instruction\":\"Rapid response coordination metaphor for incident response.\"},{\"key\":\"harbor_lighthouse_watch\",\"weight\":0.72,\"label_en\":\"Lighthouse guidance\",\"label_ru\":\"Маяк в гавани\",\"instruction\":\"Lighthouse guiding ships representing geo routing confidence.\"},{\"key\":\"warehouse_quality_check\",\"weight\":0.74,\"label_en\":\"Warehouse quality control\",\"label_ru\":\"Контроль качества на складе\",\"instruction\":\"Inspection line verifying goods as analogy for fraud filtering.\"},{\"key\":\"airport_lounge_priority\",\"weight\":0.7,\"label_en\":\"Priority lounge access\",\"label_ru\":\"Доступ в бизнес-зал\",\"instruction\":\"Selective access metaphor for zero-trust policy.\"},{\"key\":\"theater_audience_reveal\",\"weight\":0.66,\"label_en\":\"Audience reveal moment\",\"label_ru\":\"Разоблачение на сцене\",\"instruction\":\"Dramatic reveal of hidden risk actors.\"},{\"key\":\"city_surveillance_room\",\"weight\":0.81,\"label_en\":\"Urban surveillance hub\",\"label_ru\":\"Центр городского наблюдения\",\"instruction\":\"Monitoring multiple camera feeds as network analysis metaphor.\"},{\"key\":\"library_archivist_sorting\",\"weight\":0.69,\"label_en\":\"Archivist sorting records\",\"label_ru\":\"Архивариус\",\"instruction\":\"Sorting records metaphor for signal enrichment.\"},{\"key\":\"construction_site_blueprint\",\"weight\":0.83,\"label_en\":\"Blueprint construction site\",\"label_ru\":\"Стройка по чертежу\",\"instruction\":\"Architects reviewing blueprints representing system design.\"},{\"key\":\"market_auction_scene\",\"weight\":0.77,\"label_en\":\"Market auction tension\",\"label_ru\":\"Аукцион\",\"instruction\":\"Competitive bidding metaphor for fraud pressure dynamics.\"},{\"key\":\"bridge_toll_checkpoint\",\"weight\":0.8,\"label_en\":\"Toll checkpoint screening\",\"label_ru\":\"Платный мост\",\"instruction\":\"Toll booth control metaphor for API rate limiting.\"},{\"key\":\"harbor_container_scan\",\"weight\":0.82,\"label_en\":\"Container scanning\",\"label_ru\":\"Сканирование контейнеров\",\"instruction\":\"Cargo inspection metaphor for packet inspection.\"},{\"key\":\"business_team_strategy\",\"weight\":1,\"label_en\":\"Business team solving strategy\",\"label_ru\":\"Бизнес-команда решает задачу\",\"instruction\":\"B2B meeting scene: executives and analysts discussing risk dashboards and decisions.\"}],\"updated_at\":\"2026-02-20 20:38:45\",\"article_cluster_taxonomy_en\":[{\"key\":\"b2b\",\"weight\":1,\"label_en\":\"B2B\",\"label_ru\":\"B2B\",\"keywords\":\"business, enterprise, roi, revenue, operations, growth, conversion, kpi, product team, risk team, implementation, rollout, playbook, onboarding, retention, monetization\"},{\"key\":\"research\",\"weight\":0.9,\"label_en\":\"Research\",\"label_ru\":\"Исследования\",\"keywords\":\"research, study, benchmark, report, analysis, trend, dynamics, evidence, findings, dataset, methodology, hypothesis, sampling, correlation, variance, cohort\"},{\"key\":\"dev\",\"weight\":0.85,\"label_en\":\"Dev\",\"label_ru\":\"Разработка\",\"keywords\":\"api, sdk, integration, backend, frontend, microservice, architecture, php, node, python, golang, java, typescript, webhook, endpoint, auth, token, rate limit\"},{\"key\":\"theory\",\"weight\":0.55,\"label_en\":\"Theory\",\"label_ru\":\"Теория\",\"keywords\":\"concept, framework, principles, model, theory, taxonomy, paradigm, rationale, strategy, abstraction, maturity model, decision model, system thinking\"},{\"key\":\"security\",\"weight\":0.82,\"label_en\":\"Security\",\"label_ru\":\"Безопасность\",\"keywords\":\"security, abuse, attack, threat, exploit, vulnerability, hardening, zero trust, incident response, soc, detection, prevention, mitigation, anomaly, botnet\"},{\"key\":\"fraud\",\"weight\":0.92,\"label_en\":\"Fraud\",\"label_ru\":\"Антифрод\",\"keywords\":\"fraud, antifraud, chargeback, scam, mule, account takeover, multi-account, synthetic identity, promo abuse, payment fraud, card testing, risk scoring, false positive\"},{\"key\":\"compliance\",\"weight\":0.68,\"label_en\":\"Compliance\",\"label_ru\":\"Комплаенс\",\"keywords\":\"compliance, regulation, kyc, aml, sanctions, policy, audit, governance, controls, legal, privacy, gdpr, data residency, risk committee\"},{\"key\":\"analytics\",\"weight\":0.74,\"label_en\":\"Analytics\",\"label_ru\":\"Аналитика\",\"keywords\":\"analytics, dashboard, metrics, attribution, segmentation, funnel, forecasting, anomaly detection, trendline, cohort analysis, retention curve, confidence interval\"},{\"key\":\"geo_intel\",\"weight\":0.88,\"label_en\":\"Geo Intelligence\",\"label_ru\":\"Геоаналитика\",\"keywords\":\"geolocation, geoip, asn, routing, proxy, vpn, tor, datacenter ip, country risk, region profile, ip intelligence, network fingerprint, route anomaly\"},{\"key\":\"operations\",\"weight\":0.7,\"label_en\":\"Operations\",\"label_ru\":\"Операции\",\"keywords\":\"operations, workflow, sla, incident, runbook, playbook, monitoring, alerting, escalation, support, reliability, uptime, performance tuning\"},{\"key\":\"pricing\",\"weight\":0.52,\"label_en\":\"Pricing\",\"label_ru\":\"Тарификация\",\"keywords\":\"pricing, tariff, plan, subscription, billing, cost control, usage, quota, overage, enterprise contract, roi model, budget planning\"},{\"key\":\"case_study\",\"weight\":0.78,\"label_en\":\"Case Study\",\"label_ru\":\"Кейс\",\"keywords\":\"case study, real case, before after, rollout story, lessons learned, mistakes, outcomes, kpi impact, implementation journey\"},{\"key\":\"product\",\"weight\":0.72,\"label_en\":\"Product\",\"label_ru\":\"Продукт\",\"keywords\":\"product strategy, roadmap, feature adoption, product analytics, activation, engagement, user journey, experimentation, a b test\"},{\"key\":\"fintech\",\"weight\":0.8,\"label_en\":\"Fintech\",\"label_ru\":\"Финтех\",\"keywords\":\"fintech, psp, payment gateway, card present, card not present, transaction risk, issuer, acquirer, checkout risk, approval rate\"},{\"key\":\"marketplace\",\"weight\":0.66,\"label_en\":\"Marketplace\",\"label_ru\":\"Маркетплейс\",\"keywords\":\"marketplace, buyer seller, trust and safety, moderation, listing abuse, seller risk, dispute, escrow, reputation\"},{\"key\":\"gaming\",\"weight\":0.61,\"label_en\":\"Gaming\",\"label_ru\":\"Гейминг\",\"keywords\":\"gaming, anti cheat, account farming, bot abuse, regional pricing abuse, virtual goods fraud, player trust\"},{\"key\":\"saas\",\"weight\":0.73,\"label_en\":\"SaaS\",\"label_ru\":\"SaaS\",\"keywords\":\"saas, subscription, churn, trial abuse, seat management, workspace security, tenant risk, expansion revenue\"},{\"key\":\"performance\",\"weight\":0.64,\"label_en\":\"Performance\",\"label_ru\":\"Производительность\",\"keywords\":\"latency, throughput, scaling, optimization, caching, queue, load balancing, bottleneck, p95, p99\"},{\"key\":\"integration\",\"weight\":0.86,\"label_en\":\"Integration\",\"label_ru\":\"Интеграция\",\"keywords\":\"integration guide, implementation steps, code examples, reference architecture, migration, webhook flow, event pipeline\"},{\"key\":\"strategy\",\"weight\":0.58,\"label_en\":\"Strategy\",\"label_ru\":\"Стратегия\",\"keywords\":\"strategic planning, decision framework, prioritization, risk appetite, operating model, north star metric\"}],\"article_cluster_taxonomy_ru\":[{\"key\":\"b2b\",\"weight\":1,\"label_en\":\"B2B\",\"label_ru\":\"B2B\",\"keywords\":\"бизнес, enterprise, roi, выручка, операции, рост, конверсия, kpi, продуктовая команда, риск команда, внедрение, rollout, playbook, онбординг, удержание, монетизация\"},{\"key\":\"research\",\"weight\":0.9,\"label_en\":\"Research\",\"label_ru\":\"Исследования\",\"keywords\":\"исследование, study, бенчмарк, отчет, аналитика, тренд, динамика, доказательства, выводы, dataset, методология, гипотеза, выборка, корреляция, дисперсия, когорта\"},{\"key\":\"dev\",\"weight\":0.85,\"label_en\":\"Dev\",\"label_ru\":\"Разработка\",\"keywords\":\"api, sdk, интеграция, backend, frontend, микросервис, архитектура, php, node, python, golang, java, typescript, webhook, endpoint, auth, token, rate limit\"},{\"key\":\"theory\",\"weight\":0.55,\"label_en\":\"Theory\",\"label_ru\":\"Теория\",\"keywords\":\"концепция, framework, принципы, модель, theory, taxonomy, парадигма, обоснование, стратегия, абстракция, maturity model, decision model, системное мышление\"},{\"key\":\"security\",\"weight\":0.82,\"label_en\":\"Security\",\"label_ru\":\"Безопасность\",\"keywords\":\"безопасность, abuse, атака, угроза, exploit, уязвимость, hardening, zero trust, incident response, soc, детект, предотвращение, mitigation, аномалия, botnet\"},{\"key\":\"fraud\",\"weight\":0.92,\"label_en\":\"Fraud\",\"label_ru\":\"Антифрод\",\"keywords\":\"fraud, antifraud, чарджбек, мошенничество, mule, account takeover, мультиаккаунт, synthetic identity, abuse промокодов, платежный фрод, card testing, risk scoring, false positive\"},{\"key\":\"compliance\",\"weight\":0.68,\"label_en\":\"Compliance\",\"label_ru\":\"Комплаенс\",\"keywords\":\"комплаенс, regulation, kyc, aml, санкции, policy, аудит, governance, контроли, legal, privacy, gdpr, data residency, риск комитет\"},{\"key\":\"analytics\",\"weight\":0.74,\"label_en\":\"Analytics\",\"label_ru\":\"Аналитика\",\"keywords\":\"аналитика, dashboard, метрики, attribution, сегментация, funnel, прогнозирование, детект аномалий, trendline, когортный анализ, retention curve, confidence interval\"},{\"key\":\"geo_intel\",\"weight\":0.88,\"label_en\":\"Geo Intelligence\",\"label_ru\":\"Геоаналитика\",\"keywords\":\"геолокация, geoip, asn, маршрутизация, proxy, vpn, tor, datacenter ip, риск страны, региональный профиль, ip intelligence, сетевой отпечаток, аномалия маршрута\"},{\"key\":\"operations\",\"weight\":0.7,\"label_en\":\"Operations\",\"label_ru\":\"Операции\",\"keywords\":\"операции, workflow, sla, инцидент, runbook, playbook, мониторинг, alerting, эскалация, поддержка, надежность, uptime, тюнинг производительности\"},{\"key\":\"pricing\",\"weight\":0.52,\"label_en\":\"Pricing\",\"label_ru\":\"Тарификация\",\"keywords\":\"тарификация, тариф, план, подписка, биллинг, контроль затрат, usage, quota, overage, enterprise контракт, roi модель, планирование бюджета\"},{\"key\":\"case_study\",\"weight\":0.78,\"label_en\":\"Case Study\",\"label_ru\":\"Кейс\",\"keywords\":\"кейс, реальный кейс, до после, история внедрения, lessons learned, ошибки, результаты, влияние на kpi, путь реализации\"},{\"key\":\"product\",\"weight\":0.72,\"label_en\":\"Product\",\"label_ru\":\"Продукт\",\"keywords\":\"продуктовая стратегия, roadmap, внедрение фич, продуктовая аналитика, активация, вовлечение, user journey, эксперименты, a b test\"},{\"key\":\"fintech\",\"weight\":0.8,\"label_en\":\"Fintech\",\"label_ru\":\"Финтех\",\"keywords\":\"финтех, psp, платежный шлюз, card present, card not present, риск транзакции, issuer, acquirer, риск на чекауте, approval rate\"},{\"key\":\"marketplace\",\"weight\":0.66,\"label_en\":\"Marketplace\",\"label_ru\":\"Маркетплейс\",\"keywords\":\"маркетплейс, buyer seller, trust and safety, модерация, abuse листингов, риск продавца, споры, escrow, репутация\"},{\"key\":\"gaming\",\"weight\":0.61,\"label_en\":\"Gaming\",\"label_ru\":\"Гейминг\",\"keywords\":\"гейминг, anti cheat, фарм аккаунтов, bot abuse, злоупотребление региональными ценами, фрод виртуальных товаров, доверие игроков\"},{\"key\":\"saas\",\"weight\":0.73,\"label_en\":\"SaaS\",\"label_ru\":\"SaaS\",\"keywords\":\"saas, подписка, churn, abuse триалов, управление местами, безопасность workspace, риск tenant, рост выручки\"},{\"key\":\"performance\",\"weight\":0.64,\"label_en\":\"Performance\",\"label_ru\":\"Производительность\",\"keywords\":\"задержка, throughput, масштабирование, оптимизация, кэширование, queue, балансировка, bottleneck, p95, p99\"},{\"key\":\"integration\",\"weight\":0.86,\"label_en\":\"Integration\",\"label_ru\":\"Интеграция\",\"keywords\":\"гайд интеграции, шаги внедрения, примеры кода, референсная архитектура, миграция, webhook flow, event pipeline\"},{\"key\":\"strategy\",\"weight\":0.58,\"label_en\":\"Strategy\",\"label_ru\":\"Стратегия\",\"keywords\":\"стратегическое планирование, decision framework, приоритизация, риск аппетит, operating model, north star metric\"}]}', 1, '2026-02-22 14:30:42', '2026-02-22 14:55:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminpanel_users`
--
ALTER TABLE `adminpanel_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_adminpanel_users_email` (`email`),
  ADD KEY `idx_adminpanel_users_token` (`token`),
  ADD KEY `idx_adminpanel_users_token_expires` (`token_expires`);

--
-- Indexes for table `analytics_lead_events`
--
ALTER TABLE `analytics_lead_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_analytics_leads_event_time` (`event_time`),
  ADD KEY `idx_analytics_leads_type` (`event_type`),
  ADD KEY `idx_analytics_leads_user` (`user_id`),
  ADD KEY `idx_analytics_leads_subscription` (`subscription_id`),
  ADD KEY `idx_analytics_leads_source_type` (`source_type`),
  ADD KEY `idx_analytics_leads_referrer_host` (`referrer_host`),
  ADD KEY `idx_analytics_leads_utm_source` (`utm_source`),
  ADD KEY `idx_analytics_leads_utm_campaign` (`utm_campaign`),
  ADD KEY `idx_analytics_leads_search_query` (`search_query`);

--
-- Indexes for table `analytics_suspect_ip`
--
ALTER TABLE `analytics_suspect_ip`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_ip` (`ip`),
  ADD KEY `idx_confirmed` (`is_confirmed_bot`);

--
-- Indexes for table `analytics_threat_rules`
--
ALTER TABLE `analytics_threat_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_analytics_threat_rules_active_type` (`is_active`,`match_type`),
  ADD KEY `idx_analytics_threat_rules_pattern` (`pattern`(191));

--
-- Indexes for table `analytics_visits`
--
ALTER TABLE `analytics_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_analytics_visits_visited_at` (`visited_at`),
  ADD KEY `idx_analytics_visits_ip` (`ip`),
  ADD KEY `idx_analytics_visits_user_id` (`user_id`),
  ADD KEY `idx_analytics_visits_country` (`country_iso2`),
  ADD KEY `idx_analytics_visits_source` (`source_type`),
  ADD KEY `idx_analytics_visits_device` (`device_type`),
  ADD KEY `idx_analytics_visits_path` (`path`),
  ADD KEY `idx_analytics_visits_ref_host` (`referrer_host`),
  ADD KEY `idx_analytics_visits_search_query` (`search_query`),
  ADD KEY `idx_analytics_ip` (`ip`),
  ADD KEY `idx_analytics_path` (`path`),
  ADD KEY `idx_analytics_suspect` (`is_suspect`);

--
-- Indexes for table `analytics_visits_recovered`
--
ALTER TABLE `analytics_visits_recovered`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_analytics_visits_visited_at` (`visited_at`),
  ADD KEY `idx_analytics_visits_ip` (`ip`),
  ADD KEY `idx_analytics_visits_user_id` (`user_id`),
  ADD KEY `idx_analytics_visits_country` (`country_iso2`),
  ADD KEY `idx_analytics_visits_source` (`source_type`),
  ADD KEY `idx_analytics_visits_device` (`device_type`),
  ADD KEY `idx_analytics_visits_path` (`path`),
  ADD KEY `idx_analytics_visits_ref_host` (`referrer_host`),
  ADD KEY `idx_analytics_visits_search_query` (`search_query`),
  ADD KEY `idx_analytics_ip` (`ip`),
  ADD KEY `idx_analytics_path` (`path`),
  ADD KEY `idx_analytics_suspect` (`is_suspect`);

--
-- Indexes for table `examples_articles`
--
ALTER TABLE `examples_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_examples_domain_slug_lang` (`domain_host`,`slug`,`lang_code`),
  ADD KEY `idx_examples_published` (`is_published`,`published_at`),
  ADD KEY `idx_examples_slug` (`slug`),
  ADD KEY `idx_examples_cluster_code` (`cluster_code`);

--
-- Indexes for table `fx_rates_cron_log`
--
ALTER TABLE `fx_rates_cron_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fx_cron_log_job_time` (`job_name`,`created_at`);

--
-- Indexes for table `fx_rates_crypto_usd`
--
ALTER TABLE `fx_rates_crypto_usd`
  ADD PRIMARY KEY (`crypto_symbol`),
  ADD KEY `idx_fx_crypto_fetched_at` (`fetched_at`),
  ADD KEY `idx_fx_crypto_updated_at` (`updated_at`);

--
-- Indexes for table `fx_rates_fiat_usd`
--
ALTER TABLE `fx_rates_fiat_usd`
  ADD PRIMARY KEY (`currency_code`),
  ADD KEY `idx_fx_fiat_fetched_at` (`fetched_at`),
  ADD KEY `idx_fx_fiat_updated_at` (`updated_at`);

--
-- Indexes for table `mirror_domains`
--
ALTER TABLE `mirror_domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_domain` (`domain`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_template` (`template_view`);

--
-- Indexes for table `mirror_routes`
--
ALTER TABLE `mirror_routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_route` (`route_type`,`route_name`,`page_name`),
  ADD KEY `idx_route_name` (`route_name`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `mirror_templates`
--
ALTER TABLE `mirror_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_template_key` (`template_key`),
  ADD KEY `idx_template_active` (`is_active`),
  ADD KEY `idx_template_shell` (`shell_view`);

--
-- Indexes for table `seo_article_cron_runs`
--
ALTER TABLE `seo_article_cron_runs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_seo_article_slot` (`job_date`,`lang_code`,`slot_index`),
  ADD KEY `idx_seo_article_planned_status` (`planned_at`,`status`),
  ADD KEY `idx_seo_article_lang_date` (`lang_code`,`job_date`);

--
-- Indexes for table `seo_generator_logs`
--
ALTER TABLE `seo_generator_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seo_gen_logs_created` (`created_at`),
  ADD KEY `idx_seo_gen_logs_lang_date` (`lang_code`,`job_date`),
  ADD KEY `idx_seo_gen_logs_status` (`status`);

--
-- Indexes for table `seo_generator_settings`
--
ALTER TABLE `seo_generator_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminpanel_users`
--
ALTER TABLE `adminpanel_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `analytics_lead_events`
--
ALTER TABLE `analytics_lead_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `analytics_suspect_ip`
--
ALTER TABLE `analytics_suspect_ip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `analytics_threat_rules`
--
ALTER TABLE `analytics_threat_rules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `analytics_visits`
--
ALTER TABLE `analytics_visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `analytics_visits_recovered`
--
ALTER TABLE `analytics_visits_recovered`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `examples_articles`
--
ALTER TABLE `examples_articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fx_rates_cron_log`
--
ALTER TABLE `fx_rates_cron_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mirror_domains`
--
ALTER TABLE `mirror_domains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3301;

--
-- AUTO_INCREMENT for table `mirror_routes`
--
ALTER TABLE `mirror_routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mirror_templates`
--
ALTER TABLE `mirror_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9904;

--
-- AUTO_INCREMENT for table `seo_article_cron_runs`
--
ALTER TABLE `seo_article_cron_runs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seo_generator_logs`
--
ALTER TABLE `seo_generator_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
