<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-07-23
 * @version 1.0
 */
namespace business\monitorTasks;

abstract class AbTask
{
    protected $description = 'className';
    abstract public function _insertProcess($operTypeStr, $columns, $confSetting);
    abstract public function _deleteProcess($operTypeStr, $columns, $confSetting);
    abstract public function _updateProcess($operType, $columnsBefore, $columnsAfter, $confSetting);
}