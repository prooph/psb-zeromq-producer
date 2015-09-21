<?php

namespace Prooph\ServiceBus\Message\ZeroMQ;

use ZMQ;
use ZMQSocket;
use Prooph\Common\Messaging\MessageConverter;
use Prooph\Common\Messaging\MessageDataAssertion;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\Exception\RuntimeException;
use React\Promise\Deferred;

class ZeroMQMessageProducer implements MessageProducer
{
    /** @var ZMQSocket */
    private $zmqClient;

    /** @var MessageConverter */
    private $messageConverter;

    /** @var string */
    private $dsn;

    /** @var bool */
    private $connected;

    /**
     * @param ZMQSocket $zmqClient
     * @param MessageConverter $messageConverter
     * @param string $dsn
     */
    public function __construct(ZMQSocket $zmqClient, MessageConverter $messageConverter, $dsn)
    {
        $this->zmqClient = $zmqClient;
        $this->messageConverter = $messageConverter;
        $this->dsn = $dsn;
    }

    /**
     * Message producers need to be invokable.
     *
     * A producer MUST be able to handle a message async without returning a response.
     * A producer MAY also support future response by resolving the passed $deferred.
     *
     * Note: A $deferred is only passed by a QueryBus but in this case the $deferred
     *       MUST either be resolved/rejected OR the message producer
     *       MUST throw a Prooph\ServiceBus\Exception\RuntimeException if it cannot
     *       handle the $deferred
     *
     * @param Message $message
     * @param null|Deferred $deferred
     * @throws RuntimeException If a $deferred is passed but producer can not handle it
     */
    public function __invoke(Message $message, Deferred $deferred = null)
    {
        if (null !== $deferred) {
            throw new RuntimeException(__CLASS__ . ' cannot handle query messages which require future responses.');
        }

        $data = $this->arrayFromMessage($message);

        $this->connection()->send(json_encode($data), ZMQ::MODE_NOBLOCK);
    }

    /**
     * @param Message $message
     * @return array
     */
    private function arrayFromMessage(Message $message)
    {
        $messageData = $this->messageConverter->convertToArray($message);

        MessageDataAssertion::assert($messageData);

        $messageData['created_at'] = $message->createdAt()->format('Y-m-d\TH:i:s.u');

        return $messageData;
    }

    /**
     * @return ZMQSocket
     */
    private function connection()
    {
        if (! $this->connected) {
            $connectedTo = $this->zmqClient->getEndpoints();

            if (! in_array($this->dsn, $connectedTo)) {
                $this->zmqClient->connect($this->dsn);
            }
        }

        return $this->zmqClient;
    }
}
