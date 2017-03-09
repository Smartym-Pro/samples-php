<?php

namespace DomainBundle\Model\Repository;

use DomainBundle\Model\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * get QueryBuilder for findBy
     *
     * @param array      $params
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return object QueryBuilder
     */
    public function getQueryBuilderFindBy(array $params = [], array $orderBy = [], $limit = null, $offset = null);

    /**
     * get Query for findBy
     *
     * @param array      $params
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return object Query
     */
    public function getQueryFindBy(array $params = [], array $orderBy = [], $limit = null, $offset = null);

    /**
     * get add filter condition for Query by user role
     *
     * @param Query      $query
     *
     * @return object Query
     */
    public function addFilterToQueryByRole($query);

    /**
     * @param int $workerId
     * @param \DateTime $datetimeFrom
     * @param \DateTime $datetimeTo
     * @param array $orderBy
     * @return QueryBuilder
     */
    public function getRecommendedOrdersForWorkerQueryBuilder(int $workerId, \DateTime $datetimeFrom, \DateTime $datetimeTo, array $orderBy = []);
}
