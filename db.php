<?php
 // Define database connection parameters
    $servername = "localhost"; // Change this to your server name if it's different
    $username = "apple"; // Change this to your MySQL username
    $password = "cereza"; // Change this to your MySQL password
    $dbname = "cereza"; // Change this to your database name

 // Create a connection to the MySQL database
    $conn = mysqli_connect($servername, $username, $password, $dbname);

 // Check the connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    echo "<p>Connected successfully</p>";

 // Close the connection
    mysqli_close($conn);
?>
