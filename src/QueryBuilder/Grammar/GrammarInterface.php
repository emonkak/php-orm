<?php

namespace Emonkak\Orm\QueryBuilder\Grammar;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Sql;

interface GrammarInterface
{
    /**
     * @param string  $prefix
     * @param Sql[]   $select
     * @param Sql[]   $from
     * @param Sql[]   $join
     * @param Sql     $where
     * @param Sql[]   $groupBy
     * @param Sql     $having
     * @param Sql[]   $orderBy
     * @param integer $limit
     * @param integer $offset
     * @param Sql[]   $union
     * @return Sql
     */
    public function compileSelect($prefix, array $select, array $from, array $join, Sql $where = null, array $groupBy, Sql $having = null, array $orderBy, $limit, $offset, $suffix, array $union);

    /**
     * @param string   $prefix
     * @param string   $table
     * @param string[] $columns
     * @param Sql[][]  $values
     * @param Sql|null $select
     * @param Sql[]    $union
     * @return Sql
     */
    public function compileInsert($prefix, $table, array $columns, array $values, Sql $select = null, array $update);

    /**
     * @param mixed $value
     * @return Sql
     */
    public function lift($value);

    /**
     * @param mixed $value
     * @return Sql
     */
    public function liftValue($value);

    /**
     * @param Sql    $value
     * @param string $alias
     * @return Sql
     */
    public function alias(Sql $value, $alias);

    /**
     * @param Sql    $value
     * @param string $ordering
     * @return Sql
     */
    public function order(Sql $expr, $ordering);

    /**
     * @param Sql    $query
     * @param string $type
     * @return Sql
     */
    public function union(Sql $query, $type);

    /**
     * @param string $operator
     * @param Sql    $lhs
     * @param Sql    $rhs
     * @return Sql
     */
    public function operator($operator, Sql $lhs, Sql $rhs);

    /**
     * @param string $operator
     * @param Sql    $lhs
     * @param Sql    $start
     * @param Sql    $end
     * @return Sql
     */
    public function between($operator, Sql $lhs, Sql $start, Sql $end);

    /**
     * @param string $operator
     * @param Sql    $lhs
     * @return Sql
     */
    public function unaryOperator($operator, Sql $lhs);
}
