<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\EnumerableExtensions;

class PopoResultSet implements ResultSetInterface
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
     * @param PDOStatementInterface $stmt
     * @param string                $class
     */
    public function __construct(PDOStatementInterface $stmt, $class)
    {
        $this->stmt = $stmt;
        $this->class = $class;
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
        $this->stmt->execute();
        $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class);
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
    }

    /**
     * {@inheritDoc}
     */
    public function first(callable $predicate = null)
    {
        $this->stmt->execute();

        if ($predicate) {
            $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class);
            foreach ($this->stmt as $element) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        } else {
            $this->stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class);
            $element = $this->stmt->fetch();
            if ($element !== false) {
                return $element;
            }
        }

        throw new \RuntimeException('Sequence contains no elements.');
    }
}