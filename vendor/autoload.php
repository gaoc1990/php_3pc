<?php

class autoloader {
    /**
     * @param string $className 
     * @return boolean
     */
    public static function autoload($className) {
        if ($className) {
            $root_dir = dirname(__FILE__);
            $dir_arr = array('interface','library','object','swoole','transaction','swoole\http','swoole\rpc');

            $path = $root_dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . ".php";
            if(file_exists($path)){
                require_once($path);
            }

            foreach($dir_arr as $dir){
                $filePath = $root_dir .DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR , $dir) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . ".php";
                if(file_exists($filePath)){
                    require_once($filePath);
                }
            }
        }
    }
    /**
     * 注册自动加载
     */     
    public static function register($prepend=false) { 
        if(!function_exists('__autoload')) { 
            spl_autoload_register(array('autoloader', 'autoload'), true, $prepend);     
        }else {
            return false;
        }
    }
}

//注册自动加载函数
autoloader::register();



