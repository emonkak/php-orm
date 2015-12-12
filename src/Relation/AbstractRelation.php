<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ExecutableQueryInterface;
use Emonkak\QueryBuilder\QueryBuilderInterface;

abstract class AbstractRelation implements RelationInterface
{
    /**
     * @var ExecutableQueryInterface
     */
    private $query;

    /**
     * @var string
     */
    private $table;

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
     * @param array $attrs
     * @return self
     */
    public static function of(array $attrs)
    {
        $requiredKeys = ['query', 'table', 'relationKey', 'outerKey', 'innerKey'];
        $actualKeys = array_keys($attrs);
        $diffKeys = array_diff($requiredKeys, $actualKeys);

        if (!empty($diffKeys)) {
            throw new \InvalidArgumentException('Missing some keys: ' . implode(',', $diffKeys));
        }

        return new static(
            $attrs['query'],
            $attrs['table'],
            $attrs['relationKey'],
            $attrs['outerKey'],
            $attrs['innerKey']
        );
    }

    /**
     * @param ExecutableQueryInterface $query
     * @param string                   $table
     * @param string                   $relationKey
     * @param string                   $outerKey
     * @param string                   $innerKey
     */
    public function __construct(
        ExecutableQueryInterface $query,
        $table,
        $relationKey,
        $outerKey,
        $innerKey
    ) {
        $this->query = $query;
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
    }

    /**
     * @param RelationInterface $relation
     * @return self
     */
    public function with(RelationInterface $relation)
    {
        return new static(
            $this->query->with($relation),
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery(array $outerValues, $outerClass)
    {
        $outerKeySelector = $this->getOuterKeySelector()->bindTo(null, $outerClass);
        $outerKeys = array_map($outerKeySelector, $outerValues);

        return $this->query
            ->from($this->table)
            ->where(sprintf('`%s`.`%s`', $this->table, $this->innerKey), 'IN', $outerKeys);
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->query->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return $this->table;
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
        return $this->outerKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKey()
    {
        return $this->innerKey;
    }

    /**
     * @return \Closure
     */
    protected function getOuterKeySelector()
    {
        $outerKey = $this->outerKey;
        return static function($outer) use ($outerKey) {
            return $outer->$outerKey;
        };
    }

    /**
     * @return \Closure
     */
    protected function getInnerKeySelector()
    {
        $innerKey = $this->innerKey;
        return static function($inner) use ($innerKey) {
            return $inner->$innerKey;
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
