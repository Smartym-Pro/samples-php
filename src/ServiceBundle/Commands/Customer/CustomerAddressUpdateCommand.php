<?php

namespace ServiceBundle\Commands\Customer;

use ServiceBundle\Commands\BaseCommandAbstract;


class CustomerAddressUpdateCommand extends BaseCommandAbstract
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $is_disabled;
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
     * @return string
     */
    public function getIsDisabled()
    {
        return $this->is_disabled;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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