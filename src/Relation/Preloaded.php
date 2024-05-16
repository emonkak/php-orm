<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;

/**
 * @template TInner
 * @template TKey
 * @implements RelationStrategyInterface<TInner,TKey>
 */
class Preloaded implements RelationStrategyInterface
{
    private string $relationKeyName;

    private string $outerKeyName;

    private string $innerKeyName;

    /**
     * @var TInner[]
     */
    private array $innerElements;

    /**
     * @param TInner[] $innerElements
     */
    public function __construct(
        string $relationKeyName,
        string $outerKeyName,
        string $innerKeyName,
        array $innerElements
    ) {
        $this->relationKeyName = $relationKeyName;
        $this->outerKeyName = $outerKeyName;
        $this->innerKeyName = $innerKeyName;
        $this->innerElements = $innerElements;
    }

    public function getRelationKeyName(): string
    {
        return $this->relationKeyName;
    }

    public function getOuterKeyName(): string
    {
        return $this->outerKeyName;
    }

    public function getInnerKeyName(): string
    {
        return $this->innerKeyName;
    }

    /**
     * @return TInner[]
     */
    public function getInnerElements(): array
    {
        return $this->innerElements;
    }

    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable
    {
        $innerKeySelector = $joinStrategy->getInnerKeySelector();
        $reversedOuterKeys = array_flip($outerKeys);

        $filteredElements = [];

        foreach ($this->innerElements as $innerElement) {
            $innerKeyName = $innerKeySelector($innerElement);
            if (isset($reversedOuterKeys[$innerKeyName])) {
                $filteredElements[] = $innerElement;
            }
        }

        return $filteredElements;
    }
}
