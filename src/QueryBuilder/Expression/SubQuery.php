<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class SubQuery implements QueryFragmentInterface
{
    use ExpressionHelper;

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
