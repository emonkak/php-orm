<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;

trait Observable
{
    /**
     * @var array (RelationInterface, callable)[]
     */
    private $observers = [];

    /**
     * Whether this query is executing.
     *
     * @var boolean
     */
    private $executing = false;

    /**
     * @param callable $observer (query: QueryInterface, connection: PDOInterface) -> QueryInterface
     * @return self
     */
    public function observe(callable $observer)
    {
        $cloned = clone $this;
        $cloned->observers[] = $observer;
        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $connection)
    {
        if ($this->executing) {
            return $this->executeWithoutObservers($connection);
        } else {
            $this->executing = true;

            $query = $this;

            foreach ($this->observers as $observer) {
                $query = $observer($query, $connection);
            }

            $result = $query->execute($connection);

            $this->executing = false;

            return $result;
        }
    }

    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    abstract public function executeWithoutObservers(PDOInterface $connection);

    /**
     * {@inheritDoc}
     */
    public function getResult(PDOInterface $connection, $class)
    {
        if ($this->executing) {
            return $this->getResultWithoutObservers($connection, $class);
        } else {
            $this->executing = true;

            $query = $this;

            foreach ($this->observers as $observer) {
                $query = $observer($query, $connection);
            }

            $result = $query->getResult($connection, $class);

            $this->executing = false;

            return $result;
        }
    }

    /**
     * @param PDOInterface $connection
     * @param string       $class
     * @return ResultSetInterface
     */
    abstract public function getResultWithoutObservers(PDOInterface $connection, $class);
}
