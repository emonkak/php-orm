<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Query\SelectQuery;
use Emonkak\Orm\Query\QueryInterface;

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
    protected $referenceTable;

    /**
     * @var string
     */
    protected $referenceKey;

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
     * @param string       $referenceTable      The reference table name.
     * @param string       $referenceKey        The reference table key.
     * @param \Closure     $outerKeySelector    The key selector for outer value.
     * @param \Closure     $innerKeySelector    The key selector for inner value.
     * @param \Closure     $resultValueSelector The result value selector.
     */
    public function __construct(
        PDOInterface $pdo,
        $innerClass,
        $referenceTable,
        $referenceKey,
        \Closure $outerKeySelector,
        \Closure $innerKeySelector,
        \Closure $resultValueSelector
    ) {
        $this->pdo = $pdo;
        $this->innerClass = $innerClass;
        $this->referenceTable = $referenceTable;
        $this->referenceKey = $referenceKey;
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->resultValueSelector = $resultValueSelector;
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery($outerClass, array $outerValues)
    {
        $outerKeys = array_map(\Closure::bind($this->outerKeySelector, null, $outerClass), $outerValues);

        return SelectQuery::create()
            ->to($this->innerClass)
            ->from($this->referenceTable)
            ->where($this->referenceKey, 'IN', $outerKeys);
    }

    /**
     * {@inheritDoc}
     */
    public function executeQuery(QueryInterface $query)
    {
        return $query->execute($this->pdo);
    }
}
