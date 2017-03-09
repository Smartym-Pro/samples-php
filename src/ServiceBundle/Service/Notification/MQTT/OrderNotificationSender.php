<?php

namespace ServiceBundle\Service\Notification\MQTT;

use ApiBundle\Entity\User;
use ApiBundle\Service\UserManager;
use DomainBundle\Entity\Orders\OrderStatus;
use ApiBundle\Service\Fractal\FractalManager;
use Doctrine\ORM\EntityRepository;
use QueueBundle\Message\DefaultMessage;
use QueueBundle\Service\Queue\QueueClient;
use ServiceBundle\Service\Notification\SenderAbstract;
use ServiceBundle\Transformers\OrderStatusNotificationTransformer;
use Symfony\Component\Translation\TranslatorInterface;
use Gos\Bundle\PubSubRouterBundle\Router\Router;

class OrderNotificationSender extends SenderAbstract
{
    /** @var QueueClient */
    protected $_queueClient;
    /** @var FractalManager */
    protected $_fractalManager;
    /** @var EntityRepository */
    protected $_orderCheckListPointRepository;
    /** @var UserManager */
    protected $_userManager;
    /** @var TranslatorInterface */
    protected $_translator;
    /** @var EntityRepository */
    protected $_userRepository;
    /** @var OrderNotificationRouter */
    protected $_callRouter;
    /** @var Router */
    protected $_pubSubRouter;

    public function __construct(
        QueueClient $queueClient,
        FractalManager $fractalManager,
        EntityRepository $orderCheckListPointRepository,
        UserManager $userManager,
        TranslatorInterface $translator,
        EntityRepository $userRepository,
        OrderNotificationRouter $orderNotificationRouter,
        Router $pubSubRouter
    )
    {
        $this->_queueClient = $queueClient;
        $this->_fractalManager = $fractalManager;
        $this->_orderCheckListPointRepository = $orderCheckListPointRepository;
        $this->_userManager = $userManager;
        $this->_translator = $translator;
        $this->_userRepository = $userRepository;
        $this->_pubSubRouter = $pubSubRouter;
        $this->setCallRouter($orderNotificationRouter);
    }

    /**
     * @param OrderStatus $orderStatus
     */
    public function sendNotifications(OrderStatus $orderStatus)
    {
        $methodsToCall = $this->getCallRouter()->getNotificationMethodsForStatus(
            $orderStatus->getStatus()
        );
        foreach ($methodsToCall as $method) {
            $this->$method($orderStatus);
        }
    }

    /**
     * @param $userId
     */
    private function sendNotification($userId, OrderStatus $orderStatus)
    {
        $topic = $this->_pubSubRouter->generate('user_order_notification_mqtt', [
            'order_id' => $orderStatus->getOrder()->getId(),
            'user_id' => $userId
        ]);

        $queueMessage = (new DefaultMessage($topic))
            ->withPayload(
                $this->_fractalManager->toJson(
                    $orderStatus,
                    new OrderStatusNotificationTransformer(
                        $this->_orderCheckListPointRepository,
                        $this->_userManager,
                        $this->_translator
                    )
                )
            );

        $this->_queueClient->publish($queueMessage);
    }

    /**
     * Method calls defined in OrderNotificationRouter
     * @see OrderNotificationRouter
     */

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForAdmins(OrderStatus $orderStatus)
    {
        /** @var User[] $usersQueryResult */
        $usersQueryResult =  $this->_userManager->getQueryUsersBy(['roles' => User::ROLE_ADMIN])->getResult();
        foreach ($usersQueryResult as $userResult) {
            $this->sendNotification($userResult->getId(), $orderStatus);
        }
        $usersQueryResult =  $this->_userManager->getQueryUsersBy(['roles' => User::ROLE_SUPER_ADMIN])->getResult();
        foreach ($usersQueryResult as $userResult) {
            $this->sendNotification($userResult->getId(), $orderStatus);
        }
    }

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForManagers(OrderStatus $orderStatus)
    {
        /** @var User[] $usersQueryResult */
        $usersQueryResult =  $this->_userManager->getQueryUsersBy(['roles' => User::ROLE_MANAGER])->getResult();
        foreach ($usersQueryResult as $userResult) {
            $this->sendNotification($userResult->getId(), $orderStatus);
        }
    }

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForWorker(OrderStatus $orderStatus)
    {
        $worker = $orderStatus->getOrder()->getWorker();
        if ($worker instanceof User) {
            $this->sendNotification($worker->getId(), $orderStatus);
        }
    }

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForWorkers(OrderStatus $orderStatus)
    {
        /** @var User[] $usersQueryResult */
        $usersQueryResult =  $this->_userManager->getQueryUsersBy(['roles' => User::ROLE_WORKER])->getResult();
        foreach ($usersQueryResult as $userResult) {
            $this->sendNotification($userResult->getId(), $orderStatus);
        }
    }

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForCustomer(OrderStatus $orderStatus)
    {
        $customer = $orderStatus->getOrder()->getCustomer();
        if ($customer instanceof User) {
            $this->sendNotification($customer->getId(), $orderStatus);
        }
    }
}
