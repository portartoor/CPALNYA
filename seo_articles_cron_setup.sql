-- SEO articles cron state table
-- Required by: cron/generate_seo_articles.php

CREATE TABLE IF NOT EXISTS `seo_article_cron_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_date` DATE NOT NULL,
  `lang_code` VARCHAR(5) NOT NULL,
  `campaign_key` VARCHAR(32) NOT NULL DEFAULT '',
  `slot_index` TINYINT UNSIGNED NOT NULL,
  `planned_at` DATETIME NOT NULL,
  `status` ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `article_id` INT UNSIGNED NULL,
  `message` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_seo_article_slot_campaign` (`job_date`, `lang_code`, `campaign_key`, `slot_index`),
  KEY `idx_seo_article_planned_status` (`planned_at`, `status`),
  KEY `idx_seo_article_lang_date_campaign` (`lang_code`, `campaign_key`, `job_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
