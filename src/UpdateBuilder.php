<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;

/**
 * Provides the query building of UPDATE statement.
 */
class UpdateBuilder implements QueryBuilderInterface
{
    use Explainable;
    use Preparable;

    /**
     * @var GrammarInterface
     */
    private $grammar;

    /**
     * @var string
     */
    private $prefix = 'UPDATE';

    /**
     * @var Sql
     */
    private $table;

    /**
     * @var Sql[]
     */
    private $update = [];

    /**
     * @var Sql
     */
    private $where;

    /**
     * @var Sql[]
     */
    private $orderBy = [];

    /**
     * @var integer
     */
    private $limit = null;

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
     * @param mixed $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $cloned = clone $this;
        $cloned->prefix = $prefix;
        return $cloned;
    }

    /**
     * @param string      $table
     * @param string|null $alias
     * @return $this
     */
    public function table($table, $alias = null)
    {
        $table = $this->grammar->lift($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        $cloned = clone $this;
        $cloned->table = $table;
        return $cloned;
    }

    /**
     * @param string $column
     * @param mixed  $expr
     * @return $this
     */
    public function set($column, $expr)
    {
        $cloned = clone $this;
        $cloned->update[$column] = $this->grammar->liftValue($expr);
        return $cloned;
    }

    /**
     * @param mixed[] $update
     * @return $this
     */
    public function setAll(array $update)
    {
        $cloned = clone $this;
        $cloned->update = array_map([$this->grammar, 'liftValue'], $update);
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
        $builder = $callback(new UpdateBuilder($this->grammar));
        if ($builder->where === null) {
            return $this;
        }
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator('AND', $this->where, $builder->where) : $builder->where;
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
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->compileUpdate(
            $this->prefix,
            $this->table,
            $this->update,
            $this->where,
            $this->orderBy,
            $this->limit
        );
    }
}
