<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-07-23
 * @version 1.0
 */

namespace xingwenge\canal_php;


use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use function Couchbase\defaultDecoder;


class BusinessTask
{
    /**
     * @param $entry
     * @throws \Exception
     */
    public static function getEventBaseDetail($entry){
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                return;
                break;
        }
        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $header = $entry->getHeader();
        //echo sprintf("================> binlog[%s : %d],name[%s,%s], eventType: %s", $header->getLogfileName(), $header->getLogfileOffset(),
        // $header->getSchemaName(), $header->getTableName(), $header->getEventType()), PHP_EOL;
        $detail['db'] = $header->getSchemaName();
        $detail['table'] = $header->getTableName();
        $detail['type'] = $header->getEventType();
        return $detail;
    }

    /**
     * @param $db
     * @param $table
     * @param $type
     */
    public static function _getTaskByEventDetail($db, $table, $dbTaskMapps){
        $task = '';
        $dbStr = $db .'.'. $table;
        if(isset($dbTaskMapps[$dbStr])){
            $task = $dbTaskMapps[$dbStr];
        }
        $taskClass = !empty($task) ? '\business\monitorTasks\\'. $task : '';
        return $taskClass;
    }

    /**
     * @param $entry
     * @param $confSetting
     * @param $taskClass DispatchChangeTask
     * @throws \Exception
     */
    public static function run($entry, $confSetting, $dbTaskMapps){
        self::_writeLog('entry类型：'.$entry->getEntryType());
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                self::_writeLog('entry类型：'.$entry->getEntryType().'被忽略掉了');
                return;
                break;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $evenType = $rowChange->getEventType();
        $header = $entry->getHeader();

        //@todo 实例化对应的TASK
        $taskClass = self::_getTaskByEventDetail($header->getSchemaName(), $header->getTableName(), $dbTaskMapps);
        self::_writeLog('操作需要处理任务类：'.$taskClass);
        if(!class_exists($taskClass)){
            self::_writeLog('但是该处理任务类['.$taskClass.']不存在。');
            return;
        }
        $taskObj = new $taskClass;//new \business\monitorTasks\DispatchChangeTask()
        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            //print_r($rowData->getBeforeColumns());die;
            //echo sprintf("%s : %s  update= %s", $column->getName(), $column->getValue(), var_export($column->getUpdated(), true)), PHP_EOL;
            //file_put_contents('/Users/tanjian/web/zhongjian_projects/java_projects/canal_project/canal-php/business/logs/logTwo.log', print_r($rowData->getAfterColumns(), true).PHP_EOL, FILE_APPEND);

            switch ($evenType) {
                case EventType::DELETE:
                    //self::ptColumn($rowData->getBeforeColumns());
                    //@todo 【delete事件】处理
                    $taskObj->_deleteProcess('delete', $rowData->getBeforeColumns(), $confSetting);
                    break;
                case EventType::INSERT:
                    //self::ptColumn($rowData->getAfterColumns());
                    //@todo 【insert事件】处理
                    $taskObj->_insertProcess('insert', $rowData->getAfterColumns(), $confSetting);
                    break;
                default:
                    //echo '-------> before', PHP_EOL;
                    //self::ptColumn($rowData->getBeforeColumns());
                    //echo '-------> after', PHP_EOL;
                    //self::ptColumn($rowData->getAfterColumns());
                    //@todo 【update事件】处理
                    $taskObj->_updateProcess('update', $rowData->getBeforeColumns(), $rowData->getAfterColumns(), $confSetting);
                    break;
            }
        }
    }

    private static function ptColumn($columns) {
        /** @var Column $column */
        foreach ($columns as $column) {
            echo sprintf("%s : %s  update= %s", $column->getName(), $column->getValue(), var_export($column->getUpdated(), true)), PHP_EOL;
        }
    }

    public static function  _writeLog($content){
        $path =  __DIR__ . '/../logs/events-'.date('Y-m-d').'.log';
        file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
    }

}