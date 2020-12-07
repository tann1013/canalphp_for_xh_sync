#!/bin/bash
#--关闭clients和spend
echo '------关闭当前订阅和消费进程------'
pids=`ps -ef|grep -E 'php public|startService.sh'|grep -v grep|awk '{print $2}'`
#echo $pids
for pid in ${pids}
    do
      echo "kill pid" $pid
      kill -KILL $pid
    done

echo '已关闭订阅、消费脚本、服务！'