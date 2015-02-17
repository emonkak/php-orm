<?php

namespace Emonkak\Orm\Utils;

class QueryUtils
{
    private function __construct() {}

    public static function toString($sql, array $binds)
    {
        $format = str_replace(['%', '?'], ['%%', '%s'], $sql);
        $args = array_map(function($bind) { return var_export($bind, true); }, $binds);
        return vsprintf($format, $args);
    }
}
