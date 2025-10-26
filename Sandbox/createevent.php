<?php
header("Content-Type: application/json");
 // Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

 // Read the JSON input
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

?>