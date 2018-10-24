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
     * @return SelectBuilder
     */
    public function getSelect()
    {
        return new SelectBuilder($this);
    }

    /**
     * @return InsertBuilder
     */
    public function getInsert()
    {
        return new InsertBuilder($this);
    }

    /**
     * @return UpdateBuilder
     */
    public function getUpdate()
    {
        return new UpdateBuilder($this);
    }

    /**
     * @return DeleteBuilder
     */
    public function getDelete()
    {
        return new DeleteBuilder($this);
    }

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
                $expr = Sql::expr($arg1);
                return $expr;
            case 2:
                $operator = $arg1;
                $rhs = Sql::expr($arg2);
                return $this->unaryOperator($arg1, $rhs);
            case 3:
                $operator = $arg2;
                $lhs = Sql::expr($arg1);
                $rhs = Sql::literal($arg3);
                return $this->operator($operator, $lhs, $rhs);
            default:
                $operator = $arg2;
                $lhs = Sql::expr($arg1);
                $start = Sql::literal($arg3);
                $end = Sql::literal($arg4);
                return $this->betweenOperator($operator, $lhs, $start, $end);
        }
    }
}
