-- Adds preview image metadata fields to examples_articles.

SET @db := DATABASE();

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db
              AND TABLE_NAME = 'examples_articles'
              AND COLUMN_NAME = 'preview_image_url'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `preview_image_url` VARCHAR(1024) DEFAULT NULL AFTER `content_html`'
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
              AND COLUMN_NAME = 'preview_image_style'
        ),
        'SELECT 1',
        'ALTER TABLE `examples_articles` ADD COLUMN `preview_image_style` VARCHAR(64) DEFAULT NULL AFTER `preview_image_url`'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
