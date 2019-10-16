<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\Pagination\PrecountPaginator;

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
     * @var ?Sql
     */
    private $where;

    /**
     * @var Sql[]
     */
    private $groupBy = [];

    /**
     * @var ?Sql
     */
    private $having;

    /**
     * @var Sql[]
     */
    private $orderBy = [];

    /**
     * @var ?int
     */
    private $offset;

    /**
     * @var ?int
     */
    private $limit;

    /**
     * @var ?string
     */
    private $suffix;

    /**
     * @var Sql[]
     */
    private $union = [];

    /**
     * @var ?self
     */
    private $parent;

    /**
     * @param GrammarInterface $grammar
     */
    public function __construct(GrammarInterface $grammar, SelectBuilder $parent = null)
    {
        $this->grammar = $grammar;
        $this->parent = $parent;
    }

    /**
     * @return GrammarInterface
     */
    public function getGrammar()
    {
        return $this->grammar;
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
     * @return ?Sql
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return Sql[]
     */
    public function getGroupby()
    {
        return $this->groupBy;
    }

    /**
     * @return ?Sql
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @return Sql[]
     */
    public function getOrderby()
    {
        return $this->orderBy;
    }

    /**
     * @return ?int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return ?int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return ?string
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
     * @param mixed   $expr
     * @param ?string $alias
     * @return $this
     */
    public function select($expr, $alias = null)
    {
        $expr = $this->grammar->expression($expr);
        if ($alias !== null) {
            $expr = $this->grammar->alias($expr, $alias);
        }
        $cloned = clone $this;
        $cloned->select[] = $expr;
        return $cloned;
    }

    /**
     * @param array $exprs
     * @return $this
     */
    public function selectAll(array $exprs)
    {
        $select = [];
        foreach ($exprs as $key => $expr) {
            $expr = $this->grammar->expression($expr);
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
     * @param mixed   $table
     * @param ?string $alias
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $table = $this->grammar->expression($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        $cloned = clone $this;
        $cloned->from[] = $table;
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param ?mixed $arg2
     * @param ?mixed $arg3
     * @param ?mixed $arg4
     * @return $this
     */
    public function where($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_and($this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed  $arg1
     * @param ?mixed $arg2
     * @param ?mixed $arg3
     * @param ?mixed $arg4
     * @return $this
     */
    public function orWhere($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_or($this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed   $table
     * @param ?mixed  $condition
     * @param ?string $alias
     * @param string  $type
     * @return $this
     */
    public function join($table, $condition = null, $alias = null, $type = 'JOIN')
    {
        $table = $this->grammar->expression($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        $join = $this->join;
        if ($condition !== null) {
            $condition = $this->grammar->expression($condition);
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
        return $this->join($table, $condition, $alias, 'LEFT OUTER JOIN');
    }

    /**
     * @param mixed $expr
     * @return $this
     */
    public function groupBy($expr)
    {
        $expr = $this->grammar->expression($expr);
        $cloned = clone $this;
        $cloned->groupBy[] = $expr;
        return $cloned;
    }

    /**
     * @param mixed  $arg1
     * @param ?mixed $arg2
     * @param ?mixed $arg3
     * @param ?mixed $arg4
     * @return $this
     */
    public function having($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->having = $this->having ? Sql::_and($this->having, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed  $arg1
     * @param ?mixed $arg2
     * @param ?mixed $arg3
     * @param ?mixed $arg4
     * @return $this
     */
    public function orHaving($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->having = $this->having ? Sql::_or($this->having, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed   $expr
     * @param ?string $ordering
     * @return $this
     */
    public function orderBy($expr, $ordering = null)
    {
        $expr = $this->grammar->expression($expr);
        if ($ordering !== null) {
            $expr = $this->grammar->ordering($expr, $ordering);
        }
        $cloned = clone $this;
        $cloned->orderBy[] = $expr;
        return $cloned;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $cloned = clone $this;
        $cloned->limit = $limit;
        return $cloned;
    }

    /**
     * @param int $offset
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
     * @param string $type
     * @return $this
     */
    public function union($type = 'UNION')
    {
        $parent = clone $this;
        $parent->suffix = ltrim($parent->suffix . ' ' . $type, ' ');
        return new SelectBuilder($this->grammar, $parent);
    }

    /**
     * @return $this
     */
    public function unionAll()
    {
        return $this->union('UNION ALL');
    }

    /**
     * @param mixed  $query
     * @param string $type
     * @return $this
     */
    public function unionWith($query, $type = 'UNION')
    {
        $query = $this->grammar->expression($query);
        $cloned = clone $this;
        $cloned->union[] = $this->grammar->union($query, $type);
        return $cloned;
    }

    /**
     * @param mixed $query
     * @return $this
     */
    public function unionAllWith($query)
    {
        return $this->unionWith($query, 'UNION ALL');
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $builder = $this;
        $sqls = [];

        do {
            $sqls[] = $this->grammar->selectStatement(
                $builder->prefix,
                $builder->select,
                $builder->from,
                $builder->join,
                $builder->where,
                $builder->groupBy,
                $builder->having,
                $builder->orderBy,
                $builder->limit,
                $builder->offset,
                $builder->suffix,
                $builder->union
            );
        } while ($builder = $builder->parent);

        return count($sqls) > 1 ? Sql::join(' ', array_reverse($sqls)) : $sqls[0];
    }

    /**
     * {@inheritDoc}
     */
    public function aggregate(PDOInterface $pdo, $expr)
    {
        $stmt = $this->selectAll([$expr])->withoutSorting()->prepare($pdo);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @param PDOInterface     $pdo
     * @param FetcherInterface $fetcher
     * @param int              $perPage
     * @param string           $countExpr
     * @return PaginatorInterface
     */
    public function paginate(PDOInterface $pdo, FetcherInterface $fetcher, $perPage, $countExpr = 'COUNT(*)')
    {
        $count = (int) $this->aggregate($pdo, $countExpr);
        return new PrecountPaginator($this, $pdo, $fetcher, $perPage, $count);
    }

    /**
     * @return $this
     */
    private function withoutSorting()
    {
        $cloned = clone $this;
        $cloned->orderBy = [];
        $cloned->groupBy = [];
        return $cloned;
    }
}
