<?php

declare(strict_types=1);

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

abstract class AbstractGrammar implements GrammarInterface
{
    public function getSelectBuilder(): SelectBuilder
    {
        return new SelectBuilder($this);
    }

    public function getInsertBuilder(): InsertBuilder
    {
        return new InsertBuilder($this);
    }

    public function getUpdateBuilder(): UpdateBuilder
    {
        return new UpdateBuilder($this);
    }

    public function getDeleteBuilder(): DeleteBuilder
    {
        return new DeleteBuilder($this);
    }

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * {@inheritDoc}
     */
    public function lift($value): Sql
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            return $value->build()->enclosed();
        }
        if (is_string($value)) {
            return new Sql($value);
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as a query, got '$type'.");
    }

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * {@inheritDoc}
     */
    public function literal($value): Sql
    {
        if ($value === null) {
            return new Sql('NULL');
        }
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            return $value->build()->enclosed();
        }
        if ($value instanceof \JsonSerializable) {
            return Sql::value($value->jsonSerialize());
        }
        if (is_scalar($value)) {
            return Sql::value($value);
        }
        if (is_array($value)) {
            return Sql::join(', ', array_map([$this, 'literal'], $value))->enclosed();
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as a literal, got '$type'.");
    }

    /**
     * {@inheritDoc}
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null): Sql
    {
        switch (func_num_args()) {
            case 1: {
                /** @psalm-var QueryBuilderInterface|Sql|string $arg1 */
                return $this->lift($arg1);
            }
            case 2: {
                /** @psalm-var string */
                $operator = $arg1;
                /** @psalm-var QueryBuilderInterface|Sql|string $arg2 */
                $rhs = $this->lift($arg2);
                return $this->unaryOperator($arg1, $rhs);
            }
            case 3: {
                /** @psalm-var string */
                $operator = $arg2;
                /** @psalm-var QueryBuilderInterface|Sql|string $arg1 */
                $lhs = $this->lift($arg1);
                /** @psalm-var scalar|scalar[]|null $arg3 */
                $rhs = $this->literal($arg3);
                return $this->operator($operator, $lhs, $rhs);
            }
            default:
                /** @psalm-var string */
                $operator = $arg2;
                /** @psalm-var QueryBuilderInterface|Sql|string $arg1 */
                $lhs = $this->lift($arg1);
                /** @psalm-var scalar|scalar[]|null $arg3 */
                $start = $this->literal($arg3);
                /** @psalm-var scalar|scalar[]|null $arg4 */
                $end = $this->literal($arg4);
                return $this->betweenOperator($operator, $lhs, $start, $end);
        }
    }
}
