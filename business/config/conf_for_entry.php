<?php
/**
 * 配置文件
 */
return array(
    //1.基础环境配置：local|dev|alpha|online
    'CURRENT_ENV' =>'alpha',
    //2.redis配置
    'REDIS_SET' => [
        'host' => '192.168.1.88',
        'password' => null,
        'database' => 1,
        'port' => '6379',
    ],
    //3.canal配置
    'CANAL_IP' => '192.168.1.71',//192.168.101.5 192.168.1.71
    'CANAL_IP_POST' => '11111',
    //4.subscribe配置
    'SUB_CLIENT_ID' => '1003',
    'SUB_DESTINATION' => 'bankcement_dev',//example
    //新增SUB_FILTER改为数组形式
    'SUB_FILTER_ARR' => [
        'alpha_saaslogisticsdb.cgo_logisticsdispatch',
        'alpha_saaslogisticsdb.cgo_logisticsorders',
        'alpha_dealerdb.cgo_dealerladeinfo',
        'alpha_dealerdb.cgo_purchaseorder',
        //二期：合同相关，目前接口还未完成
        //'alpha_dealerdb.cgo_salescontract',
        //'alpha_dealerdb.cgo_purchasecontract',
        //'alpha_dealerdb.cgo_carriagecontract',
    ],
    //5.DbTaskMapps
    'DB_TASK_MAPPS' => [
        'alpha_saaslogisticsdb.cgo_logisticsdispatch' => 'DispatchChangeTask',
        'alpha_saaslogisticsdb.cgo_logisticsorders'   => 'OrderChangeTask',
        'alpha_dealerdb.cgo_dealerladeinfo'           => 'XiaoshouOrderChangeTask',
        'alpha_dealerdb.cgo_purchaseorder'            => 'CaigouOrderChangeTask',
        //二期：合同相关
        'alpha_dealerdb.cgo_purchasecontract'          => 'ContractForPurchaseChangeTask',
        'alpha_dealerdb.cgo_salescontract'             => 'ContractForSalesChangeTask',
        'alpha_dealerdb.cgo_carriagecontract'          => 'ContractForCarriageChangeTask',
    ],
    //6.接口重试时间区间 (说明：[5, 120, 1800, 7200, 28800]//表示的意思是接口重试两次，5秒再次调接口，假如失败，则2分分钟后再次调，
    //以此类推30分钟，2个小时、8个小时。此项目使用期间，先重启1天内完成，因为第二天有其他脚本大批量拉去数据。)
    'RETRY_DATE_RANGE' => [
        'ReportNoticeMonitor'   => [15, 120, 1800, 7200, 28800],
        'OrderChangeMonitor'    => [15, 120, 1800, 7200, 28800],
        'DispatchChangeMonitor' => [15, 120, 1800, 7200, 28800],
    ],
    //7.转换桶参数设置//5秒5条 （消息过滤中间件用到）
    'PAIL_SET' => [
        'num'        => 5,//条数
        'timeSecond' => 5,//秒
    ],
    //8.过滤现货设置
    'COMMON_FILTER_ARR' => [
        'isOpenFilter' => true,//是否过滤现货数据：true开启过滤 false关闭过滤
    ],
    //9.消费相关设置
    'SPEND_SET' => [
        'num'        => 10,//每次消费数量
    ],
    //是否要快速消费掉binlog事件(请谨慎使用！！！！)
    'QUICK_ST' => false,//true -快速消费（会舍弃binlog消息），false-正常处理(默认)
);