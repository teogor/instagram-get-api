<?php

class DbHandlerMobile {

    private $conn;
    public $validSession = false;
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
        
        $response = array();
        $response["error"] = false;

        $sqlQuery = "SELECT clearance_lvl FROM apis WHERE api_key = ? AND secret_key = ?";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bind_param("ss", $api_key, $secret_key);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 1) {
                $this->validSession = true;
                $this->clearance_lvl = $dataRows[0]["clearance_lvl"];
            } else {
                $response["error"] = true;
            }
        } else {
            $response["error"] = true;
        }

        return $response;

    }

    public function login($log_key, $password) {
        
        $response = array();
        $response["error"] = false;

        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if($this->clearance_lvl < 9)
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

        $stmt = $this->conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $log_key);
        if (!$stmt->execute()) {
            $stmt->close();
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error";
            return $response;
        }
        $dataRows = fetchData($stmt);
        $stmt->close();
        if (count($dataRows) == 1) {
            $userData = json_decode(json_encode($dataRows[0]));
            $user_id = $userData->user_id;
            $passwordN = $userData->password;
            if ($user_id) {
                if (password_verify($password, $passwordN)) {
                    unset($userData->{"password"});
                    $response["error"] = false;
                    $response["userData"] = $userData;
                    $response["type"] = 200;
                    return $response;
                } else {
                    $response["error"] = true;
                    $response["errorID"] = 104;
                    $response["errorContent"] = "wrong password";
                    return $response;
                }
            } else {
                $response["error"] = true;
                $response["errorID"] = 105;
                $response["errorContent"] = "invalid user_id";
                return $response;
            }
        } else {
            $response["error"] = true;
            $response["errorID"] = 106;
            $response["errorContent"] = "email not found";
            return $response;
        }

    }

    private function usernameLogin($log_key, $password)
    {

        $response = array();

        $sql = "SELECT user_id, password FROM users WHERE username = ?";

        if (!($stmt = $this->conn->prepare($sql))) {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error" + $stmt->error;
            return $response;
        }
        if (!$stmt->bind_param("s", $log_key)) {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error";
            $stmt->close();
            return $response;
        }
        if (!$stmt->execute()) {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error";
            $stmt->close();
            return $response;
        } else {
            $dataRows = fetchData($stmt);
            $stmt->close();
            if (count($dataRows) == 1) {
                $userData = json_decode(json_encode($dataRows[0]));
                $user_id = $userData->user_id;
                $passwordN = $userData->password;
                if ($user_id) {
                    if (password_verify($password, $passwordN)) {
                        unset($userData->{"password"});
                        $response["userData"] = $userData;
                        $response["type"] = 200;
                        $response["error"] = false;
                        return $response;
                    } else {
                        $response["error"] = true;
                        $response["errorID"] = 104;
                        $response["errorContent"] = "wrong password";
                        return $response;
                    }
                } else {
                    $response["error"] = true;
                    $response["errorID"] = 105;
                    $response["errorContent"] = "invalid user_id";
                    return $response;
                }
            } else {
                $response["error"] = true;
                $response["errorID"] = 106;
                $response["errorContent"] = "username not found";
                return $response;
            }
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

        if($this->clearance_lvl < 8)
        {
            $response["error"] = true;
            return $response;
        }

        if($user_id == $my_uid)
        {
            $sql = "SELECT user_id, email, username, password, coins FROM users WHERE user_id = ?";
        }
        else
        {
            $sql = "SELECT user_id, username, coins FROM users WHERE user_id = ?";
        }

        if (!($stmt = $this->conn->prepare($sql))) {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error";
            return $response;
        }
        if (!$stmt->bind_param("i", $user_id)) {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error";
            $stmt->close();
            return $response;
        }
        if (!$stmt->execute()) {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = "server error";
            $stmt->close();
            return $response;
        } else {
            $dataRows = fetchData($stmt);
            $stmt->close();
            if (count($dataRows) == 1) {
                $userData = json_decode(json_encode($dataRows[0]));
                $user_id = $userData->user_id;
                if ($user_id) {
                    $response["userData"] = $userData;
                    $response["type"] = 200;
                    if($user_id==$my_uid)
                    {
                        $stmt = $this->conn->prepare("SELECT LA.ig_account_id, LA.username, LA.profile_picture, LA.password FROM linked_accounts LA
                                WHERE LA.user_id=? AND LA.unlinked=0");
                        $stmt->bind_param("i", $my_uid);
                        if ($stmt->execute()) {
                            $response["userData"]->{"ig_accounts"} = fetchData($stmt);
                        }                        
                    }
                    return $response;
                } else {
                    $response["error"] = true;
                    $response["errorID"] = 107;
                    $response["errorContent"] = "user not found";
                    return $response;
                }
            } else {
                $response["error"] = true;
                $response["errorID"] = 107;
                $response["errorContent"] = "user not found";
                return $response;
            }
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
            $response["errorContent"] = "invalid sesion";
            return $response;
        }

        if($this->clearance_lvl < 9)
        {
            $response["error"] = true;
            $response["errorContent"] = "you don't have the right to access this function 'sign_up'";
            return $response;
        }
                
        $username = strtolower($username);
        $usernameExist = $this->isUsernameExist($username, 0);
        $emailExist = $this->isEmailExist($email, 0);
        if(!$usernameExist && !$emailExist) {
            $stmt = $this->conn->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $username, $password);
            if ($stmt->execute()) {
                $insert_id = $stmt->insert_id;
                $stmt->fetch();
                $stmt->close();
                $response["type"] = 200;
                $response["uuid"] = $insert_id;
            } else {
                $response["errorID"] = 108;
                $response["error"] = true;
                $response["errorContent"] = "server error";
            }
        } else {
            if($usernameExist && $emailExist) {
                $response["errorID"] = 108;
                $response["error"] = true;
                $response["errorContent"] = "username and email already exists";
            }
            else if($usernameExist) {
                $response["errorID"] = 108;
                $response["error"] = true;
                $response["errorContent"] = "username already exists";
            }
            else if($emailExist) {
                $response["errorID"] = 108;
                $response["error"] = true;
                $response["errorContent"] = "email already exists";
            }
        }
        return $response;

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

        if($this->clearance_lvl < 7)
        {
            $response["error"] = true;
            return $response;
        }

        $logType = typeLogKey($credential);
        if($logType == 0) {
            //email
            $exists = $this->isEmailExist($credential, $my_uid);
            if($exists)
            {
                $response["error"] = true;
                $response["errorID"] = 108;
                $response["errorContent"] = "email already exists";
            }
            else
            {
                $response["onSuccess"] = true;
            }
        } else if($logType == 1) {
            //phone
            $response["error"] = true;
        } else if($logType == 2) {
            //username
            $exists = $this->isUsernameExist($credential, $my_uid);
            if(strlen($credential)<3)
            {
                $response["error"] = true;
                $response["errorID"] = 178;
                $response["errorContent"] = "username must be at least 3 characters";
            } else if($exists)
            {
                $response["error"] = true;
                $response["errorID"] = 108;
                $response["errorContent"] = "username already exists";
            }
            else
            {
                $response["onSuccess"] = true;
            }
        } else {
            $response["error"] = true;
        }
        return $response;

    }

    public function isUsernameExist($username, $my_uid)
    {
        if (typeLogKey($username) != 2) return true;
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE username = ? AND NOT user_id = ?");
        $stmt->bind_param("si", $username, $my_uid);
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

    public function isEmailExist($email, $my_uid)
    {
        if (typeLogKey($email) != 0) return true;
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND NOT user_id = ?");
        $stmt->bind_param("si", $email, $my_uid);
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

    public function linkIGAccount($my_uid, $username, $igid, $password, $profile_picture, $is_private)
    {
        
        $response = array();
        $response["error"] = false;

        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if($this->clearance_lvl < 8)
        {
            $response["error"] = true;
            return $response;
        }

        $stmt = $this->conn->prepare("SELECT LA.linked_account_id FROM linked_accounts LA WHERE LA.user_id=? AND LA.ig_account_id=? AND LA.unlinked=0");
        $stmt->bind_param("ii", $my_uid, $igid);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 0) {
                $sql = "INSERT INTO linked_accounts
                    (user_id, ig_account_id, username, private, password, profile_picture)
                    VALUES (?, ?, ?, ?, ?, ?)";
        
                if (!($stmt = $this->conn->prepare($sql))) {
                    $response["error"] = true;
                    $response["errorID"] = 102;
                    $response["errorContent"] = $stmt->error;
                    return $response;
                }
                if (!$stmt->bind_param("iisiss", $my_uid, $igid, $username, $is_private, $password, $profile_picture)) {
                    $response["error"] = true;
                    $response["errorID"] = 102;
                    $response["errorContent"] = $stmt->error;
                    $stmt->close();
                    return $response;
                }
                if (!$stmt->execute()) {
                    $response["error"] = true;
                    $response["errorID"] = 102;
                    $response["errorContent"] = $stmt->error;
                    $stmt->close();
                    return $response;
                } else {
                    $stmt->close();
                    $response["error"] = false;
                    return $response;
                }
            } else {
                $response["error"] = true;
                $response["errorID"] = 102;
                $response["errorContent"] = "server error";
                $stmt->close();
                return $response;
            }
        } else {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = $stmt->error;
            $stmt->close();
            return $response;
        }

    }

    public function makeAnOrder($my_uid, $userID, $order, $type, $imgPreview, $postID)
    {
        
        $response = array();
        $response["error"] = false;

        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if($this->clearance_lvl < 9)
        {
            $response["error"] = true;
            return $response;
        }

        if($type == 0)
        {
            // likes
            if($order == 0) {
                $coins = 20;
                $target = 10;
            } else if($order == 1) {
                $coins = 50;
                $target = 25;
            } else if($order == 2) {
                $coins = 100;
                $target = 50;
            } else if($order == 3) {
                $coins = 200;
                $target = 100;
            } else if($order == 4) {
                $coins = 500;
                $target = 250;
            } else if($order == 5) {
                $coins = 1000;
                $target = 500;
            } else if($order == 6) {
                $coins = 2000;
                $target = 1000;
            } else if($order == 7) {
                $coins = 4000;
                $target = 2000;
            } else if($order == 8) {
                $coins = 10000;
                $target = 5000;
            }
        } else if($type == 1)
        {
            // followers
            if($order == 0) {
                $coins = 100;
                $target = 10;
            } else if($order == 1) {
                $coins = 250;
                $target = 25;
            } else if($order == 2) {
                $coins = 500;
                $target = 50;
            } else if($order == 3) {
                $coins = 1000;
                $target = 100;
            } else if($order == 4) {
                $coins = 2000;
                $target = 200;
            } else if($order == 5) {
                $coins = 3000;
                $target = 300;
            } else if($order == 6) {
                $coins = 5000;
                $target = 500;
            } else if($order == 7) {
                $coins = 10000;
                $target = 1000;
            }
        }

        if($type == 0) {
            $response["type"] = "likes";
        } else if($type == 1) {
            $response["type"] = "followers";
        }
        $response["coins"] = $coins;
        $response["target"] = $target;

        $stmt = $this->conn->prepare("SELECT coins FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $my_uid);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            if (count($dataRows) == 0) {
                $response["error"] = true;
                return $response;
            } else {
                $userCoins = $dataRows[0]["coins"];
                if($userCoins >= $coins)
                {
                    $coinsAfterPurchase = $userCoins - $coins;
                    $stmt = $this->conn->prepare("UPDATE users SET coins=? WHERE user_id=?");
                    $stmt->bind_param("ii", $coinsAfterPurchase, $my_uid);
                    if($stmt->execute()) {
                        $response["purchased"] = true;
                        $response["coinsAfterPurchase"] = $coinsAfterPurchase;
                        return $response;
                    } else {
                        $response["purchased"] = false;
                        $response["error"] = true;
                        return $response;
                    }
                } else
                {
                    $response["purchased"] = false;
                    return $response;
                }
            }
        } else {
            $response["error"] = true;
            return $response;
        }

    }

    public function retrieveOrders($my_uid, $type)
    {
        
        $response = array();
        $response["error"] = false;

        if(!$this->validSession)
        {
            $response["error"] = true;
            return $response;
        }

        if($this->clearance_lvl < 7)
        {
            $response["error"] = true;
            return $response;
        }

        $stmt = $this->conn->prepare("SELECT LA.linked_account_id FROM linked_accounts LA WHERE LA.user_id=? AND LA.ig_account_id=? AND LA.unlinked=0");
        $stmt->bind_param("ii", $my_uid, $igid);
        if ($stmt->execute()) {
            $dataRows = fetchData($stmt);
            
        } else {
            $response["error"] = true;
            $response["errorID"] = 102;
            $response["errorContent"] = $stmt->error;
            $stmt->close();
            return $response;
        }

    }

}

?>
