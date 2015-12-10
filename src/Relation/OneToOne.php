<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class OneToOne extends AbstractRelation
{
    /**
     * {@inheritDoc}
     */
    public function join(array $outerValues, array $innerValues)
    {
        $collection = Collection::from($outerValues)->outerJoin(
            $innerValues,
            $this->outerKeySelector,
            $this->innerKeySelector,
            $this->resultValueSelector
        );

        return $collection->getIterator();
    }
}
