<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

/**
 * @internal
 */
class PostfixOperator implements QueryBuilderInterface
{
    use ExpressionHelper;
    use ToStringable;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var QueryBuilderInterface
     */
    private $value;

    /**
     * @param string                 $operator
     * @param QueryBuilderInterface $value
     */
    public function __construct($operator, QueryBuilderInterface $value)
    {
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($sql, $binds) = $this->value->build();
        return ["($sql $this->operator)", $binds];
    }
}
