<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

final class AccessorCreators
{
    /**
     * @template T
     * @template TKey
     * @psalm-param ?class-string<T> $class
     * @psalm-return callable(T):TKey
     */
    public static function createKeySelector(?string $class, string $key): callable
    {
        if ($class !== null) {
            return \Closure::bind(
                /**
                 * @psalm-param T $obj
                 * @psalm-return TKey
                 */
                static function($obj) use ($key) {
                    return $obj->$key;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @psalm-param T $array
                 * @psalm-return TKey
                 */
                static function($array) use ($key) {
                    return $array[$key];
                };
        }
    }

    /**
     * @template T
     * @template TKey
     * @psalm-param ?class-string<T> $class
     * @psalm-return callable(T):TKey
     */
    public static function createPivotKeySelector(?string $class, string $key): callable
    {
        if ($class !== null) {
            return \Closure::bind(
                /**
                 * @psalm-param T $obj
                 * @psalm-return TKey
                 */
                static function($obj) use ($key) {
                    $pivot = $obj->$key;
                    unset($obj->$key);
                    return $pivot;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @psalm-param T $array
                 * @psalm-return TKey
                 */
                static function(&$array) use ($key) {
                    $pivot = $array[$key];
                    unset($array[$key]);
                    return $pivot;
                };
        }
    }

    /**
     * @template T
     * @psalm-param ?class-string<T> $class
     * @psalm-return callable(T):T
     */
    public static function createKeyEraser(?string $class, string $key): callable
    {
        if ($class !== null) {
            return \Closure::bind(
                /**
                 * @psalm-param T $obj
                 * @psalm-return T
                 */
                static function($obj) use ($key) {
                    unset($obj->$key);
                    return $obj;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @psalm-param T $array
                 * @psalm-return T
                 */
                static function($array) use ($key) {
                    unset($array[$key]);
                    return $array;
                };
        }
    }

    /**
     * @template TLhs
     * @template TRhs
     * @psalm-param ?class-string<TLhs> $class
     * @psalm-param string $key
     * @psalm-return callable(TLhs,TRhs):TLhs
     */
    public static function createKeyAssignee(?string $class, string $key): callable
    {
        if ($class !== null) {
            return \Closure::bind(
                /**
                 * @psalm-param TLhs $lhs
                 * @psalm-param TRhs $rhs
                 * @psalm-return TLhs
                 */
                static function($lhs, $rhs) use ($key) {
                    $lhs->$key = $rhs;
                    return $lhs;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @psalm-param TLhs $lhs
                 * @psalm-param TRhs $rhs
                 * @psalm-return TLhs
                 */
                static function($lhs, $rhs) use ($key) {
                    /** @psalm-suppress RedundantConditionGivenDocblockType */
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
