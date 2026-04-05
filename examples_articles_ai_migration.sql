-- Adds AI metadata fields for generated examples articles.

SET @db := DATABASE();

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db
              AND TABLE_NAME = 'examples_articles'
              AND COLUMN_NAME = 'is_ai_generated'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `is_ai_generated` TINYINT(1) NOT NULL DEFAULT 0 AFTER `lang_code`'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db
              AND TABLE_NAME = 'examples_articles'
              AND COLUMN_NAME = 'ai_provider'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `ai_provider` VARCHAR(32) DEFAULT NULL AFTER `is_ai_generated`'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db
              AND TABLE_NAME = 'examples_articles'
              AND COLUMN_NAME = 'ai_model'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `ai_model` VARCHAR(120) DEFAULT NULL AFTER `ai_provider`'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db
              AND TABLE_NAME = 'examples_articles'
              AND COLUMN_NAME = 'ai_prompt_version'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `ai_prompt_version` VARCHAR(32) DEFAULT NULL AFTER `ai_model`'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db
              AND TABLE_NAME = 'examples_articles'
              AND COLUMN_NAME = 'ai_generated_at'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `ai_generated_at` DATETIME DEFAULT NULL AFTER `ai_prompt_version`'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
