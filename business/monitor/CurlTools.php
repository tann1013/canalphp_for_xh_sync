<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-07-21
 * @version 1.0
 */

namespace business\monitor;


class CurlTools
{
    public function __construct()
    {
        $this->dataCryptoObj = new \DataCrypto();
        $this->swooleClient = new \SwooleClient();
        $this->ddService = new \SendDDNotice();
        //引入消息过滤中间件
        $this->msgFilterMiddle = new \MsgFilterMiddle();
    }

    function _writeLogForBinLog($content){
        $path =  __DIR__ . '/../../business/../logs/binLog-'.date('Y-m-d').'.log';
        file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
    }

    /**
     * @param $method
     * @param $params
     * @param $confSetting
     * @param string $monitorClass
     */
    public function curlPostWithApi($method, $params, $confSetting, $monitorClass=''){
        //测试
        $this->_writeLogForBinLog(json_encode([$method, $params]));

        //1 预备处理 $monitorClass分割
        //$_tmpArr = explode('\\', $monitorClass);
        //$monitorClass = $_tmpArr[sizeof($_tmpArr)-1];
        $tmpVar = $this->_getTmpVarByBusiness($method, $params, $monitorClass);//@todo 临时变量,专门分组用

        if(!empty($tmpVar)){

            //1 构建消息
            $thisMsg = array(
                'method' => $method,
                'tmpVar' => $tmpVar,
                'monitorClass' => $monitorClass,
                'paramsJson' => json_encode($params),
                'time' => date('Y-m-d H:i:s', time())
            );
            //2 消息过滤且入列
            /**---消息过滤中间件**/
            $pailSetNum = $confSetting['pail_set']['num'];
            $this->msgFilterMiddle->mainProcess($thisMsg, $pailSetNum);
        }
    }

    /**
     * @param $method
     * @param $params
     *
     * DispatchChangeMonitor -- CLDId
     * OrderChangeMonitor -- LiId
     * ReportNoticeMonitor -- Id
     *
     */
    private function _getTmpVarByBusiness($method, $params, $monitorClass){
        switch ($monitorClass){
            case 'DispatchChangeMonitor':
                $tmpVar = isset($params['CLDId']) ? $params['CLDId'] : '';
                break;
            case 'OrderChangeMonitor':
                $tmpVar = isset($params['LiId']) ? $params['LiId'] : '';
                break;
            case 'ReportNoticeMonitor':
                $tmpVar = isset($params['Id']) ? $params['Id'] : '';
                break;
        }
        return $tmpVar;
    }

    /**
     * @param $method
     * @param $params
     * @param $confSetting
     * @return bool
     */
    public function curlPostWithApiFinal($method, $params, $confSetting, $monitorClass=''){
        $isApiException = false;

        //-----测试异常重试--@date 9-28
        /*
        if(empty($monitorClass)){//RetryReq请求过来的（swoole）
            $urls = [
                '/manage/trade/api/report/DispatchReportSubscribe1',
                '/manage/trade/api/report/DispatchReportSubscribe',
                '/manage/trade/api/report/DispatchReportSubscribe3',
            ];
            $methodTest = $urls[rand(0,2)];
            $method = $methodTest;
        }
        */
        //-----测试异常重试--

        $response = $this->curlPost($method , $params, $confSetting);
        /**
         * 判断接口是否异常(兼容多种情况)
         * 1. Code/Code=200
         * 2. code/code=0
         */
        $st1 = (bool) isset($response['Code']) && $response['Code']==200;
        $st2 = (bool) isset($response['code']) && $response['code']==0;
        if($st1 || $st2){
        }else{
            $this->writeLogForApiErr($method, $params, $response);
            $isApiException=true;

            /**引入接口异常重试机制 start**/
            if(!empty($monitorClass)){//@todo retryReq不处理投递
                //接口异常则投递（接口重试）任务
                $retryDateRange = $this->_getRetry($monitorClass, $confSetting['retry_date_range']);

                $swData = [
                    'taskParamsJson' => json_encode(array('method'=>$method , 'params'=>$params, 'confSetting' => $confSetting)),
                    'taskName'       => $monitorClass,//接口名称
                    'taskMinuteRangeJson' => json_encode($retryDateRange),
                ];
                $this->swooleClient->_sendSwooleData(json_encode($swData));

                //接口异常钉钉通知
                //$this->ddService->ddRobotSendForCanal('接口('.$method.')异常，详情见>>>'.json_encode($params));
            }
            /**引入接口异常重试机制 end**/
        }
        return $isApiException;
    }

    /**
     * @param $url
     * @param $params
     * @param bool $is_debug
     * @return mixed
     */
    public function curlPost($url, $params, $confSetting){
        //1.1 基本URL
        $configBaseUrl = $this->_toolsGetConfigBaseUrl($confSetting);
        //1.2 方法URL
        $url = $configBaseUrl . $url;
        //1.3 token
        $token = $this->dataCryptoObj->sysGetLinkToken();//解决token过期问题
        //2 转换
        $postStr = json_encode($params);//转出json字符串
        $requestHeader = array(
            //'X-Tsign-Open-App-Id:' . $_config['appId'],
            //'X-Tsign-Open-App-Secret:' . $_config['appSecret'],
            'Token:' .  $token,
            'Content-Type:' . 'application/json'
        );
        //curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, false); // 输出HTTP头 true
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr);// post传输数据
        curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeader);
        $result = curl_exec($curl);
        curl_close($curl);
        //sleep(1);
        return json_decode($result, true);
    }

    /**
     * @param $method
     * @param $params
     * @param $confSetting
     */
    public function writeLogForApiErr($method, $params, $response){
        $path =  __DIR__ . '/../../logs/apiErr-'.date('Y-m-d').'.log';;
        $allParams = [$method , $params, $response];
        file_put_contents($path, date('Y-m-d H:i:s', time()).'API_ERR：' . var_export($allParams, true).PHP_EOL, FILE_APPEND);
    }

    /**
     * @return string
     */
    private function _toolsGetConfigBaseUrl($confSetting){

        $env = $confSetting['current_env'];

        $url = 'http://towerdev.com';

        switch ($env) {
            case 'local';
                $url = 'http://towerdev.com';
                break;
            case 'dev';
                $url = 'http://dev.api.cementgo.com';
                break;
            case 'alpha':
                $url = 'http://alpha.api.cementgo.com';
                break;
            case 'online':
                $url = 'http://api.cementgo.com';
                break;
        }
        return $url;
    }

    /**
     * @param $currentClass business\monitor\DispatchChangeMonitor
     * @param $confForRetry
     */
    private function _getRetry($currentClass, $confForRetry){
        return isset($confForRetry[$currentClass]) ? $confForRetry[$currentClass] : [];
    }
}