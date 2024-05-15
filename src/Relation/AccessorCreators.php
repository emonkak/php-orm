<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

final class AccessorCreators
{
    /**
     * @template T
     * @template TKey
     * @param ?class-string<T> $class
     * @return callable(T):TKey
     */
    public static function createKeySelector(?string $class, string $key): callable
    {
        if ($class !== null) {
            /** @var callable(T):TKey */
            return \Closure::bind(
                /**
                 * @param T $obj
                 * @return TKey
                 */
                static function(mixed $obj) use ($key): mixed {
                    return $obj->$key;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @param T $array
                 * @return TKey
                 */
                static function(mixed $array) use ($key): mixed {
                    return $array[$key];
                };
        }
    }

    /**
     * @template T
     * @template TKey
     * @param ?class-string<T> $class
     * @return callable(T):TKey
     */
    public static function createPivotKeySelector(?string $class, string $key): callable
    {
        if ($class !== null) {
            /** @var callable(T):TKey */
            return \Closure::bind(
                /**
                 * @param T $obj
                 * @return TKey
                 */
                static function(mixed $obj) use ($key): mixed {
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
                 * @param T $array
                 * @return TKey
                 */
                static function(mixed &$array) use ($key): mixed {
                    $pivot = $array[$key];
                    unset($array[$key]);
                    return $pivot;
                };
        }
    }

    /**
     * @template T
     * @param ?class-string<T> $class
     * @return callable(T):T
     */
    public static function createKeyEraser(?string $class, string $key): callable
    {
        if ($class !== null) {
            /** @var callable(T):T */
            return \Closure::bind(
                /**
                 * @param T $obj
                 * @return T
                 */
                static function(mixed $obj) use ($key): mixed {
                    unset($obj->$key);
                    return $obj;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @param T $array
                 * @return T
                 */
                static function(mixed $array) use ($key): mixed {
                    unset($array[$key]);
                    return $array;
                };
        }
    }

    /**
     * @template TLhs
     * @template TRhs
     * @param ?class-string<TLhs> $class
     * @return callable(TLhs,TRhs):TLhs
     */
    public static function createKeyAssignor(?string $class, string $key): callable
    {
        if ($class !== null) {
            /** @var callable(TLhs,TRhs):TLhs */
            return \Closure::bind(
                /**
                 * @param TLhs $lhs
                 * @param TRhs $rhs
                 * @return TLhs
                 */
                static function(mixed $lhs, mixed $rhs) use ($key): mixed {
                    $lhs->$key = $rhs;
                    return $lhs;
                },
                null,
                $class
            );
        } else {
            return
                /**
                 * @param TLhs $lhs
                 * @param TRhs $rhs
                 * @return TLhs
                 */
                static function(mixed $lhs, mixed $rhs) use ($key): mixed {
                    if ($rhs !== null) {
                        $lhs[$key] = $rhs;
                    }
                    /** @var TLhs $lhs */
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
