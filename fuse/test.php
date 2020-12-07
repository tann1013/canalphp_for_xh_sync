<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-08
 * @version 1.0
 *
 * 业务逻辑：
 * 消费单个对象，首次请求接口，若接口异常，则再次请求接口（再次请求出现异常，则第二次请求接口，限制两次）
 */

$monitorClass = "business\monitor\DispatchChangeMonitor";
$arr = explode('\\', $monitorClass);
$monitorClass = $arr[sizeof($arr)-1];
var_dump($monitorClass);die;




/**
 * 客户端发送数据
 *
 * @param $data
 */

/**
 * php异步请求
 * @param $host string 主机地址
 * @param $path string 路径
 * @param $param array 请求参数
 * @return string
 */
function asyncRequest($host, $path, $param = array()){
    $query = isset($param) ? http_build_query($param) : '';
    $port = 80;
    $errno = 0;
    $errstr = '';
    $timeout = 30; //连接超时时间（S）

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    //$fp = stream_socket_client("tcp://".$host.":".$port, $errno, $errstr, $timeout);

    if (!$fp) {
        //Bd_Log::debug('连接失败');
        return '连接失败';
    }
    if ($errno || !$fp) {
        //Bd_Log::debug($errstr);
        return $errstr;
    }

    stream_set_blocking($fp,0); //非阻塞
    stream_set_timeout($fp, 1);//响应超时时间（S）
    $out  = "POST " . $path . " HTTP/1.1\r\n";
    $out .= "host:" . $host . "\r\n";
    $out .= "content-length:" . strlen($query) . "\r\n";
    $out .= "content-type:application/x-www-form-urlencoded\r\n";
    $out .= "connection:close\r\n\r\n";
    $out .= $query;
    $result = @fputs($fp, $out);

    @fclose($fp);
    return $result;

}

function _curlApi($minutes=5){
    $isApiException = rand(0,1);
    return $isApiException;
}

//再次请求接口时间配置
$retryMapps = [30, 60];


//10个消费对象
for($j=1;$j<=10;$j++){
    //消费逻辑

    //1 首次请求接口
    $isApiException = _curlApi();//api请求结果
    //2 初始化变量
    $maxReqSize = 2;
    $nowReqTimes = 0;
    //3 接口报异常则再次请求接口
    while ($isApiException && $nowReqTimes<2){
        //3.1 再次请求接口(投递再次请求接口任务)

        //投递【接口重试】任务
        $method = '/manage/trade/api/report/DispatchReportSubscribe2';
        $params['CLDId']   = 'T2399999';
        $confSetting['sub_filter'] = 'alpha_saaslogisticsdb.cgo_logisticsdispatch,alpha_dealerdb.cgo_dealerladeinfo';
        $confSetting['db_task_mapps'] = [
            'alpha_saaslogisticsdb.cgo_logisticsdispatch' => 'DispatchChangeTask',
        ];
        $confSetting['current_env'] = 'local';

        $swData = [//taskParamsJson、taskMinuteRangeJson
            'taskParamsJson' => json_encode(array('method'=>$method , 'params'=>$params, 'confSetting' => $confSetting)),
            'taskName'   => 'DispatchChangeMonitor',//接口名称
            'taskMinuteRangeJson' => json_encode(array(2, 50)),//分钟
        ];
        sendSwooleData(json_encode($swData));

        //echo '第'.$j.'个消费对象api异常#isApiException='.$isApiException.',_curlApiMinutes='.@$retryMapps[$nowReqTimes].',nowReqTimes='.$nowReqTimes.PHP_EOL;
        $nowReqTimes ++;
    }
}


function retryRequestProcess(){
    $swData = [
        'taskEvent' => 'client_for_report_xs',
        'taskMinute' => rand(1,5),
    ];
    $swData = json_encode($swData);
    $result = sendSwooleData($swData);
}





echo 'The end.';
