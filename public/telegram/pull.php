<?php
if (true) {
    $url = 'https://api.telegram.org/bot8086286665:AAFoPhXWBLYRkOuMkAKglLZCZNDmj5vscug/getUpdates';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo '<p style="color: red;">Curl error: ' . curl_error($ch) . '</p>';
    } else {
        $data = json_decode($response, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE){
            echo '<p style="color: red;">Error decoding JSON: ' . json_last_error_msg() . '</p>';
        } else {
         // echo '<pre>';
            print_r($response);
         // echo '</pre>';
        }
    }

    curl_close($ch);
}
?>