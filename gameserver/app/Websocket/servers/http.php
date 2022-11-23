<?php
// Your shell script
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use App\Websocket\Chat2;
require __DIR__ . '/../../../vendor/autoload.php';

$http = new HttpServer(new Chat2, 18080);

$server = IoServer::factory($http);
$server->run();