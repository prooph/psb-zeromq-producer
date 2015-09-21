<?php

namespace Prooph\ServiceBusTest;

use ZMQSocket;
use Mockery as m;
use Prooph\Common\Messaging\DomainMessage;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQMessageProducer;

class ZeroMQMessageProducerTest extends TestCase
{
    /** @var ZMQSocket|m\MockInterface */
    private $zmqClient;

    /** @var NoOpMessageConverter */
    private $messageConverter;

    /** @var ZeroMQMessageProducer */
    private $zmqMessageProducer;

    protected function setUp()
    {
        parent::setUp();

        $this->zmqClient = m::mock(ZMQSocket::class);
        $this->messageConverter = new NoOpMessageConverter;
        $this->zmqMessageProducer = new ZeroMQMessageProducer($this->zmqClient, $this->messageConverter, 'test:dsn');
    }

    /**
     * @test
     */
    public function it_sends_message_as_a_json_encoded_string()
    {
        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);

        $this
            ->zmqClient
            ->shouldReceive('send')
            ->with($this->validate_message_body($doSomething))
            ->once()
            ->andReturnNull();

        $zmqMessageProducer($doSomething);
    }

    /**
     * @test
     */
    public function it_only_manually_connects_if_dsn_not_connected_to()
    {
        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);

        $this->add_connections(['test:dsn']);

        $this->zmqClient->shouldNotReceive('connect')->with('test:dsn');
        $this->zmqClient->shouldReceive('send')->andReturnNull();

        $zmqMessageProducer($doSomething);
    }

    /**
     * @param DomainMessage $command
     * @return m\Matcher\Closure
     */
    protected function validate_message_body($command)
    {
        return m::on(function ($actual) use ($command) {
            $messageData = $this->messageConverter->convertToArray($command);
            $messageData['created_at'] = $command->createdAt()->format('Y-m-d\TH:i:s.u');
            $expected = json_encode($messageData);

            return $expected === $actual;
        });
    }

    /**
     * @param array $dataSourceNames
     */
    protected function add_connections(array $dataSourceNames)
    {
        $this->zmqClient->shouldReceive('getEndpoints')->andReturn($dataSourceNames);
    }
}