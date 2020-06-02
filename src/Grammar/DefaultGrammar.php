<?php

declare(strict_types=1);

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\Sql;

class DefaultGrammar extends AbstractGrammar
{
    public function operator(string $operator, Sql $lhs, Sql $rhs): Sql
    {
        switch (strtoupper($operator)) {
            case '=':
            case '!=':
            case '<>':
            case '<':
            case '<=':
            case '>':
            case '>=':
            case 'IS':
            case 'IS NOT':
            case 'IN':
            case 'NOT IN':
            case 'LIKE':
            case 'NOT LIKE':
            case 'AND':
            case 'OR':
                $sql = "({$lhs->getSql()} $operator {$rhs->getSql()})";
                $bindings = array_merge($lhs->getBindings(), $rhs->getBindings());
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected operator, got '$operator'.");
    }

    public function betweenOperator(string $operator, Sql $lhs, Sql $start, Sql $end): Sql
    {
        switch (strtoupper($operator)) {
            case 'BETWEEN':
            case 'NOT BETWEEN':
                $sql = "({$lhs->getSql()} $operator {$start->getSql()} AND {$end->getSql()})";
                $bindings = array_merge($lhs->getBindings(), $start->getBindings(), $end->getBindings());
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected between operator, got '$operator'.");
    }

    public function unaryOperator(string $operator, Sql $rhs): Sql
    {
        switch (strtoupper($operator)) {
            case 'NOT':
            case 'EXISTS':
            case 'NOT EXISTS':
            case 'ALL':
            case 'NOT ALL':
            case 'ANY':
            case 'NOT ANY':
            case 'SOME':
            case 'NOT SOME':
                $sql = "($operator {$rhs->getSql()})";
                $bindings = $rhs->getBindings();
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected unary operator, got '$operator'.");
    }

    public function join(Sql $table, ?Sql $condition, string $type): Sql
    {
        if ($condition !== null) {
            $sql = $type . ' ' . $table->getSql() . ' ON ' . $condition->getSql();
            $bindings = array_merge($table->getBindings(), $condition->getBindings());
        } else {
            $sql = $type . ' ' . $table->getSql();
            $bindings = $table->getBindings();
        }
        return new Sql($sql, $bindings);
    }

    public function window(string $name, Sql $specification): Sql
    {
        $sql = $name . ' AS ' . '(' . $specification->getSql() . ')';
        $bindings = $specification->getBindings();
        return new Sql($sql, $bindings);
    }

    public function ordering(Sql $expr, string $ordering): Sql
    {
        switch (strtoupper($ordering)) {
            case 'ASC':
            case 'DESC':
                $sql = $expr->getSql() . ' ' . $ordering;
                $bindings = $expr->getBindings();
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected ordering value, got '$ordering'");
    }

    public function union(Sql $query, string $type): Sql
    {
        $sql = $type . ' ' . $query->getSql();
        $bindings = $query->getBindings();
        return new Sql($sql, $bindings);
    }

    public function alias(Sql $value, string $alias): Sql
    {
        $sql = $value->getSql() . ' AS ' . $alias;
        $bindings = $value->getBindings();
        return new Sql($sql, $bindings);
    }

    public function identifier(string $string): string
    {
        return '`' . str_replace('`', '``', $string) . '`';
    }

    public function selectStatement(string $prefix, array $select, array $from, array $join, ?Sql $where, array $groupBy, ?Sql $having, array $window, array $orderBy, ?int $limit, ?int $offset, string $suffix, array $union): Sql
    {
        $bindings = [];

        $sql = $prefix
             . $this->processSelect($select, $bindings)
             . $this->processFrom($from, $bindings)
             . $this->processJoin($join, $bindings)
             . $this->processWhere($where, $bindings)
             . $this->processGroupBy($groupBy, $bindings)
             . $this->processHaving($having, $bindings)
             . $this->processWindow($window, $bindings)
             . $this->processOrderBy($orderBy, $bindings)
             . $this->processLimit($limit, $bindings)
             . $this->processOffset($offset, $bindings)
             . ($suffix !== '' ? ' ' . $suffix : '');

        $sql .= $this->processUnion($union, $bindings);

        return new Sql($sql, $bindings);
    }

    public function insertStatement(string $prefix, string $into, array $columns, array $values, ?Sql $select): Sql
    {
        $bindings = [];

        $sql = $prefix
             . $this->processInto($into, $columns)
             . $this->processValues($values, $bindings)
             . $this->processInsertSelect($select, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatement(string $prefix, string $table, array $set, ?Sql $where): Sql
    {
        $bindings = [];

        $sql = $prefix . ' ' . $table
             . $this->processSet($set, $bindings)
             . $this->processWhere($where, $bindings);

        return new Sql($sql, $bindings);
    }

    public function deleteStatement(string $prefix, string $from, Sql $where = null): Sql
    {
        $bindings = [];

        $sql = $prefix . ' FROM ' . $from
             . $this->processWhere($where, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * @param Sql[] $select
     * @param array<int,?scalar> $bindings
     */
    private function processSelect(array $select, array &$bindings): string
    {
        if (empty($select)) {
            return ' *';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($select as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' ' . implode(', ', $tmpSqls);
    }

    /**
     * @param Sql[] $from
     * @param array<int,?scalar> $bindings
     */
    private function processFrom(array $from, array &$bindings): string
    {
        if (empty($from)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($from as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' FROM ' . implode(', ', $tmpSqls);
    }

    /**
     * @param Sql[] $join
     * @param array<int,?scalar> $bindings
     */
    private function processJoin(array $join, array &$bindings): string
    {
        if (empty($join)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($join as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' ' . implode(' ', $tmpSqls);
    }

    /**
     * @param ?Sql $where
     * @param array<int,?scalar> $bindings
     */
    private function processWhere(?Sql $where, array &$bindings): string
    {
        if ($where === null) {
            return '';
        }
        $bindings = array_merge($bindings, $where->getBindings());
        return ' WHERE ' . $where->getSql();
    }

    /**
     * @param Sql[] $groupBy
     * @param array<int,?scalar> $bindings
     */
    private function processGroupBy(array $groupBy, array &$bindings): string
    {
        if (empty($groupBy)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($groupBy as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' GROUP BY ' . implode(', ', $tmpSqls);
    }

    /**
     * @param ?Sql $having
     * @param array<int,?scalar> $bindings
     */
    private function processHaving(?Sql $having, array &$bindings): string
    {
        if ($having === null) {
            return '';
        }
        $bindings = array_merge($bindings, $having->getBindings());
        return ' HAVING ' . $having->getSql();
    }

    /**
     * @param Sql[] $window
     * @param array<int,?scalar> $bindings
     */
    private function processWindow(array $window, array &$bindings): string
    {
        if (empty($window)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($window as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' WINDOW ' . implode(', ', $tmpSqls);
    }

    /**
     * @param Sql[] $orderBy
     * @param array<int,?scalar> $bindings
     */
    private function processOrderBy(array $orderBy, array &$bindings): string
    {
        if (empty($orderBy)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($orderBy as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' ORDER BY ' . implode(', ', $tmpSqls);
    }

    /**
     * @param array<int,?scalar> $bindings
     */
    private function processLimit(?int $limit, array &$bindings): string
    {
        if ($limit === null) {
            return '';
        }
        $bindings[] = $limit;
        return ' LIMIT ?';
    }

    /**
     * @param array<int,?scalar> $bindings
     */
    private function processOffset(?int $offset, array &$bindings): string
    {
        if ($offset === null) {
            return '';
        }
        $bindings[] = $offset;
        return ' OFFSET ?';
    }

    /**
     * @param Sql[] $union
     * @param array<int,?scalar> $bindings
     */
    private function processUnion(array $union, array &$bindings): string
    {
        if (empty($union)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($union as $definition) {
            $tmpSqls[] = $definition->getSql();
            $tmpBindings[] = $definition->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' ' . implode(' ', $tmpSqls);
    }

    /**
     * @param string[] $columns
     */
    private function processInto(string $table, array $columns): string
    {
        $sql = ' INTO ' . $table;
        if (!empty($columns)) {
            $sql .= ' (' . implode(', ', $columns) . ')';
        }
        return $sql;
    }

    /**
     * @param Sql[][] $values
     * @param array<int,?scalar> $bindings
     */
    private function processValues(array $values, array &$bindings): string
    {
        if (empty($values)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($values as $row) {
            $innerSqls = [];
            foreach ($row as $value) {
                $innerSqls[] = $value->getSql();
                $tmpBindings[] = $value->getBindings();
            }
            $tmpSqls[] = '(' . implode(', ', $innerSqls) . ')';
        }
        $bindings = array_merge(...$tmpBindings);
        return ' VALUES ' . implode(', ', $tmpSqls);
    }

    /**
     * @param ?Sql $select
     * @param array<int,?scalar> $bindings
     */
    private function processInsertSelect(?Sql $select, array &$bindings): string
    {
        if ($select === null) {
            return '';
        }
        $bindings = array_merge($bindings, $select->getBindings());
        return ' ' . $select->getSql();
    }

    /**
     * @param Sql[] $update
     * @param array<int,?scalar> $bindings
     */
    private function processSet(array $update, array &$bindings): string
    {
        if (empty($update)) {
            return '';
        }
        $tmpSqls = [];
        $tmpBindings = [$bindings];
        foreach ($update as $key => $value) {
            $tmpSqls[] = $key . ' = ' . $value->getSql();
            $tmpBindings[] = $value->getBindings();
        }
        $bindings = array_merge(...$tmpBindings);
        return ' SET ' . implode(', ', $tmpSqls);
    }
}
