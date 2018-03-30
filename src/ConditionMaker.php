<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

final class ConditionMaker
{
    /**
     * @param GrammarInterface $grammar
     * @param mixed            $arg1
     * @param mixed|null       $arg2
     * @param mixed|null       $arg3
     * @param mixed|null       $arg4
     * @return Sql
     */
    public static function make(GrammarInterface $grammar, $arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        switch (func_num_args()) {
            case 2:
                $expr = $arg1;
                return $grammar->lift($expr);
            case 3:
                $operator = $arg1;
                $rhs = $grammar->liftValue($arg2);
                return $grammar->unaryOperator($operator, $rhs);
            case 4:
                $lhs = $grammar->lift($arg1);
                $operator = $arg2;
                $rhs = $grammar->liftValue($arg3);
                return $grammar->operator($operator, $lhs, $rhs);
            default:
                $lhs = $grammar->lift($arg1);
                $operator = $arg2;
                $start = $grammar->liftValue($arg3);
                $end = $grammar->liftValue($arg4);
                return $grammar->betweenOperator($operator, $lhs, $start, $end);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
    }
}
