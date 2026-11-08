-- event_attachment: Google Drive attachments linked to a calendar_event, scoped per tenant.
CREATE TABLE `event_attachment` (
  `id`             INT NOT NULL AUTO_INCREMENT,
  `tenant_username` VARCHAR(128) NULL DEFAULT NULL,
  `google_event_id` VARCHAR(255) NOT NULL,
  `file_id`        VARCHAR(255) NOT NULL,
  `file_url`       VARCHAR(1024) NULL DEFAULT NULL,
  `title`          VARCHAR(512) NULL DEFAULT NULL,
  `mime_type`      VARCHAR(255) NULL DEFAULT NULL,
  `icon_link`      VARCHAR(1024) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_attachment` (`google_event_id`, `file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
