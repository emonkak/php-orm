<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

class ObjectResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @var string
     */
    private $class;

    /**
     * @var ?mixed[]
     */
    private $constructorArguments;

    /**
     * @param PDOStatementInterface $stmt
     * @param class-string          $class
     * @param ?mixed[]              $constructorArguments
     */
    public function __construct(PDOStatementInterface $stmt, $class, array $constructorArguments = null)
    {
        $this->stmt = $stmt;
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
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
    public function toArray()
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
            $element = $this->stmt->fetch();
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
