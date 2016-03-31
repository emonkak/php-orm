<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Alias implements QueryFragmentInterface
{
    /**
     * @var QueryFragmentInterface $value
     */
    private $value;

    /**
     * @var string
     */
    private $alias;

    /**
     * @param QueryFragmentInterface $value
     * @param string                 $alias
     */
    public function __construct(QueryFragmentInterface $value, $alias)
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
