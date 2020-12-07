<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-11
 * @version 1.0
 */

namespace business\monitorTasks;


use business\constDir\OutSource;
use business\monitor\ReportNoticeWithContractMonitor;

class ContractForPurchaseChangeTask extends AbTask
{
    /**
     * @param $operTypeStr
     * @param $columns
     * @param $confSetting
     */
    private function _mainProcess($operTypeStr, $columns, $confSetting){
        //新增或修改
        //1 组装参数
        $retArr = array();
        $Id = 0;
        foreach ($columns as $key=>$column) {
            $itemCname = $column->getName();
            $itemCval =  $column->getValue();
            if($itemCname == 'PC_ContractNo'){
                $ContractNo = $itemCval;//合同编号
            }
            if($itemCname == 'PC_TaxRate'){
                $TaxRate = $itemCval;//合同编号
            }
            if($itemCname == 'PC_SellerId'){
                $SellerId = $itemCval;//PC_SellerId#卖方id
            }
            if($itemCname == 'PC_SellerName'){
                $SellerName = $itemCval;//PC_SellerName#卖方名称
            }
        }

        //组装参数
        $retArr['ContractNo'] = $ContractNo;
        $retArr['ContractType'] = 'Purchase';
        $retArr['TaxRate'] = $TaxRate;
        $retArr['SellerId'] = $SellerId;
        $retArr['SellerName'] = $SellerName;
        //other
        $retArr['OperTypeStr'] = $operTypeStr;
        $retArr['OutSource'] = OutSource::OUTSOURCE_XH;//要做统计故先赋值为现货来源，避免被过滤掉

        //2 业务事件
        $monitor = new ReportNoticeWithContractMonitor();
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