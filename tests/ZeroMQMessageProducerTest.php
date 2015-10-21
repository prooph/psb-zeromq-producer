<?php

namespace ProophTest\ServiceBus\Message\ZeroMQ;

use Prophecy\Argument;
use Prooph\Common\Messaging\DomainMessage;
use ProophTest\ServiceBus\Mock\DoSomething;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQSocket;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQMessageProducer;
use React\Promise\Deferred;

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
        $this->zmqClient->handlesDeferred()->willReturn(false);
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
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_runtime_exception_when_request_deferred_and_not_using_rpc()
    {
        $this->zmqClient->handlesDeferred()->willReturn(false)->shouldBeCalled();
        $this->zmqClient->receive()->shouldNotBeCalled();

        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);
        $zmqMessageProducer($doSomething, $this->prophesize(Deferred::class)->reveal());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_runtime_exception_when_request_is_not_deferred_and_using_rpc()
    {
        $this->zmqClient->handlesDeferred()->willReturn(true)->shouldBeCalled();

        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);
        $zmqMessageProducer($doSomething);
    }

    /**
     * @test
     */
    public function it_can_handle_rpc()
    {
        $this->zmqClient->handlesDeferred()->willReturn(true)->shouldBeCalled();
        $this->zmqClient->receive()->willReturn($response = 'Hello World')->shouldBeCalled();

        $deferred = $this->prophesize(Deferred::class);
        $deferred->resolve($response)->shouldBeCalled();

        $zmqMessageProducer = $this->zmqMessageProducer;
        $doSomething = new DoSomething(['data' => 'test command']);


        $this->zmqClient
            ->send($this->validate_message_body($doSomething), \ZMQ::MODE_NOBLOCK)
            ->willReturn(null)
            ->shouldBeCalled();

        $zmqMessageProducer($doSomething, $deferred->reveal());
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
