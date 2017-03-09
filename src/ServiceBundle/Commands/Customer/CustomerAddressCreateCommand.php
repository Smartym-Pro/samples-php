<?php

namespace ServiceBundle\Commands\Customer;

use ServiceBundle\Commands\BaseCommandAbstract;


class CustomerAddressCreateCommand extends BaseCommandAbstract
{
    /**
     * @var int
     */
    protected $customer_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $x;

    /**
     * @var string
     */
    protected $y;

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return string
     */
    public function getY()
    {
        return $this->y;
    }
}