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

    public function __construct(PDOStatementInterface $stmt, int $columnNumber)
    {
        $this->stmt = $stmt;
        $this->columnNumber = $columnNumber;
    }

    public function getClass(): ?string
    {
        return null;
    }

    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_COLUMN, $this->columnNumber);
        return $this->stmt;
    }

    public function toArray(): array
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $this->columnNumber);
    }

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
