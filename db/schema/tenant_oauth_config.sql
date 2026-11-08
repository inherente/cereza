-- tenant_oauth_config: per-tenant Google OAuth tokens + calendar id.
-- tenant_name stores the MySQL USER() value (e.g. 'cereza@localhost').
-- expires_at is a Unix timestamp (seconds); the refresh cron refreshes before expiry.
CREATE TABLE `tenant_oauth_config` (
  `id`                 INT NOT NULL AUTO_INCREMENT,
  `tenant_name`        VARCHAR(128) NOT NULL,
  `google_calendar_id` VARCHAR(255) NULL DEFAULT NULL,
  `access_token`       TEXT NULL,
  `refresh_token`      TEXT NULL,
  `expires_at`         BIGINT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_oauth_tenant` (`tenant_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
