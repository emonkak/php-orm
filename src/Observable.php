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
        $chained = $this->chained();
        $chained->observers[] = $observer;
        return $chained;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $connection)
    {
        $query = PlainQuery::fromQuery($this)->to($this->getClass());

        foreach ($this->observers as $observer) {
            $query = $observer($query, $connection);
        }

        return $query->execute($connection);
    }

    /**
     * @return string
     */
    abstract public function getClass();

    /**
     * @return self
     */
    abstract protected function chained();
}
