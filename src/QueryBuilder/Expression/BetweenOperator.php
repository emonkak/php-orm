<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class BetweenOperator implements QueryFragmentInterface
{
    use ExpressionHelper;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var QueryFragmentInterface
     */
    private $lhs;

    /**
     * @var QueryFragmentInterface
     */
    private $min;

    /**
     * @var QueryFragmentInterface
     */
    private $max;

    /**
     * @param string                 $operator
     * @param QueryFragmentInterface $lhs
     * @param QueryFragmentInterface $min
     * @param QueryFragmentInterface $max
     */
    public function __construct($operator, QueryFragmentInterface $lhs, QueryFragmentInterface $min, QueryFragmentInterface $max)
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
