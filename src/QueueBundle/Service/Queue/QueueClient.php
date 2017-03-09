<?php

namespace QueueBundle\Service\Queue;

use BinSoul\Net\Mqtt\DefaultConnection;
use BinSoul\Net\Mqtt\Client\React\ReactMqttClient;
use QueueBundle\Message\DefaultMessage;
use React\EventLoop\LoopInterface;
use React\SocketClient\TcpConnector;
use React\EventLoop\Factory as LoopFactory;

class QueueClient
{
    /** @var string */
    protected $host;

    /** @var string */
    protected $port;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var ReactMqttClient  */
    protected $client;

    /** @var LoopInterface */
    protected $loop;

    /**
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;

        if ($this->username === null ||
            $this->password === null ||
            $this->port === null ||
            $this->host === null) {
            throw new \InvalidArgumentException();
        }

        $this->loop = LoopFactory::create();
        $connector = new TcpConnector($this->loop);
        $this->client = new ReactMqttClient($connector, $this->loop);
        if ($this->client->isConnected()) {
            $this->connect();
        }
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param DefaultMessage $message
     */
    public function publish(DefaultMessage $message)
    {
        $stopLoop = function () {
            $this->loop->stop();
        };

        if ($this->client->isConnected()) {
            $promise = $this->client->publish($message);
            $promise->done($stopLoop, $stopLoop);
        } else {
            $client = $this->client;
            $this->connect()->then(function () use ($client, $message, $stopLoop) {
                $promise = $client->publish($message);
                $promise->done($stopLoop, $stopLoop);
            });
        }

        $this->loop->run();
    }

    /**
     * @return \React\Promise\ExtendedPromiseInterface
     */
    protected function connect()
    {
        return $this->client->connect(
            gethostbyname($this->host),
            $this->port,
            new DefaultConnection(
                $this->username,
                $this->password
            )
        );
    }
}
