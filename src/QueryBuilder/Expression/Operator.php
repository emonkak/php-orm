<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class Operator implements QueryBuilderInterface
{
    use ExpressionHelper;
    use Stringable;

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
    private $rhs;

    /**
     * @param string                 $operator
     * @param QueryBuilderInterface $lhs
     * @param QueryBuilderInterface $rhs
     */
    public function __construct($operator, QueryBuilderInterface $lhs, QueryBuilderInterface $rhs)
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
