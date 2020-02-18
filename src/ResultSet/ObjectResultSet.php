<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\Exception\NoSuchElementException;

/**
 * @template T of object
 * @implements \IteratorAggregate<T>
 * @implements ResultSetInterface<T>
 */
class ObjectResultSet implements \IteratorAggregate, ResultSetInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @psalm-var class-string<T>
     * @var class-string
     */
    private $class;

    /**
     * @var ?mixed[]
     */
    private $constructorArguments;

    /**
     * @psalm-param class-string<T> $class
     * @psalm-param ?mixed[] $constructorArguments
     */
    public function __construct(PDOStatementInterface $stmt, string $class, array $constructorArguments = null)
    {
        $this->stmt = $stmt;
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
    }

    /**
     * @psalm-return class-string<T>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @psalm-return ?mixed[]
     */
    public function getConstructorArguments(): ?array
    {
        return $this->constructorArguments;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        // Uses a generator to avoid the enabling of 'strict_types' directive.
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);
        foreach ($this->stmt as $element) {
            yield $element;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);
    }

    /**
     * {@inheritDoc}
     */
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
            /** @psalm-var T|false */
            $element = $this->stmt->fetch();
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

        /** @psalm-var TDefault $defaultValue */
        return $defaultValue;
    }
}
