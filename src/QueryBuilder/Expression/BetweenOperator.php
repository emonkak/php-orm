<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

/**
 * @internal
 */
class BetweenOperator implements QueryBuilderInterface
{
    use ExpressionHelper;
    use ToStringable;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var QueryBuilderInterface
     */
    private $lhs;

    /**
     * @var QueryBuilderInterface
     */
    private $min;

    /**
     * @var QueryBuilderInterface
     */
    private $max;

    /**
     * @param string                $operator
     * @param QueryBuilderInterface $lhs
     * @param QueryBuilderInterface $min
     * @param QueryBuilderInterface $max
     */
    public function __construct($operator, QueryBuilderInterface $lhs, QueryBuilderInterface $min, QueryBuilderInterface $max)
    {
        $this->operator = $operator;
        $this->lhs = $lhs;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($lhsSql, $lhsBinds) = $this->lhs->build();
        list ($minSql, $minBinds) = $this->min->build();
        list ($maxSql, $maxBinds) = $this->max->build();
        return ["($lhsSql $this->operator $minSql AND $maxSql)", array_merge($lhsBinds, $minBinds, $maxBinds)];
    }
}
