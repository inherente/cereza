<?php
require_once __DIR__ . '/../Database/DBConnect.php';

class TenantOAuthDAO {
    /**
     * Fetches the Google OAuth token + calendar id for the current tenant (MySQL USER()).
     *
     * @return array ['access_token' => string, 'google_calendar_id' => string]
     * @throws Exception When the tenant is not found or not configured.
     */
    public function fetchGoogleEventCollection(): array {
        $dbConnection = new DBConnect();
        $pdo = $dbConnection->getPDO();

        $stmt = $pdo->prepare(
            "SELECT access_token, google_calendar_id
             FROM tenant_oauth_config
             WHERE tenant_name = USER()
             LIMIT 1"
        );
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            throw new Exception("Tenant not found or not configured");
        }

        return [
            'access_token'       => $config['access_token'],
            'google_calendar_id' => $config['google_calendar_id'],
        ];
    }
}
?>
