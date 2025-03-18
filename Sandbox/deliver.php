<?php
header("Content-Type: application/json");
 // Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
$lockName= 'Queue';

 // Read the JSON input
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

 // Function to load database configuration from an .ini file
function getDatabaseConfig($file = "db.ini") {
    if (!file_exists($file)) {
        die(json_encode(["error" => "Configuration file not found."]));
    }
    return parse_ini_file($file, true)["database"];
}

function updateMessageStatus($conn, $messageId) {
    $sql="UPDATE TELEGRAM_QUEUE T SET T.STATE= 'Done' WHERE T.message_id = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messageId);    
    if ($stmt->execute()) {
        ;
    } else {
        ;
    }
}

function releaseLock($conn) {
    $sql="UPDATE APP_LOCK_MANAGER T SET T.STATUS= 'Free' WHERE T.KEY_NAME = 'Queue' ";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        ;
    } else {
        ;
    }
}

function takeLock($conn, $lockName) {
    $sql="UPDATE APP_LOCK_MANAGER T SET T.STATUS= 'Taken' WHERE T.KEY_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $lockName);    
    if ($stmt->execute()) {
        ;
    } else {
        ;
    }
}

/**
 * Function to send request with query parameters to internal API
 */
function sendToInternalAPI($chat_id, $text) {
 // Build the full URL with query parameters
    $url = "https://api.cerezasanta.com/Sandbox/push.php?chatId=" . urlencode($chat_id) . "&text=" . urlencode($text);

 // Initialize cURL
    $ch = curl_init();

 // Set cURL options for GET request
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

 // Execute request
    $response = curl_exec($ch);

 // Check for errors
    if (curl_errno($ch)) {
        echo json_encode(["success" => false, "message" => curl_error($ch) ] );
    } else {
        echo json_encode(["success" => true, "message" => "Message sent to internal API for chatId: $chat_id " ] );
    }

 // Close cURL
    curl_close($ch);
}

 // Load database configuration
$config = getDatabaseConfig();
 // Connect to the database
$conn = new mysqli($config["host"], $config["username"], $config["password"], $config["database"]);

 // Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

 // Step 1: Check if the lock already taken.
 // Define the SQL query
$checkStmt = $conn->prepare("SELECT UPDATED_BY FROM APP_LOCK_MANAGER WHERE KEY_NAME = ? AND STATUS = 'Free'");
$checkStmt->bind_param("s", $lockName);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows == 0) {
    die(json_encode(["success" => true, "error" => "Lock already taken by ."]));
}
$checkStmt->close();

takeLock($conn, $lockName);

 // Define the SQL query
$sql = "SELECT TM.telegram_message_id, TM.from_id, TM.chat_id, TM.text 
        FROM TELEGRAM_MESSAGE TM
        JOIN TELEGRAM_QUEUE TQ ON TQ.message_id = TM.telegram_message_id
        WHERE TQ.state IS NULL";

 // Execute the check query
$result = $conn->query($sql);

 // Check if rows were returned
if ($result->num_rows > 0) {
 // Loop through the result set
    while ($row = $result->fetch_assoc()) {
        $chat_id = $row["chat_id"];
        $text = $row["text"];
        $messageId= $row["telegram_message_id"];

     // Call internal API
        sendToInternalAPI($chat_id, $text);
        updateMessageStatus($conn, $messageId);
    }
} else {
    echo json_encode([
        "success" => "true",
        "message" => "No results found."
    ]);
}
releaseLock($conn);

 // Close the connection
$conn->close();

?>