<?php

namespace ServiceBundle\Handlers\Order;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use DomainBundle\Entity\Orders\Order;
use DomainBundle\Entity\Orders\OrderStatus;
use FOS\UserBundle\Doctrine\UserManager;
use ServiceBundle\Commands\Order\OrderUpdateCommand;
use ServiceBundle\Exceptions\Handlers\AddressIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateDescriptionHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateOrderAddressHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateOrganizationHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateStartDatetimeHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateWorkerHandlerException;
use ServiceBundle\Exceptions\Handlers\OrderIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateManagerHandlerException;
use ServiceBundle\Exceptions\Handlers\ManagerIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\UserIsNotManagerHandlerException;
use ServiceBundle\Exceptions\Handlers\UserIsNotWorkerHandlerException;
use ServiceBundle\Exceptions\Handlers\WorkerIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\WorkerOrdersTimeIsCrosedException;
use ServiceBundle\Model\Repository\OrderRepository;
use ServiceBundle\Model\Repository\OrderStatusRepository;
use ServiceBundle\Security\OrderVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrderUpdateHandler
{
    /**
     * format \DateInterval
     */
    const TIME_BETWEEN_ORDERS = 'PT0H59M';

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var User
     */
    protected $tokenUser;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var EntityRepository
     */
    protected $dictCustomerAddressRepository;


    public function __construct(
        User $tokenUser,
        EntityManager $em,
        EntityRepository $OrderRepository,
        EntityRepository $OrderStatusRepository,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityRepository $dictCustomerAddressRepository
    )
    {
        $this->orderRepository = $OrderRepository;
        $this->tokenUser = $tokenUser;
        $this->em = $em;
        $this->orderStatusRepository = $OrderStatusRepository;
        $this->userManager = $userManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->dictCustomerAddressRepository = $dictCustomerAddressRepository;
    }


    public function handle(OrderUpdateCommand $command)
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['id' => $command->getId()]);
        if (empty($order)) {
            throw new OrderIsNotExistHandlerException();
        }

        //MANAGER
        if ($command->getManagerId() !== null) {
            $this->setManager($command, $order);
        }

        //WORKER
        if ($command->getWorkerId() !== null) {
            $this->setWorker($command, $order);
            $this->orderStatusRepository->setOrderStatus(
                $this->authorizationChecker,
                $this->tokenUser,
                $order,
                OrderStatus::STATUS_WORKER_ADDED
            );
        }

        //ADDRESS
        if ($command->getAddressId() !== null) {
            $this->setAddress($command, $order);
        }

        if ($command->getDescription() !== null) {
            if (!$this->authorizationChecker->isGranted(OrderVoter::UPDATE_ORDER_UPDATE_DESCRIPTION, $order)) {
                throw new NoAccessToUpdateDescriptionHandlerException();
            }
            $order->setDescription($command->getDescription());
        }

        if ($command->getOrganization() !== null) {
            if (!$this->authorizationChecker->isGranted(OrderVoter::UPDATE_ORDER_UPDATE_ORGANIZATION, $order)) {
                throw new NoAccessToUpdateOrganizationHandlerException();
            }
            $order->setOrganization($command->getOrganization());
        }

        if ($command->getStartTimestamp() !== null) {
            if (!$this->authorizationChecker->isGranted(OrderVoter::UPDATE_ORDER_UPDATE_START_DATETIME, $order)) {
                throw new NoAccessToUpdateStartDatetimeHandlerException();
            }

            $order->setStartDatetime(new \DateTime('@' . $command->getStartTimestamp()));
        }

        $this->em->persist($order);

        return $order;
    }

    /**
     * @param OrderUpdateCommand $command
     * @param Order $order
     * @throws ManagerIsNotExistHandlerException
     * @throws NoAccessToUpdateManagerHandlerException
     * @throws UserIsNotManagerHandlerException
     */
    private function setManager(OrderUpdateCommand $command, Order $order)
    {
        if (!$this->authorizationChecker->isGranted(OrderVoter::UPDATE_ORDER_UPDATE_MANAGER, $order)) {
            throw new NoAccessToUpdateManagerHandlerException();
        }

        $manager = $this->userManager->findUserBy(['id' => $command->getManagerId()]);
        if (empty($manager)) {
            throw new ManagerIsNotExistHandlerException();
        }

        if (
            !$manager->hasRole(User::ROLE_MANAGER)
            && !$manager->hasRole(User::ROLE_ADMIN)
            && !$manager->hasRole(User::ROLE_SUPER_ADMIN)
        ) {
            throw new UserIsNotManagerHandlerException();
        }
        $order->setManager($manager);
    }

    /**
     * @param OrderUpdateCommand $command
     * @param Order $order
     * @throws NoAccessToUpdateWorkerHandlerException
     * @throws UserIsNotWorkerHandlerException
     * @throws WorkerIsNotExistHandlerException
     * @throws WorkerOrdersTimeIsCrosedException
     */
    private function setWorker(OrderUpdateCommand $command, Order $order)
    {
        if (!$this->authorizationChecker->isGranted(OrderVoter::UPDATE_ORDER_UPDATE_WORKER, $order)) {
            throw new NoAccessToUpdateWorkerHandlerException();
        }

        $worker = $this->userManager->findUserBy(['id' => $command->getWorkerId()]);
        if (empty($worker)) {
            throw new WorkerIsNotExistHandlerException();
        }
        if (!$worker->hasRole(User::ROLE_WORKER)) {
            throw new UserIsNotWorkerHandlerException();
        }
        $order->setWorker($worker);


        //check time availability
        $qb = $this->orderRepository->getQueryBuilderFindBy(['worker_id' => $worker->getId()]);
        $dateFrom = (new \DateTime('@' . $order->getStartDatetime()->getTimestamp()))
            ->setTimezone($order->getStartDatetime()->getTimezone())
            ->sub(New \DateInterval(self::TIME_BETWEEN_ORDERS));
        $qb->setParameter(':date_from', $dateFrom);
        $dateTo = (new \DateTime('@' . $order->getStartDatetime()->getTimestamp()))
            ->setTimezone($order->getStartDatetime()->getTimezone())
            ->add(New \DateInterval($order->getEstimates()->first()->getWorkInterval()->format('\P\TH\Hi\M')))
            ->add(New \DateInterval(self::TIME_BETWEEN_ORDERS));
        $qb->setParameter(':date_to', $dateTo);
        $list = $qb->getQuery()->getArrayResult();
        if (!empty($list)) {
            throw new WorkerOrdersTimeIsCrosedException();
        }
    }

    /**
     * @param OrderUpdateCommand $command
     * @param Order $order
     * @throws AddressIsNotExistHandlerException
     * @throws NoAccessToUpdateOrderAddressHandlerException
     */
    private function setAddress(OrderUpdateCommand $command, Order $order)
    {
        $address = $this->dictCustomerAddressRepository->findOneBy(['id' => $command->getAddressId()]);
        if (empty($address)) {
            throw new AddressIsNotExistHandlerException();
        }

        if (!$this->authorizationChecker->isGranted(OrderVoter::UPDATE_ORDER_UPDATE_ADDRESS, $order)) {
            throw new NoAccessToUpdateOrderAddressHandlerException();
        }
        $order->setAddress($address);
    }
}