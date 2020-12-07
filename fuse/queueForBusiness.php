<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-09-19
 * @version 1.0
 */

require_once __DIR__ . '/../business/service/MsgConsumeService.php';

$msgCs = new MsgConsumeService();
var_dump($msgCs->run());
