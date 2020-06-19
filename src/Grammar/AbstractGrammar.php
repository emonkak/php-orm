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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function value($value): Sql
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
            $tmpSqls = [];
            $tmpBindings = [];
            foreach ($value as $v) {
                $liftedValue = $this->value($v);
                $tmpSqls[] = $liftedValue->getSql();
                $tmpBindings[] = $liftedValue->getBindings();
            }
            $sql = '(' . implode(', ', $tmpSqls) . ')';
            $bindings = array_merge(...$tmpBindings);
            return new Sql($sql, $bindings);
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as a value, got '$type'.");
    }

    /**
     * {@inheritdoc}
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null): Sql
    {
        switch (func_num_args()) {
            case 1:
                return $this->lift($arg1);

            case 2:
                /** @psalm-var string */
                $operator = $arg1;
                /** @psalm-var mixed $arg2 */
                $rhs = $this->lift($arg2);
                return $this->unaryOperator($operator, $rhs);

            case 3:
                /** @psalm-var string */
                $operator = $arg2;
                $lhs = $this->lift($arg1);
                $rhs = $this->value($arg3);
                return $this->operator($operator, $lhs, $rhs);

            default:
                /** @psalm-var string */
                $operator = $arg2;
                $lhs = $this->lift($arg1);
                $start = $this->value($arg3);
                $end = $this->value($arg4);
                return $this->betweenOperator($operator, $lhs, $start, $end);
        }
    }
}
