<?php

namespace ServiceBundle\Exceptions\Validators\Order;

use ServiceBundle\Exceptions\Validators\ValidatorException;

class OrderAddressHaveToBeSetForAdminPanelValidatorException extends ValidatorException
{
    protected $statusCode = 400;
    protected $code = 7320;
    protected $message = 'Order address have to be set if an order is created from Admin panel';
}
