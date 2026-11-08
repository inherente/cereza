<?php
// 1. Include the Data Access Class dependencies
require_once __DIR__ . '/../../source/Calendar/CalendarDAO.php';

// 2. Set Headers for public client cross-origin traffic
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// 3. Tenant is resolved from the MySQL connection identity (USER()).

try {
    // 4. Instantiate the DAO layer and run the structured abstraction query
    $calendarDao = new CalendarDAO();
    $events = $calendarDao->fetchEventsWithAttachments();

    // 5. Return success payload downstream to Angular
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "count"  => count($events),
        "data"   => $events
    ]);

} catch (\PDOException $e) {
    error_log("[event-list.php] Critical DB Failure: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Could not retrieve events due to a server infrastructure issue."
    ]);
} catch (\Exception $e) {
    error_log("[event-list.php] Runtime Failure: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "An unexpected application issue occurred."
    ]);
}
?>
