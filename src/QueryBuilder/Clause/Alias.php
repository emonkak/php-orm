<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class Alias implements QueryBuilderInterface
{
    use Stringable;

    /**
     * @var QueryBuilderInterface $value
     */
    private $value;

    /**
     * @var string
     */
    private $alias;

    /**
     * @param QueryBuilderInterface $value
     * @param string                $alias
     */
    public function __construct(QueryBuilderInterface $value, $alias)
    {
        $this->value = $value;
        $this->alias = $alias;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($sql, $binds) = $this->value->build();
        return [$sql . ' AS ' . $this->alias, $binds];
    }
}
