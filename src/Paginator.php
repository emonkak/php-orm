<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class Paginator
{
    /**
     * @var SelectQuery
     */
    private $query;

    /**
     * @var PDOInterface
     */
    private $connection;

    /**
     * @var integer
     */
    private $perPage;

    /**
     * @var integer
     */
    private $totalItems;

    /**
     * @param SelectQuery  $query
     * @param PDOInterface $connection
     * @param integer      $perPage
     * @param integer      $totalItems
     */
    public function __construct(SelectQuery $query, PDOInterface $connection, $perPage, $totalItems)
    {
        $this->query = $query;
        $this->connection = $connection;
        $this->perPage = $perPage;
        $this->totalItems = $totalItems;
    }

    /**
     * @param integer $pageNum
     * @return ResultSetInterface
     */
    public function page($pageNum)
    {
        return $this->index($pageNum - 1);
    }

    /**
     * @param integer $index
     * @return ResultSetInterface
     */
    public function index($index)
    {
        if ($index < 0) {
            throw new \OutOfBoundsException();
        }

        $results = $this->query
            ->offset($this->perPage * $index)
            ->limit($this->perPage)
            ->execute($this->connection);

        return new PaginatedResultSet($results, $this, $index);
    }

    /**
     * @return integer
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @return integer
     */
    public function getTotalPages()
    {
        return (int) ceil($this->totalItems / $this->perPage);
    }
}
