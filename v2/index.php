<?php
require_once('utils/autoloader.php');

//by default, all requests are invalid
$valid = false;
//by default, nobody has access granted
$access = false;
//get the request method
$method = $_SERVER['REQUEST_METHOD'];
//Get the raw request uri
$rawPath = $_SERVER['REQUEST_URI'];
//define request body
$json = array();

#start authentication

if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'This page requires authenthication';
        exit;
    }else {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        $response = Security::checkLogin($username, $password);

        if($response->success){
            $userid = $response->uid;
            $access = true;
        }else{
            header('HTTP/1.0 403 Unauthorized');
            echo 'Invalid credentials';
            die();
        }
}



//transform it into an array and unset unwanted routes

$path = explode('/', $rawPath);
unset(
    $path['0'],
    $path['1'],
    $path['2'],
    $path['3']
);
//reset array indexes
$path = array_values($path);
// var_dump($path);die();
if($path[0] == 'login'){
    echo  json_encode(Security::checkLogin($username, $password));
    die();
}

#set arrays of accepted methods and paths
$accepted_paths = array(
    'user',
    'company'
    );
$accepted_methods = array(
    'GET',
    'POST',
    'DELETE',
    'PUT'
    );


#TO DO request body manipulation
if(isset($_POST['json'])){
    $json = json_decode($_POST['json']);
}elseif($method == 'PUT'){
    $json = urldecode(file_get_contents("php://input"));
    $json = str_replace("json=", '', $json);
    $json = json_decode($json);
}

if(in_array($method, $accepted_methods) &&
   in_array($path['0'], $accepted_paths)){
    $valid = true;
}

if($access){
    if($valid){

        $class = new $path['0']();
        unset($path['0']);
        $path = array_values($path);

        $response = $class->route($method, $path, $json, $userid);
        
    }else{
        header('HTTP/1.0 501 Not implemented');
        echo('HTTP/1.0 501 Not implemented');
        die();
    }
}else{
    header('HTTP/1.0 403 Unauthorized');
    echo 'Invalid credentials';
    die();
}




//format the response
// $response = '';

// switch ($method) {
//     case 'GET':
//         $response = $class->get(1);
//         break;
//     case 'POST':
//         $response = $class->create('string');
//         break;
//     case 'PUT':
//         $response = $class->edit('string');
//         break;
//     case 'DELETE':
//         $response = $class->delete();
//         break;
//     default:
//         header('HTTP/1.0 501 Not implemented');
//         die();
//         break;
// }

echo $response;

 ?>