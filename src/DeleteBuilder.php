<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

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
        return $this->grammar->deleteStatement(
            $this->prefix,
            $this->from,
            $this->where
        );
    }
}
