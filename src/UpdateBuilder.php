<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Grammar\GrammarProvider;

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
     * @var string|null
     */
    private $table;

    /**
     * @var Sql[]
     */
    private $update = [];

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
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return Sql[]
     */
    public function getUpdate()
    {
        return $this->update;
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
    public function table($table)
    {
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
        $cloned->update[$column] = $this->grammar->literal($expr);
        return $cloned;
    }

    /**
     * @param mixed[] $update
     * @return $this
     */
    public function setAll(array $update)
    {
        $cloned = clone $this;
        $cloned->update = array_map([$this->grammar, 'literal'], $update);
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
        $cloned->where = $this->where ? Sql::_and($this->where, $condition) : $condition;
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
        $cloned->where = $this->where ? Sql::_or($this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->updateStatement(
            $this->prefix,
            $this->table,
            $this->update,
            $this->where
        );
    }
}
