<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class Join implements QueryBuilderInterface
{
    use Stringable;

    /**
     * @var QueryBuilderInterface $table
     */
    private $table;

    /**
     * @var string
     */
    private $type;

    /**
     * @param QueryBuilderInterface $table
     * @param string                $type
     */
    public function __construct(QueryBuilderInterface $table, $type)
    {
        $this->table = $table;
        $this->type = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($sql, $binds) = $this->table->build();
        return [$this->type . ' ' . $sql, $binds];
    }
}
