CREATE TABLE IF NOT EXISTS `mirror_domains` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain` VARCHAR(190) NOT NULL,
  `template_view` VARCHAR(32) NOT NULL DEFAULT 'simple',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_domain` (`domain`),
  KEY `idx_active` (`is_active`),
  KEY `idx_template` (`template_view`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `mirror_domains` (`domain`, `template_view`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('geoip.space', 'simple', 1, NOW(), NOW()),
  ('www.geoip.space', 'simple', 1, NOW(), NOW()),
  ('apigeoip.ru', 'simple_apigeo_ru', 1, NOW(), NOW()),
  ('www.apigeoip.ru', 'simple_apigeo_ru', 1, NOW(), NOW());

CREATE TABLE IF NOT EXISTS `mirror_templates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_key` VARCHAR(64) NOT NULL,
  `display_name` VARCHAR(190) NOT NULL,
  `shell_view` VARCHAR(32) NOT NULL DEFAULT 'simple',
  `main_view_file` VARCHAR(128) NOT NULL DEFAULT 'main.php',
  `model_file` VARCHAR(128) NOT NULL DEFAULT 'main.php',
  `control_file` VARCHAR(128) NOT NULL DEFAULT 'main.php',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_template_key` (`template_key`),
  KEY `idx_template_active` (`is_active`),
  KEY `idx_template_shell` (`shell_view`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `mirror_templates`
  (`template_key`, `display_name`, `shell_view`, `main_view_file`, `model_file`, `control_file`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('simple', 'Landing (GeoIP)', 'simple', 'main.php', 'main.php', 'main.php', 1, NOW(), NOW()),
  ('simple_apigeo', 'Landing (ApiGeoIP)', 'simple', 'main_apigeoip.php', 'main_apigeoip.php', 'main_apigeoip.php', 1, NOW(), NOW()),
  ('simple_apigeo_ru', 'Landing (ApiGeoIP RU)', 'simple', 'main_apigeoip_ru.php', 'main_apigeoip_ru.php', 'main_apigeoip_ru.php', 1, NOW(), NOW()),
  ('dashboard', 'Dashboard UI', 'dashboard', 'main.php', 'main.php', 'main.php', 1, NOW(), NOW());
