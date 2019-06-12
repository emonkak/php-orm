<?php

namespace Emonkak\Orm;

class Sql implements QueryBuilderInterface
{
    use Explainable;
    use Fetchable;
    use Preparable;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var mixed
     */
    private $bindings;

    /**
     * @param string $format
     * @param Sql[]  ...$args
     * @return Sql
     */
    public static function format($format, ...$args)
    {
        $tmpSqls = [];
        $tmpBindings = [];

        foreach ($args as $arg) {
            $tmpSqls[] = $arg->sql;
            $tmpBindings[] = $arg->bindings;
        }

        $sql = vsprintf($format, $tmpSqls);
        $bindings = array_merge(...$tmpBindings);

        return new Sql($sql, $bindings);
    }

    /**
     * @param string $separator
     * @param Sql[]  $queries
     * @return Sql
     */
    public static function join($separator, array $queries)
    {
        $tmpSqls = [];
        $tmpBindings = [];

        foreach ($queries as $query) {
            $tmpSqls[] = $query->sql;
            $tmpBindings[] = $query->bindings;
        }

        $sql = implode($separator, $tmpSqls);
        $bindings = array_merge(...$tmpBindings);

        return new Sql($sql, $bindings);
    }

    /**
     * @param mixed $value
     * @return Sql
     */
    public static function value($value)
    {
        return new Sql('?', [$value]);
    }

    /**
     * @param mixed[] $values
     * @return Sql
     */
    public static function values(array $values)
    {
        $placeholders = array_fill(0, count($values), '?');
        $sql = '(' . implode(', ', $placeholders) . ')';
        return new Sql($sql, $values);
    }

    /**
     * @param mixed $value
     * @return Sql
     */
    public static function expr($value)
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            return $value->build()->enclosed();
        }
        if (is_string($value)) {
            return new Sql($value);
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as an expression, got '$type'.");
    }

    /**
     * @param mixed $value
     * @return Sql
     */
    public static function literal($value)
    {
        if ($value instanceof Sql) {
            return $value;
        }
        if ($value instanceof QueryBuilderInterface) {
            return $value->build()->enclosed();
        }
        if ($value === null) {
            return new Sql('NULL');
        }
        if (is_scalar($value)) {
            return Sql::value($value);
        }
        if (is_array($value)) {
            return Sql::values($value);
        }
        $type = is_object($value) ? get_class($value) : gettype($value);
        throw new \UnexpectedValueException("The value can not be lifted as a literal, got '$type'.");
    }

    /**
     * @param Sql $lhs
     * @param Sql ...$rest
     * @return Sql
     */
    public static function _and(Sql $lhs, Sql ...$rest)
    {
        $sql = $lhs->sql;
        $nestedBindings = [$lhs->bindings];

        foreach ($rest as $rhs) {
            $sql = '(' . $sql . ' AND ' . $rhs->sql . ')';
            $nestedBindings[] = $rhs->bindings;
        }

        $bindings = array_merge(...$nestedBindings);

        return new Sql($sql, $bindings);
    }

    /**
     * @param Sql $lhs
     * @param Sql ...$rest
     * @return Sql
     */
    public static function _or(Sql $lhs, Sql ...$rest)
    {
        $sql = $lhs->sql;
        $nestedBindings = [$lhs->bindings];

        foreach ($rest as $rhs) {
            $sql = '(' . $sql . ' OR ' . $rhs->sql . ')';
            $nestedBindings[] = $rhs->bindings;
        }

        $bindings = array_merge(...$nestedBindings);

        return new Sql($sql, $bindings);
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     */
    public function __construct($sql, array $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $format = str_replace(['%', '?'], ['%%', '%s'], $this->sql);
        $args = array_map(function($binding) {
            switch (gettype($binding)) {
            case 'integer':
            case 'double':
                return $binding;
            case 'boolean':
                return $binding ? 1 : 0;
            case 'NULL':
                return 'NULL';
            default:
                if (mb_check_encoding($binding, 'utf-8')) {
                    return "'" . addslashes($binding) . "'";
                } else {  // binary string
                    return "x'" . bin2hex($binding) . "'";
                }
            }
        }, $this->bindings);
        return vsprintf($format, $args);
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return mixed[]
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     * @param string  $separator
     * @return Sql
     */
    public function append($sql, array $bindings = [], $separator = ' ')
    {
        return new Sql(
            $this->sql . $separator . $sql,
            array_merge($this->bindings, $bindings)
        );
    }

    /**
     * @param Sql    $query
     * @param string $separator
     * @return Sql
     */
    public function appendQuery(Sql $query, $separator = ' ')
    {
        return $this->append($query->sql, $query->bindings, $separator);
    }

    /**
     * @param QueryBuilderInterface $builder
     * @param string                $separator
     * @return Sql
     */
    public function appendBuilder(QueryBuilderInterface $builder, $separator = ' ')
    {
        $query = $builder->build();
        return $this->append('(' . $query->sql . ')', $query->bindings, $separator);
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     * @param string  $separator
     */
    public function prepend($sql, array $bindings = [], $separator = ' ')
    {
        return new Sql(
            $sql . $separator . $this->sql,
            array_merge($bindings, $this->bindings)
        );
    }

    /**
     * @param Sql    $query
     * @param string $separator
     * @return Sql
     */
    public function prependQuery(Sql $query, $separator = ' ')
    {
        return $this->prepend($query->sql, $query->bindings, $separator);
    }

    /**
     * @param QueryBuilderInterface $builder
     * @param string                $separator
     * @return Sql
     */
    public function prependBuilder(QueryBuilderInterface $builder, $separator = ' ')
    {
        $query = $builder->build();
        return $this->prepend('(' . $query->sql . ')', $query->bindings, $separator);
    }

    /**
     * @return Sql
     */
    public function enclosed()
    {
        return new Sql(
            '(' . $this->sql . ')',
            $this->bindings
        );
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this;
    }
}
