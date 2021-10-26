<?php
require('vendor/autoload.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

$vhost = '/';
$exchange = 'import.exchange';
$queue = 'import.queue';
$routeKey = 'import.key';
$sleep = 150;
$maxMessages = 10000;

$urlOption = getopt('u:', ['url:']);
$amqpUrl = null;

if(isset($urlOption['u'])) {
 $amqpUrl = $urlOption['u'];
}
if(isset($urlOption['url'])) {
    $amqpUrl = $urlOption['url'];
}

if(!$amqpUrl) {
    throw new Exception('Not set connection url. Please set -u or --url (http/https)://user:password@hostname');
}

$url = parse_url($amqpUrl);
// $vhost = substr($url['path'], 1);

if($url['scheme'] === "amqps") {
    $ssl_opts = array(
        'capath' => '/etc/ssl/certs'
    );
    $connection = new AMQPSSLConnection($url['host'], 5671, $url['user'], $url['pass'], $vhost, $ssl_opts);
} else {
    $connection = new AMQPStreamConnection($url['host'], 5672, $url['user'], $url['pass'], $vhost);
}

$channel = $connection->channel();

$channel->exchange_declare($exchange, 'direct', false, false, false);
$channel->queue_declare($queue, false, false, false, false);
$channel->queue_bind($queue, $exchange, $routeKey);

$i = 0;
while ($i < $maxMessages) {
    $msg = new AMQPMessage(time() . '-' .microtime());
    $channel->basic_publish($msg, $exchange, $routeKey);
    $i++;
    usleep($sleep * 1000);
}

$channel->close();
$connection->close();
