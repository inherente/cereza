<?php
 // Your bot token from BotFather
$TOKEN = "bot8086286665:AAFoPhXWBLYRkOuMkAKglLZCZNDmj5vscug";

 // Get the update (Telegram sends updates in JSON format)
$content = file_get_contents("php://input");
$data = json_decode($content, true);
// Function to send data to save.php
function save($jsonData) {
    $url = "https://api.cerezasanta.com/Sandbox/save.php"; // actual save.php URL

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($jsonData)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Function to send a message to Telegram
function sendMessage($chat_id, $message) {
    global $TOKEN;
    $url = "https://api.telegram.org/bot$TOKEN/sendMessage";
    
    $data = [
        "chat_id" => $chat_id,
        "text" => $message,
        "parse_mode" => "HTML"
    ];

    // Use cURL to send the message
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

 // Check if the update contains a message
if (isset($data["message"])) {

    $messageId= $data["message"]["message_id"];
    $senderId= $data["message"]["from"]["id"];
    $text= $data["message"]["text"];
    $updateId= $data["update_id"];
    $chatId= $data["message"]["chat"]["id"];
    $isBot= $data["message"]["from"]["is_bot"];
    $senderName= $data["message"]["from"]["first_name"];
    $languageCode= $data["message"]["from"]["language_code"];

 // Create JSON payload
    $jsonData = json_encode([
        "messageId" => $messageId,
        "senderId" => $senderId,
        "text" => $text,
        "updateId" => $updateId,
        "chatId" => $chatId,
        "isBot" => $isBot,
        "senderName" => $senderName,
        "languageCode" => $languageCode
    ]);
 // Send data to save.php
    $response= save($jsonData);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required payload"
    ]);
}
echo json_encode([
    "status" => "true",
    "message" => $response
]);


?>