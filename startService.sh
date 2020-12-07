#!/bin/bash
#>>>>>>>>>>step1 关闭cilent、spend
echo '------关闭当前订阅和消费进程------'
pids=`ps -ef|grep 'php public'|grep -v grep|awk '{print $2}'`
for pid in ${pids}
    do
      kill -KILL $pid
    done

#>>>>>>>>>>step2 检查服务是否异常(redis、swoole)
echo '------检查redis和swoole服务------'
#step2.1 检查redis
#DAEMON_NUM_FOR_REDIS=`ps -ef|grep redis| grep -v "grep"| wc -l`
#step2.2 检查swoole
#DAEMON_NUM_FOR_SWOOLE=`ps -ef|grep 'php scripts/_initSw.php'| grep -v "grep"| wc -l`

#>>>>>>>>>>step3 启动订阅
echo '------启动订阅进程------'
#普通进程启动
#php public/client_for_entry.php
#守护进程启动
nohup php public/client_for_entry.php > nohub_client.log 2>&1 &
DAEMON_NUM=`ps -eaf | grep "php public/client_for_entry.php" | grep -v "grep" | wc -l`
echo "本次订阅进程数:${DAEMON_NUM}"
if [ $DAEMON_NUM -eq 0 ]; then
    echo '启动订阅失败！'
    exit 8
else
    echo '启动订阅成功！订阅中...'
fi

#>>>>>>>>>>step4 启动消费
echo '------启动消费进程------'
nohup php public/spend.php > nohub_spend.log 2>&1 &
DAEMON_NUM_FOR_SP=`ps -eaf | grep "php public/spend.php" | grep -v "grep" | wc -l`
echo "本次消费进程数:${DAEMON_NUM_FOR_SP}"
if [ $DAEMON_NUM_FOR_SP -eq 0 ]; then
    echo '启动消费失败！'
    exit 9
else
    echo '启动消费成功！消费中...'
fi

#>>>>>>>>>>step5 用于docker不自动重启，非容器时可以注释掉
#ping 127.0.0.1
