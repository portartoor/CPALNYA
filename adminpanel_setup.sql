-- Admin panel users are stored separately from dashboard users.
-- Dashboard users table: admins
-- Admin panel users table: adminpanel_users

CREATE TABLE IF NOT EXISTS `adminpanel_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password_hash_sha256` CHAR(64) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `token` VARCHAR(128) DEFAULT NULL,
  `token_expires` DATETIME DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_adminpanel_users_email` (`email`),
  KEY `idx_adminpanel_users_token` (`token`),
  KEY `idx_adminpanel_users_token_expires` (`token_expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generated admin password:
-- Adm!nPanel#2026-7QmK
-- SHA-256:
-- 89027aa463e77e002034ee1b2e8d764672e02a450a693343745be6281061b653

INSERT INTO `adminpanel_users` (`email`, `password_hash_sha256`, `is_active`)
VALUES ('adminpanel@geoip.space', '89027aa463e77e002034ee1b2e8d764672e02a450a693343745be6281061b653', 1)
ON DUPLICATE KEY UPDATE
  `password_hash_sha256` = VALUES(`password_hash_sha256`),
  `is_active` = VALUES(`is_active`);
