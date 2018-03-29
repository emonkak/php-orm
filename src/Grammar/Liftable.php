<?php

namespace Emonkak\Orm\Grammar;

use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\Sql;

trait Liftable
{
    /**
     * @param mixed $value
     * @return Sql
     */
    public function lift($value)
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
        $type = gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted, got '$type'.");
    }

    /**
     * @param mixed $value
     * @return Sql
     */
    public function liftValue($value)
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            return $value->build()->enclosed();
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
        $type = gettype($value);
        throw new \UnexpectedValueException("Unexpected value, got '$type'.");
    }

    /**
     * @param mixed       $lhs
     * @param string|null $operator
     * @param mixed       $rhs1
     * @param mixed       $rhs2
     * @return Sql
     */
    public function liftCondition($lhs, $operator = null, $rhs1 = null, $rhs2 = null)
    {
        if ($operator === null) {
            return $this->lift($lhs);
        } elseif ($rhs1 === null) {
            $lhs = $this->lift($lhs);
            return $this->unaryOperator($operator, $lhs);
        } elseif ($rhs2 === null) {
            $lhs = $this->lift($lhs);
            $rhs = $this->liftValue($rhs1);
            return $this->operator($operator, $lhs, $rhs);
        } else {
            $lhs = $this->lift($lhs);
            $start = $this->liftValue($rhs1);
            $end = $this->liftValue($rhs2);
            return $this->betweenOperator($operator, $lhs, $start, $end);
        }
    }

    /**
     * @param string $operator
     * @param Sql    $lhs
     * @param Sql    $rhs
     * @return Sql
     */
    abstract public function operator($operator, Sql $lhs, Sql $rhs);

    /**
     * @param string $operator
     * @param Sql    $lhs
     * @param Sql    $start
     * @param Sql    $end
     * @return Sql
     */
    abstract public function betweenOperator($operator, Sql $lhs, Sql $start, Sql $end);

    /**
     * @param string $operator
     * @param Sql    $lhs
     * @return Sql
     */
    abstract public function unaryOperator($operator, Sql $lhs);
}
