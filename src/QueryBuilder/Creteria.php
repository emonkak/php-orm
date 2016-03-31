<?php

namespace Emonkak\Orm\QueryBuilder;

use Emonkak\Orm\QueryBuilder\Expression\BetweenOperator;
use Emonkak\Orm\QueryBuilder\Expression\Func;
use Emonkak\Orm\QueryBuilder\Expression\NullValue;
use Emonkak\Orm\QueryBuilder\Expression\Operator;
use Emonkak\Orm\QueryBuilder\Expression\PrefixOperator;
use Emonkak\Orm\QueryBuilder\Expression\Raw;
use Emonkak\Orm\QueryBuilder\Expression\Str;
use Emonkak\Orm\QueryBuilder\Expression\SubQuery;
use Emonkak\Orm\QueryBuilder\Expression\Value;
use Emonkak\Orm\QueryBuilder\Expression\Values;

class Creteria
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @param mixed[] $condition
     * @return QueryFragmentInterface
     */
    public static function condition(array $condition)
    {
        switch (count($condition)) {
        case 1:
            return self::str($condition[0]);
        case 2:
            return self::unaryOperator($condition[0], $condition[1]);
        case 3:
            return self::operator($condition[0], $condition[1], $condition[2]);
        }
        throw new \InvalidArgumentException('The number of arguments is incorrect');
    }

    /**
     * @param mixed $expr
     * @param mixed $binds
     * @return Raw
     */
    public static function raw($expr, $binds)
    {
        return new Raw($expr, $binds);
    }

    /**
     * @param mixed $first
     * @return QueryFragmentInterface
     */
    public static function str($first)
    {
        return is_string($first) ? new Str($first) : self::value($first);
    }

    /**
     * @param mixed $value
     * @return QueryFragmentInterface
     */
    public static function value($value)
    {
        if ($value === null) {
            return new NullValue();
        }
        if (is_scalar($value)) {
            return new Value($value);
        }
        if (is_array($value)) {
            return new Values(array_map('self::value', $value));
        }
        if ($value instanceof QueryBuilderInterface) {
            return new SubQuery($value);
        }
        if ($value instanceof QueryFragmentInterface) {
            return $value;
        }
        $type = gettype($value);
        throw new \InvalidArgumentException("Invalid creteria, got '$type'.");
    }

    /**
     * @param string  $func
     * @param mixed[] $args
     * @return Func
     */
    public static function call($func, array $args)
    {
        return new Func($func, array_map('self::value', $args));
    }

    /**
     * @param string $operator
     * @param mixed  $vlaue
     * @return QueryFragmentInterface
     */
    private static function unaryOperator($operator, $value)
    {
        switch ($operator) {
        case 'ALL';
        case 'NOT ALL';
        case 'ANY';
        case 'NOT ANY';
        case 'SOME';
        case 'NOT SOME';
        case 'EXISTS';
        case 'NOT EXISTS';
            return new PrefixOperator($operator, self::value($value));
        }
        throw new \InvalidArgumentException("Invalid operator, got '$operator'.");
    }

    /**
     * @param string $lhr
     * @param string $operator
     * @param mixed  $rhs
     * @return QueryFragmentInterface
     */
    private static function operator($lhs, $operator, $rhs)
    {
        switch ($operator) {
        case '=':
        case '!=':
        case '<>':
        case '<=>':
        case '<':
        case '<=':
        case '!<':
        case '>':
        case '>=':
        case '!>':
        case 'IN':
        case 'NOT IN':
        case 'LIKE':
        case 'NOT LIKE':
        case 'REGEXP':
        case 'NOT REGEXP':
        case 'IS':
        case 'IS NOT':
            $lhs = self::str($lhs);
            $rhs = self::value($rhs);
            return new Operator($operator, $lhs, $rhs);
        case 'BETWEEN':
        case 'NOT BETWEEN':
            $lhs = self::str($lhs);
            $min = self::value($rhs[0]);
            $max = self::value($rhs[1]);
            return new BetweenOperator($operator, $lhs, $min, $max);
        }
        throw new \InvalidArgumentException("Invalid operator, got '$operator'.");
    }
}
