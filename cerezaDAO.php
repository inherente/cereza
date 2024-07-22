<?php
include 'converter.php';
class CerezaDAO {
    public $conn;
 // $failedData = "{        \"status\": \"ok\"    }";
    public function __construct() {
        error_log("Construct (ing)");
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $config = parse_ini_file('to-do.ini');
        $this->conn=$this->buildConn($config);
        if(isset($this->conn)) {
            error_log("Connection prepared");
        } else {
            error_log("WTF: conn not found");
        }
        error_log("Construct (done)");
    }

    public function buildConn($config) {
        error_log("Building conn");
        $servername = $config['dbhost'];  
        $username = $config['user'];
        $password = $config['password']; 
        $dbname = $config['dbname'];
        return new mysqli($servername, $username, $password, $dbname);

    }

    function __destruct() {
        $this->conn->close();
    }

    function nothing () {
        return "null";
    }

    function updateEvent($content) {
        $sql = "INSERT INTO EVENT_TRACK (name, content) VALUES('name', '" . $content . "' )";
        if ($this->$conn->connect_error) {
               echo "{        \"status\": \"not-ok\"    }"; 
               die("Connection failed: " . $this->$conn->connect_error);
        } 
        $result = $this->$conn->query($sql);
    }

    public function updateFineEvent ($jsonString) {
        if(isset($this->conn)) {
            error_log("Conn Done");
        } else {
            error_log("ConnNotThere");
        }
        $DEBUG_MODE = true;
        error_log("Update (ing) fine event");
        $data = json_decode($jsonString, true);
        error_log("Lenght of built data is ( " . count($data) . ")");
        if (json_last_error() === JSON_ERROR_NONE) {
         // Access data
            $kind = $data['kind'] ; //. "\n"
            $eTag = $data['etag'];// . "\n"
            $eventID = $data['id'];// . "\n"
            $status = $data['status'] ;//. "\n"
            $htmlLink = $data['htmlLink'];// . "\n"
            $created = Converter::convertToUTC($data['created'] );// "\n" convertISO8601ToMySQLDATETIME
            $updated = Converter::convertToUTC( $data['updated']) ;//. "\n" convertISO8601ToMySQLDATETIME
            $summary = $data['summary'];// . "\n"
            $creatorEmail = $data['creator']['email'];// . "\n"
         // $creatorDisplayName = $data['creator']['displayName'];// . "\n"
            $organizerEmail= $data['organizer']['email'] ;//. "\n"
         // $organizerDisplayName = $data['organizer']['displayName'];// . "\n"
            $startDate = Converter::convertToUTC($data['start']['dateTime']);// . "\n"
            $endDate = Converter::convertToUTC($data['end']['dateTime']);// . "\n"
         // $recurringEventID = $data['recurringEventId'] ;//. "\n"
         // $originalStartDate= $data['originalStartTime']['date'];// . "\n"
            $transparency = $data['transparency'] ;//. "\n"
            $iCalUID = $data['iCalUID'] ;//. "\n"
            $sequence= $data['sequence'];// . "\n"
            $eventType= $data['eventType'] ;//. "\n"
        } else {
            echo "Failed to decode JSON: " . json_last_error_msg();
        }
        if (json_last_error() === JSON_ERROR_NONE && $DEBUG_MODE === true ) {
            // Access data
            echo "Kind: " . $kind . "\n";
            echo "ETag: " . $eTag . "\n";
            echo "Event ID: " . $eventID . "\n";
            echo "Status: " . $status . "\n";
            echo "HTML Link: " . $htmlLink . "\n";
            echo "Created: " . $created . "\n";
            echo "Updated: " . $updated . "\n";
            echo "Summary: " . $summary . "\n";
            echo "Creator Email: " . $creatorEmail . "\n";
         // echo "Creator Display Name: " . $creatorDisplayName . "\n";
            echo "Organizer Email: " . $organizerEmail . "\n";
         // echo "Organizer Display Name: " . $organizerDisplayName . "\n";
            echo "Start Date: " . $startDate . "\n";
            echo "End Date: " . $endDate . "\n";
         // echo "Recurring Event ID: " . $recurringEventId . "\n";
         // echo "Original Start Date: " . $originalStartDate . "\n";
            echo "Transparency: " . $transparency . "\n";
            echo "iCalUID: " . $iCalUID . "\n";
            echo "Sequence: " . $sequence . "\n";
            echo "Event Type: " . $eventType . "\n";
        } else {
            echo "Failed to decode JSON: " . json_last_error_msg();
        }
        $sql = "INSERT INTO `FINE_EVENT` (calendar_id, created_date, creator_id, end_date, kind, organizer_id, start_date, status, updated_date, summary)
VALUES ('" . $eventID . "', '" . $created . "', '" . 0 . "', '" . ($endDate) . "', '" . $kind . "', '" . 0 . "', '" . ($startDate) . "', '" . $status . "', '" . $updated . "' , ' " . $summary . "') ON DUPLICATE KEY UPDATE calendar_id = VALUES(calendar_id),created_date = VALUES(created_date),creator_id = VALUES(creator_id),end_date = VALUES(end_date),kind = VALUES(kind),organizer_id = VALUES(organizer_id),start_date = VALUES(start_date),status = VALUES(status),updated_date = VALUES(updated_date),summary = VALUES(summary)";

        error_log("SQL Statement : " . $sql . ".");
        if(isset($this->conn)) {
            error_log("Connection ready");
        } else {
            $this->conn=$this->buildConn(parse_ini_file('to-do.ini'));
        }
        $result = $this->conn->query($sql);

        if ($this->conn->connect_error) {
            error_log('connect error');
            echo "{        \"status\": \"not-ok\"    }"; 
            die("Connection failed: " . $this->conn->connect_error);
        } 
        error_log('[' . $this->conn->affected_rows . '] Affected Row(s)');

        error_log('Execution Completed');
        error_log($result);
        if (isset($result) && $result >0) {
            error_log('Added. Check for duplication');
            $this->moveEventToBeDeteled($eventID);
         // Move the other one   
        } else {
            error_log('Nothing added');
        }
        return $result;
    } 

    function moveEventToBeDeteled($eventID) {
        $sql = "SELECT COUNT(*) FROM `FINE_EVENT` WHERE calendar_id= '" . $eventID . "'";

        error_log("SQL Statement : " . $sql . ".");
        if(isset($this->conn)) {
            error_log("Connection ready");
        } else {
            $this->conn=$this->buildConn(parse_ini_file('to-do.ini'));
        }
        $result = $this->conn->query($sql);
        error_log("[" . $result->num_rows . "] found record(s)");

        if ($this->conn->connect_error) {
            error_log('connect error');
            echo "{        \"status\": \"not-ok\"    }"; 
            die("Connection failed: " . $this->conn->connect_error);
        } 
        error_log("[" . $this->conn->affected_rows  . "] affected record(s)");

        if(isset( $result) && $result->num_rows >= 1 ) {
            $sql = "INSERT INTO FINE_EVENT_TRANSITION (calendar_id) VALUES ('" . $eventID . "')";
            error_log("SQL Statement : " . $sql . ".");
            if(isset($this->conn)) {
                error_log("Connection ready");
            } else {
                $this->conn=$this->buildConn(parse_ini_file('to-do.ini'));
            }
            $result = $this->conn->query($sql);
    
        }

    }

    function queueEvent($content) {
        $field = " VALUES";
        if ($this->$conn->connect_error) {
         // echo "<h2>Connected Failed</h2>"; 
            die("Connection failed: " . $this->$conn->connect_error);
        } 
     // echo "<br>";
     // echo "<h2>Connected Successfully</h2>";
        $field .= " ('1' , user() , `$content` , now() , now() );";
        $sql = "INSERT INTO EVENT_HOOK (entry_id, content, created_by, created_dt, updated_dt) VALUES ('110', '" . $content . "' , user(), now(), now()) ;";
        $result = $this->$conn->query($sql);
     // echo $result->num_rows . " Returned row(s) ";
    }

}

?>