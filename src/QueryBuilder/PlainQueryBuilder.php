<?php

namespace Emonkak\Orm\QueryBuilder;

class PlainQueryBuilder implements QueryBuilderInterface
{
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
    final public static function fromQuery(QueryBuilderInterface $query)
    {
        list ($sql, $binds) = $query->build();
        return new static($sql, $binds);
    }

    /**
     * @param string  $sql
     * @param mixed[] $binds
     */
    final public function __construct($sql, $binds)
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
