<?php

namespace Emonkak\Orm\Command;

class PlainCommand extends AbstractCommand
{
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
    public function __construct($sql, array $binds)
    {
        $this->sql = $sql;
        $this->binds = $binds;
    }

    /**
     * {@inheritDoc}
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getBinds()
    {
        return $this->binds;
    }
}
