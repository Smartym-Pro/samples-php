<?php

namespace ServiceBundle\Exceptions\Validators\Order;

use ServiceBundle\Exceptions\Validators\ValidatorException;

class ChecklistPointArrayKeyTitleHaveToBeSetValidatorException extends ValidatorException
{
    protected $statusCode = 400;
    protected $code = 6000;
    protected $message = 'Checklist point array key \'title\' have to be set';
}
