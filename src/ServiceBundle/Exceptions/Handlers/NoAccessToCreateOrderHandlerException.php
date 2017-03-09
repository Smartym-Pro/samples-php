<?php


namespace ServiceBundle\Exceptions\Handlers;

class NoAccessToCreateOrderHandlerException extends HandlerException
{
    protected $statusCode = 400;
    protected $code = 7301;
    protected $message = 'No access to create order';
}
