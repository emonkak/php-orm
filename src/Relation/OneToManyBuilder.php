<?php

namespace Emonkak\Orm\Relation;

class OneToManyBuilder extends AbstractRelationBuilder
{
    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return new OneToMany(
            $this->resolvePdo(),
            $this->resolveInnerClass(),
            $this->resolveForeignTable(),
            $this->resolveForeignKey(),
            $this->resolveOuterKeySelector(),
            $this->resolveInnerKeySelector(),
            $this->resolveResultValueSelector()
        );
    }
}
