<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class RelationResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var ResultSetInterface
     */
    private $result;

    /**
     * @var RelationInterface
     */
    private $relation;

    /**
     * @param ResultSetInterface $result
     * @param RelationInterface  $relation
     */
    public function __construct(ResultSetInterface $result, RelationInterface $relation)
    {
        $this->result = $result;
        $this->relation = $relation;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ?string
    {
        return $this->result->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->relation->associate($this->result);
    }
}
