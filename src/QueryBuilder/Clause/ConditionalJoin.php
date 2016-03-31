<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class ConditionalJoin implements QueryFragmentInterface
{
    /**
     * @var QueryFragmentInterface
     */
    private $table;

    /**
     * @var QueryFragmentInterface
     */
    private $condition;

    /**
     * @var string
     */
    private $type;

    /**
     * @param QueryFragmentInterface $table
     * @param QueryFragmentInterface $condition
     * @param string                 $type
     */
    public function __construct(QueryFragmentInterface $table, QueryFragmentInterface $condition, $type)
    {
        $this->table = $table;
        $this->condition = $condition;
        $this->type = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        list ($tableSql, $tableBinds) = $this->table->build();
        list ($conditionSql, $conditionBinds) = $this->condition->build();
        $sql = $this->type . ' ' . $tableSql . ' ON ' . $conditionSql;
        $binds = array_merge($tableBinds, $conditionBinds);
        return [$sql, $binds];
    }
}
