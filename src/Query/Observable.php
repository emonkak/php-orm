<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;

trait Observable
{
    /**
     * @var array (RelationInterface, callable)[]
     */
    private $observers = [];

    /**
     * @param callable (query: QueryInterface) -> QueryInterface
     * @return self
     */
    public function observe(callable $observer)
    {
        $chained = clone $this;
        $chained->observers[] = $observer;
        return $chained;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        $query = $this->toExecutable();

        foreach ($this->observers as $observer) {
            $query = $observer($query);
        }

        return $query->execute($pdo);
    }

    /**
     * @return QueryInterface
     */
    abstract public function toExecutable();
}
