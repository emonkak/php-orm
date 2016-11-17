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
     * @var AbstractRelation
     */
    private $oneToMany;

    /**
     * @var AbstractRelation
     */
    private $manyToOne;

    /**
     * @param string           $relationKey
     * @param AbstractRelation $oneToMany
     * @param AbstractRelation $manyToOne
     */
    public function __construct(
        $relationKey,
        AbstractRelation $oneToMany,
        AbstractRelation $manyToOne
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
        $pivotKeySelector = AccessorCreators::toPivotKeySelector($this->getPivotKey(), $this->manyToOne->getFetcher()->getClass());
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

        $builder = $oneToMany->getBuilder();
        $grammar = $builder->getGrammar();

        $builder = $oneToMany->getBuilder()
            ->select($grammar->identifier($manyToOne->getTable()) . '.*')
            ->select(
                $grammar->identifier($oneToMany->getTable()) . '.' . $grammar->identifier($oneToMany->getInnerKey()),
                $grammar->identifier($this->getPivotKey())
            )
            ->from($grammar->identifier($oneToMany->getTable()))
            ->outerJoin(
                $grammar->identifier($manyToOne->getTable()),
                sprintf(
                    '%s.%s = %s.%s',
                    $grammar->identifier($oneToMany->getTable()),
                    $grammar->identifier($oneToMany->getInnerKey()),
                    $grammar->identifier($manyToOne->getTable()),
                    $grammar->identifier($manyToOne->getInnerKey())
                )
            )
            ->where($grammar->identifier($oneToMany->getTable()) . '.' . $grammar->identifier($oneToMany->getInnerKey()), 'IN', $outerKeys);

        return $builder
            ->getResult($manyToOne->getConnection(), $manyToOne->getFetcher());
    }

    /**
     * @return string
     */
    protected function getPivotKey()
    {
        return '__pivot_' . $this->oneToMany->getInnerKey();
    }
}
