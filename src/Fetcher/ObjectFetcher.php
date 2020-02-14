<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ObjectResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T of object
 * @implements FetcherInterface<T>
 * @use Relatable<array<string,mixed>>
 */
class ObjectFetcher implements FetcherInterface
{
    use Relatable;

    /**
     * @psalm-var class-string<T>
     * @var class-string<T>
     */
    private $class;

    /**
     * @var ?mixed[]
     */
    private $constructorArguments;

    /**
     * @psalm-param class-string<T> $class
     * @psalm-param ?mixed[] $constructorArguments
     */
    public function __construct(string $class, array $constructorArguments = null)
    {
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        return new ObjectResultSet($stmt, $this->class, $this->constructorArguments);
    }
}
