<?php

namespace Emonkak\Orm\Query;

use Emonkak\QueryBuilder\ToStringable;

class PlainQuery implements ExecutableQueryInterface
{
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
     * @param string  $sql
     * @param mixed[] $binds
     */
    public static function create($sql, array $binds = [])
    {
        return new self($sql, $binds);
    }

    /**
     * @param string  $sql
     * @param mixed[] $binds
     */
    public function __construct($sql, array $binds = [])
    {
        $this->sql = $sql;
        $this->binds = $binds;
    }

    /**
     * {@inheritDoc}
     */
    public function compile()
    {
        return [$this->sql, $this->binds];
    }
}
