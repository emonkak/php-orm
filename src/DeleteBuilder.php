<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

/**
 * A query builder for DELETE statement.
 */
class DeleteBuilder implements QueryBuilderInterface
{
    use Explainable;
    use Preparable;

    private GrammarInterface $grammar;

    private string $prefix = 'DELETE';

    private string $from = '';

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

    public function getFrom(): string
    {
        return $this->from;
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

    public function from(string $table): self
    {
        $cloned = clone $this;
        $cloned->from = $table;
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
        return $this->grammar->deleteStatement(
            $this->prefix,
            $this->from,
            $this->where
        );
    }
}
