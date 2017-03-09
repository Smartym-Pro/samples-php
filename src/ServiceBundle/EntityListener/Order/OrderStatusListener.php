<?php

namespace ServiceBundle\EntityListener\Order;

use ServiceBundle\Event\Order\OrderStatusChangedEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DomainBundle\Entity\Orders\OrderStatus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderStatusListener
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param OrderStatus $orderStatus
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersistHandler(OrderStatus $orderStatus, LifecycleEventArgs $eventArgs)
    {
        $this->eventDispatcher->dispatch('order_status.changed', new OrderStatusChangedEvent($orderStatus));
    }
}
