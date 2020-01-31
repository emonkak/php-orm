<?php

namespace Emonkak\Orm\Relation;

final class AccessorCreators
{
    /**
     * @param string        $key
     * @param ?class-string $class
     * @return \Closure
     */
    public static function createKeySelector($key, $class)
    {
        if ($class !== null) {
            return \Closure::bind(static function($obj) use ($key) {
                return $obj->$key;
            }, null, $class);
        } else {
            return static function($array) use ($key) {
                return $array[$key];
            };
        }
    }

    /**
     * @param string        $key
     * @param ?class-string $class
     * @return \Closure
     */
    public static function createPivotKeySelector($key, $class)
    {
        if ($class !== null) {
            return \Closure::bind(static function($obj) use ($key) {
                $pivot = $obj->$key;
                unset($obj->$key);
                return $pivot;
            }, null, $class);
        } else {
            return static function(&$array) use ($key) {
                $pivot = $array[$key];
                unset($array[$key]);
                return $pivot;
            };
        }
    }

    /**
     * @param string        $key
     * @param ?class-string $class
     * @return \Closure
     */
    public static function createKeyEraser($key, $class)
    {
        if ($class !== null) {
            return \Closure::bind(static function($obj) use ($key) {
                unset($obj->$key);
                return $obj;
            }, null, $class);
        } else {
            return static function($array) use ($key) {
                unset($array[$key]);
                return $array;
            };
        }
    }

    /**
     * @param string        $key
     * @param ?class-string $class
     * @return \Closure
     */
    public static function createKeyAssignee($key, $class)
    {
        if ($class !== null) {
            return \Closure::bind(static function($lhs, $rhs) use ($key) {
                if ($rhs !== null) {
                    $lhs->$key = $rhs;
                }
                return $lhs;
            }, null, $class);
        } else {
            return static function($lhs, $rhs) use ($key) {
                if ($rhs !== null) {
                    $lhs[$key] = $rhs;
                }
                return $lhs;
            };
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
