<?php

namespace ServiceBundle\Exceptions\Validators\Order;

use ServiceBundle\Exceptions\Validators\ValidatorException;

class ManagerHaveToBeSetValidatorException extends ValidatorException
{
    protected $statusCode = 400;
    protected $code = 6000;
    protected $message = 'Manager have to be set';
}
