<?php


namespace ServiceBundle\Exceptions\Handlers;

class CustomerIsNotExistHandlerException extends HandlerException
{
    protected $statusCode = 400;
    protected $code = 7106;
    protected $message = 'Customer is not exist';
}
