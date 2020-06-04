<?php

class DbHandlerMobile {

    private $conn;
    private $validSession = false;
    private $clearance_lvl = 0;
    
    // if(!$this->validSession)
    // {
    //     $response["error"] = true;
    //     return $response;
    // }

    // if(!$this->clearance_lvl < 9)
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

    public function initializeAPI($api_key, $secret_key)
    {
        
        $sqlQuery = "SELECT clearance_lvl FROM api_clients WHERE api_key = ? AND secret_key = ?";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bind_param("ss", $api_key, $secret_key);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 1) {
                $this->validSession = true;
                $this->clearance_lvl = $dataRows[0]["clearance_lvl"];
                return $this->clearance_lvl;
            } else {
                return 0;
            }
        } else {
            return 0;
        }

    }

    public function emailLogin($log_key, $password) {
        
        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if(!$this->clearance_lvl < 9)
        {
            $response["error"] = true;
            return $response;
        }

        $response = array();
        $stmt = $this->conn->prepare("SELECT user_id, password, account_closed, verified_email, authentication_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $log_key);
        if (!$stmt->execute()) {
            $stmt->close();
            $response["type"] = 101;
            return $response;
        }
        $dataRows = fetchData($stmt);
        $stmt->close();
        if (count($dataRows) == 1) {
            $userData = json_decode(json_encode($dataRows[0]));
            $user_id = $userData->user_id;
            $passwordN = $userData->password;
            $account_closed = $userData->account_closed;
            $verified_email = $userData->verified_email;
            $authentication_type = $userData->authentication_type;
            if ($user_id) {
                if (password_verify($password, $passwordN)) {
                    if($account_closed == 1) {
                        $response["type"] = 102;
                        return $response;
                    } else if($verified_email == 0) {
                        $response["type"] = 103;
                        return $response;
                    } else if($authentication_type == 1) {
                        $response["type"] = 104;
                        return $response;
                    } else if($authentication_type == 0) {
                        unset($userData->{"password"});
                        $response["userData"] = $userData;
                        $response["type"] = 200;
                        return $response;
                    }
                } else {
                    $response["type"] = 105;
                    return $response;
                }
            } else {
                $response["type"] = 106;
                return $response;
            }
        } else {
            $response["type"] = 106;
            return $response;
        }

    }

}

?>
