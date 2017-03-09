<?php

namespace ServiceBundle\Model\Repository;

use DomainBundle\Entity\Orders\Order;
use DomainBundle\Entity\Orders\OrderStatus;
use DomainBundle\Model\Repository\OrderRepositoryInterface;
use DomainBundle\Model\Repository\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use ServiceBundle\Model\EntityRepository;

class OrderRepository extends EntityRepository implements OrderRepositoryInterface
{
    /**
     * format \DateInterval
     */
    const TIME_BETWEEN_ORDERS = 'PT0H59M';

    /**
     * get QueryBuilder for findBy
     *
     * @param array $params
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderFindBy(array $params = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $q = $this->createQueryBuilder('o');
        /**
         * @var QueryBuilder $q
         */


        if (isset($params['customer_id']) && $params['customer_id'] !== null) {
            $q->leftJoin('o.customer', 'oc');
            $q->andWhere($q->expr()->eq('oc.id', ':customer_id'));
            $q->setParameter(':customer_id', $params['customer_id']);
        }

        if (isset($params['manager_id']) && $params['manager_id'] !== null) {
            $q->leftJoin('o.manager', 'om');
            $q->andWhere($q->expr()->eq('om.id', ':manager_id'));
            $q->setParameter(':manager_id', $params['manager_id']);
        }

        if (isset($params['worker_id']) && $params['worker_id'] !== null) {
            $q->leftJoin('o.worker', 'ow');
            $q->andWhere($q->expr()->eq('ow.id', ':worker_id'));
            $q->setParameter(':worker_id', $params['worker_id']);
        }

        if (isset($params['start_from_timestamp']) && $params['start_from_timestamp'] !== null) {
            $q->andWhere($q->expr()->gte('o.startDatetime', ':start_from_timestamp'));
            $q->setParameter(':start_from_timestamp', (new \DateTime())->setTimestamp($params['start_from_timestamp']));
        }

        if (isset($params['start_to_timestamp']) && $params['start_to_timestamp'] !== null) {
            $q->andWhere($q->expr()->lte('o.startDatetime', ':start_to_timestamp'));
            $q->setParameter(':start_to_timestamp', (new \DateTime())->setTimestamp($params['start_to_timestamp']));
        }

        if (isset($params['statuses']) && $params['statuses'] !== null) {
            /**
             * @var QueryBuilder $subq
             */
            $subq = $this->createQueryBuilder('mo')
                ->select('MAX(mos.createdDatetime)')
                ->leftJoin('mo.statuses', 'mos')
                ->where('mo = o');
            $q->leftJoin('o.statuses', 'os', Expr\Join::WITH, $q->expr()->eq('os.createdDatetime', "({$subq->getDQL()})"));
            $q->andWhere($q->expr()->in('os.status', $params['statuses']));
        }


        foreach ($orderBy as $field => $order) {
            $q->addOrderBy($field, $order);
        }

        if ($limit) {
            $q->setMaxResults($limit);
        }

        if ($offset) {
            $q->setFirstResult($offset);
        }


        return $q;
    }

    /**
     * get Query for findBy
     *
     * @param array $params
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return object Query
     */
    public function getQueryFindBy(array $params = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $q = $this->getQueryBuilderFindBy($params, $orderBy, $limit, $offset);

        return $q->getQuery();
    }

    /**
     * get add filter condition for Query by user role
     *
     * @param Query $query
     *
     * @return object Query
     */
    public function addFilterToQueryByRole($query)
    {
        // TODO: Implement addFilterToQueryByRole() method.
    }

    /**
     * @param int $workerId
     * @param \DateTime $datetimeFrom
     * @param \DateTime $datetimeTo
     * @return QueryBuilder
     */
    public function getRecommendedOrdersForWorkerQueryBuilder(int $workerId, \DateTime $datetimeFrom, \DateTime $datetimeTo, array $orderBy = [])
    {
        $subq = $this->createQueryBuilder('o2')
            ->select('MAX(moe.createdDatetime) maxDatetime')
            ->leftJoin('o2.estimates', 'moe')
            ->where('o2 = o');

        //Current worker orders
        $q = $this->createQueryBuilder('o');
        /**
         * @var QueryBuilder $q
         */
        $q->leftJoin('o.estimates', 'oe', Expr\Join::WITH, $q->expr()->eq('oe.createdDatetime', "({$subq->getDQL()})"));
        $q->andWhere(
            $q->expr()->orX(
                $q->expr()->andX(
                    $q->expr()->gte(
                        'o.startDatetime',
                        ':order_from'
                    ),
                    $q->expr()->lte(
                        'o.startDatetime',
                        ':order_to'
                    )
                ),
                $q->expr()->andX(
                    $q->expr()->gte(
                        '(o.startDatetime + oe.workInterval)',
                        ':order_from'
                    ),
                    $q->expr()->lte(
                        '(o.startDatetime + oe.workInterval)',
                        ':order_to'
                    )
                )
            )
        );
        $q->setParameter(':order_from', $datetimeFrom);
        $q->setParameter(':order_to', $datetimeTo);

        $q->leftJoin('o.worker', 'ow');
        $q->andWhere($q->expr()->eq('ow.id', ':worker_id'));
        $q->setParameter(':worker_id', $workerId);

        $currentOrders = $q->getQuery()->getResult();

        //recommended orders
        $rq = $this->getQueryBuilderFindBy(
            [
                'statuses'  => [OrderStatus::STATUS_APPROVED_BY_CUSTOMER]
            ],
            $orderBy
        );
        $rq->andWhere($rq->expr()->isNull('o.worker'));

        $rq->leftJoin('o.estimates', 'oe', Expr\Join::WITH, $rq->expr()->eq('oe.createdDatetime', "({$subq->getDQL()})"));
        $rq->andWhere(
            $rq->expr()->orX(
                $rq->expr()->andX(
                    $rq->expr()->gte(
                        'o.startDatetime',
                        ':order_from'
                    ),
                    $rq->expr()->lte(
                        'o.startDatetime',
                        ':order_to'
                    )
                ),
                $rq->expr()->andX(
                    $rq->expr()->gte(
                        '(o.startDatetime + oe.workInterval)',
                        ':order_from'
                    ),
                    $rq->expr()->lte(
                        '(o.startDatetime + oe.workInterval)',
                        ':order_to'
                    )
                )
            )
        );
        $rq->setParameter(':order_from', $datetimeFrom);
        $rq->setParameter(':order_to', $datetimeTo);

        foreach ($currentOrders as $order) {
            $oFrom = (new \DateTime('@' . $order->getStartDatetime()->getTimestamp()))
                ->setTimezone($order->getStartDatetime()->getTimezone())
                ->sub(New \DateInterval(self::TIME_BETWEEN_ORDERS));
            $oTo = (new \DateTime('@' . $order->getStartDatetime()->getTimestamp()))
                ->setTimezone($order->getStartDatetime()->getTimezone())
                ->add(New \DateInterval($order->getEstimates()->first()->getWorkInterval()->format('\P\TH\Hi\M')))
                ->add(New \DateInterval(self::TIME_BETWEEN_ORDERS));

            $rq->andWhere(
                $rq->expr()->orX(
                    $rq->expr()->lt(
                        '(o.startDatetime + oe.workInterval)',
                        ':current_order_from_' . $order->getId()
                    ),
                    $rq->expr()->gt(
                        'o.startDatetime',
                        ':current_order_to_' . $order->getId()
                    )
                )
            );
            /** @var Order $order */
            $rq->setParameter(':current_order_from_' . $order->getId(), $oFrom);
            $rq->setParameter(':current_order_to_' . $order->getId(), $oTo);
        }

        return $rq;
    }

}
