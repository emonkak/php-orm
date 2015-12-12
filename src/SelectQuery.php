<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\QueryBuilder\Chainable;
use Emonkak\QueryBuilder\Creteria;
use Emonkak\QueryBuilder\SelectQueryBuilderTrait;
use Emonkak\QueryBuilder\ToStringable;

class SelectQuery implements ExecutableQueryInterface
{
    use Chainable;
    use Relatable;
    use SelectQueryBuilderTrait;
    use ToStringable;
    use Executable, Observable {
        Observable::execute insteadof Executable;
        Executable::execute as executeWithoutObservers;
    }

    /**
     * @param PDOInterface $connection
     * @param string       $func
     * @param mixed        $expr
     * @return mixed
     */
    public function aggregate(PDOInterface $connection, $func, $expr)
    {
        return $this
            ->withSelect([Creteria::of($expr)->apply($func)])
            ->executeWithoutObservers($connection)
            ->value();
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function count(PDOInterface $connection, $expr = '*')
    {
        return (int) $this->aggregate($connection, 'COUNT', $expr);
    }

    /**
     * @param PDOInterface $connection
     * @param integer $perPage
     * @return Paginator
     */
    public function paginate(PDOInterface $connection, $perPage)
    {
        $totalItems = $this->count($connection);
        return new Paginator($this, $connection, $perPage, $totalItems);
    }
}
