CREATE TABLE IF NOT EXISTS `seo_article_generation_queue` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_date` DATE NOT NULL,
  `lang_code` VARCHAR(8) NOT NULL,
  `force_mode` TINYINT(1) NOT NULL DEFAULT 0,
  `dry_run` TINYINT(1) NOT NULL DEFAULT 0,
  `max_per_run` INT NOT NULL DEFAULT 1,
  `status` ENUM('queued','processing','success','failed') NOT NULL DEFAULT 'queued',
  `attempts` INT NOT NULL DEFAULT 0,
  `planned_at` DATETIME DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `finished_at` DATETIME DEFAULT NULL,
  `last_exit_code` INT NOT NULL DEFAULT 0,
  `last_output` MEDIUMTEXT DEFAULT NULL,
  `last_error` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_planned` (`status`, `planned_at`),
  KEY `idx_job_lang` (`job_date`, `lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

