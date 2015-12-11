<?php

namespace Emonkak\Orm\Relation;

class OneToOneBuilder extends AbstractRelationBuilder
{
    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return new OneToOne(
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
