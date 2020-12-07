<?php
/**
 * 消息消费
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-19
 * @version 1.0
 */

class MsgConsumeService
{
    public function __construct()
    {

        $this->queue = new QueueService();//pushForMsgConsumeService
        $this->curl = new \business\monitor\CurlTools();

    }

    /**
     * @param $msgs
     * @param $confSetting
     */
    public function mainProcess($msgs, $confSetting){
        //1 消息处理
        $this->_handle($msgs, $confSetting);
        return sizeof($msgs);
    }

    /**
     * 消息过滤
     * @param $thisRangeMsgList
     */
    private function _filterWithBusiness($thisRangeMsgList){
    }

    /**
     * 消息处理
     * @param $newMsgList
     */
    private function _handle($newMsgList, $confSetting){
        foreach ($newMsgList as $itemMsg){
            $method = $itemMsg['method'];
            $params = json_decode($itemMsg['paramsJson'], TRUE);
            $monitorClass = $itemMsg['monitorClass'];
            //把tmpVar赋值到params数组
            $params['tmpVar'] = $itemMsg['tmpVar'];
            $this->curl->curlPostWithApiFinal($method , $params, $confSetting, $monitorClass);
        }
    }

    private function _arrayGroupByCellKey($arr, $pickKey)
    {
        $result = array();
        foreach ($arr as $k => $v) {
            $result[$v[$pickKey]][] = $v;
        }
        return $result;
    }
}