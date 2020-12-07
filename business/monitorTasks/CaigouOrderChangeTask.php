<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-08-07
 * @version 1.0
 */

namespace business\monitorTasks;


use business\constDir\OutSource;
use business\monitor\ReportNoticeMonitor;

class CaigouOrderChangeTask extends AbTask
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
            $Id = 0;
            foreach ($columns as $key=>$column) {
                $itemCname = $column->getName();
                $itemCval =  $column->getValue();
                if($itemCname == 'PO_SourceId'){
                    $Id = $itemCval;//平台订单ID
                }
            }
        }

        $retArr['Id'] = $Id;
        $retArr['OperTypeStr'] = $operTypeStr;
        $retArr['OutSource'] = OutSource::OUTSOURCE_XH;//要做统计故先赋值为现货来源，避免被过滤掉

        //2 业务事件
        $monitor = new ReportNoticeMonitor();
        $monitor->subEvent($retArr, $confSetting);
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