<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

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

    public function getClass(): ?string
    {
        return null;
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

        throw new \RuntimeException('Sequence contains no elements.');
    }

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

        return $defaultValue;
    }
}
