<?php

namespace ServiceBundle\Commands\Order;

use ServiceBundle\Commands\BaseCommandAbstract;

class OrderUpdateCommand extends BaseCommandAbstract
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $worker_id;
    /**
     * @var string
     */
    protected $customer_id;
    /**
     * @var string
     */
    protected $manager_id;
    /**
     * @var string
     */
    protected $address_id;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $organization;
    /**
     * @var string
     */
    protected $start_timestamp;

    /**
     * @return string
     */
    public function getWorkerId()
    {
        return $this->worker_id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @return string
     */
    public function getManagerId()
    {
        return $this->manager_id;
    }

    /**
     * @return string
     */
    public function getAddressId()
    {
        return $this->address_id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getStartTimestamp()
    {
        return $this->start_timestamp;
    }
}