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
     * @var ?string
     */
    private $table;

    /**
     * @var Sql[]
     */
    private $update = [];

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

    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @return Sql[]
     */
    public function getUpdateBuilder(): array
    {
        return $this->update;
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

    public function set(string $column, $expr): self
    {
        $cloned = clone $this;
        $cloned->update[$column] = $this->grammar->literal($expr);
        return $cloned;
    }

    /**
     * @param mixed[] $update
     */
    public function setAll(array $update): self
    {
        $cloned = clone $this;
        $cloned->update = array_map([$this->grammar, 'literal'], $update);
        return $cloned;
    }

    public function where($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_and($this->where, $condition) : $condition;
        return $cloned;
    }

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
            $this->update,
            $this->where
        );
    }
}
