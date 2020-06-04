<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require_once '../db_handler/web.php';
require_once '../db_handler/unity.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->post('/unity/get/skills', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'api_password', 'user_id'));

    $api_key = $app->request->post('api_key');
    $api_password = $app->request->post('api_password');
    $user_id = $app->request->post('user_id');

    $response = array();
    $db = new DbHandlerUnity();
    $db->initializeAPI($api_key, $api_password);
    if($db->validSession) {
        $response["error"] = false;
        $skills = array();
        $skill = array();
        $skill["skill_id"] = 2332;
        $skill["name"] = "Name?";
        $skill["class_type"] = 2;
        $skill["row"] = 2;
        $skills[] = $skill;
        $skills[] = $skill;
        $skills[] = $skill;
        $skills[] = $skill;
        $response["skills"] = $skills;
        echoResponse(200, $response);
    } else {
        $response["error"] = true;
        echoResponse(101, $response);
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