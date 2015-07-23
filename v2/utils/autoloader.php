<?php
//define class path and class child folders
define("classPath","");
define("class_dir_array", serialize (array ("user", 'company', 'library')));

spl_autoload_register(function($class_name) 
    {
       $dir_array = unserialize(class_dir_array);
       foreach ($dir_array as $dir) {
           if(file_exists(classPath.$dir."/".$class_name.".class.php")){
                require_once(classPath.$dir."/".$class_name.".class.php");
            break;
           }
       }
    }
);
?>