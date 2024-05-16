<?php

// NOTE: Do not enable "strict_types" to enable implicit type coercions.
declare(strict_types=0);

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

    private PDOStatementInterface $stmt;

    /**
     * @var class-string<T>
     */
    private string $class;

    /**
     * @var mixed[]|null
     */
    private ?array $constructorArguments;

    /**
     * @param class-string<T> $class
     * @param mixed[]|null $constructorArguments
     */
    public function __construct(PDOStatementInterface $stmt, string $class, ?array $constructorArguments = null)
    {
        $this->stmt = $stmt;
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
    }

    /**
     * @return class-string<T>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return mixed[]|null
     */
    public function getConstructorArguments(): ?array
    {
        return $this->constructorArguments;
    }

    public function getIterator(): \Traversable
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);

        // Iterate the statement within this function to avoid 'strict_types' directive.
        foreach ($this->stmt as $element) {
            yield $element;
        }
    }

    public function toArray(): array
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);
    }

    public function first(?callable $predicate = null): mixed
    {
        $this->stmt->execute();

        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);

        if ($predicate !== null) {
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

        throw new NoSuchElementException('Sequence contains no elements');
    }

    public function firstOrDefault(?callable $predicate = null, mixed $defaultValue = null): mixed
    {
        $this->stmt->execute();

        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class, $this->constructorArguments);

        if ($predicate !== null) {
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
