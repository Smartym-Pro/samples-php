<?php


namespace ServiceBundle\Exceptions\Handlers;

class CurrencyCodeIsNotExistHandlerException extends HandlerException
{
    protected $statusCode = 400;
    protected $code = 7304;
    protected $message = 'Currency code is not exist';
}
