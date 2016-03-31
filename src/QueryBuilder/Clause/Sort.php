<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Sort implements QueryFragmentInterface
{
    /**
     * @var QueryFragmentInterface $expr
     */
    private $expr;

    /**
     * @var string
     */
    private $ordering;

    /**
     * @param QueryFragmentInterface $expr
     * @param string                 $ordering
     */
    public function __construct(QueryFragmentInterface $expr, $ordering)
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
