<?php
header('Content-Type: application/json');

// 1. Configuration
$api_key = ''; // If public calendar
$access_token = ''; // Required for private calendars
$calendar_id = '5bf1ac8780ea4b11631c7e492935cf7810fc2b74f5bf98d5761e50f12fd05c47@group.calendar.google.com'; // Or your specific calendar ID (email address)

// 2. Calculate Current Month Range (RFC3339 format)
$first_day = date('Y-m-01\T00:00:00\Z');
$last_day  = date('Y-m-t\T23:59:59\Z');

// 3. Build the API URL
$url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendar_id) . "/events?" . http_build_query([
    'timeMin' => $first_day,
    'timeMax' => $last_day,
    'singleEvents' => 'true',
    'orderBy' => 'startTime'
]);

// 4. Fetch the Data via cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Accept: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

// 5. Output for your Angular App
echo $response;
?>