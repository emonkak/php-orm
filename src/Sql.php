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
     * @suppress PhanTypeExpectedObjectPropAccess
     *
     * @param string $format
     * @param Sql[]  ...$sqls
     * @return Sql
     */
    public static function format($format, ...$sqls)
    {
        $tmpSqls = [];
        $tmpBindings = [];

        foreach ($sqls as $sql) {
            $tmpSqls[] = $sql->sql;
            $tmpBindings[] = $sql->bindings;
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
     * @param Sql $lhs
     * @param Sql ...$rest
     * @return Sql
     */
    public static function _and(Sql $lhs, Sql ...$rest)
    {
        $tmpSql = $lhs->sql;
        $tmpBindings = [$lhs->bindings];

        foreach ($rest as $rhs) {
            $tmpSql = '(' . $tmpSql . ' AND ' . $rhs->sql . ')';
            $tmpBindings[] = $rhs->bindings;
        }

        $bindings = array_merge(...$tmpBindings);

        return new Sql($tmpSql, $bindings);
    }

    /**
     * @param Sql $lhs
     * @param Sql ...$rest
     * @return Sql
     */
    public static function _or(Sql $lhs, Sql ...$rest)
    {
        $tmpSql = $lhs->sql;
        $tmpBindings = [$lhs->bindings];

        foreach ($rest as $rhs) {
            $tmpSql = '(' . $tmpSql . ' OR ' . $rhs->sql . ')';
            $tmpBindings[] = $rhs->bindings;
        }

        $bindings = array_merge(...$tmpBindings);

        return new Sql($tmpSql, $bindings);
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
     * @param QueryBuilderInterface $queryBuilder
     * @param string                $separator
     * @return Sql
     */
    public function appendQueryBuilder(QueryBuilderInterface $queryBuilder, $separator = ' ')
    {
        $query = $queryBuilder->build();
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
     * @param QueryBuilderInterface $queryBuilder
     * @param string                $separator
     * @return Sql
     */
    public function prependQueryBuilder(QueryBuilderInterface $queryBuilder, $separator = ' ')
    {
        $query = $queryBuilder->build();
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
