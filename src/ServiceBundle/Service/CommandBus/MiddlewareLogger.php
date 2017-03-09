<?php

namespace ServiceBundle\Service\CommandBus;

use League\Tactician\Middleware;

class MiddlewareLogger implements Middleware
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function execute($command, callable $next)
    {
        $this->logger->log($this->makeLog($command));
        return $next($command);
    }

    public function makeLog($command)
    {
        $arr = ['COMMAND: ' . get_class($command)];
        $arr[] = json_encode((array) $command);

        return implode(';', $arr);
    }
}
