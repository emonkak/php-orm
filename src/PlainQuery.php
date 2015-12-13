<?php

namespace Emonkak\Orm;

use Emonkak\QueryBuilder\QueryBuilderInterface;
use Emonkak\QueryBuilder\ToStringable;
use Emonkak\QueryBuilder\Chainable;

class PlainQuery implements ExecutableQueryInterface
{
    use Chainable;
    use Executable;
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
     * @param QueryBuilderInterface $query
     * @return self
     */
    public static function fromQuery(QueryBuilderInterface $query)
    {
        list ($sql, $binds) = $query->build();
        return new self($sql, $binds);
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
