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
     * @param mixed      $arg1
     * @param mixed|null $arg2
     * @param mixed|null $arg3
     * @param mixed|null $arg4
     * @return $this
     */
    public function where($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator($this->where, 'AND', $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed      $arg1
     * @param mixed|null $arg2
     * @param mixed|null $arg3
     * @param mixed|null $arg4
     * @return $this
     */
    public function orWhere($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? $this->grammar->operator($this->where, 'OR', $condition) : $condition;
        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->deleteStatement(
            $this->prefix,
            $this->from,
            $this->where
        );
    }
}
