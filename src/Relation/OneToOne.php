<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class OneToOne extends AbstractRelation
{
    /**
     * {@inheritDoc}
     */
    public function join($outerClass, array $outerValues, array $innerValues)
    {
        $collection = Collection::from($outerValues)->outerJoin(
            $innerValues,
            ($this->outerKeySelector, null, $outerClass),
            ($this->innerKeySelector, null, $this->innerClass),
            ($this->resultValueSelector, null, $outerClass)
        );

        return $collection->getIterator();
    }
}
