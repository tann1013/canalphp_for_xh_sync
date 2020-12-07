<?php
/**
 * 配置文件
 */
return array(
    //1.基础环境配置：local|dev|alpha|online
    'CURRENT_ENV' =>'alpha',

    //2.canal配置
    'CANAL_IP' => '192.168.100.75',//192.168.101.5 192.168.1.71
    'CANAL_IP_POST' => '11111',

    //3.subscribe配置
    'SUB_CLIENT_ID' => '1003',
    'SUB_DESTINATION' => 'example',//example
    //新增SUB_FILTER改为数组形式
    'SUB_FILTER_ARR' => [
        'alpha_saaslogisticsdb.cgo_logisticsdispatch',
        'alpha_saaslogisticsdb.cgo_logisticsorders',
        'alpha_dealerdb.cgo_dealerladeinfo',
        'alpha_dealerdb.cgo_purchaseorder',
        //二期：合同相关
        'alpha_dealerdb.cgo_salescontract',
        'alpha_dealerdb.cgo_purchasecontract',
        'alpha_dealerdb.cgo_carriagecontract',
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
    //6.接口重试时间区间 (说明：[2, 10]//表示的意思是接口重试两次，2秒再次调接口，假如失败，则10秒后再次调。)
    'RETRY_DATE_RANGE' => [
        'ReportNoticeMonitor'   => [2, 10],
        'OrderChangeMonitor'    => [2, 12],
        'DispatchChangeMonitor' => [2, 15],
    ],
    //7.转换桶参数设置//5秒5条 （消息过滤中间件用到）
    'PAIL_SET' => [
        'num'        => 5,//条数
        'timeSecond' => 5,//秒
    ],
    //8.过滤现货设置
    'COMMON_FILTER_ARR' => [
        'isOpenFilter' => false,//是否过滤现货数据：true开启过滤 false关闭过滤
    ],
    //9.消费相关设置
    'SPEND_SET' => [
        'num'        => 2,//每次消费数量
    ],
);