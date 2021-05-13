<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-27
 * @version 1.0
 */
ini_set('display_errors', 'On');
error_reporting(E_ALL);
ini_set('date.timezone', 'Asia/Shanghai');
/**
 * @param $content
 */
function _writeLogForException($content){
    $path =  __DIR__ . '/../../logs/err-'.date('Y-m-d').'.log';
    file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.$content.PHP_EOL, FILE_APPEND);
}
/**
 * 返回当前毫秒数
 *  _writeLogForException( _msectime() . '#第一次循环结束时间');
 * @return false|float|string
 */
function _msectime() {
    return date('Ymd H:i:s', time());

    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}