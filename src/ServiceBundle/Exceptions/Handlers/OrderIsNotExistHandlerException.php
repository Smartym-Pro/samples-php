<?php


namespace ServiceBundle\Exceptions\Handlers;

class OrderIsNotExistHandlerException extends HandlerException
{
    protected $statusCode = 400;
    protected $code = 7300;
    protected $message = 'Order is not exist';
}
