<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ColumnResultSet;

class ColumnFetcher implements FetcherInterface
{
    /**
     * @var integer
     */
    private $columnNumber;

    /**
     * @param integer $columnNumber
     */
    public function __construct($columnNumber = 0)
    {
        $this->columnNumber = $columnNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt)
    {
        return new ColumnResultSet($stmt, $this->columnNumber);
    }
}