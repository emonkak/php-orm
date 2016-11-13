<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @internal
 */
class ModelResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @var string
     */
    private $class;

    /**
     * @param PDOStatementInterface $stmt
     * @param string                $class
     */
    public function __construct(PDOStatementInterface $stmt, $class)
    {
        $this->stmt = $stmt;
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
        $instantiator = $this->getInstantiator();
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
        $instantiator = $this->getInstantiator();
        $rows = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map($instantiator, $rows);
    }

    /**
     * {@inheritDoc}
     */
    public function first(callable $predicate = null)
    {
        $this->stmt->execute();

        $instantiator = $this->getInstantiator();

        if ($predicate) {
            $stmt->stmt->setFetchMode(\PDO::FETCH_ASSOC);
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

    /**
     * @return callable
     */
    private function getInstantiator()
    {
        $class = $this->class;
        return \Closure::bind(
            static function($props) use ($class) {
                return new $class($props);
            },
            null,
            $class
        );
    }
}
