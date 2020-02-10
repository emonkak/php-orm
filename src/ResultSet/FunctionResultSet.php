<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 */
class FunctionResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @var callable(array):T
     */
    private $instantiator;

    /**
     * @var class-string<T>
     */
    private $class;

    /**
     * @param callable(array):T $instantiator
     * @param class-string<T> $class
     */
    public function __construct(PDOStatementInterface $stmt, callable $instantiator, string $class)
    {
        $this->stmt = $stmt;
        $this->instantiator = $instantiator;
        $this->class = $class;
    }

    public function getClass(): ?string
    {
        return $this->class;
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

        throw new \RuntimeException('Sequence contains no elements.');
    }
}
