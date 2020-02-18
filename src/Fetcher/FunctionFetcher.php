<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T of object
 * @implements FetcherInterface<T>
 */
class FunctionFetcher implements FetcherInterface
{
    /**
     * @use Relatable<T>
     */
    use Relatable;

    /**
     * @var PDOInterface
     */
    private $pdo;

    /**
     * @psalm-var class-string<T>
     * @var callable
     */
    private $class;

    /**
     * @psalm-var callable(array<string,mixed>):T
     * @var callable
     */
    private $instantiator;

    /**
     * @template TStatic of object
     * @psalm-param class-string<TStatic> $class
     * @psalm-return self<TStatic>
     */
    public static function ofConstructor(PDOInterface $pdo, string $class): self
    {
        $instantiator = \Closure::bind(
            /**
             * @psalm-param array<string,mixed> $props
             * @psalm-return TStatic
             */
            function(array $props) use ($class) {
                return new $class($props);
            },
            null,
            $class
        );
        return new self($pdo, $class, $instantiator);
    }

    /**
     * @psalm-param class-string<T> $class
     * @psalm-param callable(array<string,mixed>):T $instantiator
     */
    public function __construct(PDOInterface $pdo, string $class, callable $instantiator)
    {
        $this->pdo = $pdo;
        $this->class = $class;
        $this->instantiator = $instantiator;
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
        return $this->class;
    }

    /**
     * @psalm-return callable(array<string,mixed>):T
     */
    public function getInstantiator(): callable
    {
        return $this->instantiator;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $stmt = $queryBuilder->prepare($this->pdo);
        return new FunctionResultSet($stmt, $this->class, $this->instantiator);
    }
}
