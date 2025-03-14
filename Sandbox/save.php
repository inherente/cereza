<?php
header("Content-Type: application/json");
 // Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

 // Read the JSON input
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

 // Validate input
if (!$data || !isset($data["messageId"], $data["senderId"], $data["text"])) {
    echo json_encode(["success" => false, "message" => "Invalid data received"]);
    exit;
}

$messageId= $data["messageId"];
$senderId= $data["senderId"];
$text= $data["text"];
$updateId= $data["updateId"];
$chatId= $data["chatId"];
$isBot= $data["isBot"];
$senderName= $data["senderName"];
$languageCode= $data["language"];

 // Function to load database configuration from an .ini file
function getDatabaseConfig($file = "db.ini") {
    if (!file_exists($file)) {
        die(json_encode(["error" => "Configuration file not found."]));
    }
    return parse_ini_file($file, true)["database"];
}

 // Load database configuration
$config = getDatabaseConfig();
 // Connect to the database
$conn = new mysqli($config["host"], $config["username"], $config["password"], $config["database"]);

 // Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

 // Step 1: Check if the record already there.
$checkStmt = $conn->prepare("SELECT update_id FROM TELEGRAM_QUEUE WHERE update_id = ?");
$checkStmt->bind_param("i", $updateId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    die(json_encode(["success" => false, "error" => "Record already there."]));
}
$checkStmt->close();

$sql = "INSERT INTO `TELEGRAM_QUEUE` (`Id`, `tenant`, `state`, `status`, `update_id`, `message_id`, `created_by`, `created_date`, `updated_date`, `updated_by`) 
VALUES (NULL, USER(), NULL, NULL, ?, ?, USER(), current_timestamp(), current_timestamp(), USER())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $updateId, $messageId);    

if ($stmt->execute()) {
    ;
 // echo json_encode(["success" => true, "message" => "Record inserted successfully"]);
} else {
    ;
 // echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]); // Get execution error
}

$sql = "INSERT INTO `TELEGRAM_MESSAGE` (`Id`, `tenant`, `telegram_message_id`,`from_id`, `chat_id`, `message_date`, `text`, `created_by`, `created_date`, `updated_date`, `updated_by`) 
VALUES (NULL, USER(), ?, ?, ?, current_timestamp(), ?, USER(), current_timestamp(), current_timestamp(), USER())";

$msgstmt = $conn->prepare($sql);
$msgstmt->bind_param("iiis", $messageId, $senderId, $chatId, $text);    
if ($msgstmt->execute()) {
    ;
 // echo json_encode(["success" => true, "message" => "Record inserted successfully"]);
} else {
    ;
 // echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]); // Get execution error
}


 // Step before: Check if the record already there.
$checkFromStmt = $conn->prepare("SELECT telegram_id FROM TELEGRAM_MESSAGE_FROM WHERE telegram_id = ?");
$checkFromStmt->bind_param("i", $senderId);
$checkFromStmt->execute();
$checkFromStmt->store_result();

if ($checkFromStmt->num_rows == 0) {
 // die(json_encode(["success" => false, "error" => "Record already there."]));
    $sql = "INSERT INTO `TELEGRAM_MESSAGE_FROM` (`Id`, `tenant`, `telegram_id`, `is_bot`, `name`, `language_code`, `created_by`, `created_date`, `updated_date`, `updated_by`) 
    VALUES (NULL, USER(), ?, ?, ?, ?, USER(), current_timestamp(), current_timestamp(), USER())";

    $fromstmt = $conn->prepare($sql);
    $fromstmt->bind_param("iiss", $senderId, $isBot, $senderName, $languageCode);    
    if ($fromstmt->execute()) {
        ;
    } else {
        ;
    }
    $fromstmt->close();
}
$checkFromStmt->close(); 

$response= [ "success" => true, "receivedData" => $data];

echo json_encode($response);
$stmt->close();
$msgstmt->close();

$conn->close();
?>