<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

class FunctionResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @var callable
     */
    private $instantiator;

    /**
     * @var string
     */
    private $class;

    /**
     * @param PDOStatementInterface $stmt
     * @param callable              $instantiator
     * @param class-string          $class
     */
    public function __construct(PDOStatementInterface $stmt, callable $instantiator, $class)
    {
        $this->stmt = $stmt;
        $this->instantiator = $instantiator;
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $instantiator = $this->instantiator;
        foreach ($this->stmt as $row) {
            yield $instantiator($row);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        $this->stmt->execute();
        $instantiator = $this->instantiator;
        $rows = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map($instantiator, $rows);
    }

    /**
     * {@inheritDoc}
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

        throw new \RuntimeException('Sequence contains no elements.');
    }
}
