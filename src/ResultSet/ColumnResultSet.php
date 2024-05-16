<?php

// NOTE: Do not enable "strict_types" to enable implicit type coercions.
declare(strict_types=0);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\Exception\NoSuchElementException;

/**
 * @implements \IteratorAggregate<mixed>
 * @implements ResultSetInterface<mixed>
 */
class ColumnResultSet implements \IteratorAggregate, ResultSetInterface
{
    /**
     * @use EnumerableExtensions<mixed>
     */
    use EnumerableExtensions;

    private PDOStatementInterface $stmt;

    private int $columnNumber;

    public function __construct(PDOStatementInterface $stmt, int $columnNumber)
    {
        $this->stmt = $stmt;
        $this->columnNumber = $columnNumber;
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

    public function first(?callable $predicate = null): mixed
    {
        $this->stmt->execute();

        if ($predicate !== null) {
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

    public function firstOrDefault(?callable $predicate = null, mixed $defaultValue = null): mixed
    {
        $this->stmt->execute();

        if ($predicate !== null) {
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
