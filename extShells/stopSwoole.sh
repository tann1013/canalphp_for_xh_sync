#!/bin/bash
echo 'Stop in process ...'
searchName=XXX
pids=`ps -ef|grep _initSw.php|grep -v grep|awk '{print $2}'`
#pids=`ps -ef|grep _initSw.php|grep -v grep|awk '{print $2}'`
#echo $pids
for pid in ${pids}
do
    echo "kill pid" $pid
    kill -KILL $pid
done
