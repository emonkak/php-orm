<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @internal
 */
class ManyToMany implements RelationInterface
{
    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var Relation
     */
    private $oneToMany;

    /**
     * @var Relation
     */
    private $manyToOne;

    /**
     * @param string   $relationKey
     * @param Relation $oneToMany
     * @param Relation $manyToOne
     */
    public function __construct(
        $relationKey,
        Relation $oneToMany,
        Relation $manyToOne
    ) {
        $this->relationKey = $relationKey;
        $this->oneToMany = $oneToMany;
        $this->manyToOne = $manyToOne;
    }

    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $result)
    {
        $outerElements = $result->toArray();
        if (empty($outerElements)) {
            return new \EmptyIterator();
        }

        $outerClass = $result->getClass();
        $outerKeySelector = AccessorCreators::toKeySelector($this->oneToMany->getOuterKey(), $outerClass);
        $pivotKeySelector = AccessorCreators::toPivotKeySelector($this->getPivotKey(), $this->manyToOne->getClass());
        $resultSelector = AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
        $joinStrategy = $this->oneToMany->getJoinStrategy();

        $outerKeys = array_map($outerKeySelector, $outerElements);
        $innerResult = $this->getResult($outerKeys);

        return $joinStrategy(
            $outerElements,
            $innerResult,
            $outerKeySelector,
            $pivotKeySelector,
            $resultSelector
        );
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new ManyToMany(
            $this->relationKey,
            $this->oneToMany->with($relation),
            $this->manyToOne
        );
    }

    /**
     * @param mixed[] $outerKeys
     * @return ResultSetInterface
     */
    protected function getResult($outerKeys)
    {
        $oneToMany = $this->oneToMany;
        $manyToOne = $this->manyToOne;

        $query = $oneToMany->getQuery()
            ->from(sprintf('`%s`', $oneToMany->getTable()))
            ->leftJoin(
                sprintf('`%s`', $manyToOne->getTable()),
                sprintf(
                    '`%s`.`%s` = `%s`.`%s`',
                    $oneToMany->getTable(),
                    $oneToMany->getInnerKey(),
                    $manyToOne->getTable(),
                    $manyToOne->getInnerKey()
                )
            )
            ->where(sprintf('`%s`.`%s`', $oneToMany->getTable(), $oneToMany->getInnerKey()), 'IN', $outerKeys);

        if (count($query->getSelect()) === 0) {
            $query = $query->select(sprintf('`%s`.*', $manyToOne->getTable()));
        }

        return $query
            ->select(
                sprintf('`%s`.`%s`', $oneToMany->getTable(), $oneToMany->getInnerKey()),
                sprintf('`%s`', $this->getPivotKey())
            )
            ->getResult($manyToOne->getConnection(), $manyToOne->getClass());
    }

    /**
     * @return string
     */
    protected function getPivotKey()
    {
        return '__pivot_' . $this->oneToMany->getInnerKey();
    }
}
