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
     * @var string
     */
    private $class;

    /**
     * @param SelectQuery  $query
     * @param PDOInterface $connection
     * @param integer      $perPage
     * @param integer      $numItems
     * @param string       $class
     */
    public function __construct(SelectQuery $query, PDOInterface $connection, $perPage, $numItems, $class)
    {
        $this->query = $query;
        $this->connection = $connection;
        $this->perPage = $perPage;
        $this->numItems = $numItems;
        $this->class = $class;
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
            $result = $this->query
                ->offset($this->perPage * $index)
                ->limit($this->perPage)
                ->getResult($this->connection, $this->class);
        } else {
            $result = new EmptyResultSet();
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
