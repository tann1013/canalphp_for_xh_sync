<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-08-06
 * @version 1.0
 */

namespace business\monitor;


use business\constDir\OutSource;

class ReportNoticeMonitor extends AbMonitor
{
    public $curl;

    public function __construct()
    {
        $this->curl = new CurlTools();
    }

    public function subEvent($retArr, $confSetting)
    {
        if($confSetting['common_filter_arr']['isOpenFilter']==false || $retArr['OutSource'] == OutSource::OUTSOURCE_XH){
            // TODO: Implement subEvent() method.
            $this->writeLog($retArr, __CLASS__);
            $this->requestApi($retArr, $confSetting);
        }
    }

    public function requestApi($retArr, $confSetting)
    {
        // TODO: Implement _requestApi() method.
        //1 组装参数
        if($retArr['OperTypeStr'] == 'delete'){
            $method = '/Manage/Trade/api/Report/Inside_DeleteDealerLadeReport';
        }else{
            $method = '/Manage/Trade/api/Report/Inside_AddDealerLadeReport';
        }
        $params['Id']   = $retArr['Id'];
        $params['OperType'] = self::OPER_TYPE_MAPPS[$retArr['OperTypeStr']];
        //2 请求接口
        $this->curl->curlPostWithApi($method , $params, $confSetting, 'ReportNoticeMonitor');
    }
}