<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class Value implements QueryBuilderInterface
{
    use ExpressionHelper;
    use Stringable;

    /**
     * @var mixed $value
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return ['?', [$this->value]];
    }
}
