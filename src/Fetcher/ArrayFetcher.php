<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ArrayResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @implements FetcherInterface<array<string,mixed>>
 */
class ArrayFetcher implements FetcherInterface
{
    /**
     * @use Relatable<array<string,mixed>>
     */
    use Relatable;

    private PDOInterface $pdo;

    public function __construct(PDOInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDOInterface
    {
        return $this->pdo;
    }

    public function getClass(): ?string
    {
        return null;
    }

    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $stmt = $queryBuilder->prepare($this->pdo);
        return new ArrayResultSet($stmt);
    }
}
