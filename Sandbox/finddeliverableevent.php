<?php
header("Content-Type: application/json");
 // Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

 // Read the JSON input
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);
 // Function to load database configuration from an .ini file
function getDatabaseConfig($file = "db.ini") {
    if (!file_exists($file)) {
        die(json_encode(["error" => "Configuration file not found."]));
    }
    return parse_ini_file($file, true)["database"];
}

function getAvailableSlotCollection ($conn) {
    // Check connection
       if (!$conn) {
           http_response_code(500);
           return json_encode(["error" => "Database connection failed"]);
           exit;
       }
   
    // Define input parameters
       $duration = 60;                  // appointment length in minutes
       $workStart = '09:00:00';        // work start time
       $workEnd = '17:00:00';          // work end time
       $optionNumber=3;
       $currentIteration=0;
       $textTotal='';
   
     // 📞 Prepare and execute stored procedure
        $stmt = $conn->prepare("CALL GetNextAvailableSlotCollection(?, ?, ?)");
        $stmt->bind_param("iss", $duration, $workStart, $workEnd);
        $stmt->execute();

     // 📦 Get result set
        $result = $stmt->get_result();

     // 📋 Process result set
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            if($currentIteration<=$optionNumber) {
                $currentIteration++;
                $textTotal .= "/" . str_replace(' ','|',$row['slot_start'] ) . "\n";
            }
            $slots[] = [
                'slot_start' => $row['slot_start'],
                'slot_end' => $row['slot_end']
            ];
        }

     // 🧾 Output or use the result
        return json_encode([
            "text" => $textTotal,
            "detail" => $slots
        ]);

}

 // Load database configuration
$config = getDatabaseConfig();
 // Connect to the database
$conn = new mysqli($config["host"], $config["username"], $config["password"], $config["database"]);
$result= getAvailableSlotCollection($conn);
if($result) {
 // echo $result;
    echo json_encode(
        [
            "result" => json_decode($result),
            "success" => true
        ]
    );
} else {
    echo json_encode([
        "success" => "true",
        "message" => "No results found."
    ]);    
}
?>