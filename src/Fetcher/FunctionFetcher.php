<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T
 * @implements FetcherInterface<T>
 * @use Relatable<array<string,mixed>>
 */
class FunctionFetcher implements FetcherInterface
{
    use Relatable;

    /**
     * @psalm-var callable(array<string,?scalar>):T
     * @var callable
     */
    private $instantiator;

    /**
     * @psalm-var class-string<T>
     * @var callable
     */
    private $class;

    /**
     * @template TStatic
     * @psalm-param class-string<TStatic> $class
     * @psalm-return self<TStatic>
     */
    public static function ofConstructor(string $class): self
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
        return new self($instantiator, $class);
    }

    /**
     * @psalm-param callable(array<string,mixed>):T $instantiator
     * @psalm-param class-string<T> $class
     */
    public function __construct(callable $instantiator, string $class)
    {
        $this->instantiator = $instantiator;
        $this->class = $class;
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
        return new FunctionResultSet($stmt, $this->instantiator, $this->class);
    }
}
