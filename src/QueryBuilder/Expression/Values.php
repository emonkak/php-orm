<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Values implements QueryFragmentInterface
{
    use ExpressionHelper;

    /**
     * @var QueryFragmentInterface[]
     */
    private $values;

    /**
     * @var QueryFragmentInterface[] $values
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
