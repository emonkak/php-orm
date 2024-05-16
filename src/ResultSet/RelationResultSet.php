<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\Relation\RelationInterface;

/**
 * @template T
 * @template TResult
 * @implements \IteratorAggregate<TResult>
 * @implements ResultSetInterface<TResult>
 */
class RelationResultSet implements \IteratorAggregate, ResultSetInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    /**
     * @var ResultSetInterface<T>
     */
    private ResultSetInterface $outerResult;

    /**
     * @var ?class-string
     */
    private ?string $outerClass;

    /**
     * @var RelationInterface<T,TResult>
     */
    private RelationInterface $relation;

    /**
     * @param ResultSetInterface<T> $outerResult
     * @param ?class-string $outerClass
     * @param RelationInterface<T,TResult> $relation
     */
    public function __construct(ResultSetInterface $outerResult, ?string $outerClass, RelationInterface $relation)
    {
        $this->outerResult = $outerResult;
        $this->outerClass = $outerClass;
        $this->relation = $relation;
    }

    /**
     * @return ResultSetInterface<T>
     */
    public function getOuterResult(): ResultSetInterface
    {
        return $this->outerResult;
    }

    /**
     * @return ?class-string
     */
    public function getOuterClass(): ?string
    {
        return $this->outerClass;
    }

    public function getIterator(): \Traversable
    {
        return $this->relation->associate($this->outerResult, $this->outerClass);
    }
}
