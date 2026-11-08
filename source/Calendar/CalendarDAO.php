<?php
require_once __DIR__ . '/../Database/DBConnect.php';

class CalendarDAO {

    /**
     * Retrieves all calendar events for the current tenant (MySQL USER()),
     * including nested attachments.
     *
     * @return array Pre-formatted, multi-dimensional array ready for JSON conversion.
     */
    public function fetchEventsWithAttachments(): array {
        $dbConnection = new DBConnect();
        $pdo = $dbConnection->getPDO();

        $sql = "
            SELECT 
                ce.google_event_id,
                ce.summary,
                ce.description,
                ce.location,
                ce.start_time,
                ce.end_time,
                ce.is_all_day,
                ea.file_id,
                ea.file_url,
                ea.title       AS attachment_title,
                ea.mime_type,
                ea.icon_link
            FROM calendar_event ce
            LEFT JOIN event_attachment ea ON ce.google_event_id = ea.google_event_id
            WHERE ce.tenant_username = USER()
            ORDER BY ce.start_time ASC, ea.file_id ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eventsMap = [];
        foreach ($rows as $row) {
            $eventId = $row['google_event_id'];

            // Initialize the event structure if it's the first time encountering it
            if (!isset($eventsMap[$eventId])) {
                $eventsMap[$eventId] = [
                    'googleEventId' => $eventId,
                    'summary'       => $row['summary'],
                    'description'   => $row['description'],
                    'location'      => $row['location'],
                    'startTime'     => $row['start_time'],
                    'endTime'       => $row['end_time'],
                    'isAllDay'      => (bool) $row['is_all_day'],
                    'attachments'   => []
                ];
            }

            // Nest the attachment record under the event array if it exists
            if (!empty($row['file_id'])) {
                $eventsMap[$eventId]['attachments'][] = [
                    'fileId'   => $row['file_id'],
                    'fileUrl'  => $row['file_url'],
                    'title'    => $row['attachment_title'],
                    'mimeType' => $row['mime_type'],
                    'iconLink' => $row['icon_link']
                ];
            }
        }

        // Strip the string array keys to return a standard index-sequential array
        return array_values($eventsMap);
    }

    /**
     * Finds the local auto-incremented primary key ID for a calendar event
     * using its unique Google Event string identifier, scoped to the current tenant.
     *
     * @param string $googleEventId
     * @return int|null Returns the internal ID, or null if not found.
     */
    public function findInternalEventIdByGoogleId(string $googleEventId): ?int {
        $dbConnection = new DBConnect();
        $pdo = $dbConnection->getPDO();

        $stmt = $pdo->prepare("
            SELECT id 
            FROM calendar_event 
            WHERE google_event_id = :google_event_id AND tenant_username = USER()
            LIMIT 1
        ");

        $stmt->execute([':google_event_id' => $googleEventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['id'] : null;
    }

    /**
     * Upserts a batch of Google Calendar events for the current tenant (MySQL USER()).
     *
     * @param array  $events      The events array from Google's API response.
     * @param string $calendar_id The Google Calendar ID these events belong to.
     * @return array ['processed' => int, 'errors' => array]
     */
    public function upsertCalendarEvents(array $events, string $calendar_id): array {
        $dbConnection = new DBConnect();
        $pdo = $dbConnection->getPDO();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            INSERT INTO calendar_event (
                account_id, tenant_username, google_event_id, calendar_id,
                summary, description, location, start_time, end_time,
                is_all_day, status, creator_email, created_by, last_modified, created_at
            ) VALUES (
                :account_id, USER(), :google_event_id, :calendar_id,
                :summary, :description, :location, :start_time, :end_time,
                :is_all_day, :status, :creator_email, :created_by, :last_modified, :created_at
            )
            ON DUPLICATE KEY UPDATE
                summary       = VALUES(summary),
                description   = VALUES(description),
                location      = VALUES(location),
                start_time    = VALUES(start_time),
                end_time      = VALUES(end_time),
                is_all_day    = VALUES(is_all_day),
                status        = VALUES(status),
                creator_email = VALUES(creator_email),
                created_by    = VALUES(created_by),
                last_modified = VALUES(last_modified)
        ");

        $attachmentStmt = $pdo->prepare("
            INSERT INTO event_attachment (
                tenant_username, google_event_id, file_id,
                file_url, title, mime_type, icon_link
            ) VALUES (
                USER(), :google_event_id, :file_id,
                :file_url, :title, :mime_type, :icon_link
            )
            ON DUPLICATE KEY UPDATE
                file_url  = VALUES(file_url),
                title     = VALUES(title),
                mime_type = VALUES(mime_type),
                icon_link = VALUES(icon_link)
        ");

        $processed = 0;
        $errors    = [];

        foreach ($events as $event) {
            try {
                $google_event_id = $event['id'];
                $summary         = $event['summary'] ?? null;
                $description     = $event['description'] ?? null;
                $location        = $event['location'] ?? null;

                $raw_start  = $event['start']['dateTime'] ?? $event['start']['date'] ?? null;
                $raw_end    = $event['end']['dateTime']   ?? $event['end']['date']   ?? null;
                $start_time = $this->toMySQLDatetime($raw_start);
                $end_time   = $this->toMySQLDatetime($raw_end);

                $is_all_day    = isset($event['start']['date']) && !isset($event['start']['dateTime']) ? 1 : 0;
                $status        = $event['status'] ?? null;
                $creator_email = $event['creator']['email'] ?? null;
                $created_by    = $event['creator']['displayName'] ?? null;
                $now           = date('Y-m-d H:i:s');

                $stmt->execute([
                    ':account_id'      => 1,
                    ':google_event_id' => $google_event_id,
                    ':calendar_id'     => $calendar_id,
                    ':summary'         => $summary,
                    ':description'     => $description,
                    ':location'        => $location,
                    ':start_time'      => $start_time,
                    ':end_time'        => $end_time,
                    ':is_all_day'      => $is_all_day,
                    ':status'          => $status,
                    ':creator_email'   => $creator_email,
                    ':created_by'      => $creator_email,
                    ':last_modified'   => $now,
                    ':created_at'      => $now,
                ]);

                $processed++;
                error_log("[CalendarDAO] Upserted event: $google_event_id - $summary (rows affected: {$stmt->rowCount()})");

                // Handle attachments if present
                if (!empty($event['attachments'])) {
                    $this->upsertAttachments($attachmentStmt, $event['attachments'], $google_event_id);
                } else {
                    error_log("[CalendarDAO] No attachments for event: $google_event_id");
                }

            } catch (PDOException $e) {
                $errors[] = $google_event_id ?? 'unknown';
                error_log("[CalendarDAO] Failed to upsert event: " . ($google_event_id ?? 'unknown') . " - " . $e->getMessage());
            }
        }

        return [
            'processed' => $processed,
            'errors'    => $errors,
        ];
    }

    private function upsertAttachments($stmt, array $attachments, string $google_event_id): void {
        foreach ($attachments as $attachment) {
            try {
                $file_id = $attachment['fileId'] ?? null;

                if (!$file_id) {
                    error_log("[CalendarDAO] Skipping attachment with no fileId for event: $google_event_id");
                    continue;
                }

                $stmt->execute([
                    ':google_event_id' => $google_event_id,
                    ':file_id'         => $file_id,
                    ':file_url'        => $attachment['fileUrl']  ?? null,
                    ':title'           => $attachment['title']    ?? null,
                    ':mime_type'       => $attachment['mimeType'] ?? null,
                    ':icon_link'       => $attachment['iconLink'] ?? null,
                ]);

                error_log("[CalendarDAO] Upserted attachment: $file_id for event: $google_event_id (rows affected: {$stmt->rowCount()})");

            } catch (PDOException $e) {
                error_log("[CalendarDAO] Failed to upsert attachment: " . ($file_id ?? 'unknown') . " - " . $e->getMessage());
            }
        }
    }

    private function toMySQLDatetime(?string $googleDatetime): ?string {
        if (!$googleDatetime) return null;
        try {
            $dt = new DateTime($googleDatetime);
            $dt->setTimezone(new DateTimeZone('UTC'));
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            error_log("[CalendarDAO] Invalid datetime: $googleDatetime");
            return null;
        }
    }
}
?>
