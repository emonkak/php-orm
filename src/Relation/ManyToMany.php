<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;

class ManyToMany implements RelationInterface
{
    const PIVOT_KEY_PREFIX = '__pivot_';

    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var RelationInterface
     */
    private $hasRelation;

    /**
     * @var RelationInterface
     */
    private $belongsToRelation;

    /**
     * @param string            $relationKey
     * @param RelationInterface $hasRelation
     * @param RelationInterface $belongsToRelation
     */
    public function __construct(
        $relationKey,
        RelationInterface $hasRelation,
        RelationInterface $belongsToRelation
    ) {
        $this->relationKey = $relationKey;
        $this->hasRelation = $hasRelation;
        $this->belongsToRelation = $belongsToRelation;
    }

    /**
     * @param RelationInterface $relation
     * @return self
     */
    public function with(RelationInterface $relation)
    {
        return new static(
            $this->relationKey,
            $this->hasRelation->with($relation),
            $this->belongsToRelation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery(array $outerValues, $outerClass)
    {
        $hasRelation = $this->hasRelation;
        $belongsToRelation = $this->belongsToRelation;

        $query = $hasRelation->buildQuery($outerValues, $outerClass)
            ->to($belongsToRelation->getClass())
            ->leftJoin(
                $belongsToRelation->getTable(),
                sprintf(
                    '`%s`.`%s` = `%s`.`%s`',
                    $hasRelation->getTable(),
                    $belongsToRelation->getOuterKey(),
                    $belongsToRelation->getTable(),
                    $belongsToRelation->getInnerKey()
                )
            );

        if (count($query->getSelect()) === 0) {
            $query = $query->select(sprintf('`%s`.*', $belongsToRelation->getTable()));
        }

        return $query
            ->select(
                sprintf('`%s`.`%s`', $hasRelation->getTable(), $hasRelation->getInnerKey()),
                sprintf('`%s`', $this->getPivotKey())
            );
    }

    /**
     * {@inheritDoc}
     */
    public function join(array $outerValues, array $innerValues, $outerClass)
    {
        $outerKeySelector = $this->getOuterKeySelector()->bindTo(null, $outerClass);
        $pivotKeySelector = $this->getPivotKeySelector()->bindTo(null, $this->belongsToRelation->getClass());
        $resultValueSelector = $this->getResultValueSelector()->bindTo(null, $outerClass);

        $collection = Collection::from($outerValues)->groupJoin(
            $innerValues,
            $outerKeySelector,
            $pivotKeySelector,
            $resultValueSelector
        );

        return $collection->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return $this->belongsToRelation->getTable();
    }

    /**
     * {@inheritDoc}
     */
    public function getRelationKey()
    {
        return $this->relationKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getOuterKey()
    {
        return $hasRelation->getOuterKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKey()
    {
        return $hasRelation->getInnerKey();
    }

    /**
     * @return string
     */
    protected function getPivotKey()
    {
        return self::PIVOT_KEY_PREFIX . $this->hasRelation->getInnerKey();
    }

    /**
     * @return \Closure
     */
    protected function getOuterKeySelector()
    {
        $outerKey = $this->hasRelation->getOuterKey();
        return static function($outer) use ($outerKey) {
            return $outer->$outerKey;
        };
    }

    /**
     * @return \Closure
     */
    protected function getInnerKeySelector()
    {
        $innerKey = $this->hasRelation->getInnerKey();
        return static function($inner) use ($innerKey) {
            return $inner->$innerKey;
        };
    }

    /**
     * @return \Closure
     */
    protected function getPivotKeySelector()
    {
        $pivotKey = $this->getPivotKey();
        return static function($inner) use ($pivotKey) {
            $pivotValue = $inner->$pivotKey;
            unset($inner->$pivotKey);
            return $pivotValue;
        };
    }

    /**
     * @return \Closure
     */
    protected function getResultValueSelector()
    {
        $relationKey = $this->relationKey;
        return static function($outer, $inner) use ($relationKey) {
            $outer->$relationKey = $inner;
            return $outer;
        };
    }
}
