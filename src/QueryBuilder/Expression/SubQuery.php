<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

/**
 * @internal
 */
class SubQuery implements QueryBuilderInterface
{
    use ExpressionHelper;
    use ToStringable;

    /**
     * @var QueryBuilderInterface
     */
    private $query;

    /**
     * @param QueryBuilderInterface $query
     */
    public function __construct(QueryBuilderInterface $query)
    {
        $this->query = $query;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($sql, $binds) = $this->query->build();

        return ['(' . $sql . ')', $binds];
    }
}
