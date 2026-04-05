-- Migration: project symbolic code + detail fields
-- Safe to run multiple times on MySQL 8+ / MariaDB with IF NOT EXISTS support.

ALTER TABLE public_projects
    ADD COLUMN IF NOT EXISTS symbolic_code VARCHAR(190) NOT NULL DEFAULT '' AFTER slug,
    ADD COLUMN IF NOT EXISTS industry_summary VARCHAR(255) NOT NULL DEFAULT '' AFTER role_summary,
    ADD COLUMN IF NOT EXISTS period_summary VARCHAR(255) NOT NULL DEFAULT '' AFTER industry_summary,
    ADD COLUMN IF NOT EXISTS metrics_html MEDIUMTEXT NULL AFTER impact_html,
    ADD COLUMN IF NOT EXISTS deliverables_html MEDIUMTEXT NULL AFTER metrics_html;

UPDATE public_projects
SET symbolic_code = slug
WHERE symbolic_code = ''
  AND slug <> '';

ALTER TABLE public_projects
    ADD UNIQUE KEY IF NOT EXISTS uniq_public_projects_domain_lang_symbolic_code (domain_host, lang_code, symbolic_code);
