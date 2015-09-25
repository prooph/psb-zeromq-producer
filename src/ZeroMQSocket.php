<?php

namespace Prooph\ServiceBus\Message\ZeroMQ;

use ZMQSocket;

class ZeroMQSocket
{
    /** @var \ZMQSocket */
    private $socket;

    /** @var string */
    private $dsn;

    /** @var bool */
    private $connected = false;

    /**
     * @param \ZMQSocket $socket
     * @param string $dsn
     */
    public function __construct(ZMQSocket $socket, $dsn)
    {
        $this->socket = $socket;
        $this->dsn = $dsn;
    }

    /**
     * @param string|array $message
     * @param int $mode
     */
    public function send($message, $mode)
    {
        if (false === $this->connected) {
            $connectedTo = $this->socket->getEndpoints();

            if (! in_array($this->dsn, $connectedTo)) {
                $this->socket->connect($this->dsn);
            }

            $this->connected = true;
        }

        $this->socket->send($message, $mode);
    }
}
