<?php

namespace ServiceBundle\EventListener\Order;

use League\Tactician\CommandBus;
use ServiceBundle\Commands\Notification\SendOrderStatusNotificationCommand;
use ServiceBundle\Event\Order\OrderStatusChangedEvent;

class OrderStatusListener
{
    /** @var CommandBus */
    protected $commandBus;

    /**
     * OrderStatusListener constructor.
     * @param CommandBus $commandBus
     */
    public function __construct(
        CommandBus $commandBus
    )
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param OrderStatusChangedEvent $event
     */
    public function onStatusChanged(OrderStatusChangedEvent $event)
    {
        $orderStatus = $event->getOrderStatus();
        $this->commandBus->handle(new SendOrderStatusNotificationCommand(
            ['orderStatus' => $orderStatus]
        ));
    }
}
