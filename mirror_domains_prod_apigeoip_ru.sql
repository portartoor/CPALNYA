-- Prod migration for apigeoip.ru mirror (no CREATE TABLE statements).
-- Prerequisite: tables mirror_domains and mirror_templates already exist.

START TRANSACTION;

-- 1) Ensure template exists
INSERT IGNORE INTO `mirror_templates`
  (`template_key`, `display_name`, `shell_view`, `main_view_file`, `model_file`, `control_file`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('simple_apigeo_ru', 'Landing (ApiGeoIP RU)', 'simple', 'main_apigeoip_ru.php', 'main_apigeoip_ru.php', 'main_apigeoip_ru.php', 1, NOW(), NOW());

UPDATE `mirror_templates`
SET
  `display_name` = 'Landing (ApiGeoIP RU)',
  `shell_view` = 'simple',
  `main_view_file` = 'main_apigeoip_ru.php',
  `model_file` = 'main_apigeoip_ru.php',
  `control_file` = 'main_apigeoip_ru.php',
  `is_active` = 1,
  `updated_at` = NOW()
WHERE `template_key` = 'simple_apigeo_ru';

-- 2) Ensure mirror domains exist
INSERT IGNORE INTO `mirror_domains`
  (`domain`, `template_view`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('apigeoip.ru', 'simple_apigeo_ru', 1, NOW(), NOW()),
  ('www.apigeoip.ru', 'simple_apigeo_ru', 1, NOW(), NOW());

UPDATE `mirror_domains`
SET
  `template_view` = 'simple_apigeo_ru',
  `is_active` = 1,
  `updated_at` = NOW()
WHERE `domain` IN ('apigeoip.ru', 'www.apigeoip.ru');

COMMIT;
