<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-09
 * @version 1.0
 */

class SwooleClient
{

    public $host = '127.0.0.1';
    public $port = '9501';


    /**
     * 客户端发送数据
     *
     * @param $data
     */
    public function _sendSwooleData($data, $protocol = SWOOLE_SOCK_TCP){
        $fp = fsockopen("tcp://" . $this->host, $this->port, $errno, $errstr);
        $this->_writeLogForSwoole("连接任务处理服务器：tcp://" . $this->host . ':' . $this->port);
        if (!$fp) {
            $this->_writeLogForSwoole("ERROR: $errno - $errstr");
            return false;
        }
        fwrite($fp, $data);
        fclose($fp);
        return true;
    }

    /**
     * @param $content
     */
    private function _writeLogForSwoole($content){
        $path =  __DIR__ . '/../../logs/swoole-'.date('Y-m-d').'.log';
        file_put_contents($path, ''.date('Y-m-d H:i:s', time()).' '.'SwooleService#'.$content.PHP_EOL, FILE_APPEND);
    }

    /**
     * @return bool
     */
    public function _checkService(){
        /*
        $fp = fsockopen("tcp://" . $this->host, $this->port, $errno, $errstr);
        $this->_writeLogForSwoole("连接任务处理服务器：tcp://" . $this->host . ':' . $this->port);
        if (!$fp) {
            $this->_writeLogForSwoole("ERROR: $errno - $errstr");
            return false;
        }
        fclose($fp);
        */
        return true;
    }
}