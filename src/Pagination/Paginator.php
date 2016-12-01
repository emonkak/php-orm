<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\SelectBuilder;

class Paginator implements PaginatorInterface
{
    /**
     * @var SelectBuilder
     */
    private $builder;

    /**
     * @var PDOInterface
     */
    private $pdo;

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
     * @param PDOInterface     $pdo
     * @param FetcherInterface $fetcher
     * @param integer          $perPage
     * @param integer          $numItems
     */
    public function __construct(SelectBuilder $builder, PDOInterface $pdo, FetcherInterface $fetcher, $perPage, $numItems)
    {
        $this->builder = $builder;
        $this->pdo = $pdo;
        $this->fetcher = $fetcher;
        $this->perPage = $perPage;
        $this->numItems = $numItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new PaginatorIterator($this);
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        if ($index < 0) {
            throw new \OutOfRangeException("Invalid page index, got '$index'");
        }

        if ($this->getNumPages() > $index) {
            $result = $this->builder
                ->offset($this->perPage * $index)
                ->limit($this->perPage)
                ->getResult($this->pdo, $this->fetcher);
        } else {
            $result = new EmptyResultSet($this->fetcher->getClass());
        }

        return new PaginatedResultSet($result, $this, $index);
    }

    /**
     * @return PaginatedResultSet
     */
    public function firstPage()
    {
        return $this->at(0);
    }

    /**
     * @return PaginatedResultSet
     */
    public function lastPage()
    {
        return $this->at($this->getNumPages() - 1);
    }

    /**
     * @return integer
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return integer
     */
    public function getNumItems()
    {
        return $this->numItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getNumPages()
    {
        return (int) ceil($this->numItems / $this->perPage);
    }
}
