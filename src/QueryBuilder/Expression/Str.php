<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

/**
 * @internal
 */
class Str implements QueryBuilderInterface
{
    use ExpressionHelper;
    use ToStringable;

    /**
     * @var string
     */
    private $expr;

    /**
     * @param string $expr
     */
    public function __construct($expr)
    {
        if (!is_string($expr)) {
            $type = gettype($expr);
            throw new \InvalidArgumentException("The expression must be string, got '$type'");
        }

        $this->expr = $expr;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return [$this->expr, []];
    }
}
