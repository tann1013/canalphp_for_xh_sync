<?php
/**
 * 事件一、监控订单状态变更
 *
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-06-28
 * @version 1.0
 */
namespace business\monitor;

use business\constDir\OutSource;

class OrderChangeMonitor extends AbMonitor
{
    public $curl;

    public $monitorRetrySetting;

    public function __construct()
    {
        $this->curl = new CurlTools();
    }

    /**
     * @param $retArr
     * @param $confSetting
     */
    public function subEvent($retArr, $confSetting){

        if($confSetting['common_filter_arr']['isOpenFilter']==false || $retArr['OutSource'] == OutSource::OUTSOURCE_XH){
            //1 日志
            $this->writeLog($retArr, __CLASS__);
            //2 请求接口
            $this->requestApi($retArr, $confSetting);
        }
    }

    /**
     * @param $retArr
     * @param $confSetting
     */
    public function requestApi($retArr, $confSetting){
        //1 接收参数
        $method = '/Trade/api/Order/Inside_UpdateLogisticsStatus';
        $params = $retArr;
        //2 验参
        //3 请求
        $this->curl->curlPostWithApi($method , $params, $confSetting, 'OrderChangeMonitor');
    }
}