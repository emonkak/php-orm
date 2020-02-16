<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ArrayResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @implements FetcherInterface<array<string,?scalar>>
 * @use Relatable<array<string,?scalar>>
 */
class ArrayFetcher implements FetcherInterface
{
    use Relatable;

    /**
     * @var PDOInterface
     */
    private $pdo;

    public function __construct(PDOInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): PDOInterface
    {
        return $this->pdo;
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
    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $stmt = $queryBuilder->prepare($this->pdo);
        return new ArrayResultSet($stmt);
    }
}
