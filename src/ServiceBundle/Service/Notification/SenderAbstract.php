<?php

namespace ServiceBundle\Service\Notification;

use DomainBundle\Entity\Orders\OrderStatus;

abstract class SenderAbstract
{
    /** @var  CallRouterInterface */
    private $_callRouter;

    /**
     * @param OrderStatus $orderStatus
     */
    abstract public function sendNotifications(OrderStatus $orderStatus);

    /**
     * @param CallRouterInterface $callRouter
     */
    protected function setCallRouter(CallRouterInterface $callRouter)
    {
        $this->_callRouter = $callRouter;
    }

    /**
     * @return CallRouterInterface
     */
    protected function getCallRouter()
    {
        return $this->_callRouter;
    }
}
