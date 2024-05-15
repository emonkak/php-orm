<?php

// NOTE: Do not enable "strict_types" to enable implicit type coercions.

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\Exception\NoSuchElementException;

/**
 * @implements \IteratorAggregate<array<string,mixed>>
 * @implements ResultSetInterface<array<string,mixed>>
 */
class ArrayResultSet implements \IteratorAggregate, ResultSetInterface
{
    /**
     * @use EnumerableExtensions<array<string,mixed>>
     */
    use EnumerableExtensions;

    private PDOStatementInterface $stmt;

    public function __construct(PDOStatementInterface $stmt)
    {
        $this->stmt = $stmt;
    }

    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $this->stmt;
    }

    public function toArray(): array
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function first(?callable $predicate = null): mixed
    {
        $this->stmt->execute();

        if ($predicate !== null) {
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $element = $this->stmt->fetch(\PDO::FETCH_ASSOC);
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
            $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $element = $this->stmt->fetch(\PDO::FETCH_ASSOC);
            if ($element !== false) {
                return $element;
            }
        }

        return $defaultValue;
    }
}
