<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class PrefixOperator implements QueryFragmentInterface
{
    use ExpressionHelper;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var QueryFragmentInterface
     */
    private $value;

    /**
     * @param string                 $operator
     * @param QueryFragmentInterface $value
     */
    public function __construct($operator, QueryFragmentInterface $value)
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
        return ["($this->operator $sql)", $binds];
    }
}
