<?php
/**
 * 以上是生产者代码
 * 在执行之前，先关掉前面的两个消费者，
 * 打开一个命令行/终端，输入php publisher.php,
 * 由于生产者不需要阻塞，执行完进程便退出，
 * 所以现在RabbitMQ管理界面中既没有Connections也没有Channels，
 * 但是Queues已经被Exchanges投递过去了10条消息
 */

$config = array(
    'host' => '127.0.0.1',
    'vhost' => '/',
    'port' => 5672,
    'login' => 'guest',
    'password' => 'guest'
);
$cnn = new AMQPConnection($config);
if (!$cnn->connect()) {
    echo "Cannot connect to the broker";
    exit();
}
$ch = new AMQPChannel($cnn);
$ex = new AMQPExchange($ch);
//消息的路由键，一定要和消费者端一致
$routingKey = 'key_1';
//交换机名称，一定要和消费者端一致，
$exchangeName = 'exchange_1';
$ex->setName($exchangeName);
$ex->setType(AMQP_EX_TYPE_DIRECT);
$ex->setFlags(AMQP_DURABLE);
$ex->declareExchange();
//创建10个消息
for ($i=1;$i<=999;$i++){
    //消息内容
    $msg = array(
        'data'  => 'message_'.$i,
        'hello' => 'world',
    );
    //发送消息到交换机，并返回发送结果
    //delivery_mode:2声明消息持久，持久的队列+持久的消息在RabbitMQ重启后才不会丢失
    echo "Send Message:".$ex->publish(json_encode($msg), $routingKey, AMQP_NOPARAM, array('delivery_mode' => 2))."\n";
    //代码执行完毕后进程会自动退出
}