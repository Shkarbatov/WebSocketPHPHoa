<?php

require __DIR__ . '/vendor/autoload.php';

use Hoa\Websocket\Server as WebsocketServer;
use Hoa\Socket\Server as SocketServer;
use Hoa\Event\Bucket;

$subscribedTopics = array();

// =================================

$websocket_web = new WebsocketServer(
    new SocketServer('ws://127.0.0.1:8008')
);

$websocket_web->on('open', function (Bucket $bucket) use (&$subscribedTopics) {
    $subscribedTopics[substr($bucket->getSource()->getRequest()->getUrl(), 7)] =
        ['bucket' => $bucket, 'node' => $bucket->getSource()->getConnection()->getCurrentNode()];
});

$websocket_web->on('message', function (Bucket $bucket) use (&$subscribedTopics) {
    $bucket->getSource()->send('Socket connected');
});

$websocket_web->on('close', function (Bucket $bucket) { });

// =================================

$websocket_php = new WebsocketServer(
    new SocketServer('ws://127.0.0.1:8009')
);

$websocket_php->on('open', function (Bucket $bucket) { });

$websocket_php->on('message', function (Bucket $bucket) use (&$subscribedTopics) {

    $data = json_decode($bucket->getData()['message'], true);

    if (isset($data['user']) and isset($subscribedTopics[$data['user']])) {
        echo 'message received ' . $data['command'];

        $subscribedTopics[$data['user']]['bucket']->getSource()->send(
            $data['command'],
            $subscribedTopics[$data['user']]['node']
        );
    }
});

// =================================

$group     = new Hoa\Socket\Connection\Group();
$group[]   = $websocket_web;
$group[]   = $websocket_php;

$group->run();
