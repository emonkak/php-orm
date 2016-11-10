<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class Values implements QueryBuilderInterface
{
    use ExpressionHelper;
    use Stringable;

    /**
     * @var QueryBuilderInterface[]
     */
    private $values;

    /**
     * @var QueryBuilderInterface[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $sqls = [];
        $binds = [];

        foreach ($this->values as $value) {
            list ($valueSql, $valueBinds) = $value->build();
            $sqls[] = $valueSql;
            $binds = array_merge($binds, $valueBinds);
        }

        return ['(' . implode(', ', $sqls) . ')', $binds];
    }
}
