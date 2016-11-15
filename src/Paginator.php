<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\PaginatedResultSet;

/**
 * @internal
 */
class Paginator
{
    /**
     * @var SelectBuilder
     */
    private $builder;

    /**
     * @var PDOInterface
     */
    private $connection;

    /**
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @var integer
     */
    private $perPage;

    /**
     * @var integer
     */
    private $numItems;

    /**
     * @param SelectBuilder    $builder
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param integer          $perPage
     * @param integer          $numItems
     */
    public function __construct(SelectBuilder $builder, PDOInterface $connection, FetcherInterface $fetcher, $perPage, $numItems)
    {
        $this->builder = $builder;
        $this->connection = $connection;
        $this->fetcher = $fetcher;
        $this->perPage = $perPage;
        $this->numItems = $numItems;
    }

    /**
     * @param integer $pageNum
     * @return PaginatedResultSet
     */
    public function at($pageNum)
    {
        return $this->atIndex($pageNum - 1);
    }

    /**
     * @param integer $index
     * @return PaginatedResultSet
     */
    public function atIndex($index)
    {
        if ($index < 0) {
            throw new \OutOfRangeException("Invalid page index, got '$index'");
        }

        if ($this->getNumPages() > $index) {
            $result = $this->builder
                ->offset($this->perPage * $index)
                ->limit($this->perPage)
                ->getResult($this->connection, $this->fetcher);
        } else {
            $result = new \EmptyIterator();
        }

        return new PaginatedResultSet($result, $this, $index);
    }

    /**
     * @return PaginatedResultSet
     */
    public function firstPage()
    {
        return $this->atIndex(0);
    }

    /**
     * @return PaginatedResultSet
     */
    public function lastPage()
    {
        return $this->atIndex($this->getNumPages() - 1);
    }

    /**
     * @return integer
     */
    public function getNumItems()
    {
        return $this->numItems;
    }

    /**
     * @return integer
     */
    public function getNumPages()
    {
        return (int) ceil($this->numItems / $this->perPage);
    }
}
