<?php

namespace Emonkak\Orm\QueryBuilder;

class Compiler
{
    /**
     * @param string                   $prefix
     * @param QueryFragmentInterface[] $select
     * @param QueryFragmentInterface[] $from
     * @param QueryFragmentInterface[] $join
     * @param QueryFragmentInterface   $where
     * @param QueryFragmentInterface[] $groupBy
     * @param QueryFragmentInterface   $having
     * @param QueryFragmentInterface[] $orderBy
     * @param integer                  $limit
     * @param integer                  $offset
     * @param QueryBuilderInterface[]  $union
     * @return array (sql: string, binds: mixed[])
     */
    public static function compileSelect($prefix, array $select, array $from = null, array $join, QueryFragmentInterface $where = null, array $groupBy, QueryFragmentInterface $having = null, array $orderBy, $limit, $offset, $suffix, array $union)
    {
        $binds = [];
        $sql = $prefix
             . self::processProjections($select, $binds)
             . self::processFrom($from, $binds)
             . self::processJoin($join, $binds)
             . self::processWhere($where, $binds)
             . self::processGroupBy($groupBy, $binds)
             . self::processHaving($having, $binds)
             . self::processOrderBy($orderBy, $binds)
             . self::processLimit($limit, $binds)
             . self::processOffset($offset, $binds)
             . ($suffix !== null ? ' ' . $suffix : '');

        if (!empty($union)) {
            $sql = '(' . $sql . ')';
        }

        $sql .= self::processUnion($union, $binds);

        return [$sql, $binds];
    }

    /**
     * @param QueryFragmentInterface[] $select
     * @param mixed[]                  &$binds
     * @return string
     */
    private static function processProjections(array $select, array &$binds)
    {
        if (empty($select)) {
            return ' *';
        }

        $sqls = [];
        foreach ($select as $definition) {
            list ($selectSql, $selectBinds) = $definition->build();
            $sqls[] = $selectSql;
            $binds = array_merge($binds, $selectBinds);
        }

        return ' ' . implode(', ', $sqls);
    }

    /**
     * @param QueryFragmentInterface[] $from
     * @param mixed[]                  &$binds
     * @return string
     */
    private static function processFrom(array $from, array &$binds)
    {
        if (empty($from)) {
            return '';
        }

        $sqls = [];
        foreach ($from as $definition) {
            list ($tableSql, $tableBinds) = $definition->build();
            $sqls[] = $tableSql;
            $binds = array_merge($binds, $tableBinds);
        }

        return ' FROM ' . implode(', ', $sqls);
    }

    /**
     * @param array   $join
     * @param mixed[] &$binds
     * @return string
     */
    private static function processJoin(array $join, array &$binds)
    {
        if (empty($join)) {
            return '';
        }

        $sqls = [];
        foreach ($join as $definition) {
            list ($joinSql, $joinBinds) = $definition->build();
            $sqls[] = $joinSql;
            $binds = array_merge($binds, $joinBinds);
        }

        return ' ' . implode(' ', $sqls);
    }

    /**
     * @param QueryFragmentInterface $where
     * @param mixed[]                &$binds
     * @return string
     */
    private static function processWhere(QueryFragmentInterface $where = null, array &$binds)
    {
        if (!isset($where)) {
            return '';
        }

        list ($whereSql, $whereBinds) = $where->build();
        $binds = array_merge($binds, $whereBinds);

        return ' WHERE ' . $whereSql;
    }

    /**
     * @param array   $groupBy
     * @param mixed[] &$binds
     * @return string
     */
    private static function processGroupBy(array $groupBy, array &$binds)
    {
        if (empty($groupBy)) {
            return '';
        }

        $sqls = [];
        foreach ($groupBy as $definition) {
            list ($groupBySql, $groupByBinds) = $definition->build();
            $sqls[] = $groupBySql;
            $binds = array_merge($binds, $groupByBinds);
        }

        return ' GROUP BY ' . implode(', ', $sqls);
    }

    /**
     * @param QueryFragmentInterface $having
     * @param mixed[]                &$binds
     * @return string
     */
    private static function processHaving(QueryFragmentInterface $having = null, array &$binds)
    {
        if (!isset($having)) {
            return '';
        }

        list ($havingSql, $havingBinds) = $having->build();
        $binds = array_merge($binds, $havingBinds);

        return ' HAVING ' . $havingSql;
    }

    /**
     * @param array   $orderBy
     * @param mixed[] &$binds
     * @return string
     */
    private static function processOrderBy(array $orderBy, array &$binds)
    {
        if (empty($orderBy)) {
            return '';
        }

        $sqls = [];
        foreach ($orderBy as $definition) {
            list ($orderBySql, $orderByBinds) = $definition->build();
            $sqls[] = $orderBySql;
            $binds = array_merge($binds, $orderByBinds);
        }

        return ' ORDER BY ' . implode(', ', $sqls);
    }

    /**
     * @param integer $limit
     * @param mixed[] &$binds
     * @return string
     */
    private static function processLimit($limit, array &$binds)
    {
        if ($limit === null) {
            return '';
        }

        $binds[] = $limit;
        return ' LIMIT ?';
    }

    /**
     * @param integer $offset
     * @param mixed[] &$binds
     * @return string
     */
    private static function processOffset($offset, array &$binds)
    {
        if ($offset === null) {
            return '';
        }

        $binds[] = $offset;
        return ' OFFSET ?';
    }

    /**
     * @param array   $union
     * @param mixed[] &$binds
     * @return string
     */
    private static function processUnion(array $union, array &$binds)
    {
        if (empty($union)) {
            return '';
        }

        $sqls = [];
        foreach ($union as $definition) {
            list ($unionSql, $unionBinds) = $definition->build();
            $sqls[] = $unionSql;
            $binds = array_merge($binds, $unionBinds);
        }

        return ' ' . implode(' ', $sqls);
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}