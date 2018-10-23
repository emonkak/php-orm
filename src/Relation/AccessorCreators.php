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
        if ($class !== null) {
            $func = static function($obj) use ($key) {
                return $obj->$key;
            };
        } else {
            $func = static function($array) use ($key) {
                return $array[$key];
            };
        }

        return \Closure::bind($func, null, $class);
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toPivotKeySelector($key, $class)
    {
        if ($class !== null) {
            $func = static function($obj) use ($key) {
                $pivot = $obj->$key;
                unset($obj->$key);
                return $pivot;
            };
        } else {
            $func = static function(&$array) use ($key) {
                $pivot = $array[$key];
                unset($array[$key]);
                return $pivot;
            };
        }

        return \Closure::bind($func, null, $class);
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toKeyEraser($key, $class)
    {
        if ($class !== null) {
            $func = static function($obj) use ($key) {
                unset($obj->$key);
                return $obj;
            };
        } else {
            $func = static function($array) use ($key) {
                unset($array[$key]);
                return $array;
            };
        }

        return \Closure::bind($func, null, $class);
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toKeyAssignee($key, $class)
    {
        if ($class !== null) {
            $func = static function($lhs, $rhs) use ($key) {
                if ($rhs !== null) {
                    $lhs->$key = $rhs;
                }
                return $lhs;
            };
        } else {
            $func = static function($lhs, $rhs) use ($key) {
                if ($rhs !== null) {
                    $lhs[$key] = $rhs;
                }
                return $lhs;
            };
        }

        return \Closure::bind($func, null, $class);
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
