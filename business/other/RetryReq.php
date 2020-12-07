<?php
/**
 * 重试机制v1
 *
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-08
 * @version 1.0
 */


class RetryReq
{
    public $curl;

    public function __construct(){
        $this->curl = new \business\monitor\CurlTools();
        $this->ddService = new SendDDNotice();
    }
    /**
     * @param $taskName
     * @param $taskMinuteRangeJson
     * @param $taskParamsJson
     */
    public function run($taskName, $taskMinuteRangeJson, $taskParamsJson){
        try{
            $taskParams = json_decode($taskParamsJson, true);
            $taskMinuteRange =  json_decode($taskMinuteRangeJson, true);//[20, 30]

            //1 初始化变量
            $isApiException = true;
            $maxReqSize = sizeof($taskMinuteRange);
            $nowReqTimes = 0;
            while ($isApiException && $nowReqTimes<$maxReqSize){
                //1 再次请求接口
                //钉钉接口异常通知(第一次请求才通知)
                if($nowReqTimes==0){
                    $this->ddService->ddRobotSendForCanal('接口('.$taskParams['method'].')异常，详情见>>>'.json_encode($taskParams['params']), 'RetryReq');
                }

                //1.1 休眠一段时间
                $sleepMinutes = intval($taskMinuteRange[$nowReqTimes]);
                sleep($sleepMinutes);
                $this->_writeLogForRR('taskName='.$taskName.'#sleep='.$sleepMinutes);

                //1.2 请求接口
                $isApiException = $this->curl->curlPostWithApiFinal($taskParams['method'], $taskParams['params'], $taskParams['confSetting']);

                //1.3 递增
                $nowReqTimes ++;

                //1.4 日志
                echo '第'.$nowReqTimes.'次重试，重试结果为:isApiException='.$isApiException.',_curlApiMinutes=0,nowReqTimes='.$nowReqTimes.PHP_EOL;
            }

        }catch (Exception $e){
            $this->_writeLogForRR($e->getMessage());
        }
    }

    /**
     * @param $content
     */
    private function _writeLogForRR($content){
        $path =  __DIR__ . '/../../logs/retryReq-'.date('Y-m-d').'.log';
        file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
    }
}