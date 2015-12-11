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
        $outerKeySelector = \Closure::bind($this->outerKeySelector, null, $outerClass);
        $innerKeySelector = \Closure::bind($this->innerKeySelector, null, $this->innerClass);
        $resultValueSelector = \Closure::bind($this->resultValueSelector, null, $outerClass);

        $collection = Collection::from($outerValues)->outerJoin(
            $innerValues,
            $outerKeySelector,
            $innerKeySelector,
            $resultValueSelector
        );

        return $collection->getIterator();
    }
}
