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
    const SORT_KEY = '__sort';

    /**
     * @psalm-var ?class-string<TOuter>
     * @var ?class-string
     */
    private $resultClass;

    /**
     * @psalm-var callable(TOuter):string
     */
    private $morphKeySelector;

    /**
     * @psalm-var array<string,RelationInterface<TOuter,TOuter>>
     * @var array<string,RelationInterface>
     */
    private $relations;

    /**
     * @psalm-param ?class-string<TOuter> $resultClass
     * @psalm-param callable(TOuter):string $morphKeySelector
     * @psalm-param array<string,RelationInterface<TOuter,TOuter>> $relations
     */
    public function __construct(?string $resultClass, callable $morphKeySelector, array $relations)
    {
        $this->resultClass = $resultClass;
        $this->morphKeySelector = $morphKeySelector;
        $this->relations = $relations;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultClass(): ?string
    {
        return $this->resultClass;
    }

    /**
     * @psalm-return callable(TOuter):string
     */
    public function getMorphKeySelector(): callable
    {
        return $this->morphKeySelector;
    }

    /**
     * @psalm-return array<string,RelationInterface<TOuter,TOuter>> $relations
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * {@inheritdoc}
     */
    public function associate(iterable $outerResult, ?string $outerClass): \Traversable
    {
        $morphKeySelector = $this->morphKeySelector;
        /** @psalm-var callable(TOuter,int):TOuter */
        $sortKeyAssignee = AccessorCreators::createKeyAssignee($outerClass, self::SORT_KEY);
        $outerElementsByMorphKey = [];

        foreach ($outerResult as $index => $outerElement) {
            $morphKey = $morphKeySelector($outerElement);
            $outerElement = $sortKeyAssignee($outerElement, $index);
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

        /** @psalm-var callable(TOuter):int */
        $sortKeySelector = AccessorCreators::createKeySelector($outerClass, self::SORT_KEY);
        /** @psalm-var callable(TOuter):TOuter */
        $sortKeyEraser = AccessorCreators::createKeyEraser($outerClass, self::SORT_KEY);

        return (new ConcatIterator($outerResults))
            ->orderBy($sortKeySelector)
            ->select($sortKeyEraser);
    }
}
