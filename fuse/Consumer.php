<?php
//消费任务
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
$successCount=-3; //成功多少次
while (true){
    //得到的等于当前时间或者已经超时
    $service=$redis->zRangeByScore("circuit_open","-inf",time(),['limit'=>[0,20]]);
    //需要修改这个服务的状态值
    if(count($service)>0){
        foreach ($service as $s){
            //修改了服务的状态
            $redis->zAdd("circuit",$successCount,$s);
            $redis->zRem("circuit_open", $s);
            var_dump($s);
            echo "修改了{$s}状态" . PHP_EOL;
        }
    }
    usleep(50000);
}


