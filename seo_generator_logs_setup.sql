-- SEO generator run logs (article + image generation diagnostics).
-- Admin panel section: /adminpanel/seo-generator-logs/

CREATE TABLE IF NOT EXISTS `seo_generator_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_date` DATE NULL,
  `lang_code` VARCHAR(5) NOT NULL DEFAULT 'en',
  `slot_index` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(24) NOT NULL DEFAULT 'success',
  `is_dry_run` TINYINT(1) NOT NULL DEFAULT 0,
  `article_id` INT UNSIGNED NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `slug` VARCHAR(255) NOT NULL DEFAULT '',
  `article_url` VARCHAR(1024) NOT NULL DEFAULT '',
  `words_final` INT UNSIGNED NOT NULL DEFAULT 0,
  `words_initial` INT UNSIGNED NOT NULL DEFAULT 0,
  `structure_used` VARCHAR(1024) NOT NULL DEFAULT '',
  `topic_analysis_source` VARCHAR(64) NOT NULL DEFAULT '',
  `topic_analysis_summary` TEXT NULL,
  `topic_bans_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `image_request_json` LONGTEXT NULL,
  `image_result_json` LONGTEXT NULL,
  `settings_snapshot_json` LONGTEXT NULL,
  `tg_preview_result_json` LONGTEXT NULL,
  `error_message` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_seo_gen_logs_created` (`created_at`),
  KEY `idx_seo_gen_logs_lang_date` (`lang_code`, `job_date`),
  KEY `idx_seo_gen_logs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `seo_generator_logs`
  ADD COLUMN IF NOT EXISTS `tg_preview_result_json` LONGTEXT NULL AFTER `settings_snapshot_json`;
