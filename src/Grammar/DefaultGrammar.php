<?php

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\Sql;
use Emonkak\Orm\QueryBuilderInterface;

class DefaultGrammar implements GrammarInterface
{
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
            return Sql::value($value);
        }
        if (is_array($value)) {
            return Sql::values($value);
        }
        $type = gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted, got '$type'.");
    }

    /**
     * {@inheritDoc}
     */
    public function operator($operator, Sql $lhs, Sql $rhs)
    {
        $sql = "({$lhs->getSql()} $operator {$rhs->getSql()})";
        $bindings = array_merge($lhs->getBindings(), $rhs->getBindings());
        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function betweenOperator($operator, Sql $lhs, Sql $start, Sql $end)
    {
        $sql = "({$lhs->getSql()} $operator {$start->getSql()} AND {$end->getSql()})";
        $bindings = array_merge($lhs->getBindings(), $start->getBindings(), $end->getBindings());
        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function unaryOperator($operator, Sql $rhs)
    {
        $sql = "($operator {$rhs->getSql()})";
        $bindings = $rhs->getBindings();
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
    public function ordering(Sql $expr, $ordering)
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
    public function alias(Sql $value, $alias)
    {
        $sql = $value->getSql() . ' AS ' . $alias;
        $bindings = $value->getBindings();
        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function identifier($string)
    {
        return '`' . str_replace('`', '``', $string) . '`';
    }

    /**
     * {@inheritDoc}
     */
    public function selectStatement($prefix, array $select, array $from, array $join, Sql $where = null, array $groupBy, Sql $having = null, array $orderBy, $limit, $offset, $suffix, array $union)
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
    public function insertStatement($prefix, $into, array $columns, array $values, Sql $select = null)
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
    public function updateStatement($prefix, $table, array $update, Sql $where = null)
    {
        $bindings = [];

        $sql = $prefix . ' ' . $table
             . $this->processSet($update, $bindings)
             . $this->processWhere($where, $bindings);

        return new Sql($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteStatement($prefix, $from, Sql $where = null)
    {
        $bindings = [];

        $sql = $prefix . ' FROM ' . $from
             . $this->processWhere($where, $bindings);

        return new Sql($sql, $bindings);
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
     * @param Sql[]   $from
     * @param mixed[] &$bindings
     * @return string
     */
    private function processFrom(array $from, array &$bindings)
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
     * @param Sql[]    $join
     * @param mixed[]  &$bindings
     * @return string
     */
    private function processJoin(array $join, array &$bindings)
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
