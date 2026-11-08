<?php
require_once __DIR__ . '/Config.php';

class DBConnect {
    public $pdo;

    /**
     * Establishes a PDO connection using credentials from config/db.ini.
     * The connection's MySQL USER() identifies the active tenant.
     */
    public function __construct() {
        $config  = getDatabaseConfig();
        $host    = $config['host'];
        $db      = $config['database'];
        $user    = $config['username'];
        $pass    = $config['password'];
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Database connection failed. Check credentials and server status."]);
            exit;
        }
    }

    /**
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }
}
?>
