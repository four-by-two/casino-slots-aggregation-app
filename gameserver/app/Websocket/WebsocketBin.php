<?php

namespace App\Websocket;
use App\Websocket\StatusApplication;

class WebsocketBin
{
    protected $ws_host;
    public $logger;
    public function __construct()
    {
        $this->ws_host = 
        
    }

    public function kernel() {
        return new \Wrench\Server($this->ws_host, [ //ws://127.0.0.1:8020/
            'logger' => $this->log(),
            'allowed_origins' => [
                'mysite.localhost',
            ],
        ]);
    }

    public function log($level, $message, array $context = []) {
        $format = sprintf('[%s] %s - %s', $level, $message, json_encode($context)) . PHP_EOL;
        save_log('Websocket', $format);
        echo $format;
    }

public function start()
{
        $logger = new class extends \Psr\Log\AbstractLogger implements Psr\Log\LoggerInterface
        {
            public function log($level, $message, array $context = [])
            {
                echo sprintf('[%s] %s - %s', $level, $message, json_encode($context)) . PHP_EOL;
            }
        };
        $app = new class implements \Wrench\Application\DataHandlerInterface
        {
            public function onData(string $data, \Wrench\Connection $connection): void
            {
                $connection->send($data);
            }
        };
        
        $server->setLogger($logger);
        $server->registerApplication('echo', $app);
        $server->registerApplication('status', new StatusApplication());
        $server->run();
}
}