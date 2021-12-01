<?php
require('vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

$vhost = '/'; // // $vhost = substr($url['path'], 1);

$maxMessages = 10000;
$period = 60;
$time = time();


$urlOption = getopt('u:n', ['url:', 'delaytime:', 'name:']);
$amqpUrl = $urlOption['u'] ??  $urlOption['url']  ??  null;
$stepTime = $urlOption['delaytime'] ?? 1000;
$name = $urlOption['n'] ??  $urlOption['name'] ?? 'import';
$exchange = sprintf('%s.exchange', $name);
$queue =  sprintf('%s.queue', $name);
$routeKey = sprintf('%s.key', $name);

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

$channel->exchange_declare($exchange, 'direct', false, false, false);
$channel->queue_declare($queue, false, false, false, false);
$channel->queue_bind($queue, $exchange, $routeKey);

$i = 0;
$countMessagePeriod = 0;
while ($i < $maxMessages) {
    $json =  json_encode(
        [
            'name' => bin2hex(random_bytes(3)),
            'ts' => time(),
            'mts' => round(microtime(true) * 1000)
        ]
    );


    $msg = new AMQPMessage($json);
    $channel->basic_publish($msg, $exchange, $routeKey);
    $i++;
    $countMessagePeriod++;

    if ($time  + $period <= time()) {
        echo sprintf('Sended messages count %d by last %d seconds', $countMessagePeriod, $period).PHP_EOL;
        $time = time();
        $countMessagePeriod = 0;
    }
    usleep($stepTime * 1000);
}

$channel->close();
$connection->close();
