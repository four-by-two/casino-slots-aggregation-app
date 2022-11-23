<?php
namespace App\Websocket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\CloseResponseTrait;
use Ratchet\Http\HttpRequestParser;
use Psr\Http\Message\RequestInterface;
class Chat implements MessageComponentInterface {
    protected $clients;
    protected $_i;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->casinodog_adapter = new \App\Http\Controllers\Casinodog\WebsocketAdapter;
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $this->_i++;
        $id = $conn->resourceId;
        $ip = $conn->remoteAddress;
        $connArray = collect($conn)->toArray();
        $message = array(
                "event" => "onOpen",
                "connection_id" => $id,
                "connection_ip" => $ip,
                "httpHeadersReceived"  => $conn->httpHeadersReceived,
                "httpRequest" => $conn->httpRequest,
                "headersReceived" => $connArray,
        );
        echo json_encode($message) . "\n";
        $this->sendMessage($message, "methodName", $conn);

        $this->clients->attach($conn);
    }

    public function buildMessage($eventLabel, $fromLabel, $messageLabel, $originLabel)
    {
        $message = array(
                "event" => $eventLabel,
                "from" => $fromLabel,
                "message"  => $messageLabel,
                "origin" => $originLabel,
        );

        return $message;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
            $numRecv = count($this->clients) - 1;
            echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
            
            if(str_contains($msg, 'register:')) {
                $parent_session = explode(":", $msg)[1];
                $register = $this->casinodog_adapter->register($from->resourceId, $parent_session);
                $buildMessage = $this->buildMessage("ws_registration", $from->resourceId, $register, "server");
                $this->sendMessage($buildMessage, "server_response", $from);
            } else {
                $message = $this->buildMessage("onMessage", $from->resourceId, $msg, "client");
                $this->sendMessage($message, "client_message", "all");
            }

    }

    public function sendMessage($msg, $method, $receiver) {
            if($receiver !== "all") {
                $receiver = $receiver;
                $receiverName = $receiver->resourceId;
            } else {
                $receiver = "all";
                $receiverName = "all";
            }

            $message = array(
                "message" => $msg,
                "method" => $method,
                "receiver" => $receiverName,
            );
            $json_message = json_encode($message);

            if($receiver === "all") {
                foreach ($this->clients as $client) {
                    $client->send($json_message);
                    echo "Sent: {$json_message} to connection ID: {$client->resourceId}"  . "\n\n";
                }
            } else {
                foreach ($this->clients as $client) {
                    if ($receiver === $client) {
                        $client->send($json_message);
                        echo "Sent: {$json_message} to connection ID {$receiverName}"  . "\n\n";
                    }
                }
            }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo $e->getMessage();
        $conn->close();
    }
}

