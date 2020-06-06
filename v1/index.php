<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require_once '../db_handler/mobile.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->post('/mobile/login', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'log_key', 'password'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $log_key = $app->request->post('log_key');
    $password = $app->request->post('password');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->login($log_key, $password);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            $response["data"] = $db->getUserDetails($response["userData"]["user_id"], $response["userData"]["user_id"]);
            echoResponse(200, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/signup', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'email', 'username', 'password'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $email = $app->request->post('email');
    $username = $app->request->post('username');
    $password = $app->request->post('password');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->signup($email, $username, $password);
        $response["uuid"] = $response["uuid"];
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            $response["data"] = $db->getUserDetails($response["uuid"], $response["uuid"]);
            echoResponse(200, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/credentials/check', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'credential', 'my_uid'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $credential = $app->request->post('credential');
    $my_uid = $app->request->post('my_uid');

    $response = array();
    $response["api_key"] = $api_key;
    $response["secret_key"] = $secret_key;

    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->checkCredential($credential, $my_uid);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            echoResponse(200, $response);
        }

    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>