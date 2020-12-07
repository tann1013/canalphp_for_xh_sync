<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-08-10
 * @version 1.0
 */

namespace business\monitorTasks;


use business\constDir\OutSource;
use business\monitor\OrderChangeMonitor;
use business\monitor\ReportNoticeMonitor;

class OrderChangeTask extends AbTask
{
    /**
     * @param $operTypeStr
     * @param $columns
     * @param $confSetting
     */
    private function _mainProcess($operTypeStr, $columns, $confSetting){
        //新增或修改
        if($operTypeStr=='update' || $operTypeStr=='insert'){
            //1 组装参数
            $retArr = array();
            $LiId = 0;
            $Status = 0;
            $LogisticId = 0;
            $LogisticName = '';

            foreach ($columns as $key=>$column) {
                $itemCname = $column->getName();
                $itemCval =  $column->getValue();
                if($itemCname == 'LO_Code'){
                    $LiId = $itemCval;//平台订单ID
                }

                if($itemCname == 'LO_Status'){
                    $Status = $itemCval;//平台订单ID
                }

                if($itemCname == 'LO_LC_Id'){
                    $LogisticId = $itemCval;//平台订单ID
                }

                if($itemCname == 'LO_LC_Name'){
                    $LogisticName = $itemCval;//平台订单ID
                }

                //新增LO_OutSource区分平台
                if($itemCname == 'LO_OutSource'){
                    $outSource = $itemCval;//平台订单ID
                }

            }
        }

        $retArr['LiId'] = $LiId;
        $retArr['Status'] = $Status;
        $retArr['LogisticId'] = $LogisticId;
        $retArr['LogisticName'] = $LogisticName;
        //额外参数
        $retArr['OperTypeStr'] = $operTypeStr;
        $retArr['OutSource'] = $outSource;

        //2 业务事件
        //2.1 通知订单
        $monitor = new OrderChangeMonitor();
        $monitor->subEvent($retArr, $confSetting);

        //2.2 通知报表
        $retArrV2['Id'] = $LiId;
        $retArrV2['OperTypeStr'] = $operTypeStr;
        $retArrV2['OutSource'] = $outSource;
        $reportMonitor = new ReportNoticeMonitor();
        $reportMonitor->subEvent($retArrV2, $confSetting);
    }

    /**
     * 更新数据处理
     *
     * @param $operType
     * @param $columnsBefore
     * @param $columnsAfter
     */
    public function _updateProcess($operTypeStr, $columnsBefore, $columnsAfter, $confSetting){
        $this->_mainProcess($operTypeStr, $columnsAfter, $confSetting);
    }

    /**
     * @param $operType
     * @param $columns
     */
    public function _deleteProcess($operTypeStr, $columns, $confSetting){
        //$this->_mainProcess($operTypeStr, $columns, $confSetting);
    }

    /**
     * @param $operType
     * @param $columns
     */
    public function _insertProcess($operTypeStr, $columns, $confSetting){
        $this->_mainProcess($operTypeStr, $columns, $confSetting);
    }
}