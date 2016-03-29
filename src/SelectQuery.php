<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\QueryBuilder\Chainable;
use Emonkak\QueryBuilder\Creteria;
use Emonkak\QueryBuilder\SelectQueryBuilderTrait;
use Emonkak\QueryBuilder\ToStringable;

class SelectQuery implements ExecutableQueryInterface
{
    use Chainable;
    use SelectQueryBuilderTrait;
    use ToStringable;
    use Executable, Observable {
        Observable::execute insteadof Executable;
        Observable::getResult insteadof Executable;
        Executable::execute as executeWithoutObservers;
        Executable::getResult as getResultWithoutObservers;
    }

    /**
     * @param RelationInterface $relation
     * @param PDOInterface      $relationConnection
     * @param callable|null     $constraint
     * @return SelectQuery
     */
    public function with(RelationInterface $relation, PDOInterface $relationConnection = null, callable $constraint = null)
    {
        return $this->observe(static function(ExecutableQueryInterface $query, PDOInterface $connection) use ($relation, $relationConnection, $constraint) {
            return new RelationQuery(
                $query,
                $relationConnection ?: $connection,
                $relation,
                $constraint ?: function($query) {
                    return $query;
                }
            );
        });
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function avg(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'AVG');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function count(PDOInterface $connection, $expr = '*')
    {
        return (int) $this->aggregate($connection, $expr, 'COUNT');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function max(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'MAX');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function min(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'MIN');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function sum(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'SUM');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @param string       $func
     * @return mixed
     */
    public function aggregate(PDOInterface $connection, $expr, $func)
    {
        return $this
            ->withSelect([Creteria::of($func)->call([$expr])])
            ->execute($connection)
            ->fetchColumn();
    }

    /**
     * @param PDOInterface $connection
     * @param integer      $perPage
     * @param string       $class
     * @return Paginator
     */
    public function paginate(PDOInterface $connection, $perPage, $class)
    {
        $numItems = $this->count($connection);
        return new Paginator($this, $connection, $perPage, $numItems, $class);
    }
}
