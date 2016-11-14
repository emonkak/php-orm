<?php

namespace Emonkak\Orm\QueryBuilder;

use Emonkak\Orm\QueryBuilder\Grammar\DefaultGrammar;
use Emonkak\Orm\QueryBuilder\Grammar\GrammarInterface;

class InsertBuilder implements QueryBuilderInterface
{
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
    private $table;

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
     * @var Sql[]
     */
    private $update = [];

    /**
     * @param GrammarInterface $grammar
     */
    public function __construct(GrammarInterface $grammar = null)
    {
        $this->grammar = $grammar ?: DefaultGrammar::getInstance();
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
     * @param mixed $table
     * @param array $columns
     * @return $this
     */
    public function into($table, array $columns = [])
    {
        $cloned = clone $this;
        $cloned->table = $table;
        $cloned->columns = $columns;
        return $cloned;
    }

    /**
     * @param mixed[] $values
     * @return $this
     */
    public function values(...$values)
    {
        $cloned = clone $this;
        foreach ($values as $row) {
            $cloned->values[] = $this->grammar->liftValue($row);
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
     * @param mixed[] $update
     * @return $this
     */
    public function onDuplicateKeyUpdate(array $update)
    {
        $cloned = clone $this;
        $cloned->update = array_map([$this->grammar, 'liftValue'], $update);
        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->grammar->compileInsert(
            $this->prefix,
            $this->table,
            $this->columns,
            $this->values,
            $this->select,
            $this->update
        );
    }
}
