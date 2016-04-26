<?php

namespace Emonkak\Orm\Relation;

class AccessorCreators
{
    private function __construct()
    {
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toKeySelector($key, $class)
    {
        return \Closure::bind(
            static function($value) use ($key) {
                return $value->$key;
            },
            null,
            $class
        );
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toKeyAssignee($key, $class)
    {
        return \Closure::bind(
            static function($left, $right) use ($key) {
                $left->$key = $right;
                return $left;
            },
            null,
            $class
        );
    }
}
