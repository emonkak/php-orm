<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;
use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectQuery;

class ManyToMany extends AbstractRelation
{
    /**
     * @var SelectQuery
     */
    private $query;

    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var OneToMany
     */
    private $hasRelation;

    /**
     * @var OneToOne
     */
    private $belongsToRelation;

    /**
     * @param SelectQuery $query
     * @param string      $relationKey
     * @param OneToMany   $hasRelation
     * @param OneToOne    $belongsToRelation
     */
    public function __construct(
        SelectQuery $query,
        $relationKey,
        OneToMany $hasRelation,
        OneToOne $belongsToRelation
    ) {
        $this->query = $query;
        $this->relationKey = $relationKey;
        $this->hasRelation = $hasRelation;
        $this->belongsToRelation = $belongsToRelation;
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new static(
            $this->query->with($relation),
            $this->relationKey,
            $this->hasRelation,
            $this->belongsToRelation
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getResult(array $outerValues, $outerClass)
    {
        $hasRelation = $this->hasRelation;
        $belongsToRelation = $this->belongsToRelation;

        $outerKeySelector = AccessorCreators::toKeySelector($hasRelation->getOuterKey(), $outerClass);
        $outerKeys = array_map($outerKeySelector, $outerValues);

        $query = $this->query
            ->from(sprintf('`%s`', $hasRelation->getTable()))
            ->leftJoin(
                sprintf('`%s`', $belongsToRelation->getTable()),
                sprintf(
                    '`%s`.`%s` = `%s`.`%s`',
                    $hasRelation->getTable(),
                    $belongsToRelation->getOuterKey(),
                    $belongsToRelation->getTable(),
                    $belongsToRelation->getInnerKey()
                )
            )
            ->where(sprintf('`%s`.`%s`', $hasRelation->getTable(), $hasRelation->getInnerKey()), 'IN', $outerKeys);

        if (count($query->getSelect()) === 0) {
            $query = $query->select(sprintf('`%s`.*', $belongsToRelation->getTable()));
        }

        return $query
            ->select(
                sprintf('`%s`.`%s`', $hasRelation->getTable(), $hasRelation->getInnerKey()),
                sprintf('`%s`', $this->getPivotKey())
            )
            ->getResult($this->belongsToRelation->getConnection(), $this->belongsToRelation->getClass());
    }

    /**
     * {@inheritDoc}
     */
    protected function doJoin(array $outerValues, $outerClass, ResultSetInterface $inner)
    {
        $innerValues = $inner->all();
        $outerKeySelector = AccessorCreators::toKeySelector($this->hasRelation->getOuterKey(), $outerClass);
        $pivotKeySelector = AccessorCreators::toKeySelector($this->getPivotKey(), $this->belongsToRelation->getClass());
        $resultValueSelector = AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);

        $collection = Collection::from($outerValues)->groupJoin(
            $innerValues,
            $outerKeySelector,
            $pivotKeySelector,
            $resultValueSelector
        );

        return $collection->getIterator();
    }

    /**
     * @return string
     */
    protected function getPivotKey()
    {
        return '__pivot_' . $this->hasRelation->getInnerKey();
    }
}
