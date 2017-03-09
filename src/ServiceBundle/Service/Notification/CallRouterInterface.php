<?php

namespace ServiceBundle\Service\Notification;

interface CallRouterInterface
{
    /**
     * @param $status
     * @return array
     */
    public function getNotificationMethodsForStatus($status);
}
