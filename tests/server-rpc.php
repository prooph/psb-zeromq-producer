<?php

if (! extension_loaded('zmq')) {
    throw new RuntimeException('Requires `ext-zmq` extension to run server.');
}

$context = new ZMQContext;
$socket = new ZMQSocket($context, ZMQ::SOCKET_REP);
$socket->bind('tcp://127.0.0.1:5556');

echo "ZMQ Stub Server Started.";

while ($message = $socket->recv()) {
    $socket->send($message);
}
