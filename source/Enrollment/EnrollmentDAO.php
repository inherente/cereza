<?php
require_once __DIR__ . '/../Database/DBConnect.php';

class EnrollmentDAO {

    /**
     * Queues an enrollment request into the asynchronous queue table.
     * Uses an ON DUPLICATE KEY UPDATE check to safely handle re-submissions or conflicts.
     *
     * @param int    $calendarEventId
     * @param string $email
     * @param string $phone
     * @return bool Returns true on a successful operation execution.
     * @throws PDOException With code 23000 when a duplicate enrollment is detected.
     */
    public function queueEnrollmentRequest(int $calendarEventId, string $email, string $phone): bool {
        $dbConnection = new DBConnect();
        $pdo = $dbConnection->getPDO();

        $sql = "INSERT INTO enrollment_request (
                    calendar_event_id, email, phone, processing_status
                ) VALUES (
                    :calendar_event_id, :email, :phone, 'pending'
                )
                ON DUPLICATE KEY UPDATE 
                    phone = VALUES(phone),
                    processing_status = IF(processing_status = 'failed', 'pending', processing_status),
                    retry_count = IF(processing_status = 'failed', 0, retry_count)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':calendar_event_id' => $calendarEventId,
            ':email'             => trim($email),
            ':phone'             => trim($phone),
        ]);

        // rowCount 2 (updated) or 0 (no change) means a duplicate key constraint was hit
        if ($stmt->rowCount() === 2 || $stmt->rowCount() === 0) {
            throw new PDOException("Duplicate enrollment detected.", 23000);
        }

        return true;
    }
}
?>
