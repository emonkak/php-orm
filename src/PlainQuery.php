<?php

namespace Emonkak\Orm;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Sql;

class PlainQuery implements QueryInterface
{
    use Executable;
    use Relatable;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var mixed[]
     */
    private $bindings;

    /**
     * @param QueryBuilderInterface $query
     * @return PlainQuery
     */
    public static function fromBuilder(QueryBuilderInterface $builder)
    {
        $query = $builder->build();
        return new PlainQuery($query->getSql(), $query->getBindings());
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     */
    public function __construct($sql, $bindings)
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return new Sql($this->sql, $this->bindings);
    }
}
