<?php 
/**
 * This class handles the user calls
 */

class company{

    /**
     * This function will route the request to the desired class
     * @param  string $method [HTTP request method (POST, GET, DELETE)]
     * @param  array  $paths  [The http request uri]
     * @return [object]         
     */

    public function route($method='', $paths = array(), $params, $userid){

        $identifier = !empty($paths['0']) ? $paths['0'] : '';
        //format the response
        $response = '';
        //the default class will be company
        $class = 'company';
                
        #check if the paths are empty. if they are, it is a call to master endpoint
        if(empty($paths)){
            $class = 'company';

        }elseif(!empty($paths['0']) && !is_numeric($paths['0'])){#check if the first parameter is numeric. if it's not, it is an invalid call
            echo 'HTTP/1.1 404 Not Found';
            header('HTTP/1.1 404 Not Found');
            die();
        }elseif(!empty($paths['1'])){
            #if all is fine, route to desired class
            $classname = 'company_'.$paths['1'];
            $class = new $classname;
            $identifier_subclass = !empty($paths['2']) ? $paths['2'] : '';

        }
        switch ($method) {
            case 'GET':
                if((empty($identifier) || !is_numeric($identifier)) ||
                    (empty($identifier_subclass) || !is_numeric($identifier_subclass))
                    ){
                    $response = $class::getAll($userid, $identifier);
                }else{

                    $response = $class::get($identifier, $identifier_subclass);
                }
                break;
            case 'POST':
                $response = $class::create($params, $userid, $identifier);
                break;
            case 'PUT':
                if(empty($identifier) || !is_numeric($identifier)){
                    echo 'HTTP/1.1 404 Not Found';
                    header('HTTP/1.1 404 Not Found');
                    die();
                }

                if(!empty($identifier_subclass)){
                    $response = $class::edit($identifier, $params, $identifier_subclass);
                }else{
                    $response = $class::edit($identifier, $params, $userid);
                }
                break;
            case 'DELETE':
                if(empty($identifier) || !is_numeric($identifier)){
                    echo 'HTTP/1.1 404 Not Found';
                    header('HTTP/1.1 404 Not Found');
                    die();
                }

                if(!empty($identifier_subclass)){
                    $response = $class::delete($identifier, $identifier_subclass);
                }else{
                    $response = $class::delete($userid, $identifier);
                }
                break;
            default:
                header('HTTP/1.0 501 Not implemented');
                die();
                break;
        }
        return $response;
    }

    public function create($params, $userid){
        $response = Db::createCompany($userid, $params);

        return json_encode($response);
    }

    public function edit($identifier, $params, $userid){
        $response = Db::editCompany($identifier, $userid, $params);


        return json_encode($response);
    }

    public function get($identifier){

        $response = Db::getCompany($identifier);

        return json_encode($response);
    }

    public function delete($userid, $identifier){

        $response = Db::deleteCompany($userid, $identifier);

        return json_encode($response);
    }

    public function getAll($userid){
        $response = Db::getAccountCompanies($userid);

        return json_encode($response);
    }
}
 ?>
