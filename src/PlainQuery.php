<?php

namespace Emonkak\Orm;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

class PlainQuery implements QueryInterface
{
    use Executable;
    use Relatable;
    use Stringable;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var mixed[]
     */
    private $binds;

    /**
     * @param QueryBuilderInterface $query
     * @return PlainQuery
     */
    public static function fromQuery(QueryBuilderInterface $query)
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
