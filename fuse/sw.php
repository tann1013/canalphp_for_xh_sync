<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-08
 * @version 1.0
 */

/**
 * 客户端发送数据
 *
 * @param $data
 */
function sendSwooleData($data, $protocol = SWOOLE_SOCK_TCP){
    $host = '127.0.0.1';
    $port = '9501';
    $fp = fsockopen("tcp://" . $host, $port, $errno, $errstr);
    //\Yii::trace("连接任务处理服务器：tcp://" . $this->host . ':' . $this->port, 'app.components.SwooleService');
    if (!$fp) {
        //\Yii::error("ERROR: $errno - $errstr", 'app.components.SwooleService.sendSwooleData');
        return false;
    }
    fwrite($fp, $data);
    fclose($fp);
    return true;
}

for($j=1;$j<=10000;$j++){
    $swData = [
        'taskEvent' => 'client_for_report_xs',
        'taskMinute' => rand(1,5),
    ];
    $swData = json_encode($swData);
    $result = sendSwooleData($swData);
}

echo 'The End!';die;

