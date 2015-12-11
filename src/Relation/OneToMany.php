<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class OneToMany extends AbstractRelation
{
    /**
     * {@inheritDoc}
     */
    public function join($outerClass, array $outerValues, array $innerValues)
    {
        $collection = Collection::from($outerValues)->groupJoin(
            $innerValues,
            \Closure::bind($this->outerKeySelector, null, $outerClass),
            \Closure::bind($this->innerKeySelector, null, $this->innerClass),
            \Closure::bind($this->resultValueSelector, null, $outerClass)
        );

        return $collection->getIterator();
    }
}
