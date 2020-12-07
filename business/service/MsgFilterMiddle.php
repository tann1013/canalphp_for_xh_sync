<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-22
 * @version 1.0
 */

class MsgFilterMiddle
{
    const PAIL_KEY = 'canalphp:pailKey';
    //const PAIL_FULL_TIME_KEY = 'canalphp:pailFullTimeKey';

    private $redisClient = null;
    private $queueServer = null;

    public function __construct(){
        //新增redis服务
        $this->redisClient = \RedisClient::getInstance();
        $this->queueServer = new \QueueService();
    }

    /**
     * 消息过滤中间件
     * todo  满足五条，做过滤，在入列
     * @param $thisMsg
     * @param $pailSetNum
     * @param $pailSetTimeSecond
     */
    public function mainProcess($thisMsg, $pailSetNum){
        //1 赋值到水桶
        $this->redisClient->handle->lPush(self::PAIL_KEY, json_encode($thisMsg));
        //1.1 当前桶容量 --- lSize过期了，修改为新的lLen了
        $pailLength = $this->redisClient->handle->lLen(self::PAIL_KEY);
        /*
         * 判断逻辑：5条则倒水
         */
        if($pailLength==$pailSetNum){
            //@todo 【满水或到时倒水】 处理逻辑 1.过滤 2.入列
            $this->pour('byVolume');
        }

        //特殊处理
        if($pailLength>$pailSetNum){
            //置空
            $this->redisClient->handle->del(self::PAIL_KEY);
        }
    }

    /**
     * @param $thisRangeMsgList
     * @return array
     */
    private function _filterRoute($thisRangeMsgList){
        //return $thisRangeMsgList;
        return $this->_filterWithBusiness($thisRangeMsgList);
    }

    /**
     * 消息过滤
     * @param $thisRangeMsgList
     */
    private function _filterWithBusiness($thisRangeMsgList){
        $newMsgList = [];
        //1 分组

        //print_r($thisRangeMsgList);die;
        $msgListGroupByCode = $this->_arrayGroupByCellKey($thisRangeMsgList, 'tmpVar');

        if($msgListGroupByCode){
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
        }
        return $newMsgList;
    }

    private function _arrayGroupByCellKey($arr, $pickKey)
    {
        $result = array();
        foreach ($arr as $k => $v) {
            $result[$v[$pickKey]][] = $v;
        }
        return $result;
    }

    function _writeLogForFilter($content){
        $path =  __DIR__ . '/../../business/../logs/msgFilterMiddle-'.date('Y-m-d').'.log';
        file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
    }

    /**
     * 倒水
     * @param string $operateType byTime、byVolume
     */
    public function pour($operateType){
        //2.1 取值
        $pailArrJson = $this->redisClient->handle->lRange(self::PAIL_KEY, 0, 4);
        if($pailArrJson){
            //2.1.2 解析为数组
            $pailArr = [];
            if($pailArrJson){
                foreach ($pailArrJson as $itemPail){
                    $pailArr[] = json_decode($itemPail, True);
                }
            }

            //2.2 过滤(要更具业务制定过滤逻辑)
            $newMsgList = $this->_filterRoute($pailArr);
            //2.3 入列
            if($newMsgList){
                foreach ($newMsgList as $itemNewMsg){
                    $this->queueServer->push($itemNewMsg);
                }
            }
            //2.4 置空
            $this->redisClient->handle->del(self::PAIL_KEY);
            //记录日志
            $logStr = '本次处理'.sizeof($pailArr).'条binLog消息，入列'.sizeof($newMsgList).'条消息';
            $this->_writeLogForFilter($logStr);
        }
    }
}