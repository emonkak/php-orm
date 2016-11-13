<?php

namespace Emonkak\Orm\QueryBuilder;

class Sql
{
    /**
     * @var string
     */
    private $sql;

    /**
     * @var mixed
     */
    private $bindings;

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     */
    public function __construct($sql, array $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
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
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $format = str_replace(['%', '?'], ['%%', '%s'], $this->getSql());
        $args = array_map(function($binding) {
            switch (gettype($binding)) {
            case 'integer':
            case 'double':
                return $binding;
            case 'boolean':
                return $binding ? 1 : 0;
            case 'NULL':
                return 'NULL';
            default:
                if (mb_check_encoding($binding, 'utf-8')) {
                    return "'" . addslashes($binding) . "'";
                } else {  // binary string
                    return "x'" . bin2hex($binding) . "'";
                }
                break;
            }
        }, $this->getBindings());
        return vsprintf($format, $args);
    }
}
