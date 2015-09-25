<?php

namespace ProophTest\ServiceBus\Message\ZeroMQ;

use Prophecy\Argument;
use Prooph\Common\Messaging\DomainMessage;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQSocket;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQMessageProducer;

class ZeroMQMessageProducerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ZeroMQSocket */
    private $zmqClient;

    /** @var NoOpMessageConverter */
    private $messageConverter;

    /** @var ZeroMQMessageProducer */
    private $zmqMessageProducer;

    protected function setUp()
    {
        parent::setUp();

        $this->zmqClient = $this->prophesize(ZeroMQSocket::class);
        $this->messageConverter = new NoOpMessageConverter;
        $this->zmqMessageProducer = new ZeroMQMessageProducer($this->zmqClient->reveal(), $this->messageConverter);
    }

    /**
     * @test
     */
    public function it_sends_message_as_a_json_encoded_string()
    {
        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);

        $this->zmqClient
            ->send($this->validate_message_body($doSomething), \ZMQ::MODE_NOBLOCK)
            ->willReturn(null)
            ->shouldBeCalled();

        $zmqMessageProducer($doSomething);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_throws_runtime_exception_when_request_deferred()
    {
        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);
        $zmqMessageProducer($doSomething, $this->prophesize(\React\Promise\Deferred::class)->reveal());
    }

    /**
     * @param DomainMessage $command
     * @return \Prophecy\Argument\Token\CallbackToken
     */
    protected function validate_message_body($command)
    {
        return Argument::that(function ($actual) use ($command) {
            $messageData = $this->messageConverter->convertToArray($command);
            $messageData['created_at'] = $command->createdAt()->format('Y-m-d\TH:i:s.u');
            $expected = json_encode($messageData);

            return $expected === $actual;
        });
    }
}
