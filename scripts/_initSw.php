<?php
error_reporting(E_ALL);
set_time_limit(0);

//$stopCmd = 'sh stopSwoole.sh';
//system($stopCmd);

require_once __DIR__. '/../business/lib/SendDDNotice.php';
require_once __DIR__. '/../business/other/RetryReq.php';
require_once __DIR__. '/../business/monitor/CurlTools.php';
require_once __DIR__. '/../business/lib/DataCrypto.php';
require_once __DIR__. '/../business/service/SwooleClient.php';
require_once __DIR__. '/../business/lib/SendDDNotice.php';
require_once __DIR__. '/../business/service/MsgFilterMiddle.php';
require_once __DIR__. '/../business/service/RedisClient.php';
require_once __DIR__. '/../business/service/QueueService.php';

$swooleServer = new \Swoole\Server("127.0.0.1", 9501);
$swooleServer->set(array(
    'task_worker_num' => 8,
    'worker_num' => 8,
    'daemonize' => false,
    'log_file' => __DIR__ .'/../logs/swoole.log'
));

$swooleServer->on('receive', function ($serv, $fd, $from_id, $data) {
    //投递异步任务
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id=$task_id\n";
});

//处理异步任务(此回调函数在task进程中执行)
$swooleServer->on('task', function ($serv, $task_id, $from_id, $data) {
    echo "New AsyncTask[id=$task_id]".PHP_EOL;
    //返回任务执行的结果
    /**
     * 业务处理
     */
    $dataArr = json_decode($data, true);
    $taskName            = $dataArr['taskName'];
    $taskMinuteRangeJson = $dataArr['taskMinuteRangeJson'];
    $taskParamsJson      = $dataArr['taskParamsJson'];

    $req = new RetryReq();
    $req->run($taskName, $taskMinuteRangeJson, $taskParamsJson);

    $serv->finish("$taskParamsJson -> OK");
});

//处理异步任务的结果(此回调函数在worker进程中执行)
$swooleServer->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});
echo "swooleServer runing...";
$swooleServer->start();