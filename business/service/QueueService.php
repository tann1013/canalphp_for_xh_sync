<?php
/**
 *
 *
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-18
 * @version 1.0
 */

class QueueService
{

    public $redisClient;

    const  CANAL_QUEUE_KEY = 'canalphp:binLogListKey';

    public function __construct()
    {
        //新增redis服务
        $this->redisClient = \RedisClient::getInstance();
    }

    /**
     * @param $rs
     * @param $msg
     */
    public function push($msg){
        $this->redisClient->handle->lPush(self::CANAL_QUEUE_KEY, json_encode($msg));
    }

    /**
     * @return array
     */
    public function pushForMsgConsumeService(){
    }
}