<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight cross-origin check options gracefully
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../source/Enrollment/EnrollmentDAO.php';
require_once __DIR__ . '/../../source/Calendar/CalendarDAO.php';

// 1. Process and sanitize the raw input payload
$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->googleEventId) ||
    empty($data->email) ||
    empty($data->phone)
) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete enrollment details. All fields are required."]);
    exit();
}

$googleEventId = $data->googleEventId;
$email = $data->email;
$phone = $data->phone;

try {
    // 2. Instantiate the Data Access Layer
    $enrollmentDao = new EnrollmentDAO();
    $calendarDao = new CalendarDAO();

    // 3. Resolve the structural entity connection
    $calendarEventId = $calendarDao->findInternalEventIdByGoogleId($googleEventId);

    if (!$calendarEventId) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Target calendar event not found locally."]);
        exit();
    }

    // 4. Record the request in the tracking ledger queue
    $enrollmentDao->queueEnrollmentRequest($calendarEventId, $email, $phone);

    // 5. Respond back immediately to release the client thread
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Your enrollment request has been queued successfully and will be processed shortly."
    ]);

} catch (PDOException $e) {
    error_log("[enroll.php] Database Exception: " . $e->getMessage());

    // Standard relational validation: duplicate key constraint conflict code
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "You are already enrolled or have a pending request for this event."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "A persistence failure occurred on the server layer."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("[enroll.php] Runtime Exception: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An internal system error occurred."]);
}
?>
