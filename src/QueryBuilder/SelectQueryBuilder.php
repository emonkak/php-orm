<?php

namespace Emonkak\Orm\QueryBuilder;

use Emonkak\Orm\QueryBuilder\Clause\Alias;
use Emonkak\Orm\QueryBuilder\Clause\ConditionalJoin;
use Emonkak\Orm\QueryBuilder\Clause\Join;
use Emonkak\Orm\QueryBuilder\Clause\Sort;
use Emonkak\Orm\QueryBuilder\Clause\Union;
use Emonkak\Orm\QueryBuilder\Compiler\CompilerInterface;
use Emonkak\Orm\QueryBuilder\Compiler\DefaultCompiler;

class SelectQueryBuilder implements QueryBuilderInterface
{
    use Stringable;

    /**
     * @var CompilerInterface
     */
    private $compiler;

    /**
     * @var string
     */
    private $prefix = 'SELECT';

    /**
     * @var QueryBuilderInterface[]
     */
    private $select = [];

    /**
     * @var QueryBuilderInterface[]
     */
    private $from = [];

    /**
     * @var QueryBuilderInterface[]
     */
    private $join = [];

    /**
     * @var QueryBuilderInterface
     */
    private $where = null;

    /**
     * @var QueryBuilderInterface[]
     */
    private $groupBy = [];

    /**
     * @var QueryBuilderInterface
     */
    private $having = null;

    /**
     * @var QueryBuilderInterface[]
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
     * @var QueryBuilderInterface[]
     */
    private $union = [];

    /**
     * @param CompilerInterface $compiler
     */
    public function __construct(CompilerInterface $compiler = null)
    {
        $this->compiler = $compiler ?: DefaultCompiler::getInstance();
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return QueryBuilderInterface[]
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return QueryBuilderInterface[]
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return QueryBuilderInterface[]
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @return QueryBuilderInterface
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return QueryBuilderInterface[]
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return QueryBuilderInterface
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @return QueryBuilderInterface[]
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
     * @return QueryBuilderInterface[]
     */
    public function getUnion()
    {
        return $this->union;
    }

    /**
     * @param QueryBuilderInterface[] $select
     * @return self
     */
    public function withSelect(array $select)
    {
        $cloned = clone $this;
        $cloned->select = $select;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface[] $from
     * @return self
     */
    public function withFrom(array $from)
    {
        $cloned = clone $this;
        $cloned->from = $from;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface[] $join
     * @return self
     */
    public function withJoin(array $join)
    {
        $cloned = clone $this;
        $cloned->join = $join;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface $where
     * @return self
     */
    public function withWhere(QueryBuilderInterface $where = null)
    {
        $cloned = clone $this;
        $cloned->where = $where;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface[] $groupBy
     * @return self
     */
    public function withGroupBy(array $groupBy)
    {
        $cloned = clone $this;
        $cloned->groupBy = $groupBy;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface $having
     * @return self
     */
    public function withHaving(QueryBuilderInterface $having = null)
    {
        $cloned = clone $this;
        $cloned->having = $having;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface[] $orderBy
     * @return self
     */
    public function withOrderBy(array $orderBy)
    {
        $cloned = clone $this;
        $cloned->orderBy = $orderBy;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface[] $union
     * @return self
     */
    public function withUnion(array $union)
    {
        $cloned = clone $this;
        $cloned->union = $union;
        return $cloned;
    }

    /**
     * @param string $prefix
     * @return self
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
     * @return self
     */
    public function select($expr, $alias = null)
    {
        $expr = Creteria::str($expr);
        if ($alias !== null) {
            $expr = new Alias($expr, $alias);
        }
        $select = $this->select;
        $select[] = $expr;
        return $this->withSelect($select);
    }

    /**
     * @param mixed  $expr
     * @param string $alias
     * @return self
     */
    public function from($expr, $alias = null)
    {
        $expr = Creteria::str($expr);
        if ($alias !== null) {
            $expr = new Alias($expr, $alias);
        }
        $from = $this->from;
        $from[] = $expr;
        return $this->withFrom($from);
    }

    /**
     * @param mixed[] ...$args
     * @return self
     */
    public function where()
    {
        $args = func_get_args();
        $expr = call_user_func_array([Creteria::class, 'condition'], $args);
        $where = $this->where ? $this->where->_and($expr) : $expr;
        return $this->withWhere($where);
    }

    /**
     * @param mixed[] ...$args
     * @return self
     */
    public function orWhere()
    {
        $args = func_get_args();
        $expr = call_user_func_array([Creteria::class, 'condition'], $args);
        $where = $this->where ? $this->where->_or($expr) : $expr;
        return $this->withWhere($where);
    }

    /**
     * @param mixed  $table
     * @param mixed  $condition
     * @param string $alias
     * @param string $type
     * @return self
     */
    public function join($table, $condition = null, $alias = null, $type = 'JOIN')
    {
        $table = Creteria::str($table);
        if ($alias !== null) {
            $table = new Alias($table, $alias);
        }
        $join = $this->join;
        if ($condition !== null) {
            $condition = Creteria::str($condition);
            $join[] = new ConditionalJoin($table, $condition, $type);
        } else {
            $join[] = new Join($table, $type);
        }
        return $this->withJoin($join);
    }

    /**
     * @param mixed  $table
     * @param mixed  $condition
     * @param string $alias
     * @return self
     */
    public function leftJoin($table, $condition = null, $alias = null)
    {
        return $this->join($table, $condition, $alias, 'LEFT JOIN');
    }

    /**
     * @param mixed  $expr
     * @param string $ordering
     * @return self
     */
    public function groupBy($expr, $ordering = null)
    {
        $expr = Creteria::str($expr);
        if ($ordering !== null) {
            $expr = new Sort($expr, $ordering);
        }
        $groupBy = $this->groupBy;
        $groupBy[] = $expr;
        return $this->withGroupBy($groupBy);
    }

    /**
     * @param mixed[] ...$args
     * @return self
     */
    public function having()
    {
        $args = func_get_args();
        $expr = call_user_func_array([Creteria::class, 'condition'], $args);
        $having = $this->having ? $this->having->_and($expr) : $expr;
        return $this->withHaving($having);
    }

    /**
     * @param mixed[] ...$args
     * @return self
     */
    public function orHaving()
    {
        $args = func_get_args();
        $expr = call_user_func_array([Creteria::class, 'condition'], $args);
        $having = $this->having ? $this->having->_or($expr) : $expr;
        return $this->withHaving($having);
    }

    /**
     * @param mixed  $expr
     * @param stirng $ordering
     * @return self
     */
    public function orderBy($expr, $ordering = null)
    {
        $expr = Creteria::str($expr);
        if ($ordering !== null) {
            $expr = new Sort($expr, $ordering);
        }
        $orderBy = $this->orderBy;
        $orderBy[] = $expr;
        return $this->withOrderBy($orderBy);
    }

    /**
     * @param integer $integer
     * @return self
     */
    public function limit($limit)
    {
        $cloned = clone $this;
        $cloned->limit = $limit;
        return $cloned;
    }

    /**
     * @param integer $integer
     * @return self
     */
    public function offset($offset)
    {
        $cloned = clone $this;
        $cloned->offset = $offset;
        return $cloned;
    }

    /**
     * @param string $suffix
     * @return self
     */
    public function suffix($suffix)
    {
        $cloned = clone $this;
        $cloned->suffix = $suffix;
        return $cloned;
    }

    /**
     * @return self
     */
    public function forUpdate()
    {
        return $this->suffix('FOR UPDATE');
    }

    /**
     * @param QueryBuilderInterface $query
     * @param string                $type
     * @return self
     */
    public function union(QueryBuilderInterface $query, $type = 'UNION')
    {
        $union = $this->union;
        $union[] = new Union($query, $type);
        return $this->withUnion($union);
    }

    /**
     * @param QueryBuilderInterface $query
     * @return self
     */
    public function unionAll(QueryBuilderInterface $query)
    {
        return $this->union($query, 'UNION ALL');
    }

    /**
     * @return array (string, mixed[])
     */
    public function build()
    {
        return $this->compiler->compileSelect(
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
