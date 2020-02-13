<?php

declare(strict_types=1);

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

interface GrammarInterface
{
    public function getSelectBuilder(): SelectBuilder;

    public function getInsertBuilder(): InsertBuilder;

    public function getUpdateBuilder(): UpdateBuilder;

    public function getDeleteBuilder(): DeleteBuilder;

    /**
     * @param QueryBuilderInterface|Sql|string $value
     */
    public function lift($value): Sql;

    /**
     * @param ?scalar|array<int,?scalar> $value
     */
    public function literal($value): Sql;

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null): Sql;

    public function operator(string $operator, Sql $lhs, Sql $rhs): Sql;

    public function betweenOperator(string $operator, Sql $lhs, Sql $start, Sql $end): Sql;

    public function unaryOperator(string $operator, Sql $rhs): Sql;

    public function join(Sql $table, ?Sql $condition, string $type): Sql;

    public function window(string $name, Sql $specification): Sql;

    public function ordering(Sql $expr, string $ordering): sql;

    public function union(Sql $query, string $type): Sql;

    public function alias(Sql $value, string $alias): Sql;

    public function identifier(string $string): string;

    /**
     * @param Sql[]  $select
     * @param Sql[]  $from
     * @param Sql[]  $join
     * @param Sql[]  $groupBy
     * @param Sql[]  $window
     * @param Sql[]  $orderBy
     * @param Sql[]  $union
     */
    public function selectStatement(string $prefix, array $select, array $from, array $join, ?Sql $where, array $groupBy, ?Sql $having, array $window, array $orderBy, ?int $limit, ?int $offset, string $suffix, array $union): Sql;

    /**
     * @param string[] $columns
     * @param Sql[][] $values
     */
    public function insertStatement(string $prefix, string $table, array $columns, array $values, ?Sql $select): Sql;

    /**
     * @param Sql[] $set
     */
    public function updateStatement(string $prefix, string $table, array $set, ?Sql $where): Sql;

    public function deleteStatement(string $prefix, string $from, ?Sql $where): Sql;
}
