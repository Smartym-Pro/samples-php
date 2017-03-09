<?php

namespace ServiceBundle\Commands\Order;

use ServiceBundle\Commands\BaseCommandAbstract;


class OrderReadCommand extends BaseCommandAbstract
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}