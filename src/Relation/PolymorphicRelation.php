<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Enumerable\Iterator\ConcatIterator;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class PolymorphicRelation implements RelationInterface
{
    const SORT_KEY = '__sort';

    /**
     * @var string
     */
    private $morphKey;

    /**
     * @var array
     */
    private $polymorphics;

    /**
     * @param string $morphKey
     * @param array  $polymorphics
     */
    public function __construct($morphKey, array $polymorphics)
    {
        $this->morphKey = $morphKey;
        $this->polymorphics = $polymorphics;
    }

    /**
     * @return string
     */
    public function getMorphKey()
    {
        return $this->morphKey;
    }

    /**
     * @return array
     */
    public function getPolymorphics()
    {
        return $this->polymorphics;
    }

    /**
     * {@inheritDoc}
     */
    public function associate(ResultSetInterface $result)
    {
        $outerClass = $result->getClass();
        $morphKeySelector = AccessorCreators::toKeySelector($this->morphKey, $outerClass);
        $sortKeyAssignee = AccessorCreators::toKeyAssignee(self::SORT_KEY, $outerClass);
        $outerElementsByMorphKey = [];

        foreach ($result as $index => $element) {
            $morphKey = $morphKeySelector($element);
            $element = $sortKeyAssignee($element, $index);
            $outerElementsByMorphKey[$morphKey][] = $element;
        }

        $outerResults = [];
        foreach ($outerElementsByMorphKey as $morphKey => $outerElements) {
            if (isset($this->polymorphics[$morphKey])) {
                $relation = $this->polymorphics[$morphKey];
                $outerResult = new PreloadedResultSet($outerElements, $outerClass);
                $outerResults[] = $relation->associate($outerResult);
            } else {
                $outerResults[] = $outerElements;
            }
        }

        $sortKeySelector = AccessorCreators::toKeySelector(self::SORT_KEY, $outerClass);
        $sortKeyEraser = AccessorCreators::toKeyEraser(self::SORT_KEY, $outerClass);

        return (new ConcatIterator($outerResults))
            ->orderBy($sortKeySelector)
            ->select($sortKeyEraser);
    }
}
