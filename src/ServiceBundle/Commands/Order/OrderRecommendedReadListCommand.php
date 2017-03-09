<?php

namespace ServiceBundle\Commands\Order;

use ServiceBundle\Commands\BaseCommandAbstract;

class OrderRecommendedReadListCommand extends BaseCommandAbstract
{
    /**
     * @var string
     */
    protected $start_from_timestamp;

    /**
     * @var string
     */
    protected $start_to_timestamp;

    /**
     * @var string
     */
    protected $worker_id;

    /**
     * @var string
     */
    protected $page = '1';
    /**
     * @var string
     */
    protected $per_page = '10';

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * @return string
     */
    public function getStartFromTimestamp()
    {
        return $this->start_from_timestamp;
    }

    /**
     * @return string
     */
    public function getStartToTimestamp()
    {
        return $this->start_to_timestamp;
    }

    /**
     * @return string
     */
    public function getWorkerId()
    {
        return $this->worker_id;
    }
}
