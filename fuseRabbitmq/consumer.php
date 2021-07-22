<?php
/**
 * Created by PhpStorm.
 * User: jmsite.cn
 * Date: 2019/1/15
 * Time: 13:16
 */
//声明连接参数
$config = array(
    'host' => '127.0.0.1',
    'vhost' => '/',
    'port' => 5672,
    'login' => 'guest',
    'password' => 'guest'
);
/**
 * 一、连接broker
 */
//1、连接broker
$cnn = new AMQPConnection($config);
//var_dump($config, $cnn);die;

if (!$cnn->connect()) {
    echo "Cannot connect to the broker";
    exit();
}
/**
 * 二、创建通道和交换机
 */
//在连接内创建一个通道
$ch = new AMQPChannel($cnn);
//创建一个交换机
$ex = new AMQPExchange($ch);

/**
 * 三、设置路由键和交换机名称
 */
$routingKey = 'key_1';//声明路由键
$exchangeName = 'exchange_1';//声明交换机名称
$ex->setName($exchangeName);//设置交换机名称
/**
 * 四、其他设置
 * 1 设置交换机类型
 * 2 设置交换机持久
 * 3 声明交换机
 */
$ex->setType(AMQP_EX_TYPE_DIRECT);//设置交换机类型
//(AMQP_EX_TYPE_DIRECT:直连交换机、AMQP_EX_TYPE_FANOUT:扇形交换机、AMQP_EX_TYPE_HEADERS:头交换机、AMQP_EX_TYPE_TOPIC:主题交换机)
$ex->setFlags(AMQP_DURABLE);//设置交换机持久
$ex->declareExchange();//声明交换机
/**
 * 五、创建消息队列
 * 1 创建一个消息队列
 * 2 设置队列名称
 * 3 声明消息队列
 * 4 交换机和队列通过$routingKey进行绑定
 * 5
 */
$q = new AMQPQueue($ch);//创建一个消息队列
$q->setName('queue_1');//设置队列名称
$q->setFlags(AMQP_DURABLE);//设置队列持久
$q->declareQueue();//声明消息队列
$q->bind($ex->getName(), $routingKey);//交换机和队列通过$routingKey进行绑定
/**
 * 六、接收消息
 */
//接收消息并进行处理的回调方法
function receive($envelope, $queue) {
    sleep(2);//休眠两秒，
    echo $envelope->getBody()."\n";//echo消息内容
    $queue->ack($envelope->getDeliveryTag()); //显式确认，队列收到消费者显式确认后，会删除该消息
}
$q->consume("receive");//设置消息队列消费者回调方法，并进行阻塞
//$q->consume("receive", AMQP_AUTOACK);//隐式确认,不推荐