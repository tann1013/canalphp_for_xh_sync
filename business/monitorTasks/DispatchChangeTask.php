<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-07-23
 * @version 1.0
 */
namespace business\monitorTasks;

use business\constDir\OutSource;
use business\monitor\DispatchChangeMonitor;
use business\monitor\ReportNoticeMonitor;

class DispatchChangeTask extends AbTask
{
    /**
     * @param $operTypeStr
     * @param $columns
     * @param $confSetting
     */
    private function _mainProcess($operTypeStr, $columns, $confSetting){
        //1 组装参数
        $retArr = array();
        $Id = 0;
        foreach ($columns as $key=>$column) {
            $itemCname = $column->getName();
            $itemCval =  $column->getValue();
            if($itemCname == 'CLD_Code'){
                $Id = $itemCval;
            }
            //新增运单来源CLD_OutSource
            if($itemCname == 'CLD_OutSource'){
                $OutSource = $itemCval;
            }
        }

        $retArr['Id'] = $Id;
        //额外字段
        $retArr['OutSource'] = $OutSource;
        $retArr['OperTypeStr'] = $operTypeStr;

        //2 业务事件
        $monitor = new DispatchChangeMonitor();
        $monitor->subEvent($retArr, $confSetting);
    }

    /**
     * @param $operTypeStr
     * @param $columnsBefore
     * @param $columnsAfter
     * @param $confSetting
     */
    private function _mainProcessForColumn($operTypeStr, $columnsBefore, $columnsAfter, $confSetting){
        /**
         * 计算是否有更新运费和数量这两个字段
         */
        if($operTypeStr=='update'){
            //一、组装参数
            $retArr = array();
            $Id = 0;

            $FactPriceBefore = 0;
            $NumBefore = 0;
            foreach ($columnsBefore as $key=>$column) {
                $itemCname = $column->getName();
                $itemCval =  $column->getValue();

                if($itemCname == 'CLD_FactPrice'){
                    $FactPriceBefore = $itemCval;
                }
                if($itemCname == 'CLD_Num'){
                    $NumBefore = $itemCval;
                }
            }

            $FactPriceAfter = 0;
            $NumAfter = 0;
            foreach ($columnsAfter as $key=>$column) {
                $itemCname = $column->getName();
                $itemCval =  $column->getValue();

                if($itemCname == 'CLD_Code'){
                    $Id = $itemCval;
                }

                if($itemCname == 'CLD_FactPrice'){
                    $FactPriceAfter = $itemCval;
                }

                if($itemCname == 'CLD_Num'){
                    $NumAfter = $itemCval;
                }

                //新增运单来源CLD_OutSource
                if($itemCname == 'CLD_OutSource'){
                    $OutSource = $itemCval;
                }
            }

            //二、业务处理
            $retArr['Id'] = $Id;
            $retArr['OperTypeStr'] = $operTypeStr;
            $retArr['OutSource'] = $OutSource;

            if($FactPriceBefore!==$FactPriceAfter || $NumBefore!==$NumAfter ){
                //触发报表变更
                //@todo 运费和数量更新才通知报表(CLD_FactPrice、CLD_Num)
                $monitor = new ReportNoticeMonitor();
                $monitor->subEvent($retArr, $confSetting);
            }
        }
    }

    /**
     * 更新数据处理
     *
     * @param $operType
     * @param $columnsBefore
     * @param $columnsAfter
     */
    public function _updateProcess($operTypeStr, $columnsBefore, $columnsAfter, $confSetting){
        $this->_mainProcessForColumn($operTypeStr, $columnsBefore, $columnsAfter, $confSetting);

        $this->_mainProcess($operTypeStr, $columnsAfter, $confSetting);
    }

    /**
     * @param $operType
     * @param $columns
     */
    public function _deleteProcess($operTypeStr, $columns, $confSetting){
        $this->_mainProcess($operTypeStr, $columns, $confSetting);
    }

    /**
     * @param $operType
     * @param $columns
     */
    public function _insertProcess($operTypeStr, $columns, $confSetting){
        $this->_mainProcess($operTypeStr, $columns, $confSetting);
    }
}