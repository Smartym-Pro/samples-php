<?php

namespace ServiceBundle\Service\Notification;

use DomainBundle\Entity\Orders\OrderStatus;
use ServiceBundle\Service\Notification\WS\OrderNotificationSender as SenderWS;
use ServiceBundle\Service\Notification\MQTT\OrderNotificationSender as SenderMQTT;

class OrderNotificationSenderFacade
{
    /** @var  SenderWS */
    private $_senderWS;
    /** @var  SenderMQTT */
    private $_senderMQTT;

    public function __construct(
        SenderWS $senderWS,
        SenderMQTT $senderMQTT
    )
    {
        $this->_senderWS = $senderWS;
        $this->_senderMQTT = $senderMQTT;
    }

    /**
     * @param OrderStatus $orderStatus
     */
    public function sendNotificationsForOrderStatus(OrderStatus $orderStatus)
    {
        $this->_senderMQTT->sendNotifications($orderStatus);
        $this->_senderWS->sendNotifications($orderStatus);
    }
}
