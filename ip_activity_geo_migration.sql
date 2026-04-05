-- Adds geocoordinates to ip_activity for advanced geo analytics.
-- Idempotent migration for MySQL/MariaDB.

SET @schema_name := DATABASE();

SET @has_lat := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'ip_activity'
      AND COLUMN_NAME = 'lat'
);
SET @sql := IF(
    @has_lat = 0,
    'ALTER TABLE `ip_activity` ADD COLUMN `lat` DECIMAL(10,7) NULL AFTER `country`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_lon := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'ip_activity'
      AND COLUMN_NAME = 'lon'
);
SET @sql := IF(
    @has_lon = 0,
    'ALTER TABLE `ip_activity` ADD COLUMN `lon` DECIMAL(10,7) NULL AFTER `lat`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_idx_account_created := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'ip_activity'
      AND INDEX_NAME = 'idx_ip_activity_account_created'
);
SET @sql := IF(
    @has_idx_account_created = 0,
    'CREATE INDEX `idx_ip_activity_account_created` ON `ip_activity` (`account_id`, `created_at`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_idx_created := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'ip_activity'
      AND INDEX_NAME = 'idx_ip_activity_created'
);
SET @sql := IF(
    @has_idx_created = 0,
    'CREATE INDEX `idx_ip_activity_created` ON `ip_activity` (`created_at`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
