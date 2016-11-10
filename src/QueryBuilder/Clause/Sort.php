<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class Sort implements QueryBuilderInterface
{
    use Stringable;

    /**
     * @var QueryBuilderInterface $expr
     */
    private $expr;

    /**
     * @var string
     */
    private $ordering;

    /**
     * @param QueryBuilderInterface $expr
     * @param string                $ordering
     */
    public function __construct(QueryBuilderInterface $expr, $ordering)
    {
        $this->expr = $expr;
        $this->ordering = $ordering;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($sql, $binds) = $this->expr->build();
        return [$sql . ' ' . $this->ordering, $binds];
    }
}
