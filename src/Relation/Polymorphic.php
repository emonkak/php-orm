<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Enumerable\Iterator\ConcatIterator;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class Polymorphic implements RelationInterface
{
    const SORT_KEY = '__sort';

    /**
     * @var string
     */
    protected $morphKey;

    /**
     * @var array
     */
    protected $polymorphics;

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
            if (isset($outerElementsByMorphKey[$morphKey])) {
                $outerElementsByMorphKey[$morphKey][] = $element;
            } else {
                $outerElementsByMorphKey[$morphKey] = [$element];
            }
        }

        $iterators = [];
        foreach ($outerElementsByMorphKey as $morphKey => $outerElements) {
            if (isset($this->polymorphics[$morphKey])) {
                $relation = $this->polymorphics[$morphKey];
                $outer = new PreloadResultSet($outerElements, $outerClass);
                $iterators[] = $relation->associate($outer);
            } else {
                $iterators[] = $outerElements;
            }
        }

        $sortKeySelector = AccessorCreators::toKeySelector(self::SORT_KEY, $outerClass);
        $sortKeyEraser = AccessorCreators::toKeyEraser(self::SORT_KEY, $outerClass);

        return (new ConcatIterator($iterators))
            ->orderBy($sortKeySelector)
            ->_do($sortKeyEraser);
    }
}
