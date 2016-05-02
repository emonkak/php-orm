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
     * @param string $prop
     * @param string $class
     * @return \Closure
     */
    public static function toKeySelector($prop, $class)
    {
        return \Closure::bind(
            static function($obj) use ($prop) {
                return $obj->$prop;
            },
            null,
            $class
        );
    }

    /**
     * @param string $prop
     * @param string $class
     * @return \Closure
     */
    public static function toPivotKeySelector($prop, $class)
    {
        return \Closure::bind(
            static function($obj) use ($prop) {
                $pivot = $obj->$prop;
                unset($obj->$prop);
                return $pivot;
            },
            null,
            $class
        );
    }

    /**
     * @param string $prop
     * @param string $class
     * @return \Closure
     */
    public static function toKeyAssignee($prop, $class)
    {
        return \Closure::bind(
            static function($lhs, $rhs) use ($prop) {
                $lhs->$prop = $rhs;
                return $lhs;
            },
            null,
            $class
        );
    }
}
