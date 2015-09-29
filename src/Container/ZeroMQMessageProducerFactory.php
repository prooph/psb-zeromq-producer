<?php

namespace Prooph\ServiceBus\Message\ZeroMQ\Container;

use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQSocket;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use Interop\Container\ContainerInterface;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\ZeroMQ\ZeroMQMessageProducer;

final class ZeroMQMessageProducerFactory
{
    const DEFAULT_DSN = 'tcp://127.0.0.1:5555';
    const DEFAULT_PERSISTENT_ID = 'prooph';

    /**
     * @param ContainerInterface $container
     * @return ZeroMQMessageProducer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['prooph']['producer'];

        $dsn = isset($config['dsn']) ? $config['dsn'] : self::DEFAULT_DSN;
        $persistentId = isset($config['persistent_id']) ? $config['persistent_id'] : self::DEFAULT_PERSISTENT_ID;

        $socket = $this->makeConnection($persistentId, $dsn);
        $messageConverter = $this->makeMessageConverter($config);

        return new ZeroMQMessageProducer($socket, $messageConverter);
    }

    /**
     * @param string $persistentId
     * @param string $dsn
     * @return ZeroMQSocket
     */
    private function makeConnection($persistentId, $dsn)
    {
        $context = new ZMQContext;
        $socket = new ZMQSocket($context, ZMQ::SOCKET_PUSH, $persistentId);

        return new ZeroMQSocket($socket, $dsn);
    }

    /**
     * @param array $config
     * @return NoOpMessageConverter
     */
    private function makeMessageConverter($config)
    {
        // TODO determine from config.
        return new NoOpMessageConverter;
    }
}
