<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Func implements QueryFragmentInterface
{
    use ExpressionHelper;

    /**
     * @var string
     */
    private $func;

    /**
     * @var QueryFragmentInterface[]
     */
    private $args;

    /**
     * @param string                   $func
     * @param QueryFragmentInterface[] $args
     */
    public function __construct($func, array $args)
    {
        $this->func = $func;
        $this->args = $args;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $sqls = [];
        $binds = [];

        foreach ($this->args as $arg) {
            list ($argSql, $argBinds) = $arg->build();
            $sqls[] = $argSql;
            $binds = array_merge($binds, $argBinds);
        }

        return [$this->func . '(' . implode(', ', $sqls) . ')', $binds];
    }
}
