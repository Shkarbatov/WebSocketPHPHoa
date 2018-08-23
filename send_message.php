<?php

require __DIR__ . '/vendor/autoload.php';

$client = new Hoa\Websocket\Client(
    new Hoa\Socket\Client('tcp://127.0.0.1:8009')
);

$client->setHost('127.0.0.1');
$client->connect();

$client->send(json_encode(['user' => 'tester01', 'command' => '111111']));
$client->close();