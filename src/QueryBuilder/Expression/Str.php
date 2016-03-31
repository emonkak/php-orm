<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Str implements QueryFragmentInterface
{
    use ExpressionHelper;

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
