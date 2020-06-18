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
            $response["data"] = $db->getUserDetails($response["userData"]->{"user_id"}, $response["userData"]->{"user_id"});
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
            echoResponse(145, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/user/details', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'uuid', 'my_uid'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $uuid = $app->request->post('uuid');
    $my_uid = $app->request->post('my_uid');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->getUserDetails($uuid, $my_uid);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            echoResponse(145, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/ig/link', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'my_uid', 'username', 'igid', 'password', 'profile_picture', 'is_private'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $my_uid = $app->request->post('my_uid');
    $username = $app->request->post('username');
    $igid = $app->request->post('igid');
    $password = $app->request->post('password');
    $profile_picture = $app->request->post('profile_picture');
    $is_private = $app->request->post('is_private');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->linkIGAccount($my_uid, $username, $igid, $password, $profile_picture, $is_private);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            echoResponse(178, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/ig/followers/count', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'my_uid', 'username'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $my_uid = $app->request->post('my_uid');
    $username = $app->request->post('username');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        echoResponse(178, $response);
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/ig/posts/details', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'my_uid', 'ig_userid'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $my_uid = $app->request->post('my_uid');
    $ig_userid = $app->request->post('ig_userid');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $urlPart1 = 'https://www.instagram.com/graphql/query/?query_id=17888483320059182&variables=%7B%22id%22:%22';
        $urlPart2 = '%22,%22first%22:20000,%22after%22:null%7D';
        $jsonData = file_get_contents($urlPart1 . $ig_userid . $urlPart2);
        $jsonData = json_decode($jsonData);

        $response["has_next_page"] = $jsonData->data->user->edge_owner_to_timeline_media->page_info->has_next_page;
        $response["end_cursor"] = $jsonData->data->user->edge_owner_to_timeline_media->page_info->end_cursor;

        $posts = array();
        $userPosts = $jsonData->data->user->edge_owner_to_timeline_media->edges;
        foreach ($userPosts as $value)
        {
            $post = array();
            $post["is_video"] = json_encode($value->node->is_video);
            $post["id"] = $value->node->id;
            $post["likes"] = $value->node->edge_media_preview_like->count;
            $post["img150x150"] = $value->node->thumbnail_resources[0]->src;
            $post["img240x240"] = $value->node->thumbnail_resources[1]->src;
            $post["img480x480"] = $value->node->thumbnail_resources[2]->src;
            $post["img640x640"] = $value->node->thumbnail_resources[3]->src;
            $posts[] = $post;
        }
        $response["posts"] = $posts;
        return echoResponse(178, $response);
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/user/order', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'my_uid', 'userID', 'order', 'type', 'imgPreview', 'postID'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $my_uid = $app->request->post('my_uid');
    $userID = $app->request->post('userID');
    $order = $app->request->post('order');
    $type = $app->request->post('type');
    $imgPreview = $app->request->post('imgPreview');
    $postID = $app->request->post('postID');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->makeAnOrder($my_uid, $userID, $order, $type, $imgPreview, $postID);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            echoResponse(178, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/orders/retrieve', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'my_uid', 'type'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $my_uid = $app->request->post('my_uid');
    $type = $app->request->post('type');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->retrieveOrders($my_uid, $type);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            echoResponse(178, $response);
        }
    } else {
        $response["error"] = true;
        $response["errorID"] = 511;
        $response["errorContent"] = "invalid api";
        echoResponse(511, $response);
    }

});

$app->post('/mobile/orders/interact', function() use ($app) {
    // check for required params

    verifyRequiredParams(array('api_key', 'secret_key', 'my_uid', 'ig_account_id', 'order_id', 'post_id'));

    $api_key = $app->request->post('api_key');
    $secret_key = $app->request->post('secret_key');
    $my_uid = $app->request->post('my_uid');
    $type = $app->request->post('type');

    $response = array();
    $db = new DbHandlerMobile();
    $db->initializeAPI($api_key, $secret_key);
    if($db->validSession) {
        $response = $db->retrieveOrders($my_uid, $type);
        if($response["error"])
        {
            echoResponse(511, $response);
        }
        else
        {
            echoResponse(178, $response);
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

    echo json_encode($response, JSON_UNESCAPED_SLASHES);
}

$app->run();
?>