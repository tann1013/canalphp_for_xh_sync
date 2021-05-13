<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2021-04-18
 * @version 1.0
 */
namespace xingwenge\canal_php\sample;


use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;

try {
    #客户端连接方式
    $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);

    //var_dump($client);die;


    # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);
    $client->connect("127.0.0.1", 11111); # 对应 canal.properties的配置
    $client->checkValid();
    $client->subscribe("1001", "example", ".*\\..*"); #此处1001不需要修改，example 是在canal配置文件里配置的名称
    # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤,多个数据库用逗号隔开　　 # $client->subscribe("1001", "example", "db_name.tb_name_[0-9]"); # 可以批量设置分表表名

    while (true) {
        $message = $client->get(100);
        if ($entries = $message->getEntries()) {
            foreach ($entries as $entry) {
                #此处可以处理逻辑
                Fmt::println($entry); # 返回的是一条SQL语句受影响的所有数据行
            }
        }
        sleep(1);
    }
    $client->disConnect();
} catch (\Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}