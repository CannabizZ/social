<?php

use App\Websocket\WebsocketServer;

require_once __DIR__.'/../vendor/autoload.php';

$config = [
    'host'      => '0.0.0.0',
    'port'      => 12345,
    'transport' => 'tlsv1.3',
    'ssl'       => [
        'local_cert'            => '/certs/localhost.crt',  // SSL Certificate
        'local_pk'              => '/certs/localhost.key',  // SSL Keyfile
        'disable_compression'   => true,            // TLS compression attack vulnerability
        'verify_peer'           => false,           // Set this to true if acting as an SSL client
        'ssltransport'          => 'tlsv1.3',              // Transport Methods such as 'tlsv1.1', tlsv1.2'
    ],
    'rabbit' => [
        'host' => 'node1.rabbit',
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest'
    ]
];

$server = new WebsocketServer($config);
$server->run();
