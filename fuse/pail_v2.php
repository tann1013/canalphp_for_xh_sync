<?php
/**
 *
 * 模拟过滤中间件
 *
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-21
 * @version 1.0
 */

$classMapps = array(
    'ReportNoticeMonitor',
    'OrderChangeMonitor',
    'DispatchChangeMonitor'
);
$codeMapps = array(
    'P1',
    'P2',
    'P3',
);
function _writeLogForPail($content){
    $path =  __DIR__ . '/pail-'.date('Y-m-d').'.log';
    file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
}

function _filterWithBusiness($thisRangeMsgList){
    $newMsgList = [];
    //1 分组
    $msgListGroupByCode = _arrayGroupByCellKey($thisRangeMsgList, 'code');
    //print_r($msgListGroupByCode);die;

    //2 再遍历
    foreach ($msgListGroupByCode as $keyCode=>$itemMsgList){
        //@TODO 写具体的业务过滤逻辑(eg.入账逻辑)
        $itemMsgListLength = sizeof($itemMsgList);
        if($itemMsgListLength==1){
            $cellMsg = $itemMsgList[0];
        }else{
            $cellMsg = $itemMsgList[$itemMsgListLength-1];
        }

        //赋值到新队列
        array_push($newMsgList, $cellMsg);
    }
    //var_dump('Old:'.sizeof($thisRangeMsgList), 'New:'.sizeof($newMsgList));die;
    return $newMsgList;
}
function _arrayGroupByCellKey($arr, $pickKey)
{
    $result = array();
    foreach ($arr as $k => $v) {
        $result[$v[$pickKey]][] = $v;
    }
    return $result;
}


/**
 * @param $thisMsg
 * @param $pailArr
 */
function curlPostWithApi($thisMsg, $redis){
    //定义一个水桶
    //$pailArr = [];

    /**---处理逻辑---**/
    //array_push($pailArr, $thisMsg);
    $redis->lPush(PAIL_KEY, json_encode($thisMsg));

    $pailLength = $redis->lSize(PAIL_KEY);

    if($pailLength==5){
        //@todo 满水处理逻辑 1.过滤 2.入列

        $pailArr = $redis->lRange(PAIL_KEY, 0, 4);
        //print_r($pailArr);

        //var_dump($pailArr);die;
        _writeLogForPail('--满水--j=');
        _writeLogForPail(json_encode($pailArr));

        //1 过滤
        //$newMsgList = _filterWithBusiness($pailArr);
        //2 入列
        //...

        //3 置空
        $redis->del(PAIL_KEY);

    }

    //var_dump(sizeof($pailArr));die;
    //var_dump($thisMsg);die;
    /**---处理逻辑---**/
}



//实例化redis
const PAIL_KEY = 'pail_cess';
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//测试
//$redis->lPush();
//$s = $redis->lRange(PAIL_KEY, 0, 4);
//var_dump($s);die;



$pailArr = [];

for($j=0;$j<22;$j++){
    $thisMsg = array(
        'index' => $j,
        'code' => $codeMapps[rand(0,2)],
        'className' => $classMapps[rand(0,2)],
        'time' => date('Y-m-d H:i:s', time())
    );
    curlPostWithApi($thisMsg, $redis);

}








var_dump($pailArr);die;
