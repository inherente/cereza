
<!DOCTYPE html>
<html>
<body>
<?php
    include 'cerezaDAO.php';
    function doIt() {
        $cerezaDAO = new CerezaDAO();
        $cerezaDAO->updateEvent();
        $response = array(
            'status' => 'ok',
            'receivedData' => $data
        );
    
        echo json_encode($response);
    }
    doIt();

?>
</body>
</html>