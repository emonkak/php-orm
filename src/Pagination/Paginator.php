<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Database\PDOInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

class Paginator implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

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
    private $itemCount;

    /**
     * @param SelectBuilder    $builder
     * @param PDOInterface     $pdo
     * @param FetcherInterface $fetcher
     * @param integer          $perPage
     * @param integer          $itemCount
     */
    public function __construct(SelectBuilder $builder, PDOInterface $pdo, FetcherInterface $fetcher, $perPage, $itemCount)
    {
        $this->builder = $builder;
        $this->pdo = $pdo;
        $this->fetcher = $fetcher;
        $this->perPage = $perPage;
        $this->itemCount = $itemCount;
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
    public function getClass()
    {
        return $this->fetcher->getClass();
    }

    /**
     * @param integer $index
     * @return PaginatedResultSet
     */
    public function at($index)
    {
        if ($index < 0) {
            throw new \OutOfRangeException(
                "The value must be greater than or equal to zero. but got '$index'."
            );
        }

        if ($index < $this->getPageCount()) {
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
        $numPages = $this->getPageCount();
        return $this->at($numPages > 0 ? $numPages - 1 : 0);
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
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * @return integer
     */
    public function getPageCount()
    {
        return (int) ceil($this->itemCount / $this->perPage);
    }
}
