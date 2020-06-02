<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\Exception\NoSuchElementException;

/**
 * @template T of object
 * @implements \IteratorAggregate<T>
 * @implements ResultSetInterface<T>
 */
class FunctionResultSet implements \IteratorAggregate, ResultSetInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @psalm-var ?class-string<T>
     * @var ?class-string
     */
    private $class;

    /**
     * @psalm-var callable(array<string,mixed>):T
     * @var callable
     */
    private $instantiator;

    /**
     * @psalm-param ?class-string<T> $class
     * @psalm-param callable(array<string,mixed>):T $instantiator
     */
    public function __construct(PDOStatementInterface $stmt, ?string $class, callable $instantiator)
    {
        $this->stmt = $stmt;
        $this->class = $class;
        $this->instantiator = $instantiator;
    }

    /**
     * @psalm-return ?class-string<T>
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
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $instantiator = $this->instantiator;
        foreach ($this->stmt as $row) {
            yield $instantiator($row);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $this->stmt->execute();
        $instantiator = $this->instantiator;
        $rows = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map($instantiator, $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function first(callable $predicate = null)
    {
        $this->stmt->execute();

        $instantiator = $this->instantiator;

        if ($predicate) {
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($this->stmt as $row) {
                $instance = $instantiator($row);
                if ($predicate($instance)) {
                    return $instance;
                }
            }
        } else {
            $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row !== false) {
                return $instantiator($row);
            }
        }

        throw new NoSuchElementException('Sequence contains no elements');
    }

    /**
     * {@inheritdoc}
     */
    public function firstOrDefault(callable $predicate = null, $defaultValue = null)
    {
        $this->stmt->execute();

        $instantiator = $this->instantiator;

        if ($predicate) {
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($this->stmt as $row) {
                $instance = $instantiator($row);
                if ($predicate($instance)) {
                    return $instance;
                }
            }
        } else {
            $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row !== false) {
                return $instantiator($row);
            }
        }

        /** @psalm-assert TDefault $defaultValue */
        return $defaultValue;
    }
}
