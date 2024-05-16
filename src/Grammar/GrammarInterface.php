<?php

declare(strict_types=1);

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

interface GrammarInterface
{
    public function getSelectBuilder(): SelectBuilder;

    public function getInsertBuilder(): InsertBuilder;

    public function getUpdateBuilder(): UpdateBuilder;

    public function getDeleteBuilder(): DeleteBuilder;

    public function lvalue(mixed $value): Sql;

    public function rvalue(mixed $value): Sql;

    public function condition(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): Sql;

    public function operator(string $operator, Sql $lhs, Sql $rhs): Sql;

    public function betweenOperator(string $operator, Sql $lhs, Sql $start, Sql $end): Sql;

    public function unaryOperator(string $operator, Sql $rhs): Sql;

    public function join(Sql $table, ?Sql $condition, string $type): Sql;

    public function window(string $name, Sql $specification): Sql;

    public function ordering(Sql $expr, string $ordering): Sql;

    public function union(Sql $query, string $type): Sql;

    public function alias(Sql $value, string $alias): Sql;

    public function identifier(string $string): string;

    /**
     * @param Sql[] $select
     * @param Sql[] $from
     * @param Sql[] $join
     * @param Sql[] $groupBy
     * @param Sql[] $window
     * @param Sql[] $orderBy
     * @param Sql[] $union
     */
    public function selectStatement(string $prefix, array $select, array $from, array $join, ?Sql $where, array $groupBy, ?Sql $having, array $window, array $orderBy, ?int $limit, ?int $offset, string $suffix, array $union): Sql;

    /**
     * @param string[] $columns
     * @param Sql[][] $values
     */
    public function insertStatement(string $prefix, string $into, array $columns, array $values, ?Sql $select): Sql;

    /**
     * @param Sql[] $set
     */
    public function updateStatement(string $prefix, string $table, array $set, ?Sql $where): Sql;

    public function deleteStatement(string $prefix, string $from, ?Sql $where): Sql;
}
