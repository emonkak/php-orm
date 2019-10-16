<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

class ColumnResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @var int
     */
    private $columnNumber;

    /**
     * @param PDOStatementInterface $stmt
     */
    public function __construct(PDOStatementInterface $stmt, $columnNumber)
    {
        $this->stmt = $stmt;
        $this->columnNumber = $columnNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_COLUMN, $this->columnNumber);
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $this->columnNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function first(callable $predicate = null)
    {
        $this->stmt->execute();

        if ($predicate) {
            $this->stmt->setFetchMode(\PDO::FETCH_COLUMN, $this->columnNumber);
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $element = $this->stmt->fetch(\PDO::FETCH_COLUMN, $this->columnNumber);
            if ($element !== false) {
                return $element;
            }
        }

        throw new \RuntimeException('Sequence contains no elements.');
    }

    /**
     * {@inheritDoc}
     */
    public function firstOrDefault(callable $predicate = null, $defaultValue = null)
    {
        $this->stmt->execute();

        if ($predicate) {
            $this->stmt->setFetchMode(\PDO::FETCH_COLUMN, $this->columnNumber);
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $element = $this->stmt->fetch(\PDO::FETCH_COLUMN, $this->columnNumber);
            if ($element !== false) {
                return $element;
            }
        }

        return $defaultValue;
    }
}
