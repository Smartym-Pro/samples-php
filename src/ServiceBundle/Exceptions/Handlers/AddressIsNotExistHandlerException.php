<?php


namespace ServiceBundle\Exceptions\Handlers;

class AddressIsNotExistHandlerException extends HandlerException
{
    protected $statusCode = 400;
    protected $code = 7119;
    protected $message = 'Address is not exist';
}
