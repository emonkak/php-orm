<?php

declare(strict_types=1);

namespace Emonkak\Orm;

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
     * @var string
     */
    private $table = '';

    /**
     * @var Sql[]
     */
    private $set = [];

    /**
     * @var ?Sql
     */
    private $where;

    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
    }

    public function getGrammar(): GrammarInterface
    {
        return $this->grammar;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return Sql[]
     */
    public function getSet(): array
    {
        return $this->set;
    }

    public function getWhere(): ?Sql
    {
        return $this->where;
    }

    public function prefix(string $prefix): self
    {
        $cloned = clone $this;
        $cloned->prefix = $prefix;
        return $cloned;
    }

    public function table(string $table): self
    {
        $cloned = clone $this;
        $cloned->table = $table;
        return $cloned;
    }

    /**
     * @param ?scalar|array<int,?scalar> $expr
     */
    public function set(string $column, $expr): self
    {
        $cloned = clone $this;
        $cloned->set[$column] = $this->grammar->literal($expr);
        return $cloned;
    }

    /**
     * @param array<int,?scalar|array<int,?scalar>> $set
     */
    public function withSet(array $set): self
    {
        $cloned = clone $this;
        $cloned->set = array_map([$this->grammar, 'literal'], $set);
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function where($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_and($this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function orWhere($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_or($this->where, $condition) : $condition;
        return $cloned;
    }

    public function build(): Sql
    {
        return $this->grammar->updateStatement(
            $this->prefix,
            $this->table,
            $this->set,
            $this->where
        );
    }
}
