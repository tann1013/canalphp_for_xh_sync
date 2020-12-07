<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-08-14
 * @version 1.0
 */

class SendDDNotice
{
    /**
     * @param $Phone
     * @param $Tpl
     */
    public function ddRobotSendForCanal($tmpMsg, $type){
        $content = '【CanalPhp服务异常】>>'.$tmpMsg.'('.$type.')';

        $testJson = json_encode(
            array('content' => $content)
        );
        $Params = array(
            'msgtype' => "text",
            'text' => $testJson,
        );
        $Header = array();
        $Url = 'https://oapi.dingtalk.com/robot/send?access_token=90ca82008908e07244d48920a846fb3d285671672eec4fc2f89a99521abc9e22';
        $this->_curlPost($Url, $Params, $Header);

    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     */
    private function _curlPost($url, $params, $hearder = array()){
        //配置信息
        //$url = $_config['baseUrl'].$url;
        $postStr = json_encode($params);//转出json字符串
        $requestHeader = array(
            //'Token:' . $hearder['Token'],
            //'X-Tsign-Open-App-Id:' . $_config['appId'],
            //'X-Tsign-Open-App-Secret:' . $_config['appSecret'],
            'Content-Type:' . 'application/json'
        );
        /**
         * curl
         */
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, false); // 输出HTTP头 true
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr);// post传输数据
        curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeader);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

}