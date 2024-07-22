<?php
 // Set the content type to application/json
    header('Content-Type: application/json');

 // Retrieve JSON data from POST request
    $data = json_decode(file_get_contents('php://input'), true);

 // Process the data (for example, you could save it to a database)

 // Create a response array
    $response = array(
        'status' => 'success',
        'receivedData' => $data
    );

 // Send the response back as JSON
    echo json_encode($response);
?>
