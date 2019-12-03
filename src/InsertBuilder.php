<?php

namespace Emonkak\Orm;

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
     * @var ?string
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
     * @var ?Sql
     */
    private $select;

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
     * @return ?string
     */
    public function getInto()
    {
        return $this->into;
    }

    /**
     * @return string[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return Sql[][]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return ?Sql
     */
    public function getSelect()
    {
        return $this->select;
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
    public function into($table, array $columns)
    {
        $cloned = clone $this;
        $cloned->into = $table;
        $cloned->columns = $columns;
        return $cloned;
    }

    /**
     * @param mixed[] ...$values
     * @return $this
     */
    public function values(...$values)
    {
        $cloned = clone $this;
        foreach ($values as $row) {
            $innerValues = [];
            foreach ($row as $value) {
                $innerValues[] = $this->grammar->literal($value);
            }
            $cloned->values[] = $innerValues;
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
        return $this->grammar->insertStatement(
            $this->prefix,
            $this->into,
            $this->columns,
            $this->values,
            $this->select
        );
    }
}
