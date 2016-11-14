<?php

namespace Emonkak\Orm\Relation;

class Relation extends AbstractRelation
{
    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new Relation(
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey,
            $this->connection,
            $this->fetcher,
            $this->query->with($relation),
            $this->joinStrategy
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getResult($outerKeys)
    {
        return $this->query
            ->from(sprintf('`%s`', $this->table))
            ->where(sprintf('`%s`.`%s`', $this->table, $this->innerKey), 'IN', $outerKeys)
            ->getResult($this->connection, $this->fetcher);
    }
}
