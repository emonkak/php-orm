<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class Join implements QueryFragmentInterface
{
    /**
     * @var QueryFragmentInterface $table
     */
    private $table;

    /**
     * @var string
     */
    private $type;

    /**
     * @param QueryFragmentInterface $table
     * @param string                 $type
     */
    public function __construct(QueryFragmentInterface $table, $type)
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
