
<?php
    include 'cerezaDAO.php';
    function doIt() {
        $requestBody = file_get_contents('php://input');
        $cerezaDAO = new CerezaDAO();
        $data= $cerezaDAO->updateFineEvent($requestBody);
        $response = array(
            'status' => 'ok',
            'receivedData' => $data
        );
    
        echo json_encode($data);
    }
    doIt();

?>