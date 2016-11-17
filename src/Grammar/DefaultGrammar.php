<?php

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\Sql;

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
    public function compileInsert($prefix, $into, array $columns, array $values, Sql $select = null)
    {
        $bindings = [];

        $sql = $prefix
             . $this->processInto($into, $columns)
             . $this->processValues($values, $bindings)
             . $this->processInsertSelect($select, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function compileUpdate($prefix, Sql $table, array $update, Sql $where = null, array $orderBy, $limit)
    {
        $bindings = $table->getBindings();

        $sql = $prefix . ' ' . $table->getSql()
             . $this->processSet($update, $bindings)
             . $this->processWhere($where, $bindings)
             . $this->processOrderBy($orderBy, $bindings)
             . $this->processLimit($limit, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function compileDelete($prefix, array $from, Sql $where = null, array $orderBy, $limit)
    {
        $bindings = [];

        $sql = $prefix
             . $this->processFrom($from, $bindings)
             . $this->processWhere($where, $bindings)
             . $this->processOrderBy($orderBy, $bindings)
             . $this->processLimit($limit, $bindings);

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
            return $value->build()->enclosed();
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
            return $value->build()->enclosed();
        }
        if ($value === null) {
            return new Sql('NULL');
        }
        if (is_scalar($value)) {
            return new Sql('?', [$value]);
        }
        if (is_array($value)) {
            return Sql::values($value);
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
        throw new \UnexpectedValueException("Unexpected between operator, got '$operator'.");
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
     * {@inheritDoc}
     */
    public function identifier($string)
    {
        return '`' . str_replace('`', '``', $string) . '`';
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
            $sqls[] = $definition->getSql();
            $bindings = array_merge($bindings, $definition->getBindings());
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
            $sqls[] = $definition->getSql();
            $bindings = array_merge($bindings, $definition->getBindings());
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
            $sqls[] = $definition->getSql();
            $bindings = array_merge($bindings, $definition->getBindings());
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

        $bindings = array_merge($bindings, $where->getBindings());

        return ' WHERE ' . $where->getSql();
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
            $sqls[] = $definition->getSql();
            $bindings = array_merge($bindings, $definition->getBindings());
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

        $bindings = array_merge($bindings, $having->getBindings());

        return ' HAVING ' . $having->getSql();
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
            $sqls[] = $definition->getSql();
            $bindings = array_merge($bindings, $definition->getBindings());
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
            $sqls[] = $definition->getSql();
            $bindings = array_merge($bindings, $definition->getBindings());
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
    private function processSet(array $update, array &$bindings)
    {
        if (empty($update)) {
            return '';
        }
        $sqls = [];
        foreach ($update as $key => $value) {
            $sqls[] = $key . ' = ' . $value->getSql();
            $bindings = array_merge($bindings, $value->getBindings());
        }
        return ' SET ' . implode(', ', $sqls);
    }
}
