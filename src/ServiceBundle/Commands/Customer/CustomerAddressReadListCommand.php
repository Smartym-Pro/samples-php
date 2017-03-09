<?php

namespace ServiceBundle\Commands\Customer;

use ServiceBundle\Commands\BaseCommandAbstract;


class CustomerAddressReadListCommand extends BaseCommandAbstract
{
    /**
     * @var int
     */
    protected $customer_id;

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }
}