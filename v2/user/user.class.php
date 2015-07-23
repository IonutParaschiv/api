<?php 
/**
 * This class handles the user calls
 */

class user{

    /**
     * This function will route the request to the desired class
     * @param  string $method [HTTP request method (POST, GET, DELETE)]
     * @param  array  $paths  [The http request uri]
     * @return [object]         
     */
    public function route($method='', $paths = array(), $params = array()){
        // var_dump($params);die();
        $identifier = !empty($paths['0']) ? $paths['0'] : '';
        //format the response
        $response = '';
        //the default class will be user
        $class = 'user';

        #check if the paths are empty. if they are, it is a call to master endpoint
        if(empty($paths)){
            $class = 'user';

        }elseif(!empty($paths['0']) && !is_numeric($paths['0'])){#check if the first parameter is numeric. if it's not, it is an invalid call
            echo 'HTTP/1.1 404 Not Found';
            header('HTTP/1.1 404 Not Found');
            die();
        }elseif(!empty($paths['1'])){
            #if all is fine, route to desired class
            $classname = 'user_'.$paths['1'];
            $class = new $classname;
            $identifier = !empty($paths['2']) ? $paths['2'] : '';

        }
        switch ($method) {
            case 'GET':
                if(empty($identifier) || !is_numeric($identifier)){
                    echo 'HTTP/1.1 404 Not Found';
                    header('HTTP/1.1 404 Not Found');
                    die();
                }
                $response = $class::get($identifier);
                break;
            case 'POST':
                $response = $class::create($params);
                break;
            case 'PUT':
                if(empty($identifier) || !is_numeric($identifier)){
                    echo 'HTTP/1.1 404 Not Found';
                    header('HTTP/1.1 404 Not Found');
                    die();
                }
                $response = $class::edit($params, $identifier);
                break;
            case 'DELETE':
                if(empty($identifier) || !is_numeric($identifier)){
                    echo 'HTTP/1.1 404 Not Found';
                    header('HTTP/1.1 404 Not Found');
                    die();
                }
                $response = $class::delete();
                break;
            default:
                header('HTTP/1.0 501 Not implemented');
                die();
                break;
        }
        return $response;
    }

    public function create($params){
        #if any parameter is missing, return a bad request status code
        if( empty($params->name) ||
            empty($params->surname) ||
            empty($params->email) ||
            empty($params->password)
            ){
            echo 'HTTP/1.1 400 Missing parameter';
            header('HTTP/1.1 400 Bad Request');
            die();
        }

        #create user
        $response = Db::createAccount(json_decode($_POST['json']), false, '', '1234');

        if(!empty($response->status)){
            echo 'HTTP/1.1 '.$response->status . ' ' . $response->message;
            header('HTTP/1.1 '.$response->status);
        }else{
            return json_encode($response);
        }
    }
    public function get($identifier){

        $response = Db::getAccount($identifier);

        return json_encode($response);
    }

    public function edit($params, $identifier){

        $response = Db::editAccount($params, $identifier);
        if(!empty($response->status)){
            return 'there has been an error';
        }else{
            return json_encode($response);
        }
    }


    public function delete(){
        return 'this is delete user';
    }
}
 ?>
