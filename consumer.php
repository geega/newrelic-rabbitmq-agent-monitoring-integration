<?php

require('vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

$vhost = '/'; // $vhost = substr($url['path'], 1);

$urlOption = getopt('u:n', ['url:', 'delaytime:', 'name:', 'noack::']);
$amqpUrl = $urlOption['u'] ??  $urlOption['url']  ??  null;
$noAck = boolval($urlOption['noack']) ?? false;
$sleep = $urlOption['delaytime'] ?? 1000;
$name = $urlOption['n'] ??  $urlOption['name'] ?? 'import';
$queue = sprintf('%s.queue', $name);

$i = 100;

if(!$amqpUrl) {
    throw new Exception('Not set connection url. Please set -u or --url (http/https)://user:password@hostname');
}

$url = parse_url($amqpUrl);

if($url['scheme'] === "amqps") {
    $ssl_opts = array(
        'capath' => '/etc/ssl/certs'
    );
    $connection = new AMQPSSLConnection($url['host'], 5671, $url['user'], $url['pass'], $vhost, $ssl_opts);
} else {
    $connection = new AMQPStreamConnection($url['host'], 5672, $url['user'], $url['pass'], $vhost);
}
$channel = $connection->channel();

// set 1 for prefetch size
$channel->basic_qos(
    null,   #prefetch size - prefetch window size in octets, null meaning "no specific limit"
    1,      #prefetch count - prefetch window in terms of whole messages
    null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
);

$callback = function ($msg) use ($sleep, $noAck, $channel, $connection, &$i) {
    echo ' [x] Received ', $msg->body, "\n";
    $json = $msg->body;
    $data = json_decode($json, true);

    $ts = $data['ts'];
    $time = time();
    $diffTime = $time - $ts;

    echo "ts: ${ts} diff sec: ${diffTime}".PHP_EOL;

    if($noAck) {
        $channel->close();
        $connection->close();
        exit;
    }

    echo 'delay time: '.$sleep.PHP_EOL;
    usleep($sleep * 1000);

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume($queue, '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
