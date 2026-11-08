-- enrollment_request: queue of sign-up requests against a calendar_event.
-- The unique key prevents duplicate pending requests for the same event + email.
CREATE TABLE `enrollment_request` (
  `id`               INT NOT NULL AUTO_INCREMENT,
  `calendar_event_id` INT NOT NULL,
  `email`            VARCHAR(255) NOT NULL,
  `phone`            VARCHAR(64) NULL DEFAULT NULL,
  `processing_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `retry_count`      INT NOT NULL DEFAULT 0,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enrollment_request` (`calendar_event_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
