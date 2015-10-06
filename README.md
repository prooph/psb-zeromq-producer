ZeroMQ message dispatcher for ProophServiceBus
===================================================
[![Build Status](https://travis-ci.org/prooph/psb-zeromq-producer.svg)](https://travis-ci.org/prooph/psb-zeromq-producer)
[![Coverage Status](https://coveralls.io/repos/prooph/psb-zeromq-producer/badge.svg?branch=master&service=github)](https://coveralls.io/github/prooph/psb-zeromq-producer?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

Use [ZeroMQ](http://zeromq.org/) as message producer for [Prooph Service Bus](https://github.com/prooph/service-bus).
Works together with bus types: CommandBus, EventBus.

# Requirements
PHP doesn't come with native support for ZeroMQ however there is an extension `ext-zmq` instructions are available on the ZMQ website for the PHP bindings.

http://zeromq.org/bindings:php

# Installation

So after `ext-zmq` is installed on your server/development machine you're ready for the next step! Composer will be able to install prooph zeromq producer in seconds if not quicker. Run the following command to install via composer.

`composer require prooph/psb-zeromq-producer:~0.2`

# Command/Event Bus (PUB/SUB)

To construct your Command/Event bus you'll need to have a server running ZMQ with [`ZMQ::SOCKET_SUB`](http://php.net/manual/en/class.zmq.php#zmq.constants.socket-sub) this will then receive the messages from the producer.

For basic tutorial on PUB/SUB: http://zguide.zeromq.org/page:all#Getting-the-Message-Out

### Usage Examples

```
$container = new Container;
$container['config'] = [
    'prooph' => [
        'zeromq_producer' => [
            'dsn' => 'tcp://127.0.0.1:5555', // ZMQ Server Address.
            'persistent_id' => 'example', // ZMQ Persistent ID to keep connections alive between requests.
            'rpc' => false, // Use as Query Bus.
        ]
    ]
];

$factory = Prooph\ServiceBus\Message\ZeroMQ\Container\ZeroMQMessageProducerFactory;
$zmqProducer = $factory($container);

// Setup complete, now to add it to the prooph service bus.

$commandBus = new Prooph\ServiceBus\CommandBus();
$router = new Prooph\ServiceBus\Plugin\Router\CommandRouter();
$router->route('ExampleCommand')
    ->to($zmqProducer);

$commandBus->utilize($router);
$echoText = new ExampleCommand('It works');
$commandBus->dispatch($echoText);

// Now check your server to make sure it received this command.
```

# Query Bus (REQ/REP)

To construct your Query bus you'll need to have a ZMQ server running with [`ZMQ::SOCKET_REP`](http://php.net/manual/en/class.zmq.php#zmq.constants.socket-rep) this will then receive the messages from the producer and MUST reply as part of the REQ/REP specification.

For basic tutorial on REQ/REP: http://zguide.zeromq.org/page:all#Ask-and-Ye-Shall-Receive

### Usage Examples

```
$container = new Container;
$container['config'] = [
    'prooph' => [
        'zeromq_producer' => [
            'dsn' => 'tcp://127.0.0.1:5555', // ZMQ Server Address.
            'persistent_id' => 'example', // ZMQ Persistent ID to keep connections alive between requests.
            'rpc' => true, // Use as Query Bus.
        ]
    ]
];

$factory = Prooph\ServiceBus\Message\ZeroMQ\Container\ZeroMQMessageProducerFactory;
$zmqProducer = $factory($container);

// Setup complete, now to add it to the prooph service bus.

// @codeliner can yo give this implementation.
```

# Support

- Ask questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/psb-zeromq-producer/issues](https://github.com/prooph/psb-zeromq-producer/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.


# Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

License
-------

Released under the [New BSD License](LICENSE).
