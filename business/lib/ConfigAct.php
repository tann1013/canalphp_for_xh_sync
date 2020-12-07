<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-06-28
 * @version 1.0
 */

class ConfigAct
{
    protected static $config;

    // 加载配置文件
    function loadConf($confFile){

        if (is_file($confFile)){
            self::$config = include_once $confFile;
        }
    }

    function getConf($name){
        if(isset(self::$config[$name])){
            return self::$config[$name];
        }else{
            //return "config $name is undefined ";
            throw new Exception("config $name is undefined ");
        }
    }

}