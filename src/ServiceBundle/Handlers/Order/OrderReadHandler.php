<?php

namespace ServiceBundle\Handlers\Order;

use DomainBundle\Entity\Orders\Order;
use ServiceBundle\Commands\Order\OrderReadCommand;
use ServiceBundle\Exceptions\Handlers\NoAccessToReadOrderHandlerException;
use ServiceBundle\Exceptions\Handlers\OrderIsNotExistHandlerException;
use ServiceBundle\Model\Repository\OrderRepository;
use ServiceBundle\Security\OrderVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrderReadHandler
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;


    public function __construct(
        OrderRepository $OrderRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->orderRepository = $OrderRepository;
    }


    public function handle(OrderReadCommand $command)
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['id' => $command->getId()]);
        if (empty($order)) {
            throw new OrderIsNotExistHandlerException();
        }

        //check access
        if (!$this->authorizationChecker->isGranted(OrderVoter::VIEW_ORDER, $order)) {
            throw new NoAccessToReadOrderHandlerException();
        }

        return $order;
    }
}