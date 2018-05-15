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
     * @return GrammarInterface
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function equal($lhs, $rhs)
    {
        return $this->applyOperator('=', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function notEqual($lhs, $rhs)
    {
        return $this->applyOperator('<>', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function lessThan($lhs, $rhs)
    {
        return $this->applyOperator('<', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function lessThanOrEqual($lhs, $rhs)
    {
        return $this->applyOperator('<=', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function greaterThan($lhs, $rhs)
    {
        return $this->applyOperator('>', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function greaterThanOrEqual($lhs, $rhs)
    {
        return $this->applyOperator('>=', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @return Sql
     */
    public function isNull($lhs)
    {
        return $this->applyOperator('IS', $lhs, null);
    }

    /**
     * @param mixed $lhs
     * @return Sql
     */
    public function isNotNull($lhs)
    {
        return $this->applyOperator('IS NOT', $lhs, null);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function in($lhs, $rhs)
    {
        return $this->applyOperator('IN', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function notIn($lhs, $rhs)
    {
        return $this->applyOperator('NOT IN', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function like($lhs, $rhs)
    {
        return $this->applyOperator('LIKE', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function notLike($lhs, $rhs)
    {
        return $this->applyOperator('NOT LIKE', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function _and($lhs, $rhs)
    {
        return $this->applyOperator('AND', $lhs, $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @return Sql
     */
    public function _or($lhs, $rhs)
    {
        return $this->applyOperator('OR', $lhs, $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function not($rhs)
    {
        return $this->applyUnaryOperator('NOT', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function exists($rhs)
    {
        return $this->applyUnaryOperator('EXISTS', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function notExists($rhs)
    {
        return $this->applyUnaryOperator('NOT EXISTS', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function all($rhs)
    {
        return $this->applyUnaryOperator('ALL', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function any($rhs)
    {
        return $this->applyUnaryOperator('ANY', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function notAny($rhs)
    {
        return $this->applyUnaryOperator('NOT ANY', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function notAll($rhs)
    {
        return $this->applyUnaryOperator('NOT ALL', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function some($rhs)
    {
        return $this->applyUnaryOperator('SOME', $rhs);
    }

    /**
     * @param mixed $rhs
     * @return Sql
     */
    public function notSome($rhs)
    {
        return $this->applyUnaryOperator('NOT SOME', $rhs);
    }

    /**
     * @param mixed $lhs
     * @param mixed $start
     * @param mixed $end
     * @return Sql
     */
    public function between($lhs, $start, $end)
    {
        return $this->applyBetweenOperator('BETWEEN', $lhs, $start, $end);
    }

    /**
     * @param mixed $lhs
     * @param mixed $start
     * @param mixed $end
     * @return Sql
     */
    public function notBetween($lhs, $start, $end)
    {
        return $this->applyBetweenOperator('NOT BETWEEN', $lhs, $start, $end);
    }

    /**
     * @param string $operator
     * @param mixed  $lhs
     * @param mixed  $rhs
     * @return Sql
     */
    private function applyOperator($operator, $lhs, $rhs)
    {
        $lhs = $this->grammar->liftExpr($lhs);
        $rhs = $this->grammar->liftLiteral($rhs);
        return $this->grammar->operator($operator, $lhs, $rhs);
    }

    /**
     * @param string $operator
     * @param mixed  $rhs
     * @return Sql
     */
    private function applyUnaryOperator($operator, $rhs)
    {
        $rhs = $this->grammar->liftLiteral($rhs);
        return $this->grammar->unaryOperator($operator, $rhs);
    }

    /**
     * @param string $operator
     * @param mixed  $lhs
     * @param mixed  $start
     * @param mixed  $end
     * @return Sql
     */
    private function applyBetweenOperator($operator, $lhs, $start, $end)
    {
        $lhs = $this->grammar->liftExpr($lhs);
        $start = $this->grammar->liftLiteral($start);
        $end = $this->grammar->liftLiteral($end);
        return $this->grammar->betweenOperator($operator, $lhs, $start, $end);
    }
}
