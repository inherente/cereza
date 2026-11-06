<?php

class DBConnect {
    // Database credentials
    private $host = 'localhost';
    private $db   = 'cereza'; // **CHANGE THIS TO YOUR ACTUAL DB NAME**
    private $user = 'cereza';
    private $pass = 'cereza'; 
    private $charset = 'utf8mb4';

    public $pdo;

    /**
     * Constructor attempts to establish the PDO connection.
     */
    public function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
             // In a real application, you'd log this error instead of echoing it immediately.
             http_response_code(500);
             echo json_encode(["error" => "Database connection failed. Check credentials and server status."]);
             // Terminate execution if the connection fails
             exit; 
        }
    }

    /**
     * Getter method to retrieve the PDO object.
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }
}

// Optional: To avoid immediate execution, you can remove the code below
// and just rely on the class structure.
?>