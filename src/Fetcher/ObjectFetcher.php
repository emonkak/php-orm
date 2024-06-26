<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ObjectResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T of object
 * @implements FetcherInterface<T>
 */
class ObjectFetcher implements FetcherInterface
{
    /**
     * @use Relatable<T>
     */
    use Relatable;

    private PDOInterface $pdo;

    /**
     * @var class-string<T>
     */
    private string $class;

    /**
     * @var mixed[]|null
     */
    private ?array $constructorArguments;

    /**
     * @param class-string<T> $class
     * @param ?mixed[] $constructorArguments
     */
    public function __construct(PDOInterface $pdo, string $class, ?array $constructorArguments = null)
    {
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
        $this->pdo = $pdo;
    }

    public function getPdo(): PDOInterface
    {
        return $this->pdo;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @return ?array<int,mixed>
     */
    public function getConstructorArguments(): ?array
    {
        return $this->constructorArguments;
    }

    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $stmt = $queryBuilder->prepare($this->pdo);
        return new ObjectResultSet($stmt, $this->class, $this->constructorArguments);
    }
}
