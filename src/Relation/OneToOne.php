<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class OneToOne extends AbstractRelation
{
    /**
     * {@inheritDoc}
     */
    public function join(array $outerValues, array $innerValues, $outerClass)
    {
        $outerKeySelector = $this->getOuterKeySelector()->bindTo(null, $outerClass);
        $innerKeySelector = $this->getInnerKeySelector()->bindTo(null, $this->getClass());
        $resultValueSelector = $this->getResultValueSelector()->bindTo(null, $outerClass);

        if (count($outerValues) > count($innerValues)) {
            $collection = Collection::from($outerValues)->outerJoin(
                $innerValues,
                $outerKeySelector,
                $innerKeySelector,
                $resultValueSelector
            );
        } else {
            $collection = Collection::from($innerValues)->outerJoin(
                $outerValues,
                $innerKeySelector,
                $outerKeySelector,
                static function($outer, $inner) use ($resultValueSelector) {
                    return $resultValueSelector($inner, $outer);
                }
            );
        }

        return $collection->getIterator();
    }
}
