<?php
/**
 * redis单例设计
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-18
 * @version 1.0
 */

class RedisClient
{
    public $version = 'v1.0';
    public $redis;
    public $handle = NULL;
    private static $_instance = NULL;//定义私有的属性变量
    //@todo redis设置 add 2020-9-24 todo到时候会改到配置中
    public $set_host     = '192.168.1.88';
    public $set_password = null;
    public $set_database = 1;
    public $set_port     = 6379;

    //定义公用的静态方法
    public static function getInstance() {
        if (NULL == self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function __construct() {
        //实例化redis
        $redis = new Redis();
        $redis->connect($this->set_host, $this->set_port);
        //$redis->auth(Conf::AUTH);
        $this->handle = &$redis;
        //将变量与redis通过引用符关联在一起，以后直接使用handle即可，相当于将redis付给一个变量，这是另一种写法
    }

    public function __destruct() {
        $this->handle->close();
    }
}