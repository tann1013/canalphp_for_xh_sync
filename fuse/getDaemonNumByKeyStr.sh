#!/bin/bash

#公共函数
#在函数体内部，通过 $n 的形式来获取参数的值，
#例如，$1表示第一个参数，$2表示第二个参数...
function _getDaemonNumByKeyStr(){
    grepStr=$1
    #echo $grepStr
    pids=`ps -ef|grep "$grepStr"|grep -v grep|awk '{print $2}'`
    #echo $pids
    for pid in ${pids}
    do
        echo $pid
        kill -KILL $pid
    done
}
#_getDaemonNumByKeyStr "hello"
_getDaemonNumByKeyStr "php sw.php"



#echo '------关闭当前订阅和消费进程------'
#pids=`ps -ef|grep 'php public'|grep -v grep|awk '{print $2}'`
