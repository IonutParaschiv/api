<?php
/**
 * This class handles miscelanous functions
 */
class Utils{
/**
 * Returns a random set of charachters 
 * @param  [int] $length the length of desired string
 * @return [string]         string of random chars
 */
    public function getRandom($length){
          $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
          }
          return $randomString;
    }

}