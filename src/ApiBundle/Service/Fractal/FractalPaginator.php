<?php

namespace ApiBundle\Service\Fractal;

use League\Fractal\Pagination\PaginatorInterface;
use Knp\Component\Pager\PaginatorInterface as PaginatorKNP;
use Knp\Component\Pager\Paginator;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

class FractalPaginator implements PaginatorInterface
{
    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var SlidingPagination
     */
    public $pagination;

    /**
     * @var array
     */
    public $paginationData;

    /**
     * @var array
     */
    public $pageItems;

    public function __construct(PaginatorKNP $paginator) {
        $this->paginator = $paginator;
    }

    public function setPagination($target, $page = 1, $limit = 10, array $options = array()) {
        $this->pagination = $this->paginator->paginate(
            $target,
            $page,
            $limit,
            $options
        );
        $this->paginationData = $this->pagination->getPaginationData();
        $this->pageItems = $this->pagination->getItems();

        return $this;
    }

    /**
     * Get the count items on page
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->pagination->getItems());
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->pagination->getCurrentPageNumber();
    }

    /**
     * Get total pages
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->pagination->getPageCount();
    }

    /**
     * Get the number items pre page
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->pagination->getItemNumberPerPage();
    }

    /**
     * Get the total items
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->pagination->getTotalItemCount();
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     *
     * @return string
     */
    public function getUrl($page)
    {
        return null;
    }

    /**
     * @return array
     */
    public function getPageItems()
    {
        return $this->pageItems;
    }
}
