<?php

namespace ServiceBundle\Handlers\Order;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DomainBundle\Entity\Orders\Order;
use DomainBundle\Entity\Orders\OrderStatus;
use FOS\UserBundle\Doctrine\UserManager;
use ServiceBundle\Commands\Order\OrderCreateCommand;
use ServiceBundle\Exceptions\Handlers\AddressIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\CustomerIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\ManagerIsNotExistHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToCreateOrderStatusHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToUpdateManagerHandlerException;
use ServiceBundle\Exceptions\Handlers\NoAccessToCreateOrderHandlerException;
use ServiceBundle\Exceptions\Handlers\UserIsNotCustomerHandlerException;
use ServiceBundle\Exceptions\Handlers\UserIsNotManagerHandlerException;
use ServiceBundle\Exceptions\Validators\Order\ChecklistPointArrayKeyTitleHaveToBeSetValidatorException;
use ServiceBundle\Exceptions\Validators\Order\OrderAddressHaveToBeSetForAdminPanelValidatorException;
use ServiceBundle\Model\Repository\OrderChecklistPointRepository;
use ServiceBundle\Security\OrderVoter;
use ServiceBundle\Service\CommandBus\Handler\OrderChecklistPointCreateHandlerService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrderCreateHandler
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var User
     */
    protected $tokenUser;

    /**
     * @var OrderStatus
     */
    protected $orderStatusRepository;

    /**
     * @var OrderChecklistPointRepository
     */
    protected $orderChecklistPointRepository;

    /**
     * @var EntityRepository
     */
    protected $dictCustomerAddressRepository;

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

    public function __construct(
        User $tokenUser,
        EntityManager $em,
        EntityRepository $OrderRepository,
        EntityRepository $OrderStatusRepository,
        EntityRepository $orderChecklistPointRepository,
        EntityRepository $dictCustomerAddressRepository,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->orderRepository = $OrderRepository;
        $this->em = $em;
        $this->tokenUser = $tokenUser;
        $this->orderStatusRepository = $OrderStatusRepository;
        $this->orderChecklistPointRepository = $orderChecklistPointRepository;
        $this->dictCustomerAddressRepository = $dictCustomerAddressRepository;
        $this->userManager = $userManager;
        $this->authorizationChecker = $authorizationChecker;
    }


    public function handle(OrderCreateCommand $command)
    {

        $class = $this->orderRepository->getClassName();
        $order = new $class;

        //CUSTOMER
        $customer = $this->userManager->findUserBy(['id' => $command->getCustomerId()]);
        if (empty($customer)) {
            throw new CustomerIsNotExistHandlerException();
        }
        if (!$customer->hasRole(User::ROLE_CUSTOMER)) {
            throw new UserIsNotCustomerHandlerException();
        }

        //MANAGER
        if ($command->getManagerId()) {
            $this->setManager($command, $order);
        }

        /**
         * @var Order $order
         */
        $order->setCreatedBy($this->tokenUser);
        $order->setCreatedDatetime(new \DateTime());
        $order->setUpdatedBy($this->tokenUser);
        $order->setUpdatedDatetime(new \DateTime());
        $order->setCustomer($customer);
        $order->setUniqueNumber(uniqid('O', true));
        $order->setDescription($command->getDescription());
        $order->setOrganization($command->getOrganization());
        $order->setStartDatetime($command->getStartTimestamp() ? (new \DateTime('@' . $command->getStartTimestamp())) : null);


        //add status to order
        $this->setStatus($command, $order);


        if (!$this->authorizationChecker->isGranted(OrderVoter::CREATE_ORDER, $order)) {
            throw new NoAccessToCreateOrderHandlerException();
        }

        //ADDRESS
        if (
            $command->getAddressId() === null
            && (
                $this->tokenUser->hasRole(User::ROLE_MANAGER)
                || $this->tokenUser->hasRole(User::ROLE_ADMIN)
                || $this->tokenUser->hasRole(User::ROLE_SUPER_ADMIN)
            )
        ) {
            throw new OrderAddressHaveToBeSetForAdminPanelValidatorException();
        }
        if ($command->getAddressId() !== null) {
            $this->setAddress($command, $order);
        }


        $this->em->persist($order);


        //add checklist points
        if (!empty($command->getChecklist())) {
            $this->setChecklistPoints($command, $order);
        }

        return $order;
    }

    /**
     * @param OrderCreateCommand $command
     * @param Order $order
     * @throws ManagerIsNotExistHandlerException
     * @throws NoAccessToUpdateManagerHandlerException
     * @throws UserIsNotManagerHandlerException
     */
    private function setManager(OrderCreateCommand $command, Order $order)
    {
        if (!$this->authorizationChecker->isGranted(OrderVoter::CREATE_ORDER_UPDATE_MANAGER, $order)) {
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
     * @param OrderCreateCommand $command
     * @param Order $order
     * @throws AddressIsNotExistHandlerException
     * @throws NoAccessToUpdateOrderAddressHandlerException
     */
    private function setAddress(OrderCreateCommand $command, Order $order)
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

    /**
     * @param OrderCreateCommand $command
     * @param Order $order
     * @throws NoAccessToCreateOrderStatusHandlerException
     */
    private function setStatus(OrderCreateCommand $command, Order $order)
    {
        $class = $this->orderStatusRepository->getClassName();
        /**
         * @var OrderStatus $status
         */
        $status = new $class;
        $status->setCreatedBy($this->tokenUser);
        $status->setCreatedDatetime(new \DateTime());
        $status->setStatus($command->getStatus());
        $status->setOrder($order);

        $order->addStatus($status);

        $this->em->persist($status);
    }

    /**
     * @param OrderCreateCommand $command
     * @param Order $order
     * @throws ChecklistPointArrayKeyTitleHaveToBeSetValidatorException
     */
    private function setChecklistPoints(OrderCreateCommand $command, Order $order)
    {
        foreach ($command->getChecklist() as $point) {
            if (!is_array($point) || !array_key_exists('title', $point)) {
                throw new ChecklistPointArrayKeyTitleHaveToBeSetValidatorException();
            }
            $checklistPoint = $this->orderChecklistPointRepository->createChecklistPoint(
                $this->authorizationChecker,
                $this->tokenUser,
                $order,
                $point['title'],
                !empty($point['uuid']) ? $point['uuid'] : null
            );
        }
    }
}