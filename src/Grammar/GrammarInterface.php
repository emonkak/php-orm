<?php

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

interface GrammarInterface
{
    /**
     * @return SelectBuilder
     */
    public function getSelect();

    /**
     * @return InsertBuilder
     */
    public function getInsert();

    /**
     * @return UpdateBuilder
     */
    public function getUpdate();

    /**
     * @return DeleteBuilder
     */
    public function getDelete();

    /**
     * @param mixed $value
     * @return Sql
     */
    public function expression($value);

    /**
     * @param mixed $value
     * @return Sql
     */
    public function literal($value);

    /**
     * @param mixed      $arg1
     * @param mixed|null $arg2
     * @param mixed|null $arg3
     * @param mixed|null $arg4
     * @return Sql
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null);

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
    public function betweenOperator($operator, Sql $lhs, Sql $start, Sql $end);

    /**
     * @param string $operator
     * @param mixed $rhs
     * @return Sql
     */
    public function unaryOperator($operator, Sql $rhs);

    /**
     * @param Sql      $table
     * @param Sql|null $condition
     * @param string   $type
     */
    public function join(Sql $table, Sql $condition = null, $type);

    /**
     * @param Sql    $value
     * @param string $ordering
     * @return QueryInterface
     */
    public function ordering(Sql $expr, $ordering);

    /**
     * @param Sql    $query
     * @param string $type
     * @return QueryInterface
     */
    public function union(Sql $query, $type);

    /**
     * @param Sql    $value
     * @param string $alias
     * @return Sql
     */
    public function alias(Sql $value, $alias);

    /**
     * @param string $string
     * @return string
     */
    public function identifier($string);

    /**
     * @param string   $prefix
     * @param Sql[]    $select
     * @param Sql[]    $from
     * @param Sql[]    $join
     * @param Sql|null $where
     * @param Sql[]    $groupBy
     * @param Sql|null $having
     * @param Sql[]    $orderBy
     * @param integer  $limit
     * @param integer  $offset
     * @param string   $suffix
     * @param Sql[]    $union
     * @return Sql
     */
    public function selectStatement($prefix, array $select, array $from, array $join, Sql $where = null, array $groupBy, Sql $having = null, array $orderBy, $limit, $offset, $suffix, array $union);

    /**
     * @param string   $prefix
     * @param string   $table
     * @param string[] $columns
     * @param Sql[][]  $values
     * @param Sql|null $select
     * @return Sql
     */
    public function insertStatement($prefix, $table, array $columns, array $values, Sql $select = null);

    /**
     * @param string   $prefix
     * @param string   $table
     * @param Sql[]    $update
     * @param Sql|null $where
     * @return Sql
     */
    public function updateStatement($prefix, $table, array $update, Sql $where = null);

    /**
     * @param string   $prefix
     * @param string   $from
     * @param Sql|null $where
     * @return Sql
     */
    public function deleteStatement($prefix, $from, Sql $where = null);
}
