-- SEO generator settings storage (single-row JSON document).
-- Admin panel section: /adminpanel/seo-generator/

CREATE TABLE IF NOT EXISTS `seo_generator_settings` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `settings_json` LONGTEXT NOT NULL,
  `updated_by_admin_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

