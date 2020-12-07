<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-07-21
 * @version 1.0
 */

namespace business\monitor;


use business\constDir\OutSource;

class DispatchChangeMonitor extends AbMonitor
{
    public $curl;
    public $method = '/manage/trade/api/report/DispatchReportSubscribe';

    public function __construct()
    {
        $this->curl = new CurlTools();
    }

    public function subEvent($retArr, $confSetting)
    {
        //过滤设置
        if($confSetting['common_filter_arr']['isOpenFilter']==false || $retArr['OutSource']==OutSource::OUTSOURCE_XH){
            //common_filter_arr
            //1 日志
            $this->writeLog($retArr, __CLASS__);
            //2 请求接口
            $this->requestApi($retArr, $confSetting);
        }
    }

    public function requestApi($retArr, $confSetting)
    {
        // TODO: Implement _requestApi() method.
        //1 组装参数
        $method = $this->method;
        $params['CLDId']   = $retArr['Id'];
        $params['OperType'] = self::OPER_TYPE_MAPPS[$retArr['OperTypeStr']];

        //2 请求接口
        $this->curl->curlPostWithApi($method , $params, $confSetting, 'DispatchChangeMonitor');
    }
}