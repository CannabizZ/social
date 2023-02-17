<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__.'/../vendor/autoload.php';

$userId = $argv[1] ?? null;
if (empty($userId)) {
    echo 'empty userId'.PHP_EOL;
    die();
}
$message = $argv[2] ?? null;
if (empty($message)) {
    echo 'empty message'.PHP_EOL;
    die();
}

$connection = new AMQPStreamConnection('node1.rabbit', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('news', false, false, false, false);

$msg = new AMQPMessage(json_encode([
    'user_id' => (int) $userId,
    'message' => (string) $message
]));
$channel->basic_publish($msg, 'amq.topic', 'user:' . $userId);

$channel->close();
$connection->close();

