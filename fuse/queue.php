<?php
/**
 * 模拟队列
 *
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-17
 * @version 1.0
 */
const  LIST_KEY = 'binLogListKey';
const  LIST_SET = 'binLogSet';

/**
 * @param $rs
 * @param $msg
 */
function _tmpPush($rs,$msg){
//    //去重逻辑：相同code视为一个消息
//    //判断是否存在set
//    $isMember = $rs->sIsMember(LIST_SET, $msg['code']);
//    //var_dump($isMember, $msg['code']);die;
//    if(!$isMember){
//        //入列
//        $rs->lPush(LIST_KEY, json_encode($msg));
//    }else{
//        //不处理
//        //@todo 新增逻辑更新数据
//
//
//    }
    $rs->lPush(LIST_KEY, json_encode($msg));
}

//放入集合
function _tmpSetPush($rs, $msg){
    $s = $rs->sAdd(LIST_SET, $msg['code']);
    //SISMEMBER
    //var_dump($s);die;
}

function _tmpPop($rs){

}


//处理消息
function msgMainProcess($redis, $codeMapps, $classMapps){
    for($j=1; $j<=100;$j++){
        //100次binLog消息
        //1 构建消息
        $thisMsg = array(
            'index' => $j,
            'code' => $codeMapps[rand(0,6)],
            'className' => $classMapps[rand(0,2)],
            'time' => date('Y-m-d H:i:s', time())
        );
        echo '---log---'.PHP_EOL;
        echo $thisMsg['index'].PHP_EOL;
        echo '---log---'.PHP_EOL;

        //test
        //$s = $redis->lPush(LIST_KEY, json_encode($thisMsg));
        //var_dump($s);die;

        //2 业务处理
        //2.1 入列
        _tmpPush($redis, $thisMsg);

        //2.2 把今天订单CODE的追加到集合
        _tmpSetPush($redis, $thisMsg);
    }
}


/**
 * 消费
 * @param $redis
 */
function msgConsumeProcess($redis){
}




//去重中间件
function _uniqueMiddle($rs, $msg){
}


$classMapps = array(
    'ReportNoticeMonitor',
    'OrderChangeMonitor',
    'DispatchChangeMonitor'
);
$codeMapps = array(
    'P1',
    'P2',
    'P3',
    'P5',
    'P66',
    'P77',
    'P88',
);


//实例化redis
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
/**
 * 主程序
 */
msgMainProcess($redis, $codeMapps, $classMapps);
exit('入列完成！');

/**
 * 消费
 */
//msgConsumeProcess($redis);

while ($listLength>0){
    //消费，查询且移除
    $jsonMsgData = $redis->lPop(LIST_KEY);

    //var_dump($msgData);die;
    //解析数据
    $msgData = json_decode($jsonMsgData, TRUE);
    echo '---消费---'.PHP_EOL;
    echo $msgData['index'].PHP_EOL;
    echo '---消费---'.PHP_EOL;
    var_export($msgData).PHP_EOL;

    //重新赋值长度
    $listLength = $redis->lLen(LIST_KEY);
}

//删除集合
$redis->del(LIST_SET);
exit('消费完成！');



