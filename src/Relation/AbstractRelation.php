<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Query\ExecutableQueryInterface;
use Emonkak\Orm\Query\SelectQuery;

abstract class AbstractRelation implements RelationInterface
{
    /**
     * @var PDOInterface
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $innerClass;

    /**
     * @var string
     */
    protected $foreignTable;

    /**
     * @var string
     */
    protected $foreignKey;

    /**
     * @var \Closure
     */
    protected $outerKeySelector;

    /**
     * @var \Closure
     */
    protected $innerKeySelector;

    /**
     * @var \Closure
     */
    protected $resultValueSelector;

    /**
     * @param PDOInterface $pdo                 The connection to use in this relation.
     * @param string       $innerClass          The class to map.
     * @param string       $foreignTable        The foreign table name.
     * @param string       $foreignKey          The foreign table key.
     * @param \Closure     $outerKeySelector    The key selector for outer value.
     * @param \Closure     $innerKeySelector    The key selector for inner value.
     * @param \Closure     $resultValueSelector The result value selector.
     */
    public function __construct(
        PDOInterface $pdo,
        $innerClass,
        $foreignTable,
        $foreignKey,
        \Closure $outerKeySelector,
        \Closure $innerKeySelector,
        \Closure $resultValueSelector
    ) {
        $this->pdo = $pdo;
        $this->innerClass = $innerClass;
        $this->foreignTable = $foreignTable;
        $this->foreignKey = $foreignKey;
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->resultValueSelector = $resultValueSelector;
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery($outerClass, array $outerValues)
    {
        $outerKeySelector = \Closure::bind($this->outerKeySelector, null, $outerClass);
        $outerKeys = array_map($outerKeySelector, $outerValues);

        return SelectQuery::create()
            ->to($this->innerClass)
            ->from($this->foreignTable)
            ->where($this->foreignTable . '.' . $this->foreignKey, 'IN', $outerKeys);
    }

    /**
     * {@inheritDoc}
     */
    public function executeQuery(ExecutableQueryInterface $query)
    {
        return $query->execute($this->pdo);
    }
}
