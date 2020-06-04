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

    public function login($log_key, $password) {
        
        $response = array();
        $response["error"] = false;

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

        $logType = typeLogKey($log_key);
        if($logType == 0) {
            //email login
            $response = $this->emailLogin($log_key, $password);
        } else if($logType == 1) {
            //phone login
            $response["error"] = true;
        } else if($logType == 2) {
            //username login
            $response = $this->usernameLogin($log_key, $password);
        } else {
            $response["error"] = true;
        }
        return $response;

    }

    private function emailLogin($log_key, $password)
    {

        $response = array();

        $stmt = $this->conn->prepare("SELECT user_id, password, account_closed FROM users WHERE email = ?");
        $stmt->bind_param("s", $log_key);
        if (!$stmt->execute()) {
            $stmt->close();
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["error"] = "server error";
            return $response;
        }
        $dataRows = fetchData($stmt);
        $stmt->close();
        if (count($dataRows) == 1) {
            $userData = json_decode(json_encode($dataRows[0]));
            $user_id = $userData->user_id;
            $passwordN = $userData->password;
            $account_closed = $userData->account_closed;
            if ($user_id) {
                if (password_verify($password, $passwordN)) {
                    if($account_closed == 1) {
                        $response["error"] = true;
                        $response["errorID"] = 103;
                        $response["error"] = "account closed";
                        return $response;
                    } else {
                        unset($userData->{"password"});
                        $response["userData"] = $userData;
                        $response["type"] = 200;
                        return $response;
                    }
                } else {
                    $response["error"] = true;
                    $response["errorID"] = 104;
                    $response["error"] = "wrong password";
                    return $response;
                }
            } else {
                $response["error"] = true;
                $response["errorID"] = 105;
                $response["error"] = "invalid user_id";
                return $response;
            }
        } else {
            $response["error"] = true;
            $response["errorID"] = 106;
            $response["error"] = "email not found";
            return $response;
        }

    }

    private function usernameLogin($log_key, $password)
    {

        $response = array();

        $stmt = $this->conn->prepare("SELECT user_id, password, account_closed FROM users WHERE username = ?");
        $stmt->bind_param("s", $log_key);
        if (!$stmt->execute()) {
            $stmt->close();
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["error"] = "server error";
            return $response;
        }
        $dataRows = fetchData($stmt);
        $stmt->close();
        if (count($dataRows) == 1) {
            $userData = json_decode(json_encode($dataRows[0]));
            $user_id = $userData->user_id;
            $passwordN = $userData->password;
            $account_closed = $userData->account_closed;
            if ($user_id) {
                if (password_verify($password, $passwordN)) {
                    if($account_closed == 1) {
                        $response["error"] = true;
                        $response["errorID"] = 103;
                        $response["error"] = "account closed";
                        return $response;
                    } else {
                        unset($userData->{"password"});
                        $response["userData"] = $userData;
                        $response["type"] = 200;
                        return $response;
                    }
                } else {
                    $response["error"] = true;
                    $response["errorID"] = 104;
                    $response["error"] = "wrong password";
                    return $response;
                }
            } else {
                $response["error"] = true;
                $response["errorID"] = 105;
                $response["error"] = "invalid user_id";
                return $response;
            }
        } else {
            $response["error"] = true;
            $response["errorID"] = 106;
            $response["error"] = "username not found";
            return $response;
        }

    }

    public function getUserDetails($user_id, $my_uid)
    {
        
        $response = array();
        $response["error"] = false;

        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if(!$this->clearance_lvl < 8)
        {
            $response["error"] = true;
            return $response;
        }

        if($user_id == $my_uid)
        {
            $stmt = $this->conn->prepare("SELECT user_id, password, account_closed FROM users WHERE user_id = ?");
        }
        else
        {
            $stmt = $this->conn->prepare("SELECT user_id, password, account_closed FROM users WHERE user_id = ?");
        }
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            $stmt->close();
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["error"] = "server error";
            return $response;
        }
        $dataRows = fetchData($stmt);
        $stmt->close();
        if (count($dataRows) == 1) {
            $userData = json_decode(json_encode($dataRows[0]));
            $user_id = $userData->user_id;
            if ($user_id) {
                $response["userData"] = $userData;
                $response["type"] = 200;
                return $response;
            } else {
                $response["error"] = true;
                $response["errorID"] = 107;
                $response["error"] = "user not found";
                return $response;
            }
        } else {
            $response["error"] = true;
            $response["errorID"] = 107;
            $response["error"] = "user not found";
            return $response;
        }

    }

    public function signup($email, $username, $password)
    {
        
        $response = array();
        $response["error"] = false;

        $password = password_hash($password, PASSWORD_ARGON2I);

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

        if($user_id == $my_uid)
        {
            $stmt = $this->conn->prepare("SELECT user_id, password, account_closed FROM users WHERE user_id = ?");
        }
        else
        {
            $stmt = $this->conn->prepare("SELECT user_id, password, account_closed FROM users WHERE user_id = ?");
        }
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            $stmt->close();
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["error"] = "server error";
            return $response;
        }
        $dataRows = fetchData($stmt);
        $stmt->close();
        if (count($dataRows) == 1) {
            $userData = json_decode(json_encode($dataRows[0]));
            $user_id = $userData->user_id;
            if ($user_id) {
                $response["userData"] = $userData;
                $response["type"] = 200;
                return $response;
            } else {
                $response["error"] = true;
                $response["errorID"] = 107;
                $response["error"] = "user not found";
                return $response;
            }
        } else {
            $response["error"] = true;
            $response["errorID"] = 107;
            $response["error"] = "user not found";
            return $response;
        }

    }

    public function checkCredential($credential, $my_uid)
    {

        $response = array();
        $response["error"] = false;

        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if(!$this->clearance_lvl < 7)
        {
            $response["error"] = true;
            return $response;
        }

        $logType = typeLogKey($log_key);
        if($logType == 0) {
            //email
            $response = $this->isUsernameExist($credential);
        } else if($logType == 1) {
            //phone
            $response["error"] = true;
        } else if($logType == 2) {
            //username
            $response = $this->isEmailExist($credential);
        } else {
            $response["error"] = true;
        }
        return $response;

    }

    public function isUsernameExist($username)
    {
        if (typeLogKey($username) != 2) return true;
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }

    }

    public function isEmailExist($email)
    {
        if (typeLogKey($email) != 0) return true;
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? OR email_emergency = ?");
        $stmt->bind_param("ss", $email, $email);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }

    }

}

?>
