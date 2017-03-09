<?php

namespace ServiceBundle\Service\Notification\WS;

use DomainBundle\Entity\Orders\OrderStatus;
use ServiceBundle\Service\Notification\CallRouterInterface;

class OrderNotificationRouter implements CallRouterInterface
{
    //Method for sending notifications in OrderNotificationSender
    const NOTIFICATION_FOR_MANAGERS_METHOD = 'sendNotificationForManagers';
    const NOTIFICATION_FOR_ADMINS_METHOD   = 'sendNotificationForAdmins';

    const ROLES_TO_NOTIFY = [
        OrderStatus::STATUS_NEW                  => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_PRICE_ADDED          => [
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
            self::NOTIFICATION_FOR_ADMINS_METHOD,
        ],
        OrderStatus::STATUS_APPROVED_BY_CUSTOMER => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_WORKER_ADDED         => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_WORKER_IN_WAY        => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_WORKER_DOING         => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_WORKER_DONE          => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_CLOSED               => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
        OrderStatus::STATUS_CANCELED             => [
            self::NOTIFICATION_FOR_ADMINS_METHOD,
            self::NOTIFICATION_FOR_MANAGERS_METHOD,
        ],
    ];

    /**
     * @param $status
     * @return array
     */
    public function getNotificationMethodsForStatus($status)
    {
        if (array_key_exists($status, self::ROLES_TO_NOTIFY)) {
            return self::ROLES_TO_NOTIFY[$status];
        }

        return [];
    }
}
