<?php

namespace ServiceBundle\Handlers\Order;

use Doctrine\ORM\Query;
use ServiceBundle\Commands\Order\OrderRecommendedReadListCommand;
use ServiceBundle\Model\Repository\OrderRepository;


class OrderRecommendedReadListHandler
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;


    public function __construct(
        OrderRepository $OrderRepository
    ) {
        $this->orderRepository = $OrderRepository;
    }

    /**
     * @param OrderRecommendedReadListCommand $command
     * @return Query
     */
    public function handle(OrderRecommendedReadListCommand $command)
    {
        $order = $this->orderRepository->getRecommendedOrdersForWorkerQueryBuilder(
            $command->getWorkerId(),
            (new \DateTime('@' . $command->getStartFromTimestamp())),
            (new \DateTime('@' . $command->getStartToTimestamp())),
            ['o.startDatetime' => 'ASC']
        );

        return $order->getQuery();
    }
}