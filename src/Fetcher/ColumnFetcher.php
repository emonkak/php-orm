<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ColumnResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @implements FetcherInterface<?scalar>
 */
class ColumnFetcher implements FetcherInterface
{
    /**
     * @var int
     */
    private $columnNumber;

    public function __construct(int $columnNumber = 0)
    {
        $this->columnNumber = $columnNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        return new ColumnResultSet($stmt, $this->columnNumber);
    }
}
