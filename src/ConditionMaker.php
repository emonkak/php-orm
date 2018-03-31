<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

class ConditionMaker
{
    /**
     * @var GrammarInterface
     */
    private $grammar;

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
        switch (func_num_args() - 1) {
            case 1:
                $expr = $arg1;
                return $grammar->liftExpr($expr);
            case 2:
                $operator = $arg1;
                $rhs = $grammar->liftLiteral($arg2);
                return $grammar->unaryOperator($operator, $rhs);
            case 3:
                $lhs = $grammar->liftExpr($arg1);
                $operator = $arg2;
                $rhs = $grammar->liftLiteral($arg3);
                return $grammar->operator($operator, $lhs, $rhs);
            default:
                $lhs = $grammar->liftExpr($arg1);
                $operator = $arg2;
                $start = $grammar->liftLiteral($arg3);
                $end = $grammar->liftLiteral($arg4);
                return $grammar->betweenOperator($operator, $lhs, $start, $end);
        }
    }

    /**
     * @param GrammarInterface $grammar
     */
    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * @param mixed      $arg1
     * @param mixed|null $arg2
     * @param mixed|null $arg3
     * @param mixed|null $arg4
     * @return Sql
     */
    public function __invoke($arg1, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        return self::make($this->grammar, ...func_get_args());
    }

    /**
     * @param string  $operator
     * @param mixed[] $arguments
     * @return Sql
     */
    public function __call($operator, array $arguments)
    {
        switch (count($arguments)) {
            case 0:
                throw new \InvalidArgumentException('
                    The operator function requires at least 1 argument.
                ');
            case 1:
                $operator = strtoupper($operator);
                $rhs = $this->grammar->liftLiteral($arguments[0]);
                return $this->grammar->unaryOperator($operator, $rhs);
            case 2:
                $operator = strtoupper($operator);
                $lhs = $this->grammar->liftExpr($arguments[0]);
                $rhs = $this->grammar->liftLiteral($arguments[1]);
                return $this->grammar->operator($operator, $lhs, $rhs);
            default:
                $operator = strtoupper($operator);
                $lhs = $this->grammar->liftExpr($arguments[0]);
                $start = $this->grammar->liftLiteral($arguments[1]);
                $end = $this->grammar->liftLiteral($arguments[2]);
                return $this->grammar->betweenOperator($operator, $lhs, $start, $end);
        }
    }

    /**
     * @return GrammarInterface
     */
    public function getGrammar()
    {
        return $this->grammar;
    }
}
