<?php

namespace Emonkak\Orm\Relation;

/**
 * @internal
 */
final class AccessorCreators
{
    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toKeySelector($key, $class)
    {
        return \Closure::bind(
            static function($obj) use ($key) {
                return $obj->$key;
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
    public static function toPivotKeySelector($key, $class)
    {
        return \Closure::bind(
            static function($obj) use ($key) {
                $pivot = $obj->$key;
                unset($obj->$key);
                return $pivot;
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
    public static function toKeyEraser($key, $class)
    {
        return \Closure::bind(
            static function($obj) use ($key) {
                unset($obj->$key);
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
            static function($lhs, $rhs) use ($key) {
                $lhs->$key = $rhs;
                return $lhs;
            },
            null,
            $class
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
