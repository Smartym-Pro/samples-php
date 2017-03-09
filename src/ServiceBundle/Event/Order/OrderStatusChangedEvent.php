<?php

namespace ServiceBundle\Event\Order;

use Symfony\Component\EventDispatcher\Event;
use DomainBundle\Entity\Orders\OrderStatus;

class OrderStatusChangedEvent extends Event
{
    /** @var OrderStatus */
    protected $orderStatus;

    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return OrderStatus
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }
}
