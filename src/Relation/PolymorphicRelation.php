<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Enumerable\Iterator\ConcatIterator;

/**
 * @template TOuter
 * @implements RelationInterface<TOuter,TOuter>
 */
class PolymorphicRelation implements RelationInterface
{
    public const SORT_KEY = '__sort';

    /**
     * @var ?class-string
     */
    private ?string $resultClass;

    /**
     * @var callable(TOuter):string
     */
    private $morphKeySelector;

    /**
     * @var array<string,RelationInterface<TOuter,TOuter>>
     */
    private array $relations;

    /**
     * @param ?class-string $resultClass
     * @param callable(TOuter):string $morphKeySelector
     * @param array<string,RelationInterface<TOuter,TOuter>> $relations
     */
    public function __construct(?string $resultClass, callable $morphKeySelector, array $relations)
    {
        $this->resultClass = $resultClass;
        $this->morphKeySelector = $morphKeySelector;
        $this->relations = $relations;
    }

    public function getResultClass(): ?string
    {
        return $this->resultClass;
    }

    /**
     * @return callable(TOuter):string
     */
    public function getMorphKeySelector(): callable
    {
        return $this->morphKeySelector;
    }

    /**
     * @return array<string,RelationInterface<TOuter,TOuter>> $relations
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function associate(iterable $outerResult, ?string $outerClass): \Traversable
    {
        $morphKeySelector = $this->morphKeySelector;
        /** @var callable(TOuter,int):TOuter */
        $sortKeyAssignor = AccessorCreators::createKeyAssignor($outerClass, self::SORT_KEY);
        $outerElementsByMorphKey = [];

        foreach ($outerResult as $index => $outerElement) {
            $morphKey = $morphKeySelector($outerElement);
            $outerElement = $sortKeyAssignor($outerElement, $index);
            $outerElementsByMorphKey[$morphKey][] = $outerElement;
        }

        $outerResults = [];

        foreach ($outerElementsByMorphKey as $morphKey => $outerElements) {
            if (isset($this->relations[$morphKey])) {
                $relation = $this->relations[$morphKey];
                $outerResults[] = $relation->associate($outerElements, $outerClass);
            } else {
                $outerResults[] = $outerElements;
            }
        }

        /** @var callable(TOuter):int */
        $sortKeySelector = AccessorCreators::createKeySelector($outerClass, self::SORT_KEY);
        /** @var callable(TOuter,array-key):TOuter */
        $sortKeyEraser = AccessorCreators::createKeyEraser($outerClass, self::SORT_KEY);

        return (new ConcatIterator($outerResults))
            ->orderBy($sortKeySelector)
            ->select($sortKeyEraser);
    }
}
