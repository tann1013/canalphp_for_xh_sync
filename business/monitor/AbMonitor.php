<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-07-21
 * @version 1.0
 */

namespace business\monitor;


abstract class AbMonitor
{
    abstract public function subEvent($params, $paramsTwo);
    abstract public function requestApi($params, $paramsTwo);

    const OPER_TYPE_MAPPS = array(
        //操作类型 1 新增 2 修改 3删除
        'insert' => 1,
        'update' => 2,
        'delete' => 3,
    );

    /**
     * @param $retArr
     * @param $class
     */
    public function writeLog($retArr, $class){
        $retArr['ClassName'] = $class;

        $path =  __DIR__ . '/../../logs/business-'.date('Y-m-d').'.log';
        file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.var_export($retArr, true).PHP_EOL, FILE_APPEND);
    }
}