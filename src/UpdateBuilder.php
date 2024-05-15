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

    private GrammarInterface $grammar;

    private string $prefix = 'UPDATE';

    private string $table = '';

    /**
     * @var Sql[]
     */
    private array $set = [];

    /**
     * @var ?Sql
     */
    private ?Sql $where = null;

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

    public function set(string $column, mixed $expr): self
    {
        $cloned = clone $this;
        $cloned->set[$column] = $this->grammar->value($expr);
        return $cloned;
    }

    /**
     * @param mixed[] $set
     */
    public function withSet(array $set): self
    {
        $cloned = clone $this;
        $cloned->set = array_map([$this->grammar, 'value'], $set);
        return $cloned;
    }

    public function where(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::and($this->where, $condition) : $condition;
        return $cloned;
    }

    public function orWhere(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::or($this->where, $condition) : $condition;
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
