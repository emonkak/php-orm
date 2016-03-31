<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Operator implements QueryFragmentInterface
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
    private $rhs;

    /**
     * @param string                 $operator
     * @param QueryFragmentInterface $lhs
     * @param QueryFragmentInterface $rhs
     */
    public function __construct($operator, QueryFragmentInterface $lhs, QueryFragmentInterface $rhs)
    {
        $this->operator = $operator;
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($lhsSql, $lhsBinds) = $this->lhs->build();
        list ($rhsSql, $rhsBinds) = $this->rhs->build();
        return ["($lhsSql $this->operator $rhsSql)", array_merge($lhsBinds, $rhsBinds)];
    }
}
