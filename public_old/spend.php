<?php
/**
 * 实时消费
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-21
 * @version 1.0
 */
require_once __DIR__ . '/_common/_common.php';

require_once __DIR__ . '/../business/service/QueueService.php';
require_once __DIR__ . '/../business/monitor/CurlTools.php';
require_once __DIR__ . '/../business/lib/DataCrypto.php';
require_once __DIR__ . '/../business/lib/SendDDNotice.php';
require_once __DIR__ . '/../business/lib/ConfigAct.php';
require_once __DIR__ . '/../business/service/SwooleClient.php';
require_once __DIR__ . '/../business/service/RedisClient.php';
require_once __DIR__ . '/../business/service/MsgConsumeService.php';
require_once __DIR__ . '/../business/service/MsgFilterMiddle.php';
//1.3 基础逻辑
$conf = new \ConfigAct();
$conf->loadConf(__DIR__ . '/../business/config/conf_for_entry.php');//71
//$conf->loadConf(__DIR__ . '/../business/config/conf_for_entry_local.php');//local
$confSetting['current_env'] =  $conf->getConf('CURRENT_ENV');
$confSetting['canal_ip'] = $conf->getConf('CANAL_IP');
$confSetting['canal_ip_post'] = $conf->getConf('CANAL_IP_POST');
$confSetting['sub_client_id'] = $conf->getConf('SUB_CLIENT_ID');
$confSetting['sub_destination'] = $conf->getConf('SUB_DESTINATION');
$confSetting['sub_filter_arr'] = $conf->getConf('SUB_FILTER_ARR');
$confSetting['db_task_mapps'] = $conf->getConf('DB_TASK_MAPPS');
$confSetting['retry_date_range'] = $conf->getConf('RETRY_DATE_RANGE');//新增重试时间区间配置
$confSetting['sub_filter'] = implode(',', $confSetting['sub_filter_arr']);
unset($confSetting['sub_filter_arr']);
$confSetting['spend_set'] = $conf->getConf('SPEND_SET');//消费设置 add 9-24
//print_r($confSetting);die;

function _writeLogForSpend($content){
    $path =  __DIR__ . '/../logs/spend-'.date('Y-m-d').'.log';
    file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
}
/**
 * @param $redisClient
 * @param $getSize
 * @return array
 */
function _getTwoMsg($redisClient, $getSize){
    $thisRangeMsgList = [];
    //$maxLength = $redisClient->handle->lLen(QueueService::CANAL_QUEUE_KEY);
    $jsonMsgDataList = $redisClient->handle->lRange(QueueService::CANAL_QUEUE_KEY, 0, $getSize-1);//第一个，到第2个
    if($jsonMsgDataList){//&& $maxLength>$getSize
        foreach ($jsonMsgDataList as $jsonMsgData){
            //1 解析数据
            $msgData = json_decode($jsonMsgData, TRUE);
            array_push($thisRangeMsgList, $msgData);
            //2 移除已获取消息
            $redisClient->handle->lPop(QueueService::CANAL_QUEUE_KEY);
        }
    }
    return $thisRangeMsgList;
}

/**
 * @param $confSetting
 */
function mainProcess($confSetting){
    try {
        //1 预检
        //1.1 检测redis服务是否异常
        $rs = new \RedisClient();
        if(!is_object($rs->handle)){throw new \Exception('redis服务异常！', -102);}
        //1.2 启动API消息消费
        $msgCs = new \MsgConsumeService();
        //1.3 启动队列服务
        $redisClient = RedisClient::getInstance();
        $spendNum = intval($confSetting['spend_set']['num']);

        //2 业务处理
        while (true) {
            $msgs = _getTwoMsg($redisClient, $spendNum);
            if($msgs){
                //业务处理
                $reqSize = $msgCs->mainProcess($msgs, $confSetting);

                _writeLogForSpend('已消费'.sizeof($msgs).'条ApiMsg消息!');//请求接口'.$reqSize.'次
            }
            // 循环取数据时间间隔，不能太长也不能太短，觉得 100 ~ 500毫秒合适，但要配合上面取数据$spendNum
            usleep(100 * 1000);
        }
    }catch (\Exception $e) {
        //echo $e->getMessage(), PHP_EOL;
        _writeLogForException('spend:'.$e->getMessage());
        //@todo 新增连接Canal服务异常重试机制
        //1 异常通知钉钉群
        //$ddClent = new \SendDDNotice();
        //$ddClent->ddRobotSendForCanal($e->getMessage(), 'spend');

        //2 10s后重试
        sleep(10);
        _writeLogForSpend('服务异常重试中...');
        mainProcess($confSetting);
    }
}
mainProcess($confSetting);
