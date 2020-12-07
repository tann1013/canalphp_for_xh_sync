#!/bin/bash
echo 'Starting Swoole!'

#普通进程启动
php scripts/_initSw.php
#守护进程启动
#nohup php scripts/_initSw.php > log.txt 2>&1 &

DAEMON_NUM=`ps -eaf | grep "startSwoole.sh" | grep -v "grep" | wc -l`
if [ "$DAEMON_NUM" == 0 ];then
    echo 'FAIL'
else
    echo 'OK'
fi