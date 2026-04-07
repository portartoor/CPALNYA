CREATE TABLE IF NOT EXISTS `public_footer_seo_blocks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_host` VARCHAR(190) NOT NULL DEFAULT '',
  `lang_code` VARCHAR(5) NOT NULL DEFAULT 'ru',
  `section_scope` VARCHAR(32) NOT NULL DEFAULT 'all',
  `style_variant` VARCHAR(64) NOT NULL DEFAULT 'editorial-note',
  `block_kicker` VARCHAR(190) NOT NULL DEFAULT '',
  `block_title` VARCHAR(255) NOT NULL DEFAULT '',
  `body_html` MEDIUMTEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_footer_blocks_lookup` (`lang_code`, `section_scope`, `is_active`, `sort_order`),
  KEY `idx_footer_blocks_domain` (`domain_host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
