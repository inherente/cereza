<?php
require_once __DIR__ . '/../../source/Config.php';
require_once __DIR__ . '/../../source/Database/DBConnect.php';

// Google credentials + calendar id are loaded from config/google.ini (gitignored)
$google         = getGoogleConfig();
$client_id      = $google['client_id'];
$client_secret  = $google['client_secret'];
$redirect_uri   = $google['redirect_uri'];
$calendar_id    = $google['calendar_id'];

header('Content-Type: text/html; charset=UTF-8');

// 1. Capture the authorization code returned by Google
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Warn if the redirect URI used by Google doesn't match the configured one
    $current_redirect = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
    if ($current_redirect !== $redirect_uri) {
        error_log("Warning: detected redirect URI ($current_redirect) does not match configured ($redirect_uri)");
    }

    // 2. Exchange the code for tokens
    $token_url = "https://oauth2.googleapis.com/token";
    $post_fields = [
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if (isset($data['access_token'])) {
        $access_token  = $data['access_token'];
        $refresh_token = $data['refresh_token'] ?? null;
        $expires_at    = time() + ($data['expires_in'] ?? 3600);

        // 3. Persist the tokens for the current tenant (tenant_name = MySQL USER())
        try {
            $dbConnection = new DBConnect();
            $pdo = $dbConnection->getPDO();

            $update_sql = "access_token = VALUES(access_token), expires_at = VALUES(expires_at)";
            if ($refresh_token) {
                $update_sql .= ", refresh_token = VALUES(refresh_token)";
            }

            $sql = "INSERT INTO tenant_oauth_config 
                    (tenant_name, google_calendar_id, access_token, refresh_token, expires_at) 
                    VALUES (USER(), :cal_id, :access, :refresh, :expiry)
                    ON DUPLICATE KEY UPDATE $update_sql";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cal_id'  => $calendar_id,
                ':access'  => $access_token,
                ':refresh' => $refresh_token,
                ':expiry'  => $expires_at
            ]);

            // 4. Immediate API test to confirm the token works
            $api_url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendar_id) . "/events";
            $api_ch = curl_init();
            curl_setopt($api_ch, CURLOPT_URL, $api_url);
            curl_setopt($api_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($api_ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $access_token,
                'Accept: application/json'
            ]);
            $api_response = curl_exec($api_ch);
            $api_data = json_decode($api_response, true);
            curl_close($api_ch);

            echo "<h1>Connection successful!</h1>";
            echo "<p>Tenant tokens updated for the current connection.</p>";

            if (isset($api_data['items'])) {
                echo "<h3>API test successful:</h3>";
                echo "<p>Found " . count($api_data['items']) . " events in the calendar.</p>";
            }

        } catch (PDOException $e) {
            echo "DB error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Google error: " . htmlspecialchars(json_encode($data));
    }
} else {
    echo "No authorization code found in the URL.";
}
