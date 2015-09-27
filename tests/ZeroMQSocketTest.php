<?php

namespace ProophTest\ServiceBus\Message\ZeroMQ;


use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQSocket;

class ZeroMQSocketTest extends \PHPUnit_Framework_TestCase
{
    /** @var ZeroMQSocket */
    private $zmqClient;

    /** @var \ZMQSocket */
    private $socket;

    /** @var string */
    private $dsn = 'tcp://localhost:5555';

    /** @var string */
    private $storage = __DIR__ . '/zmq-out.log';

    protected function setUp()
    {
        parent::setUp();

        $this->socket = new \ZMQSocket(new \ZMQContext, \ZMQ::SOCKET_PUSH);
        $this->zmqClient = new ZeroMQSocket($this->socket, $this->dsn);
    }

    public function tearDown()
    {
        $this->socket->disconnect($this->dsn);
    }

    /**
     * @test
     */
    public function it_sends_message()
    {
        @unlink($this->storage);
        $this->zmqClient->send($message = 'testing-123');

        $contents = trim(file_get_contents($this->storage));

        $this->assertEquals($message, $contents);
    }
}
