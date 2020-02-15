<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\Exception\NoSuchElementException;

/**
 * @implements \IteratorAggregate<?scalar>
 * @implements ResultSetInterface<?scalar>
 * @use EnumerableExtensions<?scalar>
 */
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

    /**
     * @psalm-return \Traversable<?scalar>
     */
    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_COLUMN, $this->columnNumber);
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
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

        throw new NoSuchElementException('Sequence contains no elements');
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
