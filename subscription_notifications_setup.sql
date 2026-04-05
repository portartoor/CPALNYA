-- Subscription lifecycle + notifications schema updates

ALTER TABLE admins
    ADD COLUMN IF NOT EXISTS registration_domain VARCHAR(190) NULL AFTER created_at;

ALTER TABLE subscriptions
    ADD COLUMN IF NOT EXISTS source_domain VARCHAR(190) NULL AFTER tx_hash,
    ADD COLUMN IF NOT EXISTS activated_at DATETIME NULL AFTER created_at,
    ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL AFTER activated_at,
    ADD COLUMN IF NOT EXISTS deactivated_at DATETIME NULL AFTER expires_at,
    ADD COLUMN IF NOT EXISTS extended_from_subscription_id INT NULL AFTER deactivated_at;

CREATE INDEX IF NOT EXISTS idx_subscriptions_user_status_expires
    ON subscriptions (user_id, status, expires_at);

CREATE TABLE IF NOT EXISTS user_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(64) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    payload_json JSON NULL,
    link_url VARCHAR(255) NULL,
    event_key VARCHAR(190) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_notifications_event_key (event_key),
    KEY idx_user_notifications_user_read_created (user_id, is_read, created_at),
    KEY idx_user_notifications_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE api_logs
    ADD COLUMN IF NOT EXISTS request_ua TEXT NULL AFTER ip,
    ADD COLUMN IF NOT EXISTS request_user_id VARCHAR(191) NULL AFTER request_ua,
    ADD COLUMN IF NOT EXISTS owner_id INT NULL AFTER request_user_id,
    ADD COLUMN IF NOT EXISTS subscription_type VARCHAR(64) NULL AFTER owner_id,
    ADD COLUMN IF NOT EXISTS http_status INT NULL AFTER subscription_type,
    ADD COLUMN IF NOT EXISTS status VARCHAR(32) NULL AFTER http_status,
    ADD COLUMN IF NOT EXISTS error_code INT NULL AFTER status,
    ADD COLUMN IF NOT EXISTS response_json LONGTEXT NULL AFTER error_code;

CREATE INDEX IF NOT EXISTS idx_api_logs_key_created
    ON api_logs (api_key, created_at);
