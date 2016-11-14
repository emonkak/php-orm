<?php

namespace Emonkak\Orm\QueryBuilder;

use Emonkak\Orm\QueryBuilder\Grammar\GrammarInterface;
use Emonkak\Orm\QueryBuilder\Grammar\DefaultGrammar;

class SelectBuilder implements QueryBuilderInterface
{
    /**
     * @var GrammarInterface
     */
    private $grammar;

    /**
     * @var string
     */
    private $prefix = 'SELECT';

    /**
     * @var Sql[]
     */
    private $select = [];

    /**
     * @var Sql[]
     */
    private $from = [];

    /**
     * @var Sql[]
     */
    private $join = [];

    /**
     * @var Sql
     */
    private $where = null;

    /**
     * @var Sql[]
     */
    private $groupBy = [];

    /**
     * @var Sql
     */
    private $having = null;

    /**
     * @var Sql[]
     */
    private $orderBy = [];

    /**
     * @var integer
     */
    private $offset = null;

    /**
     * @var integer
     */
    private $limit = null;

    /**
     * @var string
     */
    private $suffix = null;

    /**
     * @var Sql[]
     */
    private $union = [];

    /**
     * @param GrammarInterface $grammar
     */
    public function __construct(GrammarInterface $grammar = null)
    {
        $this->grammar = $grammar ?: DefaultGrammar::getInstance();
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return Sql[]
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return Sql[]
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Sql[]
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @return Sql
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return Sql[]
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return Sql
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @return Sql[]
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @return Sql[]
     */
    public function getUnion()
    {
        return $this->union;
    }

    /**
     * @param Sql[] $select
     * @return $this
     */
    public function withSelect(array $select)
    {
        $cloned = clone $this;
        $cloned->select = $select;
        return $cloned;
    }

    /**
     * @param Sql[] $from
     * @return $this
     */
    public function withFrom(array $from)
    {
        $cloned = clone $this;
        $cloned->from = $from;
        return $cloned;
    }

    /**
     * @param Sql[] $join
     * @return $this
     */
    public function withJoin(array $join)
    {
        $cloned = clone $this;
        $cloned->join = $join;
        return $cloned;
    }

    /**
     * @param Sql|null $where
     * @return $this
     */
    public function withWhere(Sql $where = null)
    {
        $cloned = clone $this;
        $cloned->where = $where;
        return $cloned;
    }

    /**
     * @param Sql[] $groupBy
     * @return $this
     */
    public function withGroupBy(array $groupBy)
    {
        $cloned = clone $this;
        $cloned->groupBy = $groupBy;
        return $cloned;
    }

    /**
     * @param Sql $having
     * @return $this
     */
    public function withHaving(Sql $having = null)
    {
        $cloned = clone $this;
        $cloned->having = $having;
        return $cloned;
    }

    /**
     * @param Sql[] $orderBy
     * @return $this
     */
    public function withOrderBy(array $orderBy)
    {
        $cloned = clone $this;
        $cloned->orderBy = $orderBy;
        return $cloned;
    }

    /**
     * @param Sql[] $union
     * @return $this
     */
    public function withUnion(array $union)
    {
        $cloned = clone $this;
        $cloned->union = $union;
        return $cloned;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $cloned = clone $this;
        $cloned->prefix = $prefix;
        return $cloned;
    }

    /**
     * @param mixed  $expr
     * @param string $alias
     * @return $this
     */
    public function select($expr, $alias = null)
    {
        $expr = $this->grammar->lift($expr);
        if ($alias !== null) {
            $expr = $this->grammar->alias($expr, $alias);
        }
        $select = $this->select;
        $select[] = $expr;
        return $this->withSelect($select);
    }

    /**
     * @param mixed  $table
     * @param string $alias
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $table = $this->grammar->lift($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        $from = $this->from;
        $from[] = $table;
        return $this->withFrom($from);
    }

    /**
     * @param mixed       $lhs
     * @param string|null $operator
     * @param mixed|null  $rhs1
     * @param mixed|null  $rhs2
     * @return $this
     */
    public function where($lhs, $operator = null, $rhs1 = null, $rhs2 = null)
    {
        $condition = $this->grammar->liftCondition($lhs, $operator, $rhs1, $rhs2);
        $where = $this->where ? $this->grammar->operator('AND', $this->where, $condition) : $condition;
        return $this->withWhere($where);
    }

    /**
     * @param mixed       $lhs
     * @param string|null $operator
     * @param mixed|null  $rhs1
     * @param mixed|null  $rhs2
     * @return $this
     */
    public function orWhere($lhs, $operator = null, $rhs1 = null, $rhs2 = null)
    {
        $condition = $this->grammar->liftCondition($lhs, $operator, $rhs1, $rhs2);
        $where = $this->where ? $this->grammar->operator('OR', $this->where, $condition) : $condition;
        return $this->withWhere($where);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function groupWhere(callable $callback)
    {
        $builder = $callback(new SelectBuilder($this->grammar));
        if ($builder->where === null) {
            return $this;
        }
        $where = $this->where ? $this->grammar->operator('AND', $this->where, $builder->where) : $builder->where;
        return $this->withWhere($where);
    }

    /**
     * @param mixed  $table
     * @param mixed  $condition
     * @param string $alias
     * @param string $type
     * @return $this
     */
    public function join($table, $condition = null, $alias = null, $type = 'JOIN')
    {
        $table = $this->grammar->lift($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        $cloned = clone $this;
        $join = $this->join;
        if ($condition !== null) {
            $condition = $this->grammar->lift($condition);
        }
        $join[] = $this->grammar->join($table, $condition, $type);
        return $this->withJoin($join);
    }

    /**
     * @param mixed  $table
     * @param mixed  $condition
     * @param string $alias
     * @return $this
     */
    public function leftJoin($table, $condition = null, $alias = null)
    {
        return $this->join($table, $condition, $alias, 'LEFT JOIN');
    }

    /**
     * @param mixed  $expr
     * @param string $ordering
     * @return $this
     */
    public function groupBy($expr, $ordering = null)
    {
        $expr = $this->grammar->lift($expr);
        if ($ordering !== null) {
            $expr = $this->grammar->order($expr, $ordering);
        }
        $groupBy = $this->groupBy;
        $groupBy[] = $expr;
        return $this->withGroupBy($groupBy);
    }

    /**
     * @param mixed       $lhs
     * @param string|null $operator
     * @param mixed|null  $rhs1
     * @param mixed|null  $rhs2
     * @return $this
     */
    public function having($lhs, $operator = null, $rhs1 = null, $rhs2 = null)
    {
        $condition = $this->grammar->liftCondition($lhs, $operator, $rhs1, $rhs2);
        $having = $this->having ? $this->grammar->operator('AND', $this->having, $condition) : $condition;
        return $this->withHaving($having);
    }

    /**
     * @param mixed       $lhs
     * @param string|null $operator
     * @param mixed|null  $rhs1
     * @param mixed|null  $rhs2
     * @return $this
     */
    public function orHaving($lhs, $operator = null, $rhs1 = null, $rhs2 = null)
    {
        $condition = $this->grammar->liftCondition($lhs, $operator, $rhs1, $rhs2);
        $having = $this->having ? $this->grammar->operator('OR', $this->having, $condition) : $condition;
        return $this->withHaving($having);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function groupHaving(callable $callback)
    {
        $builder = $callback(new SelectBuilder($this->grammar));
        if ($builder->having === null) {
            return $this;
        }
        $having = $this->having ? $this->grammar->operator('AND', $this->having, $builder->having) : $builder->having;
        return $this->withHaving($where);
    }

    /**
     * @param mixed  $expr
     * @param stirng $ordering
     * @return $this
     */
    public function orderBy($expr, $ordering = null)
    {
        $expr = $this->grammar->lift($expr);
        if ($ordering !== null) {
            $expr = $this->grammar->order($expr, $ordering);
        }
        $orderBy = $this->orderBy;
        $orderBy[] = $expr;
        return $this->withOrderBy($orderBy);
    }

    /**
     * @param integer $integer
     * @return $this
     */
    public function limit($limit)
    {
        $cloned = clone $this;
        $cloned->limit = $limit;
        return $cloned;
    }

    /**
     * @param integer $integer
     * @return $this
     */
    public function offset($offset)
    {
        $cloned = clone $this;
        $cloned->offset = $offset;
        return $cloned;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function suffix($suffix)
    {
        $cloned = clone $this;
        $cloned->suffix = $suffix;
        return $cloned;
    }

    /**
     * @return $this
     */
    public function forUpdate()
    {
        return $this->suffix('FOR UPDATE');
    }

    /**
     * @param mixed  $query
     * @param string $type
     * @return $this
     */
    public function union($query, $type = 'UNION')
    {
        $query = $this->grammar->lift($query);
        $cloned = clone $this;
        $cloned->union[] = $this->grammar->union($query, $type);
        return $cloned;
    }

    /**
     * @param mixed $query
     * @return $this
     */
    public function unionAll($query)
    {
        return $this->union($query, 'UNION ALL');
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->compileSelect(
            $this->prefix,
            $this->select,
            $this->from,
            $this->join,
            $this->where,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->limit,
            $this->offset,
            $this->suffix,
            $this->union
        );
    }
}
