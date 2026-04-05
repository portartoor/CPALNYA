-- Admin notifications table for backend events (SEO cron, failures, etc.).

CREATE TABLE IF NOT EXISTS `admin_notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(64) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link_url` VARCHAR(500) DEFAULT '',
  `payload_json` JSON DEFAULT NULL,
  `event_key` VARCHAR(190) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admin_notifications_event_key` (`event_key`),
  KEY `idx_admin_notifications_is_read_created` (`is_read`, `created_at`),
  KEY `idx_admin_notifications_type_created` (`type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
