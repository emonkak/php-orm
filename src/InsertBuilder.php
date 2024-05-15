<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

/**
 * Provides the query building of INSERT statement.
 */
class InsertBuilder implements QueryBuilderInterface
{
    use Explainable;
    use Preparable;

    private GrammarInterface $grammar;

    private string $prefix = 'INSERT';

    private ?string $into = null;

    /**
     * @var string[]
     */
    private array $columns = [];

    /**
     * @var Sql[][]
     */
    private array $values = [];

    /**
     * @var ?Sql
     */
    private ?Sql $select = null;

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

    public function getInto(): ?string
    {
        return $this->into;
    }

    /**
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return Sql[][]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getSelectBuilder(): ?Sql
    {
        return $this->select;
    }

    public function prefix(string $prefix): self
    {
        $cloned = clone $this;
        $cloned->prefix = $prefix;
        return $cloned;
    }

    /**
     * @param string[] $columns
     */
    public function into(string $table, array $columns): self
    {
        $cloned = clone $this;
        $cloned->into = $table;
        $cloned->columns = $columns;
        return $cloned;
    }

    /**
     * @param mixed[] ...$values
     */
    public function values(...$values): self
    {
        $cloned = clone $this;
        foreach ($values as $row) {
            $innerValues = [];
            foreach ($row as $value) {
                $innerValues[] = $this->grammar->value($value);
            }
            $cloned->values[] = $innerValues;
        }
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $query
     */
    public function select($query): self
    {
        $cloned = clone $this;
        $cloned->select = $this->grammar->lift($query);
        return $cloned;
    }

    public function build(): Sql
    {
        return $this->grammar->insertStatement(
            $this->prefix,
            $this->into ?? '',
            $this->columns,
            $this->values,
            $this->select
        );
    }
}
