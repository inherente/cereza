<?php
require_once __DIR__ . '/../../source/Calendar/TenantOAuthDAO.php';
require_once __DIR__ . '/../../source/Calendar/CalendarDAO.php';
header('Content-Type: application/json');

// 1. Resolve the current tenant's Google credentials from the DB (tenant = USER())
$response = null;
$config = null;
try {
    $dao = new TenantOAuthDAO();
    $config = $dao->fetchGoogleEventCollection();

    $access_token = $config['access_token'];
    $calendar_id  = $config['google_calendar_id'];

    // 2. Calculate Current Month Range
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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. Handle Google Response
    if ($http_code === 401) {
        http_response_code(401);
        echo json_encode(["error" => "Token expired. Maintenance script will refresh it soon."]);
        exit;
    }

} catch (\PDOException $e) {
    http_response_code(500);
    error_log("[sync-events] DB connection error: " . $e->getMessage());
    echo json_encode(["error" => "Database connection failed", "detail" => $e->getMessage()]);
    exit;
} catch (\Exception $e) {
    http_response_code(500);
    error_log("[sync-events] error: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

// 6. Persist the events into calendar_event (single batch upsert)
try {
    $decoded = json_decode($response, true);
    if ($decoded && isset($decoded['items'])) {
        $calendarDao = new CalendarDAO();
        $result = $calendarDao->upsertCalendarEvents($decoded['items'], $config['google_calendar_id']);
        error_log("[sync-events] Processed: {$result['processed']}, Errors: " . count($result['errors']));
    } else {
        echo json_encode(["error" => "No events found in the response."]);
        exit;
    }
    echo $response;
} catch (\PDOException $e) {
    http_response_code(500);
    error_log("[sync-events] DB error during upsert: " . $e->getMessage());
    echo json_encode(["error" => "Database connection failed", "detail" => $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    error_log("[sync-events] error during upsert: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
