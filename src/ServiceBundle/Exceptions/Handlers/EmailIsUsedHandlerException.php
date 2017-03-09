<?php


namespace ServiceBundle\Exceptions\Handlers;

class EmailIsUsedHandlerException extends HandlerException
{
    protected $statusCode = 400;
    protected $code = 7200;
    protected $message = 'Email already is used';
}
