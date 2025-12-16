<?php

 // 1. Include the Database Connection Class
require_once 'DBConnect.php';

 // 2. Set Header for JSON Output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 


 // 3. Initialize Connection and Get PDO Object
$dbConnection = new DBConnect();
$pdo = $dbConnection->getPDO();


 // 4. Define the SQL Query
 // We select key fields and order by start_time.
$sql = "SELECT 
            google_event_id, 
            summary, 
            description, 
            location, 
            start_time, 
            end_time, 
            is_all_day 
        FROM calendar_event ce
        Where ce.tenant_username = USER()
        ORDER BY start_time ASC";


 // 5. Execute the Query and Handle Response
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
 // Fetch all results as an associative array
    $events = $stmt->fetchAll();

 // Return Success Response
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "count" => count($events),
        "data" => $events
    ]);

} catch (\PDOException $e) {
 // Return Error Response
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Could not retrieve events." // Hide detailed error for security
    ]);
}

?>