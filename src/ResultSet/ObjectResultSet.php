<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 */
class ObjectResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @var class-string<T>
     */
    private $class;

    /**
     * @var ?mixed[]
     */
    private $constructorArguments;

    /**
     * @param class-string<T> $class
     * @param ?mixed[] $constructorArguments
     */
    public function __construct(PDOStatementInterface $stmt, $class, array $constructorArguments = null)
    {
        $this->stmt = $stmt;
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getIterator(): \Traversable
    {
        // Uses a generator to avoid the enabling of 'strict_types' directive.
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);
        foreach ($this->stmt as $element) {
            yield $element;
        }
    }

    public function toArray(): array
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);
    }

    public function first(callable $predicate = null)
    {
        $this->stmt->execute();

        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);

        if ($predicate) {
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $element = $this->stmt->fetch();
            if ($element !== false) {
                return $element;
            }
        }

        throw new \RuntimeException('Sequence contains no elements.');
    }

    public function firstOrDefault(callable $predicate = null, $defaultValue = null)
    {
        $this->stmt->execute();

        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);

        if ($predicate) {
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $element = $this->stmt->fetch();
            if ($element !== false) {
                return $element;
            }
        }

        return $defaultValue;
    }
}
