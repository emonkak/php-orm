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
            $this->builder->with($relation),
            $this->joinStrategy
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getResult($outerKeys)
    {
        $grammar = $this->builder->getGrammar();
        return $this->builder
            ->from($grammar->identifier($this->table))
            ->where($grammar->identifier($this->table) . '.' . $grammar->identifier($this->innerKey), 'IN', $outerKeys)
            ->getResult($this->connection, $this->fetcher);
    }
}
