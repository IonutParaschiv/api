<?php 
require_once('config/autoloader.php');
$access = false;
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'This page requires authenthication';
        exit;
    }else {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(empty($_POST['json'])){
                header('HTTP/1.0 400 Bad Request');
                echo 'Invalid argument supplied "json"';
                die();
            }
            if(empty($_POST['key'])){
                header('HTTP/1.0 403 Forbiden');
                echo 'Invalid Key';
            die();
        }
        }

        $response = Security::checkLogin($username, $password);
        if($response['success']){
            $access = true;
            $accountId = $response['uid'];
            // if($_POST['key'] === $response['key']){
            //     $access = true;
            //     $accountId = $response['uid'];
            // }else{

            //     $keyCheck = Db::verifyKey($_POST['key']);
            //     if($keyCheck['0']['master']){
            //         $access = true;
            //     }else{
            //         header('HTTP/1.0 401 Unauthorized');
            //         echo 'Invalid Key';
            //         die();
            //     }
            // }
        }


    }



// var_dump($_SERVER['REQUEST_URI']);
if($access){
    $paths = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    $resource = explode('/', $paths);
    unset($resource['0'], $resource['1'], $resource['2'], $resource['3']);
    $resource = array_values($resource);

    switch ($resource['0']) {
        case 'account':
            switch ($method) {
                case 'POST':
                    if(!empty($resource['1'])){
                        if(is_numeric($resource['1'])){
                            $accountId = $resource['1'];
                            if( !empty($resource['2'])  && $resource['2'] == 'company'){
                                if( !empty($resource['3']) && is_numeric($resource['3']) ){
                                    $response = Db::editCompany($resource['3'], $accountId, json_decode($_POST['json']));
                                }else{
                                    $response = Db::createCompany($accountId, json_decode($_POST['json']));
                                }
                                echo json_encode($response);
                            }else{  
                                $response = Db::editAccount(json_decode($_POST['json']), $accountId);
                                echo json_encode($response);
                            }

                        }else{
                            header("HTTP/1.0 400 Bad Request");
                            echo "HTTP/1.0 400 Bad Request";
                            die();
                        }
                    }else{
                        $response = Db::createAccount(json_decode($_POST['json']), false, '', $_POST['key']);
                        header("HTTP/1.0 200 OK");
                        echo json_encode($response);   
                    }
                    break;
                case 'GET':
                    if(!empty($resource['1'])){
                            if(is_numeric($resource['1'])){
                                    $accountId = $resource['1'];
                                    if(!empty($resource['2']) && $resource['2'] == 'company'){
                                        if(!empty($resource['3']) && is_numeric($resource['3'])){
                                            $response = Db::getSingleAccountCompany($accountId, $resource['3']);
                                        }else{
                                            $response = Db::getAccountCompanies($accountId);
                                        }
                                    }else{
                                        $response = Db::getAccount($accountId);
                                    }
                                    echo json_encode($response);
                                }else{
                                    $response = Security::checkLogin($username, $password);
                                    echo json_encode($response);
                                    // header("HTTP/1.0 400 Bad Request");
                                    // echo "HTTP/1.0 400 Bad Request";
                                    die();
                                }
                    }else{
                        $params = json_decode($_GET['json']);
                        $response = Security::checkLogin($params->email, $params->password);
                        echo json_encode($response);
                    }
                    break;
                case 'DELETE':
                    if(!empty($resource['1']) && is_numeric($resource['1'])){
                        $accountId = $resource['1'];
                        if(!empty($resource['2']) && $resource['2'] == 'company' ){
                            if(!empty($resource['3']) && is_numeric($resource['3'])){
                                $response = Db::deleteCompany($accountId, $resource['3']);
                            }else{
                                header('HTTP/1.0 501 Not implemented');
                                die();
                            }
                        }else{
                            var_dump('to be implemented: delete account');die();
                        }
                        echo json_encode($response);
                    }else{
                        return false;
                    }
                    
                    break; 
                default:
                    header('HTTP/1.0 501 Not Implemented');
                    die();
                    break;
            }
                break;
        case 'company':
             switch ($method) {
                case 'POST':
                    $key = $_POST['key'];
                    if(!empty($resource['1']) && is_numeric($resource['1'])){
                        if(!empty($resource['2']) && $resource['2'] == 'staff'){
                            if(!empty($resource['3']) && is_numeric($resource['3'])){
                                $response = Db::editStaff($resource['3'], json_decode($_POST['json']));
                            }else{
                                 $response = Db::createStaff($resource['1'], json_decode($_POST['json']));
                            }
                        }elseif(!empty($resource['2']) && $resource['2'] == 'service'){
                            if(!empty($resource['3']) && is_numeric($resource['3'])){
                            }else{
                                $response = Db::createService($resource['1'], json_decode($_POST['json']));
                            }
                            
                        }else{
                            $response = Db::editCompany($resource['1'], json_decode($_POST['json']) );
                        }
                        
                        if($response){
                            echo json_encode($response);
                        }else{
                            echo "error";
                        }
                    }else{
                        $response = Db::createCompany($accountId, json_decode($_POST['json']));
                        echo json_encode($response);
                    }
                    break;
                case 'GET':

                    if(!empty($resource['1']) && is_numeric($resource['1'])){
                        if(!empty($resource['2']) && $resource['2'] == 'service'){
                            if(!empty($resource['3']) && is_numeric($resource['3'])){
                                $response = Db::getSingleService($resource['3']);
                            }else{
                                $response = Db::getCompanyServices($resource['1']);
                            }
                        }elseif(!empty($resource['2']) && $resource['2'] == 'staff'){
                            if(is_numeric($resource['3'])){
                                $response = Db::getSingleStaff($resource['3']);
                            }
                        }
                        else{
                            $response = Db::getCompany( intval( $resource['1']) );
                        }
                        if($response){
                             echo json_encode($response);
                        }else{
                            echo 'HTTP/1.1 404 Not Found ';
                            header('HTTP/1.1 404 Not Found');
                        }
                       
                    }else{
                        header('HTTP/1.1 404 Not Found');
                    }
                    break;
                    case 'DELETE':
                        var_dump("expression");die();
                    break;
                default:
                    header('HTTP/1.0 501 Not Implemented');
                    die();
                    break; 

                }
            break;
        default:
            header('HTTP/1.0 404 Not Found');
            die();
            break;
    }
}else{
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'This page requires authenthication';
}

?>