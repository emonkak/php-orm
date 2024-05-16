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

    public function lvalue(mixed $value): Sql
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
        throw new \UnexpectedValueException("The value can not be lifted as a left valiue, got '$type'.");
    }

    public function rvalue(mixed $value): Sql
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
                $liftedValue = $this->rvalue($v);
                $tmpSqls[] = $liftedValue->getSql();
                $tmpBindings[] = $liftedValue->getBindings();
            }
            $sql = '(' . implode(', ', $tmpSqls) . ')';
            $bindings = array_merge(...$tmpBindings);
            return new Sql($sql, $bindings);
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as a right value, got '$type'.");
    }

    public function condition(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): Sql
    {
        switch (func_num_args()) {
            case 1:
                return $this->lvalue($arg1);

            case 2:
                /** @var string */
                $operator = $arg1;
                $rhs = $this->lvalue($arg2);
                return $this->unaryOperator($operator, $rhs);

            case 3:
                /** @var string */
                $operator = $arg2;
                $lhs = $this->lvalue($arg1);
                $rhs = $this->rvalue($arg3);
                return $this->operator($operator, $lhs, $rhs);

            default:
                /** @var string */
                $operator = $arg2;
                $lhs = $this->lvalue($arg1);
                $start = $this->rvalue($arg3);
                $end = $this->rvalue($arg4);
                return $this->betweenOperator($operator, $lhs, $start, $end);
        }
    }
}
