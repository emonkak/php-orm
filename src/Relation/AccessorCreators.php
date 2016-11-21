<?php

namespace Emonkak\Orm\Relation;

/**
 * @internal
 */
class AccessorCreators
{
    /**
     * @codeCoverageIgnore
     */
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
        if ($class !== null) {
            return \Closure::bind(
                static function($obj) use ($key) {
                    return $obj->$key;
                },
                null,
                $class
            );
        } else {
            return static function($array) use ($key) {
                return $array[$key];
            };
        }
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toPivotKeySelector($key, $class)
    {
        if ($class !== null) {
            return \Closure::bind(
                static function($obj) use ($key) {
                    $pivot = $obj->$key;
                    unset($obj->$key);
                    return $pivot;
                },
                null,
                $class
            );
        } else {
            return static function(&$array) use ($key) {
                $pivot = $array[$key];
                unset($array[$key]);
                return $pivot;
            };
        }
    }

    /**
     * @param string $key
     * @param string $class
     * @return \Closure
     */
    public static function toKeyAssignee($key, $class)
    {
        if ($class !== null) {
            return \Closure::bind(
                static function($lhs, $rhs) use ($key) {
                    $lhs->$key = $rhs;
                    return $lhs;
                },
                null,
                $class
            );
        } else {
            return static function($lhs, $rhs) use ($key) {
                $lhs[$key] = $rhs;
                return $lhs;
            };
        }
    }
}
