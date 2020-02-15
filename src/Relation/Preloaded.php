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
    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var string
     */
    private $outerKey;

    /**
     * @var string
     */
    private $innerKey;

    /**
     * @psalm-var TInner[]
     * @var mixed[]
     */
    private $innerElements;

    /**
     * @psalm-param TInner[] $innerElements
     */
    public function __construct(
        string $relationKey,
        string $outerKey,
        string $innerKey,
        array $innerElements
    ) {
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->innerElements = $innerElements;
    }

    public function getRelationKey(): string
    {
        return $this->relationKey;
    }

    public function getOuterKey(): string
    {
        return $this->outerKey;
    }

    public function getInnerKey(): string
    {
        return $this->innerKey;
    }

    /**
     * @psalm-return TInner[]
     */
    public function getInnerElements(): array
    {
        return $this->innerElements;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable
    {
        $innerKeySelector = $joinStrategy->getInnerKeySelector();
        $reversedOuterKeys = array_flip($outerKeys);

        $filteredElements = [];

        foreach ($this->innerElements as $innerElement) {
            $innerKey = $innerKeySelector($innerElement);
            if (isset($reversedOuterKeys[$innerKey])) {
                $filteredElements[] = $innerElement;
            }
        }

        return $filteredElements;
    }
}
