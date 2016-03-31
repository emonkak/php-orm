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
     * @param callable (query: QueryInterface, connection: PDOInterface) -> QueryInterface
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
        $query = PlainQuery::fromQuery($this);

        foreach ($this->observers as $observer) {
            $query = $observer($query, $connection);
        }

        return $query->execute($connection);
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(PDOInterface $connection, $class)
    {
        $query = PlainQuery::fromQuery($this);

        foreach ($this->observers as $observer) {
            $query = $observer($query, $connection);
        }

        return $query->getResult($connection, $class);
    }
}
