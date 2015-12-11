<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\QueryBuilder\Expression\ExpressionInterface;

class ManyToMany extends OneToMany
{
    /**
     * @var string
     */
    private $intersectionTable;

    /**
     * @var ExpressionInterface
     */
    private $intersectionCondition;

    /**
     * @param PDOInterface        $pdo                   The connection to use in this relation.
     * @param string              $innerClass            The class to map.
     * @param string              $referenceTable        The reference table name.
     * @param string              $referenceColumn       The reference table column.
     * @param string              $intersectionTable     The intersection table name.
     * @param ExpressionInterface $intersectionCondition The intersection table join condition.
     * @param \Closure            $outerKeySelector      The key selector for outer value.
     * @param \Closure            $innerKeySelector      The key selector for inner value.
     * @param \Closure            $resultValueSelector   The result value selector.
     */
    public function __construct(
        PDOInterface $pdo,
        $innerClass,
        $referenceTable,
        $referenceColumn,
        $intersectionTable,
        ExpressionInterface $intersectionCondition,
        \Closure $outerKeySelector,
        \Closure $innerKeySelector,
        \Closure $resultValueSelector
    ) {
        parent::__construct($pdo, $innerClass, $referenceTable, $referenceColumn, $outerKeySelector, $innerKeySelector, $resultValueSelector);

        $this->intersectionTable = $intersectionTable;
        $this->intersectionCondition = $intersectionCondition;
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery($outerClass, array $outerValues)
    {
        return parent::buildQuery($outerClass, $outerValues)
            ->leftJoin($this->intersectionTable, $this->intersectionCondition);
    }
}
