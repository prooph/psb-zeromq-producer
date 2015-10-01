<?php

namespace ProophTest\ServiceBus\Message\ZeroMQ;

use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQSocket;

class ZeroMQSocketTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_sends_message()
    {
        $socket = new \ZMQSocket(new \ZMQContext, \ZMQ::SOCKET_PUSH);
        $storage = __DIR__ . '/zmq-out.log';
        $zmqClient = new ZeroMQSocket($socket, 'tcp://localhost:5555');

        @unlink($storage);
        $zmqClient->send($message = 'testing-123');

        sleep(5);
        $contents = trim(file_get_contents($storage));

        $this->assertEquals($message, $contents);

        $socket->disconnect('tcp://localhost:5555');
    }

    /**
     * @test
     */
    public function it_sends_message_and_gets_response()
    {
        $socket = new \ZMQSocket(new \ZMQContext, \ZMQ::SOCKET_REQ);
        $zmqClient = new ZeroMQSocket($socket, 'tcp://localhost:5556');

        $zmqClient->send($message = 'testing-123');

        $this->assertEquals($message, $zmqClient->receive());

        $socket->disconnect('tcp://localhost:5556');
    }
}
