<?php

namespace Emonkak\Orm\QueryBuilder\Grammar;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Sql;

class DefaultGrammar implements GrammarInterface
{
    /**
     * @return DefaultGrammar
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new DefaultGrammar();
        }

        return $instance;
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function compileSelect($prefix, array $select, array $from, array $join, Sql $where = null, array $groupBy, Sql $having = null, array $orderBy, $limit, $offset, $suffix, array $union)
    {
        $bindings = [];
        $sql = $prefix
             . $this->processSelect($select, $bindings)
             . $this->processFrom($from, $bindings)
             . $this->processJoin($join, $bindings)
             . $this->processWhere($where, $bindings)
             . $this->processGroupBy($groupBy, $bindings)
             . $this->processHaving($having, $bindings)
             . $this->processOrderBy($orderBy, $bindings)
             . $this->processLimit($limit, $bindings)
             . $this->processOffset($offset, $bindings)
             . ($suffix !== null ? ' ' . $suffix : '');

        if (!empty($union)) {
            $sql = '(' . $sql . ')';
        }

        $sql .= $this->processUnion($union, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function compileInsert($prefix, $table, array $columns, array $values, Sql $select = null, array $update)
    {
        $bindings = [];
        $sql = $prefix
             . $this->processInto($table, $columns)
             . $this->processValues($values, $bindings)
             . $this->processInsertSelect($select, $bindings)
             . $this->processOnDuplicateKeyUpdate($update, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function lift($value)
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            $query = $value->build();
            return new Sql('(' . $query->getSql() . ')', $query->getBindings());
        }
        if (is_string($value)) {
            return new Sql($value);
        }
        $type = gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted, got '$type'.");
    }

    /**
     * {@inheritDoc}
     */
    public function liftValue($value)
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            $query = $value->build();
            return new Sql('(' . $query->getSql() . ')', $query->getBindings());
        }
        if ($value === null) {
            return new Sql('NULL');
        }
        if (is_scalar($value)) {
            return new Sql('?', [$value]);
        }
        if (is_array($value)) {
            $placeholders = array_fill(0, count($value), '?');
            return new Sql('(' . implode(', ', $placeholders) . ')', array_values($value));
        }
        $type = gettype($value);
        throw new \UnexpectedValueException("Unexpected value, got '$type'.");
    }

    /**
     * {@inheritDoc}
     */
    public function liftCondition($lhs, $operator = null, $rhs1 = null, $rhs2 = null)
    {
        if ($operator === null) {
            return $this->lift($lhs);
        } elseif ($rhs1 === null) {
            $lhs = $this->lift($lhs);
            return $this->unaryOperator($operator, $lhs);
        } elseif ($rhs2 === null) {
            $lhs = $this->lift($lhs);
            $rhs = $this->liftValue($rhs1);
            return $this->operator($operator, $lhs, $rhs);
        } else {
            $lhs = $this->lift($lhs);
            $start = $this->liftValue($rhs1);
            $end = $this->liftValue($rhs2);
            return $this->between($operator, $lhs, $start, $end);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function alias(Sql $value, $alias)
    {
        $sql = $value->getSql() . ' AS ' . $alias;
        $bindings = $value->getBindings();
        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function order(Sql $expr, $ordering)
    {
        $sql = $expr->getSql() . ' ' . $ordering;
        $bindings = $expr->getBindings();
        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function union(Sql $query, $type)
    {
        $sql = $type . ' ' . $query->getSql();
        $bindings = $query->getBindings();
        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function join(Sql $table, Sql $condition = null, $type)
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

    /**
     * {@inheritDoc}
     */
    public function operator($operator, Sql $lhs, Sql $rhs)
    {
        switch (strtoupper($operator)) {
            case '=':
            case '!=':
            case '<>':
            case '<':
            case '<=':
            case '>':
            case '>=':
            case 'IN':
            case 'NOT IN':
            case 'LIKE':
            case 'NOT LIKE':
            case 'REGEXP':
            case 'NOT REGEXP':
            case 'AND':
            case 'OR':
                $sql = "({$lhs->getSql()} $operator {$rhs->getSql()})";
                $bindings = array_merge($lhs->getBindings(), $rhs->getBindings());
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected operator, got '$operator'.");
    }

    /**
     * {@inheritDoc}
     */
    public function between($operator, Sql $lhs, Sql $start, Sql $end)
    {
        switch (strtoupper($operator)) {
            case 'BETWEEN':
            case 'NOT BETWEEN':
                $sql = "({$lhs->getSql()} $operator {$start->getSql()} AND {$end->getSql()})";
                $bindings = array_merge($lhs->getBindings(), $start->getBindings(), $end->getBindings());
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected operator, got '$operator'.");
    }

    /**
     * {@inheritDoc}
     */
    public function unaryOperator($operator, Sql $lhs)
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
                $sql = "($operator {$lhs->getSql()})";
                $bindings = $lhs->getBindings();
                return new Sql($sql, $bindings);

            case 'IS NULL':
            case 'IS NOT NULL':
                $sql = "({$lhs->getSql()} $operator)";
                $bindings = $lhs->getBindings();
                return new Sql($sql, $bindings);
        }
        throw new \UnexpectedValueException("Unexpected operator, got '$operator'.");
    }

    /**
     * @param Sql[]   $select
     * @param mixed[] &$bindings
     * @return string
     */
    private function processSelect(array $select, array &$bindings)
    {
        if (empty($select)) {
            return ' *';
        }

        $sqls = [];
        foreach ($select as $definition) {
            $selectSql = $definition->getSql();
            $selectBindings = $definition->getBindings();
            $sqls[] = $selectSql;
            $bindings = array_merge($bindings, $selectBindings);
        }

        return ' ' . implode(', ', $sqls);
    }

    /**
     * @param Sql[]   $from
     * @param mixed[] &$bindings
     * @return string
     */
    private function processFrom(array $from, array &$bindings)
    {
        if (empty($from)) {
            return '';
        }

        $sqls = [];
        foreach ($from as $definition) {
            $tableSql = $definition->getSql();
            $tableBindings = $definition->getBindings();
            $sqls[] = $tableSql;
            $bindings = array_merge($bindings, $tableBindings);
        }

        return ' FROM ' . implode(', ', $sqls);
    }

    /**
     * @param Sql[]    $join
     * @param mixed[]  &$bindings
     * @return string
     */
    private function processJoin(array $join, array &$bindings)
    {
        if (empty($join)) {
            return '';
        }

        $sqls = [];
        foreach ($join as $definition) {
            $joinSql = $definition->getSql();
            $joinBindings = $definition->getBindings();
            $sqls[] = $joinSql;
            $bindings = array_merge($bindings, $joinBindings);
        }

        return ' ' . implode(' ', $sqls);
    }

    /**
     * @param Sql|null $where
     * @param mixed[]  &$bindings
     * @return string
     */
    private function processWhere(Sql $where = null, array &$bindings)
    {
        if ($where === null) {
            return '';
        }

        $whereSql = $where->getSql();
        $whereBindings = $where->getBindings();

        $bindings = array_merge($bindings, $whereBindings);

        return ' WHERE ' . $whereSql;
    }

    /**
     * @param Sql[]   $groupBy
     * @param mixed[] &$bindings
     * @return string
     */
    private function processGroupBy(array $groupBy, array &$bindings)
    {
        if (empty($groupBy)) {
            return '';
        }

        $sqls = [];
        foreach ($groupBy as $definition) {
            $groupBySql = $definition->getSql();
            $groupByBindings = $definition->getBindings();
            $sqls[] = $groupBySql;
            $bindings = array_merge($bindings, $groupByBindings);
        }

        return ' GROUP BY ' . implode(', ', $sqls);
    }

    /**
     * @param Sql|null $having
     * @param mixed[]  &$bindings
     * @return string
     */
    private function processHaving(Sql $having = null, array &$bindings)
    {
        if ($having === null) {
            return '';
        }

        $havingSql = $having->getSql();
        $havingBindings = $having->getBindings();

        $bindings = array_merge($bindings, $havingBindings);

        return ' HAVING ' . $havingSql;
    }

    /**
     * @param Sql[]   $orderBy
     * @param mixed[] &$bindings
     * @return string
     */
    private function processOrderBy(array $orderBy, array &$bindings)
    {
        if (empty($orderBy)) {
            return '';
        }

        $sqls = [];
        foreach ($orderBy as $definition) {
            $orderBySql = $definition->getSql();
            $orderByBindings = $definition->getBindings();
            $sqls[] = $orderBySql;
            $bindings = array_merge($bindings, $orderByBindings);
        }

        return ' ORDER BY ' . implode(', ', $sqls);
    }

    /**
     * @param integer $limit
     * @param mixed[] &$bindings
     * @return string
     */
    private function processLimit($limit, array &$bindings)
    {
        if ($limit === null) {
            return '';
        }

        $bindings[] = $limit;
        return ' LIMIT ?';
    }

    /**
     * @param integer $offset
     * @param mixed[] &$bindings
     * @return string
     */
    private function processOffset($offset, array &$bindings)
    {
        if ($offset === null) {
            return '';
        }

        $bindings[] = $offset;
        return ' OFFSET ?';
    }

    /**
     * @param Sql[]   $union
     * @param mixed[] &$bindings
     * @return string
     */
    private function processUnion(array $union, array &$bindings)
    {
        if (empty($union)) {
            return '';
        }

        $sqls = [];
        foreach ($union as $definition) {
            $unionSql = $definition->getSql();
            $unionBindings = $definition->getBindings();
            $sqls[] = $unionSql;
            $bindings = array_merge($bindings, $unionBindings);
        }

        return ' ' . implode(' ', $sqls);
    }

    /**
     * @param string   $table
     * @param string[] $columns
     * @return string
     */
    private function processInto($table, array $columns)
    {
        $sql = ' INTO ' . $table;
        if (!empty($columns)) {
            $sql .= ' (' . implode(', ', $columns) . ')';
        }
        return $sql;
    }

    /**
     * @param Sql[][] $values
     * @param mixed[] &$bindings
     * @return string
     */
    private function processValues(array $values, array &$bindings)
    {
        if (empty($values)) {
            return '';
        }
        $sqls = [];
        $bindings = [];
        foreach ($values as $row) {
            $sqls[] = $row->getSql();
            $bindings = array_merge($bindings, $row->getBindings());
        }
        return ' VALUES ' . implode(', ', $sqls);
    }

    /**
     * @param Sql|null $select
     * @param mixed[]  &$bindings
     * @return string
     */
    private function processInsertSelect(Sql $select = null, array &$bindings)
    {
        if ($select === null) {
            return '';
        }
        $bindings = array_merge($bindings, $select->getBindings());
        return ' ' . $select->getSql();
    }

    /**
     * @param Sql[]   $update
     * @param mixed[] &$bindings
     * @return string
     */
    private function processOnDuplicateKeyUpdate(array $update, array &$bindings)
    {
        if (empty($update)) {
            return '';
        }
        $sqls = [];
        foreach ($update as $key => $value) {
            $sqls[] = $key . ' = ' . $value->getSql();
            $bindings = array_merge($bindings, $value->getBindings());
        }
        return ' ON DUPLICATE KEY UPDATE ' . implode(', ', $sqls);
    }
}
