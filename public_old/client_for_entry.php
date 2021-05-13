<?php
/**
 * 生产过程脚本(订阅binlog消息，解析为apiMsg消息)
 * @author tann1013@hotmail.com
 * @date 2020-06-24
 * @version 1.0
 */
namespace xingwenge\canal_php\sample;

use xingwenge\canal_php\BusinessTask;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;

require_once __DIR__. '/../vendor/autoload.php';


require_once __DIR__ . '/_common/_common.php';

//一、加载配置文件
//1.1 基础配置 monitor/monitorTasks
require_once __DIR__ . '/../business/monitor/AbMonitor.php';
require_once __DIR__ . '/../business/monitor/CurlTools.php';
require_once __DIR__ . '/../business/monitorTasks/AbTask.php';
require_once __DIR__ . '/../business/lib/ConfigAct.php';
require_once __DIR__ . '/../business/lib/DataCrypto.php';
require_once __DIR__ . '/../business/service/SwooleClient.php';
require_once __DIR__ . '/../business/lib/SendDDNotice.php';
//新增redis服务
require_once __DIR__ . '/../business/service/RedisClient.php';
require_once __DIR__ . '/../business/service/MsgConsumeService.php';
require_once __DIR__ . '/../business/service/MsgFilterMiddle.php';
require_once __DIR__ . '/../business/service/QueueService.php';

//1.2 加载常量
require_once __DIR__ . '/../business/constDir/OutSource.php';

//1.2 业务配置
//1.2.1 Monitor配置
require_once __DIR__ . '/../business/monitor/DispatchChangeMonitor.php';
require_once __DIR__ . '/../business/monitor/ReportNoticeMonitor.php';
require_once __DIR__ . '/../business/monitor/OrderChangeMonitor.php';
require_once __DIR__ . '/../business/monitor/ReportNoticeWithContractMonitor.php';//新增合同 9-14 add by tj

//1.2.2 Task配置
require_once __DIR__ . '/../business/monitorTasks/DispatchChangeTask.php';
require_once __DIR__ . '/../business/monitorTasks/OrderChangeTask.php';
require_once __DIR__ . '/../business/monitorTasks/XiaoshouOrderChangeTask.php';
require_once __DIR__ . '/../business/monitorTasks/CaigouOrderChangeTask.php';
//新增合同相关 9-14 add by tj
require_once __DIR__ . '/../business/monitorTasks/ContractForPurchaseChangeTask.php';
require_once __DIR__ . '/../business/monitorTasks/ContractForSalesChangeTask.php';
require_once __DIR__ . '/../business/monitorTasks/ContractForCarriageChangeTask.php';

//1.3 基础逻辑
$conf = new \ConfigAct();
//$conf->loadConf(__DIR__ . '/../business/config/conf_for_entry.php');//71
$conf->loadConf(__DIR__ . '/../business/config/conf_for_entry_local.php');//local
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
//新增设置参数
/*
$confSetting['pail_set'] = $conf->getConf('PAIL_SET');//转换桶参数配置 add 9-22
$confSetting['common_filter_arr'] = $conf->getConf('COMMON_FILTER_ARR');//过滤现货设置 add 9-23
$confSetting['redis_set'] = $conf->getConf('REDIS_SET');//新增redis货设置 add 9-24
//$confSetting['redis_set']['host']、['password']、['database']、['port']
$confSetting['quick_st'] = $conf->getConf('QUICK_ST');//新增快速消费设置 add 9-27
*/
//SPEND_SET_QUICK_ST

/**
 * @param $content
 */
function _writeLog($content){
    echo  $content . PHP_EOL;
    $path =  __DIR__ . '/../logs/events-'.date('Y-m-d').'.log';
    file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
}

/**
 * @param $client
 */
function _quickGetAndForget($client){
    $entriesSize = 1;
    while ($entriesSize>0){
        $message = $client->get(100);
        $entries = $message->getEntries();
        $entriesSize = sizeof($entries);
        _writeLog('本次快速消费数量为:'.$entriesSize);
    }
    _writeLog('已快速消费完BinLog事件!');
    exit(1);
}

/**
 * @param $confSetting 配置变量
 */
function mainProcess($confSetting){
    //try {
        /*
        //1检测服务是否异常
        //1.1 检查swoole服务是否异常
        $sw = new \SwooleClient();
        if(!$sw->_checkService()){throw new \Exception('swoole服务异常！', -101);}
        //1.2 检测redis服务是否异常
        $rs = new \RedisClient();
        if(!is_object($rs->handle)){throw new \Exception('redis服务异常！', -102);}


        //2 实例化消息过滤中间件
        $msgMiddle = new \MsgFilterMiddle();
        //3 初始化变量
        $initStartTime = time();//启动脚本时间
        //$initTimes = 1;
        */
        _writeLog('client_for_entry 准备订阅');
        $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
        $client->connect($confSetting['canal_ip'], $confSetting['canal_ip_post']);//**canal所在的主机ip**
        $client->checkValid();
        //localdb_dev_saaslogisticsdb.cgo_logisticsorders
        $client->subscribe($confSetting['sub_client_id'], $confSetting['sub_destination'], $confSetting['sub_filter']);

    //var_dump($client);die;

        _writeLog('client_for_dispatch 开始订阅中：canal_ip='.$confSetting['canal_ip'].'; canal_ip_post='.$confSetting['canal_ip_post'].'; sub_client_id='.$confSetting['sub_client_id'].'; sub_destination='.$confSetting['sub_destination'].'; sub_filter='.$confSetting['sub_filter']);

        while (true) {

            //1 初始化必要变量
            //1.1 新增逻辑是否快速消费掉binlog
            if($confSetting['quick_st']){ _quickGetAndForget($client);}

            //2 获取binlog消息
            $message = $client->get(100);
            if ($entries = $message->getEntries()) {
                //3 存在消息则处理业务
                //_writeLog('client_for_entry 需处理：'.sizeof($entries)."条数据");
                if($entries){
                    foreach ($entries as $k=>$entry) {
                        Fmt::println($entry);
                        //_writeLog($k . '-this is $entry');
                        //1、事件解析(把binglog消息解析为api消息)
                        //BusinessTask::run($entry, $confSetting, $confSetting['db_task_mapps']);
                    }
                }
            }

            //4 触发消息过滤中间件倒水事件(触发条件:启动5s且无新binlogEvents)
            $entries = is_null($entries) ? [] : $entries;
            $recycleEndTime = time();
            $diffTime = $recycleEndTime - $initStartTime;
            if(sizeof($entries)==0 && $diffTime>=5){//水未满
                _writeLog('倒水(时间间隔：'.$diffTime.')...');
                $msgMiddle->pour('byTime');
            }

            //5 缓1s
            //_writeLog('开始第'.$initTimes.'次sleep,本次取数据条数为：'.sizeof($entries));
            //$initTimes++;
            sleep(1);
        }
        $client->disConnect();

        /*
    }catch (\Exception $e) {
        //var_dump($e->getMessage());die;

        //echo $e->getMessage(), PHP_EOL;
        _writeLogForException('client:'.$e->getMessage());
        //@todo 新增连接Canal服务异常重试机制
        //1 异常通知钉钉群
        //$ddClent = new \SendDDNotice();
        //$ddClent->ddRobotSendForCanal($e->getMessage(), 'client');

        //2 缓10s
        sleep(10);
        _writeLog('服务异常重试中...');

        //3 递归调用
        //mainProcess($confSetting);

    }
    */
}

mainProcess($confSetting);