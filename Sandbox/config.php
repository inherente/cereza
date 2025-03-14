<?php
// Function to load database configuration from an .ini file
function getDatabaseConfig($file = "db.ini") {
    if (!file_exists($file)) {
        die(json_encode(["error" => "Configuration file not found."]));
    }
    return parse_ini_file($file, true)["database"];
}

// Load database configuration
$config = getDatabaseConfig();

// Connect to the database
$conn = new mysqli($config["host"], $config["username"], $config["password"], $config["database"]);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Get the 'name' parameter from the request
$name = isset($_GET["name"]) ? $conn->real_escape_string($_GET["name"]) : null;

if (!$name) {
    die(json_encode(["error" => "Missing 'name' parameter"]));
}

// Query to fetch the plugin configuration
$sql = "SELECT Id, NAME, DESCRIPTION, REFRESH_PERIOD, STATE, STATUS, CREATED_DATE, CREATED_BY 
        FROM PLUGIN_CONFIGURATION 
        WHERE NAME = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc(), JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "No plugin found with the given name"]);
}

// Close resources
$stmt->close();
$conn->close();
?>