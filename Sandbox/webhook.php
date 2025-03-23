<?php
 // Your bot token from BotFather
$TOKEN = "bot8086286665:AAFoPhXWBLYRkOuMkAKglLZCZNDmj5vscug";

 // Get the update (Telegram sends updates in JSON format)
$content = file_get_contents("php://input");
$update = json_decode($content, true);

 // Check if the update contains a message
if (isset($update["message"])) {

    $messageId= $data["message"]["message_id"];
    $senderId= $data["message"]["from"]["id"];
    $text= $data["message"]["text"];
    $updateId= $data["message"]["update_id"];
    $chatId= $data["message"]["chat"]["id"];
    $isBot= $data["message"]["from"]["is_bot"];
    $senderName= $data["message"]["from"]["first_name"];
    $languageCode= $data["message"]["from"]["language_code"];

 // Create JSON payload
    $jsonData = json_encode([
        "message_id" => $messageId,
        "sender_id" => $senderId,
        "text" => $text,
        "update_id" => $updateId,
        "chat_id" => $chatId,
        "is_bot" => $isBot,
        "sender_name" => $senderName,
        "language_code" => $languageCode
    ]);
 // Send data to save.php
    sendToWebService($jsonData);

}

// Function to send data to save.php
function sendToWebService($jsonData) {
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

?>