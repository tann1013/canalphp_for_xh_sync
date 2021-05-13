<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2021-05-13
 * @version 1.0
 */
namespace xingwenge\canal_php\sample;

use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;


///Users/tanjian/web/woniuStudioProjects/canalphp_for_xh_sync/pub
//var_dump(__DIR__);die;//
require_once __DIR__. '/../vendor/autoload.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL);

try {
    $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
    # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

    $client->connect("127.0.0.1", 11111);
    $client->checkValid();
    $client->subscribe("1001", "example", ".*\\..*");
    # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

    while (true) {
        $message = $client->get(100);
        if ($entries = $message->getEntries()) {
            foreach ($entries as $entry) {
                Fmt::println($entry);
            }
        }
        sleep(1);
    }

    $client->disConnect();
} catch (\Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}