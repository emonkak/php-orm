<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T
 * @implements FetcherInterface<T>
 */
class FunctionFetcher implements FetcherInterface
{
    /**
     * @use Relatable<T>
     */
    use Relatable;

    private PDOInterface $pdo;

    /**
     * @var ?class-string
     */
    private ?string $class;

    /**
     * @var callable(array<string,mixed>):T
     */
    private $instantiator;

    /**
     * @template TStatic of object
     * @param class-string<TStatic> $class
     * @return self<TStatic>
     */
    public static function ofConstructor(PDOInterface $pdo, string $class): self
    {
        /** @var callable(array<string,mixed>):TStatic */
        $instantiator = \Closure::bind(
            /**
             * @param array<string,mixed> $props
             * @return TStatic
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
     * @param ?class-string $class
     * @param callable(array<string,mixed>):T $instantiator
     */
    public function __construct(PDOInterface $pdo, ?string $class, callable $instantiator)
    {
        $this->pdo = $pdo;
        $this->class = $class;
        $this->instantiator = $instantiator;
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
     * @return callable(array<string,mixed>):T
     */
    public function getInstantiator(): callable
    {
        return $this->instantiator;
    }

    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $stmt = $queryBuilder->prepare($this->pdo);
        return new FunctionResultSet($stmt, $this->instantiator);
    }
}
