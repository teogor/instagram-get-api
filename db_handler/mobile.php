<?php

class DbHandlerMobile {

    private $conn;
    private $validSession = false;
    private $clearance_level = 0;
    
    // if(!$this->validSession)
    // {
    //     $response["error"] = true;
    //     return $response;
    // }

    function __construct() {
        $path = $_SERVER['DOCUMENT_ROOT'];
        require_once $path . '/include/db_connect.php';
        require_once $path . '/libs/Utils/utils.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function initializeAPI($api_key, $api_password)
    {
        
        $sqlQuery = "SELECT clearance_level FROM api_clients WHERE api_key = ? AND api_password = ?";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bind_param("ss", $api_key, $api_password);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 1) {
                $this->validSession = true;
                $this->clearance_level = $dataRows[0]["clearance_level"];
                return $this->clearance_level;
            } else {
                return 0;
            }
        } else {
            return 0;
        }

    }

}

?>
