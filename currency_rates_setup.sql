-- FX rates storage for API usage
-- 1) National fiat currencies against USD
-- 2) USD against crypto (BTC, ETH, BNB, TON)

CREATE TABLE IF NOT EXISTS `fx_rates_fiat_usd` (
  `currency_code` CHAR(3) NOT NULL,
  `units_per_usd` DECIMAL(28,12) NOT NULL COMMENT 'How many currency units for 1 USD',
  `usd_per_unit` DECIMAL(28,12) NOT NULL COMMENT 'How many USD for 1 currency unit',
  `provider` VARCHAR(64) NOT NULL,
  `provider_updated_at` DATETIME NULL,
  `fetched_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`currency_code`),
  KEY `idx_fx_fiat_fetched_at` (`fetched_at`),
  KEY `idx_fx_fiat_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fx_rates_crypto_usd` (
  `crypto_symbol` VARCHAR(10) NOT NULL,
  `coingecko_id` VARCHAR(64) NOT NULL,
  `usd_per_coin` DECIMAL(28,12) NOT NULL COMMENT 'How many USD for 1 coin',
  `coin_per_usd` DECIMAL(28,12) NOT NULL COMMENT 'How many coins for 1 USD',
  `provider` VARCHAR(64) NOT NULL,
  `provider_updated_at` DATETIME NULL,
  `fetched_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`crypto_symbol`),
  KEY `idx_fx_crypto_fetched_at` (`fetched_at`),
  KEY `idx_fx_crypto_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fx_rates_cron_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_name` VARCHAR(80) NOT NULL,
  `status` ENUM('ok','error') NOT NULL,
  `message` VARCHAR(1000) NULL,
  `fiat_rows` INT UNSIGNED NOT NULL DEFAULT 0,
  `crypto_rows` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fx_cron_log_job_time` (`job_name`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

