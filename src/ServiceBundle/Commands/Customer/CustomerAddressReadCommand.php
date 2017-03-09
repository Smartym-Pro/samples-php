<?php

namespace ServiceBundle\Commands\Customer;

use ServiceBundle\Commands\BaseCommandAbstract;


class CustomerAddressReadCommand extends BaseCommandAbstract
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}