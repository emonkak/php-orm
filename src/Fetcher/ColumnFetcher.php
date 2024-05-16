<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ColumnResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @implements FetcherInterface<mixed>
 */
class ColumnFetcher implements FetcherInterface
{
    private PDOInterface $pdo;

    private int $columnNumber;

    public function __construct(PDOInterface $pdo, int $columnNumber = 0)
    {
        $this->pdo = $pdo;
        $this->columnNumber = $columnNumber;
    }

    public function getPdo(): PDOInterface
    {
        return $this->pdo;
    }

    public function getColumnNumber(): int
    {
        return $this->columnNumber;
    }

    public function getClass(): ?string
    {
        return null;
    }

    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $stmt = $queryBuilder->prepare($this->pdo);
        return new ColumnResultSet($stmt, $this->columnNumber);
    }
}
