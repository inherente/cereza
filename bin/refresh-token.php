<?php
// CLI / cron only: refuse to run when invoked through a public URL.
if (isset($_SERVER['HTTP_HOST'])) {
    http_response_code(403);
    die("Access denied: this script can only be run internally.");
}

require_once __DIR__ . '/../source/Database/DBConnect.php';
require_once __DIR__ . '/../source/Google/Config.php';

$google         = getGoogleConfig();
$client_id      = $google['client_id'];
$client_secret  = $google['client_secret'];

try {
    $dbConnection = new DBConnect();
    $pdo = $dbConnection->getPDO();

    // 1. Find tokens expiring in the next 5 minutes so we refresh proactively
    $buffer_time = time() + 300;
    $stmt = $pdo->prepare(
        "SELECT tenant_name, refresh_token
         FROM tenant_oauth_config
         WHERE expires_at <= :buffer AND refresh_token IS NOT NULL"
    );
    $stmt->execute([':buffer' => $buffer_time]);
    $tenants_to_refresh = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tenants_to_refresh as $tenant) {
        // 2. Request a new access_token from Google using the refresh_token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $tenant['refresh_token'],
            'grant_type'    => 'refresh_token'
        ]));

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($response['access_token'])) {
            // 3. Update the DB with the new token and new expiry
            $new_expiry = time() + $response['expires_in'];
            $update = $pdo->prepare(
                "UPDATE tenant_oauth_config
                 SET access_token = :access, expires_at = :expiry
                 WHERE tenant_name = :name"
            );
            $update->execute([
                ':access' => $response['access_token'],
                ':expiry'  => $new_expiry,
                ':name'    => $tenant['tenant_name']
            ]);
            echo "Token updated for: " . $tenant['tenant_name'] . "\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
