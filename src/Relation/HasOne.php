<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class HasOne extends AbstractRelation
{
    /**
     * @param string   $class
     * @param string   $innerTable
     * @param strin    $outerKey
     * @param string   $innerKey
     * @param \Closure $resultValueSelector
     */
    public static function create(
        $class,
        $innerTable,
        $outerKey,
        $innerKey,
        \Closure $resultValueSelector
    ) {
        return new self(
            $class,
            $innerTable,
            $innerKey,
            function($value) use ($outerKey) {
                return $value->{$outerKey};
            },
            function($value) use ($innerKey) {
                return $value->{$innerKey};
            },
            $resultValueSelector
        );
    }

    /**
     * {@inheritDoc}
     */
    public function join($outerValues, $innerValues)
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
