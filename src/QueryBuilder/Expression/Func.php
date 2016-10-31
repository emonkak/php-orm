<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

/**
 * @internal
 */
class Func implements QueryBuilderInterface
{
    use ExpressionHelper;
    use ToStringable;

    /**
     * @var string
     */
    private $func;

    /**
     * @var QueryBuilderInterface[]
     */
    private $args;

    /**
     * @param string                  $func
     * @param QueryBuilderInterface[] $args
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
