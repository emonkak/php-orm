<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PaginatedResultSet;

/**
 * @internal
 */
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
    private $numItems;

    /**
     * @param SelectQuery  $query
     * @param PDOInterface $connection
     * @param integer      $perPage
     * @param integer      $numItems
     */
    public function __construct(SelectQuery $query, PDOInterface $connection, $perPage, $numItems)
    {
        $this->query = $query;
        $this->connection = $connection;
        $this->perPage = $perPage;
        $this->numItems = $numItems;
    }

    /**
     * @param integer $pageNum
     * @return PaginatedResultSet
     */
    public function at($pageNum)
    {
        return $this->index($pageNum - 1);
    }

    /**
     * @param integer $index
     * @return PaginatedResultSet
     */
    public function atIndex($index)
    {
        if ($index < 0) {
            throw new \OutOfRangeException('Invalid page index given: ' . $index);
        }

        if ($this->getNumPages() > $index) {
            $results = $this->query
                ->offset($this->perPage * $index)
                ->limit($this->perPage)
                ->execute($this->connection);
        } else {
            $results = new EmptyResultSet();
        }

        return new PaginatedResultSet($results, $this, $index);
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
