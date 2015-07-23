<?php
/**
 * This class handles security
 */
class Security{
    //this is the default hashing algorithm that will be used all around the system
    const DEFAULT_HASH_ALGORITHM = 'sha256';


/**
 * manages password salting
 * @param  [string] $password [initial password]
 * @return [array]           [final password and salt]
 */
    public function saltPassword($password){
        $salt = Utils::getRandom(16);
        $pass = self::hashString($password.$salt);

        $response = array(
            "password" => $pass,
            "salt" => $salt,
            );
        return $response;
    }

    public function hashString($string){
        return hash(self::DEFAULT_HASH_ALGORITHM, $string);
    }
    public function checkLogin($username, $password){
        $user = Db::getLogin($username);
        // var_dump($user);die();
        if(!empty($user)){
            if(     ($user['0']['token'] === $password) 
                ||  ($user['0']['password'] === self::hashString($password.$user['0']['salt']))   ){
                $response = array(
                    'success'=>true, 
                    'key'=>$user['0']['apiKey'],
                    'token' => $user['0']['token'],
                    'uid' => $user['0']['id'],
                    'email' => $user['0']['email']
                );
                return $response;
            }else{
                return false;
            }
        }else{
            return false;
        }       
    }
}