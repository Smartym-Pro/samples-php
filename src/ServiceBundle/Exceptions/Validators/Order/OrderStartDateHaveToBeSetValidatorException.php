<?php

namespace ServiceBundle\Exceptions\Validators\Order;

use ServiceBundle\Exceptions\Validators\ValidatorException;

class OrderStartDateHaveToBeSetValidatorException extends ValidatorException
{
    protected $statusCode = 400;
    protected $code = 7319;
    protected $message = 'Order start date have to be set';
}
