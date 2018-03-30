<?php

namespace Emonkak\Orm\Grammar;

abstract class AbstractGrammar implements GrammarInterface
{
    /**
     * @param mixed      $arg1
     * @param mixed|null $arg2
     * @param mixed|null $arg3
     * @param mixed|null $arg4
     * @return Sql
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        switch (func_num_args()) {
            case 1:
                $expr = $arg1;
                return $this->lift($expr);
            case 2:
                $operator = $arg1;
                $rhs = $this->liftValue($arg2);
                return $this->unaryOperator($operator, $rhs);
            case 3:
                $lhs = $this->lift($arg1);
                $operator = $arg2;
                $rhs = $this->liftValue($arg3);
                return $this->operator($lhs, $operator, $rhs);
            default:
                $lhs = $this->lift($arg1);
                $operator = $arg2;
                $start = $this->liftValue($arg3);
                $end = $this->liftValue($arg4);
                return $this->betweenOperator($lhs, $operator, $start, $end);
        }
    }
}
