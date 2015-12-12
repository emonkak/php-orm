<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class OneToMany extends AbstractRelation
{
    /**
     * {@inheritDoc}
     */
    public function join(array $outerValues, array $innerValues, $outerClass)
    {
        $outerKeySelector = $this->getOuterKeySelector()->bindTo(null, $outerClass);
        $innerKeySelector = $this->getInnerKeySelector()->bindTo(null, $this->getClass());
        $resultValueSelector = $this->getResultValueSelector()->bindTo(null, $outerClass);

        $collection = Collection::from($outerValues)->groupJoin(
            $innerValues,
            $outerKeySelector,
            $innerKeySelector,
            $resultValueSelector
        );

        return $collection->getIterator();
    }
}
