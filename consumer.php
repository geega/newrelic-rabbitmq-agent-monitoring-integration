<?php

require('vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

$vhost = '/';
$queue = 'import.queue';

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

$channel->basic_qos(
    null,   #prefetch size - prefetch window size in octets, null meaning "no specific limit"
    1,      #prefetch count - prefetch window in terms of whole messages
    null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
);

$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
    usleep(200000);
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume($queue, '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
