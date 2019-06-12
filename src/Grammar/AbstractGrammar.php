<?php

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

abstract class AbstractGrammar implements GrammarInterface
{
    /**
     * {@inheritDoc}
     */
    public function getSelect()
    {
        return new SelectBuilder($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getInsert()
    {
        return new InsertBuilder($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdate()
    {
        return new UpdateBuilder($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getDelete()
    {
        return new DeleteBuilder($this);
    }

    /**
     * {@inheritDoc}
     */
    public function expression($value)
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
        throw new \UnexpectedValueException("The value can not be lifted as an expression, got '$type'.");
    }

    /**
     * {@inheritDoc}
     */
    public function literal($value)
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            return $value->build()->enclosed();
        }
        if ($value instanceof \DateTimeInterface) {
            list ($date, $time, $micros) =
                explode(' ', $value->format('Y-m-d H:i:s u'));
            $dateTime = $date . ' ' . $time . ($micros != 0 ? rtrim('.' . $micros, '0') : '');
            return Sql::value($dateTime);
        }
        if ($value === null) {
            return new Sql('NULL');
        }
        if (is_scalar($value)) {
            return Sql::value($value);
        }
        if (is_array($value)) {
            return Sql::values($value);
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return Sql::value($value->__toString());
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as a literal, got '$type'.");
    }

    /**
     * {@inheritDoc}
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        switch (func_num_args()) {
            case 1:
                $expression = $this->expression($arg1);
                return $expression;
            case 2:
                $operator = $arg1;
                $rhs = $this->expression($arg2);
                return $this->unaryOperator($arg1, $rhs);
            case 3:
                $operator = $arg2;
                $lhs = $this->expression($arg1);
                $rhs = $this->literal($arg3);
                return $this->operator($operator, $lhs, $rhs);
            default:
                $operator = $arg2;
                $lhs = $this->expression($arg1);
                $start = $this->literal($arg3);
                $end = $this->literal($arg4);
                return $this->betweenOperator($operator, $lhs, $start, $end);
        }
    }
}
