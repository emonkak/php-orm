<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;

/**
 * Provides the query building of SELECT statement.
 */
class SelectBuilder implements QueryBuilderInterface
{
    use Aggregatable;
    use Explainable;
    use Fetchable;
    use Preparable;

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
     * @return GrammarInterface
     */
    public function getGrammar()
    {
        return $this->grammar;
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
        $cloned = clone $this;
        $cloned->select[] = $expr;
        return $cloned;
    }

    /**
     * @param mixed  $expr
     * @param string $alias
     * @return $this
     */
    public function selectAll(array $exprs)
    {
        $select = [];
        foreach ($exprs as $key => $expr) {
            $expr = $this->grammar->lift($expr);
            if (is_string($key)) {
                $expr = $this->grammar->alias($expr, $key);
            }
            $select[] = $expr;
        }
        $cloned = clone $this;
        $cloned->select = $select;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->from[] = $table;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator('AND', $this->where, $condition) : $condition;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator('OR', $this->where, $condition) : $condition;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator('AND', $this->where, $builder->where) : $builder->where;
        return $cloned;
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
        $join = $this->join;
        if ($condition !== null) {
            $condition = $this->grammar->lift($condition);
        }
        $cloned = clone $this;
        $cloned->join[] = $this->grammar->join($table, $condition, $type);
        return $cloned;
    }

    /**
     * @param mixed  $table
     * @param mixed  $condition
     * @param string $alias
     * @return $this
     */
    public function outerJoin($table, $condition = null, $alias = null)
    {
        return $this->join($table, $condition, $alias, 'OUTER LEFT JOIN');
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
        $cloned = clone $this;
        $cloned->groupBy[] = $expr;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->having = $this->having ? $this->grammar->operator('AND', $this->having, $condition) : $condition;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->having = $this->having ? $this->grammar->operator('OR', $this->having, $condition) : $condition;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->having = $this->having ? $this->grammar->operator('AND', $this->having, $builder->having) : $builder->having;
        return $cloned;
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
        $cloned = clone $this;
        $cloned->orderBy[] = $expr;
        return $cloned;
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

    /**
     * {@inheritDoc}
     */
    public function aggregate(PDOInterface $connection, $func, $expr)
    {
        $stmt = $this->selectAll(["$func($expr)"])->prepare($connection);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param integer          $perPage
     * @return Paginator
     */
    public function paginate(PDOInterface $connection, FetcherInterface $fetcher, $perPage)
    {
        $numItems = $this->count($connection);
        return new Paginator($this, $connection, $fetcher, $perPage, $numItems);
    }

}
