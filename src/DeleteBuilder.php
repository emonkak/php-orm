<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Grammar\GrammarProvider;

/**
 * Provides the query building of DELETE statement.
 */
class DeleteBuilder implements QueryBuilderInterface
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
    private $prefix = 'DELETE';

    /**
     * @var string
     */
    private $from;

    /**
     * @var Sql|null
     */
    private $where;

    /**
     * @param GrammarInterface $grammar
     */
    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
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
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Sql|null
     */
    public function getWhere()
    {
        return $this->where;
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
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $cloned = clone $this;
        $cloned->from = $table;
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
        return $this->doWhere('AND', $lhs, $operator, $rhs1, $rhs2);
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
        return $this->doWhere('OR', $lhs, $operator, $rhs1, $rhs2);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function groupWhere(callable $callback)
    {
        return $this->doGroupWhere('AND', $callback);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function orGroupWhere(callable $callback)
    {
        return $this->doGroupWhere('OR', $callback);
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->compileDelete(
            $this->prefix,
            $this->from,
            $this->where
        );
    }

    /**
     * @param string      $whereOperator
     * @param mixed       $lhs
     * @param string|null $operator
     * @param mixed|null  $rhs1
     * @param mixed|null  $rhs2
     * @return $this
     */
    private function doWhere($whereOperator, $lhs, $operator, $rhs1, $rhs2)
    {
        $condition = $this->grammar->liftCondition($lhs, $operator, $rhs1, $rhs2);
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator($whereOperator, $this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param string $whereOperator
     * @param callable $callback
     * @return $this
     */
    private function doGroupWhere($whereOperator, callable $callback)
    {
        $builder = $callback(new DeleteBuilder($this->grammar));
        if ($builder->where === null) {
            return $this;
        }
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator($whereOperator, $this->where, $builder->where) : $builder->where;
        return $cloned;
    }
}
