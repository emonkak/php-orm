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
     * @psalm-var ResultSetInterface<T>
     * @var ResultSetInterface
     */
    private $outerResult;

    /**
     * @psalm-var ?class-string<T>
     * @var ?class-string
     */
    private $outerClass;

    /**
     * @psalm-var RelationInterface<T,TResult>
     * @var RelationInterface
     */
    private $relation;

    /**
     * @psalm-param ResultSetInterface<T> $outerResult
     * @psalm-param ?class-string<T> $outerClass
     * @psalm-param RelationInterface<T,TResult> $relation
     */
    public function __construct(ResultSetInterface $outerResult, ?string $outerClass, RelationInterface $relation)
    {
        $this->outerResult = $outerResult;
        $this->outerClass = $outerClass;
        $this->relation = $relation;
    }

    /**
     * @psalm-return ResultSetInterface<T>
     */
    public function getOuterResult(): ResultSetInterface
    {
        return $this->outerResult;
    }

    /**
     * @psalm-return ?class-string<T>
     */
    public function getOuterClass(): ?string
    {
        return $this->outerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return $this->relation->associate($this->outerResult, $this->outerClass);
    }
}
