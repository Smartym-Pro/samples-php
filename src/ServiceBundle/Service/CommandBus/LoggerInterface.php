<?php

namespace ServiceBundle\Service\CommandBus;


interface LoggerInterface
{
    public function log($message);
}