<?php

namespace Emonkak\Orm\QueryBuilder\Clause;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class ConditionalJoin implements QueryBuilderInterface
{
    use Stringable;

    /**
     * @var QueryBuilderInterface
     */
    private $table;

    /**
     * @var QueryBuilderInterface
     */
    private $condition;

    /**
     * @var string
     */
    private $type;

    /**
     * @param QueryBuilderInterface $table
     * @param QueryBuilderInterface $condition
     * @param string                $type
     */
    public function __construct(QueryBuilderInterface $table, QueryBuilderInterface $condition, $type)
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
