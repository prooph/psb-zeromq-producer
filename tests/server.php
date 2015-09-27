<?php

if (! extension_loaded('zmq')) {
    throw new RuntimeException('Requires `ext-zmq` extension to run server.');
}

$context = new ZMQContext;
$socket = new ZMQSocket($context, ZMQ::SOCKET_PULL);
$socket->bind("tcp://127.0.0.1:5555");

while ($message = $socket->recv()) {
    file_put_contents(__DIR__ . '/zmq-out.log', $message . PHP_EOL, FILE_APPEND);
}
