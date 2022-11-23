<?php
use App\Websocket\Chat2;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

$server = IoServer::factory(
        new HttpServer(
                new WsServer(
                        new Chat2()
                )
        ), 18080
);

$server->run();