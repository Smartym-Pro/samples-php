<?php

namespace ServiceBundle\Service\Notification\WS;

use ApiBundle\Entity\User;
use ApiBundle\Service\Fractal\FractalManager2;
use ApiBundle\Service\UserManager;
use DomainBundle\Entity\Orders\OrderStatus;
use ApiBundle\Service\Fractal\FractalManager;
use Doctrine\ORM\EntityRepository;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Gos\Bundle\WebSocketBundle\Pusher\Message;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;
use QueueBundle\Service\Queue\QueueClient;
use ServiceBundle\Service\Notification\SenderAbstract;
use ServiceBundle\Transformers\OrderStatusNotificationTransformer;
use Symfony\Component\Translation\TranslatorInterface;
use Gos\Bundle\PubSubRouterBundle\Router\Router;

class OrderNotificationSender extends SenderAbstract
{
    /**
     * Access to update order fields by role for different statuses
     *
     * @var array
     */
    protected $updateOrderFieldsAccessByRole = [
        'ROLE_MANAGER'  => [
            'owner' => [
                OrderStatus::STATUS_PRICE_ADDED,
                OrderStatus::STATUS_APPROVED_BY_CUSTOMER,
                OrderStatus::STATUS_WORKER_ADDED,
                OrderStatus::STATUS_WORKER_IN_WAY,
                OrderStatus::STATUS_WORKER_DOING,
                OrderStatus::STATUS_WORKER_DONE,
                OrderStatus::STATUS_CLOSED,
                OrderStatus::STATUS_CANCELED
            ],
            'all'   => [
                OrderStatus::STATUS_NEW
            ]
        ],
        'ROLE_ADMIN'    => [
            'owner' => [
            ],
            'all'   => [
                OrderStatus::STATUS_NEW,
                OrderStatus::STATUS_PRICE_ADDED,
                OrderStatus::STATUS_APPROVED_BY_CUSTOMER,
                OrderStatus::STATUS_WORKER_ADDED,
                OrderStatus::STATUS_WORKER_IN_WAY,
                OrderStatus::STATUS_WORKER_DOING,
                OrderStatus::STATUS_WORKER_DONE,
                OrderStatus::STATUS_CLOSED,
                OrderStatus::STATUS_CANCELED
            ]
        ],
    ];

    /** @var QueueClient */
    protected $_queueClient;
    /** @var FractalManager2 */
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
    /** @var PusherDecorator */
    protected $_wampPusher;

    public function __construct(
        QueueClient $queueClient,
        FractalManager $fractalManager,
        EntityRepository $orderCheckListPointRepository,
        UserManager $userManager,
        TranslatorInterface $translator,
        EntityRepository $userRepository,
        OrderNotificationRouter $orderNotificationRouter,
        Router $pubSubRouter,
        PusherInterface $_wampPusher
    )
    {
        $this->_queueClient = $queueClient;
        $this->_fractalManager = $fractalManager;
        $this->_orderCheckListPointRepository = $orderCheckListPointRepository;
        $this->_userManager = $userManager;
        $this->_translator = $translator;
        $this->_userRepository = $userRepository;
        $this->_pubSubRouter = $pubSubRouter;
        $this->_wampPusher = $_wampPusher;
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
     * @param OrderStatus $orderStatus
     */
    private function sendNotification($userId, OrderStatus $orderStatus)
    {
        $data = $this->_fractalManager->toArray(
            $orderStatus,
            new OrderStatusNotificationTransformer(
                $this->_orderCheckListPointRepository,
                $this->_userManager,
                $this->_translator
            )
        );
        $this->_wampPusher->push(
            $data,
            'user_order_notification_ws',
            ['user_id' => $userId]
        );
    }

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForAdmins(OrderStatus $orderStatus)
    {
        $this->sendNotificationForRole($orderStatus, User::ROLE_SUPER_ADMIN);
        $this->sendNotificationForRole($orderStatus, User::ROLE_ADMIN);
    }

    /**
     * @param OrderStatus $orderStatus
     */
    private function sendNotificationForManagers(OrderStatus $orderStatus)
    {
        $this->sendNotificationForRole($orderStatus, User::ROLE_MANAGER);
    }

    /**
     * @param OrderStatus $orderStatus
     * @param string $role
     */
    private function sendNotificationForRole(OrderStatus $orderStatus, $role)
    {
        /** @var User[] $usersQueryResult */
        $usersQueryResult =  $this->_userManager->getQueryUsersBy(['roles' => $role])->getResult();
        foreach ($usersQueryResult as $userResult) {
            //check access
            if ($this->canView($userResult, $orderStatus)) {
                $this->sendNotification($userResult->getId(), $orderStatus);
            }
        }
    }

    private function canView(User $user, OrderStatus $orderStatus)
    {
        if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            $role = 'ROLE_ADMIN';
            $owner = '';
        } elseif ($user->hasRole('ROLE_MANAGER')) {
            $role = 'ROLE_MANAGER';
            $owner = $orderStatus->getOrder()->getManager();
        }

        //checks owner if for this field update necessary owner
        if (
            !empty($this->updateOrderFieldsAccessByRole[$role]['owner'][$orderStatus->getStatus()])
            && $owner != $user
        ) {
            return false;
        }

        return in_array($orderStatus->getStatus(), $this->updateOrderFieldsAccessByRole[$role]['all']);
    }
}
