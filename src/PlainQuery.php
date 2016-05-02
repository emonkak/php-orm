<?php

namespace Emonkak\Orm;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

class PlainQuery implements QueryInterface
{
    use Executable, Observable {
        Observable::execute insteadof Executable;
        Observable::getResult insteadof Executable;
        Executable::execute as executeWithoutObservers;
        Executable::getResult as getResultWithoutObservers;
    }
    use Relatable;
    use ToStringable;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var mixed[]
     */
    private $binds;

    /**
     * @param QueryFragmentInterface $query
     * @return PlainQuery
     */
    public static function fromQuery(QueryFragmentInterface $query)
    {
        list ($sql, $binds) = $query->build();
        return new PlainQuery($sql, $binds);
    }

    /**
     * @param string  $sql
     * @param mixed[] $binds
     */
    public function __construct($sql, $binds)
    {
        $this->sql = $sql;
        $this->binds = $binds;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return [$this->sql, $this->binds];
    }
}
