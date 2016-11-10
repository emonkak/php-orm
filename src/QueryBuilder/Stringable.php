<?php

namespace Emonkak\Orm\QueryBuilder;

trait Stringable
{
    /**
     * @return string
     */
    public function __toString()
    {
        list ($sql, $binds) = $this->build();
        $format = str_replace(['%', '?'], ['%%', '%s'], $sql);
        $args = array_map(function($bind) {
            switch (gettype($bind)) {
            case 'integer':
            case 'double':
                return $bind;
            case 'boolean':
                return $bind ? 1 : 0;
            case 'NULL':
                return 'NULL';
            default:
                if (mb_check_encoding($bind, 'utf-8')) {
                    return "'" . addslashes($bind) . "'";
                } else {  // binary string
                    return sprintf("x'%s'", bin2hex($bind));
                }
                break;
            }
        }, $binds);
        return vsprintf($format, $args);
    }

    /**
     * @return array (sql, binds)
     */
    abstract public function build();
}
