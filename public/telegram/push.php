<?php
header("Content-Type: application/json");
 // Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

 // Read the JSON input
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

 // Define query parameters
$chatId=null;
$text=null;

if (isset($_GET['chatId']) && isset($_GET['text'])) {
 // Read values from query parameters
    $chatId = $_GET['chatId'];
    $text = $_GET['text'];

 // Sanitize input (optional but recommended)
    $chatId = htmlspecialchars($chatId);
    $text = htmlspecialchars($text);
} else {
    // Handle missing parameters
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters: chatId and text"
    ]);
}

 // Define the API URL
$url = "https://api.telegram.org/bot8086286665:AAFoPhXWBLYRkOuMkAKglLZCZNDmj5vscug/sendMessage"; // Actual endpoint

 // Append query parameters to the URL
$url .= "?chat_id=" . urlencode($chatId) . "&text=" . urlencode($text);

 // Initialize cURL
$ch = curl_init($url);

 // Set cURL options for a POST request
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, []); // Sending an empty post body

 // Execute the request
$response = curl_exec($ch);

 // Check for errors
if (curl_errno($ch)) {
    echo json_encode(["success" => false, "message" => curl_error($ch)]);
}
print_r($response);

 // Close cURL
curl_close($ch);

?>