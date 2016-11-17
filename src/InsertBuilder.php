<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;

/**
 * Provides the query building of INSERT statement.
 */
class InsertBuilder implements QueryBuilderInterface
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
    private $prefix = 'INSERT';

    /**
     * @var string
     */
    private $into;

    /**
     * @var string[]
     */
    private $columns = [];

    /**
     * @var Sql[][]
     */
    private $values = [];

    /**
     * @var Sql
     */
    private $select;

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
     * @param string   $table
     * @param string[] $columns
     * @return $this
     */
    public function into($table, array $columns = [])
    {
        $cloned = clone $this;
        $cloned->into = $table;
        $cloned->columns = $columns;
        return $cloned;
    }

    /**
     * @param mixed[] $values
     * @return $this
     */
    public function values()
    {
        $cloned = clone $this;
        foreach (func_get_args() as $row) {
            $cloned->values[] = $this->grammar->liftValue(array_values($row));
        }
        return $cloned;
    }

    /**
     * @param mixed $query
     * @return $this
     */
    public function select($query)
    {
        $cloned = clone $this;
        $cloned->select = $this->grammar->lift($query);
        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->compileInsert(
            $this->prefix,
            $this->into,
            $this->columns,
            $this->values,
            $this->select
        );
    }
}
