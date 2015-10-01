<?php

namespace ProophTest\ServiceBus\Message\ZeroMQ\Container;

use Interop\Container\ContainerInterface;
use Prooph\ServiceBus\Message\ZeroMQ\Container\ZeroMQMessageProducerFactory;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQMessageProducer;

class ZeroMQMessageProducerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface */
    private $container;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @test
     */
    public function it_will_get_configuration()
    {
        $this->container->get('config')->willReturn([
            'prooph' => ['zeromq_producer' => []],
        ])->shouldBeCalled();

        $factory = new ZeroMQMessageProducerFactory;
        $factory($this->container->reveal());
    }

    /**
     * @test
     */
    public function result_it_zmq_producer()
    {
        $this->assertInstanceOf(ZeroMQMessageProducer::class, $this->make());
    }

    /**
     * @test
     */
    public function it_uses_default_default_dsn()
    {
        $result = $this->make();
        $socket = $result->getSocket();

        $this->assertEquals(ZeroMQMessageProducerFactory::DEFAULT_DSN, $socket->getDsn());
    }

    /**
     * @test
     */
    public function it_uses_overridden_default_dsn()
    {
        $result = $this->make('tcp://prooph:9000');
        $socket = $result->getSocket();

        $this->assertEquals('tcp://prooph:9000', $socket->getDsn());
    }

    /**
     * @test
     */
    public function it_uses_default_persistent_id()
    {
        $result = $this->make();
        $socket = $result->getSocket()->getSocket();

        $this->assertEquals(ZeroMQMessageProducerFactory::DEFAULT_PERSISTENT_ID, $socket->getPersistentId());
    }

    /**
     * @test
     */
    public function it_uses_overridden_persistent_id()
    {
        $result = $this->make(null, 'abc');
        $socket = $result->getSocket()->getSocket();

        $this->assertEquals('abc', $socket->getPersistentId());
    }

    /**
     * @test
     */
    public function rpc_is_disabled_by_default()
    {
        $result = $this->make();
        $socket = $result->getSocket();

        $this->assertFalse($socket->handlesDeferred());
    }

    /**
     * @test
     */
    public function rpc_can_be_enabled()
    {
        $result = $this->make(null, null, true);
        $socket = $result->getSocket();

        $this->assertTrue($socket->handlesDeferred());
    }

    /**
     * @test
     */
    public function it_does_not_require_a_configuration()
    {
        $this->container->get('config')->willReturn([])->shouldBeCalled();

        $factory = new ZeroMQMessageProducerFactory;
        $producer = $factory($this->container->reveal());

        $this->assertInstanceOf(ZeroMQMessageProducer::class, $producer);
    }

    /**
     * @param string $dsn
     * @param string $persistent_id
     * @param bool $rpc
     * @return ZeroMQMessageProducer
     */
    private function make($dsn = null, $persistent_id = null, $rpc = null)
    {
        $config = compact('dsn', 'persistent_id', 'rpc');
        $this->container->get('config')->willReturn([
            'prooph' => [
                'zeromq_producer' => $config,
            ],
        ])->shouldBeCalled();

        $factory = new ZeroMQMessageProducerFactory;
        return $factory($this->container->reveal());
    }
}
