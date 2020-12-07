<?php
error_reporting(E_ALL);
set_time_limit(0);

//$stopCmd = 'sh stopSwoole.sh';
//exec($stopCmd);

/**
 * step1、
 *
 * >>>Server 的两种运行模式
 * 1、SWOOLE_BASE -- 传统的异步非阻塞
 * BASE模式适用场景：客户端连接之间不需要交互，可以使用 BASE 模式。如 Memcache、HTTP 服务器等
 *
 * 2、SWOOLE_PROCESS -- 所有客户端的 TCP 连接都是和主进程建立的，内部实现比较复杂，用了大量的进程间通信、进程管理机制
 *
 *
 */
$swooleServer = new \Swoole\Server("127.0.0.1", 9501, SWOOLE_PROCESS);



$swooleServer->set(array(
    //'task_worker_num' => 8,
    'worker_num' => 4,//工作进程数量
    'daemonize' => false,//是否作为守护进程
));

//var_dump($swooleServer);die;

$swooleServer->on('connect', function ($serv, $fd) {
    echo "Client: Connect.\n";
});

$swooleServer->on('receive', function ($serv, $fd, $from_id, $data) {
    echo "receive:\n";

    //echo var_export($serv).PHP_EOL;
    //echo var_export($fd).PHP_EOL;//任务数
    //echo var_export($from_id).PHP_EOL;
    /**
     * 业务处理
     */
    $dataArr = json_decode($data, true);
    $cmdShellTwo = 'php public/'.$dataArr['taskEvent'].'.php';
    echo $cmdShellTwo.PHP_EOL;
    exec($cmdShellTwo);
    //echo $cmdShellTwo.PHP_EOL;
});

$swooleServer->on('close', function ($serv, $fd) {
    echo "close:\n";
    echo var_export($fd);

});

/**
 * step3、
 */
$swooleServer->start();


