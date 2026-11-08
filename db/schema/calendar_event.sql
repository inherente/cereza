-- calendar_event: canonical store for Google Calendar events, scoped per tenant via tenant_username (= MySQL USER()).
-- Inferred from CalendarDAO column usage; verify against your live database.
CREATE TABLE `calendar_event` (
  `id`             INT NOT NULL AUTO_INCREMENT,
  `account_id`     INT NULL DEFAULT NULL,
  `tenant_username` VARCHAR(128) NOT NULL,
  `google_event_id` VARCHAR(255) NOT NULL,
  `calendar_id`    VARCHAR(255) NULL DEFAULT NULL,
  `summary`        VARCHAR(512) NULL DEFAULT NULL,
  `description`    TEXT NULL,
  `location`       VARCHAR(512) NULL DEFAULT NULL,
  `start_time`     DATETIME NULL DEFAULT NULL,
  `end_time`       DATETIME NULL DEFAULT NULL,
  `is_all_day`     TINYINT(1) NOT NULL DEFAULT 0,
  `status`         VARCHAR(64) NULL DEFAULT NULL,
  `creator_email`  VARCHAR(255) NULL DEFAULT NULL,
  `created_by`     VARCHAR(255) NULL DEFAULT NULL,
  `last_modified`  DATETIME NULL DEFAULT NULL,
  `created_at`     DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_calendar_event_google_id` (`google_event_id`),
  KEY `idx_calendar_event_tenant` (`tenant_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
