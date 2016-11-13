<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
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
     * @var string
     */
    private $class;

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
     * @param string       $class
     * @param integer      $perPage
     * @param integer      $numItems
     */
    public function __construct(SelectQuery $query, PDOInterface $connection, $class, $perPage, $numItems)
    {
        $this->query = $query;
        $this->connection = $connection;
        $this->class = $class;
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
            $result = $this->query
                ->offset($this->perPage * $index)
                ->limit($this->perPage)
                ->getResult($this->connection, $this->class);
        } else {
            $result = new \EmptyIterator();
        }

        return new PaginatedResultSet($result, $this, $index);
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
