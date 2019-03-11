<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Database\PDOInterface;
use Emonkak\Enumerable\Enumerable;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\SelectBuilder;

class PrecountPaginator extends AbstractPaginator
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
    public function at($index)
    {
        if ($index >= 0 && $index < $this->getNumPages()) {
            $elements = $this->builder
                ->offset($this->perPage * $index)
                ->limit($this->perPage)
                ->getResult($this->pdo, $this->fetcher);
        } else {
            $elements = Enumerable::_empty();
        }

        return new PaginatedResultSet($elements, $this, $index);
    }

    /**
     * {@inheritDoc}
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * {@inheritDoc}
     */
    public function getNumItems()
    {
        return $this->numItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->fetcher->getClass();
    }
}