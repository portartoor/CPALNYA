-- Adds payment amount/currency tracking fields for subscriptions.
-- Safe idempotent migration for MySQL/MariaDB.

SET @schema_name := DATABASE();

SET @has_wallet_address := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'subscriptions'
      AND COLUMN_NAME = 'wallet_address'
);
SET @sql := IF(
    @has_wallet_address = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `wallet_address` VARCHAR(255) NULL AFTER `wallet_id`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_currency_code := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'subscriptions'
      AND COLUMN_NAME = 'currency_code'
);
SET @sql := IF(
    @has_currency_code = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `currency_code` VARCHAR(16) NULL AFTER `wallet_address`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_amount_usd := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'subscriptions'
      AND COLUMN_NAME = 'amount_usd'
);
SET @sql := IF(
    @has_amount_usd = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `amount_usd` DECIMAL(28,12) NULL AFTER `currency_code`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_amount_crypto := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'subscriptions'
      AND COLUMN_NAME = 'amount_crypto'
);
SET @sql := IF(
    @has_amount_crypto = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `amount_crypto` DECIMAL(28,12) NULL AFTER `amount_usd`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_amount_in_currency := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'subscriptions'
      AND COLUMN_NAME = 'amount_in_currency'
);
SET @sql := IF(
    @has_amount_in_currency = 0,
    'ALTER TABLE `subscriptions` ADD COLUMN `amount_in_currency` DECIMAL(28,12) NULL AFTER `amount_crypto`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
