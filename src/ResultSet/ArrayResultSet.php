<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\Exception\NoSuchElementException;

/**
 * @implements \IteratorAggregate<array<string,mixed>>
 * @implements ResultSetInterface<array<string,mixed>>
 * @use EnumerableExtensions<array<string,mixed>>
 */
class ArrayResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    public function __construct(PDOStatementInterface $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * @psalm-return \Traversable<array<string,mixed>>
     */
    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritDoc}
     */
    public function first(callable $predicate = null)
    {
        $this->stmt->execute();

        if ($predicate) {
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

    /**
     * {@inheritDoc}
     */
    public function firstOrDefault(callable $predicate = null, $defaultValue = null)
    {
        $this->stmt->execute();

        if ($predicate) {
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

        /** @psalm-var TDefault $defaultValue */
        return $defaultValue;
    }
}
