<?php

declare(strict_types=1);

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
     * @var array<string,RelationInterface> $polymorphics
     */
    private $polymorphics;

    /**
     * @param array<string,RelationInterface> $polymorphics
     */
    public function __construct(string $morphKey, array $polymorphics)
    {
        $this->morphKey = $morphKey;
        $this->polymorphics = $polymorphics;
    }

    public function getMorphKey(): string
    {
        return $this->morphKey;
    }

    /**
     * @return array<string,RelationInterface> $polymorphics
     */
    public function getPolymorphics(): array
    {
        return $this->polymorphics;
    }

    public function associate(ResultSetInterface $result): \Traversable
    {
        $outerClass = $result->getClass();
        $morphKeySelector = AccessorCreators::createKeySelector($this->morphKey, $outerClass);
        $sortKeyAssignee = AccessorCreators::createKeyAssignee(self::SORT_KEY, $outerClass);
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

        $sortKeySelector = AccessorCreators::createKeySelector(self::SORT_KEY, $outerClass);
        $sortKeyEraser = AccessorCreators::createKeyEraser(self::SORT_KEY, $outerClass);

        return (new ConcatIterator($outerResults))
            ->orderBy($sortKeySelector)
            ->select($sortKeyEraser);
    }
}
