<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

trait Conditional
{
    /**
     * @return GrammarInterface
     */
    abstract public function getGrammar();

    /**
     * @param mixed      $arg1
     * @param mixed|null $arg2
     * @param mixed|null $arg3
     * @param mixed|null $arg4
     * @return Sql
     */
    public function condition($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $grammar = $this->getGrammar();

        switch (func_num_args()) {
            case 1:
                $expr = Sql::expr($arg1);
                return $expr;
            case 2:
                $operator = $arg1;
                $rhs = Sql::expr($arg2);
                return $grammar->unaryOperator($arg1, $rhs);
            case 3:
                $operator = $arg2;
                $lhs = Sql::expr($arg1);
                $rhs = Sql::literal($arg3);
                return $grammar->operator($operator, $lhs, $rhs);
            default:
                $operator = $arg2;
                $lhs = Sql::expr($arg1);
                $start = Sql::literal($arg3);
                $end = Sql::literal($arg4);
                return $grammar->betweenOperator($operator, $lhs, $start, $end);
        }
    }
}
