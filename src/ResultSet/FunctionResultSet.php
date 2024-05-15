<?php

// NOTE: Do not enable "strict_types" to enable implicit type coercions.

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

    private PDOStatementInterface $stmt;

    /**
     * @var callable(array<string,mixed>):T
     */
    private $instantiator;

    /**
     * @param callable(array<string,mixed>):T $instantiator
     */
    public function __construct(PDOStatementInterface $stmt, callable $instantiator)
    {
        $this->stmt = $stmt;
        $this->instantiator = $instantiator;
    }

    /**
     * @return callable(array<string,mixed>):T
     */
    public function getInstantiator(): callable
    {
        return $this->instantiator;
    }

    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $instantiator = $this->instantiator;
        foreach ($this->stmt as $row) {
            yield $instantiator($row);
        }
    }

    public function toArray(): array
    {
        $this->stmt->execute();
        $instantiator = $this->instantiator;
        $rows = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map($instantiator, $rows);
    }

    public function first(?callable $predicate = null): mixed
    {
        $this->stmt->execute();

        $instantiator = $this->instantiator;

        if ($predicate !== null) {
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

    public function firstOrDefault(?callable $predicate = null, mixed $defaultValue = null): mixed
    {
        $this->stmt->execute();

        $instantiator = $this->instantiator;

        if ($predicate !== null) {
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

        return $defaultValue;
    }
}
